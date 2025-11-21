<?php
// chat_room_delete.php — suppression d’un salon par son créateur
declare(strict_types=1);
session_start();

require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'method_not_allowed']); exit;
}

// CSRF
$csrf = $_POST['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$uid    = (int)($_SESSION['user_id'] ?? 0);
$roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

if ($uid <= 0 || !$roomId) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad_input']); exit;
}

// Vérifier que le salon existe et appartient à cet utilisateur
$st = $mysqli->prepare("SELECT id, created_by FROM chat_rooms WHERE id = ?");
$st->bind_param('i', $roomId);
$st->execute();
$res  = $st->get_result();
$room = $res->fetch_assoc();
$st->close();

if (!$room) {
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'room_not_found']); exit;
}

if ((int)$room['created_by'] !== $uid) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'not_owner']); exit;
}

// On supprime tout : likes -> messages -> room
$mysqli->begin_transaction();

try {
  // 1) Supprimer les likes des messages de ce salon
  $sqlLikes = "
    DELETE ml FROM message_likes ml
    JOIN chat_messages m ON m.id = ml.message_id
    WHERE m.room_id = ?
  ";
  $stLikes = $mysqli->prepare($sqlLikes);
  $stLikes->bind_param('i', $roomId);
  $stLikes->execute();
  $stLikes->close();

  // 2) Supprimer les messages du salon
  $sqlMsg = "DELETE FROM chat_messages WHERE room_id = ?";
  $stMsg = $mysqli->prepare($sqlMsg);
  $stMsg->bind_param('i', $roomId);
  $stMsg->execute();
  $stMsg->close();

  // 3) Supprimer le salon
  $sqlRoom = "DELETE FROM chat_rooms WHERE id = ?";
  $stRoom = $mysqli->prepare($sqlRoom);
  $stRoom->bind_param('i', $roomId);
  $stRoom->execute();
  $stRoom->close();

  $mysqli->commit();
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  $mysqli->rollback();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db']);
}
