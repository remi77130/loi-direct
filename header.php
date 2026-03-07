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