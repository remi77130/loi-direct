<?php
declare(strict_types=1);

$password = 'Champagne-77'; // Mot de passe admin en clair (à remplacer par le mot de passe que vous voulez pour l’admin, et à garder secret !)
echo password_hash($password, PASSWORD_DEFAULT);