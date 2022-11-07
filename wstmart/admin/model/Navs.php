<?php
namespace wstmart\admin\model;
use wstmart\admin\validate\Navs as validate;
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
 * 导航管理业务处理
 */
class Navs extends Base{
	/**
	 * 分页
	 */
	public function pageQuery(){
	    $rs = $this
            ->alias('n')
            ->join('__NAVS_LANGS__ nl','nl.navId=n.id and nl.langId='.WSTCurrLang())
            ->field('n.*,nl.navTitle')->order('id desc')->paginate(input('limit/d'));
		return $rs;
	}
	public function getById($id){
        $rs = $this->where(['id'=>$id])->find();
        $rs['langs'] = Db::name('navs_langs')->where(['navId'=>$id])->column('*','langId');
		return $rs;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createTime'] = date('Y-m-d H:i:s');
		WSTUnset($data,'id');
		$validate = new validate();
		if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
		$result = $this->allowField(true)->save($data);
        if(false !== $result){
            $navId = $this->id;
            $datas = [];
            $langParams = input('post.langParams');
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['navId'] = $navId;
                $data['langId'] = $v['id'];
                $data['navTitle'] = $langParams[$v['id']]['navTitle'];
                $datas[] = $data;
            }
            Db::name('navs_langs')->insertAll($datas);
        	cache('WST_NAVS',null);
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$id = input('post.id/d',0);
		//获取数据
		$data = input('post.');
		WSTUnset($data,'createTime');
		$validate = new validate();
		if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
	    $result = $this->allowField(true)->save($data,['id'=>$id]);
        if(false !== $result){
            Db::name('navs_langs')->where(['navId'=>$id])->delete();
            $datas = [];
            $langParams = input('post.langParams');
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['navId'] = $id;
                $data['langId'] = $v['id'];
                $data['navTitle'] = $langParams[$v['id']]['navTitle'];
                $datas[] = $data;
            }
            Db::name('navs_langs')->insertAll($datas);
        	cache('WST_NAVS',null);
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
	    $result = $this->destroy($id);
        if(false !== $result){
        	cache('WST_NAVS',null);
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	/**
	 * 设置显示隐藏
	 */
    public function editiIsShow(){
        $id = input('post.id/d',0);
        $field = input('post.field');
        $val = input('post.val/d',0);
        if(!in_array($field,['isShow','isOpen']))return WSTReturn(lang('op_illega'),-1);
        $result = Db::name('navs')->where('id','eq',$id)->setField($field, $val);
        if(false !== $result){
        	cache('WST_NAVS',null);
            return WSTReturn(lang('op_ok'), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
    }

}
