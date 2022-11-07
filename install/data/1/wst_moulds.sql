SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_moulds`;
CREATE TABLE `wst_moulds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT '0',
  `goodsCatId` int(11) DEFAULT '0',
  `mouldName` varchar(200) NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
