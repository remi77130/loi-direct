<?php
/**

 * chat_room_create.php
 * Création d’un salon public OU privé.
 * Sécurité :
 *  - Auth obligatoire
 *  - CSRF obligatoire
 *  - Rate-limit : 1 création (public ou privé) / 24 h par utilisateur
 * Données :
 *  - POST[name]        : string, 1..60
 *  - POST[is_private]  : "on" si coché, sinon absent
 *  - POST[password]    : requis si is_private
 * Sortie JSON :
 *  - { ok:true, id:int }  ou  { ok:false, error:string, retry_after?:int }
 */

declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/* --- CSRF --- */
$csrf = $_POST['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$uid = (int)($_SESSION['user_id'] ?? 0);

/* --- Params // --- Inputs ------ */
$name = trim((string)($_POST['name'] ?? ''));
$name = mb_substr($name, 0, 20);
$is_private = isset($_POST['is_private']) ? 1 : 0;
$pwd = (string)($_POST['password'] ?? '');

// Validation fonctionnelle

if ($name === '') { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'nom']); exit; }
if ($is_private === 1 && $pwd === '') { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'password']); exit; }

/* ---------------- Limite 1 salon / 24 h, tout type ---------------- */
$st = $mysqli->prepare("
  SELECT TIMESTAMPDIFF(SECOND, MAX(created_at), NOW()) AS since_last
  FROM chat_rooms
  WHERE created_by = ?
");
$st->bind_param('i', $uid);
$st->execute();
$st->bind_result($since);
$st->fetch();
$st->close();

if ($since !== null && (int)$since < 86400) {
  $retry = 86400 - (int)$since;
  http_response_code(429);
  header('Retry-After: '.$retry);  // optionnel, utile pour le client
  echo json_encode(['ok'=>false,'error'=>'limit','retry_after'=>$retry]); exit;
}

// --- Prépare le hash si privé ---
$hash = $is_private ? password_hash($pwd, PASSWORD_DEFAULT) : null;

$ins = $mysqli->prepare("
  INSERT INTO chat_rooms (name, password_hash, is_private, created_by)
  VALUES (?, ?, ?, ?)
");
$ins->bind_param('ssii', $name, $hash, $is_private, $uid);

if ($ins->execute()) {
  echo json_encode(['ok'=>true,'id'=>$mysqli->insert_id], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db']);
}
$ins->close();
