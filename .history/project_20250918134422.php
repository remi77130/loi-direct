<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];
$base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

$id = (int)($_GET['id'] ?? 0);
$project = null;
$likesCount = 0;
$likedByMe  = false;



if ($id > 0) {
  // Récup projet + likes + info "liké par moi"

  $sql = 'SELECT p.id, p.title, p.summary, p.body_markdown, p.published_at, p.author_id, u.pseudo AS author
        FROM law_projects p
        JOIN users u ON u.id = p.author_id
        WHERE p.id = ? AND p.status = "published"
        LIMIT 1';



  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $project = $res->fetch_assoc();
  $stmt->close();

  if ($project) {
    $slug = slugify($project['title']);
    $base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $expected = $base . '/p/' . (int)$id . '-' . $slug;
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
    if ($path !== $expected) {
        header('Location: ' . $expected, true, 301);
        exit;
    }
}

  if ($project) {
    $c = $mysqli->prepare('SELECT COUNT(*) FROM likes WHERE project_id=?');
    $c->bind_param('i', $id);
    $c->execute();
    $c->bind_result($likesCount);
    $c->fetch();
    $c->close();

    $l = $mysqli->prepare('SELECT 1 FROM likes WHERE project_id=? AND user_id=?');
    $l->bind_param('ii', $id, $_SESSION['user_id']);
    $l->execute();
    $l->store_result();
    $likedByMe = $l->num_rows > 0;
    $l->close();
  }


// Compteur + liste des commentaires
$comments = [];
$commentsCount = 0;
if ($project) {
  $cnt = $mysqli->prepare('SELECT COUNT(*) FROM comments WHERE project_id=?');
  $cnt->bind_param('i', $id);
  $cnt->execute();
  $cnt->bind_result($commentsCount);
  $cnt->fetch();
  $cnt->close();

$list = $mysqli->prepare(
  'SELECT c.id, c.author_id, c.body, c.created_at, u.pseudo
   FROM comments c JOIN users u ON u.id=c.author_id
   WHERE c.project_id=?
   ORDER BY c.created_at ASC'
);
  $list->bind_param('i', $id);
  $list->execute();
  $res = $list->get_result();
  $comments = $res->fetch_all(MYSQLI_ASSOC);
  $list->close();
}

// Prépare un brouillon s’il y a eu une erreur
$commentDraft = $_SESSION['comment_draft'] ?? '';
unset($_SESSION['comment_draft']);


}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $project ? htmlspecialchars($project['title'],ENT_QUOTES) : 'Projet introuvable'; ?> — Loi Direct</title>
<meta property="og:title" content="<?php echo $project ? htmlspecialchars($project['title'],ENT_QUOTES) : 'Loi Direct'; ?>">
<meta property="og:description" content="<?php echo $project ? htmlspecialchars($project['summary'],ENT_QUOTES) : 'Projets de loi communautaires'; ?>">
<style>
  :root{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}
  body{background:#0f172a;color:#e5e7eb;margin:0}
  .wrap{max-width:900px;margin:24px auto;padding:0 16px}
  .card{background:#111827;border:1px solid #334155;border-radius:14px;padding:20px}
  a{color:#93c5fd;text-decoration:none}
  .muted{color:#94a3b8}
  .toolbar{display:flex;gap:10px;margin-top:12px}
  .btn{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;cursor:pointer}
  .pill{display:inline-flex;align-items:center;gap:8px;border:1px solid #334155;background:#0b1220;border-radius:999px;padding:8px 12px;cursor:pointer}
  .pill.liked{border-color:#2563eb;box-shadow:inset 0 0 0 1px #2563eb}
  .toast{position:fixed;bottom:16px;left:50%;transform:translateX(-50%);background:#111827;border:1px solid #334155;color:#e5e7eb;padding:10px 14px;border-radius:10px;display:none}
</style>
<link rel="canonical" href="<?php echo APP_BASE; ?>/p/<?php echo (int)$id . '-' . htmlspecialchars($slug ?? '', ENT_QUOTES); ?>">

</head>
<body>
  <div class="wrap">
    <a href="<?php echo $base; ?>/index.php">&larr; Retour au feed</a>

    

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

        <div class="toolbar">
          <button id="likeBtn" class="pill <?php echo $likedByMe?'liked':''; ?>">
            <span id="likeIcon">❤</span><span id="likeCount"><?php echo (int)$likesCount; ?></span>
          </button>
          <button id="shareBtn" class="btn">Partager (copier le lien)</button>
        </div>

  
<?php
$images = [];
$st = $mysqli->prepare("SELECT path, thumb_path, original_name
                        FROM project_images
                        WHERE project_id=? ORDER BY id");
$st->bind_param('i', $id);
$st->execute();
$images = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

?>






<?php if ($images): ?>
  <hr style="border:0;border-top:1px solid #334155;margin:16px 0">
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px">
    <?php foreach ($images as $im):
      $thumb = $im['thumb_path'] ?: $im['path']; ?>
      <a href="<?= APP_BASE ?>/uploads/<?= htmlspecialchars($im['path'],ENT_QUOTES) ?>" target="_blank" rel="noopener">
        <img src="<?= APP_BASE ?>/uploads/<?= htmlspecialchars($thumb,ENT_QUOTES) ?>"
             alt="<?= htmlspecialchars($im['original_name'],ENT_QUOTES) ?>"
             style="width:80%;aspect-ratio:1/1;object-fit:contain;border-radius:10px; box-shadow: 2px 2px 3px #0b0b0b8c;">
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>




        <?php
$tags = [];
$tg = $mysqli->prepare('SELECT t.name, t.slug FROM project_tags pt JOIN tags t ON t.id=pt.tag_id WHERE pt.project_id=? ORDER BY t.name');
$tg->bind_param('i', $id);
$tg->execute();
$r = $tg->get_result();
$tags = $r->fetch_all(MYSQLI_ASSOC);
$tg->close();
?>
<?php if ($tags): ?>
  <div style="margin:10px 0; display:flex; flex-wrap:wrap; gap:6px">
    <?php foreach ($tags as $tg): ?>
      <a href="<?= tag_url($tg['slug']) ?>"
         style="font-size:12px; padding:4px 8px; border:1px solid #334155; border-radius:999px; color:#cbd5e1; text-decoration:none; background:#0b1220">
         #<?= htmlspecialchars($tg['name'], ENT_QUOTES) ?>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>





<?php if (!empty($pics)): ?>
  <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px">
    <?php foreach ($pics as $img): ?>
      <?php
        $full = UPLOAD_URL . '/' . htmlspecialchars($img['path'], ENT_QUOTES);
        $thumb = !empty($img['thumb_path'])
          ? UPLOAD_URL . '/' . htmlspecialchars($img['thumb_path'], ENT_QUOTES)
          : $full;
      ?>
      <a href="<?= $full ?>" target="_blank" rel="noopener">
        <img src="<?= $thumb ?>" alt=""
             style="max-width:220px;height:auto;border:1px solid #334155;border-radius:10px">
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>







<?php if ((int)$project['author_id'] === (int)$_SESSION['user_id']): ?>
  <form method="post" action="<?= APP_BASE ?>/project_delete.php"
        onsubmit="return confirm('Supprimer ce projet ? Cette action est définitive.');"
        style="margin-left:auto">
    <input type="hidden" name="project_id" value="<?= (int)$id ?>">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
    <button class="btn" type="submit" style="background:#7f1d1d">Supprimer</button>
  </form>
<?php endif; ?>


      </article>
    <?php endif; ?>
  </div>
<?php if ($project): ?>
  <section id="comments" class="card" style="margin-top:14px">
    <h2 style="margin:0 0 8px">Commentaires (<?php echo (int)$commentsCount; ?>)</h2>

    <?php if (!empty($_SESSION['flash_errors'])): ?>
      <div style="background:#7f1d1d;color:#fecaca;padding:10px;border-radius:8px;margin:10px 0">
        <?php foreach ($_SESSION['flash_errors'] as $e) echo '<div>'.htmlspecialchars($e,ENT_QUOTES).'</div>'; ?>
      </div>
      <?php unset($_SESSION['flash_errors']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_success'])): ?>
      <div style="background:#052e16;color:#bbf7d0;padding:10px;border-radius:8px;margin:10px 0">
        <?php echo htmlspecialchars($_SESSION['flash_success'],ENT_QUOTES); unset($_SESSION['flash_success']); ?>
      </div>
    <?php endif; ?>

    <?php if ($comments): ?>
      <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">


       <?php foreach ($comments as $c): ?>
  <div style="border:1px solid #334155;border-radius:10px;padding:10px;background:#0b1220;position:relative">
    <div style="font-size:12px;color:#94a3b8;margin-bottom:6px">
      <?php echo htmlspecialchars($c['pseudo'],ENT_QUOTES); ?> •
      <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($c['created_at'])),ENT_QUOTES); ?>
    </div>
    <div style="white-space:pre-wrap"><?php echo htmlspecialchars($c['body'],ENT_QUOTES); ?></div>

    <?php if ((int)$c['author_id'] === (int)$_SESSION['user_id']): ?>
      <form method="post" action="<?php echo $base; ?>/comment_delete.php"
            onsubmit="return confirm('Supprimer ce commentaire ?');"
            style="position:absolute;top:8px;right:8px">
        <input type="hidden" name="comment_id" value="<?php echo (int)$c['id']; ?>">
        <input type="hidden" name="project_id" value="<?php echo (int)$id; ?>">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <button class="btn" type="submit" style="background:#7f1d1d;padding:6px 10px">Supprimer</button>
      </form>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="muted" style="margin:8px 0">Aucun commentaire pour l’instant.</p>
    <?php endif; ?>

    <hr style="border:0;border-top:1px solid #334155;margin:14px 0">

    <form method="post" action="<?php echo $base; ?>/comment_add.php" novalidate>
      <label for="comment_body" style="display:block;font-size:13px;color:#cbd5e1;margin-bottom:6px">Ajouter un commentaire</label>
      <textarea id="comment_body" name="body" required maxlength="2000"
        style="width:100%;min-height:110px;padding:12px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;"><?php
          echo htmlspecialchars($commentDraft, ENT_QUOTES);
      ?></textarea>
      <input type="hidden" name="project_id" value="<?php echo (int)$id; ?>">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
      <button class="btn" type="submit" style="margin-top:8px">Publier le commentaire</button>
    </form>
  </section>
<?php endif; ?>

  <div id="toast" class="toast">Lien copié ✅</div>

<script>
const likeBtn = document.getElementById('likeBtn');
if (likeBtn) {
  likeBtn.addEventListener('click', async () => {
    likeBtn.disabled = true;
    try {
      const res = await fetch('<?php echo $base; ?>/like_toggle.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({id:'<?php echo (int)$id; ?>', csrf:'<?php echo htmlspecialchars($csrf,ENT_QUOTES); ?>'}).toString()
      });
      const data = await res.json();
      if (data.ok) {
        document.getElementById('likeCount').textContent = data.count;
        likeBtn.classList.toggle('liked', !!data.liked);
      }
    } catch(e) {}
    likeBtn.disabled = false;
  });
}

const shareBtn = document.getElementById('shareBtn');
const toast = document.getElementById('toast');
if (shareBtn) {
  shareBtn.addEventListener('click', async () => {
    const url = window.location.href;
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(url);
      } else {
        const inp = document.createElement('input');
        inp.value = url; document.body.appendChild(inp);
        inp.select(); document.execCommand('copy'); document.body.removeChild(inp);
      }
      toast.style.display = 'block';
      setTimeout(()=> toast.style.display='none', 1500);
    } catch(e) {}
  });
}
</script>
</body>
</html>
