<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();


/* Ce fichier sert uniquement à mettre à jour la présence utilisateur dans un salon.

Flux normal :

Le navigateur envoie un ping régulier (toutes les X secondes).

Le serveur met à jour la ligne correspondante dans chat_presence.

last_seen est rafraîchi.

Les utilisateurs actifs sont ceux dont last_seen est récent.

Chaque onglet navigateur possède sa propre session_key.

Donc un même utilisateur peut apparaître avec plusieurs sessions.

/*
|--------------------------------------------------------------------------
| Mise à jour de la présence utilisateur
|--------------------------------------------------------------------------
|
| Cette requête permet de :
|
| - créer une présence si elle n'existe pas
| - ou mettre à jour la présence si elle existe déjà
|
| Cela fonctionne grâce à ON DUPLICATE KEY UPDATE.
|
| Le système repose sur :
|   session_key (clé primaire)
|
| Chaque onglet du navigateur possède donc sa propre présence.
|
| last_seen est mis à jour à chaque "ping".
| Les utilisateurs actifs sont ceux dont last_seen est récent.
|
*/*/ 
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user_id = (int)($_SESSION['user_id'] ?? 0);
$room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
$session = (string)($_POST['session_key'] ?? '');

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_user']);
    exit;
}

if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $session)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_session']);
    exit;
}

$sql = "
    INSERT INTO chat_presence (room_id, user_id, session_key, last_seen)
    VALUES (?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE
        room_id = VALUES(room_id),
        user_id = VALUES(user_id),
        last_seen = NOW()
";

$st = $mysqli->prepare($sql);
if (!$st) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'db_prepare_upsert']);
    exit;
}

$st->bind_param('iis', $room_id, $user_id, $session);
$st->execute();
$st->close();

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);