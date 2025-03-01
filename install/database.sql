SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL,
  `cat_desc` varchar(250) NOT NULL,
  `def_icon` varchar(150) NOT NULL DEFAULT 'images/forum.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `comment_author` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `topic_id` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `topic_name` varchar(255) NOT NULL,
  `topic_desc` text NOT NULL,
  `topic_author` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `signature` varchar(255) NOT NULL DEFAULT 'No Signature',
  `username` varchar(42) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `type` int(1) NOT NULL DEFAULT 1 COMMENT '1 - user and 2 - admin',
  `avatar` varchar(150) NOT NULL DEFAULT 'uploads/avatar-default.avif',
  `last_login` datetime NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `referrer` text DEFAULT NULL,
  `visit_time` datetime NOT NULL,
  `page_visited` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`topic_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

ALTER TABLE `users` ADD `created` DATE NOT NULL AFTER `email`;
ALTER TABLE `topics` ADD `date_added_topic` DATE NOT NULL AFTER `topic_author`; 
ALTER TABLE `comments` ADD `date_added_comment` DATE NOT NULL AFTER `comment_author`; 

INSERT INTO `categories` (`position`, `cat_name`, `cat_desc`)
VALUES (1, 'Your first category', 'With a simple description text');

INSERT INTO `topics` (`parent`, `topic_name`, `topic_desc`, `topic_author`, `date_added_topic`)
VALUES (1, 'The firs topic of this forum', 'just a simple topic description', 1, now()); -- the id of the admin 1

INSERT INTO `comments` (`topic_id`, `comment`, `comment_author`, `date_added_comment`)
VALUES (1, 'Hello dear visitor', 1, now()); -- Заменете 1 с ID на създадения администратор и ID на създадения форум
