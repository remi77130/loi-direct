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
  echo json_encode(['ok'=>false,'error'=>'method']);
  exit;
}

/* CSRF */
$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || $csrf === '' || !hash_equals($_SESSION['csrf'], $csrf)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']);
  exit;
}

$me       = (int)($_SESSION['user_id'] ?? 0);
$other_id = (int)($_POST['other_id'] ?? 0);
if ($me <= 0 || $other_id <= 0 || $other_id === $me) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'bad_params']);
  exit;
}



// Récupère les messages de ce thread que *moi* je vois encore
$sqlGet = "SELECT id, sender_id, recipient_id, body, image_path, created_at
           FROM messages
           WHERE (sender_id=? AND recipient_id=? AND (deleted_by_sender   IS NULL OR deleted_by_sender=0))
              OR (sender_id=? AND recipient_id=? AND (deleted_by_recipient IS NULL OR deleted_by_recipient=0))";
$g = $mysqli->prepare($sqlGet) ?: throw new RuntimeException('prep get');
$g->bind_param('iiii', $me, $other_id, $other_id, $me);
$g->execute();
$rs = $g->get_result();
$ins = $mysqli->prepare("INSERT INTO message_trash
(owner_id,message_id,sender_id,recipient_id,body,image_path,deleted_at,created_at)
VALUES (?,?,?,?,?,?,NOW(),?)") ?: throw new RuntimeException('prep ins');

while ($m = $rs->fetch_assoc()) {
  $ins->bind_param('iiiisss',
  $me,
  $m['id'],
  $m['sender_id'],
  $m['recipient_id'],
  $m['body'],
  $m['image_path'],
  $m['created_at']
);

  if (!$ins->execute()) throw new RuntimeException('exec ins');
}
$ins->close();
$g->close();




/*
  Soft delete :
  - Messages que j’ai ENVOYÉS à other_id -> deleted_by_sender = 1
  - Messages que j’ai REÇUS de other_id -> deleted_by_recipient = 1
  (Les données restent en base pour l’autre utilisateur.)
*/

$mysqli->begin_transaction();

try {
  // Marquer les messages que j’ai envoyés comme supprimés de MON côté
  $sql1 = "UPDATE messages
              SET deleted_by_sender = 1
            WHERE sender_id = ? AND recipient_id = ?";
  $st1 = $mysqli->prepare($sql1);
  if (!$st1) throw new RuntimeException('prep1');
  $st1->bind_param('ii', $me, $other_id);
  if (!$st1->execute()) throw new RuntimeException('exec1');
  $st1->close();

  // Marquer les messages que j’ai reçus comme supprimés de MON côté
  $sql2 = "UPDATE messages
              SET deleted_by_recipient = 1
            WHERE recipient_id = ? AND sender_id = ?";
  $st2 = $mysqli->prepare($sql2);
  if (!$st2) throw new RuntimeException('prep2');
  $st2->bind_param('ii', $me, $other_id);
  if (!$st2->execute()) throw new RuntimeException('exec2');
  $st2->close();

  $mysqli->commit();
  echo json_encode(['ok'=>true]);
  exit;

} catch (Throwable $e) {
  $mysqli->rollback();
  error_log('messages_delete_thread: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server']);
  exit;
}
