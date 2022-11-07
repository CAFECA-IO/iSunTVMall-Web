SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_seckill_time_intervals`;
CREATE TABLE `wst_seckill_time_intervals` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `startTime` time NOT NULL COMMENT '开始时间段',
  `endTime` time NOT NULL COMMENT '结束时间段',
  `dataFlag` tinyint(1) NOT NULL DEFAULT '1' COMMENT '有效状态 1:有效 -1删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `startTime` (`startTime`,`endTime`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_seckill_time_intervals_langs`;
CREATE TABLE `wst_seckill_time_intervals_langs` (
  `timeId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`timeId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `wst_seckills`;
CREATE TABLE `wst_seckills` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '秒杀活动自增ID',
  `shopId` int(10) NOT NULL COMMENT '商家ID',
  `startDate` date NOT NULL COMMENT '秒杀活动开始日期',
  `endDate` date NOT NULL COMMENT '秒杀活动结束日期',
  `isSale` tinyint(1) NOT NULL DEFAULT '1' COMMENT '上下架',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `seckillStatus` tinyint(1) NOT NULL DEFAULT '1' COMMENT '审核状态',
  `illegalRemarks` varchar(500) DEFAULT NULL COMMENT '不通过原因',
  `dataFlag` tinyint(1) NOT NULL DEFAULT '1' COMMENT '有效状态 1:有效 -1删除',
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`),
  KEY `seckillStatus` (`seckillStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_seckills_langs`;
CREATE TABLE `wst_seckills_langs` (
  `seckillId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `seckillDes` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`seckillId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `wst_seckill_goods`;
CREATE TABLE `wst_seckill_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT '0' COMMENT '店铺Id',
  `seckillId` int(11) NOT NULL DEFAULT '0' COMMENT '秒杀活动ID',
  `timeId` int(11) NOT NULL DEFAULT '0' COMMENT '秒杀时段ID',
  `goodsId` int(11) NOT NULL DEFAULT '0' COMMENT '商品Id',
  `secPrice` decimal(11,2) DEFAULT '0.00' COMMENT '秒杀价',
  `secNum` int(11) NOT NULL DEFAULT '0' COMMENT '参与秒杀商品数量',
  `secLimit` tinyint(3) NOT NULL DEFAULT '1' COMMENT '秒杀限购数量',
  `saleNum` int(11) NOT NULL DEFAULT '0' COMMENT '销量【已收货】',
  `hasBuyNum` int(11) NOT NULL DEFAULT '0' COMMENT '已购数量【包括未收货】',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '有效状态 1:有效 -1删除',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `seckillId` (`seckillId`),
  KEY `timeId` (`timeId`),
  KEY `goodsId` (`goodsId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

insert into `wst_template_msgs`(tplType,tplCode,tplContent,tplDesc) values(0,'SECKILL_ACTIVITY_ALLOW','您的秒殺活動【${TITLE}】已審核通過。','1.變量說明：${TITLE}：活動名稱。${TIME} ：活動時間。<br/>2.為空則不發送。'),
(0,'SECKILL_ACTIVITY_REJECT','您的秒殺活動【${TITLE}】因【${REASON}】審核不通過。','1.變量說明：${TITLE}：活動名稱。${TIME} ：當前時間。${REASON}：不通過原因。<br/>2.為空則不發送'),
(3,'WX_SECKILL_ACTIVITY_ALLOW','{{first.DATA}}\n活動主題：{{keyword1.DATA}}\n活動時間：{{keyword2.DATA}}\n審核結果：{{keyword3.DATA}}\n{{remark.DATA}}','1.變量說明：${TITLE}：活動名稱。${TIME} ：活動時間。${RESULT} ：審核結果。<br/>2.為空則不發送。'),
(3,'WX_SECKILL_ACTIVITY_REJECT','{{first.DATA}}\n活動主題：{{keyword1.DATA}}\n活動時間：{{keyword2.DATA}}\n審核結果：{{keyword3.DATA}}\n{{remark.DATA}}','1.變量說明：${TITLE}：活動名稱。${TIME} ：活動時間。${RESULT} ：審核結果。${REASON}：不通過原因。<br/>2.為空則不發送');


insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,1,'SECKILL_ACTIVITY_REJECT');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,4,'WX_SECKILL_ACTIVITY_REJECT');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,1,'SECKILL_ACTIVITY_ALLOW');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,4,'WX_SECKILL_ACTIVITY_ALLOW');

INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('seckill/goods/lists', 'seckill/goods/molists', 'seckill/goods/wxlists', 'seckill');
INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('seckill/goods/detail', 'seckill/goods/modetail', 'seckill/goods/wxdetail', 'seckill');

INSERT INTO `wst_crons`(cronName,cronCode,isEnable,cronJson,cronUrl,cronDesc,cronCycle,cronDay,cronWeek,cronHour,cronMinute,runTime,nextTime,author,authorUrl) VALUES ('秒杀支付超时', 'seckillPayOverTime', '1', 'b:0;', 'addon/seckill-cron-payOverTime.html', '秒杀支付超时取消订单', '2', '0', '0', '-1', '0,5,10,15,20,25,30,35,40,45,50,55', '2017-11-03 16:20:13', '2017-11-03 16:25:00','WSTMart', 'http://www.wstmart.net');
