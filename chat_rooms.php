<?php
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';
 require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();
if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16));
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Salons de discussion</title>
<style>
:root{--bg:#0f172a;--card:#111827;--line:#334155;--txt:#e5e7eb;--mut:#94a3b8;--brand:#2563eb;}
body{margin:0;background:var(--bg);color:var(--txt);font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}
.wrap{max-width:900px;margin:24px auto;padding:0 16px}
.card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px;margin-bottom:14px}
.btn{background:var(--brand);color:#fff;border:none;border-radius:100px;padding:8px 12px;cursor:pointer}
.row{display:flex;align-items:center;justify-content:space-between;padding:10px;border:1px solid var(--line);border-radius:12px;margin:8px 0}
.mut{color:var(--mut)}
.rooms{margin-top:10px}
.room{cursor:pointer}

/* --- Modal principal --- */
#chatModal{position:fixed;inset:0;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center;z-index:50}
#chatBox{background:var(--card);border:1px solid var(--line);border-radius:16px;width:min(900px,95vw);height:min(640px,90vh);display:flex;flex-direction:column;position:relative}
#chatHead{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid var(--line)}
#chatMsgs{flex:1;overflow:auto;padding:12px 14px}
#chatForm{display:flex;gap:8px;padding:12px 14px;border-top:1px solid var(--line)}
#chatForm input[name="body"]{flex:1;min-height:46px;max-height:160px;background:#0b1220;color:var(--txt);border:1px solid var(--line);border-radius:10px;padding:10px}

/* --- Messages --- */
.msg{border:1px solid var(--line);border-radius:10px;padding:8px 10px;margin:8px 0;overflow-wrap:break-word}
.msg .meta{font-size:12px;color:var(--mut);margin-bottom:4px}

/* --- Bouton bas --- */
#toBottom{position:absolute;right:18px;bottom:82px;display:none;border:1px solid var(--line);background:#111827;color:var(--txt);border-radius:999px;padding:6px 10px;cursor:pointer;box-shadow:0 2px 10px rgba(0,0,0,.25)}

/* --- Image des messages --- */
.chat-img{max-width:min(55%,420px);height:auto;border-radius:8px;cursor:zoom-in;display:block}

/* --- Effet flou sur images non vues --- */
.imageVeil{position:relative;width:auto;max-width:min(55%,420px);border:1px solid var(--line);border-radius:8px;overflow:hidden;cursor:pointer;background-size:cover;background-position:center}
.imageVeil span{color:var(--mut);font-size:14px;background:#111827;padding:8px 10px;border-radius:999px;border:1px solid var(--line)}
.imageVeil--blur::after{content:"";position:absolute;inset:0;backdrop-filter:blur(14px);background:rgba(0,0,0,.35);border-radius:8px}

/* --- Modal image --- */
#imgModal{position:fixed;inset:0;background:rgb(0 0 0 / 65%);display:flex;align-items:center;justify-content:center;z-index:70}
#imgModal[hidden]{display:none}
.imgModal__box{max-width:55vw;max-height:40vh}
#imgModalImg{max-width:85vw;max-height:65vh;border-radius:12px;display:block}


#lockModal{position:fixed;inset:0;background:rgb(0 0 0 / 65%);display:flex;align-items:center;justify-content:center;z-index:80}
#lockModal[hidden]{display:none}
.userModal__box{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:16px;width:min(520px,92vw)}
.userActions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}




/* ===== Mobile ===== */
@media (max-width:640px){
  .wrap{max-width:100%;padding:0}
  .card{border-radius:0;border-left:0;border-right:0}
  #chatModal{align-items:stretch;justify-content:stretch}
  #chatBox{border-radius:0;width:100vw;height:100dvh;max-width:100vw;max-height:100dvh}
  #chatHead{padding:12px}
  #chatMsgs{padding:10px}
  #chatForm{padding:10px;gap:8px}
  #chatForm input[name="body"]{min-height:48px}
  .msg{padding:10px;margin:10px 0}
  .row{flex-direction:column;align-items:flex-start;gap:6px;padding:10px}

  .chat-img,.imageVeil{
        max-width: 50%;
        margin-top: 8px;
        border: 0.5px solid #66339985;
        box-shadow: 5px -2px 5px #0000008c; }

  #toBottom{right:10px;bottom:86px;padding:6px 10px}
  #imgModal{align-items:center;justify-content:center}
  .imgModal__box{max-width:96vw;max-height:90dvh}
  #imgModalImg{max-width:96vw;max-height:90dvh}
}

/* ===== Très petits écrans ===== */
@media (max-width:360px){
  #chatHead h3{font-size:15px}
  .btn{padding:8px 8px}
}


/* Form */
.container_chatForm{padding:0 14px 12px;border-top:1px solid var(--line)}
#chatForm{display:flex;gap:8px;align-items:center;flex-wrap:wrap;width:100%}

/* Aplatit les wrappers <div> du form */
#chatForm > div{display:contents}

/* Champs */
#chatForm input[name="body"]{flex:1 1 260px;min-width:0}
#chatForm input[type="file"]{flex:0 0 auto;max-width:100%}
#chatForm button[type="submit"]{flex:0 0 auto}

/* Mobile */
@media (max-width:640px){
  #chatForm{gap:8px}
  #chatForm input[name="body"]{flex:1 1 100%}
  #chatForm input[type="file"]{flex:1 1 calc(60% - 8px)}
  #chatForm button[type="submit"]{flex:1 1 calc(40% - 8px)}
}




</style>

</head><body>
<div class="wrap">
  <div class="card">
    <h1 style="margin:0 0 6px">Salons de discussion</h1>

    <form id="newRoom" autocomplete="off" style="display:flex;gap:8px;align-items:center;margin-top:10px">
      <input name="name" maxlength="20" placeholder="Nom du salon (ex: Discu Sympa)" required
             style="flex:1;padding:10px;border-radius:10px;border:1px solid var(--line);background:#0b1220;color:var(--txt)">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'],ENT_QUOTES) ?>">
      <button class="btn" type="submit">Créer</button>
      <span id="roomStatus" class="mut"></span>

<label style="display:inline-flex;align-items:center;gap:8px;margin-left:8px">
  <input type="checkbox" name="is_private" id="is_private">
  Protégé
</label>
<input type="password" name="password" id="room_pwd" placeholder="Mot de passe" maxlenght="20" style="display:none">
<script>
const chk = document.getElementById('is_private');
const pwd = document.getElementById('room_pwd');
chk.addEventListener('change', (e)=>{
  const on = e.target.checked;
  pwd.style.display = on ? 'inline-block' : 'none';
  pwd.toggleAttribute('required', on); // ← ajoute/enlève required
  if (!on) pwd.value = '';
});
</script>


    </form>

<div id="lockModal" hidden>
  <div class="userModal__box">
    <h3>Salon protégé</h3>
    <p class="mut">Entrez le mot de passe.</p>
    <form id="lockForm">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">
      <input type="hidden" name="room_id" id="lock_room_id">

      <input type="password" name="password" placeholder="Mot de passe" required>
      <div class="userActions" style="margin-top:12px">

        <button class="btn" type="submit">Entrer</button>
        <button id="lockClose" class="btn" type="button" style="background:#374151">Fermer</button>
      </div>
      <div id="lockStatus" class="mut" style="margin-top:8px"></div>
    </form>
  </div>
</div>




    <div class="rooms" id="rooms"></div>
  </div>

  <p><a class="btn" href="<?= APP_BASE ?>/index.php">&larr; Retour</a></p>
</div>

<!-- Modal chat -->
<div id="chatModal">
  <div id="chatBox">
    <div id="chatHead">
      <strong id="roomTitle">Salon</strong>
      <button id="chatClose" class="btn" type="button" style="background:#374151">X</button>
    </div>
    <div id="chatMsgs"></div>

    <button id="toBottom" type="button" aria-label="Aller en bas">▼</button>

<div class="container_chatForm">
  <form id="chatForm" enctype="multipart/form-data">
    <input type="hidden" name="room_id" id="room_id" value="">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">

    <input type="text" name="body" placeholder="Écrire…" maxlength="2000" autocomplete="off">
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
    <button type="submit" class="btn">Envoyer</button>
  </form>
</div>


<!-- Modal image -->
<div id="imgModal" hidden>
  <div class="imgModal__box">
    <img id="imgModalImg" alt="image">
  </div>
</div>

  </div>
</div>



<script>
/* ============================================================================
 *           CHAT — logique côté client (commentée pour futur dev)
 * ============================================================================
 * Responsabilités :
 *  - Charger la liste des salons, en créer et en ouvrir un
 *  - Polling des messages (2s) + rendu des messages
 *  - Envoi de message (texte + image), avec compression côté client
 *  - Anti-abus : gestion des réponses 429 renvoyées par le serveur
 *  - Images : voile/blur tant qu’elles n’ont pas été vues + modal plein écran
 *  - UX : auto-scroll intelligent + bouton « aller en bas »
 * ==========================================================================*/

/* --- Raccourcis DOM & état global --- */
const BASE      = '<?= APP_BASE ?>';                 // base path côté PHP
const RLIST     = document.getElementById('rooms');  // conteneur de la liste des salons
const NST       = document.getElementById('roomStatus'); // statut création de salon
const chatModal = document.getElementById('chatModal');  // overlay du chat
const chatClose = document.getElementById('chatClose');  // bouton fermer
const chatMsgs  = document.getElementById('chatMsgs');   // liste des messages
const chatForm  = document.getElementById('chatForm');   // formulaire d’envoi
const roomIdInp = document.getElementById('room_id');    // hidden: id du salon courant
const roomTitle = document.getElementById('roomTitle');  // titre du salon
const toBottom  = document.getElementById('toBottom');   // bouton « aller en bas »



let pollTimer = null;                                   // timer du polling
let lastId    = 0;                                      // dernier id de message reçu
let currentRoom = 0;                                    // id du salon courant

/* --- Helpers génériques --- */
function escapeHtml(s){ return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function isNearBottom(el, px=80){ return el.scrollHeight - el.scrollTop - el.clientHeight <= px; }
function scrollToBottom(el, smooth=true){
  const last = el.lastElementChild;
  if (last) last.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'end' });
}

/* --- Persistance locale : images déjà vues (évite de re-blurer) --- */
const VIEWED_KEY = 'chat_viewed_images_v1';
const viewed = new Set(JSON.parse(localStorage.getItem(VIEWED_KEY) || '[]'));

function isViewed(src){ return viewed.has(src); }
function markViewed(src){
  if (!src) return;
  viewed.add(src);
  localStorage.setItem(VIEWED_KEY, JSON.stringify([...viewed]));
}

/* --- Modal image (ouverture/fermeture) --- */
const imgModal    = document.getElementById('imgModal');
const imgModalImg = document.getElementById('imgModalImg');

function openImgModal(src){
  imgModalImg.src = src;
  imgModal.hidden = false;
}
function closeImgModal(){
  imgModal.hidden = true;
  imgModalImg.removeAttribute('src');
}
// Fermer en cliquant en dehors de l’image
imgModal.addEventListener('click', e => { if (e.target === imgModal) closeImgModal(); });
// Fermer à Échap
document.addEventListener('keydown', e => { if (e.key === 'Escape' && !imgModal.hidden) closeImgModal(); });

/* ============================================================================
 *                               Salons
 * ==========================================================================*/

/* Création d’un salon : POST simple + refresh de la liste */
document.getElementById('newRoom').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type="submit"]');
  btn.disabled = true;
  NST.textContent = '';

  try {
    const r = await fetch(`${BASE}/chat_room_create.php`, { method:'POST', body:new FormData(e.target) });
    if (r.status === 429) {
      let left = 0;
      try { const j = await r.json(); if (j?.error==='limit' && Number.isFinite(j.retry_after)) left = Math.max(0, j.retry_after|0); } catch {}
      NST.style.color = '#f87171';
      NST.textContent = left>0 ? 'Vous pourrez recréer un salon dans '+formatDelay(left)+'.' : 'Limite atteinte.';
      if (left>0) startCreateCountdown(left);
      return;
    }

 const j = await r.json();
    if (j.ok) {
      NST.style.color = '#34d399';
      NST.textContent = 'Salon créé.';
      e.target.reset();
      // masque le champ password si on vient de décocher “Protégé”
      const pwd = document.getElementById('room_pwd'); if (pwd) pwd.style.display = 'none';
      loadRooms();

    } 
    
    else 
      {
      NST.style.color = '#f87171';
      NST.textContent = j.error || 'Erreur';
    }
  } catch {
    NST.style.color = '#f87171';
    NST.textContent = 'Réseau';
  }
