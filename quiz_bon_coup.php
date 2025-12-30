<?php
declare(strict_types=1);

/**
 * quiz_bon_coup.php
 * - mysqli + session + CSRF
 * - Cooldown 6h avant de pouvoir re-soumettre
 * - Stockage privacy: user_id, quiz_key, score_total, result_letter
 *
 * Utilisation:
 * - Inclure ce fichier en haut de ta page quiz (celle qui gère l'affichage)
 * - Ensuite, utiliser $questions, $errors, $result, $_SESSION['csrf'] dans ton HTML
 */

session_start();


// Empêche le cache navigateur (sinon CSRF périmé après logout/login ou retour arrière)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');


require __DIR__ . '/db.php';     // fournit $mysqli (mysqli)
require __DIR__ . '/config.php'; // si tu as APP_BASE ou helpers






// ------------------------------------------------------------
// 1) Auth: on exige un utilisateur connecté
// ------------------------------------------------------------
$userId = $_SESSION['user_id'] ?? null;
if (!$userId || !is_numeric($userId)) {
  http_response_code(401);
  exit('Connecte-toi pour faire le quiz.');
}
$uid = (int)$userId;






function base64url_token(int $bytes = 16): string {
  $raw = random_bytes($bytes);
  return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
}




// ------------------------------------------------------------
// 2) Paramètres quiz
// ------------------------------------------------------------
$quizKey = 'bon_coup_v1';
$cooldownSeconds = 6 * 3600; // 6 heures

// ------------------------------------------------------------
// 3) Questions (score par réponse: 0 / 1 / 2)
// ------------------------------------------------------------
$questions = [
  1 => ["Avant un date, tu fais quoi ?", [
    0 => "Rien, on verra sur place",
    1 => "Je me prépare un minimum",
    2 => "Je pense aussi au confort de l’autre (rythme, contexte, consentement)"
  ]],
  2 => ["Pendant un moment intime, ta priorité c’est…", [
    0 => "Moi, et vite",
    1 => "Les deux, équilibré",
    2 => "Surtout que l’autre se sente bien et en confiance (et moi aussi)"
  ]],
  3 => ["Si l’autre te dit “plus doucement / différemment”…", [
    0 => "Ça me casse le délire",
    1 => "J’écoute et j’ajuste",
    2 => "Je remercie, je m’adapte direct et je demande ce qui est mieux"
  ]],
  4 => ["Le consentement, pour toi…", [
    0 => "“Si elle/il est là, c’est ok”",
    1 => "Je fais attention aux signaux",
    2 => "Je vérifie clairement, sans malaise, et je respecte à 100%"
  ]],
  5 => ["Le rythme idéal ?", [
    0 => "Toujours intense",
    1 => "Ça dépend du moment",
    2 => "Je m’adapte au ressenti + je prends le temps quand il faut"
  ]],
  6 => ["Communication (avant/pendant/après) :", [
    0 => "Je parle pas, ça fait bizarre",
    1 => "Quelques mots, ça va",
    2 => "Je suis à l’aise : je guide, je demande, je rassure"
  ]],
  7 => ["Après, tu fais quoi ?", [
    0 => "Je dors / je pars sur mon tel",
    1 => "Un minimum de câlins / échange",
    2 => "Je prends soin : câlin, eau, discussion, respect"
  ]],
  8 => ["Si ça ne “fonctionne” pas comme prévu (stress, fatigue, etc.)", [
    0 => "Je panique / je me braque",
    1 => "Je relativise",
    2 => "Je dédramatise et je propose autre chose, sans pression"
  ]],
  9 => ["Ton rapport aux attentes de l’autre :", [
    0 => "Je fais “comme dans les films”",
    1 => "J’apprends au fil des rencontres",
    2 => "J’écoute, je m’adapte, je comprends que chaque personne est différente"
  ]],
  10 => ["Ton niveau de confiance sans arrogance :", [
    0 => "Je me vante / ou je doute trop",
    1 => "Je suis ok avec moi-même",
    2 => "Confiant + humble : je sais m’améliorer et je reste attentif"
  ]],
];

