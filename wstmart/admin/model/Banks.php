<?php
namespace wstmart\admin\model;
use wstmart\admin\validate\Banks as validate;
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
 * 银行业务处理
 */
class Banks extends Base{
	protected $pk = 'bankId';
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->alias('b')->join('__BANKS_LANGS__ bl','bl.bankId=b.bankId and langId='.WSTCurrLang())->where('dataFlag',1)->field('b.bankId,bankName,bankCode,bankImg,isShow')->order('bankId desc')->paginate(input('limit/d'));
	}
	public function getById($id){
		$rs = $this->where(['bankId'=>$id,'dataFlag'=>1])->find();
		$rs['langParams'] = Db::name('banks_langs')->where(['bankId'=>$id])->column('*','langId');
		return $rs;
	}
	/**
	 * 列表
	 */
	public function listQuery(){
		return $this->alias('b')->join('__BANKS_LANGS__ bl','bl.bankId=b.bankId and langId='.WSTCurrLang())->where(['dataFlag'=>1,'isShow'=>1])->field('b.bankId,bankName,bankImg')->select();
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = ['bankImg'=>input('post.bankImg'),'bankCode'=>input('post.bankCode'),
				 'createTime'=>date('Y-m-d H:i:s'),'isShow'=>input('post.isShow')];
		$validate = new validate();
		Db::startTrans();
		try{
			if(!$validate->scene('add')->check(input('post.')))return WSTReturn($validate->getError());
			$result = $this->allowField(['bankImg','bankCode','createTime','isShow'])->save($data);
	        if(false !== $result){
	        	$bankId = $this->bankId;
	        	$datas = [];
				$langParams = input('post.langParams');
				foreach (WSTSysLangs() as $key => $v) {
					$sdata = [];
					$sdata['bankId'] = $bankId;
					$sdata['langId'] = $v['id'];
					$sdata['bankName'] = $langParams[$v['id']]['bankName'];
					$datas[] = $sdata;
				}
				Db::name('banks_langs')->insertAll($datas);
	        	WSTUseResource(1, $bankId, $data['bankImg']);
	        	cache('WST_BANKS',null);
	        	Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }else{
	        	return WSTReturn($this->getError(),-1);
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
		$bankId = input('post.bankId/d',0);
		$validate = new validate();
		$bankImg = input('post.bankImg');
		Db::startTrans();
		try{
			$data = input('post.');
			WSTUseResource(1, $bankId, $bankImg,'banks','bankImg');
			if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
		    $result = $this->allowField(['bankCode','bankImg','isShow'])->save(['bankCode'=>input('post.bankCode'),'bankImg'=>$bankImg,'isShow'=>input('post.isShow')],['bankId'=>$bankId]);
	        if(false !== $result){
	        	Db::name('banks_langs')->where(['bankId'=>$bankId])->delete();
	        	$datas = [];
				$langParams = input('post.langParams');
				foreach (WSTSysLangs() as $key => $v) {
					$sdata = [];
					$sdata['bankId'] = $bankId;
					$sdata['langId'] = $v['id'];
					$sdata['bankName'] = $langParams[$v['id']]['bankName'];
					$datas[] = $sdata;
				}
				Db::name('banks_langs')->insertAll($datas);
	        	cache('WST_BANKS',null);
	        	Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }else{
	        	return WSTReturn($this->getError(),-1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }

	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = input('post.id/d',0);
		$data = [];
		$data['dataFlag'] = -1;
	    $result = $this->update($data,['bankId'=>$id]);
        if(false !== $result){
        	cache('WST_BANKS',null);
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
		$result = $this->where("bankId = ".$id)->update(['isShow' => $isShow]);
		if(false !== $result){
            cache('WST_BANKS',null);
			return WSTReturn(lang('op_ok'), 1);
		}else{
			return WSTReturn($this->getError(),-1);
		}
	}

}
