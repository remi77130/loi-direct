<?php
// register.php — inscription avec mot de passe (hash) + validations côté serveur
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/config.php'; // APP_BASE, helpers…


// IMPORTANT : NE PAS mettre require_login() ici,
// register.php doit rester accessible à tous (public).

// --- 1) Récupérer les 10 dernières rooms publiques (is_private = 0) ---


/* INFO FICHIER 

Lecture de $pseudo et validations serveur complètes.
Champ confirmation ajouté.
Hash du mot de passe toujours stocké (password_hash).
Gestion d’erreurs prepare/execute avec error_log + message générique.
Redirection sûre via APP_BASE.


*/







if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 1) CSRF
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $csrf)) {
    $errors[] = "Session expirée. Recharge la page.";
  } else {
    // 2) Récupération & normalisation
    $pseudo  = mb_substr(trim((string)($_POST['pseudo'] ?? '')), 0, 20);
    $pass1   = (string)($_POST['password'] ?? '');
    $pass2   = (string)($_POST['password_confirm'] ?? '');

   // pseudo déjà trim()
if (!preg_match('/^[\p{L}0-9_.-]{3,20}$/u', $pseudo)) {
    $errors[] = "Le pseudo doit faire 3 à 20 caractères (lettres avec accents, chiffres, . _ -, Pas despace).";
}


    $len = mb_strlen($pass1);
    if ($len < 8 || $len > 128) {
      $errors[] = "Mot de passe : 8 à 128 caractères.";
    }
    if ($pass1 !== $pass2) {
      $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // 4) Pseudo disponible ?
    if (!$errors) {
      $chk = $mysqli->prepare('SELECT 1 FROM users WHERE pseudo=? LIMIT 1');
      if (!$chk) {
        $errors[] = "Erreur serveur.";
        error_log('register check prepare: '.$mysqli->error);
      } else {
        $chk->bind_param('s', $pseudo);
        if (!$chk->execute()) {
          $errors[] = "Erreur serveur.";
          error_log('register check execute: '.$chk->error);
        } else {
          $chk->store_result();
          if ($chk->num_rows > 0) {
            $errors[] = "Ce pseudo est déjà pris.";
          }
        }
        $chk->close();
      }
    }

    // 5) Création du compte
    if (!$errors) {
      $hash = password_hash($pass1, PASSWORD_DEFAULT);

      $ins = $mysqli->prepare('INSERT INTO users (pseudo, password_hash) VALUES (?, ?)');
      if (!$ins) {
        $errors[] = "Erreur serveur.";
        error_log('register insert prepare: '.$mysqli->error);
      } else {
        $ins->bind_param('ss', $pseudo, $hash);
        if ($ins->execute()) {
          // Succès: login direct
          session_regenerate_id(true);
          $_SESSION['user_id'] = $ins->insert_id;
          $_SESSION['pseudo']  = $pseudo;
          $_SESSION['flash_success'] = random_punchline($pseudo);

          header('Location: '.rtrim(APP_BASE,'/').'/chat_rooms.php', true, 303);
          exit;
        } else {
          $errors[] = "Erreur serveur.";
          error_log('register insert execute: '.$ins->error);
        }
        $ins->close();
      }
    }
  }
} 
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">



<meta property="og:title" content="Tchat Direct – Chat Anonyme Gratuit, Salons Publics & Privés en Direct">
<meta property="og:description" content="Rejoins tchat direct pour discuter en ligne. tchatche gratuit.">
<meta property="og:url" content="https://tchat-direct.com/register.php">
<meta property="og:type" content="website">


  <title>Tchat Direct – Chat Anonyme Gratuit, Salons Publics & Privés en Direct</title>

  <!-- Favicon principal -->
<link rel="icon" href="favicon.ico" type="image/x-icon">
  <!-- Favicon PNG pour les navigateurs modernes -->
  <link rel="icon" type="image/png" sizes="32x32" href="/uploads/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/uploads/favicon-16x16.png">

  <!-- Icône pour iOS / mobile -->
  <link rel="apple-touch-icon" sizes="180x180" href="/uploads/apple-touch-icon.png">

  <!-- PWA / manifest -->
  <link rel="manifest" href="site.webmanifest">

  <!-- SEO -->
  <link rel="canonical" href="https://tchat-direct.com/register.php">

  <!-- Stylesheets -->
   <link rel="stylesheet" href="<?= APP_BASE ?>/styles/tokens.css?v=1">
