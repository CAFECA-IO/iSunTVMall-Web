<?php
namespace addons\auction\controller;
use think\addons\Controller;
use think\Loader;
use Env;
use wstmart\common\model\Payments as PM;
use addons\auction\model\Auctions as M;
use wstmart\common\model\Orders as OM;
use addons\auction\model\Auctions as AM;
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
class Weixinpayswx extends Controller{
	/**
	 * 初始化
	 */
	private $wxpayConfig;
	private $wxpay;
	public function initialize() {
		header ("Content-type: text/html; charset=utf-8");
		require Env::get('root_path') . 'extend/wxpay/WxPayConf.php';
		require Env::get('root_path') . 'extend/wxpay/WxJsApiPay.php';
		
		$this->wxpayConfig = array();
		$m = new PM();
		$this->wxpay = $m->getPayment("weixinpays");
		$this->wxpayConfig['appid'] = $this->wxpay['appId']; // 微信公众号身份的唯一标识
		$this->wxpayConfig['appsecret'] = $this->wxpay['appsecret']; // JSAPI接口中获取openid
		$this->wxpayConfig['mchid'] = $this->wxpay['mchId']; // 受理商ID
		$this->wxpayConfig['key'] = $this->wxpay['apiKey']; // 商户支付密钥Key
		$this->wxpayConfig['notifyurl'] = "";
		$this->wxpayConfig['curl_timeout'] = 30;
		$this->wxpayConfig['returnurl'] = "";
		// 初始化WxPayConf
		new \WxPayConf($this->wxpayConfig);
	}
	
	
	/**
	 * 获取微信URL
	 */
	public function getWeixinPaysURL(){
		$am = new AM();
		$payObj = input("payObj/s");
		$payFrom = (int)input("payFrom");//0:PC 1:微信
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
				$data["msg"] = ($data["status"]==-1)?"无需支付保证金":"";
			}
		}else{
			$auction = $am->getAuctionPay($auctionId);
			$needPay = $auction["payPrice"];
			if($auction["isPay"]==1){
				$data["status"] = -1;
				$data["msg"] = "您已缴成拍卖货款";
			}else{
				$data["status"] = $needPay>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?"无需支付拍卖货款":"";
			}
		}
		return $data;
	}
	
	public function toPay(){
		$payObj = input("payObj/s");
    	$am = new AM();
    	$obj = array();
    	$data = array();
    	$needPay = 0;
    	$auctionId = input("auctionId/d",0);
    	$userId = (int)session('WST_USER.userId');
    	$return_url = "";
    	
    	if($payObj=="bao"){//充值
    		$return_url = addon_url("auction://goods/wxdetail",array("id"=>$auctionId),true,true);
    		$auction = $am->getUserAuction($auctionId);
    		$needPay = $auction["cautionMoney"];
    		if($auction["userId"]>0){
    			header("Location:".$return_url);
				exit();
    		}else{
    			$data["status"] = $needPay>0?1:-1;
    			$data["msg"] = ($data["status"]==-1)?"无需支付保证金":"";
    		}
    	}else{
    		$auction = $am->getAuctionPay($auctionId);
    		if($auction["endPayTime"]<date("Y-m-d H:i:s")){
    			$data["status"] = -1;
    			$data["msg"] = lang('auction_have_paid_tips');
    		}else{
	    		$needPay = $auction["payPrice"];
	    		if($auction["isPay"]==1){
	    			$data["status"] = -1;
	    			$data["msg"] = "您已缴成拍卖货款";
	    		}else{
	    			$data["status"] = $needPay>0?1:-1;
	    			$data["msg"] = ($data["status"]==-1)?"无需支付拍卖货款":"";
	    		}
	    		
	    		$return_url = addon_url("auction://users/wxcheckPayStatus",array("id"=>$auctionId),true,true);
    		}
    	}
    	$notify_url = addon_url("auction://weixinpayswx/notify","",true,true);
    	if($data["status"]==1){
    		$openid = session('WST_USER.wxOpenId');
			$out_trade_no = WSTOrderNo();
			//使用jsapi接口
			$jsApi = new \JsApi();
			//使用统一支付接口
			$unifiedOrder = new \UnifiedOrder();
			$unifiedOrder->setParameter("openid",$openid);//商品描述
		
			$body = ($payObj=="bao")?"支付保证金":"支付拍卖成拍卖货款";
			
			//自定义订单号，此处仅作举例
			$unifiedOrder->setParameter("out_trade_no",$out_trade_no);//商户订单号
			$unifiedOrder->setParameter("notify_url",$notify_url);//通知地址
			$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
		
			$unifiedOrder->setParameter("body",$body);//商品描述
			$needPay = WSTBCMoney($needPay,0,2);
			$unifiedOrder->setParameter("total_fee", $needPay * 100);//总金额

			$attach = $payObj."@".$userId."@".$auctionId;
			$unifiedOrder->setParameter("attach",$attach);//附加数据
		
			$prepay_id = $unifiedOrder->getPrepayId();
			//=========步骤3：使用jsapi调起支付============
			$jsApi->setPrepayId($prepay_id);
		
			$jsApiParameters = $jsApi->getParameters();
			if($payObj=='bao'){
				$rs = $am->getPayInfo($auctionId,1);
			}else{
				$rs = $am->getPayInfo($auctionId,2);
			}

			$addonPay = array();
			$addonPay["payObj"] = $payObj;
			$addonPay["object"] = $rs['data']['auction'];
			$addonPay["jsApiParameters"] = $jsApiParameters;
			$addonPay["returnUrl"] = $return_url;
			$addonPay["needPay"] = $needPay;
			$addonPay["showUrl"] = Env::get('root_path').'addons'.DS.'auction'.DS.'view'.DS.'wechat'.DS.'index'.DS.'pay_weixin.html';
			session("addonPay",$addonPay);
			$this->redirect('wechat/weixinpays/toaddonpay');
		}
		
	}
	
	public function notify() {
		// 使用通用通知接口
		$notify = new \Notify();
		// 存储微信的回调
		$xml = file_get_contents("php://input");
		$notify->saveData ( $xml );
		if ($notify->checkSign () == FALSE) {
			$notify->setReturnParameter ( "return_code", "FAIL" ); // 返回状态码
			$notify->setReturnParameter ( "return_msg", "签名失败" ); // 返回信息
		} else {
			$notify->setReturnParameter ( "return_code", "SUCCESS" ); // 设置返回码
		}
		$returnXml = $notify->returnXml ();
		if ($notify->checkSign () == TRUE) {
			if ($notify->data ["return_code"] == "FAIL") {
				// 此处应该更新一下订单状态，商户自行增删操作
			} elseif ($notify->data ["result_code"] == "FAIL") {
				// 此处应该更新一下订单状态，商户自行增删操作
			} else {
				$order = $notify->getData ();
				$rs = $this->process($order);
				if($rs["status"]==1){
					echo "SUCCESS";
				}else{
					echo "FAIL";
				}
			}
		}
	}
	
	//订单处理
	private function process($order) {
	
		$obj = array();
		$obj["trade_no"] = $order['transaction_id'];
		$obj["out_trade_no"] = $order['out_trade_no'];
		$obj["total_fee"] = (float)$order["total_fee"]/100;
		$extras =  explode ( "@", $order ["attach"] );
		
		$obj["payObj"] = $extras[0];
		$obj["userId"] = (int)$extras[1];
		$obj["auctionId"] = (int)$extras[2];
		$obj["payFrom"] = 'weixinpays';
		// 支付成功业务逻辑
		$m = new AM();
		$rs = $m->complateCautionMoney ( $obj );
		return $rs;
		
	}

}
