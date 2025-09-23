<?php
// login.php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$errors = [];
$blocked = false;

// simple rate-limit (session-based)
$fail_count = $_SESSION['login_fail_count'] ?? 0;
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
        $pseudo = trim((string)($_POST['pseudo'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($pseudo === '' || $password === '') {
            $errors[] = 'Identifiants manquants.';
        } else {
            $stmt = $mysqli->prepare('SELECT id, password_hash FROM users WHERE pseudo = ? LIMIT 1');
            $stmt->bind_param('s', $pseudo);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();

            if ($row && is_string($row['password_hash']) && password_verify($password, $row['password_hash'])) {
                // success
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$row['id'];
                $_SESSION['pseudo'] = $pseudo;
                // reset fail counters
                unset($_SESSION['login_fail_count'], $_SESSION['login_fail_until']);
                header('Location: ' . rtrim(APP_BASE, '/') . '/index.php', true, 303);
                exit;
            } else {
                // generic error
                $errors[] = 'Pseudo ou mot de passe invalide.';

                // increment fail counter
                $fail_count++;
                $_SESSION['login_fail_count'] = $fail_count;
                if ($fail_count >= 5) {
                    // block 15 minutes
                    $_SESSION['login_fail_until'] = time() + 15*60;
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
<style>
  :root{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}
  body{background:#0f172a;color:#e5e7eb;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0}
  .card{background:#111827;padding:24px;border-radius:16px;width:100%;max-width:420px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  h1{margin:0 0 16px;font-size:24px}
  label{display:block;font-size:14px;margin-bottom:8px;color:#cbd5e1}
  input{width:100%;padding:12px 14px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb}
  .btn{width:100%;margin-top:16px;padding:12px;border:none;border-radius:10px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer}
  .errors{background:#7f1d1d;color:#fecaca;padding:10px;border-radius:8px;margin-bottom:12px}
  a{color:#93c5fd;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <h1>Connexion</h1>
  <?php if ($errors): ?><div class="errors"><?php foreach ($errors as $e) echo '<div>'.htmlspecialchars($e,ENT_QUOTES).'</div>';?></div><?php endif; ?>
  <form method="post">
    <label for="pseudo">Pseudo</label>
    <input id="pseudo" name="pseudo" required minlength="3" maxlength="20" pattern="[A-Za-z0-9_]{3,20}" placeholder="ex: Remi_81">
    <button class="btn" type="submit">Se connecter</button>
  </form>
  <p style="margin-top:12px;font-size:12px;color:#94a3b8">Pas encore inscrit ? <a href="register.php">Créer un compte</a></p>
</div>
</body>
</html>
