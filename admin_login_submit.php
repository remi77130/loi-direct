<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

$login = trim((string)($_POST['login'] ?? '')); 
$password = (string)($_POST['password'] ?? ''); 

if ($login === '' || $password === '') {
    header('Location: ' . APP_BASE . '/admin_login.php?error=' . urlencode('Identifiants requis.'));
    exit;
}

if ($login !== ADMIN_LOGIN || !password_verify($password, ADMIN_PASSWORD_HASH)) {
    header('Location: ' . APP_BASE . '/admin_login.php?error=' . urlencode('Identifiants invalides.'));
    exit;
}

$_SESSION['is_admin'] = true;
$_SESSION['admin_login'] = ADMIN_LOGIN;

header('Location: ' . APP_BASE . '/admin_payout_requests.php');
exit;