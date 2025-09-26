<?php
/**
 * profile.php — Page profil d’un utilisateur
 *
 * Responsabilités :
 * - Exiger une session authentifiée (require_login()).
 * - Charger un utilisateur par son id et afficher son profil (pseudo + nb de projets publiés).
 * - Lister ses projets publiés, avec pagination serveur.
 * - Afficher une miniature (cover_thumb) si disponible.
 *
 * Notes / TODO éventuels :
 * - Le paramètre id est « borné » à 1 via max(1, …). Si id=0/non numérique, on tombera
 *   par défaut sur l’id 1 puis 404 si inexistant. On peut préférer : $uid = (int)($_GET['id'] ?? 0);
 *   puis 404 si $uid <= 0.
 * - Penser à un layout commun (header/footer) et à un composant pager réutilisable.
 * - Index conseillés : law_projects(author_id, status, published_at), project_images(project_id, id).
 */

declare(strict_types=1);
session_start();

require __DIR__.'/config.php'; // Constantes globales (APP_BASE, helpers comme slugify(), etc.)
require __DIR__.'/db.php';     // Connexion MySQLi ($mysqli)
require __DIR__.'/auth.php';   // Auth helpers : require_login()
require_login();               // Bloque l’accès si non connecté

function sanitize_upload_relpath(?string $v): ?string {
  if ($v === null) return null;
  $v = str_replace("\0", '', trim($v));     // retire les NUL
  $v = str_replace('\\', '/', $v);          // normalise
  $v = ltrim($v, '/');                      // pas d'absolu
  if ($v === '' || strpos($v, '..') !== false) return null;

  // chaque segment = lettres/chiffres . _ - ; pas de segment caché (commençant par .)
  $parts = explode('/', $v);
  foreach ($parts as $p) {
    if ($p === '' || $p[0] === '.') return null;
    if (!preg_match('/\A[0-9A-Za-z._-]{1,128}\z/', $p)) return null;
  }

  // (optionnel) forcer l’extension
  if (!preg_match('/\.(jpe?g|png|webp|gif)\z/i', end($parts))) return null;

  return implode('/', $parts);
}



// -------------------------------
// Paramètres d’entrée & pagination
// -------------------------------

// Id utilisateur demandé dans l’URL. max(1, …) évite 0, mais voir la note en tête de fichier.
$uid  = max(1, (int)($_GET['id'] ?? 0));

// Pagination simple côté serveur
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 10;                    // Taille de page fixe (10 éléments)
$off  = ($page-1)*$per;        // Décalage (OFFSET)

// ------------------------------------
// Lecture du profil + nombre de projets
// ------------------------------------

// Récupération de l’utilisateur (id, pseudo). On s’arrête en 404 si introuvable.
$u = null; $count = 0;
$stmt = $mysqli->prepare('SELECT id, pseudo FROM users WHERE id=? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$u) { // Garde-fou : utilisateur inconnu
  http_response_code(404);
  echo 'Utilisateur introuvable !';
  exit;
}

