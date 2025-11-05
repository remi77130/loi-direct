<?php
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'method']); exit;
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$room_id = (int)($_POST['room_id'] ?? 0);
$session_key = trim((string)($_POST['session_key'] ?? ''));

if ($user_id<=0 || $room_id<=0 || $session_key==='') {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad_params']); exit;
}

$sql = "INSERT INTO chat_presence (room_id, session_key, user_id, last_seen)
        VALUES (?,?,?,NOW())
        ON DUPLICATE KEY UPDATE last_seen=NOW(), user_id=VALUES(user_id)";
if (!$st = $mysqli->prepare($sql)) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'prep']); exit; }
$st->bind_param('isis', $room_id, $session_key, $user_id);
$st->execute();
$st->close();

echo json_encode(['ok'=>true]);
