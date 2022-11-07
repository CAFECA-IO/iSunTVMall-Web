<?php
namespace addons\shopcustompage\model;
use think\addons\BaseModel as Base;
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
 * 店铺首页自定义布局业务处理
 */
class Index extends Base{
    /*
     * 获取店铺是否开启首页自定义页面功能
     */
    public function getCustomPagesSetting($type){
        $shopId = (int)input('shopId',0);
        return  Db::name('shop_custom_pages')->where(['shopId'=>$shopId,'dataFlag'=>1,'isIndex'=>1,'pageType'=>$type])->value('id');
    }

    /*
     * 获取店铺首页自定义页面的页面标题
     */
    public function getCustomPageTitle($pageId){
        $pageAttr = Db::name('shop_custom_pages')->where(['dataFlag'=>'1','id'=>$pageId])->value('attr');
        $pageAttr = unserialize($pageAttr);
        return $pageAttr['title'];
    }

    /*
     * 获取店铺首页自定义页面的组件id
     */
    function getCustomPageDecorationIds($pageId,$name){
        $rs = Db::name('shop_custom_page_decoration')->where(['pageId'=>$pageId,'name'=>$name,'dataFlag'=>'1'])->order('sort asc')->column('id');
        return $rs;
    }
}
