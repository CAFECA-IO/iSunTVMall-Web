<?php
namespace addons\ucenter\controller;

use think\addons\Controller;
use addons\ucenter\model\Ucenter as M;
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
class Ucenter extends Controller{
	public function __construct(){
		parent::__construct();
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}
    
    /**
     * 绑定邮箱
     */
    public function emailEdit(){

    	$USER = session('WST_USER');
    	if(empty($USER) && $USER['userId']==''){
    		$this->redirect("home/users/login");
    	}
    	$bindTime = session('email.time');
    	$code = session('email.key');
    	$bindEmail = session('email.val');
 
    	$m = new M();
    	if(!$m->checkLoginPass())return WSTReturn(lang('ucenter_wrong_password'),-1);
    	 
    	if(time()>floatval($bindTime)+30*60){
    		return WSTReturn(lang('ucenter_the_verification_code_is_invalid'),-1);
    	}
    	$rs = WSTCheckLoginKey($bindEmail,(int)session('WST_USER.userId'));
    	
    	if($rs["status"]!=1){
    		return WSTReturn(lang('ucenter_mailbox_already_exists'),-1);
    	}
    	$secretCode = input('secretCode');
    	
    	if($code!=$secretCode)return WSTReturn(lang('ucenter_check_code_error'),-1);
    	
    	$m = new \wstmart\common\model\Users();
    	$rs = $m->editEmail((int)session('WST_USER.userId'),$bindEmail);
    	if($rs['status'] == 1){
    		// 清空session
    		session('email',null);
    		return WSTReturn(lang('ucenter_op_ok'),1);
    	}
    	return WSTReturn(lang('ucenter_failed_to_bind_mailbox'),-1);
    }
    
    
    
    /**
     * 修改邮箱
     */
    public function emailEditt(){
    	$USER = session('WST_USER');
    	if(empty($USER) && $USER['userId']!=''){
    		$this->redirect("home/users/login");
    	}
    	if(empty(session('checkEmailEditBind'))){
    		return WSTReturn(lang('ucenter_operation_failed_binding_failed'),-1);
    	}
    	$bindTime = session('email.time');
    	$code = session('email.key');
    	$bindEmail = session('email.val');
    	
    
    	if(time()>floatval($bindTime)+30*60){
    		return WSTReturn(lang('ucenter_verification_code_is_invalid'),-1);
    	}
    	$rs = WSTCheckLoginKey($bindEmail,(int)session('WST_USER.userId'));
    
    	if($rs["status"]!=1){
    		return WSTReturn(lang('ucenter_mailbox_already_exists'),-1);
    	}
    	$secretCode = input('secretCode');
    
    	if($code!=$secretCode)return WSTReturn(lang('ucenter_check_code_error'),-1);
    
    	$m = new \wstmart\common\model\Users();
    	$rs = $m->editEmail((int)session('WST_USER.userId'),$bindEmail);
    	if($rs['status'] == 1){
    		// 清空session
    		session('email',null);
    		session('checkEmailEditBind',null);
    		return WSTReturn(lang('ucenter_op_ok'),1);
    	}
    	
    	return WSTReturn(lang('ucenter_validation_failed'),-1);
    }
    
    /**
     * 修改邮箱第二步
     */
    public function editEmail2(){
    	if(!empty(session('checkEmailEditBind'))){
    		$this->assign('process','Two');
    		return $this->fetch('home/users/user_edit_email');
    	}else{
    		//获取用户信息
    		$userId = (int)session('WST_USER.userId');
    		$m = new \wstmart\common\model\Users();
    		$data = $m->getById($userId);
    		if($data['userEmail']!='')$data['userEmail'] = WSTStrReplace($data['userEmail'],'*',2,'@');
    		$process = 'One';
    		$this->assign('process',$process);
    		$this->assign('data',$data);
    		return $this->fetch('home/users/user_edit_email');
    	}
    	
    }
    
    /**
     * 修改邮箱第三步
     */
    public function editEmail3(){
    	$this->assign('process','Three');
    	return $this->fetch('home/users/user_edit_email');
    }
    
    
    /**
     * 查询并加载用户资料
     */
    public function checkEmailEdit(){
    	$m = new M();
    	if($m->checkLoginPass()){
    		$loginPwd = input("post.loginPwd");
    		session('checkEmailEditBind',$loginPwd);
    		return WSTReturn(lang('ucenter_op_ok'),1);
    	}else{
    		return WSTReturn(lang('ucenter_wrong_password'),-1);
    	}
    }
}