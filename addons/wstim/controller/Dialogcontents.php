<?php
namespace addons\wstim\controller;
use think\addons\Controller;
use addons\wstim\model\DialogContents as M;
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
 * 聊天记录控制器
 */
class DialogContents extends Controller{
    /**
    * 用户留言
    */
    public function message(){
        $this->checkShopAuth();
        return $this->fetch('home/shop/message');
    }
    /**
    * 聊天记录页面
    */
    public function shopIndex(){
        $this->checkShopAuth();
        $path = WST_ADDON_PATH.'/wstim/view/shop/index/dialogContent.html';
        return $this->fetch($path);
    }
    /**
    * 获取聊天记录【商家端】
    */
    public function pageQuery(){
        $this->checkShopAuth();
        $m = new M();
    	return $m->pageQuery((int)session('WST_USER.shopId'));
    }
    /***************************************** 平台端 *****************************************/
    /**
    * 聊天记录页面
    */
    public function adminIndex(){
        $path = WST_ADDON_PATH.'/wstim/view/admin/dialogContent.html';
        return $this->fetch($path);
    }

    /**
    * 获取聊天记录【平台端】
    */
    public function pageQueryByAdmin(){
        $this->checkAdminAuth();
        $m = new M();
        return $m->pageQuery((int)input('shopId'));
    }
}
