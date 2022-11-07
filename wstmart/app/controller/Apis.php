<?php
namespace wstmart\app\controller;
use wstmart\app\model\Apis as M;
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
 * API控制器
 */
class Apis extends Base{
	
    public function index(){
        $m = new M();
        $rs = $m->listQuery();
        $this->assign('list',$rs);
        $this->assign('apiType',(input('apiType/d',0)==1));
    	return $this->fetch("list");
    } 
}
