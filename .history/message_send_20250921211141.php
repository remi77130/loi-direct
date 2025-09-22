<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';

require_login();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');



$body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u','', $body);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  echo json_encode(['ok'=>false,'error'=>'method']);
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


$lim = $mysqli->prepare('SELECT COUNT(*) FROM messages
                         WHERE sender_id=? AND created_at >= NOW() - INTERVAL 30 SECOND');
$lim->bind_param('i', $_SESSION['user_id']);
$lim->execute(); $lim->bind_result($cnt); $cnt=0; $lim->fetch(); $lim->close();
if ($cnt >= 5) { // 5 msg / 30s
  http_response_code(429);
  echo json_encode(['ok'=>false,'error'=>'rate']);
  exit;
}



if ($ins->execute()) {
  echo json_encode(['ok'=>true, 'id'=>$mysqli->insert_id], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db']);
}
$ins->close();
