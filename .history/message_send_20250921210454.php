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
  echo json_encode(['ok'=>false,'error'=>'csrf']);
  exit;
}

$recipient_id = (int)($_POST['recipient_id'] ?? 0);
$body = trim((string)($_POST['body'] ?? ''));
$body = mb_substr($body, 0, 2000); // borne

if ($recipient_id <= 0 || $body === '') {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'missing']);
  exit;
}
if ($recipient_id === (int)$_SESSION['user_id']) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'self']);
  exit;
}

// destinataire existe ?
$u = $mysqli->prepare('SELECT 1 FROM users WHERE id=? LIMIT 1');
$u->bind_param('i', $recipient_id);
$u->execute(); $u->store_result();
if ($u->num_rows === 0) {
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'recipient_not_found']);
  exit;
}
$u->close();

// insert
$ins = $mysqli->prepare('INSERT INTO messages (sender_id, recipient_id, body) VALUES (?,?,?)');
$ins->bind_param('iis', $_SESSION['user_id'], $recipient_id, $body);
if ($ins->execute()) {
  echo json_encode(['ok'=>true]);
} else {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db']);
}
