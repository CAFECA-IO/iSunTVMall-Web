<?php
namespace wstmart\shopapp\controller;
use wstmart\common\model\OrderRefunds as M;
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
 * 订单退款控制器
 */
class Orderrefunds extends Base{
    /**
	 * 用户申请退款
	 */
	public function refund(){
		$m = new M();
		$userId = (int)model('shopapp/users')->getUserId();
		$rs = $m->refund($userId);
		return json_encode($rs);
	}
	/**
	 * 商家处理是否同意
	 */
	public function shopRefund(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		$rs = $m->shopRefund($shopId);
		return json_encode($rs);
	}
}