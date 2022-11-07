SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_menus`;
CREATE TABLE `wst_menus` (
  `menuId` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL,
  `menuName` varchar(100) NOT NULL,
  `menuSort` int(11) NOT NULL DEFAULT '0',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '0',
  `menuMark` varchar(50) DEFAULT NULL,
  `isShow` tinyint(4) DEFAULT '1',
  `menuIcon` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`menuId`),
  KEY `parentId` (`parentId`,`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=701 DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_menus` VALUES ('1', '0', '平台', '0', '1', null, '1', 'desktop'),
('2', '1', '系统管理', '0', '1', null, '1', 'wrench'),
('3', '2', '菜单权限', '100', '1', null, '1', 'bars'),
('4', '22', '角色管理', '3', '1', null, '1', 'user'),
('5', '22', '职员管理', '2', '1', null, '1', 'user-secret'),
('6', '38', '职员登录日志', '4', '1', null, '1', ''),
('7', '38', '操作日志', '5', '1', null, '1', ''),
('8', '35', '购物设置', '100', '1', null, '1', 'cog'),
('9', '2', '平台配置', '0', '1', null, '1', 'cog'),
('10', '2', '导航管理', '1', '1', null, '1', 'tachometer'),
('11', '28', '广告管理', '2', '1', null, '1', 'bullhorn'),
('12', '8', '支付管理', '5', '1', null, '1', 'credit-card'),
('13', '8', '银行管理', '4', '1', null, '1', 'bank'),
('14', '2', '友情链接', '7', '1', null, '1', 'link'),
('15', '0', '营销', '3', '1', null, '1', 'gift'),
('16', '8', '地区管理', '6', '1', null, '1', 'sitemap'),
('17', '0', '统计', '4', '1', '', '1', 'line-chart'),
('18', '26', '会员管理', '4', '1', null, '1', 'users'),
('19', '18', '会员等级', '0', '1', null, '1', 'sitemap'),
('20', '18', '会员管理', '1', '1', null, '1', 'users'),
('22', '1', '职员管理', '0', '1', null, '1', 'id-card'),
('23', '35', '商品管理', '1', '1', null, '1', 'cubes'),
('24', '23', '商品分类', '3', '1', null, '1', 'sitemap'),
('25', '23', '品牌管理', '4', '1', null, '1', 'registered'),
('26', '0', '会员', '2', '1', null, '1', 'user'),
('27', '15', '签到', '0', '1', null, '1', ''),
('28', '15', '广告管理', '0', '1', null, '1', ''),
('29', '1', '文章管理', '3', '1', null, '1', 'leanpub'),
('30', '29', '文章分类', '1', '1', null, '1', 'folder'),
('31', '29', '文章管理', '0', '1', null, '1', 'leanpub'),
('32', '23', '商品规格', '5', '1', null, '1', 'flag-checkered'),
('33', '23', '已上架商品', '0', '1', null, '1', 'check-circle'),
('34', '23', '评价管理', '6', '1', null, '1', 'comments'),
('35', '0', '商城', '1', '1', null, '1', 'shopping-cart'),
('36', '2', '资源文件管理', '6', '1', null, '1', 'image'),
('37', '27', '签到设置', '0', '1', null, '1', ''),
('38', '1', '日志管理', '0', '1', null, '1', 'eye'),
('39', '35', '店铺管理', '3', '1', null, '1', 'institution'),
('41', '2', '商城消息', '7', '1', null, '1', 'comment'),
('42', '28', '广告位置', '3', '1', null, '1', 'bookmark'),
('43', '2', '前台菜单', '99', '1', null, '1', ''),
('44', '39', '店铺认证', '0', '1', null, '1', 'tags'),
('45', '39', '开店申请', '1', '1', null, '1', 'clock-o'),
('46', '39', '店铺管理', '2', '1', null, '1', 'check-circle'),
('47', '39', '停用店铺', '3', '1', null, '1', 'exclamation-circle'),
('48', '23', '商品属性', '3', '1', null, '1', 'flag-o'),
('49', '35', '订单管理', '0', '1', null, '1', 'shopping-cart'),
('50', '49', '订单管理', '0', '1', null, '1', 'shopping-cart'),
('51', '49', '投诉订单', '1', '1', null, '1', 'frown-o'),
('52', '49', '退款订单', '2', '1', null, '1', 'asl-interpreting'),
('53', '8', '快递管理', '8', '1', null, '1', 'truck'),
('54', '23', '待审核商品', '1', '1', null, '1', 'clock-o'),
('55', '23', '违规商品', '2', '1', null, '1', 'exclamation-circle'),
('56', '0', '财务', '2', '1', null, '1', 'truck'),
('57', '15', '推荐管理', '0', '1', null, '1', 'thumbs-o-up'),
('58', '57', '商品推荐', '0', '1', null, '1', 'cubes'),
('59', '57', '店铺推荐', '1', '1', null, '1', 'institution'),
('60', '57', '品牌推荐', '2', '1', null, '1', 'registered'),
('61', '2', '风格管理', '10', '1', null, '1', 'superpowers'),
('62', '56', '财务管理', '0', '1', null, '1', 'money'),
('63', '62', '提现申请', '0', '1', null, '1', ''),
('64', '62', '结算申请', '2', '1', null, '1', ''),
('65', '62', '商家未结算订单', '4', '1', null, '1', 'circle-o'),
('71', '17', '统计报表', '3', '1', null, '1', 'line-chart'),
('72', '71', '商品销售排行', '0', '1', null, '1', 'table'),
('73', '71', '新增会员统计', '4', '1', null, '1', 'line-chart'),
('74', '71', '销售额统计', '2', '1', null, '1', 'line-chart'),
('75', '71', '会员登录统计', '5', '1', null, '1', 'line-chart'),
('76', '71', '销售订单统计', '3', '1', null, '1', 'line-chart'),
('77', '71', '店铺销售统计', '1', '1', null, '1', 'table'),
('81', '0', '应用', '20', '1', null, '1', 'cloud'),
('82', '81', '扩展管理', '0', '1', null, '1', 'cloud'),
('83', '82', '插件管理', '0', '1', null, '1', 'cloud-download'),
('84', '82', '钩子管理', '1', '1', null, '1', 'thumb-tack'),
('85', '62', '资金管理', '0', '1', null, '1', ''),
('86', '8', '消息模板', '12', '1', null, '1', 'window-restore'),
('93', '15', '促销管理', '0', '1', null, '1', null),
('168', '23', '商品咨询', '9', '1', '', '1', 'coffee'),
('169', '2', '系统数据管理', '12', '1', null, '1', 'file-text-o'),
('170', '2', '移动端按钮管理', '13', '1', null, '1', 'mobile'),
('172', '300', '充值设置', '1', '1', null, '1', 'money'),
('185', '38', '短信日志', '6', '1', '', '1', ''),
('188', '23', '举报管理', '6', '1', '', '1', ''),
('195', '2', '页面转换', '14', '1', '', '1', ''),
('220', '39', '店铺风格管理', '0', '1', '', '1', ''),
('300', '15', '会员充值', '0', '1', '', '1', ''),
('301', '300', '充值列表', '0', '1', '', '1', ''),
('302', '27', '签到排行', '0', '1', '', '1', ''),
('303', '8', '通知设置', '0', '1', '', '1', ''),
('304', '8', '购物设置', '0', '1', '', '1', ''),
('305', '2', '功能反馈', '101', '1', '', '1', ''),
('325', '71', '充值统计报表', '7', '1', null, '1', 'table'),
('326', '71', '提现统计报表', '8', '1', null, '1', 'table'),
('328', '71', '积分抵扣报表', '10', '1', null, '1', 'table'),
('329', '71', '订单销售额统计', '6', '1', null, '1', 'table'),
('335', '39', '店铺公告', '7', '1', '', '1', ''),
('336', '39', '店铺帮助', '8', '1', '', '1', ''),
('337', '39', '入驻指南', '8', '1', '', '1', ''),
('350', '39', '入驻流程', '0', '1', '', '1', ''),
('397', '18', '注册协议', '102', '1', '', '1', ''),
('398', '2', '系统禁用关键字', '102', '1', '', '1', ''),
('399', '38', '用户登录日志', '7', '1', '', '1', ''),
('460', '39', '缴纳年费记录', '4', '1', '', '1', ''),
('500', '0', '供货商', '1', '1', null, '1', 'deaf'),
('501', '500', '供货商管理', '0', '1', null, '1', ''),
('502', '501', '入驻流程', '0', '1', null, '1', ''),
('503', '501', '供货商申请', '0', '1', null, '1', 'clock-o'),
('504', '501', '供货商管理', '0', '1', null, '1', 'check-circle'),
('505', '500', '订单管理', '0', '1', null, '1', 'shopping-cart'),
('506', '505', '订单管理', '0', '1', null, '1', 'shopping-cart'),
('507', '505', '投诉订单', '1', '1', null, '1', 'frown-o'),
('508', '505', '退款订单', '2', '1', null, '1', 'asl-interpreting'),
('509', '500', '商品管理', '1', '1', null, '1', 'cubes'),
('510', '509', '已上架商品', '0', '1', null, '1', 'check-circle'),
('511', '509', '待审核商品', '1', '1', null, '1', 'clock-o'),
('512', '509', '违规商品', '2', '1', null, '1', 'exclamation-circle'),
('513', '509', '评价管理', '4', '1', null, '1', 'comments'),
('514', '501', '停用供货商', '4', '1', null, '1', 'exclamation-circle'),
('515', '501', '入驻指南', '5', '1', null, '1', ''),
('516', '62', '供货商结算申请', '5', '1', null, '1', ''),
('517', '62', '供货商未结算订单', '6', '1', null, '1', ''),
('550', '38', '商家登录日志', '8', '1', null, '1', ''),
('551', '38', '商家操作日志', '9', '1', null, '1', ''),
('552', '62', '财务统计', '0', '1', null, '1', ''),
('553', '39', '行业管理', '0', '1', null, '1', 'sitemap'),
('554', '38', '供货商操作日志', '10', '1', null, '1', ''),
('600', '501', '缴纳年费记录', '4', '1', '', '1', ''),
('700', '71', '商家到期报表', '10', '1', null, '1', 'table');
