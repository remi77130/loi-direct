<?php
// db.php Ignore db.php (dans .gitignore) s’il contient identifiants.
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'loi_direct';
const DB_USER = 'root';
const DB_PASS = ''; // XAMPP par défaut

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    exit('Erreur BDD: '.$mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
