<?php
namespace wstmart\store\model;
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
 * 门店色务类
 */
class StoreRoles extends Base{
	/**
	 * 角色列表
	 */
	public function pageQuery(){
		$shopId = (int)session('WST_STORE.shopId');
		$storeId = (int)session('WST_STORE.storeId');
		$roleName = input("roleName/s");
        $where = [
            ["shopId",'=',$shopId],
            ["storeId",'=',$storeId],
            ["dataFlag",'=',1]
        ];
		if($roleName != ""){
			$where[] = ["roleName","like","%".$roleName."%"];
		}
		$page = $this
				->field('id,shopId,roleName,createTime')
		    	->where($where)
		    	->order('id desc')
		    	->paginate(input('limit/d'))->toArray();
		return $page;
	}

	public function listQuery(){
		$shopId = (int)session('WST_STORE.shopId');
		$storeId = (int)session('WST_STORE.storeId');
		$where = ["shopId"=>$shopId,"storeId"=>$storeId,"dataFlag"=>1];
		$list = $this
				->field('id,shopId,roleName,createTime')
		    	->where($where)
		    	->select();
		return $list;
	}
	/**
	*  根据id获取店铺角色
	*/
	public function getById($id){
		$shopId = (int)session('WST_STORE.shopId');
		$storeId = (int)session('WST_STORE.storeId');
	    $role = $this->field('id,shopId,roleName,createTime,privilegeUrls')
					->where(["id"=>$id,"shopId"=>$shopId,"storeId"=>$storeId,"dataFlag"=>1])
					->find();
		if(empty($role))return [];
		$menuList = json_decode($role["privilegeUrls"],true);
		$menuUrls = array();
		$menuOtherUrls = array();

		foreach ($menuList as $k1 => $menus1) {
			foreach ($menus1 as $k2 => $menus2) {
				$menuUrls = array_merge($menuUrls,$menus2["urls"]);
				$otherUrls = $menus2["otherUrls"];
				foreach ($otherUrls as $ko => $ourls) {
					$othurls = explode(',',$ourls);
					$menuOtherUrls = array_merge($menuOtherUrls,$othurls);
				}
			}
		}
		$role["menuUrls"] = array_filter($menuUrls);
		$role["menuOtherUrls"] = array_filter($menuOtherUrls);
		return $role;
	}

	/**
	 * 新增店铺角色
	 */
	public function add(){
		$shopId = (int)session('WST_STORE.shopId');
		$storeId = (int)session('WST_STORE.storeId');
		$data["shopId"] = $shopId;
		$data["roleName"] = input('roleName/s');
		if($data["roleName"]==""){
			return WSTReturn(lang("require_role_name"),-1);
		}
		$menuIds = input('menuIds/s');
		$urls = [];
		$otherUrls = [];
		if($menuIds==""){
			return WSTReturn(lang("require_privilege"),-1);
		}else{
			$roleMenus = model("HomeMenus")->getRoleMenus();
			$menuIds = explode(",",$menuIds);
			$menuList = array();
			for($i=0,$j=count($menuIds);$i<$j;$i++){
				$menu = $roleMenus[$menuIds[$i]];
				$menuList[$menu["grandpaId"]][$menu["parentId"]]["urls"][] = strtolower($menu["menuUrl"]);
				$menuList[$menu["grandpaId"]][$menu["parentId"]]["otherUrls"][] = strtolower($menu["menuOtherUrl"]);
			}
		}
		$data["storeId"] = $storeId;
		$data["privilegeUrls"] = json_encode($menuList);
		$data["createTime"] = date("Y-m-d H:i:s");
		$result = $this->save($data);
		if(false !== $result){
        	return WSTReturn(lang("op_ok"), 1);
        }
        return WSTReturn(lang("op_err"),-1);
	}

	/**
	 * 修改店铺角色
	 */
	public function edit(){
		$shopId = (int)session('WST_STORE.shopId');
		$id = (int)input('id');
		$data["roleName"] = input('roleName/s');
		if($data["roleName"]==""){
			return WSTReturn(lang("require_role_name"),-1);
		}
		$menuIds = input('menuIds/s');
		$urls = [];
		$otherUrls = [];
		if($menuIds==""){
			return WSTReturn(lang("require_privilege"),-1);
		}else{
			$roleMenus = model("HomeMenus")->getRoleMenus();
			$menuIds = explode(",",$menuIds);
			$menuList = array();
			for($i=0,$j=count($menuIds);$i<$j;$i++){
				$menu = $roleMenus[$menuIds[$i]];
				$menuList[$menu["grandpaId"]][$menu["parentId"]]["urls"][] = strtolower($menu["menuUrl"]);
				$menuList[$menu["grandpaId"]][$menu["parentId"]]["otherUrls"][] = strtolower($menu["menuOtherUrl"]);
			}
		}
		$storeId = (int)session('WST_STORE.storeId');
		$data["privilegeUrls"] = json_encode($menuList);
		$result = $this->where(["id"=>$id,"shopId"=>$shopId,"storeId"=>$storeId])->update($data);
		if(false !== $result){
        	return WSTReturn(lang("op_ok"), 1);
        }
        return WSTReturn(lang("op_err"),-1);
	}

	/**
	 * 删除店铺角色
	 */
	public function del(){
		$shopId = (int)session('WST_STORE.shopId');
		$storeId = (int)session('WST_STORE.storeId');
		$id = input('post.id/d');
		$data = [];
		$data['dataFlag'] = -1;
	    $result = $this->where(["id"=>$id,"shopId"=>$shopId,"storeId"=>$storeId])->update($data);
        if(false !== $result){
        	return WSTReturn(lang("op_ok"), 1);
        }
        return WSTReturn(lang("op_err"),-1);
	}
}
