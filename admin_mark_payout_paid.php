<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/admin_auth.php';

require_admin();


// Ce fichier reçoit la requête POST quand l’admin clique sur "Marquer comme payé" dans la liste des demandes de retrait en attente (admin_payout_requests.php).   
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Méthode non autorisée');
}

$payoutId = (int)($_POST['payout_id'] ?? 0);

if ($payoutId <= 0) {
    header('Location: ' . APP_BASE . '/admin_payout_requests.php');
    exit;
}

try {
    $mysqli->begin_transaction();

    // 🔒 Lock payout
    $stmt = $mysqli->prepare("
        SELECT *
        FROM payout_requests
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->bind_param('i', $payoutId);
    $stmt->execute();
    $result = $stmt->get_result();
    $payout = $result->fetch_assoc();
    $stmt->close();

    if (!$payout || $payout['status'] !== 'pending') {
        throw new Exception('Payout invalide ou déjà traité');
    }

    $creatorId = (int)$payout['creator_user_id'];
    $amount = (float)$payout['amount_eur'];

    // 🔒 Lock wallet
    $stmt = $mysqli->prepare("
        SELECT balance_eur, pending_payout_eur, lifetime_paid_out_eur
        FROM creator_wallets
        WHERE user_id = ?
        FOR UPDATE
    ");
    $stmt->bind_param('i', $creatorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $wallet = $result->fetch_assoc();
    $stmt->close();

    if (!$wallet) {
        throw new Exception('Wallet introuvable');
    }

    // Mise à jour wallet
    $newPending = (float)$wallet['pending_payout_eur'] - $amount;
    $newPaidOut = (float)$wallet['lifetime_paid_out_eur'] + $amount;

    if ($newPending < 0) {
        throw new Exception('Incohérence pending payout');
    }

    $stmt = $mysqli->prepare(" 
            UPDATE creator_wallets
            SET pending_payout_eur = ?, lifetime_paid_out_eur = ?, updated_at = NOW()
            WHERE user_id = ?
    ");
    $stmt->bind_param('ddi', $newPending, $newPaidOut, $creatorId);
    $stmt->execute();
    $stmt->close();

    // Update payout // (dans un projet plus complet, on stockerait aussi la date de paiement, et potentiellement d’autres infos comme l’ID de la transaction PayPal si on utilise l’API PayPal pour faire les paiements directement depuis la plateforme)
    $stmt = $mysqli->prepare("
        UPDATE payout_requests
        SET status = 'paid',
        reviewed_at = NOW(),
        paid_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param('i', $payoutId);
    $stmt->execute();
    $stmt->close();

    // Transaction log
    $note = 'Payout payé #' . $payoutId;

    $stmt = $mysqli->prepare("
        INSERT INTO creator_wallet_transactions (
            creator_user_id,
            amount_eur,
            balance_after,
            type,
            related_membership_id,
            note,
            created_at
        ) VALUES (?, 0, ?, 'payout_paid', ?, ?, NOW())
    ");

    $balanceAfter = (float)$wallet['balance_eur']; // inchangé
    $stmt->bind_param('idis', $creatorId, $balanceAfter, $payoutId, $note);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();

} catch (Throwable $e) {
    $mysqli->rollback();
    error_log('[admin_mark_payout_paid] ' . $e->getMessage());
}

header('Location: ' . APP_BASE . '/admin_payout_requests.php');
exit;