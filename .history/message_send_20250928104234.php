<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  echo json_encode(['ok'=>false,'error'=>'method']);
  exit;
}

// CSRF strict : refuser si session n'a pas de token ou si token POST est vide
$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || $csrf === '' || !hash_equals($_SESSION['csrf'], $csrf)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']);
  exit;
}

// Récupération & nettoyage inputs
$sender_id    = (int)($_SESSION['user_id'] ?? 0);
$recipient_id = (int)($_POST['recipient_id'] ?? 0);
$body_raw     = (string)($_POST['body'] ?? '');

// Basic sanity
if ($sender_id <= 0) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'auth']);
  exit;
}

// Remove undesirable control chars except newline/tab
$body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u', '', $body_raw);
$body = trim($body);
$body = mb_substr($body, 0, 2000); // borne serveur

if ($recipient_id <= 0 || $body === '') {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'missing']);
  exit;
}






$image_path = null;

// --- Image optionnelle ---
if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
  $f = $_FILES['image'];

  // erreurs upload
  if ($f['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok'=>false,'error'=>'upload']); exit;
  }

  // taille max ~5 Mo
  if ($f['size'] > 5 * 1024 * 1024) {
    echo json_encode(['ok'=>false,'error'=>'too_big']); exit;
  }

  // valider vrai fichier image
  $info = @getimagesize($f['tmp_name']);
  if ($info === false) {
    echo json_encode(['ok'=>false,'error'=>'not_image']); exit;
  }

  $mime = $info['mime'] ?? '';
  $ext  = match ($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    default      => null
  };
  if ($ext === null) {
    echo json_encode(['ok'=>false,'error'=>'bad_type']); exit;
  }

  // chemin de stockage
  $dir = __DIR__.'/uploads/msg';
  if (!is_dir($dir)) mkdir($dir, 0775, true);

  $name = bin2hex(random_bytes(16)).'.'.$ext;
  $dest = $dir.'/'.$name;

  if (!move_uploaded_file($f['tmp_name'], $dest)) {
    echo json_encode(['ok'=>false,'error'=>'move_failed']); exit;
  }

  // chemin relatif à servir dans le web
  $image_path = 'uploads/msg/'.$name;
}

// exiger au moins du texte OU une image
if ($image_path === null && $body === '') {
  echo json_encode(['ok'=>false,'error'=>'empty']); exit;
}

// insérer
$st = $mysqli->prepare("INSERT INTO messages (sender_id, recipient_id, body, image_path, created_at)
                        VALUES (?,?,?,?,NOW())");
$st->bind_param('iiss', $sender_id, $recipient_id, $body, $image_path);
if (!$st->execute()) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db']); exit;
}
$st->close();

echo json_encode(['ok'=>true]);















if ($recipient_id === $sender_id) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'self']);
  exit;
}

/* Rate limit simple: 3 msg / 30s
   Vérifie que prepare() retourne bien un statement.
*/
$cnt = 0;
$lim = $mysqli->prepare('SELECT COUNT(*) FROM messages WHERE sender_id=? AND created_at >= NOW() - INTERVAL 30 SECOND');
if (!$lim) {
  error_log('message_send: rate prepare failed: ' . $mysqli->error);
  // On refuse l'envoi pour être prudent
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server']);
  exit;
}
$lim->bind_param('i', $sender_id);
if (!$lim->execute()) {
  error_log('message_send: rate execute failed: ' . $lim->error);
  $lim->close();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server']);
  exit;
}
$lim->bind_result($cnt);
$lim->fetch();
$lim->close();

if ($cnt >= 3) {
  http_response_code(429);
  echo json_encode(['ok'=>false,'error'=>'rate']);
  exit;
}

/* Destinataire existe ? (prepare + error handling) */
$u = $mysqli->prepare('SELECT 1 FROM users WHERE id=? LIMIT 1');
if (!$u) {
  error_log('message_send: recipient prepare failed: ' . $mysqli->error);
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server']);
  exit;
}
$u->bind_param('i', $recipient_id);
if (!$u->execute()) {
  error_log('message_send: recipient execute failed: ' . $u->error);
  $u->close();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server']);
  exit;
}
$u->store_result();
if ($u->num_rows === 0) {
  $u->close();
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'recipient_not_found']);
  exit;
}
$u->close();

/* Insert message (prepare + execute with checks) */
$ins = $mysqli->prepare('INSERT INTO messages (sender_id, recipient_id, body) VALUES (?,?,?)');
if (!$ins) {
  error_log('message_send: insert prepare failed: ' . $mysqli->error);
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server']);
  exit;
}
$ins->bind_param('iis', $sender_id, $recipient_id, $body);
if (!$ins->execute()) {
  error_log('message_send: insert execute failed: ' . $ins->error);
  $ins->close();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db']);
  exit;
}

$id = $mysqli->insert_id;
$ins->close();

echo json_encode(['ok'=>true,'id'=>$id], JSON_UNESCAPED_UNICODE);
exit;
