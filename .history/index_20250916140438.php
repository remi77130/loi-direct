<?php
// index.php



declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/config.php';


require_login();


$user_id = (int)$_SESSION['user_id'];
$mine   = isset($_GET['mine']) && $_GET['mine'] === '1';
$q      = trim((string)($_GET['q'] ?? ''));   // recherche
$tagSlug = trim((string)($_GET['tag'] ?? ''));   // filtre tag optionnel


$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 10;
$off  = ($page - 1) * $per;

// Garde les filtres de contexte (hors recherche)
$baseQuery = [];
if ($mine)            $baseQuery['mine'] = 1;
if ($tagSlug !== '')  $baseQuery['tag']  = $tagSlug;

// Base pour les liens paginés (on y rajoute 'q' plus bas si présent)
$qsForPager = $baseQuery;
if ($q !== '') {
  // JOINS pour que la recherche voie aussi les tags
  $joins .= " LEFT JOIN project_tags qpt ON qpt.project_id = p.id
              LEFT JOIN tags qt ON qt.id = qpt.tag_id";

  // WHERE groupé : titre/objet/texte OU nom/slug de tag
  $where .= " AND (p.title LIKE ? OR p.summary LIKE ? OR p.body_markdown LIKE ? 
                   OR qt.name LIKE ? OR qt.slug LIKE ?)";
  $like    = '%'.$q.'%';
  $likeTag = '%'.ltrim($q, '#').'%';  // tolère #mot

  $types  .= 'sssss';
  array_push($params, $like, $like, $like, $likeTag, $likeTag);
}


  // tags (tolère #tag)
  $qTag = ltrim($q, '#');
  $joins .= " LEFT JOIN project_tags qpt ON qpt.project_id = p.id
              LEFT JOIN tags qt ON qt.id = qpt.tag_id";
  $where .= " OR qt.name LIKE ? OR qt.slug LIKE ?)";
  $likeTag = '%'.$qTag.'%';
  $types .= 'ss';
  array_push($params, $likeTag, $likeTag);



$sql = "SELECT p.id, p.title, p.summary, p.published_at, u.pseudo AS author, COALESCE(l.cnt,0) AS likes_count
        FROM law_projects p
        $joins
        WHERE $where
        ORDER BY p.published_at DESC
        LIMIT ? OFFSET ?";

$types.='ii'; array_push($params, $per, $off);

$stmt = $mysqli->prepare($sql) ?: exit('Prepare failed: '.$mysqli->error);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$projects = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();


