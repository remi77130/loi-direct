<?php
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// c’est lui qui renvoie les données JSON de chaque salon (id, nom, privé ou pas, éphémère ou pas, date du dernier message ou date de création si pas de message, nombre d’utilisateurs actifs dans le salon)
// si room_id > 0 → liste des actifs pour CE salon 

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$ttl = 45; // secondes de fraîcheur

// Si room_id > 0 -> liste des actifs pour CE salon

// s’il appelle juste chat_presence_list.php (ou room_id=0) → tu récupères tous les users actifs 
// (toute table chat_presence confondue), 
// donc tu peux remplir presenceInline même avant qu’un salon soit ouvert.
if ($room_id > 0) {
  $sql = "
    SELECT
      u.id,
      u.pseudo,
      u.avatar_url
    FROM chat_presence p
    JOIN users u ON u.id = p.user_id
    WHERE p.room_id = ?
      AND p.last_seen > (NOW() - INTERVAL ? SECOND)
    GROUP BY u.id, u.pseudo, u.avatar_url
    ORDER BY u.pseudo
  ";
  $st = $mysqli->prepare($sql);
  if (!$st) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'db_prepare']);
    exit;
  }
  $st->bind_param('ii', $room_id, $ttl);
} else {
  // Sinon -> liste globale des utilisateurs actifs (n'importe quel salon / room_id)
  $sql = "
    SELECT
      u.id,
      u.pseudo,
      u.avatar_url
    FROM chat_presence p
    JOIN users u ON u.id = p.user_id
    WHERE p.last_seen > (NOW() - INTERVAL ? SECOND)
    GROUP BY u.id, u.pseudo, u.avatar_url
    ORDER BY u.pseudo
  ";
  $st = $mysqli->prepare($sql);
  if (!$st) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'db_prepare']);
    exit;
  }
  $st->bind_param('i', $ttl);
}

$st->execute();
$res = $st->get_result();
$users = [];
while ($r = $res->fetch_assoc()) {
  $users[] = [
    'id'         => (int)$r['id'],
    'pseudo'     => $r['pseudo'],
    'avatar_url' => $r['avatar_url'] ?? null,
  ];
}
$st->close();

echo json_encode(['ok'=>true,'users'=>$users], JSON_UNESCAPED_UNICODE);
