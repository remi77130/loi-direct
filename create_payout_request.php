<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

 // Important : exceptions mysqli
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // pour que les erreurs SQL lancent des exceptions et soient catchables dans le try/catch global

    $mysqli->begin_transaction();

require_login();


/*
ce fichier fait  bien tout ce qu’on a défini :

refuse hors POST
vérifie utilisateur connecté
valide amount_eur
valide paypal_email
lock creator_wallets
vérifie le solde
vérifie qu’il n’existe pas déjà une demande pending
déplace l’argent de balance_eur vers pending_payout_eur
crée la ligne dans payout_requests
écrit la ligne payout_request_hold dans creator_wallet_transactions
commit ou rollback
Je n’ai pas mis de CSRF token ici pour garder l’étape simple.
En prod, il faudra l’ajouter. Mais pour avancer doucement, le cœur comptable est là.*/


// --------------------------------------------------
// Config MVP
// --------------------------------------------------
const MIN_PAYOUT_EUR = 20.00;

// --------------------------------------------------
// Helpers
// --------------------------------------------------
function redirect_with_message(string $path, string $type, string $message): never
{
    $location = APP_BASE . $path;
    $separator = (strpos($location, '?') !== false) ? '&' : '?';

    header('Location: ' . $location . $separator . 'msg_type=' . urlencode($type) . '&msg=' . urlencode($message));
    exit;
}

function normalize_amount(string $rawAmount): float
{
    $normalized = str_replace([' ', ','], ['', '.'], trim($rawAmount));

    if ($normalized === '' || !is_numeric($normalized)) {
        return -1;
    }

    return round((float) $normalized, 2);
}

// --------------------------------------------------
// Sécurité HTTP
// --------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée.');
}

$creatorUserId = (int) ($_SESSION['user_id'] ?? 0);

if ($creatorUserId <= 0) {
    redirect_with_message('/auth_page.php', 'error', 'Vous devez être connecté.');
}

// --------------------------------------------------
// Lecture / validation entrées
// --------------------------------------------------
$amountRaw   = (string) ($_POST['amount_eur'] ?? '');
$paypalEmail = trim((string) ($_POST['paypal_email'] ?? ''));

$amountEur = normalize_amount($amountRaw);

if ($amountEur <= 0) {
    redirect_with_message('/creator_dashboard.php', 'error', 'Montant invalide.');
}

if ($amountEur < MIN_PAYOUT_EUR) {
    redirect_with_message(
        '/creator_dashboard.php',
        'error',
        'Le montant minimum de retrait est de ' . number_format(MIN_PAYOUT_EUR, 2, ',', ' ') . ' €.'
    );
}

if ($paypalEmail === '' || !filter_var($paypalEmail, FILTER_VALIDATE_EMAIL)) {
    redirect_with_message('/creator_dashboard.php', 'error', 'Adresse PayPal invalide.');
}

try {
   
    // --------------------------------------------------
    // 1. Lock wallet créateur
    // --------------------------------------------------
    $sql = "
        SELECT user_id, balance_eur, pending_payout_eur
        FROM creator_wallets
        WHERE user_id = ?
        FOR UPDATE
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $creatorUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $wallet = $result->fetch_assoc();
    $stmt->close();

    if (!$wallet) {
        throw new RuntimeException('Wallet créateur introuvable.');
    }

    $currentBalance = round((float) $wallet['balance_eur'], 2);
    $currentPendingPayout = round((float) $wallet['pending_payout_eur'], 2);

    // --------------------------------------------------
    // 2. Vérifier solde suffisant
    // --------------------------------------------------
    if ($currentBalance < $amountEur) {
        $mysqli->rollback();
        redirect_with_message('/creator_dashboard.php', 'error', 'Solde disponible insuffisant.');
    }

    // --------------------------------------------------
    // 3. Vérifier absence de payout pending
    // --------------------------------------------------
    $sql = "
        SELECT id
        FROM payout_requests
        WHERE creator_user_id = ?
          AND status = 'pending'
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $creatorUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingPending = $result->fetch_assoc();
    $stmt->close();

    if ($existingPending) {
        $mysqli->rollback();
        redirect_with_message('/creator_dashboard.php', 'error', 'Une demande de retrait est déjà en attente.');
    }

    // --------------------------------------------------
    // 4. Calcul nouveaux soldes
    // --------------------------------------------------
    $newBalance = round($currentBalance - $amountEur, 2);
    $newPendingPayout = round($currentPendingPayout + $amountEur, 2);

    if ($newBalance < 0) {
        throw new RuntimeException('Le nouveau solde calculé est invalide.');
    }

    // --------------------------------------------------
    // 5. Mise à jour wallet
    // --------------------------------------------------
    $sql = "
        UPDATE creator_wallets
        SET balance_eur = ?,
            pending_payout_eur = ?,
            updated_at = NOW()
        WHERE user_id = ?
    ";
    $stmt = $mysqli->prepare($sql);
    $balanceFormatted = number_format($newBalance, 2, '.', '');
    $pendingFormatted = number_format($newPendingPayout, 2, '.', '');

$stmt->bind_param('ddi', $newBalance, $newPendingPayout, $creatorUserId);    
$stmt->execute();
    $stmt->close();

    // --------------------------------------------------
    // 6. Création payout_requests
    // --------------------------------------------------
    $createdIp = $_SERVER['REMOTE_ADDR'] ?? null;
    $createdUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $sql = "
        INSERT INTO payout_requests (
            creator_user_id,
            amount_eur,
            paypal_email,
            status,
            created_ip,
            created_user_agent
        ) VALUES (?, ?, ?, 'pending', ?, ?)
    ";
    $stmt = $mysqli->prepare($sql);
    $amountFormatted = number_format($amountEur, 2, '.', '');
   $stmt->bind_param(
    'idsss',
    $creatorUserId,
    $amountEur,
    $paypalEmail,
    $createdIp,
    $createdUserAgent
);
    $stmt->execute();
    $payoutRequestId = (int) $mysqli->insert_id;
    $stmt->close();

    if ($payoutRequestId <= 0) {
        throw new RuntimeException('Impossible de créer la demande de retrait.');
    }

    // --------------------------------------------------
    // 7. Transaction wallet créateur
    // --------------------------------------------------
$sql = "
    INSERT INTO creator_wallet_transactions (
        creator_user_id,
        amount_eur,
        balance_after,
        type,
        related_room_id,
        related_membership_id,
        note,
        created_at
    ) VALUES (?, ?, ?, 'payout_request_hold', NULL, ?, ?, NOW())
";
$stmt = $mysqli->prepare($sql);
$transactionAmount = -$amountEur;
$balanceAfterValue = $newBalance;
$note = 'Demande de retrait payout #' . $payoutRequestId;

$stmt->bind_param(
    'iddis',
    $creatorUserId,
    $transactionAmount,
    $balanceAfterValue,
    $payoutRequestId,
    $note
);
$stmt->execute();
$stmt->close();

    $mysqli->commit();

    redirect_with_message('/creator_dashboard.php', 'success', 'Demande de retrait créée avec succès.');
} catch (Throwable $e) {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        try {
            $mysqli->rollback();
        } catch (Throwable $rollbackError) {
            // on ignore l'échec du rollback secondaire
        }
    }

    error_log('[create_payout_request] ' . $e->getMessage());

    redirect_with_message('/creator_dashboard.php', 'error', 'Une erreur est survenue lors de la demande de retrait.');
}