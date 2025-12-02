<?php
// chat_typing_list.php — renvoie la liste des users qui tapent dans une room
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);

// --- Input ---
$roomId = filter_input(
    INPUT_GET,
    'room_id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!$roomId) {
    echo json_encode([
        'ok'    => false,
        'error' => 'bad_room',
        'users' => [],
    ]);
    exit;
}

// On ne garde que les pings des X dernières secondes
$delaySeconds = 8;

// IMPORTANT : correspond exactement à ta colonne `last_typing_at`
/*Points clés :
t.last_typing_at au lieu de t.last_at → aligné avec ta table.
On renvoie id + pseudo pour chaque user qui tape.
On peut filtrer l’utilisateur courant si tu ne veux pas voir “tu es en train d’écrire” (le continue). */
$sql = "
    SELECT u.id, u.pseudo
    FROM chat_typing t
    JOIN users u ON u.id = t.user_id
    WHERE t.room_id = ?
      AND t.last_typing_at >= (NOW() - INTERVAL ? SECOND)
    ORDER BY u.pseudo ASC
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    error_log('chat_typing_list prepare: ' . $mysqli->error);
    echo json_encode([
        'ok'    => false,
        'error' => 'server',
        'users' => [],
    ]);
    exit;
}

$stmt->bind_param('ii', $roomId, $delaySeconds);
$stmt->execute();
$res = $stmt->get_result();

$users = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        // Optionnel : ne pas se renvoyer soi-même
        if ((int)$row['id'] === $userId) {
            continue;
        }

        $users[] = [
            'id'     => (int)$row['id'],
            'pseudo' => $row['pseudo'],
        ];
    }
}
$stmt->close();

echo json_encode([
    'ok'    => true,
    'users' => $users,
]);
