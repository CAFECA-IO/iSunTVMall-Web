SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_supplier_cats`;
CREATE TABLE `wst_supplier_cats` (
  `catId` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL,
  `parentId` int(11) NOT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT '1',
  `catName` varchar(100) NOT NULL,
  `catSort` int(11) NOT NULL DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`catId`),
  KEY `parentId` (`isShow`,`dataFlag`) USING BTREE,
  KEY `supplierId` (`supplierId`,`dataFlag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;