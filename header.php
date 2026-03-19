<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Charge le solde de points de l'utilisateur connecté.
 * On évite une requête inutile si $userPoints est déjà défini
 * avant l'inclusion du header.
 */
if (!isset($userPoints)) {
    $userPoints = 0;

    if (!empty($_SESSION['user_id']) && isset($mysqli) && $mysqli instanceof mysqli) {
        $uid = (int)$_SESSION['user_id'];

        $stmtPts = $mysqli->prepare('SELECT balance_points FROM user_points_wallet WHERE user_id = ? LIMIT 1');
        if ($stmtPts) {
            $stmtPts->bind_param('i', $uid);
            $stmtPts->execute();
            $stmtPts->bind_result($balancePoints);

            if ($stmtPts->fetch()) {
                $userPoints = (int)$balancePoints;
            }

            $stmtPts->close();
        }
    }
}
?>

<link rel="stylesheet" href="<?= APP_BASE ?>/styles/header.css">

<header class="site-header">
  <div class="h-wrap">

    <a class="brand" href="">Tchat direct</a>

    <button class="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Ouvrir le menu">
      MENU
    </button>

    <nav id="primary-nav" class="nav">
      <div class="user-block">
        <a href="#" class="user-link" data-user-id="<?= (int)$_SESSION['user_id'] ?>">
          Salut, <?= htmlspecialchars($_SESSION['pseudo'], ENT_QUOTES) ?> 👋 - <?= number_format($userPoints, 0, ',', ' ')  ?> pts
        </a>

      </div>

      <a href="<?= APP_BASE ?>/feed.php" class="nav-link">Récents</a>
      <!--<a href="<?= APP_BASE ?>/quiz/quizzes.php" class="nav-link">Quiz</a> // en attente de la section quiz -->
      <a href="<?= APP_BASE ?>/feed.php?mine=1" class="nav-link">Mes post</a>
      <a href="<?= APP_BASE ?>/chat_rooms.php" class="nav-link--hot" rel="noopener">Rooms</a>
      <!-- <a href="<?= APP_BASE ?>/" class="nav-link" rel="noopener">Boutique</a> // en attente de la boutique -->

      <form action="<?= APP_BASE ?>/feed.php" method="get" class="nav-search">
        <input class="nav-input" name="q" placeholder="Rechercher…">
        <!--
        <select class="nav-select" name="scope">
          <option value="">Tout</option>
          <option value="title">Titre</option>
        </select>
        -->
      </form>

      <a class="nav-link" href="<?= APP_BASE ?>/write.php">Écrire un post</a>

      <a class="nav-link" href="<?= APP_BASE ?>/messages_inbox.php">
        Messages <span id="msgBadge" class="msg-badge" style="display:none">0</span>
      </a>

      <a class="nav-link" href="<?= APP_BASE ?>/chat_rooms.php" id="notifLink">
        Notifications <span id="notifBadge" class="msg-badge" style="display:none">0</span>
      </a>

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

<script>
/**
 * Charge le nombre de notifications non lues
 * et met à jour le badge.
 */
(() => {
  const notifBadge = document.getElementById('notifBadge');
  if (!notifBadge) return;

  const BASE = '<?= APP_BASE ?>';

  async function loadNotifications() {
    try {
      const r = await fetch(`${BASE}/chat_notifications_list.php`, {
        credentials: 'same-origin',
        cache: 'no-store',
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
        notifBadge.style.display = 'none';
        notifBadge.textContent = '0';
      }

    } catch (err) {
      console.error('Erreur chargement notifications :', err);
      notifBadge.style.display = 'none';
      notifBadge.textContent = '0';
    }
  }

  loadNotifications();
  setInterval(loadNotifications, 10000);
})();
</script>

<script>
/**
 * Gère le clic sur le lien de notifications :
 * - charge les notifications non lues
 * - marque la plus récente comme lue
 * - redirige vers le message ciblé
 */
(() => {
  const notifLink = document.getElementById('notifLink');
  if (!notifLink) return;

  const BASE = '<?= APP_BASE ?>';
  const CSRF = '<?= htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES) ?>';

  notifLink.addEventListener('click', async (e) => {
    e.preventDefault();

    try {
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

      const first = data.items[0];
      const notifId = Number(first.id || 0);
      const roomId = Number(first.room_id || 0);
      const messageId = Number(first.message_id || 0);

      if (!notifId || !roomId || !messageId) {
        window.location.href = `${BASE}/chat_rooms.php`;
        return;
      }

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

      window.location.href = `${BASE}/chat_rooms.php?room=${roomId}&msg=${messageId}`;

    } catch (err) {
      console.error('Erreur ouverture notif :', err);
      window.location.href = `${BASE}/chat_rooms.php`;
    }
  });
})();
</script>