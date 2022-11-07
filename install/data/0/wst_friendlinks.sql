SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_friendlinks`;
CREATE TABLE `wst_friendlinks` (
  `friendlinkId` int(11) NOT NULL AUTO_INCREMENT,
  `friendlinkIco` varchar(150) DEFAULT '',
  `friendlinkName` varchar(50) NOT NULL DEFAULT '',
  `friendlinkUrl` varchar(150) NOT NULL DEFAULT '',
  `friendlinkSort` int(11) NOT NULL DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`friendlinkId`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_friendlinks` VALUES ('1', '', 'b2b2c商城系统', 'https://www.wstmart.net', '0', '1', '2016-10-20 11:53:56'),
('2', '', '商淘软件', 'https://www.shangtao.net', '0', '1', '2016-10-20 11:53:56'),
('3', '', '社区商城', 'https://www.wstmall.net/', '3', '1', '2016-10-20 11:53:56'),
('4', '', 'b2c电子商务', 'https://www.wstshop.net/', '4', '1', '2016-10-20 11:53:56'),
('5', '', '便利店管理系统', 'https://www.wststore.net/', '5', '1', '2016-10-20 11:53:56'),
('6', '', '商淘电商学院', 'https://www.shangtaoyun.net', '1', '1', '2020-06-28 11:19:32'),
('7', '', '23', '2323', '0', '-1', '2020-06-28 11:23:11'),
('8', '', '23', '23', '0', '-1', '2020-06-28 11:23:35');
