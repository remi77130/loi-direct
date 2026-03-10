<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/* 1) Entrées */
$room_id = (int)($_GET['room_id'] ?? 0); // ID du salon dont on veut récupérer les messages, passé en paramètre de la requête (ex : chat_messages_fetch.php?room_id=123), sécurité : on s’assure que c’est un entier positif, sinon on considère que c’est une requête invalide
$after   = max(0, (int)($_GET['after_id'] ?? 0)); // ID du dernier message déjà affiché côté client, passé en paramètre de la requête (ex : chat_messages_fetch.php?room_id=123&after_id=456), sécurité : on s’assure que c’est un entier positif, sinon on considère que c’est une requête invalide, et on utilise cette info pour ne récupérer que les messages plus récents que celui-ci (id > after_id) afin d’éviter de renvoyer des messages déjà affichés côté client et d’optimiser la quantité de données transférées (surtout quand il y a beaucoup de messages dans le salon)
$focus_id = max(0, (int)($_GET['focus_id'] ?? 0)); // ID d’un message à mettre en avant (scrollIntoView + effet visuel), par exemple après l’envoi d’un message ou après un clic sur une notification, on peut vouloir faire scroller le chat vers un message précis et lui appliquer un effet de surbrillance pour le mettre en avant, dans ce cas on peut passer son ID dans le paramètre focus_id de la requête (ex : chat_messages_fetch.php?room_id=123&focus_id=456) et le frontend pourra utiliser cette info pour cibler ce message dans le DOM et lui appliquer les effets souhaités (scroll + surbrillance)
if ($room_id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'room_id']);
  exit;
}

/* 2) Accès salon + blocage si expiré */
$st = $mysqli->prepare("SELECT is_private, expires_at FROM chat_rooms WHERE id=?");
$st->bind_param('i', $room_id);
$st->execute();
$st->bind_result($is_private, $expires_at);

if (!$st->fetch()) {
  $st->close();
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'notfound']);
  exit;
}
$st->close();

// Si salon expiré => on bloque direct
// $expires_at est une string 'YYYY-MM-DD HH:MM:SS' ou NULL
if (!empty($expires_at) && strtotime((string)$expires_at) <= time()) {
  // Nettoyage optionnel : éviter "locked ok" qui traine en session
  unset($_SESSION['rooms_ok'][$room_id]);

  http_response_code(410); // Gone
  echo json_encode(['ok'=>false,'error'=>'expired']);
  exit;
}

// Salon privé => check unlock
if ((int)$is_private === 1 && empty($_SESSION['rooms_ok'][$room_id])) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'locked']);
  exit;
}

/* 3) Requête messages (JOIN users + likes dans les deux cas) */
$baseSelect = "
  SELECT
    m.id,
    m.sender_id,
    m.body,
    m.created_at,
    m.file_url,
    m.file_mime,
    m.file_w,
    m.file_h,
    u.avatar_url AS avatar_url,
    CASE
      WHEN m.color IS NOT NULL
       AND m.created_at >= NOW() - INTERVAL 5 MINUTE
      THEN m.color
      ELSE NULL
    END AS color,
    u.pseudo AS sender,
    m.like_count,
    CASE WHEN ml.user_id IS NULL THEN 0 ELSE 1 END AS liked_by_me
  FROM chat_messages m
  LEFT JOIN users u ON u.id = m.sender_id
  LEFT JOIN message_likes ml
         ON ml.message_id = m.id AND ml.user_id = ?
  WHERE m.room_id = ?
";

