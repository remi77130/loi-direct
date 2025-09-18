<?php
// Laisser le serveur intégré servir les fichiers existants (CSS/JS/images)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== '/' && file_exists(__DIR__.$path)) {
    return false;
}

// /p/123-slug  -> project.php?id=123
if (preg_match('#^/p/(\d+)(?:-[\w\-]+)?$#', $path, $m)) {
    $_GET['id'] = (int)$m[1];
    require __DIR__.'/project.php';
    exit;
}

// /tag/voiture  -> index.php?tag=voiture
if (preg_match('#^/tag/([\w\-]+)$#', $path, $m)) {
    $_GET['tag'] = $m[1];
    require __DIR__.'/index.php';
    exit;
}

// Par défaut : index.php
require __DIR__.'/index.php';
