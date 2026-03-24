<?php // constantes globales appli, surtout upload.

/*Rôle: config globale front + upload.
Points clés:
APP_BASE calcule automatiquement le chemin de base de l’application à partir de l’URL (pratique pour éviter le hardcode).
PROJECT_PAGE = 'project.php' sert de page de référence principale pour un projet (probablement détail d’un projet / loi).
random_punchline($pseudo) génère une phrase d’accueil dynamique avec le pseudo utilisateur, ton “welcome” fun post-inscription/connexion.
Gestion des uploads:
UPLOAD_DIR = dossier physique uploads à la racine du projet.
UPLOAD_URL = URL publique correspondante.
UPLOAD_MAX_MB = 5 et UPLOAD_MAX_FILES = 5 limitent poids et nombre d’images.
MIME autorisés: jpeg, png, webp.
THUMB_MAX_W / THUMB_MAX_H = dimensions max des miniatures générées.
Conclusion: ce fichier pose le cadre global (routes, punchlines, règles d’upload).

-- Je n’ai pas mis de CSRF token ici pour garder l’étape simple.
En prod, il faudra l’ajouter. Mais pour avancer doucement, le cœur comptable est là.*/

declare(strict_types=1);
$docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
$appRoot = realpath(__DIR__); // dossier où est config.php (racine du projet)
$base = str_replace($docRoot, '', $appRoot);
$base = str_replace('\\', '/', $base);

define('APP_BASE', rtrim($base, '/')); // ex: /loi
define('BLOG_BASE', APP_BASE . '/blog');


// 

define('ADMIN_LOGIN', 'admin'); // Login admin fixe (pas de gestion des rôles dans ce projet, juste un accès admin unique)
define('ADMIN_PASSWORD_HASH', '$2y$10$zPUY7JgInUnPH0wCBaIlIeN/hfM4iEE7Nwx22SzNBWaJOpO8fk1jm'); // Hash du mot de passe admin généré par password_hash() (ex: $2y$10$abc...)


function random_punchline(string $pseudo): string { // Phrase dacceuil 
  $lines = [
    "Bienvenue {pseudo} ! Ton compte est chaud bouillant 🔥",
    "C’est signé {pseudo}. On est bien 👌",
    "{pseudo}, t’es dans la place. Let’s go 🚀",
    "Bienvenue {pseudo} ! À toi de jouer ⚖️",
    "{pseudo}, Inscription validée, ma p’tite gueule ! 😎",
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


// Uploads VIDEO ET IMAGE

define('UPLOAD_DIR', __DIR__ . '/uploads');     // Dossier physique
define('UPLOAD_URL', APP_BASE . '/uploads');    // URL publique

// Taille max (vidéo = plus lourd). Mets 50 Mo par exemple.
define('UPLOAD_MAX_MB', 50);    // Poids max total par envoi
define('UPLOAD_MAX_FILES', 5);  // Nombre max de fichiers par envoi

define('UPLOAD_ALLOWED', [
  // images
  'image/jpeg',
  'image/png',
  'image/webp',
  'image/gif',

  // vidéos
  'video/mp4',
  'video/webm',
  'video/ogg',
]);


    // Thumbnails
    define('THUMB_MAX_W', 480);
    define('THUMB_MAX_H', 480);
?>

