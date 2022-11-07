<?php
namespace wstmart\admin\model;
use wstmart\admin\validate\MobileBtns as validate;
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
 * 商家认证业务处理
 */
class MobileBtns extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
		$btnSrc = (int)input('btnSrc1',-1);
		$btnName = input('btnName1');
		$where = [];
		if($btnSrc>-1){
			$where[] = ['btnSrc','=',$btnSrc];
		}
		if($btnName!=''){
			$where[] = ['mbl.btnName','like',"%$btnName%"];
		}
		$page =  $this
                    ->alias('mb')
                    ->join('__MOBILE_BTNS_LANGS__ mbl','mbl.btnId=mb.id and mbl.langId='.WSTCurrLang())
                    ->field('mb.*,mbl.btnName')
					->where($where)
					->order('btnSrc asc,btnSort asc')
					->paginate(input('limit/d'))->toArray();
	    return $page;
	}
	public function getById($id){
        $rs = $this->where(['id'=>$id])->find();
        $rs['langs'] = Db::name('mobile_btns_langs')->where(['btnId'=>$id])->column('*','langId');
		return $rs;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['btnSort'] = (int)$data['btnSort'];
		WSTUnset($data,'id');
		Db::startTrans();
		try{
			$validate = new validate();
			if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
			$result = $this->allowField(true)->save($data);
			if(false !==$result){
				cache('WST_MOBILE_BTN',null);
				$id = $this->id;
				//启用上传图片
				//WSTUseResource(1, $id, $data['btnImg']);
		        if(false !== $result){
                    $datas = [];
                    $langParams = input('post.langParams');
                    foreach (WSTSysLangs() as $key => $v) {
                        $data = [];
                        $data['btnId'] = $id;
                        $data['langId'] = $v['id'];
                        $data['btnName'] = $langParams[$v['id']]['btnName'];
                        $datas[] = $data;
                    }
                    Db::name('mobile_btns_langs')->insertAll($datas);
		        	Db::commit();
		        	return WSTReturn(lang('op_ok'), 1);
		        }
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('op_err'),-1);
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$data = input('post.');
		$data['btnSort'] = (int)$data['btnSort'];
		$id = (int)$data['id'];
		WSTUnset($data,'createTime');
		Db::startTrans();
		try{
			//WSTUseResource(1, $id, $data['btnImg'], 'mobile_btns', 'btnImg');
		    $validate = new validate();
		    if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
		    $result = $this->allowField(true)->save($data,['id'=>$id]);
	        if(false !== $result){
                Db::name('mobile_btns_langs')->where(['btnId'=>$id])->delete();
                $datas = [];
                $langParams = input('post.langParams');
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['btnId'] = $id;
                    $data['langId'] = $v['id'];
                    $data['btnName'] = $langParams[$v['id']]['btnName'];
                    $datas[] = $data;
                }
                Db::name('mobile_btns_langs')->insertAll($datas);
	        	cache('WST_MOBILE_BTN',null);
	        	Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('op_err'),-1);
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = (int)input('post.id/d');
	    Db::startTrans();
		try{
		    //WSTUnuseResource('mobile_btns','btnImg',$id);
		    $result = $this->where(['id'=>$id])->delete();
	        if(false !== $result){
	        	cache('WST_MOBILE_BTN',null);
	        	Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('op_err'),-1);
	}

}
