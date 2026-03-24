<?php
// Ce fichier va contenir juste les helpers admin.
declare(strict_types=1);

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true; // On stocke juste un booléen dans la session pour savoir si l'admin est connecté ou pas. Pas de gestion de rôles, ni de multi-admins dans ce projet.
}

function require_admin(): void // Bloque l’accès à la page si l’admin n’est pas connecté, et redirige vers la page de login admin.
{
    if (!is_admin_logged_in()) {
        header('Location: ' . APP_BASE . '/admin_login.php');
        exit;
    }
}