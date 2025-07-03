-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 02, 2025 at 04:34 PM
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `kategori_id`, `nama`, `detail`, `pengembang`, `foto`, `harga`, `harga_diskon`, `sold`) VALUES
	(1, 1, 'Call of Duty: Modern Warfare II', 'The latest in the legendary FPS franchise. Intense multiplayer and cinematic campaign.', 'Infinity Ward', 'modernCs2.png', 69.99, 59.99, 0),
	(2, 1, 'Counter-Strike: Global Offensive', 'The world’s most popular competitive shooter. Tactical, team-based action.', 'Valve', 'csGlobalAss.png', 69.99, NULL, 0),
	(3, 1, 'DOOM Eternal', 'Rip and tear through hordes of demons in this fast-paced FPS.', 'id Software', 'doomEternal.png', 59.99, 29.99, 0),
	(4, 1, 'Battlefield V', 'Large-scale warfare with vehicles and destructible environments.', 'DICE', 'battlefield5.png', 49.99, 19.99, 0),
	(5, 1, 'Rainbow Six Siege', 'Tactical team-based FPS with destructible environments.', 'Ubisoft', 'caveiraRainbowSixSiege.png', 19.99, 7.99, 0),
	(6, 2, 'The Witcher 3: Wild Hunt', 'A vast open-world RPG with a gripping story and deep combat.', 'CD Projekt Red', 'theWitcher3.png', 39.99, 19.99, 0),
	(7, 2, 'Elden Ring', 'A dark fantasy action RPG from the creators of Dark Souls.', 'FromSoftware', 'eldenRing.png', 59.99, 49.99, 0),
	(8, 2, 'Dark Souls III', 'The challenging and rewarding action RPG set in a dark fantasy world.', 'FromSoftware', 'darkSoul3.png', 39.99, 29.99, 0),
	(9, 2, 'Bloodborne', 'A gothic horror action RPG from the creators of Dark Souls.', 'FromSoftware', 'bloodborne.png', 39.99, 29.99, 0),
	(10, 2, 'Sekiro: Shadows Die Twice', 'A gripping action RPG set in Sengoku period Japan.', 'FromSoftware', 'sekiro.png', 59.99, NULL, 0),
	(11, 3, 'Fortnite', 'The battle royale phenomenon. Build, shoot, and survive.', 'Epic Games', 'fortnite.png', 69.99, NULL, 0),
	(12, 3, 'PUBG: Battlegrounds', 'The original battle royale shooter. 100 players, one winner.', 'PUBG Corporation', 'pubg.png', 69.99, NULL, 0),
	(13, 3, 'Apex Legends', 'Team up and be the last squad standing in this free-to-play battle royale.', 'Respawn Entertainment', 'apexLegend.png', 69.99, NULL, 0),
	(14, 3, 'Call of Duty: Warzone', 'Drop in, loot out! The massive free-to-play combat arena.', 'Infinity Ward', 'codWarzone.png', 69.99, NULL, 0),
	(15, 3, 'Realm Royale', 'A fantasy twist on the battle royale genre. Choose your class and claim victory.', 'Hi-Rez Studios', 'realmRoyales.png', 69.99, NULL, 0),
	(16, 4, 'Super Mario Odyssey', 'Mario embarks on a globe-trotting adventure in 3D platforming.', 'Nintendo', 'superMarioOdyssey.png', 59.99, NULL, 0),
	(17, 4, 'Tale of Peace', 'A touching story and challenging platformer about climbing a mountain.', 'Matt Makes Games', 'taleOfPeace.png', 19.99, 9.99, 0),
	(18, 4, 'Hollow Knight', 'Descend into the dark and deadly world beneath Hallownest in this action-adventure platformer.', 'Team Cherry', 'hollowKnight.png', 14.99, NULL, 0),
	(19, 4, 'Ori and the Blind Forest', 'A visually stunning action-platformer with a touching story.', 'Moon Studios', 'oriAndTheBlindForest.png', 29.99, 14.99, 0),
	(20, 4, 'Celeste', 'A platformer about climbing a mountain, both literally and metaphorically. Help Madeline survive her journey.', 'Maddy Makes Games', 'celeste.png', 19.99, NULL, 0),
	(21, 5, 'StarCraft II', 'The definitive sci-fi RTS. Three races, epic battles.', 'Blizzard Entertainment', 'starcraft2.png', 29.99, NULL, 0),
	(22, 5, 'Age of Empires IV', 'Build an empire through the ages in this classic RTS.', 'Relic Entertainment', 'ageOfEmpires4.png', 59.99, 39.99, 0),
	(23, 5, 'Warcraft III: Reforged', 'The classic RTS returns with modern graphics and new features.', 'Blizzard Entertainment', 'warcraft3.png', 29.99, NULL, 0),
	(24, 5, 'Command & Conquer: Remastered', 'Relive the classic RTS battles with updated graphics and audio.', 'Petroglyph Games', 'commandAndConquer.png', 19.99, NULL, 0),
	(25, 5, 'Company of Heroes 3', 'The next installment in the acclaimed WWII RTS series.', 'Relic Entertainment', 'companyOfHeroes3.png', 59.99, NULL, 0),
	(26, 6, 'Cities: Skylines', 'A modern take on the classic city builder. Design, build, and manage.', 'Colossal Order', 'citiesSkyline.png', 29.99, 14.99, 0),
	(27, 6, 'SimCity', 'The original city simulation game. Build your dream city.', 'Maxis', 'simcity.png', 19.99, NULL, 0),
	(28, 6, 'Tropico 6', 'Rule your own banana republic in this humorous city builder.', 'Kalypso Media', 'tropico6.png', 39.99, NULL, 0),
	(29, 6, 'Anno 1800', 'Build a thriving city in the age of industrialization.', 'Ubisoft Blue Byte', 'anno1880.png', 49.99, NULL, 0),
	(30, 6, 'Surviving Mars', 'Colonize Mars and ensure the survival of your colonists in this city-building sim.', 'Haemimont Games', 'survivingMars.png', 39.99, NULL, 0),
	(31, 7, 'Fire Emblem: Three Houses', 'A tactical RPG with deep story and turn-based combat.', 'Intelligent Systems', 'fireEmblem.png', 59.99, 49.99, 0),
	(32, 7, 'Advance Wars 1+2: Re-Boot Camp', 'Classic turn-based strategy returns with a modern twist.', 'WayForward', 'advanceWars1+2.png', 59.99, NULL, 0),
	(33, 7, 'XCOM 2', 'Command your soldiers in tactical, turn-based combat against an alien invasion.', 'Firaxis Games', 'xcom2.png', 39.99, 19.99, 0),
	(34, 7, 'Divinity: Original Sin 2', 'A critically acclaimed RPG with deep tactical combat and cooperative multiplayer.', 'Larian Studios', 'divinityOriginalSin2.png', 59.99, NULL, 0),
	(35, 7, 'Wasteland 3', 'The post-apocalyptic RPG with deep story and tactical turn-based combat.', 'inXile Entertainment', 'wasteland3.png', 39.99, NULL, 0),
	(36, 8, 'The Sims 4', 'Create and control people in a virtual world.', 'Maxis', 'thesims4.png', 39.99, 19.99, 0),
	(37, 8, 'Animal Crossing: New Horizons', 'Build your dream island life with friends and neighbors.', 'Nintendo', 'animalCrossing.png', 59.99, 49.99, 0),
	(38, 8, 'Stardew Valley', 'A relaxing farming and life sim with endless charm.', 'ConcernedApe', 'stardew.png', 14.99, NULL, 0),
	(39, 8, 'Spiritfarer', 'A cozy management game about dying and friendship.', 'Thunder Lotus Games', 'spiritfarer.png', 29.99, 19.99, 0),
	(40, 8, 'My Time at Portia', 'A charming life simulation RPG. Restore your father\'s workshop and explore the post-apocalyptic world.', 'Pathea Games', 'myTimeAtPortia.png', 29.99, NULL, 0),
	(41, 9, 'Final Fantasy XIV Online', 'A critically acclaimed MMORPG with a passionate community.', 'Square Enix', 'finalFantasyXIV.png', 19.99, NULL, 0),
	(42, 9, 'Guild Wars 2', 'A living world MMORPG with dynamic events and epic battles.', 'ArenaNet', 'guildWars2.png', 69.99, NULL, 0),
	(43, 9, 'The Elder Scrolls Online', 'Explore the vast world of Tamriel in this award-winning MMORPG.', 'ZeniMax Online Studios', 'theElderScrolls.png', 29.99, NULL, 0),
	(44, 9, 'Black Desert Online', 'A sandbox MMORPG with stunning graphics and deep combat systems.', 'Pearl Abyss', 'blackDesertOnline.png', 39.99, NULL, 0),
	(45, 9, 'Star Wars: The Old Republic', 'Experience the epic storylines and space battles in this acclaimed MMORPG.', 'BioWare', 'starWarTheOldRepublic.png', 14.99, NULL, 0),
	(46, 10, 'Persona 5 Royal', 'A stylish JRPG about high schoolers and supernatural heists.', 'Atlus', 'persona5Royal.png', 59.99, 39.99, 0),
	(47, 10, 'Dragon Quest XI', 'A classic JRPG with a modern polish and epic story.', 'Square Enix', 'dragonQuestXI.png', 49.99, NULL, 0),
	(48, 10, 'Ni no Kuni II: Revenant Kingdom', 'A beautiful JRPG with a touching story and stunning visuals.', 'Level-5', 'ninokuni.png', 59.99, NULL, 0),
	(49, 10, 'Tales of Arise', 'The latest in the beloved Tales series. Action-packed combat and a captivating story.', 'Bandai Namco', 'talesOfArise.avif', 59.99, NULL, 0),
	(50, 18, 'HARRY Dont GO', 'There is no place to HIDE and there is no where to RUN. HARRY Dont GO will let you experience the thrill of getting chased by A LOT of MONSTERS, how will Harry escape these monster?', 'Hansen Nathaniel', 'harryDontGo.png', 44.99, 14.99, 0),
	(51, 16, 'Hansen the game', 'A game where you become a Chinese and have power to fight your lecturer', 'Ilmu Komputer 23 B', 'audacity_cover.jpg', 69.99, 66.6, 0);

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
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`id`, `produk_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 5, 'Incredible gunplay and stunning visuals. A must-play for FPS fans!', '2025-06-27 08:00:00'),
(2, 1, 3, 4, 'Solid shooter, but the story is a bit generic. Still, had a lot of fun.', '2025-06-27 08:05:00'),
(3, 2, 1, 5, 'Lost hundreds of hours in this world. The exploration is top-notch.', '2025-06-27 08:10:00'),
(4, 2, 4, 5, 'Best ARPG I have played in years. The combat is so satisfying.', '2025-06-27 08:15:00'),
(5, 3, 5, 4, 'Fast, frantic, and fun. Great with friends.', '2025-06-27 08:20:00'),
(6, 4, 6, 5, 'A masterpiece of pixel art and level design. So addictive!', '2025-06-27 08:25:00'),
(7, 5, 9, 5, 'The strategic depth is immense. Every decision matters.', '2025-06-27 08:30:00'),
(8, 6, 7, 4, 'A very relaxing and detailed city builder. Easy to learn, hard to master.', '2025-06-27 08:35:00'),
(9, 7, 10, 5, 'Turn-based combat has never been this good. The story is incredible.', '2025-06-27 08:40:00'),
(10, 8, 12, 4, 'Endless fun creating and managing my virtual family.', '2025-06-27 08:45:00'),
(11, 9, 1, 4, 'A great MMORPG, but can be a bit of a grind.', '2025-06-27 08:50:00'),
(12, 10, 10, 5, 'Cried at the ending. A beautiful story.', '2025-06-27 08:55:00'),
(13, 11, 7, 5, 'The perfect game to relax with. So much charm and heart.', '2025-06-27 09:00:00'),
(14, 12, 9, 4, 'Great tactical gameplay. Really makes you think.', '2025-06-27 09:05:00'),
(15, 13, 8, 5, 'The most realistic racing sim I have ever played. The physics are perfect.', '2025-06-27 09:10:00'),
(16, 14, 4, 3, 'It\'s another FIFA. Fun, but not much has changed.', '2025-06-27 09:15:00'),
(17, 15, 11, 5, 'Pure arcade bliss. The soundtrack is amazing!', '2025-06-27 09:20:00'),
(18, 16, 1, 5, 'The king of fighting games is back and better than ever.', '2025-06-27 09:25:00'),
(19, 17, 7, 5, 'Can\'t wait for this game! Hollow Knight was a masterpiece.', '2025-06-27 09:30:00'),
(20, 18, 18, 5, 'The puzzles are genius. My brain hurts in the best way possible.', '2025-06-27 09:35:00'),
(21, 19, 12, 5, 'So cute and relaxing. I play it every day.', '2025-06-27 09:40:00'),
(22, 20, 20, 5, 'Terrifyingly good fun with friends. Never fails to make us scream.', '2025-06-27 09:45:00'),
(23, 1, 1, 5, 'A fantastic return to form for the series. The campaign is thrilling!', '2025-06-27 09:50:00'),
(24, 2, 2, 5, 'An absolute masterpiece. The world is so alive and detailed.', '2025-06-27 09:55:00'),
(25, 3, 3, 4, 'Very fun, but I prefer the original formula.', '2025-06-27 10:00:00'),
(26, 4, 4, 5, 'The level design is out of this world. A true platforming gem.', '2025-06-27 10:05:00'),
(27, 5, 5, 4, 'Solid game, but not as revolutionary as the first one.', '2025-06-27 10:10:00'),
(28, 6, 6, 5, 'I love building my own cities and watching them thrive.', '2025-06-27 10:15:00'),
(29, 7, 7, 4, 'The tactical depth is impressive, but it can be overwhelming.', '2025-06-27 10:20:00'),
(30, 8, 8, 5, 'A delightful escape from reality. I could play for hours.', '2025-06-27 10:25:00'),
(31, 9, 9, 4, 'Great lore and world-building, but the combat is not for everyone.', '2025-06-27 10:30:00'),
(32, 10, 10, 5, 'An emotional rollercoaster. I loved every minute of it.', '2025-06-27 10:35:00'),
(33, 11, 11, 4, 'Clever and unique. A breath of fresh air in the RPG genre.', '2025-06-27 10:40:00'),
(34, 12, 12, 5, 'Deeply engaging and thought-provoking. A must-play.', '2025-06-27 10:45:00'),
(35, 13, 13, 4, 'The cars feel amazing to drive, and the tracks are beautiful.', '2025-06-27 10:50:00'),
(36, 14, 14, 3, 'It\'s fun, but I expected more innovation.', '2025-06-27 10:55:00'),
(37, 15, 15, 5, 'A blast from the past! Just as fun as I remember.', '2025-06-27 11:00:00'),
(38, 16, 16, 5, 'The graphics are stunning, and the gameplay is tight.', '2025-06-27 11:05:00'),
(39, 17, 17, 4, 'I enjoyed the first one more, but this is still great.', '2025-06-27 11:10:00'),
(40, 18, 18, 5, 'A brilliant mix of humor and challenge. Loved it!', '2025-06-27 11:15:00'),
(41, 19, 19, 4, 'Charming and relaxing, but not very challenging.', '2025-06-27 11:20:00'),
(42, 20, 20, 5, 'The atmosphere is incredible. I was genuinely scared.', '2025-06-27 11:25:00'),
(43, 3, 6, 3, 'Good but not great. The pacing is off in some sections.', '2025-06-27 11:30:00'),
(44, 5, 14, 2, 'Too repetitive for my taste. Gets boring after a few hours.', '2025-06-27 11:35:00'),
(45, 6, 17, 5, 'The best in the series. Every character is memorable.', '2025-06-27 11:40:00'),
(46, 10, 19, 3, 'Solid gameplay but the story could use more depth.', '2025-06-27 11:45:00'),
(47, 21, 2, 5, 'Strategic gameplay at its finest. So many options and playstyles.', '2025-06-27 11:50:00'),
(48, 21, 4, 4, 'Good but the learning curve is steep. Not for casual gamers.', '2025-06-27 11:55:00'),
(49, 21, 6, 5, 'Perfect balance of strategy and action. I keep coming back to it.', '2025-06-27 12:00:00'),
(50, 22, 8, 5, 'Historic battles have never been this fun! Graphics are stunning.', '2025-06-27 12:05:00'),
(51, 22, 10, 4, 'Great game, but needs more unique factions.', '2025-06-27 12:10:00'),
(52, 22, 12, 5, 'I was skeptical at first, but now I\'m addicted. The multiplayer is incredible.', '2025-06-27 12:15:00'),
(53, 23, 14, 3, 'Not as good as the original. The remaster feels rushed.', '2025-06-27 12:20:00'),
(54, 23, 16, 4, 'Brings back great memories. The custom map editor is amazing.', '2025-06-27 12:25:00'),
(55, 23, 18, 3, 'Some good improvements, but still has technical issues.', '2025-06-27 12:30:00'),
(56, 24, 20, 5, 'A perfect remaster. They\'ve maintained the soul of the original.', '2025-06-27 12:35:00'),
(57, 24, 1, 4, 'The nostalgia is real! Classic gameplay with modern conveniences.', '2025-06-27 12:40:00'),
(58, 24, 3, 5, 'The soundtrack alone is worth the price. Gameplay holds up amazingly.', '2025-06-27 12:45:00'),
(59, 25, 5, 4, 'Fresh take on the series. The campaign is challenging and rewarding.', '2025-06-27 12:50:00'),
(60, 25, 7, 5, 'The tactical options are vast. Each mission feels unique.', '2025-06-27 12:55:00'),
(61, 25, 9, 3, 'Good but not revolutionary. Some mechanics feel outdated.', '2025-06-27 13:00:00'),
(62, 26, 11, 5, 'The most detailed city builder I\'ve ever played. So many options!', '2025-06-27 13:05:00'),
(63, 26, 13, 4, 'Great simulation depth, but the UI can be overwhelming.', '2025-06-27 13:10:00'),
(64, 26, 15, 5, 'Hours disappear when I play this. The DLC adds so much value.', '2025-06-27 13:15:00'),
(65, 27, 17, 3, 'Feels dated compared to newer city builders, but still has charm.', '2025-06-27 13:20:00'),
(66, 27, 19, 4, 'The classic that started it all. Still fun after all these years.', '2025-06-27 13:25:00'),
(67, 27, 2, 3, 'Nostalgic but limited compared to modern titles.', '2025-06-27 13:30:00'),
(68, 28, 4, 5, 'Hilarious and engaging. The political satire is spot on.', '2025-06-27 13:35:00'),
(69, 28, 6, 4, 'Very unique compared to other city builders. Love the humor.', '2025-06-27 13:40:00'),
(70, 28, 8, 5, 'Being an eccentric dictator has never been this fun!', '2025-06-27 13:45:00'),
(71, 29, 10, 5, 'The industrial era setting is refreshing. So many details to manage!', '2025-06-27 13:50:00'),
(72, 29, 12, 4, 'Beautiful graphics and deep systems. Can be overwhelming at first.', '2025-06-27 13:55:00'),
(73, 29, 14, 5, 'The trade system is brilliantly implemented. Hours of strategic fun.', '2025-06-27 14:00:00'),
(74, 30, 16, 4, 'Mars colonization is challenging and rewarding. Great atmosphere.', '2025-06-27 14:05:00'),
(75, 30, 18, 3, 'Interesting concept but gets repetitive after a while.', '2025-06-27 14:10:00'),
(76, 30, 20, 5, 'The disasters keep you on your toes. Very immersive experience.', '2025-06-27 14:15:00'),
(77, 31, 1, 5, 'The story and character development are phenomenal. Best in the series.', '2025-06-27 14:20:00'),
(78, 31, 3, 4, 'Excellent tactical gameplay, though some classes feel unbalanced.', '2025-06-27 14:25:00'),
(79, 31, 5, 5, 'The relationship system adds so much depth to the strategy elements.', '2025-06-27 14:30:00'),
(80, 32, 7, 4, 'A faithful remake with modern conveniences. Great nostalgic value.', '2025-06-27 14:35:00'),
(81, 32, 9, 5, 'The perfect mix of accessibility and tactical depth.', '2025-06-27 14:40:00'),
(82, 32, 11, 3, 'Good but doesn\'t add enough to the original formula.', '2025-06-27 14:45:00'),
(83, 33, 13, 5, 'The tension is real! Every mission feels like a desperate fight.', '2025-06-27 14:50:00'),
(84, 33, 15, 4, 'Great strategy game but the RNG can be frustrating at times.', '2025-06-27 14:55:00'),
(85, 33, 17, 5, 'The customization options are vast. No two playthroughs are the same.', '2025-06-27 15:00:00'),
(86, 34, 19, 5, 'The most flexible RPG system ever. Freedom to play exactly how you want.', '2025-06-27 15:05:00'),
(87, 34, 2, 5, 'The co-op experience is unmatched. So many creative solutions to problems.', '2025-06-27 15:10:00'),
(88, 34, 4, 4, 'Incredible depth, but can be overwhelming for newcomers.', '2025-06-27 15:15:00'),
(89, 35, 6, 4, 'Great post-apocalyptic atmosphere and interesting moral choices.', '2025-06-27 15:20:00'),
(90, 35, 8, 5, 'The character interactions and dialogue are top-notch.', '2025-06-27 15:25:00'),
(91, 35, 10, 3, 'Good but has some technical issues that can be frustrating.', '2025-06-27 15:30:00'),
(92, 36, 12, 5, 'The ultimate life simulation. So many possibilities!', '2025-06-27 15:35:00'),
(93, 36, 14, 4, 'Fun but requires too many DLCs for the complete experience.', '2025-06-27 15:40:00'),
(94, 36, 16, 5, 'Creating stories and watching them unfold is endlessly entertaining.', '2025-06-27 15:45:00'),
(95, 37, 18, 5, 'The most relaxing game ever. Perfect escape from reality.', '2025-06-27 15:50:00'),
(96, 37, 20, 4, 'Charming and cute, but content updates are too slow.', '2025-06-27 15:55:00'),
(97, 37, 1, 5, 'The seasonal events keep the game fresh. Always something new to do.', '2025-06-27 16:00:00'),
(98, 38, 3, 5, 'The farming mechanics are so satisfying. Love watching my farm grow!', '2025-06-27 16:05:00'),
(99, 38, 5, 5, 'The perfect indie game. So much heart and content for the price.', '2025-06-27 16:10:00'),
(100, 38, 7, 4, 'Great game but some of the daily tasks get repetitive.', '2025-06-27 16:15:00'),
(101, 39, 9, 5, 'The most emotional game I\'ve ever played. Cried multiple times.', '2025-06-27 16:20:00'),
(102, 39, 11, 5, 'Beautiful art style and meaningful storytelling. A true gem.', '2025-06-27 16:25:00'),
(103, 39, 13, 4, 'A unique management game with heart. The characters are unforgettable.', '2025-06-27 16:30:00'),
(104, 40, 15, 4, 'Crafting and building your workshop is very satisfying.', '2025-06-27 16:35:00'),
(105, 40, 17, 3, 'Fun game but the loading times can be frustrating.', '2025-06-27 16:40:00'),
(106, 40, 19, 5, 'The mix of farming, crafting, and dungeon crawling is perfect.', '2025-06-27 16:45:00'),
(107, 41, 2, 5, 'The most engaging MMO storyline ever. Actually feels like a great RPG.', '2025-06-27 16:50:00'),
(108, 41, 4, 4, 'Excellent for solo players, unlike many other MMOs.', '2025-06-27 16:55:00'),
(109, 41, 6, 5, 'The community is incredibly welcoming. Best online experience ever.', '2025-06-27 17:00:00'),
(110, 42, 8, 5, 'The dynamic events make the world feel alive. No subscription fee is a plus!', '2025-06-27 17:05:00'),
(111, 42, 10, 4, 'Great PvP and world exploration. Story is a bit weak though.', '2025-06-27 17:10:00'),
(112, 42, 12, 5, 'The mount system and world traversal is the best in any MMO.', '2025-06-27 17:15:00'),
(113, 43, 14, 4, 'Massive world with tons of lore. Great for Elder Scrolls fans.', '2025-06-27 17:20:00'),
(114, 43, 16, 5, 'The freedom to build any character type is refreshing for an MMO.', '2025-06-27 17:25:00'),
(115, 43, 18, 3, 'Good but doesn\'t capture the magic of the single-player games.', '2025-06-27 17:30:00'),
(116, 44, 20, 5, 'The combat system is unmatched in the MMO space. So fluid!', '2025-06-27 17:35:00'),
(117, 44, 1, 4, 'Gorgeous visuals and deep systems, but can be grindy.', '2025-06-27 17:40:00'),
(118, 44, 3, 5, 'The most action-packed MMO I\'ve played. Feels like a proper action game.', '2025-06-27 17:45:00'),
(119, 45, 5, 4, 'The Star Wars setting is used perfectly. Great for fans of the franchise.', '2025-06-27 17:50:00'),
(120, 45, 7, 3, 'The story is great but the gameplay feels dated now.', '2025-06-27 17:55:00'),
(121, 45, 9, 5, 'Playing as a Sith is so satisfying. The voice acting is top-notch.', '2025-06-27 18:00:00'),
(122, 46, 11, 5, 'The style and substance are both incredible. One of the best JRPGs ever.', '2025-06-27 18:05:00'),
(123, 46, 13, 5, 'The music and visual design are unmatched. Story is deep and engaging.', '2025-06-27 18:10:00'),
(124, 46, 15, 4, 'Great game but the calendar system can feel restrictive.', '2025-06-27 18:15:00'),
(125, 47, 17, 5, 'A perfect blend of classic JRPG charm and modern convenience.', '2025-06-27 18:20:00'),
(126, 47, 19, 4, 'The characters are lovable and the world is a joy to explore.', '2025-06-27 18:25:00'),
(127, 47, 2, 5, 'The definitive modern JRPG. Turn-based combat at its finest.', '2025-06-27 18:30:00'),
(128, 48, 4, 4, 'The Ghibli-esque art style is beautiful. Combat is fun if a bit simple.', '2025-06-27 18:35:00'),
(129, 48, 6, 5, 'The kingdom building aspect adds a fresh dimension to the JRPG formula.', '2025-06-27 18:40:00'),
(130, 48, 8, 3, 'Looks great but the story is somewhat predictable.', '2025-06-27 18:45:00'),
(131, 49, 10, 5, 'The combat system is a perfect evolution of the Tales series.', '2025-06-27 18:50:00'),
(132, 49, 12, 4, 'Great character development and exciting battle system.', '2025-06-27 18:55:00'),
(133, 49, 14, 5, 'The visuals are stunning and the story tackles mature themes effectively.', '2025-06-27 19:00:00'),
(134, 50, 16, 5, 'The pixel art is gorgeous and the multiple storylines are all engaging.', '2025-06-27 19:05:00'),
(135, 50, 18, 4, 'Innovative battle system, though some stories are better than others.', '2025-06-27 19:10:00'),
(136, 50, 20, 5, 'The music and art direction create an incredible atmosphere.', '2025-06-27 19:15:00'),
(137, 51, 1, 5, 'A true indie masterpiece. The way it subverts expectations is brilliant.', '2025-06-27 19:20:00'),
(138, 51, 3, 5, 'The humor and heart in this game are unmatched. So many surprises.', '2025-06-27 19:25:00'),
(139, 51, 5, 4, 'Unique and charming, though some puzzles can be obtuse.', '2025-06-27 19:30:00'),
(140, 51, 7, 5, 'A delightful experience from start to finish. The art style is so cute.', '2025-06-27 19:35:00'),
(141, 51, 9, 4, 'The game is hilarious and the mechanics are fun. A great indie title.', '2025-06-27 19:40:00'),
(142, 51, 11, 5, 'A perfect blend of humor and gameplay. I can\'t recommend it enough.', '2025-06-27 19:45:00'),
(143, 51, 13, 4, 'A fun game with a lot of heart. The characters are memorable.', '2025-06-27 19:50:00');


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
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `foto`, `role`) VALUES
(1, 'GameMaster_01', 'gamemaster01@email.com', 'pass123', NULL, NULL),
(2, 'PixelPioneer', 'pixel.pioneer@email.com', 'pass123', NULL, NULL),
(3, 'QuestSeeker', 'quest.seeker@email.com', 'pass123', NULL, NULL),
(4, 'ProGamerX', 'progamerx@email.com', 'pass123', NULL, NULL),
(5, 'LevelUpLaura', 'laura.levelup@email.com', 'pass123', NULL, NULL),
(6, 'NoobSlayer99', 'noob.slayer@email.com', 'pass123', NULL, NULL),
(7, 'IndieDevFan', 'indie.fan@email.com', 'pass123', NULL, NULL),
(8, 'RetroGamer', 'retro.gamer@email.com', 'pass123', NULL, NULL),
(9, 'StrategyKing', 'strategy.king@email.com', 'pass123', NULL, NULL),
(10, 'RPG_Fanatic', 'rpg.fanatic@email.com', 'pass123', NULL, NULL),
(11, 'SpeedRunnerSam', 'sam.run@email.com', 'pass123', NULL, NULL),
(12, 'CasualPlayer', 'casual.player@email.com', 'pass123', NULL, NULL),
(13, 'VR_Voyager', 'vr.voyager@email.com', 'pass123', NULL, NULL),
(14, 'MightyMax', 'mighty.max@email.com', 'pass123', NULL, NULL),
(15, 'StealthySusan', 'susan.stealth@email.com', 'pass123', NULL, NULL),
(16, 'CosmicChris', 'cosmic.chris@email.com', 'pass123', NULL, NULL),
(17, 'DungeonMaster', 'dungeon.master@email.com', 'pass123', NULL, NULL),
(18, 'Cyberpunk_Cody', 'cody.cyber@email.com', 'pass123', NULL, NULL),
(19, 'FantasyFiend', 'fantasy.fiend@email.com', 'pass123', NULL, NULL),
(20, 'ZombieZoe', 'zoe.zombie@email.com', 'pass123', NULL, NULL),
(21, 'MarioFan', 'mario.fan@email.com', 'pass123', NULL, NULL),
(22, 'ZeldaHero', 'zelda.hero@email.com', 'pass123', NULL, NULL),
(23, 'FPSPro', 'fps.pro@email.com', 'pass123', NULL, NULL),
(24, 'SimQueen', 'sim.queen@email.com', 'pass123', NULL, NULL),
(25, 'PuzzleKing', 'puzzle.king@email.com', 'pass123', NULL, NULL),
(26, 'HorrorAddict', 'horror.addict@email.com', 'pass123', NULL, NULL),
(27, 'RacerX', 'racer.x@email.com', 'pass123', NULL, NULL),
(28, 'SportsGuru', 'sports.guru@email.com', 'pass123', NULL, NULL),
(29, 'BuilderBob', 'builder.bob@email.com', 'pass123', NULL, NULL),
(30, 'IndieStar', 'indie.star@email.com', 'pass123', NULL, NULL),
(31, 'test', 'tes@tes.com', '123', NULL, NULL),
(32, 'admin', 'admin@admin.com', '123', NULL, NULL),
(33, 'cinder', '123@123.123', '123', NULL, NULL);

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
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `produk_id`, `user_id`) VALUES
(1, 17, 1),
(2, 10, 2),
(3, 1, 3),
(4, 7, 4),
(5, 11, 5),
(6, 20, 6),
(7, 18, 7),
(8, 4, 8),
(9, 5, 9),
(10, 2, 10);

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
