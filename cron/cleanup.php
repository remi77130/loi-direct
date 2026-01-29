<?php
declare(strict_types=1);

// cron/cleanup.php 
/* Script de nettoyage/archivage pour les rooms éphémères
Ça te sert de plan B si un jour :
OVH désactive les événements,
tu changes d'hebergeur,
ou tu veux déplacer la logique côté PHP.
Si tu le gardes : protège-le minimum (sinon quelqu'un peut l'appeler via URL).
Protection simple :
soit par un token :cleanup.php?token=xxxx
soit par IP (si tu as une IP fixe)
soit par .htaccessdans /cron(deny all + allow depuis OVH cron uniquement… mais souvent tu connais pas l'IP).
tu peux garder cleanup.php comme « plan B », mais il faut qu'il soit propre , indépendant de config.php (sinon ça casse en CLI), 
et sécurisé (sinon quelqu'un peut le déclencher depuis le web).

Ci-dessous je te donne un cleanup.php prêt qui reproduit tes 3 événements :
ev_archive_ephemeral_rooms: archive les messages des chambres éphémères expirées verschat_messages_archive
ev_delete_ephemeral_rooms: supprimer les chambres expirées + leurs données associées (messages, saisie, présence)
ev_purge_archives_72h: purger l'archive au-delà de 72h (configurable)

⚠️ Important : si vous utilisez ce script en cron , il faut désactiver les Events MySQL 
correspondants (sinon double exécution = pas grave avec INSERT IGNORE, mais tu peux te retrouver avec des suppressions redondantes).
*/
// 1) Sécurité : clé obligatoire


/**
 * cron/cleanup.php
 *
 * Backup des "MySQL Events" via un script cron PHP.
 * - Archive messages des rooms éphémères expirées
 * - Supprime rooms éphémères expirées + data associée
 * - Purge l'archive au-delà d'une rétention (72h par défaut)
 *
 * Utilisation CLI:
 *   php cron/cleanup.php
 *
 * Utilisation HTTP (si tu veux) :
 *   https://tonsite.com/cron/cleanup.php?token=XXXX
 *   (fortement recommandé de bloquer /cron par .htaccess ET/ou de mettre un token)
 */

date_default_timezone_set('Europe/Paris');

