
// Accordéon FAQ SEO (commun aux deux pages si tu recopies le bloc)
document.querySelectorAll('.seo-faq').forEach(function(faqRoot) {
  faqRoot.querySelectorAll('.seo-accordion__question').forEach(function(btn) {
    btn.addEventListener('click', function () {
      const item = btn.closest('.seo-accordion__item');
      const isOpen = item.classList.contains('is-open');

      // Fermer les autres dans la même FAQ
      faqRoot.querySelectorAll('.seo-accordion__item').forEach(function(other){
        if (other !== item) {
          other.classList.remove('is-open');
        }
      });

      // Toggle l’élément courant
      if (!isOpen) {
        item.classList.add('is-open');
      } else {
        item.classList.remove('is-open');
      }
    });
  });
});


// Vérif dispo du pseudo (comme avant)
const $pseudo  = document.getElementById('pseudo');
const $status  = document.getElementById('status');
let t = null;
$pseudo.addEventListener('input', () => {
  const v = $pseudo.value.trim();
  $status.textContent = '';
  if (!/^[\p{L}0-9_.-]{3,20}$/u.test(v)) {
    $status.textContent = 'Format invalide.'; $status.className = 'status ko'; return;
  }
  clearTimeout(t);
  t = setTimeout(async () => {
    try {
      const res  = await fetch('check_pseudo.php?pseudo=' + encodeURIComponent(v), {cache:'no-store'});
      const data = await res.json();
      if (!data.ok) { $status.textContent = 'Format invalide.'; $status.className='status ko'; return; }
      if (data.available) { $status.textContent = '✅ Pseudo disponible'; $status.className='status ok'; }
      else { $status.textContent = '❌ Déjà pris'; $status.className='status ko'; }
    } catch { $status.textContent = 'Erreur de vérification'; $status.className='status ko'; }
  }, 250);
});

document.querySelectorAll('.toggle-pass').forEach(btn=>{
  btn.addEventListener('click', ()=> {
    const targetId = btn.getAttribute('data-target');
    const input = document.getElementById(targetId);
    if (!input) return;
    if (input.type === 'password') {
      input.type = 'text';
      btn.textContent = 'Cacher';
      btn.setAttribute('aria-pressed','true');
      // Optionnel: éviter que le champ reste visible trop longtemps
       setTimeout(()=>{ input.type='password'; btn.textContent='Voir'; btn.setAttribute('aria-pressed','false'); }, 10000);
    } else {
      input.type = 'password';
      btn.textContent = 'Voir';
      btn.setAttribute('aria-pressed','false');
    }
  });
});




(function () {
  const REFRESH_INTERVAL = 10000; // 10 secondes

  async function refreshPublicRooms() {
    // On ne fait rien si la section n'existe pas (sécurité)
    const oldSection = document.querySelector('.public-rooms');
    if (!oldSection) return;

    try {
      const res = await fetch('public_rooms_snippet.php', {
        cache: 'no-store',
        credentials: 'same-origin'
      });
      if (!res.ok) {
        return;
      }

      const html = await res.text();

      // On parse le HTML reçu pour récupérer la nouvelle <section>
      const wrapper = document.createElement('div');
      wrapper.innerHTML = html.trim();

      const newSection = wrapper.querySelector('.public-rooms');
      if (!newSection) return;

      // Remplacement propre
      oldSection.replaceWith(newSection);
    } catch (e) {
      console.error('refreshPublicRooms error', e);
    }
  }

  // Premier appel (optionnel, au cas où tu veux forcer un refresh direct)
  // refreshPublicRooms();

  // Rafraîchissement toutes les 10 secondes
  setInterval(refreshPublicRooms, REFRESH_INTERVAL);
})();