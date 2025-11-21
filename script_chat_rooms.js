
/* =============================================================================
 *  CHAT — logique côté client (version structurée + commentée)
 * =============================================================================
 * Hypothèses:
 * - Le backend vérifie room_id / CSRF / fichiers.
 * - APP_BASE et l’ID user courant sont disponibles côté PHP.
 * - Le HTML contient les IDs référencés ci-dessous.
 * =============================================================================*/

/* === Références DOM + état global ========================================= */
const BASE = '<?= APP_BASE ?>';                        // Base URL de l’app
const CURRENT_USER_ID = <?= (int)($_SESSION['user_id'] ?? 0) ?>;
const CSRF = '<?= htmlspecialchars($_SESSION["csrf"] ?? "", ENT_QUOTES) ?>'; // pour le POST vers chat_message_like.php.

const RLIST          = document.getElementById('rooms');          // Liste des salons
const presenceInline = document.getElementById('presenceInline'); // Div "users en ligne" (sidebar)
const NST            = document.getElementById('roomStatus');     // Zone statut création
const chatModal      = document.getElementById('chatModal');      // Overlay chat
const chatClose      = document.getElementById('chatClose');      // Bouton fermer chat
const chatMsgs       = document.getElementById('chatMsgs');       // Flux messages
const chatForm       = document.getElementById('chatForm');       // Form envoi msg
const roomIdInp      = document.getElementById('room_id');        // Hidden room_id
const roomTitle      = document.getElementById('roomTitle');      // Titre salon
const toBottom       = document.getElementById('toBottom');       // Bouton “aller en bas”
const roomDeleteBtn = document.getElementById('roomDeleteBtn');   // Bouton supprimer salon
const roomShareBtn  = document.getElementById('roomShareBtn'); // Bouton Partager
const urlParams     = new URLSearchParams(window.location.search); // URLSearchParams
const initialRoomId = parseInt(urlParams.get('room') || '0', 10) || 0; 



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

// État runtime général Variables globales d’état
let pollTimer   = null;     // Interval du polling messages
let pollToken   = 0;        // Invalide les anciens polls si on change de salon
let lastId      = 0;        // Dernier id message reçu
let currentRoom = 0;        // Room ouverte
let pollDelay   = 2000;     // 2s entre chaque fetch des messages
let currentRoomOwner = 0;  // ID du créateur du salon courant


/* === Helpers =============================================================== */

// Suppression d’un salon DELETE ROOM

roomDeleteBtn?.addEventListener('click', async () => {  
  if (!currentRoom) return; 

  const ok = confirm("Tu es sur de vouloir supprimer ce salon ?\nTous les messages associés seront définitivement supprimés.");
  if (!ok) return;

  const fd = new FormData();
  fd.append('room_id', String(currentRoom));
  fd.append('csrf', '<?= htmlspecialchars($_SESSION["csrf"] ?? "", ENT_QUOTES) ?>');

  roomDeleteBtn.disabled = true;

  try {
    const r = await fetch(`${BASE}/chat_room_delete.php`, {  
      method: 'POST',
      body: fd,
      cache: 'no-store',
      credentials: 'same-origin'
    });

    if (!r.ok) {
      alert("Erreur serveur ("+r.status+").");
      return;
    }

    const j = await r.json();
    if (!j.ok) {
      alert("Suppression impossible : " + (j.error || 'erreur'));
      return;
    }

    // On ferme le chat et on recharge la liste des salons
    chatModal.style.display = 'none';
    currentRoom = 0;
    await loadRooms();
  } catch (err) {
    console.error(err);
    alert("Erreur réseau pendant la suppression.");
  } finally {
    roomDeleteBtn.disabled = false;
  }
});





// Échappe le HTML (sécurité XSS)
function escapeHtml(s){
  return String(s).replace(/[&<>"']/g, m => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[m]));
}

// Détection si on est proche du bas (pour autoscroll)
function isNearBottom(el, px=80){
  return el.scrollHeight - el.scrollTop - el.clientHeight <= px;
}

// Scroll doux en bas du flux
function scrollToBottom(el, smooth=true){
  const last = el.lastElementChild;
  if (last) last.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'end' });
}

/* === Persistance locale: images déjà “dévoilées” =========================== */

