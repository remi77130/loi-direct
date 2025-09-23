<?php
// login.php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';    // $mysqli : instance MySQLi connecté
require __DIR__ . '/config.php';// APP_BASE, autres helpers

/**
 * ------------------------------------------------------------------------
 * Protection/bail-out rapide : si déjà connecté, rediriger vers le feed.
 * ------------------------------------------------------------------------
 */
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . rtrim(APP_BASE, '/') . '/index.php', true, 303);
    exit;
}

/**
 * ------------------------------------------------------------------------
 * CSRF token minimal (stocké en session)
 * ------------------------------------------------------------------------
 */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

/**
 * ------------------------------------------------------------------------
 * Configuration throttling / rate limiting
 * - WINDOW_MINUTES : fenêtre temporelle pour compter les échecs
 * - MAX_PER_IP : seuil par adresse IP (plus permissif pour NAT)
 * - MAX_PER_USER : seuil serré par pseudo
 * - BLOCK_SECONDS : durée du blocage session fallback
 * ------------------------------------------------------------------------
 */
$WINDOW_MINUTES = 15;
$MAX_PER_IP     = 20;
$MAX_PER_USER   = 5;
$BLOCK_SECONDS  = 15 * 60;

$errors = [];
$blocked = false;

/**
 * ------------------------------------------------------------------------
 * Simple session-based fallback block (utile si IP counting impossible)
 * - Ce mécanisme est conservatoire et n'est **pas** la seule protection.
 * ------------------------------------------------------------------------
 */
$fail_until = $_SESSION['login_fail_until'] ?? 0;
if ($fail_until > time()) {
    $blocked = true;
    $errors[] = "Trop d'échecs. Réessaie plus tard.";
}

/**
 * ------------------------------------------------------------------------
 * Helper: récupère l'IP du client
 * - $_SERVER['REMOTE_ADDR'] est utilisé (attention aux proxys / X-Forwarded-For)
 * - Si l'app tourne derrière un proxy, utiliser la valeur contrôlée (trusted)
 * ------------------------------------------------------------------------
 */
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

/**
 * ------------------------------------------------------------------------
 * Avant traitement POST : on peut compter les échecs récents par IP.
 * - Compte le nombre d'échecs (success = 0) dans la fenêtre pour cette IP.
 * - On utilisera aussi un compte par pseudo après réception du POST.
 * ------------------------------------------------------------------------
 */
$cnt_ip = 0;
$stmt = $mysqli->prepare(
    "SELECT COUNT(*) AS c
     FROM login_attempts
     WHERE ip = ? AND success = 0 AND created_at >= NOW() - INTERVAL ? MINUTE"
);
if ($stmt) {
    $stmt->bind_param('si', $ip, $WINDOW_MINUTES);
    $stmt->execute();
    $stmt->bind_result($cnt_ip);
    $stmt->fetch();
    $stmt->close();
} else {
    // Si la table manque ou autre erreur, ne pas casser l'accès : on continue avec cnt_ip = 0.
    $cnt_ip = 0;
}

