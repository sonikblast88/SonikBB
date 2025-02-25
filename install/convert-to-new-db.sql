-- Актуализация на съществуващи таблици
ALTER TABLE `users` ADD UNIQUE (`email`), ADD UNIQUE (`username`);
ALTER TABLE `users` MODIFY `password` varchar(255) NOT NULL;
ALTER TABLE `users` ADD `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `email`;

ALTER TABLE `topics` ADD `date_added_topic` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `topic_author`;
ALTER TABLE `comments` ADD `date_added_comment` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `comment_author`;

ALTER TABLE `visitors` MODIFY `user_agent` varchar(500) NOT NULL, MODIFY `referrer` varchar(500) DEFAULT NULL;

-- Добавяне на FOREIGN KEY връзки
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`topic_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_author` FOREIGN KEY (`comment_author`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `topics`
  ADD CONSTRAINT `fk_topics_category` FOREIGN KEY (`parent`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_topics_author` FOREIGN KEY (`topic_author`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

-- Оптимизация с индекси
ALTER TABLE `comments` ADD INDEX (`topic_id`), ADD INDEX (`comment_author`);
ALTER TABLE `topics` ADD INDEX (`parent`), ADD INDEX (`topic_author`);
ALTER TABLE `visitors` ADD INDEX (`ip_address`), ADD INDEX (`visit_time`);

-- Актуализация на съществуващите записи
UPDATE `users` SET `created` = NOW() WHERE `created` IS NULL;
UPDATE `topics` SET `date_added_topic` = NOW() WHERE `date_added_topic` IS NULL;
UPDATE `comments` SET `date_added_comment` = NOW() WHERE `date_added_comment` IS NULL;

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
