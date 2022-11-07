<?php
namespace addons\auction\controller;
use think\addons\Controller;
use think\Loader;
use Env;
use wstmart\common\model\Payments as PM;
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
class Weixinpays extends Controller{
	public function __construct(){
		parent::__construct();
		$m = new AM();
		$data = $m->getConf('Auction');
		$this->assign("seoAuctionKeywords",$data['seoAuctionKeywords']);
		$this->assign("seoAuctionDesc",$data['seoAuctionDesc']);
	}
	
	/**
	 * 初始化
	 */
	private $wxpay;
	public function initialize() {
		header ("Content-type: text/html; charset=utf-8");
		require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Builder.php';
		require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Util/PemUtil.php';
		$m = new PM();
		$this->wxpay = $m->getPayment("weixinpays");
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
			$orderAmount = $auction["cautionMoney"];
			
			if($auction["userId"]>0){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$data["status"] = $orderAmount>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_bond'):"";
				$pkey = $payObj."@".$userId."@".$auctionId;
			}
		}else{
			$auction = $am->getAuctionPay($auctionId);
			if($auction["endPayTime"]<date("Y-m-d H:i:s")){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$orderAmount = $auction["payPrice"];
				if($auction["isPay"]==1){
					$data["status"] = -1;
					$data["msg"] = lang('auction_have_paid_order');
				}else{
					$data["status"] = $orderAmount>0?1:-1;
					$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_order'):"";
					$pkey = $payObj."@".$userId."@".$auctionId;
				}
			}
		}
		if($payFrom==1){//微信
			$data["url"] = addon_url('auction://weixinpays/createQrcode',array("pkey"=>WSTBase64urlEncode($pkey)));
		}else{
			$data["url"] = addon_url('auction://weixinpays/createQrcode',array("pkey"=>WSTBase64urlEncode($pkey)));
		}
		return $data;
	}
	
	public function createQrcode() {
		$pkey = WSTBase64urlDecode(input("pkey"));
		$pkeys = explode("@", $pkey );
		$flag = true;
		$am = new AM();
		$needPay = 0;
		$trade_no = 0;
		$auctionId = (int)$pkeys[2];
		if($pkeys[0]=="bao"){
			$auction = $am->getUserAuction($auctionId);
			$needPay = $auction["cautionMoney"];
			$body = lang('auction_pay_bond');
		}else{
			$auction = $am->getAuctionPay($auctionId);
			$needPay = $auction["payPrice"];
			$body = lang('auction_pay_order');
		}
		$out_trade_no = WSTOrderNo();
		$trade_no = $out_trade_no;
		if($needPay>0){
			// 商户号，假定为`1000100`
			$merchantId = $this->wxpay['mchId'];
			// 商户私钥，文件路径假定为 `/path/to/merchant/apiclient_key.pem`
			$merchantPrivateKeyFilePath = WSTRootPath().'/vendor/wechatpay/wechatpay/cert/apiclient_key.pem';
			// 加载商户私钥
			$merchantPrivateKeyInstance = \WeChatPay\Util\PemUtil::loadPrivateKey($merchantPrivateKeyFilePath);
			$merchantCertificateSerial = $this->wxpay['certificateSerial'];// API证书不重置，商户证书序列号就是个常量

			// 平台证书，可由下载器 `./bin/CertificateDownloader.php` 生成并假定保存为 `/path/to/wechatpay/cert.pem`
			//$platformCertificateFilePath = '/path/to/wechatpay/cert.pem';
			$platformCertificateFilePath = WSTRootPath().'/vendor/wechatpay/wechatpay/cert/wechatpay_cert.pem';
			// 加载平台证书
			$platformCertificateInstance = \WeChatPay\Util\PemUtil::loadCertificate($platformCertificateFilePath);
			// 解析平台证书序列号
			$platformCertificateSerial = \WeChatPay\Util\PemUtil::parseCertificateSerialNo($platformCertificateInstance);

			// 工厂方法构造一个实例
			$instance = \WeChatPay\Builder::factory([
			    'mchid'      => $merchantId,
			    'serial'     => $merchantCertificateSerial,
			    'privateKey' => $merchantPrivateKeyInstance,
			    'certs'      => [
			        $platformCertificateSerial => $platformCertificateInstance,
			    ],
			]);

			try {
			    

			    $resp = $instance->hk->v3->transactions->native->post(['json' => [
			        'mchid' => $this->wxpay['mchId'],
			        'out_trade_no' => $out_trade_no,
			        'appid' => $this->wxpay['appId'],
			        'description' => $body,
			        'attach' => "$pkey",
			        'trade_type'=>'NATIVE',
			        'notify_url' => addon_url("auction://weixinpays/wxNotify","",true,true),
			        'amount' => [
			            'total' => $needPay * 100,
			            'currency' => 'HKD'
			        ],
			        'merchant_category_code'=>$this->wxpay['mccCode']
			    ]]);


			    $wxRsStr = $resp->getBody();
			    $wxQrcodePayResult = json_decode($wxRsStr,true);
			    $code_url = '';
				// 商户根据实际情况设置相应的处理流程
				if ($resp->getStatusCode() != 200) {
					// 商户自行增加处理流程
					echo lang('communication_error')."：" . $resp->getStatusCode(). "<br>";
				} else {
					if ($wxQrcodePayResult ["code_url"] != NULL) {
						// 从统一支付接口获取到code_url
						$code_url = $wxQrcodePayResult ["code_url"];
					}
				}
			} catch (Exception $e) {
			    // 进行错误处理
			    echo $e->getMessage(), PHP_EOL;
			    if ($e instanceof \Psr\Http\Message\ResponseInterface && $e->hasResponse()) {
			        echo $e->getResponse()->getStatusCode() . ' ' . $e->getResponse()->getReasonPhrase(), PHP_EOL;
			        echo $e->getResponse()->getBody();
			    }
			}

			$this->assign('pkey',input("pkey"));
			$this->assign ( 'out_trade_no', $trade_no );
			$this->assign ( 'code_url', $code_url );
			$this->assign ( 'needPay', $needPay );
		}else{
			$flag = false;
		}
	
		if($flag){
			return $this->fetch('/home/index/pay_step2');
		}else{
			return $this->fetch('/home/index/pay_success');
		}

	}
	
	
	/**
	 * 检查支付结果
	 */
	public function getPayStatus() {
		$trade_no = input('trade_no');
		$total_fee = cache( $trade_no );
		$data = array("status"=>-1);
		if($total_fee>0){
			cache( $trade_no, null );
			$data["status"] = 1;
		}else{// 检查缓存是否存在，存在说明支付成功
			$data["status"] = -1;
		}
		return $data;
	}
	
	/**
	 * 微信异步通知
	 */
	public function wxNotify() {

		require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Crypto/AesGcm.php';
		require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Crypto/Rsa.php';

		$wxHeader = $_SERVER;
		$postData = file_get_contents("php://input");

		file_put_contents("runtime/log_wxpay_nodify.txt",date('Y-m-d H:i:s')."----postData----".$postData."----\n", FILE_APPEND);  
		file_put_contents("runtime/log_wxpay_nodify.txt",date('Y-m-d H:i:s')."----wxHeader----".json_encode($wxHeader)."----\n", FILE_APPEND);   


		$wechatpay_timestamp = $wxHeader['HTTP_WECHATPAY_TIMESTAMP'];
		$wechatpay_nonce = $wxHeader['HTTP_WECHATPAY_NONCE'];
		$wechatpay_signature = $wxHeader['HTTP_WECHATPAY_SIGNATURE'];
		$signature_str = $wechatpay_timestamp."\n".$wechatpay_nonce."\n".$postData."\n";

		$publicKey = $this->wxpay['publicKey'];
		$signStatus =  \WeChatPay\Crypto\Rsa::verify($signature_str,$wechatpay_signature,$publicKey);

		if($signStatus){
			$wxResult = json_decode($postData,true);
			$cipherText = \WeChatPay\Crypto\AesGcm::decrypt($wxResult['resource']['ciphertext'], $this->wxpay['apiKey'],$wxResult['resource']['nonce'],$wxResult['resource']['associated_data']);
			$order = json_decode($cipherText,true);

			$trade_no = $order["id"];
			$total_fee = $order['amount']["total"];
			$pkey = $order ["attach"] ;
			$pkeys = explode ( "@", $pkey );
			$out_trade_no = 0;
			
			$out_trade_no = $order["out_trade_no"];
			$userId = (int)$pkeys [1];
			$auctionId = (int)$pkeys [2];
			$obj = array ();
			$obj["trade_no"] = $trade_no;
			$obj["out_trade_no"] = $out_trade_no;
			$obj["userId"] = $userId;
			$obj["auctionId"] = $auctionId;
			$obj["total_fee"] = (float)$total_fee/100;
			$obj["payFrom"] = 'weixinpays';
			$obj["payObj"] = $pkeys[0];
			// 支付成功业务逻辑
			$m = new AM();
			$rs = $m->complateCautionMoney ( $obj );
				
			if($rs["status"]==1){
				cache("$out_trade_no",$total_fee);
				echo '{"code": "SUCCESS","message": "成功"}';
			}else{
				echo '{"code": "FAIL","message": "FAIL"}';
			}

		}else{
			echo '{"code": "FAIL","message": "签名失败"}';
		}

	}

	/**
	 * 检查支付结果
	 */
	public function paySuccess() {
		$pkey = base64_decode(input("pkey"));
		$pkeys = explode("@", $pkey );
		$auctionId = (int)$pkeys[2];
		if($pkeys[0]=="bao"){
			return $this->fetch('/home/index/pay_success');
		}else{
			$this->redirect(addon_url("auction://users/checkPayStatus",array("id"=>$auctionId),true,true));
		}
	}

	
}
