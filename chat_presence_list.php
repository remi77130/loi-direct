<?php
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$room_id = (int)($_GET['room_id'] ?? 0);
if ($room_id<=0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad_room']); exit; }

$ttl = 45; // secondes de fraîcheur
$sql = "SELECT u.id, u.pseudo
        FROM chat_presence p
        JOIN users u ON u.id = p.user_id
        WHERE p.room_id = ? AND p.last_seen > (NOW() - INTERVAL ? SECOND)
        GROUP BY u.id, u.pseudo
        ORDER BY u.pseudo";
$st = $mysqli->prepare($sql);
$st->bind_param('ii', $room_id, $ttl);
$st->execute();
$res = $st->get_result();
$users = [];
while ($r = $res->fetch_assoc()) $users[] = ['id'=>(int)$r['id'],'pseudo'=>$r['pseudo']];
$st->close();

echo json_encode(['ok'=>true,'users'=>$users], JSON_UNESCAPED_UNICODE);
