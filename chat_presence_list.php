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

// fenêtre “actif” = vu dans les 60 s
$sql = "SELECT u.id, u.pseudo
        FROM chat_presence p
        JOIN users u ON u.id = p.user_id
        WHERE p.room_id=? AND p.last_seen > (NOW() - INTERVAL 60 SECOND)
        GROUP BY u.id, u.pseudo
        ORDER BY MAX(p.last_seen) DESC";

if (!$st = $mysqli->prepare($sql)) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'prep']); exit; }
$st->bind_param('i', $room_id);
$st->execute();
$res = $st->get_result();
$out = [];
while ($r = $res->fetch_assoc()) { $out[] = ['id'=>(int)$r['id'], 'pseudo'=>$r['pseudo']]; }
$st->close();

echo json_encode(['ok'=>true,'users'=>$out], JSON_UNESCAPED_UNICODE);
