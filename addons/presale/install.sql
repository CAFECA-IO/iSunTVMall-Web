insert into `wst_template_msgs`(tplType,tplCode,tplContent,tplDesc) values(0,'PRESALE_GOODS_ALLOW','您的預售商品${GOODS}【${GOODS_SN}】已審核通過。','1.變量說明：${GOODS}：商品名稱。${GOODS_SN}：商品編號。${TIME} ：當前時間。<br/>2.為空則不發送。'),
(0,'PRESALE_GOODS_REJECT','您的預售商品${GOODS}【${GOODS_SN}】因【${REASON}】審核不通過。','1.變量說明：${GOODS}：商品名稱。${GOODS_SN}：商品編號。${TIME} ：當前時間。${REASON}：不通過原因。<br/>2.為空'),
(0,'PRESALE_USER_PAY_OVERTIME','您的預購單【${ORDERNO}】商品【${GOODS}】，${RESULT}，請留意。','1.變量說明：${GOODS}：商品名稱。${JOIN_TIME} ：競拍時間。${ASTART_TIME}：預售開始時間。${RESULT}：預售結果。<br/>2.為空則不發送。'),
(0,'PRESALE_SHOP_PAY_OVERTIME','預購單【${ORDERNO}】商品【${GOODS}】，${RESULT}，請留意。','1.變量說明：${GOODS}：商品名稱。${ASTART_TIME}：預售開始時間。${RESULT}：預售結果。<br/>2.為空則不發送。'),
(0,'PRESALE_END','您的預購單【${ORDERNO}】商品【${GOODS}】，請盡快完成尾款支付。','1.變量說明：${GOODS}：商品名稱。${ORDERNO}：預售訂單號。<br/>2.為空則不發送。'),
(0,'PRESALE_ORDER_USER_PAY_TIMEOUT',  '預售訂單【${ORDER_NO}】因長時間未支付定金，系統自動取消訂單。', '1.變量說明：${ORDER_NO}：訂單號。<br/>2.為空則不發送。'),

(3,'WX_PRESALE_USER_PAY_OVERTIME','{{first.DATA}}\n預售商品：{{keyword1.DATA}}\n參與時間：{{keyword2.DATA}}\n預售結果：{{keyword3.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${JOIN_TIME} ：競拍時間。${ASTART_TIME}：預售開始時間。${RESULT}：預售結果。<br/>2.為空則不發送。'),
(3,'WX_PRESALE_SHOP_PAY_OVERTIME','{{first.DATA}}\n預售商品：{{keyword1.DATA}}\n參與時間：{{keyword2.DATA}}\n預售結果：{{keyword3.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${ASTART_TIME}：預售開始時間。${RESULT}：預售結果。<br/>2.為空則不發送。'),
(3,'WX_PRESALE_END','{{first.DATA}}\n結束時間：{{keyword1.DATA}}\n預定商品：{{keyword2.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${END_TIME}：預售結束時間。<br/>2.為空則不發送。'),
(3,'WX_PRESALE_REFUND','{{first.DATA}}\n預售商品：{{keyword1.DATA}}\n商品金額：{{keyword2.DATA}}\n退款金額：{{keyword3.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${PRESALE_MONEY}：商品金額。${FREUND_MONEY} ：退款金額。<br/>2.為空'),
(3,'WX_PRESALE_ORDER_USER_PAY_TIMEOUT','{{first.DATA}}\n訂單號：{{keyword1.DATA}}\n失效原因：{{keyword2.DATA}}\n{{remark.DATA}}','1.變量說明：${ORDER_NO}：訂單號。<br/>2.為空則不發送。'),

(3,'WX_PRESALE_GOODS_ALLOW','{{first.DATA}}\n商品名稱：{{keyword1.DATA}}\n審核時間：{{keyword2.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${GOODS_SN}：商品編號。${TIME} ：當前時間。<br/>2.為空則不發送。'),
(3,'WX_PRESALE_GOODS_REJECT','{{first.DATA}}\n商品名稱：{{keyword1.DATA}}\n失敗原因：{{keyword2.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${GOODS_SN}：商品編號。${TIME} ：當前時間。${REASON}：不通過原因。<br/>2.為空');


