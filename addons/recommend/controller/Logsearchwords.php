<?php
namespace addons\recommend\controller;
use think\addons\Controller;
use addons\recommend\model\LogSearchWords as M;
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
 * 商品搜索日志控制器
 */
class Logsearchwords extends Controller{
	
    public function index(){
    	return $this->fetch("admin/logsearchwords/list");
    }
    
    /**
     * 获取分页
     */
    public function pageQuery(){
    	$m = new M();
    	return WSTGrid($m->pageQuery());
    }

    /*
     * 跳去关联商品页面
     */
    public function toEdit(){
        $m = new M();
        $this->assign("logId",(int)input("id"));
        $this->assign("p",(int)input("p"));
        return $this->fetch("admin/logsearchwords/edit");
    }

    /**
     * 获取已选择商品
     */
    public function listQueryByGoods(){
        $m = new M();
        $rs= $m->listQueryByGoods();
        return WSTReturn("", 1,$rs);
    }

    /**
     * 设置商品
     */
    public function edit(){
        $m = new M();
        return $m->edit();
    }

    /**
     * 查询商品
     */
    public function searchGoods(){
        $m = new M();
        $rs = $m->searchQuery();
        return WSTReturn("", 1,$rs);
    }
}
