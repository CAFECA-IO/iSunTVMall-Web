<?php
namespace addons\reward;


use think\addons\Addons;
use addons\reward\model\Rewards as DM;
use think\facade\Lang;
use Env;

/**
 * WSTMart 满就送
 * @author WSTMart
 */
class Reward extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Reward',   // 插件标识
        'title' => '满就送',  // 插件名称
        'description' => '營銷插件-滿就送、滿就減、滿包郵',    // 插件简介
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
        $langFile = Env::get('root_path').'addons'.DS.'reward'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->installMenu();
        WSTLangAddonJs('reward');
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
        $langFile = Env::get('root_path').'addons'.DS.'reward'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('reward');
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
            $rs = $m->getGoodsRewardTags($v['goodsId']);
            if($rs>0){
                if(isset($params['isApp'])){
                    $params["page"]["data"][$key]['isReward'] = true;
                }else{
                    $params["page"]["data"][$key]['tags'][] = '<span class="tag">'.lang('reward_full_delivery').'</span>';
                }
            }
        }
    }

    /**
     * 商品促销页面
     */
    public function homeDocumentGoodsPromotionDetail($params){
        $m = new DM();
        $rs = $m->getAvailableRewards($params['goods']['shopId'],$params['goods']['goodsId']);
        $this->assign('rewards',$rs);
        $this->assign('goodsId',$params['goods']['goodsId']);
        return $this->fetch('view/home/index/promotion');
    }

    /**
     * 查询购物车之后执行代码
     */
    public function afterQueryCarts($params){
        $m = new DM();
        if(!$params['isVirtual']){
            $m->afterQueryCarts($params);
        }
    }

    /**
     * 购物车-商品栏
     */
    public function homeDocumentCartGoodsPromotion($params){
        $this->assign("goods",$params['goods']);
        return $this->fetch('view/home/index/cart_reward_goods');
    }
    /**
     * 购物车结算-商品栏
     */
    public function homeDocumentSettlementGoodsPromotion($params){
        $this->assign("goods",$params['goods']);
        return $this->fetch('view/home/index/settlement_reward_goods');
    }

    /**
     * 修改订单数据
     */
    public function beforeInsertOrder($params){
        $m = new DM();
        $m->beforeInsertOrder($params);
    }
    /**
     * 修改订单商品数据
     */
    public function beforeInsertOrderGoods($params){
        $m = new DM();
        $m->beforeInsertOrderGoods($params);
    }
    /**
     * 用户收货
     */
    public function afterUserReceive($params){
        $m = new DM();
        $m->afterUserReceive($params);
    }
    /**
     * 前台订单详情-商品栏
     */
    public function homeDocumentOrderViewGoodsPromotion($params){
        $params['goods']['isHead'] = false;
        if($params['goods']['promotionJson']!=''){
            $params['goods']['promotionJson'] = json_decode($params['goods']['promotionJson'],true);
            $params['goods']['promotionJson']['extraJson'] = json_decode($params['goods']['promotionJson']['extraJson'],true);
            if($params['goods']['promotionJson']['promotionGoodsIds'][0] == $params['goods']['goodsId']){
                $params['goods']['isHead'] = true;
            }
        }else{
            $params['goods']['promotionJson'] = [];
        }
        $this->assign("goods",$params['goods']);
        return $this->fetch('view/home/order_reward_goods');
    }
    /**
     * 商家订单详情-商品栏
     */
    public function shopDocumentOrderViewGoodsPromotion($params){
        $hook = '';
        if(isset($params['isOrderVerificat']) && $params['isOrderVerificat']==1){
            $rewardMoney = 0;
            foreach($params['order']['goods'] as $k=>$v){
                if(isset($v['promotionJson']) && $v['promotionJson']!=''){
                    $params['order']['goods'][$k]['promotionJson'] = json_decode($v['promotionJson'],true);
                    $params['order']['goods'][$k]['promotionJson']['extraJson'] = json_decode($params['order']['goods'][$k]['promotionJson']['extraJson'],true);
                    // 满就送减免金额
                    $rewardMoney = $params['order']['goods'][$k]['promotionJson']['promotionMoney'];
                    break;
                }
            }
            if($rewardMoney>0){
                $hook = "<div class='summary'>".lang('reward_full_reduction_preferential')."：".lang('currency_symbol')."-<span style='color: red'>{$rewardMoney}</span></div>";
            }
            if(isset($params['order']['hook'])){
                $params['order']['hook'] .= $hook;
            }else{
                $params['order']['hook'] = $hook;
            }
        }else{
            $params['goods']['isHead'] = false;
            if($params['goods']['promotionJson']!=''){
                $params['goods']['promotionJson'] = json_decode($params['goods']['promotionJson'],true);
                $params['goods']['promotionJson']['extraJson'] = json_decode($params['goods']['promotionJson']['extraJson'],true);
                if($params['goods']['promotionJson']['promotionGoodsIds'][0] == $params['goods']['goodsId']){
                    $params['goods']['isHead'] = true;
                }
            }else{
                $params['goods']['promotionJson'] = [];
            }
            $this->assign("goods",$params['goods']);
            return $this->fetch('view/shop/order_reward_goods');
        }

    }
    /**
     * 管理员订单详情-商品栏
     */
    public function adminDocumentOrderViewGoodsPromotion($params){
        $params['goods']['isHead'] = false;
        if($params['goods']['promotionJson']!=''){
            $params['goods']['promotionJson'] = json_decode($params['goods']['promotionJson'],true);
            $params['goods']['promotionJson']['extraJson'] = json_decode($params['goods']['promotionJson']['extraJson'],true);
            if($params['goods']['promotionJson']['promotionGoodsIds'][0] == $params['goods']['goodsId']){
                $params['goods']['isHead'] = true;
            }
        }else{
            $params['goods']['promotionJson'] = [];
        }
        $this->assign("goods",$params['goods']);
        return $this->fetch('view/admin/order_reward_goods');
    }
    /*************************************************  手机版 **************************************************/
    /**
     * 购物车-商品栏
     */
    public function mobileDocumentCartGoodsPromotion($params){
        $this->assign("goods",$params['goods']);
        return $this->fetch('view/mobile/index/cart_reward_goods');
    }
    /**
     * 购物车结算-商品栏
     */
    public function mobileDocumentSettlementGoodsPromotion($params){
        $this->assign("goods",$params['goods']);
        // 活动参与门槛金额
        $orderMoney = isset($params['goods']['promotion']['data']['json'][0]['orderMoney'])?$params['goods']['promotion']['data']['json'][0]['orderMoney']:0;
        $this->assign("orderMoney",$orderMoney);
        return $this->fetch('view/mobile/index/settlement_reward_goods');
    }
    /**
     * 商品促销页面【商品详情】
     */
    public function mobileDocumentGoodsPromotionDetail($params){
        $m = new DM();
        $rs = $m->getAvailableRewards($params['goods']['shopId'],$params['goods']['goodsId']);
        $this->assign('rewards',$rs);
        $this->assign('goodsId',$params['goods']['goodsId']);
        return $this->fetch('view/mobile/index/promotion');
    }
    /**
    * 手机版订单详情
    */
    public function mobileDocumentOrderViewGoodsPromotion($params){
        $tips = [];
        $money = 0;
        foreach($params['rs']['goods'] as $k=>$v){
            if($v['promotionJson']!=''){// 有使用优惠
                $params['rs']['goods'][$k]['promotionJson'] = json_decode($v['promotionJson'],true);
                $params['rs']['goods'][$k]['promotionJson']['extraJson'] = json_decode($params['rs']['goods'][$k]['promotionJson']['extraJson'],true);
                $ids = implode('_',$params['rs']['goods'][$k]['promotionJson']['promotionGoodsIds']);
                if(isset($tips[$ids]))continue;
                $tips[$ids] = 1;
                $money += $params['rs']['goods'][$k]['promotionJson']['promotionMoney'];
            }
        }
        if($money>0){
            $hook = "<div class='ui-row-flex'><div class='ui-col ui-col wst-or-term'><span class='wst-or-describe2'>".lang('reward_full_reduction_preferential')."</span><span class='o-status2'>".lang('currency_symbol')."-". number_format($money,2)."</span></div></div>";
            if(isset($params['rs']['hook'])){
                $params['rs']['hook'] .= $hook;
            }else{
                $params['rs']['hook'] = $hook;
            }
        }
    }
    /*************************************************  微信版 **************************************************/
    /**
     * 购物车-商品栏
     */
    public function wechatDocumentCartGoodsPromotion($params){
        $this->assign("goods",$params['goods']);
        return $this->fetch('view/wechat/index/cart_reward_goods');
    }
    /**
     * 购物车结算-商品栏
     */
    public function wechatDocumentSettlementGoodsPromotion($params){
        $this->assign("goods",$params['goods']);
        // 活动参与门槛金额
        $orderMoney = isset($params['goods']['promotion']['data']['json'][0]['orderMoney'])?$params['goods']['promotion']['data']['json'][0]['orderMoney']:0;
        $this->assign("orderMoney",$orderMoney);
        return $this->fetch('view/wechat/index/settlement_reward_goods');
    }
    /**
     * 商品促销页面【商品详情】
     */
    public function wechatDocumentGoodsPromotionDetail($params){
        $m = new DM();
        $rs = $m->getAvailableRewards($params['goods']['shopId'],$params['goods']['goodsId']);
        $this->assign('rewards',$rs);
        $this->assign('goodsId',$params['goods']['goodsId']);
        return $this->fetch('view/wechat/index/promotion');
    }
    /**
    * 微信版订单详情
    */
    public function wechatDocumentOrderViewGoodsPromotion($params){
        $tips = [];
        $money = 0;
        foreach($params['rs']['goods'] as $k=>$v){
            if($v['promotionJson']!=''){// 有使用优惠券
                $params['rs']['goods'][$k]['promotionJson'] = json_decode($v['promotionJson'],true);
                $params['rs']['goods'][$k]['promotionJson']['extraJson'] = json_decode($params['rs']['goods'][$k]['promotionJson']['extraJson'],true);
                $ids = implode('_',$params['rs']['goods'][$k]['promotionJson']['promotionGoodsIds']);
                if(isset($tips[$ids]))continue;
                $tips[$ids] = 1;
                $money += $params['rs']['goods'][$k]['promotionJson']['promotionMoney'];
            }
        }
        if($money>0){
            $hook = "<div class='ui-row-flex'><div class='ui-col ui-col wst-or-term'><span class='wst-or-describe2'>".lang('reward_full_reduction_preferential')."</span><span class='o-status2'>".lang('currency_symbol')."-". number_format($money,2)."</span></div></div>";
            if(isset($params['rs']['hook'])){
                $params['rs']['hook'] .= $hook;
            }else{
                $params['rs']['hook'] = $hook;
            }
        }
    }

    /**
     * 线下收银新增订单商品前执行
     */
    public function offlineBeforeInsertOrderGoods($params){
        $m =new DM();
        return $m->offlineBeforeInsertOrderGoods($params);
    }

    /**
     * 线下收银订单完成支付后执行
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
}
