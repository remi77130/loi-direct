<?php
// room.php — Page publique SEO pour un salon


declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/config.php'; // APP_BASE etc.


// --- 1) Récupération de l'ID de salon ---
$roomId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$slugGet = (string)($_GET['slug'] ?? '');

if (!$roomId) {
    header('Location: '.rtrim(APP_BASE,'/').'/index.php', true, 302); // Rediriger vers la page d’accueil quand il n’y a pas d’id :
    exit;
}


// --- 2) Récupération des infos du salon ---
$sql = "SELECT id, name, is_private, created_at FROM chat_rooms WHERE id = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo "Erreur serveur.";
    error_log('room.php prepare room: '.$mysqli->error);
    exit;
}
$stmt->bind_param('i', $roomId);
$stmt->execute();
$res = $stmt->get_result();
$room = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$room) {
    http_response_code(404);
    echo "Salon introuvable.";
    exit;
}

// Si salon privé → 404 (ou redirection login si tu préfères)
if ((int)$room['is_private'] === 1) {
    http_response_code(404);
    echo "Salon introuvable.";
    exit;
}

// --- 3) Récupération des messages (par ex. 50 derniers) ---
$msgs = [];
$sql = "
  SELECT 
    m.id,
    m.body,
    m.created_at,
    m.color,
    m.like_count,
    m.file_url,
    m.file_mime,
    m.file_w,
    m.file_h,
    u.pseudo
  FROM chat_messages m
  LEFT JOIN users u ON u.id = m.sender_id
  WHERE m.room_id = ?
  ORDER BY m.id ASC
  LIMIT 50
";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $roomId);
    if ($stmt->execute() && ($res = $stmt->get_result())) {
        while ($row = $res->fetch_assoc()) {
            $msgs[] = $row;
        }
    }
    $stmt->close();
}

// --- 4) SEO : slug canonique, description, dates ---
$roomName = (string)$room['name'];
$roomSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $roomName), '-'));
$canonicalUrl = rtrim(APP_BASE, '/') . '/room.php?id=' . $roomId . '&slug=' . $roomSlug;

// si l’URL ne correspond pas au slug canonique → redirection 301
if ($slugGet !== '' && $slugGet !== $roomSlug) {
    header('Location: '.$canonicalUrl, true, 301);
    exit;
}

// meta description = premier message tronqué si dispo
$metaDesc = "Salon public \"$roomName\" sur Tchat-Direct. Discute en direct de façon anonyme.";
if (!empty($msgs)) {
    $firstText = strip_tags($msgs[0]['body'] ?? '');
    $firstText = preg_replace('/\s+/', ' ', $firstText);
    $firstText = mb_substr($firstText, 0, 160);
    if ($firstText !== '') {
        $metaDesc = $firstText;
    }
}
// JSON-LD complet pour une room publique
// JSON-LD complet pour une room publique
$ld = [
  '@context'  => 'https://schema.org',
  '@type'     => 'DiscussionForumPosting',
  'headline'  => 'Salon public - '.$roomName,
  'name'      => $roomName,
  'url'       => $canonicalUrl,
  'publisher' => [
      '@type' => 'Organization',
      'name'  => 'Tchat-Direct',
  ],
];

// Init SAFE (évite les warnings)
$firstDate = '';
$lastDate  = '';

if (!empty($msgs)) {
  // Premier message
  $first = $msgs[0];
  $firstAuthor = $first['pseudo'] ?: 'Anonyme';
  $firstBody   = strip_tags($first['body'] ?? '');
  $firstBody   = preg_replace('/\s+/', ' ', $firstBody);
  $firstBody   = mb_substr($firstBody, 0, 300);
  $firstDate   = $first['created_at'] ?? '';

  // Dernier message = dernière activité
  $last = end($msgs);
  $lastDate = $last['created_at'] ?? '';

  // author
  $ld['author'] = [
    '@type' => 'Person',
    'name'  => $firstAuthor,
  ];

  // text + description
  if ($firstBody !== '') {
    $ld['text']        = $firstBody;
    $ld['description'] = $firstBody;
  }

  // Dates en ISO 8601
  if ($firstDate !== '') {
    $ld['datePublished'] = date('c', strtotime($firstDate));
  }
  if ($lastDate !== '') {
    $ld['dateModified'] = date('c', strtotime($lastDate));
  }
}


