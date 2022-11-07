<?php
namespace wstmart\shop\controller;
use wstmart\common\model\Moulds as M;
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
 * 规格模板控制器
 */
class Moulds extends Base{

    /**
     * 获取分页
     */
    public function getMouldList(){
        $m = new M();
        $rs = $m->getMouldList();
        return WSTReturn("",1,$rs);
    }

    
    /**
     * 获取数据
     */
    public function get(){
        $m = new M();
        return $m->getById();
    }
    /**
     * 新增
     */
    public function addMould(){
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
     * 删除
     */
    public function del(){
        $m = new M();
        return $m->del();
    }

    public function editMouldName(){
        $m = new M();
        $rs = $m->editMouldName();
        return $rs;
    }
    
    public function checkMouldName(){
    	$m = new M();
    	$rs = $m->checkMouldName();
   	 	return $rs;
    }
    
}
