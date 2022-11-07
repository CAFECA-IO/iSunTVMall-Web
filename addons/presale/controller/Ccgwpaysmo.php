<?php
namespace addons\presale\controller;
use think\addons\Controller;
use think\Loader;
use Env;
use addons\presale\model\Presales as M;
use wstmart\common\model\Payments as Payments;
use addons\presale\model\Orders as OM;
use wstmart\common\model\LogPays as PM;
use wstmart\common\model\CcgwAddress as CA;

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
class Ccgwpaysmo extends Controller{
	
	/**
	 * 初始化
	 */
	private $payConfig;
	private $wxpay;
	public function initialize() {
	
		
		header ("Content-type: text/html; charset=utf-8");
		require Env::get('root_path') . 'extend/ccgw/CcgwPayConf.php';
		require Env::get('root_path') . 'extend/ccgw/CcgwQrcodePay.php';
		$this->payConfig = array();
		$m = new Payments();
		$this->wxpay = $m->getPayment("ccgwpays");
		$this->payConfig['appKey'] = $this->wxpay['appKey'];
		$this->payConfig['secretKey'] = $this->wxpay['secretKey'];
		$this->payConfig['notifyurl'] = addon_url("presale://ccgwpaysmo/ccgwNotify","",true,true);
		$this->payConfig['curl_timeout'] = 30;
		$this->payConfig['returnurl'] = "";

		$this->payConfig['basecctype'] = WSTConf('CONF.ccType');
		
		new \CcgwPayConf($this->payConfig);
	}


	public function getBtc(){
		$pkey = WSTBase64urlDecode(input("pkey"));
		$pkeys = explode("@", $pkey );
		$flag = true;
		$m = new M();
		$needPay = 0;
		$out_trade_no = 0;
		$trade_no = 0;
		$porderId = (int)$pkeys[2];
		if($pkeys[0]=="ding"){
			$presale = $m->getPresalePay($porderId);
    		$needPay = $presale["depositMoney"];
		}else{
			$presale = $m->getPresalePay($porderId);
			$needPay = $presale["surplusMoney"];
		}
		
		$qrcodePay = new \CcgwQrcodePay ();
		$needBtc = 0;
		$basecctype = $this->payConfig['basecctype'];
		$quotecctype = "hkd";
		$direction = "refer";
		//$url = 'https://ccgw.isuntv.com/api/cc/PricePair?quotecctype='.$quotecctype.'&basecctype='.$basecctype.'&direction='.$direction;
		$rs = $qrcodePay->getPricePair($basecctype,$quotecctype,$direction);

		if(isset($rs['success']) && $rs['success']==1){
			$exchangeRate = $rs['data'];
			$needBtc = number_format($needPay/$exchangeRate,10, '.', '');
			return json(WSTReturn("",1,$needBtc));
		}else{
			return json(WSTReturn(lang('presale_ccpay_request_fail')));
		}
	}


	public function getBtcAddress(){
		$pkey = WSTBase64urlDecode(input("pkey"));
		$pkeys = explode("@", $pkey );

		$qrcodePay = new \CcgwQrcodePay ();
		$cctype = $this->payConfig['basecctype'];
		$callbackurl = $this->payConfig['notifyurl'];
		$rs = $qrcodePay->getOneDeposit($cctype,$callbackurl);
		
		if(isset($rs['success']) && $rs['success']==1){
			$m = new M();
			$porderId = (int)$pkeys[2];
			if($pkeys[0]=="ding"){
				$presale = $m->getPresalePay($porderId);
	    		$needPay = $presale["depositMoney"];
			}else{
				$presale = $m->getPresalePay($porderId);
				$needPay = $presale["surplusMoney"];
			}

			$data = $rs['data'];
			$userId = (int)session('WST_USER.userId');
			$obj = array();
			$obj["userId"] = $userId;
			$obj["ccType"] = $data['cctype'];
			$obj["ccAddress"] = $data['ccaddress'];
			$obj["dataId"] = input('outTradeNo');
			$obj["payMoney"] = $needPay;
			$obj["pkey"] = $pkey;
			$m = new CA();
			$m->addAddress($obj);
			return json(WSTReturn("",1,$obj["ccAddress"]));
		}else{
			return json(WSTReturn(lang('presale_ccpay_request_fail')));
		}
	}
	
