<?php
namespace wstmart\app\controller;
use wstmart\app\model\ThirdLogin as M;
use think\Loader;
use Env;
use wstmart\common\model\Payments as PM;
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
 * 第三方登录控制器
 */
use think\Db;
class ThirdLogin extends Base{
    /**
     * 获取AppId、AppSecret
     */
    private function getPayment($payCode){
        $payment = model('common/payments')->where("enabled=1 AND payCode='$payCode' AND isOnline=1")->find();
        $payConfig = json_decode($payment["payConfig"]) ;
        if(empty($payConfig))return [];
        foreach ($payConfig as $key => $value) {
            $payment[$key] = $value;
        }
        return $payment;
    }

    /**
     * google登入
     */
    public function googleLogin(){
        $googleId = input("id");
        $m = new M();
        $rs = $m->googleIsExists($googleId,input('loginRemark','android'));
        // # 未關聯賬號,返回unionId
        if(empty($rs)){
            // 進入綁定賬號頁 需要返回微信名稱,微信頭像，以及系統名稱、unionId
            $data = [];
            $data['nickname'] = input("name");
            $data['headimgurl'] = input("headimgurl");
            $data['sysname'] = WSTConf('CONF.mallName');
            $data['googleId'] = $googleId;
            return json_encode(WSTReturn(lang('unbind_unionId'),2,$data));
        }
        return json_encode(WSTReturn(lang('login_success'),1,$rs));
    }
    /**
     * facebook登入
     */
    public function facebookLogin(){
        $facebookId = input("id");
        $m = new M();
        $rs = $m->facebookIsExists($facebookId,input('loginRemark','android'));
        // # 未關聯賬號,返回unionId
        if(empty($rs)){
            // 進入綁定賬號頁 需要返回微信名稱,微信頭像，以及系統名稱、unionId
            $data = [];
            $data['nickname'] = input("name");
            $data['headimgurl'] = input("headimgurl");
            $data['sysname'] = WSTConf('CONF.mallName');
            $data['facebookId'] = $facebookId;
            return json_encode(WSTReturn(lang('unbind_unionId'),2,$data));
        }
        return json_encode(WSTReturn(lang('login_success'),1,$rs));
    }

    /**
     * @OA\Post(
     *     tags={"ThirdLogin"},
     *     path="/app/ThirdLogin/WechatLogin",
     *     summary="微信登录",
     *     description="微信登录",
	 *     @OA\Parameter(name="code", in="query", @OA\Schema(type="string"), required=true, description="微信授权code，由app端调用微信sdk跳转微信确认授权后返回的字符串"),
     *     @OA\Response(
     *      response="200",
     *      description="若该微信已绑定商城账号,则返回的json数据与登录返回的json数据一致。若该微信未绑定商城账号,则返回如下",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="status", type="string", description="状态值", example="2"),
     *                 @OA\Property(property="msg", type="string", description="返回结果描述", example="没找到与该unionId关联的账号"),
     *                 @OA\Property(property="data",type="object",
     *                     @OA\Property(property="nickname", type="integer", description="微信名", example=""),
     *                     @OA\Property(property="headimgurl", type="string", description="微信头像", example=""),
     *                     @OA\Property(property="sysname", type="string", description="商城名称", example=""),
     *                     @OA\Property(property="unionId",type="string", description="unionId"),
     *                 )
     *             ),
     *          )
     *     )
     * )
     */
    public function WechatLogin(){
        $wxInfo = $this->getPayment ("app_weixinpays");// 从微信开放平台申请到的APPId与AppSecret
        if(empty($wxInfo))return json_encode(WSTReturn(lang('third_login_config_error'),-1));

    	$appId = $wxInfo['appId'];
    	$appSecret = $wxInfo['appsecret'];
    	$code = input('code');// APP端用户同意授权登录之后获得的code
    	$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appId&secret=$appSecret&code=$code&grant_type=authorization_code";
    	// 第一步,根据code拿到用户的unionId
    	/*
			正常返回的数据：
			{ 
			"access_token":"ACCESS_TOKEN", 
			"expires_in":7200, 
			"refresh_token":"REFRESH_TOKEN",
			"openid":"OPENID", 
			"scope":"SCOPE",
			"unionid":"o6_bmasdasdsad6_2sgVt7hMZOPfL"
			}
			错误返回数据：
			{"errcode":40029,"errmsg":"invalid code"}
    	*/
    	$result = $this->curl($url);

    	$rsArr = json_decode($result,true);
    	if(!isset($rsArr['errcode'])){
            $m = new M();
    		// 第二步，根据unionId判断是否已存在,存在：返回tokenId（app_session表），不存在：返回unionId 让用户在APP页面完成注册
    		$rs = $m->wechatIsExists($rsArr['unionid'],input('loginRemark','android'));
    		// # 未关联账号,返回unionId
    		if(empty($rs)){
                $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$rsArr['access_token']."&openid=".$rsArr['openid'];
                $wxUserInfo = $this->curl($url);
                $wxUserInfoArr = json_decode($wxUserInfo,true);
                if(isset($wxUserInfoArr['errcode']))return WSTReturn(lang('auth_fail'),-1);
                // 进入绑定账号页 需要返回微信名称,微信头像，以及系统名称、unionId
                $data = [];
                $data['nickname'] = $wxUserInfoArr['nickname'];
                $data['headimgurl'] = $wxUserInfoArr['headimgurl'];
                $data['sysname'] = WSTConf('CONF.mallName');
                $data['unionId'] = $rsArr['unionid'];

                return json_encode(WSTReturn(lang('unbind_unionId'),2,$data));
            }

    		return json_encode(WSTReturn(lang('login_success'),1,$rs));

    	}else{
    		return json_encode(WSTReturn(lang('login_fail'), -1));
    	}
    }

