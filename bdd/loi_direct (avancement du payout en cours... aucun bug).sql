-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 24 mars 2026 à 09:38
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `loi_direct`
--

-- --------------------------------------------------------

--
-- Structure de la table `chat_dm_typing`
--

CREATE TABLE `chat_dm_typing` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `peer_id` int(10) UNSIGNED NOT NULL,
  `last_ping` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `file_url` varchar(255) DEFAULT NULL,
  `file_mime` varchar(100) DEFAULT NULL,
  `file_w` int(10) UNSIGNED DEFAULT NULL,
  `file_h` int(10) UNSIGNED DEFAULT NULL,
  `color` char(7) DEFAULT NULL CHECK (`color` is null or `color` regexp '^#[0-9A-Fa-f]{6}$'),
  `like_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `message_type` enum('user','system') NOT NULL DEFAULT 'user',
  `system_event` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `room_id`, `sender_id`, `body`, `created_at`, `file_url`, `file_mime`, `file_w`, `file_h`, `color`, `like_count`, `message_type`, `system_event`) VALUES
(606, 109, 66, 'a rejoint le salon', '2026-03-13 12:27:37', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(607, 109, 66, 'a quitté le salon', '2026-03-13 12:28:24', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(608, 109, 66, 'a rejoint le salon', '2026-03-13 12:28:46', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(609, 109, 66, 'a quitté le salon', '2026-03-13 13:30:36', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(610, 109, 66, 'a rejoint le salon', '2026-03-13 13:30:39', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(611, 109, 66, 'a quitté le salon', '2026-03-13 14:00:18', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(612, 109, 66, 'a rejoint le salon', '2026-03-13 14:00:20', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(613, 109, 66, 'a quitté le salon', '2026-03-13 14:00:31', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(614, 109, 66, 'a rejoint le salon', '2026-03-13 14:00:33', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(615, 109, 66, 'a quitté le salon', '2026-03-13 14:00:54', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(616, 109, 66, 'a rejoint le salon', '2026-03-13 14:01:58', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(617, 109, 66, 'a quitté le salon', '2026-03-13 14:37:53', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(618, 109, 66, 'a rejoint le salon', '2026-03-13 14:48:50', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(619, 109, 66, 'a quitté le salon', '2026-03-13 14:57:01', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(620, 109, 66, 'a rejoint le salon', '2026-03-13 14:57:02', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(621, 110, 67, 'yo ?', '2026-03-13 14:59:08', NULL, NULL, NULL, NULL, '#000000', 1, 'user', NULL),
(622, 110, 67, 'a rejoint le salon', '2026-03-16 12:19:17', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(623, 110, 67, 'ssssssssss', '2026-03-16 12:19:46', NULL, NULL, NULL, NULL, '#000000', 0, 'user', NULL),
(624, 110, 67, 'a quitté le salon', '2026-03-16 12:21:42', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(625, 110, 67, 'a rejoint le salon', '2026-03-16 12:21:43', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(626, 110, 67, 'a quitté le salon', '2026-03-16 12:21:51', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(627, 109, 67, 'a rejoint le salon', '2026-03-16 12:21:51', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(628, 109, 67, 'a quitté le salon', '2026-03-16 12:22:09', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(629, 110, 67, 'a rejoint le salon', '2026-03-16 12:22:12', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'join'),
(630, 110, 67, 'a quitté le salon', '2026-03-16 12:22:22', NULL, NULL, NULL, NULL, NULL, 0, 'system', 'leave'),
(634, 110, 66, 'scccccc', '2026-03-16 16:20:56', NULL, NULL, NULL, NULL, '#000000', 1, 'user', NULL),
(635, 110, 66, 'sssssssssssssssss', '2026-03-18 13:56:52', NULL, NULL, NULL, NULL, '#000000', 1, 'user', NULL),
(636, 112, 66, 'sccccccc', '2026-03-19 15:24:06', '/loi/uploads/chat_20260319_152406_b73e6ea356ea.jpg', 'image/jpeg', 424, 300, '#000000', 1, 'user', NULL),
(637, 112, 68, 'dddddd', '2026-03-20 14:14:12', NULL, NULL, NULL, NULL, '#000000', 0, 'user', NULL),
(638, 113, 68, 'qxqxqxqxqxqxqxqxqxqxqxqxqx', '2026-03-24 09:36:32', '/loi/uploads/chat_20260324_093632_a4a50e4ba52c.jpg', 'image/jpeg', 424, 300, '#000000', 0, 'user', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `chat_messages_archive`
--

CREATE TABLE `chat_messages_archive` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `original_message_id` int(10) UNSIGNED DEFAULT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `body` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `archived_at` datetime NOT NULL DEFAULT current_timestamp(),
  `file_url` varchar(255) DEFAULT NULL,
  `file_mime` varchar(100) DEFAULT NULL,
  `file_w` smallint(5) UNSIGNED DEFAULT NULL,
  `file_h` smallint(5) UNSIGNED DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `like_count` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `chat_messages_archive`
--

INSERT INTO `chat_messages_archive` (`id`, `original_message_id`, `room_id`, `sender_id`, `body`, `created_at`, `archived_at`, `file_url`, `file_mime`, `file_w`, `file_h`, `color`, `is_system`, `like_count`) VALUES
(3, 392, 103, 43, 'xxxxxxxxxxx', '2026-01-08 15:26:13', '2026-01-18 17:33:23', NULL, NULL, NULL, NULL, '#000000', 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `chat_notifications`
--

CREATE TABLE `chat_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED DEFAULT NULL,
  `message_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('mention','dm') NOT NULL DEFAULT 'mention',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `chat_presence`
--

CREATE TABLE `chat_presence` (
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_key` char(36) NOT NULL,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `leave_notified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_presence`
--

