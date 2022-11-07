<?php
namespace wstmart\supplier\validate;
use think\Validate;
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
 * 商品验证器
 */
class SupplierGoods extends Validate{
	protected $rule = [
		'langParams' => 'require|checkLangParams',
        'goodsType' => 'in:,0,1',
        'goodsCatId' => 'require',
		'goodsImg' => 'require',
        'goodsVideo'=>'checkVideo:1',
		'goodsSn' => 'checkGoodsSn:1',
		'productNo' => 'checkProductNo:1',
		'marketPrice' => 'checkMarketPrice:1',
		'supplierPrice' => 'checkSupplierPrice:1',
		'goodsUnit' => 'require',
		'isSale' => 'in:,0,1',
		'isRecom' => 'in:,0,1',
		'isBest' => 'in:,0,1',
		'isNew' => 'in:,0,1',
		'isHot' => 'in:,0,1',
		'isFreeShipping' => 'in:,0,1',
		'specsIds' => 'checkSpecsIds:1'
	];

	protected $message  =  [
        'langParams.require' => '{%require_goods_name}',
        'langParams.checkLangParams' => '{%goods_name_max_length}',
        'goodsType.in' => '{%invalid_goods_type}',
        'goodsCatId.require' => '{%goods_cat_id_incomplete}',
		'goodsImg.require' => '{%require_goods_img}',
        'goodsImg.checkVideo' => '{%require_goods_img}',
		'goodsSn.checkGoodsSn' => '{%require_goods_sn}',
		'productNo.checkProductNo' => '{%require_product_no}',
		'marketPrice.checkMarketPrice' => '{%require_market_price}',
		'supplierPrice.checkSupplierPrice' => '{%require_shop_price}',
		'goodsUnit.require' => '{%require_goods_unit}',
		'isSale.in' => '{%invalid_goods_is_sale}',
		'isRecom.in' => '{%invalid_goods_is_rec}',
		'isBest.in' => '{%invalid_goods_is_best}',
		'isNew.in' => '{%invalid_goods_is_new}',
		'isHot.in' => '{%invalid_goods_is_hot}',
		'isFreeShipping.in' => '{%invalid_goods_is_free_shipping}',
		'specsIds.checkSpecsIds' => '{%goods_spec_info_incomplete}'
	];
	public function checkLangParams(){
        $langs = WSTSysLangs();
        $langParams = input('langParams');
        foreach ($langs as $key => $v) {
            if(input('langParams'.$v["id"].'goodsName')=='')return lang('require_goods_name');
            if(input('langParams'.$v["id"].'goodsDesc')=='')return lang('require_goods_desc');
        }
        return true;
    }
	/**
     * 检测视频后缀
     */
    public function checkVideo(){
        $goodsVideo = input('goodsVideo');
        if($goodsVideo!=''){
            $str = explode('.',$goodsVideo);
            if(!in_array(strtolower($str[1]),['3gp','mp4','rmvb','mov','avi','m4v']))return lang("invalid_video_ext");
        }
        return true;
    }
    /**
     * 检测商品编号
     */
    protected function checkGoodsSn($value){
    	$goodsId = Input('post.goodsId/d',0);
    	$key = Input('post.goodsSn');
    	if($key=='')return lang("require_goods_sn");
    	$isChk = model('SupplierGoods')->checkExistGoodsKey('goodsSn',$key,$goodsId);
    	if($isChk)return lang("product_no_exists");
    	return true;
    }
    /**
     * 检测商品货号
     */
    protected function checkProductNo($value){
    	$goodsId = Input('post.goodsId/d',0);
    	$key = Input('post.productNo');
    	if($key=='')return lang("require_product_no");
    	$isChk = model('SupplierGoods')->checkExistGoodsKey('productNo',$key,$goodsId);
    	if($isChk)return lang("goods_sn_exists");
    	return true;
    }
    /**
     * 检测价格
     */
    public function checkMarketPrice(){
        $marketPrice = floatval(input('post.marketPrice'));
        if($marketPrice<0.01)return lang("market_price_must_be_greater_than_0.01");
        return true;
    }
    public function checkSupplierPrice(){
        $supplierPrice = floatval(input('post.supplierPrice'));
        if($supplierPrice<0.01)return lang("shop_price_must_be_greater_than_0.01");
        return true;
    }
    /**
     * 检测商品规格是否填写完整
     */
    public function checkSpecsIds(){
    	$specsIds = input('post.specsIds');
    	if($specsIds!=''){
	    	$str = explode(',',$specsIds);
	    	$specsIds = [];
	    	foreach ($str as $v){
	    		$vs = explode('-',$v);
	    		foreach ($vs as $vv){
	    		   if(!in_array($vv,$specsIds))$specsIds[] = $vv;
	    		}
	    	}
    		//检测规格名称是否填写完整
    		foreach ($specsIds as $v){
                $langs = WSTSysLangs();
                foreach ($langs as $key => $lv) {
    			   if(input('post.specName_'.$v.'_'.$lv['id'])=='')return lang("goods_spec_item_val_incomplete");
                }
    		}
    		//检测销售规格是否完整
    		foreach ($str as $v){
    			if(input('post.productNo_'.$v)=='')return lang("goods_spec_item_product_no_incomplete");
                if(input('post.marketPrice_'.$v)=='')return lang("goods_spec_item_market_price_incomplete");
                if(floatval(input('post.marketPrice_'.$v))<0.01)return lang("goods_spec_item_market_price_min");
                if(input('post.specPrice_'.$v)=='')return lang("goods_spec_item_shop_price_incomplete");
                if(floatval(input('post.specPrice_'.$v))<0.01)return lang("goods_spec_item_shop_price_min");
                if(input('post.specStock_'.$v)=='')return lang("goods_spec_item_goods_stock_min");
                if(intval(input('post.specStock_'.$v))<0)return lang("goods_spec_item_goods_stock_incomplete");
                if(input('post.warnStock_'.$v)=='')return lang("goods_spec_item_warn_stock_min");
                if(intval(input('post.warnStock_'.$v))<0)return lang("goods_spec_item_warn_stock_incomplete");
    		}
    		if(input('post.defaultSpec')=='')return lang("require_default_spec");
    	}
    	return true;
    }
}