/**
 * ------------------------------------------------------------------------
 * Traitement du formulaire
 * - Vérification du CSRF
 * - Vérification des champs
 * - Compte d'échecs par pseudo (requête préparée)
 * - Lecture de l'utilisateur et vérification du mot de passe haché
 * - Enregistrement de la tentative (login_attempts)
 * - Actions en cas de succès : rehash si nécessaire, connexion, purge de vieux échecs
 * ------------------------------------------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$blocked) {
    $csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
        $errors[] = "Session expirée. Recharge la page.";
    } else {
        // Normalize / trim inputs
        $pseudo = mb_substr(trim((string)($_POST['pseudo'] ?? '')), 0, 100);
        $password = (string)($_POST['password'] ?? '');

        if ($pseudo === '' || $password === '') {
            $errors[] = 'Identifiants manquants.';
        } else {
            // Compter les échecs récents pour ce pseudo (dans la même fenêtre)
            $cnt_user = 0;
            $stmt = $mysqli->prepare(
                "SELECT COUNT(*) FROM login_attempts
                 WHERE pseudo = ? AND success = 0 AND created_at >= NOW() - INTERVAL ? MINUTE"
            );
            if ($stmt) {
                $stmt->bind_param('si', $pseudo, $WINDOW_MINUTES);
                $stmt->execute();
                $stmt->bind_result($cnt_user);
                $stmt->fetch();
                $stmt->close();
            }

            // Décision de blocage selon compteurs IP / user
            if ($cnt_ip >= $MAX_PER_IP || $cnt_user >= $MAX_PER_USER) {
                // Enregistrer l'état de blocage dans la session comme filet de sécurité
                $blocked = true;
                $_SESSION['login_fail_until'] = time() + $BLOCK_SECONDS;
                $errors[] = "Trop d'échecs. Réessaie plus tard.";
            } else {
                // Charger l'utilisateur (pseudo unique attendu)
                $stmt = $mysqli->prepare('SELECT id, password_hash FROM users WHERE pseudo = ? LIMIT 1');
                if (!$stmt) {
                    // en cas d'erreur BDD, renvoyer erreur générique
                    $errors[] = 'Erreur serveur.';
                } else {
                    $stmt->bind_param('s', $pseudo);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $row = $res->fetch_assoc();
                    $stmt->close();

                    $success = 0; // par défaut : échec

                    // Vérification du hash si l'utilisateur existe
                    if ($row && is_string($row['password_hash']) && password_verify($password, $row['password_hash'])) {
                        $success = 1;

                        // Rehash si nécessaire (upgrade d'algorithme / cost)
                        if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            if ($newHash !== false) {
                                $up = $mysqli->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                                if ($up) {
                                    $uid = (int)$row['id'];
                                    $up->bind_param('si', $newHash, $uid);
                                    $up->execute();
                                    $up->close();
                                }
                            }
                        }

                        // Enregistrer le succès dans login_attempts
                        $ins = $mysqli->prepare('INSERT INTO login_attempts (ip, pseudo, success) VALUES (?, ?, ?)');
                        if ($ins) {
                            $ins->bind_param('ssi', $ip, $pseudo, $success);
                            $ins->execute();
                            $ins->close();
                        }

                        // Connexion réussie : régénérer l'ID de session, stocker user info
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = (int)$row['id'];
                        $_SESSION['pseudo'] = $pseudo;

                        // Nettoyage/rotations : supprimer échecs très anciens pour ce pseudo (facultatif)
                        $del = $mysqli->prepare(
                            'DELETE FROM login_attempts WHERE pseudo = ? AND success = 0 AND created_at < NOW() - INTERVAL ? MINUTE'
                        );
                        if ($del) {
                            $thr = $WINDOW_MINUTES * 4;
                            $del->bind_param('si', $pseudo, $thr);
                            $del->execute();
                            $del->close();
                        }

                        // Clear session fallback counters
                        unset($_SESSION['login_fail_count'], $_SESSION['login_fail_until']);

                        // Redirection sûre vers la home (APP_BASE constant)
                        header('Location: ' . rtrim(APP_BASE, '/') . '/index.php', true, 303);
                        exit;
                    } else {
                        // échec d'authentification : enregistrer
                        $ins = $mysqli->prepare('INSERT INTO login_attempts (ip, pseudo, success) VALUES (?, ?, ?)');
                        if ($ins) {
                            $ins->bind_param('ssi', $ip, $pseudo, $success);
                            $ins->execute();
                            $ins->close();
                        }

                        // message générique (ne pas révéler si pseudo existe)
                        $errors[] = 'Pseudo ou mot de passe invalide.';

                        // petit retard pour ralentir les attaques automatisées
                        usleep(200000); // 200 ms

                        // fallback session counter (utile si DB indisponible)
                        $fail_count = ($_SESSION['login_fail_count'] ?? 0) + 1;
                        $_SESSION['login_fail_count'] = $fail_count;
                        if ($fail_count >= 10) {
                            $_SESSION['login_fail_until'] = time() + $BLOCK_SECONDS;
                        }
                    } // endif row/password_verify
                } // endif prepare select
            } // endif thresholds
        } // endif fields present
    } // endif CSRF
} // endif POST

// ---------- HTML form (préservant styles existants) ----------
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
  <?php if ($errors): ?>
    <div class="errors">
      <?php foreach ($errors as $e) echo '<div>'.htmlspecialchars($e, ENT_QUOTES).'</div>'; ?>
    </div>
  <?php endif; ?>

  <form method="post" autocomplete="off" novalidate>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">
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






<script>function get_client_ip(): string {
  // Si tu es derrière un proxy (NGINX, Cloudflare...), vérifie que c'est trusted
  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // X-Forwarded-For peut contenir une liste "client, proxy1, proxy2"
    $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim($parts[0]);
    if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
  }
  if (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
    return $_SERVER['REMOTE_ADDR'];
  }
  return '0.0.0.0';
}
</script>


</body>
</html>
