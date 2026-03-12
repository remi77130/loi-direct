<?php
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user_id = (int)($_SESSION['user_id'] ?? 0);
$room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;   // 0 = global
$session = (string)($_POST['session_key'] ?? '');


// si room_id > 0 → présence liée à un salon,

// si room_id = 0 → présence “globale” (par exemple quand l’utilisateur est juste sur la page d’accueil 
// du chat ou sur une autre page qui ping).


if ($user_id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad_user']);
  exit;
}

if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $session)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad_session']);
  exit;
}

$sql = "INSERT INTO chat_presence (room_id,user_id,session_key,last_seen) 
        VALUES (?,?,?,NOW())
        ON DUPLICATE KEY UPDATE room_id = VALUES(room_id),user_id = VALUES(user_id),last_seen = NOW() ";
$st = $mysqli->prepare($sql); // on met a jour room_id pour gérer le cas ou un utilisateur change de salon sans changer de session (ex: page d’accueil → salon puis salon → salon)
if (!$st) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db_prepare']);
  exit;
}
$st->bind_param('iis', $room_id, $user_id, $session);
$st->execute();
$st->close();

echo json_encode(['ok'=>true]);
