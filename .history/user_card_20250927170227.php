<?php
// user_card.php
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');


require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

// --- Input ---
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id) {
  http_response_code(400);
  echo json_encode(['ok'=>false, 'error'=>'bad_request']);
  exit;
}

try {
  // Récup user + nb projets publiés en une requête
  $sql = "
    SELECT u.pseudo, u.sex, u.height_cm,
           (SELECT COUNT(*) FROM law_projects p
              WHERE p.author_id=u.id AND p.status='published') AS projects_count
    FROM users u
    WHERE u.id=? LIMIT 1
  ";
  $st = $mysqli->prepare($sql);
  if (!$st) { error_log('user_card prepare: '.$mysqli->error); throw new RuntimeException('prep'); }

  $st->bind_param('i', $id);
  if (!$st->execute()) { error_log('user_card exec: '.$st->error); throw new RuntimeException('exec'); }

  // get_result si dispo, sinon bind_result/fetch
  $row = null;
  if (method_exists($st, 'get_result')) {
    $res = $st->get_result();
    if ($res === false) { error_log('user_card get_result: '.$st->error); throw new RuntimeException('getres'); }
    $row = $res->fetch_assoc() ?: null;
  } else {
    $st->bind_result($pseudo, $sex, $height_cm, $projects_count);
    if ($st->fetch()) {
      $row = [
        'pseudo'         => $pseudo,
        'sex'            => $sex,
        'height_cm'      => $height_cm,
        'projects_count' => $projects_count,
      ];
    }
  }
  $st->close();

  if (!$row) {
    http_response_code(404);
    echo json_encode(['ok'=>false, 'error'=>'not_found']);
    exit;
  }

  // Normalisation sortie
  $sexLabel = null;
  if ($row['sex'] === 'homme') $sexLabel = 'Homme';
  elseif ($row['sex'] === 'femme') $sexLabel = 'Femme';

  $height = is_null($row['height_cm']) ? null : (int)$row['height_cm'];

  http_response_code(200);
echo json_encode([
  'ok'                 => true,
  'pseudo'             => (string)$row['pseudo'],
  'projects_count'     => (int)$row['projects_count'],
  'sex'                => $sexLabel,
  'height_cm'          => $height,
  'relationship_status'=> $statusLabel,
], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>'server_error']);
}
