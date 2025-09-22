<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  echo json_encode(['ok'=>false,'error'=>'method']); exit;
}

$csrf = $_POST['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$recipient_id = (int)($_POST['recipient_id'] ?? 0);
$body = trim((string)($_POST['body'] ?? ''));
$body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u','', $body);
$body = mb_substr($body, 0, 2000);

if ($recipient_id <= 0 || $body === '') {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'missing']); exit;
}
if ($recipient_id === (int)$_SESSION['user_id']) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'self']); exit;
}

/* Rate limit simple: 5 msg / 30s */
$cnt = 0;
$lim = $mysqli->prepare('SELECT COUNT(*) FROM messages
                         WHERE sender_id=? AND created_at >= NOW() - INTERVAL 30 SECOND');
$lim->bind_param('i', $_SESSION['user_id']);
$lim->execute(); $lim->bind_result($cnt); $lim->fetch(); $lim->close();
if ($cnt >= 5) { http_response_code(429); echo json_encode(['ok'=>false,'error'=>'rate']); exit; }

/* Destinataire existe ? */
$u = $mysqli->prepare('SELECT 1 FROM users WHERE id=? LIMIT 1');
$u->bind_param('i', $recipient_id);
$u->execute(); $u->store_result();
if ($u->num_rows === 0) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'recipient_not_found']); $u->close(); exit; }
$u->close();

/* Insert */
$ins = $mysqli->prepare('INSERT INTO messages (sender_id, recipient_id, body) VALUES (?,?,?)');
$ins->bind_param('iis', $_SESSION['user_id'], $recipient_id, $body);
if ($ins->execute()) {
  echo json_encode(['ok'=>true, 'id'=>$mysqli->insert_id], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db']);
}
$ins->close();
