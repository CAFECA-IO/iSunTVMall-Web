<?php
namespace addons\presale\model;
use think\Loader;
use think\Db;
use Env;
use addons\presale\model\Presales as PRM;
use wstmart\common\model\Payments as M;
use wstmart\common\model\LogPayParams as PM;
use think\addons\BaseModel as Base;
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
 * 微信支付业务处理
 */
class WeixinpaysApp extends Base{

	/**
	 * 初始化
	 */
	private $wxpayConfig;
	private $wxpay;
	public function initialize() {
		header ("Content-type: text/html; charset=utf-8");

		require_once Env::get('root_path') . 'extend/wxpay/WxPayConf.php';
	   	require_once Env::get('root_path') . 'extend/wxpay/WxJsApiPay.php';

		$this->wxpayConfig = array();
		$m = new M();
		$this->wxpay = $m->getPayment("app_weixinpays");
		$this->wxpayConfig['appid'] = $this->wxpay['appId']; // 微信公众号身份的唯一标识
		$this->wxpayConfig['appsecret'] = $this->wxpay['appsecret']; // JSAPI接口中获取openid
		$this->wxpayConfig['mchid'] = $this->wxpay['mchId']; // 受理商ID
		$this->wxpayConfig['key'] = $this->wxpay['apiKey']; // 商户支付密钥Key

		$this->wxpayConfig['apiclient_cert'] = WSTRootPath().'/extend/wxpay/cert2/apiclient_cert.pem'; // 商户支付证书
		$this->wxpayConfig['apiclient_key'] = WSTRootPath().'/extend/wxpay/cert2/apiclient_key.pem'; // 商户支付证书

		$this->wxpayConfig['curl_timeout'] = 30;
		$this->wxpayConfig['notifyurl'] = addon_url("presale://cron/refundappnotify","",true,true);
		$this->wxpayConfig['returnurl'] = "";

	}

	/**
	 * 退款
	 */
	public function presaleRefund($amoney){
		// 初始化WxPayConf
		new \WxPayConf($this->wxpayConfig);
        $refund = new \Refund();
        $refund_no = $amoney['id'].$amoney['userId'];
        $refund->setParameter("transaction_id",$amoney['tradeNo']);//微信订单号
        $refund->setParameter("out_refund_no",$refund_no);//商户退款单号
        $refund->setParameter("total_fee",$amoney['presaleMoney']*100);//订单金额
        $refund->setParameter("refund_fee",$amoney["presaleMoney"]*100);//退款金额
        $refund->setParameter("refund_fee_type","CNY");//货币种类
        $refund->setParameter("refund_desc",lang("presale_refund"));//退款原因
        $refund->setParameter("notify_url",$this->wxpayConfig['notifyurl']);//退款原因

        $payParams = [];
        $payParams["userId"] = (int)$amoney['userId'];
        $payParams["id"] = $amoney['id'];

        $pdata = array();
        $pdata["userId"] = $amoney['userId'];
        $pdata["transId"] = $refund_no;
        $pdata["paramsVa"] = json_encode($payParams);
        $pdata["payFrom"] = 'app_weixinpays';
        $m = new PM();
        $m->addPayLog($pdata);
        $rs = $refund->getResult();
		if($rs["result_code"]=="SUCCESS"){
			return WSTReturn(lang("presale_refund_success"),1);
		}else{
			return WSTReturn($rs['err_code_des'],-1);
		}
    }

    /**
     * 异步通知
     */
   	public function presaleNotify(){
   		// 初始化WxPayConf
		new \WxPayConf($this->wxpayConfig);
   		// 使用通用通知接口
		$notify = new \Notify();
		// 存储微信的回调
		$xml = file_get_contents("php://input");
		$notify->saveData ( $xml );
		if ($notify->data ["return_code"] == "SUCCESS"){
			$order = $notify->getData ();
			$req_info = $order["req_info"];
			$reqinfo = $notify->decryptReqinfo($req_info);//解密退款加密信息
			$transId = $reqinfo["out_refund_no"];
			$m = new PM();
          	$payParams = $m->getPayLog(["transId"=>$transId]);
	   		$obj = array();
	        $obj['refundTradeNo'] = $reqinfo["refund_id"];//微信退款单号
	        $obj['id'] = $payParams['id'];
	        $m = new PRM();
	        $rs = $m->complateRefund($obj);
	        if($rs['status']==1){
	        	echo "SUCCESS";
	        }else{
	        	echo "FAIL";
	        }
	    }
   	}

}
