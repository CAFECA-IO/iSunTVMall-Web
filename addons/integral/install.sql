
DROP TABLE IF EXISTS `wst_integral_goods`;
CREATE TABLE `wst_integral_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `goodsPrice` decimal(11,2) NOT NULL DEFAULT '0.00',
  `integralNum` int(11) NOT NULL DEFAULT '0',
  `totalNum` int(11) NOT NULL DEFAULT '0',
  `orderNum` int(11) NOT NULL DEFAULT '0',
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `integralStatus` tinyint(4) DEFAULT '1',
  `dataFlag` tinyint(4) NOT NULL,
  `updateTime` datetime NOT NULL,
  `createTime` datetime NOT NULL,
  `goodsImg` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_integral_goods_langs`;
CREATE TABLE `wst_integral_goods_langs` (
  `integralId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsName` varchar(200) DEFAULT NULL,
  `integralDesc` text,
  `goodsSeoKeywords` varchar(200) DEFAULT NULL,
  `goodsSeoDesc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`integralId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_ad_positions`(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort) VALUES ( '1', '積分商城輪播廣告', '1920', '320', '1', 'ads-integral', '1');


INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('integral/goods/lists', 'integral/goods/molists', 'integral/goods/wxlists', 'integral');
INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('integral/goods/detail', 'integral/goods/modetail', 'integral/goods/wxdetail', 'integral');
