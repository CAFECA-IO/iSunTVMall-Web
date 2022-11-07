SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_shop_member_goods_reduces`;
CREATE TABLE `wst_shop_member_goods_reduces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsId` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `reduceMoney` decimal(11,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;