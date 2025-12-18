<?php
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

// Ce script renvoie “est-ce que la personne en face (peer_id) est en train d’écrire pour moi ?”.

$userId = (int)($_SESSION['user_id'] ?? 0);
$peerId = (int)($_GET['peer_id'] ?? 0);

if ($userId <= 0 || $peerId <= 0 || $peerId === $userId) {
    echo json_encode(['ok' => false, 'users' => []]);
    exit;
}

// on considère "en train d'écrire" si ping < 6 secondes
$cutoff = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
    ->modify('-6 seconds')
    ->format('Y-m-d H:i:s');

$stmt = $pdo->prepare("
  SELECT u.id, u.pseudo
  FROM chat_dm_typing t
  JOIN users u ON u.id = t.user_id
  WHERE t.user_id = :peer
    AND t.peer_id = :me
    AND t.last_ping >= :cutoff
  LIMIT 1
");
$stmt->execute([
    ':peer'   => $peerId,   // l'autre est en train d'écrire
    ':me'     => $userId,   // vers moi
    ':cutoff' => $cutoff,
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);
$users = $row ? [$row] : [];

echo json_encode(['ok' => true, 'users' => $users]);
