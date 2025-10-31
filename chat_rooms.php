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
  .btn{background:var(--brand);color:#fff;border:none;border-radius:12px;padding:8px 12px;text-decoration:none;cursor:pointer}
  .row{display:flex;align-items:center;justify-content:space-between;padding:10px;border:1px solid var(--line);border-radius:12px;margin:8px 0}
  .mut{color:var(--mut)}
  .rooms{margin-top:10px}
  .room{cursor:pointer}
  #chatModal{position:fixed;inset:0;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center;z-index:50}
  #chatBox{background:var(--card);border:1px solid var(--line);border-radius:16px;width:min(900px,95vw);height:min(640px,90vh);display:flex;flex-direction:column}
  #chatHead{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid var(--line)}
  #chatMsgs{flex:1;overflow:auto;padding:12px 14px}

  #chatForm{display:flex;gap:8px;padding:12px 14px;border-top:1px solid var(--line)}

#chatForm input[name="body"]{flex:1;min-height:46px;max-height:160px;background:#0b1220;color:var(--txt);border:1px solid var(--line);border-radius:10px;padding:10px}
  .msg{border:1px solid var(--line);border-radius:10px;padding:8px 10px;margin:8px 0; overflow-wrap: break-word;}
  .msg .meta{font-size:12px;color:var(--mut);margin-bottom:4px}

  #toBottom{
  position:absolute; right:18px; bottom:82px;
  display:none; border:1px solid var(--line); background:#111827; color:var(--txt);
  border-radius:999px; padding:6px 10px; cursor:pointer; box-shadow:0 2px 10px rgba(0,0,0,.25)
}
#chatBox{ position:relative; } /* pour positionner le bouton */



/* tu peux le placer dans le <style> existant */
.imageVeil{
    position: relative;
    width: 20%;
    min-height: 10px;                /* hauteur mini pour donner une zone cliquable */
    border: 1px solid #334155;        /* cohérent avec ta palette */
    border-radius: 8px;
    background-size: cover;           /* couvre toute la zone */
    background-position: center;
    overflow: hidden;
    cursor: pointer;
  }


  
  .imageVeil--blur::after{
    /* couche d’effet : flou + assombrissement pour lire le texte */
    content: "";
    position: absolute; inset: 0;
    backdrop-filter: blur(14px);
    background: rgba(0,0,0,.35);
  }


.imageVeil span{ color:var(--mut); font-size:14px; background:#111827;
  padding:8px 10px; border-radius:999px; border:1px solid var(--line);
}
.imageVeil--blur{ background-size:cover; background-position:center; }
.imageVeil--blur::after{
  content:""; position:absolute; inset:0; backdrop-filter: blur(16px);
  background: rgba(0,0,0,.35); border-radius:10px;
}
#imgModal{ position:fixed; inset:0; background:rgb(0 0 0 / 65%);
  display:flex; align-items:center; justify-content:center; z-index:70;
}
#imgModal[hidden]{ display:none; }
.imgModal__box{ max-width:55vw; max-height:40vh; }
#imgModalImg{ max-width:85vw; max-height:65vh; border-radius:12px; display:block; }

</style>
</head><body>
<div class="wrap">
  <div class="card">
    <h1 style="margin:0 0 6px">Salons de discussion</h1>
    <form id="newRoom" autocomplete="off" style="display:flex;gap:8px;align-items:center;margin-top:10px">
      <input name="name" maxlength="60" placeholder="Nom du salon (ex: Discu Sympa)" required
             style="flex:1;padding:10px;border-radius:10px;border:1px solid var(--line);background:#0b1220;color:var(--txt)">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'],ENT_QUOTES) ?>">
      <button class="btn" type="submit">Créer</button>
      <span id="roomStatus" class="mut"></span>
    </form>

    <div class="rooms" id="rooms"></div>
  </div>

  <p><a class="btn" href="<?= APP_BASE ?>/index.php">&larr; Retour</a></p>
</div>

<!-- Modal chat -->
<div id="chatModal">
  <div id="chatBox">
    <div id="chatHead">
      <strong id="roomTitle">Salon</strong>
      <button id="chatClose" class="btn" type="button" style="background:#374151">Fermer</button>
    </div>
    <div id="chatMsgs"></div>
    <button id="toBottom" type="button" aria-label="Aller en bas">▼</button>



<form id="chatForm" enctype="multipart/form-data" style="display:flex;gap:8px">
  <input type="text" name="body" placeholder="Écrire…" maxlength="2000" autocomplete="off" style="flex:1">
  <input type="file" name="image" accept="image/jpeg,image/png,image/webp" />
  <input type="hidden" name="room_id" id="room_id" value="">
  <input type="hidden" name="csrf"  value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">
  <button type="submit">Envoyer</button>
</form>

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
  NST.textContent = '';
  try{
    const r = await fetch(`${BASE}/chat_room_create.php`, { method: 'POST', body: new FormData(e.target) });
    const j = await r.json();
    if (j.ok){
      NST.style.color = '#34d399';
      NST.textContent = 'Créé';
      e.target.reset();
      loadRooms();                 // recharge la liste
    }else{
      NST.style.color = '#f87171';
      NST.textContent = j.error || 'Erreur';
    }
  }catch{
    NST.style.color = '#f87171';
    NST.textContent = 'Réseau';
  }
});

/* Récupère la liste des salons et l’affiche */
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
    RLIST.innerHTML = j.rooms.map(x => `
      <div class="row room" data-id="${x.id}" data-name="${escapeHtml(x.name)}">
        <div><strong>${escapeHtml(x.name)}</strong></div>
        <div class="mut">${x.last_at ? new Date(x.last_at.replace(' ','T')).toLocaleString() : '—'}</div>
      </div>
    `).join('');
  }catch{
    RLIST.innerHTML = '<div class="mut">Erreur.</div>';
  }
}

/* Click sur un salon -> ouverture du chat associé */
RLIST.addEventListener('click', (e) => {
  const row = e.target.closest('.room');
  if (!row) return;
  openRoom(parseInt(row.dataset.id, 10), row.dataset.name);
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
      img.style.maxWidth = '100%';
      img.style.borderRadius = '8px';
      img.style.cursor = 'zoom-in';
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
        img.style.maxWidth = '100%';
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
    const j = await r.json();
    if (!j.ok) return;

    if (j.messages.length){
      // Si l’utilisateur est déjà près du bas, on garde l’auto-scroll.
      const stick = isNearBottom(chatMsgs);
      const frag = document.createDocumentFragment();

      j.messages.forEach(m => {
        frag.appendChild(renderMessage(m));
        lastId = Math.max(lastId, m.id);
      });

      chatMsgs.appendChild(frag);
      if (stick) scrollToBottom(chatMsgs, true);

      // Mise à jour visibilité du bouton
      toBottom.style.display = isNearBottom(chatMsgs) ? 'none' : 'block';
    }
  }catch{/* silencieux */}
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
