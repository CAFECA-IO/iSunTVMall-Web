SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_shop_users`;
CREATE TABLE `wst_shop_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL DEFAULT '0',
  `userId` int(11) NOT NULL DEFAULT '0',
  `roleId` int(11) NOT NULL DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `privilegeMsgTypes` varchar(50) DEFAULT '',
  `privilegeMsgs` varchar(200) DEFAULT '',
  `privilegePhoneMsgs` varchar(200) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_shop_users` VALUES ('1', '1', '1', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('2', '2', '2', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('3', '3', '3', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('4', '4', '4', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('5', '5', '5', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('6', '6', '6', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('7', '7', '7', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('8', '8', '8', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('9', '9', '9', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('10', '10', '10', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('11', '11', '11', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', ''),
('12', '12', '12', '0', '1', '1,4', '1,2,3,4,5,6,7,8,9,10', '');
