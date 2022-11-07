DROP TABLE IF EXISTS `wst_combination_goods`;
CREATE TABLE `wst_combination_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `combineId` int(11) NOT NULL COMMENT '商品组合主表ID',
  `goodsType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1:主商品  0：搭配商品',
  `goodsId` int(11) NOT NULL COMMENT '商品ID',
  `reduceMoney` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '优惠价',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_combinations`;
CREATE TABLE `wst_combinations` (
  `combineId` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL COMMENT '商家ID',
  `combineImg` varchar(150) NOT NULL COMMENT '封面图片',
  `combineType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0：自由搭配  1：组合套餐',
  `combineStatus` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:进行中 0：暂停',
  `isAdvance` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否提前预热   0：否 1：是',
  `advanceDay` int(11) NOT NULL DEFAULT '0' COMMENT '提前天数',
  `startTime` datetime NOT NULL COMMENT '开始日期',
  `endTime` datetime NOT NULL COMMENT '结束日期',
  `isFreeShipping` tinyint(4) DEFAULT '0' COMMENT '0：包邮 1:不包邮',
  `combineOrder` int(11) DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:有效 -1:删除',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`combineId`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_combinations_langs`;
CREATE TABLE `wst_combinations_langs` (
  `combineId` int(11) NOT NULL,
  `langId` int(11) NOT NULL,
  `combineName` varchar(50) DEFAULT NULL,
  `combineDesc` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`combineId`,`langId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

