<?php
namespace addons\presale\controller;
use think\addons\Controller;
use addons\presale\model\Presales as M;
use addons\presale\model\Orders as OM;
use wstmart\common\model\Payments;

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
class walletswe extends Controller{
	/*************************************************  余额支付start ******************************************************/
	/**
	 * 生成支付代码--跳转余额支付前调用，获取key
	 * payObj=bao
	 */
	function getWalletsUrl(){
		$pkey = input("pkey");
		$data = array();
		$data['status'] = 1;
		// 获取用户id
		$userId = model('weapp/Index')->getUserId();
		if($userId==0)return jsonReturn(lang('presale_no_login'),-999);
		$data['pkey'] = $pkey;
		return jsonReturn('',1,$data);
	}


	/**
	 * 跳去支付页面
	 * key
	 *
	 */
	public function wallets(){

		// 获取用户id
		$userId = model('weapp/Index')->getUserId();
		if($userId==0)return jsonReturn(lang('presale_no_login'),-999);
		$pkey = input('pkey');

        $pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
        $data = [];
        $porderId = (int)$pkey[1];
        $data['porderId'] = $porderId;
        $data['userId'] = $userId;

        $m = new M();
        $needPay = 0;
        if($pkey[0]=="ding"){
            $presale = $m->getPresalePay($data['porderId'],$userId);
            $needPay = $presale["depositMoney"];
            $flag = (isset($presale["isPay"]) && $presale["isPay"]>0)?true:false;
        }else{
            $presale = $m->getPresalePay($data['porderId'],$userId);
            $needPay = $presale["surplusMoney"];
            $flag = ($presale["isPay"]==2)?true:false;
        }

        if($flag){
            return jsonReturn(lang('presale_order_has_pay_tips'),-999);
        }else{
            if($pkey[0]=='ding'){
                $rs = $m->getPayInfo($porderId,1,$userId);
            }else{
                $rs = $m->getPayInfo($porderId,2,$userId);
            }
            if($rs["status"]==1){
                $user = model('common/users')->getFieldsById($userId,'userMoney,payPwd');
                $payPwd = $user['payPwd'];
                $payPwd = empty($payPwd)?0:1;
                $data["userMoney"] = $user['userMoney'];
                $data["payPwd"] = $payPwd;
                $data['presale'] = $rs['data']['presale'];
                $data['goodsNum'] = $rs['data']['porder']['goodsNum'];
                $data['needPay'] = $needPay;
                $data['payObj'] = $pkey[0];
                return jsonReturn('',1,$data);
            }else{
                return jsonReturn(lang('presale_get_order_info_fail'),-1);
            }
        }
	}
	/**
	 * 执行余额支付
	 * 需要传递支付密码跟余额支付生成的key
	 * payPwd
	 * key
	 */
	public function payByWallet(){
		$m = new OM();
		$userId = model('weapp/Index')->getUserId();
		if($userId>0){
			$rs = $m->payByWallet($userId);
			return jsonReturn("",1,$rs);
		}
		return jsonReturn(lang('presale_no_login'),-999);
	}
	/**********************************************  余额支付end  *********************************************************/

}
