<?php
declare(strict_types=1);
session_start();

require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// --- récupération/validation des entrées ---
$me      = (int)($_SESSION['user_id'] ?? 0);
$peer_id = (int)($_POST['peer_id'] ?? 0);
if ($peer_id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad_peer']); exit; }

// >>> PLACE ICI LA DÉFENSE ANTI-SELF-DM <<<
if ($peer_id === $me) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'no_self_dm']);
  exit;
}

// suite : chercher un DM existant OU créer la room DM …
