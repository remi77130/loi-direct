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
  #chatForm textarea{flex:1;resize:vertical;min-height:46px;max-height:160px;background:#0b1220;color:var(--txt);border:1px solid var(--line);border-radius:10px;padding:10px}
  .msg{border:1px solid var(--line);border-radius:10px;padding:8px 10px;margin:8px 0}
  .msg .meta{font-size:12px;color:var(--mut);margin-bottom:4px}
</style>
</head><body>
<div class="wrap">
  <div class="card">
    <h1 style="margin:0 0 6px">Salons de discussion</h1>
    <form id="newRoom" style="display:flex;gap:8px;align-items:center;margin-top:10px">
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
    <form id="chatForm">
      <textarea name="body" maxlength="2000" placeholder="Écrire un message…" required></textarea>
      <input type="hidden" name="room_id" id="room_id">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'],ENT_QUOTES) ?>">
      <button class="btn" type="submit">Envoyer</button>
      <span id="sendStatus" class="mut"></span>
    </form>
  </div>
</div>

<script>
const BASE='<?= APP_BASE ?>';
const RLIST = document.getElementById('rooms');
const NST   = document.getElementById('roomStatus');
const chatModal = document.getElementById('chatModal');
const chatClose = document.getElementById('chatClose');
const chatMsgs  = document.getElementById('chatMsgs');
const chatForm  = document.getElementById('chatForm');
const roomIdInp = document.getElementById('room_id');
const roomTitle = document.getElementById('roomTitle');
const sendStatus= document.getElementById('sendStatus');

let pollTimer=null, lastId=0, currentRoom=0;

async function loadRooms(){
  RLIST.innerHTML = '<div class="mut">Chargement…</div>';
  try{
    const r = await fetch(`${BASE}/chat_rooms_list.php`,{cache:'no-store'});
    const j = await r.json();
    if(!j.ok) throw 0;
    if(j.rooms.length===0){ RLIST.innerHTML='<div class="mut">Aucun salon.</div>'; return;}
    RLIST.innerHTML = j.rooms.map(x=>`
      <div class="row room" data-id="${x.id}" data-name="${x.name}">
        <div><strong>${escapeHtml(x.name)}</strong></div>
        <div class="mut">${x.last_at ? new Date(x.last_at.replace(' ','T')).toLocaleString() : '—'}</div>
      </div>`).join('');
  }catch(_){ RLIST.innerHTML='<div class="mut">Erreur.</div>'; }
}
function escapeHtml(s){return s.replace(/[&<>"']/g,(m)=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));}

document.getElementById('newRoom').addEventListener('submit', async (e)=>{
  e.preventDefault(); NST.textContent='';
  const fd = new FormData(e.target);
  try{
    const r = await fetch(`${BASE}/chat_room_create.php`,{method:'POST',body:fd});
    const j = await r.json();
    if(j.ok){ NST.style.color='#34d399'; NST.textContent='Créé'; e.target.reset(); loadRooms(); }
    else{ NST.style.color='#f87171'; NST.textContent=j.error||'Erreur'; }
  }catch(_){ NST.style.color='#f87171'; NST.textContent='Réseau'; }
});

RLIST.addEventListener('click',(e)=>{
  const row = e.target.closest('.room'); if(!row) return;
  openRoom(parseInt(row.dataset.id,10), row.dataset.name);
});

function openRoom(id, name){
  currentRoom=id; lastId=0; roomIdInp.value=id; roomTitle.textContent=name;
  chatMsgs.innerHTML=''; chatModal.style.display='flex';
  startPolling();
}
chatClose.onclick=()=>{ chatModal.style.display='none'; stopPolling(); };
chatModal.addEventListener('click', (e)=>{ if(e.target===chatModal){ chatModal.style.display='none'; stopPolling(); }});

function stopPolling(){ if(pollTimer){ clearInterval(pollTimer); pollTimer=null; } }
function startPolling(){
  stopPolling();
  fetchMessages(); // immédiat
  pollTimer=setInterval(fetchMessages, 2000);
}
async function fetchMessages(){
  if(!currentRoom) return;
  try{
    const r = await fetch(`${BASE}/chat_messages_fetch.php?room_id=${currentRoom}&after_id=${lastId}`,{cache:'no-store'});
    const j = await r.json(); if(!j.ok) return;
    if(j.messages.length){
      const frag = document.createDocumentFragment();
      j.messages.forEach(m=>{
        const d=document.createElement('div'); d.className='msg';
        d.innerHTML = `<div class="meta">${escapeHtml(m.sender)} — ${new Date(m.created_at.replace(' ','T')).toLocaleString()}</div>
                       <div style="white-space:pre-wrap">${escapeHtml(m.body)}</div>`;
        frag.appendChild(d);
        lastId = Math.max(lastId, m.id);
      });
      chatMsgs.appendChild(frag);
      chatMsgs.scrollTop = chatMsgs.scrollHeight;
    }
  }catch(_){}
}

chatForm.addEventListener('submit', async (e)=>{
  e.preventDefault(); sendStatus.textContent=''; const fd=new FormData(chatForm);
  const btn = chatForm.querySelector('button[type="submit"]'); btn.disabled=true;
  try{
    const r = await fetch(`${BASE}/chat_message_send.php`,{method:'POST',body:fd});
    const j = await r.json();
    if(j.ok){ chatForm.body.value=''; fetchMessages(); }
    else{ sendStatus.style.color='#f87171'; sendStatus.textContent=j.error||'Erreur'; }
  }catch(_){ sendStatus.style.color='#f87171'; sendStatus.textContent='Réseau'; }
  finally{ btn.disabled=false; }
});

loadRooms();
</script>
</body></html>
