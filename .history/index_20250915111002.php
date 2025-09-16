<?php
// index.php



declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/config.php';


require_login();

$user_id = (int)$_SESSION['user_id'];

$mine = isset($_GET['mine']) && $_GET['mine'] === '1';
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 10;
$off  = ($page - 1) * $per;

// Base query
// Base query
$where = "p.status = 'published'";
if ($mine) {
    $where .= " AND p.author_id = ?";
    ...
}
$params = [];
$types  = '';

if ($mine) {
    $where .= " AND author_id = ?";
    $params[] = $user_id;
    $types   .= 'i';
}

$sql = "SELECT p.id, p.title, p.summary, p.published_at, p.author_id, u.pseudo AS author,
               COALESCE(l.cnt,0) AS likes_count
        FROM law_projects p
        JOIN users u ON u.id = p.author_id
        LEFT JOIN (
          SELECT project_id, COUNT(*) AS cnt
          FROM likes
          GROUP BY project_id
        ) l ON l.project_id = p.id
        WHERE $where
        ORDER BY p.published_at DESC
        LIMIT ? OFFSET ?";

$params = [];
$types  = '';
if ($mine) { $params[] = $user_id; $types .= 'i'; }
$params[] = $per; $types .= 'i';
$params[] = $off; $types .= 'i';

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$projects = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Loi Direct — Feed</title>
<style>
  :root{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}
  body{background:#0f172a;color:#e5e7eb;margin:0}
  header{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#111827;position:sticky;top:0}
  .brand{font-weight:800}
  .nav a{color:#cbd5e1;margin-right:16px;text-decoration:none}
  .nav a.active{color:#fff;font-weight:700}
  .btn{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;text-decoration:none}
  .wrap{max-width:900px;margin:24px auto;padding:0 16px}
  .card{background:#111827;border:1px solid #334155;border-radius:14px;padding:16px;margin-bottom:14px}
  .meta{font-size:12px;color:#94a3b8;margin-top:4px}
  .pager{display:flex;gap:8px;justify-content:center;margin:20px 0}
  .pager a{color:#93c5fd;text-decoration:none}
  .empty{color:#94a3b8;text-align:center;margin:40px 0}
</style>
</head>
<body>
<header>
  <div class="brand">Loi Direct</div>
  <nav class="nav">
    <a href="index.php" class="<?php echo !$mine?'active':'';?>">Récents</a>
    <a href="index.php?mine=1" class="<?php echo $mine?'active':'';?>">Mes projets</a>
  </nav>
  <div>
    <span style="margin-right:12px;color:#cbd5e1">Bonjour, <?php echo htmlspecialchars($_SESSION['pseudo'],ENT_QUOTES); ?> 👋</span>
    <a class="btn" href="write.php">Écrire un projet</a>
    <a class="btn" style="margin-left:8px;background:#374151" href="logout.php">Se déconnecter</a>
  </div>
</header>

<main class="wrap">

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="card" style="border-color:#14532d;background:#052e16;color:#bbf7d0;margin-bottom:12px">
    <?php echo htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES); unset($_SESSION['flash_success']); ?>
  </div>
<?php endif; ?>



  <?php if (!$projects): ?>
    <div class="empty">Aucun projet pour l’instant.</div>
  <?php else: ?>
    <?php foreach ($projects as $p): ?>
      <article class="card">
        <h3 style="margin:0 0 6px"><?php echo htmlspecialchars($p['title'],ENT_QUOTES); ?></h3>
        <div class="meta">
          Par <?php echo htmlspecialchars($p['author'],ENT_QUOTES); ?>
          • Publié le <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($p['published_at']??'')),ENT_QUOTES); ?>
        </div>
        <p style="margin:10px 0 0"><?php echo htmlspecialchars($p['summary'],ENT_QUOTES); ?></p>
        <?php $base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); ?>
<div style="margin-top:10px">
  <a class="btn" href="<?php echo $base; ?>/project.php?id=<?php echo (int)$p['id']; ?>">Lire</a>
</div>
<span style="margin-left:8px;font-size:12px;color:#94a3b8">
  ❤ <?php echo (int)$p['likes_count']; ?>
</span>


      </article>
    <?php endforeach; ?>

    <?php if ($totalPages > 1): ?>
      <div class="pager">
        <?php if ($page>1): ?>
          <a href="?<?php echo http_build_query(['mine'=>$mine?1:null,'page'=>$page-1]); ?>">&laquo; Précédent</a>
        <?php endif; ?>
        <span style="color:#94a3b8">Page <?php echo $page; ?> / <?php echo $totalPages; ?></span>
        <?php if ($page<$totalPages): ?>
          <a href="?<?php echo http_build_query(['mine'=>$mine?1:null,'page'=>$page+1]); ?>">Suivant &raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</main>
</body>
</html>
