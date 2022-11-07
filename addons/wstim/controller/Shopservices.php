<?php
namespace addons\wstim\controller;
use think\addons\Controller;
use addons\wstim\model\ShopServices as M;
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
 * 店铺客服控制器
 */
class ShopServices extends Controller{
    protected $beforeActionList = ['checkShopAuth'];
    public function index(){
        return $this->fetch('shop/services/index');
    }
    /**
    * 设置为客服
    */
    public function setService(){
        $m = new M();
        return $m->setService();
    }
    public function pageQuery(){
        $m = new M();
        $rs = $m->pageQuery();
    	return  WSTGrid($rs);
    }
    /**
     * 跳去编辑页面
     */
    public function toEdit(){
        $m = new M();
        $data = $this->get();
        $assign = ['data'=>$data];
        $this->assign("p",(int)input("p"));
        return $this->fetch("admin/shopservices/edit",$assign);
    }
    public function get(){
    	$m = new M();
		return $m->getById((int)input('id'));
	}
    /**
    * 修改
    */
    public function edit(){
        $m = new M();
        return $m->edit();
    }
    /**
     * 判断账号是否存在
     */
    public function checkLoginKey(){
        $rs = WSTCheckLoginKey(Input('post.loginName'),Input('post.userId/d',0));
        if($rs['status']==1){
            return ['ok'=>$rs['msg']];
        }else{
            return ['error'=>$rs['msg']];
        }
    }
}
