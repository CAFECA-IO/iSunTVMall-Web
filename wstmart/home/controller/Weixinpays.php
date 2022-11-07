<?php
namespace wstmart\home\controller;
use think\Loader;
use Env;

use wstmart\common\model\Payments as M;
use wstmart\common\model\Orders as OM;
use wstmart\common\model\LogMoneys as LM;
use wstmart\common\model\Shops as SM;
use wstmart\common\model\Suppliers as SUM;
use wstmart\common\model\LogPays as PM;
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
 * 微信支付控制器
 */
class Weixinpays extends Base{
	
	/**
	 * 初始化
	 */
	private $instance;
	public function initialize() {
		header ("Content-type: text/html; charset=utf-8");

		require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Builder.php';
		require Env::get('root_path') . 'vendor/wechatpay/wechatpay/src/Util/PemUtil.php';
		$m = new M();
		$this->wxpay = $m->getPayment("weixinpays");

	}
	
	/**
	 * 获取微信URL
	 */
	public function getWeixinPaysURL(){
		$m = new OM();
		$payObj = input("payObj/s");
		$pkey = "";
		$data = array();
		if($payObj=="recharge"){
			$cm = new CM();
			$itmeId = (int)input("itmeId/d");
			$targetType = (int)input("targetType/d");
			$targetId = (int)session('WST_USER.userId');
			if($targetType==1){//商家
				$targetId = (int)session('WST_USER.shopId');
			}
			$needPay = 0;
			if($itmeId>0){
				$item = $cm->getItemMoney($itmeId);
				$needPay = isSet($item["chargeMoney"])?$item["chargeMoney"]:0;
			}else{
				$needPay = (int)input("needPay/d");
			}
			$data["status"] = $needPay>0?1:-1;
			$pkey = $payObj."@".$targetId."@".$targetType."@".$needPay."@".$itmeId;
		}elseif($payObj=="enter"){
		    $flowId = (int)input("flowId");
            $pkey = input("pkey");
            $pkey = WSTBase64urlDecode($pkey);
            $pkey = explode('@',$pkey);
            
            $userId = (int)session('WST_USER.userId');
            $trade = model("shops")->getTradeFee($userId);
            $needPay = $trade['tradeFee'];
            $targetType = 0;
            $targetId = (int)session('WST_USER.userId');
            $data["status"] = $needPay>0?1:-1;
            $pkey = $payObj."@".$targetId."@".$targetType."@".$needPay."@".$flowId;
        }elseif($payObj=="supplier_enter"){
            $flowId = (int)input("flowId");
            $pkey = input("pkey");
            $pkey = WSTBase64urlDecode($pkey);
            $pkey = explode('@',$pkey);

            $userId = (int)session('WST_USER.userId');
            $trade = model("suppliers")->getTradeFee($userId);
            $needPay = $trade['tradeFee'];
            $targetType = 0;
            $targetId = (int)session('WST_USER.userId');
            $data["status"] = $needPay>0?1:-1;
            $pkey = $payObj."@".$targetId."@".$targetType."@".$needPay."@".$flowId;
        }else{
			$userId = (int)session('WST_USER.userId');
			$pkey = input("pkey");
			$pkey = WSTBase64urlDecode($pkey);
	        $pkey = explode('@',$pkey);
	        $orderNo = $pkey[0];
	        $isBatch = (int)$pkey[1];
	        $obj= ["userId"=>$userId,"orderNo"=>$orderNo,"isBatch"=>$isBatch];
			$data = $m->checkOrderPay2($obj);
			if($data["status"]==1){
				$pkey = $payObj."@".$userId."@".$orderNo;
				if($isBatch==1){
					$pkey = $pkey."@1";
				}else{
					$pkey = $pkey."@2";
				}
			}
		}
		if($data["status"]!=1){
			if(!(isSet($data["msg"])) || $data["msg"]=="") $data["msg"]= lang('pay_failed');
		}
		$data["url"] = url('home/weixinpays/createQrcode',array("pkey"=>WSTBase64urlEncode($pkey)));
		return $data;
	}
	
