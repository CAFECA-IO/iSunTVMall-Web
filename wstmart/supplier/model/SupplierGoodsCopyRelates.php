<?php
namespace wstmart\supplier\model;
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
class SupplierGoodsCopyRelates extends Base{
	protected $pk = 'goodsId';
     /**
      *  上架商品列表
      */
	public function pageQuery(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$where = [];
		$where[] = ['supplierId',"=",$supplierId];
		$where[] = ['s.dataFlag',"=",1];
		$shopName = input('shopName');
		if($shopName != ''){
			$where[] = ['shopName|shopSn','like',"%$shopName%"];
		}
		$rs = Db::name("supplier_goods_copyrelates gc")
			->join("shops s","gc.shopId=s.shopId","inner")
			->where($where)
			->field('s.shopId,s.shopName,s.shopSn,s.shopImg,s.shopAddress')
			->order('id', 'desc')
			->group("s.shopId")
			->paginate(input('limit/d'))->toArray();
		$shopIds = [];
		foreach ($rs['data'] as $key => $v){
			$shopIds[] = $v["shopId"];
		}
		$where = [];
		$where[] = ["supplierId","=",$supplierId];
		$where[] = ["shopId","in",$shopIds];
		$list = Db::name("supplier_goods_copyrelates")->where($where)->field("shopId,suppliergoodsId")->group("shopId,suppliergoodsId")->select();
		$gmap = [];
		foreach ($list as $k => $v) {
			$cnt = isset($gmap[$v['shopId']])?$gmap[$v['shopId']]:0;
			$gmap[$v['shopId']] = $cnt+1;
		}
		foreach ($rs['data'] as $key => $v){
			$rs['data'][$key]['gnum'] =  $gmap[$v['shopId']];
		}
		return $rs;
	}
	/**
	 * 审核中的商品
	 */
    public function goodsPageQuery(){
    	$supplierId = (int)session('WST_SUPPLIER.supplierId');
    	$shopId = (int)input("shopId");
    	$where = [];
        $where[] = ['gc.supplierId',"=",$supplierId];
        $where[] = ['gc.shopId',"=",$shopId];
        $where[] = ['gc.dataFlag',"=",1];
		$goodsName = input('goodsName');
		if($goodsName != ''){
			$where[] = ['gl.goodsName|goodsSn','like',"%$goodsName%"];
		}

		$rs = Db::name("supplier_goods_copyrelates gc")
			->join("goods g","gc.goodsId=g.goodsId","inner")
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
			->where($where)
			->field('g.goodsId,gl.goodsName,g.goodsImg,g.goodsSn,g.isSale,g.saleNum,g.shopPrice,gc.createTime')
			->order('createTime', 'desc')
			->paginate(input('limit/d'))->toArray();
        foreach ($rs['data'] as $key => $v){
		}
		return $rs;
	}

	/**
	 * 审核中的商品
	 */
    public function shopPageQuery(){
    	$supplierId = (int)session('WST_SUPPLIER.supplierId');
    	$goodsId = (int)input("goodsId");
		$where = [];
		$where[] = ['supplierId',"=",$supplierId];
		$where[] = ['supplierGoodsId',"=",$goodsId];
		$where[] = ['s.dataFlag',"=",1];
		$shopName = input('shopName');
		if($shopName != ''){
			$where[] = ['shopName|shopSn','like',"%$shopName%"];
		}
		$rs = Db::name("supplier_goods_copyrelates gc")
			->join("shops s","gc.shopId=s.shopId","inner")
			->where($where)
			->field('s.shopId,s.shopName,s.shopSn,s.shopImg,s.shopAddress,gc.createTime')
			->order('id', 'desc')
			->group("s.shopId")
			->paginate(input('limit/d'))->toArray();
		return $rs;
	}

}