<link rel="stylesheet" href="<?= APP_BASE ?>/styles/register.css?v=1">

<link rel="stylesheet" href="<?= APP_BASE ?>/styles/seo.css?v=1">


  <meta name="description" content="Inscrivez-vous sur Tchat Direct pour rejoindre des salons de discussion anonymes et discuter en ligne gratuitement.">
  <meta name="keywords" content="tchat direct, tchat en ligne, chat anonyme, coco chat, direct tchat">

  <meta name="robots" content="index,follow">

  <!-- jQuery UNIQUEMENT si tu en as besoin ici --->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>

  <!-- Google Analytics 4 -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-FHFM0ESDMP"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-FHFM0ESDMP');
  </script>








</head>

<body class="neo">




<header  id="header"  class="site-header">
  <a href="register.php" class="logo-link">


  
<img src="<?= APP_BASE ?>/uploads/tchat_direct_logo.webp" alt="Tchat Direct logo"
  class="logo-img"
  width="210"
  height="210"
  decoding="async">
  </a> <p class="logo-note">Le logo </p>

</header>



<main id="main-content">




<!-- Bannière version Bêta -->
<div class="banner" id="betaBanner">
  🚧 Ce site est actuellement en version <strong>Bêta</strong>. Certaines fonctionnalités peuvent être instables. Merci de na pas partager.
</div>

 <h1>Tchat direct - Inscription</h1>





<div class="container_text_login_01">
  <h2>Chat sans inscription</h2>
<p class="text_login_01">  Interface simple, accès rapide, salons publics et privés, discussions anonymes.
      Tchat Direct vise l’efficacité sans inscription lourde. Tu te connectes, tu choisis un salon, tu échanges.
    </p>
</div>



  <div id="register-form" class="card">
   
    <h2>Créer un compte</h2>

    <?php if ($errors): ?>
      <div class="errors">
        <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e, ENT_QUOTES)."</div>"; ?>
      </div>
    <?php endif; ?>

    <form  method="post" autocomplete="off" novalidate>
      <label for="pseudo">Pseudo
</label>
      <input type="text" id="pseudo" name="pseudo" minlength="3" maxlength="20"
      pattern="^[\p{L}0-9_.-]{3,20}$" required placeholder="ex: Remi_85, 3–20 caractères, Pas d'espace"
>
       
      <div class="hint">3–20 caractères. Lettres, chiffres et underscore uniquement.</div>
      <div id="status" class="status"></div>
<label for="password">Mot de passe</label>
<div class="pass-wrap">
  <input type="password" id="password" name="password" minlength="8" maxlength="128"
         required placeholder="••••••••" autocomplete="new-password">
  <button type="button" class="toggle-pass" data-target="password" aria-pressed="false" title="Voir le mot de passe">Voir</button>
</div>

<label for="password_confirm">Confirmer le mot de passe</label>
<div class="pass-wrap">
  <input type="password" id="password_confirm" name="password_confirm" minlength="8" maxlength="128"
         required placeholder="••••••••" autocomplete="new-password">
  <button type="button" class="toggle-pass" data-target="password_confirm" aria-pressed="false" 
  title="Voir le mot de passe">Voir</button>
</div>

      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">
      <button id="submitBtn" class="btn" type="submit">S’inscrire</button>
    </form>

    <div class="footer_link_login">Déjà inscrit ? 
      <a href="login.php"><span>Connexion</span></a>
</div>
  </div>






<?php include __DIR__ . '/public_rooms_snippet.php'; ?>

