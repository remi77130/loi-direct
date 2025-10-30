<?php
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';
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

$user_id = (int)($_SESSION['user_id'] ?? 0);
$room_id = (int)($_POST['room_id'] ?? 0);
$body = trim((string)($_POST['body'] ?? ''));
$body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u','',$body);
$body = mb_substr($body, 0, 2000);

if ($room_id <= 0) { echo json_encode(['ok'=>false,'error'=>'room']); exit; }

/* ---------- Anti-abus / rate limiting ---------- */
$now = date('Y-m-d H:i:s');

/* 1) Burst global: max 3 messages / 30s par utilisateur */
$st = $mysqli->prepare("
  SELECT COUNT(*) 
  FROM chat_messages 
  WHERE sender_id=? AND created_at >= (NOW() - INTERVAL 30 SECOND)
");
$st->bind_param('i', $user_id);
$st->execute();
$st->bind_result($cnt30);
$st->fetch();
$st->close();
if ($cnt30 >= 3) {
  http_response_code(429);
  echo json_encode(['ok'=>false,'error'=>'rate_glob']);
  exit;
}

/* 2) Par salon: max 2 messages / 5s dans le même salon */
$st = $mysqli->prepare("
  SELECT COUNT(*) 
  FROM chat_messages 
  WHERE room_id=? AND sender_id=? AND created_at >= (NOW() - INTERVAL 5 SECOND)
");
$st->bind_param('ii', $room_id, $user_id);
$st->execute();
$st->bind_result($cnt5);
$st->fetch();
$st->close();
if ($cnt5 >= 2) {
  http_response_code(429);
  echo json_encode(['ok'=>false,'error'=>'rate_room']);
  exit;
}

/* 3) Min-interval (anti double-clic/réseau): ≥ 800 ms */
$last = (float)($_SESSION['last_msg_at'] ?? 0);
$nowMs = microtime(true);
if (($nowMs - $last) < 0.8) {
  http_response_code(429);
  echo json_encode(['ok'=>false,'error'=>'rate_fast']);
  exit;
}
$_SESSION['last_msg_at'] = $nowMs;

/* ---- Upload image optionnel ---- */
$fileUrl = null; $fileMime = null; $w = null; $h = null;

if (!empty($_FILES['image']['name'])) {
  if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok'=>false,'error'=>'upload']); exit;
  }

  $maxBytes = (int)UPLOAD_MAX_MB * 1024 * 1024;
  if (($_FILES['image']['size'] ?? 0) > $maxBytes) {
    echo json_encode(['ok'=>false,'error'=>'too_big']); exit;
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($_FILES['image']['tmp_name']) ?: '';
  if (!in_array($mime, UPLOAD_ALLOWED, true)) {
    echo json_encode(['ok'=>false,'error'=>'mime']); exit;
  }

  if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0775, true)) {
    echo json_encode(['ok'=>false,'error'=>'dir']); exit;
  }

  $ext = $mime === 'image/jpeg' ? 'jpg' : ($mime === 'image/png' ? 'png' : 'webp');
  $fname = 'chat_'.date('Ymd_His').'_'.bin2hex(random_bytes(6)).'.'.$ext;
  $dest  = rtrim(UPLOAD_DIR,'/').'/'.$fname;

  if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
    echo json_encode(['ok'=>false,'error'=>'move']); exit;
  }

  [$imgW,$imgH] = @getimagesize($dest) ?: [null,null];
  $fileUrl  = rtrim(UPLOAD_URL,'/').'/'.$fname;
  $fileMime = $mime; $w = $imgW; $h = $imgH;
}

if ($body === '' && !$fileUrl) {
  echo json_encode(['ok'=>false,'error'=>'empty']); exit;
}

/* ---- Insertion (MySQLi) ---- */
$mysqli->begin_transaction();

$sql = "INSERT INTO chat_messages (room_id, sender_id, body, file_url, file_mime, file_w, file_h)
        VALUES (?,?,?,?,?,?,?)";
$st = $mysqli->prepare($sql);
$st->bind_param('iisssii', $room_id, $user_id, $body, $fileUrl, $fileMime, $w, $h);
$ok = $st->execute();
$id = $ok ? $st->insert_id : 0;
$st->close();

if ($ok) {
  $mysqli->commit();
  echo json_encode(['ok'=>true,'id'=>$id]);
} else {
  $mysqli->rollback();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db']);
}
