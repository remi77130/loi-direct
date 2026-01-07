<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';

header('Content-Type: text/plain; charset=utf-8');

// 1) supprimer les salons expirés
// si FK ON DELETE CASCADE -> messages supprimés automatiquement
$stmt = $mysqli->prepare("
  DELETE FROM chat_rooms
  WHERE is_ephemeral = 1
    AND expires_at IS NOT NULL
    AND expires_at <= NOW()
");
$stmt->execute();

echo "deleted_rooms=" . $stmt->affected_rows . "\n";
