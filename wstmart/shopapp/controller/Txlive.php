<?php
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\txlive\model\TxLive as DM;
use addons\txlive\model\TxLives as M;
class Txlive extends Base{
    // 前置方法执行列表
    protected $beforeActionList = [
        'checkAuth'
    ];

	/***********************************  商家端 **************************************/

    /**
     * 跳去新增直播间页面请求基础数据
     */
    public function toEdit(){
        $object = [];
        $m = new M();
        $dm = new DM();
        $object = $m->getEModel('tx_lives');
        $config = $dm->getAddonConfig();
        $object['expireTime'] = date('Y-m-d 23:59:59');
        $object['licenceUrl'] = isset($config['licenceUrl'])?$config['licenceUrl']:'';
        $object['licenceKey'] = isset($config['licenceKey'])?$config['licenceKey']:'';
        return json_encode(WSTReturn('ok',1,$object));
    }

    /*
     * 新增直播
     */
    public function add(){
        $dm = new DM();
        $m = new M();
        $config = $dm->getAddonConfig();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->add($userId,$shopId,$config);
        return json_encode($rs);
    }

    /*
     * 获取直播间信息
     */
    public function getById(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $rs = $m->getById($userId);
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /*
     * 将直播间状态改为已结束
     */
    public function changeLiveStatus(){
        $m = new M();
        $rs = $m->changeLiveStatus();
        return json_encode(WSTReturn('ok',1,$rs));
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

    /**
     * 编辑直播商品
     */
    public function editLiveGoods(){
        $m = new M();
        return json_encode($m->editLiveGoods());
    }

    /**
     * 查看直播间列表
     */
    public function pageQuery(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs=$m->pageQuery($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 删除直播间
     */
    public function del(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        return json_encode($m->del($shopId));
    }
}
 ?>
