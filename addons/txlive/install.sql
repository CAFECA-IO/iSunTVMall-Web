DROP TABLE IF EXISTS `wst_tx_lives`;
CREATE TABLE `wst_tx_lives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  `roomName` varchar(30) DEFAULT NULL,
  `streamName` varchar(50) DEFAULT NULL,
  `coverImg` varchar(255) DEFAULT NULL,
  `liveStatus` tinyint(4) DEFAULT NULL,
  `expireTime` datetime DEFAULT NULL,
  `pushUrl` varchar(255) DEFAULT NULL,
  `rtmpUrl` varchar(255) DEFAULT NULL,
  `flvUrl` varchar(255) DEFAULT NULL,
  `hlsUrl` varchar(255) DEFAULT NULL,
  `visitorNum` int(11) DEFAULT 0,
  `likeNum` int(11) DEFAULT 0,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_tx_live_goods`;
CREATE TABLE `wst_tx_live_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `goodsId` int(11) NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_tx_live_replys`;
CREATE TABLE `wst_tx_live_replays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `startTime` datetime NOT NULL,
  `expireTime` datetime NOT NULL,
  `mediaUrl` varchar(255) DEFAULT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `fileId` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `wst_tx_live_user_login_logs`;
CREATE TABLE `wst_tx_live_user_login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(100) NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `wst_crons`(cronName,cronCode,isEnable,cronJson,cronUrl,cronDesc,cronCycle,cronDay,cronWeek,cronHour,cronMinute,runTime,nextTime,author,authorUrl) VALUES ('获取腾讯云直播间的回放视频', 'getTxLiveReplays', '1', '', 'addon/txlive-cron-getLiveReplays.html', '获取腾讯云直播间的回放视频', '2', '9', '4', '-1', '0,5,10,15,20,25,30,35,40,45,50,55', '2020-07-02 12:00:00', '2017-07-02 12:05:00', 'WSTMart', 'http://www.wstmart.net');


