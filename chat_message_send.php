<?php

/**
 * chat_message_send.php
 * Envoi d’un message texte + image optionnelle.
 * Garde-fous :
 *  - CSRF
 *  - 3 msgs / 30 s par user (global)
 *  - 2 msgs / 5 s par salon
 *  - anti double-clic ~800 ms
 * Accès :
 *  - Refus si salon privé non déverrouillé dans la session.
 */
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';  // UPLOAD_* constantes
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'method']); exit;
}
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$color = strtoupper(trim($_POST['color'] ?? ''));
if (!preg_match('/^#[0-9A-F]{6}$/', $color)) { $color = null; }

$user_id = (int)($_SESSION['user_id'] ?? 0);
$room_id = (int)($_POST['room_id'] ?? 0);
if ($room_id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'room']); exit; }

/* 1) Accès salon (privé/public) */
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

/* 2) Rate limiting */
$st = $mysqli->prepare("SELECT COUNT(*) FROM chat_messages WHERE sender_id=? AND created_at >= (NOW() - INTERVAL 30 SECOND)");
$st->bind_param('i', $user_id); $st->execute(); $st->bind_result($cnt30); $st->fetch(); $st->close();
if ($cnt30 >= 3) { http_response_code(429); echo json_encode(['ok'=>false,'error'=>'rate_glob']); exit; }

$st = $mysqli->prepare("SELECT COUNT(*) FROM chat_messages WHERE room_id=? AND sender_id=? AND created_at >= (NOW() - INTERVAL 5 SECOND)");
$st->bind_param('ii', $room_id, $user_id); $st->execute(); $st->bind_result($cnt5); $st->fetch(); $st->close();
if ($cnt5 >= 2) { http_response_code(429); echo json_encode(['ok'=>false,'error'=>'rate_room']); exit; }

$last = (float)($_SESSION['last_msg_at'] ?? 0);
if ((microtime(true) - $last) < 0.8) { http_response_code(429); echo json_encode(['ok'=>false,'error'=>'rate_fast']); exit; }
$_SESSION['last_msg_at'] = microtime(true);

/* 3) Contenu + upload image */
$body = trim((string)($_POST['body'] ?? ''));
$body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u','',$body);
$body = mb_substr($body,0,2000);


// Détection des @mentions dans le message
$mentionedPseudos = [];

if ($body !== '') {
  preg_match_all('/(^|\s)@([A-Za-zÀ-ÖØ-öø-ÿ0-9_.-]{2,30})/u', $body, $matches); // regex pour détecter les @mentions, en capturant le pseudo dans le groupe 2 (sans le @)

  // $matches[2] contient uniquement les pseudos sans le @
  if (!empty($matches[2])) {
    $mentionedPseudos = array_values(array_unique($matches[2])); // on garde uniquement les pseudos uniques pour éviter les requêtes redondantes
  }
}


// Recherche des user_id correspondant aux pseudos mentionnés
$mentionedUserIds = [];

if (!empty($mentionedPseudos)) {
  $placeholders = implode(',', array_fill(0, count($mentionedPseudos), '?')); // on crée une chaîne de placeholders pour la requête préparée, ex: "?, ?, ?"

  $sql = "SELECT id, pseudo
          FROM users
          WHERE pseudo IN ($placeholders)";

  $st = $mysqli->prepare($sql);

  if (!$st) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'prepare_mentions_lookup']);
    exit;
  }

  $types = str_repeat('s', count($mentionedPseudos));
  $st->bind_param($types, ...$mentionedPseudos); // on lie les pseudos en tant que paramètres de la requête préparée
  $st->execute();

  $res = $st->get_result();

  while ($row = $res->fetch_assoc()) {
    $targetId = (int)($row['id'] ?? 0);

    // on évite de se mentionner soi-même
    if ($targetId > 0 && $targetId !== $user_id) {
      $mentionedUserIds[] = $targetId;
    }
  }

  $st->close();

  // sécurité anti doublons
  $mentionedUserIds = array_values(array_unique($mentionedUserIds));
}





$fileUrl = $fileMime = null;
$w = $h = null;

