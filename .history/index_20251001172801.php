<?php
/**
 * index.php — Feeed des projet publiés
 * - Auth obligatoire (require_login)
 * - Liste paginée, triée par date de publication DESC
 * - Filtres : "Mes projets", recherche texte + #tag, filtre par tag (chip)
 * - Affiche une miniature (cover) si dispo
 * - Requêtes préparées partout (SQLi-safe), pagination qui conserve le contexte
 */

declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';     // Connexion MySQLi ($mysqli)
require __DIR__ . '/auth.php';   // require_login(), session user
require __DIR__ . '/config.php'; // Constantes (APP_BASE, helpers slugify(), tag_url(), etc.)

require_login();

if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}


/* -------------------------------------------------------------------------
 * Paramètres UI / Filtres
 * ---------------------------------------------------------------------- */
$user_id = (int)$_SESSION['user_id'];

/** "Mes projets" => limite aux projets de l'auteur courant */
$mine = isset($_GET['mine']) && $_GET['mine'] === '1';

/** Recherche libre (texte +tags). On borne côté serveur pour éviter les abus. */
$q = mb_substr(preg_replace('/\s+/u',' ', trim((string)($_GET['q'] ?? ''))), 0, 30);

/** Filtre par tag via son slug (lien chip). On laiise une marge de 10*/
$tagSlug = mb_substr(trim((string)($_GET['tag'] ?? '')), 0, 10);

/** Pagination (1-based). On borne la taille de page pour éviter les gros scans en prod. */
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 10;
$off  = ($page - 1) * $per;





$scope = $_GET['scope'] ?? '';
$qraw  = trim((string)($_GET['q'] ?? ''));
$isUserQuery = ($scope === 'users') || str_starts_with($qraw, '@');

$userResults = [];
if ($isUserQuery && $qraw !== '') {
    // support @pseudo ou texte brut
    $needle = ltrim($qraw, '@');
    if (mb_strlen($needle) >= 2) {
        // IMPORTANT: index sur users(pseudo) (utf8mb4_general_ci) pour perf
        // CREATE INDEX idx_users_pseudo ON users(pseudo);
        $like = $needle.'%';
        $stmt = $mysqli->prepare('SELECT id, pseudo FROM users WHERE pseudo LIKE ? ORDER BY pseudo LIMIT 20');
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $userResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}








/* -------------------------------------------------------------------------
 * Contexte pour préserver les filtres dans les liens (pager, effacer, etc.)
 * ---------------------------------------------------------------------- */
$baseQuery = [];
if ($mine)           $baseQuery['mine']  = 1;
if ($tagSlug !== '') $baseQuery['tag']   = $tagSlug;
if ($scope !== '')   $baseQuery['scope'] = $scope;

$qsForPager = $baseQuery;
if ($q !== '')       $qsForPager['q'] = $q;

/* -------------------------------------------------------------------------
 * Construction dynamique du SQL
 * - WHERE de base: projets publiés
 * - JOINS : auteur, agrégat likes, + joins conditionnels pour tags/recherche
 * - On utilise bind_param pour tous les paramètres (SQLi-safe)
 * ---------------------------------------------------------------------- */
$where = "p.status = 'published'";

$joins = "JOIN users u ON u.id = p.author_id
          LEFT JOIN (
            SELECT project_id, COUNT(*) AS cnt
            FROM likes
            GROUP BY project_id
          ) l ON l.project_id = p.id";

$types  = '';   // chaîne des types pour bind_param (ex: 'issii')
$params = [];   // valeurs associées

/** Filtre "mes projets" */
if ($mine) {
  $where   .= " AND p.author_id = ?";
  $types   .= 'i';
  $params[] = $user_id;
}

/** Filtre explicite par tag slug (depuis un chip) */
if ($tagSlug !== '') {
  $joins   .= " JOIN project_tags pt ON pt.project_id = p.id
               JOIN tags t ON t.id = pt.tag_id AND t.slug = ?";
  $types   .= 's';
  $params[] = $tagSlug;
}

/** Recherche libre : titre/summary/corps + tags (tolère '#') */
if ($q !== '') {
  $joins   .= " LEFT JOIN project_tags qpt ON qpt.project_id = p.id
               LEFT JOIN tags qt ON qt.id = qpt.tag_id";

  $where   .= " AND (p.title LIKE ? OR p.summary LIKE ? OR p.body_markdown LIKE ?
                     OR qt.name LIKE ? OR qt.slug LIKE ?
                      OR u.pseudo LIKE ?)";

                     
  $like     = '%'.$q.'%';
  $likeTag  = '%'.ltrim($q, '#').'%';

  $types   .= 'ssssss';
  array_push($params, $like, $like, $like, $likeTag, $likeTag, $like);
}

