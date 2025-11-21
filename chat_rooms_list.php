<?php

/**
 * chat_rooms_list.php
 * Liste des 100 salons les plus récents, publics ou privés.
 * Remonte aussi :
 *  - is_private pour l’UI (icône 🔒)
 *  - last_at : date du dernier message
 *  - active_count : nombre de users actuellement en ligne dans le salon
 *
 * Sortie :
 *  { ok:true, rooms:[{id,name,is_private,last_at,active_count}] }
 */
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Durée de "fraîcheur" de la présence (en secondes)
$ttl = 45;

// last_at = dernier message sinon date de création
$sql = "
  SELECT
    r.id,
    r.name,
    r.is_private,
    r.created_by,
    (SELECT MAX(created_at)
       FROM chat_messages cm
       WHERE cm.room_id = r.id) AS last_at,
    (
      SELECT COUNT(DISTINCT p.user_id)
      FROM chat_presence p
      WHERE p.room_id = r.id
        AND p.last_seen > (NOW() - INTERVAL {$ttl} SECOND)
    ) AS active_count
  FROM chat_rooms r
  ORDER BY COALESCE(
    (SELECT MAX(created_at) FROM chat_messages cm WHERE cm.room_id = r.id),
    r.created_at
  ) DESC,
  r.id DESC
  LIMIT 100
";

$res = $mysqli->query($sql);
$rooms = [];

if ($res) {
  while ($row = $res->fetch_assoc()) {
    $rooms[] = [
      'id'           => (int)$row['id'],
      'name'         => (string)$row['name'],
      'is_private'   => (int)$row['is_private'], // 0 ou 1
      'last_at'      => $row['last_at'] ? (string)$row['last_at'] : null,
      'active_count' => isset($row['active_count']) ? (int)$row['active_count'] : 0,
      'created_by'   => isset($row['created_by']) ? (int)$row['created_by'] : 0,

    ];
  }
}

echo json_encode(['ok' => true, 'rooms' => $rooms], JSON_UNESCAPED_UNICODE);
