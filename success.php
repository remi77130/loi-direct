<?php
declare(strict_types=1);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: register.php');
    exit;
}
$pseudo = $_SESSION['pseudo'] ?? 'inconnu';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta name="robots" content="noindex, nofollow">

  <meta charset="utf-8">
  <title>Bienvenue</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;background:#0f172a;color:#e5e7eb;
         min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0}
    .box{background:#111827;padding:24px;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
    a{color:#93c5fd;text-decoration:none}
  </style>
</head>
<body>
  <div class="box">
    <h1>Bienvenue, <?php echo htmlspecialchars($pseudo, ENT_QUOTES); ?> 👋</h1>
    <p>Inscription réussie. Prochaine étape : la page d’accueil avec la liste des projets.</p>
    <p><a href="register.php">Retour</a></p>
  </div>
</body>
</html>
