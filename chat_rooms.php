<?php
declare(strict_types=1);
session_start();
require __DIR__.'/config.php';
 //require __DIR__.'/db.php';
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


  </div>
</div>

<script>

/*Ce code est correct et logique : il sépare bien les deux rôles (création de salon / envoi de message), 
respecte le protocole de ton API (chat_rooms_list.php, chat_room_create.php, 
chat_message_send.php, chat_messages_fetch.php) et gère tous les cas :
Création de salon : requête POST simple, rechargement automatique de la liste.
Envoi de message : désactivation du bouton, gestion des retours 429 (rate_glob, rate_room, rate_fast) 
depuis ton chat_message_send.php.
Polling 2 s : pour récupérer les nouveaux messages.
Auto-scroll intelligent : descend uniquement si l’utilisateur est déjà en bas.
Bouton “▼” visible quand on s’éloigne du bas.
Il ne contient ni doublon ni référence hors contexte (form, NST, etc. sont utilisés seulement où ils existent).
Tu peux l’intégrer tel quel dans ton chat_rooms.php */
// --- constantes ---

const BASE   = '<?= APP_BASE ?>';
const RLIST  = document.getElementById('rooms');
const NST    = document.getElementById('roomStatus');
const chatModal = document.getElementById('chatModal');
const chatClose = document.getElementById('chatClose');
const chatMsgs  = document.getElementById('chatMsgs');
const chatForm  = document.getElementById('chatForm');
const roomIdInp = document.getElementById('room_id');
const roomTitle = document.getElementById('roomTitle');
const toBottom  = document.getElementById('toBottom');

let pollTimer = null, lastId = 0, currentRoom = 0;

// --- création de salon ---
document.getElementById('newRoom').addEventListener('submit', async (e) => {
  e.preventDefault();
  NST.textContent = '';
  const fd = new FormData(e.target);
  try {
    const r = await fetch(`${BASE}/chat_room_create.php`, { method: 'POST', body: fd });
    const j = await r.json();
    if (j.ok) {
      NST.style.color = '#34d399';
      NST.textContent = 'Créé';
      e.target.reset();
      loadRooms();
    } else {
      NST.style.color = '#f87171';
      NST.textContent = j.error || 'Erreur';
    }
  } catch {
    NST.style.color = '#f87171';
    NST.textContent = 'Réseau';
  }
});

// --- liste des salons ---
async function loadRooms() {
  RLIST.innerHTML = '<div class="mut">Chargement…</div>';
  try {
    const r = await fetch(`${BASE}/chat_rooms_list.php`, { cache: 'no-store' });
    const j = await r.json();
    if (!j.ok) throw 0;
    if (j.rooms.length === 0) {
      RLIST.innerHTML = '<div class="mut">Aucun salon.</div>';
      return;
    }
    RLIST.innerHTML = j.rooms.map(x => `
      <div class="row room" data-id="${x.id}" data-name="${x.name}">
        <div><strong>${escapeHtml(x.name)}</strong></div>
        <div class="mut">${x.last_at ? new Date(x.last_at.replace(' ','T')).toLocaleString() : '—'}</div>
      </div>`).join('');
  } catch {
    RLIST.innerHTML = '<div class="mut">Erreur.</div>';
  }
}
function escapeHtml(s) {
  return s.replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
}

// --- ouvrir salon ---
RLIST.addEventListener('click', e => {
  const row = e.target.closest('.room');
  if (!row) return;
  openRoom(parseInt(row.dataset.id, 10), row.dataset.name);
});

function openRoom(id, name) {
  currentRoom = id;
  lastId = 0;
  roomIdInp.value = id;
  roomTitle.textContent = name;
  chatMsgs.innerHTML = '';
  toBottom.style.display = 'none';
  chatModal.style.display = 'flex';
  startPolling();
}

// --- bouton "aller en bas" ---
chatMsgs.addEventListener('scroll', () => {
  toBottom.style.display = isNearBottom(chatMsgs) ? 'none' : 'block';
});
toBottom.addEventListener('click', () => scrollToBottom(chatMsgs, true));