finally {
    btn.disabled = false;
  }

});


let createUntil = 0; // timestamp ms

function startCreateCountdown(sec){
  createUntil = Date.now() + sec*1000;
  tickCreateCountdown();
}

function tickCreateCountdown(){
  const btn = document.querySelector('#newRoom button[type="submit"]');
   const left = Math.max(0, Math.floor((createUntil - Date.now())/1000));
  if (left > 0){
    btn.disabled = true;
    NST.style.color = '#f87171';
    NST.textContent = 'Vous pourrez recréer un salon dans ' + formatDelay(left) + '.';
    setTimeout(tickCreateCountdown, 1000);
  }else{
    btn.disabled = false;
    NST.textContent = '';
  }
}


async function checkCreateQuota(){
  try{
    const r = await fetch(`${BASE}/chat_room_quota.php`, {cache:'no-store', credentials:'same-origin'});
    if (!r.ok) return;
    const j = await r.json();
    if (j?.ok && Number.isFinite(j.retry_after) && j.retry_after > 0){
      startCreateCountdown(j.retry_after|0);
    }
  }catch{}
}

checkCreateQuota();
loadRooms();


/* Utilitaire formatage secondes -> "X h Y min" ou "Y min Z s" */
function formatDelay(sec){
  sec = Math.max(0, Math.floor(sec));
  const h = Math.floor(sec/3600);
  const m = Math.floor((sec%3600)/60);
  const s = sec%60;
  if (h > 0) return `${h} h ${m.toString().padStart(2,'0')} min`;
  if (m > 0) return `${m} min ${s.toString().padStart(2,'0')} s`;
  return `${s} s`;
}


