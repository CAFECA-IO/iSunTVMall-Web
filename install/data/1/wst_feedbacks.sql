SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_feedbacks`;
CREATE TABLE `wst_feedbacks` (
  `feedbackId` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `feedbackType` int(4) DEFAULT '0' COMMENT '反馈类型',
  `feedbackContent` text COMMENT '反馈内容',
  `userId` int(11) DEFAULT NULL COMMENT '反馈者ID',
  `contactInfo` varchar(100) DEFAULT NULL COMMENT '联系方式 手机/qq/微信',
  `createTime` datetime DEFAULT NULL COMMENT '创建时间',
  `dataFlag` tinyint(4) DEFAULT '1' COMMENT '1:有效  -1:删除',
  `feedbackStatus` tinyint(4) DEFAULT '0' COMMENT '处理状态 0:未处理 1:已处理',
  `staffId` int(11) DEFAULT '0' COMMENT '处理人',
  `handleTime` datetime DEFAULT NULL COMMENT '处理时间',
  `handleContent` text COMMENT '处理结果',
  PRIMARY KEY (`feedbackId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
