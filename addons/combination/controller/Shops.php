<?php
namespace addons\combination\controller;

use think\addons\Controller;
use addons\combination\model\Combinations as M;
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
 * 商品组合活动插件
 */
class Shops extends Controller{
	/**
	 * 拍卖列表
	 */
	public function index(){
		$this->checkShopAuth();
	    $this->assign("p",(int)input("p"));
    	return $this->fetch("/shop/list");
	}
	/**
	 * 加载拍卖数据
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
            $object = $m->getById($id);
		}else{
			$object = $m->getEModel('combinations');
			$object['startTime'] = date('Y-m-d H:00:00',strtotime("+2 hours"));
			$object['endTime'] = date('Y-m-d H:00:00',strtotime("+1 month"));
			$object['combineGoodsIds'] = '';
			$object['goodsId'] = 0;
			$object['list'] = [];
            foreach (WSTSysLangs() as $key => $lv) {
                $object['langParams'][$lv['id']]['combineName'] = '';
                $object['langParams'][$lv['id']]['combineDesc'] = '';
            }
		}
		$this->assign("object",$object);
        $this->assign("p",(int)input("p"));
		return $this->fetch("/shop/edit");
	}

	/**
	 * 获取在售商品列表
	 */
	public function saleGoodsPageQuery(){
		$this->checkShopAuth();
        $m = new M();
		return WSTGrid($m->saleGoodsPageQuery());
	}

	/**
	 * 根据商品ID获取商品信息
	 */
	public function listQueryByGoodsIds(){
		$this->checkShopAuth();
        $m = new M();
		return $m->listQueryByGoodsIds();
	}
    /**
	 * 保存信息
	 */
	public function toEdit(){
		$this->checkShopAuth();
		$id = (int)input('post.combineId');
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
	 * 修改状态
	 */
	public function changeStatus(){
		$this->checkShopAuth();
		$m = new M();
		return $m->changeStatus();
	}

}
