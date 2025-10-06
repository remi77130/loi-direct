<?php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require __DIR__.'/config.php'; // <— pour UPLOAD_DIR

require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405); header('Allow: POST');
  echo json_encode(['ok'=>false,'error'=>'method']); exit;
}

$csrf = $_POST['csrf'] ?? '';
if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
  http_response_code(400); echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$body = trim((string)($_POST['body'] ?? ''));
$body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u','', $body);
$body = mb_substr($body, 0, 2000);

// alias éventuels depuis le front
if (isset($_POST['message']) && $body === '') $body = trim((string)$_POST['message']);

// ---- Mode 1 : message privé (DM) ----
$recipient_id = (int)($_POST['recipient_id'] ?? 0);
if ($recipient_id > 0 && $recipient_id !== $user_id) {
  if ($body === '' && empty($_FILES['image']['tmp_name'])) {
    http_response_code(422); echo json_encode(['ok'=>false,'error'=>'missing']); exit;
  }

  // destinataire existe ?
// vérif destinataire
$st = $mysqli->prepare('SELECT id FROM users WHERE id=? LIMIT 1');
$st->bind_param('i', $recipient_id);
$st->execute();
$st->store_result();
if ($st->num_rows === 0) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'user']); exit; }
$st->free_result(); $st->close();

  // rate-limit simple
  $rl = $mysqli->prepare("SELECT COUNT(*) FROM messages WHERE sender_id=? AND created_at>=NOW()-INTERVAL 5 SECOND");
  $rl->bind_param('i', $user_id); $rl->execute(); $rl->bind_result($c); $c=0; $rl->fetch(); $rl->close();
  if ($c >= 5) { http_response_code(429); echo json_encode(['ok'=>false,'error'=>'rate']); exit; }

  // upload image optionnel
  $image_path = null;
  if (!empty($_FILES['image']['tmp_name'])) {
    $tmp = $_FILES['image']['tmp_name'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';
    $ok = in_array($mime, ['image/jpeg','image/png','image/webp'], true);
    if (!$ok) { http_response_code(415); echo json_encode(['ok'=>false,'error'=>'mime']); exit; }
    if (($_FILES['image']['size'] ?? 0) > 5*1024*1024) { http_response_code(413); echo json_encode(['ok'=>false,'error'=>'size']); exit; }
    $ext = $mime === 'image/jpeg' ? '.jpg' : ($mime === 'image/png' ? '.png' : '.webp');
    $name = bin2hex(random_bytes(16)).$ext;



    // Assure-toi d’avoir UPLOAD_DIR défini (ou remplace par un chemin sûr)
    $dir = defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__.'/uploads';
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    $dest = $dir.'/'.$name;
    if (!move_uploaded_file($tmp, $dest)) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'upload']); exit; }
    $image_path = $name; // stocke seulement le nom; l’URL sera BASE/uploads/$name
  }

  $ins = $mysqli->prepare("INSERT INTO messages (sender_id,recipient_id,body,image_path) VALUES (?,?,?,?)");
  $ins->bind_param('iiss', $user_id, $recipient_id, $body, $image_path);
  if ($ins->execute()) {
    echo json_encode(['ok'=>true,'id'=>$mysqli->insert_id], JSON_UNESCAPED_UNICODE);
  } else {
    http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']);
  }
  $ins->close();
  exit;
}

// ---- Mode 2 : message de salon ----
$room_id = (int)($_POST['room_id'] ?? 0);
if ($room_id > 0) {
  if ($body === '') { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'missing']); exit; }

  $chk = $mysqli->prepare("SELECT is_private FROM chat_rooms WHERE id=? LIMIT 1");
  $chk->bind_param('i', $room_id);
  $chk->execute();
  $chk->bind_result($priv);
  if (!$chk->fetch()) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'room']); exit; }
  $chk->close();

  if ((int)$priv === 1) {
    // si salle privée : vérifier membership
$mem = $mysqli->prepare("SELECT 1 FROM chat_room_members WHERE room_id=? AND user_id=? AND is_banned=0 LIMIT 1");
$mem->bind_param('ii', $room_id, $user_id);
$mem->execute();
$mem->store_result();
if ($mem->num_rows === 0) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'forbidden']); exit; }
$mem->free_result(); $mem->close();
  }

  $rl = $mysqli->prepare("SELECT COUNT(*) FROM chat_messages WHERE sender_id=? AND created_at>=NOW()-INTERVAL 5 SECOND");
  $rl->bind_param('i', $user_id); $rl->execute(); $rl->bind_result($c); $c=0; $rl->fetch(); $rl->close();
  if ($c >= 5) { http_response_code(429); echo json_encode(['ok'=>false,'error'=>'rate']); exit; }

  $ins = $mysqli->prepare("INSERT INTO chat_messages (room_id,sender_id,body) VALUES (?,?,?)");
  $ins->bind_param('iis', $room_id, $user_id, $body);
  if ($ins->execute()) {
    echo json_encode(['ok'=>true,'id'=>$mysqli->insert_id], JSON_UNESCAPED_UNICODE);
  } else {
    http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']);
  }
  $ins->close();
  exit;
}

// ni recipient_id ni room_id
http_response_code(422);
echo json_encode(['ok'=>false,'error'=>'missing']);
$mime = '';
if (class_exists('finfo')) {
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($tmp) ?: '';
} else {
  $gi = @getimagesize($tmp);
  $mime = $gi['mime'] ?? '';
}