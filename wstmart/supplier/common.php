<?php
use think\Db;
use think\Session;
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
 */

/**
 * 判断门店访问权限
 */
function WSTSupplierGrant($url){
    $supplier = session('WST_SUPPLIER');
    if($supplier['userType']!=3)return false;
    if($supplier['roleId']==0)return true;
    $privilegeUrl = $supplier['privilegeUrls'];
    $hasPrivilege = false;
    if($privilegeUrl){
    	$url = strtolower($url);
    	$privilegeUrl = json_decode($privilegeUrl);
    	foreach ($privilegeUrl as $key => $rv) {
    		foreach ($rv as $rkey => $vv) {
    		    if(in_array($url,$vv->urls))$hasPrivilege = true;
    	    }
    	}
    }
    return $hasPrivilege;
}



/**
 * 处理商家结算信息提示
 */
function WSTSupplierMessageBox(){
	$USER = session('WST_SUPPLIER');
	$msg = [];
	if($USER['supplierMoney']<0){
		$msg[] = lang("supplierMoney_1", [$USER['supplierMoney']]);
	}
	if(($USER['noSettledOrderFee']+$USER['paymentMoney'])<0 && (($USER['supplierMoney']+$USER['noSettledOrderFee']+$USER['paymentMoney'])<0)){
		$msg[] = lang("supplierMoney_2", [$USER['supplierMoney'], (-1*($USER['noSettledOrderFee']+$USER['paymentMoney']))]);
	}
    if((strtotime($USER['expireDate'])-strtotime(date('Y-m-d')))<2592000){
        $msg[] = lang("supplierMoney_3");
    }
	return implode('||',$msg);
}


/**
 * 获取指定店铺经营的商城分类
 */
function WSTSupplierApplyGoodsCats($parentId = 0){
    $supplierId = (int)session('WST_SUPPLIER.supplierId');
    $rs = Db::name('goods_cats')->alias('gc') 
            ->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and langId='.WSTCurrLang())
             ->join('__CAT_SUPPLIERS__ csp','gc.catId=csp.catId')
             ->where(['dataFlag'=>1, 'isShow' => 1,'gc.parentId'=>$parentId,'csp.supplierId'=>$supplierId])
             ->field("gcl.catName,gcl.simpleName,gc.catId,parentId")->order('catSort asc')->select();
    return $rs;
}


/**
 * 获取指定父级的商家店铺分类
 */
function WSTSupplierCats($parentId){
    $supplierId = (int)session('WST_SUPPLIER.supplierId');
    $dbo = Db::table('__SUPPLIER_CATS__')->alias('a')->where(['dataFlag'=>1, 'isShow' => 1,'parentId'=>$parentId,'supplierId'=>$supplierId])
             ->join('__SUPPLIER_CATS_LANGS__ scl','scl.catId=a.catId and scl.langId='.WSTCurrLang());
    return $dbo->field("scl.catName,a.catId")->order('catSort asc')->select();
}

function WSTSupplierEncrypt($supplierId){
    return md5(base64_encode(WSTConf('urlSecretKey').date("Y-m-d").$supplierId));
}


/**
 * 获取搜索关键词
 * @param integer $goodsId 商品ID
 */
function WSTSupplierGoodsSearchKey($goodsId){
    $goods = Db::name('supplier_goods_langs')->where(['goodsId'=>$goodsId])->select();
    $searchKeys = [];
    foreach ($goods as $key => $g) {
        $searchKeys[] = $g['goodsName'];
    }
    //获取规格值
    $specs = Db::name('spec_cats')->alias('gc')
               ->join('supplier_spec_items sit','gc.catId=sit.catId','inner')
               ->join('__SUPPLIER_SPEC_ITEMS_LANGS__ scl','scl.itemId=sit.itemId','inner')
               ->where(['sit.goodsId'=>$goodsId,'gc.isShow'=>1,'sit.dataFlag'=>1])
               ->field('scl.itemName')
               ->select();                     
    foreach ($specs as $key =>$v){
            $searchKeys[] = $v['itemName'];
    }
    //获取商品属性
    $attrs = Db::name('attributes')->alias('a')
               ->join('supplier_goods_attributes ga','a.attrId=ga.attrId','inner')
               ->join('__SUPPLIER_GOODS_ATTRIBUTES_LANGS__ gal','gal.goodsAttrId=ga.id','inner')
               ->where(['a.isShow'=>1,'dataFlag'=>1,'gal.goodsId'=>$goodsId])->field('gal.attrVal')
               ->select();
    if(count($attrs)>0){
        foreach ($attrs as $key => $v) {
            $searchKeys[] = $v['attrVal'];
        }
    }
    return $searchKeys;
}



/**
 * 根据送货城市获取运费
 * @param $cityId 送货城市Id
 * @param $supplierId 店铺ID
 * @param $carts 购物车信息
 */
function WSTSupplierOrderFreight($supplierId,$cityId,$carts=[]){
    $cnt = Db::name("supplier_express")->where(["supplierId"=>$supplierId,"dataFlag"=>1,"isEnable"=>1])->count();
    $freight = 0;
    if($cnt>0){
        $freight = model("SupplierCarts")->getSupplierFreight($supplierId,$cityId,$carts);
    }
    return ($freight>0)?$freight:0;
}