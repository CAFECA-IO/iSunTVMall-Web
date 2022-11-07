<?php
namespace wstmart\shop\controller;
use think\Loader;
use Env;
use wstmart\common\model\Payments as M;
use wstmart\shop\model\SupplierOrders as OM;
use wstmart\common\model\LogMoneys as LM;
use wstmart\common\model\LogPays as PM;
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
 * 微信支付控制器
 */
class Supplierweixinpays extends Base{
	
	/**
	 * 初始化
	 */
	private $wxpayConfig;
	private $wxpay;
	public function initialize() {
		header ("Content-type: text/html; charset=utf-8");
		require Env::get('root_path') . 'extend/wxpay/WxPayConf.php';
		require Env::get('root_path') . 'extend/wxpay/WxQrcodePay.php';
		$this->wxpayConfig = array();
		$m = new M();
		$this->wxpay = $m->getPayment("weixinpays");
		$this->wxpayConfig['appid'] = $this->wxpay['appId']; // 微信公众号身份的唯一标识
		$this->wxpayConfig['appsecret'] = $this->wxpay['appsecret']; // JSAPI接口中获取openid
		$this->wxpayConfig['mchid'] = $this->wxpay['mchId']; // 受理商ID
		$this->wxpayConfig['key'] = $this->wxpay['apiKey']; // 商户支付密钥Key
		$this->wxpayConfig['notifyurl'] = url("shop/supplierweixinpays/wxNotify","",true,true);
		$this->wxpayConfig['curl_timeout'] = 30;
		$this->wxpayConfig['returnurl'] = "";
		// 初始化WxPayConf_pub
		$wxpaypubconfig = new \WxPayConf($this->wxpayConfig);
	}
	
	/**
	 * 获取微信URL
	 */
	public function getWeixinPaysURL(){
		$m = new OM();
		$payObj = input("payObj/s");
		$pkey = "";
		$data = array();
		
		$userId = (int)session('WST_USER.userId');
		$shopId = (int)session('WST_USER.shopId');
		$pkey = input("pkey");
		$pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
        $orderNo = $pkey[0];
        $isBatch = (int)$pkey[1];
        $obj= ["userId"=>$userId,"shopId"=>$shopId,"orderNo"=>$orderNo,"isBatch"=>$isBatch];
		$data = $m->checkOrderPay2($obj);
		if($data["status"]==1){
			$pkey = $payObj."@".$userId."@".$orderNo;
			if($isBatch==1){
				$pkey = $pkey."@1";
			}else{
				$pkey = $pkey."@2";
			}
		}
		$pkey = $pkey."@".$shopId;
		$data["url"] = url('shop/supplierweixinpays/createQrcode',array("pkey"=>WSTBase64urlEncode($pkey)));
		return $data;
	}
	