// ------------------------------------------------------------
// 4) CSRF
// ------------------------------------------------------------
if (empty($_SESSION['csrf']) || (($_SESSION['csrf_uid'] ?? null) !== $uid)) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
  $_SESSION['csrf_uid'] = $uid;
}

// ------------------------------------------------------------
// 5) Sorties (consommées par ta partie HTML)
// ------------------------------------------------------------
$errors = [];
$result = null;


// ------------------------------------------------------------
// 6) POST: validation + cooldown + calcul score + insert
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // 6.1) CSRF
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'], $csrf)) {
    $errors[] = "Session expirée. Recharge la page.";
  }

  // 6.2) Cooldown 6h (uniquement à la soumission)
  if (!$errors) {
    $stmt = $mysqli->prepare("
      SELECT created_at
      FROM quiz_sexperf_results
      WHERE user_id = ? AND quiz_key = ?
      ORDER BY created_at DESC
      LIMIT 1
    ");
    if (!$stmt) {
     error_log('[quiz_bon_coup] SQL prepare cooldown failed: ' . $mysqli->error);
    http_response_code(500);
    exit('Erreur serveur. Réessaie plus tard.');

    }

    $stmt->bind_param("is", $uid, $quizKey);
    $stmt->execute();
    $stmt->bind_result($lastCreatedAt);
    $hasRow = $stmt->fetch();
    $stmt->close();

   if ($hasRow && $lastCreatedAt) {
  $delta = time() - strtotime($lastCreatedAt);

  if ($delta < $cooldownSeconds) {
    $remaining = $cooldownSeconds - $delta;

    $hours = intdiv($remaining, 3600);
    $minutes = (int)ceil(($remaining % 3600) / 60);

    if ($hours > 0) {
      $errors[] = "Tu as déjà fait ce quiz récemment. Réessaie dans {$hours}h {$minutes}min.";
    } else {
      $errors[] = "Tu as déjà fait ce quiz récemment. Réessaie dans {$minutes} min.";
    }
  }
}

  }

  // 6.3) Calcul du score
  $score = 0;
  if (!$errors) {
    foreach ($questions as $i => $_q) {
      if (!isset($_POST["q$i"])) {
        $errors[] = "Il manque la question $i.";
        continue;
      }
      $v = (string)$_POST["q$i"];
      if (!in_array($v, ['0','1','2'], true)) {
        $errors[] = "Valeur invalide à la question $i.";
        continue;
      }
      $score += (int)$v;
    }
  }

  // 6.4) Score => lettre A/B/C
  if (!$errors) {
    if ($score <= 7) {
      $letter = 'A';
    } elseif ($score <= 14) {
      $letter = 'B';
    } else {
      $letter = 'C';
    }

    // Textes résultat (affichage)
    $texts = [
      'A' => "Pas encore au point : tu joues trop au hasard. Le vrai niveau c’est l’écoute, la com, le respect. Ça s’apprend vite.",
      'B' => "Bon niveau : plutôt solide et adaptable. Un peu plus de finesse/communication et tu passes au-dessus.",
      'C' => "Très bon : respect, écoute, adaptation, confiance. En général, c’est ce qui fait la diff."
    ];

    // 6.5) INSERT en base (privacy)
$shareToken = base64url_token(16); // ~22 chars

$stmt = $mysqli->prepare("
  INSERT INTO quiz_sexperf_results (user_id, quiz_key, score_total, result_letter, share_token)
  VALUES (?, ?, ?, ?, ?)
");
if (!$stmt) {
  error_log('[quiz_bon_coup] SQL prepare failed: ' . $mysqli->error);
  http_response_code(500);
  exit('Erreur serveur. Réessaie plus tard.');
}

$scoreInt = (int)$score;
$stmt->bind_param("isiss", $uid, $quizKey, $scoreInt, $letter, $shareToken);

if (!$stmt->execute()) {
  error_log('[quiz_bon_coup] SQL execute failed: ' . $stmt->error);
  http_response_code(500);
  exit('Erreur serveur. Réessaie plus tard.');
}
$stmt->close();

    $result = [
      'score'  => $scoreInt,
      'letter' => $letter,
      'text'   => $texts[$letter] ?? '',
      'share_token' => $shareToken,

    ];
    // Sécurité : on régénère le token CSRF après un POST valide
$_SESSION['csrf'] = bin2hex(random_bytes(16));



  }
  
}

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tchat direct - Quiz — Es-tu un bon coup ?</title>
  <link rel="stylesheet" href="<?= APP_BASE ?>/styles/tokens.css">
</head>
<body>

<main class="nb-wrap">
  <h1>Tchat direct</h1>
  <h2>Quiz : Es-tu un bon coup ?</h2>

  <?php if ($errors): ?>
    <div class="nb-error">
      <strong>Erreurs :</strong>
      <ul><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
    </div>
  <?php endif; ?>




  <?php if ($result): ?> 
    <div class="nb-card">
      <h2>Résultat <?= htmlspecialchars($result['letter']) ?> (score <?= (int)$result['score'] ?>/20)</h2>
      <p><?= htmlspecialchars($result['text']) ?></p>

      <div class="nb-actions" style="margin-top: var(--sp-12)">
  <button type="button" id="btnShare" class="nb-btn">
    Partager mon résultat
  </button>
</div>

    </div>
  <?php endif; ?>






  <form method="post" id="quizForm">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

    <div id="quiz" data-total="<?= count($questions) ?>">
      <div class="nb-card nb-card--tight">
        <div class="nb-row">
          <strong>Progression</strong>
          <span id="progressText">1/<?= count($questions) ?></span>
        </div>
        <div class="nb-progress">
          <div id="progressBar"></div>
        </div>
      </div>

      <?php foreach ($questions as $i => [$title, $choices]): ?>
        <section class="qstep" data-step="<?= $i ?>" style="display:none">
          <h3><?= $i ?>) <?= htmlspecialchars($title) ?></h3>

          <?php foreach ($choices as $val => $label): ?>
            <label class="answer-card">
              <input type="radio" name="q<?= $i ?>" value="<?= (int)$val ?>"
                <?= (isset($_POST["q$i"]) && $_POST["q$i"] === (string)$val) ? 'checked' : '' ?>>
              <span><?= htmlspecialchars($label) ?></span>
            </label>
          <?php endforeach; ?>

          <div id="err<?= $i ?>" class="qerr" style="display:none">
            Choisis une réponse pour continuer.
          </div>
        </section>
      <?php endforeach; ?>

      <div class="nb-actions">
        <button type="button" id="btnPrev" class="nb-btn">← Précédent</button>
        <button type="button" id="btnNext" class="nb-btn">Suivant →</button>
        <button type="submit" id="btnSubmit" class="nb-btn" style="display:none">Voir mon résultat</button>
      </div>
    </div>
  </form>
