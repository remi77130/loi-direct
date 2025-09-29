<?php
/* messages_inbox.php — Messagerie avec réponse inline (texte + image)
 * Hypothèses:
 * - Table messages(sender_id, recipient_id, body, image_path, created_at, read_at)
 * - message_send.php gère CSRF + texte et/ou image (déjà en place chez toi)
 *Sécurité : require_login(), CSRF token, échappement HTML, pas de données sensibles dans le DOM.

UX : carte pliable, aperçu 2 lignes, réponse inline sans recharger, 
support image + photo (accept="image/*" capture="environment").

Compat : pas de dépendances externes, pur PHP/MySQLi + JS natif.

Back-end : le recipient_id pour la réponse est calculé 
selon la vue (reçus/envoyés) et injecté dans chaque formulaire.*/

declare(strict_types=1);
session_start();

require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require __DIR__.'/config.php';
require_login();

/* CSRF pour les formulaires de réponse inline */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

$uid  = (int)$_SESSION['user_id'];
$sent = isset($_GET['sent']); // ?sent=1 => boîte d’envoi, sinon reçus

/* Marquer les reçus comme lus à l’ouverture (optionnel) */
if (!$sent) {
  if ($upd = $mysqli->prepare('UPDATE messages SET read_at=NOW() WHERE recipient_id=? AND read_at IS NULL')) {
    $upd->bind_param('i', $uid);
    $upd->execute();
    $upd->close();
  }
}

/* Requête principale:
 * - En “reçus”, l’“autre” = sender (other_id = sender_id)
 * - En “envoyés”, l’“autre” = recipient (other_id = recipient_id)
 * On récupère aussi image_path pour afficher la vignette éventuelle
 */
$sql = $sent
  ? 'SELECT m.id,
            m.recipient_id   AS other_id,
            u.pseudo         AS other,
            m.body, m.image_path, m.created_at
       FROM messages m
       JOIN users u ON u.id=m.recipient_id
      WHERE m.sender_id=?
      ORDER BY m.created_at DESC
      LIMIT 100'
  : 'SELECT m.id,
            m.sender_id      AS other_id,
            u.pseudo         AS other,
            m.body, m.image_path, m.created_at
       FROM messages m
       JOIN users u ON u.id=m.sender_id
      WHERE m.recipient_id=?
      ORDER BY m.created_at DESC
      LIMIT 100';

$stmt = $mysqli->prepare($sql) ?: exit('Prepare failed: '.$mysqli->error);
$stmt->bind_param('i', $uid);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<!doctype html>
<meta charset="utf-8">
<title>Mes messages</title>

