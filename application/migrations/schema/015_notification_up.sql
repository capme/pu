CREATE TABLE IF NOT EXISTS `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `url` varchar(50) NOT NULL,
  `message` TEXT NOT NULL,
  `read` tinyint(1) NOT NULL,
  `email` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `group_ids` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;