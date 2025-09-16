<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/config.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
$project = null;

if ($id > 0) {
  $sql = 'SELECT p.id, p.title, p.summary, p.body_markdown, p.published_at, u.pseudo AS author
          FROM law_projects p
          JOIN users u ON u.id = p.author_id
          WHERE p.id = ? AND p.status = "published"
          LIMIT 1';
  $stmt = $mysqli->prepare($sql);
  if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) $project = $res->fetch_assoc();
    $stmt->close();
  }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $project ? htmlspecialchars($project['title'],ENT_QUOTES) : 'Projet introuvable'; ?> — Loi Direct</title>
<style>
  :root{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}
  body{background:#0f172a;color:#e5e7eb;margin:0}
  .wrap{max-width:900px;margin:24px auto;padding:0 16px}
  .card{background:#111827;border:1px solid #334155;border-radius:14px;padding:20px}
  a{color:#93c5fd;text-decoration:none}
  .muted{color:#94a3b8}
</style>
</head>
<body>
  <div class="wrap">
    <a href="<?php echo rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); ?>/index.php">&larr; Retour au feed</a>

    <?php if (!$project): ?>
      <div class="card" style="margin-top:12px">
        <h1 style="margin:0 0 8px">Projet introuvable</h1>
        <p class="muted">Le projet demandé n’existe pas, a été retiré, ou l’URL est invalide.</p>
      </div>
    <?php else: ?>
      <article class="card" style="margin-top:12px">
        <h1 style="margin:0 0 10px"><?php echo htmlspecialchars($project['title'],ENT_QUOTES); ?></h1>
        <div style="font-size:12px;color:#94a3b8;margin-bottom:8px">
          Par <?php echo htmlspecialchars($project['author'],ENT_QUOTES); ?>
          • <?php echo htmlspecialchars($project['published_at'] ? date('d/m/Y H:i', strtotime($project['published_at'])) : '', ENT_QUOTES); ?>
        </div>
        <p style="color:#cbd5e1;white-space:pre-line"><?php echo htmlspecialchars($project['summary'],ENT_QUOTES); ?></p>
        <hr style="border:0;border-top:1px solid #334155;margin:16px 0">
        <div style="white-space:pre-wrap"><?php echo htmlspecialchars($project['body_markdown'],ENT_QUOTES); ?></div>
      </article>
    <?php endif; ?>
  </div>
</body>
</html>
