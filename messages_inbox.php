<?php
/* messages_inbox.php — Messagerie (threads) avec réponse inline (texte + image)
 * - Table messages(sender_id, recipient_id, body, image_path, created_at, read_at, deleted_by_sender, deleted_by_recipient)
 * - message_send.php gère CSRF + texte et/ou image
 * - messages_delete_thread.php gère la suppression d’un thread (soft-delete côté user)
 */

declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/config.php';

function media_url(?string $p): ?string {
  if (!$p) return null;
  $p = ltrim($p, '/');
  if (preg_match('~^https?://~i', $p)) return $p;                // déjà absolu
  if (str_starts_with($p, 'uploads/')) $p = substr($p, 8);       // legacy
  return rtrim(APP_BASE, '/') . '/uploads/' . $p;                // /uploads/…
}

/* CSRF pour les formulaires de réponse inline */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

$uid  = (int)($_SESSION['user_id'] ?? 0);
$sent = isset($_GET['sent']); // ?sent=1 => boîte d’envoi, sinon reçus

/* Marquer comme lus (en ignorant ce que j’ai "soft-deleted") */
if (!$sent) {
  if ($upd = $mysqli->prepare(
    'UPDATE messages
        SET read_at = NOW()
      WHERE recipient_id = ?
        AND read_at IS NULL
        AND (deleted_by_recipient IS NULL OR deleted_by_recipient = 0)'
  )) {
    $upd->bind_param('i', $uid);
    $upd->execute();
    $upd->close();
  }
}

/* Charger tous les messages (des 2 sens), en calculant l’interlocuteur,
   et en ignorant ceux que moi j’ai soft-supprimés */
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
  ORDER BY other_id, m.created_at ASC';

$stmt = $mysqli->prepare($sql) ?: exit('Prepare failed: ' . $mysqli->error);
$stmt->bind_param('iiii', $uid, $uid, $uid, $uid);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* Groupement par interlocuteur */
$threads = []; // other_id => ['other'=>pseudo, 'msgs'=>[...], 'last_at'=>timestamp]
foreach ($rows as $r) {
  $oid = (int)$r['other_id'];
  if (!isset($threads[$oid])) $threads[$oid] = ['other' => $r['other'], 'msgs' => [], 'last_at' => 0];
  $threads[$oid]['msgs'][] = $r;
  $ts = strtotime($r['created_at']);
  if ($ts > $threads[$oid]['last_at']) $threads[$oid]['last_at'] = $ts;
}
usort($threads, fn($a, $b) => $b['last_at'] <=> $a['last_at']);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">

  <link rel="stylesheet" href="<?= APP_BASE ?>/styles/tokens.css?v=1">
  <link rel="stylesheet" href="<?= APP_BASE ?>/styles/messages_inbox.css?v=1">

  <title>Mes messages</title>
</head>
<body>
  <div class="wrap">
    <h1><?= $sent ? 'Messages envoyés' : 'Messages reçus' ?></h1>
    <p><a href="?">Reçus</a> · <a href="?sent=1">Envoyés</a></p>

    <?php foreach ($threads as $t): ?>
      <?php
        $otherRaw  = (string)$t['other'];
        $other     = htmlspecialchars($otherRaw, ENT_QUOTES);
        $other_id  = (int)$t['msgs'][0]['other_id'];
        $lastDate  = htmlspecialchars(date('d/m/Y H:i', $t['last_at']), ENT_QUOTES);

        // Aperçu = dernier message du thread
        $lastMsg = end($t['msgs']);
        $preview = trim((string)$lastMsg['body']);
        $preview = $preview !== '' ? htmlspecialchars(mb_strimwidth($preview, 0, 120, '…', 'UTF-8'), ENT_QUOTES) : '';
      ?>

      <div class="msg-card" data-open="0" data-other="<?= $other_id ?>">
        <div class="msg-head" role="button" tabindex="0" aria-expanded="false">
          <div class="thread-title">
            Avec <strong class="thread-user"><?= $other ?></strong>
            <span class="thread-date"><?= $lastDate ?></span>
          </div>

          <span class="spacer"></span>

          <button type="button" class="thread-del" data-other="<?= $other_id ?>">Supprimer</button>
          <span class="chev">›</span>
        </div>

        <?php if ($preview !== ''): ?>
          <div class="msg-preview"><?= $preview ?></div>
        <?php endif; ?>

        <div class="msg-body">
          <?php foreach ($t['msgs'] as $m): ?>
            <?php
              $mine = ((int)$m['sender_id'] === $uid);
              $dt   = htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at'])), ENT_QUOTES);
              $txt  = trim((string)$m['body']);
              $img  = media_url($m['image_path'] ?? null);
            ?>
            <div class="<?= $mine ? 'msg-out' : 'msg-in' ?>">
              <div class="msg-out-head"><?= $mine ? 'Moi' : $other ?> — <?= $dt ?></div>

              <?php if ($txt !== ''): ?>
                <div class="msg-out-text"><?= htmlspecialchars($txt, ENT_QUOTES) ?></div>
              <?php endif; ?>

