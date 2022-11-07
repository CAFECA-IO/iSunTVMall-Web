<?php
namespace addons\auction\controller;
use think\addons\Controller;
use addons\auction\model\Auctions as M;
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
	/**
	* 获取支付方式
	*/
	public function payType(){
		//获取支付方式
		$m = new M();
		$pa = new Payments();
		$payments = $pa->getByGroup('4', -1, true)[1]; // 只能在线支付
		$rs = $m->getPayInfo((int)input('auctionId/d',0),1);
		$rs['data']['payments'] = $payments;
		return json_encode($rs);
	}
	/*************************************************  余额支付start ******************************************************/
	/**
	 * 生成支付代码--跳转余额支付前调用，获取key
	 * payObj=bao
	 */
	function getWalletsUrl(){
		$am = new M();
		$payObj = input("payObj/s");
		$payFrom = (int)input("payFrom");//0:PC 1:手机 2:微信 3:app 4:小程序
		$pkey = "";
		$data = array();
		$data['status'] = 1;
		$auctionId = input("auctionId/d",0);
		// 获取用户id
		$userId = model('weapp/Index')->getUserId();
		if($userId==0)return json_encode(WSTReturn('您还未登录~',-999));
		if($payObj=="bao"){
            $auction = $am->getUserAuction($auctionId);
			$orderAmount = $auction["cautionMoney"];
			
			if($auction["userId"]>0){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$data["status"] = $orderAmount>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?"无需支付保证金":"";
				$pkey = $payObj."@".$auctionId;
			}
		}else{
			$auction = $am->getAuctionPay($auctionId, $userId);
			if($auction["endPayTime"]<date("Y-m-d H:i:s")){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$orderAmount = $auction["payPrice"];
				$userId = (int)session('WST_USER.userId');
				if($auction["isPay"]==1){
					$data["status"] = -1;
					$data["msg"] = "您已缴拍卖货款";
				}else{
					$data["status"] = $orderAmount>0?1:-1;
					$data["msg"] = ($data["status"]==-1)?"无需支付拍卖货款":"";
					$pkey = $payObj."@".$auctionId;
				}
			}
		}
		$pkey .= "@".$payFrom;
		$pkey = WSTBase64urlEncode($pkey);
		$data['pkey'] = $pkey;
		return json_encode($data);
	}
	
	
	/**
	 * 跳去支付页面
	 * key
	 * 
	 */
	public function wallets(){
		$pkey = input('pkey');
        $key = WSTBase64urlDecode($pkey);
        $key = explode('@',$key);
        $data = [];
        $auctionId = (int)$key[1];
        $payFrom = (int)$key[2];
        $data['auctionId'] = (int)$key[1];
		// 获取用户id
		$userId = model('weapp/Index')->getUserId();
		if($userId==0)return json_encode(WSTReturn('您还未登录~',-999));
		
        $data['userId'] = $userId;
		$m = new M();
		$needPay = 0;
		$this->assign('payObj',$key[0]);
		$this->assign('auctionId',$auctionId);
		if($key[0]=="bao"){
			$auction = $m->getUserAuction($data['auctionId']);
			$needPay = $auction["cautionMoney"];
			$flag = (isset($auction["userId"]) && $auction["userId"]>0)?true:false;
		}else{
			$auction = $m->getAuctionPay($data['auctionId'],$userId);
			$needPay = $auction["payPrice"];
			$flag = ($auction["isPay"]==1)?true:false;
		}
		if($flag){
			return json_encode(WSTReturn('您已支付，请勿重复支付~',-1));
		}else{
			//获取用户钱包
			$user = model('common/users')->getFieldsById($data['userId'],'userMoney,payPwd');
			if($key[0]=='bao'){
				$rs = $m->getPayInfo($auctionId,1);
			}else{
				$rs = $m->getPayInfo($auctionId,2);
			}
			// 删除无用字段
			unset($rs['data']['payments']);
            $rs['data']['payPwd'] = $user['payPwd']!=''?1:0;
			$rs['data']['needPay'] = $needPay;
			$rs['data']['userMoney'] = $user['userMoney'];
			if(isset($rs['data']['auction']) && isset($rs['data']['auction']['goodsName'])){
				$rs['data']['auction']['goodsName'] = htmlspecialchars_decode($rs['data']['auction']['goodsName']);
			}

			return json_encode($rs);
	    }
	}
	/**
	 * 执行余额支付
	 * 需要传递支付密码跟余额支付生成的key
	 * payPwd 
	 * key
	 */
	public function payByWallet(){
		$m = new M();
		$userId = model('weapp/Index')->getUserId();
		if($userId>0){
			return json_encode($m->payByWallet($userId));
		}
		return json_encode(WSTReturn('您还未登录',-999));
	}
	/**********************************************  余额支付end  *********************************************************/
	
}