if (!empty($_FILES['file']['name'])) {

  if (($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    echo json_encode(['ok'=>false,'error'=>'upload','code'=>($_FILES['file']['error'] ?? null)]);
    exit;
  }

  $maxBytes = (int)UPLOAD_MAX_MB * 1024 * 1024;
  if (($_FILES['file']['size'] ?? 0) > $maxBytes) {
    echo json_encode(['ok'=>false,'error'=>'too_big']);
    exit;
  }

  $tmp = $_FILES['file']['tmp_name'] ?? '';
  if ($tmp === '' || !is_file($tmp)) {
    echo json_encode(['ok'=>false,'error'=>'tmp_missing']);
    exit;
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($tmp) ?: '';
  if (!in_array($mime, UPLOAD_ALLOWED, true)) {
    echo json_encode(['ok'=>false,'error'=>'mime','detected'=>$mime]);
    exit;
  }

  if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0775, true)) {
    echo json_encode(['ok'=>false,'error'=>'dir']);
    exit;
  }

  $extMap = [
    'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif',
    'video/mp4'=>'mp4','video/webm'=>'webm','video/ogg'=>'ogv',
  ];
  $ext = $extMap[$mime] ?? 'bin';

  $fname = 'chat_'.date('Ymd_His').'_'.bin2hex(random_bytes(6)).'.'.$ext;
  $dest  = rtrim(UPLOAD_DIR,'/').'/'.$fname;

  if (!move_uploaded_file($tmp, $dest)) {
    echo json_encode(['ok'=>false,'error'=>'move']);
    exit;
  }

  if (str_starts_with($mime,'image/')) {
    $info = @getimagesize($dest);
    if (!$info) { echo json_encode(['ok'=>false,'error'=>'bad_image']); exit; }
    $w = (int)$info[0];
    $h = (int)$info[1];
  }

  $fileUrl  = rtrim(UPLOAD_URL,'/').'/'.$fname;
  $fileMime = $mime;
}


if ($body === '' && !$fileUrl) { echo json_encode(['ok'=>false,'error'=>'empty']); exit; }




$mysqli->begin_transaction();

try {
  // 1) Insertion du message
  $st = $mysqli->prepare("
    INSERT INTO chat_messages
      (room_id, sender_id, body, file_url, file_mime, file_w, file_h, color)
    VALUES
      (?, ?, ?, ?, ?, ?, ?, ?)
  ");

  if (!$st) {
    throw new RuntimeException('prepare_insert_message');
  }

  $st->bind_param('iisssiis', $room_id, $user_id, $body, $fileUrl, $fileMime, $w, $h, $color);

  if (!$st->execute()) {
    $st->close();
    throw new RuntimeException('execute_insert_message');
  }

  $message_id = (int)$st->insert_id;
  $st->close();

  // 2) Insertion des notifications de mention
  // On ne fait rien si aucune mention valide n'a été trouvée
  if (!empty($mentionedUserIds)) {
    $stNotif = $mysqli->prepare("
      INSERT IGNORE INTO chat_notifications
        (user_id, sender_id, room_id, message_id, type, is_read, created_at)
      VALUES
        (?, ?, ?, ?, 'mention', 0, NOW())
    ");

    if (!$stNotif) {
      throw new RuntimeException('prepare_insert_notification');
    }

    foreach ($mentionedUserIds as $targetUserId) {
      $targetUserId = (int)$targetUserId;

      if ($targetUserId <= 0 || $targetUserId === $user_id) {
        continue;
      }

      $stNotif->bind_param('iiii', $targetUserId, $user_id, $room_id, $message_id);

      if (!$stNotif->execute()) {
        $stNotif->close();
        throw new RuntimeException('execute_insert_notification');
      }
    }

    $stNotif->close();
  }

  // 3) Tout est OK → commit
  $mysqli->commit();

  echo json_encode([
    'ok' => true,
    'id' => $message_id
  ]);
  exit;

} catch (Throwable $e) {
  $mysqli->rollback();
  http_response_code(500);

  echo json_encode([
    'ok' => false,
    'error' => 'db'
  ]);
  exit;
}


/* 4) Insert 07.03.26
$mysqli->begin_transaction();
$st = $mysqli->prepare("INSERT INTO chat_messages (room_id,sender_id,body,file_url,file_mime,file_w,file_h, color) VALUES (?,?,?,?,?,?,?,?)");
$st->bind_param('iisssiis', $room_id, $user_id, $body, $fileUrl, $fileMime, $w, $h, $color);
$ok = $st->execute(); $id = $ok ? $st->insert_id : 0; $st->close();

if ($ok) { $mysqli->commit(); echo json_encode(['ok'=>true,'id'=>$id]); }
else { $mysqli->rollback(); http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); }
*/