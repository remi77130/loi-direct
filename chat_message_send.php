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

/* 4) Insert */
$mysqli->begin_transaction();
$st = $mysqli->prepare("INSERT INTO chat_messages (room_id,sender_id,body,file_url,file_mime,file_w,file_h, color) VALUES (?,?,?,?,?,?,?,?)");
$st->bind_param('iisssiis', $room_id, $user_id, $body, $fileUrl, $fileMime, $w, $h, $color);
$ok = $st->execute(); $id = $ok ? $st->insert_id : 0; $st->close();

if ($ok) { $mysqli->commit(); echo json_encode(['ok'=>true,'id'=>$id]); }
else { $mysqli->rollback(); http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); }
