-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 03, 2025 at 04:22 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_games`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `produk_id` int NOT NULL,
  `quantity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `produk_id` (`produk_id`)
) ENGINE=InnoDB AUTO_INCREMENT=226 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

DROP TABLE IF EXISTS `kategori`;
CREATE TABLE IF NOT EXISTS `kategori` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama`) VALUES
(1, 'FPS Shooters'),
(2, 'Action RPG'),
(3, 'Battle Royale'),
(4, 'Platformers'),
(5, 'RTS Games'),
(6, 'City Builders'),
(7, 'Turn-Based'),
(8, 'Life Simulation'),
(9, 'MMORPG'),
(10, 'JRPG'),
(11, 'Indie RPG'),
(12, 'Tactical RPG'),
(13, 'Racing Sims'),
(14, 'Sports Games'),
(15, 'Arcade Racing'),
(16, 'Fighting Games'),
(17, 'Indie Games'),
(18, 'Puzzle Games'),
(19, 'Casual Games'),
(20, 'Horror Games');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `produk_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `steam_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `produk_id` (`produk_id`)
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
CREATE TABLE IF NOT EXISTS `produk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kategori_id` int DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `pengembang` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `harga` double DEFAULT NULL,
  `harga_diskon` double DEFAULT NULL,
  `sold` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kategori_id` (`kategori_id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `kategori_id`, `nama`, `detail`, `pengembang`, `foto`, `harga`, `harga_diskon`, `sold`) VALUES
(104, 13, 'CINDER', 'x', 'koh cinder', 'https://letsenhance.io/static/73136da51c245e80edc6ccfe44888a99/1015f/MainBefore.jpg', 50, 25, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

DROP TABLE IF EXISTS `rating`;
CREATE TABLE IF NOT EXISTS `rating` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produk_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `rating` tinyint NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `produk_id` (`produk_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=288 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subk`
--

DROP TABLE IF EXISTS `subk`;
CREATE TABLE IF NOT EXISTS `subk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `foto`, `role`) VALUES
(1, 'GameMaster_01', 'gamemaster01@email.com', 'pass123', '675065f0a978b.jpg', NULL),
(2, 'PixelPioneer', 'pixel.pioneer@email.com', 'pass123', '676c04fe2b0b6.png', NULL),
(3, 'QuestSeeker', 'quest.seeker@email.com', 'pass123', NULL, NULL),
(4, 'ProGamerX', 'progamerx@email.com', 'pass123', '675065f0a978b.jpg', NULL),
(5, 'LevelUpLaura', 'laura.levelup@email.com', 'pass123', NULL, NULL),
(6, 'NoobSlayer99', 'noob.slayer@email.com', 'pass123', '676c04fe2b0b6.png', NULL),
(7, 'IndieDevFan', 'indie.fan@email.com', 'pass123', NULL, NULL),
(8, 'RetroGamer', 'retro.gamer@email.com', 'pass123', '675065f0a978b.jpg', NULL),
(9, 'StrategyKing', 'strategy.king@email.com', 'pass123', NULL, NULL),
(10, 'RPG_Fanatic', 'rpg.fanatic@email.com', 'pass123', '676c04fe2b0b6.png', NULL),
(11, 'SpeedRunnerSam', 'sam.run@email.com', 'pass123', NULL, NULL),
(12, 'CasualPlayer', 'casual.player@email.com', 'pass123', '675065f0a978b.jpg', NULL),
(13, 'VR_Voyager', 'vr.voyager@email.com', 'pass123', '685f2394b1b4c.jpg', NULL),
(14, 'MightyMax', 'mighty.max@email.com', 'pass123', '676c04fe2b0b6.png', NULL),
(15, 'StealthySusan', 'susan.stealth@email.com', 'pass123', NULL, NULL),
(16, 'CosmicChris', 'cosmic.chris@email.com', 'pass123', '675065f0a978b.jpg', NULL),
(17, 'DungeonMaster', 'dungeon.master@email.com', 'pass123', NULL, NULL),
(18, 'Cyberpunk_Cody', 'cody.cyber@email.com', 'pass123', '676c04fe2b0b6.png', NULL),
(19, 'FantasyFiend', 'fantasy.fiend@email.com', 'pass123', NULL, NULL),
(20, 'ZombieZoe', 'zoe.zombie@email.com', 'pass123', '675065f0a978b.jpg', NULL),
(21, 'MarioFan', 'mario.fan@email.com', 'pass123', 'e3nmb-kua0.avif', NULL),
(22, 'ZeldaHero', 'zelda.hero@email.com', 'pass123', 'aknnhs-xn-.avif', NULL),
(23, 'FPSPro', 'fps.pro@email.com', 'pass123', '8wd6kvqvluv8hzzosotwtg.avif', NULL),
(24, 'SimQueen', 'sim.queen@email.com', 'pass123', 'qvjtc65vbzmexfegzrgs7u.avif', NULL),
(25, 'PuzzleKing', 'puzzle.king@email.com', 'pass123', '9786020651927_Funiculi_Funicula_cov.avif', NULL),
(26, 'HorrorAddict', 'horror.addict@email.com', 'pass123', 'a_Pandemic_of_love-indo-cov_page-0001.avif', NULL),
(27, 'RacerX', 'racer.x@email.com', 'pass123', '25n7v4-a3l.avif', NULL),
(28, 'SportsGuru', 'sports.guru@email.com', 'pass123', '5d98139zna.avif', NULL),
(29, 'BuilderBob', 'builder.bob@email.com', 'pass123', 'n831xcr33-.avif', NULL),
(30, 'IndieStar', 'indie.star@email.com', 'pass123', 'i2wjpuayv5xcmwcb7bhjkp.avif', NULL),
(31, 'test', 'tes@tes.com', '123', '6860241995461.jpg', NULL),
(33, 'cinder', '123@123.123', '123', NULL, NULL),
(34, 'admin', 'admin@gmail.com', '123', NULL, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produk_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produk_id` (`produk_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
