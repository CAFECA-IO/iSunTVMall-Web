SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_crons`;
CREATE TABLE `wst_crons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cronName` varchar(100) NOT NULL,
  `cronCode` varchar(20) DEFAULT NULL COMMENT '计划任务代码',
  `isEnable` tinyint(4) NOT NULL DEFAULT '0',
  `isRunning` tinyint(4) NOT NULL DEFAULT '0',
  `cronJson` text,
  `cronUrl` varchar(255) NOT NULL,
  `cronDesc` varchar(255) DEFAULT NULL,
  `cronCycle` tinyint(4) NOT NULL DEFAULT '0',
  `cronDay` tinyint(4) DEFAULT '1',
  `cronWeek` tinyint(4) DEFAULT '0',
  `cronHour` tinyint(4) DEFAULT NULL,
  `cronMinute` varchar(255) DEFAULT NULL,
  `runTime` varchar(20) DEFAULT NULL,
  `nextTime` varchar(20) DEFAULT NULL,
  `isRunSuccess` tinyint(4) NOT NULL DEFAULT '1',
  `author` varchar(255) DEFAULT NULL,
  `authorUrl` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_crons` VALUES ('2', '取消未付款订单', 'autoCancelNoPay', '0', '0', 'b:0;', 'admin/CronJobs/autoCancelNoPay.html', '取消超时未付款的订单', '2', '1', '0', '-1', '0,5,10,15,20,25,30,35,40,45,50,55', '2017-03-10 16:05:56', '2017-03-10 16:10:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('3', '自动收货', 'autoReceive', '0', '0', 'b:0;', 'admin/CronJobs/autoReceive.html', '将超时未收货的订单设置为已收货', '2', '1', '0', '0', '0', '2017-03-10 16:05:56', '2017-03-10 00:00:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('4', '自动好评', 'autoAppraise', '0', '0', 'b:0;', 'admin/CronJobs/autoAppraise.html', '将超时未评价的订单设置为好评', '2', '1', '0', '0', '0', '2017-03-10 16:05:56', '2017-03-10 00:00:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('5', '发送队列消息', 'autoSendMsg', '0', '0', 'b:0;', 'admin/CronJobs/autoSendMsg.html', '定时发送队列消息', '2', '1', '0', '-1', '0,5,10,15,20,25,30,35,40,45,50,55', '2017-12-10 16:05:56', '2017-12-10 16:10:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('6', '生成sitemap.xml', 'autoFileXml', '0', '0', 'b:0;', 'admin/CronJobs/autoFileXml.html', '定时生成sitemap.xml文件', '2', '1', '0', '-1', '0,5,10,15,20,25,30,35,40,45,50,55', '2017-12-10 16:05:56', '2017-12-10 16:10:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('7', '处理售后申请', 'autoDealOrderService', '1', '0', 'b:0;', 'admin/CronJobs/autoDealOrderService.html', '处理售后申请', '2', '1', '0', '-1', '0,5,10,15,20,25,30,35,40,45,50,55', '2019-09-24 20:00:01', '2019-09-24 20:05:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('8', '商家订单自动结算', 'autoShopSettlement', '0', '0', '', 'admin/CronJobs/autoShopSettlement.html', '商家订单自动结算', '2', '9', '4', '-1', '0,15,30,45', '2017-03-10 16:05:59', '2017-03-10 16:10:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('9', '清除海报文件', 'clearPoster', '0', '0', 'b:0;', 'admin/CronJobs/clearPoster.html', '定时清除生成的海报文件', '2', '9', '4', '-1', '0,15,30,45', '2017-03-10 16:05:59', '2017-03-10 16:10:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('10', '自动修改店铺状态', 'autoChangeShopStatus', '1', '0', 'b:0;', 'admin/CronJobs/autoChangeShopStatus.html', '自动修改店铺状态', '2', '1', '0', '0', '0', '2019-03-11 00:00:00', '2019-03-12 00:00:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('11', '自动修改供货商状态', 'autoChangeSuppStatus', '1', '0', 'b:0;', 'admin/CronJobs/autoChangeSupplierStatus.html', '自动修改供货商状态', '2', '1', '0', '0', '0', '2019-04-20 00:00:00', '2019-04-21 00:00:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('12', '自动处理商家退款订单', 'autoHandleShopRefund', '1', '0', 'b:0;', 'admin/CronJobs/autoHandleShopRefund.html', '商家逾期未处理退款订单，系统自动同意退款申请', '2', '1', '0', '0', '0', '2020-11-25 00:00:00', '2020-11-26 00:00:00', '1', 'WSTMart', 'http://www.wstmart.net'),
('13', '自动处理供货商退款订单', 'autoHandleSuppRefund', '1', '0', 'b:0;', 'admin/CronJobs/autoHandleSupplierRefund.html', '供货商逾期未处理退款订单，系统自动同意退款申请', '2', '1', '0', '0', '0', '2020-11-25 00:00:00', '2020-11-26 00:00:00', '1', 'WSTMart', 'http://www.wstmart.net');
