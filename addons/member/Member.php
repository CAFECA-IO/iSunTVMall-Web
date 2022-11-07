<?php
namespace addons\member;

use think\addons\Addons;
use addons\member\model\Member as DM;
use Env;
use think\facade\Lang;

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
 * 会员营销插件
 */
class Member extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Member',   // 插件标识
        'title' => '会员营销',  // 插件名称
        'description' => '会员营销，邀请会员注册，双方得积分',    // 插件简介
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
        $langFile = Env::get('root_path').'addons'.DS.'member'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
    	$flag = $m->installMenu();
        WSTLangAddonJs('member');
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
        return $flag;
    }
    
    /**
     * 插件启用方法
     * @return bool
     */
    public function enable(){
        $m = new DM();
        $lang = WSTSwitchLang();  
        $langFile = Env::get('root_path').'addons'.DS.'member'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('member');
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
     * 加载首页执行
     */
    public function setShareUser($params){
        if(isset($params["getParams"]["shareUserId"])){
            session("WST_shareUserId",(int)base64_decode($params["getParams"]["shareUserId"]));
        }
    }

    /**
     * 用户注册后执行
     */
    public function afterUserRegist($params){
        $m = new DM();
        $m->userRegist($params["user"]['userId']);
    }

    /**
     * 跳转商城首页前【mobile】
     */
    public function mobileControllerIndexIndex($params){
        self::setShareUser($params);
    }

    /**
     * 个人中心【mobile】
     */
    public function mobileDocumentUserIndexTools(){
        return $this->fetch('view/mobile/users/index');
    }

    /**
     * 跳转商城首页前【wechat】
     */
    public function wechatControllerIndexIndex($params){
        self::setShareUser($params);
    }

    /**
     * 个人中心【wechat】
     */
    public function wechatDocumentUserIndexTools(){
        return $this->fetch('view/wechat/users/index');
    }
}