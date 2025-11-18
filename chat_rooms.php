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

<meta name="robots" content="noindex, nofollow">

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




.msg-footer{
  margin-top: 6px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}

.msg-likeBtn{
  border: none;
  background: transparent;
  color: var(--mut);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0;
}

.msg-likeBtn.is-liked{
  color: #f97316; /* ou autre couleur pour un like actif */
}

.msg-likeCount{
  min-width: 1.4em;
  text-align: left;
}




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


.mention {
  color: #D052F2;      /* bleu clair */
  font-weight: 600;    /* optionnel : plus visible */
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

/* --- Overlay général --- */
.overlay-dialog {
  display: none;                /* caché par défaut */
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  z-index: 2000;
  align-items: center;
  justify-content: center;
  padding: 20px;                /* évite la coupure en haut/bas */
  box-sizing: border-box;
}

/* Quand la modale est active */
.overlay-dialog.is-active {
  display: flex;
  animation: overlayFade 0.25s ease-out forwards;
}

/* Variante "info" (si tu veux custom plus tard) */
.overlay-dialog--info {}

/* --- Contenu de la modale --- */
.overlay-dialog__box {
  background: #fff;
  color: #000;
  padding: 22px 26px;
  border-radius: 10px;
  max-width: 420px;      /* limite max sur grand écran */
  width: 100%;           /* prend toute la largeur dispo dans le padding */
  max-height: 90vh;      /* jamais plus haut que l’écran */
  overflow-y: auto;      /* scroll interne si trop de texte */
  position: relative;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);

  opacity: 0;
  transform: translateY(10px);
}


.overlay-dialog__box li {
  font-size:20px;
}
/* Anim sur la boîte quand la modale est active */
.overlay-dialog.is-active .overlay-dialog__box {
  animation: modalPop 0.25s ease-out 0.05s forwards;
}

/* --- Croix --- */
.overlay-dialog__close {
  position: absolute;
  top: 8px;
  right: 10px;
  background: transparent;
  border: none;
  font-size: 24px;
  cursor: pointer;
}

/* --- Animations --- */
@keyframes overlayFade {
  from { opacity: 0; }
  to   { opacity: 1; }
}

@keyframes modalPop {
  from {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}



</style>

  <meta name="robots" content="noindex, nofollow">


</head>

<body>

<!-- Modale d'information système -->
<div id="systemNotice" class="overlay-dialog overlay-dialog--info">
  <div class="overlay-dialog__box">
    <button class="overlay-dialog__close" id="systemNoticeClose">&times;</button>

    <h2>Nouveauté</h2>

    <ul>
      <li>Amélioration de l’affichage des salons (salons privés avec 🔒).</li>
      <li>@mentions dans les messages.</li>
      <li>Affichage des avatars profil / DM.</li>
      <li>Sécurisation de la page de connexion (anti brute-force).</li>
      <li> <strong>Sécurité renforcée : </strong>mots de passe hashés, protection contre les tentatives de connexion abusives 
        et validation stricte des données.</li>

    </ul>

    <p>
      Le site est toujours en version <strong>Bêta</strong>.  
      N’hésite pas à remonter les bugs dans le salon “BUGS, SIGNALEMENT”.
    </p>

  </div>
</div>





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

        <h2 style="margin-top:16px;font-size:14px">Users en ligne</h2>
    <div id="presenceInline"></div>


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

    <input type="text" id="chatInput" name="body" placeholder="Écrire…" maxlength="200" autocomplete="off">
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

// État runtime général
let pollTimer   = null;     // Interval du polling messages
let pollToken   = 0;        // Invalide les anciens polls si on change de salon
let lastId      = 0;        // Dernier id message reçu
let currentRoom = 0;        // Room ouverte
let pollDelay   = 2000;     // 2s entre chaque fetch des messages

/* === Helpers =============================================================== */

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
      const priv   = Number(x.is_private) === 1;
      const last   = x.last_at ? new Date(x.last_at.replace(' ','T')).toLocaleString() : '—';
      const active = Number(x.active_count || 0);
      const activeLabel = active === 0 ? '0 en ligne' : (active === 1 ? '1 personne en ligne' : `${active} personnes en ligne`);
      return `
        <div class="row room"
             data-id="${x.id}"
             data-name="${escapeHtml(x.name)}"
             data-private="${priv ? 1 : 0}">
          <div><strong>${escapeHtml(x.name)}${priv ? ' 🔒' : ''}</strong></div>
          <div class="mut">${last} • ${activeLabel}</div>
        </div>`;
    }).join('');
  } catch {
    RLIST.innerHTML = '<div class="mut">Erreur.</div>';
  }
}

// Clic sur un salon
RLIST?.addEventListener('click', (e) => {
  const row = e.target.closest('.room');
  if (!row) return;
  const id   = parseInt(row.dataset.id, 10);
  const name = row.dataset.name;
  const priv = row.dataset.private === '1';

  if (priv) {
    document.getElementById('lock_room_id').value = id;
    document.getElementById('lockStatus').textContent = '';
    document.getElementById('lockModal').hidden = false;
  } else {
    openRoom(id, name);
  }
});

// Init liste salons + quota
checkCreateQuota();
loadRooms();

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


</script>



</body></html>
