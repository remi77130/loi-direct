
/* =============================================================================
 *  CHAT — logique côté client
 *  But : vue unique gérant la liste des salons, l’ouverture d’un salon, 
 *        la présence temps réel par ping périodique, l’échange de messages,
 *        l’aperçu d’images et la fiche utilisateur + DM.
 *
    - Sécurité : le code suppose que le backend valide toujours room_id, CSRF si activé, et filtre les fichiers uploadés.
    - Perf : le polling est simple et robuste. Pour monter en charge, passer plus tard à SSE/WebSocket.
    - Accessibilité : les modales devraient recevoir aria-modal="true" et focus management si requis.


 *  Hypothèses côté serveur :
 *   - Endpoints REST existants :
 *       GET  /chat_rooms_list.php
 *       POST /chat_room_create.php
 *       GET  /chat_messages_fetch.php?room_id=...&after_id=...
 *       POST /chat_message_send.php   (FormData texte + image)
 *       POST /chat_room_unlock.php    (déverrouillage des salons privés)
 *       GET  /chat_room_quota.php     (quota création salon)
 *       POST /chat_presence_ping.php  (ping présence)
 *       GET  /chat_presence_list.php?room_id=...
 *       GET  /api_user_profile.php?user_id=...
 *   - PHP expose APP_BASE et user_id en session.
 *   - Le HTML fournit les IDs utilisés plus bas.
 * =============================================================================*/

/* === Références DOM + état global ========================================= */
const BASE = '<?= APP_BASE ?>';                       // Base URL de l’app
const CURRENT_USER_ID = <?= (int)($_SESSION['user_id'] ?? 0) ?>;

const RLIST       = document.getElementById('rooms');       // Liste des salons
const NST         = document.getElementById('roomStatus');  // Zone statut création
const chatModal   = document.getElementById('chatModal');   // Overlay chat
const chatClose   = document.getElementById('chatClose');   // Bouton fermer chat
const chatMsgs    = document.getElementById('chatMsgs');    // Flux des messages
const chatForm    = document.getElementById('chatForm');    // Form d’envoi
const roomIdInp   = document.getElementById('room_id');     // Hidden room_id
const roomTitle   = document.getElementById('roomTitle');   // Titre salon
const toBottom    = document.getElementById('toBottom');    // Bouton “aller en bas”

// Modale “salon privé”
const lockModal   = document.getElementById('lockModal');
const lockForm    = document.getElementById('lockForm');
const lockClose   = document.getElementById('lockClose');
const lockStatus  = document.getElementById('lockStatus');

// Modale “actifs”
const showActive      = document.getElementById('showActive');
const activeModal     = document.getElementById('activeModal');
const activeModalBody = document.getElementById('activeModalBody');
const activeClose     = document.getElementById('activeClose');

// Modale “profil utilisateur”
const userModal  = document.getElementById('userModal');
const umClose    = document.getElementById('umClose');
const umBody     = document.getElementById('umBody');
const umName     = document.getElementById('umName');

// Mini-form DM dans la modale user
const umBox        = document.getElementById('umDMBox');
const dmForm       = document.getElementById('dmSend');
const dmRecipient  = document.getElementById('dmRecipient');
const dmBody       = document.getElementById('dmBody');
const dmBtn        = document.getElementById('dmBtn');
const dmHint       = document.getElementById('dmHint');

// Modale image (optionnelle)
const imgModal    = document.getElementById('imgModal');
const imgModalImg = document.getElementById('imgModalImg');

// État runtime
let pollTimer   = null;     // Interval du polling messages
let pollToken   = 0;        // Invalide les anciens polls lors d’un changement de salon
let lastId      = 0;        // Dernier id message reçu
let currentRoom = 0;        // Room ouverte
let pollDelay   = 2000;     // 2s





// Active Users — minimal JS module
// Requirements: global BASE, CURRENT_USER_ID, escapeHtml(), and an endpoint /chat_presence_list.php
// Optional: user profile endpoint at /user_card.php?id=... and showModal/hydrateUserModal helpers

