<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/admin_auth.php';

if (is_admin_logged_in()) { // Si l’admin est déjà connecté, pas besoin de lui montrer la page de login, on le redirige direct vers les demandes de payout.
    header('Location: ' . APP_BASE . '/admin_payout_requests.php');
    exit;
}

$error = trim((string)($_GET['error'] ?? ''));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family:Arial,sans-serif;background:#0f172a;color:#fff;padding:40px;">
    <div style="max-width:420px;margin:0 auto;background:#111827;border:1px solid #334155;border-radius:14px;padding:24px;">
        <h1 style="margin-top:0;">Connexion admin</h1>

        <?php if ($error !== ''): ?>
            <div style="margin-bottom:16px;padding:12px;border-radius:10px;border:1px solid #7f1d1d;background:#450a0a;color:#fecaca;">
                <?php echo htmlspecialchars($error, ENT_QUOTES); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= APP_BASE ?>/admin_login_submit.php" style="display:grid;gap:14px;">
            <div>
                <label for="login" style="display:block;margin-bottom:6px;">Pseudo admin</label>
                <input type="text" id="login" name="login" required
                       style="width:100%;padding:12px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#fff;">
            </div>

            <div>
                <label for="password" style="display:block;margin-bottom:6px;">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       style="width:100%;padding:12px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#fff;">
            </div>

            <button type="submit"
                    style="padding:12px 16px;border:none;border-radius:10px;background:#22c55e;color:#052e16;font-weight:700;cursor:pointer;">
                Se connecter
            </button>
        </form>
    </div>
</body>
</html>