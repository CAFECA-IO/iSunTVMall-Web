SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_log_shop_operates`;
CREATE TABLE `wst_log_shop_operates` (
  `operateId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT '0',
  `operateTime` datetime NOT NULL,
  `menuId` int(11) NOT NULL,
  `operateDesc` varchar(255) NOT NULL,
  `operateUrl` varchar(255) NOT NULL,
  `content` longtext,
  `operateIP` varchar(20) DEFAULT NULL,
  `shopId` int(4) DEFAULT '1',
  PRIMARY KEY (`operateId`),
  KEY `operateTime` (`userId`,`menuId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;