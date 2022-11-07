<?php
namespace wstmart\shopapp\controller;
use wstmart\common\model\GoodsCats;
use wstmart\common\model\Attributes as AT;
use wstmart\shop\model\ShopMemberGroups;
use think\Cache;
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
 * 商品控制器
 */
class Goods extends Base{
    protected $beforeActionList = [
          'checkShopAuth' => ['only'=>'salebypage,storebypage,stockbypage,illegalbypage,del,add,toadd,edit,toedit']
    ];
    /*************************************************** 商家操作 **************************************************************/
    public function uploadVideo(){
        return WSTUploadVideo();
    }
    /**
     * 获取商品规格属性
     */
    public function getSpecAttrs(){
        $shopId = $this->getShopId();
        return json_encode(model('goods')->getSpecAttrs($shopId));
    }
    /**
     * 上传图片
     */
    public function uploadPic(){
        $rs = WSTUploadPic(0);
        $rs = json_decode($rs,true);
        $data = [];
        if($rs['status']==1){
            $data['status'] = 1;
            $data['url'] = $this->domain().$rs['savePath'].$rs['name'];
        }else{
            $data['status'] = 0;
            $data['msg'] = $rs['msg'];
        }
        return json_encode($data);
    }
    /**
    * 获取商品分类
    */
    public function getGoodsCats(){
        $shopId = $this->getShopId();
        $rs = model('shopapp/goods')->getGoodsCats($shopId);
        return json_encode($rs);
    }
    /**
     * 跳去新增页面
     */
    public function add(){
        $shopId = $this->getShopId();
        $smg = new ShopMemberGroups();
        $shopMemberGroups = $smg->listQuery($shopId);
        $data = [
            'goodsSn'=>WSTGoodsNo(),
            'productNo'=>WSTGoodsNo(),
            'express'=>model("express")->shopExpressList($shopId),
            'shopMemberGroups'=>$shopMemberGroups
        ];
        foreach (WSTSysLangs() as $key => $lv) {
            $data['langParams'][$lv['id']]['goodsName'] = '';
            $data['langParams'][$lv['id']]['goodsTips'] = '';
            $data['langParams'][$lv['id']]['goodsDesc'] = '';
            $data['langParams'][$lv['id']]['goodsSeoKeywords'] = '';
            $data['langParams'][$lv['id']]['goodsSeoDesc'] = '';
        }
        $data['goodsUnits'] = WSTDatas('GOODS_UNIT');
        return json_encode(WSTReturn('ok',1,$data));
    }

    /**
     * 新增商品
     */
    public function toAdd(){
        $shopId = $this->getShopId();
        return json_encode(model('shopapp/goods')->add($shopId));
    }

    /**
     * 跳去编辑页面
     */
    public function edit(){
        $shopId = $this->getShopId();
        $smg = new ShopMemberGroups();
        $shopMemberGroups = $smg->listQuery($shopId);
        $data = model('shopapp/goods')->getById(input('id'),$shopId);
        $data['shopCatData'] = model('shopapp/ShopCats')->getShopCats($shopId);
        $data['express'] = model("express")->shopExpressList($shopId);
        $data['shopMemberGroups'] = $shopMemberGroups;
        $data['goodsUnits'] = WSTDatas('GOODS_UNIT');
        return json_encode(WSTReturn('',1,$data));
    }

    /**
     * 编辑商品
     */
    public function toEdit(){
        $shopId = $this->getShopId();
        return json_encode(model('shopapp/goods')->edit($shopId));
    }
    /**
     * 获取待审核商品列表
     */
    public function auditByPage(){
        $shopId = $this->getShopId();
        $m = model('shopapp/goods');
        $rs = $m->auditByPage($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 获取上架商品列表
     */
    public function saleByPage(){
        $shopId = $this->getShopId();
        $m = model('shopapp/goods');
        $rs = $m->saleByPage($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 获取仓库中的商品
     */
    public function storeByPage(){
        $shopId = $this->getShopId();
        $m = model('shopapp/goods');
        $rs = $m->storeByPage($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 获取违规的商品
     */
    public function illegalByPage(){
        $shopId = $this->getShopId();
        $m = model('shopapp/goods');
        $rs = $m->illegalByPage($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 获取预警库存列表
     */
    public function stockByPage(){
        $shopId = $this->getShopId();
        $m = model('shopapp/goods');
        $rs = $m->stockByPage($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 删除商品
     */
    public function del(){
        $shopId = $this->getShopId();
        return json_encode(model('shopapp/goods')->del($shopId));
    }
    /**
    * 修改商品状态
    */
    public function changSaleStatus(){
        $shopId = $this->getShopId();
        $rs = model('shopapp/goods')->changSaleStatus($shopId);
        return json_encode($rs);
    }
    /**
    *   批量上(下)架
    */
    public function changeSale(){
        $shopId = $this->getShopId();
        $rs = model('shopapp/goods')->changeSale($shopId);
        return json_encode($rs);
    }

    // 获取商城的商品分类
    public function goodsCatsListQuery(){
        $rs = WSTGoodsCats(0);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /*************************************************** 商家操作 **************************************************************/
}