	public function createQrcode() {
		$pkey = WSTBase64urlDecode(input("pkey"));
		$pkeys = explode("@", $pkey );
		$flag = true;
		$needPay = 0;
		$out_trade_no = 0;
		$trade_no = 0;
		if($pkeys[0]=="recharge"){
			$needPay = (int)$pkeys[3];
			$out_trade_no = WSTOrderNo();
			$body = lang('recharge_wallet');
			$trade_no = $out_trade_no;
		}elseif($pkeys[0]=="enter"){
			$userId = (int)session('WST_USER.userId');
            $trade = model("shops")->getTradeFee($userId);
            $needPay = $trade['tradeFee'];
            $out_trade_no = WSTOrderNo();
            $body = lang('shop_enter_pay_annual_fee');
            $trade_no = $out_trade_no;
        }elseif($pkeys[0]=="supplier_enter"){
            $userId = (int)session('WST_USER.userId');
            $trade = model("suppliers")->getTradeFee($userId);
            $needPay = $trade['tradeFee'];
            $out_trade_no = WSTOrderNo();
            $body = lang('supplier_enter_pay_annual_fee');
            $trade_no = $out_trade_no;
        }else{
			if(count($pkeys)!= 4){
				$this->assign('out_trade_no', "");
			}else{
				$userId = (int)session('WST_USER.userId');
				$obj = array();
				$obj["userId"] = $userId;
				$obj["orderNo"] = $pkeys[2];
				$obj["isBatch"] = $pkeys[3];
				$m = new OM();
				$order = $m->getPayOrders($obj);
				$needPay = $order["needPay"];
				$payRand = $order["payRand"];
				$body = lang('pay_for_order');
				$out_trade_no = $obj["orderNo"]."a".$payRand;
				$trade_no = $obj["orderNo"];
			}
		}
		
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
			        'notify_url' => url("home/weixinpays/wxNotify","",true,true),
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
				$this->assign ( 'out_trade_no', $trade_no );
				$this->assign ( 'code_url', $code_url );
				$this->assign ( 'needPay', $needPay );
			} catch (Exception $e) {
			    // 进行错误处理
			    echo $e->getMessage(), PHP_EOL;
			    if ($e instanceof \Psr\Http\Message\ResponseInterface && $e->hasResponse()) {
			        echo $e->getResponse()->getStatusCode() . ' ' . $e->getResponse()->getReasonPhrase(), PHP_EOL;
			        echo $e->getResponse()->getBody();
			    }
			}
				
		}else{
			$flag = false;
		}
		if($pkeys[0]=="recharge"){
			if($pkeys[2]==1){
				return $this->fetch('shops/recharge/pay_step2');
			}else{
				return $this->fetch('users/recharge/pay_step2');
			}
		}elseif($pkeys[0]=="enter"){
            $flowId = $pkeys[4];
            $pkey = WSTBase64urlEncode($needPay."@2");
            $this->assign('pkey',$pkey);
            $this->assign('flowId',$flowId);
            $this->assign('payStep',2);
            $this->checkStep($flowId);
            $shopFlows = model('shops')->getShopFlowDatas($flowId);
            $stepFields = model('shops')->getFlowFieldsById($flowId);
            $this->assign('shopFlows',$shopFlows['flows']);
            $this->assign('prevStep',$shopFlows['prevStep']);
            $this->assign('currStep',$shopFlows['currStep']);
            $this->assign('nextStep',$shopFlows['nextStep']);
            $this->assign('stepFields',$stepFields);
            $apply = model('shops')->getShopApply();
            $this->assign('apply',$apply);
            $this->assign('payType','weixinpays');
            return $this->fetch('shop_join_step');
        }elseif($pkeys[0]=="supplier_enter") {
            $flowId = $pkeys[4];
            $pkey = WSTBase64urlEncode($needPay."@2");
            $this->assign('pkey',$pkey);
            $this->assign('flowId',$flowId);
            $this->assign('payStep',2);
            $this->supplierCheckStep($flowId);
            $supplierFlows = model('suppliers')->getSupplierFlowDatas($flowId);
            $stepFields = model('suppliers')->getFlowFieldsById($flowId);
            $this->assign('supplierFlows',$supplierFlows['flows']);
            $this->assign('prevStep',$supplierFlows['prevStep']);
            $this->assign('currStep',$supplierFlows['currStep']);
            $this->assign('nextStep',$supplierFlows['nextStep']);
            $this->assign('stepFields',$stepFields);
            $apply = model('suppliers')->getSupplierApply();
            $this->assign('apply',$apply);
            $this->assign('payType','weixinpays');
            return $this->fetch('suppliers/supplier_join_step');
        }else{
			if($flag){
				return $this->fetch('order_pay_step2');
			}else{
				return $this->fetch('order_pay_step3');
			}
		}
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

			// 此处应该更新一下订单状态，商户自行增删操作
			$trade_no = $order["id"];
			$total_fee = $order['amount']["total"];
			$pkey = $order ["attach"] ;
			$pkeys = explode ( "@", $pkey );
			$out_trade_no = 0;
			$userId = 0;
			if($pkeys[0]=="recharge"){//充值
				$out_trade_no = $order["out_trade_no"];
				$targetId = (int)$pkeys [1];
				$userId = $targetId;
				$targetType = (int)$pkeys [2];
				$itemId = (int)$pkeys [4];
				$obj = array ();
				$obj["trade_no"] = $trade_no;
				$obj["out_trade_no"] = $out_trade_no;
				$obj["targetId"] = $targetId;
				$obj["targetType"] = $targetType;
				$obj["itemId"] = $itemId;
				$obj["total_fee"] = (float)$total_fee/100;
				$obj["payFrom"] = 'weixinpays';
				// 支付成功业务逻辑
				$m = new LM();
				$rs = $m->complateRecharge ( $obj );
			}elseif($pkeys[0]=="enter"){ // 缴纳年费
                $out_trade_no = $order["out_trade_no"];
                $targetId = (int)$pkeys [1];
                $userId = $targetId;
                $targetType = (int)$pkeys [2];
                $obj = array ();
                $obj["trade_no"] = $trade_no;
                $obj["out_trade_no"] = $out_trade_no;
                $obj["targetId"] = $targetId;
                $obj["targetType"] = $targetType;
                $obj["total_fee"] = (float)$total_fee/100;
                $obj["payFrom"] = 'weixinpays';
                $obj["scene"] = 'enter';
                // 支付成功业务逻辑
                $m = new SM();
                $rs = $m->completeEnter($obj);
            }elseif($pkeys[0]=="supplier_enter"){ // 供货商缴纳年费
                $out_trade_no = $order["out_trade_no"];
                $targetId = (int)$pkeys [1];
                $userId = $targetId;
                $targetType = (int)$pkeys [2];
                $obj = array ();
                $obj["trade_no"] = $trade_no;
                $obj["out_trade_no"] = $out_trade_no;
                $obj["targetId"] = $targetId;
                $obj["targetType"] = $targetType;
                $obj["total_fee"] = (float)$total_fee/100;
                $obj["payFrom"] = 'weixinpays';
                $obj["scene"] = 'enter';
                // 支付成功业务逻辑
                $m = new SUM();
                $rs = $m->completeEnter($obj);
            }else{//订单支付
				$userId = (int)$pkeys [1];
				$out_trade_no = $pkeys[2];
				$isBatch = (int)$pkeys[3];
				// 商户订单
				$obj = array ();
				$obj["trade_no"] = $trade_no;
				$obj["out_trade_no"] = $out_trade_no;
				$obj["isBatch"] = $isBatch;
				$obj["total_fee"] = (float)$total_fee/100;
				$obj["userId"] = $userId;
				$obj["payFrom"] = 'weixinpays';
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
				header('HTTP/1.1 200 OK');exit();
			}else{
				header('HTTP/1.1 400 Bad Request');exit();
			}
		}else{
			header('HTTP/1.1 400 Bad Request');exit();
		}

	}

	/**
	 * 检查支付结果
	 */
	public function paySuccess() {
		return $this->fetch('order_pay_step3');
	}

    /*
     * 入驻缴纳年费同步回调方法
     */
    function payAnnualFeeSuccess(){
        return $this->redirect(url("home/shops/joinstepnext",array('id'=>(int)input("flowId")),true,true));
    }

    /**
     * 检测入驻商城时有步骤有没有遗漏，不允许跳过步骤
     */
    public function checkStep($flowId){
        $this->checkUserType('shop');
        if((int)WSTConf('CONF.isOpenShopApply')!=1)return;
        $tmpShopApplyFlow = session('tmpShopApplyFlow');
        $tmpApplyStep = (int)session('tmpApplyStep');
        //如果没有建立数组则强制重新开始
        if(!$tmpShopApplyFlow){
            return $this->redirect(Url('home/shops/join'));
        }
        $flowSteps = [];
        $isFind = false;
        foreach ($tmpShopApplyFlow as $key => $v) {
            $flowSteps[] = $v['flowId'];
            if($v['flowId']==$tmpApplyStep){
                $isFind = true;
                break;
            }
        }
        //没找到这个环节强制重新开始
        if(!$isFind){
            $this->redirect(Url('home/shops/joinStepNext',array('id'=>$tmpShopApplyFlow[0]['flowId'])));
            exit();
        }
        //如果找到则判断是否当前环节是否有效
        if(!in_array($flowId,$flowSteps)){
            $flowId = end($flowSteps);
            $this->redirect(Url('home/shops/joinStepNext',array('id'=>$flowId)));
            exit();
        }
    }

    /*
     * 供货商入驻缴纳年费同步回调方法
     */
    function supplierPayAnnualFeeSuccess(){
        return $this->redirect(url("home/suppliers/joinstepnext",array('id'=>(int)input("flowId")),true,true));
    }

    /**
     * 检测供货商入驻商城时有步骤有没有遗漏，不允许跳过步骤
     */
    public function supplierCheckStep($flowId){
        $this->checkUserType('supplier');
        if((int)WSTConf('CONF.isOpenSupplierApply')!=1)return;
        $tmpSupplierApplyFlow = session('tmpSupplierApplyFlow');
        $tmpApplyStep = (int)session('tmpApplyStep');
        //如果没有建立数组则强制重新开始
        if(!$tmpSupplierApplyFlow){
            return $this->redirect(Url('home/suppliers/join'));
        }
        $flowSteps = [];
        $isFind = false;
        foreach ($tmpSupplierApplyFlow as $key => $v) {
            $flowSteps[] = $v['flowId'];
            if($v['flowId']==$tmpApplyStep){
                $isFind = true;
                break;
            }
        }
        //没找到这个环节强制重新开始
        if(!$isFind){
            $this->redirect(Url('home/suppliers/joinStepNext',array('id'=>$tmpSupplierApplyFlow[0]['flowId'])));
            exit();
        }
        //如果找到则判断是否当前环节是否有效
        if(!in_array($flowId,$flowSteps)){
            $flowId = end($flowSteps);
            $this->redirect(Url('home/suppliers/joinStepNext',array('id'=>$flowId)));
            exit();
        }
    }

    // 检测用户账号
    public function checkUserType($type=''){
        $USER = session('WST_USER');
        if($type=='shop'){
            if(!($USER['userType']==0 || $USER['userType']==1)){
                if(request()->isAjax()){
                    die('{"status":-999,"msg":"'.lang('shop_enter_account_reday_desc').'"}');
                }else{
                    $this->redirect('home/shops/disableApply');
                    exit;
                }
            }
        }else{
            if(!($USER['userType']==0 || $USER['userType']==3)){
                if(request()->isAjax()){
                    die('{"status":-999,"msg":"'.lang('suppler_enter_account_reday_desc').'"}');
                }else{
                    $this->redirect('home/suppliers/disableApply');
                    exit;
                }
            }
        }
    }



    public function queryPayment() {
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
			    $resp = $instance->hk->v3->transactions->id->{'{transaction_id}'}
			    ->getAsync([
				    // 查询参数结构
				    'query' => ['mchid' => '132447074'],
				    // uri_template 字面量参数
				    'transaction_id' => '4200001230202108277639478143',
				])
				->then(static function($response) {
				    // 正常逻辑回调处理
				    echo $response->getBody(), PHP_EOL;
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
			    $tt = json_decode($wxRsStr,true);
			    print_r($tt);
			
			} catch (Exception $e) {exit();
			    // 进行错误处理
			    echo $e->getMessage(), PHP_EOL;
			    if ($e instanceof \Psr\Http\Message\ResponseInterface && $e->hasResponse()) {
			        echo $e->getResponse()->getStatusCode() . ' ' . $e->getResponse()->getReasonPhrase(), PHP_EOL;
			        echo $e->getResponse()->getBody();
			    }
			}
	}

	public function refund() {
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
				$resp = $instance
						->chain('hk/v3/refunds')
						->postAsync([
						    'json' => [
						    	'mchid' => $this->wxpay['mchId'],
						        'appid' => $this->wxpay['appId'],
						        'transaction_id' => '4200001230202108277639478143',
						        'out_refund_no'  => '454636564354',
						        'amount'         => [
						            'refund'   => 20,
						            'total'    => 20,
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
			    $tt = json_decode($wxRsStr,true);
			    print_r($tt);
			
			} catch (Exception $e) {exit();
			    // 进行错误处理
			    echo $e->getMessage(), PHP_EOL;
			    if ($e instanceof \Psr\Http\Message\ResponseInterface && $e->hasResponse()) {
			        echo $e->getResponse()->getStatusCode() . ' ' . $e->getResponse()->getReasonPhrase(), PHP_EOL;
			        echo $e->getResponse()->getBody();
			    }
			}
	}

}
