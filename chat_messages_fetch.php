<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$room_id = max(1, (int)($_GET['room_id'] ?? 0));
$after   = max(0, (int)($_GET['after_id'] ?? 0));

$chk = $mysqli->prepare("SELECT id,name,is_private FROM chat_rooms WHERE id=? LIMIT 1");
$chk->bind_param('i', $room_id);
$chk->execute();
$room = $chk->get_result()->fetch_assoc();
$chk->close();

if (!$room) {
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'room']);
  exit;
}
if ((int)$room['is_private'] === 1) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'private']);
  exit;
}

/* --- Requête messages (avec images) --- */
if ($after > 0) {
  $sql = "SELECT m.id, m.body, m.created_at, m.file_url, m.file_mime, u.pseudo AS sender
          FROM chat_messages m
          JOIN users u ON u.id = m.sender_id
          WHERE m.room_id = ? AND m.id > ?
          ORDER BY m.id ASC
          LIMIT 200";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('ii', $room_id, $after);
} else {
  $sql = "SELECT m.id, m.body, m.created_at, m.file_url, m.file_mime, u.pseudo AS sender
          FROM chat_messages m
          JOIN users u ON u.id = m.sender_id
          WHERE m.room_id = ?
          ORDER BY m.id DESC
          LIMIT 50";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('i', $room_id);
}

$stmt->execute();
$res  = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($after === 0) {
  $rows = array_reverse($rows); // pour afficher du plus ancien au plus récent
}

echo json_encode(['ok'=>true, 'messages'=>$rows], JSON_UNESCAPED_UNICODE);
