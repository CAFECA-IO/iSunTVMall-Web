SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_supplier_wholesale_goods`;
CREATE TABLE `wst_supplier_wholesale_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsId` int(11) NOT NULL,
  `buyNum` int(11) DEFAULT '0',
  `rebate` decimal(11,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