</main>




<script> // Gestion du quiz multi-steps


// Si la page revient du cache (bouton retour), on recharge pour récupérer le bon CSRF
window.addEventListener('pageshow', function (e) {
  if (e.persisted) window.location.reload();
});

try {
  const nav = performance.getEntriesByType('navigation')[0];
  if (nav && nav.type === 'back_forward') window.location.reload();
} catch (e) {}




(function () {
  const steps = Array.from(document.querySelectorAll('.qstep'));
  if (!steps.length) return;

  const total = steps.length; // nombre de steps
  let current = 1;

  const progressText = document.getElementById('progressText');
  const progressBar  = document.getElementById('progressBar');
  const btnPrev = document.getElementById('btnPrev');
  const btnNext = document.getElementById('btnNext');
  const btnSubmit = document.getElementById('btnSubmit');



document.addEventListener('change', (e) => { // auto-advance au step suivant quand on répond
  if (e.target && e.target.matches('input[type="radio"]')) {
if (navigator.vibrate) navigator.vibrate(10);

    const err = document.getElementById('err' + current); 
    if (err) err.style.display = 'none'; // cache l'erreur si présente

    const name = e.target.getAttribute('name');
    const stepNum = parseInt(name.replace('q',''), 10); 
    if (stepNum === current && current < total) showStep(current + 1); 
  }
});


function showStep(n) {
  const prev = current;
  current = Math.min(Math.max(n, 1), total);

  steps.forEach(s => {
    s.classList.remove('active', 'exit-left', 'exit-right');
    s.style.display = 'none';
  });

  const direction = current > prev ? 'right' : 'left';


  // nouvelle question (animation entrée)
  const newStep = steps.find(s => parseInt(s.dataset.step, 10) === current);
  if (newStep) {
    newStep.style.display = 'block';
    requestAnimationFrame(() => newStep.classList.add('active'));
  }

  // progress bar
  const pct = total > 1
    ? Math.round((current - 1) / (total - 1) * 100)
    : 100;

  progressBar.style.width = pct + '%';
  progressText.textContent = current + '/' + total;

  btnPrev.disabled = current === 1;
  btnNext.style.display = current === total ? 'none' : 'inline-block';
  btnSubmit.style.display = current === total ? 'inline-block' : 'none';

  // cache l’erreur
  const err = document.getElementById('err' + current);
  if (err) err.style.display = 'none';

  // scroll doux
  newStep?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}


  function isAnswered(stepNum) {
    return !!document.querySelector('input[name="q' + stepNum + '"]:checked');
  }

  btnPrev.addEventListener('click', () => showStep(current - 1));

  btnNext.addEventListener('click', () => {
    if (!isAnswered(current)) {
      const err = document.getElementById('err' + current);
      if (err) err.style.display = 'block';
      return;
    }
    showStep(current + 1);
  });

  // Si l’utilisateur clique submit sans avoir répondu (rare), on bloque
  btnSubmit.addEventListener('click', (e) => {
    for (let i = 1; i <= total; i++) {
      if (!isAnswered(i)) {
        e.preventDefault();
        showStep(i);
        const err = document.getElementById('err' + i);
        if (err) err.style.display = 'block';
        return;
      }
    }
  });

  // Si tu recharges après erreurs serveur, on peut reprendre au premier step incomplet
  let firstMissing = 1;
  for (let i = 1; i <= total; i++) {
    if (!isAnswered(i)) { firstMissing = i; break; }
  }
  showStep(firstMissing);
})();