(function(){
  const ACTIVE_BOX = document.getElementById('activeUsersBox');
  if (!ACTIVE_BOX) return;

  const PRESENCE_THRESHOLD_S = 90;

  function getRoomParam(){
    try { return (typeof currentRoom !== 'undefined' && currentRoom) ? `&room_id=${encodeURIComponent(currentRoom)}` : ''; }
    catch { return ''; }
  }

const ACTIVE_BOX = document.getElementById('activeUsersBox');
const PRESENCE_THRESHOLD_S = 90; // doit être ≤ au seuil serveur

async function refreshActiveUsers(){
  if (!ACTIVE_BOX) return;
  try{
    const roomParam = (typeof currentRoom !== 'undefined' && currentRoom) ? `&room_id=${encodeURIComponent(currentRoom)}` : '';
    const r = await fetch(`${BASE}/chat_presence_list.php?threshold=${PRESENCE_THRESHOLD_S}${roomParam}`, {
      credentials:'same-origin', cache:'no-store'
    });
    if (!r.ok) return;
    const j = await r.json();
    if (!j.ok) return;

    ACTIVE_BOX.innerHTML = '';
    j.users.forEach(u => {
      const d = document.createElement('div');
      d.className = 'user-card';
      d.dataset.userId = String(u.id);
      const av = escapeHtml(u.avatar_url || `${BASE}/uploads/avatars/default.png`);
      const name = escapeHtml(u.pseudo || 'Profil');
      d.innerHTML = `<img class="av" src="${av}" alt=""><div class="name">${name}</div><div class="dot" title="actif"></div>`;
      d.addEventListener('click', () => openUserModalById(u.id));
      ACTIVE_BOX.appendChild(d);
    });
  }catch{}
}

 /* === OUVERTURE MODALE PROFIL PAR ID (unique, sans collision) === */

async function openUserModalById(userId){
  try{
    const r = await fetch(`${BASE}/user_card.php?id=${encodeURIComponent(userId)}`, {
      credentials:'same-origin', cache:'no-store'
    });
    if (!r.ok) return;
    const j = await r.json();
    if (!j.ok || !j.user) return;

    if (typeof hydrateUserModal === 'function') hydrateUserModal(j.user);
    if (typeof showModal === 'function' && typeof userModal !== 'undefined') {
      showModal(userModal);
    } else if (typeof userModal !== 'undefined' && userModal) {
      userModal.hidden = false;
    }
  }catch{}
}
 /* === BOUCLE ACTIFS (timer distinct de la présence) ================= */

let activeUsersTimer = null;
function startActiveUsersLoop(){
  if (activeUsersTimer) clearInterval(activeUsersTimer);
  refreshActiveUsers();
  activeUsersTimer = setInterval(refreshActiveUsers, 15000);
}
function stopActiveUsersLoop(){
  if (activeUsersTimer){ clearInterval(activeUsersTimer); activeUsersTimer = null; }
}
document.addEventListener('DOMContentLoaded', startActiveUsersLoop);

// Si tu as déjà un hook d’ouverture de salon
if (typeof onRoomChanged === 'function'){
  const __prevOnRoomChanged = onRoomChanged;
  window.onRoomChanged = function(){
    try { __prevOnRoomChanged(); } catch {}
    refreshActiveUsers();
  };
}





/* === Aides génériques ====================================================== */
function escapeHtml(s){
  return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}
function isNearBottom(el, px=80){
  return el.scrollHeight - el.scrollTop - el.clientHeight <= px;
}
function scrollToBottom(el, smooth=true){
  const last = el.lastElementChild;
  if (last) last.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'end' });
}

/* === Persistance locale : images déjà vues (pour éviter un re-blur) ======== */
const VIEWED_KEY = 'chat_viewed_images_v1';
const viewed = new Set(JSON.parse(localStorage.getItem(VIEWED_KEY) || '[]'));
function isViewed(src){ return viewed.has(src); }
function markViewed(src){
  if (!src) return;
  viewed.add(src);
  localStorage.setItem(VIEWED_KEY, JSON.stringify([...viewed]));
}

