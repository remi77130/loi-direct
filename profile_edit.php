<?php
// profile_edit.php — édition profil (sexe + taille + CP/ville)
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/config.php';
require __DIR__.'/auth.php';
require_login();

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

$uid = (int)$_SESSION['user_id'];

// Charger l’utilisateur connecté (avec champs à éditer)
$stmt = $mysqli->prepare('SELECT id, pseudo, sex, height_cm, relationship_status FROM users WHERE id=? LIMIT 1');
if (!$stmt) { http_response_code(500); exit('Erreur serveur'); }
$stmt->bind_param('i',$uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) { http_response_code(404); exit('Utilisateur introuvable'); }

$errors = [];

$sex    = $user['sex'] ?? null;
$height = $user['height_cm'] ?? null;
$status = $user['relationship_status'] ?? null;   // 'single' | 'couple' | null



if ($_SERVER['REQUEST_METHOD']==='POST') {
  $csrf = $_POST['csrf'] ?? '';
  if (empty($csrf) || !hash_equals($_SESSION['csrf'], $csrf)) {
    $errors[] = 'Session expirée. Recharge la page.';
  } else {
$sex_in    = trim((string)($_POST['sex'] ?? ''));
$height_in = trim((string)($_POST['height_cm'] ?? ''));
$status_in = trim((string)($_POST['relationship_status'] ?? '')); // <-- ajout

// Situation
$status_new = null;
if ($status_in !== '') {
  $v = strtolower($status_in);
  if ($v==='single' || $v==='couple') $status_new = $v;
  else $errors[] = 'Situation invalide.';
}

    // Sexe
    $sex_new = null;
    if ($sex_in !== '') {
      $v = strtolower($sex_in);
      if ($v==='homme' || $v==='femme') $sex_new = $v;
      else $errors[] = 'Sexe invalide.';
    }

  
    // Taille
    $height_new = null;
    if ($height_in !== '') {
      if (ctype_digit($height_in)) {
        $h = (int)$height_in;
        if ($h>=100 && $h<=250) $height_new = $h;
        else $errors[] = 'Taille hors bornes (100–250 cm).';
      } else {
        $errors[] = 'Taille invalide.';
      }
    }


$avatarUrl = $user['avatar_url'] ?? null;

// Gestion upload avatar
if (!empty($_FILES['avatar']['name'])) {
    $file = $_FILES['avatar'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        // Règles
        $maxBytes = 2 * 1024 * 1024; // 2 Mo
        if ($file['size'] > $maxBytes) {
            $errors[] = "L'image de profil dépasse 2 Mo.";
        } else {
            $mime = mime_content_type($file['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

            if (!isset($allowed[$mime])) {
                $errors[] = "Format d'image non autorisé. Utilise JPG, PNG ou WEBP.";
            } else {
                $ext = $allowed[$mime];

                // Nom de fichier unique par user
                $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
                $targetDir = __DIR__ . '/uploads/avatars/';
                $targetPath = $targetDir . $filename;

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // Option: supprimer l'ancien avatar si présent et dans /uploads/avatars/
                    if (!empty($avatarUrl)) {
                        $old = __DIR__ . '/' . ltrim($avatarUrl, '/');
                        if (is_file($old)) {
                            @unlink($old);
                        }
                    }
                    // URL publique stockée en BDD
                    $avatarUrl = 'uploads/avatars/' . $filename;
                } else {
                    $errors[] = "Erreur lors de l'upload de l'image de profil.";
                }
            }
        }
    } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Erreur lors de l'envoi de l'image de profil.";
    }
}









    if (!$errors){
      $st = $mysqli->prepare("UPDATE users SET sex=?, height_cm=?, relationship_status=?, avatar_url = ? WHERE id=?");
      if (!$st) { $errors[]='Erreur serveur.'; }
      else {
        $height_param = $height_new ?? null; // NULL si non saisi
        $st->bind_param(
          'sissi', 
          $sex_new, 
          $height_param, 
          $status_new,
          $avatarUrl,
          $uid
        );
        if ($st->execute()){
          $_SESSION['flash_success'] = 'Profil mis à jour.';
          header('Location: '.rtrim(APP_BASE,'/').'/index.php', true, 303);
          exit;
        }
        $st->close();
        $errors[]='Erreur base de données.';
      }
    }

    // Réinjecter
    $sex    = $sex_new;
    $height = $height_new;
    $status = $status_new;
  }
}

?>
<!doctype html>
<html lang="fr">
<head>
  <meta name="robots" content="noindex, nofollow">

<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Éditer mon profil — Loi Direct</title>
<style>
  body{background:#0f172a;color:#e5e7eb;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}
  .card{background:#111827;border:1px solid #334155;border-radius:14px;padding:20px;width:100%;max-width:420px}
  label{display:block;margin:10px 0 6px;color:#cbd5e1}
  select,input{width:100%;padding:10px;border:1px solid #334155;border-radius:10px;background:#0b1220;color:#e5e7eb}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .btn{width:100%;margin-top:14px;padding:12px;border:none;border-radius:10px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer}
  .errors{background:#7f1d1d;color:#fecaca;padding:10px;border-radius:8px;margin-bottom:12px}
  a{color:#93c5fd;text-decoration:none}
</style>
</head>
<body>
  <div class="card">
    <h2 style="margin-top:0">Éditer mon profil</h2>
    <div style="color:#94a3b8;margin-bottom:8px">Compte : <strong><?= htmlspecialchars($user['pseudo'],ENT_QUOTES) ?></strong></div>

    <?php if ($errors): ?>
      <div class="errors"><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e,ENT_QUOTES).'</div>'; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'],ENT_QUOTES) ?>">

      <label for="sex">Sexe</label>
      <select id="sex" name="sex">
        <option value="" <?= $sex===null?'selected':'' ?>>— Non renseigné —</option>
        <option value="homme" <?= $sex==='homme'?'selected':'' ?>>Homme</option>
        <option value="femme" <?= $sex==='femme'?'selected':'' ?>>Femme</option>
      </select>


      <label for="relationship_status">Situation</label>
<select id="relationship_status" name="relationship_status">
  <option value="" <?= $status===null?'selected':'' ?>>— Non renseigné —</option>
  <option value="single" <?= $status==='single'?'selected':'' ?>>Célibataire</option>
  <option value="couple" <?= $status==='couple'?'selected':'' ?>>En couple</option>
</select>


      <div class="row">
        <div>
          <label for="height_cm">Taille (cm)</label>
          <input id="height_cm" name="height_cm" type="number" min="100" max="250" inputmode="numeric"
                 value="<?= $height!==null ? (int)$height : '' ?>" placeholder="ex: 175">
        </div>
      </div>


          <div class="field">
        <label for="avatar">Photo de profil (max 2 Mo)</label>
        <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp">
    </div>



      <button class="btn" type="submit">Enregistrer</button>
      <div style="margin-top:10px;text-align:center">
        <a href="<?= APP_BASE ?>/profile.php?id=<?= (int)$user['id'] ?>">&larr; Retour au profil</a>
      </div>
    </form>
  </div>


</body>
</html>
