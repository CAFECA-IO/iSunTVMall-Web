<?php
namespace addons\collection;

use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\collection\model\Collection as DM;
use addons\collection\model\Goods as GM;

/**
 * WSTMart 商品采集
 * @author WSTMart
 */
class Collection extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Collection',   // 插件标识
        'title' => '商品采集',  // 插件名称
        'description' => '支持采集淘寶，天貓，京東商品數據',    // 插件简介
        'status' => 0,  // 状态
        'author' => 'WSTMart',
        'version' => '2.1.0'
    ];


    /**
     * 插件安装方法
     * @return bool
     */
    public function install(){
    	$m = new DM();
        $lang = WSTSwitchLang();  
        $langFile = Env::get('root_path').'addons'.DS.'collection'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
    	$flag = $m->installMenu();
        WSTLangAddonJs('collection');
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
        $langFile = Env::get('root_path').'addons'.DS.'collection'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
    	$flag = $m->toggleShow(1);
        WSTLangAddonJs('collection');
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

    
}
