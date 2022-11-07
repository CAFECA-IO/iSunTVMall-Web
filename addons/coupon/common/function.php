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
use think\Db;
function coupon_list($field='',$extra=[],$num=5){
    $userId = (int)session('WST_USER.userId');
    $where = [['c.dataFlag','=',1],['endDate','>=',date('Y-m-d')]];
    $_field = array_merge(['c.*'],$extra);
	if($field!='')$_field=$field;
	$rs =  Db::name('coupons')->alias('c')
	              ->join('__SHOPS__ s','c.shopId=s.shopId and s.dataFlag=1 and s.shopStatus=1')
	              ->where($where)
	              ->field($_field)
                  ->order('c.endDate','desc')
                  ->limit($num)
              	  ->select();
    $userCoupons = [];
    if($userId>0){
        $userCoupons = Db::name('coupon_users')->where(['userId'=>$userId])->column('couponId');
    }
    $time = time();
    foreach ($rs as $key => $v) {
    	$rs[$key]['isOut'] = (($v['couponNum']<=$v['receiveNum']) || ($time>WSTStrToTime($v['endDate']." 23:59:59")))?true:false;
        $rs[$key]['isReceive'] = ($userId>0)?in_array($v['couponId'],$userCoupons):false;
    }
    return $rs;
}

/**
 * 自定义页面取出正在进行中的优惠券
 */
function custom_page_coupon_list($ids='',$shopId=0,$num=4){
    $userId = (int)session('WST_USER.userId');
    $where = [['c.dataFlag','=',1],['endDate','>=',date('Y-m-d')]];
    if($shopId>0)$where[] = ['c.shopId','=',$shopId];
    if($ids!='')$where[] = ['c.couponId','in',$ids];
    $rs =  Db::name('coupons')->alias('c')
        ->join('__SHOPS__ s','c.shopId=s.shopId and s.dataFlag=1 and s.shopStatus=1')
        ->where($where)
        ->order('c.endDate','desc')
        ->limit($num)
        ->select();

    $userCoupons = [];
    if($userId>0){
        $userCoupons = Db::name('coupon_users')->where(['userId'=>$userId])->column('couponId');
    }
    $time = time();
    foreach ($rs as $key => $v) {
        $rs[$key]['isOut'] = (($v['couponNum']<=$v['receiveNum']) || ($time>WSTStrToTime($v['endDate']." 23:59:59")))?true:false;
        $rs[$key]['isReceive'] = ($userId>0)?in_array($v['couponId'],$userCoupons):false;
    }
    return $rs;
}