const VIEWED_KEY = 'chat_viewed_images_v1';
const viewed = new Set(JSON.parse(localStorage.getItem(VIEWED_KEY) || '[]'));
function isViewed(src){ return viewed.has(src); }
function markViewed(src){
  if (!src) return;
  viewed.add(src);
  localStorage.setItem(VIEWED_KEY, JSON.stringify([...viewed]));
}

/* ===SUP ROOM CONTROL ” =========================== */


// Affiche ou cache le bouton "Supprimer ce salon" selon si on est le créateur ou pas
function updateRoomOwnerControls(){ // appelé après ouverture d’un salon
  if (!roomDeleteBtn) return; // sécurité : si le bouton n'existe pas, on ne fait rien

  // On compare l'id du créateur du salon avec l'id de l'utilisateur connecté
  const isOwner = Number(currentRoomOwner) === Number(CURRENT_USER_ID);

  // Si c'est le créateur → on montre le bouton, sinon on le cache
  roomDeleteBtn.style.display = isOwner ? 'inline-block' : 'none';
}


// PARTAGER SALON
// Construit l'URL publique d'un salon à partir de son id. 
// Exemple de résultat: https://tchat-direct.com/chat_rooms.php?room=42
function buildRoomShareUrl(roomId){
  if (!roomId) return '';

  // BASE = ex: "/tchat_direct" (APP_BASE côté PHP)
  // On construit une URL relative vers chat_rooms.php avec un paramètre ?room=
  const relative = `${BASE}/chat_rooms.php?room=${encodeURIComponent(roomId)}`;

  // On la convertit en URL absolue (avec https://tondomaine.com)
  try {
    return new URL(relative, window.location.origin).href;
  } catch {
    // fallback : on renvoie la relative si jamais l'objet URL n'est pas supporté
    return relative;
  }
}

// Partage la room courante:
// - si navigator.share dispo (mobile, certains navigateurs) → partage natif
// - sinon → copie l'URL dans le presse-papiers
async function shareCurrentRoom(){
  if (!currentRoom) {
    alert("Aucun salon ouvert à partager.");
    return;
  }

  const url = buildRoomShareUrl(currentRoom);
  const title = roomTitle?.textContent || 'Salon';

  // 1) Web Share API (sur mobile, certains navigateurs de bureau)
  if (navigator.share) {
    try {
      await navigator.share({
        title: `Rejoins mon salon "${title}"`,
        text: `Viens discuter avec moi sur ce salon : "${title}"`,
        url
      });
      return;
    } catch (err) {
      // Si l'utilisateur annule ou erreur → on tombe sur le fallback clipboard
      console.warn('Share annulé ou impossible, fallback clipboard.', err);
    }
  }

  // 2) Fallback: copie dans le presse-papiers
  if (navigator.clipboard && navigator.clipboard.writeText) {
    try {
      await navigator.clipboard.writeText(url);
      alert("Lien du salon copié dans le presse-papiers :\n" + url);
      return;
    } catch (err) {
      console.warn('Clipboard write échoué', err);
    }
  }

  // 3) Fallback ultime: on affiche un prompt à l'ancienne
  prompt("Copie manuellement ce lien :", url);
}
roomShareBtn?.addEventListener('click', () => {
  shareCurrentRoom();
});



/* === Modale image ========================================================== */

function openImgModal(src){
  if (!imgModal || !imgModalImg) return;   // HTML optionnel
  imgModalImg.src = src;
  imgModal.hidden = false;
}
function closeImgModal(){
  if (!imgModal || !imgModalImg) return;
  imgModal.hidden = true;
  imgModalImg.removeAttribute('src');
}
// Ferme la modale image en cliquant sur le fond
imgModal?.addEventListener('click', e => { if (e.target === imgModal) closeImgModal(); });

// Échap ferme toutes les modales (image, user, lock)
document.addEventListener('keydown', e => {
  if (e.key !== 'Escape') return;
  if (!imgModal?.hidden) closeImgModal();
  if (userModal && !userModal.hidden) userModal.hidden = true;
  if (lockModal && !lockModal.hidden) lockModal.hidden = true;
});

/* === Modale “actifs” (liste complète) ===================================== */

