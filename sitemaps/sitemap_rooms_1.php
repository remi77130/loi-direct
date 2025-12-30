<?php
declare(strict_types=1);

$debug = isset($_GET['debug']) && $_GET['debug'] === '1';

if ($debug) {
  header('Content-Type: text/plain; charset=UTF-8');
} else {
  header('Content-Type: application/xml; charset=UTF-8');
  ini_set('display_errors', '0');
  error_reporting(E_ALL);
}

require __DIR__ . '/../db.php'; // crée $mysqli (MySQLi)

// Sécurité : si la connexion n'existe pas
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  http_response_code(500);
  exit($debug ? "Erreur BDD: mysqli manquant\n" : '');
}

function slugify(string $text): string {
  $text = trim($text);
  $text = mb_strtolower($text, 'UTF-8');

  // retire accents
  if (class_exists('Transliterator')) {
    $tr = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC;');
    if ($tr) $text = $tr->transliterate($text);
  } else {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
  }

  $text = preg_replace('~[^a-z0-9]+~', '-', $text) ?? '';
  $text = trim($text, '-');
  return $text !== '' ? $text : 'salon';
}

/**
 * Rooms publiques, avec au moins 1 message
 * lastmod = date du dernier message
 */
$sql = "
  SELECT
    r.id,
    r.name,
    MAX(m.created_at) AS last_msg_at
  FROM chat_rooms r
  INNER JOIN chat_messages m ON m.room_id = r.id
  WHERE r.is_private = 0
  GROUP BY r.id, r.name
  ORDER BY r.id DESC
";

$res = $mysqli->query($sql);

// --- DEBUG : TEXTE SEUL ---
if ($debug) {
  echo "SQL OK\n";
  echo "Rows: " . ($res ? $res->num_rows : -1) . "\n";
  if (!$res) echo "MySQL error: " . $mysqli->error . "\n";
  exit;
}

// --- MODE NORMAL : XML ---
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

if (!$res) {
  error_log('[sitemap_rooms_1.php] SQL error: ' . $mysqli->error);
  echo "</urlset>\n";
  exit;
}

while ($row = $res->fetch_assoc()) {
  $id = (int)($row['id'] ?? 0);
  if ($id <= 0) continue;

  $slug = slugify((string)($row['name'] ?? ''));

  // & doit être &amp; en XML
  $loc = 'https://tchat-direct.com/room.php?id=' . $id . '&amp;slug=' .
         htmlspecialchars($slug, ENT_XML1 | ENT_QUOTES, 'UTF-8');

  $lastMsg = (string)($row['last_msg_at'] ?? '');
  $lastmod = $lastMsg ? date('Y-m-d', strtotime($lastMsg)) : date('Y-m-d');

echo "  <url>\n";
echo "    <loc>{$loc}</loc>\n";
echo "    <lastmod>{$lastmod}</lastmod>\n";
echo "    <changefreq>monthly</changefreq>\n";
echo "    <priority>0.6</priority>\n";
echo "  </url>\n";

}

echo "</urlset>\n";
