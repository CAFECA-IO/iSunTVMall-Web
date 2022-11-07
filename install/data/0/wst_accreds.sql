SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_accreds`;
CREATE TABLE `wst_accreds` (
  `accredId` int(11) NOT NULL AUTO_INCREMENT,
  `accredName` varchar(50) NOT NULL,
  `accredImg` varchar(150) NOT NULL DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`accredId`),
  KEY `dataFlag` (`dataFlag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `wst_accreds` VALUES ('1', '�����������˿�', 'upload/accreds/2019-02/5c6f74197968c.png', '1', '2019-02-22 12:01:30'),('2', '������֤�̼�', 'upload/accreds/2019-02/5c6f74289d057.png', '1', '2019-02-22 12:01:45');

