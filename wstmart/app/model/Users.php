<?php
namespace wstmart\app\model;
use think\Db;
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
 * 用户类
 */
class Users extends Base{
    protected $pk = 'userId';
    /**
     * 登录验证
     * loginKey 64位加密传过来，密匙->例如:base64(base64(账号)._.base64(密码))
     * loginRemark:标记是android还是ios
     *
     * status:-1:账号不存在!  -2:账号已被停用! -3:账号或密码不正确! 1:登录成功~
     * msg:登录信息
     * user:{userId,loginName,userName,userPhoto}
     */
    public function login(){
        $rv = array('status'=>-1,'msg'=>lang('no_find_user'));
        $loginKey = input('loginKey');
        $code = input("verifyCode");


        if(!WSTVerifyCheck($code) && strpos(WSTConf("CONF.captcha_model"),"4")>=0){
            return WSTReturn(lang('verify_code_error'),-1);
        }


        $loginKey = base64_decode($loginKey);
        $loginKey = explode('_',$loginKey);

        // WSTAddslashes 处理转义字符 $loginName = WSTAddslashes(base64_decode($loginKey[0]));

        $loginName = base64_decode($loginKey[0]);
        $loginPwd = base64_decode($loginKey[1]);

        if($loginName=='' || $loginPwd=='')return $rv;
        $m = model('users');

        $urs = $this->field('userId,loginName,loginSecret,loginPwd,userName,userSex,userPhoto,userStatus,userScore,userType')
                    ->where("loginName='{$loginName}' or userPhone='{$loginName}' or userEmail='{$loginName}'")
                    ->where('dataFlag=1')
                    ->find();

        if(empty($urs))return $rv;//账号不存在!




        if($urs['userStatus']==0)return array('status'=>-2,'msg'=>lang('no_find_user'));//账号已被停用!

        if(md5($loginPwd.$urs['loginSecret'])!=$urs['loginPwd'])return array('status'=>-3,'msg'=>lang('auth_error'));//账号或密码不正确!


        //【微信绑定】判断是否有传unionId
        $unionId = input('unionId');
        if($unionId!=''){
            // 判断该unionId是否已经绑定其他账号
            $has = $this->where(['wxUnionId'=>$unionId,'dataFlag'=>1,'userStatus'=>1])->find();
            if(!empty($has)){
                return WSTReturn(lang('wx_already_bind'),-1);
            }
            // 判断该账号是否已绑定其他微信
            if($urs['wxUnionId'])return WSTReturn(lang('user_already_bind_wx'),-1);
            // 绑定unionId
            $this->where("loginName='{$loginName}' or userPhone='{$loginName}' or userEmail='{$loginName}'")
                    ->where('dataFlag=1')
                    ->setField('wxUnionId',$unionId);
        }


        // 【google绑定】判斷是否有傳googleId
        $googleId = input("googleId");
        if($googleId!=""){
            // 判斷該googleId是否已經綁定其他賬號
            $has = $this->alias('u')
                        ->join('third_users tu','tu.userId=u.userId','inner')
                        ->where(['tu.thirdOpenId'=>$googleId,'u.dataFlag'=>1])
                        ->find();
            if(!empty($has)){
                return WSTReturn(lang('google_already_bind'),-1);
            }
            $tuModel = Db::name('third_users');
            // 判斷該賬號是否已綁定其他google
            $hasBindQq = $tuModel->where(['userId'=>$urs['userId'],'thirdCode'=>'google'])->find();
            if(!empty($hasBindQq))return WSTReturn(lang('user_already_bind_google'),-1);
            $bindQqData = [];
            $bindQqData['userId'] = $urs['userId'];
            $bindQqData['thirdCode'] = 'google';
            $bindQqData['thirdOpenId'] = $googleId;
            $bindQqData['createTime'] = date('Y-m-d H:i:s');
            // 綁定fbId
            $bindRs = $tuModel->insert($bindQqData);
        }



        // 【facebook绑定】判斷是否有傳facebookId
        $facebookId = input("facebookId");
        if($facebookId!=""){
            // 判斷該facebookId是否已經綁定其他賬號
            $has = $this->alias('u')
                        ->join('third_users tu','tu.userId=u.userId','inner')
                        ->where(['tu.thirdOpenId'=>$facebookId,'u.dataFlag'=>1])
                        ->find();
            if(!empty($has)){
                return WSTReturn(lang('facebook_already_bind'),-1);
            }
            $tuModel = Db::name('third_users');
            // 判斷該賬號是否已綁定其他facebook
            $hasBindQq = $tuModel->where(['userId'=>$urs['userId'],'thirdCode'=>'facebook'])->find();
            if(!empty($hasBindQq))return WSTReturn(lang('user_already_bind_facebook'),-1);
            $bindQqData = [];
            $bindQqData['userId'] = $urs['userId'];
            $bindQqData['thirdCode'] = 'facebook';
            $bindQqData['thirdOpenId'] = $facebookId;
            $bindQqData['createTime'] = date('Y-m-d H:i:s');
            // 綁定fbId
            $bindRs = $tuModel->insert($bindQqData);
        }



        //【QQ绑定】判断是否有传qqOpenId
        $qqOpenId = input('qqOpenId');
        if($qqOpenId!=''){
            // 判断该qqOpenId是否已经绑定其他账号
            $has = $this->alias('u')
                        ->join('third_users tu','tu.userId=u.userId','inner')
                        ->where(['tu.thirdOpenId'=>$qqOpenId,'u.dataFlag'=>1])
                        ->find();
            if(!empty($has)){
                return WSTReturn(lang('qq_already_bind'),-1);
            }
            $tuModel = Db::name('third_users');
            // 判断该账号是否已绑定其他QQ
            $hasBindQq = $tuModel->where(['userId'=>$urs['userId'],'thirdCode'=>'qq'])->find();
            if(!empty($hasBindQq))return WSTReturn(lang('user_already_bind_qq'),-1);
            $bindQqData = [];
            $bindQqData['userId'] = $urs['userId'];
            $bindQqData['thirdCode'] = 'qq';
            $bindQqData['thirdOpenId'] = $qqOpenId;
            $bindQqData['createTime'] = date('Y-m-d H:i:s');
            // 绑定qqOpenId
            $bindRs = $tuModel->insert($bindQqData);
        }
         //【支付宝绑定】判断是否有传支付宝user_id
        $alipayId = input('alipayId');
        if($alipayId!=''){
            // 判断该alipayId是否已经绑定其他账号
            $has = $this->alias('u')
                        ->join('third_users tu','tu.userId=u.userId','inner')
                        ->where(['tu.thirdOpenId'=>$alipayId,'u.dataFlag'=>1])
                        ->find();
            if(!empty($has)){
                return WSTReturn(lang('ali_already_bind'),-1);
            }
            $tuModel = Db::name('third_users');
            // 判断该账号是否已绑定其他支付宝账号
            $hasBindAlipay = $tuModel->where(['userId'=>$urs['userId'],'thirdCode'=>'alipay'])->find();
            if(!empty($hasBindAlipay))return WSTReturn(lang('user_already_bind_ali'),-1);
            $bindAlipayData = [];
            $bindAlipayData['userId'] = $urs['userId'];
            $bindAlipayData['thirdCode'] = 'alipay';
            $bindAlipayData['thirdOpenId'] = $alipayId;
            $bindAlipayData['createTime'] = date('Y-m-d H:i:s');
            // 绑定alipayId
            $bindRs = $tuModel->insert($bindAlipayData);
        }


        unset($urs['loginSecret'],$urs['loginPwd'],$urs['userStatus']);
        $rv['status'] = 1;
        $rv['msg'] = lang('login_success');
        $rv['data'] = $urs;
        //记录登录信息
        $data = array();
        $data["userId"] = $urs['userId'];
        $data["loginTime"] = date('Y-m-d H:i:s');

        // 用户登录地址 $data["loginIp"] = get_client_ip();
        $data["loginIp"] = request()->ip();


        //登录来源、登录设备

        $data["loginRemark"] = Input('loginRemark','android');
        $data["loginSrc"] = ($data["loginRemark"]=='android')?3:4;

        /**************** 记录登录日志  **************/
        Db::name('log_user_logins')->insert($data);

        //记录tokenId
        $m = Db::name('app_session');

        /*************************   制作key  **********************/
        $key = sprintf('%011d',$urs['userId']);

        $tokenId = $this->to_guid_string($key.time());


        $data = array();
        $data['userId'] = $urs['userId'];
        $data['tokenId'] = $tokenId;
        $data['startTime'] = date('Y-m-d H:i:s');
        $data['deviceId'] = input('deviceId');
        $m->insert($data);
        $rv['data']['tokenId'] = $tokenId;

        // 判断是否为客服账号
        hook('afterUserLogin',['user'=>&$urs,'isApp'=>1]);
        //删除上一条登录记录
        $m->where('tokenId!="'.$tokenId.'" and userId='.$urs['userId'])->delete();
        return $rv;
    }