/*loadRooms  Récupère la liste des salons et l’affiche */


async function loadRooms(){
  RLIST.innerHTML = '<div class="mut">Chargement…</div>';
  try{
    const r = await fetch(`${BASE}/chat_rooms_list.php`, { cache: 'no-store' });
    const j = await r.json();
    if (!j.ok) throw 0;
    if (j.rooms.length === 0){
      RLIST.innerHTML = '<div class="mut">Aucun salon.</div>';
      return;
    }

    // Affiche le cadenas pour les salons privés et stocke data-private

    RLIST.innerHTML = j.rooms.map(x => {
  const priv = Number(x.is_private) === 1; // ← coercition sûre
  const last = x.last_at ? new Date(x.last_at.replace(' ','T')).toLocaleString() : '—';
  return `
    <div class="row room"
         data-id="${x.id}"
         data-name="${escapeHtml(x.name)}"
         data-private="${priv ? 1 : 0}">
      <div><strong>${escapeHtml(x.name)}${priv ? ' 🔒' : ''}</strong></div>
      <div class="mut">${last}</div>
    </div>
  `;
}).join('');

  }catch{
    RLIST.innerHTML = '<div class="mut">Erreur.</div>';
  }
}
/* Clic sur un salon : l’ouvre (ou demande mot de passe si privé) */


