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

/* CSRF pour les formulaires de réponse inline */
/* CSRF pour les formulaires de réponse inline */
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



/*
  Soft delete :
  - Tous les messages que J’AI envoyés à other_id -> deleted_by_sender = 1
  - Tous les messages que J’AI reçus de other_id -> deleted_by_recipient = 1

  ⚠️ Nécessite deux colonnes dans `messages` :
    - deleted_by_sender TINYINT(1) DEFAULT 0 NULL
    - deleted_by_recipient TINYINT(1) DEFAULT 0 NULL
*/

$mysqli->begin_transaction();

try {
  // Je supprime "mon côté" pour les messages que j’ai envoyés
  $sql1 = "UPDATE messages
              SET deleted_by_sender = 1
            WHERE sender_id = ? AND recipient_id = ?";
  $st1 = $mysqli->prepare($sql1);
  if (!$st1) throw new RuntimeException('prep1');
  $st1->bind_param('ii', $me, $other_id);
  if (!$st1->execute()) throw new RuntimeException('exec1');
  $st1->close();

  // Je supprime "mon côté" pour les messages que j’ai reçus
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
} catch (Throwable $e) {
  $mysqli->rollback();
  error_log('messages_delete_thread: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server']);
}


$uid  = (int)($_SESSION['user_id'] ?? 0);
$csrf = $_POST['csrf'] ?? '';
$other_id = (int)($_POST['other_id'] ?? 0);

if ($uid<=0 || $other_id<=0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad_input']); exit; }
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
  http_response_code(400); echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$mysqli->begin_transaction();
