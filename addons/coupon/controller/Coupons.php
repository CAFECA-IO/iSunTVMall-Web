<?php
namespace addons\coupon\controller;

use think\addons\Controller;
use addons\coupon\model\Coupons as M;
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
class Coupons extends Controller{
	public function __construct(){
		parent::__construct();
		$m = new M();
		$data = $m->getConf('Coupon');
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
		$this->assign("seoKeywords",$data['seoKeywords']);
        $this->assign("seoDesc",$data['seoDesc']);
	}

   /**
    * 领券中心
    */
	public function index(){
		$catId = (int)input('catId');
        $orderBy = (int)input('orderBy');
        $order = (int)input('order');
        $data = [];
        $data['couponCatId'] = $catId;
        $m = new M();
        $data['couponPage'] = $m->pageCouponQuery();
        $cats = WSTGoodsCats(0);
        $catName = lang('coupon_all_goods_cat');
        foreach($cats as $k => $v){
            if($catId==$v['catId'])$catName = $v['catName'];
        }
        $data['catName'] = $catName;
        $data['catList'] = $cats;
		return $this->fetch("/home/index/list",$data);
	}

    /**
     * 领取优惠券
     */
    public function receive(){
        $this->checkAuth();
        $m = new M();
        return $m->receive();
    }

    /*
     * 获取指定商品的优惠券
     */
    public function getCouponsByGoods(){
        $m = new M();
        return $m->getCouponsByGoods();
    }

    /*
     * 获取指定店铺的优惠券
     */
    public function getCouponsByShop(){
        $m = new M();
        return $m->getCouponsByShop();
    }
    /*
    * 获取优惠券可用的商品
    */
    public function goods(){
        $m = new M();
        //获取优惠券信息
        $rs = $m->getByView((int)input('couponId/d'));
        $this->assign("coupon",$rs);
        //获取商品信息
        $page = $m->pageQueryByCouponGoods();
        $this->assign("couponPage",$page);
        return $this->fetch("/home/index/list_goods");
    }

    /***************************************************************** 手机版 ****************************************************************************/
    /*
    * 领券中心
    */
    public function moindex(){
        $this->assign('catId',0);
        return $this->fetch('/mobile/index/list');
    }
    /**
    *  手机版可用优惠券商品页面
    */
    public function moCouponGoods(){
        $this->assign('couponId',(int)input('couponId'));
        return $this->fetch("/mobile/index/goods_list");
    }
    /***************************************************************** 微信版 ****************************************************************************/
    /*
    * 领券中心
    */
    public function wxindex(){
        $this->assign('catId',0);
        return $this->fetch('/wechat/index/list');
    }
    /**
    *  微信版可用优惠券商品页面
    */
    public function wxCouponGoods(){
        $this->assign('couponId',(int)input('couponId'));
        return $this->fetch("/wechat/index/goods_list");
    }
    /*
    * 领券中心列表查询
    */
    public function pageCouponQuery(){
        $m = new M();
        return $m->pageCouponQuery();
    }
    
    /**
    *  可用优惠券商品查询
    */
    public function pageQueryByCouponGoods(){
        $m = new M();
        return $m->pageQueryByCouponGoods();
    }
    
    /**
     *  领取的优惠券数
     */
    public function couponsNum(){
    	$m = new M();
    	return $m->couponsNum();
    } 
}
