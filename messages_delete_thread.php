<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$uid  = (int)($_SESSION['user_id'] ?? 0);
$csrf = $_POST['csrf'] ?? '';
$other_id = (int)($_POST['other_id'] ?? 0);

if ($uid<=0 || $other_id<=0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad_input']); exit; }
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
  http_response_code(400); echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$mysqli->begin_transaction();
try {
  // 1) Récupérer les images à supprimer
  $sql = "SELECT image_path FROM messages
          WHERE (sender_id=? AND recipient_id=?)
             OR (sender_id=? AND recipient_id=?)";
  $st = $mysqli->prepare($sql) ?: throw new Exception('prep1');
  $st->bind_param('iiii', $uid, $other_id, $other_id, $uid);
  $st->execute();
  $res = $st->get_result();
  $paths = [];
  while ($row = $res->fetch_assoc()) {
    if (!empty($row['image_path'])) $paths[] = $row['image_path'];
  }
  $st->close();

  // 2) Supprimer les messages
  $del = $mysqli->prepare("DELETE FROM messages
                           WHERE (sender_id=? AND recipient_id=?)
                              OR (sender_id=? AND recipient_id=?)") ?: throw new Exception('prep2');
  $del->bind_param('iiii', $uid, $other_id, $other_id, $uid);
  if (!$del->execute()) throw new Exception('exec2');
  $del->close();

  $mysqli->commit();

  // 3) Nettoyer les fichiers (hors transaction)
  foreach ($paths as $rel) {
    $p = realpath(__DIR__ . '/' . $rel);
    $base = realpath(__DIR__ . '/uploads/msg');
    if ($p && $base && str_starts_with($p, $base)) @unlink($p);
  }

  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  $mysqli->rollback();
  error_log('delete_thread: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server']);
}
