<?php
namespace wstmart\admin\model;
use wstmart\admin\validate\Accreds as validate;
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
class Accreds extends Base{
	protected $pk = 'accredId';
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->alias('a')->join('__ACCREDS_LANGS__ al','al.accredId=a.accredId and langId='.WSTCurrLang())->where('dataFlag',1)->order('a.accredId desc')->paginate(input('limit/d'));
	}
	/**
	 * 列表
	 */
    public function listQuery(){
		return $this->alias('a')->join('__ACCREDS_LANGS__ al','al.accredId=a.accredId and langId='.WSTCurrLang())->where('dataFlag',1)->select();
	}
	public function getById($id){
		$rs = $this->where(['accredId'=>$id,'dataFlag'=>1])->find();
		$rs['langParams'] = Db::name('accreds_langs')->where(['accredId'=>$id])->column('*','langId');
		return $rs;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createTime'] = date('Y-m-d H:i:s');
		WSTUnset($data,'accredId');
		Db::startTrans();
		try{
			$validate = new validate();
		    if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
			$result = $this->allowField(true)->save($data);
			if(false !==$result){
				$id = $this->accredId;
				$datas = [];
				$langParams = input('post.langParams');
				foreach (WSTSysLangs() as $key => $v) {
					$sdata = [];
					$sdata['accredId'] = $id;
					$sdata['langId'] = $v['id'];
					$sdata['accredName'] = $langParams[$v['id']]['accredName'];
					$datas[] = $sdata;
				}
				Db::name('accreds_langs')->insertAll($datas);
				//启用上传图片
				WSTUseResource(1, $id, $data['accredImg']);
		        if(false !== $result){
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
		WSTUnset($data,'createTime');
		Db::startTrans();
		try{
			WSTUseResource(1, (int)$data['accredId'], $data['accredImg'], 'accreds', 'accredImg');
			$validate = new validate();
		    if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
		    $result = $this->allowField(true)->save($data,['accredId'=>(int)$data['accredId']]);
	        if(false !== $result){
	        	Db::name('accreds_langs')->where(['accredId'=>(int)$data['accredId']])->delete();
	        	$datas = [];
				$langParams = input('post.langParams');
				foreach (WSTSysLangs() as $key => $v) {
					$sdata = [];
					$sdata['accredId'] = (int)$data['accredId'];
					$sdata['langId'] = $v['id'];
					$sdata['accredName'] = $langParams[$v['id']]['accredName'];
					$datas[] = $sdata;
				}
				Db::name('accreds_langs')->insertAll($datas);
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
		    $result = $this->setField(['dataFlag'=>-1,'accredId'=>$id]);
		    WSTUnuseResource('accreds','accredImg',$id);	
	        if(false !== $result){
	        	Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('op_err'),-1); 
	}
	
}
