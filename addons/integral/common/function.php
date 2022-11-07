<?php
/**
 * ============================================================================
 * WSTMart多用户商城
 * 版权所有 2016-2066 广州商淘信息科技有限公司，并保留所有权利。
 * 官网地址:http://www.wstmart.net
 * 交流社区:http://bbs.shangtao.net
 * 联系QQ:153289970
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！未经本公司授权您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 */
function integral_list($field='',$extra=[],$num=5){
	$gm = think\Db::name('integral_goods');
	$time = date('Y-m-d H:i:s');
	$where = [];
	$where[] = ['startTime','<=',$time];
	$where[] = ['ig.endTime','>',$time];
	$_field = array_merge(['igl.goodsName','ig.goodsImg','ig.id','ig.goodsPrice','ig.integralNum'],$extra);
	if($field!='')$_field=$field;
	$rs = $gm->alias('ig')->join('__GOODS__ g','ig.goodsId=g.goodsId','inner')
              ->join('__INTEGRAL_GOODS_LANGS__ igl','igl.integralId=ig.id and igl.langId='.WSTCurrLang())
	          ->where('g.dataFlag=1 and g.goodsStatus=1 and ig.dataFlag=1 and ig.integralStatus=1')
	          ->where($where)
	          ->field($_field)
	          ->order('ig.updateTime desc,ig.startTime asc,ig.id desc')
	          ->limit($num)
	          ->select();
	return $rs;
}
