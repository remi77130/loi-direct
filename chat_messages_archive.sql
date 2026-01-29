-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 07 jan. 2026 à 15:32
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
-- Index pour les tables déchargées
--

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
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `chat_messages_archive`
--
ALTER TABLE `chat_messages_archive`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
