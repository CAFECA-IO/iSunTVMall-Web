<?php
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\live\model\LiveApplys as M;
use addons\live\model\LiveGoodsApplys as LGA;
class Live extends Base{
    // 前置方法执行列表
    protected $beforeActionList = [
        'checkAuth'=>['except'=>'livecfg']
    ];

    private $access_token;
    public function __construct(){
        parent::__construct();
        if(WSTConf('CONF.weAppId')!='' && WSTConf('CONF.weAppKey')!=''){
            $weapp = new \weapp\WSTWeapp(WSTConf('CONF.weAppId'), WSTConf('CONF.weAppKey'));
            $this->access_token = $weapp->getToken();
        }
    }

	/***********************************  商家端 **************************************/
	//小程序直播
    /**
     * 加载小程序直播插件状态
     */
    public function liveCfg(){
        // 获取插件状态
        $addonCfg = WSTGetAddonStatus("Live");
        if(!empty($addonCfg)){
            $rs["addonStatus"] = $addonCfg['status'];
        }
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 查看直播间申请列表
     */
    public function pageQuery(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs=$m->pageQuery($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 新增直播间申请
     */
    public function edit(){
        $id = (int)input('id');
        $object = [];
        $m = new M();
        $lga = new LGA();
        if($id>0){
            $object = $m->getById($id);
            $object['goodsData'] = $m->getLiveGoodsApplys($id);
            $object['goods'] = $lga->listQueryByGoods();
        }else{
            $object = $m->getEModel('live_applys');
            $object['startTime'] = date('Y-m-d H:00:00',strtotime("+2 hours"));
            $object['endTime'] = date('Y-m-d H:00:00',strtotime("+3 hours"));
            $object['goodsData'] = [];
        }
        return json_encode(WSTReturn('ok',1,$object));
    }


    /**
     * 保存直播间申请信息
     */
    public function toEdit(){
        $id = (int)input('post.id');
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $m = new M();
        if($id==0){
            return json_encode($m->add($this->access_token,$shopId));
        }else{
            return json_encode($m->edit($this->access_token,$shopId));
        }
    }

    /**
     * 删除直播间申请
     */
    public function del(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        return json_encode($m->del($shopId));
    }

    /**
     * 小程序直播商品库分页
     */
    public function liveGoodsPageQuery(){
        $m = new LGA();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs=$m->pageQuery(0,$shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 去处理直播间申请
     */
    public function handleApply(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        return json_encode($m->handleApply($this->access_token,$shopId));
    }

    /*
     * 同步小程序直播商品库的商品审核状态
     */
    public function syncLiveGoodsAuditStatus(){
        $m = new LGA();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->syncLiveGoodsAuditStatus($this->access_token,0,$shopId);
        return json_encode($rs);
    }

    /*
     * 删除小程序直播商品库的商品
     */
    public function delGood(){
        $m = new LGA();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->delGood($this->access_token,0,$shopId);
        return json_encode($rs);
    }

    /*
     * 修改小程序直播商品库的商品信息
     */
    public function editGood(){
        $m = new LGA();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->editGood($this->access_token,0,$shopId);
        return json_encode($rs);
    }

    /*
     * 获取直播间商品信息
     */
    public function getGoodById(){
        $m = new LGA();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->getGoodById(0,$shopId);
        return json_encode(WSTReturn('',1,$rs));
    }

    /*
     * 查找已审核通过的直播商品
     */
    public function searchGoods(){
        $m = new LGA();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs = $m->searchGoods($shopId);
        return json_encode(WSTReturn('',1,$rs));
    }

    /**
     * 获取已选择的直播商品
     */
    public function listQueryByGoods(){
        $m = new LGA();
        $rs= $m->listQueryByGoods();
        return json_encode(WSTReturn("", 1,$rs));
    }

    /**
     * 保存直播间商品
     */
    public function editLiveGoods(){
        $m = new LGA();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        return json_encode($m->editLiveGoods($this->access_token,$shopId));
    }
}
 ?>
