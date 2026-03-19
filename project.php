<?php
/**
 * project.php — Page de lecture d’un projet
 *
 * Fonctions :
 * - Affiche un projet publié
 * - Redirige vers l’URL canonique /p/{id}-{slug}
 * - Gère l’affichage des contenus gratuits / payants
 * - Si contenu payant non débloqué :
 *   -> affiche seulement titre + prix + miniature
 * - Si contenu gratuit, débloqué ou auteur :
 *   -> affiche le contenu complet
 * - Gère likes, partage, galerie, tags, commentaires
 */

declare(strict_types=1);
session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

/* ----------------------------------------------------------------------
 * CSRF token (réutilisé par like & commentaires)
 * -------------------------------------------------------------------- */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/**
 * Base path du script (ex: /loi)
 * Sert à générer des URLs relatives robustes.
 */
$base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

/* ----------------------------------------------------------------------
 * Entrée : id de projet
 * -------------------------------------------------------------------- */
$id = (int)($_GET['id'] ?? 0);

/* ----------------------------------------------------------------------
 * Variables d’état
 * -------------------------------------------------------------------- */
$project = null;
$likesCount = 0;
$likedByMe = false;


$comments = [];
$commentsCount = 0;
$images = [];
$tags = [];


$hasUnlock = false;          // L’utilisateur a déjà débloqué ce projet payant
$canViewFullContent = false; // Peut voir le contenu complet ?
$isPaid = 0;                 // Projet payant ou non
$unlockPointsPrice = 0;      // Prix du déverrouillage en points
$isOwner = false;            // L’utilisateur courant est-il le créateur ?

// Variables pour le cas de contenu payant non débloqué
$userPointsBalance = 0;      // Solde actuel de points de l’utilisateur
$hasEnoughPoints = false;    // Solde suffisant pour déverrouiller ?
$missingPoints = 0;          // Nombre de points manquants

/* ----------------------------------------------------------------------
 * Chargement du projet s’il y a un id valide
 * -------------------------------------------------------------------- */
