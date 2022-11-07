<?php
include_once ("CcgwException.php");
/**
 * 所有接口的基类
 */
class CcgwPayBase {

	function __construct() {}

	function trimString($value) {
		$ret = null;
		if (null != $value) {
			$ret = $value;
			if (strlen ( $ret ) == 0) {
				$ret = null;
			}
		}
		return $ret;
	}
	
	/**
	 * 作用：产生随机字符串，不长于32位
	 */
	public function createNoncestr($length = 32) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = "";
		for($i = 0; $i < $length; $i ++) {
			$str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
		}
		return $str;
	}
	
	/**
	 * 作用：格式化参数，签名过程需要使用
	 */
	function formatBizQueryParaMap($paraMap, $urlencode) {
		$buff = "";
		ksort ( $paraMap );
		foreach ( $paraMap as $k => $v ) {
			if ($urlencode) {
				$v = urlencode ( $v );
			}
			// $buff .= strtolower($k) . "=" . $v . "&";
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if (strlen ( $buff ) > 0) {
			$reqPar = substr ( $buff, 0, strlen ( $buff ) - 1 );
		}
		return $reqPar;
	}
	
	/**
	 * 作用：生成签名
	 */
	public function getSign($Obj) {
		foreach ( $Obj as $k => $v ) {
			$Parameters [$k] = $v;
		}
		// 签名步骤一：按字典序排序参数
		ksort ( $Parameters );
		$String = $this->formatBizQueryParaMap ( $Parameters, false );
		// 签名步骤二：在string后加入KEY
		$String = $String . "&secret_key=" . CcgwPayConf::$SECRETKEY;
		//echo $String."<br>";
		// 签名步骤三：MD5加密
		$String = md5 ( $String );
		// 签名步骤四：所有字符转为大写
		$result_ = strtoupper ( $String );
		return $result_;
	}
	
	
	/**
	 * 作用：以post方式提交xml到对应的接口url
	 */
	public function postCurl($url) {
		//$url = 'https://ccgw.isuntv.com/api/cc/ApplierInfo?appkey=stvmall2&datetime=2021/06/17%2010:42:15&sign=66C56915E5C608FC37D6D55C5BE8DB48';
		$url = str_replace(' ', '%20', $url);
		$ch = curl_init();
		//$url = $url . curl_escape($ch,$paramStr);
		//echo $url;
        //$headers = array();
        //$headers[] = 'Host:' . parse_url($url)['host'];
        //$headers[] = 'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:55.0) Gecko/20100101 Firefox/55.0';
        //$headers[] = 'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        //$headers[] = 'Accept-Language:zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3';
        //$headers[] = 'Connection:keep-alive';
        //$headers[] = 'Upgrade-Insecure-Requests:1';
        //设置选项，包括URL
        curl_setopt ($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        //curl_setopt($ch, CURLOPT_HTTPHEADER  , $headers);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);//允许请求的内容以文件流的形式返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);//禁用https
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 

        

        curl_setopt($ch,CURLOPT_PROXY,'127.0.0.1');
        curl_setopt($ch,CURLOPT_PROXYPORT,'10809');

        //执行并获取HTML文档内容
        $rs = curl_exec($ch);
        //释放curl句柄
        
        //打印获得的数据
        //var_dump(curl_error($ch));
        //var_dump($output);
        //echo $rs;
        curl_close($ch);
        return json_decode($rs,true);

	}
	
}

?>
