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


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  echo json_encode(['ok'=>false,'error'=>'method']);
  exit;
}

/* CSRF pour les formulaires de réponse inline */
/* CSRF pour les formulaires de réponse inline */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

$uid  = (int)$_SESSION['user_id'];
$sent = isset($_GET['sent']); // ?sent=1 => boîte d’envoi, sinon reçus

// avant : Marquer les reçus comme lus à l’ouverture (optionnel) 
//if (!$sent) {
 // if ($upd = $mysqli->prepare('UPDATE messages SET read_at=NOW() WHERE recipient_id=? AND read_at IS NULL')) {
   // $upd->bind_param('i', $uid);
    //$upd->execute();
    //$upd->close(); }}

$upd = $mysqli->prepare(
  'UPDATE messages
     SET read_at = NOW()
   WHERE recipient_id = ?
     AND read_at IS NULL
     AND (deleted_by_recipient IS NULL OR deleted_by_recipient = 0)'
);


/* Requête principale:
*/
// On charge TOUS les messages où je suis émetteur ou destinataire,
// en calculant l'interlocuteur (other_id) côté SQL.cette version qui 
// ignore les messages que l’utilisateur a supprimés 
// (soft delete avec deleted_by_sender et deleted_by_recipient) :
$sql = '
  SELECT
    m.id, m.sender_id, m.recipient_id,
    CASE WHEN m.sender_id = ? THEN m.recipient_id ELSE m.sender_id END AS other_id,
    u.pseudo AS other,
    m.body, m.image_path, m.created_at
  FROM messages m
  JOIN users u
    ON u.id = CASE WHEN m.sender_id = ? THEN m.recipient_id ELSE m.sender_id END
  WHERE
    (
      m.sender_id = ?
      AND (m.deleted_by_sender   IS NULL OR m.deleted_by_sender   = 0)
    )
    OR
    (
      m.recipient_id = ?
      AND (m.deleted_by_recipient IS NULL OR m.deleted_by_recipient = 0)
    )
  ORDER BY other_id, m.created_at ASC
';

$stmt = $mysqli->prepare($sql) ?: exit('Prepare failed: '.$mysqli->error);
$stmt->bind_param('iiii', $uid, $uid, $uid, $uid);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// === GROUPEMENT (colle ceci juste après $rows = …; $stmt->close();) ===
$threads = []; // other_id => ['other'=>pseudo, 'msgs'=>[...], 'last_at'=>timestamp]
foreach ($rows as $r) {
  $oid = (int)$r['other_id'];
  if (!isset($threads[$oid])) {
    $threads[$oid] = ['other'=>$r['other'], 'msgs'=>[], 'last_at'=>0];
  }
  $threads[$oid]['msgs'][] = $r;
  $ts = strtotime($r['created_at']);
  if ($ts > $threads[$oid]['last_at']) $threads[$oid]['last_at'] = $ts;
}
// Trier les threads par dernier message (récents en haut)
usort($threads, fn($a,$b)=> $b['last_at'] <=> $a['last_at']);

