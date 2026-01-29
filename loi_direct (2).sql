-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 07 jan. 2026 à 12:22
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
  `like_count` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `room_id`, `sender_id`, `body`, `created_at`, `file_url`, `file_mime`, `file_w`, `file_h`, `color`, `like_count`) VALUES
(211, 48, 24, 'Le site est encore en phase de développement.    💬 Ton avis compte ! Tu as repéré un bug ? Une idée pour rendre le site plus cool ? Poste-la ici !  ➕ Améliorations ⚙️ Bugs / dysfonctionnements 💡 Nouvelles fonctionnalités  Chaque retour est lu et pris en compte.', '2025-11-22 18:11:43', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(299, 48, 47, 'Bonjour, à tous', '2025-11-22 11:08:31', NULL, NULL, NULL, NULL, '#FF0505', 0),
(300, 48, 47, 'coucou', '2025-11-22 11:10:32', NULL, NULL, NULL, NULL, '#0011FF', 0),
(301, 48, 47, 'sssssssss', '2025-11-22 11:13:10', NULL, NULL, NULL, NULL, '#FF0000', 0),
(302, 48, 47, 'ssssssssssssssssssssssssasaxa', '2025-11-22 11:13:49', NULL, NULL, NULL, NULL, '#2E93FF', 0),
(303, 48, 47, 'ssssssssssssssssssss', '2025-11-22 11:17:32', NULL, NULL, NULL, NULL, '#FF990A', 1),
(304, 48, 47, '@frejkhif rvrvrvrvrvrvrvrvrvrvrvrvrvrvrvz\"', '2025-11-22 11:18:30', NULL, NULL, NULL, NULL, '#FFFFFF', 1),
(305, 48, 47, 'sssssssss', '2025-11-22 11:26:29', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(306, 48, 47, 'sssssssssssss', '2025-11-22 11:26:32', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(310, 68, 24, 'Bonjour.', '2025-11-22 11:31:46', NULL, NULL, NULL, NULL, '#FFFFFF', 1),
(311, 69, 25, 'cseeeeeeeeeeeeee', '2025-11-22 12:32:44', NULL, NULL, NULL, NULL, '#FFFFFF', 1),
(312, 69, 25, 'eeeeeeeeeeeeeee', '2025-11-22 12:32:45', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(313, 70, 48, 'scccccccccccccccccc', '2025-11-22 12:34:50', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(314, 70, 48, 'cccccccccccccccccccc', '2025-11-22 12:34:51', NULL, NULL, NULL, NULL, '#FFFFFF', 1),
(315, 70, 48, 'cccccccccccccccxxxcccccccccccc', '2025-11-22 12:34:56', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(316, 71, 49, 'x', '2025-11-22 12:37:11', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(317, 71, 49, 'j', '2025-11-22 12:37:13', NULL, NULL, NULL, NULL, '#FFFFFF', 1),
(318, 71, 49, 'g', '2025-11-22 12:37:18', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(319, 71, 24, '@toto2 coucou', '2025-11-22 12:38:02', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(320, 71, 24, '', '2025-11-22 12:38:11', '/loi/uploads/chat_20251121_123811_767d32fbbb6a.jpg', 'image/jpeg', 1280, 853, '#FFFFFF', 0),
(321, 71, 50, 'axssssssssssssssssssssssssssssaxssssssssssssssssssssssssssssaxssssssssssssssssssssssssssssaxssssssssssssssssssssssssssssaxssssssssssssssssssssssssssssaxssssssssssssssssssssssssssssaxssssssssssssssssss', '2025-11-22 12:49:56', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(322, 71, 49, '', '2025-11-22 13:40:38', '/loi/uploads/chat_20251121_134038_261587b18dac.jpg', 'image/jpeg', 424, 300, '#FFFFFF', 0),
(323, 48, 52, 'sdsdsdsdsdsdsd', '2025-11-22 10:09:55', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(324, 71, 25, 't\'es la ?', '2025-11-22 16:45:30', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(329, 70, 25, 'khghjihi', '2025-11-22 17:50:53', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(330, 75, 54, 'fg rfgf', '2025-11-22 18:13:17', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(331, 75, 25, 'Portugal !', '2025-11-23 09:28:12', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(332, 75, 25, 'coucou', '2025-11-23 09:53:10', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(333, 75, 25, 'erererererererererererer', '2025-11-23 09:56:00', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(334, 75, 25, 'ssssssssss', '2025-11-23 10:02:33', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(335, 75, 25, '@Rémi85 slip de guerre', '2025-11-23 10:02:56', NULL, NULL, NULL, NULL, '#FFFFFF', 1),
(336, 75, 25, '', '2025-11-23 10:03:24', '/loi/uploads/chat_20251123_100324_e00bdc25639f.jpg', 'image/jpeg', 424, 300, '#FFFFFF', 0),
(337, 71, 25, 'zccssssssssssss', '2025-11-23 10:10:12', NULL, NULL, NULL, NULL, '#FFFFFF', 1),
(338, 71, 25, 'dvzzzzzzzzzzzzzzzzzzzzzzz', '2025-11-23 10:11:02', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(341, 71, 25, 'sdsdsdsdsdsdsdsdsdsdsdsd', '2025-11-25 09:42:24', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(342, 71, 25, 'sssssssss', '2025-11-25 09:42:27', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(343, 71, 25, 'Skip to main content Skip to search Auth0 by Okta You asked, we delivered! Our Free Plan now includes a Custom Domain, 5 Actions, and 25,000 MAUs. Sign up now → Publicité Vous ne voulez pas voir de pu', '2025-11-25 09:48:18', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(344, 75, 25, 'Skip to main content Skip to search Auth0 by Okta You asked, we delivered! Our Free Plan now includes a Custom Domain, 5 Actions, and 25,000 MAUs. Sign up now → Publicité Vous ne voulez pas voir de publicités ?  HTML  CSS  JavaScript  Web APIs  All  Learn  Tools  About Blog  Se connecter Web CSS Référence CSS Propriétés CSS overflow-wrap  Theme  Français Cette page a été traduite à partir de l\'anglais par la communauté. Vous pouvez contribuer en rejoignant la communauté francophone sur MDN Web Docs.  View in English   Always switch to English  overflow-wrap Baseline Widely available La propriété overflow-wrap s\'applique aux éléments en ligne (inline) et est utilisée afin de définir si le navigateur peut ou non faire la césure à l\'intérieur d\'un mot pour éviter le dépassement d\'une chaîne qui serait trop longue afin qu\'elle ne dépasse pas de la boîte.  Dans cet article Exemple interactif Syntaxe Définition formelle Syntaxe formelle Exemples Spécifications Compatibilité des navigateurs Voir aussi Exemple interactif CSS Demo: overflow-wrap  Reset overflow-wrap: normal; overflow-wrap: anywhere; overflow-wrap: break-word;  Note : À la différence de word-break, overflow-wrap créera uniquement un saut de ligne si un mot entier ne peut pas être placé sur sa propre ligne sans dépasser.  À l\'origine, cette propriété était une extension non-standard sans préfixe de Microsoft et intitulée word-wrap. Implémentée sous ce nom par la plupart des navigateurs depuis, elle a été renommée en overflow-wrap et word-wrap est devenu un alias.  Syntaxe css  Copy /* Avec un mot-clé */ overflow-wrap: normal; overflow-wrap: break-word; overflow-wrap: anywhere;  /* Valeurs globales */ overflow-wrap: inherit; overflow-wrap: initial; overflow-wrap: unset; La propriété overflow-wrap peut être définie avec l\'un des mots-clés suivants.  Valeurs normal Indique que la césure d\'une ligne ne peut avoir lieu qu\'aux positions de césures normales.  anywhere Indique que la césure pourra avoir lieu afin d\'év', '2025-11-25 09:49:28', NULL, NULL, NULL, NULL, '#FFFFFF', 1),
(345, 75, 25, 'szadzzzdzdzdzdzdzdzdzdzdzdzdzzdz', '2025-11-25 10:09:09', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(346, 75, 25, 'sssssssssssss', '2025-11-25 11:18:21', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(347, 75, 25, 'd', '2025-11-25 13:50:37', NULL, NULL, NULL, NULL, '#FFFFFF', 1),
(348, 75, 25, 'ssssssssss', '2025-11-25 15:13:44', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(349, 75, 25, 'cccccc', '2025-11-25 15:16:12', NULL, NULL, NULL, NULL, '#D21E1E', 0),
(350, 75, 25, 's', '2025-11-25 15:38:41', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(351, 75, 25, 'sxd', '2025-12-01 16:48:50', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(352, 75, 25, 'zxzx', '2025-12-01 16:48:51', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(353, 75, 25, 'zxzxxzxzxxz', '2025-12-01 16:49:07', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(354, 71, 25, 'b', '2025-12-01 16:49:41', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(355, 71, 25, 's', '2025-12-01 16:49:44', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(356, 71, 25, 'dddddddddddddddd', '2025-12-02 09:25:49', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(358, 71, 30, 'xxxxxx', '2025-12-15 19:45:45', NULL, NULL, NULL, NULL, '#FFFFFF', 1),
(359, 71, 25, 'ssssssssss', '2026-01-01 15:02:49', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(365, 71, 25, 'ssssssssss', '2026-01-04 08:45:39', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(366, 75, 25, 'ytttttttttttt', '2026-01-04 09:28:59', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(367, 75, 25, 'iuiiiiiii', '2026-01-04 09:29:07', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(368, 75, 25, 'test 01', '2026-01-04 09:29:23', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(369, 75, 25, 'jjjjjj', '2026-01-04 09:33:24', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(370, 75, 25, 'yo', '2026-01-04 09:34:51', NULL, NULL, NULL, NULL, '#FFFFFF', 0),
(371, 75, 25, 't\'es la .', '2026-01-04 09:35:34', NULL, NULL, NULL, NULL, '#000000', 1),
(372, 75, 25, 'ujujujujujuj', '2026-01-04 09:35:39', NULL, NULL, NULL, NULL, '#FF2E2E', 1),
(373, 75, 25, 'egffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffftb gggggggee', '2026-01-04 17:18:09', '/loi/uploads/chat_20260104_171809_e051a133466b.jpg', 'image/jpeg', 251, 201, '#000000', 1),
(374, 75, 25, 'kii', '2026-01-06 09:49:57', NULL, NULL, NULL, NULL, '#000000', 0);

-- --------------------------------------------------------

--
-- Structure de la table `chat_presence`
--

CREATE TABLE `chat_presence` (
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_key` char(36) NOT NULL,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_presence`
--

INSERT INTO `chat_presence` (`room_id`, `user_id`, `session_key`, `last_seen`) VALUES
(49, 24, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-15 17:06:54'),
(64, 24, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 09:32:51'),
(65, 24, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 09:33:12'),
(63, 24, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 09:33:43'),
(67, 24, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-21 10:31:07'),
(51, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-15 17:19:46'),
(53, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-18 19:54:55'),
(50, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 08:12:02'),
(70, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-23 08:23:41'),
(69, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-23 08:23:44'),
(72, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-23 08:23:49'),
(73, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-23 08:23:51'),
(68, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-24 16:24:25'),
(76, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-24 16:25:16'),
(77, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-30 07:00:05'),
(79, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-12-01 15:49:55'),
(75, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-12-01 16:02:18'),
(48, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-12-01 16:33:51'),
(71, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-12-02 15:04:30'),
(80, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-12-02 15:04:39'),
(0, 25, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-12-02 15:13:30'),
(72, 25, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-02 09:48:36'),
(88, 25, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-03 10:35:20'),
(73, 25, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-04 07:12:52'),
(69, 25, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-04 07:18:38'),
(68, 25, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-04 07:21:46'),
(48, 25, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-04 07:42:44'),
(70, 25, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-04 16:13:40'),
(71, 25, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-06 08:20:59'),
(81, 25, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-06 08:21:08'),
(75, 25, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-06 08:54:25'),
(83, 33, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-02 11:16:54'),
(92, 33, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-07 10:41:55'),
(84, 34, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-02 11:18:32'),
(82, 35, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-02 11:12:40'),
(93, 35, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-07 10:43:56'),
(0, 35, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-07 11:22:35'),
(85, 38, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-02 11:19:48'),
(54, 41, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-18 19:55:46'),
(86, 41, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-02 11:20:37'),
(87, 41, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-02 11:36:51'),
(55, 42, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 08:12:48'),
(52, 42, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 08:37:02'),
(56, 42, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 08:41:47'),
(58, 43, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 08:57:06'),
(60, 45, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 08:58:49'),
(59, 45, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 09:25:30'),
(61, 45, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 09:25:41'),
(62, 45, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 09:25:45'),
(57, 46, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 09:34:19'),
(66, 47, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-19 09:36:22'),
(90, 48, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-07 10:24:51'),
(91, 48, '8e76d498-159c-4995-bc19-2e2f660d45a4', '2026-01-07 10:30:08'),
(74, 53, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-22 15:59:52'),
(78, 55, 'bbbaac82-e658-4e32-b2fc-bf203252cdec', '2025-11-30 07:01:32');

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
(48, 'Boîte à idées & Sign', 0, NULL, 0, 0, 24, '2025-11-07 18:10:53', NULL),
(68, 'Voiture à vendre', 0, NULL, 0, 0, 24, '2025-11-21 11:31:41', NULL),
(69, 'cherche emploi', 0, NULL, 0, 0, 25, '2025-11-21 12:32:29', NULL),
(70, 'autour de moi', 0, NULL, 0, 0, 48, '2025-11-21 12:34:33', NULL),
(71, 'guitare', 0, NULL, 0, 0, 49, '2025-11-21 12:36:12', NULL),
(72, 'cours de piano', 0, NULL, 0, 0, 51, '2025-11-22 09:29:52', NULL),
(73, 'zee', 0, NULL, 0, 0, 52, '2025-11-22 10:09:41', NULL),
(75, 'edcdcve', 0, NULL, 0, 0, 54, '2025-11-22 18:13:13', NULL),
(81, 'xazsxzsxzsxzsx', 0, NULL, 0, 0, 25, '2026-01-01 15:02:55', NULL),
(88, 'Salon protéger', 0, '$2y$10$PImRau7DHUHJFbHvAiX7IuR54obqkm2eGFneTACZVTC2ebGru5Z1u', 1, 0, 25, '2026-01-03 11:25:21', NULL),
(89, 'xdddddddd', 0, NULL, 0, 0, 25, '2026-01-07 10:44:31', NULL);

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
(80, 25, '2025-12-02 16:04:36'),
(71, 30, '2025-12-15 19:45:44'),
(82, 35, '2026-01-02 12:12:05'),
(84, 34, '2026-01-02 12:18:28'),
(85, 38, '2026-01-02 12:19:41'),
(86, 41, '2026-01-02 12:20:26'),
(71, 25, '2026-01-04 08:45:38'),
(75, 25, '2026-01-06 09:50:43'),
(90, 48, '2026-01-07 11:24:42'),
(91, 48, '2026-01-07 11:29:47'),
(93, 35, '2026-01-07 11:43:31');

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

-- --------------------------------------------------------

--
-- Structure de la table `ip_bans`
--

CREATE TABLE `ip_bans` (
  `ip` varchar(45) NOT NULL,
  `until` datetime NOT NULL,
  `reason` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `law_projects`
--

INSERT INTO `law_projects` (`id`, `author_id`, `title`, `summary`, `body_markdown`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(22, 24, '🎯 Bienvenue dans la Boîte à idées & Signalements', '💬 Ton avis compte !', '🧠 Espace feedback technique\r\nCe salon sert à centraliser les retours utilisateurs :\r\n\r\nSignalement de bugs (avec capture si possible)\r\n\r\nIdées d’amélioration\r\n\r\nSuggestions d’ergonomie ou de design\r\n\r\nLes messages utiles seront tagués À corriger ou À étudier.', 'published', '2025-11-07 18:14:02', '2025-11-07 18:14:02', '2025-11-07 18:14:02'),
(23, 24, 'qqqqqqqqqqqq', 'qqqqqqqqqqqqqqqqq', 'qqqqqqqq', 'published', '2025-11-09 16:36:39', '2025-11-09 16:36:39', '2025-11-09 16:36:39'),
(24, 33, 'sssssssss', 'sssssssssssss', 'ssssssss', 'published', '2025-11-10 10:09:14', '2025-11-10 10:09:14', '2025-11-10 10:09:14'),
(25, 36, 'sssssssssssssssssasx', 'ssssssssssssss', 'sssssssssssssssa', 'published', '2025-11-10 15:03:57', '2025-11-10 15:03:57', '2025-11-10 15:03:57'),
(26, 37, 'dddddddddddddddd', 'ddddddddddddddd', 'ddddddddd', 'published', '2025-11-10 15:21:13', '2025-11-10 15:21:13', '2025-11-10 15:21:13'),
(27, 24, '💬 Ton avis compte !', 'Tu as repéré un bug ? Une idée pour rendre le site plus cool ?\r\nPoste-la ici !', '➕ Améliorations\r\n⚙️ Bugs / dysfonctionnements\r\n💡 Nouvelles fonctionnalités\r\n\r\nChaque retour est lu et pris en compte.', 'published', '2025-11-11 11:55:18', '2025-11-11 11:55:18', '2025-11-11 11:55:18'),
(28, 25, 'mmmmmmmmmmmmmm', 'mmmmmmmmmmmmmm', 'mmmmmmmmmmmmm', 'published', '2025-11-22 17:07:18', '2025-11-22 17:07:18', '2025-11-22 17:07:18');

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
(37, 0x3a3a31, 'Rémi fenech 77', 1, '2025-11-08 17:14:29'),
(38, 0x3a3a31, 'phiL du 92', 1, '2025-11-08 17:26:58'),
(39, 0x3a3a31, 'Rémi77.t', 1, '2025-11-09 09:09:34'),
(40, 0x3a3a31, 'fiona.77', 1, '2025-11-09 09:41:19'),
(41, 0x3a3a31, 'remi_admin', 1, '2025-11-09 14:45:38'),
(42, 0x3a3a31, 'remi_admin', 1, '2025-11-09 14:46:10'),
(43, 0x3a3a31, 'remi_admin', 1, '2025-11-09 14:46:44'),
(44, 0x3a3a31, 'remi_admin', 1, '2025-11-09 14:47:01'),
(45, 0x3a3a31, 'remi_admin', 1, '2025-11-09 14:48:03'),
(46, 0x3a3a31, 'remi_admin', 1, '2025-11-09 14:48:20'),
(47, 0x3a3a31, 'remi_admin', 1, '2025-11-09 15:24:01'),
(48, 0x3a3a31, 'remi_admin', 1, '2025-11-09 15:29:03'),
(49, 0x3a3a31, 'remi77', 0, '2025-11-10 08:25:15'),
(50, 0x3a3a31, 'flute', 1, '2025-11-10 08:25:29'),
(51, 0x3a3a31, 'remi77', 0, '2025-11-10 14:03:13'),
(52, 0x3a3a31, 'Rémi_admin', 1, '2025-11-11 10:54:28'),
(53, 0x3a3a31, 'remi_admin', 1, '2025-11-11 16:46:36'),
(54, 0x3a3a31, 'remi_admin', 1, '2025-11-11 17:04:29'),
(55, 0x3a3a31, 'remi_admin', 1, '2025-11-12 12:20:44'),
(56, 0x3a3a31, 'remi85', 1, '2025-11-15 09:32:17'),
(57, 0x3a3a31, 'remi85', 1, '2025-11-15 09:50:30'),
(58, 0x3a3a31, 'remi_admin', 1, '2025-11-15 16:51:31'),
(59, 0x3a3a31, 'remi_admin', 1, '2025-11-15 17:06:41'),
(60, 0x3a3a31, 'remi85', 1, '2025-11-15 17:18:38'),
(61, 0x3a3a31, 'remi85', 1, '2025-11-16 09:34:57'),
(62, 0x3a3a31, 'remi85', 1, '2025-11-16 09:38:57'),
(63, 0x3a3a31, 'remi85', 1, '2025-11-16 16:10:26'),
(64, 0x3a3a31, 'remi85', 1, '2025-11-18 08:35:15'),
(65, 0x3a3a31, 'remi85', 1, '2025-11-18 18:43:31'),
(66, 0x3a3a31, 'remi85', 1, '2025-11-19 08:11:31'),
(67, 0x3a3a31, 'remi_admin', 1, '2025-11-19 09:26:29'),
(68, 0x3a3a31, 'remi_admin', 1, '2025-11-19 09:34:32'),
(69, 0x3a3a31, 'remi85', 1, '2025-11-20 11:29:00'),
(70, 0x3a3a31, 'remi85', 1, '2025-11-20 14:41:36'),
(71, 0x3a3a31, 'frejkhif', 1, '2025-11-21 10:08:13'),
(72, 0x3a3a31, 'remi_admin', 1, '2025-11-21 10:30:55'),
(73, 0x3a3a31, 'Rémi85', 1, '2025-11-21 11:31:30'),
(74, 0x3a3a31, 'toto2', 1, '2025-11-21 11:37:07'),
(75, 0x3a3a31, 'remi_admin', 1, '2025-11-21 11:37:49'),
(76, 0x3a3a31, 'toto2', 1, '2025-11-21 12:40:30'),
(77, 0x3a3a31, 'remi85', 1, '2025-11-22 08:29:05'),
(78, 0x3a3a31, 'remi85', 1, '2025-11-22 15:45:19'),
(79, 0x3a3a31, 'remi85', 1, '2025-11-22 15:58:25'),
(80, 0x3a3a31, 'dfefefer', 1, '2025-11-22 15:59:45'),
(81, 0x3a3a31, 'remi85', 1, '2025-11-22 16:06:19'),
(82, 0x3a3a31, 'remi85', 1, '2025-11-22 16:50:47'),
(83, 0x3a3a31, 'admin', 0, '2025-11-23 08:23:25'),
(84, 0x3a3a31, 'remi85', 1, '2025-11-23 08:23:33'),
(85, 0x3a3a31, 'remi85', 1, '2025-11-23 08:52:43'),
(86, 0x3a3a31, 'remi85', 1, '2025-11-23 08:55:55'),
(87, 0x3a3a31, 'remi85', 1, '2025-11-23 09:01:58'),
(88, 0x3a3a31, 'remi85', 1, '2025-11-23 09:02:30'),
(89, 0x3a3a31, 'remi85', 1, '2025-11-23 09:10:05'),
(90, 0x3a3a31, 'remi85', 1, '2025-11-23 09:10:55'),
(91, 0x3a3a31, 'remi85', 1, '2025-11-24 15:08:55'),
(92, 0x3a3a31, 'remi85', 1, '2025-11-25 08:00:36'),
(93, 0x3a3a31, 'admin', 0, '2025-11-25 09:52:39'),
(94, 0x3a3a31, 'remi85', 1, '2025-11-25 09:53:17'),
(95, 0x3a3a31, 'remi85', 1, '2025-11-26 10:00:44'),
(96, 0x3a3a31, 'remi85', 1, '2025-11-29 09:49:22'),
(97, 0x3a3a31, 'remi85', 1, '2025-11-30 06:59:29'),
(98, 0x3a3a31, 'remi85', 1, '2025-12-01 15:41:22'),
(99, 0x3a3a31, 'admin', 0, '2025-12-15 18:22:55'),
(100, 0x3a3a31, 'remi77', 0, '2025-12-15 18:23:03'),
(101, 0x3a3a31, 'Rémi', 1, '2025-12-15 18:23:58'),
(102, 0x3a3a31, 'admin', 0, '2025-12-27 09:23:01'),
(103, 0x3a3a31, 'remi_admin', 1, '2025-12-27 09:23:42'),
(104, 0x3a3a31, 'remi_admin', 1, '2025-12-27 15:03:08'),
(105, 0x3a3a31, 'Rémi85', 1, '2025-12-27 15:41:34'),
(106, 0x3a3a31, 'vendeur', 1, '2025-12-27 15:55:58'),
(107, 0x3a3a31, 'admin01', 1, '2025-12-27 16:02:28'),
(108, 0x3a3a31, 'admin55', 1, '2025-12-27 16:06:58'),
(109, 0x3a3a31, 'frejkhif', 1, '2025-12-27 16:08:32'),
(110, 0x3a3a31, 'toto', 1, '2025-12-27 16:09:47'),
(111, 0x3a3a31, 'Remi_admin', 1, '2025-12-29 13:15:05'),
(112, 0x3a3a31, 'admin', 0, '2025-12-29 14:38:11'),
(113, 0x3a3a31, 'admin77', 0, '2025-12-29 14:38:20'),
(114, 0x3a3a31, 'admin01', 1, '2025-12-29 14:39:02'),
(115, 0x3a3a31, 'Rémi85', 1, '2025-12-30 08:29:09'),
(116, 0x3a3a31, 'Rémi', 1, '2025-12-30 08:35:24'),
(117, 0x3a3a31, 'Rémi85', 1, '2025-12-30 13:02:22'),
(118, 0x3a3a31, 'Rémi85', 1, '2025-12-30 13:52:35'),
(119, 0x3a3a31, 'Rémi85', 1, '2025-12-31 07:21:12'),
(120, 0x3a3a31, 'Rémi85', 1, '2025-12-31 08:23:26'),
(121, 0x3a3a31, 'Rémi85', 0, '2026-01-01 11:47:25'),
(122, 0x3a3a31, 'Rémi85', 1, '2026-01-01 11:47:36'),
(123, 0x3a3a31, 'Rémi85', 1, '2026-01-02 07:56:32'),
(124, 0x3a3a31, 'remi77', 0, '2026-01-02 10:12:12'),
(125, 0x3a3a31, 'admin', 0, '2026-01-02 11:10:28'),
(126, 0x3a3a31, 'lambda', 1, '2026-01-02 11:11:42'),
(127, 0x3a3a31, 'flute', 1, '2026-01-02 11:16:41'),
(128, 0x3a3a31, 'francki', 1, '2026-01-02 11:18:06'),
(129, 0x3a3a31, 'eeeeeeeeee', 1, '2026-01-02 11:19:30'),
(130, 0x3a3a31, 'eccccccc', 1, '2026-01-02 11:20:20'),
(131, 0x3a3a31, 'flute', 1, '2026-01-02 13:05:09'),
(132, 0x3a3a31, 'admin', 0, '2026-01-03 10:12:51'),
(133, 0x3a3a31, 'Rémi85', 1, '2026-01-03 10:13:02'),
(134, 0x3a3a31, 'toto', 1, '2026-01-07 10:23:39'),
(135, 0x3a3a31, 'flute', 1, '2026-01-07 10:37:03'),
(136, 0x3a3a31, 'lambda', 1, '2026-01-07 10:42:20');

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
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  `deleted_by_sender` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_by_recipient` tinyint(1) NOT NULL DEFAULT 0,
  `color` char(7) DEFAULT NULL CHECK (`color` is null or `color` regexp '^#[0-9A-Fa-f]{6}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `body`, `image_path`, `created_at`, `read_at`, `deleted_by_sender`, `deleted_by_recipient`, `color`) VALUES
(56, 34, 33, 'dssssssssss', NULL, '2025-11-10 11:28:14', NULL, 0, 0, NULL),
(57, 35, 34, 'ssssssss', NULL, '2025-11-10 13:40:56', NULL, 0, 0, NULL),
(58, 35, 33, 'ssssss', NULL, '2025-11-10 13:54:55', NULL, 0, 0, NULL),
(59, 35, 34, 'sssssssssssssssssssss', NULL, '2025-11-10 14:16:26', NULL, 0, 0, NULL),
(60, 24, 35, 'sssssssss', NULL, '2025-11-11 16:48:14', '2026-01-02 12:16:24', 0, 0, NULL),
(61, 24, 35, 'sssssssss', NULL, '2025-11-11 16:48:17', '2026-01-02 12:16:24', 0, 0, NULL),
(62, 24, 35, 'ssssssssssssssssss', NULL, '2025-11-11 16:56:31', '2026-01-02 12:16:24', 0, 0, NULL),
(63, 25, 35, 'ssssssssssssssss', NULL, '2025-11-16 10:01:59', '2026-01-02 12:16:24', 1, 0, NULL),
(64, 42, 25, 'sssssssssssssss', NULL, '2025-11-19 09:12:58', '2025-11-20 12:29:02', 0, 1, NULL),
(65, 25, 42, 'ssssss', NULL, '2026-01-04 17:57:41', NULL, 1, 0, NULL),
(66, 25, 35, 'ouiuçi', NULL, '2026-01-04 20:02:59', NULL, 1, 0, NULL),
(67, 25, 35, 'ssssssssssss', NULL, '2026-01-05 14:17:15', NULL, 1, 0, NULL),
(68, 25, 35, 'zsdczeczefz', 'uploads/msg/46a94459e39c76396d3bdabc09b4c8fe.png', '2026-01-05 14:17:24', NULL, 1, 0, NULL),
(69, 25, 24, 'xxxxxxxxxxxxxxxxxx', NULL, '2026-01-05 14:29:16', NULL, 0, 0, NULL),
(70, 25, 24, 'sssssssssssss', 'uploads/msg/239d1cdaa1fdf9c728f7e91206116fdf.png', '2026-01-05 14:29:46', NULL, 0, 0, NULL),
(71, 25, 24, 'ssssssssssss', NULL, '2026-01-05 14:29:50', NULL, 0, 0, NULL),
(72, 25, 24, '02', NULL, '2026-01-05 14:29:56', NULL, 0, 0, NULL),
(73, 25, 24, 'ssssssssssss', NULL, '2026-01-05 14:30:27', NULL, 0, 0, NULL),
(74, 25, 24, 'sssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaasssssssssssssssssssssssssssssaaaaa', 'uploads/msg/34c60d690001e0a5ef1215da1ced91f9.jpg', '2026-01-05 14:30:37', NULL, 0, 0, NULL),
(75, 25, 24, 'sssssss', NULL, '2026-01-05 14:49:59', NULL, 0, 0, NULL);

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
(303, 25, '2026-01-01 14:52:49'),
(304, 47, '2025-11-21 11:22:31'),
(310, 24, '2025-11-21 11:31:49'),
(311, 25, '2025-11-21 12:32:47'),
(314, 48, '2025-11-21 12:34:57'),
(317, 49, '2025-11-21 12:37:19'),
(335, 25, '2025-11-23 10:03:05'),
(337, 25, '2025-11-25 09:43:11'),
(344, 25, '2025-11-25 10:09:08'),
(347, 25, '2025-11-25 13:50:41'),
(358, 25, '2026-01-01 15:02:51'),
(371, 25, '2026-01-04 09:47:08'),
(372, 25, '2026-01-04 09:35:42'),
(373, 25, '2026-01-04 17:30:31');

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
(32, 28, '2025/11/a460e716dbe2ed6f.png', 'image2.jpg', 'image/png', 3029692, 1536, 1024, '2025-11-22 16:07:18');

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
(28, 23);

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

--
-- Déchargement des données de la table `quiz_sexperf_results`
--

INSERT INTO `quiz_sexperf_results` (`id`, `user_id`, `quiz_key`, `score_total`, `result_letter`, `created_at`, `share_token`, `share_enabled`) VALUES
(1, 24, 'bon_coup_v1', 10, 'B', '2025-12-27 10:30:44', NULL, 1),
(2, 24, 'bon_coup_v1', 10, 'B', '2025-12-27 10:30:53', NULL, 1),
(3, 24, 'bon_coup_v1', 10, 'B', '2025-12-27 10:30:59', NULL, 1),
(4, 24, 'bon_coup_v1', 15, 'C', '2025-12-27 10:35:57', NULL, 1),
(5, 24, 'bon_coup_v1', 11, 'B', '2025-12-27 16:03:25', NULL, 1),
(6, 24, 'bon_coup_v1', 11, 'B', '2025-12-27 16:03:53', NULL, 1),
(7, 25, 'bon_coup_v1', 7, 'A', '2025-12-27 16:49:12', NULL, 1),
(8, 48, 'bon_coup_v1', 6, 'A', '2025-12-27 17:09:57', NULL, 1),
(9, 24, 'bon_coup_v1', 5, 'A', '2025-12-29 14:15:18', NULL, 1),
(10, 44, 'bon_coup_v1', 14, 'B', '2025-12-29 15:39:13', NULL, 1),
(11, 44, 'bon_coup_v1', 10, 'B', '2025-12-30 09:17:35', NULL, 1),
(12, 25, 'bon_coup_v1', 7, 'A', '2025-12-30 09:29:19', 'LOUBs4dOS1Y4Mq5q7ZL9vQ', 1),
(13, 30, 'bon_coup_v1', 11, 'B', '2025-12-30 09:35:40', 'LDp5Ka3wcZ_IMPCGnGBduw', 1),
(14, 25, 'bon_coup_v1', 11, 'B', '2026-01-04 20:08:07', 'm5fBcVhslCdcFUGoziqYxg', 1);

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
(24, 'ssss', 'ssss');

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
(24, 'remi_admin', 'uploads/avatars/avatar_24_1763225525.jpg', NULL, NULL, 'homme', 185, '2025-11-07 18:10:38', '$2y$10$XqJBVxE8A35FiX3/gFpjjucc4t/nPGTnid9cGServ77DAfDKcRksO', NULL, NULL, 'single'),
(25, 'Rémi85', 'uploads/avatars/avatar_25_1763224581.jpg', NULL, NULL, NULL, 175, '2025-11-08 18:01:15', '$2y$10$RrJCIUA9WeF8k1mFdppgL.0VumBUHBb0.YMMsPL72KlQ9YzMA2KTu', NULL, NULL, NULL),
(26, 'gf2é5678 dhD', NULL, NULL, NULL, NULL, NULL, '2025-11-08 18:09:11', '$2y$10$4r6NO5NT0K4Tezsn6VpfiuBKyxMqvODI7/4dq2ojCxSoiwxPXm5Lu', NULL, NULL, NULL),
(27, 'gsgMé 88', NULL, NULL, NULL, NULL, NULL, '2025-11-08 18:10:35', '$2y$10$STmnSlrD3WKNE2SgqsbzEuDEpLQuEOzZH6znFhTp5uDNOulVRvXkq', NULL, NULL, NULL),
(28, 'Rémi fenech 77', NULL, NULL, NULL, NULL, NULL, '2025-11-08 18:14:23', '$2y$10$CwC9A0yDJzObbJTVRTiDN.KU1meUNX6dSK1oqgp8rOVw81V2t9BIe', NULL, NULL, NULL),
(29, 'phiL du 92', NULL, NULL, NULL, NULL, NULL, '2025-11-08 18:26:49', '$2y$10$Lqs562awrrK6AmX0nnMbMeEGOta2k7vPXh8K3a1KS6A5bSUIILpAy', NULL, NULL, NULL),
(30, 'Rémi', NULL, NULL, NULL, NULL, NULL, '2025-11-09 09:59:39', '$2y$10$NyIwnDjOUBSxWCsfDdukDeIu7v2GjVSolbQgmr5FvGRp0AAqjCeZS', NULL, NULL, NULL),
(31, 'Rémi77.t', NULL, NULL, NULL, NULL, NULL, '2025-11-09 10:09:22', '$2y$10$PqLFY5PKAVHTEzrfxmjcZOpA.BKb083U/fjuFPWhgfIZxDW9oHjpi', NULL, NULL, NULL),
(32, 'fiona.77', NULL, NULL, NULL, NULL, NULL, '2025-11-09 10:41:05', '$2y$10$gXv2NvPdv1wTeXbk6ApAXenw444q3TnleEZj9U5BSM3j67UW0fa1q', NULL, NULL, NULL),
(33, 'flute', 'uploads/avatars/avatar_33_1762765736.jpg', NULL, NULL, 'homme', 180, '2025-11-09 16:16:53', '$2y$10$29zNSRfwDI7xSvgEZBbCwOkPxJ/ggfhVUrhyBs6jGtDg/IHRnORK2', NULL, NULL, 'couple'),
(34, 'francki', NULL, NULL, NULL, 'homme', 188, '2025-11-10 11:26:34', '$2y$10$gR3tLJYnHE4f8Q/b90..Lu0Ye0aNXQ3two21G9J6VxDjbuhtHw8zq', NULL, NULL, 'single'),
(35, 'lambda', 'uploads/avatars/avatar_35_1762771227.jpg', NULL, NULL, 'homme', 175, '2025-11-10 11:39:47', '$2y$10$nCcNDuSG388utoCxTheERu5mdc6U.hrpkdSWpvFBboPVzZp8MfqhG', NULL, NULL, 'single'),
(36, 'flute02', 'uploads/avatars/avatar_36_1762783510.jpg', NULL, NULL, 'femme', 185, '2025-11-10 15:03:38', '$2y$10$lV/S.K3buQfYEo.Fd/fhiefua/s9C8ydeCugB9JXnxrciWXJxuNMG', NULL, NULL, 'single'),
(37, 'vendeur', NULL, NULL, NULL, NULL, NULL, '2025-11-10 15:20:55', '$2y$10$2MiWRP6GzzbWzr8.3ZzyVOmbuV4x4r4NAkH2P0.XbyKNRUCSzuNZ2', NULL, NULL, NULL),
(38, 'eeeeeeeeee', NULL, NULL, NULL, NULL, NULL, '2025-11-10 17:58:51', '$2y$10$oZw76x9JUU7P9x8/9c6IA.8sm8Tg/UM1wH15e3U5srIIzUe6r9h7e', NULL, NULL, NULL),
(39, 'ffff', NULL, NULL, NULL, NULL, NULL, '2025-11-16 10:42:42', '$2y$10$5aSa.ZVRbHMOauw2KLbgVOaKETDqr7Zb4EzyWUXfXO6lbDzVTEz9S', NULL, NULL, NULL),
(40, 'suyhzagsduyz', NULL, NULL, NULL, NULL, NULL, '2025-11-16 17:21:16', '$2y$10$sbdGfqJsXG02KD6aTrX1VONDqscc9YhIjLSxZzwRIkMboJ0Hu8blu', NULL, NULL, NULL),
(41, 'eccccccc', NULL, NULL, NULL, NULL, NULL, '2025-11-18 20:55:12', '$2y$10$o2IxvtuOeiRq2GQnPADJ5up3ZWNYQljv38SeMatN3qjZy9WQZpZXu', NULL, NULL, NULL),
(42, 'zxxxxxxxx', NULL, NULL, NULL, NULL, NULL, '2025-11-19 09:12:16', '$2y$10$CUFc.CXTfTRGHzDR3i51Ae1p0Haoxc7ncvuyCN/iRb/oFrGj.rv9W', NULL, NULL, NULL),
(43, 'admin09', NULL, NULL, NULL, NULL, NULL, '2025-11-19 09:42:51', '$2y$10$8Xh8xqssZpfdli0ng0hsre8frjESKTM3saPEkUHBCsfzGVZtWb0Oi', NULL, NULL, NULL),
(44, 'admin01', NULL, NULL, NULL, NULL, NULL, '2025-11-19 09:57:28', '$2y$10$wJabiEFEbWw78Tbwy5Fczuqb6Yqkgec7R0351/J83Vwleqg3fb2ma', NULL, NULL, NULL),
(45, 'admin000', NULL, NULL, NULL, NULL, NULL, '2025-11-19 09:58:28', '$2y$10$QONEU2eQnAxVauMB2V7ieODM.bVskfW60RTEz5jfA8GqA7h/rxcGy', NULL, NULL, NULL),
(46, 'admin55', NULL, NULL, NULL, NULL, NULL, '2025-11-19 10:34:05', '$2y$10$JjhxnH0l6ddMcpfVfgnHceGk3PZguCHlhztityMtOxrHm8/59N3Ua', NULL, NULL, NULL),
(47, 'frejkhif', NULL, NULL, NULL, NULL, NULL, '2025-11-19 10:35:53', '$2y$10$fl2pdyOvkImE0aC0rsB3tuetduydHOdUaUOzQC4Q4Lc9D2HOUvOe.', NULL, NULL, NULL),
(48, 'toto', NULL, NULL, NULL, NULL, NULL, '2025-11-21 12:33:50', '$2y$10$UeisodJg.QExkqbBhM0jrOIL6Beguvxgl7ZmmMSmZ3qAwXG7jqmoS', NULL, NULL, NULL),
(49, 'toto2', NULL, NULL, NULL, NULL, NULL, '2025-11-21 12:36:03', '$2y$10$DcOWYE/McOGgkeg/D2b2pu8QAKXSXLs6uYjHCNOZKQS42HH0oUMfW', NULL, NULL, NULL),
(50, 'sssssssssss', NULL, NULL, NULL, NULL, NULL, '2025-11-21 12:49:14', '$2y$10$paaRboXoPKK1ARZmPxAeMORsGTIKk1zbN4i8u.moeB0zCxT4nG3TC', NULL, NULL, NULL),
(51, 'jeanv', NULL, NULL, NULL, NULL, NULL, '2025-11-22 09:29:39', '$2y$10$6NrCZl0BymQ/3mOoI8KUce7S0jD1zuy10ZpO5NltTEHphWNM5n11u', NULL, NULL, NULL),
(52, 'brico', NULL, NULL, NULL, NULL, NULL, '2025-11-22 10:09:37', '$2y$10$z5qOVs0SXxUmteOoT94dlOBe/yAEoTEoIkH8yGhHFqnjFGzIyjpKi', NULL, NULL, NULL),
(53, 'dfefefer', NULL, NULL, NULL, NULL, NULL, '2025-11-22 16:57:34', '$2y$10$m8F4i5WlB4zZi1JLYJJRjOD1fpfYEZsyi7NquwxuD6aW0SKSC4S5a', NULL, NULL, NULL),
(54, 'ssw', NULL, NULL, NULL, NULL, NULL, '2025-11-22 18:13:09', '$2y$10$PCQulgf4Y38jwl8arIu0DO0OBhoVS8Gd1ueY2fjELLXKHst3z/ZvC', NULL, NULL, NULL),
(55, 'edddddddd', NULL, NULL, NULL, NULL, NULL, '2025-11-30 08:00:53', '$2y$10$bR1Fdh/3gt541kSZq30eQOZkfDcTsnrsJamxLUmKrLiYbkimaOKAu', NULL, NULL, NULL);

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
-- Index pour la table `chat_presence`
--
ALTER TABLE `chat_presence`
  ADD PRIMARY KEY (`room_id`,`session_key`),
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
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=380;

--
-- AUTO_INCREMENT pour la table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT pour la table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `law_projects`
--
ALTER TABLE `law_projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT pour la table `project_images`
--
ALTER TABLE `project_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `quiz_sexperf_results`
--
ALTER TABLE `quiz_sexperf_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_msg_room` FOREIGN KEY (`room_id`) REFERENCES `chat_rooms` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `message_likes`
--
ALTER TABLE `message_likes`
  ADD CONSTRAINT `fk_ml_msg` FOREIGN KEY (`message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ml_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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

DELIMITER $$
--
-- Évènements
--
CREATE DEFINER=`root`@`localhost` EVENT `ev_cleanup_ephemeral_rooms` ON SCHEDULE EVERY 10 MINUTE STARTS '2026-01-07 11:21:15' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM chat_rooms
  WHERE is_ephemeral = 1
    AND expires_at IS NOT NULL
    AND expires_at <= NOW()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
