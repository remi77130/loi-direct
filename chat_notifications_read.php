<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

require_login();

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
  http_response_code(401);
  echo json_encode([
    'ok' => false,
    'error' => 'auth'
  ]);
  exit;
}

// Vérification CSRF
$csrf = $_POST['csrf'] ?? '';
if (!$csrf || !hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
  http_response_code(400);
  echo json_encode([
    'ok' => false,
    'error' => 'csrf'
  ]);
  exit;
}

// ID de la notification
$notif_id = (int)($_POST['id'] ?? 0); // sécurité : on s’assure que l’ID de la notification est un entier positif, sinon on considère que c’est une requête invalide
if ($notif_id <= 0) {
  http_response_code(400);
  echo json_encode([
    'ok' => false,
    'error' => 'id'
  ]);
  exit;
}

// Mise à jour de la notification (is_read = 1) si elle appartient à l’utilisateur connecté et si l’ID est valide (positif) on vérifie user_id pour empêcher quelqu’un de marquer la notif d’un autre.
$sql = "
  UPDATE chat_notifications 
  SET is_read = 1
  WHERE id = ?
    AND user_id = ?
";

$st = $mysqli->prepare($sql);

if (!$st) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => 'prepare'
  ]);
  exit;
}

$st->bind_param('ii', $notif_id, $user_id);

if (!$st->execute()) {
  $st->close();
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => 'execute'
  ]);
  exit;
}

$affected = $st->affected_rows;
$st->close();

echo json_encode([
  'ok' => true,
  'updated' => $affected
]);