activeModal?.addEventListener('click', e => {
  if (e.target === e.currentTarget) activeModal.hidden = true;
});
activeClose?.addEventListener('click', () => { activeModal.hidden = true; });

// Ouvre la modale et charge la liste d’actifs du salon courant
showActive?.addEventListener('click', async () => {
  if (!currentRoom) return;

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
          const avatarSrc = u.avatar_url ? `${BASE}/${u.avatar_url}` : `${BASE}/uploads/avatars/default.png`;
          return `
            <div class="activeRow" data-id="${u.id}">
              <img class="active-avatar" src="${avatarSrc}" alt="${safeName}" loading="lazy">
              <span class="active-name">
                ${safeName}${u.id === CURRENT_USER_ID ? ' <span class="meTag">vous</span>' : ''}
              </span>
            </div>`;
        }).join('')
      : '<div class="mut">Aucun actif</div>';

    activeModal.hidden = false;
  } catch {
    activeModalBody.innerHTML = '<div class="mut">Erreur de chargement</div>';
    activeModal.hidden = false;
  }
});

/* =============================================================================
 *                                SALONS
 * =============================================================================*/

// Création d’un salon
document.getElementById('newRoom')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type="submit"]');
  btn.disabled = true;
  NST.textContent = '';

  try {
    const r = await fetch(`${BASE}/chat_room_create.php`, { method:'POST', body:new FormData(e.target) });
    if (r.status === 429) {
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
      loadRooms();
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

// Compte à rebours “quota création”
let createUntil = 0;
function startCreateCountdown(sec){ createUntil = Date.now() + sec*1000; tickCreateCountdown(); }
function tickCreateCountdown(){
  const btn = document.querySelector('#newRoom button[type="submit"]');
  const left = Math.max(0, Math.floor((createUntil - Date.now())/1000));
  if (!btn) return;

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

// Format d’affichage d’une durée
function formatDelay(sec){
  sec = Math.max(0, Math.floor(sec));
  const h = Math.floor(sec/3600);
  const m = Math.floor((sec%3600)/60);
  const s = sec%60;
  if (h > 0) return `${h} h ${m.toString().padStart(2,'0')} min`;
  if (m > 0) return `${m} min ${s.toString().padStart(2,'0')} s`;
  return `${s} s`;
}

// Charger la liste des salons
async function loadRooms(){
  if (!RLIST) return;
  RLIST.innerHTML = '<div class="mut">Chargement…</div>';
  try{
    const r = await fetch(`${BASE}/chat_rooms_list.php`, { cache:'no-store', credentials:'same-origin' });
    const j = await r.json();
    if (!j.ok) throw 0;

    if (!j.rooms || j.rooms.length === 0){
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
         data-private="${priv ? 1 : 0}"
         data-created-by="${x.created_by}">
      <div><strong>${escapeHtml(x.name)}${priv ? ' 🔒' : ''}</strong></div>
      <div class="mut">${last}</div>
    </div>`;
}).join('');
  } catch {
    RLIST.innerHTML = '<div class="mut">Erreur.</div>';
  }
}




// Clic sur un salon
RLIST?.addEventListener('click', (e) => { 
  // On remonte jusqu'à l'élément .room le plus proche du clic
  const row = e.target.closest('.room');
  if (!row) return; // si on n'a pas cliqué sur une room → on sort

  // Id numérique du salon
  const id   = parseInt(row.dataset.id, 10) || 0;

  // Nom du salon (data-name posé dans loadRooms)
  const name = row.dataset.name || 'Salon';

  // Flag "privé" stocké dans data-private ("1" ou "0")
  const priv = row.dataset.private === '1';

  // Id du créateur du salon (remonté par l'API dans created_by)
  const createdBy = Number(row.dataset.createdBy || 0);

  // On met à jour l'état global : salon courant + proprio courant
  currentRoom      = id;
  currentRoomOwner = createdBy;

  // On met à jour la visibilité du bouton "Supprimer ce salon"
  // → s'affiche seulement si currentRoomOwner === CURRENT_USER_ID
  updateRoomOwnerControls();

  if (priv) {
    // Salon privé : on ouvre la modale de mot de passe
    const lockRoomIdInp = document.getElementById('lock_room_id');
    const lockStatusEl  = document.getElementById('lockStatus');
    const lockModalEl   = document.getElementById('lockModal');

    if (lockRoomIdInp) lockRoomIdInp.value = id;
    if (lockStatusEl)  lockStatusEl.textContent = '';
    if (lockModalEl)   lockModalEl.hidden = false;
  } else {
    // Salon public : on ouvre directement le chat
    openRoom(id, name);
  }
});

// Init liste salons + quota
checkCreateQuota();
loadRooms();




// Après avoir chargé les salons, on tente d'ouvrir celui passé en ?room=
setTimeout(() => {
  if (!initialRoomId) return; // pas de paramètre ?room= dans l'URL

  // On cherche la room correspondante dans la liste
  const row = RLIST?.querySelector(`.room[data-id="${initialRoomId}"]`);
  if (!row) return; // si la room n'existe pas / pas encore affichée

  // On la centre un peu dans la vue (optionnel)
  row.scrollIntoView({ block: 'center', behavior: 'smooth' });

  // On simule un clic sur la room
  // → ça déclenche ton listener RLIST.addEventListener('click', ...),
  //   donc toute la logique privé/public + openRoom() est réutilisée
  row.click();
}, 500);



/* =============================================================================
 *                          PRÉSENCE EN TEMPS RÉEL (globale)
 * =============================================================================
 * - Ping last_seen toutes 2s (onglet courant identifié par session_key).
 * - Rafraîchit #presenceInline toutes 5s.
 * - Si un salon est ouvert: liste des actifs du salon; sinon: liste globale.
 */

const PRESENCE_KEY = localStorage.getItem('presence_uuid')
  || (() => { const u = crypto.randomUUID(); localStorage.setItem('presence_uuid', u); return u; })();

let presenceTimer = null;       // Ping BDD
let presenceListTimer = null;   // Refresh UI

function startPresence() {
  stopPresence(); // pas de doublon

  // Ping last_seen
  presenceTimer = setInterval(presencePing, 2000);
  presencePing();

  // Rafraîchit l’affichage
  if (presenceInline) {
    refreshInlinePresence();
    presenceListTimer = setInterval(refreshInlinePresence, 500);
  }
}
function stopPresence() {
  if (presenceTimer)      { clearInterval(presenceTimer); presenceTimer = null; }
  if (presenceListTimer)  { clearInterval(presenceListTimer); presenceListTimer = null; }
}

// Envoie le ping de présence
async function presencePing() {
  try {
    const body = new URLSearchParams({
      session_key: PRESENCE_KEY,
      room_id: currentRoom ? String(currentRoom) : ''   // optionnel
    });
    await fetch(`${BASE}/chat_presence_ping.php`, {
      method:'POST', body, credentials:'same-origin', cache:'no-store'
    });
  } catch {}
}

// Met à jour la div #presenceInline avec la liste d’actifs
async function refreshInlinePresence(){
  if (!presenceInline) return;
  const url = currentRoom
    ? `${BASE}/chat_presence_list.php?room_id=${currentRoom}`
    : `${BASE}/chat_presence_list.php`;

  try {
    const r = await fetch(url, { cache:'no-store', credentials:'same-origin' });
    const j = await r.json();
    const list = Array.isArray(j?.users) ? j.users : [];

    presenceInline.innerHTML = list.length
      ? list.map(u => {
          const safeName = escapeHtml(u.pseudo || '—');
          const avatarSrc = u.avatar_url ? `${BASE}/${u.avatar_url}` : `${BASE}/uploads/avatars/default.png`;
          return `
            <div class="activeRow" data-id="${u.id}">
              <img class="active-avatar" src="${avatarSrc}" alt="${safeName}" loading="lazy">
              <span class="active-name">
                ${safeName}${u.id === CURRENT_USER_ID ? ' <span class="meTag">vous</span>' : ''}
              </span>
            </div>`;
        }).join('')
      : '<div class="mut">Aucun utilisateur en ligne</div>';
  } catch {
    presenceInline.innerHTML = '<div class="mut">Erreur de chargement</div>';
  }
}

/* =============================================================================
 *                          OUVERTURE / FERMETURE SALON
 * =============================================================================*/

function openRoom(id, name){
  // Met le chat devant et ferme une éventuelle fiche user
  chatModal.classList.remove('behind');
  if (userModal) userModal.hidden = true;

  // Reset état
  currentRoom = id;
  lastId = 0;
  roomIdInp.value = id;
  roomTitle.textContent = name;
  chatMsgs.innerHTML = '';
  toBottom.style.display = 'none';
  chatModal.style.display = 'flex';

  // Redémarre polling + présence
  stopPolling();  startPolling();
  stopPresence(); startPresence();

  // Focus rapide
  const bodyInput = chatForm.querySelector('[name="body"]');
  if (bodyInput) setTimeout(() => bodyInput.focus(), 50);
}

// Ferme le chat si on clique la croix ou l’overlay
chatClose?.addEventListener('click', () => {
  chatModal.style.display = 'none';
  stopPolling();
  stopPresence();
});
chatModal?.addEventListener('click', (e) => {
  if (e.target === chatModal) {
    chatModal.style.display = 'none';
    stopPolling();
    stopPresence();
  }
});

/* =============================================================================
 *                                 MESSAGES
 * =============================================================================*/

// Bouton “aller en bas”
chatMsgs?.addEventListener('scroll', () => {
  toBottom.style.display = isNearBottom(chatMsgs) ? 'none' : 'block';
});
toBottom?.addEventListener('click', () => scrollToBottom(chatMsgs, true));

// Rendu d’un message
function renderMessage(m){
  const el = document.createElement('div');
  el.className = 'msg';

  // Avatar
  const avatar = document.createElement('img');
  avatar.className = 'msg-avatar';
  avatar.src = m.avatar_url ? `${BASE}/${m.avatar_url}` : `${BASE}/uploads/avatars/default.png`;
  avatar.alt = m.sender || '';
  avatar.loading = 'lazy';
  el.appendChild(avatar);

  // Conteneur texte
  const content = document.createElement('div');
  content.className = 'msg-content';

  // En-tête (pseudo + datetime)
  const meta = document.createElement('div');
  meta.className = 'meta';

  const who = document.createElement(m.sender_id ? 'button' : 'span');
  if (m.sender_id) {
    who.type = 'button';
    who.className = 'userLink';
    who.dataset.userId = String(m.sender_id);
    who.title = 'Voir le profil';
    who.style.all = 'unset';
    who.style.cursor = 'pointer';
    who.style.color = '#93c5fd';
  }
  who.textContent = typeof m.sender === 'string' ? m.sender : '—';
  el.dataset.user = who.textContent || '';
  meta.appendChild(who);

  if (m.created_at) {
    const when = new Date(String(m.created_at).replace(' ','T')).toLocaleString();
    const sep = document.createElement('span');
    sep.textContent = ' • ' + when;
    meta.appendChild(sep);
  }
  content.appendChild(meta);

  // Corps texte (mentions colorées)
  if (m.body) {
    const body = document.createElement('div');
    body.className = 'msg-body';
    body.style.whiteSpace = 'pre-wrap';
    const span = document.createElement('span');
    const mentionRegex = /(@[A-Za-zÀ-ÖØ-öø-ÿ0-9_.-]+)/g;
    const htmlWithMentions = String(m.body).replace(mentionRegex, '<span class="mention">$1</span>');
    span.innerHTML = htmlWithMentions;
    if (m.color && /^#[0-9A-Fa-f]{6}$/.test(m.color)) span.style.color = m.color;
    body.appendChild(span);
    content.appendChild(body);
  }

  // Image éventuelle
  if (m.file_url && /^image\//.test(m.file_mime || '')) {
    const src = m.file_url;
    if (isViewed(src)) {
      const img = document.createElement('img');
      Object.assign(img, { src, alt:'image', loading:'lazy' });
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
        Object.assign(img, { src, alt:'image', loading:'lazy' });
        img.className = 'chat-img';
        img.addEventListener('click', () => openImgModal(src));
        veil.replaceWith(img);
      });
      content.appendChild(veil);
    }
  }


    // Footer actions (likes, etc.)
  const footer = document.createElement('div');
  footer.className = 'msg-footer';

  // Bouton like
  const likeBtn = document.createElement('button');
  likeBtn.type = 'button';
  likeBtn.className = 'msg-likeBtn';
  likeBtn.dataset.messageId = m.id;

  // Texte du bouton + compteur
  const count = Number(m.like_count || 0);
  const liked = Number(m.liked_by_me || 0) === 1;

  likeBtn.innerHTML = liked
    ? `❤️ <span class="msg-likeCount">${count}</span>`
    : `🤍 <span class="msg-likeCount">${count}</span>`;

  if (liked) likeBtn.classList.add('is-liked');

  // Clique sur like
  likeBtn.addEventListener('click', () => handleLikeClick(likeBtn));

  footer.appendChild(likeBtn);
  content.appendChild(footer);


  el.appendChild(content);
  return el;
}



async function handleLikeClick(btn){
  const msgId = btn.dataset.messageId;
  if (!msgId || !CSRF) return;

  // Désactive temporairement pour éviter les doubles clics
  btn.disabled = true;

  try{
    const fd = new FormData();
    fd.append('message_id', msgId);
    fd.append('csrf', CSRF);

    const r = await fetch(`${BASE}/chat_message_like.php`, {
      method: 'POST',
      body: fd,
      cache: 'no-store',
      credentials: 'same-origin'
    });

    if (!r.ok){
      // éventuellement affichage d’erreur silencieuse
      console.warn('HTTP erreur like:', r.status);
      return;
    }

    const j = await r.json();
    if (!j.ok) {
      console.warn('Erreur like:', j.error);
      return;
    }

    // MAJ de l’affichage local
    const spanCount = btn.querySelector('.msg-likeCount');
    if (spanCount && typeof j.like_count !== 'undefined') {
      spanCount.textContent = j.like_count;
    }

    // Vu ton backend actuel, c’est un INSERT IGNORE → pas d’unlike.
    // On considère donc que le clic ne peut qu’ "activer" le like.
    btn.classList.add('is-liked');
    btn.innerHTML = `❤️ <span class="msg-likeCount">${j.like_count ?? 0}</span>`;
  }catch(err){
    console.error('Like failed:', err);
  }finally{
    btn.disabled = false;
  }
}


// Récupère les nouveaux messages
async function fetchMessages(){
  if (!currentRoom) return;
  try{
    const r = await fetch(`${BASE}/chat_messages_fetch.php?room_id=${currentRoom}&after_id=${lastId}`, {
      cache:'no-store', credentials:'same-origin'
    });
    if (!r.ok) {
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
  } catch {}
}

// Polling des messages (2s)
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
  pollToken++; // invalide la boucle courante
  if (pollTimer){ clearInterval(pollTimer); pollTimer = null; }
}

// Compression client des images avant upload
chatForm?.querySelector('input[type="file"]')?.addEventListener('change', async (e) => {
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
  canvas.getContext('2d').drawImage(img, 0, 0, width, height);

  const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.8));
  const compressed = new File([blob], file.name.replace(/\.\w+$/, '.jpg'), { type:'image/jpeg' });

  const dt = new DataTransfer(); dt.items.add(compressed);
  e.target.files = dt.files;

  URL.revokeObjectURL(url);
});

// Envoi d’un message (texte + image)
chatForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = chatForm.querySelector('button[type="submit"]');
  btn.disabled = true;

  try{
    const r = await fetch(`${BASE}/chat_message_send.php`, {
      method:'POST', body:new FormData(chatForm), credentials:'same-origin'
    });

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

// État de la cible DM
let dmTarget = null;
let umUserId = 0;

// Hydrate la fiche utilisateur + prépare le DM
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

  dmTarget = user.id;
  const name = user.pseudo || 'Utilisateur';

  const avatarEl = document.getElementById('dmAvatar');
  const nameEl   = document.getElementById('dmName');

  if (avatarEl) {
    const src = user.avatar_url ? `${BASE}/${user.avatar_url}` : `${BASE}/uploads/avatars/default.png`;
    avatarEl.src = src;
    avatarEl.alt = name;
  }
  if (nameEl) nameEl.textContent = name;

  umBox.hidden = false;
  dmRecipient.value = String(dmTarget);
  dmBody.value = '';
  dmHint.textContent = `Message privé à ${name}`;
}

// Envoi DM
dmForm?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  if (!dmTarget) return;
  const fd = new FormData(dmForm);

  dmBtn.disabled = true;
  dmHint.textContent = 'Envoi…';
  try{
    const r = await fetch(`${BASE}/chat_dm_send.php`, 
    { method:'POST', body:fd, cache:'no-store', credentials:'same-origin' });
    if(!r.ok){
      const text = await r.text();
      throw new Error('HTTP '+r.status+' '+text);
    }
    const j = await r.json();
    if(!j.ok) throw new Error(j.error||'error');
    dmBody.value = '';
const imgInput = dmForm.querySelector('input[name="image"]');
if (imgInput) imgInput.value = '';    dmHint.textContent = 'Message envoyé.';
  }catch(err){
    const msg = String(err.message||'Erreur');
    dmHint.textContent = /rate/.test(msg) ? 'Trop de messages. Réessayez dans un instant.' : 'Échec de l’envoi.';
  }finally{
    dmBtn.disabled = false;
  }
});

// Ouvre/ferme la fiche utilisateur
function openUserModal(){
  if (userModal?.parentNode !== document.body) document.body.appendChild(userModal);
  if (chatModal?.parentNode !== document.body) document.body.appendChild(chatModal);
  chatModal.classList.add('behind');
  userModal.hidden = false;
  umBox.hidden = true;
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

// Charge fiche profil par ID et l’affiche
async function openUserProfileById(uid) {
  uid = parseInt(uid, 10) || 0;
  if (!uid) return;

  const isSelf = Number(uid) === Number(CURRENT_USER_ID);
  umBox.hidden = isSelf;

  umUserId = uid;
  umName.textContent = 'Profil';
  umBody.textContent = 'Chargement…';

  openUserModal();

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
    hydrateUserModal(u);

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
}

// Clic pseudo dans le flux → fiche profil
chatMsgs?.addEventListener('click', (e) => {
  const a = e.target.closest('.userLink');
  if (!a) return;
  openUserProfileById(a.dataset.userId);
});

// Clic user dans “Users en ligne” → même fiche
presenceInline?.addEventListener('click', (e) => {
  const row = e.target.closest('.activeRow');
  if (!row) return;
  openUserProfileById(row.dataset.id);
});

// Clic sur le corps d’un message → pré-remplit @mention
(function() {
  const chatMsgsLocal = document.getElementById('chatMsgs');
  if (!chatMsgsLocal) return;

  const getInput = () =>
    document.querySelector('#chatForm textarea, #chatForm input[name="message"], #chatInput');

  chatMsgsLocal.addEventListener('click', function(e) {
    const bodyEl = e.target.closest('.msg-body');
    if (!bodyEl) return;

    const msgEl = bodyEl.closest('.msg');
    if (!msgEl) return;

    const pseudo = msgEl.dataset.user;
    if (!pseudo || pseudo === '—') return;

    const input = getInput();
    if (!input) return;

    const mention = '@' + pseudo + ' ';
    const cleaned = input.value.replace(/^@\S+\s+/, '');
    input.value = mention + cleaned;

    input.focus();
    input.setSelectionRange(input.value.length, input.value.length);
  });
})();

/* =============================================================================
 *                                   BOOT
 * =============================================================================
 * - La liste des salons est chargée au boot (loadRooms()).
 * - On démarre aussi la présence globale (ping + liste inline).
 */
startPresence();
</script>


<script>
const systemNotice = document.getElementById('systemNotice');
const systemNoticeClose = document.getElementById('systemNoticeClose');

// Ouvrir la modale
function openSystemNotice() {
  if (!systemNotice) return;
  systemNotice.classList.add('is-active');
}

// Fermer la modale
function closeSystemNotice() {
  if (!systemNotice) return;
  systemNotice.classList.remove('is-active');
}

// Ouverture auto au chargement
document.addEventListener('DOMContentLoaded', () => {
  openSystemNotice();
});

// Bouton fermer (croix)
systemNoticeClose?.addEventListener('click', closeSystemNotice);

// Fermer en cliquant en dehors de la boîte
systemNotice?.addEventListener('click', (e) => {
  if (e.target === systemNotice) {
    closeSystemNotice();
  }
});

// Fermer avec ESC
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeSystemNotice();
  }
});

