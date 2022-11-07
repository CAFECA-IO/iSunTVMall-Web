<?php
namespace wstmart\supplier\controller;
use wstmart\common\model\Users as MUsers;
use wstmart\common\model\LogSms;
use wstmart\supplier\model\HomeMenus as HM;
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
 * 默认控制器
 */
class Index extends Base{
    protected $beforeActionList = ['checkAuth'=>['only'=>'index,main,getsysmessages,clearcache']];
    /**
     * 供货商主页
     */
    public function index(){
       $m = new HM();
       $ms = $m->getSupplierMenus();
       $this->assign("sysMenus",$ms[3]);
       return $this->fetch('/index');
    }

  /**
   * 去登录
   */
  public function login(){
    $supplier = session('WST_SUPPLIER');
    //如果已经登录了则直接跳去用户中心
    if(!empty($supplier) && $supplier['userId']!='' && $supplier['userType']==3){
      return $this->index();
    }
    $loginName = cookie("loginName");
    if(!empty($loginName)){
        $this->assign('loginName',cookie("loginName"));
    }else{
        $this->assign('loginName','');
    }
    return $this->fetch('/login');
  }

  /**
     * 获取用户信息
     */
  public function getSysMessages(){
      $rs = model('Systems')->getSysMessages();
      return $rs;
  }

  public function clearCache(){
      model('suppliers')->clearCache((int)session('WST_SUPPLIER.supplierId'));
      return WSTReturn(lang("cleared_successfully"), 1);
  }

  /**
   * 验证登录
   *
   */
  public function checkLogin(){
    $rs = model('Users')->checkSupplierLogin();
    return $rs;
  }

  /**
   * 用户退出
   */
  public function logout(){
    session('WST_SUPPLIER',null);
    return WSTReturn(lang("logout_loading"), 1);
  }

  /**
     * 系统预览
     */
    public function main(){
      $s = model('supplier/suppliers');
      $data = $s->getSupplierSummary((int)session('WST_SUPPLIER.supplierId'));
      $this->assign('data',$data);
      return $this->fetch("/main");
    }
  
}