/* === Modale image ========================================================== */
function openImgModal(src){
  if (!imgModal || !imgModalImg) return;  // HTML optionnel
  imgModalImg.src = src;
  imgModal.hidden = false;
}
function closeImgModal(){
  if (!imgModal || !imgModalImg) return;
  imgModal.hidden = true;
  imgModalImg.removeAttribute('src');
}
imgModal?.addEventListener('click', e => { if (e.target === imgModal) closeImgModal(); });

/* Fermer toutes les modales à Échap (image + user + lock) */
document.addEventListener('keydown', e => {
  if (e.key !== 'Escape') return;
  if (!imgModal?.hidden) closeImgModal();
  if (userModal && !userModal.hidden) userModal.hidden = true;
  if (lockModal && !lockModal.hidden) lockModal.hidden = true;
});

/* === Modale “actifs” : close handlers ===================================== */
activeModal?.addEventListener('click', e => { if (e.target === e.currentTarget) activeModal.hidden = true; });
activeClose?.addEventListener('click', () => { activeModal.hidden = true; });

/* =============================================================================
 *                                SALONS
 * =============================================================================*/

/* Création d’un salon */
document.getElementById('newRoom').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type="submit"]');
  btn.disabled = true;
  NST.textContent = '';

  try {
    const r = await fetch(`${BASE}/chat_room_create.php`, { method:'POST', body:new FormData(e.target) });
    if (r.status === 429) {
      // Le serveur renvoie au besoin un retry_after
      let left = 0;
      try {
        const j = await r.json();
        if (j?.error === 'limit' && Number.isFinite(j.retry_after)) left = Math.max(0, j.retry_after|0);
      } catch {}
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
      const pwd = document.getElementById('room_pwd'); if (pwd) pwd.style.display = 'none';
      loadRooms();         // Rafraîchit la liste
    } else {
      NST.style.color = '#f87171';
      NST.textContent = j.error || 'Erreur';
    }
  } catch {
    NST.style.color = '#f87171';
    NST.textContent = 'Réseau';
  } finally {
    btn.disabled = false;
  }
});