if ($id > 0) {
    /**
     * On récupère :
     * - les champs principaux du projet
     * - les champs liés au contenu payant
     * - le pseudo de l’auteur
     */
    $sql = 'SELECT
                p.id,
                p.title,
                p.summary,
                p.body_markdown,
                p.published_at,
                p.author_id,
                p.is_paid,
                p.unlock_points_price,
                p.creator_user_id,
                u.pseudo AS author
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
        /* --------------------------------------------------------------
         * URL canonique SEO : /p/{id}-{slug}
         * ------------------------------------------------------------ */
        $slug = slugify($project['title']);
        $expected = $base . '/p/' . (int)$id . '-' . $slug;
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';

        if ($path !== $expected) {
            header('Location: ' . $expected, true, 301);
            exit;
        }

        /* --------------------------------------------------------------
         * Lecture des champs de monétisation
         * ------------------------------------------------------------ */
        $isPaid = (int)($project['is_paid'] ?? 0);
        $unlockPointsPrice = (int)($project['unlock_points_price'] ?? 0);

        $currentUserId = (int)($_SESSION['user_id'] ?? 0); // sécurité supplémentaire pour éviter les warnings si la session est mal configurée
        $isOwner = $currentUserId > 0 && (int)$project['creator_user_id'] === $currentUserId; // l’auteur du projet peut toujours voir le contenu complet même s’il est payant
        
                /**
         * Solde points utilisateur courant
         * Si aucun wallet n’existe encore, on considère 0 point.
         */
        if ($currentUserId > 0) {
            $w = $mysqli->prepare(
                'SELECT balance_points
                 FROM user_points_wallet
                 WHERE user_id = ?
                 LIMIT 1'
            );
            $w->bind_param('i', $currentUserId);
            $w->execute();
            $walletRes = $w->get_result();
            $walletRow = $walletRes ? $walletRes->fetch_assoc() : null;
            $w->close();

            $userPointsBalance = (int)($walletRow['balance_points'] ?? 0);
        }
        
        
        
        
        /**
         * Si le contenu est payant et que l’utilisateur n’est pas le créateur,
         * on vérifie s’il l’a déjà débloqué.
         */
        if ($isPaid === 1 && !$isOwner) {
            $u = $mysqli->prepare('SELECT 1 FROM project_unlocks WHERE project_id = ? AND user_id = ? LIMIT 1');
            $u->bind_param('ii', $id, $currentUserId);
            $u->execute();
            $u->store_result();
            $hasUnlock = $u->num_rows > 0;
            $u->close();
        }

        /**
         * Le contenu complet est visible si :
         * - le projet est gratuit
         * - ou l’utilisateur est le créateur
         * - ou l’utilisateur l’a déjà débloqué
         */
        $canViewFullContent = ($isPaid !== 1) || $isOwner || $hasUnlock; // contenu visible si gratuit, si auteur, ou déjà débloqué

                    /**
         * Calcul UX pour le bouton de déverrouillage
         * Utile uniquement si contenu payant, non propriétaire, non débloqué.
         */
        if ($isPaid === 1 && !$isOwner && !$hasUnlock) { // Note : on vérifie $isPaid pour éviter d’afficher un prix ou un état de points sur les projets gratuits même si la base de données contient une valeur non nulle par erreur
            $hasEnoughPoints = $userPointsBalance >= $unlockPointsPrice;
            $missingPoints = max(0, $unlockPointsPrice - $userPointsBalance); // pour affichage, on s’assure que ce soit jamais négatif même si la base de données contient une valeur incohérente

        }
        /* --------------------------------------------------------------
         * Likes
         * On les charge seulement si le contenu complet est visible
         * ------------------------------------------------------------ */
        if ($canViewFullContent) {
            $c = $mysqli->prepare('SELECT COUNT(*) FROM likes WHERE project_id = ?');
            $c->bind_param('i', $id);
            $c->execute();
            $c->bind_result($likesCount);
            $c->fetch();
            $c->close();

            $l = $mysqli->prepare('SELECT 1 FROM likes WHERE project_id = ? AND user_id = ?');
            $l->bind_param('ii', $id, $_SESSION['user_id']);
            $l->execute();
            $l->store_result();
            $likedByMe = $l->num_rows > 0;
            $l->close();
        }

        /* --------------------------------------------------------------
         * Commentaires
         * On les charge seulement si le contenu complet est visible
         * ------------------------------------------------------------ */
        if ($canViewFullContent) {
            $cnt = $mysqli->prepare('SELECT COUNT(*) FROM comments WHERE project_id = ?');
            $cnt->bind_param('i', $id);
            $cnt->execute();
            $cnt->bind_result($commentsCount);
            $cnt->fetch();
            $cnt->close();

            $list = $mysqli->prepare(
                'SELECT c.id, c.author_id, c.body, c.created_at, u.pseudo
                 FROM comments c
                 JOIN users u ON u.id = c.author_id
                 WHERE c.project_id = ?
                 ORDER BY c.created_at ASC'
            );
            $list->bind_param('i', $id);
            $list->execute();
            $res = $list->get_result();
            $comments = $res->fetch_all(MYSQLI_ASSOC);
            $list->close();
        }

        /* --------------------------------------------------------------
         * Images
         * On charge toujours les images :
         * - si contenu verrouillé : on montrera seulement la première
         * - si contenu visible : on montrera la galerie complète
         * ------------------------------------------------------------ */
        $st = $mysqli->prepare(
            'SELECT path, original_name
             FROM project_images
             WHERE project_id = ?
             ORDER BY id'
        );
        $st->bind_param('i', $id);
        $st->execute();
        $images = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();

        /* --------------------------------------------------------------
         * Tags
         * On les charge seulement si le contenu complet est visible
         * ------------------------------------------------------------ */
        if ($canViewFullContent) {
            $tg = $mysqli->prepare(
                'SELECT t.name, t.slug
                 FROM project_tags pt
                 JOIN tags t ON t.id = pt.tag_id
                 WHERE pt.project_id = ?
                 ORDER BY t.name'
            );
            $tg->bind_param('i', $id);
            $tg->execute();
            $r = $tg->get_result();
            $tags = $r->fetch_all(MYSQLI_ASSOC);
            $tg->close();
        }
    }
}

/* ----------------------------------------------------------------------
 * Si on revient d’un post de commentaire en erreur, on affiche le brouillon
 * -------------------------------------------------------------------- */
