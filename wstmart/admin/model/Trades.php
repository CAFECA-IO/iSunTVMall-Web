<?php
namespace wstmart\admin\model;
use wstmart\admin\validate\Trades as validate;
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
 * 行业业务处理
 */
use think\Db;
class Trades extends Base{
	protected $pk = 'tradeId';
	/**
	 * 获取树形分类
	 */
	public function pageQuery(){
		return $this->alias('t')->join('__TRADES_LANGS__ tl','tl.tradeId=t.tradeId and tl.langId='.WSTCurrLang())->where(['dataFlag'=>1,'parentId'=>input('tradeId/d',0)])->where(['dataFlag'=>1,'parentId'=>input('tradeId/d',0)])->order('tradeSort asc,t.tradeId desc')->paginate(1000)->toArray();
	}
	/**
	 * 获取列表
	 */
	public function listQuery($parentId){
		return $this->alias('t')->join('__TRADES_LANGS__ tl','tl.tradeId=t.tradeId and tl.langId='.WSTCurrLang())->where(['dataFlag'=>1,'parentId'=>$parentId])->order('tradeSort asc,tl.tradeName asc')->select();
	}
	
	/**
	 *获取行业名值对
	 */
	public function listKeyAll(){
		$rs = $this->alias('t')->join('__TRADES_LANGS__ tl','tl.tradeId=t.tradeId and tl.langId='.WSTCurrLang())->field("t.tradeId,tl.tradeName")->where(['dataFlag'=>1])->order('tradeSort asc,tl.tradeName asc')->select();
		$data = array();
		foreach ($rs as $key => $trade) {
			$data[$trade["tradeId"]] = $trade["tradeName"];
		}
		return $data;
	}
	
	/**
	 *	获取树形分类
	 */
	public function getTree($data, $parentId=0){
		$arr = array();
		foreach($data as $k=>$v)
		{
			if($v['parentId']==$parentId && $v['dataFlag']==1)
			{
				//再查找该分类下是否还有子分类
				$v['child'] = $this->getTree($data, $v['tradeId']);
				//统计child
				$v['childNum'] = count($v['child']);
				//将找到的分类放回该数组中
				$arr[]=$v;
			}
		}
		return $arr;
	}
	
	/**
	 * 迭代获取下级
	 * 获取一个分类下的所有子级分类id
	 */
	public function getChild($pid){
		$data = $this->where("dataFlag=1")->select();
		//获取该分类id下的所有子级分类id
		$ids = $this->_getChild($data, $pid, true);//每次调用都清空一次数组
		//把自己也放进来
		array_unshift($ids, $pid);
		return $ids;
	}
	
	public function _getChild($data, $pid, $isClear=false){
		static $ids = array();
		if($isClear)//是否清空数组
			$ids = array();
		foreach($data as $k=>$v)
		{
			if($v['parentId']==$pid && $v['dataFlag']==1)
			{
				$ids[] = $v['tradeId'];//将找到的下级分类id放入静态数组
				//再找下当前id是否还存在下级id
				$this->_getChild($data, $v['tradeId']);
			}
		}
		return $ids;
	}
	
	/**
	 * 获取指定对象
	 */
	public function getTrades($id){
		return $this->where(['tradeId'=>$id])->find();
	}
	 

	/**
	 * 修改分类排序
	 */
	public function editOrder(){
		$id = (int)input('id');
		$result = $this->where("tradeId = ".$id)->update(['tradeSort' => (int)input('tradeSort')]);
		if(false !== $result){
			WSTClearAllCache();
			return WSTReturn(lang('op_ok'), 1);
		}else{
			return WSTReturn($this->getError(),-1);
		}

	}
	
	/**
	 * 显示是否显示/隐藏
	 */
	public function editiIsShow(){
		$ids = array();
		$id = input('post.id/d');
		$ids = $this->getChild($id);
		$isShow = input('post.isShow/d')?1:0;
		Db::startTrans();
        try{
			$result = $this->where("tradeId in(".implode(',',$ids).")")->update(['isShow' => $isShow]);
			if(false !== $result){
				WSTClearAllCache();
		    }
		    Db::commit();
	        return WSTReturn(lang('op_ok'), 1);
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }
			
	}
	

