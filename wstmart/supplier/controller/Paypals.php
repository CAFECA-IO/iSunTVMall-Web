<?php
namespace wstmart\supplier\controller;
use think\Loader;
use Env;
//require Env::get('root_path') . '/vendor/autoload.php';
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\PaypalApi;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

use PayPalCheckoutSdk\Payments\CapturesRefundRequest;

use wstmart\common\model\Payments as M;
use wstmart\common\model\Orders as OM;
use wstmart\common\model\LogMoneys as LM;
use wstmart\common\model\Shops as SM;
use wstmart\common\model\Suppliers as SUM;
use wstmart\common\model\LogPays as PM;
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
	 */
	public function getPaypalsURL(){
		$m = new OM();
		$payObj = input("payObj/s");
		$pkey = "";
		$return_url = "";
		$needPay = 0;
		$data = array();
		$targetId = 0;
		if($payObj=="recharge"){
			$return_url = url('supplier/logmoneys/shopmoneys',[],true,true);
			$cm = new CM();
			$itmeId = (int)input("itmeId/d");
			$targetType = 3;
			$targetId = (int)session('WST_SUPPLIER.supplierId');
			$needPay = 0;
			if($itmeId>0){
				$item = $cm->getItemMoney($itmeId);
				$needPay = isSet($item["chargeMoney"])?$item["chargeMoney"]:0;
			}else{
				$needPay = (int)input("needPay/d");
			}
			$data["status"] = $needPay>0?1:-1;
			$pkey = $payObj."@".$targetId."@".$targetType."@".$needPay."@".$itmeId;
		}elseif($payObj=="enter"){
			$return_url = url('supplier/logmoneys/torenew',[],true,true);
            $needPay = (int)input("needPay");
            $targetType = 3;
            $targetId = (int)session('WST_SUPPLIER.supplierId');
            $data["status"] = $needPay>0?1:-1;
            $pkey = $payObj."@".$targetId."@".$targetType."@".$needPay;
        }

		if($data["status"]!=1){
			if(!(isSet($data["msg"])) || $data["msg"]=="") $data["msg"]= lang('pay_failed');
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
			                          "return_url" => $return_url
			                    ] 
			                 ];

			try {
			    // Call API with your client and get a response for your call
			    $response = $client->execute($request);
			   // $response = ((array)$response);
			    // If call returns body in response, you can get the deserialized version from the result attribute of the response
			   	$pdata = array();
		        $pdata["userId"] = $targetId;
		        $pdata["orderId"] = $out_trade_no;
		        $pdata["paramsVa"] = $pkey;
		        $pdata["targetType"] = 3;
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
