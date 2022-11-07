<?php
namespace wstmart\supplier\model;
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
 * 门店管理员类
 */
class SupplierUsers extends Base{
	/**
	 * 角色列表
	 */
	public function pageQuery(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$userName = input("userName/s");
		$where = [
            ["s.supplierId",'=',$supplierId],
            ["s.dataFlag",'=',1]
        ];
		if($userName != ""){
			$where[] = ["loginName","like","%".$userName."%"];
		}
		$page = $this->alias('s')
	    		->join("__SUPPLIER_ROLES__ r","s.roleId=r.id and r.dataFlag=1","LEFT")
	    		->join("__USERS__ u", "u.userId=s.userId and u.dataFlag=1")
	    		->field('s.id,s.userId,s.supplierId,s.roleId,u.userName,u.loginName,u.createTime,u.userStatus,r.roleName')
				->order('s.id desc')
				->where($where)
				->paginate(input('limit/d'))->toArray();
		return $page;
	}

	public function listSupplierUsers(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$userName = input("userName/s");
		$where = [];
		$where[] = ["s.supplierId","=",$supplierId];
		$where[] = ["s.dataFlag","=",1];
		if($userName != ""){
			$where[] = ["loginName","like","%".$userName."%"];
		}
		$rs = $this->alias('s')
	    		->join("__SUPPLIER_ROLES__ r","s.roleId=r.id and r.dataFlag=1","LEFT")
	    		->join("__USERS__ u", "u.userId=s.userId and u.dataFlag=1")
	    		->field('s.id,s.supplierId,s.roleId,s.userId,u.userName,u.loginName,u.createTime,u.userStatus,r.roleName')
				->where($where)
				->select();
		return $rs;
	}

