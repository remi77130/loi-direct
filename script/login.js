
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
      // setTimeout(()=>{ input.type='password'; btn.textContent='Voir'; btn.setAttribute('aria-pressed','false'); }, 30000);
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

