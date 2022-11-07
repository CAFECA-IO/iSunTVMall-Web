SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_supplier_freight_template`;
CREATE TABLE `wst_supplier_freight_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierExpressId` int(11) NOT NULL COMMENT '供货商ID',
  `tempName` varchar(200) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `tempType` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0：全国 1：指定省份',
  `provinceIds` text,
  `cityIds` text,
  `buyNumStart` int(11) DEFAULT '0' COMMENT '棣栦欢(浠?',
  `buyNumStartPrice` decimal(11,2) DEFAULT '0.00' COMMENT '首件运费(元)',
  `buyNumContinue` int(11) DEFAULT '0' COMMENT '缁欢(浠?',
  `buyNumContinuePrice` decimal(11,2) DEFAULT '0.00' COMMENT '续件运费(元)',
  `weightStart` decimal(11,2) DEFAULT '0.00' COMMENT '首重（Kg）',
  `weightStartPrice` decimal(11,2) DEFAULT '0.00' COMMENT '首重价格',
  `weightContinue` decimal(11,2) DEFAULT '0.00' COMMENT '续重（Kg）',
  `weightContinuePrice` decimal(11,2) DEFAULT '0.00' COMMENT '续重价格',
  `volumeStart` decimal(11,2) DEFAULT '0.00' COMMENT '首体积量(m³)',
  `volumeStartPrice` decimal(11,2) DEFAULT '0.00' COMMENT '首体积运费(元)',
  `volumeContinue` decimal(11,2) DEFAULT '0.00' COMMENT '续体积量(m³)',
  `volumeContinuePrice` decimal(11,2) DEFAULT '0.00' COMMENT '续体积运费(元)',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `supplierId` int(11) DEFAULT '0',
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;