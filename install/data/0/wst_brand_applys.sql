SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_brand_applys`;
CREATE TABLE `wst_brand_applys` (
  `applyId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `brandId` int(11) DEFAULT '0',
  `brandName` varchar(100) NOT NULL,
  `brandImg` varchar(150) NOT NULL,
  `brandDesc` text,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `isNew` tinyint(4) NOT NULL DEFAULT '1',
  `accreditImg` varchar(255) DEFAULT NULL,
  `applyStatus` tinyint(4) NOT NULL DEFAULT '0' COMMENT '申请状态 0:待审核 1:已审核 -1:不通过',
  `applyDesc` varchar(255) DEFAULT NULL,
  `catIds` varchar(150) NOT NULL,
  PRIMARY KEY (`applyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;