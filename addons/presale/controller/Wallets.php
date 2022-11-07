<?php
namespace addons\presale\controller;
use think\addons\Controller;
use addons\presale\model\Presales as M;
use addons\presale\model\Orders as OM;
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
class Wallets extends Controller{
	public function __construct(){
		parent::__construct();
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}
	/**
	 * 生成支付代码
	 */
	function getWalletsUrl(){
		$m = new M();
		$payFrom = (int)input("payFrom");//0:PC 1:手机 2:微信
		$pkey = "";
		$data = array();
		$data['status'] = 1;
		$pkey = input("pkey");

        $vpkey = WSTBase64urlDecode($pkey);
        $vpkey = explode('@',$vpkey);
		$payObj = $vpkey[0];
		$porderId = (int)$vpkey[1];
		if($payObj=="ding"){//定金
			$presale = $m->getPresalePay($porderId);
			$orderAmount = $presale["depositMoney"];
			$userId = (int)session('WST_USER.userId');
			if($presale["presaleStatus"]>0){
				$data["status"] = -1;
				$data["msg"] = lang('presale_have_pay_deposit_title');
			}else{
				$data["status"] = $orderAmount>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('presale_no_need_pay_deposit_tips'):"";
				$pkey = $payObj."@".$porderId;
			}
		}else{//尾款
			$presale = $m->getPresalePay($porderId);
			if($presale["startPayTime"]>date("Y-m-d H:i:s")){
				$data["status"] = -1;
				$data["msg"] = lang('presale_cannt_pay_balance_tips');
			}else if($presale["endPayTime"]<date("Y-m-d H:i:s")){
				$data["status"] = -1;
				$data["msg"] = lang('presale_over_time_balance_tips');
			}else{
				$orderAmount = $presale["surplusMoney"];
				$userId = (int)session('WST_USER.userId');
				if($presale["presaleStatus"]==2){
					$data["status"] = -1;
					$data["msg"] = lang('presale_has_pay_balance_tips');
				}else{
					$data["status"] = $orderAmount>0?1:-1;
					$data["msg"] = ($data["status"]==-1)?lang('presale_not_need_pay_balance_tips'):"";
					$pkey = $payObj."@".$porderId;
				}
			}
		}
		$pkey .= "@".$payFrom;
        $pkey = WSTBase64urlEncode($pkey);
		$data['url'] = addon_url('presale://wallets/payment','pkey='.$pkey,'',true,true);
		return $data;
	}

	/**
	 * 跳去支付页面
	 */
	public function payment(){
		$userId = (int)session('WST_USER.userId');
		$m = new UM();
		$user = $m->getFieldsById($userId,["payPwd"]);
		$this->assign('hasPayPwd',($user['payPwd']!="")?1:0);

		$pkey = input('pkey');
		$this->assign('pkey',$pkey);
        $pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
        $data = [];
        $porderId = (int)$pkey[1];
        $payFrom = (int)$pkey[2];
        $data['porderId'] = $porderId;
        $data['userId'] = $userId;

        if($userId==0){
        	session('payment',lang('presale_no_login'));
        	if($payFrom==1){//1:手机
        		$this->redirect('mobile/error/message',['code'=>'payment']);
        	}else if($payFrom==2){// 2:微信
        		$this->redirect('wechat/error/message',['code'=>'payment']);
        	}else{//0:PC
                $this->redirect('home/error/message',['code'=>'payment']);
        	}
        }

		$m = new M();
		$needPay = 0;
		$this->assign('payObj',$pkey[0]);
		$this->assign('porderId',$porderId);
		if($pkey[0]=="ding"){
			$presale = $m->getPresalePay($data['porderId']);
			$needPay = $presale["depositMoney"];
			$flag = (isset($presale["isPay"]) && $presale["isPay"]>0)?true:false;
		}else{
			$presale = $m->getPresalePay($data['porderId']);
			$needPay = $presale["surplusMoney"];
			$flag = ($presale["isPay"]==2)?true:false;
		}
		if($flag){
			session('payment',lang('presale_order_has_pay_tips'));
			if($payFrom==0){//0:PC
        		$this->redirect('home/error/message',['code'=>'payment']);
        	}
		}else{
			$this->assign('needPay',$needPay);
			//获取用户钱包
			$user = model('common/users')->getFieldsById($data['userId'],'userMoney,payPwd');
			$this->assign('userMoney',$user['userMoney']);
			$payPwd = $user['payPwd'];
			$payPwd = empty($payPwd)?0:1;
			$this->assign('payPwd',$payPwd);
			if($pkey[0]=='ding'){
				$rs = $m->getPayInfo($porderId,1);
			}else{
				$rs = $m->getPayInfo($porderId,2);
			}
			$rs['data']['porder']['needPay'] = $needPay;
			$this->assign("object",$rs['data']['porder']);
	        if($payFrom==1){//1:手机
	        	return $this->fetch('/mobile/index/pay_wallets');
	        }else if($payFrom==2){// 2:微信
	        	return $this->fetch('/wechat/index/pay_wallets');
	        }else{//0:PC
	        	 return $this->fetch('/home/index/pay_wallets');
	        }
	    }
	}

	/**
	 * 钱包支付
	 */
	public function payByWallet(){
		$m = new OM();
        return $m->payByWallet();
	}
	/**
	 * 检查支付结果(PC)
	 */
	public function paySuccess() {
		return $this->fetch('/home/index/pay_success');
	}
}