<!-- SEO – Bloc inscription Tchat Direct -->
<section class="seo-section" id="seo-register-intro">
  <h2>S’inscrire sur un site de tchat gratuit et anonyme</h2>
  <p>
    Tchat Direct est un <strong>site de tchat gratuit</strong> penser pour les échanges en temps réel, sans inscription compliquée ni démarches interminables. 
    L’inscription sert uniquement à crée ton pseudo et sécuriser l’accès à tes salons, tout en restant anonyme : aucun nom réel, aucune pièce d’identité, aucune donnée sensible n’est demandée.
  </p>
  <p>
    Une fois inscrit, tu accèdes à un <strong>tchat en ligne gratuit</strong> avec des salons publics, des rooms privées protégées par mot de passe et des discussions instantanées depuis mobile, tablette ou ordinateur.
    L’objectif est simple&nbsp;: proposer une alternative moderne aux tchats anonymes classiques, dans un environnement clair, stable et facile à prendre en main.
  </p>
  <div class="seo-box seo-box--highlight">
    <strong>En résumé :</strong> tu crées un pseudo, tu définis un mot de passe, et tu peux ensuite rejoindre ou créer des salons de discussion en ligne, publics ou privés, en quelques secondes.
  </div>
</section>

<section class="seo-section" id="seo-register-avantages">
  <h2>Pourquoi créer un compte sur Tchat Direct&nbsp;?</h2>
  <div class="seo-grid">
    <div class="seo-grid__item">
      <div class="seo-grid__item-title">Tchat gratuit et anonyme</div>
      <p>
        L’accès au site est <strong>100&nbsp;% gratuit</strong> et anonyme. Le compte sert uniquement à te connecter et à retrouver tes salons.
        Tu peux discuter sans exposer ta vie privée, sous le pseudo de ton choix, dans des salons publics ou privés.
      </p>
    </div>
    <div class="seo-grid__item">
      <div class="seo-grid__item-title">Salons publics et rooms privées</div>
      <p>
        Tu peux rejoindre des <strong>salons publics</strong> déjà actifs ou créer ta <strong>room privée sécurisée par mot de passe</strong>.
        C’est idéal pour discuter en petit groupe, organiser des échanges ciblés ou filtrer qui peut entrer dans ton salon.
      </p>
    </div>
    <div class="seo-grid__item">
      <div class="seo-grid__item-title">Rencontres et échanges sans inscription lourde</div>
      <p>
        De nombreuses personnes cherchent un <strong>tchat direct sans inscription lourde</strong>, pour échanger, sympathiser ou flirter en ligne.
        Tchat Direct simplifie l’entrée&nbsp;: un pseudo, un mot de passe, et tu peux lancer la discussion.
      </p>
    </div>
  </div>
  <p>
    Que tu cherches un <strong>tchat mobile gratuit</strong>, un espace pour discuter en soirée, ou une manière de faire des rencontres anonymes en ligne, 
    le fait de créer un compte te donne un accès stable à un environnement pensé pour la discussion instantanée.
  </p>
</section>

<section class="seo-section" id="seo-register-salons">
  <h2>Salons publics, rooms privées et URL dédiée à chaque tchat</h2>
  <p>
    Sur Tchat Direct, chaque <strong>salon public</strong> dispose de sa propre URL, ce qui permet de partager facilement un espace précis avec tes contacts.
    Pour les discussions plus ciblées, tu peux créer une <strong>room privée gratuite</strong>, protégée par mot de passe.
  </p>
  <div class="seo-box">
    <strong>Créer un salon public ou privé, étape par étape :</strong>
    <ol>
      <li>Tu t’inscris en choisissant un pseudo et un mot de passe.</li>
      <li>Tu te connectes à ton compte depuis la page de connexion.</li>
      <li>Tu crées un salon public ou privé selon ton besoin.</li>
      <li>Tu définis un mot de passe si tu veux un <strong>salon privé sécurisé</strong>.</li>
      <li>Tu partages l’URL du salon avec les personnes que tu souhaites inviter.</li>
    </ol>
  </div>
  <p>
    Cette logique de <strong>tchat en ligne gratuit avec URL dédiée</strong> permet d’organiser des discussions thématiques, des groupes récurrents ou des espaces réservés à un petit cercle,
    tout en gardant un fonctionnement simple pour les utilisateurs.
  </p>
</section>

