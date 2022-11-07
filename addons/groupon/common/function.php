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
/**
* 取出正在进行中的团购商品
* @_field 需要取的字段。
* @extra 需要额外取的字段
* @num
*/
function groupon_list($field='',$extra=[],$num=5){
	$gm = new \addons\groupon\model\Groupons;
	$time = date('Y-m-d H:i:s');
	$where = [];
	$where[] = ['startTime','<=',$time];
	$where[] = ['gu.endTime','>',$time];
	$_field = array_merge(['gul.goodsName','gu.goodsImg','g.marketPrice','gu.grouponId','gu.grouponPrice'],$extra);
	if($field!='')$_field=$field;
	$rs = $gm->alias('gu')->join('__GOODS__ g','gu.goodsId=g.goodsId','inner')
              ->join('__GROUPONS_LANGS__ gul','gul.grouponId=gu.grouponId and gul.langId='.WSTCurrLang())
	          ->where('gu.grouponNum > gu.orderNum')
	          ->where('g.dataFlag=1 and gu.isSale=1 and g.goodsStatus=1 and gu.dataFlag=1 and gu.grouponStatus=1')
	          ->where($where)
	          ->field($_field)
	          ->order('gu.updateTime desc,gu.startTime asc,gu.grouponId desc')
	          ->limit($num)
	          ->select();
	return $rs;
}
