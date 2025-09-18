<?php
// save_project.php validation serveur + insertion + redirection

declare(strict_types=1);
session_start();
require __DIR__ . '/config.php';   // <-- IMPORTANT (slugify/tag_slug)
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: write.php');
  exit;
}

// CSRF
$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
  $errors[] = "Session expirée. Recharge la page.";
}

$title   = trim($_POST['title']   ?? '');
$summary = trim($_POST['summary'] ?? '');
$body    = trim($_POST['body']    ?? '');
$tagsRaw = trim($_POST['tags']    ?? '');
$userId  = (int)$_SESSION['user_id'];

// Règles métier
if ($title === '' || mb_strlen($title) > 180) {
  $errors[] = "Titre obligatoire (≤ 180 caractères).";
}
if ($summary === '' || mb_strlen($summary) > 280) {
  $errors[] = "Objet obligatoire (≤ 280 caractères).";
}
if ($body === '') {
  $errors[] = "Le texte est obligatoire.";
}
if (mb_strlen($body) > 30000) {
  $errors[] = "Le texte dépasse la limite (~30 000 caractères).";
}

// Tags: max 5, chacun ≤ 24 (alphanum + tiret/underscore/espaces)
$tagsArr = [];
if ($tagsRaw !== '') {
  $candidates = array_slice(array_filter(array_map('trim', explode(',', $tagsRaw))), 0, 5);
  foreach ($candidates as $t) {
    if (mb_strlen($t) > 24 || !preg_match('/^[\p{L}\p{N}_\- ]+$/u', $t)) {
      $errors[] = "Tag invalide: \"{$t}\" (≤24 car., lettres/chiffres/espace/_/-)";
    } else {
      $tagsArr[] = $t;
    }
  }
}

if ($errors) {
  // On renvoie vers l’éditeur avec messages en session (flash pauvre)
  $_SESSION['flash_errors'] = $errors;
  $_SESSION['draft'] = [
    'title'=>$title, 'summary'=>$summary, 'body'=>$body, 'tags'=>$tagsRaw
  ];
  header('Location: write.php');
  exit;
}

// Insertion
$stmt = $mysqli->prepare('INSERT INTO law_projects (author_id, title, summary, body_markdown, status, published_at) VALUES (?,?,?,?, "published", NOW())');
$stmt->bind_param('isss', $userId, $title, $summary, $body);
$ok = $stmt->execute();
$newId = $stmt->insert_id ?? 0;
$stmt->close();


// ---- Enregistrer les tags (si fournis) ----
if ($tagsArr) {
  $mysqli->begin_transaction();
  try {
    foreach ($tagsArr as $tname) {
      $tslug = tag_slug($tname);

      // Récupère ou crée le tag
      $sel = $mysqli->prepare('SELECT id FROM tags WHERE slug=? LIMIT 1');
      $sel->bind_param('s', $tslug);
      $sel->execute();
      $sel->bind_result($tid);
      if ($sel->fetch()) {
        $sel->close();
      } else {
        $sel->close();
        $insT = $mysqli->prepare('INSERT INTO tags (name, slug) VALUES (?,?)');
        $insT->bind_param('ss', $tname, $tslug);
        $insT->execute();
        $tid = $insT->insert_id;
        $insT->close();
      }

      // Lier au projet (ignore si déjà lié)
      $link = $mysqli->prepare('INSERT IGNORE INTO project_tags (project_id, tag_id) VALUES (?,?)');
      $link->bind_param('ii', $newId, $tid);
      $link->execute();
      $link->close();
    }
    $mysqli->commit();
  } catch (Throwable $e) {
    $mysqli->rollback(); // on n'échoue pas la publication pour un tag
  }
}





// ---- Upload d’images ----
if ($newId && !empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
  // s'assurer que le dossier existe
  $subdir = date('Y/m');
  $destDir = UPLOAD_DIR . '/' . $subdir;
  if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }

  $mapExt = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
  $fi = new finfo(FILEINFO_MIME_TYPE);

  $total = min(count($_FILES['images']['name']), (int)UPLOAD_MAX_FILES);
  for ($i = 0; $i < $total; $i++) {
    if (($_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;

    $tmp  = $_FILES['images']['tmp_name'][$i];
    $size = (int)($_FILES['images']['size'][$i] ?? 0);
    if ($size <= 0 || $size > UPLOAD_MAX_MB * 1024 * 1024) continue;

    $mime = (string)$fi->file($tmp);
    if (!in_array($mime, UPLOAD_ALLOWED, true)) continue;

    $imgInfo = @getimagesize($tmp);
    if (!$imgInfo) continue;
    [$w, $h] = [$imgInfo[0] ?? null, $imgInfo[1] ?? null];

    $ext = $mapExt[$mime] ?? 'bin';
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $destDir . '/' . $name;

    if (!@move_uploaded_file($tmp, $dest)) continue;

    $relPath = $subdir . '/' . $name;
    $orig = substr((string)($_FILES['images']['name'][$i] ?? ''), 0, 180);

    $ins = $mysqli->prepare('INSERT INTO project_images
      (project_id, path, original_name, mime, size, width, height)
      VALUES (?,?,?,?,?,?,?)');
    $ins->bind_param('isssiii', $newId, $relPath, $orig, $mime, $size, $w, $h);
    $ins->execute();
    $ins->close();
  }
}













if (!$ok || !$newId) {
  $_SESSION['flash_errors'] = ["Échec de publication (BDD)."];
  $_SESSION['draft'] = [
    'title'=>$title, 'summary'=>$summary, 'body'=>$body, 'tags'=>$tagsRaw
  ];
  header('Location: write.php');
  exit;
}

// Succès → retour au feed avec message
$_SESSION['flash_success'] = "Projet publié avec succès.";
$base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
header('Location: ' . $base . '/index.php');
exit;
