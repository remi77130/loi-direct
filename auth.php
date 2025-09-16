<?php
// auth.php
declare(strict_types=1);

function is_logged_in(): bool {
    return isset($_SESSION['user_id'], $_SESSION['pseudo']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
        exit;
    }
}
