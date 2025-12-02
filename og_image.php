<?php
// OG Image dynamique : logo rond + gros nom du salon
// Compatible PHP 7 / 8 + fallback si manque GD ou TTF

$room = $_GET['room'] ?? 'Salon';
$room = urldecode($room);
$room = substr($room, 0, 40); // on limite la longueur pour éviter de déborder

// 1) Fallback si GD non dispo
if (!function_exists('imagecreatetruecolor')) {
    header("Content-Type: image/png");
    readfile(__DIR__ . '/uploads/tchat_direct_logo.png');
    exit;
}

// 2) Dimensions OG (standard)
$w = 1200;
$h = 630;
$img = imagecreatetruecolor($w, $h);

// Gestion de l’alpha propre
imagealphablending($img, true);
imagesavealpha($img, true);

// 3) Dégradé de fond (fondu)
for ($y = 0; $y < $h; $y++) {
    // du bleu nuit foncé en haut à encore plus foncé en bas
    $ratio = $y / $h;
    $r = 15 - (int)(15 * $ratio);
    $g = 23 - (int)(10 * $ratio);
    $b = 42 - (int)(5 * $ratio);
    $color = imagecolorallocate($img, $r, $g, $b);
    imageline($img, 0, $y, $w, $y, $color);
}

// 4) Cercle derrière le logo (effet "logo rond")
$circleColor = imagecolorallocatealpha($img, 15, 23, 42, 40); // cercle un peu transparent
$circleX = (int)($w / 2);
$circleY = 220;
$circleR = 180;

imagefilledellipse($img, $circleX, $circleY, $circleR * 2, $circleR * 2, $circleColor);

// 5) Logo au centre du cercle
$logoPath = __DIR__ . '/uploads/tchat_direct_logo.png';
if (file_exists($logoPath)) {
    $logo = imagecreatefrompng($logoPath);
    imagealphablending($logo, true);
    imagesavealpha($logo, true);

    $logoW = imagesx($logo);
    $logoH = imagesy($logo);

    // On adapte le logo à 220 px max de large
    $targetW = 220;
    $scale   = $targetW / $logoW;
    $targetH = (int)($logoH * $scale);

    $logoX = (int)($circleX - $targetW / 2);
    $logoY = (int)($circleY - $targetH / 2);

    imagecopyresampled(
        $img, $logo,
        $logoX, $logoY,
        0, 0,
        $targetW, $targetH,
        $logoW, $logoH
    );

    imagedestroy($logo);
}

// 6) Gros texte : "Salon : NOM" (priorité mobile)
$text = 'Salon : ' . $room;

// TTF si possible
$fontPath = __DIR__ . '/styles/tchat_direct_font_roboto.ttf';
$useTtf = function_exists('imagettftext') && file_exists($fontPath);

if ($useTtf) {
    // Taille de police assez grande pour mobile
    $fontSize = 75;

    // Calcul du bbox pour centrer
    $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
    $textWidth  = $bbox[2] - $bbox[0];
    $textHeight = $bbox[1] - $bbox[7];

    $x = (int)(($w - $textWidth) / 2);
    $y = $h - 140; // zone basse mais bien visible

    $white = imagecolorallocate($img, 255, 255, 255);

    imagettftext($img, $fontSize, 0, $x, $y, $white, $fontPath, $text);
} else {
    // Fallback imagestring si TTF indispo
    $font = 5;
    $tw = imagefontwidth($font) * strlen($text);
    $th = imagefontheight($font);

    $x = (int)(($w - $tw) / 2);
    $y = $h - 140;

    $white = imagecolorallocate($img, 255, 255, 255);
    imagestring($img, $font, $x, $y, $text, $white);
}

// 7) Sortie finale
header("Content-Type: image/png");
imagepng($img);
imagedestroy($img);
