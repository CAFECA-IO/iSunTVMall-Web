<?php
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\groupon\model\Groupons as M;
// 团购活动
class Groupon extends Controller{
	/***********************************  商家端 **************************************/
	/**
	 * 加载团购数据
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
			$object['grouponDesc'] = htmlspecialchars_decode($object['grouponDesc']);
		}else{
			$object = $m->getEModel('groupons');
			$object['marketPrice'] = '';
			$object['goodsName'] = '';
			$object['startTime'] = date('Y-m-d H:00:00',strtotime("+2 hours"));
			$object['endTime'] = date('Y-m-d H:00:00',strtotime("+1 month"));
			foreach (WSTSysLangs() as $key => $lv) {
				$object['langParams'][$lv['id']]['goodsName'] = '';
				$object['langParams'][$lv['id']]['goodsSeoKeywords'] = '';
				$object['langParams'][$lv['id']]['goodsSeoDesc'] = '';
				$object['langParams'][$lv['id']]['grouponDesc'] = '';
			}
		}
		return json_encode(WSTReturn('ok',1,$object));
	}

	/**
	 * 保存团购信息
	 */
	public function toEdit(){
		$id = (int)input('post.grouponId');
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		$m = new M();
		if($id==0){
            return json_encode($m->add($shopId));
		}else{
            return json_encode($m->edit($shopId));
		}
	}

	/**
	 * 删除团购
	 */
	public function del(){
		$m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
		return json_encode(WSTReturn('ok',1,$m->del($shopId)));
	}
    /**
     * 查询订单列表
     */
    public function pageQueryByGoods(){
    	$m = new M();
		return $m->pageQueryByGoods();
    }

}
 ?>
