DROP TABLE IF EXISTS `wst_seckill_time_intervals`;
DROP TABLE IF EXISTS `wst_seckill_time_intervals_times`;
DROP TABLE IF EXISTS `wst_seckills`;
DROP TABLE IF EXISTS `wst_seckills_langs`;
DROP TABLE IF EXISTS `wst_seckill_goods`;

delete from `wst_template_msgs` where tplCode='SECKILL_ACTIVITY_ALLOW';
delete from `wst_template_msgs` where tplCode='SECKILL_ACTIVITY_REJECT';
delete from `wst_template_msgs` where tplCode='WX_SECKILL_ACTIVITY_ALLOW';
delete from `wst_template_msgs` where tplCode='WX_SECKILL_ACTIVITY_REJECT';
delete from `wst_navs` where navUrl='index.php/addon/seckill-goods-lists.html';

delete from `wst_shop_message_cats` where msgCode = 'SECKILL_ACTIVITY_REJECT';
delete from `wst_shop_message_cats` where msgCode = 'WX_SECKILL_ACTIVITY_REJECT';
delete from `wst_shop_message_cats` where msgCode = 'SECKILL_ACTIVITY_ALLOW';
delete from `wst_shop_message_cats` where msgCode = 'WX_SECKILL_ACTIVITY_ALLOW';

delete from `wst_switchs` where urlMark='seckill';

delete from `wst_crons` where croncode='seckillPayOverTime';
