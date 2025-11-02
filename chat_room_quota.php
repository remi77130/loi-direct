<?php

/**
 * chat_room_quota.php
 * Vérifie le temps restant avant de pouvoir recréer un salon.
 * Politique : 1 création tous les 86400 s, public ou privé.
 * Sortie JSON :
 *  - { ok:true, retry_after:int } en secondes (0 si quota disponible)
 */
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$uid = (int)($_SESSION['user_id'] ?? 0);

// On regarde la dernière création de l’utilisateur, tout type confondu
$st = $mysqli->prepare("
  SELECT TIMESTAMPDIFF(SECOND, MAX(created_at), NOW()) AS since_last
  FROM chat_rooms
  WHERE created_by=?
");
$st->bind_param('i', $uid);
$st->execute();
$st->bind_result($since);
$st->fetch();
$st->close();

$retry_after = 0;
if ($since !== null && $since < 86400) {
  $retry_after = 86400 - (int)$since;
}

echo json_encode(['ok'=>true, 'retry_after'=>$retry_after], JSON_UNESCAPED_UNICODE);
