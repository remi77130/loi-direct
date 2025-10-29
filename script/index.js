const umSex   = document.getElementById('umSex');
const umHeight= document.getElementById('umHeight');
const umStatus= document.getElementById('umStatus');

const BASE     = '<?= APP_BASE ?>';
const modal    = document.getElementById('userModal');
const umPseudo = document.getElementById('umPseudo');
const umCount  = document.getElementById('umCount');
const umClose  = document.getElementById('umClose');
const umLink   = document.getElementById('umLink');

const umMsgToggle = document.getElementById('umMsgToggle');
const umMsgForm   = document.getElementById('umMsgForm');
const umRecipient = document.getElementById('umRecipient');
const umMsgStatus = document.getElementById('umMsgStatus');


const umImage      = document.getElementById('umImage');
const umPreview    = document.getElementById('umPreview');
const umPreviewWrap= document.getElementById('umPreviewWrap');
const umClearImg   = document.getElementById('umClearImg');


let umPreviewURL = null;

function hidePreview(){
  if (umPreviewURL) URL.revokeObjectURL(umPreviewURL);
  umPreviewURL = null;
  umPreview.src = '';
  umPreviewWrap.style.display = 'none';
}

umClearImg.addEventListener('click', () => {
  umImage.value = '';           // retire le fichier
  hidePreview();
});

umImage.addEventListener('change', () => {
  hidePreview();
  const f = umImage.files && umImage.files[0];
  if (!f) return




  // garde-fous (mêmes règles que serveur)
  const okType = ['image/jpeg','image/png','image/webp'].includes(f.type);
  if (!okType){ alert('Formats autorisés : JPG, PNG, WebP.'); umImage.value=''; return; }
  if (f.size > 5*1024*1024){ alert('Image trop lourde (max 5 Mo).'); umImage.value=''; return; }

  umPreviewURL = URL.createObjectURL(f);
  umPreview.src = umPreviewURL;
  umPreviewWrap.style.display = 'block';
});



// Reset preview à l’ouverture/fermeture de la modale
document.addEventListener('click', (e)=>{
  if (e.target.id === 'umClose') hidePreview();
});
modal.addEventListener('click', (e)=>{ if (e.target === modal) hidePreview(); });


umMsgToggle.addEventListener('click', ()=> {
  umMsgForm.style.display = umMsgForm.style.display==='none' ? 'block' : 'none';
});

// open modal on user click
const umInfos = document.getElementById('umInfos');

document.addEventListener('click', async (e) => {
  const a = e.target.closest('.user-link, .js-open-user');
  if (!a) return;
  e.preventDefault();
  const id = a.getAttribute('data-user-id');

  try {
    const r = await fetch(`${BASE}/user_card.php?id=${encodeURIComponent(id)}`, {cache:'no-store'});
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    const j = await r.json();
    if (!j.ok) throw new Error(j.error || 'Réponse invalide');

  umPseudo.textContent = j.pseudo;
umCount.textContent  = j.projects_count;
umStatus.textContent = j.relationship_status ?? '—';
umSex.textContent    = j.sex ?? '—';
umHeight.textContent = (j.height_cm ? (j.height_cm + ' cm') : '—');
umLink.href       = `${BASE}/profile.php?id=${encodeURIComponent(id)}`;
umRecipient.value = id; // pour l’envoi de message


    // Construit la ligne d’infos: “Homme • 175 cm”, sinon “—”
    const parts = [];
    if (j.sex) parts.push(j.sex);
    if (typeof j.height_cm === 'number') parts.push(`${j.height_cm} cm`);
    umInfos.textContent = parts.length ? parts.join(' • ') : '—';

    if (parseInt(id,10) === <?= (int)$_SESSION['user_id'] ?>) {
      umMsgToggle.style.display = 'none';
      umMsgForm.style.display   = 'none';
    } else {
      umMsgToggle.style.display = '';
    }

    modal.style.display  = 'flex';
  } catch (err) {
    
    console.error('user_card.php error:', err);
  }
});


// submit messages
umMsgForm.addEventListener('submit', async (e)=>{
  e.preventDefault();
  umMsgStatus.textContent = '';
  const fd = new FormData(umMsgForm);
  const btn = umMsgForm.querySelector('button[type="submit"]');
  btn.disabled = true;
  try{
    //const r = await fetch(`${BASE}/message_send.php`, { method:'POST', body:fd });
    const r = await fetch(`${BASE}/chat_message_send.php`, { method:'POST', body: fd });

    const j = await r.json();
    if(j.ok){
      umMsgStatus.style.color = '#34d399';
      umMsgStatus.textContent = 'Message envoyé ✅';
      umMsgForm.reset();
      refreshBadge();
    }else{
      umMsgStatus.style.color = '#f87171';
      umMsgStatus.textContent = 'Envoi impossible ('+(j.error||'erreur')+')';
    }
  }catch(_){
    umMsgStatus.style.color = '#f87171';
    umMsgStatus.textContent = 'Erreur réseau.';
  }finally{
    btn.disabled = false;
  }
});

// fermer la modale
umClose.addEventListener('click', ()=> modal.style.display='none');
modal.addEventListener('click', (e)=> { if (e.target === modal) modal.style.display='none'; });
document.addEventListener('keydown', (e)=> { if (e.key === 'Escape') modal.style.display='none'; });

const BADGE = document.getElementById('msgBadge');
async function refreshBadge(){
  try{
    const r = await fetch('<?= APP_BASE ?>/unread_count.php',{cache:'no-store'});
    if(!r.ok) throw 0;
    const j = await r.json();
    if(j.ok && typeof j.unread==='number'){
      if(j.unread>0){ BADGE.style.display='inline-block'; BADGE.textContent=j.unread>99?'99+':j.unread; }
      else{ BADGE.style.display='none'; BADGE.textContent=''; }
    }
  }catch(e){}
}
refreshBadge();
setInterval(refreshBadge, 20000);
