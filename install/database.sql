-- phpMyAdmin SQL Dump
-- Generation Time: Mar 03, 2025 at 10:30 AM
-- Server version: 10.3.32-MariaDB-log
-- PHP Version: 8.0.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Database: `sonikbb`
-- --------------------------------------------------------

--
-- Table structure for table `categories`
--
CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `cat_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cat_desc` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `def_icon` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'images/forum.png',
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Insert test data for table `categories`
--
INSERT INTO `categories` (`cat_id`, `position`, `cat_name`, `cat_desc`, `def_icon`) VALUES
(1, 1, 'Test Category One', 'This is the first test category.', 'images/forum.png'),
(2, 2, 'Test Category Two', 'This is the second test category.', 'images/forum.png');

-- --------------------------------------------------------
-- Table structure for table `topics`
--
CREATE TABLE `topics` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `topic_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `topic_desc` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `topic_author` int(11) NOT NULL,
  `date_added_topic` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`topic_id`),
  KEY `fk_topics_category` (`parent`),
  KEY `fk_topics_user` (`topic_author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Insert test data for table `topics`
-- (Assuming the installer will add an admin user with user_id = 1)
--
INSERT INTO `topics` (`topic_id`, `parent`, `topic_name`, `topic_desc`, `topic_author`, `date_added_topic`) VALUES
(1, 1, 'Welcome to Test Category One', 'This is the first test topic in category one.', 1, '2025-03-03 05:40:59'),
(2, 2, 'Welcome to Test Category Two', 'This is the first test topic in category two.', 1, '2025-03-03 06:00:00');

-- --------------------------------------------------------
-- Table structure for table `comments`
--
CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL,
  `comment` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_author` int(11) NOT NULL,
  `date_added_comment` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `fk_comments_topic` (`topic_id`),
  KEY `fk_comments_user` (`comment_author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Insert test data for table `comments`
-- (Assuming the installer will add an admin user with user_id = 1)
--
INSERT INTO `comments` (`comment_id`, `topic_id`, `comment`, `comment_author`, `date_added_comment`) VALUES
(1, 1, 'This is a test comment on topic one.', 1, '2025-03-03 05:45:00'),
(2, 2, 'This is a test comment on topic two.', 1, '2025-03-03 06:05:00');

-- --------------------------------------------------------
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(42) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 - user, 2 - admin',
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Signature',
  `avatar` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uploads/avatar-default.avif',
  `last_login` datetime NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: No test user data is inserted because the installer will add an admin user.

-- --------------------------------------------------------
-- Table structure for table `visitors`
--
CREATE TABLE `visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `referrer` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visit_time` datetime NOT NULL,
  `page_visited` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
