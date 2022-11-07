<?php
namespace wstmart\shopapp\controller;
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
 * 资金流水控制器
 */
class Logmoneys extends Base{
	// 前置方法执行列表
    protected $beforeActionList = [
        'checkAuth'
    ];
    /**
     * 充值页面
     */
    public function toRecharge(){
    	$data = array();
    	$data['payments'] = model('common/payments')->recharePayments('4');
    	$data['chargeItems'] = model('common/ChargeItems')->queryList();
    	return json_encode(WSTReturn('ok',1,$data));
    }
	/**
     * 查看用户资金流水
     */
	public function usermoneys(){
		$userId = model('shopapp/users')->getUserId();
		$rs = model('Users')->getFieldsById($userId,['lockMoney','userMoney','payPwd','rechargeMoney']);
		$rs['withdrawMoney'] = (($rs['userMoney']-$rs['rechargeMoney'])>0)?sprintf('%.2f',($rs['userMoney']-$rs['rechargeMoney'])):0;
		unset($rs['rechargeMoney']);
		$rs['isSetPayPwd'] = ($rs['payPwd']=='')?0:1;
        unset($rs['payPwd']);
		$rs['num'] = count(model('common/cashConfigs')->listQuery(0,$userId));
		return json_encode(WSTReturn('success',1,$rs));
	}
	/**
	* 验证支付密码
	*/
	public function checkPayPwd(){
		$rs = model('shopapp/users')->checkPayPwd();
		return json_encode($rs);
	}
	/**
	 * 资金流水
	 */
	public function record(){
		$userId = model('shopapp/users')->getUserId();
		$rs = model('Users')->getFieldsById($userId,['lockMoney','userMoney']);
		return json_encode(WSTReturn('ok',1,$rs));
	}
	/**
	 * 列表
	 */
	public function pageQuery(){
		$shopId = $this->getShopId();
		$data = model('logMoneys')->pageQuery(1,$shopId);
		return json_encode(WSTReturn("ok", 1,$data));
	}

    /**
     * 缴纳年费页面
     */
    public function toRenew(){
        $shopId = $this->getShopId();
        $catShopInfo = model('common/shops')->getCatShopInfo($shopId);
        $trades = WSTTrades(0);
        $tradeInfo = [];
        foreach($trades as $key => $vo){
            if($vo['tradeId']==$catShopInfo['tradeId']){
                $tradeInfo = $vo;
            }
        }
        $needPay = $catShopInfo['needPay'];
        $shop = model('common/shops')->getFieldsById($shopId,'expireDate');
        $isExpire = ((strtotime($shop['expireDate'])-strtotime(date('Y-m-d')))<2592000)?true:false;
        $data = array();
        $data['shopId'] = $shopId;
        $data['payments'] = model('common/payments')->getByGroup('5', -1, true);;
        $data['needPay'] = $needPay?$needPay:'';
        $data['tradeInfo'] = $tradeInfo;
        $data['isExpire'] = $isExpire;
        $data['expireDate'] = $shop['expireDate'];
        return json_encode(WSTReturn('ok',1,$data));
    }
}
