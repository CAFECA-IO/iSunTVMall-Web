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
 * 门店配置控制器
 */
class Supplierconfigs extends Base{
    protected $beforeActionList = ['checkAuth'];
    /**
    * 供货商设置
    */
    public function toSupplierCfg(){
        //获取商品信息
        $m = model('SupplierConfigs');
        $this->assign('object',$m->getSupplierCfg((int)session('WST_SUPPLIER.supplierId')));
        return $this->fetch('supplierconfigs/supplier_cfg');
    }

    /**
     * 新增/修改 供货商设置
     */
    public function editSupplierCfg(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $m = model('SupplierConfigs');
        if($supplierId>0){
            $rs = $m->editSupplierCfg($supplierId);
        }
        return $rs;
    }

}
