<?php // api_user_profile.php
declare(strict_types=1);
session_start();
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

/*
Ce fichier api_user_profile.php sert d’API (interface entre ton front-end et la base de données) 
pour récupérer les informations détaillées d’un utilisateur lorsqu’on clique sur son pseudo dans le chat.
Fonction concrète
Le navigateur envoie une requête AJAX à api_user_profile.php avec :
soit ?id=14,
soit ?username=sandra77.
Le script :
vérifie la session (l’utilisateur doit être connecté) ;
interroge la table users en base pour récupérer les champs :
id, username, email, city, age, about, avatar_url ;
renvoie ces données au format JSON au navigateur.
Le navigateur reçoit ce JSON et l’affiche dans la modal profil (photo, nom, ville, âge, description, etc.).
Sans ce fichier
Le JavaScript ne peut pas remplir la fenêtre de profil, car il ne connaît pas les données de l’utilisateur.
Autrement dit :
Sans api_user_profile.php → la modal afficherait seulement le pseudo cliqué.
Avec → la modal affiche les infos complètes, directement extraites de ta base.
*/
header('Content-Type: application/json; charset=utf-8');
$qId = trim($_GET['id'] ?? '');
$qName = trim($_GET['username'] ?? '');

if ($qId===' ' && $qName==='') { echo json_encode(['ok'=>false]); exit; }

if ($qId!=='') {
  $st = $pdo->prepare('SELECT id,username,email,city,age,about,avatar_url FROM users WHERE id=?');
  $st->execute([$qId]);
} else {
  $st = $pdo->prepare('SELECT id,username,email,city,age,about,avatar_url FROM users WHERE username=?');
  $st->execute([$qName]);
}
$u = $st->fetch(PDO::FETCH_ASSOC);
if (!$u){ echo json_encode(['ok'=>false]); exit; }
echo json_encode($u);
