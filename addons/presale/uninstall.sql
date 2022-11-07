DROP TABLE IF EXISTS `wst_presales`;
DROP TABLE IF EXISTS `wst_presales_langs`;
DROP TABLE IF EXISTS `wst_presale_orders`;
DROP TABLE IF EXISTS `wst_presale_moneys`;

delete from `wst_template_msgs` where tplCode='PRESALE_GOODS_ALLOW';
delete from `wst_template_msgs` where tplCode='PRESALE_GOODS_REJECT';
delete from `wst_template_msgs` where tplCode='PRESALE_USER_PAY_OVERTIME';
delete from `wst_template_msgs` where tplCode='PRESALE_SHOP_PAY_OVERTIME';
delete from `wst_template_msgs` where tplCode='PRESALE_END';
delete from `wst_template_msgs` where tplCode='PRESALE_ORDER_USER_PAY_TIMEOUT';


delete from `wst_template_msgs` where tplCode='WX_PRESALE_USER_PAY_OVERTIME';
delete from `wst_template_msgs` where tplCode='WX_PRESALE_SHOP_PAY_OVERTIME';
delete from `wst_template_msgs` where tplCode='WX_PRESALE_END';
delete from `wst_template_msgs` where tplCode='WX_PRESALE_REFUND';
delete from `wst_template_msgs` where tplCode='WX_PRESALE_ORDER_USER_PAY_TIMEOUT';
delete from `wst_template_msgs` where tplCode='WX_PRESALE_GOODS_ALLOW';
delete from `wst_template_msgs` where tplCode='WX_PRESALE_GOODS_REJECT';

delete from `wst_shop_message_cats` where msgCode = 'PRESALE_GOODS_REJECT';
delete from `wst_shop_message_cats` where msgCode = 'PRESALE_GOODS_ALLOW';
delete from `wst_shop_message_cats` where msgCode = 'WX_PRESALE_GOODS_REJECT';
delete from `wst_shop_message_cats` where msgCode = 'WX_PRESALE_GOODS_ALLOW';

delete from `wst_switchs` where urlMark='presale';

delete from `wst_crons` where croncode='presaleOverPayTime';
delete from `wst_crons` where croncode='presalePayNotice';
