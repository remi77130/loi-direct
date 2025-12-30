
  <a class="brand" href="<?= APP_BASE ?>/">Tchat direct</a>

  <button style="padding:5px"  class="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Ouvrir le menu">
    MENU
  </button>

  <nav id="primary-nav" class="nav">
    <!-- tes liens -->

    
<span style="margin-right:12px;color:#cbd5e1">

  <a href="#" class="user-link" data-user-id="<?= (int)$_SESSION['user_id'] ?>">
     Salut,  <?= htmlspecialchars($_SESSION['pseudo'], ENT_QUOTES) ?>👋
  </a> 
</span>
    <a href="<?= APP_BASE ?>/index.php" class="active">Récents</a>
    <a href="<?= APP_BASE ?>/quiz_bon_coup.php" class="active">Quiz</a>
    <a href="<?= APP_BASE ?>/index.php?mine=1">Mes post</a>
    <a style="color: pink;font-weight:700;" href="<?= APP_BASE ?>/chat_rooms.php" rel="noopener">Rooms</a>

    <!-- ton formulaire de recherche -->
    <form action="<?= APP_BASE ?>/index.php" method="get" class="nav-search">
      <input name="q" placeholder="Rechercher… (@pseudo pour un utilisateur)">
      <select name="scope">
        <option value="">Tout</option>
        <option value="title">Titre</option>
      </select>
     <!-- <button class="btn">Rechercher</button> gain de place si on retire le boutton de recherche -->
    </form>
 <!-- Zone utilisateur -->
  <div>
    <a class="btn" href="<?= APP_BASE ?>/write.php">Écrire un post</a>
  </div>
  <a class="btn" href="<?= APP_BASE ?>/messages_inbox.php" style="position:relative">
  Messages <span id="msgBadge" style="display:none;position:absolute;top:-6px;
  right:-6px;background:#ef4444;color:#fff;border-radius:999px;padding:0 6px;font-size:11px;
  line-height:18px;min-width:18px;text-align:center"></span>
</a>
    <a class="btn" style="font-weight: 700;
    background: #af004d;" href="<?= APP_BASE ?>/logout.php">Se déconnecter</a>