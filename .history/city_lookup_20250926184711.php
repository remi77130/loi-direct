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
  echo json_encode(['ok'=>false,'error'=>'bad_postal']);
  exit;
}

try {
  // TRIM pour tolérer des CP avec espaces en base
  $sql = 'SELECT DISTINCT city FROM cities WHERE TRIM(postal_code)=? ORDER BY city LIMIT 200';
  $st = $mysqli->prepare($sql);
  $st->bind_param('s', $postal);
  $st->execute();
  $res = $st->get_result();
  $out = [];
  while ($row = $res->fetch_assoc()) $out[] = (string)$row['city'];
  $st->close();

  echo json_encode(['ok'=>true,'cities'=>$out], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'server']);
}
