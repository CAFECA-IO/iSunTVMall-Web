<?php
namespace wstmart\supplier\controller;
use wstmart\supplier\model\SupplierGoodsCopyRelates as M;
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
 * 商品控制器
 */
class Suppliergoodscopyrelates extends Base{
    protected $beforeActionList = ['checkAuth'];
    
    /**
     *  铺货商家列表
     */
    public function index(){
        $this->assign("p",(int)input("p"));
        return $this->fetch('goodsrelates/index');
    }
    /**
     * 铺货商家列表
     */
    public function pageQuery(){
        $m = new M();
        $rs = $m->pageQuery();
        $rs['status'] = 1;
        return WSTGrid($rs);
    }
    /**
     * 铺货商家-商品列表
     */
    public function goodsIndex(){
        $this->assign("p",(int)input("p"));
        $this->assign("shopId",(int)input("shopId"));
        return $this->fetch('goodsrelates/goodsindex');
    }
    /**
     * 铺货商家-商品列表
     */
    public function goodsPageQuery(){
        $m = new M();
        $rs = $m->goodsPageQuery();
        $rs['status'] = 1;
        return WSTGrid($rs);
    }
    /**
     * 商品-铺货商家列表
     */
    public function shopIndex(){
        $this->assign("p",(int)input("p"));
        $this->assign("src",input("src"));
        $this->assign("goodsId",(int)input("goodsId"));
        return $this->fetch('goodsrelates/shopindex');
    }
    /**
     * 商品-铺货商家列表
     */
    public function shopPageQuery(){
        $m = new M();
        $rs = $m->shopPageQuery();
        $rs['status'] = 1;
        return WSTGrid($rs);
    }
}
