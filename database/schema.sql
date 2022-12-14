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
  `addonId` int(10) NOT NULL AUTO_INCREMENT COMMENT '??????',
  `name` varchar(40) NOT NULL COMMENT '??????????????????',
  `title` varchar(20) NOT NULL DEFAULT '' COMMENT '?????????',
  `description` text DEFAULT NULL COMMENT '????????????',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '??????',
  `config` text DEFAULT NULL COMMENT '??????',
  `author` varchar(40) DEFAULT '' COMMENT '??????',
  `version` varchar(20) DEFAULT '' COMMENT '?????????',
  `createTime` datetime NOT NULL COMMENT '????????????',
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
  `screenName` varchar(50) NOT NULL COMMENT '????????????',
  `explain` text DEFAULT NULL COMMENT '????????????',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COMMENT='App?????????????????????';

DROP TABLE IF EXISTS `wst_app_session`;
CREATE TABLE `wst_app_session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `tokenId` varchar(32) DEFAULT NULL,
  `startTime` datetime DEFAULT NULL,
  `deviceId` varchar(50) DEFAULT NULL,
  `platform` tinyint(4) NOT NULL DEFAULT 3 COMMENT '???????????? 3:android 4:ios',
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
  `solve` int(10) unsigned DEFAULT 0 COMMENT '?????????',
  `unsolve` int(10) unsigned DEFAULT 0 COMMENT '????????????',
  `coverImg` varchar(150) DEFAULT NULL,
  `visitorNum` int(10) unsigned DEFAULT 0 COMMENT '???????????????',
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
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????',
  `shopId` int(11) NOT NULL COMMENT '??????id',
  `keyword` varchar(50) NOT NULL COMMENT '??????????????????',
  `replyContent` text DEFAULT NULL COMMENT '????????????',
  `createTime` datetime DEFAULT NULL COMMENT '??????????????????',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '????????????',
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_banks`;
CREATE TABLE `wst_banks` (
  `bankId` int(11) NOT NULL AUTO_INCREMENT,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `createTime` datetime NOT NULL,
  `bankImg` varchar(150) DEFAULT NULL COMMENT '????????????',
  `bankCode` varchar(100) DEFAULT NULL COMMENT '????????????',
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
  `applyStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '???????????? 0:????????? 1:????????? -1:?????????',
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
  `sendData` text DEFAULT NULL COMMENT '????????????',
  `returnData` text DEFAULT NULL COMMENT '????????????',
  `returnMsg` varchar(300) DEFAULT NULL COMMENT '????????????',
  `payTime` datetime DEFAULT NULL COMMENT '????????????',
  `payNo` varchar(300) DEFAULT NULL COMMENT '????????????',
  `accTargetId` int(11) DEFAULT 0 COMMENT '??????ID',
  `payFee` int(11) DEFAULT 0 COMMENT '??????????????????',
  `incNo` int(11) DEFAULT 0 COMMENT '??????????????????????????????????????????????????????',
  `queryData` text DEFAULT NULL COMMENT '????????????????????????????????????????????????',
  `queryReturnData` text DEFAULT NULL COMMENT '?????????????????????????????????????????????????????????',
  `cashType` int(11) DEFAULT 0 COMMENT '1:??????????????????;2:??????????????????',
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
  `combineId` int(11) NOT NULL COMMENT '??????????????????ID',
  `goodsType` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1:?????????  0???????????????',
  `goodsId` int(11) NOT NULL COMMENT '??????ID',
  `reduceMoney` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '?????????',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_combinations`;
CREATE TABLE `wst_combinations` (
  `combineId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL COMMENT '??????ID',
  `combineImg` varchar(150) NOT NULL COMMENT '????????????',
  `combineType` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0???????????????  1???????????????',
  `combineStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1:????????? 0?????????',
  `isAdvance` tinyint(4) NOT NULL DEFAULT 0 COMMENT '??????????????????   0?????? 1??????',
  `advanceDay` int(11) NOT NULL DEFAULT 0 COMMENT '????????????',
  `startTime` datetime NOT NULL COMMENT '????????????',
  `endTime` datetime NOT NULL COMMENT '????????????',
  `isFreeShipping` tinyint(4) DEFAULT 0 COMMENT '0????????? 1:?????????',
  `combineOrder` int(11) DEFAULT 0,
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1:?????? -1:??????',
  `createTime` datetime NOT NULL COMMENT '????????????',
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
  `couponType` tinyint(4) DEFAULT 2 COMMENT '????????????????????? 1:???????????? 2:????????????',
  PRIMARY KEY (`couponId`),
  KEY `shopId` (`shopId`,`dataFlag`),
  KEY `startDate` (`startDate`,`endDate`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_crons`;
CREATE TABLE `wst_crons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cronName` varchar(100) NOT NULL,
  `cronCode` varchar(50) DEFAULT NULL COMMENT '??????????????????',
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
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '??????????????????1:?????? -1:??????',
  `catCode` varchar(255) NOT NULL COMMENT '??????????????????',
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
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '??????????????????1:?????? -1:??????',
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
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????id',
  `dialogId` int(10) unsigned NOT NULL COMMENT '??????id',
  `type` varchar(10) DEFAULT 'chat' COMMENT 'chat ??????  message??????',
  `content` text DEFAULT NULL COMMENT '????????????(????????????)?????? serialize(array("content"=>"????????????","from"=>"??????id","serviceId"=>"??????id"))',
  `createTime` datetime NOT NULL COMMENT '??????????????????',
  `isRead` tinyint(4) NOT NULL DEFAULT 0 COMMENT '?????????????????? 1:?????? 0:??????',
  PRIMARY KEY (`id`),
  KEY `dialogId` (`dialogId`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_dialogs`;
CREATE TABLE `wst_dialogs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????id',
  `userId` int(10) unsigned NOT NULL COMMENT '??????id',
  `serviceId` varchar(50) NOT NULL COMMENT '??????id',
  `shopId` int(10) unsigned NOT NULL COMMENT '??????id',
  `createTime` datetime NOT NULL COMMENT '??????????????????',
  `userDel` tinyint(4) NOT NULL DEFAULT 0 COMMENT '???????????????????????????0:????????? 1:?????????',
  `shopDel` tinyint(4) NOT NULL DEFAULT 0 COMMENT '???????????????????????????0:????????? 1:?????????',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `serviceId` (`serviceId`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_disable_keywords`;
CREATE TABLE `wst_disable_keywords` (
  `keywords` varchar(255) NOT NULL COMMENT '???????????????,??????????????????,????????????',
  `createTime` datetime DEFAULT NULL COMMENT '??????????????????',
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
  `feedbackId` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `feedbackType` int(4) DEFAULT 0 COMMENT '????????????',
  `feedbackContent` text DEFAULT NULL COMMENT '????????????',
  `userId` int(11) DEFAULT NULL COMMENT '?????????ID',
  `contactInfo` varchar(100) DEFAULT NULL COMMENT '???????????? ??????/qq/??????',
  `createTime` datetime DEFAULT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '1:??????  -1:??????',
  `feedbackStatus` tinyint(4) DEFAULT 0 COMMENT '???????????? 0:????????? 1:?????????',
  `staffId` int(11) DEFAULT 0 COMMENT '?????????',
  `handleTime` datetime DEFAULT NULL COMMENT '????????????',
  `handleContent` text DEFAULT NULL COMMENT '????????????',
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
  `shippingFeeType` tinyint(4) DEFAULT 1 COMMENT '???????????? 1:?????? 2:?????? 3:??????',
  `goodsWeight` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `goodsVolume` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `shopExpressId` int(11) DEFAULT 0 COMMENT '??????????????????ID',
  `collectNum` int(11) DEFAULT 0,
  `isNft` tinyint(4) DEFAULT 0,
  `nftJson` text DEFAULT NULL,
  `detailGoodsImg` varchar(150) DEFAULT '' COMMENT '???NFT??????????????????',
  `author` varchar(50) DEFAULT '' COMMENT '????????????',
  `showPrice` varchar(50) DEFAULT '1' COMMENT '??????????????????????????????1?????? 0?????????',
  `canSaleStatus` tinyint(4) DEFAULT 1,
  `isDistribut` tinyint(4) NOT NULL DEFAULT 0 COMMENT '?????????????????????',
  `commission` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
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
  `orderGoodsId` int(10) unsigned NOT NULL COMMENT '???????????????Id',
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
  `catListTheme` varchar(200) NOT NULL DEFAULT 'goods_list' COMMENT '????????????????????????',
  `detailTheme` varchar(200) NOT NULL DEFAULT 'goods_detail' COMMENT '??????????????????',
  `mobileCatListTheme` varchar(200) NOT NULL DEFAULT 'goods_list' COMMENT '?????????????????????????????????',
  `mobileDetailTheme` varchar(200) NOT NULL DEFAULT 'goods_detail' COMMENT '???????????????????????????',
  `wechatCatListTheme` varchar(200) NOT NULL DEFAULT 'goods_list' COMMENT '?????????????????????????????????',
  `wechatDetailTheme` varchar(200) NOT NULL DEFAULT 'goods_detail' COMMENT '???????????????????????????',
  `showWay` tinyint(4) DEFAULT 0 COMMENT '???????????? 0:?????? 1:??????',
  `catColor` varchar(50) DEFAULT '#F23CCE',
  `isNft` tinyint(4) DEFAULT 0,
  `isMarket` tinyint(11) DEFAULT 0 COMMENT '??????????????????',
  `marketId` int(11) DEFAULT 0 COMMENT '??????ID',
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
  `goodsId` int(10) unsigned NOT NULL COMMENT '??????id',
  `userId` int(10) unsigned DEFAULT NULL COMMENT '??????id',
  `consultType` tinyint(3) unsigned DEFAULT NULL COMMENT '????????????',
  `consultContent` varchar(500) DEFAULT NULL COMMENT '????????????',
  `createTime` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '????????????',
  `reply` varchar(255) NOT NULL DEFAULT '' COMMENT '????????????',
  `replyTime` datetime DEFAULT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '??????????????????',
  `isShow` tinyint(4) DEFAULT 1 COMMENT '??????????????????',
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
  `specWeight` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `specVolume` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `costPrice` decimal(11,2) DEFAULT 0.00 COMMENT '?????????',
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
  `limitNum` int(11) NOT NULL DEFAULT 0 COMMENT '??????????????????',
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
  `isMarket` tinyint(11) DEFAULT 0 COMMENT '??????????????????',
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
  `hookId` int(10) NOT NULL AUTO_INCREMENT COMMENT '??????',
  `name` varchar(40) NOT NULL DEFAULT '' COMMENT '????????????',
  `hookRemarks` text NOT NULL COMMENT '??????',
  `hookType` tinyint(1) NOT NULL DEFAULT 1 COMMENT '??????',
  `updateTime` datetime NOT NULL COMMENT '????????????',
  `addons` text DEFAULT NULL,
  PRIMARY KEY (`hookId`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_im_chat_statistics`;
CREATE TABLE `wst_im_chat_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????id',
  `userId` int(10) unsigned NOT NULL COMMENT '??????id',
  `shopId` int(10) unsigned NOT NULL COMMENT '??????id',
  `startTime` datetime NOT NULL COMMENT '????????????',
  `ip` varchar(255) NOT NULL COMMENT '??????ip',
  `platform` tinyint(4) NOT NULL COMMENT '????????????,1:pc 2:??????|?????? 3:android 4:ios 5:?????????',
  `stayTime` int(10) unsigned NOT NULL COMMENT '????????????,??????:???',
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
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????',
  `invoiceHead` varchar(255) NOT NULL COMMENT '????????????',
  `invoiceCode` varchar(255) NOT NULL DEFAULT '' COMMENT '??????????????????',
  `userId` int(10) unsigned NOT NULL COMMENT '??????id',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '??????????????????',
  `createTime` datetime NOT NULL COMMENT '??????????????????',
  `invoiceType` tinyint(4) DEFAULT 0 COMMENT '0:?????? 1:???????????????',
  `invoiceAddr` varchar(300) DEFAULT NULL COMMENT '??????????????????????????????invoiceType???1?????????????????????',
  `invoicePhoneNumber` varchar(100) DEFAULT NULL COMMENT '??????????????????????????????invoiceType???1?????????????????????',
  `invoiceBankName` varchar(100) DEFAULT NULL COMMENT '??????????????????????????????invoiceType???1?????????????????????',
  `invoiceBankNo` varchar(100) DEFAULT NULL COMMENT '??????????????????????????????invoiceType???1?????????????????????',
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
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `word` varchar(50) DEFAULT NULL COMMENT '???????????????',
  `createTime` datetime DEFAULT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '1:??????  -1:??????',
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
  `targetType` tinyint(4) DEFAULT 0 COMMENT '???????????????0????????? 1????????? 3???????????????',
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
  `searchWord` varchar(255) DEFAULT NULL COMMENT '???????????????',
  `searchCnt` int(11) DEFAULT 0 COMMENT '????????????',
  `lastTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=360 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_services`;
CREATE TABLE `wst_log_services` (
  `logId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '??????id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '??????id',
  `logJson` text DEFAULT NULL,
  `logTargetId` int(10) unsigned NOT NULL COMMENT '?????????Id,????????????????????????????????????ID',
  `logType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '???????????????,0?????????  1?????????',
  `logTime` datetime NOT NULL COMMENT '????????????',
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
  `loginSrc` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0:??????  1:webapp  2:App',
  `loginRemark` varchar(30) DEFAULT NULL COMMENT '??????????????????',
  PRIMARY KEY (`loginId`),
  KEY `loginTime` (`loginTime`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=1427 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_mall_star_applys`;
CREATE TABLE `wst_mall_star_applys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL COMMENT '??????id',
  `email` varchar(255) NOT NULL COMMENT '????????????',
  `contact` varchar(255) NOT NULL COMMENT '????????????????????????',
  `content` text NOT NULL COMMENT '??????????????????',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '???????????? -1:???????????? 0:????????? 1:????????????',
  `handleRemark` text DEFAULT NULL COMMENT '??????????????????',
  `applyTime` datetime NOT NULL COMMENT '??????????????????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1,
  `nickname` varchar(255) NOT NULL COMMENT '??????',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='MallStar?????????';

DROP TABLE IF EXISTS `wst_mall_star_moneys`;
CREATE TABLE `wst_mall_star_moneys` (
  `moneyId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) DEFAULT 0 COMMENT '??????id',
  `orderId` int(11) DEFAULT 0 COMMENT '??????id',
  `userId` int(11) DEFAULT 0 COMMENT '??????id',
  `buyerId` int(11) DEFAULT NULL COMMENT '?????????id',
  `remark` varchar(200) DEFAULT NULL COMMENT '??????',
  `distributType` tinyint(4) DEFAULT 0 COMMENT '????????????',
  `orderGoodsId` int(11) DEFAULT 0 COMMENT '????????????id',
  `money` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `distributMoney` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  `createTime` datetime DEFAULT NULL,
  `moneyType` tinyint(4) DEFAULT 0 COMMENT '1?????????????????? 2??????????????????',
  `goodsNum` int(11) DEFAULT 0 COMMENT '??????????????????',
  `moneyStatus` tinyint(4) DEFAULT 0 COMMENT '???????????????1???????????? 0????????????',
  `lockDays` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '??????????????????',
  PRIMARY KEY (`moneyId`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='MallStar?????????';

DROP TABLE IF EXISTS `wst_mall_star_users`;
CREATE TABLE `wst_mall_star_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) DEFAULT 0 COMMENT '??????MallStar??????Id',
  `userId` int(11) DEFAULT 0 COMMENT '??????id',
  `isEnable` tinyint(4) DEFAULT 1 COMMENT '???????????????????????? 1:??? 0:???',
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='MallStar?????????';

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='???????????????';

DROP TABLE IF EXISTS `wst_market_navs_langs`;
CREATE TABLE `wst_market_navs_langs` (
  `navId` int(11) NOT NULL,
  `langId` int(11) NOT NULL DEFAULT 0,
  `marketTitle` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`navId`,`langId`),
  KEY `langId` (`langId`,`marketTitle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='?????????????????????';

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
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COMMENT='?????????????????????';

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
  `isExpress` tinyint(4) DEFAULT 0 COMMENT '1:????????????????????  0:????????????????????????',
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
  `couponVal` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '?????????????????????',
  `rewardVal` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '??????????????????',
  `useScoreVal` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '?????????????????????',
  `scoreMoney` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '??????????????????',
  `getScoreVal` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '?????????????????????????????????',
  `orderGoodscommission` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  `getScoreMoney` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '????????????????????????????????????',
  `nftTxId` varchar(150) DEFAULT '',
  `nftJson` text DEFAULT NULL,
  `commission` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  `marketCommissionRate` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  `orderGoodsMarketcommission` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  `realOrderGoodscommission` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????????????????????????????',
  `realOrderGoodsMarketcommission` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????????????????????????????',
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
  `serviceId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '?????????id',
  `isServiceRefund` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '????????????????????????0???????????? 1????????????',
  `refundProcessStatus` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_order_services`;
CREATE TABLE `wst_order_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????',
  `orderId` int(10) unsigned NOT NULL COMMENT '??????id',
  `goodsServiceType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '0??????????????? 1????????? 2?????????',
  `serviceType` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '???????????????,?????????????????????????????????',
  `serviceRemark` varchar(600) DEFAULT NULL COMMENT '???????????????',
  `serviceAnnex` text DEFAULT NULL COMMENT '??????',
  `refundScore` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '??????????????????????????????????????????????????????',
  `useScoreMoney` decimal(11,2) unsigned NOT NULL DEFAULT 0.00 COMMENT '??????????????????????????????',
  `getScoreMoney` decimal(11,2) unsigned NOT NULL DEFAULT 0.00 COMMENT '??????????????????????????????',
  `refundMoney` decimal(11,2) DEFAULT NULL COMMENT '?????????????????????',
  `refundableMoney` decimal(11,2) DEFAULT NULL COMMENT '????????????????????????',
  `isShopAgree` tinyint(4) DEFAULT 0 COMMENT '1:?????? 0????????????',
  `disagreeRemark` varchar(600) DEFAULT NULL COMMENT '?????????????????????',
  `userAddressId` int(11) unsigned DEFAULT 0 COMMENT '??????????????????id',
  `areaId` int(11) unsigned DEFAULT 0 COMMENT '??????id',
  `areaIdPath` varchar(255) DEFAULT NULL COMMENT '??????ID???',
  `userName` varchar(200) DEFAULT '' COMMENT '???????????????',
  `userAddress` varchar(200) DEFAULT '' COMMENT '????????????????????????',
  `userPhone` varchar(200) DEFAULT '' COMMENT '??????????????????',
  `shopAreaId` int(11) unsigned DEFAULT 0 COMMENT '??????????????????ID',
  `shopAreaIdPath` varchar(255) DEFAULT NULL COMMENT '??????????????????ID???',
  `shopName` varchar(200) DEFAULT '' COMMENT '???????????????',
  `shopAddress` varchar(200) DEFAULT '' COMMENT '????????????????????????',
  `shopPhone` varchar(200) DEFAULT '' COMMENT '??????????????????',
  `isUserSendGoods` tinyint(4) DEFAULT 0 COMMENT '0???????????? 1????????????',
  `expressType` tinyint(4) DEFAULT 0 COMMENT '0???????????????  1?????????',
  `expressId` int(11) unsigned DEFAULT 0 COMMENT '????????????ID',
  `expressNo` varchar(200) DEFAULT '' COMMENT '??????????????????',
  `isShopAccept` tinyint(4) DEFAULT 0 COMMENT '????????????????????? -1?????????  0????????????  1????????????',
  `shopRejectType` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '??????????????????,?????????????????????????????????',
  `shopRejectOther` varchar(600) DEFAULT NULL COMMENT '??????????????????,???????????????????????????????????????',
  `shopRejectImg` varchar(150) DEFAULT NULL COMMENT '????????????????????????',
  `isShopSend` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '?????????????????? 0???????????? 1????????????',
  `shopExpressType` tinyint(4) DEFAULT 0 COMMENT '0???????????????  1?????????',
  `shopExpressId` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '????????????ID',
  `shopExpressNo` varchar(200) DEFAULT '' COMMENT '??????????????????',
  `isUserAccept` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '-1????????? 0????????????  1????????????',
  `userRejectType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '??????????????????,?????????????????????????????????',
  `userRejectOther` varchar(600) DEFAULT NULL COMMENT '??????????????????,???????????????????????????????????????',
  `createTime` datetime NOT NULL COMMENT '????????????',
  `isClose` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '???????????? 0????????????  1:?????????',
  `serviceStatus` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '???????????????0??????????????????  1????????????????????? 2????????????????????? 3?????????????????????  4?????????????????????  5???????????????/??????  6???????????????????????? 7???????????????????????????????????????',
  `shopAcceptExpireTime` datetime DEFAULT NULL COMMENT '??????????????????',
  `userSendExpireTime` datetime DEFAULT NULL COMMENT '??????????????????',
  `shopReceiveExpireTime` datetime DEFAULT NULL COMMENT '??????????????????',
  `getScoreVal` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '?????????????????????,???????????????????????????',
  `userReceiveExpireTime` datetime DEFAULT NULL COMMENT '??????????????????',
  `shopSendExpireTime` datetime DEFAULT NULL COMMENT '??????????????????',
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
  `noticeDeliver` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '???????????? 0:????????? 1:?????????',
  `invoiceJson` text DEFAULT NULL COMMENT '????????????',
  `lockCashMoney` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  `payTime` datetime DEFAULT NULL,
  `isBatch` tinyint(4) DEFAULT 0,
  `totalPayFee` int(11) DEFAULT 0,
  `isMakeInvoice` tinyint(4) DEFAULT 0 COMMENT '??????????????? 1:??? 0:???',
  `afterSaleEndTime` datetime DEFAULT NULL COMMENT '??????????????????,??????????????????+?????????????????????????????????',
  `getScoreVal` decimal(11,2) NOT NULL DEFAULT 0.00 COMMENT '????????????????????????????????????',
  `refundedPayMoney` decimal(11,2) DEFAULT 0.00 COMMENT '?????????????????????',
  `refundedScore` int(10) unsigned DEFAULT 0 COMMENT '???????????????',
  `refundedScoreMoney` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????????????????',
  `refundedGetScore` int(10) unsigned DEFAULT 0 COMMENT '????????? ???????????????????????????(??????????????????????????????????????????),???????????????????????????',
  `refundedGetScoreMoney` decimal(11,2) DEFAULT 0.00 COMMENT '????????? ?????????????????????????????????????????????',
  `storeType` int(11) DEFAULT 0 COMMENT '??????????????? 1:??????????????? 2?????????',
  `storeId` int(11) DEFAULT 0 COMMENT '?????????ID',
  `verificationCode` varchar(20) DEFAULT '0' COMMENT '?????????',
  `verificationTime` datetime DEFAULT NULL COMMENT '????????????',
  `userCouponId` int(11) DEFAULT 0 COMMENT '????????????????????????ID',
  `userCouponJson` text DEFAULT NULL COMMENT '???????????????',
  `areaCode` varchar(6) DEFAULT '' COMMENT '????????????',
  `outTradeNo` varchar(50) DEFAULT '',
  `isNft` tinyint(4) DEFAULT 0,
  `nftWalletAddress` varchar(150) DEFAULT '',
  `topMallStarRate` tinyint(4) DEFAULT 0 COMMENT '??????mall star????????????',
  `secondMallStarRate` tinyint(4) DEFAULT 0 COMMENT '??????mall star????????????',
  `totalCommission` decimal(11,2) DEFAULT 0.00 COMMENT '?????????',
  `dmoneyIsSettlement` tinyint(4) DEFAULT 0 COMMENT '????????????????????? 1?????? 0??????',
  `distributType` tinyint(4) DEFAULT 0,
  `isMarket` tinyint(11) DEFAULT 0 COMMENT '?????????????????????????????????',
  `marketId` int(11) DEFAULT 0 COMMENT '??????ID',
  `marketCommissionFee` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
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
  `moneyType` int(11) DEFAULT 0 COMMENT '1:?????? 2?????????',
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
  `userId` int(11) DEFAULT 0 COMMENT '??????ID',
  `presaleId` int(11) DEFAULT 0 COMMENT '??????ID',
  `shopId` int(11) DEFAULT 0 COMMENT '??????ID',
  `orderId` int(11) DEFAULT 0 COMMENT '??????ID',
  `orderNo` varchar(20) DEFAULT NULL COMMENT '?????????',
  `goodsId` int(11) DEFAULT 0 COMMENT '??????ID',
  `goodsName` varchar(500) DEFAULT '' COMMENT '????????????',
  `goodsImg` varchar(150) DEFAULT '' COMMENT '????????????',
  `goodsSpecId` int(11) DEFAULT 0 COMMENT '????????????ID',
  `goodsSpecNames` varchar(500) DEFAULT '' COMMENT '??????????????????',
  `goodsNum` int(11) DEFAULT 0 COMMENT '??????????????????',
  `presaleStatus` tinyint(4) DEFAULT 0 COMMENT '???????????????0??????????????? 1??????????????? 2????????????????????????-1??????????????????',
  `failType` tinyint(4) DEFAULT 0 COMMENT '????????????????????? 1????????????????????? 2???????????????????????????',
  `areaId` int(11) DEFAULT 0 COMMENT '??????????????????Id',
  `areaIdPath` varchar(100) DEFAULT NULL COMMENT '??????Id??????',
  `orderType` tinyint(4) DEFAULT 0 COMMENT '???????????????0:???????????? 1:??????????????????',
  `goodsMoney` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????',
  `totalMoney` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????',
  `realTotalMoney` decimal(11,2) DEFAULT 0.00 COMMENT '?????????????????????',
  `deliverType` tinyint(4) DEFAULT 0 COMMENT '???????????????0:???????????? 1:?????????',
  `deliverMoney` decimal(11,2) DEFAULT NULL COMMENT '??????',
  `payType` tinyint(4) DEFAULT 0 COMMENT '????????????(0:???????????? 1:????????????)',
  `payFrom` varchar(20) DEFAULT NULL COMMENT '????????????',
  `isPay` tinyint(4) DEFAULT 0 COMMENT '??????????????????1?????? 0?????????',
  `payTime` datetime DEFAULT NULL COMMENT '????????????',
  `userName` varchar(20) DEFAULT NULL COMMENT '???????????????',
  `userAddress` varchar(255) NOT NULL COMMENT '???????????????',
  `userPhone` varchar(20) DEFAULT NULL COMMENT '???????????????',
  `orderScore` int(11) NOT NULL DEFAULT 0 COMMENT '????????????',
  `isInvoice` tinyint(4) NOT NULL DEFAULT 0 COMMENT '??????????????????',
  `invoiceClient` varchar(255) DEFAULT NULL COMMENT '????????????',
  `invoiceJson` text DEFAULT NULL COMMENT '????????????',
  `orderRemarks` varchar(255) DEFAULT NULL COMMENT '????????????',
  `orderSrc` tinyint(4) NOT NULL DEFAULT 0 COMMENT '????????????',
  `payRand` int(11) DEFAULT 1 COMMENT '???????????????',
  `orderunique` varchar(50) DEFAULT NULL COMMENT '???????????????',
  `useScore` int(11) DEFAULT 0 COMMENT '????????????',
  `scoreMoney` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `commissionFee` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `commissionRate` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `depositMoney` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `surplusMoney` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `startPayTime` datetime DEFAULT NULL COMMENT '????????????????????????',
  `endPayTime` datetime DEFAULT NULL COMMENT '????????????????????????',
  `tradeNo` varchar(100) DEFAULT NULL COMMENT '????????????????????????',
  `createTime` datetime DEFAULT NULL COMMENT '????????????',
  `extraJson` text DEFAULT NULL COMMENT '??????????????????',
  `lockCashMoney` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????????????????',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '???????????????1????????? -1?????????',
  `storeType` tinyint(4) DEFAULT 0 COMMENT '????????????',
  `storeId` int(11) DEFAULT 0 COMMENT '??????ID',
  `areaCode` varchar(6) DEFAULT '' COMMENT '????????????',
  `outTradeNo` varchar(50) DEFAULT '',
  `isMarket` tinyint(11) DEFAULT 0 COMMENT '?????????????????????????????????',
  `marketId` int(11) DEFAULT 0 COMMENT '??????ID',
  `marketCommissionFee` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  `marketCommissionRate` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_presales`;
CREATE TABLE `wst_presales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL COMMENT '??????ID',
  `goodsImg` varchar(150) NOT NULL COMMENT '??????????????????',
  `limitNum` int(11) DEFAULT 0 COMMENT '??????????????????',
  `reduceMoney` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '???????????????',
  `goodsNum` int(11) NOT NULL DEFAULT 0 COMMENT '????????????',
  `orderNum` int(11) DEFAULT 0 COMMENT '???????????????',
  `saleType` tinyint(4) NOT NULL DEFAULT 0 COMMENT '????????????  0:????????? 1????????????',
  `depositType` tinyint(4) DEFAULT 0 COMMENT '???????????? 0:????????????  1:?????????',
  `depositMoney` decimal(10,2) DEFAULT 0.00 COMMENT '??????',
  `depositRate` int(11) DEFAULT 0 COMMENT '???????????????',
  `endPayDays` int(11) DEFAULT 1 COMMENT '????????????X???????????????',
  `startTime` datetime NOT NULL COMMENT '??????????????????',
  `endTime` datetime NOT NULL COMMENT '??????????????????',
  `deliverType` tinyint(11) NOT NULL DEFAULT 0 COMMENT '???????????? 0???????????????  1???????????????',
  `deliverDays` int(11) NOT NULL DEFAULT 1 COMMENT 'X????????????',
  `afterGoodsStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '?????????????????????  1???????????????  0?????????',
  `presaleStatus` tinyint(4) DEFAULT 0 COMMENT '?????????????????? 0????????????  1????????????  -1??????????????????',
  `auditRemark` varchar(255) DEFAULT NULL COMMENT '?????????????????????',
  `isSale` tinyint(4) DEFAULT 1,
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '????????????  1?????????  -1?????????',
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
  `resType` tinyint(4) DEFAULT 0 COMMENT '0:?????? 1:??????',
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
  `rewardTitle` varchar(255) NOT NULL COMMENT '????????????',
  `startDate` date NOT NULL COMMENT '????????????',
  `endDate` date NOT NULL COMMENT '????????????',
  `rewardType` tinyint(4) NOT NULL DEFAULT 0 COMMENT '????????????',
  `useObjects` tinyint(4) NOT NULL DEFAULT 0 COMMENT '????????????',
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
  `shopId` int(11) NOT NULL DEFAULT 0 COMMENT '??????Id',
  `seckillId` int(11) NOT NULL DEFAULT 0 COMMENT '????????????ID',
  `timeId` int(11) NOT NULL DEFAULT 0 COMMENT '????????????ID',
  `goodsId` int(11) NOT NULL DEFAULT 0 COMMENT '??????Id',
  `secPrice` decimal(11,2) DEFAULT 0.00 COMMENT '?????????',
  `secNum` int(11) NOT NULL DEFAULT 0 COMMENT '????????????????????????',
  `secLimit` tinyint(3) NOT NULL DEFAULT 1 COMMENT '??????????????????',
  `saleNum` int(11) NOT NULL DEFAULT 0 COMMENT '?????????????????????',
  `hasBuyNum` int(11) NOT NULL DEFAULT 0 COMMENT '?????????????????????????????????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:?????? -1??????',
  `createTime` datetime NOT NULL COMMENT '????????????',
  PRIMARY KEY (`id`),
  KEY `seckillId` (`seckillId`),
  KEY `timeId` (`timeId`),
  KEY `goodsId` (`goodsId`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_seckill_time_intervals`;
CREATE TABLE `wst_seckill_time_intervals` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `startTime` time NOT NULL COMMENT '???????????????',
  `endTime` time NOT NULL COMMENT '???????????????',
  `dataFlag` tinyint(1) NOT NULL DEFAULT 1 COMMENT '???????????? 1:?????? -1??????',
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
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '??????????????????ID',
  `shopId` int(10) NOT NULL COMMENT '??????ID',
  `startDate` date NOT NULL COMMENT '????????????????????????',
  `endDate` date NOT NULL COMMENT '????????????????????????',
  `isSale` tinyint(1) NOT NULL DEFAULT 1 COMMENT '?????????',
  `createTime` datetime NOT NULL COMMENT '????????????',
  `seckillStatus` tinyint(1) NOT NULL DEFAULT 1 COMMENT '????????????',
  `illegalRemarks` varchar(500) DEFAULT NULL COMMENT '???????????????',
  `dataFlag` tinyint(1) NOT NULL DEFAULT 1 COMMENT '???????????? 1:?????? -1??????',
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
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `userId` int(10) unsigned NOT NULL COMMENT '??????id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '??????id,???0????????????????????????????????????',
  `shopId` int(10) unsigned NOT NULL COMMENT '??????id',
  `score` tinyint(4) unsigned NOT NULL COMMENT '??????1-5,1:???????????????,2:?????????,3:??????,4:??????,5:????????????',
  PRIMARY KEY (`id`),
  KEY `shopId` (`shopId`),
  KEY `serviceId` (`serviceId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_service_goods`;
CREATE TABLE `wst_service_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '??????id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '??????id',
  `goodsId` int(10) unsigned NOT NULL COMMENT '??????ID',
  `goodsSpecId` int(10) unsigned NOT NULL COMMENT '????????????ID',
  `goodsNum` int(10) unsigned NOT NULL COMMENT '???????????????????????????',
  `createTime` datetime NOT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:??????  -1:??????',
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
  `userId` int(11) NOT NULL COMMENT '??????ID',
  `linkPhone` varchar(20) NOT NULL COMMENT ' ??????????????????',
  `linkman` varchar(50) NOT NULL COMMENT '???????????????',
  `applyIntention` varchar(600) NOT NULL COMMENT '????????????',
  `shopName` varchar(255) DEFAULT NULL COMMENT '????????????',
  `handleReamrk` varchar(600) DEFAULT NULL,
  `applyStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '???????????? 0:?????????  1:?????????  -1:??????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:?????? -1:??????',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_bases`;
CREATE TABLE `wst_shop_bases` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `flowId` int(11) DEFAULT 0 COMMENT '??????ID',
  `fieldName` varchar(50) DEFAULT '' COMMENT '????????????',
  `dataType` varchar(10) DEFAULT '' COMMENT '????????????',
  `dataLength` int(11) DEFAULT 0 COMMENT '????????????',
  `fieldSort` tinyint(4) unsigned DEFAULT 0 COMMENT '????????????',
  `isRequire` tinyint(4) unsigned DEFAULT 0 COMMENT '???????????????0??????1???',
  `fieldType` varchar(10) DEFAULT '' COMMENT '????????????',
  `isRelevance` tinyint(4) unsigned DEFAULT 0 COMMENT '?????????????????????0??????1???',
  `fieldRelevance` varchar(50) DEFAULT '' COMMENT '????????????',
  `dateRelevance` varchar(100) DEFAULT '' COMMENT '??????????????????',
  `timeRelevance` varchar(100) DEFAULT '' COMMENT '??????????????????',
  `isShow` tinyint(4) unsigned DEFAULT 1 COMMENT '???????????????0?????????1??????',
  `isMap` tinyint(4) unsigned DEFAULT 0 COMMENT '?????????????????????0?????????1??????',
  `createTime` datetime NOT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:??????  -1:??????',
  `isDelete` tinyint(4) DEFAULT 1 COMMENT '?????????????????????0??????1???',
  `isShopsTable` tinyint(4) DEFAULT 0 COMMENT '?????????wst_shops??????0?????????1???',
  `fileNum` tinyint(4) DEFAULT NULL,
  `isPhone` tinyint(4) DEFAULT 0 COMMENT '???????????????',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_bases_langs`;
CREATE TABLE `wst_shop_bases_langs` (
  `baseId` int(11) NOT NULL COMMENT '??????ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '??????ID',
  `fieldTitle` varchar(255) DEFAULT NULL COMMENT '????????????',
  `fieldComment` varchar(500) DEFAULT NULL COMMENT '????????????',
  `fieldAttr` varchar(255) DEFAULT '' COMMENT '????????????',
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
  `shopStreetImg` varchar(150) DEFAULT '' COMMENT '???????????????',
  `shopMoveBanner` varchar(150) DEFAULT NULL,
  `shopHomeTheme` varchar(200) NOT NULL DEFAULT 'shop_home' COMMENT '????????????',
  `mobileShopHomeTheme` varchar(200) NOT NULL DEFAULT 'shop_home' COMMENT '?????????????????????',
  `wechatShopHomeTheme` varchar(200) NOT NULL DEFAULT 'shop_home' COMMENT '?????????????????????',
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
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
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
  `applyLinkTelAreaCode` varchar(6) DEFAULT '' COMMENT '????????????(applyLinkTel)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_extras_nzl`;
CREATE TABLE `wst_shop_extras_nzl` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
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
  `lockCashMoney` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????????????????',
  `outTradeNo` varchar(50) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_flow_areas`;
CREATE TABLE `wst_shop_flow_areas` (
  `areaId` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `tableExtraName` varchar(20) NOT NULL COMMENT '??????????????????',
  `sort` tinyint(4) DEFAULT 0,
  `createTime` datetime NOT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:??????  -1:??????',
  PRIMARY KEY (`areaId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_flow_areas_langs`;
CREATE TABLE `wst_shop_flow_areas_langs` (
  `areaId` int(11) NOT NULL COMMENT '????????????ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '??????ID',
  `areaName` varchar(100) DEFAULT '' COMMENT '??????????????????',
  PRIMARY KEY (`areaId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_flows`;
CREATE TABLE `wst_shop_flows` (
  `flowId` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `isShow` tinyint(4) DEFAULT 1 COMMENT '???????????????0?????????1??????',
  `sort` tinyint(4) DEFAULT 0 COMMENT '????????????',
  `isDelete` tinyint(4) DEFAULT 1 COMMENT '?????????????????????0??????1???',
  `createTime` datetime NOT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:??????  -1:??????',
  `areaId` int(11) DEFAULT 0 COMMENT '????????????ID',
  PRIMARY KEY (`flowId`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_flows_langs`;
CREATE TABLE `wst_shop_flows_langs` (
  `flowId` int(11) NOT NULL COMMENT '??????ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '??????ID',
  `flowName` varchar(100) DEFAULT '' COMMENT '????????????',
  PRIMARY KEY (`flowId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_shop_freight_template`;
CREATE TABLE `wst_shop_freight_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopExpressId` int(11) NOT NULL,
  `tempName` varchar(100) NOT NULL DEFAULT '',
  `tempType` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0????????? 1???????????????',
  `provinceIds` text DEFAULT NULL COMMENT '??????ID??????',
  `cityIds` text DEFAULT NULL COMMENT '??????ID??????',
  `buyNumStart` int(4) DEFAULT 0 COMMENT '??????(???)',
  `buyNumStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '????????????(???)',
  `buyNumContinue` int(4) DEFAULT 0 COMMENT '??????(???)',
  `buyNumContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '????????????(???)',
  `weightStart` decimal(11,2) DEFAULT 0.00 COMMENT '?????????Kg???',
  `weightStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `weightContinue` decimal(11,2) DEFAULT 0.00 COMMENT '?????????Kg???',
  `weightContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `volumeStart` decimal(11,2) DEFAULT 0.00 COMMENT '????????????(m??)',
  `volumeStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????(???)',
  `volumeContinue` decimal(11,2) DEFAULT 0.00 COMMENT '????????????(m??)',
  `volumeContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????(???)',
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
  `groupName` varchar(50) DEFAULT NULL COMMENT '??????????????????',
  `shopId` int(11) DEFAULT NULL COMMENT '??????ID',
  `groupOrder` int(11) DEFAULT 0 COMMENT '?????????',
  `minMoney` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `maxMoney` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
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
  `userId` int(11) DEFAULT NULL COMMENT '??????ID',
  `groupId` int(11) DEFAULT 0 COMMENT '????????????ID',
  `shopId` int(11) DEFAULT NULL COMMENT '??????ID',
  `totalOrderMoney` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????',
  `totalOrderNum` int(11) DEFAULT 0 COMMENT '????????????',
  `lastOrderTime` datetime DEFAULT NULL COMMENT '????????????????????????',
  `isOrder` tinyint(4) DEFAULT 0 COMMENT '0:?????????  1:?????????',
  `createTime` datetime DEFAULT NULL COMMENT '????????????',
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
  `shopId` int(11) NOT NULL COMMENT '??????id',
  `serviceId` varchar(50) NOT NULL COMMENT '??????id',
  `createTime` datetime DEFAULT NULL COMMENT '??????????????????',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '????????????',
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
  `isShow` tinyint(4) DEFAULT 1 COMMENT '1:??????  0:??????',
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
  `serviceId` varchar(50) NOT NULL DEFAULT '' COMMENT '??????id',
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
  `shopNotice` varchar(300) DEFAULT NULL COMMENT '????????????',
  `rechargeMoney` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `longitude` decimal(10,7) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `mapLevel` int(11) DEFAULT 16,
  `expireDate` date DEFAULT NULL COMMENT '????????????',
  `isPay` tinyint(4) DEFAULT 0 COMMENT '?????????????????????0???,1???',
  `payAnnualFee` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  `isRefund` tinyint(4) DEFAULT 0 COMMENT '?????????????????????0??????1???',
  `tradeId` int(11) DEFAULT 0 COMMENT '????????????ID',
  `areaCode` varchar(6) DEFAULT '' COMMENT '????????????(telephone)',
  `telephoneAreaCode` varchar(6) DEFAULT '' COMMENT '????????????(telephone)',
  `flowAreaId` int(11) DEFAULT 0 COMMENT '????????????Id',
  `isMarket` tinyint(11) DEFAULT 0 COMMENT '????????????',
  `marketId` int(11) DEFAULT 0 COMMENT '??????ID',
  `mallCommissionRate` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????????????????Mall???',
  `marketCommissionRate` decimal(11,2) DEFAULT 0.00 COMMENT '?????????????????????Mall????????????',
  `marketNotice` varchar(300) DEFAULT NULL COMMENT '????????????',
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
  `areaCode` varchar(6) DEFAULT '' COMMENT '????????????',
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
  `supplierId` int(11) NOT NULL COMMENT '?????????ID',
  `linkPhone` varchar(20) NOT NULL COMMENT ' ??????????????????',
  `linkman` varchar(50) NOT NULL COMMENT '???????????????',
  `applyIntention` varchar(600) NOT NULL COMMENT '????????????',
  `supplierName` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '?????????????????',
  `handleReamrk` varchar(600) DEFAULT NULL,
  `applyStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '???????????? 0:?????????  1:?????????  -1:??????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:?????? -1:??????',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_bases`;
CREATE TABLE `wst_supplier_bases` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `flowId` int(11) DEFAULT 0 COMMENT '??????ID',
  `fieldName` varchar(50) DEFAULT '' COMMENT '????????????',
  `dataType` varchar(10) DEFAULT '' COMMENT '????????????',
  `dataLength` int(11) DEFAULT 0 COMMENT '????????????',
  `fieldSort` tinyint(4) unsigned DEFAULT 0 COMMENT '????????????',
  `isRequire` tinyint(4) unsigned DEFAULT 0 COMMENT '???????????????0??????1???',
  `fieldType` varchar(10) DEFAULT '' COMMENT '????????????',
  `isRelevance` tinyint(4) unsigned DEFAULT 0 COMMENT '?????????????????????0??????1???',
  `fieldRelevance` varchar(50) DEFAULT '' COMMENT '????????????',
  `dateRelevance` varchar(100) DEFAULT '' COMMENT '??????????????????',
  `timeRelevance` varchar(100) DEFAULT '' COMMENT '??????????????????',
  `isShow` tinyint(4) unsigned DEFAULT 1 COMMENT '???????????????0?????????1??????',
  `isMap` tinyint(4) unsigned DEFAULT 0 COMMENT '?????????????????????0?????????1??????',
  `createTime` datetime NOT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:??????  -1:??????',
  `isDelete` tinyint(4) DEFAULT 1 COMMENT '?????????????????????0??????1???',
  `isSuppliersTable` tinyint(4) DEFAULT 0,
  `fileNum` tinyint(4) DEFAULT NULL,
  `isPhone` tinyint(4) DEFAULT 0 COMMENT '???????????????',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_bases_langs`;
CREATE TABLE `wst_supplier_bases_langs` (
  `baseId` int(11) NOT NULL COMMENT '??????ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '??????ID',
  `fieldTitle` varchar(255) DEFAULT NULL COMMENT '????????????',
  `fieldComment` varchar(500) DEFAULT NULL COMMENT '????????????',
  `fieldAttr` varchar(255) DEFAULT '' COMMENT '????????????',
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
  `applyLinkTelAreaCode` varchar(6) DEFAULT '' COMMENT '????????????(applyLinkTel)',
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
  `areaId` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `tableExtraName` varchar(20) NOT NULL COMMENT '??????????????????',
  `sort` tinyint(4) DEFAULT 0,
  `createTime` datetime NOT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:??????  -1:??????',
  PRIMARY KEY (`areaId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_flow_areas_langs`;
CREATE TABLE `wst_supplier_flow_areas_langs` (
  `areaId` int(11) NOT NULL COMMENT '????????????ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '??????ID',
  `areaName` varchar(100) DEFAULT '' COMMENT '??????????????????',
  PRIMARY KEY (`areaId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_flows`;
CREATE TABLE `wst_supplier_flows` (
  `flowId` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `isShow` tinyint(4) DEFAULT 1 COMMENT '???????????????0?????????1??????',
  `sort` tinyint(4) DEFAULT 0 COMMENT '????????????',
  `isDelete` tinyint(4) DEFAULT 1 COMMENT '?????????????????????0??????1???',
  `createTime` datetime NOT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:??????  -1:??????',
  `areaId` int(11) DEFAULT 0 COMMENT '????????????ID',
  PRIMARY KEY (`flowId`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_flows_langs`;
CREATE TABLE `wst_supplier_flows_langs` (
  `flowId` int(11) NOT NULL COMMENT '??????ID',
  `langId` int(11) NOT NULL DEFAULT 0 COMMENT '??????ID',
  `flowName` varchar(100) DEFAULT '' COMMENT '????????????',
  PRIMARY KEY (`flowId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_freight_template`;
CREATE TABLE `wst_supplier_freight_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierExpressId` int(11) NOT NULL COMMENT '?????????ID',
  `tempName` varchar(200) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `tempType` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0????????? 1???????????????',
  `provinceIds` text DEFAULT NULL,
  `cityIds` text DEFAULT NULL,
  `buyNumStart` int(11) DEFAULT 0 COMMENT '?????????(????',
  `buyNumStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '????????????(???)',
  `buyNumContinue` int(11) DEFAULT 0 COMMENT '?????????(????',
  `buyNumContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '????????????(???)',
  `weightStart` decimal(11,2) DEFAULT 0.00 COMMENT '?????????Kg???',
  `weightStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `weightContinue` decimal(11,2) DEFAULT 0.00 COMMENT '?????????Kg???',
  `weightContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `volumeStart` decimal(11,2) DEFAULT 0.00 COMMENT '????????????(m??)',
  `volumeStartPrice` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????(???)',
  `volumeContinue` decimal(11,2) DEFAULT 0.00 COMMENT '????????????(m??)',
  `volumeContinuePrice` decimal(11,2) DEFAULT 0.00 COMMENT '???????????????(???)',
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
  `shippingFeeType` tinyint(4) DEFAULT 1 COMMENT '???????????? 1:?????? 2:?????? 3:??????',
  `goodsWeight` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `goodsVolume` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `supplierExpressId` int(11) DEFAULT 0 COMMENT '??????????????????ID',
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
  `orderGoodsId` int(10) unsigned NOT NULL COMMENT '???????????????Id',
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
  `goodsId` int(10) unsigned NOT NULL COMMENT '??????id',
  `userId` int(10) unsigned DEFAULT NULL COMMENT '??????id',
  `consultType` tinyint(3) unsigned DEFAULT NULL COMMENT '????????????',
  `consultContent` varchar(500) DEFAULT NULL COMMENT '????????????',
  `createTime` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '????????????',
  `reply` varchar(255) NOT NULL DEFAULT '' COMMENT '????????????',
  `replyTime` datetime DEFAULT NULL COMMENT '????????????',
  `dataFlag` tinyint(4) DEFAULT 1 COMMENT '??????????????????',
  `isShow` tinyint(4) DEFAULT 1 COMMENT '??????????????????',
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
  `specWeight` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `specVolume` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `costPrice` decimal(11,2) DEFAULT 0.00 COMMENT '?????????',
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
  `logId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '??????id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '??????id',
  `logJson` text DEFAULT NULL,
  `logTargetId` int(10) unsigned NOT NULL COMMENT '?????????Id,????????????????????????????????????ID',
  `logType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '???????????????,0?????????  1????????????',
  `logTime` datetime DEFAULT current_timestamp() COMMENT '????????????',
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
  `orderGoodscommission` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
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
  `serviceId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '?????????id',
  `isServiceRefund` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '????????????????????????0???????????? 1????????????',
  `refundProcessStatus` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_supplier_order_services`;
CREATE TABLE `wst_supplier_order_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????',
  `orderId` int(10) unsigned NOT NULL COMMENT '??????id',
  `goodsServiceType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '0??????????????? 1????????? 2?????????',
  `serviceType` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '???????????????,?????????????????????????????????',
  `serviceRemark` varchar(600) DEFAULT NULL COMMENT '???????????????',
  `serviceAnnex` text DEFAULT NULL COMMENT '??????',
  `refundMoney` decimal(11,2) DEFAULT NULL COMMENT '?????????????????????',
  `refundableMoney` decimal(11,2) DEFAULT NULL COMMENT '????????????????????????',
  `isSupplierAgree` tinyint(4) DEFAULT 0 COMMENT '1:?????? 0????????????',
  `disagreeRemark` varchar(600) DEFAULT NULL COMMENT '?????????????????????',
  `userAddressId` int(11) unsigned DEFAULT 0 COMMENT '??????????????????id',
  `areaId` int(11) unsigned DEFAULT 0 COMMENT '??????id',
  `areaIdPath` varchar(255) DEFAULT NULL COMMENT '??????ID???',
  `userName` varchar(200) DEFAULT '' COMMENT '???????????????',
  `userAddress` varchar(200) DEFAULT '' COMMENT '????????????????????????',
  `userPhone` varchar(200) DEFAULT '' COMMENT '??????????????????',
  `supplierAreaId` int(11) unsigned DEFAULT 0 COMMENT '??????????????????ID',
  `supplierAreaIdPath` varchar(255) DEFAULT NULL COMMENT '??????????????????ID???',
  `supplierName` varchar(200) DEFAULT '' COMMENT '???????????????',
  `supplierAddress` varchar(200) DEFAULT '' COMMENT '????????????????????????',
  `supplierPhone` varchar(200) DEFAULT '' COMMENT '??????????????????',
  `isUserSendGoods` tinyint(4) DEFAULT 0 COMMENT '0???????????? 1????????????',
  `expressType` tinyint(4) DEFAULT 0 COMMENT '0???????????????  1?????????',
  `expressId` int(11) unsigned DEFAULT 0 COMMENT '????????????ID',
  `expressNo` varchar(200) DEFAULT '' COMMENT '??????????????????',
  `isSupplierAccept` tinyint(4) DEFAULT 0 COMMENT '????????????????????? -1?????????  0????????????  1????????????',
  `supplierRejectType` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '??????????????????,?????????????????????????????????',
  `supplierRejectOther` varchar(600) DEFAULT NULL COMMENT '??????????????????,???????????????????????????????????????',
  `supplierRejectImg` varchar(150) DEFAULT NULL COMMENT '????????????????????????',
  `isSupplierSend` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '?????????????????? 0???????????? 1????????????',
  `supplierExpressType` tinyint(4) DEFAULT 0 COMMENT '0???????????????  1?????????',
  `supplierExpressId` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '????????????ID',
  `supplierExpressNo` varchar(200) DEFAULT '' COMMENT '??????????????????',
  `isUserAccept` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '-1????????? 0????????????  1????????????',
  `userRejectType` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '??????????????????,?????????????????????????????????',
  `userRejectOther` varchar(600) DEFAULT NULL COMMENT '??????????????????,???????????????????????????????????????',
  `createTime` datetime NOT NULL COMMENT '????????????',
  `isClose` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '???????????? 0????????????  1:?????????',
  `serviceStatus` tinyint(4) unsigned NOT NULL DEFAULT 0 COMMENT '???????????????0??????????????????  1????????????????????? 2????????????????????? 3?????????????????????  4?????????????????????  5???????????????/??????  6???????????????????????? 7???????????????????????????????????????',
  `supplierAcceptExpireTime` datetime DEFAULT NULL COMMENT '??????????????????',
  `userSendExpireTime` datetime DEFAULT NULL COMMENT '??????????????????',
  `supplierReceiveExpireTime` datetime DEFAULT NULL COMMENT '??????????????????',
  `userReceiveExpireTime` datetime DEFAULT NULL COMMENT '??????????????????',
  `shopSendExpireTime` datetime DEFAULT NULL COMMENT '??????????????????',
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
  `noticeDeliver` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '???????????? 0:????????? 1:?????????',
  `invoiceJson` text DEFAULT NULL COMMENT '????????????',
  `lockCashMoney` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  `payTime` datetime DEFAULT NULL,
  `isBatch` tinyint(4) DEFAULT 0,
  `totalPayFee` int(11) DEFAULT 0,
  `isMakeInvoice` tinyint(4) DEFAULT 0 COMMENT '??????????????? 1:??? 0:???',
  `afterSaleEndTime` datetime DEFAULT NULL COMMENT '??????????????????,??????????????????+?????????????????????????????????',
  `refundedPayMoney` decimal(11,2) DEFAULT 0.00 COMMENT '?????????????????????',
  `shopId` int(11) DEFAULT 0,
  `verificationCode` varchar(20) DEFAULT '0' COMMENT '?????????',
  `verificationTime` datetime DEFAULT NULL COMMENT '????????????',
  `supplierRejectReason` int(11) unsigned DEFAULT 0 COMMENT '??????????????????????????????''????????????''',
  `supplierRejectOtherReason` varchar(255) DEFAULT NULL COMMENT '???????????????????????????shopRejectReason=10000?????????????????????',
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
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '??????id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '??????id',
  `goodsId` int(10) unsigned NOT NULL COMMENT '??????ID',
  `goodsSpecId` int(10) unsigned NOT NULL COMMENT '????????????ID',
  `goodsNum` int(10) unsigned NOT NULL COMMENT '???????????????????????????',
  `createTime` datetime DEFAULT current_timestamp() COMMENT '????????????',
  `dataFlag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '???????????? 1:??????  -1:??????',
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
  `rechargeMoney` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `longitude` decimal(11,7) DEFAULT NULL,
  `latitude` decimal(11,7) DEFAULT NULL,
  `mapLevel` int(11) DEFAULT 15,
  `tradeId` int(4) DEFAULT 0 COMMENT '????????????ID',
  `expireDate` date DEFAULT NULL COMMENT '????????????',
  `isPay` tinyint(4) DEFAULT 0 COMMENT '?????????????????????0???,1???',
  `payAnnualFee` decimal(11,2) DEFAULT 0.00 COMMENT '??????????????????',
  `isRefund` int(11) DEFAULT 0 COMMENT '?????????????????????0??????1???',
  `supplierNotice` varchar(300) DEFAULT NULL COMMENT '???????????????',
  `areaCode` varchar(6) DEFAULT '' COMMENT '????????????(telephone)',
  `telephoneAreaCode` varchar(6) DEFAULT '' COMMENT '????????????(telephone)',
  `flowAreaId` int(11) DEFAULT 0 COMMENT '????????????Id',
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
  `configId` int(11) NOT NULL AUTO_INCREMENT COMMENT '??????ID',
  `fieldName` varchar(50) DEFAULT NULL COMMENT '????????????',
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
  `tradeFee` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
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
  `areaCode` varchar(6) DEFAULT '' COMMENT '????????????',
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
  `rechargeMoney` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `isInform` tinyint(4) NOT NULL DEFAULT 1,
  `wxOpenId` char(100) DEFAULT NULL,
  `wxUnionId` char(100) DEFAULT NULL,
  `areaCode` varchar(6) DEFAULT '' COMMENT '????????????',
  `nftWalletAddress` varchar(150) DEFAULT '',
  `isMallStar` tinyint(4) NOT NULL DEFAULT 0 COMMENT '?????????MallStar',
  `invitationCode` varchar(50) DEFAULT '' COMMENT '?????????',
  `distributMoney` decimal(11,2) DEFAULT 0.00 COMMENT '????????????',
  `iSunOneUser` tinyint(4) NOT NULL DEFAULT 0 COMMENT '?????????iSunOne??????',
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
