<?php
// auth_page.php: page de connexion et d’inscription, avec gestion des erreurs et redirections.
include __DIR__ . '/auth_page_action.php'; ?>



<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,follow">

  <title>Tchat Direct – Tchat gratuit (salons publics et privés)</title>
  <meta name="description" content="Tchat Direct : accéder aux salons 
  de discussion publics et privés. Plateforme rapide, moderne et sécurisée.">

  <link rel="canonical" href="https://tchat-direct.com/auth_page.php">

<link rel="stylesheet" href="<?= app_base() ?>/styles/tokens.css?v=1">
<link rel="stylesheet" href="<?= app_base() ?>/styles/auth_page.css?v=1">
<link rel="stylesheet" href="<?= app_base() ?>/styles/blog.css?v=1">


</head>

<body class="neo">

<header id="header" class="site-header">
 <!-- <a href="<?= app_base() ?>/auth_page.php" class="logo-link">
    <img src="<?= app_base() ?>/uploads/tchat_direct_logo.webp" alt="Tchat Direct logo" class="logo-img" decoding="async">
  </a>-->
</header>

<main id="main-content">

  <h1 >Tchat direct</h1>

  <div class="auth-top" >
    <div>
      <h2  id="cardTitle"><?= $active === 'register' ? 'Créer un compte' : 'Se connecter' ?></h2>
      <div class="auth-actions">
 
      </div>
    </div>

    <hr>

    <!-- LOGIN -->
    <section id="loginBox" <?= $active === 'login' ? '' : 'hidden' ?>>
      <?php if (!empty($errors['login'])): ?>
        <div class="errors">
          <?php foreach ($errors['login'] as $e) echo "<div>".htmlspecialchars($e, ENT_QUOTES)."</div>"; ?>
        </div>
      <?php endif; ?>

      <form method="post" autocomplete="off" novalidate>
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="view" value="login">
        <input type="hidden" name="next" value="<?= htmlspecialchars($next, ENT_QUOTES) ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">

        <label for="pseudo_login">Pseudo</label>
        <input type="text" id="pseudo_login" autocomplete="pseudo" name="pseudo"
               minlength="3" maxlength="20"
               pattern="^[\p{L}0-9_.-]{3,20}$"
               required
               placeholder="ex: Remi_85"
               value="<?= $active === 'login' ? htmlspecialchars($postedPseudo, ENT_QUOTES) : '' ?>">

        <label for="password_login">Mot de passe</label>
        <div class="pass-wrap">
          <input type="password" id="password_login" name="password"
                 minlength="8" maxlength="128"
                 required placeholder="••••••••" autocomplete="password_hash"> 
          <button type="button" class="toggle-pass" data-target="password_login" aria-pressed="false">Voir</button>
        </div>

        <button class="btn_connect" type="submit">Se connecter</button>

        <p>
          Pas de compte ? <a href="#" class="linkToRegister" id="linkToRegister">S’enregistrer</a>
        </p>
      </form>
    </section>

    <!-- REGISTER -->
    <section id="registerBox" <?= $active === 'register' ? '' : 'hidden' ?>>
      <?php if (!empty($errors['register'])): ?>
        <div class="errors">
          <?php foreach ($errors['register'] as $e) echo "<div>".htmlspecialchars($e, ENT_QUOTES)."</div>"; ?>
        </div>
      <?php endif; ?>

      <form method="post" autocomplete="off" novalidate>
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="view" value="register">
        <input type="hidden" name="next" value="<?= htmlspecialchars($next, ENT_QUOTES) ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">

        <label for="pseudo_register">Pseudo</label>
        <input type="text" id="pseudo_register" name="pseudo" autocomplete="pseudo"
               minlength="3" maxlength="20"
               pattern="^[\p{L}0-9_.-]{3,20}$"
               required
               placeholder="ex: Remi_85, 3–20 caractères, pas d'espace"
               value="<?= $active === 'register' ? htmlspecialchars($postedPseudo, ENT_QUOTES) : '' ?>">

        <div class="hint">3–20 caractères. Lettres, chiffres, . _ - (pas d’espace).</div>

        <label for="password_register">Mot de passe</label>
        <div class="pass-wrap">
          <input type="password" id="password_register" name="password"
                 minlength="8" maxlength="128"
                 required placeholder="••••••••" autocomplete="password_hash">
          <button type="button" class="toggle-pass" data-target="password_register" aria-pressed="false">Voir</button>
        </div>

        <label for="password_confirm_register">Confirmer le mot de passe</label>
        <div class="pass-wrap">
          <input type="password" id="password_confirm_register" name="password_confirm"
                 minlength="8" maxlength="128"
                 required placeholder="••••••••" autocomplete="new-password">
          <button type="button" class="toggle-pass" data-target="password_confirm_register" aria-pressed="false">Voir</button>
        </div>

        <button class="btn_connect" type="submit">Créer mon compte</button>

        <p>
          Déjà un compte ? <a href="#" class="linkToLogin" id="linkToLogin">Se connecter</a>
        </p>
      </form>
    </section>
  </div>







  


</main>

<script>
  const loginBox = document.getElementById('loginBox');
  const registerBox = document.getElementById('registerBox');
  const btnLogin = document.getElementById('btnShowLogin');
  const btnRegister = document.getElementById('btnShowRegister');
  const linkToRegister = document.getElementById('linkToRegister');
  const linkToLogin = document.getElementById('linkToLogin');
  const cardTitle = document.getElementById('cardTitle');

  const nextVal = <?= json_encode($next, JSON_UNESCAPED_SLASHES) ?>;

  function show(which){
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

  btnLogin?.addEventListener('click', () => show('login'));
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
