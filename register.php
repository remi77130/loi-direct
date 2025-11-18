<?php
// register.php — inscription avec mot de passe (hash) + validations côté serveur
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/config.php'; // APP_BASE, helpers…


/* INFO FICHIER 

Lecture de $pseudo et validations serveur complètes.
Champ confirmation ajouté.
Hash du mot de passe toujours stocké (password_hash).
Gestion d’erreurs prepare/execute avec error_log + message générique.
Redirection sûre via APP_BASE.

CSRF token */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 1) CSRF
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $errors[] = "Session expirée. Recharge la page.";
  } else {
    // 2) Récupération & normalisation
    $pseudo  = mb_substr(trim((string)($_POST['pseudo'] ?? '')), 0, 20);
    $pass1   = (string)($_POST['password'] ?? '');
    $pass2   = (string)($_POST['password_confirm'] ?? '');

   // pseudo déjà trim()
if (!preg_match('/^[\p{L}0-9_.-]{3,30}$/u', $pseudo)) {
    $errors[] = "Le pseudo doit faire 3 à 20 caractères (lettres avec accents, chiffres, . _ -, Pas despace).";
}


    $len = mb_strlen($pass1);
    if ($len < 8 || $len > 128) {
      $errors[] = "Mot de passe : 8 à 128 caractères.";
    }
    if ($pass1 !== $pass2) {
      $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // 4) Pseudo disponible ?
    if (!$errors) {
      $chk = $mysqli->prepare('SELECT 1 FROM users WHERE pseudo=? LIMIT 1');
      if (!$chk) {
        $errors[] = "Erreur serveur.";
        error_log('register check prepare: '.$mysqli->error);
      } else {
        $chk->bind_param('s', $pseudo);
        if (!$chk->execute()) {
          $errors[] = "Erreur serveur.";
          error_log('register check execute: '.$chk->error);
        } else {
          $chk->store_result();
          if ($chk->num_rows > 0) {
            $errors[] = "Ce pseudo est déjà pris.";
          }
        }
        $chk->close();
      }
    }

    // 5) Création du compte
    if (!$errors) {
      $hash = password_hash($pass1, PASSWORD_DEFAULT);

      $ins = $mysqli->prepare('INSERT INTO users (pseudo, password_hash) VALUES (?, ?)');
      if (!$ins) {
        $errors[] = "Erreur serveur.";
        error_log('register insert prepare: '.$mysqli->error);
      } else {
        $ins->bind_param('ss', $pseudo, $hash);
        if ($ins->execute()) {
          // Succès: login direct
          session_regenerate_id(true);
          $_SESSION['user_id'] = $ins->insert_id;
          $_SESSION['pseudo']  = $pseudo;
          $_SESSION['flash_success'] = random_punchline($pseudo);

          header('Location: '.rtrim(APP_BASE,'/').'/chat_rooms.php', true, 303);
          exit;
        } else {
          $errors[] = "Erreur serveur.";
          error_log('register insert execute: '.$ins->error);
        }
        $ins->close();
      }
    }
  }
} 
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Inscription – Tchat Direct</title>

  <!-- Favicon principal -->
  <link rel="icon" href="/favicon.ico" sizes="any">
  <!-- Favicon PNG pour les navigateurs modernes -->
  <link rel="icon" type="image/png" sizes="32x32" href="/uploads/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/uploads/favicon-16x16.png">

  <!-- Icône pour iOS / mobile -->
  <link rel="apple-touch-icon" sizes="180x180" href="/uploads/apple-touch-icon.png">

  <!-- PWA / manifest -->
  <link rel="manifest" href="/uploads/site.webmanifest">

  <!-- SEO -->
  <link rel="canonical" href="https://tchat-direct.com/register.php">
  <meta name="description" content="Inscrivez-vous sur Tchat Direct pour rejoindre des salons de discussion anonymes et discuter en ligne gratuitement.">
  <meta name="robots" content="index,follow">

  <!-- jQuery UNIQUEMENT si tu en as besoin ici -
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>-->

  <!-- Google Analytics 4 -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-FHFM0ESDMP"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-FHFM0ESDMP');
  </script>
