<?php
namespace wstmart\supplier\controller;
use wstmart\supplier\model\CashDraws as M;
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
 * 提现记录控制器
 */
class Cashdraws extends Base{
    protected $beforeActionList = ['checkAuth'];
    /**
     * 查看商家资金流水
     */
    public function supplierIndex(){
        return $this->fetch('cashdraws/list');
    }
    /**
     * 获取用户数据
     */
    public function pageQueryBySupplier(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $data = model('CashDraws')->pageQuery(3,$supplierId);
        return WSTGrid($data);
    }
    /**
     * 申请提现
     */
    public function toEditBySupplier(){
        $m = model('suppliers');
        $this->assign('object',$m->getSupplierAccount());
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $supplier = $m->getFieldsById($supplierId,["supplierMoney","rechargeMoney"]);
        $this->assign('supplier',$supplier);
        return $this->fetch('cashdraws/box_draw');
    }
    /**
     * 提现
     */
    public function drawMoneyBySupplier(){
        $m = new M();
        return $m->drawMoneyBySupplier();
    }
}
