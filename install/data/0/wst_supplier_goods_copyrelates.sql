SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_supplier_goods_copyrelates`;
CREATE TABLE `wst_supplier_goods_copyrelates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsId` int(11) DEFAULT '0',
  `supplierGoodsId` int(11) DEFAULT '0',
  `shopId` int(11) DEFAULT '0',
  `supplierId` int(11) DEFAULT '0',
  `dataFlag` tinyint(4) DEFAULT '1',
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `goodsStatus` (`dataFlag`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

