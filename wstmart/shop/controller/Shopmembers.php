<?php
namespace wstmart\shop\controller;
use wstmart\shop\model\ShopMembers as M;
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
 * 店铺客户控制器
 */
class ShopMembers extends Base{
    protected $beforeActionList = ['checkAuth'];
    /**
     * 跳去列表页
     */
    public function index(){
        $this->assign("p",(int)input("p"));
        $this->assign('groups',model('shop/ShopMemberGroups')->listQuery());
        return $this->fetch("shopmembers/list");
    }

    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        $rs = $m->pageQuery();
        return WSTGrid($rs);
    }

    /**
     * 修改分组
     */
    public function setgroup(){
        $m = new M();
        $rs = $m->setgroup();
        return $rs;
    }

}
