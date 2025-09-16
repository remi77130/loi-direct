<?php
// check_pseudo.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

$raw = $_GET['pseudo'] ?? '';
$pseudo = trim($raw);

// mêmes règles que côté inscription
$valid = (bool)preg_match('/^[A-Za-z0-9_]{3,20}$/', $pseudo);
if (!$valid) {
    echo json_encode(['ok' => false, 'available' => false, 'reason' => 'format']);
    exit;
}

$stmt = $mysqli->prepare('SELECT 1 FROM users WHERE pseudo = ? LIMIT 1');
$stmt->bind_param('s', $pseudo);
$stmt->execute();
$stmt->store_result();

$available = $stmt->num_rows === 0;
$stmt->close();

echo json_encode(['ok' => true, 'available' => $available]);
