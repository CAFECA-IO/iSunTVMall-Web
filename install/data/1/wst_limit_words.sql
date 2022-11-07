SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_limit_words`;
CREATE TABLE `wst_limit_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `word` varchar(50) DEFAULT NULL COMMENT '禁用关键字',
  `createTime` datetime DEFAULT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) DEFAULT '1' COMMENT '1:有效  -1:删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

