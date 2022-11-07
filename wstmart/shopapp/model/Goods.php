<?php
namespace wstmart\shopapp\model;
use wstmart\common\model\Goods as CGoods;
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
 * 商品类
 */
class Goods extends CGoods{
	/*********************************************************  商家操作商品start ******************************************************************/
	/**
	* 获取商品分类
	*/
	public function getGoodsCats($shopId){
		$catIds =  Db::name('goods_cats')->alias('gc')
	             ->join('__CAT_SHOPS__ csp','gc.catId=csp.catId')
	             ->where(['dataFlag'=>1, 'isShow' => 1,'csp.shopId'=>$shopId])
	             ->column('gc.catId');
		$data = Db::name('goods_cats')
                ->alias('gc')
                ->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
                ->field('gc.catId,gcl.catName,parentId')
                ->where(['dataFlag'=>1])
                ->select();
		$fn = function ($data, $parentId=0) use(&$fn){//use参数传递的是函数闭包函数自身
	        $arr = array();
		    foreach($data as $k=>$v)
		    {
		        if($v['parentId']==$parentId)
		        {
		            //再查找该分类下是否还有子分类
		            $v['children'] = $fn($data, $v['catId']);
		            //将找到的分类放回该数组中
		            $arr[]=$v;
		        }
		    }
		    return $arr;
	    };
	    $rs = $fn($data);
	    $_rs = [];
	    foreach ($rs as $k => $v) {
	    	if(in_array($v['catId'], $catIds))array_push($_rs, $v);
	    }
	    return $_rs;
	}


	/**
	* 修改商品状态【可同时修改多个状态】
	*/
	public function changSaleStatus($shopId){
		$data = input('param.');
		$allowArr = ['isHot','isNew','isBest','isRecom'];
		$data = array_filter($data,function($key,$value) use ($allowArr){
			return in_array($value, $allowArr);
		},true);
		$id = (int)input('post.id');
		$rs = $this->where(["shopId"=>$shopId,'goodsId'=>$id])->update($data);
		if($rs!==false){
			return WSTReturn(lang("op_ok"),1);
		}
		return WSTReturn($this->getError(),-1);
	}



	/*********************************************************  商家操作商品end ******************************************************************/
}
