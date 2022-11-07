<?php
namespace wstmart\admin\model;
use wstmart\admin\validate\ArticleCats as validate;
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
 * 文章分类业务处理
 */
use think\Db;
class ArticleCats extends Base{
	protected $pk = 'catId';
	/**
	 * 获取树形分类
	 */
	public function pageQuery(){
		$parentId = input('catId/d',0);
		$data = $this->alias('ac')->join('article_cats_langs al','ac.catId=al.catId and al.langId='.WSTCurrLang(),'inner')->where([['ac.dataFlag','=',1],['ac.parentId','=',$parentId],['ac.catId','not in',[52,200]]])->field('ac.*,al.catName')->order('catId desc')->paginate(1000)->toArray();
		return $data;
	}
	/**
	 * 获取列表
	 */
	public function listQuery($parentId){
		$rs = $this->alias('ac')->join('article_cats_langs al','ac.catId=al.catId and al.langId='.WSTCurrLang(),'inner')->where(['ac.dataFlag'=>1,'ac.parentId'=>$parentId])->order('ac.catSort asc,al.catName asc')->select();
		if(count($rs)>0){
			foreach ($rs as $key => $v){
				$rs[$key]['childrenurl'] = url('admin/articlecats/listQuery',array('parentId'=>$v['catId']));
				$rs[$key]['children'] = [];
				$rs[$key]['isextend'] = false;
			}
		}
		return $rs;
	}
	/**
	 * 获取指定对象
	 */
	public function getById($id){
		$rs = $this->where(['dataFlag'=>1,'catId'=>$id])->find();
		$rs['langs'] = Db::name('article_cats_langs')->where(['catId'=>$id])->column('*','langId');
		return $rs;
	}
	
	/**
	 *  获取文章分类列表
	 */
	public function listQuery2(){
		$id = (int)input('id');
		$rs =  $this->alias('ac')->join('article_cats_langs al','ac.catId=al.catId and al.langId='.WSTCurrLang(),'inner')->where([['ac.dataFlag','=',1],['ac.isShow','=',1],['ac.parentId','=',$id],['ac.catId','not in',[200,52]]])->field('ac.catId as id,al.catName as name,ac.parentId as pId, 1 as isParent')->order('ac.catSort desc')->select();
	    foreach ($rs as $key => $value) {
	    	$rs[$key]['isParent'] = true;
	    }
	    return $rs;
	}
	
	/**
	 * 显示是否显示/隐藏
	 */
	public function editiIsShow(){
		$ids = array();
		$id = input('post.id/d');
		$ids = $this->getChild($id);
		$isShow = input('post.isShow/d')?1:0;
		$result = $this->where("catId in(".implode(',',$ids).")")->update(['isShow' => $isShow]);
		if(false !== $result){
			WSTClearAllCache();
			return WSTReturn(lang('op_ok'), 1);
		}else{
			return WSTReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 迭代获取下级
	 * 获取一个分类下的所有子级分类id
	 */
	public function getChild($pid=1){
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
				$ids[] = $v['catId'];//将找到的下级分类id放入静态数组
				//再找下当前id是否还存在下级id
				$this->_getChild($data, $v['catId']);
			}
		}
		return $ids;
	}
	
	/**
	 * 新增
	 */
	public function add(){
		$parentId = input('post.parentId/d');
		$data = input('post.');
		WSTUnset($data,'catId,catType,dataFlag');
		$data['parentId'] = $parentId;
		$data['createTime'] = date('Y-m-d H:i:s');
		$validate = new validate();
		if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
		Db::startTrans();
        try{
			$result = $this->allowField(true)->save($data);
			if(false !== $result){
				$catId = $this->catId;
				$datas = [];
				$langParams = input('post.langParams');
				foreach (WSTSysLangs() as $key => $v) {
					$data = [];
					$data['catId'] = $catId;
					$data['langId'] = $v['id'];
					$data['catName'] = $langParams[$v['id']]['catName'];
					$datas[] = $data;
				}
				Db::name('article_cats_langs')->insertAll($datas);
				Db::commit();
				WSTClearAllCache();
				return WSTReturn(lang('op_ok'), 1);
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
		$catId = input('post.id/d');
		$validate = new validate();
		if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
		$result = $this->allowField(['isShow','catSort'])->save($data,['catId'=>$catId]);
		$ids = array();
		$ids = $this->getChild($catId);
		$this->where("catId in(".implode(',',$ids).")")->update(['isShow' => input('post.')['isShow']]);
		if(false !== $result){
			Db::name('article_cats_langs')->where(['catId'=>$catId])->delete();
			$datas = [];
			$langParams = input('post.langParams');
			foreach (WSTSysLangs() as $key => $v) {
				$data = [];
				$data['catId'] = $catId;
				$data['langId'] = $v['id'];
				$data['catName'] = $langParams[$v['id']]['catName'];
				$datas[] = $data;
			}
			Db::name('article_cats_langs')->insertAll($datas);
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
		$ids = array();
		$id = input('post.id/d');
		$ids = $this->getChild($id);
		$data = [];
		$data['dataFlag'] = -1;
		$rs = $this->getById($id);
		if($rs['catType']==1){
			return WSTReturn(lang('article_cat_op_del_err'), -1);
		}else{
			Db::startTrans();
            try{
				$result = $this->where("catId in(".implode(',',$ids).")")->update($data);
				if(false !==$result){
					WSTClearAllCache();
					Db::name('articles')->where([['catId','in',$ids]])->update(['dataFlag'=>-1]);
				}
				Db::commit();
	            return WSTReturn(lang('op_ok'), 1);
            }catch (\Exception $e) {
                Db::rollback();
                return WSTReturn(lang('op_err'),-1);
            }
		}
	}
}