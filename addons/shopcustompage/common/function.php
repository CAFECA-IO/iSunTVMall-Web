<?php
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
 */
/**
 * 店铺自定义页面取出正在进行中的营销活动组件
 */
function shop_custom_page_marketing_data($id,$type){
    $sc = new \addons\shopcustompage\model\ShopCustomPageDecoration;
    $rs = $sc->field('id,name,attr,sort')->where(['dataFlag'=>'1','id'=>$id,'name'=>'marketing'])->order('sort asc')->select()->toArray();
    foreach($rs as $k => $v){
        $rs[$k]["attr"] = unserialize($rs[$k]["attr"]);
    }

    foreach($rs as $k => $v) {
        if($v['attr']['type'] != $type) {
            unset($rs[$k]);
        }else{
            $rs[$k]['title'] = $v["attr"]["title"];
            $rs[$k]['verticalPadding'] = $v["attr"]["vertical_padding"];
            $rs[$k]['type'] = $v["attr"]["type"];
            unset($rs[$k]['attr']);
        }
    }
    return $rs;
}

/**
 * 店铺自定义页面取出正在进行中的优惠券组件
 */
function shop_custom_page_coupon_data($id){
    $sc = new \addons\shopcustompage\model\ShopCustomPageDecoration;
    $rs = $sc->field('id,name,attr,sort')->where(['dataFlag'=>'1','id'=>$id,'name'=>'coupon'])->order('sort asc')->select()->toArray();
    foreach($rs as $k => $v){
        $rs[$k]["attr"] = unserialize($rs[$k]["attr"]);
    }
    foreach($rs as $k => $v) {
        if(isset($v["attr"]["title"])){
            // PC端的优惠券组件参数
            $rs[$k]['title'] = $v["attr"]["title"];
            $rs[$k]['desc'] = $v["attr"]["desc"];
            $rs[$k]['img'] = $v["attr"]["img"];
            $rs[$k]['titleColor'] = $v["attr"]["title_color"];
            $rs[$k]['descColor'] = $v["attr"]["desc_color"];
            $rs[$k]['textColor'] = $v["attr"]["text_color"];
            $rs[$k]['btnColor'] = $v["attr"]["btn_color"];
            $rs[$k]['btnTextColor'] = $v["attr"]["btn_text_color"];
            $rs[$k]['btnTextColor'] = $v["attr"]["btn_text_color"];
            $rs[$k]['couponIds'] = $v["attr"]["coupon_select_ids"];
        }else{
            // 手机端和微信端的优惠券组件参数
            $rs[$k]['verticalPadding'] = $v["attr"]["vertical_padding"];
            $rs[$k]['backgroundColor'] = $v["attr"]["background_color"];
            $rs[$k]['style'] = $v["attr"]["style"];
            if(WSTConf('WST_ADDONS.coupon')!='') {
                $rs[$k]['coupons'] = $sc->getCoupons($v['attr']['coupon_select_ids']);
            }else{
                $rs[$k]['coupons'] = '';
            }
        }
    }
    return $rs;
}

