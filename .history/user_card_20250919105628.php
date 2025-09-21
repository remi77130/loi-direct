<?php
// user_card.php — renvoie {ok, pseudo, projects_count} pour un user

declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad id']); exit; }

$stmt = $mysqli->prepare(
  "SELECT u.id, u.pseudo, COUNT(p.id) AS projects_count
   FROM users u
   LEFT JOIN law_projects p
     ON p.author_id = u.id AND p.status = 'published'
   WHERE u.id = ?
   GROUP BY u.id, u.pseudo"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) { http_response_code(404); echo json_encode(['ok'=>false]); exit; }

echo json_encode([
  'ok' => true,
  'id' => (int)$row['id'],
  'pseudo' => (string)$row['pseudo'],
  'projects_count' => (int)$row['projects_count'],
]);
