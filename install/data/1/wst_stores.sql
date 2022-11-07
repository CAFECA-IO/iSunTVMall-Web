SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_stores`;
CREATE TABLE `wst_stores` (
  `storeId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT '0',
  `userId` int(11) NOT NULL,
  `areaIdPath` varchar(100) DEFAULT '',
  `areaId` int(11) DEFAULT '0',
  `storeName` varchar(200) DEFAULT '',
  `storeImg` varchar(150) DEFAULT '',
  `storeTel` varchar(40) DEFAULT '',
  `storeAddress` varchar(255) DEFAULT '',
  `longitude` decimal(10,7) DEFAULT NULL,
  `createTime` date DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `mapLevel` int(11) DEFAULT '16',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `storeStatus` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`storeId`),
  KEY `shopStatus` (`dataFlag`) USING BTREE,
  KEY `areaIdPath` (`areaIdPath`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

