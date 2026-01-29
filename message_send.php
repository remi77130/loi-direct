<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  echo json_encode(['ok'=>false,'error'=>'method']); exit;
}

/* CSRF */
$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || $csrf === '' || !hash_equals($_SESSION['csrf'], $csrf)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$sender_id    = (int)($_SESSION['user_id'] ?? 0);
$recipient_id = (int)($_POST['recipient_id'] ?? 0);
$body_raw     = (string)($_POST['body'] ?? '');

if ($sender_id <= 0) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'auth']); exit; }
if ($recipient_id <= 0) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'missing_recipient']); exit; }
if ($recipient_id === $sender_id) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'self']); exit; }

/* Hygiène du texte (optionnel) */
$body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u', '', $body_raw);
$body = trim($body);
$body = mb_substr($body, 0, 2000);

/* Limiteur : 3 messages / 30s */
$lim = $mysqli->prepare('SELECT COUNT(*) FROM messages WHERE sender_id=? AND created_at >= NOW() - INTERVAL 30 SECOND');
$lim->bind_param('i', $sender_id);
$lim->execute();
$lim->bind_result($cnt); $lim->fetch(); $lim->close();
if ($cnt >= 3) { http_response_code(429); echo json_encode(['ok'=>false,'error'=>'rate']); exit; }

/* Destinataire existe ? */
$u = $mysqli->prepare('SELECT 1 FROM users WHERE id=? LIMIT 1');
$u->bind_param('i', $recipient_id);
$u->execute(); $u->store_result();
if ($u->num_rows === 0) { $u->close(); http_response_code(404); echo json_encode(['ok'=>false,'error'=>'recipient_not_found']); exit; }
$u->close();

/* Média optionnel (image ou vidéo) */
$image_path = null;
$file_mime  = null;

if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
  $f = $_FILES['image'];

  if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    echo json_encode(['ok'=>false,'error'=>'upload','code'=>($f['error'] ?? null)]);
    exit;
  }

  // Taille max (aligne avec ton config si tu veux)
  $maxBytes = 50 * 1024 * 1024; // 50MB
  if (($f['size'] ?? 0) > $maxBytes) {
    echo json_encode(['ok'=>false,'error'=>'too_big']);
    exit;
  }

  $tmp = $f['tmp_name'] ?? '';
  if ($tmp === '' || !is_file($tmp)) {
    echo json_encode(['ok'=>false,'error'=>'tmp_missing']);
    exit;
  }

  // MIME réel
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($tmp) ?: '';

  $allowed = [
    'image/jpeg','image/png','image/webp','image/gif',
    'video/mp4','video/webm','video/ogg',
  ];
  if (!in_array($mime, $allowed, true)) {
    echo json_encode(['ok'=>false,'error'=>'bad_type','detected'=>$mime]);
    exit;
  }

  // Extension
  $extMap = [
    'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif',
    'video/mp4'=>'mp4','video/webm'=>'webm','video/ogg'=>'ogv',
  ];
  $ext = $extMap[$mime] ?? null;
  if ($ext === null) {
    echo json_encode(['ok'=>false,'error'=>'bad_type']);
    exit;
  }

  $dir = __DIR__.'/uploads/msg';
  if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
    echo json_encode(['ok'=>false,'error'=>'dir']);
    exit;
  }

  $name = bin2hex(random_bytes(16)).'.'.$ext;
  $dest = $dir.'/'.$name;

  if (!move_uploaded_file($tmp, $dest)) {
    echo json_encode(['ok'=>false,'error'=>'move_failed']);
    exit;
  }

  // Si c'est une image : on peut vérifier qu'elle est valide
  if (str_starts_with($mime, 'image/')) {
    if (@getimagesize($dest) === false) {
      @unlink($dest);
      echo json_encode(['ok'=>false,'error'=>'not_image']);
      exit;
    }
  }

  $image_path = 'uploads/msg/'.$name; // chemin relatif web
  $file_mime  = $mime;
}

/* Exiger au moins du texte OU une image */
if ($image_path === null && $body === '') {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'empty']); exit;
}

/* Insertion */
$st = $mysqli->prepare("
  INSERT INTO messages (sender_id, recipient_id, body, image_path, file_mime, created_at)
  VALUES (?,?,?,?,?,NOW())
");
$st->bind_param('iisss', $sender_id, $recipient_id, $body, $image_path, $file_mime);

if (!$st->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }
$id = $mysqli->insert_id;
$st->close();

echo json_encode(['ok'=>true,'id'=>$id], JSON_UNESCAPED_UNICODE);
