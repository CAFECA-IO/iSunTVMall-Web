<?php
namespace wstmart\shopapp\controller;
use wstmart\common\model\CashDraws as M;
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
    // 前置方法执行列表
    protected $beforeActionList = [
        'checkShopAuth',
    ];
    /**
     * 获取用户数据
     */
    public function pageQueryByShop(){
        $shopId = $this->getShopId();
        $data = model('CashDraws')->pageQuery(1,$shopId);
        return json_encode(WSTReturn("", 1,$data));
    }
    /**
     * 申请提现
     */
    public function index(){
        $m = model('shops');
        $shopId = $this->getShopId();
        $userId = model('users')->getUserId();
        $data = [
            'account'=>$m->getShopAccount($shopId),
            'shop'=>$m->getFieldsById($shopId,["shopMoney","rechargeMoney","lockMoney","shopImg","shopName"]),
            'drawCashShopLimit'=>(int)WSTConf('CONF.drawCashShopLimit'),
            'drawCashCommission'=>(int)WSTConf('CONF.drawCashCommission'),
            'wxenabled'=>WSTConf('CONF.wxenabled')
        ];
        return json_encode(WSTReturn('ok',1,$data));
    }
    /**
     * 获取店铺金额
     */
    public function getShopMoney(){
        $shopId = $this->getShopId();
        $rs = model('shops')->getFieldsById($shopId,'shopMoney,lockMoney,rechargeMoney');
        $rs['isDraw'] = ((float)WSTConf('CONF.drawCashShopLimit')<=$rs['shopMoney'])?1:0;
        return json_encode(WSTReturn('',1,$rs));
    }
    /**
     * 提现
     */
    public function drawMoneyByShop(){
        $shopId = $this->getShopId();
        $userId = model('users')->getUserId();
        $m = new M();
        return json_encode($m->drawMoneyByShop($shopId,$userId));
    }
}
