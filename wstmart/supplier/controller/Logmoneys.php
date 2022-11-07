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
 * 资金流水控制器
 */
class Logmoneys extends Base{
    protected $beforeActionList = ['checkAuth'];

    /**
     * 查看商家资金流水
     */
    public function suppliermoneys(){
        $rs = model('common/Suppliers')->getFieldsById((int)session('WST_SUPPLIER.supplierId'),['lockMoney','supplierMoney','noSettledOrderFee','paymentMoney']);
        $this->assign('object',$rs);
        return $this->fetch('logmoneys/list');
    }
    /**
     * 获取商家数据
     */
    public function pageSupplierQuery(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $data = model('logMoneys')->pageQuery(3,$supplierId);
        return WSTGrid($data);
    }

	/**
	 * 充值[商家]
	 */
    public function toRecharge(){
        if((int)WSTConf('CONF.isOpenRecharge')==0)return;
    	$payments = model('common/payments')->recharePayments('1');
    	$this->assign('payments',$payments);
        $chargeItems = model('common/ChargeItems')->queryList();
        $this->assign('chargeItems',$chargeItems);
    	return $this->fetch('recharge/pay_step1');
    }

    /**
     * 缴纳年费[供货商]
     */
    public function toRenew(){
        $payments = model('common/payments')->getOnlinePayments();
        $this->assign('payments',$payments);
        $catShopInfo = model('suppliers')->getCatShopInfo();
        $needPay = $catShopInfo['needPay'];
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $data = model('common/suppliers')->getFieldsById($supplierId,'expireDate');
        $this->assign('data',$data);
        $this->assign('catShopInfo',$catShopInfo);
        $this->assign('needPay',$needPay);
        return $this->fetch('renew/pay_step1');
    }
}
