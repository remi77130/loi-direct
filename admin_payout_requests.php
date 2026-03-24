<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/admin_auth.php';

// 🔒 Protection admin
require_admin();

// Récupération des demandes pending
$sql = "
    SELECT 
        pr.id,
        pr.creator_user_id,
        pr.amount_eur,
        pr.paypal_email,
        pr.status,
        pr.requested_at
    FROM payout_requests pr
    WHERE pr.status = 'pending'
    ORDER BY pr.requested_at ASC
";

$result = $mysqli->query($sql);

$payouts = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $payouts[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Payouts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family:Arial;background:#0f172a;color:#fff;padding:40px;">

    <div style="max-width:1000px;margin:0 auto;">

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h1 style="margin:0;">Demandes de retrait</h1>
            <a href="<?= APP_BASE ?>/admin_logout.php" style="color:#f87171;">Se déconnecter</a>
        </div>

        <?php if (empty($payouts)): ?>
            <div style="padding:20px;border:1px solid #334155;border-radius:12px;background:#111827;">
                Aucune demande de retrait en attente.
            </div>
        <?php else: ?>

            <table style="width:100%;border-collapse:collapse;background:#111827;border-radius:12px;overflow:hidden;">



                <thead style="background:#1e293b;"> <!-- En-tête du tableau avec un fond différent pour le différencier des lignes de données -->
                    <tr>
                        <th style="padding:12px;text-align:left;">Action</th>
                        <th style="padding:12px;text-align:left;">ID</th>
                        <th style="padding:12px;text-align:left;">User</th>
                        <th style="padding:12px;text-align:left;">Montant</th>
                        <th style="padding:12px;text-align:left;">PayPal</th>
                        <th style="padding:12px;text-align:left;">Date</th>
                    </tr>
                </thead>



            <tbody> <!-- Corps du tableau avec les données des demandes de retrait. Chaque ligne représente une demande, avec un bouton "Marquer payé -->
    <?php foreach ($payouts as $p): ?>
        <tr style="border-top:1px solid #334155;">
            <td style="padding:12px;">
                <form method="post" action="<?= APP_BASE ?>/admin_mark_payout_paid.php">
                    <input type="hidden" name="payout_id" value="<?= (int)$p['id'] ?>">
                    <button type="submit" style="
                        padding:8px 12px;
                        border:none;
                        border-radius:8px;
                        background:#22c55e;
                        color:#052e16;
                        font-weight:600;
                        cursor:pointer;
                    ">
                        Marquer payé
                    </button>
                </form>
            </td>
            <td style="padding:12px;">#<?= (int)$p['id'] ?></td>
            <td style="padding:12px;">User <?= (int)$p['creator_user_id'] ?></td>
            <td style="padding:12px;font-weight:700;">
                <?= number_format((float)$p['amount_eur'], 2, ',', ' ') ?> €
            </td>
            <td style="padding:12px;">
                <?= htmlspecialchars($p['paypal_email'], ENT_QUOTES) ?>
            </td>
            <td style="padding:12px;">
                <?= htmlspecialchars($p['requested_at'], ENT_QUOTES) ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>



            </table>

        <?php endif; ?>

    </div>

</body>
</html>