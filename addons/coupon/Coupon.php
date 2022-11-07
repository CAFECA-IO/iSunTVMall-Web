<?php
namespace addons\coupon;

use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\coupon\model\Coupons as DM;

/**
 * WSTMart 优惠券
 * @author WSTMart
 */
class Coupon extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Coupon',   // 插件标识
        'title' => '優惠券',  // 插件名称
        'description' => '營銷插件-優惠券',    // 插件简介
        'status' => 0,  // 状态
        'author' => 'WSTMart',
        'version' => '1.0.0'
    ];


    /**
     * 插件安装方法
     * @return bool
     */
    public function install(){
        $m = new DM();
        $lang = WSTSwitchLang();  
        $langFile = Env::get('root_path').'addons'.DS.'coupon'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->installMenu();
        WSTLangAddonJs('coupon');
        WSTClearHookCache();
        return $flag;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall(){
        $m = new DM();
        $flag = $m->uninstallMenu();
        WSTClearHookCache();
        return $flag;
    }

	/**
     * 插件启用方法
     * @return bool
     */
    public function enable(){
        $m = new DM();
        $lang = WSTSwitchLang();  
        $langFile = Env::get('root_path').'addons'.DS.'coupon'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('coupon');
        WSTClearHookCache();
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable(){
        $m = new DM();
        $flag = $m->toggleShow(0);
        WSTClearHookCache();
    	return true;
    }

    /**
     * 插件设置方法
     * @return bool
     */
    public function saveConfig(){
    	WSTClearHookCache();
    	return true;
    }
    /**
     * 商品列表
     */
    public function afterQueryGoods($params){
        $m = new DM();
        foreach ($params["page"]["data"] as $key => $v){
            $rs = $m->getGoodsCouponTags($v['goodsId']);
            if($rs>0){
                if(isset($params['isApp'])){
                    $params["page"]["data"][$key]['isCoupon'] = true;
                }else{
                    $params["page"]["data"][$key]['tags'][] = '<span class="tag">'.lang('coupon_ticket').'</span>';
                }
            }
        }
    }
    /**
     * 商品详情页价格区域
     */
    public function homeDocumentGoodsPropDetail(){
        return $this->fetch('view/home/index/coupon');
    }
    /**
     * 购物车栏
     */
    public function homeDocumentCartShopPromotion($params){
        $m = new DM();
        $rs = $m->getCouponsByShop($params['shop']['shopId']);
        $this->assign("coupons",$rs['data']['coupons']);
        $this->assign("shopId",$params['shop']['shopId']);
        return $this->fetch('view/home/index/cart_coupon');
    }

    /**
     * 查询购物车之后执行代码
     */
    public function afterQueryCarts($params){
        if($params['isSettlement']){
            $m = new DM();
            foreach ($params['carts']['carts'] as $key => $v) {
                $params['carts']['carts'][$key]['coupons'] = $m->getAvailableCoupons($v['list'],$v['shopId'],$params['uId']);
            }
        }
    }

    /**
     * 计算订单金额
     */
    public function afterCalculateCartMoney($params){
        $m = new DM();
        if($params['isVirtual']){
            $m->calculateVirtualCartMoney($params);
        }else{
            $m->calculateCartMoney($params);
        }
    }

    /**
     * 新增订单前执行
     */
    public function beforeInsertOrder($params){
        $m = new DM();
        $m->beforeInsertOrder($params);
    }

    /**
     * pc版订单详情展示
     */
    public function homeDocumentOrderSummaryView($params){
        if($params['order']['userCouponId']>0){
            $params['order']['userCouponJson'] = json_decode($params['order']['userCouponJson'],true);
            $this->assign('order', $params['order']);
            return $this->fetch('view/home/view');
        }
    }
    /**
     * pc版商家订单详情展示
     */
    public function shopDocumentOrderSummaryView($params){
        $hook = '';
        if($params['order']['userCouponId']>0){
            $params['order']['userCouponJson'] = json_decode($params['order']['userCouponJson'],true);
            if(isset($params['isOrderVerificat']) && $params['isOrderVerificat']==1){
                $hook = "<div class='summary'>".lang('coupon_preferential')."：".lang('currency_symbol')."-<span style='color: red'>{$params['order']['userCouponJson']['money']}</span></div>";
                $params['order']['hook'] = $hook;
            }else{
                $this->assign('order', $params['order']);
                return $this->fetch('view/shop/view');
            }
        }
    }
    /*
     * pc版商家会员列表发放优惠券按钮
     */
    public function shopDocumentGiveUserCouponButton($params){
        $type = $params['type'];
        $this->assign('type', $type);
        return $this->fetch('view/shop/members');
    }
    /*
     * pc版商家会员列表发放优惠券
     */
    public function shopDocumentGiveUserCoupon(){
        return $this->fetch('view/shop/give');
    }
    /**
     * 管理员订单详情展示
     */
    public function adminDocumentOrderSummaryView($params){
        if($params['order']['userCouponId']>0){
            $params['order']['userCouponJson'] = json_decode($params['order']['userCouponJson'],true);
            $this->assign('order', $params['order']);
            return $this->fetch('view/admin/view');
        }
    }
    /**
     * 删除店铺时操作
     */
    public function afterChangeShopStatus($params){
        $m = new DM();
        $m->afterChangeShopStatus($params);
    }
    /**
     * 计算页面-店铺展示
     */
    public function homeDocumentSettlementShopSummary($params){
        $this->assign('coupons', $params['coupons']);
        $this->assign('shopId', $params['shopId']);
        return $this->fetch('view/home/index/settlement_shop');
    }
    /**
     * 手机用户“我的”
     */
    public function mobileDocumentUserIndexTools(){
        return $this->fetch('view/mobile/users/index');
    }
    /**
     * 微信用户“我的”
     */
    public function wechatDocumentUserIndexTools(){
        return $this->fetch('view/wechat/users/index');
    }
    /**
     * 手机用户“我的”
     */
    public function mobileDocumentUserIndexTerm(){
    	return $this->fetch('view/mobile/users/term');
    }
    /**
     * 微信用户“我的”
     */
    public function wechatDocumentUserIndexTerm(){
    	return $this->fetch('view/wechat/users/term');
    }

    /**
     * 手机版订单结算页面
     */
    public function mobileDocumentCartShopPromotion($params){
        $this->assign('coupons',$params['coupons']);
        $this->assign('shopId',$params['shopId']);
        return $this->fetch('view/mobile/users/coupon');
    }
    /**
    * 手机版订单详情
    */
    public function mobileDocumentOrderSummaryView($params){
        $hook = '';
        if($params['rs']['userCouponId']>0){
            // 获取优惠券信息
            $money = json_decode($params['rs']['userCouponJson'],true)['money']; // 优惠券优惠金额
            $hook = "<div class='ui-row-flex'><div class='ui-col ui-col wst-or-term'><span class='wst-or-describe2'>".lang('coupon_preferential')."</span><span class='o-status2'>".lang('currency_symbol')."-".number_format($money,2)."</span></div></div>";
        }
        $params['rs']['hook'] = $hook;
    }
    /**
    * 手机版商品详情
    */
    public function mobileDocumentGoodsPropDetail(){
        return $this->fetch('view/mobile/index/goods_detail_coupon');
    }
    /**
    * 微信版订单详情
    */
    public function wechatDocumentOrderSummaryView($params){
        $hook = '';
        if($params['rs']['userCouponId']>0){
            // 获取优惠券信息
            $money = json_decode($params['rs']['userCouponJson'],true)['money']; // 优惠券优惠金额
            $hook = "<div class='ui-row-flex'><div class='ui-col ui-col wst-or-term'><span class='wst-or-describe2'>".lang('coupon_preferential')."</span><span class='o-status2'>".lang('currency_symbol')."-".number_format($money,2)."</span></div></div>";
        }
        $params['rs']['hook'] = $hook;
    }
    /**
     * 微信版订单结算页面
     */
    public function wechatDocumentCartShopPromotion($params){
        $this->assign('coupons',$params['coupons']);
        $this->assign('shopId',$params['shopId']);
        return $this->fetch('view/wechat/users/coupon');
    }
    /**
    * 微信版商品详情
    */
    public function wechatDocumentGoodsPropDetail(){
        return $this->fetch('view/wechat/index/goods_detail_coupon');
    }

    /**
     * 线下收银订单支付前执行
     */
    public function offlineBeforeOrderPay($params){
         $m =new DM();
        return $m->offlineBeforeOrderPay($params);
    }

    /**
     * 线下收银订单完成支付前执行
     */
    public function offlineAfterOrderPayComplete($params){
         $m =new DM();
        return $m->offlineAfterOrderPayComplete($params);
    }

    /**
     * 线下收银查询购物车后执行
     */
    public function offlineAfterQueryCarts($params){
         $m =new DM();
        return $m->offlineAfterQueryCarts($params);
    }

    /*
     * 首页优惠券【mobile】
     */
    public function mobileDocumentIndex(){
        $m = new DM();
        $data = $m->pageCouponQuery(0,4);
        $this->assign('rs',$data['data']);
        return $this->fetch('view/mobile/index/index');
    }

    /*
     * 首页优惠券【wechat】
     */
    public function wechatDocumentIndex(){
        $m = new DM();
        $data = $m->pageCouponQuery(0,4);
        $this->assign('rs',$data['data']);
        return $this->fetch('view/wechat/index/index');
    }
}
