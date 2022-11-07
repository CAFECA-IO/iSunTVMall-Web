SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_store_users`;
CREATE TABLE `wst_store_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT '0',
  `storeId` int(11) NOT NULL DEFAULT '0',
  `userId` int(11) NOT NULL DEFAULT '0',
  `roleId` int(11) NOT NULL DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `privilegeMsgs` varchar(200) DEFAULT '',
  `privilegeMsgTypes` varchar(50) DEFAULT '',
  `privilegePhoneMsgs` varchar(200) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4;
