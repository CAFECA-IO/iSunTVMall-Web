<?php
namespace addons\coupon\controller;

use think\addons\Controller;
use addons\coupon\model\Coupons as M;
use wstmart\shop\model\ShopMemberGroups;
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
 * 优惠券插件
 */
class Shops extends Controller{
	protected $beforeActionList = ['checkShopAuth' ];
	public function __construct(){
		parent::__construct();
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}
	/**
	 * 优惠券列表
	 */
	public function index(){
	    $this->assign("p",(int)input("p"));
    	return $this->fetch("/shop/list");
	}
	/**
	 * 加载优惠券数据
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
            $object = $m->getById($id);
		}else{
			$object = $m->getEModel('coupons');
			$object['goods'] = [];
		}
		$this->assign("object",$object);
        $this->assign("p",(int)input("p"));
		return $this->fetch("/shop/edit");
	}

	/**
	 * 保存优惠券信息
	 */
	public function toEdit(){
		$id = (int)input('post.couponId');
		$m = new M();
		if($id==0){
            return $m->add();
		}else{
            return $m->edit();
		}
	}

	/**
	 * 删除优惠券
	 */
	public function del(){
		$m = new M();
		return $m->del();
	}

	/**
	 * 查询商品
	 */
	public function searchGoods(){
		$m = new M();
		return $m->searchGoods();
	}

	/**
	 * 查看优惠券领取情况
	 */
	public function coupons(){
        $this->assign("p",(int)input("p"));
        $this->assign("id",(int)input("id"));
		return $this->fetch("/shop/coupons");
	}
	/**
	 * 加载优惠券列表
	 */
	public function pageQueryByCoupons(){
		$m = new M();
		return WSTGrid($m->pageQueryByCoupons());
	}
	/**
	 * 跳去统计
	 */
	public function toStat(){
		$this->assign("p",(int)input("p"));
        $this->assign("id",(int)input("id"));
		return $this->fetch("/shop/stat");
	}
	/**
	 * 加载统计
	 */
	public function stat(){
		$m = new M();
		return $m->stat();
	}

    /**
     * 发放优惠券
     */
    public function toGive(){
        $m = new M();
        return $m->give();
    }

    /**
     * 跳去发放优惠券的页面
     */
    public function toGiveCoupon(){
        $this->assign("p",(int)input("p"));
        $this->assign("couponId",(int)input("id"));
        $s = new ShopMemberGroups();
        $shopMemberGroups = $s->pageQuery();
        $this->assign("shopMemberGroups",$shopMemberGroups['data']?$shopMemberGroups['data']:[]);
        return $this->fetch("/shop/give_coupon");
    }

    /*
     * 查询订单
     */
    public function searchOrder(){
        $m = new M();
        return $m->searchOrder();
    }

    /*
     * 查询会员分组下的会员
     */
    public function searchMemberGroupUsers(){
        $m = new M();
        $rs = $m->searchMemberGroupUsers();
        return WSTReturn("", 1,$rs);
    }
}
