<?php
namespace wstmart\shopapp\controller;
use wstmart\common\model\GoodsConsult as M;
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
 * 商品咨询控制器
 */
class GoodsConsult extends Base{
    protected $beforeActionList = [
          'checkShopAuth'=>['only'=>'pagequery,reply']
    ];
    /****************************************** 商家 ***********************************************/
    /**
    * 根据店铺id获取商品咨询
    */
    public function pageQuery(){
        $shopId = $this->getShopId();
        $m = new M();
        $rs = $m->pageQuery($shopId);
        return json_encode($rs);
    }
    /**
    * 商家回复
    */
    public function reply(){
        $shopId = $this->getShopId();
        $m = new M();
        return json_encode($m->reply($shopId));
    }
    /**
    * 设置显示隐藏
    */
    public function isShow(){
        $shopId = $this->getShopId();
        $m = new M();
        return json_encode($m->isShow($shopId));
    }
    /****************************************** 商家 ***********************************************/
}
