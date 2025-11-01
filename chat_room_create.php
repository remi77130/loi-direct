<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$csrf = $_POST['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$uid = (int)($_SESSION['user_id'] ?? 0);

/* Limite : 1 salon public / 24 h par utilisateur */
$st = $mysqli->prepare("
  SELECT TIMESTAMPDIFF(SECOND, MAX(created_at), NOW()) AS since_last
  FROM chat_rooms
  WHERE created_by=? AND is_private=0
");
$st->bind_param('i', $uid);
$st->execute();
$st->bind_result($since);
$st->fetch();
$st->close();

if ($since !== null && $since < 86400) {
  $retry = 86400 - (int)$since;
  http_response_code(429);
  header('Retry-After: '.$retry);
  echo json_encode(['ok'=>false,'error'=>'limit','retry_after'=>$retry]); exit;
}

/* === RÉCUP NOM + VALIDATION === */
$name = trim((string)($_POST['name'] ?? ''));
$name = mb_substr($name, 0, 60);
if ($name === '') {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'nom']); exit;
}

/* Création */
$ins = $mysqli->prepare(
  "INSERT INTO chat_rooms (name, is_private, created_by) VALUES (?, 0, ?)"
);
$ins->bind_param('si', $name, $uid);

if ($ins->execute()) {
  echo json_encode(['ok'=>true,'id'=>$mysqli->insert_id], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db']);
}
$ins->close();
