<?php
namespace wstmart\shopapp\controller;
use wstmart\shopapp\model\OrderComplains as M;
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
 * 投诉控制器
 */
class orderComplains extends Base{
    // 前置方法执行列表
    protected $beforeActionList = [
        'checkShopAuth'
    ];
    /**
     * 获取商家被投诉订单列表
     */
    public function queryShopComplainByPage(){
        $shopId = $this->getShopId();
        $rs = model('common/OrderComplains')->queryShopComplainByPage($shopId);
        return json_encode($rs);
    }
     /**
     * 订单应诉页面
     */
    public function respond(){
        $shopId = $this->getShopId();
        $data = model('common/OrderComplains')->getComplainDetail(1,$shopId);
        $data['complainType'] = WSTDatas('ORDER_COMPLAINT',$data['complainType'])['dataName'];
        return json_encode(WSTReturn('ok',1,$data));
    }
    /**
     * 保存订单应诉
     */
    public function saveRespond(){
        $shopId = $this->getShopId();
        $rs = model('common/OrderComplains')->saveRespond($shopId);
        return json_encode($rs);
    }
}
