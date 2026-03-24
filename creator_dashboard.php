<?php

declare(strict_types=1);

/**
 * Dashboard créateur - MVP
 *
 * Objectif :
 * - afficher les gains internes du créateur connecté
 * - accessible uniquement à l’utilisateur connecté
 * - chacun ne voit que son propre wallet créateur
 *
 * Données affichées sur la  Table : creator_wallets
 * - solde disponible (balance_eur)
 * - total cumulé gagné (lifetime_earned_eur)
 */

session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();

$currentUserId = (int)($_SESSION['user_id'] ?? 0);

if ($currentUserId <= 0) {
    http_response_code(403);
    exit('Accès refusé');
}

/**
 * Valeurs par défaut :
 * si aucun wallet créateur n’existe encore pour ce user,
 * on considère simplement qu’il n’a encore rien gagné.
 */
$creatorBalanceEur = 0.00; // Solde actuel par defaut disponible pour retrait
$creatorLifetimeEarnedEur = 0.00; // Total cumulé gagné par defaut depuis la création du compte créateur
$latestSales = []; // Liste des ventes récentes (vide par défaut)
$totalSalesCount = 0; // Nombre total de ventes payantes du créateur
$topProjects = []; // Top projets classés par gains décroissants 
$creatorPendingPayoutEur = 0.00; // Montant actuellement bloqué en attente de versement
$creatorLifetimePaidOutEur = 0.00; // Total déjà payé au créateur

/**
 * Lecture du wallet créateur du user connecté.
 * On ne lit que sa propre ligne.
 */
$stmt = $mysqli->prepare( // TODO : on pourrait aussi faire une jointure pour récupérer en même temps le paypal_email et éviter une requête supplémentaire plus tard si on veut l’afficher sur le dashboard créateur
    'SELECT balance_eur, pending_payout_eur, lifetime_earned_eur, lifetime_paid_out_eur
FROM creator_wallets
WHERE user_id = ?
LIMIT 1'
);

if (!$stmt) {
    http_response_code(500);
    exit('Erreur SQL (prepare creator_wallets)');
}

$stmt->bind_param('i', $currentUserId);
$stmt->execute();
$res = $stmt->get_result();
$wallet = $res ? $res->fetch_assoc() : null;
$stmt->close();

if ($wallet) { // si le wallet existe, on affiche les données réelles, sinon on laisse les valeurs par défaut à 0
    $creatorBalanceEur = (float)($wallet['balance_eur'] ?? 0);
    $creatorPendingPayoutEur = (float)($wallet['pending_payout_eur'] ?? 0);
    $creatorLifetimeEarnedEur = (float)($wallet['lifetime_earned_eur'] ?? 0);
    $creatorLifetimePaidOutEur = (float)($wallet['lifetime_paid_out_eur'] ?? 0); // cette valeur n’est pas affichée pour le moment sur le dashboard créateur, mais on la récupère quand même car elle peut être utile pour calculer d’autres métriques intéressantes à afficher plus tard (ex: total cumulé gagné - total déjà payé = montant restant à payer au créateur)
}

/**
 * Comptage du nombre total de ventes payantes du créateur.
 *
 * On compte uniquement les transactions de type project_unlock_sale,
 * car elles correspondent aux achats réels de contenus payants.
 */
$stmtCountSales = $mysqli->prepare(
    'SELECT COUNT(*) AS total_sales
     FROM creator_wallet_transactions
     WHERE creator_user_id = ?
       AND type = ?'
);

if (!$stmtCountSales) {
    http_response_code(500);
    exit('Erreur SQL (prepare count creator_wallet_transactions)');
}

$saleType = 'project_unlock_sale'; // Type de transaction correspondant à une vente payante liée à un déverrouillage de projet

$stmtCountSales->bind_param('is', $currentUserId, $saleType);
$stmtCountSales->execute();
$resCountSales = $stmtCountSales->get_result();
$countRow = $resCountSales ? $resCountSales->fetch_assoc() : null;
$stmtCountSales->close();

