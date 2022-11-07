<?php
namespace addons\captcha\model;
use think\addons\BaseModel as Base;
use wstmart\common\model\Users as MUsers;
use wstmart\common\model\LogSms;
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
 * 商淘软件图形验证码
 */
class Captchas extends Base{

	public function install(){
		Db::startTrans();
		try{
			$hooks = ['homeDocumentRegistSmsCaptcha','checkSendSmsCaptcha','homeDocumentUnbindSmsCaptcha','homeDocumentBindSmsCaptcha','homeDocumentVerfiySmsCaptcha','homeDocumentReBindSmsCaptcha','homeDocumentLoginSmsCaptcha','homeDocumentForgetSmsCaptcha','shopDocumentBindSmsCaptcha','shopDocumentUnbindSmsCaptcha','shopDocumentReBindSmsCaptcha','shopDocumentVerfiySmsCaptcha','supplierDocumentBindSmsCaptcha','supplierDocumentUnbindSmsCaptcha','supplierDocumentReBindSmsCaptcha','supplierDocumentVerfiySmsCaptcha',
			'mobileDocumentRegistSmsCaptcha','mobileDocumentLoginSmsCaptcha','mobileDocumentBindSmsCaptcha','mobileDocumentUnBindSmsCaptcha','mobileDocumentVerfiySmsCaptcha','mobileDocumentForgetSmsCaptcha',
		     'wechatDocumentRegistSmsCaptcha','wechatDocumentLoginSmsCaptcha','wechatDocumentBindSmsCaptcha','wechatDocumentUnBindSmsCaptcha','wechatDocumentVerfiySmsCaptcha','wechatDocumentForgetSmsCaptcha'];
			$this->bindHoods("Captcha", $hooks);
            //管理员菜单
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'圖形驗證碼'],
                2=>['menuName'=>'图形验证码'],
                3=>['menuName'=>'Graphic verification code'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>2,"menuSort"=>1,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"captcha",'menuIcon'=>'th']);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $menuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $menuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('menus_langs')->insertAll($datas);

