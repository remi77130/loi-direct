<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';

unset($_SESSION['is_admin'], $_SESSION['admin_login']);

header('Location: ' . APP_BASE . '/admin_login.php');
exit; // Simple page de logout admin qui détruit la session et redirige vers la page de login pour admin. Comme il n’y a qu’un seul admin dans ce projet, pas besoin de faire du logout plus complexe que ça (pas de gestion de rôles, ni de multi-admins).