// Compte des projets publiés de cet utilisateur
$stmt = $mysqli->prepare("
  SELECT COUNT(*)
  FROM law_projects
  WHERE author_id=? AND status='published'
");
$stmt->bind_param('i', $uid);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

// ------------------------------------
// Liste paginée des projets (avec cover)
// ------------------------------------
//
// On sélectionne la première miniature disponible via une sous-requête ordonnée par i.id ASC.
// DISTINCT n’est pas nécessaire ici (pas de JOIN tags), chaque ligne = un projet.
//
// Les champs affichés sont échappés dans le HTML (htmlspecialchars) pour éviter les XSS.

$sql = "
  SELECT
    p.id,
    p.title,
    p.summary,
    p.published_at,
    (
      SELECT thumb_path
      FROM project_images i
      WHERE i.project_id = p.id
        AND i.thumb_path IS NOT NULL
      ORDER BY i.id ASC
      LIMIT 1
    ) AS cover_thumb
  FROM law_projects p
  WHERE p.author_id = ? AND p.status = 'published'
  ORDER BY p.published_at DESC
  LIMIT ? OFFSET ?
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('iii', $uid, $per, $off);
$stmt->execute();
$res = $stmt->get_result();
$projects = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcul du nombre total de pages d’après le total des projets publiés
$totalPages = max(1, (int)ceil($count / $per));
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profil — <?= htmlspecialchars($u['pseudo'],ENT_QUOTES) ?></title>
<style>
  /* Styles locaux (peu de CSS pour rester autonome ; idéalement à factoriser) */
  body{background:#0f172a;color:#e5e7eb;margin:0;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}
  .wrap{max-width:900px;margin:24px auto;padding:0 16px}
  .card{background:#111827;border:1px solid #334155;border-radius:14px;padding:16px;margin-bottom:14px}
  .btn{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:8px 12px;text-decoration:none}
  .meta{font-size:12px;color:#94a3b8}
</style>
</head>
<body>
  <div class="wrap">
    <!-- En-tête du profil (pseudo + compteur + retour) -->
    <div class="card">
      <h1 style="margin:0 0 6px"><?= htmlspecialchars($u['pseudo'],ENT_QUOTES) ?></h1>
      <div class="meta"><?= (int)$count ?> projet(s) publié(s)</div>
      <div style="margin-top:10px">
        <a class="btn" href="<?= APP_BASE ?>/index.php">&larr; Retour au feed</a>
      </div>
    </div>

    <!-- Boucle projets : titre, date, résumé, bouton Lire, et vignette si présente -->
    <?php foreach($projects as $p): $slug = slugify($p['title']); ?>
      <article
        class="card"
        style="display:grid;grid-template-columns:1fr 64px;gap:12px;align-items:start">
        <div>
          <h3 style="margin:0 0 6px"><?= htmlspecialchars($p['title'],ENT_QUOTES) ?></h3>
          <div class="meta">
            Publié le <?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['published_at']??'')),ENT_QUOTES) ?>
          </div>
          <p><?= htmlspecialchars($p['summary'],ENT_QUOTES) ?></p>
          <a class="btn"
             href="<?= APP_BASE ?>/p/<?= (int)$p['id'] ?>-<?= htmlspecialchars($slug,ENT_QUOTES) ?>">
             Lire
          </a>
        </div>

     <?php
  $thumb = sanitize_upload_basename($p['cover_thumb'] ?? null);
  if ($thumb):
?>
  <a href="<?= APP_BASE ?>/p/<?= (int)$p['id'] ?>-<?= htmlspecialchars($slug,ENT_QUOTES) ?>"
     style="border-radius:12px;overflow:hidden;box-shadow:0 6px 16px #0003">
    <img
      src="<?= APP_BASE ?>/uploads/<?= rawurlencode($thumb) ?>"
      alt=""
      style="width:64px;height:64px;object-fit:cover;display:block">
  </a>
<?php endif; ?>

      </article>
    <?php endforeach; ?>

    <!-- Pagination (Préc./Suiv.) si plusieurs pages -->
    <?php if($totalPages>1): ?>
      <div class="card" style="display:flex;gap:10px;justify-content:center">
        <?php if($page>1): ?>
          <a class="btn"
             href="<?= APP_BASE ?>/profile.php?id=<?= (int)$uid ?>&page=<?= $page-1 ?>">
            &laquo; Préc.
          </a>
        <?php endif; ?>

        <span class="meta" style="align-self:center">
          Page <?= $page ?> / <?= $totalPages ?>
        </span>

        <?php if($page<$totalPages): ?>
          <a class="btn"
             href="<?= APP_BASE ?>/profile.php?id=<?= (int)$uid ?>&page=<?= $page+1 ?>">
            Suiv. &raquo;
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