// --- Sécurité d'accès ---
function require_auth(): void
{
    if (PHP_SAPI === 'cli') {
        return; // OK en CLI
    }

    $tokenExpected = getenv('CRON_TOKEN') ?: '';
    if ($tokenExpected === '') {
        http_response_code(403);
        exit('CRON_TOKEN manquant côté serveur.');
    }

    $token = $_GET['token'] ?? '';
    if (!hash_equals($tokenExpected, $token)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function log_line(string $msg): void
{
    error_log('[cleanup] ' . $msg);
}

// --- Bootstrap DB ---
// IMPORTANT: on ne charge PAS config.php (APP_BASE etc.) pour éviter les warnings en CLI.
$root = dirname(__DIR__); // si ton fichier est bien dans /cron
require $root . '/../db.php'; // doit définir $mysqli (mysqli)

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    exit('DB non initialisée ($mysqli manquant).');
}

require_auth();

// --- Config ---
$retentionHours = 72; // purge archives au-delà de 72h (comme ton event)
$batchLimitRooms = 500; // limite sécurité si beaucoup de rooms expirées d'un coup

// --- Helpers ---
function fetch_expired_ephemeral_room_ids(mysqli $mysqli, int $limit): array
{
    $sql = "SELECT id
            FROM chat_rooms
            WHERE is_ephemeral = 1
              AND expires_at IS NOT NULL
              AND expires_at <= NOW()
            ORDER BY expires_at ASC
            LIMIT ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return [];

    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $res = $stmt->get_result();

    $ids = [];
    while ($row = $res->fetch_assoc()) {
        $ids[] = (int)$row['id'];
    }
    $stmt->close();
    return $ids;
}

function placeholders(int $n): string
{
    return implode(',', array_fill(0, $n, '?'));
}

function bind_ints(mysqli_stmt $stmt, array $ids): void
{
    // bind_param demande des références
    $types = str_repeat('i', count($ids));
    $params = [];
    $params[] = $types;
    foreach ($ids as $k => $v) {
        $params[] = &$ids[$k];
    }
    $stmt->bind_param(...$params);
}

// --- Jobs ---
function archive_expired_ephemeral_rooms(mysqli $mysqli): int
{
    // Copie les messages des rooms expirées vers chat_messages_archive
    $sql = "
        INSERT IGNORE INTO chat_messages_archive
            (original_message_id, room_id, sender_id, body, created_at, is_system, like_count)
        SELECT
            m.id, m.room_id, m.sender_id, m.body, m.created_at, m.is_system, m.like_count
        FROM chat_messages m
        INNER JOIN chat_rooms r ON r.id = m.room_id
        WHERE r.is_ephemeral = 1
          AND r.expires_at IS NOT NULL
          AND r.expires_at <= NOW()
    ";

    if (!$mysqli->query($sql)) {
        log_line('archive FAILED: ' . $mysqli->error);
        return 0;
    }

    return $mysqli->affected_rows; // nb insertés (approximatif car IGNORE)
}

function delete_expired_ephemeral_rooms(mysqli $mysqli, int $limitRooms): int
{
    $ids = fetch_expired_ephemeral_room_ids($mysqli, $limitRooms);
    if (!$ids) {
        return 0;
    }

    $mysqli->begin_transaction();

    try {
        // 1) Nettoyage tables liées (si elles existent)
        $in = placeholders(count($ids));

        // chat_presence
        if ($stmt = $mysqli->prepare("DELETE FROM chat_presence WHERE room_id IN ($in)")) {
            bind_ints($stmt, $ids);
            $stmt->execute();
            $stmt->close();
        }

        // chat_typing
        if ($stmt = $mysqli->prepare("DELETE FROM chat_typing WHERE room_id IN ($in)")) {
            bind_ints($stmt, $ids);
            $stmt->execute();
            $stmt->close();
        }

        // chat_messages (les messages sont déjà archivés)
        if ($stmt = $mysqli->prepare("DELETE FROM chat_messages WHERE room_id IN ($in)")) {
            bind_ints($stmt, $ids);
            $stmt->execute();
            $stmt->close();
        }

        // 2) Supprime les rooms
        $deletedRooms = 0;
        if ($stmt = $mysqli->prepare("DELETE FROM chat_rooms WHERE id IN ($in)")) {
            bind_ints($stmt, $ids);
            $stmt->execute();
            $deletedRooms = $stmt->affected_rows;
            $stmt->close();
        }

        $mysqli->commit();
        return $deletedRooms;

    } catch (Throwable $e) {
        $mysqli->rollback();
        log_line('delete FAILED: ' . $e->getMessage());
        return 0;
    }
}

function purge_archives(mysqli $mysqli, int $retentionHours): int
{
    $sql = "DELETE FROM chat_messages_archive
            WHERE created_at < (NOW() - INTERVAL ? HOUR)";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        log_line('purge FAILED: ' . $mysqli->error);
        return 0;
    }

    $stmt->bind_param('i', $retentionHours);
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    $stmt->close();

    return $deleted;
}

// --- Run ---
$start = microtime(true);

$archived = archive_expired_ephemeral_rooms($mysqli);
$deletedRooms = delete_expired_ephemeral_rooms($mysqli, $batchLimitRooms);
$purged = purge_archives($mysqli, $retentionHours);

$ms = (int)round((microtime(true) - $start) * 1000);

log_line("OK archived≈{$archived} deletedRooms={$deletedRooms} purged={$purged} in {$ms}ms");

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "OK\n";
    echo "archived≈{$archived}\n";
    echo "deletedRooms={$deletedRooms}\n";
    echo "purged={$purged}\n";
    echo "timeMs={$ms}\n";
}
