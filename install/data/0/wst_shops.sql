SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_shops`;
CREATE TABLE `wst_shops` (
  `shopId` int(11) NOT NULL AUTO_INCREMENT,
  `shopSn` varchar(20) DEFAULT '',
  `userId` int(11) NOT NULL,
  `areaIdPath` varchar(255) DEFAULT '',
  `areaId` int(11) DEFAULT '0',
  `isSelf` tinyint(4) NOT NULL DEFAULT '0',
  `shopName` varchar(100) DEFAULT '',
  `shopkeeper` varchar(50) DEFAULT '',
  `telephone` varchar(20) DEFAULT '',
  `shopCompany` varchar(255) DEFAULT '',
  `shopImg` varchar(150) DEFAULT '',
  `shopTel` varchar(40) DEFAULT '',
  `shopQQ` varchar(50) DEFAULT NULL,
  `shopWangWang` varchar(50) DEFAULT NULL,
  `shopAddress` varchar(255) DEFAULT '',
  `bankId` int(11) DEFAULT '0',
  `bankNo` varchar(20) DEFAULT '',
  `bankUserName` varchar(50) DEFAULT '',
  `isInvoice` tinyint(4) NOT NULL DEFAULT '0',
  `invoiceRemarks` varchar(255) DEFAULT NULL,
  `serviceStartTime` time NOT NULL DEFAULT '08:30:00',
  `serviceEndTime` time NOT NULL DEFAULT '22:30:00',
  `shopAtive` tinyint(4) NOT NULL DEFAULT '1',
  `shopStatus` tinyint(4) NOT NULL DEFAULT '1',
  `statusDesc` varchar(255) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `createTime` date DEFAULT NULL,
  `shopMoney` decimal(11,2) DEFAULT '0.00',
  `lockMoney` decimal(11,2) DEFAULT '0.00',
  `noSettledOrderNum` int(11) DEFAULT '0',
  `noSettledOrderFee` decimal(11,2) DEFAULT '0.00',
  `paymentMoney` decimal(11,2) DEFAULT '0.00',
  `bankAreaId` int(11) DEFAULT '0',
  `bankAreaIdPath` varchar(100) DEFAULT NULL,
  `applyStatus` tinyint(4) DEFAULT '0',
  `applyDesc` varchar(255) DEFAULT NULL,
  `applyTime` datetime DEFAULT NULL,
  `applyStep` tinyint(4) DEFAULT '1',
  `shopNotice` varchar(300) DEFAULT NULL COMMENT '店铺公告',
  `rechargeMoney` decimal(11,2) DEFAULT '0.00' COMMENT '充值金额',
  `longitude` decimal(10,7) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `mapLevel` int(11) DEFAULT '16',
  `expireDate` date DEFAULT NULL COMMENT '到期日期',
  `isPay` tinyint(4) DEFAULT '0' COMMENT '是否支付年费，0否,1是',
  `payAnnualFee` decimal(11,2) DEFAULT '0.00' COMMENT '支付年费金额',
  `isRefund` tinyint(4) DEFAULT '0' COMMENT '是否退款年费，0否，1是',
  `tradeId` int(11) DEFAULT '0' COMMENT '所属行业ID',
  PRIMARY KEY (`shopId`),
  KEY `shopStatus` (`shopStatus`,`dataFlag`) USING BTREE,
  KEY `areaIdPath` (`areaIdPath`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_shops` VALUES ('1', 'S000000001', '1', '6_76_698_', '698', '1', 'WSTMart自营超市', 'wstmart', '13888888888', 'WSTMart自营超市', 'upload/shops/2019-02/5c6fd1074ce70.png', '13888888888', '153289970', '', '燕岭路89号燕侨大厦', '24', '2343243124312412', '是暗室逢灯', '0', '', '08:30:00', '22:30:00', '1', '1', '', '1', '2016-10-08', '0.00', '0.00', '0', '0.00', '0.00', '698', '6_76_698_', '2', null, '2016-10-08 00:00:00', '1', null, '0.00', '0.0000000', '0.0000000', '15', '2021-03-31', '0', '0.00', '0', '0');