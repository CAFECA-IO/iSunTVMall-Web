SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS `wst_custom_page_decoration`;
CREATE TABLE `wst_custom_page_decoration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageId` int(11) NOT NULL,
  `name` varchar(32) NOT NULL DEFAULT '',
  `attr` text NOT NULL,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `sort` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;


INSERT INTO `wst_custom_page_decoration` VALUES ('1', '1', 'swiper', 'a:9:{s:14:\"indicator_type\";s:1:\"3\";s:15:\"indicator_color\";s:7:\"#ffffff\";s:8:\"interval\";s:1:\"3\";s:11:\"padding_top\";s:1:\"0\";s:14:\"padding_bottom\";s:1:\"0\";s:12:\"padding_left\";s:1:\"0\";s:13:\"padding_right\";s:1:\"0\";s:4:\"link\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:3:\"img\";a:2:{i:0;s:53:\"upload/custompagedecoration/2019-09/5d8983704d0d0.jpg\";i:1;s:53:\"upload/custompagedecoration/2019-09/5d8983774f7e0.jpg\";}}', '2019-09-25 15:17:05', '1', '1'),
('2', '1', 'nav', 'a:7:{s:16:\"background_color\";s:7:\"#ffffff\";s:5:\"count\";s:1:\"3\";s:5:\"style\";s:1:\"2\";s:9:\"item_text\";a:10:{i:0;s:12:\"自营超市\";i:1;s:9:\"品牌街\";i:2;s:9:\"店铺街\";i:3;s:12:\"我的订单\";i:4;s:12:\"分销商品\";i:5;s:12:\"秒杀活动\";i:6;s:12:\"团购活动\";i:7;s:12:\"积分商城\";i:8;s:12:\"拍卖活动\";i:9;s:12:\"领券中心\";}s:8:\"item_img\";a:10:{i:0;s:53:\"upload/custompagedecoration/2019-09/5d8983c57e1f8.png\";i:1;s:53:\"upload/custompagedecoration/2019-09/5d8983cc8a548.png\";i:2;s:53:\"upload/custompagedecoration/2019-09/5d8983e625800.png\";i:3;s:53:\"upload/custompagedecoration/2019-09/5d8983ebe4e80.png\";i:4;s:53:\"upload/custompagedecoration/2019-09/5d89840056928.png\";i:5;s:53:\"upload/custompagedecoration/2019-09/5d8985c31bfa8.png\";i:6;s:53:\"upload/custompagedecoration/2019-09/5d8985c9b2db8.png\";i:7;s:53:\"upload/custompagedecoration/2019-09/5d8985d0da688.png\";i:8;s:53:\"upload/custompagedecoration/2019-09/5d8985d8aeb50.png\";i:9;s:53:\"upload/custompagedecoration/2019-09/5d8985dfcc7e0.png\";}s:9:\"item_link\";a:10:{i:0;s:7:\"mshop-1\";i:1;s:7:\"mbrands\";i:2;s:7:\"mstreet\";i:3;s:19:\"mobile/orders/index\";i:4;s:46:\"addon/distribut-distribut-mobiledistributgoods\";i:5;s:27:\"addon/seckill-goods-molists\";i:6;s:27:\"addon/groupon-goods-molists\";i:7;s:28:\"addon/integral-goods-molists\";i:8;s:27:\"addon/auction-goods-molists\";i:9;s:28:\"addon/coupon-coupons-moindex\";}s:10:\"item_color\";a:10:{i:0;s:7:\"#666666\";i:1;s:7:\"#666666\";i:2;s:7:\"#666666\";i:3;s:7:\"#666666\";i:4;s:7:\"#666666\";i:5;s:7:\"#666666\";i:6;s:7:\"#666666\";i:7;s:7:\"#666666\";i:8;s:7:\"#666666\";i:9;s:7:\"#666666\";}}', '2019-09-25 15:17:05', '1', '2'),
('3', '1', 'image', 'a:7:{s:4:\"link\";a:1:{i:0;s:0:\"\";}s:3:\"img\";a:1:{i:0;s:53:\"upload/custompagedecoration/2019-09/5d8983b083bd0.png\";}s:11:\"padding_top\";s:1:\"0\";s:14:\"padding_bottom\";s:1:\"0\";s:12:\"padding_left\";s:1:\"0\";s:13:\"padding_right\";s:1:\"0\";s:16:\"background_color\";s:7:\"#ffffff\";}', '2019-09-25 15:17:05', '1', '3'),
('4', '1', 'tabbar', 'a:10:{s:16:\"background_color\";s:7:\"#ffffff\";s:12:\"border_color\";s:7:\"#ffffff\";s:10:\"text_color\";s:7:\"#2c2c2c\";s:18:\"text_checked_color\";s:7:\"#df0202\";s:4:\"icon\";a:5:{i:0;s:48:\"upload/custompagedecoration/base/footer-home.png\";i:1;s:52:\"upload/custompagedecoration/base/footer-classify.png\";i:2;s:48:\"upload/custompagedecoration/base/footer-cart.png\";i:3;s:50:\"upload/custompagedecoration/base/footer-follow.png\";i:4;s:48:\"upload/custompagedecoration/base/footer-user.png\";}s:11:\"select_icon\";a:5:{i:0;s:49:\"upload/custompagedecoration/base/footer-home2.png\";i:1;s:53:\"upload/custompagedecoration/base/footer-classify2.png\";i:2;s:49:\"upload/custompagedecoration/base/footer-cart2.png\";i:3;s:51:\"upload/custompagedecoration/base/footer-follow2.png\";i:4;s:49:\"upload/custompagedecoration/base/footer-user2.png\";}s:4:\"text\";a:5:{i:0;s:6:\"主页\";i:1;s:6:\"分类\";i:2;s:9:\"购物车\";i:3;s:6:\"关注\";i:4;s:6:\"我的\";}s:4:\"link\";a:5:{i:0;s:18:\"mobile/index/index\";i:1;s:22:\"mobile/goodscats/index\";i:2;s:18:\"mobile/carts/index\";i:3;s:22:\"mobile/favorites/goods\";i:4;s:18:\"mobile/users/index\";}s:9:\"link_text\";a:5:{i:0;s:6:\"首页\";i:1;s:6:\"分类\";i:2;s:9:\"购物车\";i:3;s:6:\"关注\";i:4;s:6:\"我的\";}s:9:\"menu_flag\";a:5:{i:0;s:5:\"index\";i:1;s:8:\"category\";i:2;s:4:\"cart\";i:3;s:8:\"favorite\";i:4;s:4:\"user\";}}', '2019-09-25 15:17:05', '1', '5'),
('5', '1', 'goods_group', 'a:17:{s:16:\"background_color\";s:7:\"#ffffff\";s:15:\"show_goods_name\";s:1:\"1\";s:16:\"show_goods_price\";s:1:\"1\";s:16:\"show_praise_rate\";s:1:\"1\";s:13:\"show_sale_num\";s:1:\"1\";s:18:\"show_columns_title\";s:1:\"1\";s:7:\"columns\";s:1:\"2\";s:9:\"goods_cat\";N;s:10:\"goods_nums\";s:1:\"6\";s:13:\"columns_title\";a:3:{i:0;s:12:\"时蔬水果\";i:1;s:12:\"酒水饮料\";i:2;s:12:\"粮油食品\";}s:12:\"goods_select\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"1\";i:2;s:1:\"1\";}s:16:\"goods_select_ids\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:20:\"goods_select_cats_id\";a:3:{i:0;s:2:\"47\";i:1;s:2:\"49\";i:2;s:2:\"50\";}s:9:\"goods_tag\";a:3:{i:0;s:0:\"\";i:1;s:1:\"0\";i:2;s:1:\"0\";}s:15:\"goods_min_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:15:\"goods_max_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:11:\"goods_order\";s:1:\"1\";}', '2019-09-25 15:17:05', '1', '4'),
('6', '2', 'swiper', 'a:9:{s:14:\"indicator_type\";s:1:\"3\";s:15:\"indicator_color\";s:7:\"#ffffff\";s:8:\"interval\";s:1:\"3\";s:11:\"padding_top\";s:1:\"0\";s:14:\"padding_bottom\";s:1:\"0\";s:12:\"padding_left\";s:1:\"0\";s:13:\"padding_right\";s:1:\"0\";s:4:\"link\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:3:\"img\";a:2:{i:0;s:53:\"upload/custompagedecoration/2019-09/5d8b101891c08.jpg\";i:1;s:53:\"upload/custompagedecoration/2019-09/5d8b102064960.jpg\";}}', '2019-09-25 15:13:13', '1', '1'),
('7', '2', 'goods_group', 'a:17:{s:16:\"background_color\";s:7:\"#ffffff\";s:15:\"show_goods_name\";s:1:\"1\";s:16:\"show_goods_price\";s:1:\"1\";s:16:\"show_praise_rate\";s:1:\"1\";s:13:\"show_sale_num\";s:1:\"1\";s:18:\"show_columns_title\";s:1:\"1\";s:7:\"columns\";s:1:\"2\";s:9:\"goods_cat\";N;s:10:\"goods_nums\";s:1:\"6\";s:13:\"columns_title\";a:3:{i:0;s:12:\"时蔬水果\";i:1;s:12:\"酒水饮料\";i:2;s:12:\"精油食品\";}s:12:\"goods_select\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"1\";i:2;s:1:\"1\";}s:16:\"goods_select_ids\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:20:\"goods_select_cats_id\";a:3:{i:0;s:2:\"47\";i:1;s:2:\"49\";i:2;s:2:\"50\";}s:9:\"goods_tag\";a:3:{i:0;s:1:\"0\";i:1;s:1:\"0\";i:2;s:1:\"0\";}s:15:\"goods_min_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:15:\"goods_max_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:11:\"goods_order\";s:1:\"1\";}', '2019-09-25 15:13:13', '1', '4'),
('8', '2', 'nav', 'a:7:{s:16:\"background_color\";s:7:\"#ffffff\";s:5:\"count\";s:1:\"3\";s:5:\"style\";s:1:\"2\";s:9:\"item_text\";a:12:{i:0;s:12:\"自营超市\";i:1;s:9:\"品牌街\";i:2;s:9:\"店铺街\";i:3;s:12:\"我的订单\";i:4;s:12:\"分销商品\";i:5;s:12:\"秒杀活动\";i:6;s:12:\"团购活动\";i:7;s:6:\"拼团\";i:8;s:12:\"积分商城\";i:9;s:12:\"拍卖活动\";i:10;s:12:\"领券中心\";i:11;s:12:\"全民砍价\";}s:8:\"item_img\";a:12:{i:0;s:53:\"upload/custompagedecoration/2019-09/5d8b1080b0450.png\";i:1;s:53:\"upload/custompagedecoration/2019-09/5d8b108620b70.png\";i:2;s:53:\"upload/custompagedecoration/2019-09/5d8b10a450528.png\";i:3;s:53:\"upload/custompagedecoration/2019-09/5d8b10c911940.png\";i:4;s:53:\"upload/custompagedecoration/2019-09/5d8b10ddecd10.png\";i:5;s:53:\"upload/custompagedecoration/2019-09/5d8b10f84f588.png\";i:6;s:53:\"upload/custompagedecoration/2019-09/5d8b118726930.png\";i:7;s:53:\"upload/custompagedecoration/2019-09/5d8b119fbf298.png\";i:8;s:53:\"upload/custompagedecoration/2019-09/5d8b11b0493e0.png\";i:9;s:53:\"upload/custompagedecoration/2019-09/5d8b11c9a21c0.png\";i:10;s:53:\"upload/custompagedecoration/2019-09/5d8b120275eb8.png\";i:11;s:53:\"upload/custompagedecoration/2019-09/5d8b12157b890.png\";}s:9:\"item_link\";a:12:{i:0;s:18:\"wechat/shops/index\";i:1;s:19:\"wechat/brands/index\";i:2;s:23:\"wechat/shops/shopstreet\";i:3;s:19:\"wechat/orders/index\";i:4;s:46:\"addon/distribut-distribut-wechatdistributgoods\";i:5;s:27:\"addon/seckill-goods-wxlists\";i:6;s:27:\"addon/groupon-goods-wxlists\";i:7;s:27:\"addon/pintuan-goods-wxlists\";i:8;s:28:\"addon/integral-goods-wxlists\";i:9;s:27:\"addon/auction-goods-wxlists\";i:10;s:28:\"addon/coupon-coupons-wxindex\";i:11;s:27:\"addon/bargain-goods-wxlists\";}s:10:\"item_color\";a:12:{i:0;s:7:\"#666666\";i:1;s:7:\"#666666\";i:2;s:7:\"#666666\";i:3;s:7:\"#666666\";i:4;s:7:\"#666666\";i:5;s:7:\"#666666\";i:6;s:7:\"#666666\";i:7;s:7:\"#666666\";i:8;s:7:\"#666666\";i:9;s:7:\"#666666\";i:10;s:7:\"#666666\";i:11;s:7:\"#666666\";}}', '2019-09-25 15:13:13', '1', '2'),
('9', '2', 'image', 'a:7:{s:4:\"link\";a:1:{i:0;s:0:\"\";}s:3:\"img\";a:1:{i:0;s:53:\"upload/custompagedecoration/2019-09/5d8b12f277df8.png\";}s:11:\"padding_top\";s:1:\"0\";s:14:\"padding_bottom\";s:1:\"0\";s:12:\"padding_left\";s:1:\"0\";s:13:\"padding_right\";s:1:\"0\";s:16:\"background_color\";s:7:\"#ffffff\";}', '2019-09-25 15:13:13', '1', '3'),
('10', '2', 'tabbar', 'a:10:{s:16:\"background_color\";s:7:\"#ffffff\";s:12:\"border_color\";s:7:\"#ffffff\";s:10:\"text_color\";s:7:\"#2c2c2c\";s:18:\"text_checked_color\";s:7:\"#df0202\";s:4:\"icon\";a:5:{i:0;s:48:\"upload/custompagedecoration/base/footer-home.png\";i:1;s:52:\"upload/custompagedecoration/base/footer-classify.png\";i:2;s:48:\"upload/custompagedecoration/base/footer-cart.png\";i:3;s:50:\"upload/custompagedecoration/base/footer-follow.png\";i:4;s:48:\"upload/custompagedecoration/base/footer-user.png\";}s:11:\"select_icon\";a:5:{i:0;s:49:\"upload/custompagedecoration/base/footer-home2.png\";i:1;s:53:\"upload/custompagedecoration/base/footer-classify2.png\";i:2;s:49:\"upload/custompagedecoration/base/footer-cart2.png\";i:3;s:51:\"upload/custompagedecoration/base/footer-follow2.png\";i:4;s:49:\"upload/custompagedecoration/base/footer-user2.png\";}s:4:\"text\";a:5:{i:0;s:6:\"主页\";i:1;s:6:\"分类\";i:2;s:9:\"购物车\";i:3;s:6:\"关注\";i:4;s:6:\"我的\";}s:4:\"link\";a:5:{i:0;s:18:\"wechat/index/index\";i:1;s:22:\"wechat/goodscats/index\";i:2;s:18:\"wechat/carts/index\";i:3;s:22:\"wechat/favorites/goods\";i:4;s:18:\"wechat/users/index\";}s:9:\"link_text\";a:5:{i:0;s:6:\"首页\";i:1;s:6:\"分类\";i:2;s:9:\"购物车\";i:3;s:6:\"关注\";i:4;s:6:\"我的\";}s:9:\"menu_flag\";a:5:{i:0;s:5:\"index\";i:1;s:8:\"category\";i:2;s:4:\"cart\";i:3;s:8:\"favorite\";i:4;s:4:\"user\";}}', '2019-09-25 15:13:13', '1', '5'),
('11', '3', 'swiper', 'a:9:{s:14:\"indicator_type\";s:1:\"3\";s:15:\"indicator_color\";s:7:\"#ffffff\";s:8:\"interval\";s:1:\"3\";s:11:\"padding_top\";s:1:\"0\";s:14:\"padding_bottom\";s:1:\"0\";s:12:\"padding_left\";s:1:\"0\";s:13:\"padding_right\";s:1:\"0\";s:4:\"link\";a:2:{i:0;s:0:\"\";i:1;s:0:\"\";}s:3:\"img\";a:2:{i:0;s:53:\"upload/custompagedecoration/2019-09/5d8b196346118.jpg\";i:1;s:53:\"upload/custompagedecoration/2019-09/5d8b19666d600.jpg\";}}', '2019-09-25 16:04:25', '1', '1'),
('12', '3', 'goods_group', 'a:17:{s:16:\"background_color\";s:7:\"#ffffff\";s:15:\"show_goods_name\";s:1:\"1\";s:16:\"show_goods_price\";s:1:\"1\";s:16:\"show_praise_rate\";s:1:\"1\";s:13:\"show_sale_num\";s:1:\"1\";s:18:\"show_columns_title\";s:1:\"1\";s:7:\"columns\";s:1:\"2\";s:9:\"goods_cat\";N;s:10:\"goods_nums\";s:1:\"6\";s:13:\"columns_title\";a:3:{i:0;s:12:\"时蔬水果\";i:1;s:12:\"酒水饮料\";i:2;s:12:\"精油食品\";}s:12:\"goods_select\";a:3:{i:0;s:1:\"1\";i:1;s:1:\"1\";i:2;s:1:\"1\";}s:16:\"goods_select_ids\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:20:\"goods_select_cats_id\";a:3:{i:0;s:2:\"47\";i:1;s:2:\"49\";i:2;s:2:\"50\";}s:9:\"goods_tag\";a:3:{i:0;s:0:\"\";i:1;s:1:\"0\";i:2;s:1:\"0\";}s:15:\"goods_min_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:15:\"goods_max_price\";a:3:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";}s:11:\"goods_order\";s:1:\"1\";}', '2019-09-25 16:04:25', '1', '4'),
('13', '3', 'nav', 'a:7:{s:16:\"background_color\";s:7:\"#ffffff\";s:5:\"count\";s:1:\"3\";s:5:\"style\";s:1:\"2\";s:9:\"item_text\";a:12:{i:0;s:12:\"自营超市\";i:1;s:9:\"品牌街\";i:2;s:9:\"店铺街\";i:3;s:12:\"我的订单\";i:4;s:12:\"分销商品\";i:5;s:12:\"秒杀活动\";i:6;s:12:\"团购活动\";i:7;s:6:\"拼团\";i:8;s:12:\"积分商城\";i:9;s:12:\"拍卖活动\";i:10;s:12:\"领券中心\";i:11;s:12:\"全民砍价\";}s:8:\"item_img\";a:12:{i:0;s:53:\"upload/custompagedecoration/2019-09/5d8b1a750c350.png\";i:1;s:53:\"upload/custompagedecoration/2019-09/5d8b1aacf0f78.png\";i:2;s:53:\"upload/custompagedecoration/2019-09/5d8b1ac442298.png\";i:3;s:53:\"upload/custompagedecoration/2019-09/5d8b1aca5dc00.png\";i:4;s:53:\"upload/custompagedecoration/2019-09/5d8b1ae8d5610.png\";i:5;s:53:\"upload/custompagedecoration/2019-09/5d8b1afa1e460.png\";i:6;s:53:\"upload/custompagedecoration/2019-09/5d8b1b0f9eef8.png\";i:7;s:53:\"upload/custompagedecoration/2019-09/5d8b1b7fd4288.png\";i:8;s:53:\"upload/custompagedecoration/2019-09/5d8b1b9e49f98.png\";i:9;s:53:\"upload/custompagedecoration/2019-09/5d8b1bbcbc3b8.png\";i:10;s:53:\"upload/custompagedecoration/2019-09/5d8b1bd9153d8.png\";i:11;s:53:\"upload/custompagedecoration/2019-09/5d8b1c17d61c8.png\";}s:9:\"item_link\";a:12:{i:0;s:26:\"/pages/shop-self/shop-self\";i:1;s:20:\"/pages/brands/brands\";i:2;s:30:\"/pages/shop-street/shop-street\";i:3;s:26:\"/pages/users/orders/orders\";i:4;s:48:\"/pages/addons/package/pages/distribut/goods/list\";i:5;s:46:\"/pages/addons/package/pages/seckill/goods/list\";i:6;s:46:\"/pages/addons/package/pages/groupon/goods/list\";i:7;s:46:\"/pages/addons/package/pages/pintuan/goods/list\";i:8;s:47:\"/pages/addons/package/pages/integral/goods/list\";i:9;s:46:\"/pages/addons/package/pages/auction/goods/list\";i:10;s:41:\"/pages/addons/package/pages/coupon/coupon\";i:11;s:46:\"/pages/addons/package/pages/bargain/goods/list\";}s:10:\"item_color\";a:12:{i:0;s:7:\"#666666\";i:1;s:7:\"#666666\";i:2;s:7:\"#666666\";i:3;s:7:\"#666666\";i:4;s:7:\"#666666\";i:5;s:7:\"#666666\";i:6;s:7:\"#666666\";i:7;s:7:\"#666666\";i:8;s:7:\"#666666\";i:9;s:7:\"#666666\";i:10;s:7:\"#666666\";i:11;s:7:\"#666666\";}}', '2019-09-25 16:04:25', '1', '2'),
('14', '3', 'image', 'a:7:{s:4:\"link\";a:1:{i:0;s:0:\"\";}s:3:\"img\";a:1:{i:0;s:53:\"upload/custompagedecoration/2019-09/5d8b1c4a2c6f0.png\";}s:11:\"padding_top\";s:1:\"0\";s:14:\"padding_bottom\";s:1:\"0\";s:12:\"padding_left\";s:1:\"0\";s:13:\"padding_right\";s:1:\"0\";s:16:\"background_color\";s:7:\"#ffffff\";}', '2019-09-25 16:04:25', '1', '3'),
('15', '3', 'tabbar', 'a:10:{s:16:\"background_color\";s:7:\"#ffffff\";s:12:\"border_color\";s:7:\"#ffffff\";s:10:\"text_color\";s:7:\"#2c2c2c\";s:18:\"text_checked_color\";s:7:\"#df0202\";s:4:\"icon\";a:5:{i:0;s:48:\"upload/custompagedecoration/base/footer-home.png\";i:1;s:52:\"upload/custompagedecoration/base/footer-classify.png\";i:2;s:48:\"upload/custompagedecoration/base/footer-cart.png\";i:3;s:50:\"upload/custompagedecoration/base/footer-follow.png\";i:4;s:48:\"upload/custompagedecoration/base/footer-user.png\";}s:11:\"select_icon\";a:5:{i:0;s:49:\"upload/custompagedecoration/base/footer-home2.png\";i:1;s:53:\"upload/custompagedecoration/base/footer-classify2.png\";i:2;s:49:\"upload/custompagedecoration/base/footer-cart2.png\";i:3;s:51:\"upload/custompagedecoration/base/footer-follow2.png\";i:4;s:49:\"upload/custompagedecoration/base/footer-user2.png\";}s:4:\"text\";a:5:{i:0;s:6:\"主页\";i:1;s:6:\"分类\";i:2;s:9:\"购物车\";i:3;s:6:\"关注\";i:4;s:6:\"我的\";}s:4:\"link\";a:5:{i:0;s:18:\"/pages/index/index\";i:1;s:24:\"/pages/classify/classify\";i:2;s:18:\"/pages/carts/carts\";i:3;s:32:\"/pages/users/attension/attension\";i:4;s:18:\"/pages/users/users\";}s:9:\"link_text\";a:5:{i:0;s:6:\"首页\";i:1;s:6:\"分类\";i:2;s:9:\"购物车\";i:3;s:6:\"关注\";i:4;s:6:\"我的\";}s:9:\"menu_flag\";a:5:{i:0;s:5:\"index\";i:1;s:8:\"category\";i:2;s:4:\"cart\";i:3;s:8:\"favorite\";i:4;s:4:\"user\";}}', '2019-09-25 16:04:25', '1', '5');
