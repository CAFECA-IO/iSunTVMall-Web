<?php 
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\coupon\model\Coupons as M;
class Coupon extends Controller{
	/***********************************  商家端 **************************************/
	/**
	 * 加载优惠券数据
	 */
	public function pageQuery(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		$rs=$m->pageQueryByShop($shopId);
		return json_encode(WSTReturn('ok',1,$rs));
	}

	/**
	 * 跳去编辑页面
	 */
	public function edit(){
		$id = (int)input('id');
		$object = [];
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		if($id>0){
            $object = $m->getById($id,$shopId);
		}else{
			$object = $m->getEModel('coupons');
			$object['goods'] = [];
		}
		return json_encode(WSTReturn('ok',1,$object));
	}
	/**
	 * 获取在售商品
	 */
	public function getSaleGoods(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		return json_encode($m->getSaleGoods($shopId));
	}
	/**
	 * 保存优惠券信息
	 */
	public function toEdit(){
        $m = new M();
		$id = (int)input('post.couponId');
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		if($id==0){
            return json_encode($m->add($shopId));
		}else{
            return json_encode($m->edit($shopId));
		}
	}

	/**
	 * 删除优惠券
	 */
	public function del(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		return json_encode($m->del($shopId));
	}

	/**
	 * 查询商品
	 */
	public function searchGoods(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		return json_encode($m->searchGoods($shopId));
	}
}
 ?>
