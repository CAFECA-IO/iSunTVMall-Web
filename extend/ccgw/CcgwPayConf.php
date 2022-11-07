<?php

/**
 * 	配置账号信息
 */

class CcgwPayConf {
	
	static public $APPKEY;
	static public $SECRETKEY;
	static public $CURL_TIMEOUT;

	
	static public $NOTIFY_URL;
	static public $RETURN_URL;
	// =======【基本信息设置】=====================================
	public function __construct($payconfig = array()) {
	
		self::$APPKEY = $payconfig['appKey'];
		self::$SECRETKEY = $payconfig['secretKey'];

		self::$CURL_TIMEOUT = $payconfig['curl_timeout'];
		self::$NOTIFY_URL = $payconfig['notifyurl'];
		self::$RETURN_URL = $payconfig['returnurl'];
	
	}
}

?>