insert into wst_datas(catId,dataName,dataVal) values(5,'APP版',4);

insert into wst_ad_positions(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort)
values ('4','APP版首页轮播广告','375','188','1','app-ads-index','3000'),
('4','APP版首页中间大广告','171','96','1','app-index-large','3001'),
('4','APP版首页中间小广告','87','110','1','app-index-small','3002'),
('4','APP版首页分层10F顶部广告','375','60','1','app-ads-9','3019'),
('4','APP版店铺街广告','87','110','1','app-ads-street','3020');

create table wst_app_session(
id int unsigned primary key auto_increment,
userId int unsigned not null,
tokenId varchar(32),
startTime datetime,
deviceId varchar(50),
platform tinyint(4) not null default 3 comment '登录来源 3:android 4:ios'
)engine=InnoDb charset=utf8mb4;


insert into wst_payments(payCode,payName,payDesc,payOrder,payConfig,enabled,isOnline,payFor) values('app_weixinpays', '微信支付', 'APP微信支付', '0', null, '0', '1', '4');
update wst_payments set payFor='4' where payCode='app_alipays';
update wst_payments set payFor='4' where payCode='app_weixinpays';

/**2018-04-12**/
INSERT INTO `wst_ad_positions`(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort) VALUES
('4', 'APP版分类1广告', '471', '200', '1', 'app-category-1', '0'),
('4', 'APP版分类2广告', '471', '200', '1', 'app-category-2', '0'),
('4', 'APP版分类3广告', '471', '200', '1', 'app-category-3', '0'),
('4', 'APP版分类4广告', '471', '200', '1', 'app-category-4', '0'),
('4', 'APP版分类5广告', '471', '200', '1', 'app-category-5', '0'),
('4', 'APP版分类6广告', '471', '200', '1', 'app-category-6', '0'),
('4', 'APP版分类7广告', '471', '200', '1', 'app-category-7', '0'),
('4', 'APP版分类8广告', '471', '200', '1', 'app-category-8', '0'),
('4', 'APP版分类9广告', '471', '200', '1', 'app-category-9', '0'),
('4', 'APP版分类10广告', '471', '200', '1', 'app-category-10', '0'),
('4', 'APP版分类11广告', '471', '200', '1', 'app-category-11', '0'),
('4', 'APP版分类12广告', '471', '200', '1', 'app-category-12', '0');



/* 可选页面 */
drop table if exists `wst_app_screens`;
create table `wst_app_screens`(
  `id` int unsigned auto_increment primary key,
  `screenName` varchar(50) not null comment '页面名称',
  `explain` text comment '参数说明'
)engine=InnoDB charset=utf8mb4 comment 'App可供跳转页面表';

insert into `wst_app_screens` values
(null,'自营店铺(SelfShop)','wst://SelfShop'),
(null,'商城分类(GoodsCat)','可选参数:选中左侧第几项(默认选中第一项),示例：wst://GoodsCat?tabSelected=0'),
(null,'品牌街(Brands)','wst://Brands'),
(null,'店铺街(ShopStreet)','可选参数:catId(主营分类id),示例：wst://ShopStreet?catId=52'),
(null,'普通店铺主页(ShopHome)','可选参数:shopId(店铺id),示例：wst://ShopHome?shopId=2'),
(null,'商品详情(GoodsDetail)','必传参数:goodsId(商品id),示例：wst://GoodsDetail?goodsId=3'),
(null,'商品列表(GoodsList)','可选参数:catId(分类id)、goodsName(搜索关键字),示例：wst://GoodsList?catId=47<br>或<br>wst://GoodsList?goodsName=榴莲<br>或<br>wst://GoodsList?catId=47&goodsName=榴莲'),
(null,'我的订单(OrderList)','wst://OrderList'),
(null,'插件-积分商城(Integral)','wst://Integral'),
(null,'插件-团购(Groupon)','wst://Groupon'),
(null,'插件-分销(Distribute)','wst://Distribute'),
(null,'插件-拍卖(Auction)','wst://Auction'),
(null,'插件-优惠券(Coupon)','wst://Coupon'),
(null,'插件-拼团(PinTuan)','wst://PinTuan'),
(null,'插件-砍价(Bargain)','wst://Bargain');

# app端基本导航按钮
insert into `wst_mobile_btns`(`btnName`,`btnSrc`,`btnUrl`,`btnImg`,`addonsName`,`btnSort`)
values
('自营超市','3','wst://SelfShop','static/images/self.png','','0'),
('店铺街','3','wst://ShopStreet','static/images/shopstreet.png','','2'),
('我的订单','3','wst://OrderList','static/images/order.png','','3'),
('品牌街','3','wst://Brands','static/images/brand.png','','1');

/***2019-07-20***/
update `wst_ad_positions` set positionName='app首页商城快讯上方广告' , positionWidth=375 , positionHeight=115 where positionCode='app-index-large';
update `wst_ad_positions` set positionName='app首页横向滚动广告' , positionWidth=125 , positionHeight=105  where positionCode='app-index-small';
insert into `wst_ad_positions`(`positionId`,`positionType`,`positionName`,`positionWidth`,`positionHeight`,`dataFlag`,`positionCode`,`apSort`)
values (null,4,'app首页商城快讯下方左侧(1个)广告',375,402,1,'app-index-left',3001),
	   (null,4,'app首页商城快讯下方(2个)右侧广告',375,201,1,'app-index-right',3001);

