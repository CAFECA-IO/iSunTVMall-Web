SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_shop_members`;
CREATE TABLE `wst_shop_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL COMMENT '用户ID',
  `groupId` int(11) DEFAULT '0' COMMENT '会员分组ID',
  `shopId` int(11) DEFAULT NULL COMMENT '商家ID',
  `totalOrderMoney` decimal(11,2) DEFAULT '0.00' COMMENT '订单总金额',
  `totalOrderNum` int(11) DEFAULT '0' COMMENT '总下单数',
  `lastOrderTime` datetime DEFAULT NULL COMMENT '最后一次下单时间',
  `isOrder` tinyint(4) DEFAULT '0' COMMENT '0:未下单  1:已下单',
  `createTime` datetime DEFAULT NULL COMMENT '关注时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;