<style>
  body{background:#0f172a;color:#e5e7eb;font-family:system-ui}
  .wrap{max-width:800px;margin:20px auto}
  .msg-card{border:1px solid #334155;border-radius:10px;padding:12px;margin:10px 0;background:#111827;cursor:pointer}
  .msg-head{font-size:12px;color:#94a3b8;display:flex;gap:6px;align-items:center}
  .msg-preview{color:#e5e7eb;margin-top:6px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .msg-body{overflow:hidden;max-height:0;transition:max-height .2s ease}
  .msg-card[data-open="1"] .msg-body{max-height:5000px} /* assez grand pour tout contenu */
  .msg-img img{max-width:220px;max-height:220px;border-radius:8px;display:block}
  .chev{margin-left:auto;opacity:.7;transition:transform .15s}
  .msg-card[data-open="1"] .chev{transform:rotate(90deg)}
  /* Formulaire de réponse inline */
  .reply{margin-top:12px;border-top:1px dashed #334155;padding-top:10px}
  .reply textarea{width:100%;padding:10px;border:1px solid #334155;border-radius:10px;background:#0b1220;color:#e5e7eb;resize:vertical}
  .reply .row{display:flex;gap:8px;align-items:center;margin-top:8px}
  .reply input[type="file"]{color:#94a3b8}
  .btn{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:8px 12px;cursor:pointer}
  .muted{font-size:12px;color:#94a3b8;margin-top:6px}

  /* …tes styles existants… */

  /* Bulle du message envoyé localement */
  .msg-out{margin-top:10px;border:1px solid #1f3a8a;background:#0b1220;border-radius:12px;padding:10px}
  .msg-out-head{font-size:12px;color:#93c5fd;margin-bottom:6px}
  .msg-out-text{white-space:pre-wrap}
  .msg-out img{max-width:220px;max-height:220px;border-radius:8px;display:block;margin-top:8px}
</style>



</style>

<body>
  <div class="wrap">
    <h1><?= $sent ? 'Messages envoyés' : 'Messages reçus' ?></h1>
    <p><a href="?">Reçus</a> · <a href="?sent=1">Envoyés</a></p>

    <?php foreach ($rows as $m): ?>
      <?php
        /* Préparation des champs affichés (toujours échapper) */
        $who   = $sent ? 'À' : 'De';
        $date  = htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at'])),ENT_QUOTES);
        $other = htmlspecialchars($m['other'],ENT_QUOTES);
        $bodyFull = trim((string)$m['body']);
        $preview  = $bodyFull !== '' ? htmlspecialchars(mb_strimwidth($bodyFull, 0, 120, '…', 'UTF-8'),ENT_QUOTES) : '';
        $img = !empty($m['image_path']) ? (APP_BASE . '/' . $m['image_path']) : null;
        $other_id = (int)$m['other_id']; // Destinataire pour la réponse inline
      ?>
      <div class="msg-card" data-open="0">
        <!-- En-tête cliquable pour plier/déplier -->
        <div class="msg-head">
          <?= $who ?> <strong><?= $other ?></strong> — <?= $date ?>
          <span class="chev">▶</span>
        </div>

        <!-- Aperçu (2 lignes) quand la carte est fermée -->
        <?php if ($preview !== ''): ?>
          <div class="msg-preview"><?= $preview ?></div>
        <?php endif; ?>

        <!-- Corps complet: texte + image + formulaire de réponse -->
        <div class="msg-body">
          <?php if ($bodyFull !== ''): ?>
            <div style="white-space:pre-wrap;margin-top:6px">
              <?= htmlspecialchars($bodyFull,ENT_QUOTES) ?>
            </div>
          <?php endif; ?>

          <?php if ($img): ?>
            <div class="msg-img" style="margin-top:8px">
              <a href="<?= htmlspecialchars($img,ENT_QUOTES) ?>" target="_blank" rel="noopener">
                <img src="<?= htmlspecialchars($img,ENT_QUOTES) ?>" alt="Image envoyée">
              </a>
            </div>
          <?php endif; ?>

          <!-- Formulaire de réponse inline (texte et/ou image) -->
          <form class="reply" method="post" enctype="multipart/form-data" data-recipient="<?= $other_id ?>">
            <label class="muted">Répondre à <strong><?= $other ?></strong></label>
            <textarea name="body" rows="3" maxlength="2000" placeholder="Écris ta réponse…"></textarea>
            <div class="row">
              <!-- accept + capture => autorise prise de photo sur mobile -->
              <input type="file" name="image" accept="image/*" capture="environment">
              <button class="btn" type="submit">Envoyer</button>
              <span class="muted reply-status"></span>
            </div>
            <!-- Champs requis par message_send.php -->
            <input type="hidden" name="recipient_id" value="<?= $other_id ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf,ENT_QUOTES) ?>">
          </form>
        </div>
      </div>
    <?php endforeach; ?>

    <p><a href="<?= APP_BASE ?>/index.php" style="color:#93c5fd">← Retour</a></p>
  </div>

<script>
/* BASE = préfixe des URLs (ex: /loi) */
const BASE = '<?= APP_BASE ?>';

/* Ouvrir/fermer une carte en cliquant dans la zone “non interactive”.
 * On ignore les clics sur liens, boutons, inputs, etc. pour éviter les conflits.
 */
document.addEventListener('click', (e)=>{
  const card = e.target.closest('.msg-card');
  if(!card) return;
  if (e.target.closest('a,button,img,input,textarea,select,label,form')) return;
  card.dataset.open = card.dataset.open === '1' ? '0' : '1';
});

/* Soumission AJAX des réponses inline (texte et/ou image) vers message_send.php */
document.addEventListener('submit', async (e)=>{
  const form = e.target.closest('form.reply');
  if(!form) return;                 // ne traiter que nos formulaires .reply
  e.preventDefault();

  const status = form.querySelector('.reply-status');
  status.textContent = '';
  const btn = form.querySelector('button[type="submit"]');
  btn.disabled = true;

  try{
    const fd = new FormData(form);

    // Garde-fou client: exiger du texte OU une image
    const hasText = (fd.get('body')||'').toString().trim().length>0;
    const hasImg  = form.querySelector('input[type="file"]').files.length>0;
    if(!hasText && !hasImg){
      status.style.color = '#f87171';
      status.textContent = 'Écris un message ou choisis une image.';
      btn.disabled = false;
      return;
    }

    const r = await fetch(`${BASE}/message_send.php`, { method:'POST', body:fd });
    const j = await r.json().catch(()=>({ok:false,error:'bad_json'}));
if (j.ok){
  // on capture le texte + le fichier AVANT reset()
  const bodyText = (fd.get('body')||'').toString().trim();
  const fileInput = form.querySelector('input[type="file"]');
  const file = fileInput.files[0] || null;

  // Crée une bulle "sortante" et l'insère juste avant le formulaire
  const out = document.createElement('div');
  out.className = 'msg-out';
  out.innerHTML =
    `<div class="msg-out-head">Moi — à l’instant</div>` +
    (bodyText ? `<div class="msg-out-text">${bodyText.replace(/[&<>"']/g, s => (
        {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]))}</div>` : '');

  // Aperçu local de l'image (si jointe) via URL.createObjectURL
  if (file){
    const url = URL.createObjectURL(file);
    const img = new Image();
    img.src = url;
    img.onload = () => URL.revokeObjectURL(url); // libère mémoire
    out.appendChild(img);
  }

  // Insère la bulle avant le <form> (dans le même "thread")
  form.before(out);

  // Feedback + nettoyage
  status.style.color = '#34d399';
  status.textContent = 'Envoyé ✅';
  form.reset();

  if (typeof refreshBadge === 'function') refreshBadge();
} else {
  status.style.color = '#f87171';
  status.textContent = 'Échec ('+(j.error||'erreur')+')';
}

</script>
</body>
