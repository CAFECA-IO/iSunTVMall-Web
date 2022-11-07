<?php
namespace addons\thirdlogin\controller;

use think\addons\Controller;
use addons\thirdlogin\model\Thirdlogin as M;
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
class Thirdlogin extends Controller{
    public function __construct(){
        parent::__construct();
        $this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
    }
    
    /**
     * Facebook登录回调方法【home】
     */
    public function googleLogin(){
          
        include_once WST_ADDON_PATH."thirdlogin/sdk/Google/vendor/autoload.php";
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $clientID = $thirdLogins["appId_google"];
        $clientSecret = $thirdLogins["appKey_google"];
      
        $aUrl = Url("addon/thirdlogin-thirdlogin-googlecallback",'',true,true);
        $redirectUri = $aUrl;
        // create Client Request to access Google API
        $client = new \Google_Client(); 
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("email");
        $client->addScope("profile");

        $loginUrl = $client->createAuthUrl();

        return $this->redirect($loginUrl);
    }


    public function googleCallback(){
    
        include_once WST_ADDON_PATH."thirdlogin/sdk/Google/vendor/autoload.php";
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $clientID = $thirdLogins["appId_google"];
        $clientSecret = $thirdLogins["appKey_google"];

        $aUrl = Url("addon/thirdlogin-thirdlogin-googlecallback",'',true,true);
        $redirectUri = $aUrl;

        // create Client Request to access Google API
        $client = new \Google_Client();
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("email");
        $client->addScope("profile");
        
        if (isset($_GET['code'])) {
            $token = $client->authenticate($_GET['code']);
            $client->setAccessToken($token['access_token']);
            $q = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$token['access_token'];
            $json = file_get_contents($q);
            $user = json_decode($json,true);
            //var_dump($user);die;  //接收的数据
            if(!empty($user)){
                //已注册，则直接登录
                $openid = $user['id'];
                if($m->checkThirdIsReg("google",$openid)){
                    $obj["thirdOpenId"] = $openid;
                    $obj["thirdCode"] = "google";
                    $rd = $m->thirdLogin($obj);
                    if($rd["status"]==1){
                        return $this->redirect("home/index/index");
                    }else{
                        return $this->redirect("home/users/login");
                    }
                }else{
                    //未注册，则先注册
                    $obj["userName"] = $user["name"];
                    $obj["thirdOpenId"] = $openid;
                    $obj["thirdCode"] = "google";
                    $obj["userPhoto"] = $user["picture"];
                    session('binding_login',$obj);
                    $this->redirect("addon/thirdlogin-thirdlogin-homebind");
                }
            }else{
                return $this->redirect("home/users/login");
            }
            //处理
        }else{
            return $this->redirect("home/users/login");
        }

    } 


