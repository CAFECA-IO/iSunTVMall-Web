<?php
namespace wstmart\admin\model;
use wstmart\admin\validate\Privileges as validate;
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
 * 权限业务处理
 */
class Privileges extends Base{
    protected $pk = 'privilegeId';
	/**
	 * 加载指定菜单的权限
	 */
	public function listQuery($parentId){
		$rs = $this->alias('p')->join('__PRIVILEGES_LANGS__ pl','p.privilegeId = pl.privilegeId and langId='.WSTCurrLang())->where([['menuId','=',$parentId],['dataFlag','=',1]])->order('p.privilegeId', 'asc')->select();
		foreach ($rs as $key =>$v){
			$rs[$key]['privilegeName'] = $v['privilegeName'];
		}
		return ['data'=>$rs,'total'=>count($rs)];
	}
	/**
	 * 获取指定权限
	 */
    public function getById($id){
		$rs = $this->get(['privilegeId'=>$id,'dataFlag'=>1]);
		$rs['langParams'] = Db::name('privileges_langs')->where(['privilegeId'=>$id])->column('*','langId');
		return $rs;
	}
	
    /**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$validate = new validate();
		if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
		$result = $this->allowField(true)->save($data);
        if(false !== $result){
        	$privilegeId = $this->privilegeId;
        	$datas = [];
			$langParams = input('post.langParams');
			foreach (WSTSysLangs() as $key => $v) {
				$data = [];
				$data['privilegeId'] = $privilegeId;
				$data['langId'] = $v['id'];
				$data['privilegeName'] = $langParams[$v['id']]['privilegeName'];
				$datas[] = $data;
			}
			Db::name('privileges_langs')->insertAll($datas);
        	WSTClearAllCache();
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = (int)input('post.id/d');
		$data = input('post.');
		$validate = new validate();
		if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
	    $result = $this->allowField(true)->save($data,['privilegeId'=>$id]);
        if(false !== $result){
        	Db::name('privileges_langs')->where(['privilegeId'=>$id])->delete();
        	$datas = [];
			$langParams = input('post.langParams');
			foreach (WSTSysLangs() as $key => $v) {
				$data = [];
				$data['privilegeId'] = $id;
				$data['langId'] = $v['id'];
				$data['privilegeName'] = $langParams[$v['id']]['privilegeName'];
				$datas[] = $data;
			}
			Db::name('privileges_langs')->insertAll($datas);
        	WSTClearAllCache();
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	/**
	 * 删除
	 */
	public function del(){
	    $id = input('post.id/d');
	    $result = $this->where('privilegeId','=',$id)->delete();
        if(false !== $result){
        	WSTClearAllCache();
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 检测权限代码是否存在
	 */
	public function checkPrivilegeCode(){
		$privilegeId = input('privilegeId/d',0);
		$code = input('code');
		if($code=='')return WSTReturn("", 1);
		$where = [];
		$where[] = ["privilegeCode",'=',$code];
		$where[] = ["dataFlag",'=',1];
		if($privilegeId>0){
			$where[] = ["privilegeId","<>",$privilegeId];
		}
		$rs = $this->where($where)->Count();
		if($rs==0)return WSTReturn("", 1);
		return WSTReturn(lang('check_sys_privelege_exist'), -1);
	}
	
	/**
	 * 加载权限并且标用户的权限
	 */
	public function listQueryByRole($id){
		$mrs = Db::name('menus')->alias('m')
            ->join('__MENUS_LANGS__ ml','ml.menuId=m.menuId and ml.langId='.WSTCurrLang())
		    ->join('__PRIVILEGES__ p','m.menuId= p.menuId and isMenuPrivilege=1 and p.dataFlag=1','left')
			->where([['parentId','=',$id],['m.dataFlag','=',1]])
			->field('m.menuId id,ml.menuName name,p.privilegeCode,1 as isParent')
			->order('menuSort', 'asc')
			->select();
		foreach ($mrs as $key =>$v){
			$mrs[$key]['name'] = $v['name'];
		}	
		$prs = $this->alias('p')->join('__PRIVILEGES_LANGS__ pl','p.privilegeId = pl.privilegeId and langId='.WSTCurrLang())->where([['dataFlag','=',1],['menuId','=',$id]])->field('p.privilegeId id,privilegeName name,privilegeCode,0 as isParent')->select();
		foreach ($prs as $key =>$v){
			$prs[$key]['name'] = $v['name'];
		}
		if($mrs){
			if($prs){
				foreach ($prs as $v){
					array_unshift($mrs,$v);
				}
			}
		}else{
		    if($prs)$mrs = $prs;
		}
		if(!$mrs)return [];
		foreach ($mrs as $key =>$v){
			if($v['isParent']==1){
				$mrs[$key]['isParent'] = true;
				$mrs[$key]['open'] = true;
			}else{
			    $mrs[$key]['id'] = 'p'.$v['id'];
			}
		}
		return $mrs;
	}
	/**
	 * 加载全部权限
	 */
	public function getAllPrivileges(){
		return $this->alias('p')->join('__PRIVILEGES_LANGS__ pl','p.privilegeId = pl.privilegeId and langId='.WSTCurrLang())->where('dataFlag','=',1)->field('menuId,privilegeName,privilegeCode,privilegeUrl,otherPrivilegeUrl')->select();
	}
}
