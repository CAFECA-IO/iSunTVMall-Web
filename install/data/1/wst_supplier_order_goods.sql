SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_supplier_order_goods`;
CREATE TABLE `wst_supplier_order_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `goodsNum` int(11) NOT NULL DEFAULT '0',
  `goodsPrice` decimal(11,2) NOT NULL DEFAULT '0.00',
  `goodsSpecId` int(11) DEFAULT '0',
  `goodsSpecNames` varchar(500) DEFAULT NULL,
  `goodsName` varchar(200) NOT NULL,
  `goodsImg` varchar(150) NOT NULL,
  `extraJson` text,
  `goodsType` tinyint(4) NOT NULL DEFAULT '0',
  `commissionRate` decimal(11,2) DEFAULT '0.00',
  `goodsCode` varchar(20) DEFAULT NULL,
  `promotionJson` text,
  `orderGoodscommission` decimal(11,2) DEFAULT '0.00' COMMENT '订单商品佣金',
  PRIMARY KEY (`id`),
  KEY `goodsId` (`goodsId`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;