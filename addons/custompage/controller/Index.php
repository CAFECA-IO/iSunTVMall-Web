<?php
namespace addons\custompage\controller;

use think\addons\Controller;
use addons\custompage\model\Index as M;
use addons\custompage\model\CustomPageDecoration as CM;
use wstmart\common\model\Tags;
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
 * 首页自定义布局控制器
 */
class Index extends Controller{
    public function __construct(){
        parent::__construct();
    }

    /*
     * 获取商城首页自定义页面多店铺组件数据
     */
    public function customPageShopsList(){
        $m = new M();
        return $m->customPageShopsList();
    }

    /**************************************小程序**********************************/
    /**
     * 获取小程序端是否开启首页自定义布局
     */
    public function getWeappIndexDecorationSetting(){
        $m = new M();
        $pageId = $m->getCustomPagesSetting(3);
        $hasShop = $m->hasShopComponent($pageId);
        if(!$pageId)$pageId = 0;
        return jsonReturn('success',1,['pageId'=>$pageId,'hasShop'=>$hasShop]);
    }

    /*
     * 获取小程序端首页自定义页面数据
     */
    public function getWeappCustomPageDecorationData(){
        $m = new CM();
        $rs = [];
        $rs['list'] = $m->getWeappCustomPageDecorationData();
        // 获取弹窗广告
        $model = new Tags();
        $rs['popAds'] = $model->listAds('weapp-pop-ads',1,86400,1);
        return jsonReturn('success',1,$rs);
    }

    /*
     * 获取后台自定义的底部导航栏菜单
     */
    public function getTabbarMenu(){
        $m = new M();
        $pageId = $m->getCustomPagesSetting(3);
        if($pageId > 0){
            $res = $m->getTabbarMenu($pageId);
            $res['cartNum'] = model('weapp/carts')->cartNum();
            if($res['tabbars']){
                return jsonReturn('success',1,$res);
            }else{
                return jsonReturn(lang('custompage_no_set_bottom_nav'),-1);
            }
        }else{
            return jsonReturn(lang('custompage_no_open_weapp'),-1);
        }
    }
}
