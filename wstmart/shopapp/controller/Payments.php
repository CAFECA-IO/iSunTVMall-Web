<?php
namespace wstmart\shopapp\controller;
use think\Loader;
use Env;
use wstmart\shopapp\model\Orders as M;
use wstmart\common\model\Orders as OM;
use wstmart\common\model\Payments as PM;
use wstmart\common\model\ChargeItems as CM;
use wstmart\common\model\LogPayParams as LPM;
use wstmart\common\model\LogMoneys as LM;
use wstmart\common\model\Shops as SM;
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
 * 支付控制器
 */
class Payments extends Base{
    /**
    * 获取在线支付方式
    */
    public function getPayments(){
        $pa = new PM();
        $payments = $pa->getByGroup('5', 1, true);
        return json_encode(WSTReturn('ok',1,$payments));
    }
	/**
     * 支付宝支付跳转方法
     */
    public function aliPay(){
	    $m = new M();
	    $om = new OM();
	    $userId = $m->getUserId();

        $payObj = input('payObj');

        if($payObj=="recharge"){//充值
            $itemId = (int)input("itemId/d");
            $orderAmount = 0;
            if($itemId>0){
                $cm = new CM();
                $item = $cm->getItemMoney($itemId);
                $total_fee = isSet($item["chargeMoney"])?$item["chargeMoney"]:0;
            }else{
                $total_fee = (int)input("needPay/d");
            }
            $out_trade_no = WSTOrderNo();
            $obj = array();
            $obj["targetId"] = $userId;
            $obj["targetType"] = 0;// 充值对象1:商家 0:用户
            $obj["itemId"] = $itemId;
            $obj["payObj"] = $payObj;
            $subject = lang("wallets_recharge");
        }elseif($payObj=="enter"){
            $shopId = $this->getShopId();
            $obj = array();
            $obj["targetId"] = $shopId;
            $obj["targetType"] = 1;// 充值对象1:商家 0:用户
            $obj["payObj"] = $payObj;
            $out_trade_no = WSTOrderNo();
            $total_fee = (int)input("needPay/d");
            $subject = lang("pay_shop_fee");
        }



	   	require Env::get('root_path') . 'extend/alipay/aop/AopClient.php';
	   	require Env::get('root_path') . 'extend/alipay/aop/request/AlipayTradeAppPayRequest.php';
	    $m = new PM();
	    $payment = $m->getPayment("alipays");
	    $aop = new \AopClient;
		$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
		$aop->appId = $payment["appId"];
		$aop->rsaPrivateKey = $payment["rsaPrivateKey"];
		$aop->format = "json";
		$aop->charset = "UTF-8";
		$aop->signType = "RSA2";
		$aop->alipayrsaPublicKey = $payment["alipayrsaPublicKey"];
		//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
		$request = new \AlipayTradeAppPayRequest();
		//SDK已经封装掉了公共参数，这里只需要传入业务参数
		$bizcontent = "{\"body\":\"$subject\","
						. "\"subject\": \"$subject\","
						. "\"out_trade_no\": \"$out_trade_no\","
						. "\"timeout_express\": \"30m\","
						. "\"total_amount\": \"$total_fee\","
						. "\"product_code\":\"QUICK_MSECURITY_PAY\""
						. "}";


		$request->setNotifyUrl(url("shopapp/payments/aliNotify","",true,true));
		$request->setBizContent($bizcontent);

        /** 记录日志以便回调时区分是充值还是订单支付 **/
        $data = array();
        $data["userId"] = $userId;
        $data["transId"] = $out_trade_no;
        $data["paramsVa"] = json_encode($obj);
        $data["payFrom"] = 'alipays';
        $m = new LPM();
        $m->addPayLog($data);
        /** 记录日志以便回调时区分是充值还是订单支付 **/

		//这里和普通的接口调用不同，使用的是sdkExecute
		$response = $aop->sdkExecute($request);
		//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        return json_encode(WSTReturn('ok',1,$response));
    }