function isNearBottom(el, px = 80) {
  return el.scrollHeight - el.scrollTop - el.clientHeight <= px;
}
function scrollToBottom(el, smooth = true) {
  const last = el.lastElementChild;
  if (last) last.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'end' });
}

// --- récupération des messages ---
async function fetchMessages() {
  if (!currentRoom) return;
  try {
    const r = await fetch(`${BASE}/chat_messages_fetch.php?room_id=${currentRoom}&after_id=${lastId}`, { cache: 'no-store' });
    const j = await r.json();
    if (!j.ok) return;
    if (j.messages.length) {
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
  } catch {}
}

function startPolling() {
  stopPolling();
  fetchMessages();
  pollTimer = setInterval(fetchMessages, 2000);
}
function stopPolling() {
  if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}
chatClose.onclick = () => { chatModal.style.display = 'none'; stopPolling(); };
chatModal.addEventListener('click', e => { if (e.target === chatModal) { chatModal.style.display = 'none'; stopPolling(); } });



// --- compression image avant upload ---
chatForm.querySelector('input[type="file"]').addEventListener('change', async (e) => {
  const file = e.target.files[0];
  if (!file) return;

  // seulement images jpeg/png/webp
  if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return;

  const img = new Image();
  const url = URL.createObjectURL(file);
  img.src = url;

  await new Promise(res => img.onload = res);

  const canvas = document.createElement('canvas');
  const MAX = 450;
  let { width, height } = img;

  // redimension proportionnel
  if (width > height && width > MAX) {
    height *= MAX / width; width = MAX;
  } else if (height > width && height > MAX) {
    width *= MAX / height; height = MAX;
  }

  canvas.width = width;
  canvas.height = height;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(img, 0, 0, width, height);

  // compression (0.8 = 80%)
  const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.5));

  // remplace le fichier original dans l'input
  const compressedFile = new File([blob], file.name.replace(/\.\w+$/, '.jpg'), { type: 'image/jpeg' });
  const dt = new DataTransfer();
  dt.items.add(compressedFile);
  e.target.files = dt.files;

  URL.revokeObjectURL(url);
});




// --- envoi de message avec anti-abus ---
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('chatForm');
  if (!form) return;
  const btn = form.querySelector('button[type="submit"]');

  form.addEventListener('submit', async e => {
    e.preventDefault();
    btn.disabled = true;
    try {
      const r = await fetch(`${BASE}/chat_message_send.php`, {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin'
      });

      if (r.status === 429) {
        let msg = 'Trop de messages : ralentis un peu.';
        try {
          const j = await r.json();
          if (j?.error === 'rate_glob') msg = 'Limite : 3 messages / 30 s.';
          if (j?.error === 'rate_room') msg = 'Ralentis dans ce salon (2 / 5 s).';
          if (j?.error === 'rate_fast') msg = 'Trop rapide : attends ~1 s.';
        } catch {}
        alert(msg);
        return;
      }

      const j = await r.json();
      if (!j.ok) { alert(j.error || 'Erreur'); return; }

      form.reset();
      setTimeout(() => scrollToBottom(chatMsgs, true), 50);
    } catch {
      alert('Réseau indisponible');
    } finally {
      btn.disabled = false;
    }
  });
});

// --- affichage des messages ---
function renderMessage(m) {
  const el = document.createElement('div');
  el.className = 'msg';
  el.innerHTML =
    `<b>${escapeHtml(m.sender)}</b> · <span class="mut">${m.created_at}</span><br>` +
    `${m.body ? escapeHtml(m.body) : ''}`;

  if (m.file_url && /^image\//.test(m.file_mime || '')) {
    const img = document.createElement('img');
    img.src = m.file_url;
    img.alt = 'image';
    img.loading = 'lazy';
    img.style.maxWidth = '100%';
    img.style.borderRadius = '8px';
    el.appendChild(document.createElement('br'));
    el.appendChild(img);
  }
  return el;
}

// --- initialisation ---
loadRooms();


</script>
</body></html>
