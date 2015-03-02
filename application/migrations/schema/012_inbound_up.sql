CREATE TABLE `inb_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_number` varchar(30) NOT NULL,
  `client_id` int(11) NOT NULL,
  `note` varchar(300) NULL,
  `type` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `filename` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `doc_number` (`doc_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `inb_inventory_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tag` int(11) NOT NULL,
  `action` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8