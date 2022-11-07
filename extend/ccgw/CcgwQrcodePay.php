<?php

/**
 * 扫码支付接口类
 */

include_once ("CcgwPayBase.php");


class CcgwQrcodePay extends CcgwPayBase {
	public $parameters; // 请求参数，类型为关联数组
	public $response; // 微信返回的响应
	public $result; // 返回参数，类型为关联数组
	public $url; // 接口链接
	public $curl_timeout; // curl超时时间
	public $data; // 接收到的数据，类型为关联数组
	public $returnParameters; // 返回参数，类型为关联数组
	
	function __construct() {
		// 设置接口链接
		$this->url = "https://ccgw.isuntv.com/";
		// 设置curl超时时间
		$this->curl_timeout = CcgwPayConf::$CURL_TIMEOUT;
	}

	function getPriceList(){

		$url = 'https://ccgw.isuntv.com/api/cc/PriceList';
		$rs = $this->postCurl($url);
		return $rs;

	}

	function getTokenPrice($basecctype,$direction){

		$url = 'https://ccgw.isuntv.com/api/cc/TokenPrice?basecctype='.$basecctype.'&direction='.$direction;
		$rs = $this->postCurl($url);
		print_r($rs);

	}

	function getPricePair($basecctype,$quotecctype,$direction){

		$url = 'https://ccgw.isuntv.com/api/cc/PricePair?quotecctype='.$quotecctype.'&basecctype='.$basecctype.'&direction='.$direction;
		//echo $url;
		$rs = $this->postCurl($url);
		return $rs;

	}

	

	function getOneDeposit($cctype,$callbackurl){
		
		$appkey = CcgwPayConf::$APPKEY;
		$datetime = date("Y/m/d H:i:s");
		$obj = ['appkey'=>$appkey,'callbackurl'=>$callbackurl,'cctype'=>$cctype,'datetime'=>$datetime];
		$sign =  $this->getSign($obj);
		$url = 'https://ccgw.isuntv.com/api/cc/OneDeposit?appkey='.$appkey.'&callbackurl='.$callbackurl.'&cctype='.$cctype.'&datetime='.$datetime.'&sign='.$sign;

		$rs = $this->postCurl($url);
		return $rs;
	}
	

	function getCCPaymentDetail($txid){
		
		$appkey = CcgwPayConf::$APPKEY;
		$datetime = date("Y/m/d H:i:s");
		$obj = ['appkey'=>$appkey,'datetime'=>$datetime,'txid'=>$txid];
		$sign =  $this->getSign($obj);
		$url = 'https://ccgw.isuntv.com/api/cc/CCPaymentDetail?appkey='.$appkey.'&datetime='.$datetime.'&txid='.$txid.'&sign='.$sign;

		$rs = $this->postCurl($url);
		return $rs;
	}

	/**
	 * 将微信的请求xml转换成关联数组，以方便数据处理
	 */
	function saveData($postData) {
		$this->data = json_decode ( $postData, true );
	}

	/**
	 * 获取微信的请求数据
	 */
	function getData() {
		return $this->data;
	}
	

	/**
	 * 检测签名
	 * @return boolean
	 */
	function checkSign($tmpData) {
		//$tmpData = $this->data;
		unset ( $tmpData ['sign'] );
		$sign = $this->getSign ( $tmpData ); // 本地签名
		if ($this->data ['sign'] == $sign) {
			return TRUE;
		}
		return FALSE;
	}
	
}

?>
