DROP TABLE IF EXISTS `wst_addon_captchas`;
CREATE TABLE `wst_addon_captchas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `imgPath` varchar(255) NOT NULL ,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;