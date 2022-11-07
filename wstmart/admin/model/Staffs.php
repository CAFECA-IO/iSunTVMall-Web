<?php
namespace wstmart\admin\model;
use wstmart\admin\model\Roles;
use wstmart\admin\model\LogStaffLogins;
use wstmart\admin\validate\Staffs as validate;
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
 * 职员业务处理
 */
class Staffs extends Base{
	protected $pk = 'staffId';
	/**
	 * 判断用户登录帐号密码
	 */
	public function checkLogin(){
		$loginName = input("post.loginName");
		$loginPwd = input("post.loginPwd");
		$code = input("post.verifyCode");
		if(!WSTVerifyCheck($code)){
			return WSTReturn(lang('staffs_err1'));
		}
		$decrypt_data = WSTRSA($loginPwd);
		if($decrypt_data['status']==1){
			$loginPwd = $decrypt_data['data'];
		}else{
            return WSTReturn(lang('staffs_err2'));
		}
		hook("beforeAdminLogin",["data"=>input()]);
		$staff = $this->where(['loginName'=>$loginName,'staffStatus'=>1,'dataFlag'=>1])->find();
		if(empty($staff))return WSTReturn(lang('staffs_err3'));
		if($staff['loginPwd']==md5($loginPwd.$staff['secretKey'])){
	 		$staff->lastTime = date('Y-m-d H:i:s');
	 		$staff->lastIP = request()->ip();
	 		$staff->save();
	 		//记录登录日志
		 	LogStaffLogins::create([
		 	     'staffId'=>$staff['staffId'],
		 	     'loginTime'=> date('Y-m-d H:i:s'),
		 	     'loginIp'=>request()->ip()
		 	]);
	 		//获取角色权限
	 		$role = Roles::get(['dataFlag'=>1,'roleId'=>$staff['staffRoleId']]);
	 		$staff['roleName'] = $role['roleName'];
	 		if($staff['staffId']==1){
	 			$staff['privileges'] = Db::name('privileges')->where(['dataFlag'=>1])->column('privilegeCode');
	 			$staff['menuIds'] = Db::name('menus')->where('dataFlag',1)->column('menuId');
	 		}else{
		 		$staff['privileges'] = ($role['privileges']!='')?explode(',',$role['privileges']):[];
		 		$staff['menuIds'] = [];
		 		//获取管理员拥有的菜单
		 		if(!empty($staff['privileges'])){
		 		     $menus = Db::name('menus')->alias('m')->join('__PRIVILEGES__ p','m.menuId=p.menuId and p.dataFlag=1','inner')
		 		                ->where([['p.privilegeCode','in',$staff['privileges']]])->field('m.menuId')->select();
		 		     $menuIds = [];
		 		     if(!empty($menus)){
		 		     	foreach ($menus as $key => $v){
		 		     		$menuIds[] = $v['menuId'];
		 		     	}
		 		     	$staff['menuIds'] = $menuIds;
		 		     }
		 		}
	 		}
	 		session("WST_STAFF",$staff);
			return WSTReturn("",1,$staff);
		}
		return WSTReturn(lang('staffs_err3'));
	}
	
    /**
	 * 分页
	 */
	public function pageQuery(){
		$key = input('key');
		$where = [];
		$where[] = ['s.dataFlag','=',1];
		if($key!='')$where[] = ['loginName|staffName|staffNo','like','%'.$key.'%'];
		return Db::name('staffs')->alias('s')->join('__ROLES__ r','s.staffRoleId=r.roleId and r.dataFlag=1','left')
		       ->where($where)->field('staffId,staffName,loginName,workStatus,staffNo,lastTime,lastIP,roleName')
		       ->order('staffId', 'desc')->paginate(input('limit/d'));
	}
	/**
	 * 删除
	 */
    public function del(){
    	$staffId = (int)session('WST_STAFF.staffId');
	    $id = (int)input('post.id/d');
	    if($staffId==$id)return WSTReturn(lang('staffs_err4'),-1);
	    if($id==1)return WSTReturn(lang('staffs_err5'),-1);
		$data = [];
		$data['dataFlag'] = -1;
		Db::startTrans();
		try{
		    $result = $this->update($data,['staffId'=>$id]);
	        if(false !== $result){
	        	WSTUnuseResource('staffs','staffPhoto',$id);
	        	Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }
	}
	
	/**
	 * 获取角色权限
	 */
	public function getById($id){
		return $this->get(['dataFlag'=>1,'staffId'=>$id]);
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['secretKey'] = rand(1000,9999);
		$data["staffFlag"] = 1;
		$data["createTime"] = date('Y-m-d H:i:s');
		Db::startTrans();
		try{
		   $validate = new validate();
		   if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
		   $data["loginPwd"] = md5(input("post.loginPwd").$data["secretKey"]);
		   $result = $this->allowField(true)->save($data);
		   if(false !== $result){
		      WSTUseResource(1, $this->staffId,$data['staffPhoto']);
		      Db::commit();
              return WSTReturn(lang('op_ok'), 1);
		   }
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = input('post.staffId/d');
		$data = input('post.');
		WSTUnset($data, 'staffId,loginPwd,secretKey,dataFlag,createTime,lastTime,lastIP');
		Db::startTrans();
		try{
			WSTUseResource(1, $id,$data['staffPhoto'],'staffs','staffPhoto');
			$validate = new validate();
		    if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
		    $result = $this->allowField(true)->save($data,['staffId'=>$id]);
	        if(false !== $result){
		        Db::commit();
		        if(session('WST_STAFF.staffId')==$id){
		        session('WST_STAFF.staffPhoto',$data['staffPhoto']);
		        session('WST_STAFF.staffName',$data['staffName']);
		    }
	        	return WSTReturn(lang('op_ok'), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }
	}
	
	/**
	 * 检测账号是否重复
	 */
	public function checkLoginKey($key){
		$rs = $this->where(['loginName'=>$key,'dataFlag'=>1])->count();
		return ($rs==0)?WSTReturn(lang('staffs_info1'), 1):WSTReturn(lang('staffs_err6'));
	}
	/**
	 * 修改自己密码
	 */
	public function editMyPass($staffId){
		$rs = WSTCheckPwdRule(input("post.newPass"));
		if(!($rs===true)){
			return WSTReturn($rs);
		}
		$staff = $this->where('staffId',$staffId)->field('secretKey,loginPwd')->find();
		if(empty($staff))return WSTReturn(lang('op_err'));
		$srcPass = md5(input("post.srcPass").$staff["secretKey"]);
		if($srcPass!=$staff['loginPwd'])return WSTReturn(lang('staffs_err7'));
		$staff->loginPwd = md5(input("post.newPass").$staff["secretKey"]);
		$result = $staff->save();
        if(false !== $result){
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
   /**
	 * 修改职员密码
	 */
	public function editPass($staffId){
		$rs = WSTCheckPwdRule(input("post.newPass"));
		if(!($rs===true)){
			return WSTReturn($rs);
		}
		$staff = $this->where('staffId',$staffId)->field('secretKey')->find();
		if(empty($staff))return WSTReturn(lang('op_err'));
		$staff->loginPwd = md5(input("post.newPass").$staff["secretKey"]);
		$result = $staff->save();
        if(false !== $result){
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}

	/**
	 * 获取所有的职员列表
	 */
	public function listQuery(){
		$where = [];
		$where['dataFlag'] = 1;
		$where['staffStatus'] = 1;
		return Db::name('staffs')->where($where)->field('staffId,staffName')
		       ->order('staffName', 'asc')->select();
	}
}
