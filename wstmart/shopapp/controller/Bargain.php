<?php 
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\bargain\model\Shops as M;
class Bargain extends Controller{

	/***********************************  商家端 **************************************/
	//全民砍价
    /**
     * 查看砍价商品列表
     */
    public function pageQuery(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        $rs=$m->pageQuery($shopId);
        if(isset($rs['data'])){
			foreach ($rs['data'] as $k => $v) {
				$rs['data'][$k]['startTime']=date('Y-m-d',strtotime($v['startTime']));
				$rs['data'][$k]['endTime']=date('Y-m-d',strtotime($v['endTime']));
			}
		}
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 新增活动商品
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
            $object = $m->getEModel('bargains');
            $object['goodsName'] = lang("require_chose_goods");
            $object['startTime'] = date('Y-m-d H:00:00',strtotime("+2 hours"));
            $object['endTime'] = date('Y-m-d H:00:00',strtotime("+1 month"));
        }
        return json_encode(WSTReturn('ok',1,$object));
    }
    /**
     * 查询商品
     */
    public function searchGoods(){
        $m = new M();
        $shopId = (int)model('shopapp/users')->getUserId();
        return json_encode($m->searchGoods($shopId));
    }

    /**
     * 保存拍卖信息
     */
    public function toEdit(){
        $id = (int)input('post.bargainId');
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
     * 删除拍卖
     */
    public function del(){
        $m = new M();
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        return json_encode($m->del($shopId));
    }
    /**
     *  获取参与者记录
     */
    public function pageByJoins(){
       $m = new M();
       $shopId = (int)model('shopapp/users')->getUserId();
       return $m->pageByJoins($shopId);
    }
    /**
     * 查看帮助砍价人
     */
    public function showHelps(){
        $this->assign("bargainId",input('bargainId/d'));
        $this->assign("bargainJoinId",input('bargainJoinId/d'));
        return $this->fetch("/shop/list_helps");
    }
    public function pageByHelps(){
        $m = new M();
        return $m->pageByHelps();
    }
    /**
     * 查看订单数
     */
    public function orders(){
        $this->assign("bargainId",input('bargainId/d'));
        return $this->fetch("/shop/list_orders");
    }
    public function pageByOrders(){
        $m = new M();
        return $m->pageByOrders();
    }
}
 ?>
