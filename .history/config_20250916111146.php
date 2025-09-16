<?php
declare(strict_types=1);
define('APP_BASE', rtrim(dirname($_SERVER['PHP_SELF']), '/\\')); // ex: /loi
define('PROJECT_PAGE', 'project.php'); // unique
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