    /**
     * Google登录回调方法【home】
     */
    public function mobileGoogleLogin(){
          
        include_once WST_ADDON_PATH."thirdlogin/sdk/Google/vendor/autoload.php";
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $clientID = $thirdLogins["appId_google"];
        $clientSecret = $thirdLogins["appKey_google"];
      
        $aUrl = Url("addon/thirdlogin-thirdlogin-mobilegooglecallback",'',true,true);
        $redirectUri = $aUrl;
        // create Client Request to access Google API
        $client = new \Google_Client(); 
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("email");
        $client->addScope("profile");

        $loginUrl = $client->createAuthUrl();

        return $this->redirect($loginUrl);
    }
    /**
     * QQ登录回调方法【mobile】
     */
    public function mobileGoogleCallback(){
        include_once WST_ADDON_PATH."thirdlogin/sdk/Google/vendor/autoload.php";
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $clientID = $thirdLogins["appId_google"];
        $clientSecret = $thirdLogins["appKey_google"];

        $aUrl = Url("addon/thirdlogin-thirdlogin-mobilegooglecallback",'',true,true);
        $redirectUri = $aUrl;

        // create Client Request to access Google API
        $client = new \Google_Client();
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("email");
        $client->addScope("profile");

        if (isset($_GET['code'])) {
            $token = $client->authenticate($_GET['code']);
            $client->setAccessToken($token['access_token']);
            $q = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$token['access_token'];
            $json = file_get_contents($q);
            $user = json_decode($json,true);
            //var_dump($user);die;  //接收的数据
            if(!empty($user)){
                //已注册，则直接登录
                $openid = $user['id'];
                if($m->checkThirdIsReg("google",$openid)){
                    $obj["thirdOpenId"] = $openid;
                    $obj["thirdCode"] = "google";
                    $rd = $m->thirdLogin($obj);
                    if($rd["status"]==1){
                        $backUrl = session('WST_MO_WlADDRESS');
                        if($backUrl==""){
                            return $this->redirect("mobile/users/index");
                        }else{
                            header("Location: ".$backUrl);
                            exit; 
                        }
                    }else{
                        return $this->redirect("mobile/users/login");
                    }
                }else{
                    //未注册，则先注册
                    $obj["userName"] = $user["name"];
                    $obj["thirdOpenId"] = $openid;
                    $obj["thirdCode"] = "google";
                    $obj["userPhoto"] = $user["picture"];
                    session('binding_login',$obj);
                    $this->redirect("addon/thirdlogin-thirdlogin-mobilebind");
                }
            }else{
                return $this->redirect("mobile/users/login");
            }
            //处理
        }else{
            return $this->redirect("mobile/users/login");
        }
    }
    
    /**
     * Facebook登录回调方法【home】
     */
    public function fbLogin(){
          
        include_once WST_ADDON_PATH."thirdlogin/sdk/Facebook/autoload.php";
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $appId = $thirdLogins["appId_facebook"];
        $appKey = $thirdLogins["appKey_facebook"];
        //创建实例
        $fb = new \Facebook\Facebook([
            'app_id' => $appId, // 这里就是应用的APPID
            'app_secret' => $appKey, //这里填写应用的密钥
            'default_graph_version' => 'v2.10',//这里sdk的版本号
        ]);
        $helper = $fb->getRedirectLoginHelper();
        //需要获取的登录用户参数，有的参数需要权限
        $permissions = ['email']; // Optional permissions\
        $aUrl = Url("addon/thirdlogin-thirdlogin-fbcallback",'',true,true);
        //获取到的登录连接
        $loginUrl = $helper->getLoginUrl($aUrl, $permissions);
        return $this->redirect($loginUrl);
     
    }
    
    
    public function fbCallback(){
    
        include_once WST_ADDON_PATH."thirdlogin/sdk/Facebook/autoload.php";
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $appId = $thirdLogins["appId_facebook"];
        $appKey = $thirdLogins["appKey_facebook"];
        //创建实例
        $fb = new \Facebook\Facebook([
            'app_id' => $appId, // 这里就是应用的APPID
            'app_secret' => $appKey, //这里填写应用的密钥
            'default_graph_version' => 'v2.10',//这里sdk的版本号
        ]);
        var_dump($_GET['state']);
        $helper = $fb->getRedirectLoginHelper();
        if (isset($_GET['state'])) { 
            $helper->getPersistentDataHandler()->set('state', $_GET['state']); 
        }
        
        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $oAuth2Client = $fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $tokenMetadata->validateAppId($appId);
        $tokenMetadata->validateExpiration();
        if (! $accessToken->isLongLived()) {
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                exit;
            }
        }
        $_SESSION['fb_access_token'] = (string) $accessToken;
        try {
            $response = $fb->get('/me?fields=id,name', $_SESSION['fb_access_token']); 
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        //获取用户Facebook信息 id：关联Facebookid;name：姓名
        $user = $response->getGraphUser();
        if(!empty($user)){
            //已注册，则直接登录
            $openid = $user['id'];
            if($m->checkThirdIsReg("facebook",$openid)){
                $obj["thirdOpenId"] = $openid;
                $obj["thirdCode"] = "facebook";
                $rd = $m->thirdLogin($obj);
                if($rd["status"]==1){
                    return $this->redirect("home/index/index");
                }else{
                    return $this->redirect("home/users/login");
                }
            }else{
                //未注册，则先注册
                $obj["userName"] = $user["name"];
                $obj["thirdOpenId"] = $openid;
                $obj["thirdCode"] = "facebook";
                $obj["userPhoto"] = "https://graph.facebook.com/".$user['id']."/picture";
                session('binding_login',$obj);
                $this->redirect("addon/thirdlogin-thirdlogin-homebind");
            }
        }else{
            return $this->redirect("home/users/login");
        }

    }


