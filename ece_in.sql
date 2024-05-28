-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 28 mai 2024 à 09:56
-- Version du serveur : 8.2.0
-- Version de PHP : 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ece_in`
--

-- --------------------------------------------------------

--
-- Structure de la table `chats`
--

DROP TABLE IF EXISTS `chats`;
CREATE TABLE IF NOT EXISTS `chats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user1_id` int NOT NULL,
  `user2_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user1_id` (`user1_id`),
  KEY `user2_id` (`user2_id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `reg_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `friends`
--

DROP TABLE IF EXISTS `friends`;
CREATE TABLE IF NOT EXISTS `friends` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `friend_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `friend_id` (`friend_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `friends`
--

INSERT INTO `friends` (`id`, `user_id`, `friend_id`, `created_at`) VALUES
(3, 3, 2, '2024-05-26 16:40:23'),
(4, 2, 3, '2024-05-26 16:40:23'),
(5, 3, 1, '2024-05-26 23:50:49'),
(6, 1, 3, '2024-05-26 23:50:49'),
(7, 8, 5, '2024-05-27 09:04:24'),
(8, 5, 8, '2024-05-27 09:04:24'),
(9, 9, 8, '2024-05-27 09:12:15'),
(10, 8, 9, '2024-05-27 09:12:15'),
(11, 3, 1, '2024-05-27 17:21:37'),
(12, 1, 3, '2024-05-27 17:21:37'),
(13, 10, 11, '2024-05-28 08:46:46'),
(14, 11, 10, '2024-05-28 08:46:46'),
(18, 10, 5, '2024-05-28 08:58:52'),
(17, 5, 10, '2024-05-28 08:58:52');

-- --------------------------------------------------------

--
-- Structure de la table `friend_requests`
--

DROP TABLE IF EXISTS `friend_requests`;
CREATE TABLE IF NOT EXISTS `friend_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `friend_requests`
--

INSERT INTO `friend_requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(1, 3, 1, 'accepted', '2024-05-26 16:20:20'),
(2, 1, 3, 'pending', '2024-05-26 16:22:40'),
(3, 3, 2, 'accepted', '2024-05-26 16:39:45'),
(4, 3, 1, 'accepted', '2024-05-26 23:50:36'),
(5, 8, 5, 'accepted', '2024-05-27 09:03:47'),
(6, 9, 8, 'accepted', '2024-05-27 09:09:54'),
(7, 9, 1, 'pending', '2024-05-27 17:53:25'),
(8, 10, 11, 'accepted', '2024-05-28 08:46:26'),
(9, 11, 10, 'accepted', '2024-05-28 08:56:40'),
(10, 5, 10, 'accepted', '2024-05-28 08:57:38'),
(11, 5, 11, 'pending', '2024-05-28 08:58:35');

-- --------------------------------------------------------

--
-- Structure de la table `likes`
--

DROP TABLE IF EXISTS `likes`;
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `reg_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `likes`
--

INSERT INTO `likes` (`id`, `post_id`, `user_id`, `reg_date`) VALUES
(12, 30, 2, '2024-05-26 15:25:10'),
(11, 30, 0, '2024-05-26 15:24:21'),
(10, 30, 0, '2024-05-26 15:24:21'),
(9, 30, 0, '2024-05-26 15:24:19'),
(8, 30, 0, '2024-05-26 15:24:19'),
(7, 30, 0, '2024-05-26 15:24:18'),
(13, 30, 3, '2024-05-26 23:37:56'),
(14, 30, 3, '2024-05-26 23:37:59'),
(15, 33, 5, '2024-05-27 09:04:55'),
(16, 33, 5, '2024-05-27 09:04:58'),
(17, 33, 9, '2024-05-27 09:15:12');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chat_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_id` (`chat_id`),
  KEY `sender_id` (`sender_id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `media` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `reg_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `content`, `media`, `location`, `date`, `reg_date`) VALUES
(33, 8, 'je cherche', 'je cherche du taff', '', '', '0000-00-00 00:00:00', '2024-05-27 09:03:33'),
(32, 1, 'd', 'dd', 'ddd', 'ddd', '0000-00-00 00:00:00', '2024-05-26 23:51:52'),
(31, 3, 'Cherche un Travail', 'Dans la cybersecu', '', '', '0000-00-00 00:00:00', '2024-05-26 23:39:03'),
(30, 2, 'a', 'a', 'a', 'a', '0000-00-00 00:00:00', '2024-05-26 15:22:59'),
(29, 1, '0', '0', '', '', '0000-00-00 00:00:00', '2024-05-26 15:21:16');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `photo` varchar(100) DEFAULT NULL,
  `background` varchar(100) DEFAULT NULL,
  `reg_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_admin` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `pseudo`, `email`, `name`, `photo`, `background`, `reg_date`, `is_admin`) VALUES
(11, '11', '11@11', '11', '', '', '2024-05-28 08:45:49', 0),
(5, 'admin', 'admin@admin', 'admin', '', '', '2024-05-26 21:08:18', 1),
(10, '10', '10@10', '10', '', '', '2024-05-28 08:45:25', 0),
(8, 'lop', 'moussouni@gmail.com', 'moussouni', 'l', '', '2024-05-27 09:02:47', 0),
(9, 'miguel', 'miguel@miguel', 'Miguel', '', '', '2024-05-27 09:09:36', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
