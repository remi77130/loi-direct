<?php
// auth_page.php: page de connexion et d’inscription, avec gestion des erreurs et redirections.
include __DIR__ . '/auth_page_action.php'; ?>



<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Tchat Direct – Tchat gratuit (salons publics et privés)</title>
  <meta name="description" content="Tchat Direct : accéder aux salons 
  de discussion publics et privés. Plateforme rapide, moderne et sécurisée.">

  <link rel="canonical" href="https://tchat-direct.com/">

<link rel="stylesheet" href="<?= app_base() ?>/styles/tokens.css?v=1">
<link rel="stylesheet" href="<?= app_base() ?>/styles/auth_page.css?v=1">
<link rel="stylesheet" href="<?= app_base() ?>/styles/blog.css?v=1">

</head>

<body class="neo">

<header class="b-header">


<p style="font-size: 0.80rem; line-height: 1.6; padding:12px">
 <strong>Nouvelle version V0.8.10</strong>, mise en place de notifications, salons publics et privés, URL dédiée par salon, 
 interface adaptée au mobile&nbsp;

</p>

<!--
<a href="<?= app_base() ?>/" class="logo-link">
  <img src="<?= app_base() ?>/uploads/tchat_direct_logo.webp"
       alt="Tchat Direct logo"
       class="logo-img"
       decoding="async">
</a> -->
<nav class="b-nav">
  <a class="b-link" href="<?= app_base() ?>/">Accueil</a>
  <a class="b-link" href="<?= app_base() ?>/auth_page.php">Se connecter</a>
  <a class="b-link b-link--cta" href="<?= app_base() ?>/auth_page.php">S’inscrire</a>
</nav>
</header>





<main id="main-content">


  <h1 >Tchat direct</h1>






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

  <div class="footer-inner">

    <div class="footer-col">
      <h3>Tchat Direct</h3>
      <p>
        Plateforme moderne de salons publics et privés.
        Discussion libre, anonyme ou privée.
      </p>
    </div>




    <div class="footer-col">
      <h4>Navigation</h4>
      <ul>
       <!-- <li><a href="/rooms.php">Salons publics</a></li> -->
        <li><a href="auth_page.php">Connexion / Inscription</a></li>
        <li><a href="blog/blog.php">Blog</a></li>
      </ul>
    </div>
<!--
    <div class="footer-col">
      <h4>Légal</h4>
      <ul>
        <li><a href="/mentions-legales.php">Mentions légales</a></li>
        <li><a href="/confidentialite.php">Confidentialité</a></li>
        <li><a href="/cgu.php">CGU</a></li>
      </ul>
    </div>

  </div>
      -->
  <div class="footer-bottom">
    © <?= date('Y') ?> Tchat Direct — BETA
  </div>
      
</footer>

















</main>

<script> // Logique d’affichage du formulaire de connexion/inscription
  const loginBox = document.getElementById('loginBox');
  const registerBox = document.getElementById('registerBox');
  const btnRegister = document.getElementById('btnShowRegister');
  const linkToRegister = document.getElementById('linkToRegister');
  const linkToLogin = document.getElementById('linkToLogin');
  const cardTitle = document.getElementById('cardTitle');

  const nextVal = <?= json_encode($next, JSON_UNESCAPED_SLASHES) ?>; // pour garder la valeur de next dans les URLs

  function show(which){ // which = 'login' ou 'register'
    if(which === 'register'){
      registerBox.hidden = false;
      loginBox.hidden = true;
      if(cardTitle) cardTitle.textContent = 'Créer un compte';
      history.replaceState(null, '', '?view=register&next=' + encodeURIComponent(nextVal));
    } else {
      loginBox.hidden = false;
      registerBox.hidden = true;
      if(cardTitle) cardTitle.textContent = 'Se connecter';
      history.replaceState(null, '', '?view=login&next=' + encodeURIComponent(nextVal));
    }
  }

  btnRegister?.addEventListener('click', () => show('register'));
  linkToRegister?.addEventListener('click', (e) => { e.preventDefault(); show('register'); });
  linkToLogin?.addEventListener('click', (e) => { e.preventDefault(); show('login'); });

  // Toggle password buttons (réutilise ta logique)
  document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-target');
      const input = document.getElementById(id);
      if(!input) return;
      const isPw = input.getAttribute('type') === 'password';
      input.setAttribute('type', isPw ? 'text' : 'password');
      btn.setAttribute('aria-pressed', isPw ? 'true' : 'false');
      btn.textContent = isPw ? 'Masquer' : 'Voir';
    });
  });
</script>

</body>
</html>
