<?php
namespace wstmart\common\model;
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
 * 商品分类类
 */
class GoodsCats extends Base{
	protected $pk = 'catId';
	/**
	 * 获取列表
	 */
	public function listQuery($parentId,$isFloor = -1){
		$dbo = $this->alias('c')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=c.catId and gcl.langId='.WSTCurrLang())->where(['dataFlag'=>1,'isShow'=>1,'parentId'=>$parentId]);
		if($isFloor!=-1)$dbo->where('isFloor',$isFloor);
		$rs = $dbo->order('catSort asc')->select();
		return $rs;
	}


    /**
     * 获取店铺申请的商品分类
     */
    public function getShopApplyGoodsCats($shopId){
    	$ids = Db::name('cat_shops')->where(['shopId'=>$shopId])->column('catId');
    	return $this->getChildIds($ids);
    }

	/**
	 * 根据父分类获取其下所有子分类[包括自己]
	 */
	public function getChildIds($ids,$data = []){
		$data = array_merge($data,$ids);
		$rs = $this->where([['dataFlag','=',1],['isShow','=',1],['parentId','in',$ids]])->column('catId');
        if(count($rs)>0){
            return $this->getChildIds($rs,$data);
        }else{
        	return $data;
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
	public function getParentNames($id){
		if($id<=0)return [];
	    $ids = $this->getParentIs($id);
        $rs = Db::name('goodsCats')->alias('c')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=c.catId and gcl.langId='.WSTCurrLang())->where([['c.catId','in',$ids]])->field('catName')->order('c.catId desc')->select();
        $names = [];
        foreach($rs as $v){
            $names[] = $v['catName'];
        }
        return $names;
	}
   /**
     * 获取首页楼层
     */
    public function getFloors(){
	    $cats1 = Db::name('goods_cats')->alias('c')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=c.catId and gcl.langId='.WSTCurrLang())->where([['dataFlag','=',1],['isShow','=',1] ,['parentId','=',0],['isFloor','=',1]])
		             ->field("catName,c.catId,subTitle")->order('catSort')->limit(10)->select();
		if(!empty($cats1)){
			$ids = [];
			foreach ($cats1 as $key =>$v){
				$ids[] = $v['catId'];
			}
			$cats2 = [];
			$rs = Db::name('goods_cats')->alias('c')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=c.catId and gcl.langId='.WSTCurrLang())->where([['dataFlag','=',1],['isShow','=',1],['parentId','in',$ids],['isFloor','=',1]])
				             ->field("parentId,catName,c.catId,subTitle")->order('catSort asc')->select();
			foreach ($rs as $key => $v){
				$cats2[$v['parentId']][] = $v;
			}
			foreach ($cats1 as $key =>$v){
				$cats1[$key]['children'] = (isset($cats2[$v['catId']]))?$cats2[$v['catId']]:[];
			}
		}
		return $cats1;
    }


	/**
	 *获取商品分类名值对
	 */
	public function listKeyAll(){
		$rs = $this->alias('c')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=c.catId and gcl.langId='.WSTCurrLang())->field("c.catId,gcl.catName")->where(['dataFlag'=>1])->order('catSort asc,catName asc')->select();
		$data = array();
		foreach ($rs as $key => $cat) {
			$data[$cat["catId"]] = $cat["catName"];
		}
		return $data;
	}

	/**
	 * 获取单条记录
	 */
	public function getCatInfo($catId){
		$rs = $this->alias('c')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=c.catId and gcl.langId='.WSTCurrLang())
					->field("catName,seoTitle,seoKeywords,seoDes,mobileCatListTheme,showWay")
					->where(['c.catId'=>$catId,'dataFlag'=>1])
					->find();
		return $rs;
	}
}
