SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL,
  `cat_desc` varchar(250) NOT NULL,
  `def_icon` varchar(150) NOT NULL DEFAULT 'images/forum.png',
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(42) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `signature` varchar(255) NOT NULL DEFAULT 'No Signature',
  `type` int(1) NOT NULL DEFAULT 1 COMMENT '1 - user, 2 - admin',
  `avatar` varchar(150) NOT NULL DEFAULT 'uploads/avatar-default.avif',
  `last_login` datetime NOT NULL,
  `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `topics` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `topic_name` varchar(255) NOT NULL,
  `topic_desc` text NOT NULL,
  `topic_author` int(11) NOT NULL,
  `date_added_topic` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`topic_id`),
  INDEX (`parent`),
  INDEX (`topic_author`),
  CONSTRAINT `fk_topics_category` FOREIGN KEY (`parent`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_topics_author` FOREIGN KEY (`topic_author`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `comment_author` int(11) NOT NULL,
  `date_added_comment` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  INDEX (`topic_id`),
  INDEX (`comment_author`),
  CONSTRAINT `fk_comments_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`topic_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_author` FOREIGN KEY (`comment_author`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(500) NOT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `visit_time` datetime NOT NULL,
  `page_visited` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`ip_address`),
  INDEX (`visit_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Добавяне на начални данни
INSERT INTO `categories` (`position`, `cat_name`, `cat_desc`)
VALUES (1, 'Your first category', 'With a simple description text');

INSERT INTO `users` (`username`, `password`, `email`, `type`)
VALUES ('admin', 'hashed_password', 'admin@example.com', 2);

INSERT INTO `topics` (`parent`, `topic_name`, `topic_desc`, `topic_author`)
VALUES (1, 'The first topic of this forum', 'Just a simple topic description', 1);

INSERT INTO `comments` (`topic_id`, `comment`, `comment_author`)
VALUES (1, 'Hello dear visitor', 1);

COMMIT;
