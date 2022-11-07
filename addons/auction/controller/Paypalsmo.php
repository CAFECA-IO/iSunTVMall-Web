<?php
namespace addons\auction\controller;
use think\addons\Controller;
use think\Loader;
use Env;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\PaypalApi;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

use PayPalCheckoutSdk\Payments\CapturesRefundRequest;

use wstmart\common\model\Payments as PM;
use addons\auction\model\Auctions as M;
use wstmart\common\model\Orders as OM;
use addons\auction\model\Auctions as AM;
use wstmart\common\model\LogPaypals;
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
 * Paypal支付控制器
 */
class Paypalsmo extends Controller{
		
	/**
	 * 初始化
	 */
	private $wxpay;
	public function initialize() {

		header ("Content-type: text/html; charset=utf-8");
		$m = new PM();
		$this->wxpay = $m->getPayment("paypals");

	}
	
	/**
	 * 获取微信URL
	 */
	public function getPaypalsURL(){
		$am = new AM();
		$payObj = input("payObj/s");
		$pkey = "";
		$data = array();
		$auctionId = input("auctionId/d",0);
		$userId = (int)session('WST_USER.userId');
		if($payObj=="bao"){
			$auction = $am->getUserAuction($auctionId);
			$needPay = $auction["cautionMoney"];
			
			if($auction["userId"]>0){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$data["status"] = $needPay>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_bond'):"";
			}
		}else{
			$auction = $am->getAuctionPay($auctionId);
			$needPay = $auction["payPrice"];
			if($auction["isPay"]==1){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_order');
			}else{
				$data["status"] = $needPay>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_order'):"";
			}
		}
		return $data;
	}

	/**
	 * 获取PaypalURL
	 */
	public function toPay(){
	
        $am = new AM();
		$payObj = input("payObj/s");
		$pkey = "";
		$needPay = 0;
		$data = array();
		$auctionId = input("auctionId/d",0);
		$userId = (int)session('WST_USER.userId');
		$returnUrl = "";
		if($payObj=="bao"){
			$auction = $am->getUserAuction($auctionId);
			$needPay = $auction["cautionMoney"];
			$returnUrl = addon_url("auction://goods/modetail",array("id"=>$auctionId),true,true);
			if($auction["userId"]>0){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$data["status"] = $needPay>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_bond'):"";
				$pkey = $payObj."@".$userId."@".$auctionId;
			}
			$rs = $am->getPayInfo($auctionId,1);
		}else{
			$returnUrl = addon_url("auction://users/mocheckPayStatus",array("id"=>$auctionId),true,true);
			$auction = $am->getAuctionPay($auctionId);
			if($auction["endPayTime"]<date("Y-m-d H:i:s")){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$needPay = $auction["payPrice"];
				if($auction["isPay"]==1){
					$data["status"] = -1;
					$data["msg"] = lang('auction_have_paid_order');
				}else{
					$data["status"] = $needPay>0?1:-1;
					$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_order'):"";
					$pkey = $payObj."@".$userId."@".$auctionId;
				}
			}
			$rs = $am->getPayInfo($auctionId,2);
		}
        $this->assign('payFrom','paypals');
        $this->assign('returnUrl',$returnUrl);
        $this->assign('needPay',$needPay);
	    $this->assign('pkey',WSTBase64urlEncode($pkey) );
	    $this->assign('payObj',$payObj);
	    $this->assign('object', $rs['data']['auction']);
		return $this->fetch('mobile/index/pay_weixin');
		
	}

	/**
	 * 获取微信URL
	 */
	public function getPaypalInfo(){
		$am = new AM();

		$pkey = WSTBase64urlDecode(input("pkey"));
     	$pkeys = explode('@',$pkey);

		$payObj = $pkeys[0];
		$needPay = 0;
		$data = array();
		$auctionId = (int)$pkeys[2];
		$userId = (int)session('WST_USER.userId');
		$returnUrl = "";
		if($payObj=="bao"){
			$returnUrl = addon_url("auction://goods/modetail",array("id"=>$auctionId),true,true);
			$auction = $am->getUserAuction($auctionId);
			$needPay = $auction["cautionMoney"];
			
			if($auction["userId"]>0){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$data["status"] = $needPay>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_bond'):"";
				
			}
		}else{
			$returnUrl = addon_url("auction://users/mocheckPayStatus",array("id"=>$auctionId),true,true);
			$auction = $am->getAuctionPay($auctionId);
			if($auction["endPayTime"]<date("Y-m-d H:i:s")){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$needPay = $auction["payPrice"];
				if($auction["isPay"]==1){
					$data["status"] = -1;
					$data["msg"] = lang('auction_have_paid_order');
				}else{
					$data["status"] = $needPay>0?1:-1;
					$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_order'):"";
					
				}
			}
		}
		
		if($data["status"]!=1){
			if(!(isSet($data["msg"])) || $data["msg"]=="") $data["msg"]= lang('auction_pay_failed');
		}else{

			$out_trade_no = WSTOrderNo();
			$isSandbox = $this->wxpay['isSandbox'];
			$clientId = $this->wxpay['clientId'];
			$clientSecret = $this->wxpay['secret'];
			$environment = null;
			if($isSandbox==1){
			    //Sandbox
			    $environment = new SandboxEnvironment($clientId, $clientSecret);
			}else{
			    //live
			    $environment = new ProductionEnvironment($clientId, $clientSecret);
			}


			$client = new PayPalHttpClient($environment);
			//print_r($client);
			
			$request = new OrdersCreateRequest();
			$request->prefer('return=representation');
			$request->body = [
			                    "intent" => "CAPTURE",
			                    "purchase_units" => [[
			                         "reference_id" => $out_trade_no,
			                         "custom_id" => $out_trade_no,
			                         "amount" => [
			                             "value" => $needPay,
			                             "currency_code" => "HKD"
			                         ]
			                    ]],
			                    "application_context" => [
			                          "return_url" => $returnUrl
			                    ] 
			                 ];

			try {
			    // Call API with your client and get a response for your call
			    $response = $client->execute($request);
			   // $response = ((array)$response);
			    // If call returns body in response, you can get the deserialized version from the result attribute of the response
			   	$pdata = array();
		        $pdata["userId"] = $userId;
		        $pdata["orderId"] = $out_trade_no;
		        $pdata["paramsVa"] = $pkey;
		        $m = new LogPaypals();
		        $m->addPayLog($pdata);
		        return json($response->result);
			    //echo json_encode($response->result, JSON_PRETTY_PRINT);
			}catch (HttpException $ex) {
			    echo $ex->statusCode;
			    print_r($ex->getMessage());
			}

			
		}
		return $data;
	}
	
}