/* Countdown côté client pour le quota création */
let createUntil = 0;
function startCreateCountdown(sec){ createUntil = Date.now() + sec*1000; tickCreateCountdown(); }
function tickCreateCountdown(){
  const btn = document.querySelector('#newRoom button[type="submit"]');
  const left = Math.max(0, Math.floor((createUntil - Date.now())/1000));
  if (left > 0){
    btn.disabled = true;
    NST.style.color = '#f87171';
    NST.textContent = 'Vous pourrez recréer un salon dans ' + formatDelay(left) + '.';
    setTimeout(tickCreateCountdown, 1000);
  } else {
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

/* Format “X h Y min / Y min Z s / s s” */
function formatDelay(sec){
  sec = Math.max(0, Math.floor(sec));
  const h = Math.floor(sec/3600);
  const m = Math.floor((sec%3600)/60);
  const s = sec%60;
  if (h > 0) return `${h} h ${m.toString().padStart(2,'0')} min`;
  if (m > 0) return `${m} min ${s.toString().padStart(2,'0')} s`;
  return `${s} s`;
}

/* Liste des salons */
async function loadRooms(){
  RLIST.innerHTML = '<div class="mut">Chargement…</div>';
  try{
    const r = await fetch(`${BASE}/chat_rooms_list.php`, { cache:'no-store', credentials:'same-origin' });
    const j = await r.json();
    if (!j.ok) throw 0;
    if (j.rooms.length === 0){
      RLIST.innerHTML = '<div class="mut">Aucun salon.</div>';
      return;
    }
    RLIST.innerHTML = j.rooms.map(x => {
      const priv = Number(x.is_private) === 1;
      const last = x.last_at ? new Date(x.last_at.replace(' ','T')).toLocaleString() : '—';
      return `
        <div class="row room"
             data-id="${x.id}"
             data-name="${escapeHtml(x.name)}"
             data-private="${priv ? 1 : 0}">
          <div><strong>${escapeHtml(x.name)}${priv ? ' 🔒' : ''}</strong></div>
          <div class="mut">${last}</div>
        </div>`;
    }).join('');
  } catch {
    RLIST.innerHTML = '<div class="mut">Erreur.</div>';
  }
}

/* Click sur un salon : ouvre ou demande le mot de passe */
RLIST.addEventListener('click', (e) => {
  const row = e.target.closest('.room');
  if (!row) return;
  const id   = parseInt(row.dataset.id, 10);
  const name = row.dataset.name;
  const priv = row.dataset.private === '1';

  if (priv) {
    // Affiche la modale “privé”
    document.getElementById('lock_room_id').value = id;
    document.getElementById('lockStatus').textContent = '';
    document.getElementById('lockModal').hidden = false;
  } else {
    openRoom(id, name);
  }
});

/* Quotas init + charge la liste au boot */
checkCreateQuota();
loadRooms();

/* =============================================================================
 *                          PRÉSENCE EN TEMPS RÉEL
 * =============================================================================
 * Un identifiant “session_key” est stocké en localStorage pour représenter
 * l’onglet courant. On ping le serveur toutes les 20 s.
 * La liste des actifs se base sur un “last_seen” < 45 s.
 */
const PRESENCE_KEY = localStorage.getItem('presence_uuid')
  || (() => { const u = crypto.randomUUID(); localStorage.setItem('presence_uuid', u); return u; })();

let presenceTimer = null;

function startPresence(){
  stopPresence();             // pas de doublon de timers
  if (!currentRoom) return;
  presenceTimer = setInterval(presencePing, 20000);
  presencePing();             // ping immédiat dès l’ouverture
}
function stopPresence(){
  if (presenceTimer) { clearInterval(presenceTimer); presenceTimer = null; }
}

async function presencePing(){
  if (!currentRoom) return;
  try {
    const body = new URLSearchParams({ room_id:String(currentRoom), session_key:PRESENCE_KEY });
    const r = await fetch(`${BASE}/chat_presence_ping.php`, {
      method:'POST', body, credentials:'same-origin', cache:'no-store'
    });
    // DEBUG temporaire ; à retirer en prod si bruyant :
    // const t = await r.clone().text(); console.debug('presencePing', r.status, t);
  } catch {}
}/* Ouvre la modale “Actifs” et te surligne si présent */
showActive?.addEventListener('click', async () => {
  if (!currentRoom) return;

  try { await presencePing(); } catch {}

  try {
    const r = await fetch(`${BASE}/chat_presence_list.php?room_id=${currentRoom}`, {
      cache: 'no-store',
      credentials: 'same-origin'
    });
    const j = await r.json();
    const list = Array.isArray(j?.users) ? j.users : [];

    activeModalBody.innerHTML = list.length
      ? list.map(u => {
          const safeName = escapeHtml(u.pseudo || '—');
          const avatarSrc = u.avatar_url
            ? `${BASE}/${u.avatar_url}`
            : `${BASE}/uploads/avatars/default.png`;

          return `
            <div class="activeRow" data-id="${u.id}">
              <img class="active-avatar" src="${avatarSrc}" alt="${safeName}" loading="lazy">
              <span class="active-name">
                ${safeName}${u.id === CURRENT_USER_ID ? ' <span class="meTag">vous</span>' : ''}
              </span>
            </div>
          `;
        }).join('')
      : '<div>Aucun actif</div>';

    activeModal.hidden = false;

    // Scroll vers toi si tu es listé
    const me = activeModalBody.querySelector(`.activeRow[data-id="${CURRENT_USER_ID}"]`);
    if (me) {
      me.classList.add('is-me');
      me.scrollIntoView({ block: 'center' });
    }
  } catch {}
});

/* =============================================================================
 *                         DÉVERROUILLAGE SALON PRIVÉ
 * =============================================================================*/
lockClose.addEventListener('click', ()=> lockModal.hidden = true);
lockModal.addEventListener('click', e => { if (e.target === lockModal) lockModal.hidden = true; });

async function safeJson(resp){ try { return await resp.json(); } catch { return null; } }

lockForm.addEventListener('submit', async (e)=>{
  e.preventDefault();
  lockStatus.textContent = '';
  const btn = lockForm.querySelector('button[type="submit"]');
  const pwdInput = lockForm.querySelector('input[type="password"]');
  btn.disabled = true;

  try{
    const r = await fetch(`${BASE}/chat_room_unlock.php`, {
      method:'POST', body:new FormData(lockForm), credentials:'same-origin'
    });
    if (r.status === 429){
      lockStatus.textContent = 'Trop d’essais. Attendez un peu.';
      return;
    }

    const j = await safeJson(r);
    if (j && j.ok){
      lockModal.hidden = true;
      const id  = parseInt(document.getElementById('lock_room_id').value,10);
      const row = RLIST.querySelector(`.room[data-id="${id}"]`);
      openRoom(id, row ? row.dataset.name : 'Salon');
    } else if (j && j.error === 'bad_password'){
      lockStatus.textContent = 'Mot de passe incorrect.';
    } else {
      lockStatus.textContent = 'Erreur.';
    }
  } catch {
    lockStatus.textContent = 'Réseau indisponible.';
  } finally {
    if (pwdInput) pwdInput.value = '';
    btn.disabled = false;
  }
});

/* =============================================================================
 *                          OUVERTURE / FERMETURE SALON
 * =============================================================================*/
function openRoom(id, name){
  // Place le chat devant, ferme la fiche user si ouverte
  chatModal.classList.remove('behind');
  if (userModal) userModal.hidden = true;

  // Reset d’état pour ce salon
  currentRoom = id;
  lastId = 0;
  roomIdInp.value = id;
  roomTitle.textContent = name;
  chatMsgs.innerHTML = '';
  toBottom.style.display = 'none';
  chatModal.style.display = 'flex';

  // Redémarre polling messages + présence
  stopPolling();  startPolling();
  stopPresence(); startPresence();

  // Focus rapide dans la zone de saisie
  const bodyInput = chatForm.querySelector('[name="body"]');
  if (bodyInput) setTimeout(() => bodyInput.focus(), 50);
}

chatClose.onclick = () => {
  chatModal.style.display = 'none';
  stopPolling();
  stopPresence();
};
chatModal.addEventListener('click', (e) => {
  if (e.target === chatModal) {
    chatModal.style.display = 'none';
    stopPolling();
    stopPresence();
  }
});

/* =============================================================================
 *                                 MESSAGES
 * =============================================================================*/

/* Bouton “aller en bas” activé si on remonte dans l’historique */
chatMsgs.addEventListener('scroll', () => {
  toBottom.style.display = isNearBottom(chatMsgs) ? 'none' : 'block';
});
toBottom.addEventListener('click', () => scrollToBottom(chatMsgs, true));
/* Rendu d’un message (texte + image) */

function renderMessage(m){
  const el = document.createElement('div');
  el.className = 'msg';





  // Avatar
  const avatar = document.createElement('img');
  avatar.className = 'msg-avatar';
  const avatarSrc = m.avatar_url
    ? `${BASE}/${m.avatar_url}`
    : `${BASE}/uploads/avatars/default.png`;
  avatar.src = avatarSrc;
  avatar.alt = m.sender || '';
  avatar.loading = 'lazy';
  el.appendChild(avatar);

  // Conteneur texte
  const content = document.createElement('div');
  content.className = 'msg-content';

  // Meta: pseudo + date/heure construction du message 
  const meta = document.createElement('div');
  meta.className = 'meta';

   const who = document.createElement(m.sender_id ? 'button' : 'span'); // pseudo CLICK
  if (m.sender_id) {
    who.type = 'button';
    who.className = 'userLink';
    who.dataset.userId = String(m.sender_id);
    who.title = 'Voir le profil';
    who.style.all = 'unset';
    who.style.cursor = 'pointer';
    who.style.color = '#93c5fd';
  
    
  }
  who.textContent = typeof m.sender === 'string' ? m.sender : '—'; // pseudo safe
  
  // ajoute le pseudo sur le conteneur du message pour le clic
  el.dataset.user = who.textContent || '';

  meta.appendChild(who); // pseudo CLICK

  if (m.created_at) {
    const when = new Date(String(m.created_at).replace(' ','T')).toLocaleString();
    const sep = document.createElement('span');
    sep.textContent = ' • ' + when;
    meta.appendChild(sep);
  }

  content.appendChild(meta);

  // Corps texte
  if (m.body) {
    const body = document.createElement('div');
    body.className = 'msg-body';
    body.style.whiteSpace = 'pre-wrap';
    const span = document.createElement('span');
   // Mise en forme des @mentions dans le texte du message
    // Exemple: "@Remi-85" devient <span class="mention">@Remi-85</span>
    const mentionRegex = /(@[A-Za-zÀ-ÖØ-öø-ÿ0-9_.-]+)/g;
    const htmlWithMentions = m.body.replace(mentionRegex, '<span class="mention">$1</span>');

    // On injecte le HTML généré (ok ici car le texte vient de ton backend, pas d'un input brut non filtré)
    span.innerHTML = htmlWithMentions;

    // Couleur perso si définie et valide (s'applique au texte entier du message)
    if (m.color && /^#[0-9A-Fa-f]{6}$/.test(m.color)) {
      span.style.color = m.color;
    }

    body.appendChild(span);
    content.appendChild(body);
  }


  // Image éventuelle
  if (m.file_url && /^image\//.test(m.file_mime || '')) {
    const src = m.file_url;

    if (isViewed(src)) {
      const img = document.createElement('img');
      Object.assign(img, { src, alt: 'image', loading: 'lazy' });
      img.className = 'chat-img';
      img.addEventListener('click', () => openImgModal(src));
      content.appendChild(img);
    } else {
      const veil = document.createElement('div');
      veil.className = 'imageVeil imageVeil--blur';
      veil.style.backgroundImage = `url('${src}')`;
      veil.innerHTML = '<span>Cliquer pour afficher l’image</span>';
      veil.addEventListener('click', () => {
        openImgModal(src);
        markViewed(src);
        const img = document.createElement('img');
        Object.assign(img, { src, alt: 'image', loading: 'lazy' });
        img.className = 'chat-img';
        img.addEventListener('click', () => openImgModal(src));
        veil.replaceWith(img);
      });
      content.appendChild(veil);
    }
  }


  

  el.appendChild(content);
  return el;





  
}




/* Fetch des nouveaux messages depuis lastId */
async function fetchMessages(){
  if (!currentRoom) return;
  try{
    const r = await fetch(`${BASE}/chat_messages_fetch.php?room_id=${currentRoom}&after_id=${lastId}`, {
      cache:'no-store', credentials:'same-origin'
    });
    if (!r.ok) {
      // 403/404 : on stoppe le polling (ex: salon redevenu privé)
      if (r.status === 403 || r.status === 404) {
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
      j.messages.forEach(m => { frag.appendChild(renderMessage(m)); lastId = Math.max(lastId, m.id); });
      chatMsgs.appendChild(frag);
      if (stick) scrollToBottom(chatMsgs, true);
      toBottom.style.display = isNearBottom(chatMsgs) ? 'none' : 'block';
    }
  } catch { /* silencieux */ }
}

/* Polling 2 s avec token pour éviter les “réponses tardives” d’un ancien salon */
function startPolling(){
  stopPolling();
  const myToken = ++pollToken;
  const wrappedFetch = async () => {
    if (myToken !== pollToken) return; // on a changé de salon entre-temps
    await fetchMessages();
  };
  wrappedFetch();
  pollTimer = setInterval(wrappedFetch, pollDelay);
}
function stopPolling(){
  pollToken++;                 // invalide le tour courant
  if (pollTimer){ clearInterval(pollTimer); pollTimer = null; }
}

/* Upload image : compression côté client avant envoi
   - formats acceptés : jpeg/png/webp → converti en JPEG
   - max dimension 1280 px, qualité 0.8 */
chatForm.querySelector('input[type="file"]').addEventListener('change', async (e) => {
  const file = e.target.files[0];
  if (!file || !/^image\/(jpeg|png|webp)$/.test(file.type)) return;

  const url = URL.createObjectURL(file);
  const img = new Image(); img.src = url;
  await new Promise(res => img.onload = res);

  const MAX = 1280;
  let { width, height } = img;
  if (width > height && width > MAX){ height *= MAX/width; width = MAX; }
  else if (height > width && height > MAX){ width *= MAX/height; height = MAX; }

  const canvas = document.createElement('canvas');
  canvas.width = width; canvas.height = height;
  const ctx = canvas.getContext('2d'); ctx.drawImage(img, 0, 0, width, height);

  const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.8));
  const compressed = new File([blob], file.name.replace(/\.\w+$/, '.jpg'), { type:'image/jpeg' });

  const dt = new DataTransfer(); dt.items.add(compressed);
  e.target.files = dt.files;

  URL.revokeObjectURL(url);
});

/* Envoi d’un message (texte + image) */
chatForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const btn = chatForm.querySelector('button[type="submit"]');
  btn.disabled = true;

  try{
    const r = await fetch(`${BASE}/chat_message_send.php`, {
      method:'POST', body:new FormData(chatForm), credentials:'same-origin'
    });

    if (r.status === 429){
      // Cas limites gérés côté serveur pour anti-spam
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

    const j = await r.json();
    if (!j.ok){ alert(j.error || 'Erreur'); return; }

    chatForm.reset();
    setTimeout(() => scrollToBottom(chatMsgs, true), 50);
  } catch {
    alert('Réseau indisponible');
  } finally {
    btn.disabled = false;
  }
});

