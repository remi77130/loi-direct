<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php'; require __DIR__.'/auth.php'; require_login();
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');

$csrf = $_POST['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf']??'', $csrf)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'csrf']); exit; }

$name = trim((string)($_POST['name']??'')); $name = mb_substr($name,0,60);
if ($name===''){ http_response_code(422); echo json_encode(['ok'=>false,'error'=>'nom']); exit; }

$ins = $mysqli->prepare("INSERT INTO chat_rooms (name, created_by) VALUES (?, ?)");
$ins->bind_param('si', $name, $_SESSION['user_id']);
if($ins->execute()) echo json_encode(['ok'=>true,'id'=>$mysqli->insert_id], JSON_UNESCAPED_UNICODE);
else { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); }
$ins->close();