    /**
     * 手机登录验证
     */
    public function loginByPhone(){
        $rv = array('status'=>-1,'msg'=>lang('no_find_user'));
        $loginName = input('loginName');
        if($loginName=='')return $rv;

        $phoneVerify = input("post.mobileCode");
        $timeVerify = session('VerifyCode_userPhone_Time2');
        if($loginName!=session('VerifyCode_userPhone2')){
            return WSTReturn(lang('verify_phone_no_match'),-1);
        }
        if(!session('VerifyCode_userPhone_Verify2') || time()>floatval($timeVerify)+10*60){
            return WSTReturn(lang('sms_code_expired'),-1);
        }

        if($phoneVerify==session('VerifyCode_userPhone_Verify2')){
            $m = model('users');

            $urs = $this->field('userId,loginName,loginSecret,loginPwd,userName,userSex,userPhoto,userStatus,userScore,userType,wxUnionId')
                ->where("userPhone='{$loginName}'")
                ->where('dataFlag=1')
                ->find();

            if(empty($urs))return $rv;//账号不存在!

            if($urs['userStatus']==0)return array('status'=>-2,'msg'=>lang('no_find_user'));//账号已被停用!

            //【微信绑定】判断是否有传unionId
            $unionId = input('unionId');
            if($unionId!=''){
                // 判断该unionId是否已经绑定其他账号
                $has = $this->where(['wxUnionId'=>$unionId,'dataFlag'=>1,'userStatus'=>1])->find();
                if(!empty($has)){
                    return WSTReturn(lang('wx_already_bind'),-1);
                }
                // 判断该账号是否已绑定其他微信
                if($urs['wxUnionId'])return WSTReturn(lang('user_already_bind_wx'),-1);
                // 绑定unionId
                $this->where("loginName='{$loginName}' or userPhone='{$loginName}' or userEmail='{$loginName}'")
                    ->where('dataFlag=1')
                    ->setField('wxUnionId',$unionId);
            }

            // 【google绑定】判斷是否有傳googleId
            $googleId = input("googleId");
            if($googleId!=""){
                // 判斷該googleId是否已經綁定其他賬號
                $has = $this->alias('u')
                            ->join('third_users tu','tu.userId=u.userId','inner')
                            ->where(['tu.thirdOpenId'=>$googleId,'u.dataFlag'=>1])
                            ->find();
                if(!empty($has)){
                    return WSTReturn(lang('google_already_bind'),-1);
                }
                $tuModel = Db::name('third_users');
                // 判斷該賬號是否已綁定其他google
                $hasBindQq = $tuModel->where(['userId'=>$urs['userId'],'thirdCode'=>'google'])->find();
                if(!empty($hasBindQq))return WSTReturn(lang('user_already_bind_google'),-1);
                $bindQqData = [];
                $bindQqData['userId'] = $urs['userId'];
                $bindQqData['thirdCode'] = 'google';
                $bindQqData['thirdOpenId'] = $googleId;
                $bindQqData['createTime'] = date('Y-m-d H:i:s');
                // 綁定fbId
                $bindRs = $tuModel->insert($bindQqData);
            }



            // 【facebook绑定】判斷是否有傳facebookId
            $facebookId = input("facebookId");
            if($facebookId!=""){
                // 判斷該facebookId是否已經綁定其他賬號
                $has = $this->alias('u')
                            ->join('third_users tu','tu.userId=u.userId','inner')
                            ->where(['tu.thirdOpenId'=>$facebookId,'u.dataFlag'=>1])
                            ->find();
                if(!empty($has)){
                    return WSTReturn(lang('facebook_already_bind'),-1);
                }
                $tuModel = Db::name('third_users');
                // 判斷該賬號是否已綁定其他facebook
                $hasBindQq = $tuModel->where(['userId'=>$urs['userId'],'thirdCode'=>'facebook'])->find();
                if(!empty($hasBindQq))return WSTReturn(lang('user_already_bind_facebook'),-1);
                $bindQqData = [];
                $bindQqData['userId'] = $urs['userId'];
                $bindQqData['thirdCode'] = 'facebook';
                $bindQqData['thirdOpenId'] = $facebookId;
                $bindQqData['createTime'] = date('Y-m-d H:i:s');
                // 綁定fbId
                $bindRs = $tuModel->insert($bindQqData);
            }


            //【QQ绑定】判断是否有传qqOpenId
            $qqOpenId = input('qqOpenId');
            if($qqOpenId!=''){
                // 判断该qqOpenId是否已经绑定其他账号
                $has = $this->alias('u')
                    ->join('third_users tu','tu.userId=u.userId','inner')
                    ->where(['tu.thirdOpenId'=>$qqOpenId,'u.dataFlag'=>1])
                    ->find();
                if(!empty($has)){
                    return WSTReturn(lang('qq_already_bind'),-1);
                }
                $tuModel = Db::name('third_users');
                // 判断该账号是否已绑定其他QQ
                $hasBindQq = $tuModel->where(['userId'=>$urs['userId'],'thirdCode'=>'qq'])->find();
                if(!empty($hasBindQq))return WSTReturn(lang('user_already_bind_qq'),-1);
                $bindQqData = [];
                $bindQqData['userId'] = $urs['userId'];
                $bindQqData['thirdCode'] = 'qq';
                $bindQqData['thirdOpenId'] = $qqOpenId;
                $bindQqData['createTime'] = date('Y-m-d H:i:s');
                // 绑定qqOpenId
                $bindRs = $tuModel->insert($bindQqData);
            }
            //【支付宝绑定】判断是否有传支付宝user_id
            $alipayId = input('alipayId');
            if($alipayId!=''){
                // 判断该alipayId是否已经绑定其他账号
                $has = $this->alias('u')
                    ->join('third_users tu','tu.userId=u.userId','inner')
                    ->where(['tu.thirdOpenId'=>$alipayId,'u.dataFlag'=>1])
                    ->find();
                if(!empty($has)){
                    return WSTReturn(lang('ali_already_bind'),-1);
                }
                $tuModel = Db::name('third_users');
                // 判断该账号是否已绑定其他支付宝账号
                $hasBindAlipay = $tuModel->where(['userId'=>$urs['userId'],'thirdCode'=>'alipay'])->find();
                if(!empty($hasBindAlipay))return WSTReturn(lang('user_already_bind_ali'),-1);
                $bindAlipayData = [];
                $bindAlipayData['userId'] = $urs['userId'];
                $bindAlipayData['thirdCode'] = 'alipay';
                $bindAlipayData['thirdOpenId'] = $alipayId;
                $bindAlipayData['createTime'] = date('Y-m-d H:i:s');
                // 绑定alipayId
                $bindRs = $tuModel->insert($bindAlipayData);
            }


            unset($urs['loginSecret'],$urs['loginPwd'],$urs['userStatus']);
            $rv['status'] = 1;
            $rv['msg'] = lang('login_success');
            $rv['data'] = $urs;
            //记录登录信息
            $data = array();
            $data["userId"] = $urs['userId'];
            $data["loginTime"] = date('Y-m-d H:i:s');

            // 用户登录地址 $data["loginIp"] = get_client_ip();
            $data["loginIp"] = request()->ip();


            //登录来源、登录设备

            $data["loginRemark"] = Input('loginRemark','android');
            $data["loginSrc"] = ($data["loginRemark"]=='android')?3:4;

            /**************** 记录登录日志  **************/
            Db::name('log_user_logins')->insert($data);

            //记录tokenId
            $m = Db::name('app_session');

            /*************************   制作key  **********************/
            $key = sprintf('%011d',$urs['userId']);

            $tokenId = $this->to_guid_string($key.time());


            $data = array();
            $data['userId'] = $urs['userId'];
            $data['tokenId'] = $tokenId;
            $data['startTime'] = date('Y-m-d H:i:s');
            $data['deviceId'] = input('deviceId');
            $m->insert($data);
            $rv['data']['tokenId'] = $tokenId;

            // 判断是否为客服账号
            hook('afterUserLogin',['user'=>&$urs,'isApp'=>1]);

            //删除上一条登录记录
            $m->where('tokenId!="'.$tokenId.'" and userId='.$urs['userId'])->delete();
            return $rv;
        }else{
            return WSTReturn(lang('sms_code_no_match'),-1);
        }
    }

