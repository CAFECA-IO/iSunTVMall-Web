DROP TABLE IF EXISTS `wst_shop_custom_pages`;
CREATE TABLE `wst_shop_custom_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `pageName` varchar(32) NOT NULL DEFAULT '',
  `isIndex` tinyint(4) NOT NULL DEFAULT '0',
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `pageType` tinyint(4) NOT NULL DEFAULT '1',
  `attr` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
INSERT INTO `wst_shop_custom_pages` VALUES ('1', '1', '手机端自定义店铺首页', '0', '2020-02-05 09:08:51', '1', '1', 'a:3:{s:5:\"title\";s:19:\"WSTMart自营超市\";s:11:\"share_title\";s:0:\"\";s:6:\"poster\";s:57:\"upload/shopcustompagedecoration/2020-02/5e3a16ed31c4e.png\";}');
INSERT INTO `wst_shop_custom_pages` VALUES ('2', '1', '微信端自定义店铺首页', '0', '2020-02-05 09:08:51', '1', '2', 'a:3:{s:5:\"title\";s:19:\"WSTMart自营超市\";s:11:\"share_title\";s:0:\"\";s:6:\"poster\";s:57:\"upload/shopcustompagedecoration/2020-02/5e3a3091592f6.png\";}');
INSERT INTO `wst_shop_custom_pages` VALUES ('3', '1', '小程序端自定义店铺首页', '0', '2020-02-06 15:48:50', '1', '3', 'a:3:{s:5:\"title\";s:19:\"WSTMart自营超市\";s:11:\"share_title\";s:0:\"\";s:6:\"poster\";s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc4e007598.png\";}');
INSERT INTO `wst_shop_custom_pages` VALUES ('4', '1', '电脑端自定义店铺首页', '0', '2020-02-20 09:26:22', '1', '4', 'a:3:{s:5:\"title\";s:19:\"WSTMart自营超市\";s:11:\"share_title\";s:0:\"\";s:6:\"poster\";s:57:\"upload/shopcustompagedecoration/2020-02/5e4ddef2b4c5a.png\";}');

DROP TABLE IF EXISTS `wst_shop_custom_page_decoration`;
CREATE TABLE `wst_shop_custom_page_decoration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageId` int(11) NOT NULL,
  `name` varchar(32) NOT NULL DEFAULT '',
  `attr` text NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `sort` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('1', '1', 'notice', 'a:7:{s:16:\"background_color\";s:7:\"#ffffff\";s:10:\"text_color\";s:7:\"#000000\";s:3:\"img\";s:48:\"/upload/shopcustompagedecoration/base/notice.png\";s:4:\"text\";a:2:{i:0;s:24:\"此处填写公告内容\";i:1;s:24:\"此处填写公告内容\";}s:4:\"link\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:16:\"vertical_padding\";s:1:\"0\";s:9:\"direction\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '0');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('2', '1', 'swiper', 'a:9:{s:14:\"indicator_type\";s:1:\"3\";s:15:\"indicator_color\";s:7:\"#ffffff\";s:8:\"interval\";s:1:\"3\";s:11:\"padding_top\";s:1:\"0\";s:14:\"padding_bottom\";s:1:\"0\";s:12:\"padding_left\";s:1:\"0\";s:13:\"padding_right\";s:1:\"0\";s:4:\"link\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:3:\"img\";a:2:{i:0;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a156db65a9.jpg\";i:1;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a15726afae.jpg\";}}', '2020-02-05 09:08:51', '1', '1');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('3', '1', 'nav', 'a:7:{s:16:\"background_color\";s:7:\"#ffffff\";s:5:\"count\";s:1:\"3\";s:5:\"style\";s:1:\"2\";s:9:\"item_text\";a:10:{i:0;s:12:\"自营超市\";i:1;s:9:\"品牌街\";i:2;s:9:\"店铺街\";i:3;s:12:\"我的订单\";i:4;s:12:\"分销商品\";i:5;s:12:\"秒杀活动\";i:6;s:12:\"团购活动\";i:7;s:12:\"积分商城\";i:8;s:12:\"拍卖活动\";i:9;s:12:\"领券中心\";}s:8:\"item_img\";a:10:{i:0;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a15844795b.png\";i:1;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a1587a467c.png\";i:2;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a158ae69cf.png\";i:3;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a158e002fe.png\";i:4;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a1591f1f12.png\";i:5;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a15954234e.png\";i:6;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a1598b57d4.png\";i:7;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a159bbfc92.png\";i:8;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a159e83470.png\";i:9;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a15a1746d0.png\";}s:9:\"item_link\";a:10:{i:0;s:7:\"mshop-1\";i:1;s:7:\"mbrands\";i:2;s:7:\"mstreet\";i:3;s:19:\"mobile/orders/index\";i:4;s:46:\"addon/distribut-distribut-mobiledistributgoods\";i:5;s:27:\"addon/seckill-goods-molists\";i:6;s:27:\"addon/groupon-goods-molists\";i:7;s:28:\"addon/integral-goods-molists\";i:8;s:27:\"addon/auction-goods-molists\";i:9;s:28:\"addon/coupon-coupons-moindex\";}s:10:\"item_color\";a:10:{i:0;s:7:\"#666666\";i:1;s:7:\"#666666\";i:2;s:7:\"#666666\";i:3;s:7:\"#666666\";i:4;s:7:\"#666666\";i:5;s:7:\"#666666\";i:6;s:7:\"#666666\";i:7;s:7:\"#666666\";i:8;s:7:\"#666666\";i:9;s:7:\"#666666\";}}', '2020-02-05 09:08:51', '1', '2');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('4', '1', 'goods_swiper', 'a:10:{s:10:\"goods_nums\";s:1:\"4\";s:5:\"title\";s:12:\"商家推荐\";s:8:\"title_bg\";s:1:\"1\";s:12:\"goods_select\";s:1:\"1\";s:16:\"goods_select_ids\";s:0:\"\";s:20:\"goods_select_cats_id\";s:1:\"1\";s:9:\"goods_tag\";s:0:\"\";s:15:\"goods_min_price\";s:0:\"\";s:15:\"goods_max_price\";s:0:\"\";s:11:\"goods_order\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '3');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('5', '1', 'goods_swiper', 'a:10:{s:10:\"goods_nums\";s:1:\"4\";s:5:\"title\";s:12:\"热卖商品\";s:8:\"title_bg\";s:1:\"2\";s:12:\"goods_select\";s:1:\"1\";s:16:\"goods_select_ids\";s:0:\"\";s:20:\"goods_select_cats_id\";s:1:\"1\";s:9:\"goods_tag\";s:0:\"\";s:15:\"goods_min_price\";s:0:\"\";s:15:\"goods_max_price\";s:0:\"\";s:11:\"goods_order\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '4');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('6', '1', 'goods_group', 'a:17:{s:16:\"background_color\";s:7:\"#ffffff\";s:15:\"show_goods_name\";s:1:\"1\";s:16:\"show_goods_price\";s:1:\"1\";s:16:\"show_praise_rate\";s:1:\"1\";s:13:\"show_sale_num\";s:1:\"1\";s:18:\"show_columns_title\";s:1:\"1\";s:7:\"columns\";s:1:\"2\";s:9:\"goods_cat\";N;s:10:\"goods_nums\";s:1:\"6\";s:13:\"columns_title\";a:3:{i:0;s:13:\"列表名称1\";i:1;s:13:\"列表名称2\";i:2;s:13:\"列表名称3\";}s:12:\"goods_select\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"1\";i:2;s:1:\"1\";}s:16:\"goods_select_ids\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:20:\"goods_select_cats_id\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"1\";i:2;s:1:\"1\";}s:9:\"goods_tag\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:15:\"goods_min_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:15:\"goods_max_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:11:\"goods_order\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '5');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('7', '2', 'notice', 'a:7:{s:16:\"background_color\";s:7:\"#ffffff\";s:10:\"text_color\";s:7:\"#000000\";s:3:\"img\";s:48:\"/upload/shopcustompagedecoration/base/notice.png\";s:4:\"text\";a:2:{i:0;s:24:\"此处填写公告内容\";i:1;s:24:\"此处填写公告内容\";}s:4:\"link\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:16:\"vertical_padding\";s:1:\"0\";s:9:\"direction\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '0');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('8', '2', 'swiper', 'a:9:{s:14:\"indicator_type\";s:1:\"3\";s:15:\"indicator_color\";s:7:\"#ffffff\";s:8:\"interval\";s:1:\"3\";s:11:\"padding_top\";s:1:\"0\";s:14:\"padding_bottom\";s:1:\"0\";s:12:\"padding_left\";s:1:\"0\";s:13:\"padding_right\";s:1:\"0\";s:4:\"link\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:3:\"img\";a:2:{i:0;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a3098248ba.jpg\";i:1;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a309acebfd.jpg\";}}', '2020-02-05 11:04:36', '1', '1');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('9', '2', 'nav', 'a:7:{s:16:\"background_color\";s:7:\"#ffffff\";s:5:\"count\";s:1:\"3\";s:5:\"style\";s:1:\"2\";s:9:\"item_text\";a:12:{i:0;s:12:\"自营超市\";i:1;s:9:\"品牌街\";i:2;s:9:\"店铺街\";i:3;s:12:\"我的订单\";i:4;s:12:\"分销商品\";i:5;s:12:\"秒杀活动\";i:6;s:12:\"团购活动\";i:7;s:6:\"拼团\";i:8;s:12:\"积分商城\";i:9;s:12:\"拍卖活动\";i:10;s:12:\"领券中心\";i:11;s:12:\"全民砍价\";}s:8:\"item_img\";a:12:{i:0;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30a1315e0.png\";i:1;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30a359314.png\";i:2;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30a5e9070.png\";i:3;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30a9021cf.png\";i:4;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30abda764.png\";i:5;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30aebc790.png\";i:6;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30b16d2a1.png\";i:7;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30b4b191d.png\";i:8;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30b7b7f5a.png\";i:9;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30bb7329f.png\";i:10;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30bed03a8.png\";i:11;s:57:\"upload/shopcustompagedecoration/2020-02/5e3a30c24fdbf.png\";}s:9:\"item_link\";a:12:{i:0;s:18:\"wechat/shops/index\";i:1;s:19:\"wechat/brands/index\";i:2;s:23:\"wechat/shops/shopstreet\";i:3;s:19:\"wechat/orders/index\";i:4;s:46:\"addon/distribut-distribut-wechatdistributgoods\";i:5;s:27:\"addon/seckill-goods-wxlists\";i:6;s:27:\"addon/groupon-goods-wxlists\";i:7;s:27:\"addon/pintuan-goods-wxlists\";i:8;s:28:\"addon/integral-goods-wxlists\";i:9;s:27:\"addon/auction-goods-wxlists\";i:10;s:28:\"addon/coupon-coupons-wxindex\";i:11;s:27:\"addon/bargain-goods-wxlists\";}s:10:\"item_color\";a:12:{i:0;s:7:\"#666666\";i:1;s:7:\"#666666\";i:2;s:7:\"#666666\";i:3;s:7:\"#666666\";i:4;s:7:\"#666666\";i:5;s:7:\"#666666\";i:6;s:7:\"#666666\";i:7;s:7:\"#666666\";i:8;s:7:\"#666666\";i:9;s:7:\"#666666\";i:10;s:7:\"#666666\";i:11;s:7:\"#666666\";}}', '2020-02-05 11:04:36', '1', '2');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('10', '2', 'goods_swiper', 'a:10:{s:10:\"goods_nums\";s:1:\"4\";s:5:\"title\";s:12:\"商家推荐\";s:8:\"title_bg\";s:1:\"1\";s:12:\"goods_select\";s:1:\"1\";s:16:\"goods_select_ids\";s:0:\"\";s:20:\"goods_select_cats_id\";s:1:\"1\";s:9:\"goods_tag\";s:0:\"\";s:15:\"goods_min_price\";s:0:\"\";s:15:\"goods_max_price\";s:0:\"\";s:11:\"goods_order\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '3');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('11', '2', 'goods_swiper', 'a:10:{s:10:\"goods_nums\";s:1:\"4\";s:5:\"title\";s:12:\"热卖商品\";s:8:\"title_bg\";s:1:\"2\";s:12:\"goods_select\";s:1:\"1\";s:16:\"goods_select_ids\";s:0:\"\";s:20:\"goods_select_cats_id\";s:1:\"1\";s:9:\"goods_tag\";s:0:\"\";s:15:\"goods_min_price\";s:0:\"\";s:15:\"goods_max_price\";s:0:\"\";s:11:\"goods_order\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '4');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('12', '2', 'goods_group', 'a:17:{s:16:\"background_color\";s:7:\"#ffffff\";s:15:\"show_goods_name\";s:1:\"1\";s:16:\"show_goods_price\";s:1:\"1\";s:16:\"show_praise_rate\";s:1:\"1\";s:13:\"show_sale_num\";s:1:\"1\";s:18:\"show_columns_title\";s:1:\"1\";s:7:\"columns\";s:1:\"2\";s:9:\"goods_cat\";N;s:10:\"goods_nums\";s:1:\"6\";s:13:\"columns_title\";a:3:{i:0;s:13:\"列表名称1\";i:1;s:13:\"列表名称2\";i:2;s:13:\"列表名称3\";}s:12:\"goods_select\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"1\";i:2;s:1:\"1\";}s:16:\"goods_select_ids\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:20:\"goods_select_cats_id\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"1\";i:2;s:1:\"1\";}s:9:\"goods_tag\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:15:\"goods_min_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:15:\"goods_max_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:11:\"goods_order\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '5');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('13', '3', 'notice', 'a:7:{s:16:\"background_color\";s:7:\"#ffffff\";s:10:\"text_color\";s:7:\"#000000\";s:3:\"img\";s:48:\"/upload/shopcustompagedecoration/base/notice.png\";s:4:\"text\";a:2:{i:0;s:24:\"此处填写公告内容\";i:1;s:24:\"此处填写公告内容\";}s:4:\"link\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:16:\"vertical_padding\";s:1:\"0\";s:9:\"direction\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '0');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('14', '3', 'swiper', 'a:9:{s:14:\"indicator_type\";s:1:\"3\";s:15:\"indicator_color\";s:7:\"#ffffff\";s:8:\"interval\";s:1:\"3\";s:11:\"padding_top\";s:1:\"0\";s:14:\"padding_bottom\";s:1:\"0\";s:12:\"padding_left\";s:1:\"0\";s:13:\"padding_right\";s:1:\"0\";s:4:\"link\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:3:\"img\";a:2:{i:0;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc45ec91a2.jpg\";i:1;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc462097f2.jpg\";}}', '2020-02-06 15:48:50', '1', '1');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('15', '3', 'nav', 'a:7:{s:16:\"background_color\";s:7:\"#ffffff\";s:5:\"count\";s:1:\"3\";s:5:\"style\";s:1:\"2\";s:9:\"item_text\";a:12:{i:0;s:12:\"自营超市\";i:1;s:9:\"品牌街\";i:2;s:9:\"店铺街\";i:3;s:12:\"我的订单\";i:4;s:12:\"分销商品\";i:5;s:12:\"秒杀活动\";i:6;s:12:\"团购活动\";i:7;s:6:\"拼团\";i:8;s:12:\"积分商城\";i:9;s:12:\"拍卖活动\";i:10;s:12:\"领券中心\";i:11;s:12:\"全民砍价\";}s:8:\"item_img\";a:12:{i:0;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc47a87924.png\";i:1;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc48035dd0.png\";i:2;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc4835f696.png\";i:3;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc4865fb29.png\";i:4;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc48a3f096.png\";i:5;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc48d91d8d.png\";i:6;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc4908d018.png\";i:7;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc493cdfe3.png\";i:8;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc49709fe2.png\";i:9;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc49a572ff.png\";i:10;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc49e2a131.png\";i:11;s:57:\"upload/shopcustompagedecoration/2020-02/5e3bc4a196856.png\";}s:9:\"item_link\";a:12:{i:0;s:26:\"/pages/shop-self/shop-self\";i:1;s:20:\"/pages/brands/brands\";i:2;s:30:\"/pages/shop-street/shop-street\";i:3;s:26:\"/pages/users/orders/orders\";i:4;s:52:\"/pages/addons/package/pages/distribut/goods/listoods\";i:5;s:46:\"/pages/addons/package/pages/seckill/goods/list\";i:6;s:46:\"/pages/addons/package/pages/groupon/goods/list\";i:7;s:46:\"/pages/addons/package/pages/pintuan/goods/list\";i:8;s:47:\"/pages/addons/package/pages/integral/goods/list\";i:9;s:46:\"/pages/addons/package/pages/auction/goods/list\";i:10;s:41:\"/pages/addons/package/pages/coupon/coupon\";i:11;s:46:\"/pages/addons/package/pages/bargain/goods/list\";}s:10:\"item_color\";a:12:{i:0;s:7:\"#666666\";i:1;s:7:\"#666666\";i:2;s:7:\"#666666\";i:3;s:7:\"#666666\";i:4;s:7:\"#666666\";i:5;s:7:\"#666666\";i:6;s:7:\"#666666\";i:7;s:7:\"#666666\";i:8;s:7:\"#666666\";i:9;s:7:\"#666666\";i:10;s:7:\"#666666\";i:11;s:7:\"#666666\";}}', '2020-02-06 16:37:10', '1', '2');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('16', '3', 'goods_swiper', 'a:10:{s:10:\"goods_nums\";s:1:\"4\";s:5:\"title\";s:12:\"商家推荐\";s:8:\"title_bg\";s:1:\"1\";s:12:\"goods_select\";s:1:\"1\";s:16:\"goods_select_ids\";s:0:\"\";s:20:\"goods_select_cats_id\";s:1:\"1\";s:9:\"goods_tag\";s:0:\"\";s:15:\"goods_min_price\";s:0:\"\";s:15:\"goods_max_price\";s:0:\"\";s:11:\"goods_order\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '3');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('17', '3', 'goods_swiper', 'a:10:{s:10:\"goods_nums\";s:1:\"4\";s:5:\"title\";s:12:\"热卖商品\";s:8:\"title_bg\";s:1:\"2\";s:12:\"goods_select\";s:1:\"1\";s:16:\"goods_select_ids\";s:0:\"\";s:20:\"goods_select_cats_id\";s:1:\"1\";s:9:\"goods_tag\";s:0:\"\";s:15:\"goods_min_price\";s:0:\"\";s:15:\"goods_max_price\";s:0:\"\";s:11:\"goods_order\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '4');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('18', '3', 'goods_group', 'a:17:{s:16:\"background_color\";s:7:\"#ffffff\";s:15:\"show_goods_name\";s:1:\"1\";s:16:\"show_goods_price\";s:1:\"1\";s:16:\"show_praise_rate\";s:1:\"1\";s:13:\"show_sale_num\";s:1:\"1\";s:18:\"show_columns_title\";s:1:\"1\";s:7:\"columns\";s:1:\"2\";s:9:\"goods_cat\";N;s:10:\"goods_nums\";s:1:\"6\";s:13:\"columns_title\";a:3:{i:0;s:13:\"列表名称1\";i:1;s:13:\"列表名称2\";i:2;s:13:\"列表名称3\";}s:12:\"goods_select\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"1\";i:2;s:1:\"1\";}s:16:\"goods_select_ids\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:20:\"goods_select_cats_id\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"1\";i:2;s:1:\"1\";}s:9:\"goods_tag\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:15:\"goods_min_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:15:\"goods_max_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:11:\"goods_order\";s:1:\"1\";}', '2020-02-05 09:08:51', '1', '5');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('19', '4', 'nav', 'a:6:{s:17:\"show_nav_category\";s:1:\"1\";s:15:\"show_nav_button\";s:1:\"1\";s:16:\"background_color\";s:7:\"#ff6a53\";s:9:\"item_text\";a:6:{i:0;s:12:\"店铺分类\";i:1;s:12:\"店铺分类\";i:2;s:12:\"店铺分类\";i:3;s:12:\"店铺分类\";i:4;s:12:\"店铺分类\";i:5;s:12:\"店铺分类\";}s:9:\"item_link\";a:6:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";}s:10:\"item_color\";a:6:{i:0;s:7:\"#ffffff\";i:1;s:7:\"#ffffff\";i:2;s:7:\"#ffffff\";i:3;s:7:\"#ffffff\";i:4;s:7:\"#ffffff\";i:5;s:7:\"#ffffff\";}}', '2020-02-20 09:26:22', '1', '1');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('20', '4', 'swiper', 'a:9:{s:14:\"indicator_type\";s:1:\"3\";s:15:\"indicator_color\";s:7:\"#e13335\";s:8:\"interval\";s:1:\"3\";s:11:\"padding_top\";s:1:\"0\";s:14:\"padding_bottom\";s:1:\"0\";s:12:\"padding_left\";s:1:\"0\";s:13:\"padding_right\";s:1:\"0\";s:4:\"link\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:3:\"img\";a:2:{i:0;s:57:\"upload/shopcustompagedecoration/2020-02/5e4ddf3632dad.png\";i:1;s:57:\"upload/shopcustompagedecoration/2020-02/5e4ddf3cd6c43.png\";}}', '2020-02-20 09:26:22', '1', '2');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('21', '4', 'goods_recommend', 'a:10:{s:16:\"background_color\";s:7:\"#ffe9dc\";s:21:\"goods_recommend_title\";s:12:\"店长推荐\";s:26:\"show_goods_recommend_title\";s:1:\"1\";s:12:\"goods_select\";s:1:\"1\";s:16:\"goods_select_ids\";s:0:\"\";s:20:\"goods_select_cats_id\";s:1:\"1\";s:9:\"goods_tag\";s:0:\"\";s:15:\"goods_min_price\";s:0:\"\";s:15:\"goods_max_price\";s:0:\"\";s:11:\"goods_order\";s:1:\"1\";}', '2020-02-20 09:26:22', '1', '3');
INSERT INTO `wst_shop_custom_page_decoration` VALUES ('22', '4', 'floor_goods', 'a:14:{s:16:\"background_color\";s:7:\"#ffffff\";s:5:\"title\";s:12:\"楼层标题\";s:4:\"icon\";s:52:\"/upload/shopcustompagedecoration/base/floor_icon.png\";s:3:\"img\";s:57:\"upload/shopcustompagedecoration/2020-02/5e4ddf9f6c7a0.png\";s:4:\"link\";s:0:\"\";s:13:\"columns_title\";a:3:{i:0;s:13:\"列表名称1\";i:1;s:13:\"列表名称2\";i:2;s:13:\"列表名称3\";}s:12:\"columns_link\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:12:\"goods_select\";s:1:\"1\";s:16:\"goods_select_ids\";s:0:\"\";s:20:\"goods_select_cats_id\";s:1:\"1\";s:9:\"goods_tag\";s:0:\"\";s:15:\"goods_min_price\";s:0:\"\";s:15:\"goods_max_price\";s:0:\"\";s:11:\"goods_order\";s:1:\"1\";}', '2020-02-20 09:26:22', '1', '4');



