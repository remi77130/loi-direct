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

$csrf = $_POST['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$sender_id = (int)($_SESSION['user_id'] ?? 0);
$recipient_id = (int)($_POST['recipient_id'] ?? 0);
if ($recipient_id <= 0 || $recipient_id === $sender_id) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'recipient']); exit;
}

/* Rate-limit léger: 3 DM / 30s */
$st = $mysqli->prepare("
  SELECT COUNT(*) FROM messages
  WHERE sender_id=? AND created_at >= (NOW() - INTERVAL 30 SECOND)
");
$st->bind_param('i', $sender_id);
$st->execute(); $st->bind_result($cnt); $st->fetch(); $st->close();
if ($cnt >= 3) { http_response_code(429); echo json_encode(['ok'=>false,'error'=>'rate_dm']); exit; }

/* Check destinataire */
$st = $mysqli->prepare("SELECT id FROM users WHERE id=? LIMIT 1");
$st->bind_param('i', $recipient_id);
$st->execute(); $exists = $st->get_result()->fetch_row(); $st->close();
if (!$exists) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'user']); exit; }


/* Corps + upload media optionnel */
$body = trim((string)($_POST['body'] ?? ''));
$body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u','', $body);
$body = mb_substr($body, 0, 2000);

$image_path = null; // on garde ce nom pour compat DB
$file_mime  = null;

if (!empty($_FILES['file']['name'])) {
  if (($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'upload_err','code'=>($_FILES['file']['error'] ?? null)]);
    exit;
  }

  // taille (utilise ta constante globale si tu veux)
  $maxBytes = (int)UPLOAD_MAX_MB * 1024 * 1024;   // exemple: 50MB si tu l'as mis
  if (($_FILES['file']['size'] ?? 0) > $maxBytes) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'size']);
    exit;
  }

  $tmp = $_FILES['file']['tmp_name'] ?? '';
  if ($tmp === '' || !is_file($tmp)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'tmp_missing']);
    exit;
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($tmp) ?: '';

  // whitelist image+video
  $allowed = [
    'image/jpeg','image/png','image/webp','image/gif',
    'video/mp4','video/webm','video/ogg',
  ];
  if (!in_array($mime, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'mime','detected'=>$mime]);
    exit;
  }

  $dir = __DIR__.'/uploads/msg';
  if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'dir']);
    exit;
  }

  $extMap = [
    'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif',
    'video/mp4'=>'mp4','video/webm'=>'webm','video/ogg'=>'ogv',
  ];
  $ext = $extMap[$mime] ?? 'bin';

  $basename = bin2hex(random_bytes(16));
  $dest = $dir.'/'.$basename.'.'.$ext;

  if (!move_uploaded_file($tmp, $dest)) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'move']);
    exit;
  }

  $image_path = 'uploads/msg/'.$basename.'.'.$ext; // chemin relatif
  $file_mime  = $mime;
}

if ($body === '' && !$image_path) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'empty']);
  exit;
}

/* Insert */
$st = $mysqli->prepare("
INSERT INTO messages (sender_id, recipient_id, body, image_path, file_mime, created_at)
  VALUES (?,?,?,?,?, NOW())
");
$st->bind_param('iisss', $sender_id, $recipient_id, $body, $image_path, $file_mime);
$ok = $st->execute();
if (!$ok) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }

echo json_encode(['ok'=>true, 'id'=>$st->insert_id]);
