<?php
declare(strict_types=1);

session_start();

/* Le but de chat_notifications_list.php :

vérifier que l’utilisateur est connecté

récupérer ses notifications non lues

renvoyer du JSON propre avec la liste de ces notifications, et le nombre total de notifications non lues (pour affichage du badge)

*/

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

require_login();




$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
  http_response_code(401);
  echo json_encode([
    'ok' => false,
    'error' => 'auth'
  ]); // sécurité : on s’assure que l’utilisateur est connecté et que son ID est valide (positif)
  exit;
}

// Nombre max de notifs renvoyées
$limit = (int)($_GET['limit'] ?? 20);
if ($limit <= 0) $limit = 20; // sécurité : on s’assure que le paramètre limit est un entier positif, sinon on lui donne une valeur par défaut (20)
if ($limit > 100) $limit = 100; // sécurité : on impose une limite max raisonnable pour éviter les requêtes trop lourdes qui pourraient ralentir le serveur (on n’autorise pas de demander 1000 notifs d’un coup par exemple)

// Compteur des notifications non lues
$sqlCount = "
  SELECT COUNT(*) AS unread_count
  FROM chat_notifications
  WHERE user_id = ?
    AND is_read = 0
";
$stCount = $mysqli->prepare($sqlCount);

if (!$stCount) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => 'prepare_count'
  ]);
  exit;
}

$stCount->bind_param('i', $user_id);
$stCount->execute();
$resCount = $stCount->get_result();
$rowCount = $resCount ? $resCount->fetch_assoc() : null;
$unreadCount = (int)($rowCount['unread_count'] ?? 0);
$stCount->close();


// Liste détaillée des notifs non lues
$sql = "
  SELECT
    n.id,
    n.type,
    n.room_id,
    n.message_id,
    n.created_at,
    u.pseudo AS sender_pseudo,
    r.name   AS room_name
  FROM chat_notifications n
  INNER JOIN users u
    ON u.id = n.sender_id
  LEFT JOIN chat_rooms r
    ON r.id = n.room_id
  WHERE n.user_id = ?
    AND n.is_read = 0
  ORDER BY n.created_at DESC, n.id DESC
  LIMIT ?
";

$st = $mysqli->prepare($sql);

if (!$st) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => 'prepare_list'
  ]);
  exit;
}

$st->bind_param('ii', $user_id, $limit);
$st->execute();
$res = $st->get_result();

$items = [];

while ($row = $res->fetch_assoc()) {
  $items[] = [
    'id' => (int)$row['id'],
    'type' => (string)$row['type'],
    'room_id' => isset($row['room_id']) ? (int)$row['room_id'] : null,
    'message_id' => isset($row['message_id']) ? (int)$row['message_id'] : null,
    'created_at' => (string)$row['created_at'],
    'sender_pseudo' => (string)($row['sender_pseudo'] ?? ''),
    'room_name' => (string)($row['room_name'] ?? '')
  ];
}

$st->close();

echo json_encode([
  'ok' => true,
  'count_unread' => $unreadCount,
  'items' => $items
], JSON_UNESCAPED_UNICODE);