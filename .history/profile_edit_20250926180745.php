<?php
// profile_edit.php — édition profil (sexe + taille)
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/config.php';
require __DIR__.'/auth.php';
require_login();

// CSRF
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

// Charger l’utilisateur connecté
$uid = (int)$_SESSION['user_id'];
$stmt = $mysqli->prepare('SELECT id, pseudo, sex, height_cm FROM users WHERE id=? LIMIT 1');
if (!$stmt) { http_response_code(500); exit('Erreur serveur'); }
$stmt->bind_param('i',$uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) { http_response_code(404); exit('Utilisateur introuvable'); }

$errors = [];
$sex = $user['sex'] ?? null;
$height = $user['height_cm'] ?? null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $csrf = $_POST['csrf'] ?? '';
  if (empty($csrf) || !hash_equals($_SESSION['csrf'],$csrf)) {
    $errors[] = 'Session expirée. Recharge la page.';
  } else {
    // Normalisation + validations
    $sex_in = $_POST['sex'] ?? '';
    $height_in = $_POST['height_cm'] ?? '';

    $sex_new = null;
    if ($sex_in !== '') {
      $sex_in = strtolower(trim($sex_in));
      if ($sex_in==='homme' || $sex_in==='femme') $sex_new = $sex_in;
      else $errors[] = 'Sexe invalide.';
    }

  if ($postal!=='' && !preg_match('/^\d{5}$/',$postal)) $errors[]='postal';

  // si postal + city fournis, on vérifie que la ville existe pour ce CP
  if ($postal!=='' && $city!=='') {
    $v = $mysqli->prepare("SELECT 1 FROM cities WHERE postal_code=? AND city=? LIMIT 1");
    $v->bind_param('ss',$postal,$city);
    $v->execute(); $v->store_result();
    if ($v->num_rows===0) $errors[]='city';
    $v->close();
  }


    $height_new = null;
    if ($height_in !== '') {
      if (ctype_digit($height_in)) {
        $h = (int)$height_in;
        // bornes raisonnables
        if ($h>=100 && $h<=250) $height_new = $h;
        else $errors[] = 'Taille hors bornes (100–250 cm).';
      } else {
        $errors[] = 'Taille invalide.';
      }
    }

    if (!$errors){
    $st=$mysqli->prepare("UPDATE users SET sex=?, height_cm=?, postal_code=?, city=? WHERE id=?");
    $uid=(int)$_SESSION['user_id'];
    $height = $height?:null; // NULL si 0
    $st->bind_param('sissi',$sex,$height,$postal,$city,$uid);
    if($st->execute()){
      $_SESSION['flash_success']='Profil mis à jour.';
      header('Location: '.rtrim(APP_BASE,'/').'/index.php', true, 303); exit;
    }
        }
      }
    }
  }
}
?>
<!doctype html>
<html lang="fr">
<head>
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

    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'],ENT_QUOTES) ?>">

      <label for="sex">Sexe</label>
      <select id="sex" name="sex">
        <option value="" <?= $sex===null?'selected':'' ?>>— Non renseigné —</option>
        <option value="homme" <?= $sex==='homme'?'selected':'' ?>>Homme</option>
        <option value="femme" <?= $sex==='femme'?'selected':'' ?>>Femme</option>
      </select>

      <div class="row">
        <div>
          <label for="height_cm">Taille (cm)</label>
          <input id="height_cm" name="height_cm" type="number" min="100" max="250"
                 inputmode="numeric" value="<?= $height!==null ? (int)$height : '' ?>"
                 placeholder="ex: 175">
        </div>
      </div>

      <button class="btn" type="submit">Enregistrer</button>
      <div style="margin-top:10px;text-align:center">
        <a href="<?= APP_BASE ?>/profile.php?id=<?= (int)$user['id'] ?>">&larr; Retour au profil</a>
      </div>
    </form>
  </div>
</body>
</html>
