<?php
// login.php — Connexion sécurisée + rate-limit & ban IP + PREPARE safe
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/config.php'; // APP_BASE


/*Le code est solide et couvre bien :
Vérifs systématiques après prepare / bind / execute / get_result (avec error_log et message générique).
Fallback si get_result() n’est pas dispo (pas de mysqlnd).
Rate-limit + ban IP (fenêtre, seuil, durée), journalisation des tentatives.
Rehash automatique si l’algorithme/cost évolue.
CSRF + redirection via APP_BASE.
Si reverse proxy : n’activer X-Forwarded-For que s’il est trusted.*/

// Comme tu es en local sans proxy, il vaut mieux ignorer HTTP_X_FORWARDED_FOR 
// (supprimer la branche ou la garder derrière un flag de config). 
// Sinon n’importe quel outil HTTP pourrait forger l’en-tête et contourner 
// ton anti-bruteforce quand tu passeras en production.
// Simplifier get_client_ip() pour ne renvoyer que REMOTE_ADDR tant que le site reste local ; 
// si un reverse proxy est ajouté plus tard, prévois une configuration explicite pour l’activer.

/* -------------------- Brute-force config -------------------- */
const IP_WINDOW_SECONDS = 15 * 60;
const IP_MAX_FAILS      = 10;
const IP_BAN_SECONDS    = 15 * 60;


/* -------------------- Minimal DB error helpers -------------------- */
function db_fail(string $where, mysqli $db, ?mysqli_stmt $st = null): void {
  $msg = $st ? ($st->error ?: $db->error) : $db->error;
  error_log("[login.php] DB error @ {$where}: " . ($msg ?: 'unknown'));
}
function db_prepare(mysqli $db, string $sql): ?mysqli_stmt {
  $st = $db->prepare($sql);
  if (!$st) db_fail('prepare', $db);
  return $st ?: null;
}

/* -------------------- IP helper -------------------- */
function get_client_ip(): string {
  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
  }
  if (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
    return $_SERVER['REMOTE_ADDR'];
  }
  return '0.0.0.0';
}