/* =============================================================================
 *                           MODALE PROFIL + DM
 * =============================================================================*/

// Cible DM en cours
let dmTarget = null;
let umUserId = 0;

/* Hydratation de la modale profil.
   - Initialise le bloc DM avec avatar + pseudo si ce n’est pas toi. */
function hydrateUserModal(user){
  if (!user || !user.id) {
    umBox.hidden = true;
    dmTarget = null;
    dmRecipient.value = '';
    dmBody.value = '';
    dmHint.textContent = '';
    return;
  }

  const isSelf = Number(user.id) === Number(CURRENT_USER_ID);

  if (isSelf) {
    umBox.hidden = true;
    dmTarget = null;
    dmRecipient.value = '';
    dmBody.value = '';
    dmHint.textContent = '';
    return;
  }

  // Cible DM valide
  dmTarget = user.id;
  const name = user.pseudo || 'Utilisateur';

  const avatarEl = document.getElementById('dmAvatar');
  const nameEl   = document.getElementById('dmName');

  if (avatarEl) {
    const src = user.avatar_url
      ? `${BASE}/${user.avatar_url}`
      : `${BASE}/uploads/avatars/default.png`;
    avatarEl.src = src;
    avatarEl.alt = name;
  }

  if (nameEl) {
    nameEl.textContent = name;
  }

  umBox.hidden = false;
  dmRecipient.value = String(dmTarget);
  dmBody.value = '';
  dmHint.textContent = `Message privé à ${name}`;
}
/* Envoi DM */
dmForm?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  if (!dmTarget) return;
  const fd = new FormData(dmForm);
  
  dmBtn.disabled = true;
  dmHint.textContent = 'Envoi…';
  try{
    const r = await fetch(`${BASE}/chat_dm_send.php`, { method:'POST', body:fd, cache:'no-store', credentials:'same-origin' });
    if(!r.ok){
      const text = await r.text();
      throw new Error('HTTP '+r.status+' '+text);
    }
    const j = await r.json();
    if(!j.ok) throw new Error(j.error||'error');
    dmBody.value = '';
    dmForm.querySelector('input[name="image"]').value = '';
    dmHint.textContent = 'Message envoyé.';
  }catch(err){
    const msg = String(err.message||'Erreur');
    dmHint.textContent = /rate/.test(msg) ? 'Trop de messages. Réessayez dans un instant.' : 'Échec de l’envoi.';
  }finally{
    dmBtn.disabled = false;
  }
});



