<?php
namespace wstmart\shop\controller;
use wstmart\shop\model\ShopMemberGroups as M;
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
 * 会员分组控制器
 */
class Shopmembergroups extends Base{
    /**
     * 跳去列表页面
     */
    public function index(){
        $this->assign("p",(int)input("p"));
        return $this->fetch("shopmembergroups/list");
    }
    /**
     * 新增
     */
    public function add(){
        $m = new M();
        return $m->add();
    }
    /**
    * 修改
    */
    public function edit(){
        $m = new M();
        return $m->edit();
    }
    /**
     * 获取品牌
     */
    public function get(){
        $m = new M();
        $rs = $m->getById((int)input("post.id"));
        return $rs;
    }
    /**
     * 删除
     */
    public function del(){
        $m = new M();
        return $m->del();
    }
    /**
     * 列表查询
     */
    public function pageQuery(){
    	$m = new M();
    	$list = $m->pageQuery();
    	return WSTGrid($list);
    }
}
