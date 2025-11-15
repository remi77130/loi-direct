<?php

// login.php — Connexion sécurisée (validation serveur renforcée)
declare(strict_types=1);


session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/config.php'; // pour APP_BASE

// === Configuration brute-force (ajuste si besoin) ===
const IP_WINDOW_SECONDS = 15 * 60;  // fenêtre 15 min
const IP_MAX_FAILS      = 10;       // seuil échecs
const IP_BAN_SECONDS    = 15 * 60;  // durée ban

// === Helpers (inchangés) ===
function get_client_ip(): string {
  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim($parts[0]);
    if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
  }
  if (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
    return $_SERVER['REMOTE_ADDR'];
  }
  return '0.0.0.0';
}

function ip_is_banned(mysqli $db, string $ip): bool {
  $q = $db->prepare('SELECT 1 FROM ip_bans WHERE ip=? AND `until` > NOW() LIMIT 1');
  if (!$q) { error_log("ip_is_banned prepare failed: ".$db->error); return false; }
  $q->bind_param('s', $ip);
  $q->execute(); $q->store_result();
  $banned = $q->num_rows > 0;
  $q->close();
  return $banned;
}

function ban_ip(mysqli $db, string $ip, int $seconds, string $reason='too_many_failures'): void {
  $q = $db->prepare("
    INSERT INTO ip_bans (ip, `until`, reason)
    VALUES (?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?)
    ON DUPLICATE KEY UPDATE `until` = GREATEST(`until`, VALUES(`until`)), reason = VALUES(reason)
  ");
  if (!$q) { error_log("ban_ip prepare failed: ".$db->error); return; }
  $q->bind_param('sis', $ip, $seconds, $reason);
  $q->execute(); $q->close();
}

function count_recent_fails(mysqli $db, string $ip, int $windowSeconds): int {
  $q = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip=? AND success=0 AND created_at >= (NOW() - INTERVAL ? SECOND)');
  if (!$q) { error_log("count_recent_fails prepare failed: ".$db->error); return 0; }
  $q->bind_param('si', $ip, $windowSeconds);
  $q->execute(); $q->bind_result($c); $c = 0; $q->fetch(); $q->close();
  return (int)$c;
}

function log_attempt(mysqli $db, string $ip, ?string $pseudo, bool $success): void {
  $pseudo = $pseudo !== null ? mb_substr($pseudo, 0, 30) : null;
  $s = $success ? 1 : 0;
  $q = $db->prepare('INSERT INTO login_attempts (ip, pseudo, success) VALUES (?,?,?)');
  if (!$q) { error_log("log_attempt prepare failed: ".$db->error); return; }
  $q->bind_param('ssi', $ip, $pseudo, $s);
  $q->execute(); $q->close();
}

// === CSRF token ===
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

// === Si déjà connecté -> redirection ===
if (!empty($_SESSION['user_id'])) {
  header('Location: '.rtrim(APP_BASE,'/').'/index.php', true, 303);
  exit;
}

// === Pré-vérifs brute-force ===
$errors  = [];
$blocked = false;
$ip      = get_client_ip();

if (ip_is_banned($mysqli, $ip)) {
  $blocked = true;
  $errors[] = "Trop de tentatives. Réessaie plus tard.";
}

if (!$blocked) {
  $recentFails = count_recent_fails($mysqli, $ip, IP_WINDOW_SECONDS);
  if ($recentFails >= IP_MAX_FAILS) {
    ban_ip($mysqli, $ip, IP_BAN_SECONDS);
    $blocked = true;
    $errors[] = "Trop de tentatives. Réessaie plus tard.";
  }
}

// === Soumission ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$blocked) {
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $errors[] = "Session expirée. Recharge la page.";
  } else {
    // --- Validation serveur renforcée ---
    $pseudo_raw   = (string)($_POST['pseudo'] ?? '');
    $password_raw = (string)($_POST['password'] ?? '');

    // Normalisation/troncature pour éviter payloads massifs
    $pseudo = mb_substr(trim($pseudo_raw), 0, 100); // cut early, on validera la forme ensuite
    $password = $password_raw; // ne tronque pas le mot de passe — on vérifie longueur

  // Même règle que register.php : 3-20, lettres (accents ok), chiffres, underscore, pas d'espace
if (!preg_match('/^[\p{L}0-9_.-]{3,20}$/u', $pseudo)) {
    $errors[] = 'Identifiants invalides.';
}

    // Mot de passe : min 8, max raisonnable (ex: 128)
    $pwLen = mb_strlen($password);
    if ($pwLen < 8 || $pwLen > 128) {
      $errors[] = 'Identifiants invalides.'; // message générique
    }

    // Si validation serveur échoue, on évite toute requête SQL inutile
    if (empty($errors)) {
      // lookup user (prepare() sécurisé + check)
      $st = $mysqli->prepare('SELECT id, password_hash FROM users WHERE pseudo=? LIMIT 1');
      if (!$st) {
        // Ne pas divulguer d'infos ; log côté serveur.
        error_log('login prepare failed: '.$mysqli->error);
        $errors[] = 'Erreur serveur. Réessaie plus tard.';
      } else {
        $st->bind_param('s', $pseudo);
        if (!$st->execute()) {
          error_log('login execute failed: '.$st->error);
          $errors[] = 'Erreur serveur. Réessaie plus tard.';
          $st->close();
        } else {
          // get_result peut échouer sans mysqlnd — fallback contrôlé
          $res = null;
          if (method_exists($st, 'get_result')) {
            $res = $st->get_result();
            if ($res === false) {
              error_log('login get_result returned false: '.$st->error);
              $errors[] = 'Erreur serveur. Réessaie plus tard.';
            }
          } else {
            // fallback : bind_result + fetch
          }

          $row = null;
          if (empty($errors)) {
            if ($res !== null) {
              $row = $res->fetch_assoc();
            } else {
              // fallback sans mysqlnd
              $st->bind_result($tmp_id, $tmp_hash);
              if ($st->fetch()) {
                $row = ['id'=>$tmp_id, 'password_hash'=>$tmp_hash];
              }
            }
          }
          $st->close();

          // Vérification du mot de passe — timing-safe via password_verify
          $ok = $row && is_string($row['password_hash']) && password_verify($password, $row['password_hash']);

          // Log AVANT la redirection (toujours)
          log_attempt($mysqli, $ip, $pseudo, $ok);

          if ($ok) {
            // rehash si nécessaire — avec vérif prepare()
            if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
              $new = password_hash($password, PASSWORD_DEFAULT);
              $up  = $mysqli->prepare('UPDATE users SET password_hash=? WHERE id=?');
              if (!$up) {
                error_log('password rehash prepare failed: '.$mysqli->error);
                // on ne bloque pas la connexion pour ça; log seulement
              } else {
                $uid = (int)$row['id'];
                $up->bind_param('si', $new, $uid);
                if (!$up->execute()) {
                  error_log('password rehash execute failed: '.$up->error);
                }
                $up->close();
              }
            }

            // succès : créer session
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$row['id'];
            $_SESSION['pseudo']  = $pseudo;

             // message à afficher sur index.php après chaque connexion
            $_SESSION['flash_success'] = random_punchline($pseudo);

            header('Location: '.rtrim(APP_BASE,'/').'/index.php', true, 303);
            exit;
          }

          // échec : possible ban escalation
          $fails = count_recent_fails($mysqli, $ip, IP_WINDOW_SECONDS);
          if ($fails >= IP_MAX_FAILS) {
            ban_ip($mysqli, $ip, IP_BAN_SECONDS);
          }

          // message générique + delay constant
          $errors[] = 'Pseudo ou mot de passe invalide.';
          usleep(200000);
        } // end execute
      } // end prepare check
    } // end server-side validation passed
  } // end csrf ok
} // end POST handling

