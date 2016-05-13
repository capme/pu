UPDATE `module` SET name = 'Order' WHERE id = 1;
INSERT INTO `module` (`id`, `name`, `slug`, `icon`, `parent`, `sort`, `hidden`, `status`) VALUES (74, 'Operation', NULL, 'fa-archive', '0', '20', '0', '1');
INSERT INTO `module` (`id`, `name`, `slug`, `icon`, `parent`, `sort`, `hidden`, `status`) VALUES (75, 'AWB Printing', NULL, 'fa-print', '74', '1', '0', '1');
INSERT INTO `module` (`id`, `name`, `slug`, `icon`, `parent`, `sort`, `hidden`, `status`) VALUES (76, 'Print AWB', 'awbprinting', 'fa-print', '75', '0', '0', '1');
CREATE TABLE `awb_upload_file` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `name` varchar(50) NOT NULL,  `filename` varchar(50) NOT NULL,  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  PRIMARY KEY (`id`)) ENGINE=InnoDB;
CREATE TABLE `awb_queue_printing` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `ordernr` varchar(50) DEFAULT NULL, `client_id` int(1) NOT NULL,  `receiver` varchar(200) DEFAULT NULL,  `company` varchar(200) DEFAULT NULL,  `address` text,  `city` varchar(100) DEFAULT NULL,  `province` varchar(100) DEFAULT NULL,  `zipcode` varchar(7) DEFAULT NULL,  `country` varchar(50) DEFAULT NULL,  `phone` varchar(20) DEFAULT NULL,  `items` text NOT NULL,  `shipping_type` varchar(50) DEFAULT NULL,  `package_type` varchar(50) DEFAULT NULL,  `status` tinyint(1) DEFAULT '0',  `reference_file_id` int(10) NOT NULL,  `created_at` timestamp NULL DEFAULT NULL,  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`)) ENGINE=InnoDB;
