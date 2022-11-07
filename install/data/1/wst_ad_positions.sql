SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_ad_positions`;
CREATE TABLE `wst_ad_positions` (
  `positionId` int(11) NOT NULL AUTO_INCREMENT,
  `positionType` tinyint(4) NOT NULL DEFAULT '0',
  `positionName` varchar(100) NOT NULL,
  `positionWidth` int(11) NOT NULL DEFAULT '0',
  `positionHeight` int(11) NOT NULL DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `positionCode` varchar(20) DEFAULT NULL,
  `apSort` int(11) NOT NULL,
  PRIMARY KEY (`positionId`),
  KEY `dataFlag` (`positionType`) USING BTREE,
  KEY `positionCode` (`positionCode`)
) ENGINE=InnoDB AUTO_INCREMENT=501 DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_ad_positions` VALUES ('34', '1', '首页轮播广告', '1920', '420', '1', 'ads-index', '99'),
('35', '1', '首页顶部广告', '1920', '100', '1', 'index-top-ads', '99'),
('63', '1', '首页分层1F顶部广告', '1200', '110', '1', 'ads-1-1', '99'),
('69', '1', '首页分层3F顶部广告', '1200', '110', '1', 'ads-3-1', '99'),
('75', '1', '首页分层5F顶部广告', '1200', '110', '1', 'ads-5-1', '99'),
('81', '1', '首页分层7F顶部广告', '1200', '110', '1', 'ads-7-1', '99'),
('95', '1', '首页资讯上方广告', '210', '128', '1', 'index-art', '99'),
('290', '1', '商家入驻广告', '1920', '350', '1', 'ads-shop-apply', '99'),
('291', '1', '首页广告墙左上', '448', '237', '1', 'wall-left-top', '99'),
('292', '1', '首页广告墙左下', '448', '237', '1', 'wall-left-bottom', '99'),
('293', '1', '首页广告墙中间', '292', '480', '1', 'wall-center', '99'),
('294', '1', '首页广告墙右上', '448', '237', '1', 'wall-right-top', '99'),
('295', '1', '首页广告墙右下', '448', '237', '1', 'wall-right-bottom', '99'),
('296', '1', '爱上团购替换广告', '329', '500', '1', 'rbnh-left-ads', '99'),
('297', '1', '自营店铺1f广告', '1200', '320', '1', 'self-shop-f1', '99'),
('298', '1', '自营店铺2f广告', '1200', '320', '1', 'self-shop-f2', '99'),
('299', '1', '自营店铺3f广告', '1200', '320', '1', 'self-shop-f3', '99'),
('300', '1', '自营店铺4f广告', '1200', '320', '1', 'self-shop-f4', '99'),
('301', '1', '自营店铺5f广告', '1200', '320', '1', 'self-shop-f5', '99'),
('302', '1', '自营店铺6f广告', '1200', '320', '1', 'self-shop-f6', '99'),
('303', '1', '1F楼层左侧背景图', '240', '560', '1', 'index-floor-left-1', '99'),
('304', '1', '3F楼层左侧背景图', '240', '560', '1', 'index-floor-left-3', '99'),
('305', '1', '5F楼层左侧背景图', '240', '560', '1', 'index-floor-left-5', '99'),
('306', '1', '7F楼层左侧背景图', '240', '560', '1', 'index-floor-left-7', '99'),
('307', '1', '用户登录背景广告', '1920', '480', '1', 'ads-login-user', '99'),
('308', '1', '商家登录背景广告', '1920', '480', '1', 'ads-login-shop', '99'),
('309', '1', '首页分层2F顶部广告', '1200', '110', '1', 'ads-2-1', '99'),
('310', '1', '首页分层4F顶部广告', '1200', '110', '1', 'ads-4-1', '99'),
('311', '1', '首页分层6F顶部广告', '1200', '110', '1', 'ads-6-1', '99'),
('312', '1', '首页分层8F顶部广告', '1200', '110', '1', 'ads-8-1', '99'),
('313', '1', '首页分层9F顶部广告', '1200', '110', '1', 'ads-9-1', '99'),
('314', '1', '首页分层10F顶部广告', '1200', '110', '1', 'ads-10-1', '99'),
('315', '1', '2F楼层左侧背景图', '240', '560', '1', 'index-floor-left-2', '99'),
('316', '1', '4F楼层左侧背景图', '240', '560', '1', 'index-floor-left-4', '99'),
('317', '1', '6F楼层左侧背景图', '240', '560', '1', 'index-floor-left-6', '99'),
('318', '1', '8F楼层左侧背景图', '240', '560', '1', 'index-floor-left-8', '99'),
('319', '1', '9F楼层左侧背景图', '240', '560', '1', 'index-floor-left-9', '99'),
('320', '1', '10F楼层左侧背景图', '240', '560', '1', 'index-floor-left-10', '99'),
('321', '1', 'PC首页弹出广告', '800', '500', '1', 'home-pop-ads', '99'),
('500', '1', '供货商入驻广告', '1920', '350', '1', 'ads-supplier-apply', '100');
