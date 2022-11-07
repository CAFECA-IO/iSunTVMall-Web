<?php
namespace wstmart\shopapp\model;
use think\Db;
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
 * 店铺设置分类
 */
class ShopConfigs extends Base{
    /*
     * 获取店铺广告
     */
    public function getShopAds($shopId){
        $shopAds = $this->where(['shopId'=>$shopId])->field('shopStreetImg,shopBanner,shopMoveBanner,shopAds')->find();
        return $shopAds;
    }

    /*
     * 编辑店铺广告
     */
    public function editAds($shopId){
        $shopcg = $this->where('shopId='.$shopId)->find();
        Db::startTrans();
        try{
            $scdata = array();
            $shopAds = input("shopAds");
            $shopAdsUrl = '';
            $shopAdsArr = [];
            if(strpos($shopAds,',')!==false){
                $shopAdsArr = explode(',',$shopAds);
            }
            if(count($shopAdsArr)>0){
                for($i=1;$i<count($shopAdsArr);$i++){
                    $shopAdsUrl .= ',';
                }
            }
            $scdata["shopStreetImg"] = input("shopStreetImg",'');
            $scdata["shopBanner"] = input("shopBanner",'');
            $scdata["shopMoveBanner"] = input("shopMoveBanner",'');
            $scdata["shopAds"] = $shopAds;
            $scdata["shopAdsUrl"] = $shopAdsUrl;
            WSTUseResource(0, $shopcg['configId'], $scdata['shopStreetImg'],'shop_configs','shopStreetImg');
            WSTUseResource(0, $shopcg['configId'], $scdata['shopBanner'],'shop_configs','shopBanner');
            WSTUseResource(0, $shopcg['configId'], $scdata['shopMoveBanner'],'shop_configs','shopMoveBanner');
            WSTUseResource(0, $shopcg['configId'], $scdata['shopAds'],'shop_configs','shopAds');
            $rs = $this->where("shopId=".$shopId)->update($scdata);
            if($rs!==false){
                Db::commit();
                return WSTReturn(lang("op_ok"),1);
            }
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang("op_err"),-1,$e->getMessage());
    }
}
