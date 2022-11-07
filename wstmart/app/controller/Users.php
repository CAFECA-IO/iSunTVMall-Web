<?php
namespace wstmart\app\controller;
use wstmart\app\model\Users as M;
use wstmart\common\model\LogSms;
use wstmart\common\model\Users as MUsers;
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
 * 用户控制器
 */
class Users extends Base{
    // 前置方法执行列表
    protected $beforeActionList = [
          'checkAuth' =>  ['except'=>'getverify,index,protocol,login,checklogin,loginbyphone,register,registerbyaccount,getphonecode,getphonecode2,forgetpasst,findpass,getfindphone,resetpass,getfindemail,getloginandregistersetting']// 访问这些except下的方法不需要执行前置操作
    ];
    /**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/checklogin",
     *     summary="验证是否登录",
     *     description="验证是否登录",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
    public function checklogin(){
        if($this->checkAuth())return json_encode(WSTReturn(lang('auth_passed'),1));
    }
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/editloginPwd",
     *     summary="修改登录密码",
     *     description="修改登录密码",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="oldPass", in="query", @OA\Schema(type="string"), required=true, description="原始密码"),
     *     @OA\Parameter(name="newPass", in="query", @OA\Schema(type="string"), required=true, description="新密码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
    public function editloginPwd(){
		$m = new M();
		$userId = model('index')->getUserId();
		return json_encode($m->editPass($userId));
	}

	public function getLoginAndRegisterSetting(){
        $type = input("type");
        if($type=="login"){
            return json_encode(WSTReturn('success',1,WSTConf('CONF.mobileLoginType')));
        }else{
            return json_encode(WSTReturn('success',1,WSTConf('CONF.mobileRegisterType')));
        }
    }

	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/protocol",
     *     summary="用户注册协议",
     *     description="用户注册协议,由webview页面调用",
     *     @OA\Response(
     *      response="200",
     *      description="返回页面",
     *     )
     * )
     */
    public function protocol(){
        $rs = model('common/articles')->getById(300);
        return $this->fetch('protocol', ['data'=>$rs]);
    }
	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/logout",
     *     summary="用户注销",
     *     description="用户注销",
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
    public function logout(){
        $m = new M();
        return json_encode($m->logout());
    }
	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/index",
     *     summary="会员中心",
     *     description="会员中心",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="userId", type="integer", description="用户id", example="1"),
     *                         @OA\Property(property="loginName", type="string", description="登录名", example=""),
     *                         @OA\Property(property="userType", type="integer", description="用户类型 1:商家 0:普通用户", example=""),
     *                         @OA\Property(property="userName", type="integer", description="用户名", example=""),
     *                         @OA\Property(property="userPhoto", type="string", description="用户头像", example=""),
     *                         @OA\Property(property="userPhone", type="string", description="用户手机号", example=""),
     *                         @OA\Property(property="userScore", type="string", description="用户积分", example=""),
     *                         @OA\Property(property="userTotalScore", type="string", description="用户历史总积分", example=""),
     *                         @OA\Property(property="userMoney", type="number", description="用户余额", example=""),
     *                         @OA\Property(property="lockMoney", type="number", description="冻结金额", example=""),
     *                         @OA\Property(property="rechargeMoney", type="number", description="充值获得的金额", example=""),
     *                         @OA\Property(property="isInform", type="string", description="是否禁止举报商品，1:是 0:否", example=""),
	 *                         @OA\Property(property="ranks", type="object", description="用户等级相关",
     *                             @OA\Property(property="rankName", type="string", description="等级名称", example=""),
     *                             @OA\Property(property="rankId", type="string", description="等级id", example=""),
     *                             @OA\Property(property="userrankImg", type="string", description="等级图标", example=""),
	 * 						   ),
	 *                         @OA\Property(property="datam", type="object", description="用户等级相关",
     *                             @OA\Property(property="message", type="object",
	 * 							       @OA\Property(property="num", type="integer", description="未读商城消息数", example="")
	 * 							   ),
     *                             @OA\Property(property="order", type="object",
	 * 							       @OA\Property(property="waitPay", type="integer", description="待支付订单数", example=""),
	 * 							       @OA\Property(property="waitSend", type="integer", description="待发货订单数", example=""),
	 * 							       @OA\Property(property="waitReceive", type="integer", description="待收货订单数", example=""),
	 * 							       @OA\Property(property="waitAppraise", type="integer", description="待评价订单数", example=""),
	 * 							   ),
	 * 						   ),
	 * 						   @OA\Property(property="isOpenSign", type="boolean", description="是否开启签到获得积分(并且配置正确)", default=true),
	 * 						   @OA\Property(property="isSign", type="boolean", description="今日是否已签到", default=true),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
	public function index(){
		$m = new M();
		$userId = (int)$m->getUserId();
		$user = $m->getById();
		if($user['userName']=='')$user['userName']=$user['loginName'];
		if($user['userPhoto']==null || $user['userPhoto']=='')$user['userPhoto']='';
		//商城未读消息的数量 及 各订单状态数量
		$user['datam'] = model('index')->getSysMsg('msg','order');
		// 是否开启签到获得积分
		$signScore = explode(",",WSTConf('CONF.signScore'));// 签到积分配置
    	$user['isOpenSign'] = (WSTConf('CONF.signScoreSwitch')==1 && $signScore[0]>0);//是否开启积分
    	$user['isOpenShopApply'] = WSTConf('CONF.isOpenShopApply');//是否开启商家入驻
    	// 是否已签到
    	$m = model('common/UserScores');
    	$user['isSign'] = $m->isSign($userId);
        $conf = model('index')->getAddonConfig('Distribut');
        if(WSTConf('WST_ADDONS.distribut')!=''){
            if(($user["isBuyer"]!=1 && $conf['distributDeal']==2)){
                $user["canShare"] = 0;
            }else{
                $user["canShare"] = 1;
            }
        }
		echo(json_encode(WSTReturn('success',1,$user)));die;
	}
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/login",
     *     summary="登录接口",
     *     description="登录接口，通过账号密码登录",
	 * 	   @OA\Parameter(name="loginKey", in="query", @OA\Schema(type="string"), required=true, description="64位加密传过来，密匙->例如:base64(base64(账号)._.base64(密码))"),
	 * 	   @OA\Parameter(name="loginRemark", in="query", @OA\Schema(type="string"), required=true, description="传递 android 或 ios"),
	 * 	   @OA\Parameter(name="verifyCode", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
	 * 	   @OA\Parameter(name="unionId", in="query", @OA\Schema(type="string"), required=false, description="使用微信登录时传递 微信unionId"),
	 * 	   @OA\Parameter(name="qqOpenId", in="query", @OA\Schema(type="string"), required=false, description="使用qq登录时传递 qqOpenId"),
	 * 	   @OA\Parameter(name="alipayId", in="query", @OA\Schema(type="string"), required=false, description="使用支付宝登录时传递 alipayId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="userId", type="integer", description="用户id", example="1"),
     *                         @OA\Property(property="loginName", type="string", description="登录名", example=""),
     *                         @OA\Property(property="userType", type="integer", description="用户类型 1:商家 0:普通用户", example=""),
     *                         @OA\Property(property="userName", type="integer", description="用户名", example=""),
     *                         @OA\Property(property="userPhoto", type="string", description="用户头像", example=""),
     *                         @OA\Property(property="userPhone", type="string", description="用户手机号", example=""),
     *                         @OA\Property(property="tokenId", type="string", description="唯一凭证", example=""),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
	public function login(){
        if(strpos(WSTConf('CONF.mobileLoginType'),'1')===false)return json_encode(WSTReturn(lang('illegal_login'),-1));
		$m = new M();
		return json_encode($m->login());
	}
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/loginByPhone",
     *     summary="登录接口",
     *     description="登录接口，通过手机号登录",
	 * 	   @OA\Parameter(name="loginName", in="query", @OA\Schema(type="string"), required=true, description="手机号"),
	 * 	   @OA\Parameter(name="loginRemark", in="query", @OA\Schema(type="string"), required=true, description="传递 android 或 ios"),
	 * 	   @OA\Parameter(name="mobileCode", in="query", @OA\Schema(type="string"), required=true, description="短信验证码"),
	 * 	   @OA\Parameter(name="unionId", in="query", @OA\Schema(type="string"), required=false, description="使用微信登录时传递 微信unionId"),
	 * 	   @OA\Parameter(name="qqOpenId", in="query", @OA\Schema(type="string"), required=false, description="使用qq登录时传递 qqOpenId"),
	 * 	   @OA\Parameter(name="alipayId", in="query", @OA\Schema(type="string"), required=false, description="使用支付宝登录时传递 alipayId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="userId", type="integer", description="用户id", example="1"),
     *                         @OA\Property(property="loginName", type="string", description="登录名", example=""),
     *                         @OA\Property(property="userType", type="integer", description="用户类型 1:商家 0:普通用户", example=""),
     *                         @OA\Property(property="userName", type="integer", description="用户名", example=""),
     *                         @OA\Property(property="userPhoto", type="string", description="用户头像", example=""),
     *                         @OA\Property(property="userPhone", type="string", description="用户手机号", example=""),
     *                         @OA\Property(property="tokenId", type="string", description="唯一凭证", example=""),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
	public function loginByPhone(){
        if(strpos(WSTConf('CONF.mobileLoginType'),'2')===false)return json_encode(WSTReturn(lang('illegal_login'),-1));
        $m = new M();
        $rs =  $m->loginByPhone();
        return json_encode($rs);
    }
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/register",
     *     summary="会员注册接口",
     *     description="会员注册接口",
	 * 	   @OA\Parameter(name="registerKey", in="query", @OA\Schema(type="string"), required=true, description="64位加密传过来，密匙->例如:base64(base64(账号)._.base64(密码))"),
	 * 	   @OA\Parameter(name="loginRemark", in="query", @OA\Schema(type="string"), required=true, description="传递 android 或 ios"),
	 * 	   @OA\Parameter(name="verifyCode", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
	 * 	   @OA\Parameter(name="mobileCode", in="query", @OA\Schema(type="string"), required=true, description="短信验证码"),
	 * 	   @OA\Parameter(name="unionId", in="query", @OA\Schema(type="string"), required=false, description="使用微信登录时传递 微信unionId"),
	 * 	   @OA\Parameter(name="qqOpenId", in="query", @OA\Schema(type="string"), required=false, description="使用qq登录时传递 qqOpenId"),
	 * 	   @OA\Parameter(name="alipayId", in="query", @OA\Schema(type="string"), required=false, description="使用支付宝登录时传递 alipayId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="userId", type="integer", description="用户id", example="1"),
     *                         @OA\Property(property="loginName", type="string", description="登录名", example=""),
     *                         @OA\Property(property="userType", type="integer", description="用户类型 1:商家 0:普通用户", example=""),
     *                         @OA\Property(property="userName", type="integer", description="用户名", example=""),
     *                         @OA\Property(property="userPhoto", type="string", description="用户头像", example=""),
     *                         @OA\Property(property="userPhone", type="string", description="用户手机号", example=""),
     *                         @OA\Property(property="tokenId", type="string", description="唯一凭证", example=""),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function register(){
        if(strpos(WSTConf('CONF.mobileRegisterType'),'2')===false)return json_encode(WSTReturn(lang('illegal_regist'),-1));
    	$m = new M();
    	return json_encode($m->register());
    }
    /**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/registerByAccount",
     *     summary="会员注册（账号注册）",
     *     description="会员注册（账号注册）",
	 * 	   @OA\Parameter(name="registerKey", in="query", @OA\Schema(type="string"), required=true, description="64位加密传过来，密匙->例如:base64(base64(账号)._.base64(密码))"),
	 * 	   @OA\Parameter(name="loginRemark", in="query", @OA\Schema(type="string"), required=true, description="传递 android 或 ios"),
	 * 	   @OA\Parameter(name="verifyCode", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
	 * 	   @OA\Parameter(name="mobileCode", in="query", @OA\Schema(type="string"), required=true, description="短信验证码"),
	 * 	   @OA\Parameter(name="unionId", in="query", @OA\Schema(type="string"), required=false, description="使用微信登录时传递 微信unionId"),
	 * 	   @OA\Parameter(name="qqOpenId", in="query", @OA\Schema(type="string"), required=false, description="使用qq登录时传递 qqOpenId"),
	 * 	   @OA\Parameter(name="alipayId", in="query", @OA\Schema(type="string"), required=false, description="使用支付宝登录时传递 alipayId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="userId", type="integer", description="用户id", example="1"),
     *                         @OA\Property(property="loginName", type="string", description="登录名", example=""),
     *                         @OA\Property(property="userType", type="integer", description="用户类型 1:商家 0:普通用户", example=""),
     *                         @OA\Property(property="userName", type="integer", description="用户名", example=""),
     *                         @OA\Property(property="userPhoto", type="string", description="用户头像", example=""),
     *                         @OA\Property(property="userPhone", type="string", description="用户手机号", example=""),
     *                         @OA\Property(property="tokenId", type="string", description="唯一凭证", example=""),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function registerByAccount(){
        if(strpos(WSTConf('CONF.mobileRegisterType'),'1')===false)return json_encode(WSTReturn(lang('illegal_regist'),-1));
        $m = new M();
        return json_encode($m->registerByAccount());
    }
    /**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/getphonecode",
     *     summary="注册获取验证码",
     *     description="注册获取验证码",
	 * 	   @OA\Parameter(name="userPhone", in="query", @OA\Schema(type="string"), required=true, description="手机号"),
	 * 	   @OA\Parameter(name="smsVerfy", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
    public function getphonecode(){
    	$userPhone = input("post.userPhone");
        $areaCode = input("post.areaCode");
    	$rs = array();
    	if(!WSTIsPhone($userPhone)){
    		return json_encode(WSTReturn(lang('illegal_phone')));
    		exit();
    	}
    	$musers = new MUsers();
    	$rs = $musers->checkUserPhone($userPhone,0);
    	if($rs["status"]!=1){
    		return json_encode(WSTReturn(lang('phone_exists')));
    		exit();
    	}
    	$phoneVerify = rand(100000,999999);
    	$tpl = WSTMsgTemplates('PHONE_USER_REGISTER_VERFIY');
    	if( $tpl['tplContent']!='' && $tpl['status']=='1'){
    		$params = ['tpl'=>$tpl,'params'=>['MALL_NAME'=>WSTConf("CONF.mallName"),'VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
    		$m = new LogSms();
    		$rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'getPhoneVerifyCode',$phoneVerify);
    	}

    	if($rv['status']==1){
    		session('VerifyCode_userPhone',$userPhone);
    		session('VerifyCode_userPhone_Verify',$phoneVerify);
    		session('VerifyCode_userPhone_Time',time());
    	}
    	return json_encode($rv);
    }
	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/getphonecode2",
     *     summary="获取验证码(手机登录)",
     *     description="获取验证码(手机登录)",
	 * 	   @OA\Parameter(name="userPhone", in="query", @OA\Schema(type="string"), required=true, description="手机号"),
	 * 	   @OA\Parameter(name="smsVerfy", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
    public function getphonecode2(){
        $userPhone = input("post.userPhone");
        $rs = array();
        if(!WSTIsPhone($userPhone)){
            return json_encode(WSTReturn(lang('illegal_phone')));
            exit();
        }
        $areaCode = input("post.areaCode");
        $phoneVerify = rand(100000,999999);
        $tpl = WSTMsgTemplates('PHONE_COMMON_VERFIY');
        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
            $params = ['tpl'=>$tpl,'params'=>['VERFIY_CODE'=>$phoneVerify]];
            $m = new LogSms();
            $rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'getPhoneVerifyCode',$phoneVerify);
        }

        if($rv['status']==1){
            session('VerifyCode_userPhone2',$userPhone);
            session('VerifyCode_userPhone_Verify2',$phoneVerify);
            session('VerifyCode_userPhone_Time2',time());
        }
        return json_encode($rv);
    }
    /************************************************   ********************************************************/
	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/getById",
     *     summary="获取用户信息",
     *     description="获取用户信息",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="userId", type="integer", description="用户id", example="1"),
     *                         @OA\Property(property="loginName", type="string", description="登录名", example=""),
     *                         @OA\Property(property="userType", type="integer", description="用户类型 1:商家 0:普通用户", example=""),
     *                         @OA\Property(property="userName", type="integer", description="用户名", example=""),
     *                         @OA\Property(property="userPhoto", type="string", description="用户头像", example=""),
     *                         @OA\Property(property="userPhone", type="string", description="用户手机号", example=""),
     *                         @OA\Property(property="userScore", type="string", description="用户积分", example=""),
     *                         @OA\Property(property="userTotalScore", type="string", description="用户历史总积分", example=""),
     *                         @OA\Property(property="userMoney", type="number", description="用户余额", example=""),
     *                         @OA\Property(property="lockMoney", type="number", description="冻结金额", example=""),
     *                         @OA\Property(property="rechargeMoney", type="number", description="充值获得的金额", example=""),
     *                         @OA\Property(property="isInform", type="string", description="是否禁止举报商品，1:是 0:否", example=""),
	 *                         @OA\Property(property="ranks", type="object", description="用户等级相关",
     *                             @OA\Property(property="rankName", type="string", description="等级名称", example=""),
     *                             @OA\Property(property="rankId", type="string", description="等级id", example=""),
     *                             @OA\Property(property="userrankImg", type="string", description="等级图标", example=""),
	 * 						   ),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function getById(){
        $m = new M();
        $rs = $m->getById();
        return json_encode(WSTReturn(lang('success_msg'),1,$rs));
    }
	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/getUserMoneyAndScore",
     *     summary="获取用户余额与积分",
     *     description="获取用户余额与积分",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="userId", type="integer", description="用户id", example="1"),
     *                         @OA\Property(property="userScore", type="string", description="用户积分", example=""),
     *                         @OA\Property(property="userMoney", type="number", description="用户余额", example=""),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function getUserMoneyAndScore(){
        $m = new M();
        $userId = (int)$m->getUserId();
        $rs = model('common/users')->getFieldsById($userId,["userScore","userMoney","userId"]);
        return json_encode(WSTReturn('success',1,$rs));
    }
	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/edit",
     *     summary="修改个人信息",
     *     description="修改个人信息",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 * 	   @OA\Parameter(name="userName", in="query", @OA\Schema(type="string"), required=true, description="昵称"),
	 * 	   @OA\Parameter(name="userSex", in="query", @OA\Schema(type="string"), required=true, description="性别，0:保密 1：男 2：女"),
	 * 	   @OA\Parameter(name="userPhoto", in="query", @OA\Schema(type="string"), required=true, description="用户头像"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="userId", type="integer", description="用户id", example="1"),
     *                         @OA\Property(property="userScore", type="string", description="用户积分", example=""),
     *                         @OA\Property(property="userMoney", type="number", description="用户余额", example=""),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
	public function edit(){
    	$m = new M();
    	return json_encode($m->edit());
	}
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/editpayPwd",
     *     summary="修改支付密码",
     *     description="修改支付密码",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="oldPass", in="query", @OA\Schema(type="string"), required=true, description="原始密码"),
     *     @OA\Parameter(name="newPass", in="query", @OA\Schema(type="string"), required=true, description="新密码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function editpayPwd(){
		$m = new M();
		return json_encode($m->editPayPass());
	}



	/***********************************  修改\绑定 手机号码 **************************************/

	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/sendCodeTie",
     *     summary="绑定手机：发送短信验证码",
     *     description="绑定手机：发送短信验证码",
	 * 	   @OA\Parameter(name="userPhone", in="query", @OA\Schema(type="string"), required=true, description="手机号"),
	 * 	   @OA\Parameter(name="smsVerfy", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function sendCodeTie(){
		$userPhone = input("post.userPhone");
        if(!WSTIsPhone($userPhone)){
            return json_encode(WSTReturn(lang('illegal_phone'),-1));
            exit();
        }
        $rs = array();
        $m = new M();
        // 获取用户id
        $userId = (int)$m->getUserId();

        $rs = WSTCheckLoginKey($userPhone, $userId);
        if($rs["status"]!=1){
            return json_encode(WSTReturn(lang('phone_exists'),-1));
            exit();
        }
        $data = $m->getById();
        $phoneVerify = rand(100000,999999);
        $rv = ['status'=>-1,'msg'=>lang('sms_send_fail')];
        $tpl = WSTMsgTemplates('PHONE_BIND');
        $areaCode = $data['areaCode'];
        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
            $params = ['tpl'=>$tpl,'params'=>['LOGIN_NAME'=>$data['loginName'],'VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
            $m = new LogSms();
            $rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'sendCodeTie',$phoneVerify);
        }
        if($rv['status']==1){
            $USER = [];
            $USER['userPhone'] = $userPhone;
            $USER['phoneVerify'] = $phoneVerify;
            session('Verify_info',$USER);
            session('Verify_userPhone_Time',time());
            return json_encode(WSTReturn(lang('sms_send_success'),1));
        }
        return json_encode($rv);
	}
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/phoneEdit",
     *     summary="绑定手机:验证校验码是否正确",
     *     description="绑定手机:验证校验码是否正确",
	 * 	   @OA\Parameter(name="phoneCode", in="query", @OA\Schema(type="string"), required=true, description="短信验证码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function phoneEdit(){
		$phoneVerify = input("post.phoneCode");
        $timeVerify = session('Verify_userPhone_Time');
        if(!session('Verify_info.phoneVerify') || time()>floatval($timeVerify)+10*60){
            return WSTReturn(lang('verify_code_expired'));
            exit();
        }
        if($phoneVerify==session('Verify_info.phoneVerify')){
            $m = new M();
            $rs = $m->editPhone(session('Verify_info.userPhone'));
            return json_encode($rs);
        }
        return json_encode(WSTReturn(lang('verify_code_nomatch'),-1));
	}

	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/sendCodeEdit",
     *     summary="修改手机：发送短信验证码",
     *     description="修改手机：发送短信验证码",
	 * 	   @OA\Parameter(name="userPhone", in="query", @OA\Schema(type="string"), required=true, description="手机号"),
	 * 	   @OA\Parameter(name="smsVerfy", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function sendCodeEdit(){
    	$m = new M();
        $data = $m->getById();
        $userPhone = $data['userPhone'];
        $phoneVerify = rand(100000,999999);
        $rv = ['status'=>-1,'msg'=>lang('sms_send_fail')];
        $tpl = WSTMsgTemplates('PHONE_EDIT');
        $areaCode = $data['areaCode'];
        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
            $params = ['tpl'=>$tpl,'params'=>['LOGIN_NAME'=>$data['loginName'],'VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
            $m = new LogSms();
            $rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'getPhoneVerifyt',$phoneVerify);
        }
        if($rv['status']==1){
            $USER = [];
            $USER['userPhone'] = $userPhone;
            $USER['phoneVerify'] = $phoneVerify;
            session('Verify_info2',$USER);
            session('Verify_userPhone_Time2',time());
            return json_encode(WSTReturn(lang('sms_send_success'),1));
        }
        return json_encode($rv);
	}
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/phoneEdito",
     *     summary="修改手机：检查短信验证码是否正确",
     *     description="修改手机：检查短信验证码是否正确",
	 * 	   @OA\Parameter(name="phoneCode", in="query", @OA\Schema(type="string"), required=true, description="短信验证码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function phoneEdito(){
		$phoneVerify = input("post.phoneCode");
        $timeVerify = session('Verify_userPhone_Time2');
        if(!session('Verify_info2.phoneVerify') || time()>floatval($timeVerify)+10*60){
            return json_encode(WSTReturn(lang('verify_code_expired')));
            exit();
        }
        if($phoneVerify==session('Verify_info2.phoneVerify')){
            session('Edit_userPhone_Time',time());
            return json_encode(WSTReturn(lang('verify_success'),1));
        }
        return json_encode(WSTReturn(lang('verify_code_nomatch'),-1));
	}

	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/security",
     *     summary="账户安全",
     *     description="账户安全",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="userId", type="integer", description="用户id", example="1"),
     *                         @OA\Property(property="loginName", type="string", description="登录名", example=""),
     *                         @OA\Property(property="userType", type="integer", description="用户类型 1:商家 0:普通用户", example=""),
     *                         @OA\Property(property="userName", type="integer", description="用户名", example=""),
     *                         @OA\Property(property="userPhoto", type="string", description="用户头像", example=""),
     *                         @OA\Property(property="userPhone", type="string", description="是否有绑定手机号，1:有 0:没有", example=""),
     *                         @OA\Property(property="payPwd", type="string", description="是否有设置支付密码，1:有 0:没有", example=""),
     *                         @OA\Property(property="userScore", type="string", description="用户积分", example=""),
     *                         @OA\Property(property="userTotalScore", type="string", description="用户历史总积分", example=""),
     *                         @OA\Property(property="userMoney", type="number", description="用户余额", example=""),
     *                         @OA\Property(property="lockMoney", type="number", description="冻结金额", example=""),
     *                         @OA\Property(property="rechargeMoney", type="number", description="充值获得的金额", example=""),
     *                         @OA\Property(property="isInform", type="string", description="是否禁止举报商品，1:是 0:否", example=""),
	 *                         @OA\Property(property="ranks", type="object", description="用户等级相关",
     *                             @OA\Property(property="rankName", type="string", description="等级名称", example=""),
     *                             @OA\Property(property="rankId", type="string", description="等级id", example=""),
     *                             @OA\Property(property="userrankImg", type="string", description="等级图标", example=""),
	 * 						   ),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
	public function security(){
		$m = new M();
		$user = $m->getById();
		$payPwd = $user['payPwd'];
		$userPhone = $user['userPhone'];
		$user['payPwd'] = empty($payPwd)?0:1;
		$user['userPhone'] = WSTStrReplace($user['userPhone'],'*',3);
		$user['phoneType'] = empty($userPhone)?0:1;
        $user['areaCode'] = isset($user['areaCode'])?$user['areaCode']:"";
		session('Edit_userPhone_Time', null);
		echo(json_encode(WSTReturn('success',1,$user)));die;
	}

	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/forgetPasst",
     *     summary="找回登录密码：检查该用户的手机和邮箱",
     *     description="找回登录密码：检查该用户的手机和邮箱",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="loginName", type="integer", description="登录名", example=""),
     *                         @OA\Property(property="userEmail", type="integer", description="邮箱", example=""),
     *                         @OA\Property(property="userPhone", type="integer", description="手机号", example=""),
     *
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
	public function forgetPasst(){
		if(time()<floatval(session('findPass.findTime'))+30*60){
			$userId = session('findPass.userId');
			$m = new MUsers();
			$info = $m->getById($userId);
			$infos['loginName'] = $info['loginName'];
            $infos['areaCode'] = $info['areaCode'];
			if($info['userPhone']!='')$infos['userPhone'] = WSTStrReplace($info['userPhone'],'*',3);
			if($info['userEmail']!='')$infos['userEmail'] = WSTStrReplace($info['userEmail'],'*',2,'@');
		}else{
			$infos['loginName'] = $infos['userPhone'] = $infos['userEmail'] ='';
		}
		echo(json_encode(WSTReturn('success',1,$infos)));die;
	}

	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/findPass",
     *     summary="找回密码",
     *     description="找回密码，根据传递的step不同，传递的参数也不同。第一步账号是否存在，第二步验证手机的验证码或者验证邮箱的验证码是否正确，第三步设置新密码",
	 * 	   @OA\Parameter(name="step", in="query", @OA\Schema(type="string"), required=true, description="当前为第几步，可选值1，2，3"),
	 * 	   @OA\Parameter(name="verifyCode", in="query", @OA\Schema(type="string"), required=true, description="step=1时传递，验证码"),
	 * 	   @OA\Parameter(name="loginName", in="query", @OA\Schema(type="string"), required=true, description="step=1时传递，登录名"),
	 *
	 * 	   @OA\Parameter(name="Checkcode", in="query", @OA\Schema(type="string"), required=true, description="step=2时传递，手机验证码/邮箱验证码"),
	 * 	   @OA\Parameter(name="modes", in="query", @OA\Schema(type="string"), required=true, description="step=2时传递，0手机找回方式，1邮箱找回方式"),
	 *
	 * 	   @OA\Parameter(name="loginPwd", in="query", @OA\Schema(type="string"), required=true, description="step=3时传递，新密码"),
     *     @OA\Parameter(name="repassword", in="query", @OA\Schema(type="string"), required=true, description="step=3时传递，确认新密码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function findPass(){
		//禁止缓存
		header('Cache-Control:no-cache,must-revalidate');
		header('Pragma:no-cache');
		$code = input("post.verifyCode");
		$step = input("post.step/d");
		switch ($step) {
			case 1:#第一步，验证身份
				session('findPhone',null);
				if(!WSTVerifyCheck($code)){
					return json_encode(WSTReturn(lang('verify_code_error'),-1));
				}
				$loginName = input("post.loginName");
				$rs = WSTCheckLoginKey($loginName);
				if($rs["status"]==1){
					return json_encode(WSTReturn(lang('no_find_login_name')));
					exit();
				}
				$m = new MUsers();
				$info = $m->checkAndGetLoginInfo($loginName);
				if ($info != false) {
					session('findPass',array('userId'=>$info['userId'],'loginName'=>$loginName, 'areaCode'=>$info['areaCode'], 'userPhone'=>$info['userPhone'],'userEmail'=>$info['userEmail'],'loginSecret'=>$info['loginSecret'],'findTime'=>time()));
					return json_encode(WSTReturn(lang("operation_success"),1));
				}else return json_encode(WSTReturn(lang('no_find_login_name')));
				break;
			case 2:#第二步,验证方式
				if (session('findPass.loginName') != null ){
					$obtainVerify = input("post.Checkcode");
					if(!$obtainVerify){
						return json_encode(WSTReturn(lang('require_verify_code'),-1));
					}
					if((int)input("modes")==1){
						if ( session('findPass.userPhone') == null) {
							return json_encode(WSTReturn(lang('no_bind_phone'),-1));
						}
						return $this->testingVerify($obtainVerify);
					}else{
						if (session('findPass.userEmail')==null) {
							return json_encode(WSTReturn(lang('no_bind_phone'),-1));
						}
						return $this->testingVerify($obtainVerify);
					}
				}else return json_encode(WSTReturn(lang('op_err'),-1));
				break;
			case 3:#第三步,设置新密码
				$resetPass = session('REST_success');
				if($resetPass != 1)return json_encode(WSTReturn(lang('op_err'),-1));
				$loginPwd = input("post.loginPwd");
				$repassword = input("post.repassword");
				if ($loginPwd == $repassword) {
					$m = new MUsers();
					$rs = $m->resetPass(1);
					return json_encode($rs);
				}else return json_encode(WSTReturn(lang('repwd_diff'),-1));
				break;
			default:
				return json_encode(WSTReturn(lang('op_err'),-1));
				break;
		}
	}
	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/getfindPhone",
     *     summary="找回登录密码：手机方式找回，手机发送验证码",
     *     description="找回登录密码：手机方式找回，手机发送验证码",
	 * 	   @OA\Parameter(name="userPhone", in="query", @OA\Schema(type="string"), required=true, description="手机号"),
	 * 	   @OA\Parameter(name="smsVerfy", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function getfindPhone(){
		session('WST_USER',session('findPass.userId'));
		if(session('findPass.userPhone')==''){
			return json_encode(WSTReturn(lang('no_bind_phone'),-1));
		}
		$phoneVerify = rand(100000,999999);
		session('WST_USER',null);
		$rv = ['status'=>-1,'msg'=>lang('sms_send_fail')];
		$tpl = WSTMsgTemplates('PHONE_FOTGET');
        $areaCode = session('findPass.areaCode');
		if( $tpl['tplContent']!='' && $tpl['status']=='1'){
			$params = ['tpl'=>$tpl,'params'=>['VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
			$m = new LogSms();
			$rv = $m->sendSMS(0,$areaCode.session('findPass.userPhone'),$params,'getPhoneVerify',$phoneVerify);
		}
		if($rv['status']==1){
			$USER = [];
			$USER['phoneVerify'] = $phoneVerify;
			$USER['time'] = time();
			session('findPhone',$USER);
			return json_encode(WSTReturn(lang('sms_send_success'),1));
		}
		return json_encode($rv);
	}
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/getfindEmail",
     *     summary="找回登录密码：邮箱方式找回，邮箱发送验证码",
     *     description="找回登录密码：邮箱方式找回，邮箱发送验证码",
	 * 	   @OA\Parameter(name="smsVerfy", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function getfindEmail(){
		$smsverfy = input("post.smsVerfy");
		if(!WSTVerifyCheck($smsverfy)){
            
			return json_encode(WSTReturn(lang('verify_code_error'),-1));
		}
		if (session('findPass.userEmail')==null) {
			return json_encode(WSTReturn(lang('no_bind_phone'),-1));
		}
		$code = rand(0,999999);
		$sendRs = ['status'=>-1,'msg'=>lang('email_send_fail')];
		$tpl = WSTMsgTemplates('EMAIL_EDIT');
		if( $tpl['tplContent']!='' && $tpl['status']=='1'){
			$find = ['${LOGIN_NAME}','${SEND_TIME}','${VERFIY_CODE}','${VERFIY_TIME}'];
			$replace = [session('findPass.loginName'),date('Y-m-d H:i:s'),$code,30];
			$sendRs = WSTSendMail(session('findPass.userEmail'),lang('reset_password'),str_replace($find,$replace,$tpl['content']));
		}
		if($sendRs['status']==1){
			$USER = [];
			$USER['phoneVerify'] = $code;
			$USER['time'] = time();
			session('findPhone',$USER);
			return json_encode(WSTReturn(lang('email_send_success'),1));
		}else{
			return json_encode(WSTReturn($sendRs['msg'],-1));
		}
	}
	/**
	 * 验证码检测/找回密码【由该控制器调用】
	 * -1 错误，1正确
	 */
	private function testingVerify($obtainVerify){
		if(!session('findPhone.phoneVerify') || time()>floatval(session('findPhone.time'))+10*60){
			return json_encode(WSTReturn(lang('verify_code_expired')));
			exit();
		}
		if (session('findPhone.phoneVerify') == $obtainVerify) {
			$fuserId = session('findPass.userId');
			if(!empty($fuserId)){
				// 记录发送短信的时间,用于验证是否过期
				session('REST_Time',time());
				session('REST_userId',$fuserId);
				session('REST_success','1');
				$rs['status'] = 1;
				$rs['url'] = 'ForgetPassLast';
				$rs['msg'] = lang('verify_success');
				return json_encode($rs);
			}
			return json_encode(WSTReturn(lang('invalid_user'),-1));
		}
		return json_encode(WSTReturn(lang('check_code_error'),-1));
	}
	/**********************************************			找回支付密码		*************************************************************/
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/backPayPass",
     *     summary="忘记支付密码，请求接口主要是判断是否有绑定手机",
     *     description="忘记支付密码，请求接口主要是判断是否有绑定手机",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="userPhone", type="integer", description="用户绑定的手机", example=""),
     *                         @OA\Property(property="phoneType", type="integer", description="是否绑定手机，1:已绑定 0:未绑定", example=""),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
	public function backPayPass(){
		$m = new M();
		$user = $m->getById();
		$_users = [];
		$userPhone = $user['userPhone'];
		$_users['userPhone'] = WSTStrReplace($user['userPhone'],'*',3);
		$_users['phoneType'] = empty($userPhone)?0:1;// 是否绑定了手机
        $_users['areaCode'] = $user['areaCode'];
		$timeVerify = session('Verify_backPaypwd_Time');
		return json_encode(WSTReturn('success',1,$_users));

	}
	/**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/app/Users/backpayCode",
     *     summary="忘记支付密码：发送短信",
     *     description="忘记支付密码：发送短信",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 * 	   @OA\Parameter(name="smsVerfy", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function backpayCode(){
		$m = new MUsers();
		$userId = model('index')->getUserId();
		$data = $m->getById($userId);
		$userPhone = $data['userPhone'];
        $areaCode = $data['areaCode'];
		$phoneVerify = rand(100000,999999);
		$rv = ['status'=>-1,'msg'=>lang('sms_send_fail')];
		$tpl = WSTMsgTemplates('PHONE_FOTGET_PAY');
		if( $tpl['tplContent']!='' && $tpl['status']=='1'){
			$params = ['tpl'=>$tpl,'params'=>['LOGIN_NAME'=>$data['loginName'],'VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
			$m = new LogSms();
			$rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'getPhoneVerifyt',$phoneVerify);
		}
		if($rv['status']==1){
			$USER = [];
			$USER['userPhone'] = $userPhone;
			$USER['phoneVerify'] = $phoneVerify;
			session('Verify_backPaypwd_info',$USER);
			session('Verify_backPaypwd_Time',time());
			return json_encode(WSTReturn(lang('sms_send_success'),1));
		}
		return json_encode($rv);
	}
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/verifybackPay",
     *     summary="忘记支付密码：验证短信",
     *     description="忘记支付密码：验证短信",
	 * 	   @OA\Parameter(name="phoneCode", in="query", @OA\Schema(type="string"), required=true, description="短信验证码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function verifybackPay(){
		$phoneVerify = input("post.phoneCode");
		$timeVerify = session('Verify_backPaypwd_Time');
		if(!session('Verify_backPaypwd_info.phoneVerify') || time()>floatval($timeVerify)+10*60){
			return json_encode(WSTReturn(lang('verify_code_expired')));
			exit();
		}
		if($phoneVerify==session('Verify_backPaypwd_info.phoneVerify')){
			return json_encode(WSTReturn(lang('verify_success'),1));
		}
		return json_encode(WSTReturn(lang('verify_code_nomatch')));
	}
	/**
     * @OA\Post(
     *     tags={"Users"},
     *     path="/app/Users/resetbackPay",
     *     summary="忘记支付密码：重置密码",
     *     description="忘记支付密码：重置密码",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="newPass", in="query", @OA\Schema(type="string"), required=true, description="新密码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
	public function resetbackPay(){
		$m = new MUsers();
		$userId = model('index')->getUserId();
		return json_encode($m->resetbackPay($userId));
	}
}
