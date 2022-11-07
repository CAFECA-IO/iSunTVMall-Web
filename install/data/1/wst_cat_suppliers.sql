SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_cat_suppliers`;
CREATE TABLE `wst_cat_suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL DEFAULT '0',
  `catId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `catId` (`catId`),
  KEY `supplierId` (`supplierId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;