    /**
     * 服务器异步通知页面方法
     *
     */
    function aliNotify() {
    	$m = new M();
    	$om = new OM();

    	require Env::get('root_path') . 'extend/alipay/aop/AopClient.php';

    	$aop = new \AopClient;
        $m = new PM();
    	$payment = $m->getPayment("alipays");
		$aop->alipayrsaPublicKey = $payment["alipayrsaPublicKey"];
		$flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");

    	if ($flag) {
    		$notify_data = $_POST;
    		// 交易号
    		$trade_no = $_POST["trade_no"];
    		// 商户订单号
    		$out_trade_no = $_POST["out_trade_no"];
    		$total_fee = $_POST["total_amount"];
    		// 交易状态
    		$trade_status = $_POST["trade_status"];
    		if ($trade_status == 'TRADE_FINISHED' OR $trade_status  == 'TRADE_SUCCESS') {
    			$obj["trade_no"] = $trade_no;
    			$tradeNo = explode("a",$out_trade_no);

      			$obj["out_trade_no"] = $tradeNo[0];
    			$obj["total_fee"] = $total_fee;
    			$obj["payFrom"] = "alipays";

                $m = new LPM();
                $payParams = $m->getPayLog(["transId"=>$obj["out_trade_no"]]);
                if(isSet($payParams["payObj"]) && $payParams["payObj"]=='recharge'){
                    $obj["targetId"] = $payParams["targetId"];
                    $obj["targetType"] = $payParams["targetType"];
                    $obj["itemId"] = $payParams["itemId"];;
                    // 支付成功业务逻辑
                    $m = new LM();
                    $rs = $m->complateRecharge ( $obj );
                }else if(isSet($payParams["payObj"]) && $payParams["payObj"]=='enter'){
                    $obj["targetId"] = $payParams["targetId"];
                    $obj["targetType"] = $payParams["targetType"];
                    $obj["payFrom"] = 'alipays';
                    $obj["scene"] = 'renew';
                    // 支付成功业务逻辑
                    $m = new SM();
                    $rs = $m->completeEnter($obj);
                }
    			if($rs["status"]==1){
    				echo 'success';
    			}else{
    				echo 'fail';
    			}
    		}
    		echo "success"; // 请不要修改或删除

    	} else {
    		echo "fail";
    	}
    }



    /**
     * 微信支付
     */
    public function weixinPay(){
    	$m = new PM();
	    $om = new OM();
        $userId = model('users')->getUserId();
        $payObj = input('payObj');
        if($payObj=='recharge'){
            $cm = new CM();
            $itemId = (int)input("itemId/d");
            $targetType = 0;// 充值对象1:商家 0:用户

            $needPay = 0;
            if($itemId>0){
                $item = $cm->getItemMoney($itemId);
                $needPay = isSet($item["chargeMoney"])?$item["chargeMoney"]:0;
            }else{
                $needPay = (int)input("needPay/d");
            }
            $out_trade_no = WSTOrderNo();
            $subject = lang("wallets_recharge");
            $attach = $payObj."@".$userId."@".$targetType."@".$needPay."@".$itemId;
            $total_fee = $needPay;
        }else if($payObj=='enter'){
            $shopId = $this->getShopId();
            $targetType = 1;
            $needPay = (int)input("needPay/d");
            $out_trade_no = WSTOrderNo();
            $subject = lang("pay_shop_fee");
            $attach = $payObj."@".$shopId."@".$targetType."@".$needPay;
            $total_fee = $needPay;
        }

        header ( "Content-type: text/html; charset=utf-8" );
        require Env::get('root_path') . 'extend/wxpay/WxPayConf.php';
        require Env::get('root_path') . 'extend/wxpay/WxJsApiPay.php';
    	$wxpay = $m->getPayment ( "shopapp_weixinpays" );
    	$wxpayConfig = array();
    	$wxpayConfig ['appid'] = $wxpay ['appId']; // 微信公众号身份的唯一标识
    	$wxpayConfig ['appsecret'] = $wxpay['appsecret']; // JSAPI接口中获取openid
    	$wxpayConfig ['mchid'] = $wxpay['mchId']; // 受理商ID
    	$wxpayConfig ['key'] = $wxpay['apiKey']; // 商户支付密钥Key
    	$wxpayConfig ['curl_timeout'] = 30;
    	$wxpayConfig ['notifyurl'] = url("shopapp/payments/weixinNotify","",true,true);
    	$wxpayConfig ['returnurl'] =  "";

    	// 初始化WxPayConf
    	new \WxPayConf ( $wxpayConfig );
    	//使用统一支付接口
    	$unifiedOrder = new \UnifiedOrder();
    	$unifiedOrder->setParameter("out_trade_no",$out_trade_no);//商户订单号
    	$unifiedOrder->setParameter("notify_url",$wxpayConfig ['notifyurl']);//通知地址
    	$unifiedOrder->setParameter("trade_type","APP");//交易类型
        $unifiedOrder->setParameter("attach",$attach);//扩展参数
    	$unifiedOrder->setParameter("body",$subject);//商品描述
    	$needPay = WSTBCMoney($total_fee,0,2);
    	$unifiedOrder->setParameter("total_fee", $needPay * 100);//总金额

    	$prepay_id = $unifiedOrder->getPrepayId();
    	$obj["prepayid"] = $prepay_id;
    	$rs = $unifiedOrder->getParameters($obj);
        $data = WSTReturn('ok',1,$rs);
        return json_encode($data);

    }


