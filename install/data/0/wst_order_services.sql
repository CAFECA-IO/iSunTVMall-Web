SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_order_services`;
CREATE TABLE `wst_order_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `orderId` int(10) unsigned NOT NULL COMMENT '订单id',
  `goodsServiceType` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '0：退款退货 1：退款 2：换货',
  `serviceType` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '退换货类型,数据由基础数据类型里取',
  `serviceRemark` varchar(600) DEFAULT NULL COMMENT '退换货原因',
  `serviceAnnex` text COMMENT '附件',
  `refundScore` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '本次申请可退还的积分，由系统计算得出',
  `useScoreMoney` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '使用的积分可抵扣金额',
  `getScoreMoney` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '获得的积分可抵扣金额',
  `refundMoney` decimal(11,2) DEFAULT NULL COMMENT '申请退款的金额',
  `refundableMoney` decimal(11,2) DEFAULT NULL COMMENT '售后单可退款金额',
  `isShopAgree` tinyint(4) DEFAULT '0' COMMENT '1:同意 0：不同意',
  `disagreeRemark` varchar(600) DEFAULT NULL COMMENT '商家不同意原因',
  `userAddressId` int(11) unsigned DEFAULT '0' COMMENT '用户收货地址id',
  `areaId` int(11) unsigned DEFAULT '0' COMMENT '地区id',
  `areaIdPath` varchar(255) DEFAULT NULL COMMENT '地区ID值',
  `userName` varchar(200) DEFAULT '' COMMENT '用户收货人',
  `userAddress` varchar(200) DEFAULT '' COMMENT '用户详细收货地址',
  `userPhone` varchar(200) DEFAULT '' COMMENT '用户收货电话',
  `shopAreaId` int(11) unsigned DEFAULT '0' COMMENT '商家收货地区ID',
  `shopAreaIdPath` varchar(255) DEFAULT NULL COMMENT '商家收货地区ID值',
  `shopName` varchar(200) DEFAULT '' COMMENT '商家收货人',
  `shopAddress` varchar(200) DEFAULT '' COMMENT '商家详细收货地址',
  `shopPhone` varchar(200) DEFAULT '' COMMENT '商家收货电话',
  `isUserSendGoods` tinyint(4) DEFAULT '0' COMMENT '0：未发货 1：已发货',
  `expressType` tinyint(4) DEFAULT '0' COMMENT '0：无需物流  1：快递',
  `expressId` int(11) unsigned DEFAULT '0' COMMENT '买家快递ID',
  `expressNo` varchar(200) DEFAULT '' COMMENT '买家物流单号',
  `isShopAccept` tinyint(4) DEFAULT '0' COMMENT '商家是否收到货 -1：拒收  0：未收货  1：已收货',
  `shopRejectType` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商家拒收类型,数据由基础数据类型里取',
  `shopRejectOther` varchar(600) DEFAULT NULL COMMENT '商家拒收原因,选择“其他”的时候填写文字',
  `shopRejectImg` varchar(150) DEFAULT NULL COMMENT '拒收时的货物图片',
  `isShopSend` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '商家是否发货 0：未发货 1：已发货',
  `shopExpressType` tinyint(4) DEFAULT '0' COMMENT '0：无需物流  1：快递',
  `shopExpressId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商家快递ID',
  `shopExpressNo` varchar(200) DEFAULT '' COMMENT '商家快递单号',
  `isUserAccept` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '-1：拒收 0：未收货  1：已收货',
  `userRejectType` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '用户拒收原因,数据由基础数据类型里取',
  `userRejectOther` varchar(600) DEFAULT NULL COMMENT '用户拒收原因,选择“其他”的时候填写文字',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `isClose` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否结束 0：进行中  1:已结束',
  `serviceStatus` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态备注：0：待商家审核  1：等待用户发货 2：等待商家收货 3：等待商家发货  4：等待用户收货  5：完成退款/退货  6：商家已确认收货 7：商家受理，等待管理员退款',
  `shopAcceptExpireTime` datetime DEFAULT NULL COMMENT '商家受理期限',
  `userSendExpireTime` datetime DEFAULT NULL COMMENT '用户发货期限',
  `shopReceiveExpireTime` datetime DEFAULT NULL COMMENT '商家收货期限',
  `getScoreVal` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '本次申请的商品,购买时获得的积分数',
  `userReceiveExpireTime` datetime DEFAULT NULL COMMENT '用户收货期限',
  `shopSendExpireTime` datetime DEFAULT NULL COMMENT '商家发货期限',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8mb4;