// Hygiène légère : purge anciennes tentatives

// --- GC probabiliste: ~1% des requêtes déclenchent le nettoyage ---
const LOGIN_GC_NUM = 1;   // numérateur
const LOGIN_GC_DEN = 100; // dénominateur (1/100 = 1%)

if (random_int(1, LOGIN_GC_DEN) <= LOGIN_GC_NUM) {
    $sql = "DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 30 DAY)";
    if (!$mysqli->query($sql)) {
        error_log('login GC failed: '.$mysqli->error);
    }
}

?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Tchat Direct – Tchat en ligne anonyme et gratuit</title>

<link rel="icon" type="image/png" href="uploads/tchat_direct_favicon.png">


<meta name="description" content="Tchat Direct est une plateforme de tchat en ligne anonyme et gratuit. Rejoignez des salons, créez vos propres rooms et discutez en direct.">
<meta name="keywords" content="tchat direct, tchat en ligne, chat anonyme, salon de discussion">
<link rel="canonical" href="https://tchat-direct.com/login.php">
<meta name="robots" content="index,follow">

<!-- Google Tag Manager -->
<script>
  window.dataLayer = window.dataLayer || [];
</script>
<script async src="https://www.googletagmanager.com/gtm.js?id=GTM-WRBFLTW8"></script>
<!-- End Google Tag Manager -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>



