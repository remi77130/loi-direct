<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');               // show errors in browser (dev only)
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_error.log'); // write here if PHP log path is bad


// save_project.php validation serveur + insertion + redirection

declare(strict_types=1);
session_start();

require __DIR__ . '/config.php';   // <-- IMPORTANT (slugify/tag_slug)
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();
$HAS_GD = extension_loaded('gd'); // flag, pas de return qui stoppe le script

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
    if (mb_strlen($t) > 10 || !preg_match('/^[\p{L}\p{N}_\- ]+$/u', $t)) {
      $errors[] = "Tag invalide: \"{$t}\" (≤10 car., lettres/chiffres/espace/_/-)";
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

//if (!extension_loaded('gd')) return false;
$HAS_GD = extension_loaded('gd');  // <-- au lieu du return



function make_thumbnail(string $srcPath, string $srcMime, string $destPath, int $maxW, int $maxH, ?int &$outW, ?int &$outH): bool {
  $outW = $outH = null;

  // Charge l’image source
  switch (strtolower($srcMime)) {
    case 'image/jpeg': $im = @imagecreatefromjpeg($srcPath); break;
    case 'image/png':  $im = @imagecreatefrompng($srcPath);  break;
    case 'image/webp': $im = @imagecreatefromwebp($srcPath); break;
    default: return false;
  }
  if (!$im) return false;

  $w = imagesx($im); $h = imagesy($im);
  if ($w <= 0 || $h <= 0) { imagedestroy($im); return false; }

  $scale = min($maxW / $w, $maxH / $h, 1.0);
  $nw = (int)max(1, round($w * $scale));
  $nh = (int)max(1, round($h * $scale));

  $thumb = imagecreatetruecolor($nw, $nh);
  // préserve la transparence pour PNG/WebP
  if (in_array(strtolower($srcMime), ['image/png','image/webp'], true)) {
    imagealphablending($thumb, false);
    imagesavealpha($thumb, true);
    $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
    imagefilledrectangle($thumb, 0, 0, $nw, $nh, $transparent);
  }

  imagecopyresampled($thumb, $im, 0,0, 0,0, $nw,$nh, $w,$h);
  imagedestroy($im);

  // Sortie : WebP si dispo, sinon format d’origine
  $ok = false;
  if (function_exists('imagewebp')) {
    $ok = imagewebp($thumb, $destPath, 80);
  } else {
    switch (strtolower($srcMime)) {
      case 'image/jpeg': $ok = imagejpeg($thumb, $destPath, 82); break;
      case 'image/png':  $ok = imagepng($thumb, $destPath, 6);   break;
      case 'image/webp': $ok = imagewebp($thumb, $destPath, 80); break;
    }
  }
  imagedestroy($thumb);

  if ($ok) { $outW = $nw; $outH = $nh; }
  return $ok;
}



// ---- Upload d’images ----
if ($newId && !empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
    // dossier cible : /uploads/YYYY/MM
    $subdir  = date('Y/m');
    $destDir = UPLOAD_DIR . '/' . $subdir;
    if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }

    $mapExt = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $fi = new finfo(FILEINFO_MIME_TYPE);

    // préparer une seule fois l’INSERT
    $ins = $mysqli->prepare('INSERT INTO project_images
      (project_id, path, original_name, mime, size, width, height, thumb_path, thumb_w, thumb_h)
      VALUES (?,?,?,?,?,?,?,?,?,?)');

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
        [$w, $h] = [$imgInfo[0] ?? 0, $imgInfo[1] ?? 0];

        $ext  = $mapExt[$mime] ?? 'bin';
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $destDir . '/' . $name;

        if (!@move_uploaded_file($tmp, $dest)) continue;

        // valeurs pour la BDD
        $relPath = $subdir . '/' . $name;
        $orig    = basename($_FILES['images']['name'][$i] ?? $name);

     // miniature
$thumbRel = null; $tw = null; $th = null;

if ($HAS_GD) {
  $thumbExt  = function_exists('imagewebp') ? 'webp' : ($ext === 'jpg' ? 'jpg' : $ext);
  $thumbDest = $destDir.'/'.pathinfo($name, PATHINFO_FILENAME).'-thumb.'.$thumbExt;

  if (make_thumbnail($dest, $mime, $thumbDest, 600, 600, $tw, $th)) {
    $thumbRel = $subdir.'/'.basename($thumbDest);
  }
}

// puis l'INSERT (thumb_* peut rester NULL)
$ins->bind_param('isssiiisii', $newId, $relPath, $orig, $mime, $size, $w, $h, $thumbRel, $tw, $th);
$ins->execute();

    }

    $ins->close();
}



if (!$ok || !$newId) {
  $_SESSION['flash_errors'] = ["Échec de publication (BDD)."];
  $_SESSION['draft'] = [
    'title'=>$title, 'summary'=>$summary, 'body'=>$body, 'tags'=>$tagsRaw
  ];
  header('Location: write.php');
  exit;
}




// DIAG: si des headers ont déjà été envoyés (BOM, echo, espace, etc.)
if (headers_sent($f, $l)) {
    error_log("headers already sent in $f:$l");
    // fallback JS si headers HS
    echo '<!doctype html><meta charset="utf-8"><script>location.href="'.APP_BASE.'/index.php"</script>';
    exit;
}

// redirection propre (URL absolue => évite dirname/PHP_SELF surprises)
header('Location: '.APP_BASE.'/index.php', true, 302);
exit;>