<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$pc = trim((string)($_GET['postal'] ?? ''));
if (!preg_match('/^\d{5}$/', $pc)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad_postal']); exit; }

$stmt = $mysqli->prepare("SELECT DISTINCT city FROM cities WHERE postal_code=? ORDER BY city LIMIT 15");
if(!$stmt){ http_response_code(500); echo json_encode(['ok'=>false]); exit; }
$stmt->bind_param('s',$pc);
$stmt->execute();
$res = $stmt->get_result();
$cities = [];
while($r=$res->fetch_assoc()){ $cities[]=$r['city']; }
$stmt->close();

echo json_encode(['ok'=>true,'cities'=>$cities], JSON_UNESCAPED_UNICODE);
