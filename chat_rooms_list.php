<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php'; require __DIR__.'/auth.php'; require_login();
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');

$sql = "SELECT r.id, r.name,
        (SELECT MAX(created_at) FROM chat_messages cm WHERE cm.room_id=r.id) AS last_at
        FROM chat_rooms r
        ORDER BY COALESCE(last_at, r.created_at) DESC, r.id DESC
        LIMIT 100";
$res = $mysqli->query($sql);
$rooms = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
echo json_encode(['ok'=>true,'rooms'=>$rooms], JSON_UNESCAPED_UNICODE);