RLIST.addEventListener('click', (e) => {
  const row = e.target.closest('.room');
  if (!row) return;
  const id = parseInt(row.dataset.id, 10);
  const name = row.dataset.name;
  const priv = row.dataset.private === '1';

  if (priv) {
    // ouvre la modal de mot de passe
    document.getElementById('lock_room_id').value = id;
    document.getElementById('lockStatus').textContent = '';
    document.getElementById('lockModal').hidden = false;
  } else {
    openRoom(id, name);
  }
});

const lockModal = document.getElementById('lockModal');
const lockForm  = document.getElementById('lockForm');
const lockClose = document.getElementById('lockClose');
const lockStatus= document.getElementById('lockStatus');

lockClose.addEventListener('click', ()=> lockModal.hidden = true);
lockModal.addEventListener('click', e => { if (e.target === lockModal) lockModal.hidden = true; });

lockForm.addEventListener('submit', async (e)=>{
  e.preventDefault();
  lockStatus.textContent = '';
  const pwdInput = lockForm.querySelector('input[type="password"]');
  try{
    const r = await fetch(`${BASE}/chat_room_unlock.php`, { method:'POST', body:new FormData(lockForm), credentials:'same-origin' });
    if (r.status === 429){ lockStatus.textContent = 'Trop d’essais. Attendez un peu.'; return; }
    const j = await r.json();
    if (j.ok){
      lockModal.hidden = true;
      const id = parseInt(document.getElementById('lock_room_id').value,10);
      const row = RLIST.querySelector(`.room[data-id="${id}"]`);
      openRoom(id, row ? row.dataset.name : 'Salon');
    } else if (j.error==='bad_password'){
      lockStatus.textContent = 'Mot de passe incorrect.';
    } else {
      lockStatus.textContent = 'Erreur.';
    }
  } catch {
    lockStatus.textContent = 'Réseau indisponible.';
  } finally {
    if (pwdInput) pwdInput.value = ''; // clear
  }
});









