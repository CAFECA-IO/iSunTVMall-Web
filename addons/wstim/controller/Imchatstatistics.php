<?php
namespace addons\wstim\controller;
use think\addons\Controller;
use addons\wstim\model\ImchatStatistics as M;
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
 * 用户访问统计控制器
 */
class Imchatstatistics extends Controller{
    /**
     * 列表页
     */
    public function index(){
    	$this->checkShopAuth();
    	$this->assign("p",(int)input("p"));
    	return $this->fetch("shop/statistics/list");
    }
    /**
     * 列表查询
     */
    public function pageQuery(){
        $this->checkShopAuth();
    	$m = new M();
        $rs = $m->pageQuery();
        if(empty($rs))return WSTReturn(lang('wstim_not_login'),-999);
    	return WSTGrid($rs);
    }
}