$totalSalesCount = (int)($countRow['total_sales'] ?? 0);





/**
 * Top projets les plus rentables
 *
 * On agrège les transactions créateur par projet :
 * - COUNT(*) = nombre de ventes
 * - SUM(amount_eur) = total gagné
 *
 * related_room_id contient l'id du projet.
 */
$stmtTopProjects = $mysqli->prepare( // TODO : on pourrait aussi faire un classement par nombre de ventes, ou un classement combiné (ex: 50% gains + 50% ventes) pour faire ressortir les projets les plus populaires et pas seulement les plus chers
    'SELECT
        t.related_room_id AS project_id,
        p.title,
        COUNT(*) AS sales_count,
        COALESCE(SUM(t.amount_eur), 0) AS total_earned
     FROM creator_wallet_transactions t
     LEFT JOIN law_projects p ON p.id = t.related_room_id
     WHERE t.creator_user_id = ?
       AND t.type = ?
     GROUP BY t.related_room_id, p.title
     ORDER BY total_earned DESC, sales_count DESC, t.related_room_id DESC
     LIMIT 5'
);

if (!$stmtTopProjects) {
    http_response_code(500);
    exit('Erreur SQL (prepare top creator projects)');
}

$stmtTopProjects->bind_param('is', $currentUserId, $saleType);
$stmtTopProjects->execute();
$resTopProjects = $stmtTopProjects->get_result();

while ($row = $resTopProjects->fetch_assoc()) {
    $topProjects[] = $row;
}

$stmtTopProjects->close();









/**
 * Chargement des dernières ventes créateur
 *
 * On lit l’historique des gains liés aux déverrouillages de projets.
 * related_room_id = project_id
 * related_membership_id = id du déverrouillage
 */
$stmtSales = $mysqli->prepare(
    'SELECT
    t.id,
    t.amount_eur,
    t.related_room_id,
    t.related_membership_id,
    t.created_at,
    p.title
FROM creator_wallet_transactions t
LEFT JOIN law_projects p ON p.id = t.related_room_id
WHERE t.creator_user_id = ?
  AND t.type = ?
ORDER BY t.id DESC
LIMIT 10'
);

if (!$stmtSales) {
    http_response_code(500);
    exit('Erreur SQL (prepare creator_wallet_transactions)');
}


$stmtSales->bind_param('is', $currentUserId, $saleType);
$stmtSales->execute();
$resSales = $stmtSales->get_result();

while ($row = $resSales->fetch_assoc()) {
    $latestSales[] = $row;
}

$stmtSales->close();









/**
 * Petit helper d’affichage pour uniformiser les montants en euros.
 */
function format_eur(float $amount): string
{
    return number_format($amount, 2, ',', ' ') . ' €';
}


/**
 * Formatage simple d’une date SQL en affichage FR.
 */
function format_datetime_fr(?string $datetime): string
{
    if (!$datetime) {
        return 'Date inconnue';
    }

    $ts = strtotime($datetime);
    if ($ts === false) {
        return 'Date inconnue';
    }

    return date('d/m/Y H:i', $ts);
}

$pageTitle = 'Mes gains créateur'; // Titre de la page pour le header
$msgType = trim((string)($_GET['msg_type'] ?? '')); // Type de message (ex: success, error) pour l’affichage d’une notification après une action (ex: demande de retrait)
$msg = trim((string)($_GET['msg'] ?? '')); // Contenu du message à afficher dans une notification après une action (ex: demande de retrait)
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES); ?> - Createur </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>