(function () {
  const btn = document.getElementById('btnShare');
  if (!btn) return;

  // Données injectées depuis PHP
  const letter = "<?= $result['letter'] ?? '' ?>";
  const labelMap = { A: "À améliorer", B: "Bon niveau", C: "Très bon" };
  const label = labelMap[letter] || "";

  // URL shareable unique
  const token = "<?= $result['share_token'] ?? '' ?>";
const url = window.location.origin + "<?= APP_BASE ?>/quiz_share.php?t=" + encodeURIComponent(token);

  const text =
    `J’ai fait le quiz "Es-tu un bon coup ?" sur Tchat Direct 😏\n` +
    `Résultat : ${letter} – ${label}\n` +
    `👉 ${url}`;

  btn.addEventListener('click', async () => {
    // Partage natif (mobile)
    if (navigator.share) {
      try {
        await navigator.share({
          title: "Quiz Tchat Direct",
          text,
          url
        });
        return;
      } catch (e) {
        // annulé -> fallback copie
      }
    }
if (!token) {
  alert("Impossible de générer le lien de partage. Recommence le quiz.");
  return;
}

    // Fallback desktop : copie presse-papiers
    try {
      await navigator.clipboard.writeText(text);
      btn.textContent = "Résultat copié ✔";
      setTimeout(() => (btn.textContent = "Partager mon résultat"), 2000);
    } catch (e) {
      alert("Impossible de copier le résultat.");
    }
  });
})();














</script>












</body>
</html>