    public function mobileFbLogin(){
          
        include_once WST_ADDON_PATH."thirdlogin/sdk/Facebook/autoload.php";
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $appId = $thirdLogins["appId_facebook"];
        $appKey = $thirdLogins["appKey_facebook"];
        //创建实例
        $fb = new \Facebook\Facebook([
            'app_id' => $appId, // 这里就是应用的APPID
            'app_secret' => $appKey, //这里填写应用的密钥
            'default_graph_version' => 'v2.10',//这里sdk的版本号
        ]);
        $helper = $fb->getRedirectLoginHelper();
        //需要获取的登录用户参数，有的参数需要权限
        $permissions = ['email']; // Optional permissions\
        $aUrl = Url("addon/thirdlogin-thirdlogin-mobilefbcallback",'',true,true);
        //获取到的登录连接
        $loginUrl = $helper->getLoginUrl($aUrl, $permissions);
        return $this->redirect($loginUrl);
     
    }
    
    
    public function mobileFbCallback(){
    
        include_once WST_ADDON_PATH."thirdlogin/sdk/Facebook/autoload.php";
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $appId = $thirdLogins["appId_facebook"];
        $appKey = $thirdLogins["appKey_facebook"];
        //创建实例
        $fb = new \Facebook\Facebook([
            'app_id' => $appId, // 这里就是应用的APPID
            'app_secret' => $appKey, //这里填写应用的密钥
            'default_graph_version' => 'v2.10',//这里sdk的版本号
        ]);
        var_dump($_GET['state']);
        $helper = $fb->getRedirectLoginHelper();
        if (isset($_GET['state'])) { 
            $helper->getPersistentDataHandler()->set('state', $_GET['state']); 
        }
        
        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $oAuth2Client = $fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $tokenMetadata->validateAppId($appId);
        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                exit;
            }
        }
        $_SESSION['fb_access_token'] = (string) $accessToken;
        try {
            $response = $fb->get('/me?fields=id,name', $_SESSION['fb_access_token']); 
             
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        //获取用户Facebook信息 id：关联Facebookid;name：姓名
        $user = $response->getGraphUser();
        if(!empty($user)){
            //已注册，则直接登录
            $openid = $user['id'];
            if($m->checkThirdIsReg("facebook",$openid)){
                $obj["thirdOpenId"] = $openid;
                $obj["thirdCode"] = "facebook";
                $rd = $m->thirdLogin($obj);
                if($rd["status"]==1){
                    $backUrl = session('WST_MO_WlADDRESS');
                    if($backUrl==""){
                        return $this->redirect("mobile/users/index");
                    }else{
                        header("Location: ".$backUrl);
                        exit; 
                    }
                }else{
                    return $this->redirect("mobile/users/login");
                }
            }else{
                //未注册，则先注册
                $obj["userName"] = $user["name"];
                $obj["thirdOpenId"] = $openid;
                $obj["thirdCode"] = "facebook";
                $obj["userPhoto"] = "https://graph.facebook.com/".$user['id']."/picture";
                session('binding_login',$obj);
                $this->redirect("addon/thirdlogin-thirdlogin-mobilebind");
            }
        }else{
            return $this->redirect("mobile/users/login");
        }

    }

    /**
     * QQ登录回调方法【home】
     */
    public function qqCallback(){
        header ( "Content-type: text/html; charset=utf-8" );
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        
        $appId = $thirdLogins["appId_qq"];
        $appKey = $thirdLogins["appKey_qq"];
        //回调接口，接受QQ服务器返回的信息的脚本
        $callbackUrl = Url("addon/thirdlogin-thirdlogin-qqcallback",'',true,true);
        //实例化qq登录类，传入上面三个参数
        $qq = new \addons\thirdlogin\model\Qq($appId,$appKey,$callbackUrl);
        //得到access_token验证值
        $accessToken = $qq->getToken();
        if(!$accessToken){
            return $this->redirect("home/users/login");
        }
        //得到用户的openid(登录用户的识别码)和Client_id和unionId
        $arr = $qq->getClientId($accessToken);
        if(isset($arr['client_id'])){
            $clientId = $arr['client_id'];
            $openId = $arr['openid'];
            $unionId = isset($arr['unionid'])?$arr['unionid']:$arr['openid'];
            //已注册，则直接登录
            if($m->checkThirdIsReg("qq",$unionId)){
                $obj["thirdOpenId"] = $unionId;
                $obj["thirdCode"] = "qq";
                $rd = $m->thirdLogin($obj);
                if($rd["status"]==1){
                    return $this->redirect("home/index/index");
                }else{
                    return $this->redirect("home/users/login");
                }
            }else{
                //未注册，则先注册
                $arr = $qq->getUserInfo($clientId,$openId,$accessToken);
                $obj["userName"] = $arr["nickname"];
                $obj["thirdOpenId"] = $unionId;
                $obj["thirdCode"] = "qq";
                $obj["userPhoto"] = $arr["figureurl_2"];
                session('binding_login',$obj);
                $this->redirect("addon/thirdlogin-thirdlogin-homebind");
            }
        }else{
            return $this->redirect("home/users/login");
        }
    }

    
    /**
     * 微信登录回调方法【home】
     */
    public function weixinCallback(){
        header ( "Content-type: text/html; charset=utf-8" );
        
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $appId = $thirdLogins["appId_weixin"];
        $appKey = $thirdLogins["appKey_weixin"];
    
        $wx = new \addons\thirdlogin\model\Weixin($appId,$appKey);
        //得到access_token验证值
        $accessToken = $wx->getToken();
         
        if(!$accessToken){
            return $this->redirect("home/users/login");
        }
        //得到用户的openid(登录用户的识别码)和Client_id
        $openId = $wx->getOpenId();
        if($openId!=""){
            $arr = $wx->getUserInfo($openId,$accessToken);
            if(isset($arr["unionid"]) && $arr["unionid"]!="") $openId = $arr["unionid"];
            //已注册，则直接登录
            if($m->checkThirdIsReg("weixin",$openId)){
                $obj["thirdOpenId"] = $openId;
                $obj["thirdCode"] = "weixin";
                $rd = $m->thirdLogin($obj);
                if($rd["status"]==1){
                    return $this->redirect("home/index/index");
                }else{
                    return $this->redirect("home/users/login");
                }
            }else{
                //未注册，则先注册
                $obj["userName"] = $arr["nickname"];
                $obj["thirdOpenId"] = $openId;
                $obj["thirdCode"] = "weixin";
                $obj["userPhoto"] = $arr["headimgurl"];
                if(isset($arr["unionid"]) && $arr["unionid"]!=""){
                    $obj["unionId"] = $arr["unionid"];
                }
                session('binding_login',$obj);
                $this->redirect("addon/thirdlogin-thirdlogin-homebind");
            }
        }else{
            return $this->redirect("home/users/login");
        }
    }
    
    /**
     * 微博登录回调方法【home】
     */
    public function weiboCallback(){
        header ( "Content-type: text/html; charset=utf-8" );
         
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $appId = $thirdLogins["appId_weibo"];
        $appKey = $thirdLogins["appKey_weibo"];
        $callbackUrl = Url("addon/thirdlogin-thirdlogin-weibocallback",'',true,true);
        
        $wb = new \addons\thirdlogin\model\WeiBo($appId,$appKey,$callbackUrl);
        //得到access_token验证值
        $accessToken = $wb->getToken();
    
        if(!$accessToken){
            return $this->redirect("home/users/login");
        }
        //得到用户的openid(登录用户的识别码)和Client_id
        $arr = $wb->getClientId($accessToken);
        if(isset($arr['uid'])){
            $openId = $arr['uid'];
            //已注册，则直接登录
            if($m->checkThirdIsReg("weibo",$openId)){
                $obj["thirdOpenId"] = $openId;
                $obj["thirdCode"] = "weibo";
                $rd = $m->thirdLogin($obj);
                if($rd["status"]==1){
                    return $this->redirect("home/index/index");
                }else{
                    return $this->redirect("home/users/login");
                }
            }else{
                //未注册，则先注册
                $arr = $wb->getUserInfo($openId,$accessToken);
                $obj["userName"] = $arr["screen_name"];
                $obj["thirdOpenId"] = $openId;
                $obj["thirdCode"] = "weibo";
                $obj["userPhoto"] = $arr["profile_image_url"];
                session('binding_login',$obj);
                $this->redirect("addon/thirdlogin-thirdlogin-homebind");
            }
        }else{
            return $this->redirect("home/users/login");
        }
    }
    
    /**
     * 跳去绑定界面
     */
    public function homeBind(){
        //如果已经登录了则直接跳去后台
        $user = session('WST_USER');
        if(!empty($user) && $user['userId']!=''){
            return $this->redirect("home/users/index");
        }
        if(isset($_COOKIE["loginName"])){
            $this->assign('loginName',$_COOKIE["loginName"]);
        }else{
            $this->assign('loginName','');
        }
        $info = session('binding_login');
        $this->assign('info',$info);
        return $this->fetch('/home/index/login_bind');
    }
    
    
    /**
     * QQ登录回调方法【mobile】
     */
    public function mobileQqCallback(){
        header ( "Content-type: text/html; charset=utf-8" );
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
         
        $appId = $thirdLogins["appId_qq"];
        $appKey = $thirdLogins["appKey_qq"];
        //回调接口，接受QQ服务器返回的信息的脚本
        $callbackUrl = Url("addon/thirdlogin-thirdlogin-mobileqqcallback",'',true,true);
        //实例化qq登录类，传入上面三个参数
        $qq = new \addons\thirdlogin\model\Qq($appId,$appKey,$callbackUrl);
        //得到access_token验证值
        $accessToken = $qq->getToken();
        if(!$accessToken){
            return $this->redirect("mobile/users/login");
        }
        //得到用户的openid(登录用户的识别码)和Client_id
        $arr = $qq->getClientId($accessToken);
        if(isset($arr['client_id'])){
            $clientId = $arr['client_id'];
            $openId = $arr['openid'];
            $unionId = isset($arr['unionid'])?$arr['unionid']:$arr['openid'];
            //已注册，则直接登录
            if($m->checkThirdIsReg("qq",$unionId)){
                $obj["thirdOpenId"] = $unionId;
                $obj["thirdCode"] = "qq";
                $rd = $m->thirdLogin($obj);
                if($rd["status"]==1){
                    $backUrl = session('WST_MO_WlADDRESS');
                    if($backUrl==""){
                        return $this->redirect("mobile/users/index");
                    }else{
                        header("Location: ".$backUrl);
                        exit; 
                    }
                }else{
                    return $this->redirect("mobile/users/login");
                }
            }else{
                //未注册，则先注册
                $arr = $qq->getUserInfo($clientId,$openId,$accessToken);
                $obj["userName"] = $arr["nickname"];
                $obj["thirdOpenId"] = $unionId;
                $obj["thirdCode"] = "qq";
                $obj["userPhoto"] = $arr["figureurl_2"];
                session('binding_login',$obj);
                $this->redirect("addon/thirdlogin-thirdlogin-mobilebind");
            }
        }else{
            return $this->redirect("mobile/users/login");
        }
        
    }
    
    
    /**
     * 微博登录回调方法【mobile】
     */
    public function mobileWeiboCallback(){
        header ( "Content-type: text/html; charset=utf-8" );
    
        $m = new M();
        $thirdLogins = $m->getThirdLogins();
        $appId = $thirdLogins["appId_weibo"];
        $appKey = $thirdLogins["appKey_weibo"];
        $callbackUrl = Url("addon/thirdlogin-thirdlogin-mobileweibocallback",'',true,true);
         
        $wb = new \addons\thirdlogin\model\WeiBo($appId,$appKey,$callbackUrl);
        //得到access_token验证值
        $accessToken = $wb->getToken();
    
        if(!$accessToken){
            return $this->redirect("mobile/users/login");
        }
        //得到用户的openid(登录用户的识别码)和Client_id
        $arr = $wb->getClientId($accessToken);
        if(isset($arr['uid'])){
            $openId = $arr['uid'];
            //已注册，则直接登录
            if($m->checkThirdIsReg("weibo",$openId)){
                $obj["thirdOpenId"] = $openId;
                $obj["thirdCode"] = "weibo";
                $rd = $m->thirdLogin($obj);
                if($rd["status"]==1){
                    $backUrl = session('WST_MO_WlADDRESS');
                    if($backUrl==""){
                        return $this->redirect("mobile/users/index");
                    }else{
                        header("Location: ".$backUrl);
                        exit; 
                    }
                }else{
                    return $this->redirect("mobile/users/login");
                }
            }else{
                //未注册，则先注册
                $arr = $wb->getUserInfo($openId,$accessToken);
                $obj["userName"] = $arr["screen_name"];
                $obj["thirdOpenId"] = $openId;
                $obj["thirdCode"] = "weibo";
                $obj["userPhoto"] = $arr["profile_image_url"];
                session('binding_login',$obj);
                $this->redirect("addon/thirdlogin-thirdlogin-mobilebind");
            }
        }else{
            return $this->redirect("mobile/users/login");
        }
    }
    /**
     * 跳去绑定界面
     */
    public function mobileBind(){
        //如果已经登录了则直接跳去后台
        $user = session('WST_USER');
        if(!empty($user) && $user['userId']!=''){
            return $this->redirect("mobile/users/index");
        }
        if(isset($_COOKIE["loginName"])){
            $this->assign('loginName',$_COOKIE["loginName"]);
        }else{
            $this->assign('loginName','');
        }
        $info = session('binding_login');
        $this->assign('info',$info);
        return $this->fetch('/mobile/index/login_bind');
    }
    /**
     * 跳去登录绑定界面
     */
    public function mobileBindLogin(){
        $type = (int)input("type");
        $type = ($type==1)?1:2;
        //如果已经登录了则直接跳去后台
        $user = session('WST_USER');
        if(!empty($user) && $user['userId']!=''){
            return $this->redirect("mobile/users/index");
        }
        if(isset($_COOKIE["loginName"])){
            $this->assign('loginName',$_COOKIE["loginName"]);
        }else{
            $this->assign('loginName','');
        }
        $info = session('binding_login');
        $this->assign('info',$info);
        $this->assign('type',$type);
        return $this->fetch('/mobile/index/m_login');
    }
    /**
     * 跳去注册绑定界面
     */
    public function mobileBindReg(){
        $type = (int)input("type");
        $type = ($type==1)?1:2;
        //如果已经登录了则直接跳去后台
        $user = session('WST_USER');
        if(!empty($user) && $user['userId']!=''){
            return $this->redirect("mobile/users/index");
        }
        if(isset($_COOKIE["loginName"])){
            $this->assign('loginName',$_COOKIE["loginName"]);
        }else{
            $this->assign('loginName','');
        }
        $info = session('binding_login');
        $this->assign('info',$info);
        $this->assign('type',$type);
        return $this->fetch('/mobile/index/m_reg');
    }
}