<main class="container" style="max-width:960px;margin:24px auto;padding:0 16px;">

    <div style="margin-bottom:18px;">
        <a href="<?= APP_BASE ?>/profile.php?id=<?php echo (int)$currentUserId; ?>" style="text-decoration:none;">
            ← Retour à mon profil
        </a>
    </div>

    <section style="border:1px solid #334155;border-radius:16px;padding:20px;background:#0f172a;">
        <h1 style="margin:0 0 16px 0;">Mes gains créateur</h1>

        <p style="margin:0 0 20px 0;color:#94a3b8;">
            Cet espace affiche vos revenus internes générés par les déverrouillages de contenus payants.
        </p>



        <?php if ($msg !== ''): ?>
    <div style="
        margin:0 0 20px 0;
        padding:14px 16px;
        border-radius:12px;
        border:1px solid <?php echo $msgType === 'success' ? '#166534' : '#7f1d1d'; ?>;
        background:<?php echo $msgType === 'success' ? '#052e16' : '#450a0a'; ?>;
        color:<?php echo $msgType === 'success' ? '#bbf7d0' : '#fecaca'; ?>;
    ">
        <?php echo htmlspecialchars($msg, ENT_QUOTES); ?>
    </div>
<?php endif; ?>



        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">

            <!-- Carte : solde disponible -->
            <div style="border:1px solid #334155;border-radius:14px;padding:18px;background:#111827;">
                <div style="font-size:13px;color:#94a3b8;margin-bottom:8px;">
                    Solde disponible
                </div>
                <div style="font-size:28px;font-weight:700;">
                    <?php echo htmlspecialchars(format_eur($creatorBalanceEur), ENT_QUOTES); ?>
                </div>
                <div style="margin-top:8px;font-size:13px;color:#94a3b8;">
                    Montant actuellement disponible dans votre wallet créateur.
                </div>
            </div>

            <!-- Carte : total cumulé gagné -->
            <div style="border:1px solid #334155;border-radius:14px;padding:18px;background:#111827;">
                <div style="font-size:13px;color:#94a3b8;margin-bottom:8px;">
                    Total cumulé gagné
                </div>
                <div style="font-size:28px;font-weight:700;">
                    <?php echo htmlspecialchars(format_eur($creatorLifetimeEarnedEur), ENT_QUOTES); ?>
                </div>
                <div style="margin-top:8px;font-size:13px;color:#94a3b8;">
                    Total historique de vos revenus créateur.
                </div>
            </div>

            <!-- Carte : nombre total de ventes -->
            <div style="border:1px solid #334155;border-radius:14px;padding:18px;background:#111827;">
                <div style="font-size:13px;color:#94a3b8;margin-bottom:8px;">
                    Nombre de ventes
                </div>
                <div style="font-size:28px;font-weight:700;">
                    <?php echo (int)$totalSalesCount; ?>
                </div>
                <div style="margin-top:8px;font-size:13px;color:#94a3b8;">
                    Nombre total de contenus payants déverrouillés par vos acheteurs.
                </div>
            </div>

                <!-- Carte : montant en attente de versement -->
<div style="border:1px solid #334155;border-radius:14px;padding:18px;background:#111827;">
    <div style="font-size:13px;color:#94a3b8;margin-bottom:8px;">
        En attente de versement
    </div>
    <div style="font-size:28px;font-weight:700;">
        <?php echo htmlspecialchars(format_eur($creatorPendingPayoutEur), ENT_QUOTES); ?>
    </div>
    <div style="margin-top:8px;font-size:13px;color:#94a3b8;">
        Montant actuellement bloqué dans une demande de retrait en attente.
    </div>
</div>

            <!-- Carte : Tu peux aussi ajouter une 5e carte plus tard pour lifetime_paid_out_eur, mais pour l’instant ce n’est pas indispensable. -->


        </div>
        <!-- Fin des cartes de synthèse en haut du dashboard créateur, formulaire de demande payout-->
        <section style="margin-top:22px;">
    <h2 style="margin:0 0 12px 0;font-size:20px;">
        Demander un retrait
    </h2>

    <div style="padding:18px;border:1px solid #334155;border-radius:12px;background:#0b1220;">
        <p style="margin:0 0 14px 0;color:#94a3b8;">
<?php if ($creatorBalanceEur >= 20 && $creatorPendingPayoutEur <= 0): ?>
    <p style="margin:0 0 14px 0;color:#94a3b8;">
        Vous pouvez demander un retrait manuel via PayPal à partir de 20,00 €.
    </p>
