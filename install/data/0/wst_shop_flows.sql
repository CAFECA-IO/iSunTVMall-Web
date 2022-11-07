SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_shop_flows`;
CREATE TABLE `wst_shop_flows` (
  `flowId` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `flowName` varchar(100) NOT NULL COMMENT '流程名称',
  `isShow` tinyint(4) DEFAULT '1' COMMENT '是否显示，0隐藏，1显示',
  `sort` tinyint(4) DEFAULT '0' COMMENT '显示排序',
  `isDelete` tinyint(4) DEFAULT '1' COMMENT '是否可以删除，0否，1是',
  `createTime` datetime NOT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '有效状态 1:有效  -1:删除',
  PRIMARY KEY (`flowId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_shop_flows` VALUES ('1', '签订入驻协议', '1', '1', '0', '2019-03-27 15:04:59', '1'),
('2', '公司信息', '1', '2', '0', '2019-03-27 15:05:23', '1'),
('3', '店铺信息', '1', '3', '0', '2019-03-27 15:05:41', '1'),
('4', '入驻审核', '1', '4', '0', '2019-03-27 15:06:37', '1');
