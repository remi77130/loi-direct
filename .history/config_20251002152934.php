<?php // constantes globales appli, surtout upload.
declare(strict_types=1);
define('APP_BASE', rtrim(dirname($_SERVER['PHP_SELF']), '/\\')); // ex: /loi
define('PROJECT_PAGE', 'project.php'); // unique



function random_punchline(string $pseudo): string { // Phrase dacceuil 
  $lines = [
    "Bienvenue {pseudo} ! Ton compte est chaud bouillant 🔥",
    "C’est signé {pseudo}. On est bien 👌",
    "{pseudo}, t’es dans la place. Let’s go 🚀",
    "Bienvenue {pseudo} ! À toi de jouer ⚖️",
    "Inscription validée, — ma p’tite gueule ! 😎",
    "On t’attendait {pseudo} ! Fais-nous rêver ✍️",
    "{pseudo}, le peuple compte sur toi 💪",
    "Own the feed, {pseudo} ! 🏛️",
  ];
  $i = random_int(0, count($lines)-1);
  return strtr($lines[$i], ['{pseudo}' => $pseudo]);
}



/** Slugify : "Éxemple de Titre !" -> "exemple-de-titre" */
function slugify(string $text): string {
    $text = trim($text);

    if (function_exists('transliterator_transliterate')) {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
    } else {
        $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($tmp !== false) $text = $tmp;
    }

    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'projet');
}

function tag_slug(string $name): string { return slugify($name); }
function tag_url(string $slug): string { return APP_BASE . '/tag/' . rawurlencode($slug); }
function project_url(int $id, string $title): string {
    return APP_BASE . '/p/' . $id . '-' . rawurlencode(slugify($title));
}


// images
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('UPLOAD_URL', APP_BASE . '/uploads');
define('UPLOAD_MAX_MB', 5);                        // 5 Mo par image
define('UPLOAD_MAX_FILES', 5);                     // max 5 images
define('UPLOAD_ALLOWED', ['image/jpeg','image/png','image/webp']);

    // Thumbnails
    define('THUMB_MAX_W', 480);
    define('THUMB_MAX_H', 480);