<?php else: ?>
    <p style="margin:0 0 14px 0;color:#94a3b8;">
        Le retrait sera disponible dès que votre solde atteindra 20,00 € et qu’aucune autre demande ne sera en attente.
    </p>
<?php endif; ?>        </p>

        <?php if ($creatorPendingPayoutEur > 0): ?>
            <div style="margin-bottom:14px;padding:12px;border:1px solid #854d0e;border-radius:10px;background:#451a03;color:#fde68a;">
                Une demande de retrait est déjà en attente de traitement. Vous ne pouvez pas en créer une nouvelle pour le moment.
            </div>
        <?php endif; ?>

        <form method="post" action="<?= APP_BASE ?>/create_payout_request.php" style="display:grid;gap:14px;max-width:520px;">
            <div>
                <label for="amount_eur" style="display:block;margin-bottom:6px;font-weight:600;">
                    Montant à retirer (€)
                </label>
                <input
                    type="number"
                    id="amount_eur"
                    name="amount_eur"
                    min="20"
                    step="0.01"
                    required
                    value="<?php echo $creatorBalanceEur >= 20 ? htmlspecialchars(number_format($creatorBalanceEur, 2, '.', ''), ENT_QUOTES) : ''; ?>"  
                    <?php echo ($creatorPendingPayoutEur > 0 || $creatorBalanceEur < 20) ? 'disabled' : ''; ?> 
                    style="width:100%;padding:12px;border-radius:10px;border:1px solid #334155;background:#111827;color:#ffffff;"
                > <!-- Plus tard, on pourra laisser vide par défaut pour éviter les clics trop rapides.-->
                <div style="margin-top:6px;font-size:13px;color:#94a3b8;">
                    Solde disponible : <?php echo htmlspecialchars(format_eur($creatorBalanceEur), ENT_QUOTES); ?> — minimum 20,00 €.
                </div>
            </div>

            <div>
                <label for="paypal_email" style="display:block;margin-bottom:6px;font-weight:600;">
                    Email PayPal
                </label>
                <input
                    type="email"
                    id="paypal_email"
                    name="paypal_email"
                    required
                    placeholder="exemple@paypal.com"
                    <?php echo ($creatorPendingPayoutEur > 0 || $creatorBalanceEur < 20) ? 'disabled' : ''; ?>
                    style="width:100%;padding:12px;border-radius:10px;border:1px solid #334155;background:#111827;color:#ffffff;"
                >
            </div>

            <div>
                <button
                    type="submit"
                    <?php echo ($creatorPendingPayoutEur > 0 || $creatorBalanceEur < 20) ? 'disabled' : ''; ?>
                    style="
                        padding:12px 16px;
                        border:none;
                        border-radius:10px;
                        font-weight:700;
                        cursor:<?php echo ($creatorPendingPayoutEur > 0 || $creatorBalanceEur < 20) ? 'not-allowed' : 'pointer'; ?>;
                        opacity:<?php echo ($creatorPendingPayoutEur > 0 || $creatorBalanceEur < 20) ? '0.6' : '1'; ?>;
                        background:#22c55e;
                        color:#052e16;
                    "
                >
                    Demander un retrait
                </button>
            </div>
        </form>

        <?php if ($creatorBalanceEur < 20): ?>
            <div style="margin-top:14px;font-size:13px;color:#94a3b8;">
                Votre solde est insuffisant pour demander un retrait pour le moment.
            </div>
        <?php endif; ?>
    </div>
