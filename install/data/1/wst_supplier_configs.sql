SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_supplier_configs`;
CREATE TABLE `wst_supplier_configs` (
  `configId` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL,
  `supplierTitle` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `supplierKeywords` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `supplierDesc` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `supplierBanner` varchar(150) CHARACTER SET utf8 DEFAULT NULL,
  `supplierAds` text CHARACTER SET utf8,
  `supplierAdsUrl` text CHARACTER SET utf8,
  `supplierServicer` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `supplierHotWords` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`configId`),
  KEY `supplierId` (`supplierId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;