<?php if ($img): ?> <!-- Affichage image ou vidéo, gardes image_path comme “média”, et tu choisis <video> si extension vidéo. -->
  <?php
    $path = (string)parse_url($img, PHP_URL_PATH);
    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $isVideo = in_array($ext, ['mp4','webm','ogv','ogg'], true); // ogv chez toi, parfois ogg
  ?>
  <div class="msg-img">
    <?php if ($isVideo): ?>
      <video class="msg-video" controls playsinline preload="metadata"> <!-- preload metadata pour ne pas charger toute la vidéo d’un coup -->
        <source src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" type="video/<?= $ext === 'ogv' ? 'ogg' : $ext ?>">
      </video>
    <?php else: ?>
      <a href="<?= htmlspecialchars($img, ENT_QUOTES) ?>" target="_blank" rel="noopener">
        <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="">
      </a>
    <?php endif; ?>
  </div>
<?php endif; ?>



            </div>
          <?php endforeach; ?>

          <!-- Formulaire de réponse inline -->
          <form class="reply" method="post" enctype="multipart/form-data" data-recipient="<?= $other_id ?>">
            <p class="muted">Répondre à <strong><?= $other ?></strong></p>
            <textarea name="body" rows="3" maxlength="2000" placeholder="Écris ta réponse…"></textarea>
            <div class="row">
<input type="file" name="image" accept="image/*,video/mp4,video/webm,video/ogg">
              <button class="btn" type="submit">Envoyer</button>
              <span class="muted reply-status"></span>
            </div>
            <input type="hidden" name="recipient_id" value="<?= $other_id ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
          </form>
        </div>
      </div>
    <?php endforeach; ?>

    <p><a class="backlink" href="<?= APP_BASE ?>/index.php">← Retour</a></p>
  </div>

  <!-- Modal suppression thread -->
  <div id="delModal" class="delModal" aria-hidden="true">
    <div class="delBox" role="dialog" aria-modal="true" aria-labelledby="delTitle">
      <h3 id="delTitle" class="delTitle">Supprimer la conversation ?</h3>
      <p class="delText">Tous les messages avec <strong id="delUser"></strong> seront supprimés. Action irréversible.</p>
      <div id="delErr" class="delErr"></div>
      <div class="delActions">
        <button id="delCancel" class="btn btn-muted" type="button">Annuler</button>
        <button id="delConfirm" class="btn btn-danger" type="button">Oui, supprimer</button>
      </div>
    </div>
  </div>

<script>
const BASE = '<?= APP_BASE ?>';
const CSRF = '<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>';

/* ===== Helpers UI (thread card) ===== */
function setExpanded(card, isOpen){
  card.dataset.open = isOpen ? "1" : "0";
  const head = card.querySelector(".msg-head");
  if (head) head.setAttribute("aria-expanded", isOpen ? "true" : "false");
}
function setThreadDate(card, dateText){
  const el = card.querySelector(".thread-date");
  if (el) el.textContent = dateText;
}
function setThreadPreview(card, text){
  let el = card.querySelector(".msg-preview");
  if (!el){
    el = document.createElement("div");
    el.className = "msg-preview";
    const body = card.querySelector(".msg-body");
    card.insertBefore(el, body);
  }
  el.textContent = text;
}

/* ===== Delete modal ===== */
let delCtx = { id: 0, card: null, name: '' };

const delModal   = document.getElementById('delModal');
const delUser    = document.getElementById('delUser');
const delErr     = document.getElementById('delErr');
const delCancel  = document.getElementById('delCancel');
const delConfirm = document.getElementById('delConfirm');

function openDelModal(otherId, card){
  delCtx.id = otherId;
  delCtx.card = card;
  delCtx.name = card.querySelector('.thread-user')?.textContent || '';
  delUser.textContent = delCtx.name;
  delErr.textContent = '';
  delErr.style.display = 'none';
  delModal.style.display = 'flex';
  delModal.setAttribute('aria-hidden', 'false');
}

function closeDelModal(){
  delModal.style.display = 'none';
  delModal.setAttribute('aria-hidden', 'true');
}

document.addEventListener('click', (e) => {
  const btn = e.target.closest('.thread-del');
  if (!btn) return;
  e.stopPropagation(); // ne pas toggler la card
  const card = btn.closest('.msg-card');
  const otherId = parseInt(btn.dataset.other, 10) || 0;
  if (!card || !otherId) return;
  openDelModal(otherId, card);
});

