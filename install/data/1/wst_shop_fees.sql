SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_shop_fees`;
CREATE TABLE `wst_shop_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  `money` decimal(11,0) DEFAULT '0',
  `tradeNo` varchar(100) DEFAULT NULL,
  `isRefund` tinyint(4) DEFAULT '0',
  `logMoneyId` int(11) DEFAULT '0',
  `remark` varchar(255) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `lockCashMoney` decimal(11,2) DEFAULT '0.00' COMMENT '所使用的充值送金额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
