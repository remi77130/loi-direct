<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php'; require __DIR__.'/auth.php'; require_login();
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');

if($_SERVER['REQUEST_METHOD']!=='POST'){ http_response_code(405); header('Allow: POST'); echo json_encode(['ok'=>false,'error'=>'method']); exit; }
if(!hash_equals($_SESSION['csrf']??'', $_POST['csrf']??'')){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'csrf']); exit; }

$room_id = (int)($_POST['room_id']??0);
$body = trim((string)($_POST['body']??'')); $body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u','', $body);
$body = mb_substr($body,0,2000);

if($room_id<=0 || $body===''){ http_response_code(422); echo json_encode(['ok'=>false,'error'=>'missing']); exit; }

$chk = $mysqli->prepare("SELECT is_private FROM chat_rooms WHERE id=? LIMIT 1");
$chk->bind_param('i',$room_id); $chk->execute(); $chk->bind_result($priv); if(!$chk->fetch()){ http_response_code(404); echo json_encode(['ok'=>false,'error'=>'room']); exit; } $chk->close();
if((int)$priv===1){ http_response_code(403); echo json_encode(['ok'=>false,'error'=>'private']); exit; }

$rl = $mysqli->prepare("SELECT COUNT(*) FROM chat_messages WHERE sender_id=? AND created_at>=NOW()-INTERVAL 5 SECOND");
$rl->bind_param('i', $_SESSION['user_id']); $rl->execute(); $rl->bind_result($c); $c=0; $rl->fetch(); $rl->close();
if($c>=5){ http_response_code(429); echo json_encode(['ok'=>false,'error'=>'rate']); exit; }

$ins = $mysqli->prepare("INSERT INTO chat_messages (room_id,sender_id,body) VALUES (?,?,?)");
$ins->bind_param('iis', $room_id, $_SESSION['user_id'], $body);
if($ins->execute()) echo json_encode(['ok'=>true,'id'=>$mysqli->insert_id], JSON_UNESCAPED_UNICODE);
else { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); }
$ins->close();
