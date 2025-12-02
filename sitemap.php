<?php
declare(strict_types=1);
header("Content-Type: application/xml; charset=UTF-8");

require __DIR__ . '/db.php';
require __DIR__ . '/config.php'; // contient APP_BASE

$base = rtrim(APP_BASE, '/');

// Récupération des rooms publiques
$sql = "
    SELECT 
        id,
        name,
        created_at,
        is_private
    FROM chat_rooms
    WHERE is_private = 0
    ORDER BY id ASC
";

$res = $mysqli->query($sql);

$rooms = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $rooms[] = $row;
    }
}

// Fonction pour sécuriser XML
function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <!-- Page d’accueil -->
  <url>
    <loc><?= esc($base . '/') ?></loc>
    <priority>1.0</priority>
    <changefreq>daily</changefreq>
  </url>

  <!-- Login -->
  <url>
    <loc><?= esc($base . '/login.php') ?></loc>
    <priority>0.6</priority>
  </url>

  <!-- Register -->
  <url>
    <loc><?= esc($base . '/register.php') ?></loc>
    <priority>0.6</priority>
  </url>

<?php foreach ($rooms as $r): ?>
<?php
    // Slug
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $r['name']), '-'));

    // URL de la room
    $url = $base . '/room.php?id=' . $r['id'] . '&slug=' . $slug;

    // Lastmod = date de création (on peut améliorer plus tard)
    $lastmod = substr($r['created_at'], 0, 10);
?>
  <url>
    <loc><?= esc($url) ?></loc>
    <lastmod><?= esc($lastmod) ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.5</priority>
  </url>
<?php endforeach; ?>

</urlset>
