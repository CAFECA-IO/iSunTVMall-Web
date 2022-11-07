<?php
namespace wstmart\supplier\model;
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
 * 门店配置类
 */
use think\Db;
class SupplierConfigs extends Base{
    /**
    * 供货商设置
    */
     public function getSupplierCfg($id){
        $rs = $this->where("supplierId=".$id)->find();
        if($rs != ''){
            //图片
            $rs['supplierAds'] = ($rs['supplierAds']!='')?explode(',',$rs['supplierAds']):null;
            //图片的广告地址
            $rs['supplierAdsUrl'] = ($rs['supplierAdsUrl']!='')?explode(',',$rs['supplierAdsUrl']):null;
            return $rs;
        }
     }

     /**
      * 修改供货商设置
      */
     public function editSupplierCfg($supplierId){
        $data = input('post.');
        //加载商店信息
        Db::startTrans();
		try{
	        $suppliercg = $this->where('supplierId='.$supplierId)->find(); 
	        $scdata = array();
	        $scdata["supplierKeywords"] =  Input("supplierKeywords");
	        $scdata["supplierBanner"] =  Input("supplierBanner");
	        $scdata["supplierDesc"] =  Input("supplierDesc");
	        $scdata["supplierAds"] =  Input("supplierAds");
	        $scdata["supplierAdsUrl"] =  Input("supplierAdsUrl");
            $scdata["supplierHotWords"] =  Input("supplierHotWords");
	        WSTUseResource(0, $suppliercg['configId'], $scdata['supplierBanner'],'supplier_configs','supplierBanner');
	        WSTUseResource(0, $suppliercg['configId'], $scdata['supplierAds'],'supplier_configs','supplierAds');
	        $rs = $this->where("supplierId=".$supplierId)->update($scdata);	
	        if($rs!==false){
	        	Db::commit();
	            return WSTReturn(lang("op_ok"),1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang("op_err"),-1);
     }
     /**
      * 获取商城搜索关键字
      */
     public function searchSupplierkey($supplierId){
     	$rs = $this->where('supplierId='.$supplierId)->field('configId,supplierHotWords')->find();
     	$keys = [];
     	if($rs['supplierHotWords']!='')$keys = explode(',',$rs['supplierHotWords']);
     	return $keys;
     }
}