/* Ouvre un salon : nettoie l’état, montre le modal, démarre le polling */
function openRoom(id, name){
  currentRoom = id;
  lastId = 0;
  roomIdInp.value = id;
  roomTitle.textContent = name;
  chatMsgs.innerHTML = '';
  toBottom.style.display = 'none';
  chatModal.style.display = 'flex';
  startPolling();
}

/* Fermer le chat */
chatClose.onclick = () => { chatModal.style.display = 'none'; stopPolling(); };
chatModal.addEventListener('click', (e) => { if (e.target === chatModal){ chatModal.style.display = 'none'; stopPolling(); }});

/* ============================================================================
 *                              Messages
 * ==========================================================================*/

/* Bouton « aller en bas » visible seulement si on s’éloigne du bas */
chatMsgs.addEventListener('scroll', () => {
  toBottom.style.display = isNearBottom(chatMsgs) ? 'none' : 'block';
});
toBottom.addEventListener('click', () => scrollToBottom(chatMsgs, true));

/* Rendu d’un message (texte + éventuellement image avec voile/blur) */
function renderMessage(m){
  const el = document.createElement('div');
  el.className = 'msg';

  // En-tête (auteur + horodatage)
  el.innerHTML =
    `<div class="meta">${escapeHtml(m.sender)} — ${new Date(m.created_at.replace(' ','T')).toLocaleString()}</div>` +
    (m.body ? `<div style="white-space:pre-wrap">${escapeHtml(m.body)}</div>` : '');

  // Si c’est une image
  if (m.file_url && /^image\//.test(m.file_mime || '')){
    const src = m.file_url;

    if (isViewed(src)){
      // Déjà vue : on l’affiche directement (cliquable -> modal)
      const img = document.createElement('img');
      Object.assign(img, { src, alt: 'image', loading: 'lazy' });
     // img.style.maxWidth = '15%';
     // img.style.borderRadius = '8px';
     // img.style.cursor = 'zoom-in';
     img.className = 'chat-img';

      img.addEventListener('click', () => openImgModal(src));
      el.appendChild(img);
    }else{
      // Pas encore vue : on affiche un voile flouté cliquable
      const veil = document.createElement('div');
      veil.className = 'imageVeil imageVeil--blur';
      veil.style.backgroundImage = `url('${src}')`;
      veil.innerHTML = `<span>Cliquer pour afficher l’image</span>`;

      veil.addEventListener('click', () => {
        openImgModal(src);   // affiche en grand
        markViewed(src);     // mémorise comme "vue"

        // Et remplace le voile par l’image nette dans le flux
        const img = document.createElement('img');
        Object.assign(img, { src, alt: 'image', loading: 'lazy' });
        img.style.maxWidth = '20%';
        img.style.width = '20%';
         img.style.borderRadius = '8px';
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', () => openImgModal(src));
        veil.replaceWith(img);
      });

      el.appendChild(veil);
    }
  }

  return el;
}

