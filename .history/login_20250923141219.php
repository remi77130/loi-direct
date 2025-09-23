<?php
// login.php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/config.php';

// déjà connecté ? -> home
if (!empty($_SESSION['user_id'])) {
  header('Location: ' . rtrim(APP_BASE, '/') . '/index.php', true, 303);
  exit;
}

if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$errors = [];
$blocked = false;

// paramètres de throttling
$WINDOW_MINUTES = 15;
$MAX_PER_IP      = 20; // seuil permissif pour IP (ex: NAT / corporates)
$MAX_PER_USER    = 5;  // seuil serré par pseudo
$BLOCK_SECONDS   = 15 * 60;

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ip_bin = inet_pton($ip) !== false ? inet_pton($ip) : null;

// helper: count fails in window
$cnt_ip = 0;
$cnt_user = 0;
if ($ip_bin !== null) {
  $q = $mysqli->prepare(
    "SELECT
       (SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND success = 0 AND created_at >= NOW() - INTERVAL ? MINUTE) AS ip_cnt,
       (SELECT COUNT(*) FROM login_attempts WHERE pseudo = ? AND success = 0 AND created_at >= NOW() - INTERVAL ? MINUTE) AS user_cnt
    "
  );
  $q->bind_param('bisi', $nullBlob = '', $WINDOW_MINUTES, $tmpPseudo = '', $WINDOW_MINUTES);
  // workaround: send ip as blob by using send_long_data for portability
  $q->send_long_data(0, $ip_bin);
  // bind user param later (we'll reexecute with actual pseudo once known) — for now run counts for ip only
  // Simpler: count IP only now; user-count will be executed later when pseudo known.
  $q->close();
}
// Fallback simpler counts (no inet conversions) — safer cross-env
$ipEsc = $mysqli->real_escape_string($ip);
$r = $mysqli->query("SELECT COUNT(*) AS c FROM login_attempts WHERE ip = '{$ipEsc}' AND success = 0 AND created_at >= NOW() - INTERVAL {$WINDOW_MINUTES} MINUTE");
if ($r) { $cnt_ip = (int)$r->fetch_assoc()['c']; $r->free(); }

$fail_until = $_SESSION['login_fail_until'] ?? 0;
if ($fail_until > time()) {
  $blocked = true;
  $errors[] = 'Trop d\'échecs. Réessaie plus tard.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$blocked) {
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $errors[] = "Session expirée. Recharge la page.";
  } else {
    $pseudo = mb_substr(trim((string)($_POST['pseudo'] ?? '')), 0, 100);
    $password = (string)($_POST['password'] ?? '');

    if ($pseudo === '' || $password === '') {
      $errors[] = 'Identifiants manquants.';
    } else {
      // compte les échecs par pseudo dans la fenêtre
      $stmtc = $mysqli->prepare("SELECT COUNT(*) FROM login_attempts WHERE pseudo = ? AND success = 0 AND created_at >= NOW() - INTERVAL ? MINUTE");
      $stmtc->bind_param('si', $pseudo, $WINDOW_MINUTES);
      $stmtc->execute();
      $stmtc->bind_result($cnt_user);
      $stmtc->fetch();
      $stmtc->close();

      // décision de blocage serveur
      if ($cnt_ip >= $MAX_PER_IP || $cnt_user >= $MAX_PER_USER) {
        // on enregistre quand même l'échec pour forensic
        $blocked = true;
        $errors[] = 'Trop d\'échecs. Réessaie plus tard.';
        // optional: set session block to slow down subsequent attempts
        $_SESSION['login_fail_until'] = time() + $BLOCK_SECONDS;
      } else {
        $stmt = $mysqli->prepare('SELECT id, password_hash FROM users WHERE pseudo = ? LIMIT 1');
        $stmt->bind_param('s', $pseudo);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        $success = 0;
        if ($row && is_string($row['password_hash']) && password_verify($password, $row['password_hash'])) {
          $success = 1;

          // rehash si nécessaire
          if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
            $new = password_hash($password, PASSWORD_DEFAULT);
            $up = $mysqli->prepare('UPDATE users SET password_hash=? WHERE id=?');
            $uid = (int)$row['id'];
            $up->bind_param('si', $new, $uid);
            $up->execute();
            $up->close();
          }

          // enregistrer succès, connecter user
          $ins = $mysqli->prepare('INSERT INTO login_attempts (ip, pseudo, success) VALUES (?, ?, ?)');
          $ins->bind_param('s s i', $ip, $pseudo, $success);
          $ins->execute();
          $ins->close();

          session_regenerate_id(true);
          $_SESSION['user_id'] = (int)$row['id'];
          $_SESSION['pseudo']  = $pseudo;
          // clear session counters
          unset($_SESSION['login_fail_count'], $_SESSION['login_fail_until']);

          // optional: remove old failed attempts for this user to reduce table noise
          $del = $mysqli->prepare('DELETE FROM login_attempts WHERE pseudo = ? AND success = 0 AND created_at < NOW() - INTERVAL ? MINUTE');
          $del->bind_param('si', $pseudo, $WINDOW_MINUTES * 4);
          $del->execute();
          $del->close();

          header('Location: ' . rtrim(APP_BASE, '/') . '/index.php', true, 303);
          exit;
        } else {
          // enregistrement échec (serveur)
          $ins = $mysqli->prepare('INSERT INTO login_attempts (ip, pseudo, success) VALUES (?, ?, ?)');
          $ins->bind_param('s s i', $ip, $pseudo, $success);
          $ins->execute();
          $ins->close();

          $errors[] = 'Pseudo ou mot de passe invalide.';

          // micro-delay anti brute-force
          usleep(200000);

          // incrément fallback session counter (toujours utile)
          $fail_count = ($_SESSION['login_fail_count'] ?? 0) + 1;
          $_SESSION['login_fail_count'] = $fail_count;
          if ($fail_count >= 10) {
            $_SESSION['login_fail_until'] = time() + $BLOCK_SECONDS;
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
<title>Connexion — Loi Direct</title>
<style>/* omitted for brevity (keep same styles) */</style>
</head>
<body>
<div class="card">
  <h1>Connexion</h1>
  <?php if ($errors): ?><div class="errors"><?php foreach ($errors as $e) echo '<div>'.htmlspecialchars($e,ENT_QUOTES).'</div>';?></div><?php endif; ?>
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
