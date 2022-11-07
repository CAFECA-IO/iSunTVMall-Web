<?php
namespace wstmart\shopapp\controller;
use wstmart\common\model\Attributes as M;
use wstmart\common\model\GoodsCats as GC;
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
 * 属性控制器
 */
class Attributes extends Base{

    /**
     * 获取分页
     */
    public function pageQuery(){
        $shopId = $this->getShopId();
        $m = new M();
        $rs = $m->pageQuery($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 获取数据
     */
    public function get(){
        $shopId = $this->getShopId();
        $m = new M();
        $rs = $m->getById(input("attrId/d"),$shopId);
        $gc = new GC();
        $goodsCats = $gc->getParentIs($rs['goodsCatId']);
        $rs['goodsCatIdPath'] = implode('_',$goodsCats)."_";
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 显示隐藏
     */
    public function setToggle(){
        $shopId = $this->getShopId();
        $m = new M();
        $rs = $m->setToggle($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 新增
     */
    public function add(){
        $shopId = $this->getShopId();
        $m = new M();
        $rs = $m->add($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
    * 修改
    */
    public function edit(){
        $shopId = $this->getShopId();
        $m = new M();
        $rs = $m->edit($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 删除
     */
    public function del(){
        $shopId = $this->getShopId();
        $m = new M();
        $rs = $m->del($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }

}
