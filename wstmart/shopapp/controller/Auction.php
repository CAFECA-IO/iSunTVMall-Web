<?php
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\auction\model\Auctions as M;
class Auction extends Controller{

	/***********************************  商家端 **************************************/
	//拍卖
	/**
	 * 加载拍卖数据
	 */
	public function pageQuery(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		$rs=$m->pageQueryByShop($shopId);
		if(isset($rs['data'])){
			foreach ($rs['data'] as $k => $v) {
				$rs['data'][$k]['startTime']=date('Y-m-d',strtotime($v['startTime']));
				$rs['data'][$k]['endTime']=date('Y-m-d',strtotime($v['endTime']));
			}
		}
		return json_encode(WSTReturn('ok',1,$rs));
	}

	/**
	 * 搜索商品
	 */
	public function searchGoods(){
		$m = new M();
		$userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		$rs=$m->searchGoods($shopId);
		return json_encode($rs);
	}

	/**
	 * 跳去编辑页面
	 */
	public function edit(){
		$m = new M();
		$id = (int)input('id');
		$object = [];
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		if($id>0){
            $object = $m->getById($id,$shopId);
		}else{
			$object = $m->getEModel('auctions');
			$object['marketPrice'] = '';
			$object['goodsName'] = lang("require_chose_goods");
            $object['startTime'] = date('Y-m-d H:00:00',strtotime("+2 hours"));
            $object['endTime'] = date('Y-m-d H:00:00',strtotime("+1 month"));
			foreach (WSTSysLangs() as $key => $lv) {
				$object['langParams'][$lv['id']]['goodsName'] = '';
				$object['langParams'][$lv['id']]['goodsSeoKeywords'] = '';
				$object['langParams'][$lv['id']]['goodsSeoDesc'] = '';
				$object['langParams'][$lv['id']]['auctionDesc'] = '';
			}
		}
		return json_encode(WSTReturn('ok',1,$object));
	}

	/**
	 * 保存拍卖信息
	 */
	public function toEdit(){
		$m = new M();
		$id = (int)input('post.auctionId');
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		if($id==0){
            return json_encode($m->add($shopId));
		}else{
            return json_encode($m->edit($shopId));
		}
	}

	/**
	 * 删除拍卖
	 */
	public function del(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		return json_encode($m->del($shopId));
	}
    /**
     * 查询拍卖参与者列表
     */
    public function pageAuctionLogQueryByShops(){
		$m = new M();
		$userId = (int)model('shopapp/users')->getUserId();
		$shopId = (int)model('shopapp/shops')->getShopId($userId);
		return json_encode($m->pageAuctionLogQuery((int)input('id'),false,$shopId));
    }
}
 ?>
