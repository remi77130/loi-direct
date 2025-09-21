<?php
// register.php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

// CSRF token léger
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $csrf)) {
        $errors[] = "Session expirée. Recharge la page.";
    } else {
        $pseudo = trim($_POST['pseudo'] ?? '');

        // Règles: 3-20, alphanum + underscore
        if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $pseudo)) {
            $errors[] = "Le pseudo doit faire 3 à 20 caractères (lettres, chiffres, underscore).";
        } else {
            // Dispo serveur
            $stmt = $mysqli->prepare('SELECT 1 FROM users WHERE pseudo = ? LIMIT 1');
            $stmt->bind_param('s', $pseudo);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "Ce pseudo est déjà pris.";
            }
            $stmt->close();
        }

        if (!$errors) {
            // Insert
            $ins = $mysqli->prepare('INSERT INTO users (pseudo) VALUES (?)');
            $ins->bind_param('s', $pseudo);


          if ($ins->execute()) {
   session_regenerate_id(true);
$_SESSION['user_id'] = $ins->insert_id;
$_SESSION['pseudo']  = $pseudo;
$_SESSION['flash_success'] = random_punchline($pseudo);

$base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
header('Location: ' . $base . '/index.php');
exit;



 else {
                $errors[] = "Échec d’inscription (BDD).";
            }
            $ins->close();
        }
    }
}



?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Inscription — Loi Direct</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
    body { background:#0f172a; color:#e5e7eb; display:flex; min-height:100vh; align-items:center; justify-content:center; margin:0; }
    .card { background:#111827; padding:24px; border-radius:16px; width:100%; max-width:420px; box-shadow: 0 10px 30px rgba(0,0,0,.35); }
    h1 { margin:0 0 16px; font-size:24px; }
    label { display:block; font-size:14px; margin-bottom:8px; color:#cbd5e1; }
    input[type="text"]{
      width:100%; padding:12px 14px; border-radius:10px; border:1px solid #334155;
      background:#0b1220; color:#e5e7eb; outline:none;
    }
    .hint { font-size:12px; color:#94a3b8; margin-top:6px; }
    .status { font-size:12px; margin-top:6px; }
    .ok { color:#34d399; }
    .ko { color:#f87171; }
    .btn {
      width:100%; margin-top:16px; padding:12px; border:none; border-radius:10px;
      background:#2563eb; color:white; font-weight:600; cursor:pointer;
    }
    .btn:disabled { opacity:.6; cursor:not-allowed; }
    .errors { background:#7f1d1d; color:#fecaca; padding:10px; border-radius:8px; margin-bottom:12px; }
    .footer { margin-top:14px; font-size:12px; color:#94a3b8; text-align:center; }
    a { color:#93c5fd; text-decoration: none; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Créer un compte</h1>

    <?php if ($errors): ?>
      <div class="errors">
        <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e, ENT_QUOTES)."</div>"; ?>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off" novalidate>
      <label for="pseudo">Pseudo</label>
      <input type="text" id="pseudo" name="pseudo" minlength="3" maxlength="20"
             pattern="[A-Za-z0-9_]{3,20}" required placeholder="ex: Remi_81">
      <div class="hint">3–20 caractères. Lettres, chiffres et underscore uniquement.</div>
      <div id="status" class="status"></div>

      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES); ?>">
      <button id="submitBtn" class="btn" type="submit" disabled>S’inscrire</button>
    </form>

    <div class="footer">Déjà inscrit ? <a href="#">Connexion (plus tard)</a></div>
  </div>

<script>
const $pseudo = document.getElementById('pseudo');
const $status = document.getElementById('status');
const $submit = document.getElementById('submitBtn');

let t = null;
$pseudo.addEventListener('input', () => {
  const v = $pseudo.value.trim();
  $submit.disabled = true;
  $status.textContent = '';

  if (!/^[A-Za-z0-9_]{3,20}$/.test(v)) {
    $status.textContent = 'Format invalide.';
    $status.className = 'status ko';
    return;
  }

  clearTimeout(t);
  t = setTimeout(async () => {
    try {
      const res = await fetch('check_pseudo.php?pseudo=' + encodeURIComponent(v), {cache: 'no-store'});
      const data = await res.json();
      if (!data.ok) {
        $status.textContent = 'Format invalide.';
        $status.className = 'status ko';
        return;
      }
      if (data.available) {
        $status.textContent = '✅ Pseudo disponible';
        $status.className = 'status ok';
        $submit.disabled = false;
      } else {
        $status.textContent = '❌ Déjà pris';
        $status.className = 'status ko';
      }
    } catch (e) {
      $status.textContent = 'Erreur de vérification';
      $status.className = 'status ko';
    }
  }, 250); // petit debounce
});
</script>
</body>
</html>
