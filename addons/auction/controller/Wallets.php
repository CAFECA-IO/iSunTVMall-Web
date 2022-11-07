<?php
namespace addons\auction\controller;
use think\addons\Controller;
use addons\auction\model\Auctions as AM;
use addons\auction\model\Auctions as M;
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
		$m = new M();
		$data = $m->getConf('Auction');
		$this->assign("seoAuctionKeywords",$data['seoAuctionKeywords']);
		$this->assign("seoAuctionDesc",$data['seoAuctionDesc']);
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}
	/**
	 * 生成支付代码
	 */
	function getWalletsUrl(){
		$am = new AM();
		$payObj = input("payObj/s");
		$payFrom = (int)input("payFrom");//0:PC 1:手机 2:微信
		$pkey = "";
		$data = array();
		$data['status'] = 1;
		$auctionId = input("auctionId/d",0);
		if($payObj=="bao"){
			$auction = $am->getUserAuction($auctionId);
			$orderAmount = $auction["cautionMoney"];
			$userId = (int)session('WST_USER.userId');
			if($auction["userId"]>0){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$data["status"] = $orderAmount>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_bond'):"";
				$pkey = $payObj."@".$auctionId;
			}
		}else{
			$auction = $am->getAuctionPay($auctionId);
			if($auction["endPayTime"]<date("Y-m-d H:i:s")){
				$data["status"] = -1;
				$data["msg"] = lang('auction_order_pay_overtime');
			}else{
				$orderAmount = $auction["payPrice"];
				$userId = (int)session('WST_USER.userId');
				if($auction["isPay"]==1){
					$data["status"] = -1;
					$data["msg"] = lang('auction_have_paid_order');
				}else{
					$data["status"] = $orderAmount>0?1:-1;
					$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_order'):"";
					$pkey = $payObj."@".$auctionId;
				}
			}
		}
		$pkey .= "@".$payFrom;
        $pkey = WSTBase64urlEncode($pkey);
		$data['url'] = addon_url('auction://wallets/payment','pkey='.$pkey,'',true,true);
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
        $auctionId = (int)$pkey[1];
        $payFrom = (int)$pkey[2];
        $data['auctionId'] = $auctionId;
        $data['userId'] = $userId;
        
        if($userId==0){
        	session('payment',lang('no_login'));
        	if($payFrom==1){//1:手机
        		$this->redirect('mobile/error/message',['code'=>'payment']);
        	}else if($payFrom==2){// 2:微信
        		$this->redirect('wechat/error/message',['code'=>'payment']);
        	}else{//0:PC 
                $this->redirect('home/error/message',['code'=>'payment']);
        	}
        }
        
		$m = new AM();
		$needPay = 0;
		$this->assign('payObj',$pkey[0]);
		$this->assign('auctionId',$auctionId);
		if($pkey[0]=="bao"){
			$auction = $m->getUserAuction($data['auctionId']);
			$needPay = $auction["cautionMoney"];
			$flag = (isset($auction["userId"]) && $auction["userId"]>0)?true:false;
		}else{
			$auction = $m->getAuctionPay($data['auctionId']);
			$needPay = $auction["payPrice"];
			$flag = ($auction["isPay"]==1)?true:false;
		}
		if($flag){
			session('payment',lang('auction_has_pay_not_again'));
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
			if($pkey[0]=='bao'){
				$rs = $m->getPayInfo($auctionId,1);
			}else{
				$rs = $m->getPayInfo($auctionId,2);
			}
			$this->assign("object",$rs['data']['auction']);
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
		$m = new AM();
        return $m->payByWallet();
	}
	/**
	 * 检查支付结果
	 */
	public function paySuccess() {
		return $this->fetch('/home/index/pay_success');
	}
}