if ($focus_id > 0) { // cas d’un focus sur un message précis (ex : après envoi d’un message ou après clic sur une notification), on affiche un bloc centré sur ce message (par exemple 25 messages avant et 25 messages après) pour donner du contexte autour de ce message ciblé, et on vérifie que ce message ciblé appartient bien à ce salon pour éviter les incohérences (par exemple un message d’un autre salon qui s’affiche dans le chat alors qu’on a cliqué sur une notification d’un autre salon, ou un message qui n’existe pas du tout)

  // Vérifie que le message ciblé appartient bien à cette room
  $stFocus = $mysqli->prepare("SELECT id FROM chat_messages WHERE id = ? AND room_id = ? LIMIT 1");
  $stFocus->bind_param('ii', $focus_id, $room_id);
  $stFocus->execute();
  $stFocus->store_result();

  if ($stFocus->num_rows === 0) {
    $stFocus->close();
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'focus_notfound']);
    exit;
  }
  $stFocus->close();


    // Si on ouvre un message précis via focus_id,
  // on marque comme lues les notifications de cet utilisateur
  // liées à ce message dans cette room.
  $stRead = $mysqli->prepare("
    UPDATE chat_notifications
    SET is_read = 1
    WHERE user_id = ?
      AND room_id = ?
      AND message_id = ?
      AND is_read = 0
  ");

  if ($stRead) {
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    $stRead->bind_param('iii', $currentUserId, $room_id, $focus_id);
    $stRead->execute();
    $stRead->close();
  }


  // On récupère un bloc centré sur le message ciblé :
  // - jusqu'à 25 messages avant (avec le focus inclus dans ce bloc)
  // - jusqu'à 25 messages après
  $sql = $baseSelect . "
    AND m.id IN (
      SELECT z.id
      FROM (
        SELECT id
        FROM chat_messages
        WHERE room_id = ?
          AND id <= ?
        ORDER BY id DESC
        LIMIT 25
      ) AS z

      UNION

      SELECT z2.id
      FROM (
        SELECT id
        FROM chat_messages
        WHERE room_id = ?
          AND id > ?
        ORDER BY id ASC
        LIMIT 25
      ) AS z2
    )
    ORDER BY m.id ASC
  ";

  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param(
    'iiiiii',
    $_SESSION['user_id'], // pour liked_by_me dans LEFT JOIN message_likes
    $room_id,             // WHERE m.room_id = ? du $baseSelect
    $room_id,             // sous-requête "avant"
    $focus_id,
    $room_id,             // sous-requête "après"
    $focus_id
  );

} elseif ($after > 0) { // pages suivantes après la première, on affiche les messages dans l’ordre de création normal (ASC) pour que ça s’affiche dans le bon sens dans le chat

  $sql  = $baseSelect . " AND m.id > ? ORDER BY m.id ASC LIMIT 200";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('iii', $_SESSION['user_id'], $room_id, $after);

} else {

  // Première ouverture : on prend les 50 derniers, puis on les remet dans l'ordre chrono
  $sql = "
    SELECT * FROM (
      " . $baseSelect . "
      ORDER BY m.id DESC
      LIMIT 50
    ) t
    ORDER BY t.id ASC
  ";

  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('ii', $_SESSION['user_id'], $room_id);
}

/*Cas 1 — focus_id > 0

Tu obtiens un bloc centré sur le message demandé.
Pas juste “les derniers messages”.
Cas 2 — after_id > 0
Tu gardes ton comportement actuel :
nouveaux messages uniquement
ordre asc
limite 200 
chat_messages_fetch
Cas 3 — ouverture normale
Tu gardes aussi ton comportement actuel :
les 50 derniers
puis remise en ordre chronologique pour affichage dans le bon sens dans le chat
*/



/* ancien bloque 08.03.26
// bloc conditionnel SQL pour ne récupérer que les messages plus récents que after_id si after_id > 0, sinon on récupère les 50 derniers messages du salon (dans ce cas on affiche les messages dans l’ordre chronologique inverse côté client pour que les plus récents soient en bas du chat, et pour les pages suivantes après la première on affiche les messages dans l’ordre de création normal (ASC) pour que ça s’affiche dans le bon sens dans le chat)
if ($after > 0) {
  $sql  = $baseSelect . " AND m.id > ? ORDER BY m.id ASC LIMIT 200"; // pour la première page (after=0) on affiche les 50 derniers messages en ordre chronologique, pour les pages suivantes (after>0) on affiche les messages dans l’ordre de création (ASC) pour que ça s’affiche dans le bon sens dans le chat, et on augmente la limite à 200 pour permettre de charger plus de messages à la fois quand on scroll vers le haut (vu que c’est plus rare de devoir charger beaucoup de messages récents que d’avoir besoin de charger beaucoup de messages anciens quand on scroll vers le haut)
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('iii', $_SESSION['user_id'], $room_id, $after);
} 

else { // première page, on affiche les 50 derniers messages du salon dans l’ordre chronologique inverse (les plus récents en bas du chat)
  $sql  = $baseSelect . " ORDER BY m.id DESC LIMIT 50";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('ii', $_SESSION['user_id'], $room_id);
}*/

$stmt->execute();
$res  = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* 4) Première page en ordre chronologique */
if ($after === 0 && $focus_id === 0) {
  $rows = array_reverse($rows);
}

/* 5) Sortie */
echo json_encode([
  'ok' => true,
  'messages' => $rows,
  'focus_id' => $focus_id,
  'test_debug' => 'HELLO123'
], JSON_UNESCAPED_UNICODE);