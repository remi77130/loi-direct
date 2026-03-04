<?php
declare(strict_types=1);
require __DIR__ . '/../config.php'; // là où APP_BASE est défini
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blog — Tchat Direct</title>

  <link rel="canonical" href="https://tchat-direct.com/blog">
  <meta name="description" content="Articles et guides sur le tchat gratuit, anonyme et sans inscription. Découvrez des alternatives modernes aux tchats classiques.">

<link rel="stylesheet" href="<?= APP_BASE ?>/styles/tokens.css?v=1">
<link rel="stylesheet" href="<?= APP_BASE ?>/styles/blog.css?v=1">

</head>
<body>

<header class="b-header">
  <nav class="b-nav">
    <a class="b-link" href="<?= APP_BASE ?>/index.php">Accueil</a>
    <!--<a class="b-link b-link--active" href="<?= APP_BASE ?>/../blog.php">Blog</a>-->
    <a class="b-link" href="<?= APP_BASE ?>/auth_page.php">Se connecter</a>
    
    <a class="b-link b-link--cta" href="<?= APP_BASE ?>/auth_page.php">S’inscrire</a>
  </nav>
</header>

<main class="b-wrap">
  <section class="b-hero">
    <h1 class="b-title">Blog Tchat Direct</h1>
    <p class="b-lead">
      Découvrez nos derniers articles sur les tchats gratuits, anonymes et sans inscription.
      Fonctionnement, alternatives modernes aux tchats classiques et conseils pour discuter en ligne librement.
    </p>
  </section>

  <section class="b-grid" aria-label="Derniers articles">

    <article class="b-card">
      <h2 class="b-cardTitle">
  <a href="<?= BLOG_BASE ?>/tchat-gratuit-sans-inscription.php">
      </h2>
      <p class="b-cardText">Discuter en ligne fait partie des usages quotidiens.</p>
      <div class="b-cardMeta">
        <span class="b-tag">Guide</span>
        <span class="b-date">—</span>
      </div>


      <h2 class="b-cardTitle">
<a href="<?= BLOG_BASE ?>/gay-rencontre.php">
      </h2>
      <p class="b-cardText">Discutez et faite des rencontre entre mec.</p>
      <div class="b-cardMeta">
        <span class="b-tag">Guide</span>
        <span class="b-date">—</span>
      </div>



    </article>

  </section>
</main>

<footer class="b-footer">
  <a class="b-link" href="<?= APP_BASE ?>/index.php">Retour à l’accueil</a>
</footer>

</body>
</html>
