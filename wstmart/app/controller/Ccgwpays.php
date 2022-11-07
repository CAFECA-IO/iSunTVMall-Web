<?php
namespace wstmart\app\controller;
use think\Loader;
use Env;
use wstmart\common\model\Payments as M;
use wstmart\common\model\Orders as OM;
use wstmart\common\model\LogMoneys as LM;
use wstmart\common\model\LogPays as PM;
use wstmart\common\model\CcgwAddress as CA;
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
 * ccgw支付控制器
 */
class Ccgwpays extends Base{
	
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
		$m = new M();
		$this->wxpay = $m->getPayment("ccgwpays");
		$this->payConfig['appKey'] = $this->wxpay['appKey'];
		$this->payConfig['secretKey'] = $this->wxpay['secretKey'];
		$this->payConfig['notifyurl'] = url("app/ccgwpays/ccgwNotify","",true,true);
		$this->payConfig['curl_timeout'] = 30;
		$this->payConfig['returnurl'] = "";

		$this->payConfig['basecctype'] = WSTConf('CONF.ccType');
		
		new \CcgwPayConf($this->payConfig);
	}



	public function getBtc(){
		$userId = model("app/users")->getUserId();
		$pkey = WSTBase64urlDecode(input("pkey"));
		$pkeys = explode("@", $pkey );
		$needPay = 0;
		if($pkeys[0]=="recharge"){
			$needPay = (int)$pkeys[3];
		}else{
			if(count($pkeys)!= 4){
				return json(lang('ccpay_request_fail'));
			}else{
				$obj = array();
				$obj["userId"] = $userId;
				$obj["orderNo"] = $pkeys[2];
				$obj["isBatch"] = $pkeys[3];
				$m = new OM();
				$order = $m->getPayOrders($obj);
				$needPay = $order["needPay"];
			}
		}

		$qrcodePay = new \CcgwQrcodePay();
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
			return json(WSTReturn(lang('ccpay_request_fail')));
		}
	}


	public function getBtcAddress(){
		$userId = model("app/users")->getUserId();
		$pkey = WSTBase64urlDecode(input("pkey"));
		$pkeys = explode("@", $pkey );
		$qrcodePay = new \CcgwQrcodePay();
		$cctype = $this->payConfig['basecctype'];
		$callbackurl = $this->payConfig['notifyurl'];
		$rs = $qrcodePay->getOneDeposit($cctype,$callbackurl);
		$payObj = input("payObj");
		if(isset($rs['success']) && $rs['success']==1){
			if($payObj=="recharge"){
		    	$itemId = (int)$pkeys[4];
		    	$needPay = 0;
		    	if($itemId>0){
		    		$cm = new CM();
		    		$item = $cm->getItemMoney($itemId);
		    		$needPay = isSet($item["chargeMoney"])?$item["chargeMoney"]:0;
		    	}else{
		    		$needPay = (int)input("needPay/d");
		    	}
		    	
		    }else{
				$orderNo = $pkeys[2];
				$isBatch = $pkeys[3];
				$m = new OM();
				$obj["userId"] = $userId;
				$obj["orderNo"] = $orderNo;
				$obj["isBatch"] = $isBatch;
				$order = $m->getPayOrders($obj);
				$needPay = $order["needPay"];
		    }

			$data = $rs['data'];
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
			return json(WSTReturn(lang('ccpay_request_fail')));
		}
	}
	
	
	
	public function toCcgwPay() {
		$data = [];
	    $payObj = input("payObj/s");
	    $pkey = "";
		$userId = model("app/users")->getUserId();
		$trade_no = "";
		$fail = "<script>window.ReactNativeWebView.postMessage(JSON.stringify({case:'payFail'}));</script>";
	    if($payObj=="recharge"){
	    	$cm = new CM();
	    	$itemId = (int)input("itemId/d");
	    	$targetType = 0;
	    	$targetId = $userId;
	    	$out_trade_no = WSTOrderNo();
			$trade_no = $out_trade_no;
	    	if($targetType==1){//商家
	    		$targetId = (int)session('WST_USER.shopId');
	    	}
	    	$lm = new LM();
	    	$log = $lm->getLogMoney(['targetType'=>$targetType,'targetId'=>$targetId,'dataId'=>$out_trade_no,'dataSrc'=>4]);
	    	if(!empty($log)){
	    		return $fail;
	    	}
	    	$needPay = 0;
	    	if($itemId>0){
	    		$item = $cm->getItemMoney($itemId);
	    		$needPay = isSet($item["chargeMoney"])?$item["chargeMoney"]:0;
	    	}else{
	    		$needPay = (int)input("needPay/d");
	    	}
	    	
	    	$body = lang('recharge_wallet');
	    	$data["status"] = $needPay>0?1:-1;
	    	$pkey = $payObj."@".$targetId."@".$targetType."@".$needPay."@".$itemId;
	    	
	    	if($needPay==0){
				return $fail;
			}
	    }else{
	    	$pkey = WSTBase64urlDecode(input("pkey"));
	    	
            $pkey = explode('@',$pkey);
            $orderNo = $pkey[0];
            $isBatch = (int)$pkey[1];

	        $data['orderNo'] = $orderNo;
	        $data['isBatch'] = $isBatch;
	        $data['userId'] = $userId;
			$m = new OM();
			$rs = $m->getOrderPayInfo($data);
			if(empty($rs)){
				return $fail;
				exit();
			}else{
				$m = new OM();
				$userId = $userId;
				$obj["userId"] = $userId;
				$obj["orderNo"] = $orderNo;
				$obj["isBatch"] = $isBatch;
		
				$rs = $m->getByUnique($userId,$orderNo,$isBatch);
				$this->assign('rs',$rs);
				$body = lang('order_pay_title');
				$order = $m->getPayOrders($obj);
				$needPay = $order["needPay"];
				$payRand = $order["payRand"];
				$out_trade_no = $obj["orderNo"]."a".$payRand;
				$trade_no = $obj["orderNo"];
				$pkey = "order@".$userId."@".$obj["orderNo"]."@".$obj["isBatch"];
				if($needPay==0){
					return $fail;
					exit();
				}
			}
	    }
		
		$this->assign ( 'isCCgwPay', 1 );
		$this->assign ( 'out_trade_no', $trade_no );
		$this->assign ( 'code_url', '' );
		$this->assign ( 'needPay', $needPay );
		$this->assign ( 'payObj', $payObj );
	    $this->assign ( 'pkey', WSTBase64urlEncode($pkey) );
	    $this->assign ( 'tokenId', input("tokenId") );

		$this->assign([
			"getBtcUrl"=>url("app/ccgwpays/getBtc"),
			"getBtcAddress"=>url("app/ccgwpays/getBtcAddress")
		]);

		$appPath = $this->request->root().'/wstmart/app/view/ccgwpays';
		return $this->fetch('ccgwpay', ["v"=>WSTConf("wstVersion"), "APP_PATH"=>$appPath]);

	}
	
	
	/**
	 * 检查支付结果
	 */
	public function getPayStatus() {
		$userId = model("app/users")->getUserId();
		$trade_no = input('trade_no');
		$obj = array();
		$obj["userId"] = $userId;
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
	 * 异步通知
	 */
	public function ccgwNotify() {
		set_time_limit (300);
		// 使用通用通知接口
		$qrcodePay = new \CcgwQrcodePay();
		$bkData = input();
		file_put_contents("runtime/ccgwpay_app_log.txt",date('Y-m-d H:i:s')."----ttbb----".json_encode($bkData)."----\n", FILE_APPEND);
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
								$total_fee = $payMoney;
								$out_trade_no = 0;
								if($pkeys[0]=="recharge"){//充值
									$out_trade_no = $dataId;
									$targetId = (int)$pkeys [1];
									$targetType = (int)$pkeys [2];
									$itemId = (int)$pkeys [4];

									$obj["trade_no"] = $trade_no;
									$obj["total_fee"] = (float)$total_fee;
									$obj["out_trade_no"] = $dataId;
									$obj["targetId"] = $targetId;
									$obj["targetType"] = $targetType;
									$obj["itemId"] = $itemId;
									$obj["payFrom"] = $payFrom;
									// 支付成功业务逻辑
									$m = new LM();
									$rs = $m->complateRecharge ( $obj );
								}else{
									$out_trade_no = $pkeys[2];
									$obj["trade_no"] = $trade_no;
									$obj["total_fee"] = (float)$total_fee;
									$obj["userId"] = $pkeys[1];
									$obj["out_trade_no"] = $pkeys[2];
									$obj["isBatch"] = $pkeys[3];
									$obj["payFrom"] = $payFrom;
									// 支付成功业务逻辑
									$m = new OM();
									$rs = $m->complatePay ( $obj );
								}
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
									$lm['remark'] = "充值HK$".$needPay;
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


}
