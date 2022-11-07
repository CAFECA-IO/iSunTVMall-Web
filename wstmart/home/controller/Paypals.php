<?php
namespace wstmart\home\controller;
use think\Loader;
use Env;
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
		$out_trade_no = "";
		$needPay = 0;
		$data = array();
		if($payObj=="recharge"){
			$userId = (int)session('WST_USER.userId');
			$cm = new CM();
			$itmeId = (int)input("itmeId/d");
			$targetType = (int)input("targetType/d");
			$targetId = (int)session('WST_USER.userId');
			if($targetType==1){//商家
				$targetId = (int)session('WST_USER.shopId');
			}
			if($itmeId>0){
				$item = $cm->getItemMoney($itmeId);
				$needPay = isSet($item["chargeMoney"])?$item["chargeMoney"]:0;
			}else{
				$needPay = (int)input("needPay/d");
			}
			$data["status"] = $needPay>0?1:-1;
			$pkey = $payObj."@".$targetId."@".$targetType."@".$needPay."@".$itmeId;
			$out_trade_no = WSTOrderNo();
		}elseif($payObj=="enter"){
		    $flowId = (int)input("flowId");
            $pkey = input("pkey");
            $pkey = WSTBase64urlDecode($pkey);
            $pkey = explode('@',$pkey);
            
            $userId = (int)session('WST_USER.userId');
            $trade = model("shops")->getTradeFee($userId);
            $needPay = $trade['tradeFee'];
            $targetType = 0;
            $targetId = (int)session('WST_USER.userId');
            $data["status"] = $needPay>0?1:-1;
            $pkey = $payObj."@".$targetId."@".$targetType."@".$needPay."@".$flowId;
            $out_trade_no = WSTOrderNo();
        }elseif($payObj=="supplier_enter"){
            $flowId = (int)input("flowId");
            $pkey = input("pkey");
            $pkey = WSTBase64urlDecode($pkey);
            $pkey = explode('@',$pkey);

            $userId = (int)session('WST_USER.userId');
            $trade = model("suppliers")->getTradeFee($userId);
            $needPay = $trade['tradeFee'];
            $targetType = 0;
            $targetId = (int)session('WST_USER.userId');
            $data["status"] = $needPay>0?1:-1;
            $pkey = $payObj."@".$targetId."@".$targetType."@".$needPay."@".$flowId;
            $out_trade_no = WSTOrderNo();
        }else{
			$userId = (int)session('WST_USER.userId');
			$pkey = input("pkey");
			$pkey = WSTBase64urlDecode($pkey);
	        $pkey = explode('@',$pkey);
	        $orderNo = $pkey[0];
	        $isBatch = (int)$pkey[1];
	        $obj= ["userId"=>$userId,"orderNo"=>$orderNo,"isBatch"=>$isBatch];
			$data = $m->checkOrderPay2($obj);
			if($data["status"]==1){
				$pkey = $payObj."@".$userId."@".$orderNo;
				if($isBatch==1){
					$pkey = $pkey."@1";
				}else{
					$pkey = $pkey."@2";
				}
			}

			$obj = array();
			$obj["userId"] = $userId;
			$obj["orderNo"] = $orderNo;
			$obj["isBatch"] = $isBatch;
			$m = new OM();
			$order = $m->getPayOrders($obj);
			$needPay = $order["needPay"];
			$payRand = $order["payRand"];
			$out_trade_no = $obj["orderNo"]."a".$payRand;
		}
		if($data["status"]!=1){
			if(!(isSet($data["msg"])) || $data["msg"]=="") $data["msg"]= lang('pay_failed');
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
			                          "cancel_url" => url('home/orders/waitPay',[],true,true),
			                          "return_url" => url('home/orders/waitReceive',[],true,true)
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
	
	
	/**
	 * Paypal异步通知
	 */
	public function paypalNotify() {
		
		$headers = $_SERVER;

		$cert_url = $headers[ 'HTTP_PAYPAL_CERT_URL' ];
		$transmission_id = $headers[ 'HTTP_PAYPAL_TRANSMISSION_ID' ];
		$timestamp = $headers[ 'HTTP_PAYPAL_TRANSMISSION_TIME' ];
		$algo = $headers[ 'HTTP_PAYPAL_AUTH_ALGO' ];
		$signature = $headers[ 'HTTP_PAYPAL_TRANSMISSION_SIG' ];
		$webhook_id = $this->wxpay['webhookId']; // Replace with your webhook ID
		$webhook_body = file_get_contents("php://input");

		file_put_contents("runtime/log_paypal_nodify.txt",date('Y-m-d H:i:s')."----webhook_body----".$webhook_body."----\n", FILE_APPEND);
		file_put_contents("runtime/log_paypal_nodify.txt",date('Y-m-d H:i:s')."----headers----".json_encode($headers)."----\n", FILE_APPEND); 
		try {
			$paypalApi = new PaypalApi();
		  	if( $paypalApi->verify_webhook( $cert_url, $transmission_id, $timestamp, $webhook_id, $algo, $signature, $webhook_body ) ) {
			  
				file_put_contents("runtime/log_paypal_nodify.txt",date('Y-m-d H:i:s')."----verify_webhook----ok----\n", FILE_APPEND); 
			  	$details = json_decode($webhook_body,true);
				$out_trade_no = $trade_no = '';
				if ($details == NULL) {
					file_put_contents("runtime/log_paypal_nodify.txt",date('Y-m-d H:i:s')."--------Failed to convert JSON----\n", FILE_APPEND);
					header('HTTP/1.1 400 Bad Request');exit();
					return;
					//echo 'Failed to convert JSON';
				} elseif ($details['event_type']=='CHECKOUT.ORDER.APPROVED' && isset($details['resource']['purchase_units'][0]['custom_id'])) {
					
					if(isset($details['resource']['purchase_units'][0]['payments']['captures'][0]['status']) && $details['resource']['purchase_units'][0]['payments']['captures'][0]['status']=='COMPLETED'){
						$out_trade_no = $details['resource']['purchase_units'][0]['custom_id'];
						$trade_no = $details['resource']['purchase_units'][0]['payments']['captures'][0]['id'];
						
						$m = new LogPaypals();
						$payLog = $m->getPayLog(['orderId'=>$out_trade_no]);

						$total_fee = $details['resource']['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
						$pkey = $payLog ["paramsVa"] ;
						$pkeys = explode ( "@", $pkey );
						$userId = 0;
						if($pkeys[0]=="recharge"){//充值
							$targetId = (int)$pkeys [1];
							$userId = $targetId;
							$targetType = (int)$pkeys [2];
							$itemId = (int)$pkeys [4];
							$obj = array ();
							$obj["trade_no"] = $trade_no;
							$obj["out_trade_no"] = $out_trade_no;
							$obj["targetId"] = $targetId;
							$obj["targetType"] = $targetType;
							$obj["itemId"] = $itemId;
							$obj["total_fee"] = (float)$total_fee;
							$obj["payFrom"] = 'paypals';
							// 支付成功业务逻辑
							$m = new LM();
							$rs = $m->complateRecharge ( $obj );
						}elseif($pkeys[0]=="enter"){ // 缴纳年费
			                $targetId = (int)$pkeys [1];
			                $userId = $targetId;
			                $targetType = (int)$pkeys [2];
			                $obj = array ();
			                $obj["trade_no"] = $trade_no;
			                $obj["out_trade_no"] = $out_trade_no;
			                $obj["targetId"] = $targetId;
			                $obj["targetType"] = $targetType;
			                $obj["total_fee"] = (float)$total_fee;
			                $obj["payFrom"] = 'paypals';
			                $obj["scene"] = ($targetType==0)?'enter':'enter2';
			                // 支付成功业务逻辑
			                $m = new SM();
			                $rs = $m->completeEnter($obj);
			            }elseif($pkeys[0]=="supplier_enter"){ // 供货商缴纳年费
			                $targetId = (int)$pkeys [1];
			                $userId = $targetId;
			                $targetType = (int)$pkeys [2];
			                $obj = array ();
			                $obj["trade_no"] = $trade_no;
			                $obj["out_trade_no"] = $out_trade_no;
			                $obj["targetId"] = $targetId;
			                $obj["targetType"] = $targetType;
			                $obj["total_fee"] = (float)$total_fee;
			                $obj["payFrom"] = 'paypals';
			                $obj["scene"] = ($targetType==0)?'enter':'enter2';;
			                // 支付成功业务逻辑
			                $m = new SUM();
			                $rs = $m->completeEnter($obj);
			            }elseif($pkeys[0]=="bao" || $pkeys[0]=="deal"){ // 拍卖保证金/尾款
			            	$obj = array ();
			                $obj["payObj"] = $pkeys[0];
			                $obj["trade_no"] = $trade_no;
							$obj["out_trade_no"] = $out_trade_no;
							$obj["userId"] = (int)$pkeys[1];
							$obj["auctionId"] = (int)$pkeys[2];
							$obj["total_fee"] = (float)$total_fee;
							$obj["payFrom"] = 'paypals';
							// 支付成功业务逻辑
							$m = new \addons\auction\model\Auctions();
							$rs = $m->complateCautionMoney ( $obj );
			            }elseif($pkeys[0]=="ding" || $pkeys[0]=="surplus"){ // 预售定金/尾款
			            	
							$obj = array ();
							$obj["payObj"] = $pkeys[0];
							$obj["trade_no"] = $trade_no;
							$obj["out_trade_no"] = $out_trade_no;
							$obj["userId"] = (int)$payLog['userId'];
							$obj["porderId"] = (int)$pkeys [1];
							$obj["total_fee"] = (float)$total_fee;
							$obj["payFrom"] = 'paypals';

							// 支付成功业务逻辑
							$m = new \addons\presale\model\Orders();
							$rs = $m->complatePay ( $obj );
			            }else{//订单支付
							$userId = (int)$pkeys [1];
							$out_trade_no = $pkeys[2];
							$isBatch = (int)$pkeys[3];
							// 商户订单
							$obj = array ();
							$obj["trade_no"] = $trade_no;
							$obj["out_trade_no"] = $out_trade_no;
							$obj["isBatch"] = $isBatch;
							$obj["total_fee"] = (float)$total_fee;
							$obj["userId"] = $userId;
							$obj["payFrom"] = 'paypals';
							// 支付成功业务逻辑
							$m = new OM();
							$rs = $m->complatePay ( $obj );
						}

						if($rs["status"]==1){
							
							file_put_contents("runtime/log_paypal_nodify.txt",date('Y-m-d H:i:s')."--------1111----\n", FILE_APPEND);
							$data = array();
							$data["userId"] = $userId;
							$data["transId"] = $out_trade_no;
							$m = new PM();
							$m->addPayLog($data);
							
							file_put_contents("runtime/log_paypal_nodify.txt",date('Y-m-d H:i:s')."--------SUCCESS----\n", FILE_APPEND);
							header('HTTP/1.1 200 OK');exit();
						}else{
							file_put_contents("runtime/log_paypal_nodify.txt",date('Y-m-d H:i:s')."--------FAIL----\n", FILE_APPEND);
							//echo '{"status": "FAIL","message": "FAIL"}';
							header('HTTP/1.1 400 Bad Request');exit();
						}
					}
					
				}
		  	} else {
			  	file_put_contents("runtime/log_paypal_nodify.txt",date('Y-m-d H:i:s')."--------Verification failed!----\n", FILE_APPEND);
			  	//echo "Verification failed!";
			    // Verification failed!
			    header('HTTP/1.1 400 Bad Request');exit();
		  	}
		} catch(Exception $ex) {
			file_put_contents("runtime/log_paypal_nodify.txt",date('Y-m-d H:i:s')."--------Something went wrong during verification!----\n", FILE_APPEND);
			//echo "Something went wrong during verification!";
			header('HTTP/1.1 400 Bad Request');exit();
		}

	}

	/**
	 * 检查支付结果
	 */
	public function paySuccess() {
		return $this->fetch('order_pay_step3');
	}

    /*
     * 入驻缴纳年费同步回调方法
     */
    function payAnnualFeeSuccess(){
        return $this->redirect(url("home/shops/joinstepnext",array('id'=>(int)input("flowId")),true,true));
    }

    /**
     * 检测入驻商城时有步骤有没有遗漏，不允许跳过步骤
     */
    public function checkStep($flowId){
        $this->checkUserType('shop');
        if((int)WSTConf('CONF.isOpenShopApply')!=1)return;
        $tmpShopApplyFlow = session('tmpShopApplyFlow');
        $tmpApplyStep = (int)session('tmpApplyStep');
        //如果没有建立数组则强制重新开始
        if(!$tmpShopApplyFlow){
            return $this->redirect(Url('home/shops/join'));
        }
        $flowSteps = [];
        $isFind = false;
        foreach ($tmpShopApplyFlow as $key => $v) {
            $flowSteps[] = $v['flowId'];
            if($v['flowId']==$tmpApplyStep){
                $isFind = true;
                break;
            }
        }
        //没找到这个环节强制重新开始
        if(!$isFind){
            $this->redirect(Url('home/shops/joinStepNext',array('id'=>$tmpShopApplyFlow[0]['flowId'])));
            exit();
        }
        //如果找到则判断是否当前环节是否有效
        if(!in_array($flowId,$flowSteps)){
            $flowId = end($flowSteps);
            $this->redirect(Url('home/shops/joinStepNext',array('id'=>$flowId)));
            exit();
        }
    }

    /*
     * 供货商入驻缴纳年费同步回调方法
     */
    function supplierPayAnnualFeeSuccess(){
        return $this->redirect(url("home/suppliers/joinstepnext",array('id'=>(int)input("flowId")),true,true));
    }

    /**
     * 检测供货商入驻商城时有步骤有没有遗漏，不允许跳过步骤
     */
    public function supplierCheckStep($flowId){
        $this->checkUserType('supplier');
        if((int)WSTConf('CONF.isOpenSupplierApply')!=1)return;
        $tmpSupplierApplyFlow = session('tmpSupplierApplyFlow');
        $tmpApplyStep = (int)session('tmpApplyStep');
        //如果没有建立数组则强制重新开始
        if(!$tmpSupplierApplyFlow){
            return $this->redirect(Url('home/suppliers/join'));
        }
        $flowSteps = [];
        $isFind = false;
        foreach ($tmpSupplierApplyFlow as $key => $v) {
            $flowSteps[] = $v['flowId'];
            if($v['flowId']==$tmpApplyStep){
                $isFind = true;
                break;
            }
        }
        //没找到这个环节强制重新开始
        if(!$isFind){
            $this->redirect(Url('home/suppliers/joinStepNext',array('id'=>$tmpSupplierApplyFlow[0]['flowId'])));
            exit();
        }
        //如果找到则判断是否当前环节是否有效
        if(!in_array($flowId,$flowSteps)){
            $flowId = end($flowSteps);
            $this->redirect(Url('home/suppliers/joinStepNext',array('id'=>$flowId)));
            exit();
        }
    }

    // 检测用户账号
    public function checkUserType($type=''){
        $USER = session('WST_USER');
        if($type=='shop'){
            if(!($USER['userType']==0 || $USER['userType']==1)){
                if(request()->isAjax()){
                    die('{"status":-999,"msg":"'.lang('shop_enter_account_reday_desc').'"}');
                }else{
                    $this->redirect('home/shops/disableApply');
                    exit;
                }
            }
        }else{
            if(!($USER['userType']==0 || $USER['userType']==3)){
                if(request()->isAjax()){
                    die('{"status":-999,"msg":"'.lang('suppler_enter_account_reday_desc').'"}');
                }else{
                    $this->redirect('home/suppliers/disableApply');
                    exit;
                }
            }
        }
    }


}
