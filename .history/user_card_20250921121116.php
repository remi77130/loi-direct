<?php
// user_card.php
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id) {
  http_response_code(400);
  echo json_encode(['ok'=>false, 'error'=>'Requête invalide (id)']);
  exit;
}

try {
  // 1) Pseudo
  $u = $mysqli->prepare('SELECT pseudo FROM users WHERE id=? LIMIT 1');
  $u->bind_param('i', $id);
  $u->execute();
  $u->bind_result($pseudo);
  if (!$u->fetch()) {
    $u->close();
    http_response_code(404);
    echo json_encode(['ok'=>false, 'error'=>'Utilisateur introuvable']);
    exit;
  }
  $u->close();

  // 2) Nb de projets publiés
  $c = $mysqli->prepare("SELECT COUNT(*) FROM law_projects WHERE author_id=? AND status='published'");
  $c->bind_param('i', $id);
  $c->execute();
  $c->bind_result($count);
  $c->fetch();
  $c->close();

  http_response_code(200);
  echo json_encode([
    'ok' => true,
    'pseudo' => $pseudo,
    'projects_count' => (int)$count,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>'Erreur serveur']);
}