	/**
	 * 获取微信URL
	 */
	public function getCcgwPaysURL(){
		$m = new M();
		$payFrom = (int)input("payFrom");//0:PC 1:微信
		$pkey = "";
		$data = array();
		$pkey = input("pkey");

        $pkey = WSTBase64urlDecode($pkey);
        $pkeys = explode('@',$pkey);
		$payObj = $pkeys[0];
		$porderId = (int)$pkeys[1];
		if($payObj=="ding"){//定金
			$presale = $m->getPresalePay($porderId);
			$needPay = $presale["depositMoney"];
			if($presale["isCanPay"]==0){
				$data["status"] = -1;
				$data["msg"] = lang("presale_have_pay_deposit_title");
			}else{
				$data["status"] = $needPay>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang("presale_no_need_pay_deposit_tips"):"";
			}
		}else{//尾款
			$presale = $m->getPresalePay($porderId);
			$needPay = $presale["surplusMoney"];
			if($presale["startPayTime"]>date("Y-m-d H:i:s")){
				$data["status"] = -1;
				$data["msg"] = lang("presale_cannt_pay_balance_tips");
			}else if($presale["endPayTime"]<date("Y-m-d H:i:s")){
				$data["status"] = -1;
				$data["msg"] = lang("presale_over_time_balance_tips");
			}else{
				if($presale["presaleStatus"]==2){
					$data["status"] = -1;
					$data["msg"] = lang("presale_has_pay_balance_tips");
				}else{
					$data["status"] = $needPay>0?1:-1;
					$data["msg"] = ($data["status"]==-1)?lang("presale_not_need_pay_balance_tips"):"";
				}
			}
		}
		return $data;
	}
	
	public function toPay() {
		$pkey = WSTBase64urlDecode(input('pkey'));
        $pkeys = explode('@',$pkey);
        $payObj = $pkeys[0];
        $porderId = (int)$pkeys[1];
    	$m = new M();
    	$obj = array();
    	$data = array();
    	$needPay = 0;
    	$userId = (int)session('WST_USER.userId');
    	$return_url = "";
    	if($payObj=="ding"){//定金
    		$return_url = addon_url("presale://users/molist",array("id"=>$porderId),true,true);
    		$presale = $m->getPresalePay($porderId);
    		$needPay = $presale["depositMoney"];

    		if($presale["isCanPay"]==0){
    			header("Location:".$return_url);
				exit();
    		}else{
    			$data["status"] = $needPay>0?1:-1;
    			$data["msg"] = ($data["status"]==-1)?lang("presale_no_need_pay_deposit_tips"):"";
    		}
    	}else{//尾款
    		$presale = $m->getPresalePay($porderId);
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

	    		$return_url = addon_url("presale://users/mocheckPayStatus",array("id"=>$porderId),true,true);
    		}
    	}
    	
