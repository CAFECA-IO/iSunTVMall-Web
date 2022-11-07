SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_invoices`;
CREATE TABLE `wst_invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `invoiceHead` varchar(255) NOT NULL COMMENT '发票抬头',
  `invoiceCode` varchar(255) NOT NULL DEFAULT '' COMMENT '纳税人识别号',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '数据有效标记',
  `createTime` datetime NOT NULL COMMENT '数据创建时间',
  `invoiceType` tinyint(4) DEFAULT '0' COMMENT '0:普票 1:增值税发票',
  `invoiceAddr` varchar(300) DEFAULT NULL COMMENT '增值税发票注册地址（invoiceType为1时该值不为空）',
  `invoicePhoneNumber` varchar(100) DEFAULT NULL COMMENT '增值税发票注册电话（invoiceType为1时该值不为空）',
  `invoiceBankName` varchar(100) DEFAULT NULL COMMENT '增值税发票开户银行（invoiceType为1时该值不为空）',
  `invoiceBankNo` varchar(100) DEFAULT NULL COMMENT '增值税发票银行账户（invoiceType为1时该值不为空）',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