DROP TABLE IF EXISTS `wst_presales`;
CREATE TABLE `wst_presales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL COMMENT '商品ID',
  `goodsImg` varchar(150) NOT NULL COMMENT '商品封面图片',
  `limitNum` int(11) DEFAULT '0' COMMENT '每个限制数量',
  `reduceMoney` decimal(10,2) NOT NULL DEFAULT '0' COMMENT '立付减金额',
  `goodsNum` int(11) NOT NULL DEFAULT '0' COMMENT '预售数量',
  `orderNum` int(11) DEFAULT '0' COMMENT '已预订数量',
  `saleType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '预付方式  0:全款付 1：定金付',
  `depositType` tinyint(4) DEFAULT '0' COMMENT '定金类型 0:固定金额  1:百分比',
  `depositMoney` decimal(10,2) DEFAULT '0' COMMENT '定金',
  `depositRate` int(11) DEFAULT '0' COMMENT '定金百分比',
  `endPayDays` int(11) DEFAULT '1' COMMENT '预售结束X天内付尾款',
  `startTime` datetime NOT NULL COMMENT '预售开始时间',
  `endTime` datetime NOT NULL COMMENT '预售结束时间',
  `deliverType` tinyint(11) NOT NULL DEFAULT '0' COMMENT '发货方式 0：支付成功  1：预售结束',
  `deliverDays` int(11) NOT NULL DEFAULT '1' COMMENT 'X天内发货',
  `afterGoodsStatus` tinyint(4) NOT NULL DEFAULT '1' COMMENT '活动结束后状态  1：正常上架  0：下架',
  `presaleStatus` tinyint(4) DEFAULT '0' COMMENT '预售审核状态 0：待审核  1：已审核  -1：审核不通过',
  `auditRemark` varchar(255) DEFAULT NULL COMMENT '审核不通过原因',
  `isSale` tinyint(4) DEFAULT '1',
  `dataFlag` tinyint(4) DEFAULT '1' COMMENT '有效状态  1：有效  -1：无效',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_presales_langs`;
CREATE TABLE `wst_presales_langs` (
  `presaleId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsName` varchar(200) DEFAULT NULL,
  `goodsTips` text,
  `goodsSeoKeywords` varchar(200) DEFAULT NULL,
  `goodsSeoDesc` varchar(600) DEFAULT NULL,
  PRIMARY KEY (`presaleId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_presale_orders`;
CREATE TABLE `wst_presale_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT '0' COMMENT '用户ID',
  `presaleId` int(11) DEFAULT '0' COMMENT '预售ID',
  `shopId` int(11) DEFAULT '0' COMMENT '店铺ID',
  `orderId` int(11) DEFAULT '0' COMMENT '订单ID',
  `orderNo` varchar(20) DEFAULT NULL COMMENT '订单号',
  `goodsId` int(11) DEFAULT '0' COMMENT '商品ID',
  `goodsName` varchar(500) DEFAULT '' COMMENT '商品名称',
  `goodsImg` varchar(150) DEFAULT '' COMMENT '商品图片',
  `goodsSpecId` int(11) DEFAULT '0' COMMENT '商品规格ID',
  `goodsSpecNames` varchar(500) DEFAULT '' COMMENT '商品规格名称',
  `goodsNum` int(11) DEFAULT '0' COMMENT '购买商品数量',
  `presaleStatus` tinyint(4) DEFAULT '0' COMMENT '预定状态（0：待效定金 1：待交尾款 2：预定已交尾款，-1：预定失败）',
  `failType` tinyint(4) DEFAULT '0' COMMENT '预定失败原因（ 1：逾期未交尾款 2：管理员下架商品）',
  `areaId` int(11) DEFAULT '0' COMMENT '最后一级区域Id',
  `areaIdPath` varchar(100) DEFAULT NULL COMMENT '区域Id路径',
  `orderType` tinyint(4) DEFAULT '0' COMMENT '订单类型（0:实物订单 1:非实物订单）',
  `goodsMoney` decimal(11,2) DEFAULT '0.00' COMMENT '商品总金额',
  `totalMoney` decimal(11,2) DEFAULT '0.00' COMMENT '订单总金额',
  `realTotalMoney` decimal(11,2) DEFAULT '0.00' COMMENT '实际订单总金额',
  `deliverType` tinyint(4) DEFAULT '0' COMMENT '收货方式（0:送货上门 1:自提）',
  `deliverMoney` decimal(11,2) DEFAULT NULL COMMENT '运费',
  `payType` tinyint(4) DEFAULT '0' COMMENT '支付方式(0:货到付款 1:在线支付)',
  `payFrom` varchar(20) DEFAULT NULL COMMENT '支付来源',
  `isPay` tinyint(4) DEFAULT '0' COMMENT '是否支付完（1：是 0：否）',
  `payTime` datetime DEFAULT NULL COMMENT '支付时间',
  `userName` varchar(20) DEFAULT NULL COMMENT '收货人名称',
  `userAddress` varchar(255) NOT NULL COMMENT '收件人地址',
  `userPhone` varchar(20) DEFAULT NULL COMMENT '收件人手机',
  `orderScore` int(11) NOT NULL DEFAULT '0' COMMENT '所得积分',
  `isInvoice` tinyint(4) NOT NULL DEFAULT '0'COMMENT '是否需要发票',
  `invoiceClient` varchar(255) DEFAULT NULL COMMENT '发票抬头',
  `invoiceJson` text COMMENT '发票信息',
  `orderRemarks` varchar(255) DEFAULT NULL COMMENT '订单备注',
  `orderSrc` tinyint(4) NOT NULL DEFAULT '0' COMMENT '订单来源',
  `payRand` int(11) DEFAULT '1' COMMENT '支付随机数',
  `orderunique` varchar(50) DEFAULT NULL COMMENT '订单流水号',
  `useScore` int(11) DEFAULT '0' COMMENT '使用积分',
  `scoreMoney` decimal(11,2) DEFAULT '0.00' COMMENT '抵扣的钱',
  `commissionFee` decimal(11,2) DEFAULT '0.00' COMMENT '订单佣金',
  `commissionRate` decimal(11,2) DEFAULT '0.00' COMMENT '佣金比例',
  `depositMoney` decimal(11,2) DEFAULT '0.00' COMMENT '订金金额',
  `surplusMoney` decimal(11,2) DEFAULT '0.00' COMMENT '尾款金额',
  `startPayTime` datetime NULL COMMENT '尾款支付开始时间',
  `endPayTime` datetime NULL COMMENT '尾款支付结束时间',
  `tradeNo` varchar(100) DEFAULT NULL COMMENT '在线支付交易流水',
  `createTime` datetime DEFAULT NULL COMMENT '下单时间',
  `extraJson` text COMMENT '订单附加备注',
  `lockCashMoney` decimal(11,2) DEFAULT '0.00' COMMENT '所使用的充值送金额',
  `dataFlag` tinyint(4) DEFAULT '1' COMMENT '有效状态（1：有效 -1无效）',
  `storeType` tinyint(4) DEFAULT '0' COMMENT '自提类型',
  `storeId` int(11) DEFAULT '0' COMMENT '门店ID',
  `areaCode` varchar(6) DEFAULT '' COMMENT '电话区号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `wst_presale_moneys`;
