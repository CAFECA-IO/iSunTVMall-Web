<?php
namespace addons\auction\model;
use think\Loader;
use think\Db;
use Env;
use wstmart\common\model\Payments as M;
use addons\auction\model\Auctions as AM;
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
 * 阿里支付控制器
 */
class Alipays extends Base{

	/**
	 * 退款
	 */
	public function auctionRefund($amoney){

        $request_no = $amoney['id'].$amoney['userId'];
        $backMoney = $amoney['cautionMoney'];
        $tradeNo = $amoney['tradeNo'];
        $refund_reason = lang('auction_refund');
        
        require Env::get('root_path') . 'extend/alipay/aop/AopClient.php';
	   	require Env::get('root_path') . 'extend/alipay/aop/request/AlipayTradeRefundRequest.php';
        $m = new M();
	   	$payment = $m->getPayment("alipays");
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $payment["appId"];
        $aop->rsaPrivateKey = $payment["rsaPrivateKey"];
        $aop->alipayrsaPublicKey=$payment["alipayrsaPublicKey"];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayTradeRefundRequest ();

        $request->setBizContent("{" .
            "\"trade_no\":\"$tradeNo\"," .
            "\"refund_amount\":\"$backMoney\"," .
            "\"refund_reason\":\"$refund_reason\"," .
            "\"out_request_no\":\"$request_no\"" .
        "  }");

        $result = $aop->execute ( $request); 
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode) && $resultCode == 10000){
        	if($result->$responseNode->fund_change=="Y"){
        		$obj = array();
		        $obj['refundTradeNo'] = $request_no;//退款单号
		        $obj['id'] = $amoney['id'];
                $m = new AM();
		        $rs = $m->complateAuctionRefund($obj);
		        if($rs['status']==1){
		        	return WSTReturn(lang('auction_refund_success'),1); 
		        }else{
		        	return WSTReturn(lang('auction_refund_fail'),1);
		        }
        	}
        } else {
        	$msg = $result->$responseNode->sub_msg;
            return WSTReturn($msg,-1); 
        }
    }

}
