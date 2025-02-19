-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Време на генериране: 
-- Версия на сървъра: 5.6.12-log
-- Версия на PHP: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- БД: `myforum`
--
CREATE DATABASE IF NOT EXISTS `myforum` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `myforum`;

-- --------------------------------------------------------

--
-- Структура на таблица `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL,
  `cat_desc` varchar(250) NOT NULL,
  `def_icon` varchar(150) NOT NULL DEFAULT 'images/forum.png',
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=45 ;

--
-- Схема на данните от таблица `categories`
--

INSERT INTO `categories` (`cat_id`, `position`, `cat_name`, `cat_desc`, `def_icon`) VALUES
(38, 1, 'PHP форуми', '<p>Интересни неща, които не искам да губя относно PHP ще ги слагам в тази секция и по този начин се надявам знанията да не бъдат губени.</p>', 'images/php.png'),
(39, 2, 'MySQL', '<p>Тъй като започваме да влизаме в главната бета на форумите и темите и коментарите и другите такива работи тук в тази секция ще започна да правя разни интересни експерименти, които се надявам да работят.</p>', 'images/mysql.png'),
(44, 2, 'Тук ще си играем да изчистваме бъговете във форумите', '<p>Тъй като започваме да влизаме в главната бета на форумите и темите и коментарите и другите такива работи тук в тази секция ще започна да правя разни интересни експерименти, които се надявам да работят.</p>', 'images/forum.png');

-- --------------------------------------------------------

--
-- Структура на таблица `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `comment_author` int(11) NOT NULL,
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=62 ;

--
-- Схема на данните от таблица `comments`
--

INSERT INTO `comments` (`comment_id`, `topic_id`, `comment`, `comment_author`) VALUES
(60, 85, '<p>$numbers = array(1,2,3,5,4,55,66,7);</p>\r\n<p>echo array_sum($numbers);</p>\r\n<p>&nbsp;</p>\r\n<p>По този начин всички числа в масива ще бъдат преброени и ще получим резултата</p>', 1),
(61, 85, '<p>$first = array(''a'' =&gt; ''apple'', ''b'' =&gt; ''bannana'', ''c'' =&gt; ''chill'');</p>\r\n<p>$second = array(''z'' =&gt; ''zonda'', ''w'' =&gt; ''wine'', ''x'' =&gt; ''xart'');</p>\r\n<p>$third = array_merge($first, $second);</p>\r\n<p>print_r($third);</p>', 1);

-- --------------------------------------------------------

--
-- Структура на таблица `topics`
--

CREATE TABLE IF NOT EXISTS `topics` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `topic_name` varchar(100) NOT NULL,
  `topic_desc` text NOT NULL,
  `topic_author` int(11) NOT NULL,
  PRIMARY KEY (`topic_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=86 ;

--
-- Схема на данните от таблица `topics`
--

INSERT INTO `topics` (`topic_id`, `parent`, `topic_name`, `topic_desc`, `topic_author`) VALUES
(85, 38, 'PHP - Arrays', '<p>Сортиране на масив:</p>\r\n<p>$unsorted = array(9,7,8,2,4,1);</p>\r\n<p>sort($unsorted); // Сортира ги от 1 нагоре</p>\r\n<p>print_r($unsorted); // Ще ги изкара 1,2,4,7,8,9</p>\r\n<p>---</p>\r\n<p>Ако ползваме rsort() , което означава reverse sort обратно подреждане ще ги подреди от 9 надолу до 1</p>\r\n<p>Сортирането става по следния начин първо са буквите Големи после малки после са цифрите.</p>\r\n<p>natcasesort() Прави точно обратното , то ги подрежда по цифри после букви и пренебрегва малките и големите букви.</p>\r\n<p>shuffle() - Ще разбърква масива , всеки път ще го подрежда по различен начин.</p>\r\n<p>&nbsp;</p>', 1);

-- --------------------------------------------------------

--
-- Структура на таблица `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `signature` varchar(255) NOT NULL,
  `username` varchar(42) NOT NULL,
  `password` varchar(42) NOT NULL,
  `type` int(1) NOT NULL DEFAULT '1' COMMENT '1 - user and 2 - admin',
  `avatar` varchar(150) NOT NULL DEFAULT 'images/avatar-default.jpg',
  `last_login` datetime NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Схема на данните от таблица `users`
--

INSERT INTO `users` (`user_id`, `signature`, `username`, `password`, `type`, `avatar`, `last_login`) VALUES
(1, 'Не е важното къде си, важното е да си там, но да не си сам!', 'admin', 'admin', 2, 'images/avatar-admin.png', '2014-05-15 15:59:52'),
(2, 'Подпис на потребител 1', 'user', 'user', 1, 'images/avatar-default.jpg', '2014-05-14 18:19:51'),
(3, 'подпис на потребител 2', 'user2', 'user2', 1, 'images/avatar-default.jpg', '2014-05-14 18:23:22');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