/* -------------------------------------------------------------------------
 * SELECT principal
 * - DISTINCT car on peut dupliquer via les LEFT JOIN tags en recherche
 * - cover_thumb : première miniature si dispo (subquery simple)
 * - Pagination via LIMIT/OFFSET
 * ---------------------------------------------------------------------- */
$sql = "SELECT DISTINCT p.author_id AS author_id,
  p.id, p.title, p.summary, p.published_at,
  u.pseudo AS author,
  COALESCE(l.cnt,0) AS likes_count,
  (SELECT thumb_path
     FROM project_images i
     WHERE i.project_id = p.id AND i.thumb_path IS NOT NULL
     ORDER BY i.id ASC LIMIT 1) AS cover_thumb
FROM law_projects p
$joins
WHERE $where
ORDER BY p.published_at DESC
LIMIT ? OFFSET ?";

$types  .= 'ii';
$params[] = $per;
$params[] = $off;

/** Exécution */
$stmt = $mysqli->prepare($sql) ?: exit('nul'.$mysqli->error);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$projects = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* -------------------------------------------------------------------------
 * Récupérer les tags pour chaque projet (affichage chips en liste)
 * - Version préparée + garde IN() + chunking
 * ---------------------------------------------------------------------- */
$projectIds    = array_values(array_unique(array_map('intval', array_column($projects, 'id'))));
$tagsByProject = [];

if (!empty($projectIds)) {
  // éviter de binder une très grande liste d'un coup
  foreach (array_chunk($projectIds, 200) as $chunk) {  // array_chunk(..., 200) prévient un bind trop gros d’un coup.
    $placeholders = implode(',', array_fill(0, count($chunk), '?')) ;// Placeholders dynamiques (?, ?, …) + bind_param ⇒ pas d’injection.
    $types        = str_repeat('i', count($chunk));

    $sqlTags = "SELECT pt.project_id, t.name, t.slug
                FROM project_tags pt
                JOIN tags t ON t.id = pt.tag_id
                WHERE pt.project_id IN ($placeholders)
                ORDER BY t.name";

    $stmt = $mysqli->prepare($sqlTags) ?: exit('nul '.$mysqli->error);
    $stmt->bind_param($types, ...$chunk);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
      $tagsByProject[(int)$row['project_id']][] = $row;
    }
    $stmt->close();
  }
}


/* -------------------------------------------------------------------------
 * Count total pour pagination — version optimisée
 * - Pas de JOIN inutiles (users / likes)
 * - Filtre tag via EXISTS
 * - Recherche texte: LIKE sur p.*, + EXISTS pour tags
 * ---------------------------------------------------------------------- */
$whereCount  = "p.status = 'published'";
$typesCount  = '';
$paramsCount = [];

/** Mes projets */
if ($mine) {
  $whereCount  .= " AND p.author_id = ?";
  $typesCount  .= 'i';
  $paramsCount[] = $user_id;
}

/** Filtre explicite par tag (chip) */
if ($tagSlug !== '') {
  $whereCount  .= " AND EXISTS (
                      SELECT 1
                      FROM project_tags pt
                      JOIN tags t ON t.id = pt.tag_id
                      WHERE pt.project_id = p.id
                        AND t.slug = ?
                    )";
  $typesCount  .= 's';
  $paramsCount[] = $tagSlug;
}

