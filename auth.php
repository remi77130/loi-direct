<?php
// auth.php: contrôle d’accès et redirections.
/*Fonctions:
is_logged_in():
Vérifie la présence de $_SESSION['user_id'] et $_SESSION['pseudo'].
Si ces deux infos sont là, on considère l’utilisateur connecté.
require_login():
Si non connecté:
Redirige vers login.php avec un paramètre next contenant l’URL demandée.
Permet de renvoyer l’utilisateur sur la bonne page après connexion.
Conclusion: à inclure sur toutes les pages qui nécessitent une session utilisateur active.
*/

declare(strict_types=1);

function is_logged_in(): bool {
    return isset($_SESSION['user_id'], $_SESSION['pseudo']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: auth_page.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
        exit;
    }
}