$commentDraft = $_SESSION['comment_draft'] ?? '';
unset($_SESSION['comment_draft']);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta name="robots" content="noindex, nofollow">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo $project ? htmlspecialchars($project['title'], ENT_QUOTES) : 'Projet introuvable'; ?> — Loi Direct</title>

    <?php $slugForCanonical = ($project ? slugify($project['title']) : ''); ?>
    <link rel="canonical" href="<?php echo APP_BASE; ?>/p/<?php echo (int)$id . '-' . htmlspecialchars($slugForCanonical, ENT_QUOTES); ?>">

    <meta property="og:title" content="<?php echo $project ? htmlspecialchars($project['title'], ENT_QUOTES) : 'Loi Direct'; ?>">
    <meta property="og:description" content="<?php echo $project ? htmlspecialchars($project['summary'], ENT_QUOTES) : 'Projets de loi communautaires'; ?>">

    <style>
        :root { font-family: system-ui, Segoe UI, Roboto, Arial, sans-serif; }
        body { background: #0f172a; color: #e5e7eb; margin: 0; }
        .wrap { max-width: 900px; margin: 24px auto; padding: 0 16px; }
        .card {
            background: #111827;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 20px;
            overflow-wrap: break-word;
        }
        a { color: #93c5fd; text-decoration: none; }
        .muted { color: #94a3b8; }
        .toolbar { display: flex; gap: 10px; margin-top: 12px; }
        .btn {
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #334155;
            background: #0b1220;
            border-radius: 999px;
            padding: 8px 12px;
            cursor: pointer;
        }
        .pill.liked {
            border-color: #2563eb;
            box-shadow: inset 0 0 0 1px #2563eb;
        }
        .toast {
            position: fixed;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            background: #111827;
            border: 1px solid #334155;
            color: #e5e7eb;
            padding: 10px 14px;
            border-radius: 10px;
            display: none;
        }
    </style>
</head>

<body>
<div class="wrap">
    <a href="<?php echo $base; ?>/feed.php">&larr; Retour au feed</a>

    <?php if (!empty($_SESSION['flash_errors'])): ?>
  <div style="background:#7f1d1d;color:#fecaca;padding:10px;border-radius:8px;margin:12px 0">
    <?php foreach ($_SESSION['flash_errors'] as $e): ?>
      <div><?php echo htmlspecialchars($e, ENT_QUOTES); ?></div>
    <?php endforeach; ?>
  </div>
  <?php unset($_SESSION['flash_errors']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div style="background:#052e16;color:#bbf7d0;padding:10px;border-radius:8px;margin:12px 0">
    <?php echo htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES); ?>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

    <?php if (!$project): ?>
        <div class="card" style="margin-top:12px">
            <h1 style="margin:0 0 8px">Projet introuvable</h1>
            <p class="muted">Le projet demandé n’existe pas, a été retiré, ou l’URL est invalide.</p>
        </div>
    <?php else: ?>

        <article class="card" style="margin-top:12px">
            <h1 style="margin:0 0 10px"><?php echo htmlspecialchars($project['title'], ENT_QUOTES); ?></h1>

            <div style="font-size:12px;color:#94a3b8;margin-bottom:8px">
                Par
                <a href="#" class="user-link" data-user-id="<?= (int)$project['author_id'] ?>">
                    <?= htmlspecialchars($project['author'], ENT_QUOTES); ?>
                </a>
                • <?= htmlspecialchars($project['published_at'] ? date('d/m/Y H:i', strtotime($project['published_at'])) : '', ENT_QUOTES); ?>
            </div>

            <?php if ($canViewFullContent): ?>
                <p style="color:#cbd5e1;white-space:pre-line">
                    <?php echo htmlspecialchars($project['summary'], ENT_QUOTES); ?>
                </p>
            <?php endif; ?>

            <?php if ($isPaid === 1): ?> <!-- Note : on vérifie $isPaid pour éviter d’afficher le prix sur les projets gratuits même si la base de données contient une valeur non nulle par erreur
            -->     <div style="margin:12px 0;padding:10px 12px;border:1px solid #f59e0b;border-radius:10px;background:#3b2f0e;color:#fcd34d;">
                    🔒 Contenu payant — <?php echo (int)$unlockPointsPrice; ?> points
                </div>
            <?php else: ?>
                <div style="margin:12px 0;padding:10px 12px;border:1px solid #334155;border-radius:10px;background:#0f172a;color:#94a3b8;">
                    Contenu gratuit
                </div>
            <?php endif; ?>

            <?php if (!$canViewFullContent && !empty($images[0])): ?>
                <div style="margin:14px 0">
                    <img
                        src="<?= APP_BASE ?>/uploads/<?= htmlspecialchars($images[0]['path'], ENT_QUOTES) ?>"
                        alt="<?= htmlspecialchars($images[0]['original_name'], ENT_QUOTES) ?>"
                        style="max-width:220px;width:100%;height:auto;border-radius:10px;border:1px solid #334155;object-fit:contain;">
                </div>
            <?php endif; ?>

            <?php if ($canViewFullContent): ?>

                <hr style="border:0;border-top:1px solid #334155;margin:16px 0">

                <div style="white-space:pre-wrap">
                    <?php echo htmlspecialchars($project['body_markdown'], ENT_QUOTES); ?>
                </div>

            <?php else: ?>

                                 <div style="margin:16px 0;padding:14px;border:1px solid #334155;border-radius:12px;background:#0b1220;">
                    <div style="font-weight:700;margin-bottom:8px;">🔒 Contenu verrouillé</div>

                    <div class="muted" style="margin-bottom:6px;">
                        Déverrouille ce contenu pour <?php echo (int)$unlockPointsPrice; ?> points.
                    </div>

                    <div style="font-size:13px;color:#cbd5e1;margin-bottom:10px;">
                        Votre solde : <?php echo (int)$userPointsBalance; ?> points
                    </div>

                    <?php if (!$hasEnoughPoints): ?>
                        <div style="margin-bottom:10px;padding:10px 12px;border:1px solid #7f1d1d;border-radius:10px;background:#2a1313;color:#fecaca;">
                            <div style="font-weight:600;">Solde insuffisant</div>
                            <div>Il vous manque <?php echo (int)$missingPoints; ?> points.</div>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= APP_BASE ?>/project_unlock_pay.php" style="margin-top:10px">
                        <input type="hidden" name="project_id" value="<?= (int)$project['id'] ?>">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

                        <button
                            class="btn"
                            type="submit"
                            <?php echo !$hasEnoughPoints ? 'disabled aria-disabled="true"' : ''; ?>
                            style="<?php echo !$hasEnoughPoints ? 'opacity:.6;cursor:not-allowed;' : ''; ?>"
                        >
                            Déverrouiller pour <?php echo (int)$unlockPointsPrice; ?> points
                        </button>
                    </form>
                </div>

            <?php endif; ?>

            <?php if ($canViewFullContent): ?>
                <div class="toolbar">
                    <button id="likeBtn" class="pill <?php echo $likedByMe ? 'liked' : ''; ?>">
                        <span id="likeIcon">❤</span>
                        <span id="likeCount"><?php echo (int)$likesCount; ?></span>
                    </button>

                    <button id="shareBtn" class="btn">Partager (copier le lien)</button>

                    <?php if ((int)$project['author_id'] === (int)$_SESSION['user_id']): ?>
                        <form method="post"
                              action="<?= APP_BASE ?>/project_delete.php"
                              onsubmit="return confirm('Supprimer ce projet ? Cette action est définitive.');"
                              style="margin-left:auto">
                            <input type="hidden" name="project_id" value="<?= (int)$id ?>">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                            <button class="btn" type="submit" style="background:#7f1d1d">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($canViewFullContent && $images): ?>
                <hr style="border:0;border-top:1px solid #334155;margin:16px 0">

                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px">
                    <?php foreach ($images as $im): ?>
                        <a href="<?= APP_BASE ?>/uploads/<?= htmlspecialchars($im['path'], ENT_QUOTES) ?>"
                           target="_blank" rel="noopener">
                            <img
                                src="<?= APP_BASE ?>/uploads/<?= htmlspecialchars($im['path'], ENT_QUOTES) ?>"
                                alt="<?= htmlspecialchars($im['original_name'], ENT_QUOTES) ?>"
                                style="width:80%;aspect-ratio:1/1;object-fit:contain;border-radius:10px;box-shadow:2px 2px 3px #0b0b0b8c;">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($canViewFullContent && $tags): ?>
                <div style="margin:10px 0; display:flex; flex-wrap:wrap; gap:6px">
                    <?php foreach ($tags as $tg): ?>
                        <a href="<?= tag_url($tg['slug']) ?>"
                           style="font-size:12px;padding:4px 8px;border:1px solid #334155;border-radius:999px;color:#cbd5e1;text-decoration:none;background:#0b1220">
                            #<?= htmlspecialchars($tg['name'], ENT_QUOTES) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endif; ?>
</div>

<?php if ($project && $canViewFullContent): ?>
    <section id="comments" class="card" style="margin-top:14px">
        <h2 style="margin:0 0 8px">Commentaires (<?php echo (int)$commentsCount; ?>)</h2>

        <?php if (!empty($_SESSION['flash_errors'])): ?>
            <div style="background:#7f1d1d;color:#fecaca;padding:10px;border-radius:8px;margin:10px 0">
                <?php foreach ($_SESSION['flash_errors'] as $e): ?>
                    <div><?php echo htmlspecialchars($e, ENT_QUOTES); ?></div>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['flash_errors']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div style="background:#052e16;color:#bbf7d0;padding:10px;border-radius:8px;margin:10px 0">
                <?php echo htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES); unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <?php if ($comments): ?>
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">
                <?php foreach ($comments as $c): ?>
                    <div style="border:1px solid #334155;border-radius:10px;padding:10px;background:#0b1220;position:relative">
                        <div style="font-size:12px;color:#94a3b8;margin-bottom:6px">
                            <?php echo htmlspecialchars($c['pseudo'], ENT_QUOTES); ?> •
                            <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($c['created_at'])), ENT_QUOTES); ?>
                        </div>

                        <div style="white-space:pre-wrap"><?php echo htmlspecialchars($c['body'], ENT_QUOTES); ?></div>

                        <?php if ((int)$c['author_id'] === (int)$_SESSION['user_id']): ?>
                            <form method="post"
                                  action="<?php echo $base; ?>/comment_delete.php"
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
            <label for="comment_body" style="display:block;font-size:13px;color:#cbd5e1;margin-bottom:6px">
                Ajouter un commentaire
            </label>

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
/**
 * Like / Unlike
 * - Appelle like_toggle.php en POST
 * - Réponse attendue : { ok: bool, count: number, liked: bool }
 */
