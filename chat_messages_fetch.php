<?php

/**
 * chat_messages_fetch.php
 * Récupération paginée des messages d’un salon.
 * Entrée :
 *  - GET[room_id]  : id salon
 *  - GET[after_id] : id dernier message connu (0 = première page)
 * Sécurité :
 *  - Si salon privé : accès autorisé seulement si rooms_ok[room_id] en session
 * Sortie :
 *  - { ok:true, messages:[...]}  (50 derniers si after_id=0, sinon messages > after_id)
 */
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/* 1) Entrées */
$room_id = (int)($_GET['room_id'] ?? 0);
$after   = max(0, (int)($_GET['after_id'] ?? 0));
if ($room_id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'room_id']); exit; }

/* 2) Droit d'accès (public/privé) // Vérifie accès si privé */
$st = $mysqli->prepare("SELECT is_private FROM chat_rooms WHERE id=?");
$st->bind_param('i', $room_id);
$st->execute();
$st->bind_result($is_private);
if (!$st->fetch()) { $st->close(); http_response_code(404); echo json_encode(['ok'=>false,'error'=>'notfound']); exit; }
$st->close();

if ((int)$is_private === 1 && empty($_SESSION['rooms_ok'][$room_id])) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'locked']); exit;
}


/* 3) Requêtes messages */
if ($after > 0) {
  $sql = "
    SELECT
      m.id,
      m.sender_id,                      -- ← une seule fois
      m.body,
      m.created_at,
      m.file_url,
      m.file_mime,
      u.pseudo AS sender
    FROM chat_messages m
    LEFT JOIN users u ON u.id = m.sender_id
    WHERE m.room_id = ? AND m.id > ?
    ORDER BY m.id ASC
    LIMIT 200
  ";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('ii', $room_id, $after);
} else {
  $sql = "
    SELECT
      m.id,
      m.sender_id,                      -- ← une seule fois
      m.body,
      m.created_at,
      m.file_url,
      m.file_mime,
      u.pseudo AS sender
    FROM chat_messages m
    LEFT JOIN users u ON u.id = m.sender_id
    WHERE m.room_id = ?
    ORDER BY m.id DESC
    LIMIT 50
  ";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('i', $room_id);
}

$stmt->execute();
$res  = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($after === 0) {
  $rows = array_reverse($rows);
}

/* 4) Sortie */
echo json_encode(['ok'=>true,'messages'=>$rows], JSON_UNESCAPED_UNICODE);