CREATE TABLE `wst_presale_moneys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `porderId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `presaleMoney` decimal(11,2) NOT NULL DEFAULT '0',
  `presaleStatus` tinyint(4) NOT NULL DEFAULT '0',
  `createTime` datetime NOT NULL,
  `payType` varchar(50) DEFAULT '',
  `tradeNo` varchar(100) DEFAULT NULL,
  `moneyType` int(11) DEFAULT '0' COMMENT '1:定金 2：尾款',
  `lockCashMoney` decimal(11,2) DEFAULT '0.00',
  `refundStatus` tinyint(4) DEFAULT '0',
  `refundTime` datetime DEFAULT NULL,
  `refundTradeNo` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `porderId` (`porderId`,`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,1,'PRESALE_GOODS_REJECT');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,4,'WX_PRESALE_GOODS_REJECT');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,1,'PRESALE_GOODS_ALLOW');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,4,'WX_PRESALE_GOODS_ALLOW');


INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('presale/goods/lists', 'presale/goods/molists', 'presale/goods/wxlists', 'presale');
INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('presale/goods/detail', 'presale/goods/modetail', 'presale/goods/wxdetail', 'presale');


INSERT INTO `wst_crons`(cronName,cronCode,isEnable,cronJson,cronUrl,cronDesc,cronCycle,cronDay,cronWeek,cronHour,cronMinute,runTime,nextTime,author,authorUrl) VALUES ('預售支付尾款超期', 'presaleOverPayTime', '1', '', 'addon/presale-cron-scanTask.html', '將到期未支付預購尾款的訂單定金沒收，結算給商家', '2', '9', '4', '-1', '0,15,30,45', '2017-03-10 16:05:59', '2020-10-09 16:10:00', 'WSTMart', 'http://www.wstmart.net');
INSERT INTO `wst_crons`(cronName,cronCode,isEnable,cronJson,cronUrl,cronDesc,cronCycle,cronDay,cronWeek,cronHour,cronMinute,runTime,nextTime,author,authorUrl) VALUES ('預售結束提醒支付尾款', 'presalePayNotice', '1', '', 'addon/presale-cron-payNotice.html', '預售結束提醒訂購用戶支付尾款', '2', '9', '4', '-1', '0', '2017-03-10 16:05:59', '2020-10-09 16:10:00', 'WSTMart', 'http://www.wstmart.net');

