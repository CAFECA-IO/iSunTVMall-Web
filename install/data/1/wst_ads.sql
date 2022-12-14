SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_ads`;
CREATE TABLE `wst_ads` (
  `adId` int(11) NOT NULL AUTO_INCREMENT,
  `adPositionId` int(11) NOT NULL DEFAULT '0',
  `adFile` varchar(150) NOT NULL,
  `adName` varchar(100) NOT NULL,
  `adURL` varchar(255) DEFAULT NULL,
  `adStartDate` date NOT NULL,
  `adEndDate` date NOT NULL,
  `adSort` int(11) NOT NULL DEFAULT '0',
  `adClickNum` int(11) NOT NULL DEFAULT '0',
  `positionType` tinyint(4) DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `createTime` datetime NOT NULL,
  `subTitle` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`adId`),
  KEY `adPositionId` (`adPositionId`,`adStartDate`,`adEndDate`),
  KEY `adPositionId_2` (`adPositionId`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_ads` VALUES ('40', '290', 'upload/adspic/2017-06/59520643d6c51.jpg', '商家入驻广告', null, '2016-01-01', '2066-01-01', '0', '0', '1', '1', '2017-06-29 14:48:17', null),
('41', '290', 'upload/adspic/2017-06/5952047b41189.jpg', '商家入驻广告', null, '2016-01-01', '2066-01-01', '0', '0', '1', '1', '2017-06-29 14:48:17', null),
('42', '307', 'upload/adspic/2019-01/5c36f8cb6a589.jpg', '111', '', '2019-01-01', '2023-01-31', '0', '0', '1', '1', '2019-01-10 15:48:36', '111'),
('43', '308', 'upload/adspic/2019-01/5c36f8de66f2f.jpg', '1111', '', '2019-01-01', '2025-02-06', '0', '0', '1', '1', '2019-01-10 15:48:52', '111'),
('44', '34', 'upload/adspic/2019-02/5c6fbbca43a13.png', '轮播广告', '', '2015-12-01', '2030-02-28', '0', '0', '1', '1', '2019-02-22 17:07:43', '轮播广告'),
('46', '34', 'upload/adspic/2019-02/5c6fbc05a2740.png', '轮播广告', '', '2016-01-01', '2030-02-22', '0', '0', '1', '1', '2019-02-22 17:08:41', '轮播广告'),
('47', '34', 'upload/adspic/2019-02/5c6fbc2de1c27.png', '轮播广告', '', '2016-02-01', '2030-02-28', '0', '0', '1', '1', '2019-02-22 17:09:13', '轮播广告'),
('48', '95', 'upload/adspic/2019-02/5c6fbc65742f0.jpg', '资讯上方广告', '', '2016-02-04', '2030-02-22', '0', '0', '1', '1', '2019-02-22 17:10:09', '资讯上方广告'),
('49', '296', 'upload/adspic/2019-02/5c6fbcd873592.png', '品牌街下方左侧广告', '', '2015-02-07', '2030-02-28', '0', '0', '1', '1', '2019-02-22 17:12:03', '品牌街下方左侧广告'),
('50', '303', 'upload/adspic/2019-02/5c70ee16d1ccc.png', '1F', '', '2017-02-01', '2031-02-28', '0', '0', '1', '1', '2019-02-23 14:54:30', '1F'),
('51', '315', 'upload/adspic/2019-02/5c70ee4fb9342.png', '2F', '', '2016-02-03', '2028-02-25', '0', '0', '1', '1', '2019-02-23 14:55:20', '2F'),
('53', '304', 'upload/adspic/2019-02/5c70ee704a548.png', '3F', '', '2016-02-01', '2031-08-29', '0', '0', '1', '1', '2019-02-23 14:56:05', '3F'),
('54', '316', 'upload/adspic/2019-02/5c70ef013db4a.png', '4F', '', '2016-12-08', '2030-02-27', '0', '0', '1', '1', '2019-02-23 14:58:23', '4F'),
('55', '305', 'upload/adspic/2019-02/5c70ef403a30d.png', '5F', '', '2016-02-02', '2029-02-28', '0', '0', '1', '1', '2019-02-23 14:59:21', '5F'),
('56', '317', 'upload/adspic/2019-02/5c70ef627ae0d.png', '6F', '', '2017-01-02', '2029-02-28', '0', '0', '1', '1', '2019-02-23 14:59:56', '6F'),
('57', '306', 'upload/adspic/2019-02/5c70ef9b78be8.png', '7F', '', '2016-02-02', '2028-02-29', '0', '0', '1', '1', '2019-02-23 15:00:51', '7F'),
('58', '318', 'upload/adspic/2019-02/5c70efba204e1.png', '8F', '', '2016-02-02', '2030-03-05', '0', '0', '1', '1', '2019-02-23 15:01:23', '8F'),
('59', '500', 'upload/adspic/2020-03/5e7b1dd310261.jpg', '供货商入驻广告', '', '2020-03-15', '2028-03-31', '1', '0', '1', '1', '2020-03-23 16:14:47', ''),
('60', '500', 'upload/adspic/2020-03/5e786fece1266.jpg', '供货商入驻广告', '', '2020-03-15', '2028-03-31', '1', '0', '1', '1', '2020-03-23 16:14:47', '');
