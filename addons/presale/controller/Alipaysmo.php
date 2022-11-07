<?php
namespace addons\presale\controller;
use think\Loader;
use Env;
use think\addons\Controller;
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
 * 阿里支付控制器
 */
class Alipaysmo extends Controller{

	public function getAlipaysUrl(){

		$m = new M();
		$pkey = input("pkey");
        $vpkey = WSTBase64urlDecode($pkey);
        $vpkey = explode('@',$vpkey);
        $payObj = $vpkey[0];
        $porderId = (int)$vpkey[1];
		$data = array();
		if($payObj=="ding"){
			$presale = $m->getPresalePay($porderId);
			$orderAmount = $presale["depositMoney"];
			if($presale["isCanPay"]==0){
				$data["status"] = -1;
				$data["msg"] = lang('presale_cannt_pay_deposit_tips');
			}else{
				$data["status"] = $orderAmount>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('presale_no_need_pay_deposit_tips'):"";
			}
		}else{
			$presale = $m->getPresalePay($porderId);
			if($presale["startPayTime"]>date("Y-m-d H:i:s")){
                $data["status"] = -1;
                $data["msg"] = lang('presale_cannt_pay_balance_tips');
            }else if($presale["endPayTime"]<date("Y-m-d H:i:s")){
                $data["status"] = -1;
                $data["msg"] = lang('presale_over_time_balance_tips');
            }else{
				$orderAmount = $presale["surplusMoney"];
				if($presale["presaleStatus"]==2){
                    $data["status"] = -1;
                    $data["msg"] = lang('presale_has_pay_balance_tips');
                }else{
                    $data["status"] = $orderAmount>0?1:-1;
                    $data["msg"] = ($data["status"]==-1)?lang('presale_not_need_pay_balance_tips'):"";
                }
			}
		}
		return $data;
	}
	
    /**
     * 支付宝支付跳转方法
     */
    public function toAliPay(){

        $pkey = input('pkey');
        $pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
        $payObj = $pkey[0];
        $porderId = (int)$pkey[1];

    	$m = new M();
    	$obj = array();
    	$data = array();
    	$orderAmount = 0;
    	$userId = (int)session('WST_USER.userId');
    	$call_back_url = "";
    	
    	if($payObj=="ding"){//充值
    		$presale = $m->getPresalePay($porderId);
    		$orderAmount = $presale["depositMoney"];
    		if($presale["isCanPay"]==0){
    			$data["status"] = -1;
                session('0001',lang('presale_cannt_pay_deposit_tips'));
                $this->redirect('mobile/error/message',['code'=>'0001']);
    		}else{
    			$data["status"] = $orderAmount>0?1:-1;
    			$data["msg"] = ($data["status"]==-1)?lang('presale_no_need_pay_deposit_tips'):"";
    		}
    		$returnUrl = addon_url("presale://users/molist",array("id"=>$porderId),true,true);
    		
    	}else{
    		$presale = $m->getPresalePay($porderId);
    		if($presale["endPayTime"]<date("Y-m-d H:i:s")){
    			$data["status"] = -1;
    			$data["msg"] = lang('presale_over_time_balance_tips');
    		}else{
	    		$orderAmount = $presale["surplusMoney"];
	    		if($presale["isPay"]==2){
	    			$data["status"] = -1;
	    			$data["msg"] = lang('presale_has_pay_balance_tips');
	    		}else{
	    			$data["status"] = $orderAmount>0?1:-1;
	    			$data["msg"] = ($data["status"]==-1)?lang('presale_not_need_pay_balance_tips'):"";
	    		}
	    		$returnUrl = addon_url("presale://users/mocheckPayStatus",array("id"=>$porderId),true,true);
    		}
    	}
    	
        if($data["status"]==1){
            $out_trade_no = WSTOrderNo();
            $body = ($payObj=="ding")?lang('presale_pay_deposit_title'):lang('presale_pay_balance_title');
            $subject = ($payObj=="ding")?lang('presale_deposit_money_title'):lang('presale_deposit_balance_title');
            $passback_params = $payObj."@".$userId."@".$porderId;
            $m = new PM();
            $payment = $m->getPayment("alipays");
            require Env::get('root_path') . 'extend/alipay/aop/AopClient.php' ;
            require Env::get('root_path') . 'extend/alipay/aop/request/AlipayTradeWapPayRequest.php' ;
            $aop = new \AopClient ();  
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';  
            $aop->appId = $payment["appId"];  
            $aop->rsaPrivateKey = $payment["rsaPrivateKey"]; 
            $aop->alipayrsaPublicKey = $payment["alipayrsaPublicKey"];
            $aop->apiVersion = '1.0';  
            $aop->signType = 'RSA2';  
            $aop->postCharset= "UTF-8";;  
            $aop->format='json';  
            $request = new \AlipayTradeWapPayRequest  ();  
            $request->setReturnUrl($returnUrl);  
            $request->setNotifyUrl(addon_url("presale://alipaysmo/aliNotify","",true,true));
            $passback_params = urlencode($passback_params);
            $bizcontent = "{\"body\":\"$body\","
                        . "\"subject\": \"$subject\","
                        . "\"out_trade_no\": \"$out_trade_no\","
                        . "\"timeout_express\": \"90m\","
                        . "\"total_amount\": \"$orderAmount\","
                        . "\"passback_params\": \"$passback_params\","
                        . "\"product_code\":\"QUICK_WAP_WAY\""
                        . "}";
            $request->setBizContent($bizcontent);
            //请求  
            $result = $aop->pageExecute ($request);
            echo $result;
        }else{
    		echo "<span style='font-size:40px;'>".$data["msg"]."</span>";
    		return;
    	}
    }
    
    /**
     * 验证签名
     */
    function aliCheck($params){
        require Env::get('root_path') . 'extend/alipay/aop/AopClient.php' ;
        $aop = new \AopClient;
        $m = new PM();
        $payment = $m->getPayment("alipays");
        $aop->alipayrsaPublicKey = $payment["alipayrsaPublicKey"];
        $flag = $aop->rsaCheckV1($params, NULL, "RSA2");
        return $flag;
    }

    /**
     * 服务器异步通知方法
     */
    function aliNotify() {
        if($this->aliCheck($_POST)){
            if ($_POST['trade_status'] == 'TRADE_SUCCESS' || $_POST['trade_status'] == 'TRADE_FINISHED'){
                $extras = explode("@",urldecode($_POST['passback_params']));
                $obj = array ();
                $obj["trade_no"] = $_POST['trade_no'];
                $obj["out_trade_no"] = $_POST["out_trade_no"];
                $obj["total_fee"] = $_POST['total_amount'];
                $obj["payObj"] = $extras[0];
                $obj["userId"] = (int)$extras[1];
                $obj["porderId"] = (int)$extras[2];
                $obj["payFrom"] = 'alipays';
                // 支付成功业务逻辑
                $m = new OM();
                $rs = $m->complatePay ( $obj );
                if($rs["status"]==1){
                    echo 'success';
                }else{
                    echo 'fail';
                }
            }
        }
    }

}
