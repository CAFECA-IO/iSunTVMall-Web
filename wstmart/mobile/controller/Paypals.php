<?php
namespace wstmart\mobile\controller;
use think\Loader;
use Env;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\PaypalApi;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

use PayPalCheckoutSdk\Payments\CapturesRefundRequest;
use wstmart\common\model\LogMoneys as LM;
use wstmart\common\model\Payments as M;
use wstmart\common\model\Orders as OM;
use wstmart\common\model\LogPaypals;
use wstmart\common\model\ChargeItems as CM;


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
class Paypals extends Base{
	
	/**
	 * 初始化
	 */
	private $wxpay;
	public function initialize() {

		header ("Content-type: text/html; charset=utf-8");
		$m = new M();
		$this->wxpay = $m->getPayment("paypals");

	}
	
	/**
	 * 获取PaypalURL
	 * 
	 */
	public function toPaypal(){

        $payObj = input("payObj/s");
        $returnUrl = "";
        $subject = "";
        $userId = 0;
     	$pkey = "";
     	$needPay = 0;
     	$data = array();
        if($payObj=="recharge"){//充值

        	$returnUrl = url("mobile/logmoneys/usermoneys","",true,true);
	    	$cm = new CM();
	    	$itemId = (int)input("itemId/d");
	    	$targetType = 0;
	    	$targetId = (int)session('WST_USER.userId');
	    	$out_trade_no = WSTOrderNo();
	    	$lm = new LM();
	    	$log = $lm->getLogMoney(['targetType'=>$targetType,'targetId'=>$targetId,'dataId'=>$out_trade_no,'dataSrc'=>4]);
	    	if(!empty($log)){
	    		header("Location:".$returnUrl);
				exit();
	    	}
	    	
	    	$needPay = 0;
	    	if($itemId>0){
	    		$item = $cm->getItemMoney($itemId);
	    		$needPay = isset($item["chargeMoney"])?$item["chargeMoney"]:0;
	    	}else{
	    		$needPay = (int)input("needPay/d");
	    	}
	    	
	    	$body = lang('recharge_wallet');
	    	$data["status"] = $needPay>0?1:-1;
	    	$pkey = $payObj."@".$targetId."@".$targetType."@".$needPay."@".$itemId;
	    	
	    	if($needPay==0){
				header("Location:".$returnUrl);
				exit();
			}

            $data["status"] = $needPay>0?1:-1;
        }else{
        	$payObj = "orderPay";
        	$pkey = WSTBase64urlDecode(input("pkey"));
	    	$userId = (int)session('WST_USER.userId');
            $pkey = explode('@',$pkey);
            $orderNo = $pkey[0];
            $isBatch = (int)$pkey[1];

	        $data['orderNo'] = $orderNo;
	        $data['isBatch'] = $isBatch;
	        $data['userId'] = $userId;
			$m = new OM();
			$rs = $m->getOrderPayInfo($data);
			$returnUrl = url("mobile/orders/index","",true,true);
			if(empty($rs)){
				header("Location:".$returnUrl);
				exit();
			}else{
				$m = new OM();
				
				$obj["userId"] = $userId;
				$obj["orderNo"] = $orderNo;
				$obj["isBatch"] = $isBatch;
		
				$rs = $m->getByUnique($userId,$orderNo,$isBatch);
				$this->assign('rs',$rs);
				$body = lang('order_pay_title');
				$order = $m->getPayOrders($obj);
				$needPay = $order["needPay"];
				$payRand = $order["payRand"];
				$out_trade_no = $obj["orderNo"]."a".$payRand;
			
	            $pkey = $payObj."@".$userId."@".$orderNo;
				if($isBatch==1){
					$pkey = $pkey."@1";
				}else{
					$pkey = $pkey."@2";
				}

				if($needPay==0){
					header("Location:".$returnUrl);
					exit();
				}
			}
        }
        $this->assign('payFrom','paypals');
        $this->assign('needPay',$needPay);
	    $this->assign('pkey',WSTBase64urlEncode($pkey) );
	    $this->assign('returnUrl',$returnUrl );
	    $this->assign('payObj',$payObj);
		return $this->fetch('users/orders/orders_wxpay');
		
	}


	/**
	 * 获取PaypalURL
	 */
	public function getPaypalsURL(){
	
        $payObj = input("payObj/s");
        $returnUrl = "";
        $subject = "";
        $userId = 0;
     	$needPay = 0;
     	$data = array();
     	$pkey = WSTBase64urlDecode(input("pkey"));
     	$pkeys = explode('@',$pkey);
        if($payObj=="recharge"){//充值
        	$returnUrl = url("mobile/logmoneys/usermoneys","",true,true);
	    	$cm = new CM();
	    	$itemId = (int)$pkeys[4];
	    	$targetType = 0;
	    	$targetId = (int)session('WST_USER.userId');
	    	$out_trade_no = (int)input("trade_no");
	    	
	    	$lm = new LM();
	    	$log = $lm->getLogMoney(['targetType'=>$targetType,'targetId'=>$targetId,'dataId'=>$out_trade_no,'dataSrc'=>4]);
	    	if(!empty($log)){
				exit();
	    	}
	    	$needPay = 0;
	    	if($itemId>0){
	    		$item = $cm->getItemMoney($itemId);
	    		$needPay = isSet($item["chargeMoney"])?$item["chargeMoney"]:0;
	    	}else{
	    		$needPay = (int)input("needPay/d");
	    	}
	  		
            $data["status"] = $needPay>0?1:-1;
        }else{
        	
	    	$userId = (int)session('WST_USER.userId');
            

            $orderNo = $pkeys[2];
            $isBatch = (int)$pkeys[3];

	        $data['orderNo'] = $orderNo;
	        $data['isBatch'] = $isBatch;
	        $data['userId'] = $userId;
			$m = new OM();
			$rs = $m->getOrderPayInfo($data);
			
			$returnUrl = url("mobile/orders/index","",true,true);
			if(empty($rs)){
				header("Location:".$returnurl);
				exit();
			}else{
				$m = new OM();
				
				$obj["userId"] = $userId;
				$obj["orderNo"] = $orderNo;
				$obj["isBatch"] = $isBatch;
		
				$rs = $m->getByUnique($userId,$orderNo,$isBatch);
				$this->assign('rs',$rs);
				$body = lang('order_pay_title');
				$order = $m->getPayOrders($obj);
				$needPay = $order["needPay"];
				$payRand = $order["payRand"];
				$out_trade_no = $obj["orderNo"]."a".$payRand;

				$data["status"] = $needPay>0?1:-1;
			}

        }

		if($data["status"]!=1){
			if(!(isset($data["msg"])) || $data["msg"]=="") $data["msg"]= lang('pay_failed');
		}else{


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
		        $pdata["paramsVa"] = WSTBase64urlDecode(input("pkey"));
		        $m = new LogPaypals();
		        $m->addPayLog($pdata);
		        return json($response->result);
			    //echo json_encode($response->result, JSON_PRETTY_PRINT);
			}catch (HttpException $ex) {
			    echo $ex->statusCode;
			    print_r($ex->getMessage());
			}
		}
		
	}
	
	

}
