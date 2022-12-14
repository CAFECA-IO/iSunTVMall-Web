SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_supplier_goods_attributes`;
CREATE TABLE `wst_supplier_goods_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `attrId` int(11) NOT NULL,
  `attrVal` text NOT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplierId` (`supplierId`),
  KEY `goodsId` (`goodsId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
