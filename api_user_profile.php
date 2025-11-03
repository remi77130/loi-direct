<?php
declare(strict_types=1);
session_start();

require __DIR__.'/config.php';
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    if ($user_id <= 0) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'bad_user_id']);
        exit;
    }

    $sql = "SELECT id, pseudo, city, sex, height_cm, postal_code, relationship_status
            FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $u   = $res->fetch_assoc();
    $stmt->close();

    if (!$u) {
        http_response_code(404);
        echo json_encode(['ok'=>false,'error'=>'not_found']);
        exit;
    }

    $out = [
        'id'   => (int)$u['id'],
        'pseudo' => $u['pseudo'],
        'city' => $u['city'] ?? null,
        'avatar_url' => null,
        'bio'        => null,
        'age'        => null,
        'sex'                 => $u['sex'] ?? null,
        'height_cm'           => $u['height_cm'] !== null ? (int)$u['height_cm'] : null,
        'postal_code'         => $u['postal_code'] ?? null,
        'relationship_status' => $u['relationship_status'] ?? null,
    ];

    echo json_encode(['ok'=>true,'user'=>$out], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'server']);
}
    