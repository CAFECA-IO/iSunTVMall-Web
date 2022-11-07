SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_shop_applys`;
CREATE TABLE `wst_shop_applys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL COMMENT '用户ID',
  `linkPhone` varchar(20) NOT NULL COMMENT ' 申请联系电话',
  `linkman` varchar(50) NOT NULL COMMENT '申请联系人',
  `applyIntention` varchar(600) NOT NULL COMMENT '申请意向',
  `shopName` varchar(255) DEFAULT NULL COMMENT '店铺名称',
  `handleReamrk` varchar(600) DEFAULT NULL,
  `applyStatus` tinyint(4) NOT NULL DEFAULT '0' COMMENT '申请状态 0:待处理  1:已处理  -1:无效',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '有效状态 1:有效 -1:删除',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;