$projectIds = array_column($projects, 'id');
$tagsByProject = [];
if ($projectIds) {
  $in = implode(',', array_map('intval', $projectIds));
  $qr = $mysqli->query("SELECT pt.project_id, t.name, t.slug
                        FROM project_tags pt JOIN tags t ON t.id=pt.tag_id
                        WHERE pt.project_id IN ($in)
                        ORDER BY t.name");
  while ($row = $qr->fetch_assoc()) {
    $pid = (int)$row['project_id'];
    $tagsByProject[$pid][] = $row;
  }
}

/* Pagination identique (mêmes filtres sans LIMIT/OFFSET) */
$countSql = "SELECT COUNT(DISTINCT p.id)
             FROM law_projects p
             $joins
             WHERE $where";
$stmt = $mysqli->prepare($countSql) ?: exit('Prepare failed(count): '.$mysqli->error);
if ($types !== '') {
  // enlève les deux derniers 'i' de LIMIT/OFFSET pour binder que les filtres
  $bindTypes = substr($types, 0, -2);
  $bindParams = array_slice($params, 0, count($params)-2);
  if ($bindTypes !== '') $stmt->bind_param($bindTypes, ...$bindParams);
}
$stmt->execute();
$stmt->bind_result($totalRows);
$stmt->fetch();
$stmt->close();

$totalPages = max(1, (int)ceil($totalRows / $per));

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
  .btn{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:5px 14px;text-decoration:none}
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
    <a href="<?= APP_BASE ?>/index.php" class="<?= !$mine ? 'active' : '' ?>">Récents</a>
    <a href="<?= APP_BASE ?>/index.php?mine=1" class="<?= $mine ? 'active' : '' ?>">Mes projets</a>
  </nav>

  <form method="get" action="<?= APP_BASE ?>/index.php" style="display:flex;gap:8px;align-items:center">
    <?php foreach ($baseQuery as $k=>$v): ?>
      <input type="hidden" name="<?= htmlspecialchars($k,ENT_QUOTES) ?>" value="<?= htmlspecialchars((string)$v,ENT_QUOTES) ?>">
    <?php endforeach; ?>

    <input name="q" value="<?= htmlspecialchars($q, ENT_QUOTES) ?>" placeholder="Rechercher..."
           style="padding:8px 10px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb">
    <button class="btn" type="submit" style="padding:8px 12px">Rechercher</button>

    <?php if ($q !== ''): ?>
      <a class="btn" href="<?= APP_BASE ?>/index.php<?= $baseQuery ? ('?'.http_build_query($baseQuery)) : '' ?>" style="background:#374151">Effacer</a>
    <?php endif; ?>
  </form>

  <div>
    <span style="margin-right:12px;color:#cbd5e1">Bonjour, <?= htmlspecialchars($_SESSION['pseudo'],ENT_QUOTES) ?> 👋</span>
    <a class="btn" href="<?= APP_BASE ?>/write.php">Écrire un projet</a>
    <a class="btn" style="margin-left:8px;background:#374151" href="<?= APP_BASE ?>/logout.php">Se déconnecter</a>
  </div>
</header>




<main class="wrap">

<?php if ($tagSlug !== ''): ?>
  <div class="meta" style="margin-bottom:10px">
    Filtré par tag : <strong>#<?= htmlspecialchars($tagSlug,ENT_QUOTES) ?></strong>

    <a href="<?= APP_BASE ?>/index.php" style="color:#93c5fd">Effacer</a>

  </div>
<?php endif; ?>

  <?php if ($q !== ''): ?>
  <div class="meta" style="margin-bottom:10px">Résultats pour « <?php echo htmlspecialchars($q,ENT_QUOTES); ?> »</div>
<?php endif; ?>


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
        <p ><?php echo htmlspecialchars($p['summary'],ENT_QUOTES); ?></p>

<?php $slug = slugify($p['title']); // ?>
<div style="margin-top:10px">
  <a class="btn" href="<?= APP_BASE ?>/p/<?= (int)$p['id'] ?>-<?= htmlspecialchars($slug, ENT_QUOTES) ?>">Lire</a>
</div>
    

<?php if (!empty($tagsByProject[(int)$p['id']] ?? [])): ?>
  <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:6px">
    <?php foreach ($tagsByProject[(int)$p['id']] as $tg): ?>
      <a href="<?= tag_url($tg['slug']) ?>"
         style="font-size:12px; padding:4px 8px; border:1px solid #334155; border-radius:999px; color:#cbd5e1; text-decoration:none; background:#0b1220">
         #<?= htmlspecialchars($tg['name'], ENT_QUOTES) ?>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>


<span style="margin-left:8px;font-size:12px;color:#94a3b8">
  ❤ <?php echo (int)$p['likes_count']; ?>
</span>
      </article>





    <?php endforeach; ?>

    <?php if ($totalPages > 1): ?>
     <div class="pager">
    <?php if ($page > 1): ?>
      <a href="<?= APP_BASE ?>/index.php?<?= http_build_query($qsForPager + ['page'=>$page-1]) ?>">&laquo; Précédent</a>
    <?php endif; ?>

    <span style="color:#94a3b8">Page <?= $page; ?> / <?= $totalPages; ?></span>

    <?php if ($page < $totalPages): ?>
      <a href="<?= APP_BASE ?>/index.php?<?= http_build_query($qsForPager + ['page'=>$page+1]) ?>">Suivant &raquo;</a>
    <?php endif; ?>
  </div>
    <?php endif; ?>
  <?php endif; ?>
</main>
</body>
</html>
