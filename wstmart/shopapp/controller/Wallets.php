<?php
namespace wstmart\shopapp\controller;
use wstmart\common\model\Shops as SM;
use wstmart\common\model\Users as UM;
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
 * 余额控制器
 */
class Wallets extends Base{
	// 前置方法执行列表
	protected $beforeActionList = [
			'checkAuth'
	];

	public function payment(){
	    $userId = model('users')->getUserId();
        $shopId = $this->getShopId();

        //获取商家钱包
        $shop = model('common/shops')->getFieldsById($shopId,'shopMoney');
        $list['shopMoney'] = $shop['shopMoney'];// 商家钱包可用余额

        $m = new UM();
        $user = $m->getFieldsById($userId,["payPwd"]);
        $list['hasPayPwd'] = ($user['payPwd']!="")?1:0;
        $pkey = base64_decode(input('pkey'));
        $pkey = explode('_',$pkey);
        $needPay = base64_decode($pkey[0]);
        $list['needPay'] = (float)$needPay;
	    return json_encode(WSTReturn(lang("success_msg"), 1, $list));
	}

	public function payByWallet(){
		$m = new SM();
        $shopId = $this->getShopId();
        $pkey = base64_decode(input('pkey'));
        $pkey = explode('_',$pkey);
        $needPay = base64_decode($pkey[0]);
        $obj = array ();
        $obj["orderNo"] = WSTOrderNo();
        $obj["targetId"] = $shopId;
        $obj["targetType"] = 1;
        $obj["total_fee"] = (float)$needPay;
        $obj["scene"] = 'renew';
        $obj['type'] = 1;
		return json_encode($m->payByWallet($obj));
	}
}
