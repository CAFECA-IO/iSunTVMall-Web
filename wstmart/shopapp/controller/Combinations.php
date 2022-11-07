<?php 
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\combination\model\Combinations as M;
class Combinations extends Controller{
	/***********************************  商家端 **************************************/
    /**
     * 查看商品组合列表
     * @param combineName 商品组合名称
     */
    public function pageQuery(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->pageQueryByShop($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 删除商品组合
     * @param id 商品组合id
     */
    public function del(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        return json_encode($m->del($shopId));
    }

    /**
     * 修改商品组合状态
     * @param id 商品组合id
     * @param combineStatus 状态 1:启用 0:暂停
     */
    public function changeStatus(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        return json_encode($m->changeStatus($shopId));
    }

    /**
     * 获取在售商品列表
     * @param cat1 店铺一级分类
     * @param cat2 店铺二级分类
     * @param goodsName 商品名称
     */
    public function saleGoodsPageQuery(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->saleGoodsPageQuery($shopId);
        return json_encode(WSTReturn("", 1, $rs));
    }

    /**
     * 新增
     * @param 
     */
    public function add(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->add($shopId);
        return json_encode($rs);
    }
    /**
     * 编辑
     * @param 
     */
    public function edit(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->edit($shopId);
        return json_encode($rs);
    }
    /**
     * 根据id获取数据
     * @param 
     */
    public function getById(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->getById((int)input('combineId'), $shopId);
        return json_encode(WSTReturn("", 1, $rs));
    }

}
 ?>
