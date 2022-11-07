DROP TABLE IF EXISTS `wst_log_search_words`;
CREATE TABLE `wst_log_search_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `searchWord` varchar(255) DEFAULT NULL COMMENT '搜索关键词',
  `searchCnt` int(11) DEFAULT 0 COMMENT '搜索次数',
  `lastTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_log_search_word_goods`;
CREATE TABLE `wst_log_search_word_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logId` int(11) NOT NULL,
  `goodsId` int(11) DEFAULT '0',
  `sort` tinyint(4) DEFAULT '0',
  `createTime` datetime DEFAULT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;