<section class="seo-section" id="seo-register-adulte">
  <h2>Tchat adulte soft et rencontres anonymes sans inscription lourde</h2>
  <p>
    Sur Internet, beaucoup d’utilisateurs saisissent des requêtes comme <em>tchat gratuit sans inscription</em>, <em>rencontre tchat gratuit</em> ou encore 
    des expressions liées à un <em>tchat adulte soft</em>. L’idée est souvent la même&nbsp;: trouver un espace de discussion anonyme, rapide d’accès, 
    sans devoir remplir de formulaires interminables.
  </p>
  <p>
    Tchat Direct se positionne comme une <strong>alternative neutre et moderne</strong> à ces usages&nbsp;: 
    tu peux discuter, sympathiser, flirter de façon soft et respectueuse, en gardant le contrôle sur ton anonymat.
    Les rooms privées avec mot de passe permettent de filtrer qui entre, et les salons publics servent de point de départ pour rencontrer de nouvelles personnes.
  </p>
  <p>
    Certaines recherches populaires montrent l’intérêt des internautes pour les échanges plus intimes en ligne (du type «&nbsp;chat sexuel sans inscription&nbsp;» ou «&nbsp;tchat sexe sans inscription&nbsp;»).
    Tchat Direct reste centré sur la <strong>discussion</strong> et la <strong>modération</strong>, en offrant un cadre sobre, anonyme et clairement séparé des contenus explicites.
  </p>
</section>

<section class="seo-section" id="seo-register-comparatif">
  <h2>Tchat Direct, une alternative moderne aux tchats anonymes classiques</h2>
  <p>
    Pendant longtemps, les internautes se sont tournés vers des plateformes de tchat anonymes très connues, 
    comme certains services historiques 
    de discussion en ligne (<span>coco gg
    </span>...)
    Tchat Direct propose une approche plus récente&nbsp;: interface adaptée au mobile, rooms publiques et privées, URL dédiée par salon, et inscription rapide.
  </p>
  <div class="seo-table-wrapper">
    <table class="seo-table" aria-label="Comparatif entre différents types de tchats en ligne">
      <thead>
        <tr>
          <th>Plateforme</th>
          <th>Accès</th>
          <th>Anonymat</th>
          <th>Salons privés</th>
          <th>URL dédiée par salon</th>
          <th>Ecrire des posts</th>
          <th>Commenter/liker les posts</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Tchat Direct</td>
          <td>Gratuit, compte simple</td>
          <td>Pseudo uniquement</td>
          <td>Oui, avec mot de passe</td>
          <td>Oui</td>
          <td>Oui</td>
          <td>Oui</td>

        </tr>
        <tr>
          <td>Plateformes type Coco / Coconut</td>
          <td>Gratuit, accès variable</td>
          <td>Souvent anonyme</td>
          <td>Présents ou non selon le service</td>
          <td>Généralement non dédié par salon</td>
           <td>Non</td>
          <td>Non</td>
        </tr>
        <tr>
          <td>Tchats généralistes anciens</td>
          <td>Accès libre</td>
          <td>Bourré de pub</td>
          <td>Variable</td>
          <td>Parfois limité ou inexistant</td>
           <td>Non</td>
          <td>Non</td>
        </tr>
      </tbody>
    </table>
  </div>
  <p>
    Sans chercher à remplacer qui que ce soit, Tchat Direct se présente simplement comme un <strong>tchat en ligne gratuit</strong> 
    qui reprend les points forts des tchats anonymes historiques, tout en ajoutant une expérience plus structurée pour les rooms et la gestion des salons.
  </p>
</section>

