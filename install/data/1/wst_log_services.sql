SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_log_services`;
CREATE TABLE `wst_log_services` (
  `logId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '订单id',
  `serviceId` int(10) unsigned NOT NULL COMMENT '售后id',
  `logContent` varchar(255) DEFAULT '' COMMENT '操作日志',
  `logTargetId` int(10) unsigned NOT NULL COMMENT '操作者Id,如果是商家的话记录商家的ID',
  `logType` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '操作者类型,0：用户  1：商家',
  `logTime` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`logId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单售后日志';