// 2) ouvre la modale utilisateur + hydrater le bloc DM
async function openUserModal(userId){
  try{
    // Tu as déjà un endpoint profil; adapte l’URL si nécessaire
    const r = await fetch(`${BASE}/user_card.php?id=${encodeURIComponent(userId)}`, {
      credentials:'same-origin', cache:'no-store'
    });
    if (!r.ok) return;
    const j = await r.json();
    if (!j.ok || !j.user) return;

    hydrateUserModal(j.user);  // ta fonction existante
    showModal(userModal);      // ta fonction existante
  }catch(e){/* silencieux */}
}




// Appelle startActiveUsersLoop() après que la page est prête
document.addEventListener('DOMContentLoaded', startActiveUsersLoop);

// Si tu changes de salon: relance pour prendre en compte room_id
function onRoomChanged(){
  // ... ton code existant ...
  refreshActiveUsers(); // rafraîchit la liste pour ce salon
}



function closeUserModal(){
  userModal.hidden = true;
  chatModal.classList.remove('behind');
  umBody.textContent = '';
  umUserId = 0;
  dmTarget = null;
  dmRecipient.value = '';
  umBox.hidden = true;
}
umClose?.addEventListener('click', closeUserModal);
userModal?.addEventListener('click', e => { if (e.target === userModal) closeUserModal(); });


