-- -------------------------------------------------------------
-- TablePlus 5.1.0(468)
--
-- https://tableplus.com/
--
-- Database: isuntvmall
-- Generation Time: 2022-11-14 12:23:18.2110
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


DROP TABLE IF EXISTS `wst_accreds`;
CREATE TABLE `wst_accreds` (
  `accredId` int(11) NOT NULL AUTO_INCREMENT,
  `accredImg` varchar(150) NOT NULL DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`accredId`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_accreds_langs`;
CREATE TABLE `wst_accreds_langs` (
  `accredId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `accredName` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`accredId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_ad_positions`;
CREATE TABLE `wst_ad_positions` (
  `positionId` int(11) NOT NULL AUTO_INCREMENT,
  `positionType` tinyint(4) NOT NULL DEFAULT 0,
  `positionName` varchar(100) NOT NULL,
  `positionWidth` int(11) NOT NULL DEFAULT 0,
  `positionHeight` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `positionCode` varchar(50) DEFAULT '',
  `apSort` int(11) NOT NULL,
  PRIMARY KEY (`positionId`),
  KEY `dataFlag` (`positionType`) USING BTREE,
  KEY `positionCode` (`positionCode`)
) ENGINE=InnoDB AUTO_INCREMENT=599 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_addons`;
CREATE TABLE `wst_addons` (
  `addonId` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(40) NOT NULL COMMENT '插件名或标识',
  `title` varchar(20) NOT NULL DEFAULT '' COMMENT '中文名',
  `description` text DEFAULT NULL COMMENT '插件描述',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `config` text DEFAULT NULL COMMENT '配置',
  `author` varchar(40) DEFAULT '' COMMENT '作者',
  `version` varchar(20) DEFAULT '' COMMENT '版本号',
  `createTime` datetime NOT NULL COMMENT '安装时间',
  `dataFlag` tinyint(4) DEFAULT 1,
  `isConfig` tinyint(4) DEFAULT 0,
  `updateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`addonId`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_ads`;
CREATE TABLE `wst_ads` (
  `adId` int(11) NOT NULL AUTO_INCREMENT,
  `adPositionId` int(11) NOT NULL DEFAULT 0,
  `adFile` varchar(150) NOT NULL,
  `adName` varchar(100) NOT NULL,
  `adURL` varchar(255) DEFAULT NULL,
  `adStartDate` date NOT NULL,
  `adEndDate` date NOT NULL,
  `adSort` int(11) NOT NULL DEFAULT 0,
  `adClickNum` int(11) NOT NULL DEFAULT 0,
  `positionType` tinyint(4) DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `subTitle` varchar(255) DEFAULT NULL,
  `isNft` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`adId`),
  KEY `adPositionId` (`adPositionId`,`adStartDate`,`adEndDate`),
  KEY `adPositionId_2` (`adPositionId`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_app_screens`;
CREATE TABLE `wst_app_screens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `screenName` varchar(50) NOT NULL COMMENT '页面名称',
  `explain` text DEFAULT NULL COMMENT '参数说明',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COMMENT='App可供跳转页面表';

DROP TABLE IF EXISTS `wst_app_session`;
CREATE TABLE `wst_app_session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `tokenId` varchar(32) DEFAULT NULL,
  `startTime` datetime DEFAULT NULL,
  `deviceId` varchar(50) DEFAULT NULL,
  `platform` tinyint(4) NOT NULL DEFAULT 3 COMMENT '登录来源 3:android 4:ios',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_areas`;
CREATE TABLE `wst_areas` (
  `areaId` int(11) NOT NULL,
  `parentId` int(11) NOT NULL,
  `areaName` varchar(100) NOT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `areaSort` int(11) NOT NULL DEFAULT 0,
  `areaKey` char(10) DEFAULT NULL,
  `areaType` tinyint(4) NOT NULL DEFAULT 1,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_areas_bk`;
CREATE TABLE `wst_areas_bk` (
  `areaId` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL,
  `areaName` varchar(100) NOT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `areaSort` int(11) NOT NULL DEFAULT 0,
  `areaKey` char(10) DEFAULT NULL,
  `areaType` tinyint(4) NOT NULL DEFAULT 1,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`areaId`),
  KEY `isShow` (`isShow`,`dataFlag`),
  KEY `areaType` (`areaType`),
  KEY `parentId` (`parentId`)
) ENGINE=InnoDB AUTO_INCREMENT=820307 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_article_cats`;
CREATE TABLE `wst_article_cats` (
  `catId` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL DEFAULT 0,
  `catType` tinyint(4) NOT NULL DEFAULT 0,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `catSort` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`catId`),
  KEY `isShow` (`catType`,`dataFlag`,`isShow`) USING BTREE,
  KEY `parentId` (`dataFlag`,`parentId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=407 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_article_cats_langs`;
CREATE TABLE `wst_article_cats_langs` (
  `catId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `catName` varchar(255) NOT NULL,
  PRIMARY KEY (`catId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_articles`;
CREATE TABLE `wst_articles` (
  `articleId` int(11) NOT NULL AUTO_INCREMENT,
  `catId` int(11) NOT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `staffId` int(11) NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `solve` int(10) unsigned DEFAULT 0 COMMENT '解决数',
  `unsolve` int(10) unsigned DEFAULT 0 COMMENT '未解决数',
  `coverImg` varchar(150) DEFAULT NULL,
  `visitorNum` int(10) unsigned DEFAULT 0 COMMENT '文章浏览量',
  `TypeStatus` int(10) DEFAULT 1,
  `likeNum` int(50) DEFAULT 0,
  `catSort` int(11) DEFAULT 0,
  `isHide` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`articleId`),
  KEY `catId` (`catId`,`isShow`)
) ENGINE=InnoDB AUTO_INCREMENT=1003 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_articles_langs`;
CREATE TABLE `wst_articles_langs` (
  `articleId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `articleTitle` varchar(255) DEFAULT NULL,
  `articleContent` longtext DEFAULT NULL,
  `articleKey` varchar(600) DEFAULT NULL,
  `articleDesc` varchar(600) DEFAULT NULL,
  PRIMARY KEY (`articleId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_attributes`;
CREATE TABLE `wst_attributes` (
  `attrId` int(11) NOT NULL AUTO_INCREMENT,
  `goodsCatId` int(11) NOT NULL DEFAULT 0,
  `goodsCatPath` varchar(100) NOT NULL,
  `attrType` tinyint(4) NOT NULL DEFAULT 0,
  `attrSort` int(11) NOT NULL DEFAULT 0,
  `isShow` tinyint(4) DEFAULT 1,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `shopId` int(11) DEFAULT 0,
  PRIMARY KEY (`attrId`),
  KEY `shopId` (`goodsCatId`,`isShow`,`dataFlag`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1472 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_attributes_langs`;
CREATE TABLE `wst_attributes_langs` (
  `attrId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `attrName` varchar(100) DEFAULT NULL,
  `attrVal` text DEFAULT NULL,
  PRIMARY KEY (`attrId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_auction_logs`;
CREATE TABLE `wst_auction_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auctionId` int(11) NOT NULL DEFAULT 0,
  `userId` int(11) NOT NULL,
  `payPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `dataFlag` tinyint(4) DEFAULT 1,
  `isTop` tinyint(4) DEFAULT 0,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auctionId` (`auctionId`,`payPrice`),
  KEY `auctionId_2` (`auctionId`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_auction_moneys`;
CREATE TABLE `wst_auction_moneys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auctionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `cautionMoney` int(11) NOT NULL DEFAULT 0,
  `cautionStatus` tinyint(4) NOT NULL DEFAULT 0,
  `createTime` datetime NOT NULL,
  `payType` varchar(50) DEFAULT '',
  `tradeNo` varchar(100) DEFAULT NULL,
  `moneyType` tinyint(4) DEFAULT 0,
  `lockCashMoney` decimal(11,2) DEFAULT 0.00,
  `refundStatus` tinyint(4) DEFAULT 0,
  `refundTime` datetime DEFAULT NULL,
  `refundTradeNo` varchar(100) DEFAULT NULL,
  `outTradeNo` varchar(50) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `auctionId` (`auctionId`,`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_auctions`;
CREATE TABLE `wst_auctions` (
  `auctionId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `goodsImg` varchar(150) DEFAULT NULL,
  `goodsVideo` varchar(150) DEFAULT NULL,
  `goodsJson` text DEFAULT NULL,
  `auctionPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `currPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `fareInc` int(11) NOT NULL DEFAULT 0,
  `cautionMoney` int(11) NOT NULL DEFAULT 0,
  `auctionNum` int(11) NOT NULL DEFAULT 0,
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `visitNum` int(11) NOT NULL DEFAULT 0,
  `auctionStatus` tinyint(4) DEFAULT 1,
  `illegalRemarks` varchar(255) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL,
  `updateTime` datetime NOT NULL,
  `createTime` datetime NOT NULL,
  `orderId` int(11) NOT NULL DEFAULT 0,
  `bidLogId` int(11) NOT NULL DEFAULT 0,
  `isPay` tinyint(4) DEFAULT 0,
  `isClose` tinyint(4) DEFAULT 0,
  `endPayTime` datetime DEFAULT NULL,
  `isSale` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`auctionId`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_auctions_langs`;
CREATE TABLE `wst_auctions_langs` (
  `auctionId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsName` varchar(200) DEFAULT NULL,
  `auctionDesc` text DEFAULT NULL,
  `goodsSeoKeywords` varchar(255) DEFAULT NULL,
  `goodsSeoDesc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`auctionId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_auto_replys`;
CREATE TABLE `wst_auto_replys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `shopId` int(11) NOT NULL COMMENT '店铺id',
  `keyword` varchar(50) NOT NULL COMMENT '触发的关键字',
  `replyContent` text DEFAULT NULL COMMENT '回复内容',
  `createTime` datetime DEFAULT NULL COMMENT '数据创建时间',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '删除标记',
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_banks`;
CREATE TABLE `wst_banks` (
  `bankId` int(11) NOT NULL AUTO_INCREMENT,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `bankImg` varchar(150) DEFAULT NULL COMMENT '银行图标',
  `bankCode` varchar(100) DEFAULT NULL COMMENT '银行代码',
  `isShow` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`bankId`),
  KEY `bankFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_banks_langs`;
CREATE TABLE `wst_banks_langs` (
  `bankId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `bankName` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`bankId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_brand_applys`;
CREATE TABLE `wst_brand_applys` (
  `applyId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `brandId` int(11) DEFAULT 0,
  `brandName` varchar(100) NOT NULL,
  `brandImg` varchar(150) NOT NULL,
  `brandDesc` text DEFAULT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `isNew` tinyint(4) NOT NULL DEFAULT 1,
  `accreditImg` varchar(255) DEFAULT NULL,
  `applyStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '申请状态 0:待审核 1:已审核 -1:不通过',
  `applyDesc` varchar(255) DEFAULT NULL,
  `catIds` varchar(150) NOT NULL,
  PRIMARY KEY (`applyId`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_brands`;
CREATE TABLE `wst_brands` (
  `brandId` int(11) NOT NULL AUTO_INCREMENT,
  `brandName` varchar(100) NOT NULL,
  `brandImg` varchar(150) NOT NULL,
  `brandDesc` text DEFAULT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `sortNo` int(11) DEFAULT 0,
  PRIMARY KEY (`brandId`),
  KEY `brandFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_carts`;
CREATE TABLE `wst_carts` (
  `cartId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT 0,
  `isCheck` tinyint(4) NOT NULL DEFAULT 1,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `goodsSpecId` varchar(200) NOT NULL DEFAULT '0',
  `cartNum` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cartId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=302 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_cash_configs`;
CREATE TABLE `wst_cash_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `targetType` tinyint(4) NOT NULL DEFAULT 0,
  `targetId` int(11) NOT NULL,
  `accType` tinyint(4) NOT NULL DEFAULT 0,
  `accTargetId` int(11) NOT NULL DEFAULT 0,
  `accAreaId` int(11) DEFAULT NULL,
  `accNo` varchar(100) NOT NULL,
  `accUser` varchar(100) NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `targetType` (`targetType`,`targetId`,`dataFlag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_cash_draws`;
CREATE TABLE `wst_cash_draws` (
  `cashId` int(11) NOT NULL AUTO_INCREMENT,
  `cashNo` varchar(50) NOT NULL,
  `targetType` tinyint(4) NOT NULL DEFAULT 0,
  `targetId` int(11) NOT NULL DEFAULT 0,
  `money` decimal(11,2) NOT NULL DEFAULT 0.00,
  `accType` tinyint(4) NOT NULL DEFAULT 0,
  `accTargetName` varchar(100) DEFAULT NULL,
  `accAreaName` varchar(100) DEFAULT NULL,
  `accNo` varchar(100) NOT NULL,
  `accUser` varchar(100) DEFAULT NULL,
  `cashSatus` tinyint(4) NOT NULL DEFAULT 0,
  `cashRemarks` varchar(255) DEFAULT NULL,
  `cashConfigId` int(11) NOT NULL,
  `createTime` datetime NOT NULL,
  `commission` decimal(11,2) DEFAULT 0.00,
  `actualMoney` decimal(11,2) DEFAULT 0.00,
  `commissionRate` tinyint(4) DEFAULT 0,
  `sendData` text DEFAULT NULL COMMENT '传出内容',
  `returnData` text DEFAULT NULL COMMENT '返回内容',
  `returnMsg` varchar(300) DEFAULT NULL COMMENT '返回提示',
  `payTime` datetime DEFAULT NULL COMMENT '付款时间',
  `payNo` varchar(300) DEFAULT NULL COMMENT '付款流水',
  `accTargetId` int(11) DEFAULT 0 COMMENT '银行ID',
  `payFee` int(11) DEFAULT 0 COMMENT '手续费（分）',
  `incNo` int(11) DEFAULT 0 COMMENT '订单失败递增序号，重新付款的时候用到',
  `queryData` text DEFAULT NULL COMMENT '查询状态内容，付款到银行卡会用到',
  `queryReturnData` text DEFAULT NULL COMMENT '返回的查询状态内容，付款到银行卡会用到',
  `cashType` int(11) DEFAULT 0 COMMENT '1:商家订单提现;2:大店佣金提现',
  PRIMARY KEY (`cashId`),
  KEY `targetType` (`targetType`,`targetId`),
  KEY `cashNo` (`cashNo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_cash_settlements`;
CREATE TABLE `wst_cash_settlements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cashId` int(11) DEFAULT 0,
  `settlementId` int(11) DEFAULT 0,
  `isMarket` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_cat_brands`;
CREATE TABLE `wst_cat_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catId` int(11) DEFAULT NULL,
  `brandId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `catId` (`catId`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_cat_shops`;
CREATE TABLE `wst_cat_shops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `catId` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `catId` (`catId`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=709 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_cat_suppliers`;
CREATE TABLE `wst_cat_suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL DEFAULT 0,
  `catId` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `catId` (`catId`),
  KEY `supplierId` (`supplierId`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_ccgw_address`;
CREATE TABLE `wst_ccgw_address` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userId` bigint(20) DEFAULT NULL,
  `ccAddress` varchar(150) DEFAULT NULL,
  `ccType` varchar(20) DEFAULT NULL,
  `createTime` datetime DEFAULT NULL,
  `dataId` varchar(50) DEFAULT NULL,
  `payMoney` decimal(11,2) DEFAULT 0.00,
  `pkey` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_ccgw_address_trans`;
CREATE TABLE `wst_ccgw_address_trans` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `addressId` bigint(20) DEFAULT NULL,
  `ccTxid` varchar(150) DEFAULT NULL,
  `ccAddress` varchar(150) DEFAULT NULL,
  `ccType` varchar(20) DEFAULT NULL,
  `ccAmount` decimal(20,8) DEFAULT 0.00000000,
  `createTime` datetime NOT NULL,
  `dataStatus` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_charge_items`;
CREATE TABLE `wst_charge_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chargeMoney` int(11) DEFAULT 0,
  `giveMoney` decimal(11,1) DEFAULT 0.0,
  `itemSort` int(11) DEFAULT 0,
  `dataFlag` tinyint(4) DEFAULT 1,
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_combination_goods`;
CREATE TABLE `wst_combination_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `combineId` int(11) NOT NULL COMMENT '商品组合主表ID',
  `goodsType` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1:主商品  0：搭配商品',
  `goodsId` int(11) NOT NULL COMMENT '商品ID',
  `reduceMoney` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '优惠价',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_combinations`;
CREATE TABLE `wst_combinations` (
  `combineId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL COMMENT '商家ID',
  `combineImg` varchar(150) NOT NULL COMMENT '封面图片',
  `combineType` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0：自由搭配  1：组合套餐',
  `combineStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1:进行中 0：暂停',
  `isAdvance` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否提前预热   0：否 1：是',
  `advanceDay` int(11) NOT NULL DEFAULT 0 COMMENT '提前天数',
  `startTime` datetime NOT NULL COMMENT '开始日期',
  `endTime` datetime NOT NULL COMMENT '结束日期',
  `isFreeShipping` tinyint(4) DEFAULT 0 COMMENT '0：包邮 1:不包邮',
  `combineOrder` int(11) DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1:有效 -1:删除',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`combineId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_combinations_langs`;
CREATE TABLE `wst_combinations_langs` (
  `combineId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `combineName` varchar(50) DEFAULT NULL,
  `combineDesc` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`combineId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_coupon_cats`;
CREATE TABLE `wst_coupon_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `couponId` int(11) NOT NULL,
  `catId` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `goodsId` (`catId`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_coupon_goods`;
CREATE TABLE `wst_coupon_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `couponId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `goodsId` (`goodsId`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_coupon_users`;
CREATE TABLE `wst_coupon_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `couponId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `isUse` tinyint(4) NOT NULL DEFAULT 0,
  `orderNo` varchar(50) DEFAULT NULL,
  `useTime` datetime DEFAULT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_coupons`;
CREATE TABLE `wst_coupons` (
  `couponId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `couponValue` int(11) NOT NULL DEFAULT 0,
  `useCondition` tinyint(4) NOT NULL DEFAULT 0,
  `useMoney` int(11) DEFAULT NULL COMMENT '0',
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `couponNum` int(11) NOT NULL DEFAULT 0,
  `limitNum` int(11) NOT NULL DEFAULT 0,
  `receiveNum` int(11) DEFAULT 0,
  `useObjects` tinyint(4) NOT NULL DEFAULT 0,
  `useObjectIds` text DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `couponType` tinyint(4) DEFAULT 2 COMMENT '优惠券发放类型 1:人工发放 2:页面领取',
  PRIMARY KEY (`couponId`),
  KEY `shopId` (`shopId`,`dataFlag`),
  KEY `startDate` (`startDate`,`endDate`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_crons`;
CREATE TABLE `wst_crons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cronName` varchar(100) NOT NULL,
  `cronCode` varchar(50) DEFAULT NULL COMMENT '计划任务代码',
  `isEnable` tinyint(4) NOT NULL DEFAULT 0,
  `isRunning` tinyint(4) NOT NULL DEFAULT 0,
  `cronJson` text DEFAULT NULL,
  `cronUrl` varchar(255) NOT NULL,
  `cronDesc` varchar(255) DEFAULT NULL,
  `cronCycle` tinyint(4) NOT NULL DEFAULT 0,
  `cronDay` tinyint(4) DEFAULT 1,
  `cronWeek` tinyint(4) DEFAULT 0,
  `cronHour` tinyint(4) DEFAULT NULL,
  `cronMinute` varchar(255) DEFAULT NULL,
  `runTime` varchar(20) DEFAULT NULL,
  `nextTime` varchar(20) DEFAULT NULL,
  `isRunSuccess` tinyint(4) NOT NULL DEFAULT 1,
  `author` varchar(255) DEFAULT NULL,
  `authorUrl` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_custom_page_decoration`;
CREATE TABLE `wst_custom_page_decoration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageId` int(11) NOT NULL,
  `name` varchar(32) NOT NULL DEFAULT '',
  `attr` text NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `sort` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_custom_pages`;
CREATE TABLE `wst_custom_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageName` varchar(32) NOT NULL DEFAULT '',
  `isIndex` tinyint(4) NOT NULL DEFAULT 0,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `pageType` tinyint(4) NOT NULL DEFAULT 1,
  `attr` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_data_cats`;
CREATE TABLE `wst_data_cats` (
  `catId` int(11) NOT NULL AUTO_INCREMENT,
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '数据有效标志1:有效 -1:无效',
  `catCode` varchar(255) NOT NULL COMMENT '数据分类代码',
  PRIMARY KEY (`catId`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_data_cats_langs`;
CREATE TABLE `wst_data_cats_langs` (
  `catId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `catName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`catId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_datas`;
CREATE TABLE `wst_datas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catId` int(11) NOT NULL DEFAULT 0,
  `dataVal` varchar(255) NOT NULL,
  `dataSort` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '数据有效标志1:有效 -1:无效',
  `subCatId` int(11) DEFAULT 0,
  `subDataVal` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `catId` (`catId`)
) ENGINE=InnoDB AUTO_INCREMENT=401 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_datas_langs`;
CREATE TABLE `wst_datas_langs` (
  `dataId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `dataName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`dataId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_dialog_contents`;
CREATE TABLE `wst_dialog_contents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `dialogId` int(10) unsigned NOT NULL COMMENT '会话id',
  `type` varchar(10) DEFAULT 'chat' COMMENT 'chat 聊天  message留言',
  `content` text DEFAULT NULL COMMENT '会话内容(序列化后)类似 serialize(array("content"=>"发送内容","from"=>"用户id","serviceId"=>"客服id"))',
  `createTime` datetime NOT NULL COMMENT '会话创建时间',
  `isRead` tinyint(4) NOT NULL DEFAULT 0 COMMENT '消息是否已读 1:已读 0:未读',
  PRIMARY KEY (`id`),
  KEY `dialogId` (`dialogId`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_dialogs`;
CREATE TABLE `wst_dialogs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '会话id',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `serviceId` varchar(50) NOT NULL COMMENT '客服id',
  `shopId` int(10) unsigned NOT NULL COMMENT '店铺id',
  `createTime` datetime NOT NULL COMMENT '会话创建时间',
  `userDel` tinyint(4) NOT NULL DEFAULT 0 COMMENT '用户删除对话标识：0:未删除 1:已删除',
  `shopDel` tinyint(4) NOT NULL DEFAULT 0 COMMENT '商家删除对话标识：0:未删除 1:已删除',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `serviceId` (`serviceId`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_disable_keywords`;
CREATE TABLE `wst_disable_keywords` (
  `keywords` varchar(255) NOT NULL COMMENT '禁用关键词,多个关键词以,进行分割',
  `createTime` datetime DEFAULT NULL COMMENT '数据创建时间',
  KEY `keywords` (`keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_express`;
CREATE TABLE `wst_express` (
  `expressId` int(11) NOT NULL AUTO_INCREMENT,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `expressCode` varchar(50) DEFAULT '',
  `isShow` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`expressId`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=573 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_express_langs`;
CREATE TABLE `wst_express_langs` (
  `expressId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `expressName` varchar(50) NOT NULL,
  PRIMARY KEY (`expressId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_favorites`;
CREATE TABLE `wst_favorites` (
  `favoriteId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT 0,
  `goodsId` int(11) DEFAULT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`favoriteId`),
  UNIQUE KEY `favoriteId` (`userId`,`goodsId`) USING BTREE,
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_feedbacks`;
CREATE TABLE `wst_feedbacks` (
  `feedbackId` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `feedbackType` int(4) DEFAULT 0 COMMENT '反馈类型',
  `feedbackContent` text DEFAULT NULL COMMENT '反馈内容',
  `userId` int(11) DEFAULT NULL COMMENT '反馈者ID',
  `contactInfo` varchar(100) DEFAULT NULL COMMENT '联系方式 手机/qq/微信',
  `createTime` datetime DEFAULT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '1:有效  -1:删除',
  `feedbackStatus` tinyint(4) DEFAULT 0 COMMENT '处理状态 0:未处理 1:已处理',
  `staffId` int(11) DEFAULT 0 COMMENT '处理人',
  `handleTime` datetime DEFAULT NULL COMMENT '处理时间',
  `handleContent` text DEFAULT NULL COMMENT '处理结果',
  PRIMARY KEY (`feedbackId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_friendlinks`;
CREATE TABLE `wst_friendlinks` (
  `friendlinkId` int(11) NOT NULL AUTO_INCREMENT,
  `friendlinkIco` varchar(150) DEFAULT '',
  `friendlinkName` varchar(50) NOT NULL DEFAULT '',
  `friendlinkUrl` varchar(150) NOT NULL DEFAULT '',
  `friendlinkSort` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`friendlinkId`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_goods`;
CREATE TABLE `wst_goods` (
  `goodsId` int(11) NOT NULL AUTO_INCREMENT,
  `goodsSn` varchar(20) NOT NULL,
  `productNo` varchar(20) NOT NULL,
  `goodsImg` varchar(150) NOT NULL,
  `shopId` int(11) NOT NULL,
  `goodsType` tinyint(4) NOT NULL DEFAULT 0,
  `marketPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `shopPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `warnStock` int(11) NOT NULL DEFAULT 0,
  `goodsStock` int(11) NOT NULL DEFAULT 0,
  `goodsUnit` char(10) NOT NULL,
  `isSale` tinyint(4) NOT NULL DEFAULT 1,
  `isBest` tinyint(4) NOT NULL DEFAULT 0,
  `isHot` tinyint(4) NOT NULL DEFAULT 0,
  `isNew` tinyint(4) NOT NULL DEFAULT 0,
  `isRecom` tinyint(4) DEFAULT 0,
  `goodsCatIdPath` varchar(255) DEFAULT NULL,
  `goodsCatId` int(11) NOT NULL,
  `shopCatId1` int(11) NOT NULL,
  `shopCatId2` int(11) NOT NULL,
  `brandId` int(11) DEFAULT 0,
  `goodsStatus` tinyint(4) NOT NULL DEFAULT 0,
  `saleNum` int(11) NOT NULL DEFAULT 0,
  `saleTime` datetime NOT NULL,
  `visitNum` int(11) DEFAULT 0,
  `appraiseNum` int(11) DEFAULT 0,
  `isSpec` tinyint(4) NOT NULL DEFAULT 0,
  `gallery` text DEFAULT NULL,
  `illegalRemarks` varchar(255) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `isFreeShipping` tinyint(4) DEFAULT 0,
  `goodsSerachKeywords` text DEFAULT NULL,
  `goodsVideo` varchar(150) DEFAULT NULL,
  `costPrice` decimal(11,2) DEFAULT 0.00,
  `goodsVideoThumb` varchar(150) DEFAULT '',
  `shippingFeeType` tinyint(4) DEFAULT 1 COMMENT '计价方式 1:计件 2:重量 3:体积',
  `goodsWeight` decimal(11,2) DEFAULT 0.00 COMMENT '商品重量',
  `goodsVolume` decimal(11,2) DEFAULT 0.00 COMMENT '商品体积',
  `shopExpressId` int(11) DEFAULT 0 COMMENT '店铺快递公司ID',
  `collectNum` int(11) DEFAULT 0,
  `isNft` tinyint(4) DEFAULT 0,
  `nftJson` text DEFAULT NULL,
  `detailGoodsImg` varchar(150) DEFAULT '' COMMENT '非NFT商品详情主图',
  `author` varchar(50) DEFAULT '' COMMENT '作者名称',
  `showPrice` varchar(50) DEFAULT '1' COMMENT '首页是否显商品价格（1：是 0：否）',
  `canSaleStatus` tinyint(4) DEFAULT 1,
  `isDistribut` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否為分銷商品',
  `commission` decimal(11,2) DEFAULT 0.00 COMMENT '商品佣金',
  PRIMARY KEY (`goodsId`),
  KEY `shopId` (`shopId`) USING BTREE,
  KEY `goodsStatus` (`goodsStatus`,`dataFlag`,`isSale`) USING BTREE,
  KEY `goodsCatIdPath` (`goodsCatIdPath`)
) ENGINE=InnoDB AUTO_INCREMENT=1336 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_goods_appraises`;
CREATE TABLE `wst_goods_appraises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `orderId` int(11) NOT NULL DEFAULT 0,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `goodsSpecId` int(11) NOT NULL DEFAULT 0,
  `userId` int(11) NOT NULL DEFAULT 0,
  `goodsScore` int(11) NOT NULL DEFAULT 0,
  `serviceScore` int(11) NOT NULL DEFAULT 0,
  `timeScore` int(11) NOT NULL DEFAULT 0,
  `content` text NOT NULL,
  `shopReply` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `replyTime` date DEFAULT NULL,
  `orderGoodsId` int(10) unsigned NOT NULL COMMENT '订单商品表Id',
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`),
  KEY `goodsId` (`goodsId`,`goodsSpecId`,`dataFlag`,`isShow`) USING BTREE,
  KEY `userId` (`userId`),
  KEY `orderGoodsId` (`orderGoodsId`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_goods_attributes`;
CREATE TABLE `wst_goods_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `attrId` int(11) NOT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`),
  KEY `goodsId` (`goodsId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19076 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_goods_attributes_langs`;
CREATE TABLE `wst_goods_attributes_langs` (
  `goodsAttrId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `attrVal` text DEFAULT NULL,
  PRIMARY KEY (`goodsAttrId`,`langId`,`goodsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `wst_goods_cats`;
CREATE TABLE `wst_goods_cats` (
  `catId` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `isFloor` tinyint(4) NOT NULL DEFAULT 1,
  `catSort` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `commissionRate` decimal(11,2) DEFAULT -1.00,
  `catImg` varchar(150) DEFAULT NULL,
  `catListTheme` varchar(200) NOT NULL DEFAULT 'goods_list' COMMENT '商品分类列表风格',
  `detailTheme` varchar(200) NOT NULL DEFAULT 'goods_detail' COMMENT '商品详情风格',
  `mobileCatListTheme` varchar(200) NOT NULL DEFAULT 'goods_list' COMMENT '移动端商品分类列表风格',
  `mobileDetailTheme` varchar(200) NOT NULL DEFAULT 'goods_detail' COMMENT '移动端商品详情风格',
  `wechatCatListTheme` varchar(200) NOT NULL DEFAULT 'goods_list' COMMENT '微信端商品分类列表风格',
  `wechatDetailTheme` varchar(200) NOT NULL DEFAULT 'goods_detail' COMMENT '微信端商品详情风格',
  `showWay` tinyint(4) DEFAULT 0 COMMENT '显示方式 0:卡片 1:列表',
  `catColor` varchar(50) DEFAULT '#F23CCE',
  `isNft` tinyint(4) DEFAULT 0,
  `isMarket` tinyint(11) DEFAULT 0 COMMENT '是否大店分类',
  `marketId` int(11) DEFAULT 0 COMMENT '大店ID',
  PRIMARY KEY (`catId`),
  KEY `parentId` (`parentId`,`isShow`,`dataFlag`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=677 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_goods_cats_langs`;
CREATE TABLE `wst_goods_cats_langs` (
  `catId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `catName` varchar(100) DEFAULT NULL,
  `subTitle` varchar(150) DEFAULT NULL,
  `simpleName` varchar(100) DEFAULT NULL,
  `seoTitle` varchar(200) DEFAULT NULL,
  `seoKeywords` varchar(200) DEFAULT NULL,
  `seoDes` varchar(600) DEFAULT NULL,
  PRIMARY KEY (`catId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_goods_consult`;
CREATE TABLE `wst_goods_consult` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `goodsId` int(10) unsigned NOT NULL COMMENT '商品id',
  `userId` int(10) unsigned DEFAULT NULL COMMENT '用户id',
  `consultType` tinyint(3) unsigned DEFAULT NULL COMMENT '咨询类别',
  `consultContent` varchar(500) DEFAULT NULL COMMENT '咨询内容',
  `createTime` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '咨询时间',
  `reply` varchar(255) NOT NULL DEFAULT '' COMMENT '商家回复',
  `replyTime` datetime DEFAULT NULL COMMENT '回复时间',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '数据有效标志',
  `isShow` tinyint(4) DEFAULT 1 COMMENT '是否显示数据',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_goods_langs`;
CREATE TABLE `wst_goods_langs` (
  `goodsId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsName` varchar(200) DEFAULT NULL,
  `goodsTips` text DEFAULT NULL,
  `goodsDesc` text DEFAULT NULL,
  `goodsSeoKeywords` varchar(200) DEFAULT NULL,
  `goodsSeoDesc` varchar(600) DEFAULT NULL,
  PRIMARY KEY (`goodsId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_goods_scores`;
CREATE TABLE `wst_goods_scores` (
  `scoreId` int(11) NOT NULL AUTO_INCREMENT,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `totalScore` int(11) NOT NULL DEFAULT 0,
  `totalUsers` int(11) NOT NULL DEFAULT 0,
  `goodsScore` int(11) NOT NULL DEFAULT 0,
  `goodsUsers` int(11) NOT NULL DEFAULT 0,
  `serviceScore` int(11) NOT NULL DEFAULT 0,
  `serviceUsers` int(11) NOT NULL DEFAULT 0,
  `timeScore` int(11) NOT NULL DEFAULT 0,
  `timeUsers` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`scoreId`),
  KEY `goodsId` (`goodsId`,`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=1326 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_goods_specs`;
CREATE TABLE `wst_goods_specs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `productNo` varchar(20) NOT NULL,
  `specIds` varchar(255) NOT NULL,
  `marketPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `specPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `specStock` int(11) NOT NULL DEFAULT 0,
  `warnStock` int(11) NOT NULL DEFAULT 0,
  `saleNum` int(11) NOT NULL DEFAULT 0,
  `isDefault` tinyint(4) DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `specWeight` decimal(11,2) DEFAULT 0.00 COMMENT '商品重量',
  `specVolume` decimal(11,2) DEFAULT 0.00 COMMENT '商品体积',
  `costPrice` decimal(11,2) DEFAULT 0.00 COMMENT '成本价',
  PRIMARY KEY (`id`),
  KEY `shopId` (`goodsId`,`dataFlag`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7440 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_goods_virtuals`;
CREATE TABLE `wst_goods_virtuals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `cardNo` varchar(20) NOT NULL,
  `cardPwd` varchar(20) NOT NULL,
  `orderId` int(11) NOT NULL DEFAULT 0,
  `orderNo` varchar(20) DEFAULT NULL,
  `isUse` tinyint(4) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`,`cardNo`),
  KEY `goodsId` (`goodsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_groupons`;
CREATE TABLE `wst_groupons` (
  `grouponId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `grouponPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `grouponNum` int(11) NOT NULL DEFAULT 0,
  `orderNum` int(11) NOT NULL DEFAULT 0,
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `grouponStatus` tinyint(4) DEFAULT 1,
  `illegalRemarks` varchar(255) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL,
  `updateTime` datetime NOT NULL,
  `createTime` datetime NOT NULL,
  `limitNum` int(11) NOT NULL DEFAULT 0 COMMENT '每人可团数量',
  `isSale` tinyint(4) DEFAULT 1,
  `goodsImg` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`grouponId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_groupons_langs`;
CREATE TABLE `wst_groupons_langs` (
  `grouponId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsName` varchar(200) DEFAULT NULL,
  `grouponDesc` text DEFAULT NULL,
  `goodsSeoKeywords` varchar(200) DEFAULT NULL,
  `goodsSeoDesc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`grouponId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_home_menus`;
CREATE TABLE `wst_home_menus` (
  `menuId` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL DEFAULT 0,
  `menuUrl` varchar(100) NOT NULL,
  `menuOtherUrl` text DEFAULT NULL,
  `menuType` tinyint(4) NOT NULL DEFAULT 0,
  `isShow` tinyint(4) DEFAULT 1,
  `menuSort` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime DEFAULT NULL,
  `menuMark` varchar(50) DEFAULT NULL,
  `isMarket` tinyint(11) DEFAULT 0 COMMENT '是否大店菜单',
  PRIMARY KEY (`menuId`),
  KEY `parentId` (`parentId`,`isShow`,`dataFlag`),
  KEY `menuType` (`menuType`)
) ENGINE=InnoDB AUTO_INCREMENT=974 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_home_menus_langs`;
CREATE TABLE `wst_home_menus_langs` (
  `menuId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `menuName` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`menuId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_hooks`;
CREATE TABLE `wst_hooks` (
  `hookId` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(40) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `hookRemarks` text NOT NULL COMMENT '描述',
  `hookType` tinyint(1) NOT NULL DEFAULT 1 COMMENT '类型',
  `updateTime` datetime NOT NULL COMMENT '更新时间',
  `addons` text DEFAULT NULL,
  PRIMARY KEY (`hookId`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_im_chat_statistics`;
CREATE TABLE `wst_im_chat_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `shopId` int(10) unsigned NOT NULL COMMENT '店铺id',
  `startTime` datetime NOT NULL COMMENT '访问时间',
  `ip` varchar(255) NOT NULL COMMENT '访问ip',
  `platform` tinyint(4) NOT NULL COMMENT '访问平台,1:pc 2:手机|微信 3:android 4:ios 5:小程序',
  `stayTime` int(10) unsigned NOT NULL COMMENT '停留时间,单位:秒',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_informs`;
CREATE TABLE `wst_informs` (
  `informId` int(11) NOT NULL AUTO_INCREMENT,
  `informTargetId` int(11) NOT NULL,
  `goodId` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  `informType` int(11) NOT NULL DEFAULT 1,
  `informContent` text DEFAULT NULL,
  `informAnnex` text NOT NULL,
  `informTime` datetime NOT NULL,
  `informStatus` tinyint(4) NOT NULL,
  `respondContent` text DEFAULT NULL,
  `finalHandleStaffId` int(11) DEFAULT NULL,
  `finalHandleTime` datetime DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`informId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_integral_goods`;
CREATE TABLE `wst_integral_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `goodsPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `integralNum` int(11) NOT NULL DEFAULT 0,
  `totalNum` int(11) NOT NULL DEFAULT 0,
  `orderNum` int(11) NOT NULL DEFAULT 0,
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `integralStatus` tinyint(4) DEFAULT 1,
  `dataFlag` tinyint(4) NOT NULL,
  `updateTime` datetime NOT NULL,
  `createTime` datetime NOT NULL,
  `goodsImg` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_integral_goods_langs`;
CREATE TABLE `wst_integral_goods_langs` (
  `integralId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsName` varchar(200) DEFAULT NULL,
  `integralDesc` text DEFAULT NULL,
  `goodsSeoKeywords` varchar(200) DEFAULT NULL,
  `goodsSeoDesc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`integralId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_invoices`;
CREATE TABLE `wst_invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `invoiceHead` varchar(255) NOT NULL COMMENT '发票抬头',
  `invoiceCode` varchar(255) NOT NULL DEFAULT '' COMMENT '纳税人识别号',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '数据有效标记',
  `createTime` datetime NOT NULL COMMENT '数据创建时间',
  `invoiceType` tinyint(4) DEFAULT 0 COMMENT '0:普票 1:增值税发票',
  `invoiceAddr` varchar(300) DEFAULT NULL COMMENT '增值税发票注册地址（invoiceType为1时该值不为空）',
  `invoicePhoneNumber` varchar(100) DEFAULT NULL COMMENT '增值税发票注册电话（invoiceType为1时该值不为空）',
  `invoiceBankName` varchar(100) DEFAULT NULL COMMENT '增值税发票开户银行（invoiceType为1时该值不为空）',
  `invoiceBankNo` varchar(100) DEFAULT NULL COMMENT '增值税发票银行账户（invoiceType为1时该值不为空）',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_languages`;
CREATE TABLE `wst_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_limit_words`;
CREATE TABLE `wst_limit_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `word` varchar(50) DEFAULT NULL COMMENT '禁用关键字',
  `createTime` datetime DEFAULT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '1:有效  -1:删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_moneys`;
CREATE TABLE `wst_log_moneys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `targetType` tinyint(4) NOT NULL DEFAULT 0,
  `targetId` int(11) NOT NULL DEFAULT 0,
  `dataId` varchar(50) DEFAULT NULL,
  `dataSrc` varchar(20) DEFAULT NULL,
  `remark` text NOT NULL,
  `moneyType` tinyint(4) NOT NULL DEFAULT 1,
  `money` decimal(11,2) NOT NULL DEFAULT 0.00,
  `tradeNo` varchar(100) DEFAULT NULL,
  `payType` varchar(20) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `giveMoney` decimal(11,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=285 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_operates`;
CREATE TABLE `wst_log_operates` (
  `operateId` int(11) NOT NULL AUTO_INCREMENT,
  `staffId` int(11) NOT NULL DEFAULT 0,
  `operateTime` datetime NOT NULL,
  `menuId` int(11) NOT NULL,
  `operateDesc` varchar(255) NOT NULL,
  `operateUrl` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `operateIP` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`operateId`),
  KEY `operateTime` (`staffId`,`menuId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=31682 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_orders`;
CREATE TABLE `wst_log_orders` (
  `logId` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL DEFAULT 0,
  `orderStatus` int(11) NOT NULL,
  `logJson` text DEFAULT NULL,
  `logUserId` int(11) NOT NULL DEFAULT 0,
  `logType` tinyint(4) NOT NULL DEFAULT 0,
  `logTime` datetime NOT NULL,
  PRIMARY KEY (`logId`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=1165 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_pay_params`;
CREATE TABLE `wst_log_pay_params` (
  `logId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL COMMENT '0',
  `transId` varchar(50) DEFAULT NULL,
  `paramsVa` varchar(500) DEFAULT NULL,
  `payFrom` varchar(20) DEFAULT NULL,
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`logId`)
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_paypals`;
CREATE TABLE `wst_log_paypals` (
  `logId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL COMMENT '0',
  `orderId` varchar(50) DEFAULT NULL,
  `paramsVa` varchar(500) DEFAULT NULL,
  `createTime` datetime DEFAULT NULL,
  `targetType` tinyint(4) DEFAULT 0 COMMENT '对象类型（0：用户 1：商家 3：供货商）',
  PRIMARY KEY (`logId`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_pays`;
CREATE TABLE `wst_log_pays` (
  `logId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL COMMENT '0',
  `transId` varchar(50) DEFAULT NULL,
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`logId`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_search_word_goods`;
CREATE TABLE `wst_log_search_word_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logId` int(11) NOT NULL,
  `goodsId` int(11) DEFAULT 0,
  `sort` tinyint(4) DEFAULT 0,
  `createTime` datetime DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_search_words`;
CREATE TABLE `wst_log_search_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `searchWord` varchar(255) DEFAULT NULL COMMENT '搜索关键词',
  `searchCnt` int(11) DEFAULT 0 COMMENT '搜索次数',
  `lastTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=360 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_services`;
CREATE TABLE `wst_log_services` (
  `logId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '订单id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '售后id',
  `logJson` text DEFAULT NULL,
  `logTargetId` int(10) unsigned NOT NULL COMMENT '操作者Id,如果是商家的话记录商家的ID',
  `logType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '操作者类型,0：用户  1：商家',
  `logTime` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`logId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_shop_operates`;
CREATE TABLE `wst_log_shop_operates` (
  `operateId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT 0,
  `operateTime` datetime NOT NULL,
  `menuId` int(11) NOT NULL,
  `operateDesc` varchar(255) NOT NULL,
  `operateUrl` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `operateIP` varchar(20) DEFAULT NULL,
  `shopId` int(4) DEFAULT 1,
  PRIMARY KEY (`operateId`),
  KEY `operateTime` (`userId`,`menuId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=65504 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_sms`;
CREATE TABLE `wst_log_sms` (
  `smsId` int(11) NOT NULL AUTO_INCREMENT,
  `smsSrc` tinyint(4) NOT NULL DEFAULT 0,
  `smsUserId` int(11) NOT NULL DEFAULT 0,
  `smsContent` varchar(255) NOT NULL,
  `smsPhoneNumber` varchar(20) DEFAULT NULL,
  `smsReturnCode` text DEFAULT NULL,
  `smsCode` varchar(255) DEFAULT NULL,
  `smsFunc` varchar(50) NOT NULL,
  `smsIP` varchar(16) NOT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`smsId`),
  KEY `smsPhoneNumber` (`smsPhoneNumber`),
  KEY `smsIP` (`smsIP`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_staff_logins`;
CREATE TABLE `wst_log_staff_logins` (
  `loginId` int(11) NOT NULL AUTO_INCREMENT,
  `staffId` int(11) NOT NULL DEFAULT 0,
  `loginTime` datetime NOT NULL,
  `loginIp` varchar(16) NOT NULL,
  PRIMARY KEY (`loginId`),
  KEY `loginTime` (`loginTime`),
  KEY `staffId` (`staffId`)
) ENGINE=InnoDB AUTO_INCREMENT=797 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_user_logins`;
CREATE TABLE `wst_log_user_logins` (
  `loginId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `loginTime` datetime NOT NULL,
  `loginIp` varchar(255) NOT NULL,
  `loginSrc` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0:商城  1:webapp  2:App',
  `loginRemark` varchar(30) DEFAULT NULL COMMENT '登录备注信息',
  PRIMARY KEY (`loginId`),
  KEY `loginTime` (`loginTime`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=1427 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_mall_star_applys`;
CREATE TABLE `wst_mall_star_applys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL COMMENT '用戶id',
  `email` varchar(255) NOT NULL COMMENT '用戶電郵',
  `contact` varchar(255) NOT NULL COMMENT '其他線上聯繫方式',
  `content` text NOT NULL COMMENT '社交頻道鏈接',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '審核狀態 -1:審核失敗 0:待審核 1:審核通過',
  `handleRemark` text DEFAULT NULL COMMENT '處理結果備註',
  `applyTime` datetime NOT NULL COMMENT '提交申請時間',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `nickname` varchar(255) NOT NULL COMMENT '稱謂',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='MallStar申請表';

DROP TABLE IF EXISTS `wst_mall_star_moneys`;
CREATE TABLE `wst_mall_star_moneys` (
  `moneyId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) DEFAULT 0 COMMENT '店鋪id',
  `orderId` int(11) DEFAULT 0 COMMENT '訂單id',
  `userId` int(11) DEFAULT 0 COMMENT '用戶id',
  `buyerId` int(11) DEFAULT NULL COMMENT '購買者id',
  `remark` varchar(200) DEFAULT NULL COMMENT '備註',
  `distributType` tinyint(4) DEFAULT 0 COMMENT '預留字段',
  `orderGoodsId` int(11) DEFAULT 0 COMMENT '訂單商品id',
  `money` decimal(11,2) DEFAULT 0.00 COMMENT '成交金額',
  `distributMoney` decimal(11,2) DEFAULT 0.00 COMMENT '分銷分成金額',
  `createTime` datetime DEFAULT NULL,
  `moneyType` tinyint(4) DEFAULT 0 COMMENT '1：第一級分成 2：第二級分成',
  `goodsNum` int(11) DEFAULT 0 COMMENT '购买商品数量',
  `moneyStatus` tinyint(4) DEFAULT 0 COMMENT '佣金狀態：1：已結算 0：待結算',
  `lockDays` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '佣金鎖定天數',
  PRIMARY KEY (`moneyId`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='MallStar分佣表';

DROP TABLE IF EXISTS `wst_mall_star_users`;
CREATE TABLE `wst_mall_star_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) DEFAULT 0 COMMENT '上級MallStar用戶Id',
  `userId` int(11) DEFAULT 0 COMMENT '用戶id',
  `isEnable` tinyint(4) DEFAULT 1 COMMENT '是否啟用分佣獎勵 1:是 0:否',
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='MallStar用戶表';

DROP TABLE IF EXISTS `wst_market_navs`;
CREATE TABLE `wst_market_navs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marketId` int(11) NOT NULL DEFAULT 0,
  `marketImg` varchar(150) DEFAULT NULL,
  `dataSort` int(11) NOT NULL DEFAULT 0,
  `isShow` tinyint(4) DEFAULT 1,
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketId` (`marketId`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='大店导航表';

DROP TABLE IF EXISTS `wst_market_navs_langs`;
CREATE TABLE `wst_market_navs_langs` (
  `navId` int(11) NOT NULL,
  `langId` int(11) NOT NULL DEFAULT 0,
  `marketTitle` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`navId`,`langId`),
  KEY `langId` (`langId`,`marketTitle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='大店导航语言表';

DROP TABLE IF EXISTS `wst_market_recommends`;
CREATE TABLE `wst_market_recommends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marketId` int(11) NOT NULL DEFAULT 0,
  `dataType` tinyint(4) NOT NULL DEFAULT 0,
  `dataSrc` tinyint(4) DEFAULT 0,
  `dataId` int(11) NOT NULL DEFAULT 0,
  `dataSort` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `marketId` (`marketId`,`dataType`,`dataSrc`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COMMENT='大店推荐商品表';

DROP TABLE IF EXISTS `wst_member_recommend_configs`;
CREATE TABLE `wst_member_recommend_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recommendNum` int(11) DEFAULT 0,
  `score` int(11) DEFAULT 0,
  `createTime` datetime DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_member_users`;
CREATE TABLE `wst_member_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) DEFAULT 0,
  `userId` int(11) DEFAULT 0,
  `createTime` datetime DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_menus`;
CREATE TABLE `wst_menus` (
  `menuId` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL,
  `menuSort` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 0,
  `menuMark` varchar(50) DEFAULT NULL,
  `isShow` tinyint(4) DEFAULT 1,
  `menuIcon` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`menuId`),
  KEY `parentId` (`parentId`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=952 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_menus_langs`;
CREATE TABLE `wst_menus_langs` (
  `menuId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `menuName` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`menuId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_message_queues`;
CREATE TABLE `wst_message_queues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `msgType` tinyint(4) DEFAULT 0,
  `paramJson` text DEFAULT NULL,
  `msgJson` text DEFAULT NULL,
  `createTime` datetime DEFAULT NULL,
  `sendTime` datetime DEFAULT NULL,
  `sendStatus` tinyint(4) DEFAULT 0,
  `msgCode` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_messages`;
CREATE TABLE `wst_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msgType` tinyint(4) NOT NULL DEFAULT 0,
  `sendUserId` int(11) NOT NULL DEFAULT 0,
  `receiveUserId` int(11) NOT NULL DEFAULT 0,
  `msgContent` text NOT NULL,
  `msgStatus` tinyint(4) NOT NULL DEFAULT 0,
  `msgJson` varchar(255) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `receiveUserId` (`receiveUserId`,`dataFlag`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=361 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_mobile_btns`;
CREATE TABLE `wst_mobile_btns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `btnSrc` tinyint(4) NOT NULL DEFAULT 0,
  `btnUrl` varchar(255) DEFAULT NULL,
  `btnImg` varchar(255) DEFAULT NULL,
  `addonsName` varchar(255) DEFAULT NULL,
  `btnSort` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `btnSrc` (`btnSrc`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_mobile_btns_langs`;
CREATE TABLE `wst_mobile_btns_langs` (
  `btnId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `btnName` varchar(255) NOT NULL,
  PRIMARY KEY (`btnId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_mould_goods_attributes`;
CREATE TABLE `wst_mould_goods_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `mouldId` int(11) DEFAULT 0,
  `goodsCatId` int(11) NOT NULL DEFAULT 0,
  `attrId` int(11) NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`),
  KEY `goodsId` (`goodsCatId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_mould_goods_attributes_langs`;
CREATE TABLE `wst_mould_goods_attributes_langs` (
  `mouldGoodsAttrId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `mouldId` int(11) NOT NULL,
  `attrVal` text DEFAULT NULL,
  PRIMARY KEY (`mouldGoodsAttrId`,`langId`,`mouldId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_mould_goods_spec_items`;
CREATE TABLE `wst_mould_goods_spec_items` (
  `itemId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `mouldId` int(11) DEFAULT 0,
  `goodsCatId` int(11) NOT NULL DEFAULT 0,
  `catId` int(11) NOT NULL DEFAULT 0,
  `itemImg` varchar(150) NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`itemId`),
  KEY `goodsId` (`goodsCatId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_mould_goods_spec_items_langs`;
CREATE TABLE `wst_mould_goods_spec_items_langs` (
  `mouldGoodsSpecItemId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `mouldId` int(11) NOT NULL,
  `itemName` varchar(100) DEFAULT NULL,
  `itemDesc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`mouldGoodsSpecItemId`,`langId`,`mouldId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_moulds`;
CREATE TABLE `wst_moulds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `goodsCatId` int(11) DEFAULT 0,
  `mouldName` varchar(200) NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_navs`;
CREATE TABLE `wst_navs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navType` tinyint(4) NOT NULL DEFAULT 0,
  `navUrl` varchar(100) NOT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `isOpen` tinyint(4) NOT NULL DEFAULT 0,
  `navSort` int(11) NOT NULL DEFAULT 0,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `navType` (`navType`,`isShow`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_navs_langs`;
CREATE TABLE `wst_navs_langs` (
  `navId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `navTitle` varchar(50) NOT NULL,
  PRIMARY KEY (`navId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_order_complains`;
CREATE TABLE `wst_order_complains` (
  `complainId` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL DEFAULT 0,
  `complainType` tinyint(4) NOT NULL DEFAULT 1,
  `complainTargetId` int(11) NOT NULL DEFAULT 0,
  `respondTargetId` int(11) NOT NULL DEFAULT 0,
  `needRespond` tinyint(4) NOT NULL DEFAULT 0,
  `deliverRespondTime` datetime DEFAULT NULL,
  `complainContent` text NOT NULL,
  `complainAnnex` varchar(255) DEFAULT NULL,
  `complainStatus` tinyint(4) NOT NULL DEFAULT 0,
  `complainTime` datetime NOT NULL,
  `respondContent` text DEFAULT NULL,
  `respondAnnex` varchar(255) DEFAULT NULL,
  `respondTime` datetime DEFAULT NULL,
  `finalResult` text DEFAULT NULL,
  `finalResultTime` datetime DEFAULT NULL,
  `finalHandleStaffId` int(11) DEFAULT 0,
  PRIMARY KEY (`complainId`),
  KEY `complainStatus` (`complainStatus`),
  KEY `complainType` (`complainTargetId`,`complainType`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_order_express`;
CREATE TABLE `wst_order_express` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `orderGoodsId` varchar(600) NOT NULL,
  `deliveryTime` datetime DEFAULT NULL,
  `isExpress` tinyint(4) DEFAULT 0 COMMENT '1:��Ҫ����  0:��������',
  `expressId` int(11) DEFAULT NULL,
  `expressNo` varchar(20) DEFAULT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `deliverType` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_order_goods`;
CREATE TABLE `wst_order_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `goodsNum` int(11) NOT NULL DEFAULT 0,
  `goodsPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `goodsSpecId` int(11) DEFAULT 0,
  `goodsSpecNames` varchar(500) DEFAULT NULL,
  `goodsName` varchar(200) NOT NULL,
  `goodsImg` varchar(150) NOT NULL,
  `extraJson` text DEFAULT NULL,
  `goodsType` tinyint(4) NOT NULL DEFAULT 0,
  `commissionRate` decimal(11,2) DEFAULT 0.00,
  `goodsCode` varchar(20) DEFAULT NULL,
  `promotionJson` text DEFAULT NULL,
  `couponVal` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '优惠券减免金额',
  `rewardVal` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '满减减免金额',
  `useScoreVal` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '商品使用的积分',
  `scoreMoney` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '积分抵扣金额',
  `getScoreVal` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '购买该商品获得的积分数',
  `orderGoodscommission` decimal(11,2) DEFAULT 0.00 COMMENT '订单商品佣金',
  `getScoreMoney` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '获得的积分数可抵扣的金额',
  `nftTxId` varchar(150) DEFAULT '',
  `nftJson` text DEFAULT NULL,
  `commission` decimal(11,2) DEFAULT 0.00 COMMENT '訂單商品佣金',
  `marketCommissionRate` decimal(11,2) DEFAULT 0.00 COMMENT '大店佣金比例',
  `orderGoodsMarketcommission` decimal(11,2) DEFAULT 0.00 COMMENT '大店佣金金额',
  `realOrderGoodscommission` decimal(11,2) DEFAULT 0.00 COMMENT '实际大店佣金金额（退款后）',
  `realOrderGoodsMarketcommission` decimal(11,2) DEFAULT 0.00 COMMENT '实际大店佣金金额（退款后）',
  `refundedPayMoney` decimal(11,2) DEFAULT 0.00,
  `refundedScore` int(11) DEFAULT 0,
  `refundedScoreMoney` decimal(11,2) DEFAULT 0.00,
  `refundedGetScore` int(11) DEFAULT 0,
  `refundedGetScoreMoney` decimal(11,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `goodsId` (`goodsId`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_order_refunds`;
CREATE TABLE `wst_order_refunds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `refundTo` int(11) NOT NULL DEFAULT 0,
  `refundReson` int(11) NOT NULL DEFAULT 0,
  `refundOtherReson` varchar(255) DEFAULT NULL,
  `backMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `refundTradeNo` varchar(100) DEFAULT NULL,
  `refundRemark` varchar(500) DEFAULT NULL,
  `refundTime` datetime DEFAULT NULL,
  `shopRejectReason` varchar(255) DEFAULT NULL,
  `refundStatus` tinyint(4) NOT NULL DEFAULT 0,
  `createTime` datetime NOT NULL,
  `serviceId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '售后单id',
  `isServiceRefund` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '售后单是否已退款0：未退款 1：已退款',
  `refundProcessStatus` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_order_services`;
CREATE TABLE `wst_order_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `orderId` int(10) unsigned NOT NULL COMMENT '订单id',
  `goodsServiceType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '0：退款退货 1：退款 2：换货',
  `serviceType` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '退换货类型,数据由基础数据类型里取',
  `serviceRemark` varchar(600) DEFAULT NULL COMMENT '退换货原因',
  `serviceAnnex` text DEFAULT NULL COMMENT '附件',
  `refundScore` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '本次申请可退还的积分，由系统计算得出',
  `useScoreMoney` decimal(11,2) unsigned NOT NULL DEFAULT 0.00 COMMENT '使用的积分可抵扣金额',
  `getScoreMoney` decimal(11,2) unsigned NOT NULL DEFAULT 0.00 COMMENT '获得的积分可抵扣金额',
  `refundMoney` decimal(11,2) DEFAULT NULL COMMENT '申请退款的金额',
  `refundableMoney` decimal(11,2) DEFAULT NULL COMMENT '售后单可退款金额',
  `isShopAgree` tinyint(4) DEFAULT 0 COMMENT '1:同意 0：不同意',
  `disagreeRemark` varchar(600) DEFAULT NULL COMMENT '商家不同意原因',
  `userAddressId` int(11) unsigned DEFAULT 0 COMMENT '用户收货地址id',
  `areaId` int(11) unsigned DEFAULT 0 COMMENT '地区id',
  `areaIdPath` varchar(255) DEFAULT NULL COMMENT '地区ID值',
  `userName` varchar(200) DEFAULT '' COMMENT '用户收货人',
  `userAddress` varchar(200) DEFAULT '' COMMENT '用户详细收货地址',
  `userPhone` varchar(200) DEFAULT '' COMMENT '用户收货电话',
  `shopAreaId` int(11) unsigned DEFAULT 0 COMMENT '商家收货地区ID',
  `shopAreaIdPath` varchar(255) DEFAULT NULL COMMENT '商家收货地区ID值',
  `shopName` varchar(200) DEFAULT '' COMMENT '商家收货人',
  `shopAddress` varchar(200) DEFAULT '' COMMENT '商家详细收货地址',
  `shopPhone` varchar(200) DEFAULT '' COMMENT '商家收货电话',
  `isUserSendGoods` tinyint(4) DEFAULT 0 COMMENT '0：未发货 1：已发货',
  `expressType` tinyint(4) DEFAULT 0 COMMENT '0：无需物流  1：快递',
  `expressId` int(11) unsigned DEFAULT 0 COMMENT '买家快递ID',
  `expressNo` varchar(200) DEFAULT '' COMMENT '买家物流单号',
  `isShopAccept` tinyint(4) DEFAULT 0 COMMENT '商家是否收到货 -1：拒收  0：未收货  1：已收货',
  `shopRejectType` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '商家拒收类型,数据由基础数据类型里取',
  `shopRejectOther` varchar(600) DEFAULT NULL COMMENT '商家拒收原因,选择“其他”的时候填写文字',
  `shopRejectImg` varchar(150) DEFAULT NULL COMMENT '拒收时的货物图片',
  `isShopSend` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '商家是否发货 0：未发货 1：已发货',
  `shopExpressType` tinyint(4) DEFAULT 0 COMMENT '0：无需物流  1：快递',
  `shopExpressId` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '商家快递ID',
  `shopExpressNo` varchar(200) DEFAULT '' COMMENT '商家快递单号',
  `isUserAccept` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '-1：拒收 0：未收货  1：已收货',
  `userRejectType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '用户拒收原因,数据由基础数据类型里取',
  `userRejectOther` varchar(600) DEFAULT NULL COMMENT '用户拒收原因,选择“其他”的时候填写文字',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `isClose` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '是否结束 0：进行中  1:已结束',
  `serviceStatus` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '状态备注：0：待商家审核  1：等待用户发货 2：等待商家收货 3：等待商家发货  4：等待用户收货  5：完成退款/退货  6：商家已确认收货 7：商家受理，等待管理员退款',
  `shopAcceptExpireTime` datetime DEFAULT NULL COMMENT '商家受理期限',
  `userSendExpireTime` datetime DEFAULT NULL COMMENT '用户发货期限',
  `shopReceiveExpireTime` datetime DEFAULT NULL COMMENT '商家收货期限',
  `getScoreVal` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '本次申请的商品,购买时获得的积分数',
  `userReceiveExpireTime` datetime DEFAULT NULL COMMENT '用户收货期限',
  `shopSendExpireTime` datetime DEFAULT NULL COMMENT '商家发货期限',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_orderids`;
CREATE TABLE `wst_orderids` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `rnd` float(16,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10001397 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_orders`;
CREATE TABLE `wst_orders` (
  `orderId` int(11) NOT NULL AUTO_INCREMENT,
  `orderNo` varchar(20) NOT NULL,
  `shopId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `orderStatus` tinyint(4) NOT NULL DEFAULT -2,
  `goodsMoney` decimal(11,2) NOT NULL,
  `deliverType` tinyint(4) NOT NULL DEFAULT 0,
  `deliverMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `totalMoney` decimal(11,2) NOT NULL,
  `realTotalMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `payType` tinyint(4) NOT NULL DEFAULT 0,
  `payFrom` varchar(20) DEFAULT NULL,
  `isPay` tinyint(4) NOT NULL DEFAULT 0,
  `areaId` int(11) NOT NULL,
  `areaIdPath` varchar(255) DEFAULT NULL,
  `userName` varchar(50) NOT NULL,
  `userAddress` varchar(255) NOT NULL,
  `userPhone` char(20) DEFAULT NULL,
  `orderScore` int(11) NOT NULL DEFAULT 0,
  `isInvoice` tinyint(4) NOT NULL DEFAULT 0,
  `invoiceClient` varchar(255) DEFAULT NULL,
  `orderRemarks` varchar(255) DEFAULT NULL,
  `orderSrc` tinyint(4) NOT NULL DEFAULT 0,
  `needPay` decimal(11,2) DEFAULT 0.00,
  `payRand` int(11) DEFAULT 1,
  `orderType` int(11) DEFAULT 0,
  `isRefund` tinyint(4) NOT NULL DEFAULT 0,
  `isAppraise` tinyint(4) DEFAULT 0,
  `cancelReason` int(11) DEFAULT 0,
  `rejectReason` int(11) DEFAULT 0,
  `rejectOtherReason` varchar(255) DEFAULT NULL,
  `isClosed` tinyint(4) NOT NULL DEFAULT 0,
  `goodsSearchKeys` text DEFAULT NULL,
  `orderunique` varchar(50) NOT NULL,
  `receiveTime` datetime DEFAULT NULL,
  `deliveryTime` datetime DEFAULT NULL,
  `tradeNo` varchar(100) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `settlementId` int(11) DEFAULT 0,
  `commissionFee` decimal(11,2) DEFAULT 0.00,
  `scoreMoney` decimal(11,2) DEFAULT 0.00,
  `useScore` int(11) DEFAULT 0,
  `orderCode` varchar(20) DEFAULT 'order',
  `extraJson` text DEFAULT NULL,
  `orderCodeTargetId` int(11) DEFAULT 0,
  `noticeDeliver` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '提醒发货 0:未提醒 1:已提醒',
  `invoiceJson` text DEFAULT NULL COMMENT '发票信息',
  `lockCashMoney` decimal(11,2) DEFAULT 0.00 COMMENT '锁定提现金额',
  `payTime` datetime DEFAULT NULL,
  `isBatch` tinyint(4) DEFAULT 0,
  `totalPayFee` int(11) DEFAULT 0,
  `isMakeInvoice` tinyint(4) DEFAULT 0 COMMENT '是否开发票 1:是 0:否',
  `afterSaleEndTime` datetime DEFAULT NULL COMMENT '售后结束时间,确认收货时间+后台设置的售后有效天数',
  `getScoreVal` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '获得的积分数可抵扣的金额',
  `refundedPayMoney` decimal(11,2) DEFAULT 0.00 COMMENT '已退款支付金额',
  `refundedScore` int(10) unsigned DEFAULT 0 COMMENT '已退款积分',
  `refundedScoreMoney` decimal(11,2) DEFAULT 0.00 COMMENT '已退款积分抵扣金额',
  `refundedGetScore` int(10) unsigned DEFAULT 0 COMMENT '已退款 购买商品获得的积分(用户本身购买商品可获得的积分),即失效的获得积分数',
  `refundedGetScoreMoney` decimal(11,2) DEFAULT 0.00 COMMENT '已退款 购买商品获得的积分可抵扣的金额',
  `storeType` int(11) DEFAULT 0 COMMENT '自提点类型 1:店铺自提点 2：平台',
  `storeId` int(11) DEFAULT 0 COMMENT '自提点ID',
  `verificationCode` varchar(20) DEFAULT '0' COMMENT '核验码',
  `verificationTime` datetime DEFAULT NULL COMMENT '核验时间',
  `userCouponId` int(11) DEFAULT 0 COMMENT '用户使用的优惠券ID',
  `userCouponJson` text DEFAULT NULL COMMENT '优惠券说明',
  `areaCode` varchar(6) DEFAULT '' COMMENT '電話區號',
  `outTradeNo` varchar(50) DEFAULT '',
  `isNft` tinyint(4) DEFAULT 0,
  `nftWalletAddress` varchar(150) DEFAULT '',
  `topMallStarRate` tinyint(4) DEFAULT 0 COMMENT '一級mall star分佣比例',
  `secondMallStarRate` tinyint(4) DEFAULT 0 COMMENT '二級mall star分佣比例',
  `totalCommission` decimal(11,2) DEFAULT 0.00 COMMENT '總佣金',
  `dmoneyIsSettlement` tinyint(4) DEFAULT 0 COMMENT '佣金是否已結算 1：是 0：否',
  `distributType` tinyint(4) DEFAULT 0,
  `isMarket` tinyint(11) DEFAULT 0 COMMENT '是否大店从属商家的订单',
  `marketId` int(11) DEFAULT 0 COMMENT '大店ID',
  `marketCommissionFee` decimal(11,2) DEFAULT 0.00 COMMENT '订单大店佣金',
  PRIMARY KEY (`orderId`),
  KEY `shopId` (`shopId`,`dataFlag`),
  KEY `userId` (`userId`,`dataFlag`),
  KEY `isRefund` (`isRefund`),
  KEY `orderStatus` (`orderStatus`)
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_payments`;
CREATE TABLE `wst_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payCode` varchar(20) DEFAULT NULL,
  `payName` varchar(255) DEFAULT NULL,
  `payDesc` text DEFAULT NULL,
  `payOrder` int(11) DEFAULT 0,
  `payConfig` text DEFAULT NULL,
  `enabled` tinyint(4) DEFAULT 0,
  `isOnline` tinyint(4) DEFAULT 0,
  `payFor` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payCode` (`payCode`,`enabled`,`isOnline`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_presale_moneys`;
CREATE TABLE `wst_presale_moneys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `porderId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `presaleMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `presaleStatus` tinyint(4) NOT NULL DEFAULT 0,
  `createTime` datetime NOT NULL,
  `payType` varchar(50) DEFAULT '',
  `tradeNo` varchar(100) DEFAULT NULL,
  `moneyType` int(11) DEFAULT 0 COMMENT '1:定金 2：尾款',
  `lockCashMoney` decimal(11,2) DEFAULT 0.00,
  `refundStatus` tinyint(4) DEFAULT 0,
  `refundTime` datetime DEFAULT NULL,
  `refundTradeNo` varchar(100) DEFAULT NULL,
  `outTradeNo` varchar(50) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `porderId` (`porderId`,`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_presale_orders`;
CREATE TABLE `wst_presale_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT 0 COMMENT '用户ID',
  `presaleId` int(11) DEFAULT 0 COMMENT '预售ID',
  `shopId` int(11) DEFAULT 0 COMMENT '店铺ID',
  `orderId` int(11) DEFAULT 0 COMMENT '订单ID',
  `orderNo` varchar(20) DEFAULT NULL COMMENT '订单号',
  `goodsId` int(11) DEFAULT 0 COMMENT '商品ID',
  `goodsName` varchar(500) DEFAULT '' COMMENT '商品名称',
  `goodsImg` varchar(150) DEFAULT '' COMMENT '商品图片',
  `goodsSpecId` int(11) DEFAULT 0 COMMENT '商品规格ID',
  `goodsSpecNames` varchar(500) DEFAULT '' COMMENT '商品规格名称',
  `goodsNum` int(11) DEFAULT 0 COMMENT '购买商品数量',
  `presaleStatus` tinyint(4) DEFAULT 0 COMMENT '预定状态（0：待效定金 1：待交尾款 2：预定已交尾款，-1：预定失败）',
  `failType` tinyint(4) DEFAULT 0 COMMENT '预定失败原因（ 1：逾期未交尾款 2：管理员下架商品）',
  `areaId` int(11) DEFAULT 0 COMMENT '最后一级区域Id',
  `areaIdPath` varchar(100) DEFAULT NULL COMMENT '区域Id路径',
  `orderType` tinyint(4) DEFAULT 0 COMMENT '订单类型（0:实物订单 1:非实物订单）',
  `goodsMoney` decimal(11,2) DEFAULT 0.00 COMMENT '商品总金额',
  `totalMoney` decimal(11,2) DEFAULT 0.00 COMMENT '订单总金额',
  `realTotalMoney` decimal(11,2) DEFAULT 0.00 COMMENT '实际订单总金额',
  `deliverType` tinyint(4) DEFAULT 0 COMMENT '收货方式（0:送货上门 1:自提）',
  `deliverMoney` decimal(11,2) DEFAULT NULL COMMENT '运费',
  `payType` tinyint(4) DEFAULT 0 COMMENT '支付方式(0:货到付款 1:在线支付)',
  `payFrom` varchar(20) DEFAULT NULL COMMENT '支付来源',
  `isPay` tinyint(4) DEFAULT 0 COMMENT '是否支付完（1：是 0：否）',
  `payTime` datetime DEFAULT NULL COMMENT '支付时间',
  `userName` varchar(20) DEFAULT NULL COMMENT '收货人名称',
  `userAddress` varchar(255) NOT NULL COMMENT '收件人地址',
  `userPhone` varchar(20) DEFAULT NULL COMMENT '收件人手机',
  `orderScore` int(11) NOT NULL DEFAULT 0 COMMENT '所得积分',
  `isInvoice` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否需要发票',
  `invoiceClient` varchar(255) DEFAULT NULL COMMENT '发票抬头',
  `invoiceJson` text DEFAULT NULL COMMENT '发票信息',
  `orderRemarks` varchar(255) DEFAULT NULL COMMENT '订单备注',
  `orderSrc` tinyint(4) NOT NULL DEFAULT 0 COMMENT '订单来源',
  `payRand` int(11) DEFAULT 1 COMMENT '支付随机数',
  `orderunique` varchar(50) DEFAULT NULL COMMENT '订单流水号',
  `useScore` int(11) DEFAULT 0 COMMENT '使用积分',
  `scoreMoney` decimal(11,2) DEFAULT 0.00 COMMENT '抵扣的钱',
  `commissionFee` decimal(11,2) DEFAULT 0.00 COMMENT '订单佣金',
  `commissionRate` decimal(11,2) DEFAULT 0.00 COMMENT '佣金比例',
  `depositMoney` decimal(11,2) DEFAULT 0.00 COMMENT '订金金额',
  `surplusMoney` decimal(11,2) DEFAULT 0.00 COMMENT '尾款金额',
  `startPayTime` datetime DEFAULT NULL COMMENT '尾款支付开始时间',
  `endPayTime` datetime DEFAULT NULL COMMENT '尾款支付结束时间',
  `tradeNo` varchar(100) DEFAULT NULL COMMENT '在线支付交易流水',
  `createTime` datetime DEFAULT NULL COMMENT '下单时间',
  `extraJson` text DEFAULT NULL COMMENT '订单附加备注',
  `lockCashMoney` decimal(11,2) DEFAULT 0.00 COMMENT '所使用的充值送金额',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '有效状态（1：有效 -1无效）',
  `storeType` tinyint(4) DEFAULT 0 COMMENT '自提类型',
  `storeId` int(11) DEFAULT 0 COMMENT '门店ID',
  `areaCode` varchar(6) DEFAULT '' COMMENT '電話區號',
  `outTradeNo` varchar(50) DEFAULT '',
  `isMarket` tinyint(11) DEFAULT 0 COMMENT '是否大店从属商家的订单',
  `marketId` int(11) DEFAULT 0 COMMENT '大店ID',
  `marketCommissionFee` decimal(11,2) DEFAULT 0.00 COMMENT '订单大店佣金',
  `marketCommissionRate` decimal(11,2) DEFAULT 0.00 COMMENT '大店佣金比例',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_presales`;
CREATE TABLE `wst_presales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL COMMENT '商品ID',
  `goodsImg` varchar(150) NOT NULL COMMENT '商品封面图片',
  `limitNum` int(11) DEFAULT 0 COMMENT '每个限制数量',
  `reduceMoney` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '立付减金额',
  `goodsNum` int(11) NOT NULL DEFAULT 0 COMMENT '预售数量',
  `orderNum` int(11) DEFAULT 0 COMMENT '已预订数量',
  `saleType` tinyint(4) NOT NULL DEFAULT 0 COMMENT '预付方式  0:全款付 1：定金付',
  `depositType` tinyint(4) DEFAULT 0 COMMENT '定金类型 0:固定金额  1:百分比',
  `depositMoney` decimal(10,2) DEFAULT 0.00 COMMENT '定金',
  `depositRate` int(11) DEFAULT 0 COMMENT '定金百分比',
  `endPayDays` int(11) DEFAULT 1 COMMENT '预售结束X天内付尾款',
  `startTime` datetime NOT NULL COMMENT '预售开始时间',
  `endTime` datetime NOT NULL COMMENT '预售结束时间',
  `deliverType` tinyint(11) NOT NULL DEFAULT 0 COMMENT '发货方式 0：支付成功  1：预售结束',
  `deliverDays` int(11) NOT NULL DEFAULT 1 COMMENT 'X天内发货',
  `afterGoodsStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '活动结束后状态  1：正常上架  0：下架',
  `presaleStatus` tinyint(4) DEFAULT 0 COMMENT '预售审核状态 0：待审核  1：已审核  -1：审核不通过',
  `auditRemark` varchar(255) DEFAULT NULL COMMENT '审核不通过原因',
  `isSale` tinyint(4) DEFAULT 1,
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '有效状态  1：有效  -1：无效',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_presales_langs`;
CREATE TABLE `wst_presales_langs` (
  `presaleId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsName` varchar(200) DEFAULT NULL,
  `goodsTips` text DEFAULT NULL,
  `goodsSeoKeywords` varchar(200) DEFAULT NULL,
  `goodsSeoDesc` varchar(600) DEFAULT NULL,
  PRIMARY KEY (`presaleId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_privileges`;
CREATE TABLE `wst_privileges` (
  `privilegeId` int(11) NOT NULL AUTO_INCREMENT,
  `menuId` int(11) NOT NULL,
  `privilegeCode` varchar(50) DEFAULT NULL,
  `isMenuPrivilege` tinyint(4) NOT NULL DEFAULT 0,
  `privilegeUrl` varchar(255) DEFAULT NULL,
  `otherPrivilegeUrl` text DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `isEnable` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`privilegeId`),
  UNIQUE KEY `privilegeCode` (`privilegeCode`),
  KEY `menuId` (`menuId`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=1012 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_privileges_langs`;
CREATE TABLE `wst_privileges_langs` (
  `privilegeId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `privilegeName` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`privilegeId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_recommends`;
CREATE TABLE `wst_recommends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsCatId` int(11) NOT NULL DEFAULT 0,
  `dataType` tinyint(4) NOT NULL DEFAULT 0,
  `dataSrc` tinyint(4) DEFAULT 0,
  `dataId` int(11) NOT NULL DEFAULT 0,
  `dataSort` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `goodsCatId` (`goodsCatId`,`dataType`,`dataSrc`)
) ENGINE=InnoDB AUTO_INCREMENT=1985 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_resources`;
CREATE TABLE `wst_resources` (
  `resId` int(11) NOT NULL AUTO_INCREMENT,
  `fromType` tinyint(4) NOT NULL DEFAULT 0,
  `dataId` int(11) NOT NULL DEFAULT 0,
  `resPath` varchar(150) NOT NULL,
  `resSize` int(11) NOT NULL DEFAULT 0,
  `isUse` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `fromTable` varchar(50) DEFAULT NULL,
  `ownId` int(11) DEFAULT NULL,
  `dataFlag` tinyint(4) DEFAULT 1,
  `resType` tinyint(4) DEFAULT 0 COMMENT '0:图片 1:视频',
  PRIMARY KEY (`resId`),
  KEY `isUse` (`isUse`,`fromTable`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=19820 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_reward_favourables`;
CREATE TABLE `wst_reward_favourables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rewardId` int(11) NOT NULL,
  `orderMoney` int(11) DEFAULT 0,
  `favourableJson` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_reward_goods`;
CREATE TABLE `wst_reward_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rewardId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `goodsId` (`goodsId`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_rewards`;
CREATE TABLE `wst_rewards` (
  `rewardId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `rewardTitle` varchar(255) NOT NULL COMMENT '活动标题',
  `startDate` date NOT NULL COMMENT '开始时间',
  `endDate` date NOT NULL COMMENT '结束时间',
  `rewardType` tinyint(4) NOT NULL DEFAULT 0 COMMENT '优惠方式',
  `useObjects` tinyint(4) NOT NULL DEFAULT 0 COMMENT '适用对象',
  `useObjectIds` text DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`rewardId`),
  KEY `shopId` (`shopId`,`dataFlag`),
  KEY `startDate` (`startDate`,`endDate`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_roles`;
CREATE TABLE `wst_roles` (
  `roleId` int(11) NOT NULL AUTO_INCREMENT,
  `roleName` varchar(30) NOT NULL,
  `roleDesc` varchar(255) DEFAULT NULL,
  `privileges` text DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`roleId`),
  KEY `roleFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_seckill_goods`;
CREATE TABLE `wst_seckill_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0 COMMENT '店铺Id',
  `seckillId` int(11) NOT NULL DEFAULT 0 COMMENT '秒杀活动ID',
  `timeId` int(11) NOT NULL DEFAULT 0 COMMENT '秒杀时段ID',
  `goodsId` int(11) NOT NULL DEFAULT 0 COMMENT '商品Id',
  `secPrice` decimal(11,2) DEFAULT 0.00 COMMENT '秒杀价',
  `secNum` int(11) NOT NULL DEFAULT 0 COMMENT '参与秒杀商品数量',
  `secLimit` tinyint(3) NOT NULL DEFAULT 1 COMMENT '秒杀限购数量',
  `saleNum` int(11) NOT NULL DEFAULT 0 COMMENT '销量【已收货】',
  `hasBuyNum` int(11) NOT NULL DEFAULT 0 COMMENT '已购数量【包括未收货】',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效 -1删除',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `seckillId` (`seckillId`),
  KEY `timeId` (`timeId`),
  KEY `goodsId` (`goodsId`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_seckill_time_intervals`;
CREATE TABLE `wst_seckill_time_intervals` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `startTime` time NOT NULL COMMENT '开始时间段',
  `endTime` time NOT NULL COMMENT '结束时间段',
  `dataFlag` tinyint(1) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效 -1删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `startTime` (`startTime`,`endTime`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

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
  `isSale` tinyint(1) NOT NULL DEFAULT 1 COMMENT '上下架',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `seckillStatus` tinyint(1) NOT NULL DEFAULT 1 COMMENT '审核状态',
  `illegalRemarks` varchar(500) DEFAULT NULL COMMENT '不通过原因',
  `dataFlag` tinyint(1) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效 -1删除',
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`),
  KEY `seckillStatus` (`seckillStatus`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_seckills_langs`;
CREATE TABLE `wst_seckills_langs` (
  `seckillId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `seckillDes` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`seckillId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_service_evaluates`;
CREATE TABLE `wst_service_evaluates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '客服id,为0则表示无客服接待时的评分',
  `shopId` int(10) unsigned NOT NULL COMMENT '店铺id',
  `score` tinyint(4) unsigned NOT NULL COMMENT '评分1-5,1:非常不满意,2:不满意,3:一般,4:满意,5:非常满意',
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`),
  KEY `serviceId` (`serviceId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_service_goods`;
CREATE TABLE `wst_service_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '订单id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '售后id',
  `goodsId` int(10) unsigned NOT NULL COMMENT '商品ID',
  `goodsSpecId` int(10) unsigned NOT NULL COMMENT '商品规格ID',
  `goodsNum` int(10) unsigned NOT NULL COMMENT '申请售后的商品数量',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效  -1:删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_settlements`;
CREATE TABLE `wst_settlements` (
  `settlementId` int(11) NOT NULL AUTO_INCREMENT,
  `settlementNo` varchar(20) NOT NULL,
  `settlementType` tinyint(4) NOT NULL DEFAULT 0,
  `shopId` int(11) NOT NULL,
  `settlementMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `commissionFee` decimal(11,2) NOT NULL DEFAULT 0.00,
  `backMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `settlementStatus` tinyint(4) NOT NULL DEFAULT 0,
  `settlementTime` datetime DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`settlementId`),
  KEY `shopId` (`shopId`),
  KEY `settlementStatus` (`settlementStatus`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_accreds`;
CREATE TABLE `wst_shop_accreds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accredId` int(11) NOT NULL DEFAULT 0,
  `shopId` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_applys`;
CREATE TABLE `wst_shop_applys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL COMMENT '用户ID',
  `linkPhone` varchar(20) NOT NULL COMMENT ' 申请联系电话',
  `linkman` varchar(50) NOT NULL COMMENT '申请联系人',
  `applyIntention` varchar(600) NOT NULL COMMENT '申请意向',
  `shopName` varchar(255) DEFAULT NULL COMMENT '店铺名称',
  `handleReamrk` varchar(600) DEFAULT NULL,
  `applyStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '申请状态 0:待处理  1:已处理  -1:无效',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效 -1:删除',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_bases`;
CREATE TABLE `wst_shop_bases` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `flowId` int(11) DEFAULT 0 COMMENT '流程ID',
  `fieldName` varchar(50) DEFAULT '' COMMENT '表单字段',
  `dataType` varchar(10) DEFAULT '' COMMENT '数据类型',
  `dataLength` int(11) DEFAULT 0 COMMENT '数据长度',
  `fieldSort` tinyint(4) unsigned DEFAULT 0 COMMENT '显示排序',
  `isRequire` tinyint(4) unsigned DEFAULT 0 COMMENT '是否必填，0否，1是',
  `fieldType` varchar(10) DEFAULT '' COMMENT '表单类型',
  `isRelevance` tinyint(4) unsigned DEFAULT 0 COMMENT '是否关联字段，0否，1是',
  `fieldRelevance` varchar(50) DEFAULT '' COMMENT '关联字段',
  `dateRelevance` varchar(100) DEFAULT '' COMMENT '日期关联字段',
  `timeRelevance` varchar(100) DEFAULT '' COMMENT '时间关联字段',
  `isShow` tinyint(4) unsigned DEFAULT 1 COMMENT '是否显示，0隐藏，1显示',
  `isMap` tinyint(4) unsigned DEFAULT 0 COMMENT '是否显示地图，0隐藏，1显示',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效  -1:删除',
  `isDelete` tinyint(4) DEFAULT 1 COMMENT '是否可以删除，0否，1是',
  `isShopsTable` tinyint(4) DEFAULT 0 COMMENT '是否在wst_shops表，0不是，1是',
  `fileNum` tinyint(4) DEFAULT NULL,
  `isPhone` tinyint(4) DEFAULT 0 COMMENT '是否手机号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_bases_langs`;
CREATE TABLE `wst_shop_bases_langs` (
  `baseId` int(11) NOT NULL COMMENT '自增ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '流程ID',
  `fieldTitle` varchar(255) DEFAULT NULL COMMENT '表单标题',
  `fieldComment` varchar(500) DEFAULT NULL COMMENT '表单注释',
  `fieldAttr` varchar(255) DEFAULT '' COMMENT '表单属性',
  PRIMARY KEY (`baseId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_cats`;
CREATE TABLE `wst_shop_cats` (
  `catId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `parentId` int(11) NOT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `catSort` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`catId`),
  KEY `parentId` (`isShow`,`dataFlag`) USING BTREE,
  KEY `shopId` (`shopId`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=222 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_cats_langs`;
CREATE TABLE `wst_shop_cats_langs` (
  `catId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `catName` varchar(50) NOT NULL,
  PRIMARY KEY (`catId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_configs`;
CREATE TABLE `wst_shop_configs` (
  `configId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `shopTitle` varchar(255) DEFAULT NULL,
  `shopKeywords` varchar(255) DEFAULT NULL,
  `shopDesc` varchar(255) DEFAULT NULL,
  `shopBanner` varchar(150) DEFAULT NULL,
  `shopAds` text DEFAULT NULL,
  `shopAdsUrl` text DEFAULT NULL,
  `shopServicer` varchar(100) DEFAULT NULL,
  `shopHotWords` varchar(255) DEFAULT NULL,
  `shopStreetImg` varchar(150) DEFAULT '' COMMENT '店铺街背景',
  `shopMoveBanner` varchar(150) DEFAULT NULL,
  `shopHomeTheme` varchar(200) NOT NULL DEFAULT 'shop_home' COMMENT '店铺风格',
  `mobileShopHomeTheme` varchar(200) NOT NULL DEFAULT 'shop_home' COMMENT '手机端店铺风格',
  `wechatShopHomeTheme` varchar(200) NOT NULL DEFAULT 'shop_home' COMMENT '微信端店铺风格',
  `appShopHomeTheme` varchar(200) DEFAULT 'shop_home',
  PRIMARY KEY (`configId`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_custom_page_decoration`;
CREATE TABLE `wst_shop_custom_page_decoration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageId` int(11) NOT NULL,
  `name` varchar(32) NOT NULL DEFAULT '',
  `attr` text NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `sort` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_custom_pages`;
CREATE TABLE `wst_shop_custom_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `pageName` varchar(32) NOT NULL DEFAULT '',
  `isIndex` tinyint(4) NOT NULL DEFAULT 0,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `pageType` tinyint(4) NOT NULL DEFAULT 1,
  `attr` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_express`;
CREATE TABLE `wst_shop_express` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expressId` int(11) NOT NULL,
  `isEnable` tinyint(4) NOT NULL DEFAULT 0,
  `isDefault` tinyint(4) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `shopId` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_extras_cn`;
CREATE TABLE `wst_shop_extras_cn` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `shopId` int(11) NOT NULL,
  `businessLicenceType` tinyint(4) DEFAULT 0,
  `businessLicence` varchar(100) DEFAULT NULL,
  `licenseAddress` varchar(500) DEFAULT NULL,
  `businessAreaPath` varchar(100) DEFAULT NULL,
  `legalPersonName` varchar(100) DEFAULT NULL,
  `establishmentDate` date DEFAULT NULL,
  `businessStartDate` date DEFAULT NULL,
  `businessEndDate` date DEFAULT NULL,
  `isLongbusinessDate` tinyint(4) DEFAULT 0,
  `registeredCapital` decimal(11,0) DEFAULT 0,
  `empiricalRange` varchar(1000) DEFAULT NULL,
  `legalCertificateType` tinyint(4) DEFAULT 0,
  `legalCertificate` varchar(50) DEFAULT NULL,
  `legalCertificateStartDate` date DEFAULT NULL,
  `legalCertificateEndDate` date DEFAULT NULL,
  `isLonglegalCertificateDate` tinyint(4) DEFAULT 0,
  `legalCertificateImg` varchar(150) DEFAULT NULL,
  `businessLicenceImg` varchar(150) DEFAULT NULL,
  `bankAccountPermitImg` varchar(150) DEFAULT NULL,
  `organizationCode` varchar(100) DEFAULT NULL,
  `organizationCodeStartDate` date DEFAULT NULL,
  `organizationCodeEndDate` date DEFAULT NULL,
  `isLongOrganizationCodeDate` tinyint(4) DEFAULT 0,
  `organizationCodeImg` varchar(150) DEFAULT NULL,
  `taxRegistrationCertificateImg` varchar(450) DEFAULT NULL,
  `taxpayerQualificationImg` varchar(150) DEFAULT NULL,
  `taxpayerType` tinyint(4) DEFAULT 0,
  `taxpayerNo` varchar(100) DEFAULT NULL,
  `applyLinkMan` varchar(50) DEFAULT NULL,
  `applyLinkTel` varchar(50) DEFAULT NULL,
  `applyLinkEmail` varchar(50) DEFAULT NULL,
  `isInvestment` tinyint(4) NOT NULL DEFAULT 0,
  `investmentStaff` varchar(50) DEFAULT NULL,
  `applyLinkTelAreaCode` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_extras_hk`;
CREATE TABLE `wst_shop_extras_hk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `businessLicenceType` tinyint(4) DEFAULT 0,
  `businessLicence` varchar(100) DEFAULT NULL,
  `licenseAddress` varchar(500) DEFAULT NULL,
  `businessAreaPath` varchar(100) DEFAULT NULL,
  `legalPersonName` varchar(100) DEFAULT NULL,
  `establishmentDate` date DEFAULT NULL,
  `businessStartDate` date DEFAULT NULL,
  `businessEndDate` date DEFAULT NULL,
  `isLongbusinessDate` tinyint(4) DEFAULT 0,
  `registeredCapital` decimal(11,0) DEFAULT 0,
  `empiricalRange` varchar(1000) DEFAULT NULL,
  `legalCertificateType` tinyint(4) DEFAULT 0,
  `legalCertificate` varchar(50) DEFAULT NULL,
  `legalCertificateStartDate` date DEFAULT NULL,
  `legalCertificateEndDate` date DEFAULT NULL,
  `isLonglegalCertificateDate` tinyint(4) DEFAULT 0,
  `legalCertificateImg` varchar(150) DEFAULT NULL,
  `businessLicenceImg` varchar(150) DEFAULT NULL,
  `bankAccountPermitImg` varchar(150) DEFAULT NULL,
  `organizationCode` varchar(100) DEFAULT NULL,
  `organizationCodeStartDate` date DEFAULT NULL,
  `organizationCodeEndDate` date DEFAULT NULL,
  `isLongOrganizationCodeDate` tinyint(4) DEFAULT 0,
  `organizationCodeImg` varchar(150) DEFAULT NULL,
  `taxRegistrationCertificateImg` varchar(450) DEFAULT NULL,
  `taxpayerQualificationImg` varchar(150) DEFAULT NULL,
  `taxpayerType` tinyint(4) DEFAULT 0,
  `taxpayerNo` varchar(100) DEFAULT NULL,
  `applyLinkMan` varchar(50) DEFAULT NULL,
  `applyLinkTel` varchar(50) DEFAULT NULL,
  `applyLinkEmail` varchar(50) DEFAULT NULL,
  `isInvestment` tinyint(4) NOT NULL DEFAULT 0,
  `investmentStaff` varchar(50) DEFAULT NULL,
  `applyLinkTelAreaCode` varchar(6) DEFAULT '' COMMENT '電話區號(applyLinkTel)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_extras_nzl`;
CREATE TABLE `wst_shop_extras_nzl` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `shopId` int(11) NOT NULL,
  `businessLicenceType` tinyint(4) DEFAULT 0,
  `businessLicence` varchar(100) DEFAULT NULL,
  `licenseAddress` varchar(500) DEFAULT NULL,
  `businessAreaPath` varchar(100) DEFAULT NULL,
  `legalPersonName` varchar(100) DEFAULT NULL,
  `establishmentDate` date DEFAULT NULL,
  `businessStartDate` date DEFAULT NULL,
  `businessEndDate` date DEFAULT NULL,
  `isLongbusinessDate` tinyint(4) DEFAULT 0,
  `empiricalRange` varchar(1000) DEFAULT NULL,
  `legalCertificateType` tinyint(4) DEFAULT 0,
  `legalCertificate` varchar(50) DEFAULT NULL,
  `legalCertificateStartDate` date DEFAULT NULL,
  `legalCertificateEndDate` date DEFAULT NULL,
  `isLonglegalCertificateDate` tinyint(4) DEFAULT 0,
  `legalCertificateImg` varchar(150) DEFAULT NULL,
  `businessLicenceImg` varchar(150) DEFAULT NULL,
  `bankAccountPermitImg` varchar(150) DEFAULT NULL,
  `organizationCode` varchar(100) DEFAULT NULL,
  `organizationCodeStartDate` date DEFAULT NULL,
  `organizationCodeEndDate` date DEFAULT NULL,
  `isLongOrganizationCodeDate` tinyint(4) DEFAULT 0,
  `organizationCodeImg` varchar(150) DEFAULT NULL,
  `taxRegistrationCertificateImg` varchar(450) DEFAULT NULL,
  `taxpayerQualificationImg` varchar(150) DEFAULT NULL,
  `taxpayerType` tinyint(4) DEFAULT 0,
  `taxpayerNo` varchar(100) DEFAULT NULL,
  `applyLinkMan` varchar(50) DEFAULT NULL,
  `applyLinkTel` varchar(50) DEFAULT NULL,
  `applyLinkEmail` varchar(50) DEFAULT NULL,
  `isInvestment` tinyint(4) NOT NULL DEFAULT 0,
  `investmentStaff` varchar(50) DEFAULT NULL,
  `applyLinkTelAreaCode` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_fees`;
CREATE TABLE `wst_shop_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  `money` decimal(11,0) DEFAULT 0,
  `tradeNo` varchar(100) DEFAULT NULL,
  `isRefund` tinyint(4) DEFAULT 0,
  `logMoneyId` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `lockCashMoney` decimal(11,2) DEFAULT 0.00 COMMENT '所使用的充值送金额',
  `outTradeNo` varchar(50) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_flow_areas`;
CREATE TABLE `wst_shop_flow_areas` (
  `areaId` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tableExtraName` varchar(20) NOT NULL COMMENT '入驻扩展表名',
  `sort` tinyint(4) DEFAULT 0,
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效  -1:删除',
  PRIMARY KEY (`areaId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_flow_areas_langs`;
CREATE TABLE `wst_shop_flow_areas_langs` (
  `areaId` int(11) NOT NULL COMMENT '入驻地区ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '语言ID',
  `areaName` varchar(100) DEFAULT '' COMMENT '入驻地区名称',
  PRIMARY KEY (`areaId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_flows`;
CREATE TABLE `wst_shop_flows` (
  `flowId` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `isShow` tinyint(4) DEFAULT 1 COMMENT '是否显示，0隐藏，1显示',
  `sort` tinyint(4) DEFAULT 0 COMMENT '显示排序',
  `isDelete` tinyint(4) DEFAULT 1 COMMENT '是否可以删除，0否，1是',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效  -1:删除',
  `areaId` int(11) DEFAULT 0 COMMENT '入驻地区ID',
  PRIMARY KEY (`flowId`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_flows_langs`;
CREATE TABLE `wst_shop_flows_langs` (
  `flowId` int(11) NOT NULL COMMENT '自增ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '流程ID',
  `flowName` varchar(100) DEFAULT '' COMMENT '流程名称',
  PRIMARY KEY (`flowId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_freight_template`;
CREATE TABLE `wst_shop_freight_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopExpressId` int(11) NOT NULL,
  `tempName` varchar(100) NOT NULL DEFAULT '',
  `tempType` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0：全国 1：指定地区',
  `provinceIds` text DEFAULT NULL COMMENT '省份ID数组',
  `cityIds` text DEFAULT NULL COMMENT '城市ID数组',
  `buyNumStart` int(4) DEFAULT 0 COMMENT '首件(件)',
  `buyNumStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '首件运费(元)',
  `buyNumContinue` int(4) DEFAULT 0 COMMENT '续件(件)',
  `buyNumContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '续件运费(元)',
  `weightStart` decimal(11,2) DEFAULT 0.00 COMMENT '首重（Kg）',
  `weightStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '首重价格',
  `weightContinue` decimal(11,2) DEFAULT 0.00 COMMENT '续重（Kg）',
  `weightContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '续重价格',
  `volumeStart` decimal(11,2) DEFAULT 0.00 COMMENT '首体积量(m³)',
  `volumeStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '首体积运费(元)',
  `volumeContinue` decimal(11,2) DEFAULT 0.00 COMMENT '续体积量(m³)',
  `volumeContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '续体积运费(元)',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `shopId` int(11) DEFAULT 0,
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_member_goods_reduces`;
CREATE TABLE `wst_shop_member_goods_reduces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsId` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `reduceMoney` decimal(11,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_member_groups`;
CREATE TABLE `wst_shop_member_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupName` varchar(50) DEFAULT NULL COMMENT '商家会员分组',
  `shopId` int(11) DEFAULT NULL COMMENT '商家ID',
  `groupOrder` int(11) DEFAULT 0 COMMENT '排序号',
  `minMoney` decimal(11,2) DEFAULT 0.00 COMMENT '最低消费',
  `maxMoney` decimal(11,2) DEFAULT 0.00 COMMENT '最高消费',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_member_user_consumes`;
CREATE TABLE `wst_shop_member_user_consumes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  `money` decimal(11,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_members`;
CREATE TABLE `wst_shop_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL COMMENT '用户ID',
  `groupId` int(11) DEFAULT 0 COMMENT '会员分组ID',
  `shopId` int(11) DEFAULT NULL COMMENT '商家ID',
  `totalOrderMoney` decimal(11,2) DEFAULT 0.00 COMMENT '订单总金额',
  `totalOrderNum` int(11) DEFAULT 0 COMMENT '总下单数',
  `lastOrderTime` datetime DEFAULT NULL COMMENT '最后一次下单时间',
  `isOrder` tinyint(4) DEFAULT 0 COMMENT '0:未下单  1:已下单',
  `createTime` datetime DEFAULT NULL COMMENT '关注时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_message_cats`;
CREATE TABLE `wst_shop_message_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msgDataId` int(11) DEFAULT 0,
  `msgType` tinyint(4) DEFAULT 0,
  `msgCode` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_roles`;
CREATE TABLE `wst_shop_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) DEFAULT 0,
  `roleName` varchar(100) DEFAULT NULL,
  `privilegeMsgs` text DEFAULT NULL,
  `privilegeUrls` text DEFAULT NULL,
  `createTime` datetime DEFAULT NULL,
  `dataFlag` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_scores`;
CREATE TABLE `wst_shop_scores` (
  `scoreId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `totalScore` int(11) NOT NULL DEFAULT 0,
  `totalUsers` int(11) NOT NULL DEFAULT 0,
  `goodsScore` int(11) NOT NULL DEFAULT 0,
  `goodsUsers` int(11) NOT NULL DEFAULT 0,
  `serviceScore` int(11) NOT NULL DEFAULT 0,
  `serviceUsers` int(11) NOT NULL DEFAULT 0,
  `timeScore` int(11) NOT NULL DEFAULT 0,
  `timeUsers` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`scoreId`),
  UNIQUE KEY `shopId` (`shopId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_services`;
CREATE TABLE `wst_shop_services` (
  `shopId` int(11) NOT NULL COMMENT '店铺id',
  `serviceId` varchar(50) NOT NULL COMMENT '客服id',
  `createTime` datetime DEFAULT NULL COMMENT '数据创建时间',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '删除标记',
  KEY `shopId` (`shopId`),
  KEY `serviceId` (`serviceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_styles`;
CREATE TABLE `wst_shop_styles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `styleSys` varchar(255) DEFAULT NULL,
  `styleName` varchar(255) DEFAULT NULL,
  `styleCat` tinyint(4) unsigned DEFAULT 0,
  `stylePath` varchar(255) DEFAULT NULL,
  `screenshot` varchar(255) DEFAULT NULL,
  `isShow` tinyint(4) DEFAULT 1 COMMENT '1:显示  0:隐藏',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_users`;
CREATE TABLE `wst_shop_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `userId` int(11) NOT NULL DEFAULT 0,
  `roleId` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `privilegeMsgTypes` varchar(50) DEFAULT '',
  `privilegeMsgs` varchar(200) DEFAULT '',
  `privilegePhoneMsgs` varchar(200) DEFAULT '',
  `serviceId` varchar(50) NOT NULL DEFAULT '' COMMENT '客服id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shops`;
CREATE TABLE `wst_shops` (
  `shopId` int(11) NOT NULL AUTO_INCREMENT,
  `shopSn` varchar(20) DEFAULT '',
  `userId` int(11) NOT NULL,
  `areaIdPath` varchar(255) DEFAULT '',
  `areaId` int(11) DEFAULT 0,
  `isSelf` tinyint(4) NOT NULL DEFAULT 0,
  `shopName` varchar(100) DEFAULT '',
  `shopkeeper` varchar(50) DEFAULT '',
  `telephone` varchar(20) DEFAULT '',
  `shopCompany` varchar(255) DEFAULT '',
  `shopImg` varchar(150) DEFAULT '',
  `shopTel` varchar(40) DEFAULT '',
  `shopQQ` varchar(50) DEFAULT NULL,
  `shopWangWang` varchar(50) DEFAULT NULL,
  `shopAddress` varchar(255) DEFAULT '',
  `bankId` int(11) DEFAULT 0,
  `bankNo` varchar(20) DEFAULT '',
  `bankUserName` varchar(50) DEFAULT '',
  `isInvoice` tinyint(4) NOT NULL DEFAULT 0,
  `invoiceRemarks` varchar(255) DEFAULT NULL,
  `serviceStartTime` time NOT NULL DEFAULT '08:30:00',
  `serviceEndTime` time NOT NULL DEFAULT '22:30:00',
  `shopAtive` tinyint(4) NOT NULL DEFAULT 1,
  `shopStatus` tinyint(4) NOT NULL DEFAULT 1,
  `statusDesc` varchar(255) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` date DEFAULT NULL,
  `shopMoney` decimal(11,2) DEFAULT 0.00,
  `lockMoney` decimal(11,2) DEFAULT 0.00,
  `noSettledOrderNum` int(11) DEFAULT 0,
  `noSettledOrderFee` decimal(11,2) DEFAULT 0.00,
  `paymentMoney` decimal(11,2) DEFAULT 0.00,
  `bankAreaId` int(11) DEFAULT 0,
  `bankAreaIdPath` varchar(100) DEFAULT NULL,
  `applyStatus` tinyint(4) DEFAULT 0,
  `applyDesc` varchar(255) DEFAULT NULL,
  `applyTime` datetime DEFAULT NULL,
  `applyStep` tinyint(4) DEFAULT 1,
  `shopNotice` varchar(300) DEFAULT NULL COMMENT '店铺公告',
  `rechargeMoney` decimal(11,2) DEFAULT 0.00 COMMENT '充值金额',
  `longitude` decimal(10,7) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `mapLevel` int(11) DEFAULT 16,
  `expireDate` date DEFAULT NULL COMMENT '到期日期',
  `isPay` tinyint(4) DEFAULT 0 COMMENT '是否支付年费，0否,1是',
  `payAnnualFee` decimal(11,2) DEFAULT 0.00 COMMENT '支付年费金额',
  `isRefund` tinyint(4) DEFAULT 0 COMMENT '是否退款年费，0否，1是',
  `tradeId` int(11) DEFAULT 0 COMMENT '所属行业ID',
  `areaCode` varchar(6) DEFAULT '' COMMENT '電話區號(telephone)',
  `telephoneAreaCode` varchar(6) DEFAULT '' COMMENT '電話區號(telephone)',
  `flowAreaId` int(11) DEFAULT 0 COMMENT '入驻地区Id',
  `isMarket` tinyint(11) DEFAULT 0 COMMENT '是否大店',
  `marketId` int(11) DEFAULT 0 COMMENT '大店ID',
  `mallCommissionRate` decimal(11,2) DEFAULT 0.00 COMMENT '平台佣金比例（商户给Mall）',
  `marketCommissionRate` decimal(11,2) DEFAULT 0.00 COMMENT '大店佣金比例（Mall给大店）',
  `marketNotice` varchar(300) DEFAULT NULL COMMENT '大店公告',
  PRIMARY KEY (`shopId`),
  KEY `shopStatus` (`shopStatus`,`dataFlag`) USING BTREE,
  KEY `areaIdPath` (`areaIdPath`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_spec_cats`;
CREATE TABLE `wst_spec_cats` (
  `catId` int(11) NOT NULL AUTO_INCREMENT,
  `goodsCatId` int(11) NOT NULL DEFAULT 0,
  `goodsCatPath` varchar(100) NOT NULL,
  `isAllowImg` tinyint(4) NOT NULL DEFAULT 0,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `catSort` int(11) DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `shopId` int(11) DEFAULT 0,
  PRIMARY KEY (`catId`),
  KEY `shopId` (`goodsCatPath`,`dataFlag`),
  KEY `isShow` (`isShow`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=570 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_spec_cats_langs`;
CREATE TABLE `wst_spec_cats_langs` (
  `catId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `catName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`catId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_spec_items`;
CREATE TABLE `wst_spec_items` (
  `itemId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `catId` int(11) NOT NULL DEFAULT 0,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `itemImg` varchar(150) NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`itemId`),
  KEY `goodsId` (`goodsId`)
) ENGINE=InnoDB AUTO_INCREMENT=4615 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_spec_items_langs`;
CREATE TABLE `wst_spec_items_langs` (
  `itemId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `itemName` varchar(100) DEFAULT NULL,
  `itemDesc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`itemId`,`langId`,`goodsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_staffs`;
CREATE TABLE `wst_staffs` (
  `staffId` int(11) NOT NULL AUTO_INCREMENT,
  `loginName` varchar(40) NOT NULL,
  `loginPwd` varchar(50) NOT NULL,
  `secretKey` int(32) NOT NULL,
  `staffName` varchar(50) NOT NULL,
  `staffNo` varchar(20) DEFAULT NULL,
  `staffPhoto` varchar(150) DEFAULT NULL,
  `staffRoleId` int(11) NOT NULL,
  `workStatus` tinyint(4) NOT NULL DEFAULT 1,
  `staffStatus` tinyint(4) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `lastTime` datetime DEFAULT NULL,
  `lastIP` char(16) DEFAULT NULL,
  `wxOpenId` varchar(100) DEFAULT NULL,
  `staffPhone` char(11) DEFAULT NULL,
  `areaCode` varchar(6) DEFAULT '' COMMENT '電話區號',
  PRIMARY KEY (`staffId`),
  KEY `loginName` (`loginName`),
  KEY `staffStatus` (`staffStatus`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_store_roles`;
CREATE TABLE `wst_store_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) DEFAULT 0,
  `storeId` int(11) DEFAULT 0,
  `roleName` varchar(100) DEFAULT NULL,
  `privilegeUrls` text DEFAULT NULL,
  `createTime` datetime DEFAULT NULL,
  `dataFlag` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_store_users`;
CREATE TABLE `wst_store_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `storeId` int(11) NOT NULL DEFAULT 0,
  `userId` int(11) NOT NULL DEFAULT 0,
  `roleId` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `privilegeMsgs` varchar(200) DEFAULT '',
  `privilegeMsgTypes` varchar(50) DEFAULT '',
  `privilegePhoneMsgs` varchar(200) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_stores`;
CREATE TABLE `wst_stores` (
  `storeId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT 0,
  `userId` int(11) NOT NULL,
  `areaIdPath` varchar(100) DEFAULT '',
  `areaId` int(11) DEFAULT 0,
  `storeName` varchar(200) DEFAULT '',
  `storeImg` varchar(150) DEFAULT '',
  `storeTel` varchar(40) DEFAULT '',
  `storeAddress` varchar(255) DEFAULT '',
  `longitude` decimal(10,7) DEFAULT NULL,
  `createTime` date DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `mapLevel` int(11) DEFAULT 16,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `storeStatus` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`storeId`),
  KEY `shopStatus` (`dataFlag`) USING BTREE,
  KEY `areaIdPath` (`areaIdPath`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_styles`;
CREATE TABLE `wst_styles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `styleSys` varchar(20) DEFAULT NULL,
  `styleName` varchar(255) NOT NULL,
  `styleAuthor` varchar(255) DEFAULT NULL,
  `styleShopSite` varchar(11) DEFAULT NULL,
  `styleShopId` int(11) DEFAULT 0,
  `stylePath` varchar(255) NOT NULL,
  `isUse` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `isUse` (`isUse`,`styleSys`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_applys`;
CREATE TABLE `wst_supplier_applys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL COMMENT '供货商ID',
  `linkPhone` varchar(20) NOT NULL COMMENT ' 申请联系电话',
  `linkman` varchar(50) NOT NULL COMMENT '申请联系人',
  `applyIntention` varchar(600) NOT NULL COMMENT '申请意向',
  `supplierName` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '搴楅摵鍚嶇О',
  `handleReamrk` varchar(600) DEFAULT NULL,
  `applyStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '申请状态 0:待处理  1:已处理  -1:无效',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效 -1:删除',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_bases`;
CREATE TABLE `wst_supplier_bases` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `flowId` int(11) DEFAULT 0 COMMENT '流程ID',
  `fieldName` varchar(50) DEFAULT '' COMMENT '表单字段',
  `dataType` varchar(10) DEFAULT '' COMMENT '数据类型',
  `dataLength` int(11) DEFAULT 0 COMMENT '数据长度',
  `fieldSort` tinyint(4) unsigned DEFAULT 0 COMMENT '显示排序',
  `isRequire` tinyint(4) unsigned DEFAULT 0 COMMENT '是否必填，0否，1是',
  `fieldType` varchar(10) DEFAULT '' COMMENT '表单类型',
  `isRelevance` tinyint(4) unsigned DEFAULT 0 COMMENT '是否关联字段，0否，1是',
  `fieldRelevance` varchar(50) DEFAULT '' COMMENT '关联字段',
  `dateRelevance` varchar(100) DEFAULT '' COMMENT '日期关联字段',
  `timeRelevance` varchar(100) DEFAULT '' COMMENT '时间关联字段',
  `isShow` tinyint(4) unsigned DEFAULT 1 COMMENT '是否显示，0隐藏，1显示',
  `isMap` tinyint(4) unsigned DEFAULT 0 COMMENT '是否显示地图，0隐藏，1显示',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效  -1:删除',
  `isDelete` tinyint(4) DEFAULT 1 COMMENT '是否可以删除，0否，1是',
  `isSuppliersTable` tinyint(4) DEFAULT 0,
  `fileNum` tinyint(4) DEFAULT NULL,
  `isPhone` tinyint(4) DEFAULT 0 COMMENT '是否手机号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_bases_langs`;
CREATE TABLE `wst_supplier_bases_langs` (
  `baseId` int(11) NOT NULL COMMENT '自增ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '流程ID',
  `fieldTitle` varchar(255) DEFAULT NULL COMMENT '表单标题',
  `fieldComment` varchar(500) DEFAULT NULL COMMENT '表单注释',
  `fieldAttr` varchar(255) DEFAULT '' COMMENT '表单属性',
  PRIMARY KEY (`baseId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_carts`;
CREATE TABLE `wst_supplier_carts` (
  `cartId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT 0,
  `isCheck` tinyint(4) NOT NULL DEFAULT 1,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `goodsSpecId` varchar(200) NOT NULL DEFAULT '0',
  `cartNum` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`cartId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_cats`;
CREATE TABLE `wst_supplier_cats` (
  `catId` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL,
  `parentId` int(11) NOT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `catSort` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`catId`),
  KEY `parentId` (`isShow`,`dataFlag`) USING BTREE,
  KEY `supplierId` (`supplierId`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_cats_langs`;
CREATE TABLE `wst_supplier_cats_langs` (
  `catId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `catName` varchar(50) NOT NULL,
  PRIMARY KEY (`catId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_configs`;
CREATE TABLE `wst_supplier_configs` (
  `configId` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL,
  `supplierTitle` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `supplierKeywords` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `supplierDesc` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `supplierBanner` varchar(150) CHARACTER SET utf8 DEFAULT NULL,
  `supplierAds` text CHARACTER SET utf8 DEFAULT NULL,
  `supplierAdsUrl` text CHARACTER SET utf8 DEFAULT NULL,
  `supplierServicer` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `supplierHotWords` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`configId`),
  KEY `supplierId` (`supplierId`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_express`;
CREATE TABLE `wst_supplier_express` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expressId` int(11) NOT NULL,
  `isEnable` tinyint(4) NOT NULL DEFAULT 0,
  `isDefault` tinyint(4) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `supplierId` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_extras_hk`;
CREATE TABLE `wst_supplier_extras_hk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL,
  `businessLicenceType` tinyint(4) DEFAULT 0,
  `businessLicence` varchar(100) DEFAULT NULL,
  `licenseAddress` varchar(500) DEFAULT NULL,
  `businessAreaPath` varchar(100) DEFAULT NULL,
  `legalPersonName` varchar(100) DEFAULT NULL,
  `establishmentDate` date DEFAULT NULL,
  `businessStartDate` date DEFAULT NULL,
  `businessEndDate` date DEFAULT NULL,
  `isLongbusinessDate` tinyint(4) DEFAULT 0,
  `registeredCapital` decimal(11,0) DEFAULT 0,
  `empiricalRange` varchar(1000) DEFAULT NULL,
  `legalCertificateType` tinyint(4) DEFAULT 0,
  `legalCertificate` varchar(50) DEFAULT NULL,
  `legalCertificateStartDate` date DEFAULT NULL,
  `legalCertificateEndDate` date DEFAULT NULL,
  `isLonglegalCertificateDate` tinyint(4) DEFAULT 0,
  `legalCertificateImg` varchar(150) DEFAULT NULL,
  `businessLicenceImg` varchar(150) DEFAULT NULL,
  `bankAccountPermitImg` varchar(150) DEFAULT NULL,
  `organizationCode` varchar(100) DEFAULT NULL,
  `organizationCodeStartDate` date DEFAULT NULL,
  `organizationCodeEndDate` date DEFAULT NULL,
  `isLongOrganizationCodeDate` tinyint(4) DEFAULT 0,
  `organizationCodeImg` varchar(150) DEFAULT NULL,
  `taxRegistrationCertificateImg` varchar(450) DEFAULT NULL,
  `taxpayerQualificationImg` varchar(150) DEFAULT NULL,
  `taxpayerType` tinyint(4) DEFAULT 0,
  `taxpayerNo` varchar(100) DEFAULT NULL,
  `applyLinkMan` varchar(50) DEFAULT NULL,
  `applyLinkTel` varchar(50) DEFAULT NULL,
  `applyLinkEmail` varchar(50) DEFAULT NULL,
  `isInvestment` tinyint(4) NOT NULL DEFAULT 0,
  `investmentStaff` varchar(50) DEFAULT NULL,
  `applyLinkTelAreaCode` varchar(6) DEFAULT '' COMMENT '電話區號(applyLinkTel)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_fees`;
CREATE TABLE `wst_supplier_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `supplierId` int(11) NOT NULL,
  `money` decimal(11,0) DEFAULT 0,
  `tradeNo` varchar(100) DEFAULT NULL,
  `isRefund` tinyint(4) DEFAULT 0,
  `logMoneyId` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `lockCashMoney` decimal(11,2) DEFAULT 0.00,
  `outTradeNo` varchar(50) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_flow_areas`;
CREATE TABLE `wst_supplier_flow_areas` (
  `areaId` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tableExtraName` varchar(20) NOT NULL COMMENT '入驻扩展表名',
  `sort` tinyint(4) DEFAULT 0,
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效  -1:删除',
  PRIMARY KEY (`areaId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_flow_areas_langs`;
CREATE TABLE `wst_supplier_flow_areas_langs` (
  `areaId` int(11) NOT NULL COMMENT '入驻地区ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '语言ID',
  `areaName` varchar(100) DEFAULT '' COMMENT '入驻地区名称',
  PRIMARY KEY (`areaId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_flows`;
CREATE TABLE `wst_supplier_flows` (
  `flowId` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `isShow` tinyint(4) DEFAULT 1 COMMENT '是否显示，0隐藏，1显示',
  `sort` tinyint(4) DEFAULT 0 COMMENT '显示排序',
  `isDelete` tinyint(4) DEFAULT 1 COMMENT '是否可以删除，0否，1是',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效  -1:删除',
  `areaId` int(11) DEFAULT 0 COMMENT '入驻地区ID',
  PRIMARY KEY (`flowId`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_flows_langs`;
CREATE TABLE `wst_supplier_flows_langs` (
  `flowId` int(11) NOT NULL COMMENT '自增ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '流程ID',
  `flowName` varchar(100) DEFAULT '' COMMENT '流程名称',
  PRIMARY KEY (`flowId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_freight_template`;
CREATE TABLE `wst_supplier_freight_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierExpressId` int(11) NOT NULL COMMENT '供货商ID',
  `tempName` varchar(200) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `tempType` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0：全国 1：指定省份',
  `provinceIds` text DEFAULT NULL,
  `cityIds` text DEFAULT NULL,
  `buyNumStart` int(11) DEFAULT 0 COMMENT '棣栦欢(浠?',
  `buyNumStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '首件运费(元)',
  `buyNumContinue` int(11) DEFAULT 0 COMMENT '缁欢(浠?',
  `buyNumContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '续件运费(元)',
  `weightStart` decimal(11,2) DEFAULT 0.00 COMMENT '首重（Kg）',
  `weightStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '首重价格',
  `weightContinue` decimal(11,2) DEFAULT 0.00 COMMENT '续重（Kg）',
  `weightContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '续重价格',
  `volumeStart` decimal(11,2) DEFAULT 0.00 COMMENT '首体积量(m³)',
  `volumeStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '首体积运费(元)',
  `volumeContinue` decimal(11,2) DEFAULT 0.00 COMMENT '续体积量(m³)',
  `volumeContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '续体积运费(元)',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `supplierId` int(11) DEFAULT 0,
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_goods`;
CREATE TABLE `wst_supplier_goods` (
  `goodsId` int(11) NOT NULL AUTO_INCREMENT,
  `goodsSn` varchar(20) NOT NULL,
  `productNo` varchar(20) NOT NULL,
  `goodsImg` varchar(150) NOT NULL,
  `supplierId` int(11) NOT NULL,
  `goodsType` tinyint(4) NOT NULL DEFAULT 0,
  `marketPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `supplierPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `warnStock` int(11) NOT NULL DEFAULT 0,
  `goodsStock` int(11) NOT NULL DEFAULT 0,
  `goodsUnit` char(10) NOT NULL,
  `isSale` tinyint(4) NOT NULL DEFAULT 1,
  `isBest` tinyint(4) NOT NULL DEFAULT 0,
  `isHot` tinyint(4) NOT NULL DEFAULT 0,
  `isNew` tinyint(4) NOT NULL DEFAULT 0,
  `isRecom` tinyint(4) DEFAULT 0,
  `goodsCatIdPath` varchar(255) DEFAULT NULL,
  `goodsCatId` int(11) NOT NULL,
  `supplierCatId1` int(11) NOT NULL,
  `supplierCatId2` int(11) NOT NULL,
  `brandId` int(11) DEFAULT 0,
  `goodsStatus` tinyint(4) NOT NULL DEFAULT 0,
  `saleNum` int(11) NOT NULL DEFAULT 0,
  `saleTime` datetime NOT NULL,
  `visitNum` int(11) DEFAULT 0,
  `appraiseNum` int(11) DEFAULT 0,
  `isSpec` tinyint(4) NOT NULL DEFAULT 0,
  `gallery` text DEFAULT NULL,
  `illegalRemarks` varchar(255) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `isFreeShipping` tinyint(4) DEFAULT 0,
  `goodsSerachKeywords` text DEFAULT NULL,
  `goodsVideo` varchar(150) DEFAULT NULL,
  `costPrice` decimal(11,2) DEFAULT 0.00,
  `goodsVideoThumb` varchar(150) DEFAULT '',
  `shippingFeeType` tinyint(4) DEFAULT 1 COMMENT '计价方式 1:计件 2:重量 3:体积',
  `goodsWeight` decimal(11,2) DEFAULT 0.00 COMMENT '商品重量',
  `goodsVolume` decimal(11,2) DEFAULT 0.00 COMMENT '商品体积',
  `supplierExpressId` int(11) DEFAULT 0 COMMENT '店铺快递公司ID',
  `isWholesale` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`goodsId`),
  KEY `supplierId` (`supplierId`) USING BTREE,
  KEY `goodsStatus` (`goodsStatus`,`dataFlag`,`isSale`) USING BTREE,
  KEY `goodsCatIdPath` (`goodsCatIdPath`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_goods_appraises`;
CREATE TABLE `wst_supplier_goods_appraises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL DEFAULT 0,
  `orderId` int(11) NOT NULL DEFAULT 0,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `goodsSpecId` int(11) NOT NULL DEFAULT 0,
  `userId` int(11) NOT NULL DEFAULT 0,
  `goodsScore` int(11) NOT NULL DEFAULT 0,
  `serviceScore` int(11) NOT NULL DEFAULT 0,
  `timeScore` int(11) NOT NULL DEFAULT 0,
  `content` text NOT NULL,
  `supplierReply` text CHARACTER SET utf8 DEFAULT NULL,
  `images` text DEFAULT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `replyTime` date DEFAULT NULL,
  `orderGoodsId` int(10) unsigned NOT NULL COMMENT '订单商品表Id',
  PRIMARY KEY (`id`),
  KEY `supplierId` (`supplierId`),
  KEY `goodsId` (`goodsId`,`goodsSpecId`,`dataFlag`,`isShow`) USING BTREE,
  KEY `userId` (`userId`),
  KEY `orderGoodsId` (`orderGoodsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_goods_attributes`;
CREATE TABLE `wst_supplier_goods_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `attrId` int(11) NOT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplierId` (`supplierId`),
  KEY `goodsId` (`goodsId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_goods_attributes_langs`;
CREATE TABLE `wst_supplier_goods_attributes_langs` (
  `goodsAttrId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `attrVal` text DEFAULT NULL,
  PRIMARY KEY (`goodsAttrId`,`langId`,`goodsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `wst_supplier_goods_consult`;
CREATE TABLE `wst_supplier_goods_consult` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `goodsId` int(10) unsigned NOT NULL COMMENT '商品id',
  `userId` int(10) unsigned DEFAULT NULL COMMENT '用户id',
  `consultType` tinyint(3) unsigned DEFAULT NULL COMMENT '咨询类别',
  `consultContent` varchar(500) DEFAULT NULL COMMENT '咨询内容',
  `createTime` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '咨询时间',
  `reply` varchar(255) NOT NULL DEFAULT '' COMMENT '商家回复',
  `replyTime` datetime DEFAULT NULL COMMENT '回复时间',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '数据有效标志',
  `isShow` tinyint(4) DEFAULT 1 COMMENT '是否显示数据',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_goods_copyrelates`;
CREATE TABLE `wst_supplier_goods_copyrelates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsId` int(11) DEFAULT 0,
  `supplierGoodsId` int(11) DEFAULT 0,
  `shopId` int(11) DEFAULT 0,
  `supplierId` int(11) DEFAULT 0,
  `dataFlag` tinyint(4) DEFAULT 1,
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `goodsStatus` (`dataFlag`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_goods_langs`;
CREATE TABLE `wst_supplier_goods_langs` (
  `goodsId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsName` varchar(200) DEFAULT NULL,
  `goodsTips` text DEFAULT NULL,
  `goodsDesc` text DEFAULT NULL,
  `goodsSeoKeywords` varchar(200) DEFAULT NULL,
  `goodsSeoDesc` varchar(600) DEFAULT NULL,
  PRIMARY KEY (`goodsId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_goods_scores`;
CREATE TABLE `wst_supplier_goods_scores` (
  `scoreId` int(11) NOT NULL AUTO_INCREMENT,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `supplierId` int(11) NOT NULL DEFAULT 0,
  `totalScore` int(11) NOT NULL DEFAULT 0,
  `totalUsers` int(11) NOT NULL DEFAULT 0,
  `goodsScore` int(11) NOT NULL DEFAULT 0,
  `goodsUsers` int(11) NOT NULL DEFAULT 0,
  `serviceScore` int(11) NOT NULL DEFAULT 0,
  `serviceUsers` int(11) NOT NULL DEFAULT 0,
  `timeScore` int(11) NOT NULL DEFAULT 0,
  `timeUsers` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`scoreId`),
  KEY `goodsId` (`goodsId`,`supplierId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_goods_specs`;
CREATE TABLE `wst_supplier_goods_specs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL DEFAULT 0,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `productNo` varchar(20) NOT NULL,
  `specIds` varchar(255) NOT NULL,
  `marketPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `specPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `specStock` int(11) NOT NULL DEFAULT 0,
  `warnStock` int(11) NOT NULL DEFAULT 0,
  `saleNum` int(11) NOT NULL DEFAULT 0,
  `isDefault` tinyint(4) DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `specWeight` decimal(11,2) DEFAULT 0.00 COMMENT '商品重量',
  `specVolume` decimal(11,2) DEFAULT 0.00 COMMENT '商品体积',
  `costPrice` decimal(11,2) DEFAULT 0.00 COMMENT '成本价',
  PRIMARY KEY (`id`),
  KEY `supplierId` (`goodsId`,`dataFlag`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_log_operates`;
CREATE TABLE `wst_supplier_log_operates` (
  `operateId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT 0,
  `operateTime` datetime NOT NULL,
  `menuId` int(11) NOT NULL,
  `operateDesc` varchar(255) NOT NULL,
  `operateUrl` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `operateIP` varchar(20) DEFAULT NULL,
  `supplierId` int(4) DEFAULT 1,
  PRIMARY KEY (`operateId`),
  KEY `operateTime` (`userId`,`menuId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_log_orders`;
CREATE TABLE `wst_supplier_log_orders` (
  `logId` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL DEFAULT 0,
  `orderStatus` int(11) NOT NULL,
  `logJson` text DEFAULT NULL,
  `logUserId` int(11) NOT NULL DEFAULT 0,
  `logType` tinyint(4) NOT NULL DEFAULT 0,
  `logTime` datetime NOT NULL,
  PRIMARY KEY (`logId`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_log_services`;
CREATE TABLE `wst_supplier_log_services` (
  `logId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '订单id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '售后id',
  `logJson` text DEFAULT NULL,
  `logTargetId` int(10) unsigned NOT NULL COMMENT '操作者Id,如果是商家的话记录商家的ID',
  `logType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '操作者类型,0：商家  1：供货商',
  `logTime` datetime DEFAULT current_timestamp() COMMENT '创建时间',
  PRIMARY KEY (`logId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_order_complains`;
CREATE TABLE `wst_supplier_order_complains` (
  `complainId` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL DEFAULT 0,
  `complainType` tinyint(4) NOT NULL DEFAULT 1,
  `complainTargetId` int(11) NOT NULL DEFAULT 0,
  `respondTargetId` int(11) NOT NULL DEFAULT 0,
  `needRespond` tinyint(4) NOT NULL DEFAULT 0,
  `deliverRespondTime` datetime DEFAULT NULL,
  `complainContent` text NOT NULL,
  `complainAnnex` varchar(255) DEFAULT NULL,
  `complainStatus` tinyint(4) NOT NULL DEFAULT 0,
  `complainTime` datetime NOT NULL,
  `respondContent` text DEFAULT NULL,
  `respondAnnex` varchar(255) DEFAULT NULL,
  `respondTime` datetime DEFAULT NULL,
  `finalResult` text DEFAULT NULL,
  `finalResultTime` datetime DEFAULT NULL,
  `finalHandleStaffId` int(11) DEFAULT 0,
  PRIMARY KEY (`complainId`),
  KEY `complainStatus` (`complainStatus`),
  KEY `complainType` (`complainTargetId`,`complainType`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_order_express`;
CREATE TABLE `wst_supplier_order_express` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `orderGoodsId` varchar(100) NOT NULL,
  `deliveryTime` datetime DEFAULT NULL,
  `isExpress` tinyint(4) DEFAULT 0,
  `expressId` int(11) DEFAULT NULL,
  `expressNo` varchar(20) DEFAULT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `deliverType` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_order_goods`;
CREATE TABLE `wst_supplier_order_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `goodsNum` int(11) NOT NULL DEFAULT 0,
  `goodsPrice` decimal(11,2) NOT NULL DEFAULT 0.00,
  `goodsSpecId` int(11) DEFAULT 0,
  `goodsSpecNames` varchar(500) DEFAULT NULL,
  `goodsName` varchar(200) NOT NULL,
  `goodsImg` varchar(150) NOT NULL,
  `extraJson` text DEFAULT NULL,
  `goodsType` tinyint(4) NOT NULL DEFAULT 0,
  `commissionRate` decimal(11,2) DEFAULT 0.00,
  `goodsCode` varchar(20) DEFAULT NULL,
  `promotionJson` text DEFAULT NULL,
  `orderGoodscommission` decimal(11,2) DEFAULT 0.00 COMMENT '订单商品佣金',
  PRIMARY KEY (`id`),
  KEY `goodsId` (`goodsId`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_order_refunds`;
CREATE TABLE `wst_supplier_order_refunds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `refundTo` int(11) NOT NULL DEFAULT 0,
  `refundReson` int(11) NOT NULL DEFAULT 0,
  `refundOtherReson` varchar(255) DEFAULT NULL,
  `backMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `refundTradeNo` varchar(100) DEFAULT NULL,
  `refundRemark` varchar(500) DEFAULT NULL,
  `refundTime` datetime DEFAULT NULL,
  `supplierRejectReason` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `refundStatus` tinyint(4) NOT NULL DEFAULT 0,
  `createTime` datetime NOT NULL,
  `serviceId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '售后单id',
  `isServiceRefund` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '售后单是否已退款0：未退款 1：已退款',
  `refundProcessStatus` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_order_services`;
CREATE TABLE `wst_supplier_order_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `orderId` int(10) unsigned NOT NULL COMMENT '订单id',
  `goodsServiceType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '0：退款退货 1：退款 2：换货',
  `serviceType` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '退换货类型,数据由基础数据类型里取',
  `serviceRemark` varchar(600) DEFAULT NULL COMMENT '退换货原因',
  `serviceAnnex` text DEFAULT NULL COMMENT '附件',
  `refundMoney` decimal(11,2) DEFAULT NULL COMMENT '申请退款的金额',
  `refundableMoney` decimal(11,2) DEFAULT NULL COMMENT '售后单可退款金额',
  `isSupplierAgree` tinyint(4) DEFAULT 0 COMMENT '1:同意 0：不同意',
  `disagreeRemark` varchar(600) DEFAULT NULL COMMENT '商家不同意原因',
  `userAddressId` int(11) unsigned DEFAULT 0 COMMENT '用户收货地址id',
  `areaId` int(11) unsigned DEFAULT 0 COMMENT '地区id',
  `areaIdPath` varchar(255) DEFAULT NULL COMMENT '地区ID值',
  `userName` varchar(200) DEFAULT '' COMMENT '用户收货人',
  `userAddress` varchar(200) DEFAULT '' COMMENT '用户详细收货地址',
  `userPhone` varchar(200) DEFAULT '' COMMENT '用户收货电话',
  `supplierAreaId` int(11) unsigned DEFAULT 0 COMMENT '商家收货地区ID',
  `supplierAreaIdPath` varchar(255) DEFAULT NULL COMMENT '商家收货地区ID值',
  `supplierName` varchar(200) DEFAULT '' COMMENT '商家收货人',
  `supplierAddress` varchar(200) DEFAULT '' COMMENT '商家详细收货地址',
  `supplierPhone` varchar(200) DEFAULT '' COMMENT '商家收货电话',
  `isUserSendGoods` tinyint(4) DEFAULT 0 COMMENT '0：未发货 1：已发货',
  `expressType` tinyint(4) DEFAULT 0 COMMENT '0：无需物流  1：快递',
  `expressId` int(11) unsigned DEFAULT 0 COMMENT '买家快递ID',
  `expressNo` varchar(200) DEFAULT '' COMMENT '买家物流单号',
  `isSupplierAccept` tinyint(4) DEFAULT 0 COMMENT '商家是否收到货 -1：拒收  0：未收货  1：已收货',
  `supplierRejectType` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '商家拒收类型,数据由基础数据类型里取',
  `supplierRejectOther` varchar(600) DEFAULT NULL COMMENT '商家拒收原因,选择“其他”的时候填写文字',
  `supplierRejectImg` varchar(150) DEFAULT NULL COMMENT '拒收时的货物图片',
  `isSupplierSend` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '商家是否发货 0：未发货 1：已发货',
  `supplierExpressType` tinyint(4) DEFAULT 0 COMMENT '0：无需物流  1：快递',
  `supplierExpressId` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '商家快递ID',
  `supplierExpressNo` varchar(200) DEFAULT '' COMMENT '商家快递单号',
  `isUserAccept` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '-1：拒收 0：未收货  1：已收货',
  `userRejectType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '用户拒收原因,数据由基础数据类型里取',
  `userRejectOther` varchar(600) DEFAULT NULL COMMENT '用户拒收原因,选择“其他”的时候填写文字',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `isClose` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '是否结束 0：进行中  1:已结束',
  `serviceStatus` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '状态备注：0：待商家审核  1：等待用户发货 2：等待商家收货 3：等待商家发货  4：等待用户收货  5：完成退款/退货  6：商家已确认收货 7：商家受理，等待管理员退款',
  `supplierAcceptExpireTime` datetime DEFAULT NULL COMMENT '商家受理期限',
  `userSendExpireTime` datetime DEFAULT NULL COMMENT '用户发货期限',
  `supplierReceiveExpireTime` datetime DEFAULT NULL COMMENT '商家收货期限',
  `userReceiveExpireTime` datetime DEFAULT NULL COMMENT '用户收货期限',
  `shopSendExpireTime` datetime DEFAULT NULL COMMENT '商家发货期限',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_orders`;
CREATE TABLE `wst_supplier_orders` (
  `orderId` int(11) NOT NULL AUTO_INCREMENT,
  `orderNo` varchar(20) NOT NULL,
  `supplierId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `orderStatus` tinyint(4) NOT NULL DEFAULT -2,
  `goodsMoney` decimal(11,2) NOT NULL,
  `deliverType` tinyint(4) NOT NULL DEFAULT 0,
  `deliverMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `totalMoney` decimal(11,2) NOT NULL,
  `realTotalMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `payType` tinyint(4) NOT NULL DEFAULT 0,
  `payFrom` varchar(20) DEFAULT NULL,
  `isPay` tinyint(4) NOT NULL DEFAULT 0,
  `areaId` int(11) NOT NULL,
  `areaIdPath` varchar(255) DEFAULT NULL,
  `userName` varchar(50) NOT NULL,
  `userAddress` varchar(255) NOT NULL,
  `userPhone` char(20) DEFAULT NULL,
  `isInvoice` tinyint(4) NOT NULL DEFAULT 0,
  `invoiceClient` varchar(255) DEFAULT NULL,
  `orderRemarks` varchar(255) DEFAULT NULL,
  `orderSrc` tinyint(4) NOT NULL DEFAULT 0,
  `needPay` decimal(11,2) DEFAULT 0.00,
  `payRand` int(11) DEFAULT 1,
  `orderType` int(11) DEFAULT 0,
  `isRefund` tinyint(4) NOT NULL DEFAULT 0,
  `isAppraise` tinyint(4) DEFAULT 0,
  `cancelReason` int(11) DEFAULT 0,
  `rejectReason` int(11) DEFAULT 0,
  `rejectOtherReason` varchar(255) DEFAULT NULL,
  `isClosed` tinyint(4) NOT NULL DEFAULT 0,
  `orderunique` varchar(50) NOT NULL,
  `receiveTime` datetime DEFAULT NULL,
  `deliveryTime` datetime DEFAULT NULL,
  `tradeNo` varchar(100) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `settlementId` int(11) DEFAULT 0,
  `commissionFee` decimal(11,2) DEFAULT 0.00,
  `orderCode` varchar(20) DEFAULT 'order',
  `extraJson` text DEFAULT NULL,
  `orderCodeTargetId` int(11) DEFAULT 0,
  `noticeDeliver` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '提醒发货 0:未提醒 1:已提醒',
  `invoiceJson` text DEFAULT NULL COMMENT '发票信息',
  `lockCashMoney` decimal(11,2) DEFAULT 0.00 COMMENT '锁定提现金额',
  `payTime` datetime DEFAULT NULL,
  `isBatch` tinyint(4) DEFAULT 0,
  `totalPayFee` int(11) DEFAULT 0,
  `isMakeInvoice` tinyint(4) DEFAULT 0 COMMENT '是否开发票 1:是 0:否',
  `afterSaleEndTime` datetime DEFAULT NULL COMMENT '售后结束时间,确认收货时间+后台设置的售后有效天数',
  `refundedPayMoney` decimal(11,2) DEFAULT 0.00 COMMENT '已退款支付金额',
  `shopId` int(11) DEFAULT 0,
  `verificationCode` varchar(20) DEFAULT '0' COMMENT '核验码',
  `verificationTime` datetime DEFAULT NULL COMMENT '核验时间',
  `supplierRejectReason` int(11) unsigned DEFAULT 0 COMMENT '供货商拒收原因，用于''自提订单''',
  `supplierRejectOtherReason` varchar(255) DEFAULT NULL COMMENT '供货商拒收说明，当shopRejectReason=10000时该字段不为空',
  `outTradeNo` varchar(50) DEFAULT '',
  PRIMARY KEY (`orderId`),
  KEY `supplierId` (`supplierId`,`dataFlag`),
  KEY `userId` (`userId`,`dataFlag`),
  KEY `isRefund` (`isRefund`),
  KEY `orderStatus` (`orderStatus`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_roles`;
CREATE TABLE `wst_supplier_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) DEFAULT 0,
  `roleName` varchar(100) DEFAULT NULL,
  `privilegeUrls` text DEFAULT NULL,
  `createTime` datetime DEFAULT NULL,
  `dataFlag` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_scores`;
CREATE TABLE `wst_supplier_scores` (
  `scoreId` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL,
  `totalScore` int(11) NOT NULL DEFAULT 0,
  `totalUsers` int(11) NOT NULL DEFAULT 0,
  `goodsScore` int(11) NOT NULL DEFAULT 0,
  `goodsUsers` int(11) NOT NULL DEFAULT 0,
  `serviceScore` int(11) NOT NULL DEFAULT 0,
  `serviceUsers` int(11) NOT NULL DEFAULT 0,
  `timeScore` int(11) NOT NULL DEFAULT 0,
  `timeUsers` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`scoreId`),
  UNIQUE KEY `supplierId` (`supplierId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_service_goods`;
CREATE TABLE `wst_supplier_service_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '订单id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '售后id',
  `goodsId` int(10) unsigned NOT NULL COMMENT '商品ID',
  `goodsSpecId` int(10) unsigned NOT NULL COMMENT '商品规格ID',
  `goodsNum` int(10) unsigned NOT NULL COMMENT '申请售后的商品数量',
  `createTime` datetime DEFAULT current_timestamp() COMMENT '创建时间',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '有效状态 1:有效  -1:删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_settlements`;
CREATE TABLE `wst_supplier_settlements` (
  `settlementId` int(11) NOT NULL AUTO_INCREMENT,
  `settlementNo` varchar(20) NOT NULL,
  `settlementType` tinyint(4) NOT NULL DEFAULT 0,
  `supplierId` int(11) NOT NULL,
  `settlementMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `commissionFee` decimal(11,2) NOT NULL DEFAULT 0.00,
  `backMoney` decimal(11,2) NOT NULL DEFAULT 0.00,
  `settlementStatus` tinyint(4) NOT NULL DEFAULT 0,
  `settlementTime` datetime DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`settlementId`),
  KEY `supplierId` (`supplierId`),
  KEY `settlementStatus` (`settlementStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_spec_items`;
CREATE TABLE `wst_supplier_spec_items` (
  `itemId` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL DEFAULT 0,
  `catId` int(11) NOT NULL DEFAULT 0,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `itemImg` varchar(150) NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`itemId`),
  KEY `goodsId` (`goodsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_spec_items_langs`;
CREATE TABLE `wst_supplier_spec_items_langs` (
  `itemId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL DEFAULT 0,
  `itemName` varchar(100) DEFAULT NULL,
  `itemDesc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`itemId`,`langId`,`goodsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_users`;
CREATE TABLE `wst_supplier_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierId` int(11) NOT NULL DEFAULT 0,
  `userId` int(11) NOT NULL DEFAULT 0,
  `roleId` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `privilegeMsgs` varchar(200) DEFAULT '',
  `privilegeMsgTypes` varchar(50) DEFAULT '',
  `privilegePhoneMsgs` varchar(200) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_wholesale_goods`;
CREATE TABLE `wst_supplier_wholesale_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsId` int(11) NOT NULL,
  `buyNum` int(11) DEFAULT 0,
  `rebate` decimal(11,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_suppliers`;
CREATE TABLE `wst_suppliers` (
  `supplierId` int(11) NOT NULL AUTO_INCREMENT,
  `supplierSn` varchar(20) CHARACTER SET utf8 DEFAULT '',
  `userId` int(11) NOT NULL,
  `areaIdPath` varchar(255) DEFAULT '',
  `areaId` int(11) DEFAULT 0,
  `supplierName` varchar(100) CHARACTER SET utf8 DEFAULT '',
  `supplierkeeper` varchar(50) CHARACTER SET utf8 DEFAULT '',
  `telephone` varchar(20) DEFAULT '',
  `supplierCompany` varchar(255) CHARACTER SET utf8 DEFAULT '',
  `supplierImg` varchar(150) CHARACTER SET utf8 DEFAULT '',
  `supplierTel` varchar(40) CHARACTER SET utf8 DEFAULT '',
  `supplierQQ` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `supplierWangWang` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `supplierAddress` varchar(255) CHARACTER SET utf8 DEFAULT '',
  `bankId` int(11) DEFAULT 0,
  `bankNo` varchar(20) DEFAULT '',
  `bankUserName` varchar(50) DEFAULT '',
  `isInvoice` tinyint(4) NOT NULL DEFAULT 0,
  `invoiceRemarks` varchar(255) DEFAULT NULL,
  `serviceStartTime` time NOT NULL DEFAULT '08:30:00',
  `serviceEndTime` time NOT NULL DEFAULT '22:30:00',
  `supplierAtive` tinyint(4) NOT NULL DEFAULT 1,
  `supplierStatus` tinyint(4) NOT NULL DEFAULT 1,
  `statusDesc` varchar(255) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` date DEFAULT NULL,
  `supplierMoney` decimal(11,2) DEFAULT 0.00,
  `lockMoney` decimal(11,2) DEFAULT 0.00,
  `noSettledOrderNum` int(11) DEFAULT 0,
  `noSettledOrderFee` decimal(11,2) DEFAULT 0.00,
  `paymentMoney` decimal(11,2) DEFAULT 0.00,
  `bankAreaId` int(11) DEFAULT 0,
  `bankAreaIdPath` varchar(100) DEFAULT NULL,
  `applyStatus` tinyint(4) DEFAULT 0,
  `applyDesc` varchar(255) DEFAULT NULL,
  `applyTime` datetime DEFAULT NULL,
  `applyStep` tinyint(4) DEFAULT 1,
  `rechargeMoney` decimal(11,2) DEFAULT 0.00 COMMENT '充值金额',
  `longitude` decimal(11,7) DEFAULT NULL,
  `latitude` decimal(11,7) DEFAULT NULL,
  `mapLevel` int(11) DEFAULT 15,
  `tradeId` int(4) DEFAULT 0 COMMENT '所属行业ID',
  `expireDate` date DEFAULT NULL COMMENT '到期日期',
  `isPay` tinyint(4) DEFAULT 0 COMMENT '是否支付年费，0否,1是',
  `payAnnualFee` decimal(11,2) DEFAULT 0.00 COMMENT '支付年费金额',
  `isRefund` int(11) DEFAULT 0 COMMENT '是否退款年费，0否，1是',
  `supplierNotice` varchar(300) DEFAULT NULL COMMENT '供货商公告',
  `areaCode` varchar(6) DEFAULT '' COMMENT '電話區號(telephone)',
  `telephoneAreaCode` varchar(6) DEFAULT '' COMMENT '電話區號(telephone)',
  `flowAreaId` int(11) DEFAULT 0 COMMENT '入驻地区Id',
  PRIMARY KEY (`supplierId`),
  KEY `shopStatus` (`supplierStatus`,`dataFlag`) USING BTREE,
  KEY `areaIdPath` (`areaIdPath`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_switchs`;
CREATE TABLE `wst_switchs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `homeURL` varchar(255) DEFAULT NULL,
  `mobileURL` varchar(255) DEFAULT NULL,
  `wechatURL` varchar(255) DEFAULT NULL,
  `urlMark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_sys_configs`;
CREATE TABLE `wst_sys_configs` (
  `configId` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `fieldName` varchar(50) DEFAULT NULL COMMENT '字段名称',
  `fieldCode` varchar(50) DEFAULT NULL,
  `fieldValue` text DEFAULT NULL,
  `fieldType` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`configId`)
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_template_msgs`;
CREATE TABLE `wst_template_msgs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tplType` tinyint(4) NOT NULL DEFAULT 0,
  `tplCode` varchar(50) NOT NULL,
  `tplExternaId` varchar(255) DEFAULT NULL,
  `tplContent` varchar(255) NOT NULL,
  `isEnbale` tinyint(4) DEFAULT 1,
  `tplDesc` text DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `status` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `tplType` (`tplType`),
  KEY `tplCode` (`tplCode`)
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_third_users`;
CREATE TABLE `wst_third_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `thirdCode` varchar(20) DEFAULT NULL,
  `thirdOpenId` varchar(100) DEFAULT NULL,
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_trades`;
CREATE TABLE `wst_trades` (
  `tradeId` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL,
  `isShow` tinyint(4) NOT NULL DEFAULT 1,
  `tradeSort` int(11) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `tradeImg` varchar(150) DEFAULT NULL,
  `tradeFee` decimal(11,2) DEFAULT 0.00 COMMENT '类目费用',
  PRIMARY KEY (`tradeId`),
  KEY `parentId` (`parentId`,`isShow`,`dataFlag`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=365 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_trades_langs`;
CREATE TABLE `wst_trades_langs` (
  `tradeId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `tradeName` varchar(100) DEFAULT NULL,
  `subTitle` varchar(150) DEFAULT NULL,
  `simpleName` varchar(100) DEFAULT NULL,
  `seoTitle` varchar(200) DEFAULT NULL,
  `seoKeywords` varchar(200) DEFAULT NULL,
  `seoDes` varchar(600) DEFAULT NULL,
  PRIMARY KEY (`tradeId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_user_address`;
CREATE TABLE `wst_user_address` (
  `addressId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `userName` varchar(50) NOT NULL,
  `userPhone` varchar(20) DEFAULT NULL,
  `areaIdPath` varchar(255) NOT NULL DEFAULT '0',
  `areaId` int(11) NOT NULL DEFAULT 0,
  `userAddress` varchar(255) NOT NULL,
  `isDefault` tinyint(4) NOT NULL DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `areaCode` varchar(6) DEFAULT '' COMMENT '電話區號',
  PRIMARY KEY (`addressId`),
  KEY `userId` (`userId`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_user_ranks`;
CREATE TABLE `wst_user_ranks` (
  `rankId` int(11) NOT NULL AUTO_INCREMENT,
  `startScore` int(11) NOT NULL DEFAULT 0,
  `endScore` int(11) NOT NULL DEFAULT 0,
  `userrankImg` varchar(150) DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`rankId`),
  KEY `startScore` (`startScore`,`endScore`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_user_ranks_langs`;
CREATE TABLE `wst_user_ranks_langs` (
  `rankId` int(11) NOT NULL AUTO_INCREMENT,
  `langId` int(11) NOT NULL,
  `rankName` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`rankId`,`langId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_user_scores`;
CREATE TABLE `wst_user_scores` (
  `scoreId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT 0,
  `score` int(11) NOT NULL DEFAULT 0,
  `dataSrc` int(11) NOT NULL DEFAULT 0,
  `dataId` int(11) NOT NULL DEFAULT 0,
  `dataRemarks` text DEFAULT NULL,
  `scoreType` int(11) NOT NULL DEFAULT 0,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`scoreId`),
  KEY `userId` (`userId`),
  KEY `userId_2` (`userId`,`dataSrc`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_users`;
CREATE TABLE `wst_users` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `loginName` varchar(20) NOT NULL,
  `loginSecret` int(11) NOT NULL,
  `loginPwd` varchar(50) NOT NULL,
  `userType` tinyint(4) NOT NULL DEFAULT 0,
  `userSex` tinyint(4) DEFAULT 0,
  `userName` varchar(100) DEFAULT NULL,
  `trueName` varchar(100) DEFAULT NULL,
  `brithday` date DEFAULT NULL,
  `userPhoto` varchar(200) DEFAULT NULL,
  `userQQ` varchar(20) DEFAULT NULL,
  `userPhone` char(11) DEFAULT '',
  `userEmail` varchar(50) DEFAULT '',
  `userScore` int(11) DEFAULT 0,
  `userTotalScore` int(11) DEFAULT 0,
  `lastIP` varchar(16) DEFAULT NULL,
  `lastTime` datetime DEFAULT NULL,
  `userFrom` tinyint(4) DEFAULT 0,
  `userMoney` decimal(11,2) DEFAULT 0.00,
  `lockMoney` decimal(11,2) DEFAULT 0.00,
  `userStatus` tinyint(4) NOT NULL DEFAULT 1,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `payPwd` varchar(100) DEFAULT NULL,
  `rechargeMoney` decimal(11,2) DEFAULT 0.00 COMMENT '充值金额',
  `isInform` tinyint(4) NOT NULL DEFAULT 1,
  `wxOpenId` char(100) DEFAULT NULL,
  `wxUnionId` char(100) DEFAULT NULL,
  `areaCode` varchar(6) DEFAULT '' COMMENT '電話區號',
  `nftWalletAddress` varchar(150) DEFAULT '',
  `isMallStar` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否為MallStar',
  `invitationCode` varchar(50) DEFAULT '' COMMENT '邀请码',
  `distributMoney` decimal(11,2) DEFAULT 0.00 COMMENT '累计佣金',
  `iSunOneUser` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否為iSunOne用戶',
  `cancelRemarks` varchar(100) DEFAULT '',
  `applyCancelTime` datetime DEFAULT NULL,
  PRIMARY KEY (`userId`),
  KEY `userStatus` (`userStatus`,`dataFlag`),
  KEY `loginName` (`loginName`),
  KEY `userPhone` (`userPhone`),
  KEY `userEmail` (`userEmail`),
  KEY `userType` (`userType`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=354 DEFAULT CHARSET=utf8mb4;



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