delCancel.addEventListener('click', closeDelModal);
delModal.addEventListener('click', (e) => { if (e.target === delModal) closeDelModal(); });
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDelModal(); });

delConfirm.addEventListener('click', async () => {
  delErr.style.display = 'none';
  delErr.textContent = '';

  try{
    const fd = new FormData();
    fd.append('other_id', String(delCtx.id));
    fd.append('csrf', CSRF);

    const r = await fetch(`${BASE}/messages_delete_thread.php`, { method:'POST', body: fd });
    const j = await r.json();

    if (j.ok){
      if (delCtx.card) delCtx.card.remove();
      closeDelModal();
    } else {
      delErr.textContent = 'Suppression impossible (' + (j.error || 'erreur') + ')';
      delErr.style.display = 'block';
    }
  } catch(_){
    delErr.textContent = 'Erreur réseau.';
    delErr.style.display = 'block';
  }
});

/* ===== Toggle open/close ===== */
document.addEventListener('click', (e) => {
  // si click sur delete button -> ignore (déjà géré)
  if (e.target.closest('.thread-del')) return;

  const head = e.target.closest('.msg-head');
  if (!head) return;

  const card = head.closest('.msg-card');
  if (!card) return;

  const isOpen = card.dataset.open === '1';
  setExpanded(card, !isOpen);

  if (!isOpen) {
    const body = card.querySelector('.msg-body');
    if (body) body.scrollTop = body.scrollHeight;
  }
});

// accessibilité clavier
document.addEventListener('keydown', (e) => {
  const head = e.target.closest?.('.msg-head');
  if (!head) return;
  if (e.key !== 'Enter' && e.key !== ' ') return;
  e.preventDefault();
  head.click();
});

/* ===== Envoi inline + MAJ aperçu/date + remonter la card ===== */
document.addEventListener('submit', async (e) => {
  const form = e.target.closest('form.reply');
  if (!form) return;
  e.preventDefault();

  const status = form.querySelector('.reply-status');
  const btn = form.querySelector('button[type="submit"]');

  status.textContent = '';
  btn.disabled = true;

  try {
    const fd = new FormData(form);
    const bodyText = (fd.get('body') || '').toString().trim();
    const fileInput = form.querySelector('input[type="file"]');
    const hasImg = fileInput && fileInput.files && fileInput.files.length > 0;

    if (!bodyText && !hasImg) {
      status.textContent = 'Écris un message ou choisis une image.';
      btn.disabled = false;
      return;
    }

    const r = await fetch(`${BASE}/message_send.php`, { method: 'POST', body: fd });
    const j = await r.json().catch(() => ({ ok:false, error:'bad_json' }));

    if (!j.ok) {
      status.textContent = 'Échec (' + (j.error || 'erreur') + ')';
      return;
    }

    // ajoute le message à la fin
    const out = document.createElement('div');
    out.className = 'msg-out';
    out.innerHTML = `<div class="msg-out-head">Moi — à l’instant</div>` +
      (bodyText ? `<div class="msg-out-text"></div>` : '');

    if (bodyText){
      // escape simple
      out.querySelector('.msg-out-text').textContent = bodyText;
    }

if (hasImg) { // affiche l’image ou vidéo
  const file = fileInput.files[0];
  const url = URL.createObjectURL(file);

  const wrap = document.createElement('div');
wrap.className = 'msg-img';

// pas de <a> en preview blob
if (file.type.startsWith('video/')) {
  const v = document.createElement('video');
  v.className = 'msg-video';
  v.controls = true;
  v.playsInline = true;
  v.preload = 'metadata';
  v.src = url;

  // ne revoke pas ici
  wrap.appendChild(v);
} else {
  const img = new Image();
  img.src = url;
  wrap.appendChild(img);
}

out.appendChild(wrap);

}

    form.before(out);

    const card = form.closest('.msg-card');
    const body = card.querySelector('.msg-body');
    if (body) body.scrollTop = body.scrollHeight;

    // maj preview + date
    setThreadPreview(card, bodyText || '[image]');
    const now = new Date();
    const dateStr =
      now.toLocaleDateString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric' }) +
      ' ' +
      now.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' });
    setThreadDate(card, dateStr);

    // remonter la card en haut
    const list = card.parentElement;
    const first = list.querySelector('.msg-card');
    if (first && card !== first) list.insertBefore(card, first);

    status.textContent = 'Envoyé';
    form.reset();
    form.querySelector('textarea')?.focus();

    if (typeof refreshBadge === 'function') refreshBadge();

  } catch(_) {
    status.textContent = 'Erreur réseau.';
  } finally {
    btn.disabled = false;
  }
});
</script>
</body>
</html>