</head>










  <style>
    :root { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
    body { background:#0f172a; color:#e5e7eb; display:flex; min-height:100vh; align-items:center; justify-content:center; margin:0; }
    .card { background:#111827;margin:5px; padding:24px; border-radius:16px; width:90%; max-width:420px; box-shadow: 0 10px 30px rgba(0,0,0,.35); }
    h1 { margin:0 0 16px; font-size:24px; }
    label { display:block; font-size:17px;     font-weight: 500; margin-bottom:12px; color:#cbd5e1; }
    input { width:90%; padding:12px 14px; border-radius:10px; border:1px solid #334155; background:#0b1220; color:#e5e7eb; outline:none; }
    .hint { font-size:12px; color:#94a3b8; margin-top:6px; }
    .status { font-size:12px; margin-top:6px; }
    .ok { color:#34d399; } .ko { color:#f87171; }
    .btn { width:100%; margin-top:16px; padding:12px; border:none; border-radius:10px; background:#2563eb; color:white; font-weight:600; cursor:pointer; }
    .btn:disabled { opacity:.6; cursor:not-allowed; }
    .errors { background:#7f1d1d; color:#fecaca; padding:10px; border-radius:8px; margin-bottom:12px; }
    .footer { margin-top:14px; font-size:12px; color:#94a3b8; text-align:center; }
    a { color:#93c5fd; text-decoration: none; }

    .pass-wrap { position:relative; }
.toggle-pass {
  position:absolute;
  right:10px;
  top:50%;
  transform:translateY(-50%);
  background:transparent;
  border:none;
  color:#93c5fd;
  cursor:pointer;
  font-weight:600;
  padding:6px;
  border-radius:6px;
}
.toggle-pass:focus { outline:2px solid rgba(147,197,253,.25); }


.site-header{
  position:absolute;
  top:0;
  left:0;
  width:100%;
  padding:12px 20px;
  display:flex;
  align-items:center;
  justify-content:flex-start;
  background:transparent;
  z-index:5;
}
.logo-img{
  height:150px;
  display:block;
}
body{
  background:#0f172a;
  color:#e5e7eb;
  display:flex;
  flex-direction:column;
  min-height:100vh;
  align-items:center;
  justify-content:center;
  margin:0;
  padding-top:60px; /* pour ne pas que le contenu passe sous le header */
}




  </style>
</head>
<body>



<!-- Bannière version Bêta -->
<div class="banner" id="betaBanner">
  🚧 Ce site est actuellement en version <strong>Bêta</strong>. Certaines fonctionnalités peuvent être instables. Merci de na pas partager.
</div>
</div>

<style>
#betaBanner {
  margin-top: 100px;
  background: linear-gradient(90deg, #1e293b, #0f172a);
  color: #facc15;              /* jaune doré bien visible */
  text-align: center;
  padding: 10px 16px;
  font-size: 14px;
  border-bottom: 1px solid #334155;
  font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
}
#betaBanner strong {
  color: #fbbf24;              /* accent sur le mot "Bêta" */
}
</style>



<header class="site-header">
  <a href="register.php" class="logo-link">
    <img src="uploads/tchat_direct_logo.webp" alt="Tchat Direct logo" class="logo-img">
  </a>

</header>
  <div class="card">
    <h1>Créer un compte</h1>

    <?php if ($errors): ?>
      <div class="errors">
        <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e, ENT_QUOTES)."</div>"; ?>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off" novalidate>
      <label for="pseudo">Pseudo
</label>
      <input type="text" id="pseudo" name="pseudo" minlength="3" maxlength="20"
      pattern="^[\p{L}0-9_.-]{3,20}$" required placeholder="ex: Remi_85, 3–20 caractères, Pas d'espace"
>
       
      <div class="hint">3–20 caractères. Lettres, chiffres et underscore uniquement.</div>
      <div id="status" class="status"></div>
<label for="password">Mot de passe</label>
<div class="pass-wrap">
  <input type="password" id="password" name="password" minlength="8" maxlength="128"
         required placeholder="••••••••" autocomplete="new-password">
  <button type="button" class="toggle-pass" data-target="password" aria-pressed="false" title="Voir le mot de passe">Voir</button>
</div>

<label for="password_confirm">Confirmer le mot de passe</label>
<div class="pass-wrap">
  <input type="password" id="password_confirm" name="password_confirm" minlength="8" maxlength="128"
         required placeholder="••••••••" autocomplete="new-password">
  <button type="button" class="toggle-pass" data-target="password_confirm" aria-pressed="false" 
  title="Voir le mot de passe">Voir</button>
</div>

      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">
      <button id="submitBtn" class="btn" type="submit">S’inscrire</button>
    </form>

    <div class="footer">Déjà inscrit ? <a href="login.php">Connexion</a></div>
  </div>

<script>
// Vérif dispo du pseudo (comme avant)
const $pseudo  = document.getElementById('pseudo');
const $status  = document.getElementById('status');
let t = null;
$pseudo.addEventListener('input', () => {
  const v = $pseudo.value.trim();
  $status.textContent = '';
  if (!/^[\p{L}0-9_.-]{3,30}$/u.test(v)) {
    $status.textContent = 'Format invalide.'; $status.className = 'status ko'; return;
  }
  clearTimeout(t);
  t = setTimeout(async () => {
    try {
      const res  = await fetch('check_pseudo.php?pseudo=' + encodeURIComponent(v), {cache:'no-store'});
      const data = await res.json();
      if (!data.ok) { $status.textContent = 'Format invalide.'; $status.className='status ko'; return; }
      if (data.available) { $status.textContent = '✅ Pseudo disponible'; $status.className='status ok'; }
      else { $status.textContent = '❌ Déjà pris'; $status.className='status ko'; }
    } catch { $status.textContent = 'Erreur de vérification'; $status.className='status ko'; }
  }, 250);
});
</script>


<script>
document.querySelectorAll('.toggle-pass').forEach(btn=>{
  btn.addEventListener('click', ()=> {
    const targetId = btn.getAttribute('data-target');
    const input = document.getElementById(targetId);
    if (!input) return;
    if (input.type === 'password') {
      input.type = 'text';
      btn.textContent = 'Cacher';
      btn.setAttribute('aria-pressed','true');
      // Optionnel: éviter que le champ reste visible trop longtemps
       setTimeout(()=>{ input.type='password'; btn.textContent='Voir'; btn.setAttribute('aria-pressed','false'); }, 10000);
    } else {
      input.type = 'password';
      btn.textContent = 'Voir';
      btn.setAttribute('aria-pressed','false');
    }
  });
});
</script>

</body>
</html>