    protected function getQqUnionId($access_token){
        $url = "https://graph.qq.com/oauth2.0/me?access_token={$access_token}&unionid=1";
        $jsonp = $this->curl($url);
        // $jsonp = callback( {"client_id":"1106009253","openid":"FF2AAC0F165944604F01E966B656B636","unionid":"UID_B2CDCC6E99933CFB1E74CB8128E64483"} );
        $jsonp = str_replace(' );','',str_replace('callback( ','', $jsonp));
        $json = json_decode($jsonp,true);
        if(isset($json['error']))return false;
        return $json;
    }
    /**
     * @OA\Post(
     *     tags={"ThirdLogin"},
     *     path="/app/ThirdLogin/qqLogin",
     *     summary="QQ登录",
     *     description="QQ登录",
	 *     @OA\Parameter(name="openid", in="query", @OA\Schema(type="string"), required=true, description="qq授权后获得"),
	 *     @OA\Parameter(name="access_token", in="query", @OA\Schema(type="string"), required=true, description="qq授权后获得"),
	 *     @OA\Parameter(name="oauth_consumer_key", in="query", @OA\Schema(type="string"), required=true, description="qq授权后获得"),
     *     @OA\Response(
     *      response="200",
     *      description="若该QQ已绑定商城账号,则返回的json数据与登录返回的json数据一致。若该QQ未绑定商城账号,则返回如下",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="status", type="string", description="状态值", example="2"),
     *                 @OA\Property(property="msg", type="string", description="返回结果描述", example="没找到与该qqOpenId关联的账号"),
     *                 @OA\Property(property="data",type="object",
     *                     @OA\Property(property="nickname", type="integer", description="QQ名", example=""),
     *                     @OA\Property(property="headimgurl", type="string", description="QQ头像", example=""),
     *                     @OA\Property(property="sysname", type="string", description="商城名称", example=""),
     *                     @OA\Property(property="unionId",type="string", description="unionId"),
     *                 )
     *             ),
     *          )
     *     )
     * )
     */
    public function qqLogin(){
        /* 
          检测是否存在账户,若用户同意授权之后传过来openId，存在，正常返回tokenId,
          不存在，调用接口，获取qq名称，qq头像
        */
        $openId = input('openid');
        $accessToken = input('access_token');
        $oauth_consumer_key = input('oauth_consumer_key');// APP端用户同意授权登录之后获得的oauth_consumer_key

        if(empty($openId) || empty($accessToken) || empty($oauth_consumer_key)){
            return json_encode(WSTReturn(lang('third_login_args_error')));
        }
        // #第一步：根据openId去获取unionId,
        $unionIdArr = $this->getQqUnionId($accessToken);
        if(!$unionIdArr)return json_encode(WSTReturn(lang('third_login_args_error')));
        $qqUnionId = $unionIdArr['unionid'];


        // #第二步：检测账号是否存在,存在：返回tokenId（app_session表），不存在：返回用户基础信息以及openId 让用户在APP页面完成注册
        $m = new M();
        $rs = $m->qqIsExists($qqUnionId,input('loginRemark','android'));
        if(empty($rs)){
                $url = "https://graph.qq.com/user/get_user_info?openid={$openId}&access_token={$accessToken}&oauth_consumer_key={$oauth_consumer_key}";
                $qqUserInfo = $this->curl($url);
                $qqUserInfoArr = json_decode($qqUserInfo,true);
                 /*
                    正常返回的数据：
                    {
                        "ret": 0,
                        "msg": "",
                        "is_lost": 0,
                        "nickname": "",  #qq用户名
                        "gender": "男",
                        "figureurl_qq_1": "http://q.qlogo.cn/qqapp/1106009253/869AE26AD0CEF78D738E75EB3F2EAA18/40", #用户头像 40*40
                        "figureurl_qq_2": "http://q.qlogo.cn/qqapp/1106009253/869AE26AD0CEF78D738E75EB3F2EAA18/100", #用户头像 100*100
                    }
                    错误返回数据：
                    {"ret":100008,"msg":"client request's app is not existed"}
                */
                if($qqUserInfoArr['ret']!=0)return WSTReturn(lang('auth_fail'),-1);
                // 进入绑定账号页 需要返回微信名称,微信头像，以及系统名称、unionId
                $data = [];
                $data['nickname'] = $qqUserInfoArr['nickname'];
                $data['headimgurl'] = $qqUserInfoArr['figureurl_qq_2'];
                $data['sysname'] = WSTConf('CONF.mallName');
                $data['openId'] = $qqUnionId;

                return json_encode(WSTReturn(lang('unbind_qqOpenId'),2,$data));
        }
        return json_encode(WSTReturn(lang('login_success'),1,$rs));
    }
    /************************************************************************** 支付宝登录 *********************************************************************************/
    /**
     * @OA\Get(
     *     tags={"ThirdLogin"},
     *     path="/app/ThirdLogin/alipayAuth",
     *     summary="支付宝授权接口",
     *     description="支付宝授权接口",
	 *     @OA\Parameter(name="alipayId", in="query", @OA\Schema(type="string"), required=true, description="用户通过支付宝授权后返回的user_id"),
	 *     @OA\Parameter(name="auth_code", in="query", @OA\Schema(type="string"), required=true, description="用户通过支付宝授权后返回的auth_code"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="status", type="string", description="状态值", example="1"),
     *                 @OA\Property(property="msg", type="string", description="返回结果描述", example="ok"),
     *                 @OA\Property(property="data",type="string", description="app端用这段字符串对支付宝发起授权请求", example="")
     *             ),
     *          )
     *     )
     * )
     */
    public function alipayAuth(){
        require_once Env::get('root_path') . 'extend/alipay/aop/AopClient.php';
        $m = new PM();
        $payment = $m->getPayment("alipays");
        $configs = Db::name('addons')->where(['dataFlag'=>1,'name'=>'Thirdlogin'])->value('config');
        $configs = json_decode($configs,true);
        $parentId = '';
        if(isset($configs['parentId']) && $configs['parentId']!=''){
            $parentId = $configs['parentId'];
        }
        trace('err'.$parentId,'error');
        $aop = new \AopClient;
        $arr = [];
        $arr['apiname']='com.alipay.account.auth';
        $arr['method']='alipay.open.auth.sdk.code.get';
        $arr['app_id']= $payment['appId'];
        $arr['app_name']='mc';
        $arr['biz_type']='openservice';
        $arr['pid'] = $parentId;// 商户parentId
        $arr['product_id']='APP_FAST_LOGIN';
        $arr['scope']='kuaijie';
        $arr['target_id'] = time();
        $arr['auth_type']='AUTHACCOUNT';
        $arr['sign_type']='RSA2';
        $aop->rsaPrivateKey = $payment['rsaPrivateKey'];
        $sign = $aop->rsaSign($arr,'RSA2');
        $sign = $aop->getSignContent($arr).'&sign='.urlencode($sign);
        return json_encode(WSTReturn('ok',1,$sign));
    }
    /**
     * @OA\Post(
     *     tags={"ThirdLogin"},
     *     path="/app/ThirdLogin/alipayLogin",
     *     summary="支付宝登录",
     *     description="支付宝登录",
	 *     @OA\Parameter(name="alipayId", in="query", @OA\Schema(type="string"), required=true, description="用户通过支付宝授权后返回的user_id"),
	 *     @OA\Parameter(name="auth_code", in="query", @OA\Schema(type="string"), required=true, description="用户通过支付宝授权后返回的auth_code"),
     *     @OA\Response(
     *      response="200",
     *      description="若该支付宝已绑定商城账号,则返回的json数据与登录返回的json数据一致。若该支付宝未绑定商城账号,则返回如下",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="status", type="string", description="状态值", example="2"),
     *                 @OA\Property(property="msg", type="string", description="返回结果描述", example="没找到与该user_id关联的账号"),
     *                 @OA\Property(property="data",type="object",
     *                     @OA\Property(property="nickname", type="integer", description="若存在支付宝名称则返回支付名称，否则返回'支付宝用户'", example=""),
     *                     @OA\Property(property="headimgurl", type="string", description="若存在支付宝头像则返回支付头像，否则返回'商城用户默认头像'", example=""),
     *                     @OA\Property(property="sysname", type="string", description="商城名称", example=""),
     *                     @OA\Property(property="unionId",type="string", description="unionId"),
     *                 )
     *             ),
     *          )
     *     )
     * )
     */
    public function alipayLogin(){
        /* 
          检测是否存在账户,若用户同意授权之后传过来auth_code，存在，正常返回tokenId,
          不存在，调用接口，获取名称，头像
        */
        $alipayId = input('alipayId');
        $auth_code = input('auth_code');// APP端用户同意授权登录之后获得的auth_code
        if(empty($auth_code || $alipayId)){
            return json_encode(WSTReturn(lang('third_login_args_error')));
        }
        // #第一步：检测账号是否存在,存在：返回tokenId（app_session表），不存在：返回用户基础信息以及user_di 让用户在APP页面完成注册
        $m = new M();
        $rs = $m->alipayIsExists($alipayId,input('loginRemark','android'));
        if(empty($rs)){// 没有相应记录，先获取access_token、再获取用户信息
                return $this->getAlipayUserInfo($auth_code);      
        }
        return json_encode(WSTReturn(lang('login_success'),1,$rs));
    }
    /**
    *  支付宝获取用户信息接口
    *  @param $code 用户在app页面授权后返回的auth_code
    *
    */
    private function getAlipayUserInfo($code){
        $obj = $this->getAlipayToken($code);
        if($obj['status']==-1)return json_encode(WSTReturn(lang('auth_fail')));
        // 取出access_token
        $obj = $obj['data'];
        $accessToken = $obj->access_token;
        require_once Env::get('root_path') . 'extend/alipay/aop/AopClient.php';
        require_once Env::get('root_path') . 'extend/alipay/aop/request/AlipayUserInfoShareRequest.php';
        $m = new PM();
        $payment = $m->getPayment("alipays");
        $aop = new \AopClient;
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $payment['appId'];
        $aop->rsaPrivateKey = $payment['rsaPrivateKey'];
        $aop->alipayrsaPublicKey = $payment["alipayrsaPublicKey"];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayUserInfoShareRequest;
        $result = $aop->execute ( $request , $accessToken ); 
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";


        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
             /*
                ###########如果用户未设置过支付宝头像则不包含 avatar 属性
                           未设置名称则 不包含 nick_name 属性
            */
            // 请求成功,返回user_id，支付宝名称(若存在)，支付宝头像(若存在)
            $userInfo = $result->$responseNode;
            $data = [];
            // 有设置支付宝名称,则取支付宝名称,否则返回`支付宝用户`
            $data['nickname'] = (isset($userInfo->nick_name))?$userInfo->nick_name:lang('ali_user');
            // 有设置支付宝头像,则取支付宝头像,否则返回`商城设置的用户默认头像`
            $data['headimgurl'] = (isset($userInfo->avatar))?$userInfo->avatar:WSTConf('CONF.userLogo');
            $data['sysname'] = WSTConf('CONF.mallName');
            $data['alipayId'] = $userInfo->user_id;
            return json_encode(WSTReturn(lang('unbind_ali'),2,$data));
        } else {
            return json_encode(WSTReturn(lang('auth_fail')));
        }
    }
    /**
    * 获取支付宝的access_token
    */
    private function getAlipayToken($code){
        require_once Env::get('root_path') . 'extend/alipay/aop/AopClient.php';
        require_once Env::get('root_path') . 'extend/alipay/aop/request/AlipaySystemOauthTokenRequest.php';
        $m = new PM();
        $payment = $m->getPayment("alipays");
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $payment['appId'];
        $aop->rsaPrivateKey = $payment['rsaPrivateKey'];
        $aop->alipayrsaPublicKey = $payment["alipayrsaPublicKey"];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipaySystemOauthTokenRequest ();
        $request->setGrantType("authorization_code");
        $request->setCode($code);
        $result = $aop->execute ( $request); 
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        if(isset($result->$responseNode)){
            $result = $result->$responseNode;
            // 返回获取到的对象
            return WSTReturn('ok',1,$result);
        }else{
            return WSTReturn(lang('get_access_token_fail'),-1);
        }
    }
    /**
     * 通过curl取得页面返回值
     * 需要打开配置中的php_curl
     * */
    private function curl($url){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);//允许请求的内容以文件流的形式返回
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);//禁用https
        curl_setopt($ch,CURLOPT_URL,$url);//设置请求的url地址
        $str = curl_exec($ch);//执行发送
        curl_close($ch);
        return $str;
    }
}
