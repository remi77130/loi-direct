<?php
// city_lookup.php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$postal = $_GET['postal'] ?? '';
if (!preg_match('/^\d{5}$/', $postal)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad_postal']); exit;
}

$sql = "
  SELECT DISTINCT city
  FROM cities
  WHERE CONCAT(';', REPLACE(postal_code, ' ', ''), ';')
        LIKE CONCAT('%;', ?, ';%')
  ORDER BY city
  LIMIT 200
";
$st = $mysqli->prepare($sql) ?: throw new RuntimeException($mysqli->error);
$st->bind_param('s', $postal);
$st->execute();
$res = $st->get_result();
$out = [];
while ($row = $res->fetch_assoc()) $out[] = (string)$row['city'];
$st->close();

echo json_encode(['ok'=>true,'cities'=>$out], JSON_UNESCAPED_UNICODE);
