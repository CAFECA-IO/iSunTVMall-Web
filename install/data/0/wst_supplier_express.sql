SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_supplier_express`;
CREATE TABLE `wst_supplier_express` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expressId` int(11) NOT NULL,
  `isEnable` tinyint(4) NOT NULL DEFAULT '0',
  `isDefault` tinyint(4) NOT NULL DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `supplierId` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;