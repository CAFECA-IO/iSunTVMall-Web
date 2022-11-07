SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_mould_goods_spec_items`;
CREATE TABLE `wst_mould_goods_spec_items` (
  `itemId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT '0',
  `mouldId` int(11) DEFAULT '0',
  `goodsCatId` int(11) NOT NULL DEFAULT '0',
  `catId` int(11) NOT NULL DEFAULT '0',
  `itemName` varchar(100) NOT NULL,
  `itemDesc` varchar(255) DEFAULT NULL,
  `itemImg` varchar(150) NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`itemId`),
  KEY `goodsId` (`goodsCatId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;