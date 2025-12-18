<?php
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string)$_POST['csrf'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'csrf']);
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$peerId = (int)($_POST['recipient_id'] ?? 0);

if ($userId <= 0 || $peerId <= 0 || $peerId === $userId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_ids']);
    exit;
}

$now = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

$stmt = $pdo->prepare("
  INSERT INTO chat_dm_typing (user_id, peer_id, last_ping)
  VALUES (:u, :p, :t)
  ON DUPLICATE KEY UPDATE last_ping = VALUES(last_ping)
");
$stmt->execute([
    ':u' => $userId,
    ':p' => $peerId,
    ':t' => $now,
]);

echo json_encode(['ok' => true]);
