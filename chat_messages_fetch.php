<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/* 1) Entrées */
$room_id = (int)($_GET['room_id'] ?? 0);
$after   = max(0, (int)($_GET['after_id'] ?? 0));
if ($room_id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'room_id']); exit; }

/* 2) Accès salon */
$st = $mysqli->prepare("SELECT is_private FROM chat_rooms WHERE id=?");
$st->bind_param('i', $room_id);
$st->execute();
$st->bind_result($is_private);
if (!$st->fetch()) { $st->close(); http_response_code(404); echo json_encode(['ok'=>false,'error'=>'notfound']); exit; }
$st->close();

if ((int)$is_private === 1 && empty($_SESSION['rooms_ok'][$room_id])) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'locked']); exit;
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

if ($after > 0) {
  $sql  = $baseSelect . " AND m.id > ? ORDER BY m.id ASC LIMIT 200";
  $stmt = $mysqli->prepare($sql);
  // ordre des paramètres = user_id, room_id, after
  $stmt->bind_param('iii', $_SESSION['user_id'], $room_id, $after);
} else {
  $sql  = $baseSelect . " ORDER BY m.id DESC LIMIT 50";
  $stmt = $mysqli->prepare($sql);
  // ordre des paramètres = user_id, room_id
  $stmt->bind_param('ii', $_SESSION['user_id'], $room_id);
}

$stmt->execute();
$res  = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* 4) Première page en ordre chronologique */
if ($after === 0) {
  $rows = array_reverse($rows);
}

/* 5) Sortie */
echo json_encode(['ok'=>true,'messages'=>$rows], JSON_UNESCAPED_UNICODE);