	/**
	 * 获取数据编辑
	 */
	public function getById($id){
		$id = ($id>0)?$id:(int)input('id');
		$rs =  Db::name('trades')->where(['tradeId'=>$id])->find();
		$rs['langParams'] = Db::name('trades_langs')->where(['tradeId'=>$id])->column('*','langId');
        return $rs;
	}

	/**
	 * 新增
	 */
	public function add(){
		$parentId = input('post.parentId/d');
		$data = input('post.');
		WSTUnset($data,'tradeId,dataFlag');
		$data['parentId'] = $parentId;
		$data['createTime'] = date('Y-m-d H:i:s');
	    $validate = new validate();
	    Db::startTrans();
		try{
			if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
			
			$result = $this->allowField(true)->save($data);
			if(false !== $result){
				$tradeId = $this->tradeId;

				$langParams = input('post.langParams');
				$gcs = [];
				foreach (WSTSysLangs() as $key => $v) {
					$gc = [];
					$gc['tradeId'] = $tradeId;
		        	$gc['langId'] = $v['id'];
		        	$gc['tradeName'] =  $langParams[$v['id']]['tradeName'];
		        	$gc['simpleName'] =  $langParams[$v['id']]['simpleName'];
                    $gc['subTitle'] =  $langParams[$v['id']]['subTitle'];
                    $gc['seoTitle'] =  $langParams[$v['id']]['seoTitle'];
                    $gc['seoKeywords'] =  $langParams[$v['id']]['seoKeywords'];
                    $gc['seoDes'] =  $langParams[$v['id']]['seoDes'];
		        	$gcs[] = $gc;
		        }
				Db::name('trades_langs')->insertAll($gcs);

				WSTUseResource(1, $tradeId, $data['tradeImg']);
				WSTClearAllCache();
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
		$tradeId = input('post.id/d');
		$data = input('post.');
		WSTUnset($data,'tradeId,dataFlag,createTime');
		$validate = new validate();
		if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
		
		Db::startTrans();
		try{
			WSTUseResource(1, $tradeId, $data['tradeImg'], 'trades', 'tradeImg');
			$result = $this->allowField(true)->save($data,['tradeId'=>$tradeId]);
			$ids = array();
			$ids = $this->getChild($tradeId);
			$this->where("tradeId in(".implode(',',$ids).")")->update(['isShow' => (int)$data['isShow']]);
			if(false !== $result){

				Db::name('trades_langs')->where(['tradeId'=>$tradeId])->delete();
				$gcs = [];
				$langParams = input('post.langParams');
				foreach (WSTSysLangs() as $key => $v) {
					$gc = [];
					$gc['tradeId'] = $tradeId;
		        	$gc['langId'] = $v['id'];
		        	$gc['tradeName'] =  $langParams[$v['id']]['tradeName'];
		        	$gc['simpleName'] =  $langParams[$v['id']]['simpleName'];
                    $gc['subTitle'] =  $langParams[$v['id']]['subTitle'];
                    $gc['seoTitle'] =  $langParams[$v['id']]['seoTitle'];
                    $gc['seoKeywords'] =  $langParams[$v['id']]['seoKeywords'];
                    $gc['seoDes'] =  $langParams[$v['id']]['seoDes'];
		        	$gcs[] = $gc;
		        }
				Db::name('trades_langs')->insertAll($gcs);
				
				WSTClearAllCache();
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
		$ids = array();
		$id = input('post.id/d');
		$ids = $this->getChild($id);
		Db::startTrans();
        try{
		    $data = [];
		    $data['dataFlag'] = -1;
		    $result = $this->where([['tradeId','in',$ids]])->update($data);
		    if(false !== $result){
		        WSTClearAllCache();
		    }
            Db::commit();
	        return WSTReturn(lang('op_ok'), 1);
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }
	}
	
    /**
	 * 根据子分类获取其父级分类
	 */
	public function getParentIs($id,$data = array()){
		$data[] = $id;
		$parentId = $this->where('tradeId',$id)->value('parentId');
		if($parentId==0){
			krsort($data);
			return $data;
		}else{
			return $this->getParentIs($parentId, $data);
		}
	}
}