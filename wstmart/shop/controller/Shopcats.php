<?php
namespace wstmart\shop\controller;
use wstmart\common\model\ShopCats as M;
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
 * 商家商品分类控制器
 */
class Shopcats extends Base{
    protected $beforeActionList = ['checkAuth'];

    /**
     * 列表
     */
    public function index(){
        $this->assign("p",(int)input("p"));
        return $this->fetch("shopcats/list");
    }

    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        $rs = $m->pageQuery();
        return $rs;
    }

    /**
     * 修改排序
     */
    public function editSort(){
        $m = new M();
        $rs = $m->editSort();
        return $rs;
    }

    /**
     * 删除操作
     */
    public function del(){
        $m = new M();
        $rs = $m->del();
        return $rs;
    }

    /**
     * 列表查询
     */
    public function listQuery(){
        $m = new M();
        $list = $m->listQuery((int)session('WST_USER.shopId'),input('post.parentId/d'));
        $rs = array();
        $rs['status'] = 1;
        $rs['list'] = $list;
        return $rs;
    }

    public function changeCatStatus(){
        $m = new M();
        $rs = $m->changeCatStatus();
        return $rs;
    }

    /**
     * 获取分类
     */
    public function get(){
        $m = new M();
        $rs = $m->getById((int)input("post.id"));
        return $rs;
    }

    /**
     * 新增
     */
    public function add(){
        $m = new M();
        $rs = $m->add();
        return $rs;
    }
    /**
     * 修改
     */
    public function edit(){
        $m = new M();
        $rs = $m->edit();
        return $rs;
    }
}
