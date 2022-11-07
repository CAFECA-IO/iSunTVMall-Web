<?php
namespace addons\shopcustompage;
use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\shopcustompage\model\ShopCustomPages as DM;
use addons\shopcustompage\model\Index as IM;
use addons\shopcustompage\model\ShopCustomPageDecoration as SM;

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
 * 店铺首页自定义布局插件
 */
class Shopcustompage extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Shopcustompage',   // 插件标识
        'title' => '店鋪首頁自定義布局',  // 插件名称
        'description' => '店鋪首頁自定義布局',    // 插件简介
        'status' => 0,  // 状态
        'author' => 'WSTMart',
        'version' => '1.0.0'
    ];

    /**
     * 插件安装方法
     * @return bool
     */
    public function install(){
    	$m = new DM();
        $lang = WSTSwitchLang();
        $langFile = Env::get('root_path').'addons'.DS.'shopcustompage'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
    	$flag = $m->installMenu();
        WSTLangAddonJs('shopcustompage');
    	WSTClearHookCache();
    	return $flag;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall(){
        $m = new DM();
        $flag = $m->uninstallMenu();
        WSTClearHookCache();
        // 删除静态文件
        $m->delTplFile(WSTRootPath() . "/addons/shopcustompage/view/tpl/mobile/");
        $m->delTplFile(WSTRootPath() . "/addons/shopcustompage/view/tpl/wechat/");
        $m->delTplFile(WSTRootPath() . "/addons/shopcustompage/view/tpl/home/");
        return $flag;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable(){
        $m = new DM();
        $lang = WSTSwitchLang();
        $langFile = Env::get('root_path').'addons'.DS.'shopcustompage'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('shopcustompage');
        WSTClearHookCache();
        return $flag;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable(){
        $m = new DM();
        $flag = $m->toggleShow(0);
        WSTClearHookCache();
        return $flag;
    }

    /**
     * 插件设置方法
     * @return bool
     */
    public function saveConfig(){
        WSTClearHookCache();
        return true;
    }

    /**
     * 店铺首页自定义布局【home】
     */
    public function homeDocumentShopHomeDisplay(){
        $m = new IM();
        $shopId = input('shopId');
        $pageId = $m->getCustomPagesSetting(4);
        $customPageTitle = $m->getCustomPageTitle($pageId);
        if($pageId>0){
            $html = "<input type='hidden' id='title' value='{$customPageTitle}' />
                    <input type='hidden' id='shopHomePageId' value='{$pageId}' />
                    <script>
                    $('.shop-home-top-container').remove();
                    $('.shop-home-container').hide();
                    var title = $('#title').val();
                    $('title').html(title);
                    </script>";
            $html .= "<script>";
            if(WSTConf('WST_ADDONS.coupon')=='') {
                $html .= "var couponStatus = false;";
            }else{
                $html .= "var couponStatus = true;";
            }
            if(WSTConf('WST_ADDONS.auction')=='') {
                $html .= "var auctionStatus = false;";
            }else{
                $html .= "var auctionStatus = true;";
            }
            if(WSTConf('WST_ADDONS.seckill')=='') {
                $html .= "var seckillStatus = false;";
            }else{
                $html .= "var seckillStatus = true;";
            }
            $html .= "</script>";
            $this->display($html);
            $this->pcLoadCustomPage($shopId,$pageId,4);
        }
    }

    /*
     * 加载店铺首页自定义页面【home】
     */
    public function pcLoadCustomPage($shopId,$pageId,$pageType){
        $filePath = 'custom_page_shop_home_'.$pageId;
        $fileName = $filePath.'.html';
        $customPageFilePath = WSTRootPath().'/addons/shopcustompage/view/tpl/home/'.$fileName;
        if(file_exists($customPageFilePath)){
            $m = new IM();
            // 获取自定义页面的优惠券组件id
            $couponComponentIds = $m->getCustomPageDecorationIds($pageId,'coupon');
            if($couponComponentIds){
                $this->replaceCouponHtml($customPageFilePath,$shopId,$pageType,$couponComponentIds);
            }
            // 获取自定义页面的营销组件id
            $marketingComponentIds = $m->getCustomPageDecorationIds($pageId,'marketing');
            if($marketingComponentIds){
                $this->replaceMarketingHtml($customPageFilePath,$shopId,$pageType,$marketingComponentIds);
            }
            return $this->fetch('view/tpl/home/'.$filePath);
        }
    }

    /**
     * 店铺首页自定义布局【mobile】
     */
    public function mobileDocumentShopHomeDisplay(){
        $m = new IM();
        $shopId = input('shopId');
        $pageId = $m->getCustomPagesSetting(1);
        $customPageTitle = $m->getCustomPageTitle($pageId);
    	if($pageId>0){
    	    $html = "<input type='hidden' id='title' value='{$customPageTitle}' />
                    <input type='hidden' id='shopHomePageId' value='{$pageId}' />
                    <script>
                    $('.main-container').hide();
                    var title = $('#title').val();
                    $('title').html(title);
                    </script>";
    	    $html .= "<script>";
            if(WSTConf('WST_ADDONS.coupon')=='') {
                $html .= "var couponStatus = false;";
            }else{
                $html .= "var couponStatus = true;";
            }
            if(WSTConf('WST_ADDONS.auction')=='') {
                $html .= "var auctionStatus = false;";
            }else{
                $html .= "var auctionStatus = true;";
            }
            if(WSTConf('WST_ADDONS.seckill')=='') {
                $html .= "var seckillStatus = false;";
            }else{
                $html .= "var seckillStatus = true;";
            }
            $html .= "</script>";
    	    $this->display($html);
            $this->moLoadCustomPage($shopId,$pageId,1);
        }
    }

    /*
     * 加载店铺首页自定义页面【mobile】
     */
    public function moLoadCustomPage($shopId,$pageId,$pageType){
        $filePath = 'custom_page_shop_home_'.$pageId;
        $fileName = $filePath.'.html';
        $customPageFilePath = WSTRootPath().'/addons/shopcustompage/view/tpl/mobile/'.$fileName;
        if(file_exists($customPageFilePath)){
            $m = new IM();
            // 获取自定义页面的优惠券组件id
            $couponComponentIds = $m->getCustomPageDecorationIds($pageId,'coupon');
            if($couponComponentIds){
                $this->replaceCouponHtml($customPageFilePath,$shopId,$pageType,$couponComponentIds);
            }
            // 获取自定义页面的营销组件id
            $marketingComponentIds = $m->getCustomPageDecorationIds($pageId,'marketing');
            if($marketingComponentIds){
                $this->replaceMarketingHtml($customPageFilePath,$shopId,$pageType,$marketingComponentIds);
            }
            return $this->fetch('view/tpl/mobile/'.$filePath);
        }
    }

    /**
     * 店铺首页自定义布局【wechat】
     */
    public function wechatDocumentShopHomeDisplay(){
        $m = new IM();
        $shopId = input('shopId');
        $pageId = $m->getCustomPagesSetting(2);
        $customPageTitle = $m->getCustomPageTitle($pageId);
        if($pageId>0){
            $html = "<input type='hidden' id='title' value='{$customPageTitle}' />
                    <input type='hidden' id='shopHomePageId' value='{$pageId}' />
                    <script>
                    $('.main-container').hide();
                    var title = $('#title').val();
                    $('title').html(title);
                    </script>";
            $html .= "<script>";
            if(WSTConf('WST_ADDONS.coupon')=='') {
                $html .= "var couponStatus = false;";
            }else{
                $html .= "var couponStatus = true;";
            }
            if(WSTConf('WST_ADDONS.auction')=='') {
                $html .= "var auctionStatus = false;";
            }else{
                $html .= "var auctionStatus = true;";
            }
            if(WSTConf('WST_ADDONS.seckill')=='') {
                $html .= "var seckillStatus = false;";
            }else{
                $html .= "var seckillStatus = true;";
            }
            if(WSTConf('WST_ADDONS.pintuan')=='') {
                $html .= "var pintuanStatus = false;";
            }else{
                $html .= "var pintuanStatus = true;";
            }
            if(WSTConf('WST_ADDONS.bargain')=='') {
                $html .= "var bargainStatus = false;";
            }else{
                $html .= "var bargainStatus = true;";
            }
            $html .= "</script>";
            $this->display($html);
            $this->wxLoadCustomPage($shopId,$pageId,2);
        }
    }

    /*
     * 加载店铺首页自定义页面【wechat】
     */
    public function wxLoadCustomPage($shopId,$pageId,$pageType){
        $filePath = 'custom_page_shop_home_'.$pageId;
        $fileName = $filePath.'.html';
        $customPageFilePath = WSTRootPath().'/addons/shopcustompage/view/tpl/wechat/'.$fileName;
        if(file_exists($customPageFilePath)){
            $m = new IM();
            // 获取自定义页面的优惠券组件id
            $couponComponentIds = $m->getCustomPageDecorationIds($pageId,'coupon');
            if($couponComponentIds){
                $this->replaceCouponHtml($customPageFilePath,$shopId,$pageType,$couponComponentIds);
            }
            // 获取自定义页面的营销组件id
            $marketingComponentIds = $m->getCustomPageDecorationIds($pageId,'marketing');
            if($marketingComponentIds){
                $this->replaceMarketingHtml($customPageFilePath,$shopId,$pageType,$marketingComponentIds);
            }
            return $this->fetch('view/tpl/wechat/'.$filePath);
        }
    }

    /*
     * 将静态页面的标记替换成营销组件的动态代码
     */
    public function replaceMarketingHtml($path,$shopId,$pageType,$componentIds){
        $fileContent = file_get_contents($path);
        $templatePath = "mobiletemplates";
        if($pageType==2)$templatePath = "wechattemplates";
        if($pageType==4)$templatePath = "hometemplates";
        // 前台营销组件标记 <custompage-marketing @{$vo['type']}@{$vo['id']}@></custompage>，type为Seckill、Auction等，id为组件id
        $preg = "/<custompage-marketing.*?><\/custompage>/";
        preg_match_all($preg,$fileContent,$matches);
        // 如果找到相应的匹配，证明自定义页面有营销组件，则需进行以下替换
        if(count($matches[0])>0){
            $newContent = '';
            foreach($matches[0] as $k=>$v){
                $temp = explode('@',$matches[0][$k]);
                // 获取营销组件的PHP代码
                $content = file_get_contents( WSTRootPath() . "/addons/shopcustompage/view/shop/custompagedecoration/".$templatePath."/marketing.html");
                // 替换占位符
                if($temp[2] == $componentIds[$k]){
                    $content = str_replace('componentIdPlaceholder',$componentIds[$k],$content);
                }
                $content = str_replace('shopIdPlaceholder',$shopId,$content);
                $content = str_replace('marketingTypePlaceholder',$temp[1],$content);
                // 将营销组件的PHP代码写入静态文件中
                if($k==0){
                    $newContent = str_replace($matches[0][$k],$content,$fileContent);
                }else{
                    $newContent = str_replace($matches[0][$k],$content,$newContent);
                }
                file_put_contents($path,$newContent);
            }
        }
    }

    /*
     * 将静态页面的标记替换成优惠券组件的动态代码
     */
    public function replaceCouponHtml($path,$shopId,$pageType,$componentIds){
        $fileContent = file_get_contents($path);
        $templatePath = "mobiletemplates";
        if($pageType==2)$templatePath = "wechattemplates";
        if($pageType==4)$templatePath = "hometemplates";
        // 前台优惠券组件标记 <custompage-coupon @{$vo['id']}@></custompage>，id为组件id
        $preg = "/<custompage-coupon.*?><\/custompage>/";
        preg_match_all($preg,$fileContent,$matches);
        // 如果找到相应的匹配，证明自定义页面有优惠券组件，则需进行以下替换
        if(count($matches[0])>0){
            $newContent = '';
            foreach($matches[0] as $k=>$v){
                $temp = explode('@',$matches[0][$k]);
                // 获取优惠券组件的PHP代码
                $content = file_get_contents( WSTRootPath() . "/addons/shopcustompage/view/shop/custompagedecoration/".$templatePath."/coupon.html");
                // 替换占位符
                if($temp[1] == $componentIds[$k]){
                    $content = str_replace('componentIdPlaceholder',$componentIds[$k],$content);
                }
                $content = str_replace('shopIdPlaceholder',$shopId,$content);
                // 将营销组件的PHP代码写入静态文件中
                if($k==0){
                    $newContent = str_replace($matches[0][$k],$content,$fileContent);
                }else{
                    $newContent = str_replace($matches[0][$k],$content,$newContent);
                }
                file_put_contents($path,$newContent);
            }
        }
    }
}
