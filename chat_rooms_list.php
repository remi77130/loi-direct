<?php

/**
 * chat_rooms_list.php
 * Liste des 100 salons les plus récents, publics ou privés.
 * Remonte aussi un indicateur is_private pour l’UI (icône 🔒).
 * Sortie :
 *  { ok:true, rooms:[{id,name,is_private,last_at}] }
 */
declare(strict_types=1);
session_start();
require __DIR__.'/db.php'; require __DIR__.'/auth.php'; require_login();
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');

// last_at = dernier message sinon date de création

$sql = "
  SELECT
    r.id,
    r.name,
    r.is_private,
    (SELECT MAX(created_at) FROM chat_messages cm WHERE cm.room_id = r.id) AS last_at
  FROM chat_rooms r
  ORDER BY COALESCE(
    (SELECT MAX(created_at) FROM chat_messages cm WHERE cm.room_id = r.id),
    r.created_at
  ) DESC, r.id DESC
  LIMIT 100
";
$res = $mysqli->query($sql);
$rooms = [];
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $rooms[] = [
      'id'         => (int)$row['id'],
      'name'       => (string)$row['name'],
      'is_private' => (int)$row['is_private'], // ← renvoie 0 ou 1
      'last_at'    => $row['last_at'] ? (string)$row['last_at'] : null,
    ];
  }
}
echo json_encode(['ok'=>true,'rooms'=>$rooms], JSON_UNESCAPED_UNICODE);