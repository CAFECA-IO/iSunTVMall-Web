<?php
namespace addons\reward\controller;

use think\addons\Controller;
use addons\reward\model\Rewards as M;
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
 * 满就送插件
 */
class Shops extends Controller{
	protected $beforeActionList = ['checkShopAuth'];
	public function __construct(){
		parent::__construct();
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}
	/**
	 * 满就送列表
	 */
	public function index(){
	    $this->assign("p",(int)input("p"));
    	return $this->fetch("/shop/list");
	}
	/**
	 * 加载活动数据
	 */
	public function pageQuery(){
		$m = new M();
		return WSTGrid($m->pageQueryByShop());
	}

	/**
	 * 跳去编辑页面
	 */
	public function edit(){
		$id = (int)input('id');
		$object = [];
		$m = new M();
		if($id>0){
            $object = $m->getById();
		}else{
			$object = $m->getEModel('rewards');
			$object['goods'] = [];
		}
		$this->assign("object",$object);
        $this->assign("p",(int)input("p"));
		return $this->fetch("/shop/edit");
	}

	/**
	 * 保存活动信息
	 */
	public function toEdit(){
		$id = (int)input('post.rewardId');
		$m = new M();
		if($id==0){
            return $m->add();
		}else{
            return $m->edit();
		}
	}

	/**
	 * 删除活动
	 */
	public function del(){
		$m = new M();
		return $m->del();
	}
	/**
	 * 获取在售商品
	 */
	public function getSaleGoods(){
		$m = new M();
		return $m->getSaleGoods();
	}
	/**
	 * 获取优惠券
	 */
	public function getCoupons(){
		$m = new M();
		return $m->getCoupons();
	}

	/**
	 * 查询商品
	 */
	public function searchGoods(){
		$m = new M();
		return $m->searchGoods();
	}
}