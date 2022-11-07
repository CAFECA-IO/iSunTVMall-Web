insert into `wst_template_msgs`(tplType,tplCode,tplContent,tplDesc) values
(0,'AUCTION_GOODS_ALLOW','您的拍賣商品${GOODS}已審核通過。','1.變量說明：${GOODS}：商品名稱。${TIME} ：當前時間。<br/>2.為空則不發送。'),
(0,'AUCTION_GOODS_REJECT','您的拍賣商品${GOODS}因【${REASON}】審核不通過。','1.變量說明：${GOODS}：商品名稱。${TIME} ：當前時間。${REASON}：不通過原因。<br/>2.為空'),
(0,'AUCTION_USER_RESULT','您參加的拍賣活動【${GOODS}】拍賣結果為：${RESULT}，請留意。','1.變量說明：${GOODS}：商品名稱。${JOIN_TIME} ：競拍時間。${ASTART_TIME}：拍賣開始時間。${RESULT}：拍賣結果。<br/>2.為空則不發送。'),
(0,'AUCTION_SHOP_RESULT','妳的拍賣活動【${GOODS}】拍賣結果為：${RESULT}，請留意。','1.變量說明：${GOODS}：商品名稱。${ASTART_TIME}：拍賣開始時間。${RESULT}：拍賣結果。<br/>2.為空則不發送。'),
(3,'WX_AUCTION_GOODS_ALLOW','{{first.DATA}}\n商品名稱：{{keyword1.DATA}}\n審核時間：{{keyword2.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${TIME} ：當前時間。<br/>2.為空則不發送。'),
(3,'WX_AUCTION_GOODS_REJECT','{{first.DATA}}\n商品名稱：{{keyword1.DATA}}\n失敗原因：{{keyword2.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${TIME} ：當前時間。${REASON}：不通過原因。<br/>2.為空'),
(3,'WX_AUCTION_USER_RESULT','{{first.DATA}}\n拍賣商品：{{keyword1.DATA}}\n參與時間：{{keyword2.DATA}}\n競拍結果：{{keyword3.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${JOIN_TIME} ：競拍時間。${ASTART_TIME}：拍賣開始時間。${RESULT}：拍賣結果。<br/>2.為空則不發送。'),
(3,'WX_AUCTION_SHOP_RESULT','{{first.DATA}}\n拍賣商品：{{keyword1.DATA}}\n參與時間：{{keyword2.DATA}}\n競拍結果：{{keyword3.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${ASTART_TIME}：拍賣開始時間。${RESULT}：拍賣結果。<br/>2.為空則不發送。');

DROP TABLE IF EXISTS `wst_auction_moneys`;
CREATE TABLE `wst_auction_moneys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auctionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `cautionMoney` int(11) NOT NULL DEFAULT '0',
  `cautionStatus` tinyint(4) NOT NULL DEFAULT '0',
  `createTime` datetime NOT NULL,
  `payType` varchar(50) DEFAULT '',
  `tradeNo` varchar(100) DEFAULT NULL,
  `moneyType` tinyint(4) DEFAULT '0',
  `lockCashMoney` decimal(11,2) DEFAULT '0.00',
  `refundStatus` tinyint(4) DEFAULT '0',
  `refundTime` datetime DEFAULT NULL,
  `refundTradeNo` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auctionId` (`auctionId`,`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_auction_logs`;
CREATE TABLE `wst_auction_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auctionId` int(11) NOT NULL DEFAULT '0',
  `userId` int(11) NOT NULL,
  `payPrice` decimal(11,2) NOT NULL DEFAULT '0.00',
  `dataFlag` tinyint(4) DEFAULT '1',
  `isTop` tinyint(4) DEFAULT '0',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auctionId` (`auctionId`,`payPrice`),
  KEY `auctionId_2` (`auctionId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_auctions`;
CREATE TABLE `wst_auctions` (
  `auctionId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `goodsImg` varchar(150) DEFAULT NULL,
  `goodsVideo` varchar(150) DEFAULT NULL,
  `goodsJson` text,
  `auctionPrice` decimal(11,2) NOT NULL DEFAULT '0.00',
  `currPrice` decimal(11,2) NOT NULL DEFAULT '0.00',
  `fareInc` int(11) NOT NULL DEFAULT '0',
  `cautionMoney` int(11) NOT NULL DEFAULT '0',
  `auctionNum` int(11) NOT NULL DEFAULT '0',
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `visitNum` int(11) NOT NULL DEFAULT '0',
  `auctionStatus` tinyint(4) DEFAULT '1',
  `illegalRemarks` varchar(255) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL,
  `updateTime` datetime NOT NULL,
  `createTime` datetime NOT NULL,
  `orderId` int(11) NOT NULL DEFAULT '0',
  `bidLogId` int(11) NOT NULL DEFAULT '0',
  `isPay` tinyint(4) DEFAULT '0',
  `isClose` tinyint(4) DEFAULT '0',
  `endPayTime` datetime DEFAULT NULL,
  `isSale` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`auctionId`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_auctions_langs`;
CREATE TABLE `wst_auctions_langs` (
  `auctionId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsName` varchar(200) DEFAULT NULL,
  `auctionDesc` text,
  `goodsSeoKeywords` varchar(255) DEFAULT NULL,
  `goodsSeoDesc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`auctionId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `wst_crons`(cronName,cronCode,isEnable,cronJson,cronUrl,cronDesc,cronCycle,cronDay,cronWeek,cronHour,cronMinute,runTime,nextTime,author,authorUrl) VALUES ('完成拍賣', 'autoAuctionEnd', '1', 'a:1:{i:0;a:3:{s:10:\"fieldLabel\";s:21:\"每次執行記錄數\";s:9:\"fieldCode\";s:7:\"cronNum\";s:8:\"fieldVal\";s:1:\"5\";}}', 'addon/auction-cron-scanTask.html', '將到期的拍賣活動結束並計算出拍賣勝出者', '2', '9', '4', '-1', '0,5,10,15,20,25,30,35,40,45,50,55', '2017-03-10 16:05:59', '2017-03-10 16:10:00', 'WSTMart', 'http://www.wstmart.net');

insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,1,'AUCTION_GOODS_REJECT');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,4,'WX_AUCTION_GOODS_REJECT');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,1,'AUCTION_GOODS_ALLOW');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,4,'WX_AUCTION_GOODS_ALLOW');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(2,1,'AUCTION_SHOP_RESULT');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(2,4,'WX_AUCTION_SHOP_RESULT');

INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('auction/goods/lists', 'auction/goods/molists', 'auction/goods/wxlists', 'auction');
INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('auction/goods/detail', 'auction/goods/modetail', 'auction/goods/wxdetail', 'auction');

