SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_shop_member_user_consumes`;
CREATE TABLE `wst_shop_member_user_consumes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  `money` decimal(11,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;