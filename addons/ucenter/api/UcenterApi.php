<?php

namespace addons\ucenter\api;

class UcenterApi {
	public function __construct() {
		include_once ('config_ucenter.php');
		include_once (dirname (dirname ( dirname ( dirname ( __File__ ) ) )) . '/api/uc_client/client.php');
	}
	// 更新用户资料
	public function ucedit($username, $oldpassword, $newpassword, $email,$ignoreoldpw=0) { // 如不修改为空
		
		$ucresult = uc_user_edit ( $username, $oldpassword, $newpassword, $email,$ignoreoldpw );
		if ($ucresult == - 1) {
			return lang('ucenter_the_old_password_is_incorrect');
		} elseif ($ucresult == - 4) {
			return lang('ucenter_email_format_error');
		} elseif ($ucresult == - 5) {
			return lang('ucenter_email_is_not_allowed_to_register');
		} elseif ($ucresult == - 6) {
			return lang('ucenter_the_email_has_been_registered');
		}
	}
	// 用户注册接口
	public function register($username, $password, $email) {
		$uid = uc_user_register ( $username, $password, $email );
		if ($uid <= 0) {
			if ($uid == - 1) {
				return lang('ucenter_the_user_name_is_illegal');
			} elseif ($uid == - 2) {
				return lang('ucenter_contains_words_that_are_not_allowed_to_register');
			} elseif ($uid == - 3) {
				return lang('ucenter_the_user_name_already_exists');
			} elseif ($uid == - 4) {
				return lang('ucenter_email_format_error');
			} elseif ($uid == - 5) {
				return lang('ucenter_email_is_not_allowed_to_register');
			} elseif ($uid == - 6) {
				return lang('ucenter_the_email_has_been_registered');
			} else {
				return lang('ucenter_undefined');
			}
		} else {
			return intval ( $uid );
		}
	}
	// 用户登录
	public function uclogin($username, $password) {
		$uids = uc_user_login ( $username, $password );
	
		if (count ( $uids ) > 0) {
			$uid = $uids [0];
			if ($uid > 0) {
				$data ['uid'] = $uid;
				$data ['username'] = $uids [1];
				$data ['password'] = $uids [2];
				$data ['email'] = $uids [3];
				return $data; // 返回用户信息
			} else if ($uid == - 1) {
				$data ['uid'] = - 2;
				return $data; // 用户不存在,或者被删除
			} elseif ($uid == - 2) {
				return lang('ucenter_wrong_password');
			} else {
				$data ['uid'] = - 2;
				return $data; // 未定义
			}
		}
		return $uids;
	}
	// 用户同步登录
	public function synlogin($uid) {
		$ucsynlogin = uc_user_synlogin ( $uid );
		return $ucsynlogin;
	}
	// 用户同步退出
	public function synlogout() {
		setcookie('Ucenter_auth','',time()-10000,'/','');//清除同步的cookie
		$ucsynlogout = uc_user_synlogout();
		return $ucsynlogout;
		
	}
	// 同步
	public function dislogin() {
		header ( 'P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"' );
		$uName = $_COOKIE ['Ucenter_auth'];
		$data = array (
			'uName' => $uName 
		);
		if ($uName && $uName != - 1) { // 登录
			$data ['type'] = 1;
			return $data;
		} else if ($uName == - 1) { // 退出
			$data ['type'] = - 1;
			return $data;
		} else {
			$data ['type'] = - 2;
			return $data;
		}
	}
	
	public function ucdelete($uid){
		uc_user_delete($uid);
	}
}
?>