    	if($needPay>0){
			$code_url = "";
			$out_trade_no = WSTOrderNo();
			$this->assign ( 'isCCgwPay', 1 );
			$this->assign ( 'returnUrl', $return_url );
			$this->assign ( 'out_trade_no', $out_trade_no );
			$this->assign ( 'code_url', $code_url );
			$this->assign ( 'needPay', $needPay );
		}else{
			$flag = false;
		}
		$pkey = $payObj.'@'.$userId.'@'.$porderId;
		$this->assign('pkey',WSTBase64urlEncode($pkey));
		return $this->fetch('mobile/index/pay_ccgw');
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
	public function ccgwNotify() {
		set_time_limit (300);
		// 使用通用通知接口
		$qrcodePay = new \CcgwQrcodePay ();
		$bkData = input();
		file_put_contents("runtime/ccgwpay_presale_log.txt",date('Y-m-d H:i:s')."----ttbb----".json_encode($bkData)."----\n", FILE_APPEND);
		$ccTxid = $bkData['txid'];
		$ccAddress = $bkData['address'];
		$ccAmount = $bkData['amount'];
		$ccType = $bkData['type'];

		$m = new CA();
		$obj = [];
		$obj['ccAddress'] = $ccAddress;
		$rs = $m->getAddress($obj);
		if(!empty($rs)){

			$addressId = $rs['id'];
			$pkey = $rs['pkey'] ;
			$dataId = $rs['dataId'] ;
			$payMoney = (float)$rs['payMoney'];

			$payDetail = $qrcodePay->getCCPaymentDetail($ccTxid);
			
			//完成交易
			if(isSet($payDetail['data']['aasm_state']) && $payDetail['data']['aasm_state']=="confirmed"){

				$payData = $payDetail['data'];
				
				if($payData['address']==$ccAddress){
					
					$obj = [];
					$ccAmount = $payData['amount'];
					$obj['addressId'] = $addressId;
					$obj['ccAddress'] = $ccAddress;
					$obj['ccTxid'] = $payData['txid'];
					$obj['ccType'] = $payData['type'];
					$obj['ccAmount'] = $ccAmount;
					$flag = $m->checkCcgwTrans($obj);
					if($flag){
						
						$payFrom = 'ccgwpays';
						$basecctype = $payData['type'];
						$quotecctype = "hkd";
						$direction = "refer";
						$pricePair = $qrcodePay->getPricePair($basecctype,$quotecctype,$direction);
						if(isset($pricePair['success']) && $pricePair['success']==1){
							
							$exchangeRate = $pricePair['data'];
							$tranMoney = number_format($ccAmount * $exchangeRate,6, '.', '');
							$pkeys = explode ( "@", $pkey );
							$trade_no = $payData['txid'];
							$userId = (int)$pkeys [1];
							
							if($tranMoney>=$payMoney*0.985){//支付金额转换波动小于1.5%后应付金额，正常执行，否则不能执行相关操作
								// 此处应该更新一下订单状态，商户自行增删操作
								
								
								$total_fee = $payMoney;

								$out_trade_no = $dataId;
								$userId = (int)$pkeys [1];
								$porderId = (int)$pkeys [2];
								$obj = array ();
								$obj["trade_no"] = $trade_no;
								$obj["out_trade_no"] = $out_trade_no;
								$obj["userId"] = $userId;
								$obj["porderId"] = $porderId;
								$obj["total_fee"] = (float)$total_fee;
								$obj["payFrom"] = $payFrom;
								$obj["payObj"] = $pkeys[0];

								// 支付成功业务逻辑
								$m = new OM();
								$rs = $m->complatePay ( $obj );

								if($rs["status"]==1){
									$data = array();
									$data["userId"] = $userId;
									$data["transId"] = $out_trade_no;
									$m = new PM();
									$m->addPayLog($data);
									echo "SUCCESS";
								}else{
									echo "FAIL";
								}
							}else{
								$needPay = number_format($tranMoney,2, '.', '');
								
								$lmrs = model('common/LogMoneys')->getLogMoney(['tradeNo'=>$trade_no]);
								if(empty($lmrs)){
									$lm = [];
									$lm['targetType'] = 0;
									$lm['targetId'] = $userId;
									$lm['dataId'] = $dataId;
									$lm['dataSrc'] = 1;
									$lm['remark'] = lang('presale_ccpay_recharge',[$needPay]);
									$lm['moneyType'] = 1;
									$lm['money'] = $needPay;
									$lm['payType'] = $payFrom;
									$lm['tradeNo'] = $trade_no;
									$lm['createTime'] = date('Y-m-d H:i:s');
									model('common/LogMoneys')->add($lm);
								}
								echo "FAIL2";
							}
						}
					}
				}
			}
		}
			
			
	}

	/**
	 * 检查支付结果
	 */
	public function paySuccess() {

		return $this->fetch('/home/index/pay_success');

	}

}
