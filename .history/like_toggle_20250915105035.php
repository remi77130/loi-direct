<?php
// like_toggle.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'auth']);
  exit;
}

$projectId = (int)($_POST['id'] ?? 0);
$csrf = $_POST['csrf'] ?? '';

if ($projectId <= 0 || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad_request']);
  exit;
}

// Vérifier que le projet existe & publié
$chk = $mysqli->prepare('SELECT 1 FROM law_projects WHERE id=? AND status="published"');
$chk->bind_param('i', $projectId);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
  $chk->close();
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'not_found']);
  exit;
}
$chk->close();

$userId = (int)$_SESSION['user_id'];

// Toggle: si existe -> delete, sinon insert
$exists = $mysqli->prepare('SELECT 1 FROM likes WHERE user_id=? AND project_id=?');
$exists->bind_param('ii', $userId, $projectId);
$exists->execute();
$exists->store_result();
$already = $exists->num_rows > 0;
$exists->close();

if ($already) {
  $del = $mysqli->prepare('DELETE FROM likes WHERE user_id=? AND project_id=?');
  $del->bind_param('ii', $userId, $projectId);
  $del->execute();
  $del->close();
  $liked = false;
} else {
  $ins = $mysqli->prepare('INSERT INTO likes (user_id, project_id) VALUES (?,?)');
  $ins->bind_param('ii', $userId, $projectId);
  $ins->execute();
  $ins->close();
  $liked = true;
}

// Compteur à jour
$countStmt = $mysqli->prepare('SELECT COUNT(*) FROM likes WHERE project_id=?');
$countStmt->bind_param('i', $projectId);
$countStmt->execute();
$countStmt->bind_result($count);
$countStmt->fetch();
$countStmt->close();

echo json_encode(['ok'=>true, 'liked'=>$liked, 'count'=>$count]);
