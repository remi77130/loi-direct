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

/* Corps + upload image optionnel */
$body = trim((string)($_POST['body'] ?? ''));
$body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u','', $body);
$body = mb_substr($body, 0, 2000);
$image_path = null;

if (!empty($_FILES['image']['name'])) {
  if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['image']['tmp_name']) ?: '';
    if (!in_array($mime, ['image/jpeg','image/png','image/webp','image/gif'], true)) {
      http_response_code(400); echo json_encode(['ok'=>false,'error'=>'mime']); exit;
    }
    if ($_FILES['image']['size'] > 5*1024*1024) {
      http_response_code(400); echo json_encode(['ok'=>false,'error'=>'size']); exit;
    }
    $dir = __DIR__.'/uploads/msg';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $basename = bin2hex(random_bytes(16));
    $ext = match ($mime) {
      'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif', default=>'bin'
    };
    $dest = $dir.'/'.$basename.'.'.$ext;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
      http_response_code(500); echo json_encode(['ok'=>false,'error'=>'upload']); exit;
    }
    $image_path = 'uploads/msg/'.$basename.'.'.$ext;
  } else {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'upload_err']); exit;
  }
}

if ($body === '' && !$image_path) {
  http_response_code(400); echo json_encode(['ok'=>false,'error'=>'empty']); exit;
}

/* Insert */
$st = $mysqli->prepare("
  INSERT INTO messages (sender_id, recipient_id, body, image_path, created_at)
  VALUES (?,?,?,?, NOW())
");
$st->bind_param('iiss', $sender_id, $recipient_id, $body, $image_path);
$ok = $st->execute();
if (!$ok) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }

echo json_encode(['ok'=>true, 'id'=>$st->insert_id]);
