<?php
namespace wstmart\shopapp\controller;
use wstmart\common\model\GoodsVirtuals as M;
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
 * 虚拟商品卡券控制器
 */
class Goodsvirtuals extends Base{
    protected $beforeActionList = ['checkShopAuth'];
    /**
     * 获取虚拟商品库存列表
     */
    public function stockByPage(){
        $shopId = $this->getShopId();
        $m = new M();
        $rs = $m->stockByPage($shopId);
        $rs['status'] = 1;
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 生成卡券
     */
    public function add(){
        $shopId = $this->getShopId();
    	$m = new M();
        $rs = $m->add($shopId);
        return json_encode($rs);
    }
    /**
     * 删除
     */
    public function del(){
        $shopId = $this->getShopId();
    	$m = new M();
        $rs = $m->del($shopId);
        return json_encode($rs);
    }
    /**
     * 编辑
     */
    public function edit(){
        $shopId = $this->getShopId();
    	$m = new M();
        $rs = $m->edit($shopId);
        return json_encode($rs);
    }
}
