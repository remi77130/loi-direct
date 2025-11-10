<?php
// check_pseudo.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

$pseudo = trim((string)($_GET['pseudo'] ?? ''));

// mêmes règles que register.php
if (!preg_match('/^[\p{L}0-9_.-]{3,20}$/u', $pseudo)) {
    echo json_encode(['ok' => false, 'available' => false, 'reason' => 'format']);
    exit;
}

$stmt = $mysqli->prepare('SELECT 1 FROM users WHERE pseudo = ? LIMIT 1');
if (!$stmt) {
    echo json_encode(['ok' => false, 'available' => false, 'reason' => 'server']);
    exit;
}

$stmt->bind_param('s', $pseudo);
$stmt->execute();
$stmt->store_result();

$available = $stmt->num_rows === 0;

$stmt->close();

echo json_encode(['ok' => true, 'available' => $available]);