/* -------------------- BF helpers (with error checks) -------------------- */
function ip_is_banned(mysqli $db, string $ip): bool {
  $st = db_prepare($db, 'SELECT 1 FROM ip_bans WHERE ip=? AND `until`>NOW() LIMIT 1');
  if (!$st) return false; // fail-safe : ne bloque pas à cause d’une panne SQL
  if (!$st->bind_param('s', $ip) || !$st->execute()) { db_fail('ip_is_banned', $db, $st); $st->close(); return false; }
  $st->store_result();
  $banned = $st->num_rows > 0;
  $st->close();
  return $banned;
}
function ban_ip(mysqli $db, string $ip, int $seconds, string $reason='too_many_failures'): void {
  $st = db_prepare($db, "INSERT INTO ip_bans (ip,`until`,reason)
                         VALUES (?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?)
                         ON DUPLICATE KEY UPDATE `until`=GREATEST(`until`,VALUES(`until`)), reason=VALUES(reason)");
  if (!$st) return;
  if (!$st->bind_param('sis', $ip, $seconds, $reason) || !$st->execute()) db_fail('ban_ip', $db, $st);
  $st->close();
}
function count_recent_fails(mysqli $db, string $ip, int $win): int {
  $st = db_prepare($db, 'SELECT COUNT(*) FROM login_attempts WHERE ip=? AND success=0 AND created_at >= (NOW() - INTERVAL ? SECOND)');
  if (!$st) return 0;
  if (!$st->bind_param('si', $ip, $win) || !$st->execute()) { db_fail('count_recent_fails', $db, $st); $st->close(); return 0; }
  $st->bind_result($c); $c = 0; $st->fetch(); $st->close();
  return (int)$c;
}
function log_attempt(mysqli $db, string $ip, ?string $pseudo, bool $success): void {
  $pseudo = $pseudo !== null ? mb_substr($pseudo, 0, 30) : null;
  $s = $success ? 1 : 0;
  $st = db_prepare($db, 'INSERT INTO login_attempts (ip, pseudo, success) VALUES (?,?,?)');
  if (!$st) return;
  if (!$st->bind_param('ssi', $ip, $pseudo, $s) || !$st->execute()) db_fail('log_attempt', $db, $st);
  $st->close();
}

/* -------------------- CSRF -------------------- */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

/* -------------------- Déjà connecté ? -------------------- */
if (!empty($_SESSION['user_id'])) {
  header('Location: '.rtrim(APP_BASE,'/').'/index.php', true, 303);
  exit;
}

/* -------------------- Pré-vérifs BF -------------------- */
$errors = [];
$blocked = false;
$ip = get_client_ip();

if (ip_is_banned($mysqli, $ip)) {
  $blocked = true;
  $errors[] = "Trop de tentatives. Réessaie plus tard.";
}
if (!$blocked) {
  $recent = count_recent_fails($mysqli, $ip, IP_WINDOW_SECONDS);
  if ($recent >= IP_MAX_FAILS) {
    ban_ip($mysqli, $ip, IP_BAN_SECONDS);
    $blocked = true;
    $errors[] = "Trop de tentatives. Réessaie plus tard.";
  }
}

/* -------------------- Soumission -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$blocked) {
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $errors[] = "Session expirée. Recharge la page.";
  } else {
    $pseudo   = mb_substr(trim((string)($_POST['pseudo'] ?? '')), 0, 20);
    $password = mb_substr(trim((string)($_POST['password'] ?? '')),0 20);
    if ($pseudo === '' || $password === '') {
      $errors[] = 'Identifiants manquants.';
    } else {
      // SELECT user (prépare sécurisé)
      $st = db_prepare($mysqli, 'SELECT id, password_hash FROM users WHERE pseudo=? LIMIT 1');
      if (!$st) {
        $errors[] = 'Service momentanément indisponible.';
      } else {
        if (!$st->bind_param('s', $pseudo) || !$st->execute()) {
          db_fail('user_lookup', $mysqli, $st);
          $errors[] = 'Service momentanément indisponible.';
          $st->close();
        } else {
          // get_result peut être indispo sans mysqlnd → fallback bind_result
          $row = null;
          if (method_exists($st, 'get_result')) {
            $res = $st->get_result();
            if ($res === false) { db_fail('get_result', $mysqli, $st); $errors[] = 'Service momentanément indisponible.'; }
            else { $row = $res->fetch_assoc() ?: null; }
          } else {
            $st->bind_result($id, $hash);
            if ($st->fetch()) $row = ['id'=>$id, 'password_hash'=>$hash];
          }
          $st->close();

          $ok = $row && is_string($row['password_hash']) && password_verify($password, $row['password_hash']);

          // journalise toujours
          log_attempt($mysqli, $ip, $pseudo, $ok);

          if ($ok) {
            // rehash sécurisé (avec prepare checks)
            if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
              $new = password_hash($password, PASSWORD_DEFAULT);
              $up  = db_prepare($mysqli, 'UPDATE users SET password_hash=? WHERE id=?');
              if ($up) {
                $uid = (int)$row['id'];
                if (!$up->bind_param('si', $new, $uid) || !$up->execute()) db_fail('rehash_update', $mysqli, $up);
                $up->close();
              } else {
                // on ne casse pas la connexion si le rehash échoue : on log juste
                // (l’utilisateur pourra se reconnecter; le rehash sera retenté plus tard)
              }
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$row['id'];
            $_SESSION['pseudo']  = $pseudo;

            header('Location: '.rtrim(APP_BASE,'/').'/index.php', true, 303);
            exit;
          }

          // échec d’auth : escalade possible + message générique
          $fails = count_recent_fails($mysqli, $ip, IP_WINDOW_SECONDS);
          if ($fails >= IP_MAX_FAILS) ban_ip($mysqli, $ip, IP_BAN_SECONDS);
          $errors[] = 'Pseudo ou mot de passe invalide.';
          usleep(200000);
        }
      }
    }
  }
}

/* -------------------- Hygiène (optionnel) -------------------- */
$mysqli->query("DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 30 DAY)");
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion — Loi Direct</title>
<style>
  :root{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}
  body{background:#0f172a;color:#e5e7eb;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0}
  .card{background:#111827;padding:24px;border-radius:16px;width:100%;max-width:420px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  h1{margin:0 0 16px;font-size:24px}
  label{display:block;font-size:14px;margin-bottom:8px;color:#cbd5e1}
  input{width:100%;padding:12px 14px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb}
  .btn{width:100%;margin-top:16px;padding:12px;border:none;border-radius:10px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer}
  .btn[disabled]{opacity:.6;cursor:not-allowed}
  .errors{background:#7f1d1d;color:#fecaca;padding:10px;border-radius:8px;margin-bottom:12px}
  a{color:#93c5fd;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <h1>Connexion</h1>
  <?php if ($errors): ?>
    <div class="errors"><?php foreach ($errors as $e) echo '<div>'.htmlspecialchars($e,ENT_QUOTES).'</div>'; ?></div>
  <?php endif; ?>
  <form method="post" autocomplete="off" novalidate>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'],ENT_QUOTES) ?>">
    <label>Pseudo
      <input type="text" name="pseudo" required minlength="3" maxlength="20" pattern="[A-Za-z0-9_]{3,20}">
    </label>
    <label>Mot de passe
      <input type="password" name="password" required minlength="8" autocomplete="current-password">
    </label>
    <button class="btn" type="submit" <?= $blocked ? 'disabled' : '' ?>>Se connecter</button>
  </form>
  <p style="margin-top:12px;font-size:12px;color:#94a3b8">Pas encore inscrit ? <a href="register.php">Créer un compte</a></p>
</div>
</body>
</html>
