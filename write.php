<?php
// write.php
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

// CSRF token
if (empty($_SESSION['csrf'])) { // Génère un token CSRF unique par session (16 octets aléatoires, hexadécimal)
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf']; // Ce token doit être inclus dans le formulaire et vérifié à la réception (save_project.php) pour éviter les attaques CSRF.
?>
<!doctype html>
<html lang="fr">
<head>
  <meta name="robots" content="noindex, nofollow">

<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Écrire un projet — Loi Direct</title>
<style>
  :root{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}
  body{background:#0f172a;color:#e5e7eb;margin:0}
  header{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#111827;position:sticky;top:0}
  a{color:#93c5fd;text-decoration:none}
  .wrap{max-width:1100px;margin:18px auto;padding:0 16px}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  .card{background:#111827;border:1px solid #334155;border-radius:14px;padding:16px}
  label{display:block;font-size:13px;color:#cbd5e1;margin:10px 0 6px}
  input[type=text], textarea{
    width:100%;padding:12px 14px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;outline:none
  }
  textarea{min-height:340px;resize:vertical}
  .muted{font-size:12px;color:#94a3b8}
  .count{font-size:12px;margin-left:6px;color:#94a3b8}
  .btn{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;text-decoration:none;cursor:pointer}
  .actions{display:flex;gap:8px;margin-top:14px}
  .preview h1,.preview h2,.preview h3{margin:8px 0}
  .preview p{line-height:1.5}
  .preview code{background:#0b1220;padding:2px 6px;border-radius:6px}
  .preview pre{white-space:pre-wrap;background:#0b1220;padding:10px;border-radius:10px;border:1px solid #334155}
  .preview ul, .preview ol{padding-left:20px}
  .taghint{font-size:12px;color:#94a3b8}
</style>
</head>
<body>
<header>
  <div><a href="feed.php">&larr; Retour</a></div>
  <strong>Loi Direct — Rédaction</strong>
  <div>Connecté en tant que <span style="color:#e5e7eb"><?php echo htmlspecialchars($_SESSION['pseudo'],ENT_QUOTES); ?></span></div>
</header>

<main class="wrap">
  <form id="form" method="post" action="save_project.php" enctype="multipart/form-data" novalidate>
    <div class="row">
      <!-- Editeur -->
      <section class="card">
        <h2 style="margin:0 0 6px">Rédiger</h2>
        <label for="title">Titre <span class="count" id="ctTitle">0/180</span></label>
        <input type="text" id="title" name="title" maxlength="180" required placeholder="Ex. Encadrement des frais bancaires">

        <label for="summary">Objet (résumé) <span class="count" id="ctSummary">0/280</span></label>
        <textarea id="summary" name="summary" maxlength="280" placeholder="Objet bref : intention, périmètre... (≤ 280 caractères)"></textarea>

        <label for="body">Texte (Markdown léger accepté) <span class="muted">(max ~30 000 car.)</span></label>
        <textarea id="body" name="body" placeholder="# Titre de section
## Sous-titre
Texte libre…

- Listes
- **Gras**, *Italique*
- `code`
[Un lien](https://exemple.org)" maxlength="500000"></textarea>



<div style="margin-top:12px">
  <label style="display:block;margin-bottom:6px">Images (max 5, JPG/PNG/WebP, 5 Mo chacun)</label>
  <input type="file" name="images[]" id="images" accept="image/*" multiple>
  <div id="imgPreview" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px"></div>
</div>




        <label for="tags">Tags (optionnels)</label>
        <input type="text" id="tags" name="tags"  maxlength="10" placeholder="ex: fiscalité, banques, (10 caractères ! ) ">
      
        <div class="taghint">Sépare par des virgules. Max 5 tags, 10 caractères chacun.</div>

        <div class="actions">

          <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
          <button class="btn" type="submit">Publier</button>
          <button class="btn" type="button" id="btnPreview">Actualiser la prévisualisation</button>
        </div>

        <div class="muted" style="margin-top:8px">
          Rappels : titre ≤ 180, objet ≤ 280, tags ≤ 5. Les images ne sont pas prises en charge pour l’instant.
        </div>



<hr style="margin:16px 0;border:0;border-top:1px solid #334155">

<h3 style="margin:0 0 6px">Monétisation</h3>

<label style="display:flex;align-items:center;gap:8px;cursor:pointer">
  <input type="checkbox" id="is_paid" name="is_paid" value="1">
  Contenu payant
</label>

<div id="priceWrap" style="display:none;margin-top:10px">
  <label for="unlock_points_price">Prix en points</label>
  <input 
    type="number" 
    id="unlock_points_price" 
    name="unlock_points_price" 
    min="1" 
    placeholder="ex: 50"
  >
  <div class="muted">Définit le prix pour débloquer ce contenu</div>
</div>




      </section>

      <!-- Preview -->
      <aside class="card">
        <h2 style="margin:0 0 6px">Prévisualisation</h2>
        <div class="muted">Rendu approximatif côté client (le serveur sécurise le contenu à l’affichage).</div>
        <article id="preview" class="preview" style="margin-top:10px">
          <h1 id="pv-title" style="margin:0">—</h1>
          <p id="pv-summary" class="muted">L’objet apparaîtra ici.</p>
          <hr style="border:0;border-top:1px solid #334155;margin:12px 0">
          <div id="pv-body" class="muted">Le contenu apparaîtra ici…</div>
          <div id="pv-tags" class="muted" style="margin-top:10px"></div>
        </article>
      </aside>
    </div>
  </form>
</main>

<script>
// Compteurs
const titleEl = document.getElementById('title');
const summaryEl = document.getElementById('summary');
const bodyEl = document.getElementById('body');
const tagsEl = document.getElementById('tags');
const ctTitle = document.getElementById('ctTitle');
const ctSummary = document.getElementById('ctSummary');

// Toggle prix 
const isPaidCheckbox = document.getElementById('is_paid'); // case à cocher pour activer le contenu payant
const priceWrap = document.getElementById('priceWrap'); // conteneur du champ de prix, affiché uniquement si le contenu payant est activé

function togglePrice() {
  priceWrap.style.display = isPaidCheckbox.checked ? 'block' : 'none';
}

isPaidCheckbox.addEventListener('change', togglePrice);
togglePrice();





function updateCounts(){
  ctTitle.textContent = `${titleEl.value.length}/180`;
  ctSummary.textContent = `${summaryEl.value.length}/280`;
}
['input','change'].forEach(evt=>{
  titleEl.addEventListener(evt, updateCounts);
  summaryEl.addEventListener(evt, updateCounts);
});
updateCounts();

// Mini renderer Markdown (basique) pour la preview
function mdRender(src){
  let html = src
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

  // Headings
  html = html.replace(/^### (.*)$/gm, '<h3>$1</h3>');
  html = html.replace(/^## (.*)$/gm, '<h2>$1</h2>');
  html = html.replace(/^# (.*)$/gm, '<h1>$1</h1>');

  // Bold / italic / code inline
  html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
  html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
  html = html.replace(/`([^`]+?)`/g, '<code>$1</code>');

  // Links
  html = html.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');

  // Lists
  html = html.replace(/(?:^|\n)[ \t]*- (.*)(?=\n|$)/g, match => match.replace(/^- /gm,'• ').replace(/^/,'<div>').replace(/$/,'</div>'));
  html = html.replace(/\n{2,}/g, '\n\n');
  html = html.split(/\n\n/).map(p=>{
    if (p.match(/^<h[1-3]>/)) return p;
    if (p.startsWith('<div>')) return p; // liste transformée
    return `<p>${p.replace(/\n/g,'<br>')}</p>`;
  }).join('');
  return html;
}

function refreshPreview(){
  document.getElementById('pv-title').textContent = titleEl.value || '—';
  document.getElementById('pv-summary').textContent = summaryEl.value || 'L’objet apparaîtra ici.';
  document.getElementById('pv-body').innerHTML = bodyEl.value ? mdRender(bodyEl.value) : 'Le contenu apparaîtra ici…';

  const tags = (tagsEl.value || '')
    .split(',')
    .map(t=>t.trim())
    .filter(Boolean)
    .slice(0,5);
  document.getElementById('pv-tags').textContent = tags.length ? 'Tags : ' + tags.join(', ') : '';
}
['input','change'].forEach(evt=>{
  titleEl.addEventListener(evt, refreshPreview);
  summaryEl.addEventListener(evt, refreshPreview);
  bodyEl.addEventListener(evt, refreshPreview);
  tagsEl.addEventListener(evt, refreshPreview);
});
document.getElementById('btnPreview').addEventListener('click', refreshPreview);
refreshPreview();
</script>


<script>
const input = document.getElementById('images');
const preview = document.getElementById('imgPreview');
input?.addEventListener('change', () => {
  preview.innerHTML = '';
  const files = Array.from(input.files || []).slice(0, 5);
  files.forEach(f => {
    const okType = /^image\/(jpeg|png|webp)$/i.test(f.type);
    const okSize = f.size <= (5 * 1024 * 1024);
    if (!okType || !okSize) return;
    const img = document.createElement('img');
    img.src = URL.createObjectURL(f);
    img.style.maxWidth = '120px';
    img.style.height = 'auto';
    img.style.border = '1px solid #334155';
    img.style.borderRadius = '8px';
    preview.appendChild(img);
  });
});
</script>








</body>
</html>