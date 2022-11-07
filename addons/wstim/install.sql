

DROP TABLE IF EXISTS `wst_dialogs`;
CREATE TABLE `wst_dialogs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '会话id',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `serviceId` varchar(50) NOT NULL COMMENT '客服id',
  `shopId` int(10) unsigned NOT NULL COMMENT '店铺id',
  `createTime` datetime NOT NULL COMMENT '会话创建时间',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `serviceId` (`serviceId`),
  KEY `shopId` (`shopId`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COMMENT='会话表';

DROP TABLE IF EXISTS `wst_dialog_contents`;
CREATE TABLE `wst_dialog_contents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `dialogId` int(10) unsigned NOT NULL COMMENT '会话id',
  `type` varchar(10) DEFAULT 'chat' COMMENT 'chat 聊天  message留言',
  `content` text COMMENT '会话内容(序列化后)类似 serialize(array("content"=>"发送内容","from"=>"用户id","serviceId"=>"客服id"))',
  `createTime` datetime NOT NULL COMMENT '会话创建时间',
  `isRead` tinyint(4) NOT NULL DEFAULT '0' COMMENT '消息是否已读 1:已读 0:未读',
  PRIMARY KEY (`id`),
  KEY `dialogId` (`dialogId`)
) ENGINE=InnoDB AUTO_INCREMENT=968 DEFAULT CHARSET=utf8mb4 COMMENT='会话内容表';

DROP TABLE IF EXISTS `wst_shop_services`;
CREATE TABLE `wst_shop_services` (
  `shopId` int(11) NOT NULL COMMENT '店铺id',
  `serviceId` varchar(50) NOT NULL COMMENT '客服id',
  `createTime` datetime DEFAULT NULL COMMENT '数据创建时间',
  `dataFlag` tinyint(4) DEFAULT '1' COMMENT '删除标记',
  KEY `shopId` (`shopId`),
  KEY `serviceId` (`serviceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='店铺客服表';


-- 该字加到店铺职员表
alter table `wst_shop_users` add column `serviceId` varchar(50) NOT NULL default '' COMMENT '客服id';




-- 禁用关键词
DROP TABLE IF EXISTS `wst_disable_keywords`;
CREATE TABLE `wst_disable_keywords`(
  `keywords` varchar(180) NOT NULL COMMENT '禁用关键词,多个关键词以,进行分割',
  `createTime` datetime DEFAULT NULL COMMENT '数据创建时间',
  KEY `keywords` (`keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='禁用关键字表';

-- 关键字回复
DROP TABLE IF EXISTS `wst_auto_replys`;
CREATE TABLE `wst_auto_replys`(
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `shopId` int(11) NOT NULL COMMENT '店铺id',
  `keyword` varchar(50) NOT NULL COMMENT '触发的关键字',
  `replyContent` text COMMENT '回复内容',
  `createTime` datetime DEFAULT NULL COMMENT '数据创建时间',
  `dataFlag` tinyint(4) DEFAULT '1' COMMENT '删除标记',
  PRIMARY KEY(`id`),
  KEY `shopId` (`shopId`)
) ENGINE=MyIsam DEFAULT CHARSET=utf8mb4 COMMENT='店铺关键字自动回复表';

-- 删除对话功能
alter table wst_dialogs add column userDel tinyint(4) not null default 0 comment '用户删除对话标识：0:未删除 1:已删除';
alter table wst_dialogs add column shopDel tinyint(4) not null default 0 comment '商家删除对话标识：0:未删除 1:已删除';



-- 客服评分表
DROP TABLE IF EXISTS `wst_service_evaluates`;
CREATE TABLE `wst_service_evaluates`(
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '自增ID',
`userId` INT UNSIGNED NOT NULL COMMENT '用户id',
`serviceId` INT UNSIGNED NOT NULL COMMENT '客服id,为0则表示无客服接待时的评分',
`shopId` INT UNSIGNED NOT NULL COMMENT '店铺id',
`score` TINYINT(4) UNSIGNED NOT NULL COMMENT '评分1-5,1:非常不满意,2:不满意,3:一般,4:满意,5:非常满意',
KEY `shopId` (`shopId`),
KEY `serviceId` (`serviceId`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '客服评分表';

-- 店铺客服咨询统计表
DROP TABLE IF EXISTS `wst_im_chat_statistics`;
CREATE TABLE `wst_im_chat_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `shopId` int(10) unsigned NOT NULL COMMENT '店铺id',
  `startTime` datetime NOT NULL COMMENT '访问时间',
  `ip` varchar(255) NOT NULL COMMENT '访问ip',
  `platform` tinyint(4) NOT NULL COMMENT '访问平台,1:pc 2:手机|微信 3:android 4:ios 5:小程序',
  `stayTime` int(10) unsigned NOT NULL COMMENT '停留时间,单位:秒',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='店铺客服咨询统计表';