/* === DÉLÉGATION : clic pseudo dans le flux messages → modale profil === */

chatMsgs.addEventListener('click', (e) => {
  const a = e.target.closest('.userLink');
  if (!a) return;
  const uid = parseInt(a.dataset.userId, 10) || 0;
  if (!uid) return;
  openUserModalById(uid);
});


  try {
    const url = `${BASE}/api_user_profile.php?user_id=${uid}`;
    const r   = await fetch(url, { cache:'no-store', credentials:'same-origin' });
    if (!r.ok) {
      umBody.textContent = r.status === 404 ? 'Utilisateur introuvable.' : 'Erreur serveur.';
      return;
    }
    const j = await r.json();
    if (!j || !j.ok) {
      umBody.textContent = (j && j.error) || 'Erreur.';
      return;
    }

    const u = j.user;
    hydrateUserModal(u);  // initialise la cible DM avec avatar/pseudo si ≠ soi

    umName.textContent = u.pseudo || 'Profil';
    umBody.innerHTML =
      `<div style="display:flex;gap:12px;align-items:flex-start">
         ${u.avatar_url ? `<img src="${u.avatar_url}" alt="" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:1px solid var(--line)">` : ''}
         <div>
           ${u.city ? `<div><strong>Ville :</strong> ${escapeHtml(u.city)}</div>` : ''}
           ${u.sex ? `<div><strong>Sexe :</strong> ${escapeHtml(u.sex)}</div>` : ''}
           ${u.height_cm ? `<div><strong>Taille :</strong> ${u.height_cm} cm</div>` : ''}


           ${u.postal_code ? `<div><strong>CP :</strong> ${escapeHtml(u.postal_code)}</div>` : ''}
           ${u.relationship_status ? `<div><strong>Statut :</strong> ${escapeHtml(u.relationship_status)}</div>` : ''}
           ${u.bio ? `<div style="margin-top:6px;white-space:pre-wrap">${escapeHtml(u.bio)}</div>` : ''}
         </div>
       </div>`;
  } catch {
    umBody.textContent = 'Réseau indisponible.';
  }
});

// Clic sur un message -> insère @pseudo dans le champ de saisie
(function() {
  const chatMsgs = document.getElementById('chatMsgs');
  if (!chatMsgs) return;

  const getInput = () =>
    document.querySelector('#chatForm textarea, #chatForm input[name="message"], #chatInput');

  chatMsgs.addEventListener('click', function(e) {
    const bodyEl = e.target.closest('.msg-body');
    if (!bodyEl) return;

    const msgEl = bodyEl.closest('.msg');
    if (!msgEl) return;

    const pseudo = msgEl.dataset.user;
    if (!pseudo || pseudo === '—') return;

    const input = getInput();
    if (!input) return;

    const mention = '@' + pseudo + ' ';

    // enlève une mention déjà en début si présente
    const cleaned = input.value.replace(/^@\S+\s+/, '');
    input.value = mention + cleaned;

    input.focus();
    input.setSelectionRange(input.value.length, input.value.length);
  });
})();




/* =============================================================================
 *                                   BOOT
 * =============================================================================*/
 // Rien d’autre : la liste est chargée au boot plus haut.