INSERT INTO `chat_presence` (`room_id`, `user_id`, `session_key`, `last_seen`, `leave_notified`) VALUES
(0, 68, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-03-21 07:19:11', 0),
(0, 68, '912341a5-b92e-4f43-80a9-964f56b1dbb3', '2026-03-24 08:38:25', 0);

-- --------------------------------------------------------

--
-- Structure de la table `chat_rooms`
--

CREATE TABLE `chat_rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `owner_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `password_hash` varchar(255) DEFAULT NULL,
  `is_private` tinyint(1) NOT NULL DEFAULT 0,
  `is_ephemeral` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_rooms`
--

INSERT INTO `chat_rooms` (`id`, `name`, `owner_id`, `password_hash`, `is_private`, `is_ephemeral`, `created_by`, `created_at`, `expires_at`) VALUES
(109, 'kiki', 0, NULL, 0, 0, 66, '2026-03-13 12:27:35', NULL),
(110, 'qqqqqqq', 0, NULL, 0, 0, 67, '2026-03-13 14:58:59', NULL),
(112, 'scscscs', 0, NULL, 0, 1, 66, '2026-03-19 15:24:00', '2026-03-20 15:24:00'),
(113, 'xqqqqq', 0, NULL, 0, 0, 68, '2026-03-24 09:36:20', NULL);

--
-- Déclencheurs `chat_rooms`
--
DELIMITER $$
CREATE TRIGGER `trg_archive_ephemeral_room_messages` BEFORE DELETE ON `chat_rooms` FOR EACH ROW BEGIN
  IF OLD.is_ephemeral = 1 THEN
    INSERT IGNORE INTO chat_messages_archive
      (original_message_id, room_id, sender_id, body, created_at,
       file_url, file_mime, file_w, file_h, color, like_count)
    SELECT
      m.id, m.room_id, m.sender_id, m.body, m.created_at,
      m.file_url, m.file_mime, m.file_w, m.file_h, m.color, m.like_count
    FROM chat_messages m
    WHERE m.room_id = OLD.id;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `chat_typing`
--

CREATE TABLE `chat_typing` (
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_typing_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_typing`
--

INSERT INTO `chat_typing` (`room_id`, `user_id`, `last_typing_at`) VALUES
(110, 67, '2026-03-16 12:19:38'),
(110, 66, '2026-03-18 13:56:51'),
(112, 66, '2026-03-19 15:24:01'),
(112, 68, '2026-03-20 14:14:11'),
(113, 68, '2026-03-24 09:36:21');

-- --------------------------------------------------------

--
-- Structure de la table `cities`
--

CREATE TABLE `cities` (
  `id` int(10) UNSIGNED NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `insee_code` varchar(10) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `department` varchar(80) DEFAULT NULL,
  `lat` decimal(9,6) DEFAULT NULL,
  `lon` decimal(9,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `author_id` int(10) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `comments`
--

INSERT INTO `comments` (`id`, `project_id`, `author_id`, `body`, `created_at`) VALUES
(13, 42, 68, 'vdddddddddzs', '2026-03-18 11:37:02'),
(14, 42, 68, 'xxxxxxxxx', '2026-03-18 11:40:56'),
(16, 45, 68, 'xcsecdv cdf', '2026-03-19 15:10:19'),
(17, 45, 68, 'ddddddddddd', '2026-03-19 15:10:26');

-- --------------------------------------------------------

--
-- Structure de la table `creator_payout_requests`
--

CREATE TABLE `creator_payout_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `creator_user_id` int(10) UNSIGNED NOT NULL,
  `amount_eur` decimal(10,2) NOT NULL,
  `payout_method` enum('paypal') NOT NULL DEFAULT 'paypal',
  `payout_email` varchar(190) NOT NULL,
  `status` enum('pending','approved','paid','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `admin_note` varchar(255) DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `creator_wallets`
--

CREATE TABLE `creator_wallets` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `balance_eur` decimal(10,2) NOT NULL DEFAULT 0.00,
  `lifetime_earned_eur` decimal(10,2) NOT NULL DEFAULT 0.00,
  `lifetime_paid_eur` decimal(10,2) NOT NULL DEFAULT 0.00,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pending_payout_eur` decimal(10,2) NOT NULL DEFAULT 0.00,
  `lifetime_paid_out_eur` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `creator_wallets`
--

INSERT INTO `creator_wallets` (`user_id`, `balance_eur`, `lifetime_earned_eur`, `lifetime_paid_eur`, `updated_at`, `pending_payout_eur`, `lifetime_paid_out_eur`) VALUES
(68, 10.00, 59.50, 0.00, '2026-03-24 09:26:42', 29.50, 0.00);

-- --------------------------------------------------------

--
-- Structure de la table `creator_wallet_transactions`
--

CREATE TABLE `creator_wallet_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `creator_user_id` int(10) UNSIGNED NOT NULL,
  `type` enum('room_sale','adjustment_credit','adjustment_debit','payout_request_hold','payout_paid','payout_released','refund_reversal','project_unlock_sale') NOT NULL,
  `amount_eur` decimal(10,2) NOT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `related_room_id` int(10) UNSIGNED DEFAULT NULL,
  `related_membership_id` bigint(20) UNSIGNED DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `creator_wallet_transactions`
--

INSERT INTO `creator_wallet_transactions` (`id`, `creator_user_id`, `type`, `amount_eur`, `balance_after`, `related_room_id`, `related_membership_id`, `note`, `created_at`) VALUES
(1, 68, 'project_unlock_sale', 17.50, 17.50, 42, 1, 'Revenu déverrouillage projet #42', '2026-03-18 12:43:14'),
(2, 68, 'project_unlock_sale', 3.50, 21.00, 44, 2, 'Revenu déverrouillage projet #44', '2026-03-18 12:46:22'),
(3, 68, 'project_unlock_sale', 35.00, 56.00, 45, 3, 'Revenu déverrouillage projet #45', '2026-03-19 15:11:15'),
(4, 68, 'project_unlock_sale', 3.50, 59.50, 46, 4, 'Revenu déverrouillage projet #46', '2026-03-20 14:51:13'),
(5, 68, 'payout_request_hold', -29.50, 30.00, NULL, 1, 'Demande de retrait payout #1', '2026-03-24 09:22:20');

-- --------------------------------------------------------

--
-- Structure de la table `ip_bans`
--

CREATE TABLE `ip_bans` (
  `ip` varchar(45) NOT NULL,
  `until` datetime NOT NULL,
  `reason` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ip_bans`
--

INSERT INTO `ip_bans` (`ip`, `until`, `reason`) VALUES
('::1', '2026-03-05 12:14:33', 'too_many_failures');

-- --------------------------------------------------------

--
-- Structure de la table `law_projects`
--

CREATE TABLE `law_projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `author_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `summary` varchar(280) NOT NULL,
  `body_markdown` mediumtext NOT NULL,
  `status` enum('draft','published','removed') NOT NULL DEFAULT 'published',
  `published_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `unlock_points_price` int(10) UNSIGNED DEFAULT NULL,
  `creator_user_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `law_projects`
--

INSERT INTO `law_projects` (`id`, `author_id`, `title`, `summary`, `body_markdown`, `status`, `published_at`, `created_at`, `updated_at`, `is_paid`, `unlock_points_price`, `creator_user_id`) VALUES
(22, 24, '🎯 Bienvenue dans la Boîte à idées & Signalements', '💬 Ton avis compte !', '🧠 Espace feedback technique\r\nCe salon sert à centraliser les retours utilisateurs :\r\n\r\nSignalement de bugs (avec capture si possible)\r\n\r\nIdées d’amélioration\r\n\r\nSuggestions d’ergonomie ou de design\r\n\r\nLes messages utiles seront tagués À corriger ou À étudier.', 'published', '2025-11-07 18:14:02', '2025-11-07 18:14:02', '2025-11-07 18:14:02', 0, NULL, NULL),
(23, 24, 'qqqqqqqqqqqq', 'qqqqqqqqqqqqqqqqq', 'qqqqqqqq', 'published', '2025-11-09 16:36:39', '2025-11-09 16:36:39', '2025-11-09 16:36:39', 0, NULL, NULL),
(24, 33, 'sssssssss', 'sssssssssssss', 'ssssssss', 'published', '2025-11-10 10:09:14', '2025-11-10 10:09:14', '2025-11-10 10:09:14', 0, NULL, NULL),
(25, 36, 'sssssssssssssssssasx', 'ssssssssssssss', 'sssssssssssssssa', 'published', '2025-11-10 15:03:57', '2025-11-10 15:03:57', '2025-11-10 15:03:57', 0, NULL, NULL),
(26, 37, 'dddddddddddddddd', 'ddddddddddddddd', 'ddddddddd', 'published', '2025-11-10 15:21:13', '2025-11-10 15:21:13', '2025-11-10 15:21:13', 0, NULL, NULL),
(27, 24, '💬 Ton avis compte !', 'Tu as repéré un bug ? Une idée pour rendre le site plus cool ?\r\nPoste-la ici !', '➕ Améliorations\r\n⚙️ Bugs / dysfonctionnements\r\n💡 Nouvelles fonctionnalités\r\n\r\nChaque retour est lu et pris en compte.', 'published', '2025-11-11 11:55:18', '2025-11-11 11:55:18', '2025-11-11 11:55:18', 0, NULL, NULL),
(28, 25, 'mmmmmmmmmmmmmm', 'mmmmmmmmmmmmmm', 'mmmmmmmmmmmmm', 'published', '2025-11-22 17:07:18', '2025-11-22 17:07:18', '2025-11-22 17:07:18', 0, NULL, NULL),
(30, 56, 'cccccc', 'ccccccccccccccccc', 'cccccccccc', 'published', '2026-03-03 12:04:28', '2026-03-03 12:04:28', '2026-03-03 12:04:28', 0, NULL, NULL),
(31, 56, 'g,,,,,,,,,', ',,,,,,,,,,,,,,,', 'g,,,,,,,,,,,,,,,', 'published', '2026-03-03 14:08:46', '2026-03-03 14:08:46', '2026-03-03 14:08:46', 0, NULL, NULL),
(32, 56, 'g,,,,,,,,,', ',,,,,,,,,,,,,,,', 'g,,,,,,,,,,,,,,,', 'published', '2026-03-03 15:11:47', '2026-03-03 15:11:47', '2026-03-03 15:11:47', 0, NULL, NULL),
(33, 56, 'XSSSSSSS', 'SXSQ', 'SSSSSSSSS', 'published', '2026-03-03 15:12:59', '2026-03-03 15:12:59', '2026-03-03 15:12:59', 0, NULL, NULL),
(34, 56, 'SSSSSSS', 'SSSSSSSS', 'SSSSSSSS', 'published', '2026-03-03 15:13:23', '2026-03-03 15:13:23', '2026-03-03 15:13:23', 0, NULL, NULL),
(39, 66, 'xssssssssssss', 'sssssssssssss', 'sxsxsxsxsxsxsxsxsxsxsxsx', 'published', '2026-03-17 16:13:33', '2026-03-17 16:13:33', '2026-03-17 16:13:33', 1, 50, 66),
(41, 66, 'QCSSSSSSS', 'SSSSSS', 'CSQSQSQSQSQSQSQSQSQ', 'published', '2026-03-18 11:20:21', '2026-03-18 11:20:21', '2026-03-18 11:20:21', 1, 500, 66),
(42, 68, 'ceeeeeeeee', 'eeceeeeeeeeeeeeeez', 'czzzzzzzzzzzze', 'published', '2026-03-18 11:36:16', '2026-03-18 11:36:16', '2026-03-18 11:36:16', 1, 500, 68),
(43, 66, 'sccccccccccccc', 'ccccccccccccccc', 'cssssssssss', 'published', '2026-03-18 12:35:10', '2026-03-18 12:35:10', '2026-03-18 12:35:10', 1, 100, 66),
(44, 68, 'xxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxxx', 'xxxx', 'published', '2026-03-18 12:45:47', '2026-03-18 12:45:47', '2026-03-18 12:45:47', 1, 100, 68),
(45, 68, 'test point', 'test point', 'test point', 'published', '2026-03-19 15:10:13', '2026-03-19 15:10:13', '2026-03-19 15:10:13', 1, 1000, 68);

-- --------------------------------------------------------

--
-- Structure de la table `likes`
--

CREATE TABLE `likes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `project_id`, `created_at`) VALUES
(0, 24, 22, '2025-11-07 18:14:07');

-- --------------------------------------------------------

--
-- Structure de la table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip` varbinary(16) NOT NULL,
  `pseudo` varchar(100) DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip`, `pseudo`, `success`, `created_at`) VALUES
(163, 0x3a3a31, 'lepoitu33', 1, '2026-02-14 17:17:29'),
(164, 0x3a3a31, 'ssssssss', 0, '2026-02-15 13:21:40'),
(165, 0x3a3a31, 'lepoitu33', 1, '2026-02-15 13:22:07'),
(166, 0x3a3a31, 'lepoitu33', 1, '2026-02-15 13:22:47'),
(167, 0x3a3a31, 'lepoitu33', 1, '2026-02-15 13:25:47'),
(168, 0x3a3a31, 'remi_admin', 1, '2026-02-15 13:43:41'),
(169, 0x3a3a31, 'admin', 1, '2026-02-17 13:00:20'),
(170, 0x3a3a31, 'admin', 1, '2026-02-17 13:00:45'),
(171, 0x3a3a31, 'remi77', 0, '2026-02-28 17:16:21'),
(172, 0x3a3a31, 'Rémi85', 1, '2026-03-02 14:38:34'),
(173, 0x3a3a31, 'admin', 1, '2026-03-03 10:42:45'),
(174, 0x3a3a31, 'Rémi85', 1, '2026-03-03 14:18:36'),
(175, 0x3a3a31, 'Rémi85', 1, '2026-03-03 15:18:35'),
(176, 0x3a3a31, 'Rémi85', 1, '2026-03-04 13:41:02'),
(177, 0x3a3a31, 'Rémi85', 1, '2026-03-04 14:20:39'),
(178, 0x3a3a31, 'Rémi85', 1, '2026-03-05 09:42:50'),
(179, 0x3a3a31, 'Rémi85', 1, '2026-03-05 10:45:15'),
(180, 0x3a3a31, 'Rémi85', 1, '2026-03-05 10:45:57'),
(181, 0x3a3a31, 'Rémi85', 1, '2026-03-05 10:46:14'),
(182, 0x3a3a31, 'Rémi85', 1, '2026-03-05 10:46:27'),
(183, 0x3a3a31, 'Rémi85', 1, '2026-03-05 10:52:42'),
(184, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 1, '2026-03-05 10:56:46'),
(185, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 0, '2026-03-05 10:59:20'),
(186, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 0, '2026-03-05 10:59:23'),
(187, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 0, '2026-03-05 10:59:25'),
(188, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 0, '2026-03-05 10:59:26'),
(189, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 0, '2026-03-05 10:59:28'),
(190, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 0, '2026-03-05 10:59:29'),
(191, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 0, '2026-03-05 10:59:30'),
(192, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 0, '2026-03-05 10:59:31'),
(193, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 0, '2026-03-05 10:59:32'),
(194, 0x3a3a31, 'ZCZCZCZCZCZCZCZCZCZC', 0, '2026-03-05 10:59:33'),
(195, 0x3a3a31, 'remi77', 0, '2026-03-05 11:41:29'),
(196, 0x3a3a31, 'Rémi85', 1, '2026-03-05 11:41:41'),
(197, 0x3a3a31, 'balancetonporc', 0, '2026-03-05 15:02:54'),
(198, 0x3a3a31, 'balancetonporc', 0, '2026-03-05 15:03:01'),
(199, 0x3a3a31, 'balancetonporc', 1, '2026-03-05 15:04:21'),
(200, 0x3a3a31, 'Rémi85', 1, '2026-03-06 17:51:56'),
(201, 0x3a3a31, 'toto2', 1, '2026-03-07 11:00:13'),
(202, 0x3a3a31, 'Rémi85', 1, '2026-03-07 11:09:28'),
(203, 0x3a3a31, 'toto2', 1, '2026-03-07 11:10:11'),
(204, 0x3a3a31, 'Rémi85', 1, '2026-03-08 07:26:27'),
(205, 0x3a3a31, 'Rémi85', 1, '2026-03-08 09:16:55'),
(206, 0x3a3a31, 'remi_admin', 1, '2026-03-08 09:17:38'),
(207, 0x3a3a31, 'Rémi85', 1, '2026-03-08 09:18:13'),
(208, 0x3a3a31, 'toto2', 1, '2026-03-08 09:18:42'),
(209, 0x3a3a31, 'Rémi85', 1, '2026-03-08 09:22:29'),
(210, 0x3a3a31, 'toto2', 1, '2026-03-08 09:23:15'),
(211, 0x3a3a31, 'Rémi85', 1, '2026-03-08 09:23:35'),
(212, 0x3a3a31, 'toto2', 1, '2026-03-08 09:24:17'),
(213, 0x3a3a31, 'toto2', 1, '2026-03-08 09:27:26'),
(214, 0x3a3a31, 'gilbert89', 1, '2026-03-08 09:28:58'),
(215, 0x3a3a31, 'toto2', 1, '2026-03-08 09:29:46'),
(216, 0x3a3a31, 'Rémi85', 1, '2026-03-09 11:07:21'),
(217, 0x3a3a31, 'toto', 1, '2026-03-09 11:09:47'),
(218, 0x3a3a31, 'phil', 1, '2026-03-09 11:25:27'),
(219, 0x3a3a31, 'marco', 1, '2026-03-09 11:26:40'),
(220, 0x3a3a31, 'Rémi85', 1, '2026-03-09 11:28:04'),
(221, 0x3a3a31, 'marco', 1, '2026-03-09 11:29:05'),
(222, 0x3a3a31, 'gilbert89', 1, '2026-03-09 11:32:04'),
(223, 0x3a3a31, 'toto2', 1, '2026-03-09 11:33:03'),
(224, 0x3a3a31, 'marco', 1, '2026-03-09 11:42:32'),
(225, 0x3a3a31, 'phil', 1, '2026-03-09 11:48:21'),
(226, 0x3a3a31, 'Rémi85', 1, '2026-03-09 11:49:20'),
(227, 0x3a3a31, 'phil', 1, '2026-03-11 11:06:53'),
(228, 0x3a3a31, 'Rémi85', 1, '2026-03-11 11:08:00'),
(229, 0x3a3a31, 'phil', 1, '2026-03-11 11:08:33'),
(230, 0x3a3a31, 'ssw', 1, '2026-03-11 11:30:40'),
(231, 0x3a3a31, 'toto2', 1, '2026-03-11 11:38:50'),
(232, 0x3a3a31, 'Rémi85', 0, '2026-03-13 11:27:07'),
(233, 0x3a3a31, 'Rémi85', 1, '2026-03-16 15:20:53'),
(234, 0x3a3a31, 'Rémi85', 1, '2026-03-17 14:35:44'),
(235, 0x3a3a31, 'balancetonporc', 0, '2026-03-18 10:20:34'),
(236, 0x3a3a31, 'admin', 0, '2026-03-18 10:20:43'),
(237, 0x3a3a31, 'Rémi85', 1, '2026-03-18 10:41:07'),
(238, 0x3a3a31, 'Rémi85', 1, '2026-03-18 11:34:36'),
(239, 0x3a3a31, 'balancetonporc', 1, '2026-03-18 11:35:21'),
(240, 0x3a3a31, 'Rémi85', 1, '2026-03-18 11:38:14'),
(241, 0x3a3a31, 'balancetonporc', 1, '2026-03-18 11:45:22'),
(242, 0x3a3a31, 'Rémi85', 1, '2026-03-18 11:45:59'),
(243, 0x3a3a31, 'Rémi85', 1, '2026-03-19 12:57:17'),
(244, 0x3a3a31, 'balancetonporc', 1, '2026-03-19 14:09:42'),
(245, 0x3a3a31, 'Rémi85', 1, '2026-03-19 14:10:33'),
(246, 0x3a3a31, 'Rémi85', 1, '2026-03-20 11:14:06'),
(247, 0x3a3a31, 'balancetonporc', 1, '2026-03-20 13:14:01'),
(248, 0x3a3a31, 'Rémi85', 1, '2026-03-20 13:50:24'),
(249, 0x3a3a31, 'balancetonporc', 1, '2026-03-20 13:54:01'),
(250, 0x3a3a31, 'Rémi85', 1, '2026-03-21 07:09:00'),
(251, 0x3a3a31, 'balancetonporc', 1, '2026-03-21 07:19:06'),
(252, 0x3a3a31, 'Rémi_85', 0, '2026-03-24 08:17:36'),
(253, 0x3a3a31, 'Remi_85', 0, '2026-03-24 08:17:48'),
(254, 0x3a3a31, 'Rémi85', 1, '2026-03-24 08:18:08'),
(255, 0x3a3a31, 'balancetonporc', 1, '2026-03-24 08:20:42');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `file_mime` varchar(60) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  `deleted_by_sender` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_recipient` tinyint(1) NOT NULL DEFAULT 0,
  `color` char(7) DEFAULT NULL CHECK (`color` is null or `color` regexp '^#[0-9A-Fa-f]{6}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `body`, `image_path`, `file_mime`, `created_at`, `read_at`, `deleted_by_sender`, `deleted_by_recipient`, `color`) VALUES
(56, 34, 33, 'dssssssssss', NULL, NULL, '2025-11-10 11:28:14', NULL, 0, 0, NULL),
(57, 35, 34, 'ssssssss', NULL, NULL, '2025-11-10 13:40:56', NULL, 0, 0, NULL),
(58, 35, 33, 'ssssss', NULL, NULL, '2025-11-10 13:54:55', NULL, 0, 0, NULL),
(59, 35, 34, 'sssssssssssssssssssss', NULL, NULL, '2025-11-10 14:16:26', NULL, 0, 0, NULL),
(60, 24, 35, 'sssssssss', NULL, NULL, '2025-11-11 16:48:14', '2026-01-02 12:16:24', 0, 0, NULL),
(61, 24, 35, 'sssssssss', NULL, NULL, '2025-11-11 16:48:17', '2026-01-02 12:16:24', 0, 0, NULL),
(62, 24, 35, 'ssssssssssssssssss', NULL, NULL, '2025-11-11 16:56:31', '2026-01-02 12:16:24', 0, 0, NULL),
(63, 25, 35, 'ssssssssssssssss', NULL, NULL, '2025-11-16 10:01:59', '2026-01-02 12:16:24', 1, 0, NULL),
(64, 42, 25, 'sssssssssssssss', NULL, NULL, '2025-11-19 09:12:58', '2025-11-20 12:29:02', 0, 1, NULL),
(65, 25, 42, 'ssssss', NULL, NULL, '2026-01-04 17:57:41', NULL, 1, 0, NULL),
(66, 25, 35, 'ouiuçi', NULL, NULL, '2026-01-04 20:02:59', NULL, 1, 0, NULL),
(67, 25, 35, 'ssssssssssss', NULL, NULL, '2026-01-05 14:17:15', NULL, 1, 0, NULL),
(68, 25, 35, 'zsdczeczefz', 'uploads/msg/46a94459e39c76396d3bdabc09b4c8fe.png', NULL, '2026-01-05 14:17:24', NULL, 1, 0, NULL),
(69, 25, 24, 'xxxxxxxxxxxxxxxxxx', NULL, NULL, '2026-01-05 14:29:16', '2026-01-26 16:20:05', 0, 0, NULL),
(70, 25, 24, 'sssssssssssss', 'uploads/msg/239d1cdaa1fdf9c728f7e91206116fdf.png', NULL, '2026-01-05 14:29:46', '2026-01-26 16:20:05', 0, 0, NULL),
(71, 25, 24, 'ssssssssssss', NULL, NULL, '2026-01-05 14:29:50', '2026-01-26 16:20:05', 0, 0, NULL),
(72, 25, 24, '02', NULL, NULL, '2026-01-05 14:29:56', '2026-01-26 16:20:05', 0, 0, NULL),
(73, 25, 24, 'ssssssssssss', NULL, NULL, '2026-01-05 14:30:27', '2026-01-26 16:20:05', 0, 0, NULL),
(74, 25, 24, 'sssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaa', 'uploads/msg/34c60d690001e0a5ef1215da1ced91f9.jpg', NULL, '2026-01-05 14:30:37', '2026-01-26 16:20:05', 0, 0, NULL),
(75, 25, 24, 'sssssss', NULL, NULL, '2026-01-05 14:49:59', '2026-01-26 16:20:05', 0, 0, NULL),
(76, 24, 25, 'dcccccccc', NULL, NULL, '2026-01-26 16:19:41', '2026-01-27 11:55:10', 0, 0, NULL),
(77, 25, 54, 'salut', NULL, NULL, '2026-01-27 11:56:02', '2026-01-27 11:56:31', 0, 0, NULL),
(78, 54, 48, 'salut', NULL, NULL, '2026-01-27 11:59:01', '2026-01-27 11:59:29', 0, 0, NULL),
(79, 48, 24, 'salut', NULL, NULL, '2026-01-27 12:02:08', '2026-01-27 12:02:42', 0, 0, NULL),
(80, 24, 48, 'salut', NULL, NULL, '2026-01-27 12:02:52', '2026-01-29 15:22:23', 0, 0, NULL),
(81, 48, 25, 'ssssssssssss', NULL, NULL, '2026-01-29 14:07:43', '2026-01-29 14:08:21', 0, 0, NULL),
(82, 48, 25, 'ssssssssss', 'uploads/msg/c8cc7819cd1b5d1156a83b81c853d7d0.mp4', 'video/mp4', '2026-01-29 14:07:55', '2026-01-29 14:08:21', 0, 0, NULL),
(83, 25, 48, 'dddddd', NULL, NULL, '2026-01-29 14:48:30', '2026-01-29 15:22:23', 0, 0, NULL),
(84, 25, 48, '', 'uploads/msg/7adb6e161d564fc796551c53d0648be9.mp4', NULL, '2026-01-29 14:48:38', '2026-01-29 15:22:23', 0, 0, NULL),
(85, 25, 48, '', 'uploads/msg/32ce91def2b3226847a9e3a61113a29a.jpg', NULL, '2026-01-29 14:48:46', '2026-01-29 15:22:23', 0, 0, NULL),
(86, 25, 48, 'qqqqqq', NULL, NULL, '2026-01-29 14:54:01', '2026-01-29 15:22:23', 0, 0, NULL),
(87, 25, 48, '', 'uploads/msg/1030613b7cdf5f9c81e0ff6f49bcaf52.jpg', 'image/jpeg', '2026-01-29 14:54:07', '2026-01-29 15:22:23', 0, 0, NULL),
(88, 25, 48, 'qqqqqqqqqqqqqqqqqq', 'uploads/msg/05809512acd1d9fa92574dec621cd456.mp4', 'video/mp4', '2026-01-29 14:54:16', '2026-01-29 15:22:23', 0, 0, NULL),
(89, 25, 48, '', 'uploads/msg/439a340dc62b56dccf0bc40637b3ae84.mp4', 'video/mp4', '2026-01-29 15:04:08', '2026-01-29 15:22:23', 0, 0, NULL),
(90, 25, 48, 'sssssss', NULL, NULL, '2026-01-29 15:16:38', '2026-01-29 15:22:23', 0, 0, NULL),
(91, 25, 48, '', 'uploads/msg/ef335c9aacca3874e9a9042ac4b4a23a.jpg', 'image/jpeg', '2026-01-29 15:16:46', '2026-01-29 15:22:23', 0, 0, NULL),
(92, 25, 48, 'ssssssssss', 'uploads/msg/36a17c6d92780e9bf4bd67b9a436f8ff.mp4', 'video/mp4', '2026-01-29 15:16:59', '2026-01-29 15:22:23', 0, 0, NULL),
(93, 25, 48, '', 'uploads/msg/315ac20007e23f4f202ebbde672ccf19.mp4', 'video/mp4', '2026-01-29 15:17:27', '2026-01-29 15:22:23', 0, 0, NULL),
(94, 25, 48, 'sssssssssssss', 'uploads/msg/34aa8e59d1727a6a3e43c3e4cb359296.mp4', 'video/mp4', '2026-01-29 15:22:03', '2026-01-29 15:22:23', 0, 0, NULL),
(95, 48, 25, 'video ok', 'uploads/msg/b7dde1e15348299de260868555ad6fcf.mp4', 'video/mp4', '2026-01-29 15:23:44', '2026-01-29 15:25:30', 0, 0, NULL),
(96, 48, 25, '', 'uploads/msg/20543230dbd2484da41f88e08394a0d5.mp4', 'video/mp4', '2026-01-29 15:23:47', '2026-01-29 15:25:30', 0, 0, NULL),
(97, 25, 64, 'sqsqsqsqsqsqsqsqsqsqsqsqsq', NULL, NULL, '2026-03-11 12:06:35', '2026-03-11 12:06:58', 0, 0, NULL),
(98, 25, 64, 'test', NULL, NULL, '2026-03-11 12:08:17', NULL, 0, 0, NULL),
(99, 64, 54, 'sssssss', NULL, NULL, '2026-03-11 12:30:24', '2026-03-11 12:30:50', 0, 0, NULL),
(100, 49, 65, 'SSSSSSSSSSSSSSSS', NULL, NULL, '2026-03-11 14:29:53', NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `message_likes`
--

CREATE TABLE `message_likes` (
  `message_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `message_likes`
--

INSERT INTO `message_likes` (`message_id`, `user_id`, `created_at`) VALUES
(621, 67, '2026-03-13 14:59:10'),
(634, 66, '2026-03-16 16:20:58'),
(635, 66, '2026-03-18 13:56:56'),
(636, 66, '2026-03-19 15:24:09');

-- --------------------------------------------------------

--
-- Structure de la table `message_trash`
--

CREATE TABLE `message_trash` (
  `id` bigint(20) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `message_id` bigint(20) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `body` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `deleted_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `message_trash`
--

INSERT INTO `message_trash` (`id`, `owner_id`, `message_id`, `sender_id`, `recipient_id`, `body`, `image_path`, `deleted_at`, `created_at`) VALUES
(0, 25, 64, 42, 25, 'sssssssssssssss', NULL, '2026-01-04 20:02:36', '2025-11-19 09:12:58');

-- --------------------------------------------------------

--
-- Structure de la table `payout_requests`
--

CREATE TABLE `payout_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `creator_user_id` int(10) UNSIGNED NOT NULL,
  `amount_eur` decimal(10,2) NOT NULL,
  `paypal_email` varchar(255) NOT NULL,
  `status` enum('pending','approved','paid','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `reviewed_by_admin_id` int(10) UNSIGNED DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_ip` varchar(45) DEFAULT NULL,
  `created_user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `payout_requests`
--

INSERT INTO `payout_requests` (`id`, `creator_user_id`, `amount_eur`, `paypal_email`, `status`, `admin_note`, `payment_reference`, `reviewed_by_admin_id`, `requested_at`, `reviewed_at`, `paid_at`, `created_ip`, `created_user_agent`) VALUES
(1, 68, 29.50, 'f.facturehack@gmail.com', 'pending', NULL, NULL, NULL, '2026-03-24 09:22:20', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Structure de la table `project_images`
--

CREATE TABLE `project_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `original_name` varchar(180) NOT NULL,
  `mime` varchar(60) NOT NULL,
  `size` int(10) UNSIGNED NOT NULL,
  `width` int(10) UNSIGNED DEFAULT NULL,
  `height` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `project_images`
--

INSERT INTO `project_images` (`id`, `project_id`, `path`, `original_name`, `mime`, `size`, `width`, `height`, `created_at`) VALUES
(31, 24, '2025/11/91b080ef69ec6f02.png', 'image2.jpg', 'image/png', 3029692, 1536, 1024, '2025-11-10 09:09:14'),
(32, 28, '2025/11/a460e716dbe2ed6f.png', 'image2.jpg', 'image/png', 3029692, 1536, 1024, '2025-11-22 16:07:18'),
(35, 39, '2026/03/dbe40c2bd2beb6f3.jpg', 'chat_20251029_120139_19f8c38539af.jpg', 'image/jpeg', 10050, 259, 194, '2026-03-17 15:13:33'),
(37, 42, '2026/03/c01b580a74349414.png', '1d52d1c395ebe9a2295936e121a2a44d.png', 'image/png', 2994560, 1536, 1024, '2026-03-18 10:36:16'),
(38, 44, '2026/03/493cf92760c8b079.png', '1d52d1c395ebe9a2295936e121a2a44d.png', 'image/png', 2994560, 1536, 1024, '2026-03-18 11:45:47'),
(39, 44, '2026/03/d4e9114be54938a6.jpg', '0405bfcdf9138890131b2647ff30be20.jpg', 'image/jpeg', 10050, 259, 194, '2026-03-18 11:45:47'),
(40, 44, '2026/03/ae90b11a3a988ceb.jpg', 'chat_20251029_120203_38afb1bbde78.jpg', 'image/jpeg', 10050, 259, 194, '2026-03-18 11:45:47'),
(41, 45, '2026/03/abdcdcd119a4386a.jpg', '77ca7543246612129dc448b3e68625f2.jpg', 'image/jpeg', 10050, 259, 194, '2026-03-19 14:10:13'),
(42, 45, '2026/03/25f15eb980c095f0.jpg', 'chat_20251029_121209_f9745f7bc14e.jpg', 'image/jpeg', 10050, 259, 194, '2026-03-19 14:10:13'),
(43, 45, '2026/03/a3bfc4f598ce259e.jpg', 'chat_20251030_141201_9a647f9ebc74.jpg', 'image/jpeg', 8081, 259, 194, '2026-03-19 14:10:13');

-- --------------------------------------------------------

--
-- Structure de la table `project_tags`
--

CREATE TABLE `project_tags` (
  `project_id` int(10) UNSIGNED NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `project_tags`
--

INSERT INTO `project_tags` (`project_id`, `tag_id`) VALUES
(22, 17),
(23, 18),
(24, 19),
(25, 20),
(26, 21),
(27, 17),
(27, 22),
(28, 23),
(30, 25),
(33, 24),
(34, 24),
(39, 30),
(41, 32),
(42, 33),
(43, 24),
(44, 28),
(45, 34);

-- --------------------------------------------------------

--
-- Structure de la table `project_tips`
--

CREATE TABLE `project_tips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `from_user_id` int(10) UNSIGNED NOT NULL,
  `creator_user_id` int(10) UNSIGNED NOT NULL,
  `points_amount` int(10) UNSIGNED NOT NULL,
  `creator_amount_eur` decimal(10,2) NOT NULL,
  `platform_amount_eur` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `project_unlocks`
--

CREATE TABLE `project_unlocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `points_paid` int(10) UNSIGNED NOT NULL,
  `creator_amount_eur` decimal(10,2) NOT NULL,
  `platform_amount_eur` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `project_unlocks`
--

INSERT INTO `project_unlocks` (`id`, `project_id`, `user_id`, `points_paid`, `creator_amount_eur`, `platform_amount_eur`, `created_at`) VALUES
(1, 42, 66, 500, 17.50, 7.50, '2026-03-18 12:43:14'),
(2, 44, 66, 100, 3.50, 1.50, '2026-03-18 12:46:22'),
(3, 45, 66, 1000, 35.00, 15.00, '2026-03-19 15:11:15'),
(4, 46, 66, 100, 3.50, 1.50, '2026-03-20 14:51:13');

-- --------------------------------------------------------

--
-- Structure de la table `quiz_sexperf_results`
--

CREATE TABLE `quiz_sexperf_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `quiz_key` varchar(40) NOT NULL DEFAULT 'bon_coup_v1',
  `score_total` tinyint(3) UNSIGNED NOT NULL,
  `result_letter` enum('A','B','C') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `share_token` char(22) DEFAULT NULL,
  `share_enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `room_memberships`
--

CREATE TABLE `room_memberships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('active','expired','cancelled','revoked') NOT NULL DEFAULT 'active',
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tags`
--

CREATE TABLE `tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL,
  `slug` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tags`
--

INSERT INTO `tags` (`id`, `name`, `slug`) VALUES
(17, 'bug', 'bug'),
(18, 'qqqqqqq', 'qqqqqqq'),
(19, 'ssssssss', 'ssssssss'),
(20, 'ssssss', 'ssssss'),
(21, 'ddddd', 'ddddd'),
(22, 'signal', 'signal'),
(23, 'pmpm', 'pmpm'),
(24, 'ssss', 'ssss'),
(25, 'ccc', 'ccc'),
(26, 'CCSD', 'ccsd'),
(27, 'CCCC', 'cccc'),
(28, 'XXXX', 'xxxx'),
(29, 'xx', 'xx'),
(30, 'xxxxx', 'xxxxx'),
(31, 'oooo', 'oooo'),
(32, 'CCCCC', 'ccccc'),
(33, 'czzzzzzzzz', 'czzzzzzzzz'),
(34, 'test', 'test'),
(35, 'b2oba', 'b2oba');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `pseudo` varchar(30) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `sex` enum('homme','femme') DEFAULT NULL,
  `height_cm` smallint(5) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `password_hash` varchar(255) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `relationship_status` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `pseudo`, `avatar_url`, `birthdate`, `bio`, `sex`, `height_cm`, `created_at`, `password_hash`, `postal_code`, `city`, `relationship_status`) VALUES
(66, 'Rémi85', 'uploads/avatars/avatar_66_1774005543.jpg', NULL, NULL, NULL, NULL, '2026-03-13 12:27:21', '$2y$10$L7oKBdictU9EEsjP2YEUEugWMsSTwkYluOTltNfMn.DsUwqhY/QOO', NULL, NULL, NULL),
(67, 'tonton', NULL, NULL, NULL, NULL, NULL, '2026-03-13 14:58:56', '$2y$10$OHO8MCqaeWaOq0A7dfhl0OsFPyz.nzDTa/cseuTya/WwTWOEp1j26', NULL, NULL, NULL),
(68, 'balancetonporc', NULL, NULL, NULL, NULL, NULL, '2026-03-18 11:20:54', '$2y$10$VJuIObP2zAz0nfBBD8nJcOe2dsjAeBvhN6dJMIcFgQOIwXZTuHnuS', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user_points_transactions`
--

CREATE TABLE `user_points_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` enum('purchase','room_access','refund','bonus','admin_credit','admin_debit','project_unlock') NOT NULL,
  `points_delta` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `amount_eur` decimal(10,2) DEFAULT NULL,
  `related_room_id` int(10) UNSIGNED DEFAULT NULL,
  `related_pack_id` int(10) UNSIGNED DEFAULT NULL,
  `related_payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_points_transactions`
--

INSERT INTO `user_points_transactions` (`id`, `user_id`, `type`, `points_delta`, `balance_after`, `amount_eur`, `related_room_id`, `related_pack_id`, `related_payment_id`, `note`, `created_at`) VALUES
(1, 66, 'admin_credit', 1000, 1000, NULL, NULL, NULL, NULL, 'Crédit test dev', '2026-03-18 12:37:54'),
(2, 66, 'project_unlock', -500, 999500, 25.00, 42, NULL, NULL, 'Déverrouillage projet #42', '2026-03-18 12:43:14'),
(3, 66, 'project_unlock', -100, 999400, 5.00, 44, NULL, NULL, 'Déverrouillage projet #44', '2026-03-18 12:46:22'),
(4, 66, 'project_unlock', -1000, 998400, 50.00, 45, NULL, NULL, 'Déverrouillage projet #45', '2026-03-19 15:11:15'),
(5, 66, 'project_unlock', -100, 998300, 5.00, 46, NULL, NULL, 'Déverrouillage projet #46', '2026-03-20 14:51:13');

-- --------------------------------------------------------

--
-- Structure de la table `user_points_wallet`
--

CREATE TABLE `user_points_wallet` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `balance_points` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_points_wallet`
--

INSERT INTO `user_points_wallet` (`user_id`, `balance_points`, `updated_at`) VALUES
(66, 998300, '2026-03-20 14:51:13');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `chat_dm_typing`
--
ALTER TABLE `chat_dm_typing`
  ADD PRIMARY KEY (`user_id`,`peer_id`),
  ADD KEY `idx_peer` (`peer_id`,`last_ping`);

--
-- Index pour la table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room_id` (`room_id`),
  ADD KEY `fk_chat_messages_sender` (`sender_id`),
  ADD KEY `idx_room_created_3` (`room_id`,`created_at`,`id`),
  ADD KEY `idx_msgs_sender_time` (`sender_id`,`created_at`),
  ADD KEY `idx_msgs_room_sender_time` (`room_id`,`sender_id`,`created_at`),
  ADD KEY `idx_msg_likecount` (`room_id`,`id`,`like_count`);

--
-- Index pour la table `chat_messages_archive`
--
ALTER TABLE `chat_messages_archive`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_original_message_id` (`original_message_id`),
  ADD KEY `idx_archive_room` (`room_id`),
  ADD KEY `idx_archive_archived_at` (`archived_at`),
  ADD KEY `idx_archive_sender` (`sender_id`);

--
-- Index pour la table `chat_notifications`
--
ALTER TABLE `chat_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`,`created_at`),
  ADD KEY `idx_room` (`room_id`),
  ADD KEY `idx_message` (`message_id`);

--
-- Index pour la table `chat_presence`
--
ALTER TABLE `chat_presence`
  ADD PRIMARY KEY (`session_key`),
  ADD KEY `room_seen` (`room_id`,`last_seen`),
  ADD KEY `user_seen` (`user_id`,`last_seen`);

--
-- Index pour la table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chat_rooms_created_by` (`created_by`),
  ADD KEY `idx_created_by_created_at` (`created_by`,`created_at`),
  ADD KEY `idx_room_priv` (`is_private`),
  ADD KEY `idx_rooms_expires_at` (`expires_at`);

--
-- Index pour la table `chat_typing`
--
ALTER TABLE `chat_typing`
  ADD PRIMARY KEY (`room_id`,`user_id`),
  ADD KEY `idx_last_typing` (`last_typing_at`);

--
-- Index pour la table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_insee_postal` (`insee_code`,`postal_code`),
  ADD KEY `idx_postal` (`postal_code`),
  ADD KEY `idx_city` (`city`);

--
-- Index pour la table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_created` (`project_id`,`created_at`),
  ADD KEY `fk_comment_user` (`author_id`);

--
-- Index pour la table `creator_payout_requests`
--
ALTER TABLE `creator_payout_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cpr_creator` (`creator_user_id`),
  ADD KEY `idx_cpr_status` (`status`);

--
-- Index pour la table `creator_wallets`
--
ALTER TABLE `creator_wallets`
  ADD PRIMARY KEY (`user_id`);

--
-- Index pour la table `creator_wallet_transactions`
--
ALTER TABLE `creator_wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cwt_creator` (`creator_user_id`),
  ADD KEY `idx_cwt_type` (`type`);

--
-- Index pour la table `ip_bans`
--
ALTER TABLE `ip_bans`
  ADD PRIMARY KEY (`ip`),
  ADD KEY `idx_until` (`until`);

--
-- Index pour la table `law_projects`
--
ALTER TABLE `law_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_published` (`status`,`published_at`),
  ADD KEY `idx_author` (`author_id`),
  ADD KEY `idx_lp_status_pub` (`status`,`published_at`),
  ADD KEY `idx_lp_author_status_pub` (`author_id`,`status`,`published_at`);

--
-- Index pour la table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_project` (`user_id`,`project_id`),
  ADD UNIQUE KEY `uq_likes_project_user` (`project_id`,`user_id`),
  ADD KEY `idx_project` (`project_id`),
  ADD KEY `idx_likes_project` (`project_id`);

--
-- Index pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_created` (`ip`,`created_at`),
  ADD KEY `idx_pseudo_created` (`pseudo`,`created_at`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipient_id` (`recipient_id`,`read_at`),
  ADD KEY `sender_id` (`sender_id`,`created_at`),
  ADD KEY `msg_vis_idx` (`sender_id`,`recipient_id`,`deleted_by_sender`,`deleted_by_recipient`,`created_at`);

--
-- Index pour la table `message_likes`
--
ALTER TABLE `message_likes`
  ADD PRIMARY KEY (`message_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `message_trash`
--
ALTER TABLE `message_trash`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `deleted_at` (`deleted_at`),
  ADD KEY `message_id` (`message_id`);

--
-- Index pour la table `payout_requests`
--
ALTER TABLE `payout_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_creator_status` (`creator_user_id`,`status`),
  ADD KEY `idx_status_requested_at` (`status`,`requested_at`),
  ADD KEY `fk_payout_admin` (`reviewed_by_admin_id`);

--
-- Index pour la table `project_images`
--
ALTER TABLE `project_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project` (`project_id`);

--
-- Index pour la table `project_tags`
--
ALTER TABLE `project_tags`
  ADD PRIMARY KEY (`project_id`,`tag_id`),
  ADD KEY `idx_tag` (`tag_id`),
  ADD KEY `idx_pt_project` (`project_id`),
  ADD KEY `idx_pt_tag` (`tag_id`);

--
-- Index pour la table `project_tips`
--
ALTER TABLE `project_tips`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `project_unlocks`
--
ALTER TABLE `project_unlocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_project_user` (`project_id`,`user_id`);

--
-- Index pour la table `quiz_sexperf_results`
--
ALTER TABLE `quiz_sexperf_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_qsr_share_token` (`share_token`),
  ADD KEY `idx_qsr_user` (`user_id`),
  ADD KEY `idx_qsr_quiz_key` (`quiz_key`),
  ADD KEY `idx_qsr_created_at` (`created_at`),
  ADD KEY `idx_user_quiz_created` (`user_id`,`quiz_key`,`created_at`),
  ADD KEY `idx_qsr_user_quiz_created` (`user_id`,`quiz_key`,`created_at`);

--
-- Index pour la table `room_memberships`
--
ALTER TABLE `room_memberships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_room_user_period` (`room_id`,`user_id`,`starts_at`),
  ADD KEY `idx_rm_room` (`room_id`),
  ADD KEY `idx_rm_user` (`user_id`),
  ADD KEY `idx_rm_status` (`status`);

--
-- Index pour la table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_slug` (`slug`),
  ADD KEY `idx_name` (`name`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_pseudo` (`pseudo`),
  ADD KEY `idx_users_sex` (`sex`),
  ADD KEY `idx_users_height` (`height_cm`),
  ADD KEY `idx_user_postal` (`postal_code`),
  ADD KEY `idx_user_city` (`city`);

--
-- Index pour la table `user_points_transactions`
--
ALTER TABLE `user_points_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_upt_user` (`user_id`),
  ADD KEY `idx_upt_type` (`type`),
  ADD KEY `idx_upt_room` (`related_room_id`);

--
-- Index pour la table `user_points_wallet`
--
ALTER TABLE `user_points_wallet`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=639;

--
-- AUTO_INCREMENT pour la table `chat_messages_archive`
--
ALTER TABLE `chat_messages_archive`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `chat_notifications`
--
ALTER TABLE `chat_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT pour la table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `creator_payout_requests`
--
ALTER TABLE `creator_payout_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `creator_wallet_transactions`
--
ALTER TABLE `creator_wallet_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `law_projects`
--
ALTER TABLE `law_projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=256;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT pour la table `payout_requests`
--
ALTER TABLE `payout_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `project_images`
--
ALTER TABLE `project_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT pour la table `project_tips`
--
ALTER TABLE `project_tips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `project_unlocks`
--
ALTER TABLE `project_unlocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `quiz_sexperf_results`
--
ALTER TABLE `quiz_sexperf_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `room_memberships`
--
ALTER TABLE `room_memberships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT pour la table `user_points_transactions`
--
ALTER TABLE `user_points_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_msg_room` FOREIGN KEY (`room_id`) REFERENCES `chat_rooms` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `creator_payout_requests`
--
ALTER TABLE `creator_payout_requests`
  ADD CONSTRAINT `fk_cpr_user` FOREIGN KEY (`creator_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `creator_wallets`
--
ALTER TABLE `creator_wallets`
  ADD CONSTRAINT `fk_cw_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `creator_wallet_transactions`
--
ALTER TABLE `creator_wallet_transactions`
  ADD CONSTRAINT `fk_cwt_user` FOREIGN KEY (`creator_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `message_likes`
--
ALTER TABLE `message_likes`
  ADD CONSTRAINT `fk_ml_msg` FOREIGN KEY (`message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ml_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `payout_requests`
--
ALTER TABLE `payout_requests`
  ADD CONSTRAINT `fk_payout_admin` FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_payout_creator` FOREIGN KEY (`creator_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `project_images`
--
ALTER TABLE `project_images`
  ADD CONSTRAINT `fk_pi_project` FOREIGN KEY (`project_id`) REFERENCES `law_projects` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `quiz_sexperf_results`
--
ALTER TABLE `quiz_sexperf_results`
  ADD CONSTRAINT `fk_qsr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `room_memberships`
--
ALTER TABLE `room_memberships`
  ADD CONSTRAINT `fk_rm_room` FOREIGN KEY (`room_id`) REFERENCES `chat_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_points_transactions`
--
ALTER TABLE `user_points_transactions`
  ADD CONSTRAINT `fk_upt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_points_wallet`
--
ALTER TABLE `user_points_wallet`
  ADD CONSTRAINT `fk_upw_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Évènements
--
CREATE DEFINER=`root`@`localhost` EVENT `ev_delete_ephemeral_rooms` ON SCHEDULE EVERY 10 MINUTE STARTS '2026-01-07 15:43:23' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM chat_rooms
  WHERE is_ephemeral = 1
    AND expires_at IS NOT NULL
    AND expires_at <= NOW()$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_archive_ephemeral_rooms` ON SCHEDULE EVERY 10 MINUTE STARTS '2026-01-07 14:34:22' ON COMPLETION NOT PRESERVE ENABLE DO INSERT IGNORE INTO chat_messages_archive
    (original_message_id, room_id, sender_id, body, created_at, is_system, like_count)
  SELECT
    m.id, m.room_id, m.sender_id, m.body, m.created_at, m.is_system, m.like_count
  FROM chat_messages m
  INNER JOIN chat_rooms r ON r.id = m.room_id
  WHERE r.is_ephemeral = 1
    AND r.expires_at IS NOT NULL
    AND r.expires_at <= NOW()$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_purge_archives_72h` ON SCHEDULE EVERY 1 HOUR STARTS '2026-01-07 14:38:39' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM chat_messages_archive
  WHERE archived_at <= NOW() - INTERVAL 72 HOUR$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