	public function createQrcode() {
		$pkey = WSTBase64urlDecode(input("pkey"));
		$pkeys = explode("@", $pkey );
		$flag = true;
		$needPay = 0;
		$out_trade_no = 0;
		$trade_no = 0;
		
		if(count($pkeys)!= 5){
			$this->assign('out_trade_no', "");
		}else{
			$shopId = (int)session('WST_USER.shopId');
			$obj = array();
			$obj["shopId"] = $shopId;
			$obj["orderNo"] = $pkeys[2];
			$obj["isBatch"] = $pkeys[3];
			$m = new OM();
			$order = $m->getPayOrders($obj);
			$needPay = $order["needPay"];
			$payRand = $order["payRand"];
			$body = "支付订单费用";
			$out_trade_no = $obj["orderNo"]."a".$payRand;
			$trade_no = $obj["orderNo"];
		}
		
		
		if($needPay>0){
			// 使用统一支付接口
			$wxQrcodePay = new \WxQrcodePay ();
			$wxQrcodePay->setParameter ( "body", $body ); // 商品描述
			$wxQrcodePay->setParameter ( "out_trade_no", $out_trade_no ); // 商户订单号
			$wxQrcodePay->setParameter ( "total_fee", $needPay * 100 ); // 总金额
			$wxQrcodePay->setParameter ( "notify_url", $this->wxpayConfig['notifyurl'] ); // 通知地址
			$wxQrcodePay->setParameter ( "trade_type", "NATIVE" ); // 交易类型
			$wxQrcodePay->setParameter ( "attach", "$pkey" ); // 附加数据
			$wxQrcodePay->SetParameter ( "input_charset", "UTF-8" );
			// 获取统一支付接口结果
			$wxQrcodePayResult = $wxQrcodePay->getResult ();
			$code_url = '';
			// 商户根据实际情况设置相应的处理流程
			if ($wxQrcodePayResult ["return_code"] == "FAIL") {
				// 商户自行增加处理流程
				echo "通信出错：" . $wxQrcodePayResult ['return_msg'] . "<br>";
			} elseif ($wxQrcodePayResult ["result_code"] == "FAIL") {
				// 商户自行增加处理流程
				echo "错误代码：" . $wxQrcodePayResult ['err_code'] . "<br>";
				echo "错误代码描述：" . $wxQrcodePayResult ['err_code_des'] . "<br>";
			} elseif ($wxQrcodePayResult ["code_url"] != NULL) {
				// 从统一支付接口获取到code_url
				$code_url = $wxQrcodePayResult ["code_url"];
				// 商户自行增加处理流程
			}
			$this->assign ( 'out_trade_no', $trade_no );
			$this->assign ( 'code_url', $code_url );
			$this->assign ( 'wxQrcodePayResult', $wxQrcodePayResult );
			$this->assign ( 'needPay', $needPay );
		}else{
			$flag = false;
		}
		
		if($flag){
			return $this->fetch('supplier/order_pay_step2');
		}else{
			return $this->fetch('supplier/order_pay_step3');
		}
		
	}
	
	
	/**
	 * 检查支付结果
	 */
	public function getPayStatus() {
		$trade_no = input('trade_no');
		$obj = array();
		$obj["userId"] = (int)session('WST_USER.userId');
		$obj["transId"] = $trade_no;
		$m = new PM();
		$log = $m->getPayLog($obj);
		$data = array("status"=>-1);
		// 检查是否存在，存在说明支付成功
		if(isset($log["logId"]) && $log["logId"]>0){
			$m->delPayLog($obj);
			$data["status"] = 1;
		}else{
			$data["status"] = -1;
		}
		return $data;
	}
	
	/**
	 * 微信异步通知
	 */
	public function wxNotify() {
		// 使用通用通知接口
		$wxQrcodePay = new \WxQrcodePay ();
		// 存储微信的回调
		$xml = file_get_contents("php://input");
		$wxQrcodePay->saveData ( $xml );
		// 验证签名，并回应微信。
		if ($wxQrcodePay->checkSign () == FALSE) {
			$wxQrcodePay->setReturnParameter ( "return_code", "FAIL" ); // 返回状态码
			$wxQrcodePay->setReturnParameter ( "return_msg", "签名失败" ); // 返回信息
		} else {
			$wxQrcodePay->setReturnParameter ( "return_code", "SUCCESS" ); //设置返回码
		}
		
		$returnXml = $wxQrcodePay->returnXml ();
		if ($wxQrcodePay->checkSign () == TRUE) {
			if ($wxQrcodePay->data ["return_code"] == "FAIL") {
				echo "FAIL";
			} elseif ($wxQrcodePay->data ["result_code"] == "FAIL") {
				echo "FAIL";
			} else {
				// 此处应该更新一下订单状态，商户自行增删操作
				$order = $wxQrcodePay->getData ();
				$trade_no = $order["transaction_id"];
				$total_fee = $order ["total_fee"];
				$pkey = $order ["attach"] ;
				$pkeys = explode ( "@", $pkey );
				$out_trade_no = 0;
				$userId = 0;
				
				$userId = (int)$pkeys [1];
				$out_trade_no = $pkeys[2];
				$isBatch = (int)$pkeys[3];
				$shopId = (int)$pkeys[4];
				// 商户订单
				$obj = array ();
				$obj["trade_no"] = $trade_no;
				$obj["out_trade_no"] = $out_trade_no;
				$obj["isBatch"] = $isBatch;
				$obj["total_fee"] = (float)$total_fee/100;
				$obj["userId"] = $userId;
				$obj["shopId"] = $shopId;
				$obj["payFrom"] = 'weixinpays';
				// 支付成功业务逻辑
				$m = new OM();
				$rs = $m->complatePay ( $obj );
				
				if($rs["status"]==1){
					$data = array();
					$data["userId"] = $userId;
					$data["transId"] = $out_trade_no;
					$m = new PM();
					$m->addPayLog($data);
					echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
				}else{
					echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
				}
			}
		}else{
			echo "FAIL";
		}
	}

	/**
	 * 检查支付结果
	 */
	public function paySuccess() {
		return $this->fetch('supplier/order_pay_step3');
	}

}
