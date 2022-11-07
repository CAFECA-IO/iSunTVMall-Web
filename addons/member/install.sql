DROP TABLE IF EXISTS `wst_member_recommend_configs`;
CREATE TABLE `wst_member_recommend_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recommendNum` int(11) DEFAULT '0',
  `score` int(11) DEFAULT '0',
  `createTime` datetime DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_member_users`;
CREATE TABLE `wst_member_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) DEFAULT '0',
  `userId` int(11) DEFAULT '0',
  `createTime` datetime DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;