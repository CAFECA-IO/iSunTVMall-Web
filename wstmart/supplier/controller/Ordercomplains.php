<?php
namespace wstmart\supplier\controller;
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
 * 订单投诉控制器
 */
class OrderComplains extends Base{
    protected $beforeActionList = ['checkAuth'];
    

    /**
    * 供货商-查看投诉列表
    */
    public function supplierComplain(){
        $this->assign("p",(int)input("p"));
        return $this->fetch("orders/list_complain");
    }

    /**
     * 获取供货商被投诉订单列表
     */
    public function querySupplierComplainByPage(){
        $rs = model('SupplierOrderComplains')->querySupplierComplainByPage();
        return WSTGrid($rs['data']);
    }

    /**
     * 查投诉详情
     */
    public function getSupplierComplainDetail(){
        $data = model('SupplierOrderComplains')->getComplainDetail(1);
        $this->assign("data",$data);
        $this->assign("p",(int)input("p"));
        return $this->fetch("orders/complain_detail");
    }

     /**
     * 订单应诉页面
     */
    public function respond(){
        $data = model('SupplierOrderComplains')->getComplainDetail(1);
        $this->assign("data",$data);
        $this->assign("p",(int)input("p"));
        return $this->fetch("orders/respond");
    }
    /**
     * 保存订单应诉
     */
    public function saveRespond(){
        return model('SupplierOrderComplains')->saveRespond();
    }


    


}
