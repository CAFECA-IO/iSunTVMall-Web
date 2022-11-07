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
class Weixinpays extends Base{

	/**
	 * 初始化
	 */
	private $wxpay;
	public function initialize() {
		header ("Content-type: text/html; charset=utf-8");

        require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Builder.php';
        require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Util/PemUtil.php';
		$m = new M();
		$this->wxpay = $m->getPayment("weixinpays");
		
	}

	/**
	 * 退款
	 */
	public function presaleRefund($amoney){


        $refund_no = $amoney['id'].$amoney['userId'];
        $refund_reason = lang("presale_order_fail_back").($amoney['moneyType']==1?lang("presale_deposit_money"):lang("presale_deposit_balance_title"));
        $notify_url = addon_url("presale://cron/refundnotify","",true,true);

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
	
		$resp = $instance
				->chain('hk/v3/refunds') 
				->postAsync([
					'json' => [
						'mchid' => $this->wxpay['mchId'],
						'appid' => $this->wxpay['appId'],
						'transaction_id' => $amoney['tradeNo'],
						'out_refund_no' => $refund_no,
						'reason' => $refund_reason,
						'notify_url' => $notify_url,
						'amount' => [
							'refund' => $amoney['presaleMoney']*100,
							'total' => $amoney["presaleMoney"]*100,
							'currency' => 'HKD',
						],
					],
				])
				->then(static function($response) {
					// 正常逻辑回调处理
					//echo $response->getBody(), PHP_EOL;
					return $response;
				})
				->otherwise(static function($e) {
					// 异常错误处理
					echo $e->getMessage(), PHP_EOL;
					if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
						$r = $e->getResponse();
						echo $r->getStatusCode() . ' ' . $r->getReasonPhrase(), PHP_EOL;
						echo $r->getBody(), PHP_EOL, PHP_EOL, PHP_EOL;
					}
					echo $e->getTraceAsString(), PHP_EOL;
				})
				->wait();
		
		$wxRsStr = $resp->getBody();
		$wxData = json_decode($wxRsStr,true);

		if(isset($wxData['id'])){

	        $payParams = [];
	        $payParams["userId"] = (int)$amoney['userId'];
	        $payParams["id"] = $amoney['id'];

	        $pdata = array();
	        $pdata["userId"] = $amoney['userId'];
	        $pdata["transId"] = $refund_no;
	        $pdata["paramsVa"] = json_encode($payParams);
	        $pdata["payFrom"] = 'weixinpays';
	        $m = new PM();
	        $m->addPayLog($pdata);
	        $rs = $refund->getResult();
			if($rs["result_code"]=="SUCCESS"){
				return WSTReturn(WSTLang("presale_refund_success"),1);
			}else{
				return WSTReturn($rs['err_code_des'],-1);
			}
		}
    }

    /**
     * 异步通知
     */
   	public function presaleNotify(){

        require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Crypto/AesGcm.php';
        require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Crypto/Rsa.php';

        $wxHeader = $_SERVER;
        $postData = file_get_contents("php://input");
		
		file_put_contents("runtime/log_wxpay_refund_nodify.txt",date('Y-m-d H:i:s')."----postData----".$postData."----\n", FILE_APPEND);  
		file_put_contents("runtime/log_wxpay_refund_nodify.txt",date('Y-m-d H:i:s')."----wxHeader----".json_encode($wxHeader)."----\n", FILE_APPEND);    
		
		
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
            if ($order["refund_status"] == "SUCCESS"){
                $transId = $order["out_refund_no"];
 
				$m = new PM();
	          	$payParams = $m->getPayLog(["transId"=>$transId]);
		   		$obj = array();
		        $obj['refundTradeNo'] = $order["refund_id"];//微信退款单号
		        $obj['id'] = $payParams['id'];
		        $m = new PRM();
		        $rs = $m->complateRefund($obj);

                if($rs['status']==1){
                    echo '{"code": "SUCCESS","message": "成功"}';
                }else{
                    header('HTTP/1.1 400 Bad Request');exit();
                }
            }else{
                header('HTTP/1.1 400 Bad Request');exit();
            }
            
        }else{
            header('HTTP/1.1 400 Bad Request');exit();
        }

   	}

}
