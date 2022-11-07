INSERT INTO `wst_styles`(styleSys,styleName,styleAuthor,styleShopSite,styleShopId,stylePath,isUse)
 VALUES ('mobile', '默认模板', 'WSTMart', '', '1', 'default', '1');

insert into wst_datas(catId,dataName,dataVal) values(5,'手机版',3);

INSERT INTO `wst_mobile_btns`(btnName,btnSrc,btnUrl,btnImg,addonsName,btnSort) VALUES ('自营超市', '0', 'mshop-1.html', 'static/images/self.png', null, '1');
INSERT INTO `wst_mobile_btns`(btnName,btnSrc,btnUrl,btnImg,addonsName,btnSort) VALUES ('品牌街', '0', 'mbrands.html', 'static/images/brand.png', null, '2');
INSERT INTO `wst_mobile_btns`(btnName,btnSrc,btnUrl,btnImg,addonsName,btnSort) VALUES ('店铺街', '0', 'mstreet', 'static/images/shopstreet.png', null, '3');
INSERT INTO `wst_mobile_btns`(btnName,btnSrc,btnUrl,btnImg,addonsName,btnSort) VALUES ('我的订单', '0', 'mobile/orders/index', 'static/images/order.png', null, '4');

insert into wst_ad_positions(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort) 
values ('3','手机版首页轮播广告','375','188','1','mo-ads-index','2000'),
('3','手机版首页中间大广告','171','96','1','mo-index-large','2001'),
('3','手机版首页中间小广告','87','110','1','mo-index-small','2002'),
('3','手机版店铺街广告','87','110','1','mo-ads-street','2020');

update wst_sys_configs set fieldValue='_m' where fieldCode='wstMobileImgSuffix';

INSERT INTO `wst_ad_positions`(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort) VALUES 
('3', '手机版首页版咨询上方广告', '375', '115', '1', 'mo-index-long', '2001'),
('3', '手机版首页左边一个广告', '187', '201', '1', 'mo-index-left', '2002'),
('3', '	手机版首页右边两个广告', '187', '100', '1', 'mo-index-right', '2002');

update wst_ad_positions set positionWidth='125',positionHeight='105' where positionCode='mo-index-small';
delete a from wst_ads a left join wst_ad_positions ap on ap.positionId = a.adPositionId where ap.positionCode = 'mo-index-large';
delete  FROM `wst_ad_positions` where positionCode = 'mo-index-large';

INSERT INTO `wst_shop_styles` VALUES ('3', 'mobile', '默认模板', '0', 'shop_home', 'img/shop_home.png', '1');
INSERT INTO `wst_shop_styles` VALUES ('4', 'mobile', '楼层模板', '0', 'shop_floor', 'img/shop_floor.png', '1');


/**2018-04-12**/
INSERT INTO `wst_ad_positions`(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort) VALUES 
('3', '手机版分类1广告', '471', '200', '1', 'mo-category-1', '0'),
('3', '手机版分类2广告', '471', '200', '1', 'mo-category-2', '0'),
('3', '手机版分类3广告', '471', '200', '1', 'mo-category-3', '0'),
('3', '手机版分类4广告', '471', '200', '1', 'mo-category-4', '0'),
('3', '手机版分类5广告', '471', '200', '1', 'mo-category-5', '0'),
('3', '手机版分类6广告', '471', '200', '1', 'mo-category-6', '0'),
('3', '手机版分类7广告', '471', '200', '1', 'mo-category-7', '0'),
('3', '手机版分类8广告', '471', '200', '1', 'mo-category-8', '0'),
('3', '手机版分类9广告', '471', '200', '1', 'mo-category-9', '0'),
('3', '手机版分类10广告', '471', '200', '1', 'mo-category-10', '0'),
('3', '手机版分类11广告', '471', '200', '1', 'mo-category-11', '0'),
('3', '手机版分类12广告', '471', '200', '1', 'mo-category-12', '0');

/**2019-10-30**/
update 	wst_ad_positions set positionWidth=750 ,positionHeight=220, positionName='手机版首页版快讯下方长广告' where positionCode='mo-index-long';
update 	wst_ad_positions set positionWidth=286 ,positionHeight=320 where positionCode='mo-index-1';
update 	wst_ad_positions set positionWidth=396 ,positionHeight=150 where positionCode='mo-index-2';
update 	wst_ad_positions set positionWidth=396 ,positionHeight=150 where positionCode='mo-index-3';
update 	wst_ad_positions set positionWidth=702 ,positionHeight=284 where positionCode='mo-ads-index';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-1';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-2';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-3';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-4';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-5';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-6';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-7';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-8';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-9';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-10';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-11';
update 	wst_ad_positions set positionWidth=526 ,positionHeight=240 where positionCode='mo-category-12';

INSERT INTO `wst_ad_positions`(positionType,positionName,positionWidth,positionHeight,dataFlag,positionCode,apSort) VALUES('3', '手机版首页中间三个广告', '221', '258', '1', 'mo-index-three', '0');