<?php
namespace addons\presale\controller;
use think\addons\Controller;
use think\Loader;
use Env;
use wstmart\common\model\Payments as PM;
use addons\presale\model\Presales as M;
use addons\presale\model\Orders as OM;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\PaypalApi;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

use PayPalCheckoutSdk\Payments\CapturesRefundRequest;

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
class Paypals extends Controller{
		
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
	public function getPaypalInfo(){
		//$pkey = input('pkey');
		$pkey = WSTBase64urlDecode(input('pkey'));
        $pkeys = explode('@',$pkey);
        $payObj = $pkeys[0];
        $porderId = (int)$pkeys[1];
    	$m = new M();
    	$obj = array();
    	$data = array();
    	$needPay = 0;
    	$userId = (int)session('WST_USER.userId');
    	$returnUrl = addon_url("presale://users/plist",[],true,true);
    	if($payObj=="ding"){//定金
    		
    		$presale = $m->getPresalePay($porderId);
    		$needPay = $presale["depositMoney"];

    		if($presale["isCanPay"]==0){
    			header("Location:".$return_url);
				exit();
    		}else{
    			$data["status"] = $needPay>0?1:-1;
    			$data["msg"] = ($data["status"]==-1)?lang("presale_no_need_pay_deposit_tips"):"";
    		}
    		$rs = $m->getPayInfo($porderId,1);
    	}else{//尾款
    		$presale = $m->getPresalePay($porderId);
    		if($presale["endPayTime"]<date("Y-m-d H:i:s")){
    			$data["status"] = -1;
    			$data["msg"] = lang("presale_over_time_balance_tips");
    		}else{
	    		$needPay = $presale["surplusMoney"];
	    		if($presale["presaleStatus"]==2){
	    			$data["status"] = -1;
	    			$data["msg"] = lang("presale_has_pay_balance_tips");
	    		}else{
	    			$data["status"] = $needPay>0?1:-1;
	    			$data["msg"] = ($data["status"]==-1)?lang("presale_not_need_pay_balance_tips"):"";
	    		}
    		}
    		$rs = $m->getPayInfo($porderId,2);
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
