<?php
namespace addons\auction\controller;
use think\addons\Controller;
use think\Loader;
use Env;
use addons\auction\model\Auctions as AM;
use wstmart\common\model\Payments as M;
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
		$m = new AM();
		$data = $m->getConf('Auction');
		$this->assign("seoAuctionKeywords",$data['seoAuctionKeywords']);
		$this->assign("seoAuctionDesc",$data['seoAuctionDesc']);
		
		header ("Content-type: text/html; charset=utf-8");
		require Env::get('root_path') . 'extend/ccgw/CcgwPayConf.php';
		require Env::get('root_path') . 'extend/ccgw/CcgwQrcodePay.php';
		$this->payConfig = array();
		$m = new M();
		$this->wxpay = $m->getPayment("ccgwpays");
		$this->payConfig['appKey'] = $this->wxpay['appKey'];
		$this->payConfig['secretKey'] = $this->wxpay['secretKey'];
		$this->payConfig['notifyurl'] = addon_url("auction://ccgwpaysmo/ccgwNotify","",true,true);
		$this->payConfig['curl_timeout'] = 30;
		$this->payConfig['returnurl'] = "";

		$this->payConfig['basecctype'] = WSTConf('CONF.ccType');
		
		new \CcgwPayConf($this->payConfig);
	}


	public function getBtc(){
		
		$am = new AM();
		$needPay = 0;
		$payObj = input("payObj/s");
		$auctionId = (int)input("auctionId");
		if($payObj=="bao"){
			$auction = $am->getUserAuction($auctionId);
			$needPay = $auction["cautionMoney"];
		}else{
			$auction = $am->getAuctionPay($auctionId);
			$needPay = $auction["payPrice"];
		}

		$qrcodePay = new \CcgwQrcodePay ();
		$needBtc = 0;
		$basecctype = $this->payConfig['basecctype'];
		$quotecctype = "hkd";
		$direction = "refer";
		
		$rs = $qrcodePay->getPricePair($basecctype,$quotecctype,$direction);

		if(isset($rs['success']) && $rs['success']==1){
			$exchangeRate = $rs['data'];
			$needBtc = number_format($needPay/$exchangeRate,10, '.', '');
			return json(WSTReturn("",1,$needBtc));
		}else{
			return json(WSTReturn(lang('auction_ccpay_request_fail')));
		}
	}


	public function getBtcAddress(){
		
		$qrcodePay = new \CcgwQrcodePay ();
		$cctype = $this->payConfig['basecctype'];
		$callbackurl = $this->payConfig['notifyurl'];
		$rs = $qrcodePay->getOneDeposit($cctype,$callbackurl);
		
		if(isset($rs['success']) && $rs['success']==1){
			$payObj = input("payObj/s");
			$auctionId = (int)input("auctionId");
			$needPay = 0;
			$am = new AM();
			if($payObj=="bao"){
				$auction = $am->getUserAuction($auctionId);
				$needPay = $auction["cautionMoney"];
			}else{
				$auction = $am->getAuctionPay($auctionId);
				$needPay = $auction["payPrice"];
			}

			$data = $rs['data'];
			$userId = (int)session('WST_USER.userId');
			$obj = array();
			$obj["userId"] = $userId;
			$obj["ccType"] = $data['cctype'];
			$obj["ccAddress"] = $data['ccaddress'];
			$obj["dataId"] = input('outTradeNo');
			$obj["payMoney"] = $needPay;
			$obj["pkey"] = $payObj.'@'.$userId.'@'.$auctionId;
			$m = new CA();
			$m->addAddress($obj);
			return json(WSTReturn("",1,$obj["ccAddress"]));
		}else{
			return json(WSTReturn(lang('auction_ccpay_request_fail')));
		}
	}
	
	/**
	 * 获取微信URL
	 */
	public function getCcgwPaysURL(){
		$am = new AM();
		$payObj = input("payObj/s");
		$pkey = "";
		$data = array();
		$auctionId = (int)input("auctionId");
		$userId = (int)session('WST_USER.userId');
		if($payObj=="bao"){
			$auction = $am->getUserAuction($auctionId);
			$needPay = $auction["cautionMoney"];
			if($auction["userId"]>0){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$data["status"] = $needPay>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_bond'):"";
			}
		}else{
			$auction = $am->getAuctionPay($auctionId);
			$needPay = $auction["payPrice"];
			if($auction["isPay"]==1){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_order');
			}else{
				$data["status"] = $needPay>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_order'):"";
			}
		}
		return $data;
	}
	
	public function toPay() {
		$payObj = input("payObj/s");
    	$am = new AM();
    	$obj = array();
    	$data = array();
    	$needPay = 0;
    	$auctionId = (int)input("auctionId");
    	$userId = (int)session('WST_USER.userId');
    	$return_url = "";
    	
    	if($payObj=="bao"){//充值
    		$return_url = addon_url("auction://goods/modetail",array("id"=>$auctionId),true,true);
    		$auction = $am->getUserAuction($auctionId);
    		$needPay = $auction["cautionMoney"];
    		if($auction["userId"]>0){
    			header("Location:".$return_url);
				exit();
    		}else{
    			$data["status"] = $needPay>0?1:-1;
    			$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_bond'):"";
    		}
    		if($needPay==0){
				header("Location:".$return_url);
				exit();
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
	    			$data["msg"] = lang('auction_have_paid_order');
	    		}else{
	    			$data["status"] = $needPay>0?1:-1;
	    			$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_order'):"";
	    		}
	    		$return_url = addon_url("auction://users/mocheckPayStatus",array("id"=>$auctionId),true,true);
	    		if($needPay==0){
					header("Location:".$return_url);
					exit();
				}
    		}
    	}
		if($needPay>0){
			$code_url = "";
			$this->assign ( 'isCCgwPay', 1 );
			$out_trade_no = WSTOrderNo();
			$this->assign ( 'out_trade_no', $out_trade_no );
			$this->assign ( 'code_url', $code_url );
			$this->assign ( 'returnUrl', $return_url );
			$this->assign ( 'needPay', $needPay );
			$this->assign ( 'auctionId', $auctionId );
			$this->assign ( 'payObj', $payObj );
		}else{
			$flag = false;
		}
		
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
		file_put_contents("runtime/ccgwpay_auction_log.txt",date('Y-m-d H:i:s')."----ttbb----".json_encode($bkData)."----\n", FILE_APPEND);
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
								$out_trade_no = $dataId;
								$obj = array();
								$obj["trade_no"] = $trade_no;
								$obj["out_trade_no"] = $dataId;
								$obj["total_fee"] = $payMoney;
								
								$obj["payObj"] = $pkeys[0];
								$obj["userId"] = (int)$pkeys[1];
								$obj["auctionId"] = (int)$pkeys[2];
								$obj["payFrom"] = $payFrom;
								// 支付成功业务逻辑
								$m = new AM();
								$rs = $m->complateCautionMoney ( $obj );

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
									$lm['remark'] = lang('auction_ccpay_recharge',[$needPay]);
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
