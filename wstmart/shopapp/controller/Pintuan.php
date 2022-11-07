<?php 
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\pintuan\model\Pintuans as M;
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
 /***********************************  商家端 **************************************/
/*
 * 拼团插件
 */
class Pintuan extends Controller{
	/**
	 * 加载拼团数据
	 */
	public function pageQuery(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		$rs=$m->pageQueryByShop($shopId);
		return json_encode(WSTReturn('ok',1,$rs));
	}
	/**
	 * 加载开团列表
	 */
	public function openPageQuery(){
		$m = new M();
		return $m->pageQueryByTuan();
	}

	/**
	 * 搜索商品
	 */
	public function searchGoods(){
		$m = new M();
		$shopId = (int)model('shopapp/users')->getUserId();
		return json_encode($m->searchGoods($shopId));
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
			$object['tuanDesc'] = htmlspecialchars_decode($object['tuanDesc']);
		}else{
			$object = $m->getEModel('pintuans');
			$object['marketPrice'] = '';
			$object['goodsName'] = lang("require_chose_goods");
		}
		return json_encode(WSTReturn('ok',1,$object));
	}

	/**
	 * 保存拼团信息
	 */
	public function toEdit(){
		$id = (int)input('post.tuanId');
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
	 * 删除拼团
	 */
	public function del(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		return json_encode($m->del($shopId));
	}
	/**
	 * 下架拼团
	 */
	public function unSale(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		return json_encode($m->unSale($shopId));
	}
    /**
     * 查询订单列表
     */ 
    public function pageQueryByGoods(){
    	$m = new M();
		return $m->pageQueryByGoods();
    }
}