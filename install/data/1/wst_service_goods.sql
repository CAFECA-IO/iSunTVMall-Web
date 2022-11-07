SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_service_goods`;
CREATE TABLE `wst_service_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '订单id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '售后id',
  `goodsId` int(10) unsigned NOT NULL COMMENT '商品ID',
  `goodsSpecId` int(10) unsigned NOT NULL COMMENT '商品规格ID',
  `goodsNum` int(10) unsigned NOT NULL COMMENT '申请售后的商品数量',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '有效状态 1:有效  -1:删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='售后商品表';
