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

/* Image optionnelle */
$image_path = null;
if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
  $f = $_FILES['image'];
  if ($f['error'] !== UPLOAD_ERR_OK) { echo json_encode(['ok'=>false,'error'=>'upload']); exit; }
  if ($f['size'] > 5*1024*1024)      { echo json_encode(['ok'=>false,'error'=>'too_big']); exit; }

  $info = @getimagesize($f['tmp_name']);
  if ($info === false) { echo json_encode(['ok'=>false,'error'=>'not_image']); exit; }
  $mime = $info['mime'] ?? '';
  $ext  = match($mime){ 'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp', default=>null };
  if ($ext === null) { echo json_encode(['ok'=>false,'error'=>'bad_type']); exit; }

  $dir = __DIR__.'/uploads/msg';
  if (!is_dir($dir)) mkdir($dir, 0775, true);
  $name = bin2hex(random_bytes(16)).'.'.$ext;
  $dest = $dir.'/'.$name;
  if (!move_uploaded_file($f['tmp_name'], $dest)) { echo json_encode(['ok'=>false,'error'=>'move_failed']); exit; }
  $image_path = 'uploads/msg/'.$name; // chemin relatif pour le web
}

/* Exiger au moins du texte OU une image */
if ($image_path === null && $body === '') {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'empty']); exit;
}

/* Insertion */
$st = $mysqli->prepare("INSERT INTO messages (sender_id, recipient_id, body, image_path, created_at)
                        VALUES (?,?,?,?,NOW())");
$st->bind_param('iiss', $sender_id, $recipient_id, $body, $image_path);
if (!$st->execute()) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit; }
$id = $mysqli->insert_id;
$st->close();

echo json_encode(['ok'=>true,'id'=>$id], JSON_UNESCAPED_UNICODE);
