<?php
namespace addons\presale\controller;

use think\addons\Controller;
use addons\presale\model\Presales as M;
use addons\presale\model\Orders as OM;
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
 * 预售活动插件
 */
class Shops extends Controller{
	/**
	 * 预售列表
	 */
	public function index(){
		$this->checkShopAuth();
	    $this->assign("p",(int)input("p"));
    	return $this->fetch("/shop/list");
	}
	/**
	 * 加载预售数据
	 */
	public function pageQuery(){
		$this->checkShopAuth();
		$m = new M();
		return WSTGrid($m->pageQueryByShop());
	}
	/**
	 * 跳去编辑页面
	 */
	public function edit(){
		$this->checkShopAuth();
		$id = (int)input('id');
		$object = [];
		$m = new M();
		if($id>0){
            $object = $m->getById();
		}else{
			$object = $m->getEModel('presales');
			$object['startTime'] = date('Y-m-d H:00:00',strtotime("+2 hours"));
			$object['endTime'] = date('Y-m-d H:00:00',strtotime("+1 month"));
			$object['shopPrice'] = '0';
		    $object['isSpec'] = '0';
		    $object['minShopPrice'] = '0';
		    $object['maxShopPrice'] = '0';
            foreach (WSTSysLangs() as $key => $lv) {
                $object['langParams'][$lv['id']]['goodsName'] = '';
                $object['langParams'][$lv['id']]['goodsTips'] = '';
                $object['langParams'][$lv['id']]['goodsSeoKeywords'] = '';
                $object['langParams'][$lv['id']]['goodsSeoDesc'] = '';
            }
		}
		$this->assign("object",$object);
        $this->assign("p",(int)input("p"));
		return $this->fetch("/shop/edit");
	}

    /**
	 * 保存信息
	 */
	public function toEdit(){
		$this->checkShopAuth();
		$id = (int)input('post.id');
		$m = new M();
		if($id==0){
            return $m->add();
		}else{
            return $m->edit();
		}
	}
    /**
	 * 删除
	 */
	public function del(){
		$this->checkShopAuth();
		$m = new M();
		return $m->del();
	}

	/**
	 * 搜索商品
	 */
	public function searchGoods(){
		$this->checkShopAuth();
		$m = new M();
		return $m->searchGoods();
	}

	/**
	 * 查看团购订单列表
	 */
    public function orders(){
    	$this->checkShopAuth();
    	$this->assign("presaleId",(int)input('presaleId'));
        $this->assign("p",(int)input("p"));
    	return $this->fetch("/shop/list_orders");
    }
    /**
     * 查询订单列表
     */
    public function pageQueryOrders(){
    	$this->checkShopAuth();
    	$m = new OM();
		return $m->pageQueryOrders();
    }

	/**
	 * 获取预售单列表
	 */
	public function toView(){
		$this->checkShopAuth();
		$m = new OM();
		$userId = (int)session('WST_USER.userId');
		$shopId = (int)session('WST_USER.shopId');
		$rs = $m->getOrderDetail($userId,$shopId);
		$rs['goodsImg'] = WSTImg($rs['goodsImg'],3);
		$rs['payInfo'] = WSTLangPayType($rs['payType']);
		$rs['deliverInfo'] = WSTLangDeliverType($rs['deliverType']);
		$this->assign('object',$rs);
		return $this->fetch("/shop/view");
	}

	/**
	 * 修改上下架状态
	 */
	public function changeSale(){
		$this->checkShopAuth();
    	$m = new M();
		return $m->changeSale();
	}
}