	/**
	*  根据id获取供货商用户
	*/
	public function getById(){
		$id = (int)input('id');
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
	    $user = $this->alias('s')
	    		->join("__SUPPLIER_ROLES__ r","s.roleId=r.id","LEFT")
	    		->join("__USERS__ u", "u.userId=s.userId and u.dataFlag=1")
	    		->field('s.id,s.supplierId,s.roleId,u.userName,u.loginName,u.createTime,u.userStatus,r.roleName')
				->where([["s.id",'=',$id],["s.supplierId",'=',$supplierId],["s.dataFlag",'=',1]])
				->find();
		return $user;
	}

	
	/**
     * 新增供货商用户
     */
    public function add(){
    	$data = array();
    	$roleId = (int)input("roleId");
    	$data['loginName'] = input("post.loginName");
    	$data['loginPwd'] = input("post.loginPwd");
    	$data['reUserPwd'] = input("post.reUserPwd");
    	$loginName = $data['loginName'];
    	if($roleId<=0){
    		return WSTReturn(lang("op_illegal"));
    	}
    	//检测账号是否存在
    	$crs = WSTCheckLoginKey($loginName);
    	if($crs['status']!=1)return $crs;
    	$decrypt_data = WSTRSA($data['loginPwd']);
    	$decrypt_data2 = WSTRSA($data['reUserPwd']);
    	if($decrypt_data['status']==1 && $decrypt_data2['status']==1){
    		$data['loginPwd'] = $decrypt_data['data'];
    		$data['reUserPwd'] = $decrypt_data2['data'];
    	}else{
    		return WSTReturn(lang("op_err"));
    	}
    	if($data['loginPwd']!=$data['reUserPwd']){
    		return WSTReturn(lang("repwd_diff"));
    	}
    	foreach ($data as $v){
    		if($v ==''){
    			return WSTReturn(lang("incomplete_information"));
    		}
    	}
    	if($loginName=='')return WSTReturn(lang("op_err"));//分派不了登录名
    
    	unset($data['reUserPwd']);
    	//检测账号，邮箱，手机是否存在
    	$data["loginSecret"] = rand(1000,9999);
    	$data['loginPwd'] = md5($data['loginPwd'].$data['loginSecret']);
    	$data['userName'] = input("post.userName");
    	$data['userQQ'] = "";
    	$data['userScore'] = 0;
    	$data['createTime'] = date('Y-m-d H:i:s');
    	$data['dataFlag'] = 1;
    	$data['userType'] = 3;
    	Db::startTrans();
        try{
	    	$userId = Db::name("users")->insertGetId($data);
	    	if(false !== $userId){
	    		//添加门店用户
	    		$supplierId = (int)session('WST_SUPPLIER.supplierId');
	    		$data = array();
	    		$data["supplierId"] = $supplierId;
	    		$data["userId"] = $userId;
	    		$data["roleId"] = (int)input("roleId");
	    		Db::name('supplier_users')->insert($data);
	    		$user = model("common/Users")->get($userId);
                //发送消息
                $tpl = WSTMsgTemplates('USER_REGISTER');
                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
                    $find = ['${LOGIN_NAME}','${MALL_NAME}'];
                    $replace = [$user['loginName'],WSTConf('CONF.mallName')];
                    WSTSendMsg($userId,str_replace($find,$replace,$tpl['tplContent']),['from'=>0,'dataId'=>0]);
                }

	    		Db::commit();
	    		return WSTReturn(lang("op_ok"),1);
	    	}
        }catch (\Exception $e) {
        	Db::rollback();
        }
    	return WSTReturn(lang("op_err"));
    }

	/**
     * 修改供货商用户
     */
    public function edit(){

    	$supplierId = (int)session('WST_SUPPLIER.supplierId');
    	Db::startTrans();
		try{
	    	$data = array();
	    	$roleId = (int)input("post.roleId");
	    	$id = (int)input("post.id");
	    	$newPass = input("post.newPass/s");
	    	if($newPass!=""){
	    		$decrypt_data = WSTRSA($newPass);
		    	if($decrypt_data['status']==1){
		    		$newPass = $decrypt_data['data'];
		    	}else{
		    		return WSTReturn(lang("op_err"));
		    	}
		    	if(!$newPass){
		    		return WSTReturn(lang("password_cannot_be_empty"),-1);
		    	}
		    	$roleUser = $this->where(["id"=>$id,"supplierId"=>$supplierId])->find();
		    	$userId = $roleUser["userId"];
		    	$rs = model("users")->where(["userId"=>$userId])->find();
		    	//核对密码
		  
				$oldPass = input("post.oldPass");
				$decrypt_data2 = WSTRSA($oldPass);
				if($decrypt_data2['status']==1){
					$oldPass = $decrypt_data2['data'];
				}else{
					return WSTReturn(lang("op_err"));
				}
				if($rs['loginPwd']==md5($oldPass.$rs['loginSecret'])){
					
						$data["loginPwd"] = md5($newPass.$rs['loginSecret']);
						$rs = model("users")->update($data,['userId'=>$userId]);
						if(false !== $rs){
							$this->where([['roleId','>',0],['id','=',$id],['supplierId','=',$supplierId]])->update(["roleId"=>$roleId]);
						}else{
							return WSTReturn(lang("op_err"), -1);
						}
						Db::commit();
				 		return WSTReturn(lang("op_ok"), 1);
			       
				}else{
					return WSTReturn(lang("original_password_error"),-1);
				}
	    	}else{
	    		$this->where([['roleId','>',0],['id','=',$id],['supplierId','=',$supplierId]])->update(["roleId"=>$roleId]);
	    		Db::commit();
				return WSTReturn(lang("op_ok"), 1);
	    	}
		}catch (\Exception $e) {
        	Db::rollback();
        }
    }

	

	/**
	 * 删除供货商用户
	 */
	public function del(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$userId = (int)session('WST_SUPPLIER.userId');
		$id = input('post.id/d');
		$data = [];
		$data['dataFlag'] = -1;
		Db::startTrans();
		try{
			$role = $this->where([["id",'=',$id],["supplierId",'=',$supplierId]])->field("userId,id")->find();
			if($userId==$role['userId'])return WSTReturn(lang("cannot_del_self"));
			$result = $this->where([["id",'=',$id],["supplierId",'=',$supplierId],["roleId",'>',0]])->update($data);
		   	if(false !== $result){
		   		Db::name("users")->where([["userId",'=',$role["userId"]]])->update(['dataFlag'=>-1]);
		   		Db::commit();
	        	return WSTReturn(lang("op_ok"), 1);
	        }
     	}catch (\Exception $e) {
        	Db::rollback();
        }
        return WSTReturn(lang("op_err"),-1);
	}

	/**
     * 根据ID获取店员信息
     */
    public function getFieldsById($id,$fields){
    	$supplierId = (int)session('WST_SUPPLIER.supplierId');
    	return $this->where(['id'=>$id,'supplierId'=>$supplierId,'dataFlag'=>1])->field($fields)->find();
    }
    /**
     * 根据ID获取店员信息
     */
    public function editNotifyConfig(){
    	$supplierId = (int)session('WST_SUPPLIER.supplierId');
    	$id = (int)input('id');
    	$data = [];
    	$data['privilegeMsgs'] = input("privilegeMsgs/s");
    	$data['privilegeMsgTypes'] = input("privilegeMsgTypes/s");
    	$data['privilegePhoneMsgs'] = input("privilegePhoneMsgs/s");
    	$result = $this->where([["id",'=',$id],["supplierId",'=',$supplierId]])->update($data);
    	return WSTReturn(lang("op_ok"), 1);
    }
}
