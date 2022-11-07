insert into `wst_template_msgs`(tplType,tplCode,tplContent,tplDesc) values(0,'GROUPON_GOODS_ALLOW','您的團購商品${GOODS}【${GOODS_SN}】已審核通過。','1.變量說明：${GOODS}：商品名稱。${GOODS_SN}：商品編號。${TIME} ：當前時間。<br/>2.為空則不發送。'),
(0,'GROUPON_GOODS_REJECT','您的團購商品${GOODS}【${GOODS_SN}】因【${REASON}】審核不通過。','1.變量說明：${GOODS}：商品名稱。${GOODS_SN}：商品編號。${TIME} ：當前時間。${REASON}：不通過原因。<br/>2.為空'),
(3,'WX_GROUPON_GOODS_ALLOW','{{first.DATA}}\n商品名稱：{{keyword1.DATA}}\n審核時間：{{keyword2.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${GOODS_SN}：商品編號。${TIME} ：當前時間。<br/>2.為空則不發送。'),
(3,'WX_GROUPON_GOODS_REJECT','{{first.DATA}}\n商品名稱：{{keyword1.DATA}}\n失敗原因：{{keyword2.DATA}}\n{{remark.DATA}}','1.變量說明：${GOODS}：商品名稱。${GOODS_SN}：商品編號。${TIME} ：當前時間。${REASON}：不通過原因。<br/>2.為空');
DROP TABLE IF EXISTS `wst_groupons`;
CREATE TABLE `wst_groupons` (
  `grouponId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `grouponPrice` decimal(11,2) NOT NULL DEFAULT '0.00',
  `grouponNum` int(11) NOT NULL DEFAULT '0',
  `orderNum` int(11) NOT NULL DEFAULT '0',
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `grouponStatus` tinyint(4) DEFAULT '1',
  `illegalRemarks` varchar(255) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL,
  `updateTime` datetime NOT NULL,
  `createTime` datetime NOT NULL,
  `limitNum` int(11) NOT NULL DEFAULT '0' COMMENT '每人可团数量',
  `isSale` tinyint(4) DEFAULT '1',
  `goodsImg` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`grouponId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_groupons_langs`;
CREATE TABLE `wst_groupons_langs` (
  `grouponId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsName` varchar(200) DEFAULT NULL,
  `grouponDesc` text,
  `goodsSeoKeywords` varchar(200) DEFAULT NULL,
  `goodsSeoDesc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`grouponId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,1,'GROUPON_GOODS_REJECT');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,4,'WX_GROUPON_GOODS_REJECT');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,1,'GROUPON_GOODS_ALLOW');
insert into `wst_shop_message_cats`(msgDataId,msgType,msgCode) values(1,4,'WX_GROUPON_GOODS_ALLOW');

INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('groupon/goods/lists', 'groupon/goods/molists', 'groupon/goods/wxlists', 'groupon');
INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('groupon/goods/detail', 'groupon/goods/modetail', 'groupon/goods/wxdetail', 'groupon');


INSERT INTO `wst_ad_positions`(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort) VALUES ( '1', '團購頂部廣告', '1920', '320', '1', 'ads-groupon', '1');
