<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/feed.php');
    exit;
}

/*
Le fichier confirme que :

la sécurité backend est bien en place

le contrôle de solde existe déjà côté serveur

les messages flash sont déjà gérés

le projectUrl de retour est cohérent

la transaction SQL est bien pensée pour éviter les incohérences wallet/unlock/créateur ²

/* =========================================================
 * CSRF
 * ========================================================= */
$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
    $_SESSION['flash_errors'] = ['Session expirée. Recharge la page.'];
    header('Location: ' . APP_BASE . '/feed.php');
    exit;
}

/* =========================================================
 * Entrées
 * ========================================================= */
$projectId = (int)($_POST['project_id'] ?? 0);
$currentUserId = (int)($_SESSION['user_id'] ?? 0);

if ($projectId <= 0 || $currentUserId <= 0) {
    $_SESSION['flash_errors'] = ['Requête invalide.'];
    header('Location: ' . APP_BASE . '/feed.php');
    exit;
}

/* =========================================================
 * 1) Charger le projet
 * ========================================================= */
$sql = 'SELECT id, title, is_paid, unlock_points_price, creator_user_id
        FROM law_projects
        WHERE id = ? AND status = "published"
        LIMIT 1';

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $projectId);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    $_SESSION['flash_errors'] = ['Projet introuvable.'];
    header('Location: ' . APP_BASE . '/feed.php');
    exit;
}

$isPaid = (int)($project['is_paid'] ?? 0);
$unlockPointsPrice = (int)($project['unlock_points_price'] ?? 0);
$creatorUserId = (int)($project['creator_user_id'] ?? 0);
$projectUrl = APP_BASE . '/p/' . (int)$projectId . '-' . slugify($project['title']);

/* =========================================================
 * 2) Vérifications métier
 * ========================================================= */
if ($isPaid !== 1) {
    $_SESSION['flash_errors'] = ['Ce contenu est gratuit.'];
    header('Location: ' . $projectUrl);
    exit;
}

if ($unlockPointsPrice <= 0) {
    $_SESSION['flash_errors'] = ['Prix de déverrouillage invalide.'];
    header('Location: ' . $projectUrl);
    exit;
}

if ($creatorUserId <= 0) {
    $_SESSION['flash_errors'] = ['Créateur du contenu introuvable.'];
    header('Location: ' . $projectUrl);
    exit;
}

if ($creatorUserId === $currentUserId) {
    $_SESSION['flash_success'] = 'Tu es le créateur de ce contenu.';
    header('Location: ' . $projectUrl);
    exit;
}

/* =========================================================
 * 3) Déjà débloqué ?
 * ========================================================= */
$check = $mysqli->prepare('SELECT 1 FROM project_unlocks WHERE project_id = ? AND user_id = ? LIMIT 1');
$check->bind_param('ii', $projectId, $currentUserId);
$check->execute();
$check->store_result();
$alreadyUnlocked = $check->num_rows > 0;
$check->close();

if ($alreadyUnlocked) {
    $_SESSION['flash_success'] = 'Contenu déjà déverrouillé.';
    header('Location: ' . $projectUrl);
    exit;
}

/* =========================================================
 * 4) Calculs financiers
 * ========================================================= */
$grossAmountEur = round($unlockPointsPrice * 0.05, 2);
$creatorAmountEur = round($grossAmountEur * 0.70, 2);
$platformAmountEur = round($grossAmountEur * 0.30, 2);

/* =========================================================
 * 5) Transaction SQL
 * ========================================================= */
$mysqli->begin_transaction();