</section>

        <?php if ($creatorBalanceEur <= 0.0 && $creatorLifetimeEarnedEur <= 0.0): ?>
            <div style="margin-top:18px;padding:14px;border:1px solid #334155;border-radius:12px;background:#0b1220;color:#cbd5e1;">
                Vous n’avez encore aucun revenu créateur enregistré.
            </div>
        <?php endif; ?>

        <section style="margin-top:22px;">
            <h2 style="margin:0 0 12px 0;font-size:20px;">
                Top projets les plus rentables
            </h2>

            <?php if (empty($topProjects)): ?>
                <div style="padding:14px;border:1px solid #334155;border-radius:12px;background:#0b1220;color:#cbd5e1;">
                    Aucun projet rentable à afficher pour le moment.
                </div>
            <?php else: ?>
                <div style="border:1px solid #334155;border-radius:12px;overflow:hidden;">
                    <table style="width:100%;border-collapse:collapse;background:#111827;">
                        <thead>
                            <tr style="background:#0b1220;color:#94a3b8;text-align:left;">
                                <th style="padding:12px;border-bottom:1px solid #334155;">Projet</th>
                                <th style="padding:12px;border-bottom:1px solid #334155;">Ventes</th>
                                <th style="padding:12px;border-bottom:1px solid #334155;">Total gagné</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProjects as $projectStat): ?>
                                <tr style="border-bottom:1px solid #334155;">
                                    <td style="padding:12px;vertical-align:top;">
                                        <a href="<?= APP_BASE ?>/p/<?php echo (int)$projectStat['project_id']; ?>"
                                           style="color:#60a5fa;text-decoration:none;font-weight:600;">
                                            <?php echo htmlspecialchars(
                                                $projectStat['title'] ?? ('Projet #' . (int)$projectStat['project_id']),
                                                ENT_QUOTES
                                            ); ?>
                                        </a>
                                    </td>

                                    <td style="padding:12px;vertical-align:top;">
                                        <?php echo (int)($projectStat['sales_count'] ?? 0); ?>
                                    </td>

                                    <td style="padding:12px;vertical-align:top;font-weight:700;color:#22c55e;">
                                        <?php echo htmlspecialchars(
                                            format_eur((float)($projectStat['total_earned'] ?? 0)),
                                            ENT_QUOTES
                                        ); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section style="margin-top:22px;">
            <h2 style="margin:0 0 12px 0;font-size:20px;">
                Dernières ventes
            </h2>

            <?php if (empty($latestSales)): ?>
                <div style="padding:14px;border:1px solid #334155;border-radius:12px;background:#0b1220;color:#cbd5e1;">
                    Aucune vente enregistrée pour le moment.
                </div>
            <?php else: ?>
                <div style="border:1px solid #334155;border-radius:12px;overflow:hidden;">
                    <table style="width:100%;border-collapse:collapse;background:#111827;">
                        <thead>
                            <tr style="background:#0b1220;color:#94a3b8;text-align:left;">
                                <th style="padding:12px;border-bottom:1px solid #334155;">Date</th>
                                <th style="padding:12px;border-bottom:1px solid #334155;">Projet</th>
                                <th style="padding:12px;border-bottom:1px solid #334155;">Déverrouillage</th>
                                <th style="padding:12px;border-bottom:1px solid #334155;">Montant gagné</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latestSales as $sale): ?>
                                <tr style="border-bottom:1px solid #334155;">
                                    <td style="padding:12px;vertical-align:top;">
                                        <?php echo htmlspecialchars(format_datetime_fr($sale['created_at'] ?? null), ENT_QUOTES); ?>
                                    </td>

                                    <td style="padding:12px;vertical-align:top;">
                                        <a href="<?= APP_BASE ?>/p/<?php echo (int)$sale['related_room_id']; ?>"
                                           style="color:#60a5fa;text-decoration:none;font-weight:600;">
                                            <?php echo htmlspecialchars(
                                                $sale['title'] ?? ('Projet #' . (int)$sale['related_room_id']),
                                                ENT_QUOTES
                                            ); ?>
                                        </a>
                                    </td>

                                    <td style="padding:12px;vertical-align:top;">
                                        #<?php echo (int)($sale['related_membership_id'] ?? 0); ?>
                                    </td>

                                    <td style="padding:12px;vertical-align:top;font-weight:700;">
                                        <?php echo htmlspecialchars(
                                            format_eur((float)($sale['amount_eur'] ?? 0)),
                                            ENT_QUOTES
                                        ); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </section>
</main>

</body>
</html>