<?php
namespace addons\presale\controller;
use think\addons\Controller;
use think\Loader;
use Env;
use wstmart\common\model\Payments as PM;
use addons\presale\model\Presales as M;
use addons\presale\model\Orders as OM;
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
 * 微信支付控制器
 */
class Weixinpaysmo extends Controller{
	/**
	 * 初始化
	 */
	private $instance;
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
		$m = new M();
		$payFrom = (int)input("payFrom");//0:PC 1:微信
		$data = array();
		$pkey = input("pkey");
        $vpkey = WSTBase64urlDecode($pkey);
        $vpkey = explode('@',$vpkey);
		$payObj = $vpkey[0];
		$porderId = (int)$vpkey[1];
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


	public function toPay(){
		$pkey = input('pkey');
		$pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
        $payObj = $pkey[0];
        $porderId = (int)$pkey[1];
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
    	$notify_url = addon_url("presale://weixinpaysmo/notify","",true,true);

    	if($data["status"]==1){
			$out_trade_no = WSTOrderNo();
			$body = ($payObj=="ding")?lang("presale_pay_deposit_title"):lang("presale_pay_balance_title");
			$attach = $payObj."@".$userId."@".$porderId;
			$wap_name = WSTConf('CONF.mallName');

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

			    $resp = $instance->hk->v3->transactions->mweb->post(['json' => [
			        'mchid' => $this->wxpay['mchId'],
			        'out_trade_no' => $out_trade_no,
			        'appid' => $this->wxpay['appId'],
			        'description' => $body,
			        'attach' => "$attach",
			        'trade_type'=>'MWEB',
			        'notify_url' => addon_url("presale://weixinpaysmo/notify","",true,true),
			        'amount' => [
			            'total' => $needPay * 100,
			            'currency' => 'HKD'
			        ],
			        'scene_info' => [
			            'payer_client_ip' => WSTGetClientIp()
			        ],
			        'merchant_category_code'=>$this->wxpay['mccCode']
			    ]]);
			    

			    $wxRsStr = $resp->getBody();
			    $wxQrcodePayResult = json_decode($wxRsStr,true);
			    $mweb_url = '';
				// 商户根据实际情况设置相应的处理流程
				if ($resp->getStatusCode() != 200) {
					// 商户自行增加处理流程
					echo lang('communication_error')."：" . $resp->getStatusCode(). "<br>";
				} else {
					if ($wxQrcodePayResult ["mweb_url"] != NULL) {
						// 从统一支付接口获取到code_url
						$mweb_url = $wxQrcodePayResult ["mweb_url"];
					}
				}
				$this->assign ( 'out_trade_no', $out_trade_no );
				$this->assign ( 'mweb_url', $mweb_url );
				$this->assign ( 'needPay', $needPay );
	    		$this->assign('mweb_url',$mweb_url."&redirect_url".urlencode($return_url));
			} catch (Exception $e) {
			    // 进行错误处理
			    echo $e->getMessage(), PHP_EOL;
			    if ($e instanceof \Psr\Http\Message\ResponseInterface && $e->hasResponse()) {
			        echo $e->getResponse()->getStatusCode() . ' ' . $e->getResponse()->getReasonPhrase(), PHP_EOL;
			        echo $e->getResponse()->getBody();
			    }
			}


			if($payObj=='ding'){
				$rs = $m->getPayInfo($porderId,1);
			}else{
				$rs = $m->getPayInfo($porderId,2);
			}

			$rs['data']['porder']['needPay'] = $needPay;
			$this->assign('payFrom','weixinpays');
			$this->assign('payObj',$payObj);
			$this->assign('object', $rs['data']['porder']);
			$this->assign('returnUrl',$return_url);
			$this->assign('needPay',$needPay);

			return $this->fetch('mobile/index/pay_weixin');
		}

	}

	public function notify() {

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
			file_put_contents("runtime/log_wxpay_nodify.txt",date('Y-m-d H:i:s')."----presale--signStatus----ok----\n", FILE_APPEND); 
			$wxResult = json_decode($postData,true);
			$cipherText = \WeChatPay\Crypto\AesGcm::decrypt($wxResult['resource']['ciphertext'], $this->wxpay['apiKey'],$wxResult['resource']['nonce'],$wxResult['resource']['associated_data']);
			file_put_contents("runtime/log_wxpay_nodify.txt",date('Y-m-d H:i:s')."----presale----".$cipherText."----\n", FILE_APPEND); 
			$order = json_decode($cipherText,true);
			file_put_contents("runtime/log_wxpay_nodify.txt",date('Y-m-d H:i:s')."----presale----process----\n", FILE_APPEND); 
			$rs = $this->process($order);
			if($rs["status"]==1){
				header('HTTP/1.1 200 ok');exit();
			}else{
				header('HTTP/1.1 400 Bad Request');exit();
			}
		}else{
			header('HTTP/1.1 400 Bad Request');exit();
		}

	}


	//订单处理
	private function process($order) {

		$obj = array();

		$obj["trade_no"] = $order['id'];
		$totalFee = $order['amount']["total"];
		$obj["total_fee"] = (float)$totalFee/100;
		$obj["out_trade_no"] = $order['out_trade_no'];
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
