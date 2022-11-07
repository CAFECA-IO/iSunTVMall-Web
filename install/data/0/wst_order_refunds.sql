SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_order_refunds`;
CREATE TABLE `wst_order_refunds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `refundTo` int(11) NOT NULL DEFAULT '0',
  `refundReson` int(11) NOT NULL DEFAULT '0',
  `refundOtherReson` varchar(255) DEFAULT NULL,
  `backMoney` decimal(11,2) NOT NULL DEFAULT '0.00',
  `refundTradeNo` varchar(100) DEFAULT NULL,
  `refundRemark` varchar(500) DEFAULT NULL,
  `refundTime` datetime DEFAULT NULL,
  `shopRejectReason` varchar(255) DEFAULT NULL,
  `refundStatus` tinyint(4) NOT NULL DEFAULT '0',
  `createTime` datetime NOT NULL,
  `serviceId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '售后单id',
  `isServiceRefund` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '售后单是否已退款0：未退款 1：已退款',
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
