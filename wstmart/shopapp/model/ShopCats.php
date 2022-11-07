<?php
namespace wstmart\shopapp\model;
use wstmart\common\model\ShopCats as CSC;
class ShopCats extends CSC{
	/**
	* 删除
	*/
	public function del($shopId=0){
		$ids = input("post.ids");
		//把相关的商品下架了
		$gm = new \wstmart\home\model\Goods();
		$gm->whereIn("shopCatId1|shopCatId2",$ids)
			->where(['shopId'=>$shopId])
			->update(['isSale'=>0]);
		//删除商品分类
		$rs = $this->whereIn('catId|parentId',$ids)->where(["shopId"=>$shopId])->update(['dataFlag'=>-1]);
		if(false !== $rs){
			return WSTReturn(lang("op_ok"),1);
		}
		return WSTReturn($this->getError());

	}
}
