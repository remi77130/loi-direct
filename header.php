  <link rel="stylesheet" href="<?= APP_BASE ?>/styles/header.css">

<header class="site-header">
  <div class="h-wrap">

    <a class="brand" href="">Tchat direct</a>
    
    <button class="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Ouvrir le menu">
      MENU
    </button>

    <nav id="primary-nav" class="nav">
      <a href="#" class="user-link" data-user-id="<?= (int)$_SESSION['user_id'] ?>">
        Salut, <?= htmlspecialchars($_SESSION['pseudo'], ENT_QUOTES) ?> 👋
      </a>

      <a href="<?= APP_BASE ?>/feed.php" class="nav-link">Récents</a>
      <!--<a href="<?= APP_BASE ?>/quiz/quizzes.php" class="nav-link">Quiz</a> // en attente de la section quiz -->
      <a href="<?= APP_BASE ?>/feed.php?mine=1" class="nav-link">Mes post</a>
      <a href="<?= APP_BASE ?>/chat_rooms.php" class="nav-link--hot" rel="noopener">Rooms</a>
     <!-- <a href="<?= APP_BASE ?>/" class="nav-link" rel="noopener">Boutique</a> // en attente de la boutique  -->

      <form action="<?= APP_BASE ?>/feed.php" method="get" class="nav-search">
        <input class="nav-input" name="q" placeholder="Rechercher…">
      <!--  <select class="nav-select" name="scope">
          <option value="">Tout</option>
          <option value="title">Titre</option>
        </select>-->
      </form>

      <a class="nav-link" href="<?= APP_BASE ?>/write.php">Écrire un post</a>

      <a class="nav-link" href="<?= APP_BASE ?>/messages_inbox.php">
        Messages <span id="msgBadge" class="msg-badge" style="display:none" >0</span>
      </a>


<a class="nav-link" href="<?= APP_BASE ?>/chat_rooms.php" id="notifLink">
  Notifications <span id="notifBadge" class="msg-badge" style="display:none">0</span>
</a> <!--le lien vers les notifications, avec un badge pour afficher le nombre de notifications non lues (initialement caché avec display:none) -->


      <a class="nav-link-disconect" href="<?= APP_BASE ?>/logout.php">Se déconnecter</a>
    </nav>
  </div>
</header>

<script>
  (() => {
  const btn = document.querySelector('.nav-toggle');
  const nav = document.getElementById('primary-nav');
  if (!btn || !nav) return;

  btn.addEventListener('click', () => {
    const open = nav.classList.toggle('is-open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
})();

</script>


<script> // Script pour charger le nombre de notifications non lues et mettre à jour le badge en temps réel
(() => {
  const notifBadge = document.getElementById('notifBadge');
  if (!notifBadge) return;

  const BASE = '<?= APP_BASE ?>'; // on définit une constante BASE pour stocker la base de l’URL de l’application, ce qui nous permet de construire facilement les URLs des requêtes fetch vers les endpoints PHP (par exemple `${BASE}/chat_notifications_list.php`) et de faciliter la maintenance du code (si jamais la base de l’URL change, on n’aura qu’à mettre à jour cette constante à un seul endroit au lieu de devoir modifier toutes les URLs dans le code)

  async function loadNotifications() {
    try {
      const r = await fetch(`${BASE}/chat_notifications_list.php`, {
        credentials: 'same-origin',
        cache: 'no-store', // on évite de stocker la réponse en cache pour être sûr d’avoir une info à jour à chaque requête
        headers: {
          'Accept': 'application/json'
        }
      });

      if (!r.ok) {
        throw new Error(`HTTP ${r.status} sur chat_notifications_list.php`);
      }

      const data = await r.json();

      if (!data || !data.ok) {
        console.warn('Notifications invalides :', data);
        notifBadge.style.display = 'none';
        notifBadge.textContent = '0';
        return;
      }

      const count = Number(data.count_unread || 0);

      if (count > 0) {
        notifBadge.textContent = String(count);
        notifBadge.style.display = 'inline-block';
      } else {
        notifBadge.style.display = 'none'; // on cache le badge s’il n’y a aucune notification non lue
        notifBadge.textContent = '0'; // on remet le texte à 0 pour éviter d’avoir un nombre obsolète si jamais le badge est réaffiché plus tard (par exemple après une nouvelle notification)
      }

    } catch (err) { // en cas d’erreur (problème réseau, réponse non JSON, etc.) on affiche une erreur dans la console pour les développeurs, et on cache le badge de notification pour éviter d’afficher une info potentiellement obsolète ou incorrecte
      console.error('Erreur chargement notifications :', err);
      notifBadge.style.display = 'none';
      notifBadge.textContent = '0';
    }
  }

  // chargement immédiat
  loadNotifications(); // on charge le nombre de notifications non lues dès que la page est chargée pour afficher une info à jour dans le badge (par exemple si l’utilisateur a des notifications non lues au moment où il arrive sur la page, ou si une nouvelle notification arrive pendant qu’il navigue)

  // refresh toutes les 10 secondes
  setInterval(loadNotifications, 10000);
})();
</script>






<script> // Script pour gérer le clic sur le lien de notifications, charger les notifications non lues, marquer la plus récente comme lue, et rediriger vers le message ciblé
(() => {
  const notifLink = document.getElementById('notifLink');
  if (!notifLink) return;

  const BASE = '<?= APP_BASE ?>';
  const CSRF = '<?= htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES) ?>';

  notifLink.addEventListener('click', async (e) => {
    e.preventDefault();

    try {
      // 1) on charge les notifications non lues
      const r = await fetch(`${BASE}/chat_notifications_list.php`, {
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {
          'Accept': 'application/json'
        }
      });

      if (!r.ok) {
        window.location.href = `${BASE}/chat_rooms.php`;
        return;
      }

      const data = await r.json();

      if (!data || !data.ok || !Array.isArray(data.items) || data.items.length === 0) {
        window.location.href = `${BASE}/chat_rooms.php`;
        return;
      }

      // 2) on prend la plus récente non lue
      const first = data.items[0];
      const notifId = Number(first.id || 0);
      const roomId = Number(first.room_id || 0);
      const messageId = Number(first.message_id || 0);

      if (!notifId || !roomId || !messageId) {
        window.location.href = `${BASE}/chat_rooms.php`;
        return;
      }

      // 3) on la marque comme lue AVANT redirection
      const body = new URLSearchParams();
      body.set('id', String(notifId));
      body.set('csrf', CSRF);

      const rRead = await fetch(`${BASE}/chat_notifications_read.php`, {
        method: 'POST',
        body,
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {
          'Accept': 'application/json'
        }
      });

      if (!rRead.ok) {
        console.warn('Impossible de marquer la notif comme lue');
      } else {
        const jRead = await rRead.json();
        if (!jRead || !jRead.ok) {
          console.warn('Réponse lecture notif invalide :', jRead);
        }
      }

      // 4) redirection vers le message ciblé
      window.location.href = `${BASE}/chat_rooms.php?room=${roomId}&msg=${messageId}`;

    } catch (err) {
      console.error('Erreur ouverture notif :', err);
      window.location.href = `${BASE}/chat_rooms.php`;
    }
  });
})();
</script>