/* Récupère les nouveaux messages depuis le serveur */
async function fetchMessages(){
  if (!currentRoom) return;
  try{
    const r = await fetch(`${BASE}/chat_messages_fetch.php?room_id=${currentRoom}&after_id=${lastId}`, { cache: 'no-store' });
    if (!r.ok) {
      if (r.status === 403 || r.status === 404) { /* salon verrouillé (sécurité côté serveur) */
        stopPolling();
        toBottom.style.display = 'none';
      }
      return;
    }
    const j = await r.json();
    if (!j.ok) return;

    if (j.messages.length){
      const stick = isNearBottom(chatMsgs);
      const frag = document.createDocumentFragment();
      j.messages.forEach(m => {
        frag.appendChild(renderMessage(m));
        lastId = Math.max(lastId, m.id);
      });
      chatMsgs.appendChild(frag);
      if (stick) scrollToBottom(chatMsgs, true);
      toBottom.style.display = isNearBottom(chatMsgs) ? 'none' : 'block';
    }
  } catch {/* silencieux */}
}

/* Démarre/arrête le polling (2s) */
function startPolling(){
  stopPolling();
  fetchMessages();                       // premier fetch immédiat
  pollTimer = setInterval(fetchMessages, 2000);
}
function stopPolling(){
  if (pollTimer){ clearInterval(pollTimer); pollTimer = null; }
}

/* ============================================================================
 *                Compression image côté client AVANT upload
 * ============================================================================
 * - Accepte jpeg/png/webp, convertit en JPEG.
 * - Redimensionne dans un carré max 1280 px (proportionnel).
 * - Qualité JPEG à 0.8 (ajuste si besoin).
 * ==========================================================================*/
chatForm.querySelector('input[type="file"]').addEventListener('change', async (e) => {
  const file = e.target.files[0];
  if (!file || !/^image\/(jpeg|png|webp)$/.test(file.type)) return;

  // Crée un ObjectURL et charge dans un Image() pour connaître dimensions
  const url = URL.createObjectURL(file);
  const img = new Image();
  img.src = url;
  await new Promise(res => img.onload = res);

  // Redimension proportionnel (max 1280)
  const MAX = 1280;
  let { width, height } = img;
  if (width > height && width > MAX){ height *= MAX/width; width = MAX; }
  else if (height > width && height > MAX){ width *= MAX/height; height = MAX; }

  // Dessine dans canvas
  const canvas = document.createElement('canvas');
  canvas.width = width; canvas.height = height;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(img, 0, 0, width, height);

  // Exporte en JPEG (qualité 0.8)
  const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.8));
  const compressed = new File([blob], file.name.replace(/\.\w+$/, '.jpg'), { type: 'image/jpeg' });

  // Remplace le fichier original dans l’input
  const dt = new DataTransfer();
  dt.items.add(compressed);
  e.target.files = dt.files;

  URL.revokeObjectURL(url);
});

/* ============================================================================
 *                    Envoi du message (texte + image)
 * ============================================================================
 * - Envoie le FormData vers chat_message_send.php
 * - Gère les erreurs 429 de rate limiting renvoyées par le serveur :
 *   rate_glob (3/30s), rate_room (2/5s), rate_fast (~1s)
 * ==========================================================================*/
chatForm.addEventListener('submit', async (e) => {
  e.preventDefault();

  const btn = chatForm.querySelector('button[type="submit"]');
  btn.disabled = true;

  try{
    const r = await fetch(`${BASE}/chat_message_send.php`, {
      method: 'POST',
      body: new FormData(chatForm),
      credentials: 'same-origin'
    });

    // Si le serveur renvoie 429, affiche un message plus précis
    if (r.status === 429){
      let msg = 'Trop de messages.';
      try{
        const j = await r.json();
        if (j?.error === 'rate_glob') msg = 'Limite : 3 messages / 30 s.';
        if (j?.error === 'rate_room') msg = 'Ralentis dans ce salon (2 / 5 s).';
        if (j?.error === 'rate_fast') msg = 'Trop rapide : attends ~1 s.';
      }catch{}
      alert(msg);
      return;
    }

    // Réponse JSON standard
    const j = await r.json();
    if (!j.ok){ alert(j.error || 'Erreur'); return; }

    // Reset du formulaire + scroll vers le bas (léger délai pour laisser le DOM s’actualiser)
    chatForm.reset();
    setTimeout(() => scrollToBottom(chatMsgs, true), 50);

  }catch{
    alert('Réseau indisponible');
  }finally{
    btn.disabled = false;
  }
});

/* --- Bootstrapping : charge la liste des salons au chargement de la page --- */
loadRooms();
</script>

</body></html>