const likeBtn = document.getElementById('likeBtn');
if (likeBtn) {
    likeBtn.addEventListener('click', async () => {
        likeBtn.disabled = true;
        try {
            const res = await fetch('<?php echo $base; ?>/like_toggle.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    id: '<?php echo (int)$id; ?>',
                    csrf: '<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>'
                }).toString()
            });

            const data = await res.json();
            if (data.ok) {
                document.getElementById('likeCount').textContent = data.count;
                likeBtn.classList.toggle('liked', !!data.liked);
            }
        } catch (e) {
            // silencieux pour l’instant
        }
        likeBtn.disabled = false;
    });
}

/**
 * Copie du lien courant
 */
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
                inp.value = url;
                document.body.appendChild(inp);
                inp.select();
                document.execCommand('copy');
                document.body.removeChild(inp);
            }

            toast.style.display = 'block';
            setTimeout(() => toast.style.display = 'none', 1500);
        } catch (e) {
            // silencieux pour l’instant
        }
    });
}
</script>

<div id="userModal" style="position:fixed;inset:0;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center;z-index:50">
    <div style="background:#111827;border:1px solid #334155;border-radius:14px;padding:16px;min-width:280px;max-width:90%">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <strong id="umPseudo" style="font-size:16px">Pseudo</strong>
            <button id="umClose" class="btn" type="button" style="background:#374151;padding:4px 8px">×</button>
        </div>
        <div class="muted">Projets publiés : <span id="umCount">0</span></div>
    </div>
</div>

<script>
const BASE = '<?= APP_BASE ?>';
const modal = document.getElementById('userModal');
const umPseudo = document.getElementById('umPseudo');
const umCount = document.getElementById('umCount');
const umClose = document.getElementById('umClose');

document.addEventListener('click', async (e) => {
    const a = e.target.closest('.user-link');
    if (!a) return;

    e.preventDefault();
    const id = a.getAttribute('data-user-id');

    try {
        const r = await fetch(`${BASE}/user_card.php?id=${encodeURIComponent(id)}`);
        const j = await r.json();

        if (j && j.ok) {
            umPseudo.textContent = j.pseudo;
            umCount.textContent = j.projects_count;
            modal.style.display = 'flex';
        }
    } catch (_) {}
});

umClose.addEventListener('click', () => modal.style.display = 'none');
modal.addEventListener('click', (e) => {
    if (e.target === modal) modal.style.display = 'none';
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') modal.style.display = 'none';
});
</script>

</body>
</html>