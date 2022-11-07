<?php
namespace wstmart\app\model;
use think\Db;
use think\Model;
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
 * 第三方登录业务处理类
 */
class ThirdLogin extends Model{
	private function doLogin($rs,$loginRemark){
		// 存在该用户,生成TokenId返回
		//记录登录信息
		$data = array();
		$data["userId"] = $rs['userId'];
		$data["loginTime"] = date('Y-m-d H:i:s');
		// 用户登录地址 $data["loginIp"] = get_client_ip();
		$data["loginIp"] = request()->ip();
		//登录来源、登录设备
		$data["loginSrc"] = 2;
		$data["loginRemark"] = $loginRemark;
		/**************** 记录登录日志  **************/
		Db::name('log_user_logins')->insert($data);
		//记录tokenId
		$m = Db::name('app_session');
		/*************************   制作key  **********************/
		$key = sprintf('%011d',$rs['userId']);
		$tokenId = $this->to_guid_string($key.time());
		$data = array();
		$data['userId'] = $rs['userId'];
		$data['tokenId'] = $tokenId;
		$data['startTime'] = date('Y-m-d H:i:s');
		$data['deviceId'] = input('deviceId');
		$m->insert($data);
		//删除上一条登录记录
		$m->where('tokenId!="'.$tokenId.'" and userId='.$rs['userId'])->delete();
		$rs['tokenId'] = $tokenId;
        hook('afterUserLogin',['user'=>&$rs,'isApp'=>1]);
		// 返回tokenId及用户数据
		return $rs;
	}


	/**
	 * 根據google的id檢測賬號是否存在
	 * @param $id google id
	 * @param $loginRemark 登錄來源 3:android 4:ios
	 */
	public function googleIsExists($id,$loginRemark){
		$rs = Db::name('users')->alias('u')
							   ->join('third_users tu','u.userId=tu.userId','inner')
							   ->field('u.userId,loginName,loginSecret,loginPwd,userName,userSex,userPhoto,userStatus,userScore,userType')
							   ->where([['thirdCode','=','google'],['thirdOpenId','=',$id],['u.dataFlag','=',1],['u.userStatus','<>',0]])
							   ->find();
		if(!empty($rs)){
			// 執行登錄
			return $this->doLogin($rs,$loginRemark);
		}
		return $rs;
	}
	/**
	 * 根據facebook的id檢測賬號是否存在
	 * @param $id facebook id
	 * @param $loginRemark 登錄來源 3:android 4:ios
	 */
	public function facebookIsExists($id,$loginRemark){
		$rs = Db::name('users')->alias('u')
							   ->join('third_users tu','u.userId=tu.userId','inner')
							   ->field('u.userId,loginName,loginSecret,loginPwd,userName,userSex,userPhoto,userStatus,userScore,userType')
							   ->where([['thirdCode','=','facebook'],['thirdOpenId','=',$id],['u.dataFlag','=',1],['u.userStatus','<>',0]])
							   ->find();
		if(!empty($rs)){
			// 執行登錄
			return $this->doLogin($rs,$loginRemark);
		}
		return $rs;
	}

	/**
	 * 根据unionId检测账号是否存在
	 * @param $unionId
	 * @param $loginRemark 登录来源 3:android 4:ios
	 */
	public function wechatIsExists($unionId,$loginRemark){
		$rs = Db::name('users')->field('userId,loginName,loginSecret,loginPwd,userName,userSex,userPhoto,userStatus,userScore,userType')
							   ->where([['wxUnionId','=',$unionId],['dataFlag','=',1],['userStatus','<>',0]])
							   ->find();
		if(!empty($rs)){
			// 执行登录
			return $this->doLogin($rs,$loginRemark);
		}
		return $rs;
	}
	/**
	 * 根据QQ的unionId检测账号是否存在
	 * @param $unionId
	 * @param $loginRemark 登录来源 3:android 4:ios
	 */
	public function qqIsExists($unionId,$loginRemark){
		$rs = Db::name('users')->alias('u')
							   ->join('third_users tu','u.userId=tu.userId','inner')
							   ->field('u.userId,loginName,loginSecret,loginPwd,userName,userSex,userPhoto,userStatus,userScore,userType')
							   ->where([['thirdCode','=','qq'],['thirdOpenId','=',$unionId],['u.dataFlag','=',1],['u.userStatus','<>',0]])
							   ->find();
		if(!empty($rs)){
			// 执行登录
			return $this->doLogin($rs,$loginRemark);
		}
		return $rs;
	}
	/**
	 * 根据支付宝的user_id检测账号是否存在
	 * @param $openId 支付宝user_id
	 * @param $loginRemark 登录来源 3:android 4:ios
	 */
	public function alipayIsExists($openId,$loginRemark){
		$rs = Db::name('users')->alias('u')
							   ->join('third_users tu','u.userId=tu.userId','inner')
							   ->field('u.userId,loginName,loginSecret,loginPwd,userName,userSex,userPhoto,userStatus,userScore,userType')
							   ->where([['thirdCode','=','alipay'],['thirdOpenId','=',$openId],['u.dataFlag','=',1],['u.userStatus','<>',0]])
							   ->find();
		if(!empty($rs)){
			// 执行登录
			return $this->doLogin($rs,$loginRemark);
		}
		return $rs;
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





}
