<?php
namespace wstmart\supplier\controller;
use think\Loader;
use Env;
use wstmart\common\model\Payments as M;
use wstmart\common\model\Orders as OM;
use wstmart\common\model\LogMoneys as LM;
use wstmart\common\model\Suppliers as SM;
use wstmart\common\model\ChargeItems as CM;
/**
 * ============================================================================
 * WSTMart多用户商城
 * 版权所有 2016-2066 广州商淘信息科技有限公司，并保留所有权利。
 * 官网地址:http://www.wstmart.net
 * 交流社区:http://bbs.shangtaosoft.com
 * 联系QQ:153289970
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！未经本公司授权您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 阿里支付控制器
 */
class Alipays extends Base{

    /**
     * 生成支付代码
     */
    function getAlipaysUrl(){
        $payObj = input("payObj/s");
        
        $obj = array();
        $data = array('status'=>-1);
        $orderAmount = 0;
        $out_trade_no = "";
        $passback_params = "";
        $subject = "";
        $body = "";
        $m = new M();
        $payment = $m->getPayment("alipays");
        require Env::get('root_path') . 'extend/alipay/aop/AopClient.php' ;
        require Env::get('root_path') . 'extend/alipay/aop/request/AlipayTradePagePayRequest.php' ;
        $m = new OM();
        $returnUrl = url("supplier/alipays/payorders","",true,true);
        if($payObj=="recharge") {//充值
            $itmeId = (int)input("itmeId/d");
            $orderAmount = 0;
            if ($itmeId > 0) {
                $cm = new CM();
                $item = $cm->getItemMoney($itmeId);
                $orderAmount = isSet($item["chargeMoney"]) ? $item["chargeMoney"] : 0;
            } else {
                $orderAmount = (int)input("needPay/d");
            }

            $supplierId = (int)session('WST_SUPPLIER.supplierId');
            $targetType = 3;

            $targetId = $supplierId;
            $returnUrl = url("supplier/alipays/suppliermoneys", "", true, true);

            $data["status"] = $orderAmount > 0 ? 1 : -1;
            $out_trade_no = WSTOrderNo();
            $passback_params = $payObj . "@" . $targetId . "@" . $targetType . "@" . $itmeId;
            $subject = lang("wallet_recharge1", [$orderAmount]);
            $body = lang("wallet_recharge");
        }elseif($payObj=="enter"){ // 缴纳年费
            $orderAmount = (int)input("needPay");
            $targetType = 3;
            $targetId = (int)session('WST_SUPPLIER.supplierId');
            $returnUrl = url("supplier/alipays/suppliermoneys","",true,true);
            $data["status"] = $orderAmount>0?1:-1;
            $out_trade_no = WSTOrderNo();
            $passback_params = $payObj."@".$targetId."@".$targetType;
            $subject = lang("s_pays_annual_fee", [$orderAmount]);
            $body = lang("s_pays_annual_fee");
        }

        if($data["status"]==1){

            $aop = new \AopClient ();
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = $payment["appId"];
            $aop->rsaPrivateKey = $payment["rsaPrivateKey"];
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset= "UTF-8";;
            $aop->format='json';
            $request = new \AlipayTradePagePayRequest ();
            $request->setReturnUrl($returnUrl);
            $request->setNotifyUrl(url("supplier/alipays/aliNotify","",true,true));
            $passback_params = urlencode($passback_params);
            $bizcontent = "{\"body\":\"$body\","
                        . "\"subject\": \"$subject\","
                        . "\"out_trade_no\": \"$out_trade_no\","
                        . "\"total_amount\": \"$orderAmount\","
                        . "\"passback_params\": \"$passback_params\","
                        . "\"product_code\":\"FAST_INSTANT_TRADE_PAY\""
                        . "}";
            $request->setBizContent($bizcontent);

            //请求
            $result = $aop->pageExecute ($request);
            $data["result"]= $result;
            return $data;
        }else{
            return $data;
        }
    }

    /**
     * 验证签名
     */
    function aliCheck($params){
        require Env::get('root_path') . 'extend/alipay/aop/AopClient.php' ;
        $aop = new \AopClient;
        $m = new M();
        $payment = $m->getPayment("alipays");
        $aop->alipayrsaPublicKey = $payment["alipayrsaPublicKey"];
        $flag = $aop->rsaCheckV1($params, NULL, "RSA2");
        return $flag;
    }
    /**
     * 支付结果同步回调
     */
    function suppliermoneys(){
        if($this->aliCheck($_GET)){
            $this->redirect(url("supplier/logmoneys/suppliermoneys"));
        }else{
            $this->error(lang("payment_failed"));
        }
    }
   
    function payorders(){
        if($this->aliCheck($_GET)){
            $this->redirect(url("supplier/alipays/paysuccess"));
        }else{
            $this->error(lang("payment_failed"));
        }
    }
    
    /**
     * 服务器异步通知方法
     */
    function aliNotify() {
        if($this->aliCheck($_POST)){
            if ($_POST['trade_status'] == 'TRADE_SUCCESS' || $_POST['trade_status'] == 'TRADE_FINISHED'){
                $extras = explode("@",urldecode($_POST['passback_params']));
                $rs = array();
                if($extras[0]=="recharge"){//充值
                    $targetId = (int)$extras [1];
                    $targetType = (int)$extras [2];
                    $itemId = (int)$extras [3];
                    $obj = array ();
                    $obj["trade_no"] = $_POST['trade_no'];
                    $obj["out_trade_no"] = $_POST["out_trade_no"];
                    $obj["targetId"] = $targetId;
                    $obj["targetType"] = $targetType;
                    $obj["itemId"] = $itemId;
                    $obj["total_fee"] = $_POST['total_amount'];
                    $obj["payFrom"] = 'alipays';
                    // 支付成功业务逻辑
                    $m = new LM();
                    $rs = $m->complateRecharge ( $obj );
                }elseif($extras[0]=="enter") { // 缴纳年费
                    $targetId = (int)$extras [1];
                    $targetType = (int)$extras [2];
                    $obj = array();
                    $obj["trade_no"] = $_POST['trade_no'];
                    $obj["out_trade_no"] = $_POST["out_trade_no"];
                    $obj["targetId"] = $targetId;
                    $obj["targetType"] = $targetType;
                    $obj["total_fee"] = $_POST['total_amount'];
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
        } else {
            echo "fail";
        }
    }
    
    /**
     * 检查支付结果
     */
    public function paySuccess() {
        return $this->fetch('order_pay_step3');
    }

}
