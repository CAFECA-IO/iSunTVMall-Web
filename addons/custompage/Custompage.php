<?php
namespace addons\custompage;
use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\custompage\model\CustomPages as DM;
use addons\custompage\model\Index as IM;

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
 * 首页自定义布局插件
 */
class Custompage extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Custompage',   // 插件标识
        'title' => '首頁自定義布局',  // 插件名称
        'description' => '首頁自定義布局',    // 插件简介
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
        $langFile = Env::get('root_path').'addons'.DS.'custompage'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
    	$flag = $m->installMenu();
        WSTLangAddonJs('custompage');
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
        $m->delTplFile(WSTRootPath() . "/addons/custompage/view/tpl/home/");
        $m->delTplFile(WSTRootPath() . "/addons/custompage/view/tpl/mobile/");
        $m->delTplFile(WSTRootPath() . "/addons/custompage/view/tpl/wechat/");
        return $flag;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable(){
        $m = new DM();
        $lang = WSTSwitchLang();
        $langFile = Env::get('root_path').'addons'.DS.'custompage'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('custompage');
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
     * 首页自定义布局【home】
     */
    public function homeDocumentIndexDisplay(){
        $m = new IM();
        $pageId = $m->getCustomPagesSetting(4);
        $customPageTitle = $m->getCustomPageTitle($pageId);
        if($pageId>0){
            $html = "<input type='hidden' id='title' value='{$customPageTitle}' />
                    <input type='hidden' id='pageId' value='{$pageId}' />
                    <script>
                    $('.wst-search-container').remove();
                    $('.wst-nav-menus').remove();
                    $('.home-container').hide();
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
            $this->pcLoadCustomPage($pageId,4);
        }
    }

    /*
     * 加载首页自定义页面【home】
     */
    public function pcLoadCustomPage($pageId,$pageType){
        $filePath = 'custom_page_index_'.$pageId;
        $fileName = $filePath.'.html';
        $customPageFilePath = WSTRootPath().'/addons/custompage/view/tpl/home/'.$fileName;
        if(file_exists($customPageFilePath)){
            $m = new IM();
            // 获取自定义页面的优惠券组件id
            $couponComponentIds = $m->getCustomPageDecorationIds($pageId,'coupon');
            if($couponComponentIds){
                $this->replaceCouponHtml($customPageFilePath,$pageType,$couponComponentIds);
            }
            // 获取自定义页面的营销组件id
            $marketingComponentIds = $m->getCustomPageDecorationIds($pageId,'marketing');
            if($marketingComponentIds){
                $this->replaceMarketingHtml($customPageFilePath,$pageType,$marketingComponentIds);
            }
            return $this->fetch('view/tpl/home/'.$filePath);
        }
    }

    /**
     * 首页自定义布局【mobile】
     */
    public function mobileDocumentIndexDisplay(){
        $m = new IM();
        $pageId = $m->getCustomPagesSetting(1);
        $customPageTitle = $m->getCustomPageTitle($pageId);
    	if($pageId>0){
    	    $html = "<input type='hidden' id='title' value='{$customPageTitle}' />
                    <script>
                    $('.ui-container').hide();
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
            $this->moLoadCustomPage($pageId,1);
        }
    }

    /**
     * 首页自定义布局底部按钮【mobile】
     */
    public function mobileDocumentIndexFooter(){
        $pageId = WSTIsOpenIndexCustomPage(1);
        if($pageId>0){
            $menu = WSTIndexCustomPageMenu($pageId);
            $cartNum = WSTCartNum();
            $this->assign('menu',$menu);
            $this->assign('cartNum',$cartNum);
            $this->assign('pageId',$pageId);
            return $this->fetch('view/mobile/footer');
        }
    }

    /*
     * 加载首页自定义页面【mobile】
     */
    public function moLoadCustomPage($pageId,$pageType){
        $filePath = 'custom_page_index_'.$pageId;
        $fileName = $filePath.'.html';
        $customPageFilePath = WSTRootPath().'/addons/custompage/view/tpl/mobile/'.$fileName;
        if(file_exists($customPageFilePath)){
            $m = new IM();
            // 获取自定义页面的优惠券组件id
            $couponComponentIds = $m->getCustomPageDecorationIds($pageId,'coupon');
            if($couponComponentIds){
                $this->replaceCouponHtml($customPageFilePath,$pageType,$couponComponentIds);
            }
            // 获取自定义页面的营销组件id
            $marketingComponentIds = $m->getCustomPageDecorationIds($pageId,'marketing');
            if($marketingComponentIds){
                $this->replaceMarketingHtml($customPageFilePath,$pageType,$marketingComponentIds);
            }
            return $this->fetch('view/tpl/mobile/'.$filePath);
        }
    }

    /**
     * 首页自定义布局【wechat】
     */
    public function wechatDocumentIndexDisplay(){
        $m = new IM();
        $pageId = $m->getCustomPagesSetting(2);
        $customPageTitle = $m->getCustomPageTitle($pageId);
        $hasShop = $m->hasShopComponent($pageId);
        if($pageId>0){
            $html = "<input type='hidden' id='title' value='{$customPageTitle}' />
                    <input type='hidden' class='hasShop' value='{$hasShop}' />
                    <script>
                    $('.ui-container').hide();
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
            $this->wxLoadCustomPage($pageId,2);
        }
    }

    /*
     * 首页自定义布局底部按钮【wechat】
     */
    public function wechatDocumentIndexFooter(){
        $pageId = WSTIsOpenIndexCustomPage(2);
        if($pageId>0){
            $menu = WSTIndexCustomPageMenu($pageId);
            $cartNum = WSTCartNum();
            $this->assign('menu',$menu);
            $this->assign('cartNum',$cartNum);
            $this->assign('pageId',$pageId);
            return $this->fetch('view/wechat/footer');
        }
    }

    /*
     * 加载首页自定义页面【wechat】
     */
    public function wxLoadCustomPage($pageId,$pageType){
        $filePath = 'custom_page_index_'.$pageId;
        $fileName = $filePath.'.html';
        $customPageFilePath = WSTRootPath().'/addons/custompage/view/tpl/wechat/'.$fileName;
        if(file_exists($customPageFilePath)){
            $m = new IM();
            // 获取自定义页面的优惠券组件id
            $couponComponentIds = $m->getCustomPageDecorationIds($pageId,'coupon');
            if($couponComponentIds){
                $this->replaceCouponHtml($customPageFilePath,$pageType,$couponComponentIds);
            }
            // 获取自定义页面的营销组件id
            $marketingComponentIds = $m->getCustomPageDecorationIds($pageId,'marketing');
            if($marketingComponentIds){
                $this->replaceMarketingHtml($customPageFilePath,$pageType,$marketingComponentIds);
            }
            return $this->fetch('view/tpl/wechat/'.$filePath);
        }
    }

    /*
     * 将静态页面的标记替换成营销组件的动态代码
     */
    public function replaceMarketingHtml($path,$pageType,$componentIds){
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
                $content = file_get_contents( WSTRootPath() . "/addons/custompage/view/admin/custompagedecoration/".$templatePath."/marketing.html");
                // 替换占位符
                if($temp[2] == $componentIds[$k]){
                    $content = str_replace('componentIdPlaceholder',$componentIds[$k],$content);
                }
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
    public function replaceCouponHtml($path,$pageType,$componentIds){
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
                $content = file_get_contents( WSTRootPath() . "/addons/custompage/view/admin/custompagedecoration/".$templatePath."/coupon.html");
                // 替换占位符
                if($temp[1] == $componentIds[$k]){
                    $content = str_replace('componentIdPlaceholder',$componentIds[$k],$content);
                }
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
