alter table `wst_orders` add userCouponId int default 0 comment '用户使用的优惠券ID';
alter table `wst_orders` add userCouponJson text comment '优惠券说明';

DROP TABLE IF EXISTS `wst_coupon_goods`;
CREATE TABLE `wst_coupon_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `couponId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `goodsId` (`goodsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
DROP TABLE IF EXISTS `wst_coupon_cats`;
CREATE TABLE `wst_coupon_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `couponId` int(11) NOT NULL,
  `catId` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `goodsId` (`catId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
DROP TABLE IF EXISTS `wst_coupon_users`;
CREATE TABLE `wst_coupon_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `couponId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `isUse` tinyint(4) NOT NULL DEFAULT '0',
  `orderNo` varchar(50) DEFAULT NULL,
  `useTime` datetime DEFAULT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_coupons`;
CREATE TABLE `wst_coupons` (
  `couponId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT '0',
  `couponValue` int(11) NOT NULL DEFAULT '0',
  `useCondition` tinyint(4) NOT NULL DEFAULT '0',
  `useMoney` int(11) DEFAULT NULL COMMENT '0',
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `couponNum` int(11) NOT NULL DEFAULT '0',
  `limitNum` int(11) NOT NULL DEFAULT '0',
  `receiveNum` int(11) DEFAULT '0',
  `useObjects` tinyint(4) NOT NULL DEFAULT '0',
  `useObjectIds` text,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `createTime` datetime NOT NULL,
  couponType tinyint(4) default 2 comment '优惠券发放类型 1:人工发放 2:页面领取',
  PRIMARY KEY (`couponId`),
  KEY `shopId` (`shopId`,`dataFlag`),
  KEY `startDate` (`startDate`,`endDate`,`dataFlag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `wst_switchs`(homeURL,mobileURL,wechatURL,urlMark) VALUES ('coupon/coupons/index', 'coupon/coupons/moindex', 'coupon/coupons/wxindex', 'coupon');
