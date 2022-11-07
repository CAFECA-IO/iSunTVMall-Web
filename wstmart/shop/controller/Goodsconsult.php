<?php
namespace wstmart\shop\controller;
use wstmart\common\model\GoodsConsult as M;
use wstmart\shop\model\GoodsConsult as N;
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
 * 商品咨询控制器
 */
class Goodsconsult extends Base{
    protected $beforeActionList = ['checkAuth'];

    /**
     * 修改
     */
    public function edit(){
        $m = new M();
        return $m->edit();
    }
    /**
     * 根据店铺id获取商品咨询
     */
    public function pageQuery(){
        $m = new N();
        $rs = $m->pageQuery();

        return WSTGrid($rs);
    }
    /**
     * 获取商品咨询 商家
     */
    public function shopReplyConsult(){
        $this->assign("p",(int)input("p"));
        return $this->fetch('goodsconsult/list');
    }
    /**
     * 商家回复
     */
    public function reply(){
        $m = new M();
        return $m->reply();
    }
}
