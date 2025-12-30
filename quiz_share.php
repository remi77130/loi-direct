<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/config.php';

$token = $_GET['t'] ?? '';
if (!preg_match('/^[A-Za-z0-9\-_]{16,32}$/', $token)) {
  http_response_code(404);
  exit('Lien invalide.');
}

$stmt = $mysqli->prepare("
  SELECT quiz_key, score_total, result_letter, created_at
  FROM quiz_sexperf_results
  WHERE share_token = ? AND share_enabled = 1
  LIMIT 1
");
$stmt->execute();

$row = null;
if (method_exists($stmt, 'get_result')) {
  $res = $stmt->get_result();
  $row = $res ? $res->fetch_assoc() : null;
} else {
  // fallback sans mysqlnd
  $stmt->bind_result($quizKeyDb, $scoreTotalDb, $resultLetterDb, $createdAtDb);
  if ($stmt->fetch()) {
    $row = [
      'quiz_key'      => $quizKeyDb,
      'score_total'   => $scoreTotalDb,
      'result_letter' => $resultLetterDb,
      'created_at'    => $createdAtDb,
    ];
  }
}
$stmt->close();

$texts = [
  'A' => "Pas encore au point : tu joues trop au hasard. Le vrai niveau c’est l’écoute, la com, le respect. Ça s’apprend vite.",
  'B' => "Bon niveau : plutôt solide et adaptable. Un peu plus de finesse/communication et tu passes au-dessus.",
  'C' => "Très bon : respect, écoute, adaptation, confiance. En général, c’est ce qui fait la diff."
];
$labelMap = ['A' => 'À améliorer', 'B' => 'Bon niveau', 'C' => 'Très bon'];


$letter = $row['result_letter'] ?? '';
if (!in_array($letter, ['A','B','C'], true)) {
  http_response_code(404);
  exit('Résultat invalide.');
}

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tchat direct — Résultat du quiz</title>
  <link rel="stylesheet" href="<?= APP_BASE ?>/styles/tokens.css">
</head>
<body>
<main class="nb-wrap">
  <h1>Tchat direct</h1>

  <div class="nb-card">
    <h2>Résultat du quiz</h2>
    <p><strong><?= htmlspecialchars($letter) ?> — <?= htmlspecialchars($labelMap[$letter] ?? '') ?></strong></p>
    <p>Score : <?= $score ?>/20</p>
    <p><?= htmlspecialchars($texts[$letter] ?? '') ?></p>
  </div>

  <div class="nb-actions">
    <a class="nb-btn" href="<?= APP_BASE ?>/quiz_bon_coup.php">Faire le quiz</a>
  </div>
</main>
</body>
</html>