    /**
     * 根据PHP各种类型变量生成唯一标识号
     * @param mixed $mix 变量
     * @return string
     */
    private function to_guid_string($mix) {
        if (is_object($mix)) {
            return spl_object_hash($mix);
        } elseif (is_resource($mix)) {
            $mix = get_resource_type($mix) . strval($mix);
        } else {
            $mix = serialize($mix);
        }
        return md5($mix);
    }

    /**
     * 用户注册（手机注册）
     * registerKey 64位加密传过来，密匙->例如:base64(base64(账号)._.base64(密码))
     * loginRemark:标记是android还是ios
     * deviceId:设备Id
     *
     */
    public function register(){
        $rv = array('status'=>-1,'msg'=>lang('user_accnout_exists'));
        $registerKey = input('registerKey');
        $registerKey = base64_decode($registerKey);
        $registerKey = explode('_',$registerKey);
        $loginName = base64_decode($registerKey[0]);
        $loginPwd = base64_decode($registerKey[1]);

        $startTime = (int)session('VerifyCode_userPhone_Time');
        if((time()-$startTime)>120){
            return WSTReturn(lang('sms_code_expired'));
        }
        $loginName2 = session('VerifyCode_userPhone');
        if($loginName!=$loginName2){
            return WSTReturn(lang('regist_phone_no_match'));
        }
        // 检测手机号
        if(!WSTIsPhone($loginName))return WSTReturn(lang('valid_user_phone'));
        //检测账号是否存在
        $rs = WSTCheckLoginKey($loginName);

        $data = array();
        $nameType = (int)input("post.nameType");
        $mobileCode = input("post.mobileCode");

        //只允许手机号码注册
        $data['userPhone'] = $loginName;
        $verify = session('VerifyCode_userPhone_Verify');
        if($mobileCode=="" || $verify != $mobileCode){
            return WSTReturn(lang('verify_code_error'));
        }
        $loginName = WSTRandomLoginName($loginName);

        //【微信注册】判断是否有传unionId
        $unionId = input('unionId');
        if($unionId!=''){
            // 判断该unionId是否已经绑定其他账号
            $has = $this->where(['wxUnionId'=>$unionId,'dataFlag'=>1,'userStatus'=>1])->find();
            if(!empty($has)){
                return WSTReturn(lang('wx_already_bind'),-1);
            }
            $data['wxUnionId'] = $unionId;
        }

        //【facebook綁定】判斷是否有傳facebookId
        $facebookId = input('facebookId');
        if($facebookId!=''){
            // 判斷該facebookId是否已經綁定其他賬號
            $has = $this->alias('u')
                        ->join('third_users tu','tu.userId=u.userId','inner')
                        ->where(['tu.thirdOpenId'=>$facebookId,'u.dataFlag'=>1])
                        ->find();
            if(!empty($has)){
                return WSTReturn(lang('facebook_already_bind'),-1);
            }
        }

        //【QQ绑定】判断是否有传qqOpenId
        $qqOpenId = input('qqOpenId');
        if($qqOpenId!=''){
            // 判断该qqOpenId是否已经绑定其他账号
            $has = $this->alias('u')
                        ->join('third_users tu','tu.userId=u.userId','inner')
                        ->where(['tu.thirdOpenId'=>$qqOpenId,'u.dataFlag'=>1])
                        ->find();
            if(!empty($has)){
                return WSTReturn(lang('qq_already_bind'),-1);
            }
        }
        //【支付宝绑定】判断是否有传支付宝user_di
        $alipayId = input('alipayId');
        if($alipayId!=''){
            // 判断该alipayId是否已经绑定其他账号
            $has = $this->alias('u')
                        ->join('third_users tu','tu.userId=u.userId','inner')
                        ->where(['tu.thirdOpenId'=>$alipayId,'u.dataFlag'=>1])
                        ->find();
            if(!empty($has)){
                return WSTReturn(lang('ali_already_bind'),-1);
            }
        }
        if($rs['status']==1){
            $data['loginName'] = $loginName;
            $data['userName'] = lang('phone_account').substr($data['userPhone'],-4);
            $data["loginSecret"] = rand(1000,9999);
            $data['loginPwd'] = md5($loginPwd.$data['loginSecret']);
            $data['userType'] = 0;
            $data['createTime'] = date('Y-m-d H:i:s');
            $data['dataFlag'] = 1;
            $data['lastTime'] = date('Y-m-d H:i:s');
            $data['lastIP'] = request()->ip();
            $userId = $this->data($data)->save();
            if(false !== $userId){
                 // 執行【facebookId綁定】
                 if($facebookId!=''){
                    $tuModel = Db::name('third_users');
                    $bindFbData = [];
                    $bindFbData['userId'] = $this->userId;
                    $bindFbData['thirdCode'] = 'facebook';
                    $bindFbData['thirdOpenId'] = $facebookId;
                    $bindFbData['createTime'] = date('Y-m-d H:i:s');
                    // 綁定facebookId
                    $bindRs = $tuModel->insert($bindFbData);
                }
                 // 執行【googleId綁定】
                 if($googleId!=''){
                    $tuModel = Db::name('third_users');
                    $bindFbData = [];
                    $bindFbData['userId'] = $this->userId;
                    $bindFbData['thirdCode'] = 'google';
                    $bindFbData['thirdOpenId'] = $googleId;
                    $bindFbData['createTime'] = date('Y-m-d H:i:s');
                    // 綁定googleId
                    $bindRs = $tuModel->insert($bindFbData);
                }
                // 执行【QQ绑定】
                if($qqOpenId!=''){
                    $tuModel = Db::name('third_users');
                    $bindQqData = [];
                    $bindQqData['userId'] = $this->userId;
                    $bindQqData['thirdCode'] = 'qq';
                    $bindQqData['thirdOpenId'] = $qqOpenId;
                    $bindQqData['createTime'] = date('Y-m-d H:i:s');
                    // 绑定qqOpenId
                    $bindRs = $tuModel->insert($bindQqData);
                }
                // 执行【支付宝账号绑定】
                if($alipayId!=''){
                    $tuModel = Db::name('third_users');
                    $bindAlipayData = [];
                    $bindAlipayData['userId'] = $this->userId;
                    $bindAlipayData['thirdCode'] = 'alipay';
                    $bindAlipayData['thirdOpenId'] = $alipayId;
                    $bindAlipayData['createTime'] = date('Y-m-d H:i:s');
                    // 绑定alipayId
                    $bindRs = $tuModel->insert($bindAlipayData);
                }
                $data = array();
                $userId = $this->userId;
                $data["userId"] = $userId;
                $data["loginTime"] = date('Y-m-d H:i:s');
                $data["loginIp"] = request()->ip();
                $data["loginRemark"] = input('loginRemark');
                $data["loginSrc"] = ($data["loginRemark"]=='android')?3:4;
                Db::name('log_user_logins')->insert($data);
                //记录tokenId
                $data = array();
                $key = sprintf('%011d',$userId);
                $tokenId = $this->to_guid_string($key.time());
                $rv['status']= 1;
                $rv['msg']= lang('regist_success');
                $data['userId'] = $userId;
                $data['tokenId'] = $tokenId;
                $data['startTime'] = date('Y-m-d H:i:s');
                $data['deviceId'] = input('deviceId');
                Db::name('app_session')->insert($data);
                //发送消息
                $tpl = WSTMsgTemplates('USER_REGISTER');
                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
                    $find = ['${LOGIN_NAME}','${MALL_NAME}'];
                    $replace = [$loginName,WSTConf('CONF.mallName')];
                    WSTSendMsg($userId,str_replace($find,$replace,$tpl['tplContent']),['from'=>0,'dataId'=>0]);
                }
                $user = $this->where("userId=".$userId)->field("userId,loginName,userName,userSex,userType,userPhoto,userScore")->find();
                hook('afterUserRegist',['user'=>&$user]);
                $rv['data'] = $user;
                $rv['data']['tokenId'] = $tokenId;
            }
        }
        return $rv;
    }

    /**
     * 用户注册（账号注册）
     * registerKey 64位加密传过来，密匙->例如:base64(base64(账号)._.base64(密码))
     * loginRemark:标记是android还是ios
     * deviceId:设备Id
     *
     */
    public function registerByAccount(){
        $rv = array('status'=>-1,'msg'=>lang('user_accnout_exists'));
        $registerKey = input('registerKey');
        $registerKey = base64_decode($registerKey);
        $registerKey = explode('_',$registerKey);
        $loginName = base64_decode($registerKey[0]);
        $loginPwd = base64_decode($registerKey[1]);
        $code = input("post.verifyCode");
        if(!WSTVerifyCheck($code)){
            return WSTReturn(lang('verify_code_error'));
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $loginName)) {
            return WSTReturn(lang('login_name_rule'));
        }

        //检测账号是否存在
        $rs = WSTCheckLoginKey($loginName);

        $data = array();
        $nameType = (int)input("post.nameType");

        //$loginName = WSTRandomLoginName($loginName);

        //【微信注册】判断是否有传unionId
        $unionId = input('unionId');
        if($unionId!=''){
            // 判断该unionId是否已经绑定其他账号
            $has = $this->where(['wxUnionId'=>$unionId,'dataFlag'=>1,'userStatus'=>1])->find();
            if(!empty($has)){
                return WSTReturn(lang('wx_already_bind'),-1);
            }
            $data['wxUnionId'] = $unionId;
        }
        //【facebook綁定】判斷是否有傳facebookId
        $facebookId = input('facebookId');
        if($facebookId!=''){
            // 判斷該facebookId是否已經綁定其他賬號
            $has = $this->alias('u')
                ->join('third_users tu','tu.userId=u.userId','inner')
                ->where(['tu.thirdOpenId'=>$facebookId,'u.dataFlag'=>1])
                ->find();
            if(!empty($has)){
                return PPLReturn(lang('facebook_already_bind'),-1);
            }
        }
        $googleId = input('googleId');
        if($googleId!=''){
            // 判斷該googleId是否已經綁定其他賬號
            $has = $this->alias('u')
                ->join('third_users tu','tu.userId=u.userId','inner')
                ->where(['tu.thirdOpenId'=>$googleId,'u.dataFlag'=>1])
                ->find();
            if(!empty($has)){
                return PPLReturn(lang('google_already_bind'),-1);
            }
        }

        //【QQ绑定】判断是否有传qqOpenId
        $qqOpenId = input('qqOpenId');
        if($qqOpenId!=''){
            // 判断该qqOpenId是否已经绑定其他账号
            $has = $this->alias('u')
                ->join('third_users tu','tu.userId=u.userId','inner')
                ->where(['tu.thirdOpenId'=>$qqOpenId,'u.dataFlag'=>1])
                ->find();
            if(!empty($has)){
                return WSTReturn(lang('qq_already_bind'),-1);
            }
        }
        //【支付宝绑定】判断是否有传支付宝user_di
        $alipayId = input('alipayId');
        if($alipayId!=''){
            // 判断该alipayId是否已经绑定其他账号
            $has = $this->alias('u')
                ->join('third_users tu','tu.userId=u.userId','inner')
                ->where(['tu.thirdOpenId'=>$alipayId,'u.dataFlag'=>1])
                ->find();
            if(!empty($has)){
                return WSTReturn(lang('ali_already_bind'),-1);
            }
        }
        if($rs['status']==1){
            $data['loginName'] = $loginName;
            $data['userName'] = $loginName;
            $data["loginSecret"] = rand(1000,9999);
            $data['loginPwd'] = md5($loginPwd.$data['loginSecret']);
            $data['userType'] = 0;
            $data['createTime'] = date('Y-m-d H:i:s');
            $data['dataFlag'] = 1;
            $data['lastTime'] = date('Y-m-d H:i:s');
            $data['lastIP'] = request()->ip();
            $userId = $this->data($data)->save();
            if(false !== $userId){
                // 執行【facebookId綁定】
                if($facebookId!=''){
                    $tuModel = Db::name('third_users');
                    $bindFbData = [];
                    $bindFbData['userId'] = $this->userId;
                    $bindFbData['thirdCode'] = 'facebook';
                    $bindFbData['thirdOpenId'] = $facebookId;
                    $bindFbData['createTime'] = date('Y-m-d H:i:s');
                    // 綁定facebookId
                    $bindRs = $tuModel->insert($bindFbData);
                }
                 // 執行【googleId綁定】
                 if($googleId!=''){
                    $tuModel = Db::name('third_users');
                    $bindGgData = [];
                    $bindGgData['userId'] = $this->userId;
                    $bindGgData['thirdCode'] = 'google';
                    $bindGgData['thirdOpenId'] = $googleId;
                    $bindGgData['createTime'] = date('Y-m-d H:i:s');
                    // 綁定googleId
                    $bindRs = $tuModel->insert($bindGgData);
                }
                // 执行【QQ绑定】
                if($qqOpenId!=''){
                    $tuModel = Db::name('third_users');
                    $bindQqData = [];
                    $bindQqData['userId'] = $this->userId;
                    $bindQqData['thirdCode'] = 'qq';
                    $bindQqData['thirdOpenId'] = $qqOpenId;
                    $bindQqData['createTime'] = date('Y-m-d H:i:s');
                    // 绑定qqOpenId
                    $bindRs = $tuModel->insert($bindQqData);
                }
                // 执行【支付宝账号绑定】
                if($alipayId!=''){
                    $tuModel = Db::name('third_users');
                    $bindAlipayData = [];
                    $bindAlipayData['userId'] = $this->userId;
                    $bindAlipayData['thirdCode'] = 'alipay';
                    $bindAlipayData['thirdOpenId'] = $alipayId;
                    $bindAlipayData['createTime'] = date('Y-m-d H:i:s');
                    // 绑定alipayId
                    $bindRs = $tuModel->insert($bindAlipayData);
                }
                $data = array();
                $userId = $this->userId;
                $data["userId"] = $userId;
                $data["loginTime"] = date('Y-m-d H:i:s');
                $data["loginIp"] = request()->ip();
                $data["loginRemark"] = input('loginRemark');
                $data["loginSrc"] = ($data["loginRemark"]=='android')?3:4;
                Db::name('log_user_logins')->insert($data);
                //记录tokenId
                $data = array();
                $key = sprintf('%011d',$userId);
                $tokenId = $this->to_guid_string($key.time());
                $rv['status']= 1;
                $rv['msg']= lang('regist_success');
                $data['userId'] = $userId;
                $data['tokenId'] = $tokenId;
                $data['startTime'] = date('Y-m-d H:i:s');
                $data['deviceId'] = input('deviceId');
                Db::name('app_session')->insert($data);
                //发送消息
                $tpl = WSTMsgTemplates('USER_REGISTER');
                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
                    $find = ['${LOGIN_NAME}','${MALL_NAME}'];
                    $replace = [$loginName,WSTConf('CONF.mallName')];
                    WSTSendMsg($userId,str_replace($find,$replace,$tpl['tplContent']),['from'=>0,'dataId'=>0]);
                }
                $user = $this->where("userId=".$userId)->field("userId,loginName,userName,userSex,userType,userPhoto,userScore")->find();
                hook('afterUserRegist',['user'=>&$user]);
                $rv['data'] = $user;
                $rv['data']['tokenId'] = $tokenId;
            }
        }
        return $rv;
    }


    /***********************************************************  ***************************************************************/

    /**
     * 修改用户密码
     */
    public function editPass($id){
        $data = array();
        $data["loginPwd"] = input("post.newPass");
        if(!$data["loginPwd"]){
            return WSTReturn(lang('require_password'),-1);
        }
        // 检测密码长度
        $len = strlen($data["loginPwd"]);
        if($len<6 || $len>20){
            return WSTReturn(lang('login_pwd_rule'),-1);
        }
        $rs = $this->where('userId='.$id)->find();
        //核对密码
        if($rs['loginPwd']){
            if($rs['loginPwd']==md5(input("post.oldPass").$rs['loginSecret'])){
                $data["loginPwd"] = md5(input("post.newPass").$rs['loginSecret']);
                $rs = $this->update($data,['userId'=>$id]);
                if(false !== $rs){
                    return WSTReturn(lang('login_pwd_edit_success'), 1);
                }else{
                    return WSTReturn($this->getError(),-1);
                }
            }else{
                return WSTReturn(lang('old_login_pwd_error'),-1);
            }
        }else{
            $data["loginPwd"] = md5(input("post.newPass").$rs['loginSecret']);
            $rs = $this->update($data,['userId'=>$id]);
            if(false !== $rs){
                return WSTReturn(lang('login_pwd_edit_success'), 1);
            }else{
                return WSTReturn($this->getError(),-1);
            }
        }
    }
    /**
     * 修改用户支付密码
     */
    public function editPayPass(){
        $id = $this->getUserId();
        $data = array();
        $data["payPwd"] = input("post.newPass");
        if(!$data["payPwd"]){
            return WSTReturn(lang('require_pay_pwd'),-1);
        }
        $rs = $this->where('userId='.$id)->find();
        //核对密码
        if($rs['payPwd']){
            if($rs['payPwd']==md5(input("post.oldPass").$rs['loginSecret'])){
                $data["payPwd"] = md5($data["payPwd"].$rs['loginSecret']);
                $rs = $this->update($data,['userId'=>$id]);
                if(false !== $rs){
                    return WSTReturn(lang('pay_pwd_edit_success'), 1);
                }else{
                    return WSTReturn(lang('pay_pwd_edit_fail'),-1);
                }
            }else{
                return WSTReturn(lang('old_pay_pwd_error'),-1);
            }
        }else{
            $data["payPwd"] = md5($data["payPwd"].$rs['loginSecret']);
            $rs = $this->update($data,['userId'=>$id]);
            if(false !== $rs){
                return WSTReturn(lang('set_pay_pwd_success'), 1);
            }else{
                return WSTReturn(lang('set_pay_pwd_fail'),-1);
            }
        }
    }
   /**
    *  获取用户信息
    */
    public function getById(){
        $id = $this->getUserId();
        $rs = $this->field('loginSecret,loginPwd,userQQ,userEmail,trueName,lastIP,lastTime,dataFlag,userStatus,createTime,wxOpenId,wxUnionId,distributMoney,brithday',true)
                   ->where(['userId'=>(int)$id])
                   ->find();
        $rs['ranks'] = WSTUserRank($rs['userTotalScore']);
        return $rs;
    }
    /**
     * 编辑资料
    */
    public function edit(){
        $Id = $this->getUserId();
        $data = input('post.');
        if(isset($data['brithday']))$data['brithday'] = ($data['brithday']=='')?date('Y-m-d'):$data['brithday'];
        WSTAllow($data,'brithday,trueName,userName,userId,userPhoto,userQQ,userSex');
        Db::startTrans();
        try{
            if(isset($data['userPhoto']) && $data['userPhoto']!='')
                 WSTUseResource(0, $Id, $data['userPhoto'],'users','userPhoto');

            $result = $this->allowField(true)->save($data,['userId'=>$Id]);
            if(false !== $result){
                Db::commit();
                return WSTReturn(lang('op_ok'), 1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }
    }

    /**
     * 绑定手机
     */
    public function editPhone($userPhone){
        $userId = $this->getUserId();
        $data = array();
        $data["userPhone"] = $userPhone;
        $rs = $this->update($data,['userId'=>$userId]);
        if(false !== $rs){
            return WSTReturn(lang('bind_ok'), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
    }
    /**
     * 重置用户密码
     */
    public function resetPass(){
        if(time()>floatval(session('REST_Time'))+30*60){
            return WSTReturn(lang('link_expired'), -1);
        }
        $reset_userId = (int)session('REST_userId');
        if($reset_userId==0){
            return WSTReturn(lang('invalid_user'), -1);
        }
        $user = $this->where(["dataFlag"=>1,"userStatus"=>1,"userId"=>$reset_userId])->find();
        if(empty($user)){
            return WSTReturn(lang('invalid_user'), -1);
        }
        $loginPwd = input("post.loginPwd");
        if(trim($loginPwd)==''){
            return WSTReturn(lang('invalid_pwd'), -1);
        }
        $data['loginPwd'] = md5($loginPwd.$user["loginSecret"]);
        $rc = $this->update($data,['userId'=>$reset_userId]);
        if(false !== $rc){
            session('REST_userId',null);
            session('REST_Time',null);
            session('REST_success',null);
            session('findPass',null);
            return WSTReturn(lang('edit_ok'), 1);
        }
        return $rs;
    }

    /**
     * 获取用户可用积分
     */
    public function getFieldsById($userId,$fields){
        return $this->where(['userId'=>$userId,'dataFlag'=>1])->field($fields)->find();
    }
    /**
     * 验证用户支付密码
     */
    function checkPayPwd(){
        $userId = $this->getUserId();
        $rs = $this->field('payPwd,loginSecret')->find($userId);
        $payPwd = input('payPwd');
        if($rs['payPwd']==md5($payPwd.$rs['loginSecret'])){
            return WSTReturn('',1);
        }
        return WSTReturn(lang('pay_pwd_error'),-1);
    }
    /**
    * 用户注销
    *
    */
    public function logout(){
        $tokenId = input('tokenId');
        $rs = Db::name('app_session')->where("tokenId='{$tokenId}'")->delete();
        if($rs!==false)return WSTReturn(lang('logout_ok'),1);
        return WSTReturn(lang('exception_msg'),-1);
    }
}