INSERT INTO `wst_sys_configs` (fieldName,fieldCode,fieldValue,fieldType)
VALUES
('app图标', 'appLogo',  '', '0'),
('app名称', 'appName',  '', '0'),
('app描述', 'appDesc',  '', '0'),
('android下载地址', 'apkUrl',  '', '0'),
('ios下载地址', 'ipaUrl',  '', '0');

update wst_sys_configs set fieldValue='_m' where fieldCode='wstMobileImgSuffix';

/**2019-10-30**/
update 	wst_ad_positions set positionWidth=750 ,positionHeight=220, positionName='App首页版快讯下方长广告' where positionCode='app-index-large';
update 	wst_ad_positions set positionWidth=286 ,positionHeight=320 where positionCode='app-index-1';
update 	wst_ad_positions set positionWidth=396 ,positionHeight=150 where positionCode='app-index-2';
update 	wst_ad_positions set positionWidth=396 ,positionHeight=150 where positionCode='app-index-3';
update 	wst_ad_positions set positionWidth=702 ,positionHeight=284 where positionCode='app-ads-index';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-1';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-2';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-3';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-4';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-5';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-6';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-7';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-8';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-9';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-10';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-11';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='app-category-12';

INSERT INTO `wst_ad_positions`(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort) VALUES ('4', 'App首页热门活动底部三个广告', '221', '258', '1', 'app-index-three', '1');


ALTER TABLE `wst_shop_configs` ADD `appShopHomeTheme` varchar(200) default 'shop_home';
INSERT INTO `wst_shop_styles`(styleSys,styleName,styleCat,stylePath,screenshot,isShow) VALUES('app', '默认模板', '0', 'shop_home', 'shop_home.png', '1');
INSERT INTO `wst_shop_styles`(styleSys,styleName,styleCat,stylePath,screenshot,isShow) VALUES('app', '楼层模板', '0', 'shop_floor', 'shop_floor.png', '1');

/* 2020-08 增加首页弹出广告 */
INSERT INTO `wst_ad_positions`(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort) VALUES ('4', 'APP版首页弹出广告', '600', '865', '1', 'app-pop-ads', '1');

/* 2020-09 独立app端配置菜单 */
INSERT INTO `wst_menus`(menuId,parentId,menuName,menuSort,dataFlag,menuIcon) VALUES ('110', '0', 'APP', '6', '1','android');
INSERT INTO `wst_menus`(menuId,parentId,menuName,menuSort,dataFlag) VALUES ('111', '110', 'app設置{L}App Settings{L}app设置', '0', '1');
INSERT INTO `wst_menus`(menuId,parentId,menuName,menuSort,dataFlag) VALUES ('112', '111', 'app設置{L}App Settings{L}app设置', '0', '1');
INSERT INTO `wst_privileges`(menuId,privilegeCode,privilegeName,isMenuPrivilege,privilegeUrl,otherPrivilegeUrl,dataFlag,isEnable) VALUES ('111', 'APP_APPGL_00', '查看APP端設置{L}查看APP端設置{L}查看APP端设置', '0', '', '', '1','1');
INSERT INTO `wst_privileges`(menuId,privilegeCode,privilegeName,isMenuPrivilege,privilegeUrl,otherPrivilegeUrl,dataFlag,isEnable) VALUES ('112', 'APP_CONFIG_00', '查看app設置{L}查看app設置{L}查看APP段设置', '1', 'admin/appconfigs/index', '', '1','1');
INSERT INTO `wst_privileges`(menuId,privilegeCode,privilegeName,isMenuPrivilege,privilegeUrl,otherPrivilegeUrl,dataFlag,isEnable) VALUES ('112', 'APP_CONFIG_04', '編輯app設置{L}編輯app設置{L}编辑APP设置', '0', 'admin/appconfigs/edit', '', '1','1');
-- 更新fieldType值
update wst_sys_configs set fieldType='44' where fieldCode in ("appLogo", "appName", "appDesc", "apkUrl", "ipaUrl");
-- app商品详情海报二维码内容
INSERT INTO `wst_sys_configs` (fieldName,fieldCode,fieldValue,fieldType) VALUES ('app端海报二维码内容', 'appShareOpenType',  '2', '44');
-- 唤起app端的scheme
INSERT INTO `wst_sys_configs` (fieldName,fieldCode,fieldValue,fieldType) VALUES ('打开app端的协议', 'appScheme',  'wst://wstmart', '44');
/* 2020-09 app端启动广告 */
INSERT INTO `wst_ad_positions`(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort) VALUES
('4', 'APP版启动广告', '750', '1334', '1', 'app-start-ads', '0');
/**2020-10-13 商家端APP支付**/
insert into wst_payments(payCode,payName,payDesc,payOrder,payConfig,enabled,isOnline,payFor) values('shopapp_weixinpays', '微信支付', '商家端APP微信支付', '0', null, '0', '1', '5');

