<?php
namespace addons\shopcustompage\controller;

use think\addons\Controller;
use addons\shopcustompage\model\Index as M;
use addons\shopcustompage\model\ShopCustomPageDecoration as SM;
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
 * 店铺首页自定义布局控制器
 */
class Index extends Controller{
    public function __construct(){
        parent::__construct();
    }


    /**************************************小程序**********************************/
    /**
     * 获取小程序端是否开启店铺首页自定义布局
     */
    public function getWeappShopHomeDecorationSetting(){
        $m = new M();
        $pageId = $m->getCustomPagesSetting(3);
        if(!$pageId)$pageId = 0;
        return jsonReturn('success',1,['pageId'=>$pageId]);
    }

    /*
     * 获取小程序端店铺首页自定义页面数据
     */
    public function getWeappCustomPageDecorationData(){
        $m = new SM();
        $rs = $m->getWeappCustomPageDecorationData();
        return jsonReturn('success',1,$rs);
    }

}