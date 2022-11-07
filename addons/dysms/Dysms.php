<?php
namespace addons\dysms;  // 注意命名空间规范


use think\addons\Addons;
use addons\dysms\model\Dysms as DM;
use think\facade\Lang;
use Env;

/**
 * WSTMart 阿里云-云通信
 * @author WSTMart
 */
class Dysms extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Dysms',   // 插件标识
        'title' => '短信接口(阿里云-云通信)',  // 插件名称
        'description' => '阿里云-云通信短信服务',    // 插件简介
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
        $langFile = Env::get('root_path').'addons'.DS.'dysms'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        WSTLangAddonJs('dysms');
        $flag = $m->install();
    	WSTClearHookCache();
        return $flag;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall(){
        $m = new DM();
        $flag = $m->uninstall();
    	WSTClearHookCache();
        return $flag;
    }
    
	/**
     * 插件启用方法
     * @return bool
     */
    public function enable(){
        WSTLangAddonJs('dysms');
    	WSTClearHookCache();
        return true;
    }
    
    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable(){
    	WSTClearHookCache();
    	return true;
    }

    /**
     * 插件设置方法
     * @return bool
     */
    public function saveConfig(){
    	WSTClearHookCache();
        cache('dysms_sms',null);
    	return true;
    }
    
    /**
     * 阿里云-云通信短信服务商
     * @param string $phoneNumer  手机号码
     * @param string $content     短信内容
     */
    function sendSMS($params){
       $dm = new DM();
       $dm->sendSMS($params);
       return true;
    }
    
}