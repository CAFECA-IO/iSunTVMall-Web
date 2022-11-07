<?php
namespace wstmart\shopapp\controller;
use think\addons\Controller;
use addons\presale\model\Presales as M;
class Presale extends Controller{
    /***********************************  商家端 **************************************/
    private function getShopId(){
        $userId = (int)model('shopapp/users')->getUserId();
        $shopId = (int)model('shopapp/shops')->getShopId($userId);
        return $shopId;
    }
    /**
     * 查看预售商品列表
     * @param goodsName 商品名称
     */
    public function pageQuery(){
        $m = new M();
        $shopId = $this->getShopId();
        $rs = $m->pageQueryByShop($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 删除预售商品
     * @param id 商品id
     */
    public function del(){
        $m = new M();
        $shopId = $this->getShopId();
        return json_encode($m->del($shopId));
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
     * 编辑
     * @param
     */
    public function edit(){
        $m = new M();
        $id = (int)input('id');
        $object = [];
        $shopId = $this->getShopId();
        if($id>0){
            $object = $m->getById($shopId);
        }else{
            $object = $m->getEModel('presales');
            $object['goodsName'] = lang("require_chose_goods");
            $object['startTime'] = date('Y-m-d H:00:00',strtotime("+2 hours"));
            $object['endTime'] = date('Y-m-d H:00:00',strtotime("+1 month"));
            foreach (WSTSysLangs() as $key => $lv) {
                $object['langParams'][$lv['id']]['goodsName'] = '';
                $object['langParams'][$lv['id']]['goodsSeoKeywords'] = '';
                $object['langParams'][$lv['id']]['goodsSeoDesc'] = '';
                $object['langParams'][$lv['id']]['goodsTips'] = '';
            }
        }
        return json_encode(WSTReturn('ok',1,$object));
    }

    /**
     * 保存预售信息
     */
    public function toEdit(){
        $id = (int)input('post.id');
        $shopId = $this->getShopId();
        $m = new M();
        if($id==0){
            return json_encode($m->add($shopId));
        }else{
            return json_encode($m->edit($shopId));
        }
    }

    /**
     * 根据id获取数据
     * @param
     */
    public function getById(){
        $m = new M();
        $shopId = $this->getShopId();
        $rs = $m->getById($shopId);
        return json_encode(WSTReturn("", 1, $rs));
    }

    /**
	 * 修改上下架状态
	 */
	public function changeSale(){
        $m = new M();
        $shopId = $this->getShopId();
		return json_encode($m->changeSale($shopId));
	}

}
 ?>