/** Recherche libre (titre/summary/corps + tags) */
if ($q !== '') {
  $like    = '%'.$q.'%';
  $likeTag = '%'.ltrim($q, '#').'%';

  $whereCount .= " AND (
      p.title LIKE ?
      OR p.summary LIKE ?
      OR p.body_markdown LIKE ?
      OR EXISTS (
          SELECT 1
          FROM project_tags qpt
          JOIN tags qt ON qt.id = qpt.tag_id
          WHERE qpt.project_id = p.id
            AND (qt.name LIKE ? OR qt.slug LIKE ?)
      )
      OR EXISTS (
          SELECT 1
          FROM users u
          WHERE u.id = p.author_id
            AND u.pseudo LIKE ?
      )
  )";

  $typesCount .= 'ssssss';
  array_push($paramsCount, $like, $like, $like, $likeTag, $likeTag, $like); // +$like pour le pseudo
}


$countSql = "SELECT COUNT(*) FROM law_projects p WHERE $whereCount";
$stmt = $mysqli->prepare($countSql) ?: exit('nul: '.$mysqli->error);
if ($typesCount !== '') $stmt->bind_param($typesCount, ...$paramsCount);
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
  header{display:flex;justify-content:space-between;align-items:
    center;padding:16px 20px;background:#111827;position:sticky;top:0;z-index: 5;}
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



/* ---------- UI polish pack ---------- */
:root{
  --bg:#0f172a; --card:#111827; --line:#334155;
  --text:#e5e7eb; --muted:#94a3b8; --brand:#2563eb; --chip:#0b1220;
}

*{box-sizing:border-box}
a{color:#93c5fd;text-decoration:none}
a:hover{opacity:.9}

/* Header */
header{backdrop-filter:saturate(1.2) blur(6px); border-bottom:1px solid #0b1220}
.nav a{padding:6px 10px;border-radius:10px;transition:background .15s}
.nav a.active,.nav a:hover{background:#0b12205c}

/* Inputs & buttons */
input[name="q"]{min-width:260px; transition:border .15s, box-shadow .15s}
input[name="q"]:focus{outline:none;border-color:#475569; box-shadow:0 0 0 3px #2563eb33}
.btn{transition:transform .04s ease, box-shadow .15s}
.btn:hover{box-shadow:0 6px 18px #1d4ed81c}
.btn:active{transform:translateY(1px)}
.btn[disabled]{opacity:.6; cursor:not-allowed}


/* Make article layout 2-columns when a cover exists */
.card:has(.cover){display:grid; grid-template-columns:1fr 64px; gap:14px; align-items:start}
.cover{grid-column:2; width:64px; height:64px; border-radius:12px; overflow:hidden;
       box-shadow:0 6px 16px #0003; transform:translateZ(0); transition:transform .15s}
.cover img{width:100%; height:100%; object-fit:cover; display:block}
.cover:hover{transform:scale(1.04)}

/* Chips */
.card a[href*="tag="]{border-radius:999px;background:var(--chip);border:1px solid var(--line)}
.card a[href*="tag="]:hover{border-color:#475569}

/* Meta & user link */
.meta{color:var(--muted)}
.user-link{font-weight:600}
.user-link:hover{text-decoration:underline}

/* Pager */
.pager{gap:12px}
.pager a{padding:6px 10px;border:1px solid var(--line);border-radius:10px}
.pager a:hover{background:#0b1220}

/* Modal polish */
#userModal{backdrop-filter:blur(4px)}
#userModal>div{animation:pop .12s ease-out}
@keyframes pop{from{transform:scale(.96);opacity:0} to{transform:scale(1);opacity:1}}





</style>
</head>
<body>

<header>
  <div class="brand">Loi Direct</div>

  <!-- Onglets : Récents / Mes projets -->
  <nav class="nav">
    <a href="<?= APP_BASE ?>/index.php" class="<?= !$mine ? 'active' : '' ?>">Récents</a>
    <a href="<?= APP_BASE ?>/index.php?mine=1" class="<?= $mine ? 'active' : '' ?>">Mes projets</a>
    <a href="chat_rooms.php" target="_blank" rel="noopener noreferrer" style="color:red">Créer un salon</a>
  </nav>

  <!-- Formulaire de recherche.
       On reconduit le contexte (mine/tag) via des inputs hidden. -->
 <form method="get" action="<?= APP_BASE ?>/index.php" style="display:flex;gap:8px;align-items:center">
  <?php foreach ($baseQuery as $k=>$v): ?>
    <input type="hidden" name="<?= htmlspecialchars($k,ENT_QUOTES) ?>" value="<?= htmlspecialchars((string)$v,ENT_QUOTES) ?>">
  <?php endforeach; ?>

  <input name="q" value="<?= htmlspecialchars($q, ENT_QUOTES) ?>" placeholder="Rechercher... (@pseudo pour un utilisateur)" maxlength="30"
         style="padding:8px 10px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb">
  <select name="scope" style="padding:8px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb">
    <option value="">Tout</option>
    <option value="users" <?= (($_GET['scope']??'')==='users'?'selected':'') ?>>Utilisateurs</option>
  </select>
  <button class="btn" type="submit" style="padding:8px 12px">Rechercher</button>

  <?php if ($q !== ''): ?>
    <a class="btn" href="<?= APP_BASE ?>/index.php<?= $baseQuery ? ('?'.http_build_query($baseQuery)) : '' ?>" style="background:#374151">Effacer</a>
  <?php endif; ?>
</form>

  <!-- Zone utilisateur -->
  <div>
<span style="margin-right:12px;color:#cbd5e1">
  Salut,
  <a href="#"
     class="user-link"
     data-user-id="<?= (int)$_SESSION['user_id'] ?>"
     style="color:#cbd5e1; text-decoration:underline;">
     <?= htmlspecialchars($_SESSION['pseudo'], ENT_QUOTES) ?>
  </a> 👋
</span>
    <a class="btn" href="<?= APP_BASE ?>/write.php">Écrire un projet</a>
    <a class="btn" style="margin-left:8px;background:#374151" href="<?= APP_BASE ?>/logout.php">Se déconnecter</a>
  </div>


  <a class="btn" href="<?= APP_BASE ?>/messages_inbox.php" style="position:relative">
  Messages <span id="msgBadge" style="display:none;position:absolute;top:-6px;
  right:-6px;background:#ef4444;color:#fff;border-radius:999px;padding:0 6px;font-size:11px;
  line-height:18px;min-width:18px;text-align:center"></span>
</a>

</header>

<main class="wrap">

<?php if ($tagSlug !== ''): ?>
  <?php
    $noTagQuery = $baseQuery; unset($noTagQuery['tag']); if ($q!=='') $noTagQuery['q']=$q;
  ?>
  <div class="meta" style="margin-bottom:10px">
    Filtré par tag : <strong>#<?= htmlspecialchars($tagSlug,ENT_QUOTES) ?></strong>
    — <a href="<?= APP_BASE ?>/index.php<?= $noTagQuery ? ('?'.http_build_query($noTagQuery)) : '' ?>" style="color:#93c5fd">Effacer</a>
  </div>
<?php endif; ?>  <!-- ferme bien le bloc tag ici -->



  

<?php if ($isUserQuery): ?>
  <h2 style="margin-top:16px">Utilisateurs</h2>
  <?php if (!$userResults): ?>
    <p>Aucun utilisateur trouvé.</p>
  <?php else: foreach ($userResults as $u): ?>
    <div style="border:1px solid #334155;border-radius:10px;padding:10px;margin:8px 0;background:#111827;display:flex;justify-content:space-between;align-items:center">
      <div>@<?= htmlspecialchars($u['pseudo'],ENT_QUOTES) ?></div>
      <div style="display:flex;gap:8px">
        <a class="btn" href="<?= APP_BASE ?>/messages_inbox.php?user_id=<?= (int)$u['id'] ?>">Message</a>
        <a class="btn" style="background:#374151" href="<?= APP_BASE ?>/profile.php?id=<?= (int)$u['id'] ?>">Voir profil</a>
      </div>
    </div>
  <?php endforeach; endif; ?>
<?php endif; ?>



 

<?php if ($q !== ''): ?>
  <div class="meta" style="margin-bottom:10px">Résultats pour « <?= htmlspecialchars($q,ENT_QUOTES); ?> »</div>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="card" style="border-color:#14532d;background:#052e16;color:#bbf7d0;margin-bottom:12px">
    <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES); unset($_SESSION['flash_success']); ?>
  </div>
<?php endif; ?>
  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="card" style="border-color:#14532d;background:#052e16;color:#bbf7d0;margin-bottom:12px">
      <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES); unset($_SESSION['flash_success']); ?>
    </div>
  <?php endif; ?>
<?php if (!$projects): ?>
  <!-- Cas sans résultats / sans projets -->
  <div class="empty">
    <?php if ($q !== '' || $tagSlug !== '' || $mine): ?>
      Aucun résultat.
    <?php else: ?>
      Aucun projet pour l’instant.
    <?php endif; ?>
  </div>





<?php else: ?>
  <!-- Liste des projets -->
  <?php foreach ($projects as $p): ?>
    <article class="card">
      <h3 style="margin:0 0 6px"><?= htmlspecialchars($p['title'],ENT_QUOTES); ?></h3>
      <div class="meta">
  Par <a href="#" class="user-link" data-user-id="<?= (int)$p['author_id'] ?>">
       <?= htmlspecialchars($p['author'], ENT_QUOTES) ?>
     </a>
  • Publié le <?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['published_at']??'')),ENT_QUOTES); ?>
</div>
      <p><?= htmlspecialchars($p['summary'],ENT_QUOTES); ?></p>

      <?php $slug = slugify($p['title']); ?>

      <!-- CTA Lire -->
      <div style="margin-top:10px">
        <a class="btn" href="<?= APP_BASE ?>/p/<?= (int)$p['id'] ?>-<?= htmlspecialchars($slug, ENT_QUOTES) ?>">Lire</a>
      </div>

      <!-- Miniature cover cliquable → page projet -->
      <?php if (!empty($p['cover_thumb'])): ?>
        <a href="<?= APP_BASE ?>/p/<?= (int)$p['id'] ?>-<?= htmlspecialchars($slug, ENT_QUOTES) ?>"
           style="float:right;display:block;border-radius:10px;overflow:hidden;margin:0;position:relative;z-index:2;box-shadow:3px 2px 5px #0000004f;">
          <img
            src="<?= APP_BASE ?>/uploads/<?= htmlspecialchars($p['cover_thumb'], ENT_QUOTES) ?>"
            alt="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
            width="50" height="50" loading="lazy"
            style="width:50px;height:50px;object-fit:cover;display:block;">
          </a>
      <?php endif; ?>

      <!-- Chips de tags -->
      <?php if (!empty($tagsByProject[(int)$p['id']] ?? [])): ?>
        <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:6px">
          <?php foreach ($tagsByProject[(int)$p['id']] as $tg): ?>
            <a href="<?= tag_url($tg['slug']) ?>"
               style="font-size:12px; padding:4px 8px; border:1px solid #334155;
                      border-radius:999px; color:#cbd5e1; text-decoration:none; background:#0b1220">
               #<?= htmlspecialchars($tg['name'], ENT_QUOTES) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      

      <!-- Compteur de likes -->
      <span style="margin-left:8px;font-size:12px;color:#94a3b8">
        ❤ <?= (int)$p['likes_count']; ?>
      </span>
    </article>
  <?php endforeach; ?>

  <!-- Pagination (préserve le contexte via $qsForPager) -->
  <?php if ($totalPages > 1): ?>
    <div class="pager">
      <?php if ($page > 1): ?>
        <a href="<?= APP_BASE ?>/index.php?<?= http_build_query($qsForPager + ['page'=>$page-1]) ?>">&laquo; Précédent</a>
      <?php endif; ?>

      <span style="color:#94a3b8">Page <?= $page; ?> / <?= $totalPages; ?></span>

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


<!-- MODAL USER -->                   <!-- MODAL USER -->

<div id="userModal" style="position:fixed;inset:0;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center;z-index:50">
  <div style="background:#111827;border:1px solid #334155;border-radius:14px;padding:16px;min-width:280px;max-width:90%">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <strong id="umPseudo" style="font-size:16px">Pseudo</strong>
      <button id="umClose" class="btn" type="button" style="background:#374151;padding:4px 8px">×</button>
    </div>



      <!-- INFOS PROFIL -->
    <div id="umInfos" class="muted" style="margin-top:6px; font-size:13px">
      <!-- rempli en JS -->
    </div>

    <div class="muted">Projets publiés : <span id="umCount">0</span></div>

    <div class="muted">Situation : <span id="umStatus">—</span></div>
<div class="muted">Sexe : <span id="umSex">—</span> • Taille : <span id="umHeight">—</span></div>


    <div style="margin-top:12px">
      <a id="umLink" class="btn" href="#" style="display:inline-block">Voir le profil</a>
    </div>

    <button id="umMsgToggle" class="btn" type="button" style="margin-top:12px;background:#2563eb">Envoyer un message</button>

    <form id="umMsgForm" enctype="multipart/form-data" style="display:none;margin-top:10px">
      <textarea name="body" rows="4" maxlength="2000" required placeholder="Ton message…" 
      style="width:100%;padding:10px;border-radius:10px;border:1px 
      solid #334155;background:#0b1220;color:#e5e7eb"></textarea>

     

<label style="display:block;margin-top:10px">Image (optionnel)
  <input id="umImage" name="image" type="file"
         accept="image/*" capture="environment"
         style="display:block;margin-top:6px">
</label>

<!-- preview -->
<div id="umPreviewWrap" style="display:none;margin-top:8px">
  <img id="umPreview" alt="Aperçu"
       style="max-width:160px;max-height:160px;border-radius:8px;display:block">
  <button type="button" id="umClearImg"
          class="btn" style="background:#374151;margin-top:6px">Retirer l’image</button>
</div>

      <input type="hidden" name="recipient_id" id="umRecipient">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'],ENT_QUOTES) ?>">
      <button class="btn" type="submit" style="margin-top:8px">Envoyer</button>
      <div id="umMsgStatus" style="font-size:12px;color:#94a3b8;margin-top:6px"></div>

    </form>
  </div>
</div>


<script>
  const umSex   = document.getElementById('umSex');
const umHeight= document.getElementById('umHeight');
const umStatus= document.getElementById('umStatus');

</script>

<script>
const BASE     = '<?= APP_BASE ?>';
const modal    = document.getElementById('userModal');
const umPseudo = document.getElementById('umPseudo');
const umCount  = document.getElementById('umCount');
const umClose  = document.getElementById('umClose');
const umLink   = document.getElementById('umLink');

const umMsgToggle = document.getElementById('umMsgToggle');
const umMsgForm   = document.getElementById('umMsgForm');
const umRecipient = document.getElementById('umRecipient');
const umMsgStatus = document.getElementById('umMsgStatus');


const umImage      = document.getElementById('umImage');
const umPreview    = document.getElementById('umPreview');
const umPreviewWrap= document.getElementById('umPreviewWrap');
const umClearImg   = document.getElementById('umClearImg');








let umPreviewURL = null;

function hidePreview(){
  if (umPreviewURL) URL.revokeObjectURL(umPreviewURL);
  umPreviewURL = null;
  umPreview.src = '';
  umPreviewWrap.style.display = 'none';
}

umClearImg.addEventListener('click', () => {
  umImage.value = '';           // retire le fichier
  hidePreview();
});

umImage.addEventListener('change', () => {
  hidePreview();
  const f = umImage.files && umImage.files[0];
  if (!f) return




  // garde-fous (mêmes règles que serveur)
  const okType = ['image/jpeg','image/png','image/webp'].includes(f.type);
  if (!okType){ alert('Formats autorisés : JPG, PNG, WebP.'); umImage.value=''; return; }
  if (f.size > 5*1024*1024){ alert('Image trop lourde (max 5 Mo).'); umImage.value=''; return; }

  umPreviewURL = URL.createObjectURL(f);
  umPreview.src = umPreviewURL;
  umPreviewWrap.style.display = 'block';
});



// Reset preview à l’ouverture/fermeture de la modale
document.addEventListener('click', (e)=>{
  if (e.target.id === 'umClose') hidePreview();
});
modal.addEventListener('click', (e)=>{ if (e.target === modal) hidePreview(); });


umMsgToggle.addEventListener('click', ()=> {
  umMsgForm.style.display = umMsgForm.style.display==='none' ? 'block' : 'none';
});

// open modal on user click
const umInfos = document.getElementById('umInfos');

document.addEventListener('click', async (e) => {
  const a = e.target.closest('.user-link');
  if (!a) return;
  e.preventDefault();
  const id = a.getAttribute('data-user-id');

  try {
    const r = await fetch(`${BASE}/user_card.php?id=${encodeURIComponent(id)}`, {cache:'no-store'});
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    const j = await r.json();
    if (!j.ok) throw new Error(j.error || 'Réponse invalide');

  umPseudo.textContent = j.pseudo;
umCount.textContent  = j.projects_count;
umStatus.textContent = j.relationship_status ?? '—';
umSex.textContent    = j.sex ?? '—';
umHeight.textContent = (j.height_cm ? (j.height_cm + ' cm') : '—');
umLink.href       = `${BASE}/profile.php?id=${encodeURIComponent(id)}`;
umRecipient.value = id; // pour l’envoi de message


    // Construit la ligne d’infos: “Homme • 175 cm”, sinon “—”
    const parts = [];
    if (j.sex) parts.push(j.sex);
    if (typeof j.height_cm === 'number') parts.push(`${j.height_cm} cm`);
    umInfos.textContent = parts.length ? parts.join(' • ') : '—';

    if (parseInt(id,10) === <?= (int)$_SESSION['user_id'] ?>) {
      umMsgToggle.style.display = 'none';
      umMsgForm.style.display   = 'none';
    } else {
      umMsgToggle.style.display = '';
    }

    modal.style.display  = 'flex';
  } catch (err) {
    console.error('user_card.php error:', err);
  }
});


// submit message
umMsgForm.addEventListener('submit', async (e)=>{
  e.preventDefault();
  umMsgStatus.textContent = '';
  const fd = new FormData(umMsgForm);
  const btn = umMsgForm.querySelector('button[type="submit"]');
  btn.disabled = true;
  try{
    const r = await fetch(`${BASE}/message_send.php`, { method:'POST', body:fd });
    const j = await r.json();
    if(j.ok){
      umMsgStatus.style.color = '#34d399';
      umMsgStatus.textContent = 'Message envoyé ✅';
      umMsgForm.reset();
      refreshBadge();
    }else{
      umMsgStatus.style.color = '#f87171';
      umMsgStatus.textContent = 'Envoi impossible ('+(j.error||'erreur')+')';
    }
  }catch(_){
    umMsgStatus.style.color = '#f87171';
    umMsgStatus.textContent = 'Erreur réseau.';
  }finally{
    btn.disabled = false;
  }
});

// fermer la modale
umClose.addEventListener('click', ()=> modal.style.display='none');
modal.addEventListener('click', (e)=> { if (e.target === modal) modal.style.display='none'; });
document.addEventListener('keydown', (e)=> { if (e.key === 'Escape') modal.style.display='none'; });
</script>



<script>
const BADGE = document.getElementById('msgBadge');
async function refreshBadge(){
  try{
    const r = await fetch('<?= APP_BASE ?>/unread_count.php',{cache:'no-store'});
    if(!r.ok) throw 0;
    const j = await r.json();
    if(j.ok && typeof j.unread==='number'){
      if(j.unread>0){ BADGE.style.display='inline-block'; BADGE.textContent=j.unread>99?'99+':j.unread; }
      else{ BADGE.style.display='none'; BADGE.textContent=''; }
    }
  }catch(e){}
}
refreshBadge();
setInterval(refreshBadge, 20000);
</script>

</body>
</html>
