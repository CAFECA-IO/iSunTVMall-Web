<?php
namespace wstmart\admin\model;
use wstmart\admin\validate\GoodsCats as validate;
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
 * 商品分类业务处理
 */
use think\Db;
class GoodsCats extends Base{
	protected $pk = 'catId';
	/**
	 * 获取树形分类
	 */
	public function pageQuery(){
		return $this->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())->where(['dataFlag'=>1,'parentId'=>input('catId/d',0)])->order('catSort asc,gc.catId desc')->paginate(1000)->toArray();
	}
	/**
	 * 获取列表
	 */
	public function listQuery($parentId){
		return $this->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())->where(['dataFlag'=>1,'parentId'=>$parentId])->order('catSort asc,gcl.catName asc')->select();
	}

	/**
	 *获取商品分类名值对
	 */
	public function listKeyAll(){
		$rs = $this->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())->field("gc.catId,gcl.catName")->where(['dataFlag'=>1])->order('catSort asc,gcl.catName asc')->select();
		$data = array();
		foreach ($rs as $key => $cat) {
			$data[$cat["catId"]] = $cat["catName"];
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
				$v['child'] = $this->getTree($data, $v['catId']);
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
				$ids[] = $v['catId'];//将找到的下级分类id放入静态数组
				//再找下当前id是否还存在下级id
				$this->_getChild($data, $v['catId']);
			}
		}
		return $ids;
	}

	/**
	 * 获取指定对象
	 */
	public function getGoodscats($id){
		return $this->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())->where(['gc.catId'=>$id])->find();
	}
	/**
	 * 获取数据编辑
	 */
	public function getById($id){
		$id = ($id>0)?$id:(int)input('id');
		$rs =  Db::name('goods_cats')->where(['catId'=>$id])->find();
		$rs['langParams'] = Db::name('goods_cats_langs')->where(['catId'=>$id])->column('*','langId');
        return $rs;
	}

	 /**
	  * 显示是否推荐/不推荐
	  */
	 public function editiIsFloor(){
	    $ids = array();
		$id = input('post.id/d');
		$ids = $this->getChild($id);
	 	$isFloor = input('post.isFloor/d')?1:0;
	 	$result = $this->where("catId in(".implode(',',$ids).")")->update(['isFloor' => $isFloor]);
	 	if(false !== $result){
	 		WSTClearAllCache();
	 		return WSTReturn(lang('op_ok'), 1);
	 	}else{
	 		return WSTReturn($this->getError(),-1);
	 	}
	 }
	/**
	 * 修改分类排序
	 */
	public function editOrder(){
		$id = (int)input('id');
		$result = $this->where("catId = ".$id)->update(['catSort' => (int)input('catSort')]);
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
			$result = $this->where("catId in(".implode(',',$ids).")")->update(['isShow' => $isShow]);
			if(false !== $result){
				if($isShow==0){
					//删除购物车里的相关商品
					$goods = Db::name('goods')->where([["goodsCatId",'in',$ids],['isSale','=',1]])->field('goodsId')->select();
					if(count($goods)>0){
						$goodsIds = [];
						foreach ($goods as $key =>$v){
							$goodsIds[] = $v['goodsId'];
						}
						Db::name('carts')->where([['goodsId','in',$goodsIds]])->delete();
					}
					//把相关的商品下架了
					Db::name('goods')->where("goodsCatId in(".implode(',',$ids).")")->update(['isSale' => 0]);
					WSTClearAllCache();
				}
		    }
		    Db::commit();
	        return WSTReturn(lang('op_ok'), 1);
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }

	}

	/**
	 * 新增
	 */
	public function add(){
		$parentId = input('post.parentId/d');
		$data = input('post.');
		WSTUnset($data,'catId,dataFlag');
		$data['parentId'] = $parentId;
		$data['createTime'] = date('Y-m-d H:i:s');
	    $validate = new validate();
	    Db::startTrans();
		try{
			if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
			if(WSTDatas('ADS_TYPE',3)!=''){
				if($data['mobileCatListTheme']=='')return WSTReturn(lang('goodsCats_err5'));
				if($data['mobileDetailTheme']=='')return WSTReturn(lang('goodsCats_err6'));
			}
		    if(WSTDatas('ADS_TYPE',2)!=''){
				if($data['wechatCatListTheme']=='')return WSTReturn(lang('goodsCats_err7'));
				if($data['wechatDetailTheme']=='')return WSTReturn(lang('goodsCats_err8'));
			}
			$result = $this->allowField(true)->save($data);
			if(false !== $result){
				$catId = $this->catId;
				$langParams = input('post.langParams');
				$gcs = [];
				foreach (WSTSysLangs() as $key => $v) {
					$gc = [];
					$gc['catId'] = $catId;
		        	$gc['langId'] = $v['id'];
		        	$gc['catName'] =  $langParams[$v['id']]['catName'];
		        	$gc['simpleName'] =  $langParams[$v['id']]['simpleName'];
                    $gc['subTitle'] =  $langParams[$v['id']]['subTitle'];
                    $gc['seoTitle'] =  $langParams[$v['id']]['seoTitle'];
                    $gc['seoKeywords'] =  $langParams[$v['id']]['seoKeywords'];
                    $gc['seoDes'] =  $langParams[$v['id']]['seoDes'];
		        	$gcs[] = $gc;
		        }
				Db::name('goods_cats_langs')->insertAll($gcs);
				WSTUseResource(1, $catId, $data['catImg']);
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
		$catId = input('post.id/d');
		$data = input('post.');
		WSTUnset($data,'catId,dataFlag,createTime');
		$validate = new validate();
		if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
		if(WSTDatas('ADS_TYPE',3)!=''){
			if($data['mobileCatListTheme']=='')return WSTReturn(lang('goodsCats_err5'));
			if($data['mobileDetailTheme']=='')return WSTReturn(lang('goodsCats_err6'));
		}
	    if(WSTDatas('ADS_TYPE',2)!=''){
			if($data['wechatCatListTheme']=='')return WSTReturn(lang('goodsCats_err7'));
			if($data['wechatDetailTheme']=='')return WSTReturn(lang('goodsCats_err8'));
		}
		Db::startTrans();
		try{
			WSTUseResource(1, $catId, $data['catImg'], 'goods_cats', 'catImg');
			$result = $this->allowField(true)->save($data,['catId'=>$catId]);
			$ids = array();
			$ids = $this->getChild($catId);
			$this->where("catId in(".implode(',',$ids).")")->update(['isShow' => (int)$data['isShow'],'isFloor'=> $data['isFloor'],'commissionRate'=>(float)$data['commissionRate'],'showWay'=>$data['showWay']]);
			if(false !== $result){
				Db::name('goods_cats_langs')->where(['catId'=>$catId])->delete();
				$gcs = [];
				$langParams = input('post.langParams');
				foreach (WSTSysLangs() as $key => $v) {
					$gc = [];
					$gc['catId'] = $catId;
		        	$gc['langId'] = $v['id'];
		        	$gc['catName'] =  $langParams[$v['id']]['catName'];
		        	$gc['simpleName'] =  $langParams[$v['id']]['simpleName'];
                    $gc['subTitle'] =  $langParams[$v['id']]['subTitle'];
                    $gc['seoTitle'] =  $langParams[$v['id']]['seoTitle'];
                    $gc['seoKeywords'] =  $langParams[$v['id']]['seoKeywords'];
                    $gc['seoDes'] =  $langParams[$v['id']]['seoDes'];
		        	$gcs[] = $gc;
		        }
				Db::name('goods_cats_langs')->insertAll($gcs);
				//修改其下的所有风格模板
				if((int)$data['isForce']==1){
					$theme['catListTheme'] = $data['catListTheme'];
					$theme['detailTheme'] = $data['detailTheme'];
					if(WSTDatas('ADS_TYPE',3)!=''){
						$theme['mobileCatListTheme'] = $data['mobileCatListTheme'];
					    $theme['mobileDetailTheme'] = $data['mobileDetailTheme'];
					}
					if(WSTDatas('ADS_TYPE',3)!=''){
						$theme['wechatCatListTheme'] = $data['wechatCatListTheme'];
					    $theme['wechatDetailTheme'] = $data['wechatDetailTheme'];
					}
	                $this->where("catId in(".implode(',',$ids).")")->update($theme);
				}
				if($data['isShow']==0){
					//删除购物车里的相关商品
					$goods = Db::name('goods')->where([["goodsCatId",'in',$ids],['isSale','=',1]])->field('goodsId')->select();
					if(count($goods)>0){
						$goodsIds = [];
						foreach ($goods as $key =>$v){
								$goodsIds[] = $v['goodsId'];
						}
						Db::name('carts')->where([['goodsId','in',$goodsIds]])->delete();
					}
			    	//把相关的商品下架了
			        Db::name('goods')->where("goodsCatId in(".implode(',',$ids).")")->update(['isSale' => 0]);
			        WSTClearAllCache();
				}
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
		    $result = $this->where([['catId','in',$ids]])->update($data);
		    if(false !== $result){
		        //删除购物车里的相关商品
				$goods = Db::name('goods')->where([["goodsCatId",'in',$ids],['isSale','=',1]])->field('goodsId')->select();
				if(count($goods)>0){
					$goodsIds = [];
					foreach ($goods as $key =>$v){
							$goodsIds[] = $v['goodsId'];
					}
					Db::name('carts')->where([['goodsId','in',$goodsIds]])->delete();
				}
				//删除商品属性
				Db::name('attributes')->where("goodsCatId in(".implode(',',$ids).")")->update(['dataFlag'=>-1]);
		    	//删除商品规格
				Db::name('spec_cats')->where("goodsCatId in(".implode(',',$ids).")")->update(['dataFlag'=>-1]);
		    	//把相关的商品下架了
		        Db::name('goods')->where("goodsCatId in(".implode(',',$ids).")")->update(['isSale' => 0]);
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
		$parentId = $this->where('catId',$id)->value('parentId');
		if($parentId==0){
			krsort($data);
			return $data;
		}else{
			return $this->getParentIs($parentId, $data);
		}
	}
}
