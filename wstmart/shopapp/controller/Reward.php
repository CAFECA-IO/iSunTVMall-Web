<?php 
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\reward\model\Rewards as M;
// 满就送
class Reward extends Controller{
	/**
	 * 加载活动数据
	 */
	public function pageQuery(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		$rs=$m->pageQueryByShop($shopId);
		$rewardStatus=input('rewardStatus');
		$arr=[];
		foreach($rs['data'] as $k => $v) {
			if($rewardStatus!=$rs['data'][$k]['rewardStatus']){
				unset($rs['data'][$k]);
			}else{
				array_push($arr,$rs['data'][$k]);
			}
		}
		$rs['data']=$arr;
		return json_encode(WSTReturn('ok',1,$rs));
	}

	/**
	 * 跳去编辑页面
	 */
	public function edit(){
		$id = (int)input('id');
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		$object = [];
		$m = new M();
		if($id>0){
            $object = $m->getById($shopId);
		}else{
			$object = $m->getEModel('rewards');
			$object['goods'] = [];
		}
		return json_encode(WSTReturn('ok',1,$object));
	}

	/**
	 * 保存活动信息
	 */
	public function toEdit(){
		$id = (int)input('post.rewardId');
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		if($id==0){
            return json_encode($m->add($shopId));
		}else{
            return json_encode($m->edit($shopId));
		}
	}

	/**
	 * 删除活动
	 */
	public function del(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		return json_encode($m->del($shopId));
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
	 * 获取优惠券
	 */
	public function getCoupons(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->getCoupons($shopId);
        if(isset($rs['data'])){
        	// 生成优惠券标题
        	foreach($rs['data'] as $k=>$v){
				$couponTitle = lang("no_condition",['couponValue'=>$v['couponValue'], "currencySymbol"=>lang("currency_symbol")]);
        		if($v['useCondition']==1){
					$tag = $v['useObjectIds']!=''?lang("some_goods"):lang("all_goods");
        			$couponTitle = $tag.lang("coupon_title",['useMoney'=>$v['useMoney'], 'couponValue'=>$v['couponValue']]);
	    		}
        		$rs['data'][$k]['couponTitle'] = $couponTitle;
        	}
        }
		return json_encode($rs);
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
