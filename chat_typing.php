<?php
// chat_typing.php — enregistre le "je suis en train d'écrire"
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login(); // doit mettre $_SESSION['user_id']

$userId = (int)($_SESSION['user_id'] ?? 0);

// --- Récup input ---
$roomId = filter_input(
    INPUT_POST,
    'room_id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$csrf = $_POST['csrf'] ?? '';

if (!$roomId) {
    echo json_encode(['ok' => false, 'error' => 'bad_room']);
    exit;
}

// Vérif CSRF
if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    echo json_encode(['ok' => false, 'error' => 'csrf']);
    exit;
}

// Optionnel : vérifier que la room existe
$chk = $mysqli->prepare('SELECT 1 FROM chat_rooms WHERE id=? LIMIT 1');
if (!$chk) {
    error_log('chat_typing room check prepare: ' . $mysqli->error);
    echo json_encode(['ok' => false, 'error' => 'server']);
    exit;
}
$chk->bind_param('i', $roomId);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    $chk->close();
    echo json_encode(['ok' => false, 'error' => 'room_not_found']);
    exit;
}
$chk->close();

// --- Upsert dans table "chat_typing" ---
// Schéma attendu : PRIMARY KEY (room_id, user_id)
// Et colonne DATETIME : last_typing_at
$stmt = $mysqli->prepare("
    INSERT INTO chat_typing (room_id, user_id, last_typing_at)
    VALUES (?, ?, NOW())
    ON DUPLICATE KEY UPDATE last_typing_at = NOW()
");
if (!$stmt) {
    error_log('chat_typing upsert prepare: ' . $mysqli->error);
    echo json_encode(['ok' => false, 'error' => 'server']);
    exit;
}
$stmt->bind_param('ii', $roomId, $userId);
$stmt->execute();
$stmt->close();

echo json_encode(['ok' => true]);
