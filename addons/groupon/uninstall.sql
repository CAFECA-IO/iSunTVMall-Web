DROP TABLE IF EXISTS `wst_groupons`;
DROP TABLE IF EXISTS `wst_groupons_langs`;
delete from `wst_template_msgs` where tplCode='GROUPON_GOODS_ALLOW';
delete from `wst_template_msgs` where tplCode='GROUPON_GOODS_REJECT';
delete from `wst_template_msgs` where tplCode='WX_GROUPON_GOODS_ALLOW';
delete from `wst_template_msgs` where tplCode='WX_GROUPON_GOODS_REJECT';

delete from `wst_shop_message_cats` where msgCode = 'GROUPON_GOODS_REJECT';
delete from `wst_shop_message_cats` where msgCode = 'WX_GROUPON_GOODS_REJECT';
delete from `wst_shop_message_cats` where msgCode = 'GROUPON_GOODS_ALLOW';
delete from `wst_shop_message_cats` where msgCode = 'WX_GROUPON_GOODS_ALLOW';

delete from `wst_switchs` where urlMark='groupon';
delete from `wst_ad_positions` where positionCode='ads-groupon';