<section class="seo-section" id="seo-register-faq">
  <h2>FAQ – Inscription et tchat gratuit</h2>
  <div class="seo-faq">
    <div class="seo-accordion__item">
      <button class="seo-accordion__question" type="button">
        <span>Pourquoi dois-je m’inscrire si le tchat est gratuit&nbsp;?</span>
        <span class="seo-accordion__icon">›</span>
      </button>
      <div class="seo-accordion__answer">
        <div class="seo-accordion__answer-inner">
          L’inscription sur un <span>tchat anonyme gratuit</span> sert uniquement à créer ton pseudo et ton mot de passe. 
          Elle permet de sécuriser l’accès à ton compte et à tes salons, sans demander d’informations personnelles.
          Le tchat reste gratuit, et tu peux rester anonyme.
        </div>
      </div>
    </div>

    <div class="seo-accordion__item">
      <button class="seo-accordion__question" type="button">
        <span>Est-ce que Tchat Direct est vraiment un tchat gratuit sans inscription lourde&nbsp;?</span>
        <span class="seo-accordion__icon">›</span>
      </button>
      <div class="seo-accordion__answer">
        <div class="seo-accordion__answer-inner">
          Oui. L’inscription est réduite au strict minimum&nbsp;: un pseudo et un mot de passe. 
          Aucun long formulaire, aucun numéro de téléphone, aucune carte bancaire n’est demandée pour accéder aux salons de discussion.
        </div>
      </div>
    </div>

    <div class="seo-accordion__item">
      <button class="seo-accordion__question" type="button">
        <span>Mon tchat est-il anonyme une fois inscrit&nbsp;?</span>
        <span class="seo-accordion__icon">›</span>
      </button>
      <div class="seo-accordion__answer">
        <div class="seo-accordion__answer-inner">
          Oui. Tu échanges sous un pseudo. Tu peux choisir un identifiant qui ne permet pas de te reconnaître dans la vie réelle.
          L’objectif est de proposer un <strong>tchat anonyme gratuit</strong>, tout en gardant des règles de respect et de modération.
        </div>
      </div>
    </div>

    <div class="seo-accordion__item">
      <button class="seo-accordion__question" type="button">
        <span>Puis-je créer un salon privé avec mot de passe&nbsp;?</span>
        <span class="seo-accordion__icon">›</span>
      </button>
      <div class="seo-accordion__answer">
        <div class="seo-accordion__answer-inner">
          Oui. Après inscription et connexion, tu peux créer des rooms privées protégées par mot de passe.
          Seules les personnes à qui tu communiques ce mot de passe pourront rejoindre le salon.
        </div>
      </div>
    </div>

    <div class="seo-accordion__item">
      <button class="seo-accordion__question" type="button">
        <span>Comment fonctionne la rencontre tchat gratuit sur Tchat Direct&nbsp;?</span>
        <span class="seo-accordion__icon">›</span>
      </button>
      <div class="seo-accordion__answer">
        <div class="seo-accordion__answer-inner">
          Tu peux rejoindre des salons publics thématiques ou créer ton propre espace de discussion.
          La rencontre se fait par le dialogue, sans algorithme de matching, dans un cadre anonyme et sobre, 
          centré sur l’échange par messages.
        </div>
      </div>
    </div>



    <div class="seo-accordion__item">
      <button class="seo-accordion__question" type="button">
        <span>        Un tchat adulte sans inscription, est-ce que c’est vraiment rare ?&nbsp;?</span>
        <span class="seo-accordion__icon">›</span>
      </button>
      <div class="seo-accordion__answer">
        <div class="seo-accordion__answer-inner">
      Non, il existe plusieurs plateformes de <span>tchatche en ligne</span>.
      Cependant, les <span>tchats adultes</span> sans inscription restent peu nombreux, car beaucoup de sites 
      imposent une <span>création de compte</span> ou des étapes compliquées. Un service de Tchat Coquin fait partie 
      des exceptions : accès direct, anonyme, rapide, idéal pour des échanges légers et des rencontres occasionnelles. 
      La majorité des autres chats en ligne ne proposent pas une expérience aussi simple 
      et immédiate (comfirmation de mail avant de pouvoir écrire ou demande un abonnement )parfois demande même les deux (abonnement et email...)

        </div>
      </div>
    </div>




    <div class="seo-accordion__item">
      <button class="seo-accordion__question" type="button">
        <span>Y a-t-il une application mobile ou un tchat mobile gratuit&nbsp;?</span>
        <span class="seo-accordion__icon">›</span>
      </button>
      <div class="seo-accordion__answer">
        <div class="seo-accordion__answer-inner">
          Le site est conçu pour fonctionner sur mobile, directement dans ton navigateur. 
          Tu peux donc utiliser Tchat Direct comme un <strong>tchat mobile gratuit</strong> sans installer d’application.
        </div>
      </div>
    </div>

    <div class="seo-accordion__item">
      <button class="seo-accordion__question" type="button">
        <span>Quelle est la différence avec les anciens tchats anonymes connus&nbsp;?</span>
        <span class="seo-accordion__icon">›</span>
      </button>
      <div class="seo-accordion__answer">
        <div class="seo-accordion__answer-inner">
          Tchat Direct reprend l’idée du tchat en ligne anonyme, mais avec une interface plus récente&nbsp;: 
          meilleure lisibilité, gestion des rooms publiques et privées, URL dédiée par salon, et inscription simplifiée.
        </div>
      </div>
    </div>


    <div class="seo-accordion__item">
      <button class="seo-accordion__question" type="button">
        <span>Comment supprimer mon compte si je ne souhaite plus utiliser le tchat&nbsp;?</span>
        <span class="seo-accordion__icon">›</span>
      </button>
      <div class="seo-accordion__answer">
        <div class="seo-accordion__answer-inner">
          Tu peux contacter l’administrateur du site ou utiliser les options prévues (lorsqu’elles sont disponibles) 
          pour demander la suppression de ton compte. L’objectif est de te laisser le choix d’utiliser le service ou non, sans contrainte.
        </div>
      </div>
    </div>


      <div class="seo-accordion__item">
      <button class="seo-accordion__question" type="button">
        <span>Est-ce que Tchat Direct convient aussi à une utilisation plus sérieuse (amitié, échanges de groupe)&nbsp;?</span>
        <span class="seo-accordion__icon">›</span>
      </button>
      <div class="seo-accordion__answer">
        <div class="seo-accordion__answer-inner">
          Oui. Les rooms privées et les salons publics thématiques permettent aussi de créer des espaces orientés amitié, 
          discussions de groupe, entraide ou simple échange quotidien, sans forcément chercher la rencontre.
        </div>
      </div>
    </div>



  </div>
