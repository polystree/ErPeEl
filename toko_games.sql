-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 03, 2025 at 05:33 PM
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
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `created_at`) VALUES
(35, 33, 3.98, '2025-07-03 16:56:14'),
(36, 33, 32.98, '2025-07-03 17:07:44'),
(37, 33, 51.96, '2025-07-03 17:11:54'),
(38, 33, 3.98, '2025-07-03 17:32:16');

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
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `produk_id`, `price`, `steam_key`) VALUES
(161, 35, 33, 0.99, 'VAULT-A4695-8ADA5-FF4AE'),
(162, 36, 32, 29.99, 'VAULT-58BD6-DC3E6-F14EC'),
(163, 37, 2, 23.99, 'VAULT-09AC9-F81EA-A67D9'),
(164, 37, 19, 4.99, 'VAULT-ACCB7-ED5FD-BB2D0'),
(165, 37, 11, 19.99, 'VAULT-5FFC3-97C3A-AC16E'),
(166, 38, 33, 0.99, 'VAULT-4F7A7-FA6CD-86803');

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
(1, 2, 'Elden Ring', 'A vast world of fantasy and danger from FromSoftware, creators of the Dark Souls series.', 'FromSoftware', 'eldenRing.png', 59.99, NULL, 0),
(2, 2, 'Tales of Arise', 'A story of liberation, featuring a vibrant world, dynamic combat, and a compelling cast of characters.', 'Bandai Namco', 'talesOfArise.avif', 59.99, 23.99, 1),
(3, 10, 'Persona 5 Royal', 'The definitive edition of the award-winning RPG, featuring new characters, story, and locations.', 'ATLUS', 'persona5Royal.png', 59.99, NULL, 0),
(4, 8, 'Animal Crossing: New Horizons', 'Escape to a deserted island and create your own paradise. A relaxing life simulation game.', 'Nintendo', 'animalCrossing.png', 59.99, NULL, 0),
(5, 5, 'Company of Heroes 3', 'The legendary strategy franchise is back! A cinematic WWII experience awaits.', 'Relic Entertainment', 'companyOfHeroes3.png', 59.99, 29.99, 0),
(6, 4, 'Super Mario Odyssey', 'Explore huge 3D kingdoms filled with secrets and surprises, using Mario\'s new abilities.', 'Nintendo', 'superMarioOdyssey.png', 59.99, NULL, 0),
(7, 2, 'Avatar: Frontiers of Pandora', 'Explore the breathtaking Western Frontier of Pandora in this first-person action-adventure.', 'Ubisoft', 'avatar.png', 69.99, 41.99, 0),
(8, 2, 'The Witcher 3: Wild Hunt', 'A story-driven, open-world RPG set in a visually stunning fantasy universe.', 'CD PROJEKT RED', 'theWitcher3.png', 39.99, 9.99, 0),
(9, 2, 'Sekiro: Shadows Die Twice', 'Carve your own clever path to vengeance in this award-winning action-adventure game.', 'FromSoftware', 'sekiro.png', 59.99, 29.99, 0),
(10, 2, 'Dark Souls III', 'An action RPG set in a hauntingly beautiful, dark world. Prepare to die.', 'FromSoftware', 'darkSoul3.png', 59.99, 29.99, 0),
(11, 2, 'Bloodborne', 'Hunt your nightmares in the ancient city of Yharnam, a place cursed with a strange endemic illness.', 'FromSoftware', 'bloodborne.png', 19.99, NULL, 1),
(12, 1, 'DOOM Eternal', 'Rip and tear through demonic hordes in the ultimate formula for first-person combat.', 'id Software', 'doomEternal.png', 39.99, 9.99, 0),
(13, 2, 'Divinity: Original Sin 2', 'The critically acclaimed role-playing game with turn-based combat and deep story.', 'Larian Studios', 'divinityOriginalSin2.png', 44.99, 17.99, 0),
(14, 5, 'Age of Empires IV', 'Classic real-time strategy returns to glory with new civilizations and mechanics.', 'Relic Entertainment', 'ageOfEmpires4.png', 39.99, NULL, 0),
(15, 6, 'Cities: Skylines', 'A modern take on the classic city simulation. Design, build, and manage the city of your dreams.', 'Colossal Order', 'citiesSkyline.png', 29.99, 8.99, 0),
(16, 6, 'Anno 1800', 'Lead the Industrial Revolution and build a sprawling metropolis in this city-builder RTS.', 'Ubisoft Mainz', 'anno1880.png', 59.99, 14.99, 0),
(17, 7, 'XCOM 2', 'Lead the resistance against an alien occupation in this turn-based tactics video game.', 'Firaxis Games', 'xcom2.png', 59.99, 5.99, 0),
(18, 6, 'Tropico 6', 'Rule your own banana republic as El Presidente in this construction and management simulation.', 'Limbic Entertainment', 'tropico6.png', 39.99, 15.99, 0),
(19, 8, 'The Sims 4: Starter Edition', 'The ultimate life simulation game where you can create and control people.', 'Maxis', 'thesims4.png', 9.99, 4.99, 1),
(20, 17, 'Hollow Knight', 'A challenging and atmospheric 2D action-adventure. Explore a vast, ruined kingdom of insects.', 'Team Cherry', 'hollowKnight.png', 14.99, NULL, 0),
(21, 17, 'Stardew Valley', 'You\'ve inherited your grandfather\'s old farm plot. Create the farm of your dreams!', 'ConcernedApe', 'stardew.png', 14.99, NULL, 0),
(22, 17, 'Celeste', 'A narrative-driven, single-player adventure about climbing a mountain, with charming characters.', 'Maddy Makes Games', 'celeste.png', 19.99, 4.99, 0),
(23, 17, 'Spiritfarer', 'A cozy management game about dying. Build a boat to explore the world, then befriend and care for spirits.', 'Thunder Lotus Games', 'spiritfarer.png', 29.99, 7.49, 0),
(24, 4, 'Ori and the Blind Forest: Definitive Edition', 'A visually stunning action-platformer with a deeply emotional story.', 'Moon Studios', 'oriAndTheBlindForest.png', 19.99, 4.99, 0),
(28, 1, 'Counter-Strike 2 (Prime Status)', 'The next evolution of the world\'s most popular tactical shooter. Includes Prime Status.', 'Valve', 'modernCs2.png', 14.99, NULL, 0),
(29, 3, 'PUBG: BATTLEGROUNDS', 'The original battle royale game. Land, loot, and survive to become the last one standing.', 'KRAFTON, Inc.', 'pubg.png', 29.99, 9.89, 0),
(30, 9, 'Final Fantasy XIV: Complete Edition', 'Join millions of adventurers in Eorzea. Includes the base game and all expansions.', 'Square Enix', 'finalFantasyXIV.png', 59.99, 29.99, 0),
(31, 9, 'The Elder Scrolls Online: Standard Edition', 'Join over 22 million players in the award-winning online multiplayer RPG.', 'ZeniMax Online', 'theElderScrolls.png', 19.99, 5.99, 0),
(32, 9, 'Guild Wars 2: Heroic Edition', 'An online role-playing game with fast-paced action combat and a rich story.', 'ArenaNet', 'guildWars2.png', 29.99, NULL, 1),
(33, 9, 'Black Desert Online', 'A revolutionary MMORPG that delivers intense, fast-paced combat and a vast open world.', 'Pearl Abyss', 'blackDesertOnline.png', 9.99, 0.99, 2);

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
) ENGINE=InnoDB AUTO_INCREMENT=291 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`id`, `produk_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(288, 33, 33, 5, '', '2025-07-03 16:56:36'),
(289, 11, 33, 5, '', '2025-07-03 17:12:03'),
(290, 32, 33, 5, 'KEREN BANGET GAMENYA', '2025-07-03 17:15:37');

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