    /**
     * 微信回调接口
     */
    public function weixinNotify() {

    	$m = new PM();
    	$om = new OM();
    	header ( "Content-type: text/html; charset=utf-8" );
    	require Env::get('root_path') . 'extend/wxpay/WxPayConf.php';
    	require Env::get('root_path') . 'extend/wxpay/WxJsApiPay.php';
    	$wxpay = $m->getPayment ( "shopapp_weixinpays" );

    	$wxpayConfig = array();
    	$wxpayConfig ['appid'] = $wxpay ['appId']; // 微信公众号身份的唯一标识
    	$wxpayConfig ['appsecret'] = $wxpay['appsecret']; // JSAPI接口中获取openid
    	$wxpayConfig ['mchid'] = $wxpay['mchId']; // 受理商ID
    	$wxpayConfig ['key'] = $wxpay['apiKey']; // 商户支付密钥Key
    	$wxpayConfig ['curl_timeout'] = 30;
    	$wxpayConfig ['notifyurl'] = url("shopapp/payments/weixinNotify","",true,true);
    	$wxpayConfig ['returnurl'] =  "";
    	// 初始化WxPayConf
    	new \WxPayConf ( $wxpayConfig );

    	// 使用通用通知接口
    	$notify = new \Notify();
    	// 存储微信的回调
    	$xml = file_get_contents('php://input');
    	$notify->saveData ( $xml );
    	$notify->setReturnParameter ( "return_code", "SUCCESS" ); // 设置返回码

    	$returnXml = $notify->returnXml ();

    	if ($notify->data ["return_code"] == "FAIL") {
    		// 此处应该更新一下订单状态，商户自行增删操作
    	} elseif ($notify->data ["result_code"] == "FAIL") {
    		// 此处应该更新一下订单状态，商户自行增删操作
    	} else {
    		$order = $notify->getData ();
            $obj = array();
            $obj["trade_no"] = $order['transaction_id'];
            $obj["total_fee"] = (float)$order["total_fee"]/100;
            $extras =  explode ( "@", $order ["attach"] );
            if($extras[0]=="recharge"){//充值
                $targetId = (int)$extras [1];
                $targetType = (int)$extras [2];
                $itemId = (int)$extras [4];

                $obj["out_trade_no"] = $order['out_trade_no'];
                $obj["targetId"] = $targetId;
                $obj["targetType"] = $targetType;
                $obj["itemId"] = $itemId;
                $obj["payFrom"] = 'shopapp_weixinpays';
                // 支付成功业务逻辑
                $m = new LM();
                $rs = $m->complateRecharge ( $obj );
            }else if($extras[0]=="enter"){ // 缴纳年费
                $targetId = (int)$extras [1];
                $targetType = (int)$extras [2];
                $obj["out_trade_no"] = $order['out_trade_no'];
                $obj["targetId"] = $targetId;
                $obj["targetType"] = $targetType;
                $obj["payFrom"] = 'shopapp_weixinpays';
                $obj["scene"] = 'renew';
                // 支付成功业务逻辑
                $m = new SM();
                $rs = $m->completeEnter($obj);
            }

    		if($rs["status"]==1){
    			echo 'success';
    		}else{
    			echo 'fail';
    		}
    	}

    }
}
