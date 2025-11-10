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

.activeRow{
  display:flex;
  align-items:center;
  gap:8px;
  padding:6px 8px;
  border-radius:8px;
}

.activeRow.is-me { outline: 2px solid var(--brand); background: rgba(37,99,235,.08); }

/* Avatar dans les messages */
.msg-avatar{
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  display: block;
}

/* Contenu texte à côté */
.msg-content{
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.active-avatar{
  width:24px;
  height:24px;
  border-radius:50%;
  object-fit:cover;
  flex-shrink:0;
}




/* DM header avec avatar destinataire */
.dm-user{
  display:flex;
  align-items:center;
  gap:8px;
  margin-bottom:6px;
}
.dm-avatar{
  width:32px;
  height:32px;
  border-radius:50%;
  object-fit:cover;
  flex-shrink:0;
}
.dm-name{
  font-size:14px;
  color:var(--txt);
  font-weight:500;
}




.active-name{
  font-size:14px;
  color:#e5e7eb;
}

.meTag { font-size: .75rem; padding: 2px 6px; border: 1px solid var(--line); border-radius: 999px; margin-left: 6px; }


/* --- Modal principal --- */
#chatModal{position:fixed;inset:0;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center;}
#chatBox{background:var(--card);border:1px solid var(--line);border-radius:16px;width:min(900px,95vw);height:min(640px,90vh);display:flex;flex-direction:column;position:relative}
#chatHead{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid var(--line)}
#chatMsgs{flex:1;overflow:auto;padding:12px 14px;}
#chatForm{display:flex;gap:8px;padding:12px 14px;border-top:1px solid var(--line)}
#chatForm input[name="body"]{flex:1;min-height:46px;max-height:160px;background:#0b1220;color:var(--txt);border:1px solid var(--line);border-radius:10px;padding:10px}

/* --- Messages --- */
.msg{border:1px solid var(--line);border-radius:10px;padding:8px 10px;margin:8px 0;overflow-wrap:break-word}
.msg .meta{font-size:12px;color:var(--mut);margin-bottom:4px}

.msg-body { white-space:pre-wrap; }

.msg-body > span {
  font-weight: 500;
  letter-spacing: 0.5px;
}

.meta { color: var(--mut); } /* déjà présent chez toi */


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

.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:90}
.modal-overlay[hidden]{display:none}
.modal-box{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:16px;width:min(520px,92vw)}
.btn[disabled],
.btn[aria-disabled="true"]{
  opacity:.5;
  cursor:not-allowed;
  pointer-events:none;
}


/* On s'assure que #activeModal > #chatModal.*/
.modal { position: fixed; inset: 0; }
#chatModal   { z-index: 1002; } /* au-dessus de modal.behind */
#userModal   { z-index: 1003; }   /* au-dessus du chatModal */
#activeModal { z-index: 1004; }   /* au-dessus du userModal */
.modal.behind { z-index: 800; pointer-events: none; } /* passe visuellement derrière */



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
<input type="password" name="password" id="room_pwd" placeholder="Mot de passe" maxlength="20" style="display:none">
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


    
<!-- Modale utilisateurs actifs -->
<div id="activeModal" class="modal" hidden>
  <div class="modal-box">
    <div class="modal-head">
      <strong>Utilisateurs actifs</strong>
      <button id="activeClose" type="button" class="btn">X</button>
    </div>
    <div id="activeModalBody"></div>
  </div>
</div>




<div id="userModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="umName" hidden>
  <div class="modal-box">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h3 id="umName" style="margin:0">Profil</h3>
      <button id="umClose" class="btn" type="button" style="background:#374151">Fermer</button>
    </div>

    <div id="umBody" class="mut" style="margin-top:8px">Chargement…</div>






    




    <div id="umDMBox" hidden style="margin-top:10px">
 <img id="dmAvatar" class="dm-avatar" src="" alt="">  

     <div id="dmUser" class="dm-user">
       
      </div>
      <form id="dmSend" method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES) ?>">
        <input type="hidden" name="recipient_id" id="dmRecipient">
        <textarea name="body" id="dmBody" rows="3" maxlength="2000" placeholder="Votre message…"></textarea>
        <input type="file" name="image" accept="image/*">
        <button type="submit" id="dmBtn">Envoyer</button>
      </form>
      <div id="dmHint" class="muted" style="margin-top:6px"></div>
    </div>
  </div>
</div>






<div id="lockModal" class="modal-overlay"  role="dialog" aria-modal="true" hidden hidden>
  <div class="modal-box">
    <h3>Salon protégé</h3>
    <p class="mut">Entrez le mot de passe.</p>
    <form id="lockForm">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">
      <input type="hidden" name="room_id" id="lock_room_id">

      <input type="password" name="password" autocomplete="new-password" style="display:block" placeholder="Mot de passe" required>
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
<div id="chatModal" class="modal" hidden >
  <div id="chatBox">
    <div id="chatHead">
      <strong id="roomTitle">Salon</strong>
<button id="showActive" type="button">Actifs</button>


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
      <input type="color" id="msgColor" name="color" value="#FFFFFF" title="Choisir une couleur de message (optionnel)">

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

  // Meta: pseudo + date/heure
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
  meta.appendChild(who);

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
    span.textContent = m.body;
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

/* Ouverture / fermeture modale profil */
function openUserModal(){
  // S’assure que les modales sont au bon niveau du DOM
  if (userModal?.parentNode !== document.body) document.body.appendChild(userModal);
  if (chatModal?.parentNode !== document.body) document.body.appendChild(chatModal);

  // Donne la priorité visuelle à la fiche utilisateur
  chatModal.classList.add('behind');   // bloque les clics sur le chat
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

/* Délégation : clic sur pseudo dans le flux messages → card profil */
chatMsgs.addEventListener('click', async (e) => {
  const a = e.target.closest('.userLink');
  if (!a) return;

  const uid = parseInt(a.dataset.userId, 10) || 0;
  if (!uid) return;

  const isSelf = Number(uid) === Number(CURRENT_USER_ID);
  umBox.hidden = isSelf;   // Cache DM si c’est toi

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

/* =============================================================================
 *                                   BOOT
 * =============================================================================*/
 // Rien d’autre : la liste est chargée au boot plus haut.
</script>



</body></html>
