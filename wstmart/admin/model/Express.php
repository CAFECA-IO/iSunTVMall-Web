<?php
namespace wstmart\admin\model;
use wstmart\admin\validate\Express as validate;
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
 * 快递业务处理
 */
class Express extends Base{
	protected $pk = 'expressId';
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->alias('a')
		            ->join('__EXPRESS_LANGS__ el','el.expressId=a.expressId and el.langId='.WSTCurrLang())
		            ->where('dataFlag',1)->field('a.expressId,el.expressName,expressCode,isShow')->order('a.expressId desc')->paginate(input('limit/d'));
	}
	public function getById($id){
		$rs =  $this->where(['expressId'=>$id,'dataFlag'=>1])->find();
		$rs['langs'] = Db::name('express_langs')->where(['expressId'=>$id])->column('*','langId');
		return $rs;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		unset($data['expressId']);
		$validate = new validate();
		if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
		Db::startTrans();
        try{
			$result = $this->allowField(true)->save($data);
	        if(false !== $result){
	        	$expressId = $this->expressId;
				$datas = [];
				$langParams = input('post.langParams');
				foreach (WSTSysLangs() as $key => $v) {
					$data = [];
					$data['expressId'] = $expressId;
					$data['langId'] = $v['id'];
					$data['expressName'] = $langParams[$v['id']]['expressName'];
					$datas[] = $data;
				}
				Db::name('express_langs')->insertAll($datas);
				Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }else{
	        	return WSTReturn($this->getError(),-1);
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
		$expressId = (int)input('post.expressId/d',0);
		$validate = new validate();
		if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
		Db::startTrans();
        try{
		    $result = $this->allowField(['expressCode','isShow'])->save(['expressCode'=>input('post.expressCode'),'isShow'=>(int)input('post.isShow')],['expressId'=>$expressId]);
	        if(false !== $result){
	        	Db::name('express_langs')->where(['expressId'=>$expressId])->delete();
	        	$datas = [];
				$langParams = input('post.langParams');
				foreach (WSTSysLangs() as $key => $v) {
					$data = [];
					$data['expressId'] = $expressId;
					$data['langId'] = $v['id'];
					$data['expressName'] = $langParams[$v['id']]['expressName'];
					$datas[] = $data;
				}
				Db::name('express_langs')->insertAll($datas);
				Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }else{
	        	return WSTReturn($this->getError(),-1);
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
	    $id = (int)input('post.id/d',0);
		$data = [];
		$data['dataFlag'] = -1;
	    $result = $this->update($data,['expressId'=>$id]);
        if(false !== $result){
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}

	/**
	 * 显示是否显示/隐藏
	 */
	public function editiIsShow(){
		//获取子集
		$id = (int)input('post.id/d',0);
		$isShow = (int)input('post.isShow/d',0)?0:1;
		$result = $this->where("expressId = ".$id)->update(['isShow' => $isShow]);
		if(false !== $result){
			return WSTReturn(lang('op_ok'), 1);
		}else{
			return WSTReturn($this->getError(),-1);
		}
	}
	
}
