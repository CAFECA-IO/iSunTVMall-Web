<?php
namespace addons\presale\controller;
use think\addons\Controller;
use think\Loader;
use Env;
use wstmart\weapp\model\Users as U;
use wstmart\common\model\Payments as PM;
use addons\presale\model\Presales as M;
use addons\presale\model\Orders as OM;
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
class Weixinpaysweapp extends Controller{
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
		$this->wxpayConfig['appid'] = WSTConf('CONF.weAppId'); // 小程序appId
		$this->wxpayConfig['appsecret'] = $this->wxpay['appsecret']; // JSAPI接口中获取openid
		$this->wxpayConfig['mchid'] = $this->wxpay['mchId']; // 受理商ID
		$this->wxpayConfig['key'] = $this->wxpay['apiKey']; // 商户支付密钥Key
		$this->wxpayConfig['notifyurl'] = addon_url("presale://weixinpaysweapp/notify","",true,true);
		$this->wxpayConfig['returnurl'] = '';
		$this->wxpayConfig['curl_timeout'] = 30;

		// 初始化WxPayConf
		new \WxPayConf($this->wxpayConfig);
	}
	/**
	* 微信支付
	*/
	public function toPay(){

    	$userId = model('weapp/index')->getUserId();
        if($userId <= 0){
            return json_encode(WSTReturn(lang('presale_no_login'),-999));
        }
    	$m = new M();
    	$pkey = input("pkey");
        $pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
        $payObj = $pkey[0];
        $porderId = (int)$pkey[1];

        if($payObj=="ding"){//定金
            $presale = $m->getPresalePay($porderId,$userId);
            $needPay = $presale["depositMoney"];
            if($presale["isCanPay"]==0){
                $data["status"] = -1;
                $data["msg"] = lang("presale_have_pay_deposit_title");
            }else{
                $data["status"] = $needPay>0?1:-1;
                $data["msg"] = ($data["status"]==-1)?lang("presale_no_need_pay_deposit_tips"):"";
            }
        }else{//尾款
            $presale = $m->getPresalePay($porderId,$userId);
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
        }

    	if($data["status"]==1){
			$out_trade_no = WSTOrderNo();

            $body = ($payObj=="ding")?lang("presale_pay_deposit_title"):lang("presale_pay_balance_title");

            // 	附加数据 给异步回调函数调用
            $attach = $payObj."@".$userId."@".$porderId;

	    	//使用jsapi接口
            $jsApi = new \JsApi();
            //使用统一支付接口
            $unifiedOrder = new \UnifiedOrder();

            $u = new U();
            $openid = $u->userOpenid($userId);
            $unifiedOrder->setParameter("openid",$openid);//商品描述

            //自定义订单号，此处仅作举例
            $unifiedOrder->setParameter("out_trade_no",$out_trade_no);//商户订单号
            $unifiedOrder->setParameter("notify_url",$this->wxpayConfig ['notifyurl']);//通知地址
            $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
            $unifiedOrder->setParameter("body",$body);//商品描述
            $needPay = WSTBCMoney($needPay,0,2);
            $unifiedOrder->setParameter("total_fee", $needPay * 100);//总金额
            $unifiedOrder->setParameter("attach",$attach);//附加数据

            $prepay_id = $unifiedOrder->getPrepayId();
            //=========步骤3：使用jsapi调起支付============
            $jsApi->setPrepayId($prepay_id);
            $jsApiParameters = $jsApi->getParameters();

            return jsonReturn('success',1,json_decode($jsApiParameters,true));
		}else{
			return json_encode($data);
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
			$notify->setReturnParameter ( "return_msg", lang("presale_sign_fail") ); // 返回信息
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
				$order = $notify->getData();
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
        $obj["porderId"] = (int)$extras[2];

		$obj["payFrom"] = 'weixinpays';
		// 支付成功业务逻辑
		$m = new OM();
		$rs = $m->complatePay ( $obj );

		return $rs;

	}

}
