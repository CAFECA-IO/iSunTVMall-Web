<?php
namespace wstmart\supplier\controller;
use wstmart\common\model\GoodsCats;
use wstmart\supplier\validate\Suppliers as Validate;
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
 * 门店控制器
 */

class Suppliers extends Base{
    protected $beforeActionList = ['checkAuth'];
    /**
    * 供货商公告页
    */
    public function notice(){
        $notice = model('suppliers')->getNotice();
        $this->assign('notice',$notice);
        return $this->fetch('supplier/notice');
    }
    /**
    * 修改供货商公告
    */
    public function editNotice(){
        $s = model('suppliers');
        return $s->editNotice();
    }
    
    
    /**
     * 查看供货商设置
     */
    public function info(){
    	$s = model('suppliers');
    	$object = $s->getByView((int)session('WST_SUPPLIER.supplierId'));
    	$this->assign('object',$object);
    	return $this->fetch('supplier/view');
    }

    /**
     * 编辑供货商资料
     */
    public function editInfo(){
        $rs = model('suppliers')->editInfo();
        return $rs;
    }

    /**
     * 获取供货商金额
     */
    public function getSupplierMoney(){
        $rs = model('suppliers')->getFieldsById((int)session('WST_SUPPLIER.supplierId'),'supplierMoney,lockMoney,rechargeMoney');
        $urs = model('users')->getFieldsById((int)session('WST_SUPPLIER.userId'),'payPwd');
        $rs['isSetPayPwd'] = ($urs['payPwd']=='')?0:1;
        $rs['isDraw'] = ((float)WSTConf('CONF.drawCashSupplierLimit')<=$rs['supplierMoney'])?1:0;
        unset($urs);
        return WSTReturn('',1,$rs);
    }

    /*
 * 商家续费
 */
    public function renew(){
        $rs = model('suppliers')->renew();
        return $rs;
    }
}
