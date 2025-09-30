<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'method']); exit;
}

$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || $csrf === '' || !hash_equals($_SESSION['csrf'], $csrf)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$uid = (int)($_SESSION['user_id'] ?? 0);
$other_id = (int)($_POST['other_id'] ?? 0);
if ($uid<=0 || $other_id<=0) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'bad_params']); exit;
}

/* collect images to remove */
$imgStmt = $mysqli->prepare(
  "SELECT image_path FROM messages
   WHERE (sender_id=? AND recipient_id=?) OR (sender_id=? AND recipient_id=?)"
);
$imgStmt->bind_param('iiii', $uid,$other_id,$other_id,$uid);
$imgStmt->execute();
$res = $imgStmt->get_result();
$paths = [];
while ($row = $res->fetch_assoc()) {
  if (!empty($row['image_path'])) $paths[] = $row['image_path'];
}
$imgStmt->close();

/* delete messages (both directions) */
$del = $mysqli->prepare(
  "DELETE FROM messages
   WHERE (sender_id=? AND recipient_id=?) OR (sender_id=? AND recipient_id=?)"
);
$del->bind_param('iiii', $uid,$other_id,$other_id,$uid);
if (!$del->execute()) { echo json_encode(['ok'=>false,'error'=>'db']); exit; }
$deleted = $del->affected_rows;
$del->close();

/* delete files on disk (best-effort) */
$base = realpath(__DIR__.'/uploads/msg');
foreach ($paths as $rel) {
  $p = realpath(__DIR__.'/'.$rel);
  if ($p && $base && strpos($p, $base) === 0 && is_file($p)) @unlink($p);
}

echo json_encode(['ok'=>true,'deleted'=>$deleted]);