try {
    /* -----------------------------------------------------
     * 5.1) Verrouiller le wallet points utilisateur
     * --------------------------------------------------- */
    $walletStmt = $mysqli->prepare(
        'SELECT balance_points
         FROM user_points_wallet
         WHERE user_id = ?
         FOR UPDATE'
    );
    $walletStmt->bind_param('i', $currentUserId);
    $walletStmt->execute();
    $walletStmt->bind_result($currentBalance);
    $walletExists = $walletStmt->fetch();
    $walletStmt->close();

    if (!$walletExists) {
        throw new RuntimeException('Wallet points introuvable.');
    }

    $currentBalance = (int)$currentBalance;

    if ($currentBalance < $unlockPointsPrice) {
        throw new RuntimeException('Solde de points insuffisant.');
    }

    $newBalance = $currentBalance - $unlockPointsPrice;

    /* -----------------------------------------------------
     * 5.2) Débiter le wallet user
     * --------------------------------------------------- */
    $updWallet = $mysqli->prepare(
        'UPDATE user_points_wallet
         SET balance_points = ?, updated_at = NOW()
         WHERE user_id = ?'
    );
    $updWallet->bind_param('ii', $newBalance, $currentUserId);
    $updWallet->execute();
    $updWallet->close();

    /* -----------------------------------------------------
     * 5.3) Historique points user
     * ATTENTION :
     * ta table n’a pas related_project_id
     * on utilise related_room_id pour stocker l’id du projet
     * même si le nom est moche
     * --------------------------------------------------- */
    $userTxType = 'project_unlock';
    $userTxNote = 'Déverrouillage projet #' . $projectId;
    $pointsDelta = -$unlockPointsPrice;

    $insUserTx = $mysqli->prepare(
        'INSERT INTO user_points_transactions
         (user_id, type, points_delta, balance_after, amount_eur, related_room_id, note, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $insUserTx->bind_param(
        'isiidis',
        $currentUserId,
        $userTxType,
        $pointsDelta,
        $newBalance,
        $grossAmountEur,
        $projectId,
        $userTxNote
    );
    $insUserTx->execute();
    $insUserTx->close();

    /* -----------------------------------------------------
     * 5.4) Créer l’unlock
     * --------------------------------------------------- */
    $insUnlock = $mysqli->prepare(
        'INSERT INTO project_unlocks
         (project_id, user_id, points_paid, creator_amount_eur, platform_amount_eur, created_at)
         VALUES (?, ?, ?, ?, ?, NOW())'
    );
    $insUnlock->bind_param(
        'iiidd',
        $projectId,
        $currentUserId,
        $unlockPointsPrice,
        $creatorAmountEur,
        $platformAmountEur
    );
    $insUnlock->execute();
    $unlockId = (int)$insUnlock->insert_id;
    $insUnlock->close();

    /* -----------------------------------------------------
     * 5.5) Créer le wallet créateur si absent
     * --------------------------------------------------- */
    $createWalletIfMissing = $mysqli->prepare(
        'INSERT IGNORE INTO creator_wallets
         (user_id, balance_eur, pending_eur, lifetime_earned_eur, lifetime_paid_eur, updated_at)
         VALUES (?, 0.00, 0.00, 0.00, 0.00, NOW())'
    );
    $createWalletIfMissing->bind_param('i', $creatorUserId);
    $createWalletIfMissing->execute();
    $createWalletIfMissing->close();

    /* -----------------------------------------------------
     * 5.6) Verrouiller le wallet créateur
     * --------------------------------------------------- */
    $creatorWalletStmt = $mysqli->prepare(
        'SELECT balance_eur, lifetime_earned_eur
         FROM creator_wallets
         WHERE user_id = ?
         FOR UPDATE'
    );
    $creatorWalletStmt->bind_param('i', $creatorUserId);
    $creatorWalletStmt->execute();
    $creatorWalletStmt->bind_result($creatorBalance, $creatorLifetimeEarned);
    $creatorWalletStmt->fetch();
    $creatorWalletStmt->close();

    $creatorBalance = (float)$creatorBalance;
    $creatorLifetimeEarned = (float)$creatorLifetimeEarned;

    $newCreatorBalance = round($creatorBalance + $creatorAmountEur, 2);
    $newCreatorLifetimeEarned = round($creatorLifetimeEarned + $creatorAmountEur, 2);

    /* -----------------------------------------------------
     * 5.7) Créditer le wallet créateur
     * --------------------------------------------------- */
    $updCreatorWallet = $mysqli->prepare(
        'UPDATE creator_wallets
         SET balance_eur = ?, lifetime_earned_eur = ?, updated_at = NOW()
         WHERE user_id = ?'
    );
    $updCreatorWallet->bind_param(
        'ddi',
        $newCreatorBalance,
        $newCreatorLifetimeEarned,
        $creatorUserId
    );
    $updCreatorWallet->execute();
    $updCreatorWallet->close();

    /* -----------------------------------------------------
     * 5.8) Historique wallet créateur
     * ATTENTION :
     * ta table n’a pas related_project_id
     * on utilise related_room_id pour stocker l’id du projet
     * et related_membership_id pour stocker l’id du unlock
     * --------------------------------------------------- */
    $creatorTxType = 'project_unlock_sale';
    $creatorTxNote = 'Revenu déverrouillage projet #' . $projectId;

    $insCreatorTx = $mysqli->prepare(
        'INSERT INTO creator_wallet_transactions
         (creator_user_id, type, amount_eur, balance_after, related_room_id, related_membership_id, note, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $insCreatorTx->bind_param(
        'isddiis',
        $creatorUserId,
        $creatorTxType,
        $creatorAmountEur,
        $newCreatorBalance,
        $projectId,
        $unlockId,
        $creatorTxNote
    );
    $insCreatorTx->execute();
    $insCreatorTx->close();

    $mysqli->commit();

    $_SESSION['flash_success'] = 'Contenu déverrouillé avec succès.';

} catch (Throwable $e) {
    $mysqli->rollback();

    if ($e->getMessage() === 'Solde de points insuffisant.') {
        $_SESSION['flash_errors'] = ['Tu n’as pas assez de points pour déverrouiller ce contenu.'];
    } else {
        $_SESSION['flash_errors'] = ['Erreur lors du déverrouillage : ' . $e->getMessage()];
    }
}

header('Location: ' . $projectUrl);
exit;