<style>
/* styles identiques à ton UI */
:root{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}


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



.card{background:#111827;padding:24px;margin:5px;border-radius:16px;width:90%;max-width:420px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
h1{margin:0 0 16px;font-size:24px}
label{display:block;font-size:17px;font-weight: 500;margin-bottom:8px;color:#cbd5e1}
input{width:90%;padding:12px 14px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb}
.btn{width:100%;margin-top:16px;padding:12px;border:none;border-radius:10px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer}
.btn[disabled]{opacity:.6;cursor:not-allowed}
.errors{background:#7f1d1d;color:#fecaca;padding:10px;border-radius:8px;margin-bottom:12px}
a{color:#93c5fd;text-decoration:none}
.container_text_login_01 {
margin-top: 25px; width: 90%;
}

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

</style>
</head>
<body>


<!-- Bannière version Bêta -->
<div class="banner" id="betaBanner">
  🚧 Ce site est actuellement en version <strong>Bêta</strong>. Certaines fonctionnalités peuvent être instables. Merci de na pas partager.
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
  <a href="index.php" class="logo-link">
    <img src="uploads/tchat_direct_logo.webp" alt="Tchat Direct logo" class="logo-img">
  </a>
</header>

  
<!-- Google Tag Manager (noscript) -->

<noscript>
  <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WRBFLTW8"
          height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>



<div class="container_text_login_01">
<p class="text_login_01">  Interface simple, accès rapide, salons publics et privés, discussions anonymes :
      Tchat Direct vise l’efficacité sans inscription lourde. Tu te connectes, tu choisis un salon, tu échanges.
    </p>
</div>

<div class="card">
  <h1>Connexion</h1>
  <?php if ($errors): ?>
    <div class="errors"><?php foreach ($errors as $e) echo '<div>'.htmlspecialchars($e,ENT_QUOTES).'</div>'; ?></div>
  <?php endif; ?>

  <form method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'],ENT_QUOTES) ?>">
   <label>Pseudo
  <input type="text" name="pseudo"
         autocomplete="username"
         required
         minlength="3"
         maxlength="30"
         pattern="^[\p{L}0-9_.-]{3,20}$">
</label>

  <label>Mot de passe
  <div class="pass-wrap">
    <input type="password" name="password" id="login_password" required minlength="8" autocomplete="current-password">
    <button type="button" class="toggle-pass" data-target="login_password" aria-pressed="false" title="Voir le mot de passe">Voir</button>
  </div>
</label>

    <button class="btn" type="submit" <?= $blocked ? 'disabled' : '' ?>>Se connecter</button>
  </form>

  <p style="margin-top:12px;font-size:12px;color:#94a3b8">Pas encore inscrit ? <a href="register.php">Créer un compte</a></p>
</div>



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
      // setTimeout(()=>{ input.type='password'; btn.textContent='Voir'; btn.setAttribute('aria-pressed','false'); }, 30000);
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
