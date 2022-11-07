SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_custom_pages`;
CREATE TABLE `wst_custom_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageName` varchar(32) NOT NULL DEFAULT '',
  `isIndex` tinyint(4) NOT NULL DEFAULT '0',
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `pageType` tinyint(4) NOT NULL DEFAULT '1',
  `attr` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_custom_pages` VALUES ('1', '手机端自定义首页', '0', '2019-09-25 15:17:05', '1', '1', 'a:3:{s:5:\"title\";s:25:\"WSTMart电子商务系统\";s:11:\"share_title\";s:0:\"\";s:6:\"poster\";s:53:\"upload/custompagedecoration/2019-09/5d89b2a202968.jpg\";}'),
('2', '微信端自定义首页', '0', '2019-09-25 15:13:13', '1', '2', 'a:3:{s:5:\"title\";s:25:\"WSTMart电子商务系统\";s:11:\"share_title\";s:0:\"\";s:6:\"poster\";s:53:\"upload/custompagedecoration/2019-09/5d8b13866baa8.jpg\";}'),
('3', '小程序端自定义首页', '0', '2019-09-25 16:04:25', '1', '3', 'a:3:{s:5:\"title\";s:25:\"WSTMart电子商务系统\";s:11:\"share_title\";s:0:\"\";s:6:\"poster\";s:53:\"upload/custompagedecoration/2019-09/5d8b1d0cf1f18.jpg\";}');
