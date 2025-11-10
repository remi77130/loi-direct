<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$user_id = (int)($_SESSION['user_id'] ?? 0);
$msg_id  = (int)($_POST['message_id'] ?? 0);
$csrf    = $_POST['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'csrf']); exit; }
if ($msg_id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad_id']); exit; }

/* Vérif accès si privé */
$sql = "SELECT m.id, r.id AS room_id, r.is_private FROM chat_messages m JOIN chat_rooms r ON r.id=m.room_id WHERE m.id=?";
$st=$mysqli->prepare($sql); $st->bind_param('i',$msg_id); $st->execute(); $st->store_result();
if (!$st->num_rows){ http_response_code(404); echo json_encode(['ok'=>false,'error'=>'notfound']); exit; }
$st->bind_result($mid,$room_id,$is_private); $st->fetch(); $st->close();
if ((int)$is_private===1 && empty($_SESSION['rooms_ok'][$room_id])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'locked']); exit; }

/* Like one-shot: INSERT IGNORE, +1 seulement si insertion */
$mysqli->begin_transaction();
try {
  $ins = $mysqli->prepare("INSERT IGNORE INTO message_likes (message_id,user_id) VALUES (?,?)");
  $ins->bind_param('ii', $msg_id, $user_id);
  $ins->execute();
  $inserted = ($ins->affected_rows === 1);

  if ($inserted) {
    $upd = $mysqli->prepare("UPDATE chat_messages SET like_count = like_count + 1 WHERE id=?");
    $upd->bind_param('i',$msg_id); $upd->execute();
  }

  $cnt = $mysqli->prepare("SELECT like_count FROM chat_messages WHERE id=?");
  $cnt->bind_param('i',$msg_id); $cnt->execute(); $cnt->bind_result($like_count); $cnt->fetch(); $cnt->close();

  $mysqli->commit();
  echo json_encode(['ok'=>true, 'liked'=>true, 'like_count'=>$like_count, 'inserted'=>$inserted]);
} catch(Throwable $e){
  $mysqli->rollback();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server']);
}
