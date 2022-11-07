SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_mould_goods_attributes`;
CREATE TABLE `wst_mould_goods_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `mouldId` int(11) DEFAULT '0',
  `goodsCatId` int(11) NOT NULL DEFAULT '0',
  `attrId` int(11) NOT NULL,
  `attrVal` text NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` int(11) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`),
  KEY `goodsId` (`goodsCatId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