			if($menuId!==false){
                $privilegeLangParams = [
                    1=>[
                        'privilegeName_00'=>'查看圖形驗證碼',
                        'privilegeName_01'=>'新增圖形驗證碼',
                        'privilegeName_02'=>'編輯圖形驗證碼',
                        'privilegeName_03'=>'刪除圖形驗證碼'
                    ],
                    2=>[
                        'privilegeName_00'=>'查看图形验证码',
                        'privilegeName_01'=>'新增图形验证码',
                        'privilegeName_02'=>'编辑图形验证码',
                        'privilegeName_03'=>'删除图形验证码'
                    ],
                    3=>[
                        'privilegeName_00'=>'View graphic verification code',
                        'privilegeName_01'=>'Add graphic verification code',
                        'privilegeName_02'=>'Edit graphic verification code',
                        'privilegeName_03'=>'Delete graphic verification code'
                    ],
                ];
                $privilegeIds = [];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"ADDON_CAPTCHA_00","isMenuPrivilege"=>1,"privilegeUrl"=>"/addon/captcha-admin-index","otherPrivilegeUrl"=>"/addon/captcha-admin-pageQuery","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'00'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"ADDON_CAPTCHA_01","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/captcha-admin-add","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'01'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"ADDON_CAPTCHA_02","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/captcha-admin-edit","otherPrivilegeUrl"=>"/addon/captcha-admin-getById","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'02'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"ADDON_CAPTCHA_03","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/captcha-admin-del,/addon/captcha-admin-batchDel","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'03'];
                $datas = [];
                for($i=0;$i<count($privilegeIds);$i++){
                    foreach (WSTSysLangs() as $key => $v) {
                        $data = [];
                        $data['privilegeId'] = $privilegeIds[$i]['privilegeId'];
                        $data['langId'] = $v['id'];
                        $data['privilegeName'] = $privilegeLangParams[$v['id']]['privilegeName_'.$privilegeIds[$i]['code']];
                        $datas[] = $data;
                    }
                }
                Db::name('privileges_langs')->insertAll($datas);
			}
			$this->changgeEnableStatus(1);
			//新增上传目录
            $dataLangParams = [
                1=>['dataName'=>'圖形驗證碼'],
                2=>['dataName'=>'图形验证码'],
                3=>['dataName'=>'Graphic verification code'],
            ];
            $datas = [];
            $dataId = Db::name('datas')->insertGetId(['catId'=>3,'dataVal'=>'captcha']);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['dataId'] = $dataId;
                $data['langId'] = $v['id'];
                $data['dataName'] = $dataLangParams[$v['id']]['dataName'];
                $datas[] = $data;
            }
            Db::name('datas_langs')->insertAll($datas);
			installSql("captcha");
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}
	public function uninstall(){
		Db::startTrans();
		try{
			$hooks = ['homeDocumentRegistSmsCaptcha','checkSendSmsCaptcha','homeDocumentUnbindSmsCaptcha','homeDocumentBindSmsCaptcha','homeDocumentVerfiySmsCaptcha','homeDocumentReBindSmsCaptcha','homeDocumentLoginSmsCaptcha','homeDocumentForgetSmsCaptcha','shopDocumentBindSmsCaptcha','shopDocumentUnbindSmsCaptcha','shopDocumentReBindSmsCaptcha','shopDocumentVerfiySmsCaptcha','supplierDocumentBindSmsCaptcha','supplierDocumentUnbindSmsCaptcha','supplierDocumentReBindSmsCaptcha','supplierDocumentVerfiySmsCaptcha',
			'mobileDocumentRegistSmsCaptcha','mobileDocumentLoginSmsCaptcha','mobileDocumentBindSmsCaptcha','mobileDocumentUnBindSmsCaptcha','mobileDocumentVerfiySmsCaptcha','mobileDocumentForgetSmsCaptcha',
		     'wechatDocumentRegistSmsCaptcha','wechatDocumentLoginSmsCaptcha','wechatDocumentBindSmsCaptcha','wechatDocumentUnBindSmsCaptcha','wechatDocumentVerfiySmsCaptcha','wechatDocumentForgetSmsCaptcha'];
			$this->unbindHoods("Captcha", $hooks);
            $dataId = Db::name('datas')->where(["dataVal"=>"captcha"])->value('id');
            Db::name('datas')->where(["dataVal"=>"captcha"])->delete();
            Db::name('datas_langs')->where(["dataId"=>$dataId])->delete();

            $menuId = Db::name('menus')->where(["menuMark"=>"captcha"])->value('menuId');
            Db::name('menus')->where(["menuMark"=>"captcha"])->delete();
            Db::name('menus_langs')->where(['menuId'=>$menuId])->delete();

            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","ADDON_CAPTCHA_%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","ADDON_CAPTCHA_%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();
			$this->changgeEnableStatus(0);
			uninstallSql("captcha");
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}

	public function changgeEnableStatus($status){
        Db::name('sys_configs')->where(['fieldCode'=>'isAddonCaptcha'])->update(['fieldValue'=>$status]);
	}

	/**
	 * 获取图形列表
	 */
	public function pageQuery(){
		$name = input('name');
		$isShow = (int)input('isShow',-1);
		$where = [];
		$where[] = ['dataFlag','=',1];
		if($name!='')$where[] = ['title','like','%'.$name.'%'];
		$page = Db::name('addon_captchas')
		->where($where)
		->order('id desc')
		->paginate(input('pagesize/d'))->toArray();
		return $page;
	}

	/**
	 * 新增图形码
	 */
	public function add(){
		$no = (int)input('no',0);
		if($no<=0)return WSTReturn(lang("captcha_invalid_operation"));
		Db::startTrans();
        try{
			for($i=0; $i<$no;$i++) {
				$title = input('title_'.$i);
				$imgPath = input('imgPath_'.$i);
				if($title=='' || $imgPath=='')continue;
				$data = [];
	            $data['title'] = $title;
	            $data['imgPath'] = $imgPath;
	            $id = Db::name('addon_captchas')->insertGetId($data);
	            //启用上传图片
				WSTUseResource(1, $id, $data['imgPath']);
				Db::commit();
			}
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang("captcha_operation_fail"));
        }
		return WSTReturn(lang("captcha_operation_success"),1);
	}

	/**
	 * 删除
	 */
	public function del(){
	    $id = (int)input('post.id/d');
	    Db::startTrans();
		try{
		    $result = Db::name('addon_captchas')->where(['id'=>$id])->update(['dataFlag'=>-1]);
		    WSTUnuseResource('addon_captchas','imgPath',$id);
	        if(false !== $result){
	        	Db::commit();
	        	return WSTReturn(lang("captcha_operation_success"), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang("captcha_operation_fail"),-1);
        }
	}

	/**
	 * 批量删除
	 */
	public function batchDel(){
		$ids = WSTFormatIn(',',input('post.ids'),false);
	    Db::startTrans();
		try{
			foreach ($ids as $key => $id) {
				$result = Db::name('addon_captchas')->where(['id'=>$id])->update(['dataFlag'=>-1]);
		        WSTUnuseResource('addon_captchas','imgPath',$id);
			}
		    Db::commit();
	        return WSTReturn(lang("captcha_operation_success"), 1);
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang("captcha_operation_fail"),-1);
        }
	}
	/**
	 * 获取记录
	 */
	public function getById(){
        $id = (int)input('post.id/d');
        $rs = Db::name('addon_captchas')->where(['id'=>$id,'dataFlag'=>1])->find();
        return WSTReturn('',1,$rs);
	}

	/**
	 * 编辑
	 */
	public function edit(){
		$id = (int)input('id');
		$data = [];
		$data['title'] = input('title');
		if($data['title']=='')return WSTReturn(lang("captcha_require_picture_explain"));
		$data['imgPath'] = input('imgPath');
		if($data['imgPath']=='')return WSTReturn(lang("captcha_require_upload_pic"));
		$data['dataFlag'] = 1;
		Db::startTrans();
        try{
            $id = Db::name('addon_captchas')->where(['id'=>$id,'dataFlag'=>1])->update($data);
	        //启用上传图片
			WSTUseResource(1, $id, $data['imgPath']);
			Db::commit();
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang("captcha_operation_fail"));
        }
        return WSTReturn(lang("captcha_operation_success"),1);
	}

    /***********电脑端*************/
	/**
	 * 获取随机的验证码
	 */
	public function getCaptcha(){
		$rs = Db::name('addon_captchas')->where(['dataFlag'=>1])->field('title,imgPath')->orderRaw('rand()')->limit(9)->select();
		$chk = rand(0,8);
		if(count($rs)==0 || count($rs)<$chk)return WSTReturn(lang("captcha_get_pic_fail"),-1);
		$imgs = [];
		$title = '';
		foreach ($rs as $key => $v) {
			$code = md5($v['imgPath']."_".date('Ymdhis')."_".$chk);
			if($key==$chk){
				session('addon_captcha_code',$code);
				session('addon_captcha_text',$v['title']);
			}
			$imgs[] = ['imgPath'=>$v['imgPath'],'code'=>$code];
		}
		return WSTReturn('',1,['list'=>$imgs]);
	}

	/**
	 * 对比注册验证码是否正确
	 */
	public function checkRegistCaptcha(){
		$code = input('code');
		$captcha_code = session('addon_captcha_code');
		if($code!=$captcha_code){
			return WSTReturn(lang("captcha_select_pic_error"));
		}else{
			$userPhone = input("post.userPhone");
			$areaCode = (int)input("post.areaCode");
			$rs = array();
			if(!WSTIsPhone($userPhone))return WSTReturn(lang("captcha_phone_format_wrong"));
			$m = new MUsers();
			$rs = $m->checkUserPhone($userPhone);
			if($rs["status"]!=1)return WSTReturn(lang("captcha_phone_exist"));
			$phoneVerify = rand(100000,999999);
	        $rv = ['status'=>-1,'msg'=>lang("captcha_sms_send_fail")];
	        $tpl = WSTMsgTemplates('PHONE_USER_REGISTER_VERFIY');
	        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	            $params = ['tpl'=>$tpl,'params'=>['MALL_NAME'=>WSTConf("CONF.mallName"),'VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
	            $m = new LogSms();
	            $rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'getPhoneVerify',$phoneVerify);
	        }
			if($rv['status']==1){
				session('VerifyCode_userPhone_Verify',$phoneVerify);
				session('VerifyCode_userPhone',$userPhone);
				session('VerifyCode_areaCode',$areaCode);
				session('VerifyCode_userPhone_Time',time());
				return WSTReturn(lang("captcha_pic_check_success"),1);
			}
			return $rv;
		}
	}

	/**
	 * 对比解除绑定验证码是否正确
	 */
	public function checkUnbindCaptcha($uId=0){
	    $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$code = input('code');
		$captcha_code = session('addon_captcha_code');
		if($code!=$captcha_code){
			return WSTReturn(lang("captcha_select_pic_error"));
		}else{
			$m = new MUsers();
	    	$data = $m->getById($userId);
	    	$userPhone = $data['userPhone'];
	    	$areaCode = (int)$data['areaCode'];
	    	$phoneVerify = rand(100000,999999);
	        $rv = ['status'=>-1,'msg'=>lang("captcha_sms_send_fail")];
	        $tpl = WSTMsgTemplates('PHONE_EDIT');
	        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	            $params = ['tpl'=>$tpl,'params'=>['LOGIN_NAME'=>$data['loginName'],'VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
	            $m = new LogSms();
	            $rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'getPhoneVerifyt',$phoneVerify);
	        }
	     	if($rv['status']==1){
		    	$USER = [];
		    	$USER['userPhone'] = $userPhone;
		    	$USER['areaCode'] = $areaCode;
		    	$USER['phoneVerify'] = $phoneVerify;
		    	session('Verify_info2',$USER);
		    	session('Verify_userPhone_Time2',time());
		    	return WSTReturn(lang("captcha_pic_check_success"),1);
	    	}
			return $rv;
		}
	}

	/**
	 * 对比再次绑定验证码是否正确
	 */
	public function checkReBindCaptcha($uId=0){
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$code = input('code');
		$captcha_code = session('addon_captcha_code');
		if($code!=$captcha_code){
			return WSTReturn(lang("captcha_select_pic_error"));
		}else{
			$userPhone = input("post.userPhone");
			$areaCode = (int)input("post.areaCode");
	    	if(!WSTIsPhone($userPhone)){
	    		return WSTReturn(lang("captcha_phone_format_wrong"));
	    		exit();
	    	}
	    	$rs = array();
	    	$m = new MUsers();
	    	$rs = WSTCheckLoginKey($userPhone,(int)session('WST_USER.userId'));
	    	if($rs["status"]!=1){
	    		return WSTReturn(lang("captcha_phone_exist"));
	    		exit();
	    	}
	        $data = $m->getById(session('WST_USER.userId'));
	    	$phoneVerify = rand(100000,999999);
	        $rv = ['status'=>-1,'msg'=>lang("captcha_sms_send_fail")];
	        $tpl = WSTMsgTemplates('PHONE_EDIT');
	        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	            $params = ['tpl'=>$tpl,'params'=>['LOGIN_NAME'=>$data['loginName'],'VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
	            $m = new LogSms();
	            $rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'getPhoneVerifyo',$phoneVerify);
	        }
	     	if($rv['status']==1){
		    	$USER = [];
		    	$USER['userPhone'] = $userPhone;
		    	$USER['areaCode'] = $areaCode;
		    	$USER['phoneVerify'] = $phoneVerify;
		    	session('Verify_info',$USER);
		    	session('Verify_userPhone_Time',time());
		    	return WSTReturn(lang("captcha_pic_check_success"),1);
	    	}
			return $rv;
		}
	}

	/**
	 * 验证已绑定的手机号[忘记支付密码]
	 */
	public function checkVerfiyCaptcha($uId=0){
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$code = input('code');
		$captcha_code = session('addon_captcha_code');
		if($code!=$captcha_code){
			return WSTReturn(lang("captcha_select_pic_error"));
		}else{
			$m = new MUsers();
	    	$data = $m->getById($userId);
	    	$userPhone = $data['userPhone'];
	    	$areaCode = (int)$data['areaCode'];
	    	$phoneVerify = rand(100000,999999);
	    	$rv = ['status'=>-1,'msg'=>lang("captcha_sms_send_fail")];
	    	$tpl = WSTMsgTemplates('PHONE_FOTGET_PAY');
	    	if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	    		$params = ['tpl'=>$tpl,'params'=>['LOGIN_NAME'=>$data['loginName'],'VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
	    		$m = new LogSms();
	    		$rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'getPhoneVerifyt',$phoneVerify);
	    	}
	    	if($rv['status']==1){
	    		$USER = [];
	    		$USER['userPhone'] = $userPhone;
	    		$USER['areaCode'] = $areaCode;
	    		$USER['phoneVerify'] = $phoneVerify;
	    		session('Verify_backPaypwd_info',$USER);
	    		session('Verify_backPaypwd_Time',time());
	    		return WSTReturn(lang("captcha_pic_check_success"),1);
	    	}
	    	return $rv;
	    }
		return $rv;
	}

	/**
	 * 对比绑定验证码是否正确
	 */
	public function checkBindCaptcha($uId=0){
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$code = input('code');
		$captcha_code = session('addon_captcha_code');
		if($code!=$captcha_code){
			return WSTReturn(lang("captcha_select_pic_error"));
		}else{
			$userPhone = input("post.userPhone");
			$areaCode = (int)input("post.areaCode");
	        if(!WSTIsPhone($userPhone)){
	            return WSTReturn(lang("captcha_phone_format_wrong"));
	            exit();
	        }
	        $rs = array();
	        $m = new MUsers();
	        $rs = WSTCheckLoginKey($userPhone,$userId);
	        if($rs["status"]!=1){
	            return WSTReturn(lang("captcha_phone_exist"));
	            exit();
	        }
	        $data = $m->getById($userId);
	        $phoneVerify = rand(100000,999999);
	        $rv = ['status'=>-1,'msg'=>lang("captcha_sms_send_fail")];
	        $tpl = WSTMsgTemplates('PHONE_BIND');
	        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	            $params = ['tpl'=>$tpl,'params'=>['LOGIN_NAME'=>$data['loginName'],'VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
	            $m = new LogSms();
	            $rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'getPhoneVerifyb',$phoneVerify);
	        }
	     	if($rv['status']==1){
		    	$USER = [];
		    	$USER['userPhone'] = $userPhone;
		    	$USER['areaCode'] = $areaCode;
		    	$USER['phoneVerify'] = $phoneVerify;
		    	session('Verify_info',$USER);
		    	session('Verify_userPhone_Time',time());
		    	return WSTReturn(lang("captcha_pic_check_success"),1);
	    	}
			return $rv;
		}
	}

	/**
	 * 登录之前验证
	 */
	public function checkLoginCaptcha(){
		$code = input('code');
		$captcha_code = session('addon_captcha_code');
		if($code!=$captcha_code){
			return WSTReturn(lang("captcha_select_pic_error"));
		}else{
			$userPhone = input("post.userPhone");
			$areaCode = (int)input("post.areaCode");
	        if(!WSTIsPhone($userPhone)){
	            return WSTReturn(lang("captcha_phone_format_wrong"));
	        }
	        $m = new MUsers();
	        $rs = $m->checkUserPhone($userPhone);
	        if($rs["status"]==1){
	            return WSTReturn(lang("captcha_phone_not_regist"));
	        }
	        $phoneVerify = rand(100000,999999);
	        $rv = ['status'=>-1,'msg'=>lang("captcha_sms_send_fail")];
	        $tpl = WSTMsgTemplates('PHONE_COMMON_VERFIY');
	        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	            $params = ['tpl'=>$tpl,'params'=>['VERFIY_CODE'=>$phoneVerify]];
	            $m = new LogSms();
	            $rv = $m->sendSMS(0,$areaCode.$userPhone,$params,'getPhoneVerify',$phoneVerify);
	        }
	        if($rv['status']==1){
	            session('VerifyCode_userPhone2',$userPhone);
	            session('VerifyCode_areaCode2',$areaCode);
	            session('VerifyCode_userPhone_Verify2',$phoneVerify);
	            session('VerifyCode_userPhone_Time2',time());
	            return WSTReturn(lang("captcha_pic_check_success"),1);
	        }
	        return $rv;
	    }
	}

    /**
     * 验证已绑定的手机号[忘记登录密码]
     */
    public function checkForgetCaptcha(){
        $code = input('code');
        $captcha_code = session('addon_captcha_code');
        if ($code != $captcha_code) {
            return WSTReturn(lang("captcha_select_pic_error"));
        } else {
            session('WST_USER',session('findPass.userId'));
            if(session('findPass.userPhone')==''){
                return WSTReturn(lang("captcha_verify_no_phone"),-1);
            }
            $phoneVerify = rand(100000,999999);
            session('WST_USER',null);
            $rv = ['status'=>-1,'msg'=>lang("captcha_sms_send_fail")];
            $tpl = WSTMsgTemplates('PHONE_FOTGET');
            if( $tpl['tplContent']!='' && $tpl['status']=='1'){
                $params = ['tpl'=>$tpl,'params'=>['VERFIY_CODE'=>$phoneVerify,'VERFIY_TIME'=>10]];
                $m = new LogSms();
                $rv = $m->sendSMS(0,session('findPass.areaCode').session('findPass.userPhone'),$params,'getPhoneVerify',$phoneVerify);
            }
            if($rv['status']==1){
        		// 记录发送短信的时间,用于验证是否过期
                session('REST_Time',time());
                $USER = [];
                $USER['phoneVerify'] = $phoneVerify;
                $USER['time'] = time();
                session('findPhone',$USER);
                return WSTReturn(lang("captcha_pic_check_success"),1);
            }
            return $rv;
        }
    }

}
