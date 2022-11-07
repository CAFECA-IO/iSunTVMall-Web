<?php 
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\distribut\model\Distribut as M;
// 分销
class Distribut extends Controller{
    /***********************************  商家端 **************************************/
	/**
     * 加载店铺分销设置
     */
    public function shopDistributCfg(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->getDistributCfg($shopId);
        // 获取插件状态
        $addonCfg =  WSTGetAddonStatus("Distribut");
        if(!empty($addonCfg) && is_array($rs)){
             $rs["addonStatus"] = $addonCfg['status'];
        }
        return json_encode(WSTReturn('ok',1,$rs));
    }
    
    /**
     * 保存店铺设置
     */
    public function saveCfg(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->saveCfg($shopId);
        return json_encode($rs);
    }
    
    /**
     * 获取店铺分销商品列表
     */
    public function queryDistributGoods(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->querydistributgoods($shopId);
        $rs['status'] = 1;
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 获取店铺分成列表
     */
    public function queryDistributMoneys(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->queryDistributMoneys($shopId);
        $rs['status'] = 1;
        return json_encode(WSTReturn('ok',1,$rs));
    }
}
 ?>