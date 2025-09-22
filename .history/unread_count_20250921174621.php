<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();
header('Content-Type: application/json; charset=utf-8');

$uid = (int)$_SESSION['user_id'];
$stmt = $mysqli->prepare('SELECT COUNT(*) FROM messages WHERE recipient_id=? AND read_at IS NULL');
$stmt->bind_param('i', $uid);
$stmt->execute(); $stmt->bind_result($n); $stmt->fetch(); $stmt->close();

echo json_encode(['ok'=>true,'unread'=>(int)$n]);
    