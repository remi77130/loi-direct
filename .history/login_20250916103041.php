<?php
// login.php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $pseudo)) {
        $errors[] = "Format pseudo invalide.";
    } else {
        $stmt = $mysqli->prepare('SELECT id, pseudo FROM users WHERE pseudo = ? LIMIT 1');
        $stmt->bind_param('s', $pseudo);
        $stmt->execute();
        $stmt->bind_result($uid, $pp);
        if ($stmt->fetch()) {
            // session persistante 14 jours (cookie PHPSESSID)
            // côté php.ini: session.cookie_lifetime=1209600 ou ci-dessous:
            if (session_status() === PHP_SESSION_ACTIVE) {
                // Regénère l’ID pour éviter fixation
                session_regenerate_id(true);
            }
            $_SESSION['user_id'] = (int)$uid;
            $_SESSION['pseudo']  = $pp;
            $next = $_GET['next'] ?? '/loi/index.php.php';
            header('Location: ' . $next);
            exit;
        } else {
            $errors[] = "Pseudo introuvable.";
        }
        $stmt->close();
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
