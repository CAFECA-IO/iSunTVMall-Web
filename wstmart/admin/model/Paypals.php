<?php
namespace wstmart\admin\model;
use think\Loader;
use think\Db;
use Env;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\PaypalApi;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;

use wstmart\common\model\Payments as M;
use wstmart\common\model\LogPayParams as PM;
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
 * Paypal支付业务处理
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
	 * 退款
	 */
	public function orderRefund($refund,$order){

        $content = input('post.content');
        $refundId = (int)input('post.id');

        $isSandbox = $this->wxpay['isSandbox'];
        $clientId = $this->wxpay['clientId'];
        $clientSecret = $this->wxpay['secret'];
        $environment = null;

        try {

            if($isSandbox==1){
                //Sandbox
                $environment = new SandboxEnvironment($clientId, $clientSecret);
            }else{
                //live
                $environment = new ProductionEnvironment($clientId, $clientSecret);
            }

            $client = new PayPalHttpClient($environment);

            //$notify_url = url("admin/shops/wxenterrefundnotify","",true,true);
            $captureId = $order['tradeNo'];
            $request = new CapturesRefundRequest($captureId);
            $request->body = array(
                      'amount' =>
                        array(
                          'value' => $refund["backMoney"],
                          'currency_code' => 'HKD'
                        ),
                        "note_to_payer"=> lang('weixinpaysApp_info1', [$order['orderNo']])
                    );
            
            $client = new PayPalHttpClient($environment);
            $response = $client->execute($request);
            file_put_contents("runtime/log_paypal_refund.txt",date('Y-m-d H:i:s')."----webhook_body----".json_encode($response)."----\n", FILE_APPEND);
            
            if(isset($response->result)){
                $details = $response->result;
                if($details->status=='COMPLETED'){

                    $obj = array();
                    $obj['refundTradeNo'] = $details->id;//Paypal退款单号
                    $obj['content'] = $content;
                    $obj['refundId'] = $refundId;
                    $rs = model('admin/OrderRefunds')->complateOrderRefund($obj);
                    if($rs['status']==1){
                        return WSTReturn(lang('orderRefunds_return1'),1); 
                    }else{
                        return WSTReturn(lang('op_err'),1);
                    }
                }else {
                    return WSTReturn(lang('op_err'),-1); 
                }
            }else{
                echo json_encode($response, JSON_PRETTY_PRINT), "\n"; 
            }
        } catch(Exception $ex) {
           return WSTReturn(lang('op_err'),-1); 
        }
        //echo json_encode($response->result, JSON_PRETTY_PRINT), "\n"; 
       
    }

    /**
     * 入驻不通过，退款年费
     */
    public function enterRefund($obj){

        $refund_no = $obj['orderNo'].$obj['userId'];
        $backMoney = $obj['money'];
        $tradeNo = $obj['tradeNo'];
        $refund_reason = lang('weixinpays_err1');

        $payParams = [];
        $payParams["userId"] = $obj['userId'];
        
        $pdata = array();
        $pdata["userId"] = $obj['userId'];
        $pdata["transId"] = $refund_no;
        $pdata["paramsVa"] = json_encode($payParams);
        $pdata["payFrom"] = 'paypals';
        $m = new PM();
        $m->addPayLog($pdata);

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

        //$notify_url = url("admin/shops/wxenterrefundnotify","",true,true);
        $captureId = $order['tradeNo'];
        $request = new CapturesRefundRequest($captureId);
        $request->body = array(
                  'amount' =>
                    array(
                      'value' => $refund["backMoney"],
                      'currency_code' => 'HKD'
                    ),
                    "note_to_payer"=> lang('weixinpaysApp_info1', [$order['orderNo']])
                );
        
        $client = new PayPalHttpClient($environment);
        $response = $client->execute($request);
        print_r(json_encode($response));
    }

    /**
     * 异步通知
     */
   	public function notify(){

        $headers = $_SERVER;

        $cert_url = $headers[ 'HTTP_PAYPAL_CERT_URL' ];
        $transmission_id = $headers[ 'HTTP_PAYPAL_TRANSMISSION_ID' ];
        $timestamp = $headers[ 'HTTP_PAYPAL_TRANSMISSION_TIME' ];
        $algo = $headers[ 'HTTP_PAYPAL_AUTH_ALGO' ];
        $signature = $headers[ 'HTTP_PAYPAL_TRANSMISSION_SIG' ];
        $webhook_id = $this->wxpay['refundWebhookId'];
        $webhook_body = file_get_contents("php://input");

        file_put_contents("runtime/log_paypal_refund_nodify.txt",date('Y-m-d H:i:s')."----webhook_body----".$webhook_body."----\n", FILE_APPEND);
        file_put_contents("runtime/log_paypal_refund_nodify.txt",date('Y-m-d H:i:s')."----headers----".json_encode($headers)."----\n", FILE_APPEND); 
        try {
            $paypalApi = new PaypalApi();
            if( $paypalApi->verify_webhook( $cert_url, $transmission_id, $timestamp, $webhook_id, $algo, $signature, $webhook_body ) ) {
              
                file_put_contents("runtime/log_paypal_refund_nodify.txt",date('Y-m-d H:i:s')."----verify_webhook----ok----\n", FILE_APPEND); 
                $details = json_decode($webhook_body,true);
                
                /*$order = json_decode($cipherText,true);
                if ($order["refund_status"] == "SUCCESS"){
                    $transId = $order["out_refund_no"];
                    $m = new PM();
                    $payParams = $m->getPayLog(["transId"=>$transId]);
                    $content = $payParams['content'];
                    $refundId = $payParams['refundId'];

                    $obj = array();
                    $obj['refundTradeNo'] = $order["refund_id"];//Paypal退款单号
                    $obj['content'] = $content;
                    $obj['refundId'] = $refundId;
                    $rs = model('admin/OrderRefunds')->complateOrderRefund($obj);
                    if($rs['status']==1){
                        echo '{"code": "SUCCESS","message": "成功"}';
                    }else{
                        echo '{"code": "FAIL","message": "FAIL"}';
                    }
                }else{
                    echo '{"code": "FAIL","message": "FAIL"}';
                }
                */
            }else{
                echo '{"code": "FAIL","message": "签名失败"}';
            }
        } catch(Exception $ex) {
            file_put_contents("runtime/log_paypal_nodify.txt",date('Y-m-d H:i:s')."--------Something went wrong during verification!----\n", FILE_APPEND);
            //echo "Something went wrong during verification!";
        }

   	}

    /**
     * 异步通知
     */
    public function enterRefundNotify(){

        require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Crypto/AesGcm.php';
        require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Crypto/Rsa.php';

        $wxHeader = $_SERVER;
        $postData = file_get_contents("php://input");
        $wechatpay_timestamp = $wxHeader['HTTP_WECHATPAY_TIMESTAMP'];
        $wechatpay_nonce = $wxHeader['HTTP_WECHATPAY_NONCE'];
        $wechatpay_signature = $wxHeader['HTTP_WECHATPAY_SIGNATURE'];
        $signature_str = $wechatpay_timestamp."\n".$wechatpay_nonce."\n".$postData."\n";

        $publicKey = $this->wxpay['publicKey'];
        $signStatus =  \WeChatPay\Crypto\Rsa::verify($signature_str,$wechatpay_signature,$publicKey);

        if($signStatus){
            $wxResult = json_decode($postData,true);
            $cipherText = \WeChatPay\Crypto\AesGcm::decrypt($wxResult['resource']['ciphertext'], $this->wxpay['apiKey'],$wxResult['resource']['nonce'],$wxResult['resource']['associated_data']);

            $order = json_decode($cipherText,true);
            if ($order["refund_status"] == "SUCCESS"){
                $transId = $order["out_refund_no"];
                
                $m = new PM();
                $payParams = $m->getPayLog(["transId"=>$transId]);
                $userId = $payParams['userId'];
                // 更新店铺的到期日期、退款状态
                $shop = Db::name('shops')->where(['userId'=>$userId])->find();
                $shopExpireDate = $shop["expireDate"];
                $newExpireDate = date('Y-m-d',strtotime("$shopExpireDate -1 year"));
                $shopsData['expireDate'] = $newExpireDate;
                $shopsData['isRefund'] = 1;
                $rs = Db::name('shops')->where(['userId'=>$userId])->update($shopsData);
                // 更新缴费记录
                Db::name('shop_fees')->where(['shopId'=>$shop['shopId'],'dataFlag'=>1,'isRefund'=>0])->update(['isRefund'=>1]);

                if($rs){
                    echo '{"code": "SUCCESS","message": "成功"}';
                }else{
                    echo '{"code": "FAIL","message": "FAIL"}';
                }
            }else{
                echo '{"code": "FAIL","message": "FAIL"}';
            }
            
        }else{
            echo '{"code": "FAIL","message": "签名失败"}';
        }

    }

    /**
     * 供货商入驻不通过，退款年费
     */
    public function supplierEnterRefund($obj){

        $refund_no = $obj['orderNo'].$obj['userId'];
        $backMoney = $obj['money'];
        $tradeNo = $obj['tradeNo'];
        $refund_reason = lang('weixinpays_err2');

        $payParams = [];
        $payParams["userId"] = $obj['userId'];

        $pdata = array();
        $pdata["userId"] = $obj['userId'];
        $pdata["transId"] = $refund_no;
        $pdata["paramsVa"] = json_encode($payParams);
        $pdata["payFrom"] = 'paypals';
        $m = new PM();
        $m->addPayLog($pdata);

        $notify_url = url("admin/suppliers/wxenterrefundnotify","",true,true);
        
     
    }

    /**
     * 异步通知
     */
    public function supplierEnterRefundNotify(){

        require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Crypto/AesGcm.php';
        require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Crypto/Rsa.php';

        $wxHeader = $_SERVER;
        $postData = file_get_contents("php://input");
        $wechatpay_timestamp = $wxHeader['HTTP_WECHATPAY_TIMESTAMP'];
        $wechatpay_nonce = $wxHeader['HTTP_WECHATPAY_NONCE'];
        $wechatpay_signature = $wxHeader['HTTP_WECHATPAY_SIGNATURE'];
        $signature_str = $wechatpay_timestamp."\n".$wechatpay_nonce."\n".$postData."\n";

        $publicKey = $this->wxpay['publicKey'];
        $signStatus =  \WeChatPay\Crypto\Rsa::verify($signature_str,$wechatpay_signature,$publicKey);

        if($signStatus){
            $wxResult = json_decode($postData,true);
            $cipherText = \WeChatPay\Crypto\AesGcm::decrypt($wxResult['resource']['ciphertext'], $this->wxpay['apiKey'],$wxResult['resource']['nonce'],$wxResult['resource']['associated_data']);

            $order = json_decode($cipherText,true);
            if ($order["refund_status"] == "SUCCESS"){
                $transId = $order["out_refund_no"];

                $m = new PM();
                $payParams = $m->getPayLog(["transId"=>$transId]);
                $userId = $payParams['userId'];
                // 更新店铺的到期日期、退款状态
                $supplier = Db::name('suppliers')->where(['userId'=>$userId])->find();
                $supplierExpireDate = $supplier["expireDate"];
                $newExpireDate = date('Y-m-d',strtotime("$supplierExpireDate -1 year"));
                $suppliersData['expireDate'] = $newExpireDate;
                $suppliersData['isRefund'] = 1;
                $rs = Db::name('suppliers')->where(['userId'=>$userId])->update($suppliersData);
                // 更新缴费记录
                Db::name('supplier_fees')->where(['supplierId'=>$supplier['supplierId'],'dataFlag'=>1,'isRefund'=>0])->update(['isRefund'=>1]);

            }else{
                echo '{"code": "FAIL","message": "FAIL"}';
            }
            
        }else{
            echo '{"code": "FAIL","message": "签名失败"}';
        }

    }

}