?>
<!doctype html>
<meta charset="utf-8">
<title>Mes messages</title>
<!-- CSS -->
<style>
  body{background:#0f172a;color:#e5e7eb;font-family:system-ui}
.wrap{max-width:800px;margin:20px auto}

.msg-card{border:1px solid #334155;border-radius:10px;padding:12px;margin:10px 0;background:#111827;cursor:default}
.msg-head{font-size:12px;color:#94a3b8;display:flex;gap:6px;align-items:center;cursor:pointer}
.chev{margin-left:auto;opacity:.7;transition:transform .15s}
.msg-card[data-open="1"] .chev{transform:rotate(90deg)}

.msg-preview{color:#e5e7eb;margin-top:6px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}

/* Corps repliable avec scroll interne */
.msg-body{max-height:0;overflow:hidden;transition:max-height .2s ease}
.msg-card[data-open="1"] .msg-body{
  max-height:65vh;      /* hauteur max visible */
  overflow:auto;        /* scroll interne */
  padding-right:4px;    /* éviter le recouvrement du texte par la barre */
}


 .msg-head{display:flex;gap:6px;align-items:center}
  .msg-head .spacer{flex:1}
  .thread-del{background:#ef4444;border:none;color:#fff;border-radius:8px;
              padding:4px 8px;cursor:pointer;font-size:12px}
  .thread-del:hover{opacity:.9}


/* Formulaire de réponse */
.reply{margin-top:12px;border-top:1px dashed #334155;padding-top:10px}
.reply textarea{width:100%;padding:10px;border:1px solid #334155;border-radius:10px;background:#0b1220;color:#e5e7eb;resize:vertical}
.reply .row{display:flex;gap:8px;align-items:center;margin-top:8px}
.reply input[type="file"]{color:#94a3b8}
.btn{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:8px 12px;cursor:pointer}
.muted{font-size:12px;color:#94a3b8;margin-top:6px}

/* Bulles */
.msg-out{margin-top:10px;border:1px solid #1f3a8a;background:#0b1220;border-radius:12px;padding:10px}
.msg-out-head{font-size:12px;color:#93c5fd;margin-bottom:6px}
.msg-out-text{white-space:pre-wrap}
.msg-in{
  border:1px solid #334155;
  background:#0b1220;
  border-radius:12px;
  padding:10px;
  margin-top:10px;
}

</style>

<body>
  <div class="wrap">
    <h1><?= $sent ? 'Messages envoyés' : 'Messages reçus' ?></h1>
    <p><a href="?">Reçus</a> · <a href="?sent=1">Envoyés</a></p>


    

    <?php foreach ($threads as $t): ?>
  <?php



    $other    = htmlspecialchars($t['other'], ENT_QUOTES);
    $other_id = (int)$t['msgs'][0]['other_id'];
    $lastDate = htmlspecialchars(date('d/m/Y H:i', $t['last_at']), ENT_QUOTES);

    // Aperçu = 1ère/dernière ligne de la conv (ici la dernière)
    $lastMsg  = end($t['msgs']);
    $preview  = trim((string)$lastMsg['body']);
    $preview  = $preview !== '' ? htmlspecialchars(mb_strimwidth($preview,0,120,'…','UTF-8'),ENT_QUOTES) : '';
  ?>
  <div class="msg-card" data-open="0">

  <!--  <div class="msg-head">Avec <strong>
      /*?= $other ?></strong> — <//?= $lastDate ?>
      <span class="chev">▶</span></div>-->

      <div class="msg-head">
  Avec <strong><?= $other ?></strong> — <?= $lastDate ?>
  <span class="spacer"></span>
  <button type="button" class="thread-del" data-other="<?= $other_id ?>">Supprimer</button>
  <span class="chev">▶</span>
</div>


    <?php if ($preview !== ''): ?><div class="msg-preview"><?= $preview ?></div><?php endif; ?>

    <div class="msg-body">
      <?php foreach ($t['msgs'] as $m): ?>
        <?php
          $mine = ((int)$m['sender_id'] === $uid);
          $dt   = htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at'])), ENT_QUOTES);
          $txt  = trim((string)$m['body']);
          $img  = !empty($m['image_path']) ? (APP_BASE . '/' . $m['image_path']) : null;
        ?>
        <div class="<?= $mine ? 'msg-out' : 'msg-in' ?>" style="<?= $mine?'':'border:1px solid #334155;background:#0b1220;border-radius:12px;padding:10px;margin-top:10px' ?>">
          <div class="msg-out-head"><?= $mine ? 'Moi' : $other ?> — <?= $dt ?></div>
          <?php if ($txt !== ''): ?>
            <div class="msg-out-text"><?= htmlspecialchars($txt, ENT_QUOTES) ?></div>
          <?php endif; ?>
          <?php if ($img): ?>
            <div style="margin-top:8px">
              <a href="<?= htmlspecialchars($img,ENT_QUOTES) ?>" target="_blank" rel="noopener">
                <img src="<?= htmlspecialchars($img,ENT_QUOTES) ?>" style="max-width:100px;max-height:100px;border-radius:8px;display:block">
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <!-- Formulaire de réponse inline (inchangé) -->
      <form class="reply" method="post" enctype="multipart/form-data" data-recipient="<?= $other_id ?>">
        <label class="muted">Répondre à <strong><?= $other ?></strong></label>
        <textarea name="body" rows="3" maxlength="2000" placeholder="Écris ta réponse…"></textarea>
        <div class="row">
          <input type="file" name="image" accept="image/*" capture="environment">
          <button class="btn" type="submit">Envoyer</button>
          <span class="muted reply-status"></span>
        </div>
        <input type="hidden" name="recipient_id" value="<?= $other_id ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf,ENT_QUOTES) ?>">
      </form>
    </div>
  </div>
<?php endforeach; ?>

    <p><a href="<?= APP_BASE ?>/index.php" style="color:#93c5fd">← Retour</a></p>
  </div>


<div id="delModal" style="position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.6);z-index:60">
  <div style="background:#111827;border:1px solid #334155;border-radius:14px;padding:16px;max-width:420px;width:90%">
    <h3 style="margin:0 0 8px">Supprimer la conversation ?</h3>
    <p style="color:#94a3b8">Tous les messages avec <strong id="delUser"></strong> seront supprimés. Action irréversible.</p>
    <div id="delErr" style="display:none;color:#f87171;font-size:13px;margin-bottom:8px"></div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button id="delCancel" class="btn" type="button" style="background:#374151">Annuler</button>
      <button id="delConfirm" class="btn" type="button" style="background:#ef4444">Oui, supprimer</button>
    </div>
  </div>
</div>



<!-- JS -->
<script>
const BASE = '<?= APP_BASE ?>';
const CSRF = '<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>';









/* open modal */
let delCtx = { id:0, card:null, name:'' };
document.addEventListener('click', (e)=>{
  const btn = e.target.closest('.thread-del');
  if (!btn) return;
  e.stopPropagation();               // don't toggle the card
  const card = btn.closest('.msg-card');
  delCtx.id = parseInt(btn.dataset.other, 10);
  delCtx.card = card;
  delCtx.name = card.querySelector('.msg-head strong')?.textContent || '';
  document.getElementById('delUser').textContent = delCtx.name;
  document.getElementById('delErr').style.display = 'none';
  document.getElementById('delModal').style.display = 'flex';
});

/* close helpers */
const delModal = document.getElementById('delModal');
document.getElementById('delCancel').onclick = ()=> delModal.style.display='none';
delModal.addEventListener('click', (e)=>{ if (e.target===delModal) delModal.style.display='none'; });
document.addEventListener('keydown', (e)=>{ if (e.key==='Escape') delModal.style.display='none'; });

/* confirm delete */
document.getElementById('delConfirm').addEventListener('click', async ()=>{
  const err = document.getElementById('delErr');
  err.style.display = 'none';
  try{
    const fd = new FormData();
    fd.append('other_id', String(delCtx.id));
    fd.append('csrf', CSRF);
    const r = await fetch(`${BASE}/messages_delete_thread.php`, { method:'POST', body: fd });
    const j = await r.json();
    if (j.ok){
      if (delCtx.card) delCtx.card.remove();
      delModal.style.display = 'none';
    } else {
      err.textContent = 'Suppression impossible ('+(j.error||'erreur')+')';
      err.style.display = 'block';
    }
  }catch(_){
    err.textContent = 'Erreur réseau.';
    err.style.display = 'block';
  }
});




/* Ouvrir/fermer en cliquant sur l’entête uniquement
   et auto-scroll en bas quand on ouvre */
document.addEventListener('click', (e)=>{
  const head = e.target.closest('.msg-head');
  if(!head) return;
  const card = head.closest('.msg-card');
  card.dataset.open = card.dataset.open === '1' ? '0' : '1';
  if (card.dataset.open === '1') {
    const body = card.querySelector('.msg-body');
    body.scrollTop = body.scrollHeight;
  }
});

/* Envoi inline + MAJ aperçu/date + remonter la card + rester en bas */
document.addEventListener('submit', async (e)=>{
  const form = e.target.closest('form.reply');
  if(!form) return;
  e.preventDefault();

  const status = form.querySelector('.reply-status');
  status.textContent = '';
  const btn = form.querySelector('button[type="submit"]');
  btn.disabled = true;

  try {
    const fd = new FormData(form);
    const hasText = (fd.get('body')||'').toString().trim().length>0;
    const hasImg  = form.querySelector('input[type="file"]').files.length>0;
    if (!hasText && !hasImg) {
      status.style.color = '#f87171';
      status.textContent = 'Écris un message ou choisis une image.';
      btn.disabled = false; return;
    }

    const r = await fetch(`${BASE}/message_send.php`, { method:'POST', body:fd });
    const j = await r.json().catch(()=>({ok:false,error:'bad_json'}));

    if (j.ok) {
      const bodyText  = (fd.get('body')||'').toString().trim();
      const fileInput = form.querySelector('input[type="file"]');
      const file      = fileInput.files[0] || null;

      const out = document.createElement('div');
      out.className = 'msg-out';
      out.innerHTML =
        `<div class="msg-out-head">Moi — à l’instant</div>` +
        (bodyText ? `<div class="msg-out-text">${bodyText.replace(/[&<>"']/g, s => (
          {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]))}</div>` : '');
      if (file){
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.src = url; img.onload = () => URL.revokeObjectURL(url);
        out.appendChild(img);
      }
      form.before(out);

      // rester scrolé en bas
      const body = form.closest('.msg-card').querySelector('.msg-body');
      body.scrollTop = body.scrollHeight;

      // MAJ aperçu/date + remonter la card
      const card = form.closest('.msg-card');
      const head = card.querySelector('.msg-head');
      const previewDiv = card.querySelector('.msg-preview') ||
                         card.insertBefore(document.createElement('div'), card.querySelector('.msg-body'));
      previewDiv.className = 'msg-preview';
      previewDiv.textContent = bodyText || '[image]';
      const now = new Date();
      const dateStr = now.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit',year:'numeric'})+
                      ' '+now.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
const pat = /— .*?<span class="chev">/;
head.innerHTML = pat.test(head.innerHTML)
  ? head.innerHTML.replace(pat, `— ${dateStr} <span class="chev">`)
  : head.innerHTML.replace('<span class="chev">', `— ${dateStr} <span class="chev">`);
      const list = card.parentElement;
      const first = list.querySelector('.msg-card');
      if (first && card !== first) list.insertBefore(card, first);

      status.style.color = '#34d399';
      status.textContent = 'Envoyé ✅';
      form.reset();
      form.querySelector('textarea').focus();
      if (typeof refreshBadge === 'function') refreshBadge();
    } else {
      status.style.color = '#f87171';
      status.textContent = 'Échec ('+(j.error||'erreur')+')';
    }
  } catch(_) {
    status.style.color = '#f87171';
    status.textContent = 'Erreur réseau.';
  } finally {
    btn.disabled = false;
  }
});
</script>

</body>