?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars('Salon '.$roomName.' – Tchat Direct', ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="index, follow">


  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">

  <!-- Favicon / icons comme sur login/register -->
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link rel="icon" type="image/png" sizes="32x32" href="/uploads/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/uploads/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/uploads/apple-touch-icon.png">
  <link rel="manifest" href="site.webmanifest"><!-- OpenGraph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Tchat-Direct">
<meta property="og:title" content="<?= htmlspecialchars('Salon ' . $roomName . ' – Tchat Direct', ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">

<!-- Image par défaut (peut être remplacée par une image dynamique par salon) -->
<meta property="og:image" content="https://tchat-direct.com/og_image.php?room=<?= urlencode($roomName) ?>">
<meta property="og:image:width" content="600">
<meta property="og:image:height" content="600">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars('Salon ' . $roomName . ' – Tchat Direct', ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:image" content="https://tchat-direct.com/og_image.php?room=<?= urlencode($roomName) ?>">
<meta name="twitter:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">


  <script type="application/ld+json">
<?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>

  </script>

  <style>
    :root{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif}
    body{
      margin:0;
      background:#0f172a;
      color:#e5e7eb;
      display:flex;
      flex-direction:column;
      min-height:100vh;
      align-items:center;
    }
    .site-header{
      width:100%;
      padding:12px 20px;
      display:flex;
      align-items:center;
      justify-content:flex-start;
    }
    .logo-img{height:80px;display:block}
    .page{
      width:100%;
      max-width:900px;
      padding:16px;
      box-sizing:border-box;
    }
    .room-header{
      margin-bottom:16px;
    }
    .room-header h1{
      margin:0 0 8px;
      font-size:24px;
    }
    .room-header p{
      margin:0;
      font-size:14px;
      color:#9ca3af;
    }

    .public-room__messages{
      max-height:none;
      overflow:visible;
      padding-right:0;
      margin-bottom:25px
    }
    .public-room__list{
      list-style:none;
      margin:0;
      padding:0;
    }
    .public-room__item{
      padding:0.6rem 0;
      border-bottom:1px dashed #1f2937;
    }
    .public-room__meta{
      font-size:0.8rem;
      color:#9ca3af;
      margin-bottom:0.15rem;
    }
    .public-room__author{font-weight:600}
    .public-room__sep{margin:0 0.25rem}
    .public-room__body{
      font-size:0.9rem;
      line-height:1.4;
    }
    .public-room__likes{
      margin-left:0.5rem;
      font-size:0.8rem;
      padding:0.1rem 0.4rem;
      border-radius:999px;
      background:#111827;
      color:#fbbf24;
    }
    .public-room__image-wrap{margin-top:4px}
    .public-room__image{
      max-width:60%;
      height:auto;
      border-radius:8px;
      display:block;
    }

    .cta-bar{
      margin:16px 0;
      padding:12px;
      background:#111827;
      border-radius:10px;
      font-size:14px;
    }
    .cta-bar a.btn{
      display:inline-block;
      margin-top:8px;
      padding:8px 14px;
      border-radius:999px;
      background:#2563eb;
      color:#fff;
      text-decoration:none;
      font-weight:600;
    }
    .cta-bar a.btn:hover{background:#1d4ed8;}

    .link_response{    color: aliceblue;
    border: 0.5px solid hotpink;
    padding: 10px 15px;
    border-radius: 900px;
    text-decoration: none;}
  </style>
</head>
<body>

<header class="site-header">
  <a href="index.php" class="logo-link">
    <img src="uploads/tchat_direct_logo.webp" alt="Tchat Direct logo" class="logo-img">
  </a>
</header>

<main class="page">
  <div class="room-header">
    <h1>Salon public – <?= htmlspecialchars($roomName, ENT_QUOTES, 'UTF-8') ?></h1>
    <p>
      Salon public de Tchat-Direct. Lecture libre.
      Pour répondre, connecte-toi ou crée un compte.
    </p>
  </div>

  <div id="cta-bar" class="cta-bar">
    Tu veux participer à la discussion ?
    <br>
    <a href="login.php" class="btn">Se connecter</a>
    <a href="register.php" class="btn">Créer un compte gratuit</a>
  </div>

  <section class="public-room">
    <div class="public-room__messages">
      <?php if (empty($msgs)): ?>
        <p class="public-room__empty-msg">
          Pas encore de messages dans ce salon.
        </p>
      <?php else: ?>
        <ul class="public-room__list">
          <?php foreach ($msgs as $msg): ?>
            <?php
              $color = $msg['color'] ?? '';
              $style = '';
              if ($color !== '') {
                  $style = ' style="color:' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '"';
              }

              $likes = isset($msg['like_count']) ? (int)$msg['like_count'] : 0;
              $fileMime = $msg['file_mime'] ?? '';
              $fileUrl  = $msg['file_url']  ?? '';
              $fileH    = isset($msg['file_h']) ? (int)$msg['file_h'] : 0;
              $fileW    = isset($msg['file_w']) ? (int)$msg['file_w'] : 0;
              
                            // Compatibilité PHP 7 — remplace str_starts_with

              $isImage = $fileUrl !== '' && (
                $fileMime === '' || strpos($fileMime, 'image/') === 0 
              );



            ?>
            <li class="public-room__item">
              <div class="public-room__meta">
                <strong class="public-room__author">
                  <?= htmlspecialchars($msg['pseudo'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8') ?>
                </strong>
                <span class="public-room__sep">·</span>
                <time datetime="<?= htmlspecialchars($msg['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                  <?= htmlspecialchars($msg['created_at'], ENT_QUOTES, 'UTF-8') ?>
                </time>

                <?php if ($likes > 0): ?>
                  <span class="public-room__likes">
                    ❤️ <?= $likes ?>
                  </span>
                <?php endif; ?>
              </div>

              <div class="public-room__body"<?= $style ?>>
                <?= nl2br(htmlspecialchars($msg['body'], ENT_QUOTES, 'UTF-8')) ?>
              </div>

             <?php if ($isImage): ?>
  <figure class="public-room__image-wrap">
    <img
      src="<?= htmlspecialchars($fileUrl, ENT_QUOTES, 'UTF-8') ?>"
      alt="Image envoyée dans le salon"
      class="public-room__image"
      <?php if ($fileW > 0 && $fileH > 0): ?>
        width="<?= (int)$fileW ?>" height="<?= (int)$fileH ?>"
      <?php endif; ?>
    >
  </figure>
<?php endif; ?>


            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>


    
                            <a class="link_response" href="register.php">Répondre </a>
  </section>
</main>

</body>
</html>