</section>

<footer class="site-footer brutal" role="contentinfo">
  <div class="footer-strip footer-links-strip">
    <nav class="footer-links" aria-label="Liens du site">
      <a class="footer-link" href="<?= APP_BASE ?>">Mentions légales</a>
      <a class="footer-link" href="<?= APP_BASE ?>">Confidentialité</a>
      <a class="footer-link" href="<?= APP_BASE ?>">CGU</a>
      <a class="footer-link" href="<?= APP_BASE ?>">Contact</a>
      <a class="footer-link" href="<?= APP_BASE ?>/blog/blog.php">Blog</a>
    </nav>
  </div>

  <div class="footer-strip footer-social-strip">
    <div class="footer-social">
      <a class="social-btn" href="https://instagram.com/TON_COMPTE" target="_blank" aria-label="Instagram">
        <!-- Instagram SVG identique -->
        <svg class="social-ico" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Z"/>
          <path d="M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Z"/>
          <path d="M17.5 6.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/>
        </svg>
      </a>

      <a class="social-btn" href="https://t.me/TON_GROUPE" target="_blank" aria-label="Telegram">
        <!-- Telegram SVG identique -->
        <svg class="social-ico" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M21.9 4.6c.2-.8-.5-1.4-1.2-1.1L2.8 10.6c-.8.3-.7 1.5.1 1.8l4.6 1.6 1.8 5.7c.2.7 1 .8 1.5.4l2.6-2.3 5.1 3.7c.6.4 1.4.1 1.6-.6l3.8-16.3Z"/>
        </svg>
      </a>
    </div>
  </div>

  <div class="footer-meta brutal-meta">
    © <?= date('Y') ?> Tchat Direct — BETA
  </div>
</footer>











    </main>



<script>
  window.APP = {
    baseUrl: "<?= APP_BASE ?>",
    hasError: <?= empty($errors) ? 'false' : 'true' ?>
  };
</script>

<script src="<?= APP_BASE ?>/script/register.js?v=1" defer></script>




</body>
</html>
