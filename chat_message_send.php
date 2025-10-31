<?php
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';
require __DIR__.'/db.php';      // Définit $mysqli (connexion MySQLi)
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/* ============================================================
 *     1. VALIDATION BASIQUE DE LA REQUÊTE
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'method']);
  exit;
}

/* Vérification du token CSRF (anti falsification de requête) */
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'csrf']);
  exit;
}

/* ============================================================
 *     2. ANTI-ABUS / RATE LIMITING
 * ============================================================
 * Objectif : limiter le spam ou les attaques flood sur le chat.
 * Trois niveaux de protection :
 *   (a) global : max 3 messages / 30 secondes par utilisateur
 *   (b) par salon : max 2 messages / 5 secondes dans le même salon
 *   (c) min interval : délai minimal 800 ms entre deux envois (anti double-clic)
 * ============================================================ */
$user_id = (int)($_SESSION['user_id'] ?? 0);
$room_id = (int)($_POST['room_id'] ?? 0);
$now     = date('Y-m-d H:i:s');

/* (a) Limite globale : 3 messages / 30 secondes */
$st = $mysqli->prepare("
  SELECT COUNT(*)
  FROM chat_messages
  WHERE sender_id = ? AND created_at >= (NOW() - INTERVAL 30 SECOND)
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

/* (b) Limite par salon : 2 messages / 5 secondes */
$st = $mysqli->prepare("
  SELECT COUNT(*)
  FROM chat_messages
  WHERE room_id = ? AND sender_id = ? AND created_at >= (NOW() - INTERVAL 5 SECOND)
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

/* (c) Intervalle minimal (évite les doubles clics rapides) */
$last = (float)($_SESSION['last_msg_at'] ?? 0);
$nowMs = microtime(true);
if (($nowMs - $last) < 0.8) { // 800 ms
  http_response_code(429);
  echo json_encode(['ok'=>false,'error'=>'rate_fast']);
  exit;
}
$_SESSION['last_msg_at'] = $nowMs;

/* ============================================================
 *     3. VALIDATION DU CONTENU ET UPLOAD IMAGE
 * ============================================================ */
$body = trim((string)($_POST['body'] ?? ''));
$body = preg_replace('/[\p{Cc}\p{Cf}&&[^\n\t]]/u', '', $body);
$body = mb_substr($body, 0, 2000);

if ($room_id <= 0) {
  echo json_encode(['ok'=>false,'error'=>'room']);
  exit;
}

/* Préparation variables fichier */
$fileUrl = null;
$fileMime = null;
$w = null;
$h = null;

/* --- Upload image optionnel --- */
if (!empty($_FILES['image']['name'])) {
  if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok'=>false,'error'=>'upload']);
    exit;
  }

  /* Taille max autorisée (constante définie dans config.php) */
  $maxBytes = (int)UPLOAD_MAX_MB * 1024 * 1024;
  if (($_FILES['image']['size'] ?? 0) > $maxBytes) {
    echo json_encode(['ok'=>false,'error'=>'too_big']);
    exit;
  }

  /* Vérification du type MIME */
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($_FILES['image']['tmp_name']) ?: '';
  if (!in_array($mime, UPLOAD_ALLOWED, true)) {
    echo json_encode(['ok'=>false,'error'=>'mime']);
    exit;
  }

  /* Création du dossier d’upload si nécessaire */
  if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0775, true)) {
    echo json_encode(['ok'=>false,'error'=>'dir']);
    exit;
  }

  /* Génération d’un nom de fichier unique */
  $ext = match ($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    default      => 'bin',
  };
  $fname = 'chat_'.date('Ymd_His').'_'.bin2hex(random_bytes(6)).'.'.$ext;
  $dest  = rtrim(UPLOAD_DIR,'/').'/'.$fname;

  /* Déplacement du fichier temporaire vers le dossier cible */
  if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
    echo json_encode(['ok'=>false,'error'=>'move']);
    exit;
  }

  /* Récupération dimensions */
  [$imgW, $imgH] = @getimagesize($dest) ?: [null, null];
  $fileUrl  = rtrim(UPLOAD_URL,'/').'/'.$fname;
  $fileMime = $mime;
  $w = $imgW;
  $h = $imgH;
}

/* Rejeter un message totalement vide (ni texte ni image) */
if ($body === '' && !$fileUrl) {
  echo json_encode(['ok'=>false,'error'=>'empty']);
  exit;
}

/* ============================================================
 *     4. INSERTION DU MESSAGE EN BASE
 * ============================================================ */
$mysqli->begin_transaction();

$sql = "
  INSERT INTO chat_messages
    (room_id, sender_id, body, file_url, file_mime, file_w, file_h)
  VALUES (?, ?, ?, ?, ?, ?, ?)
";
$st = $mysqli->prepare($sql);
$st->bind_param('iisssii', $room_id, $user_id, $body, $fileUrl, $fileMime, $w, $h);
$ok = $st->execute();
$id = $ok ? $st->insert_id : 0;
$st->close();

/* Validation ou rollback de la transaction */
if ($ok) {
  $mysqli->commit();
  echo json_encode(['ok'=>true,'id'=>$id]);
} else {
  $mysqli->rollback();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db']);
}
