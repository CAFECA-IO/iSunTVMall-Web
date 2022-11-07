<?php
namespace addons\presale;  // 注意命名空间规范
use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\presale\model\Presales as DM;

/**
 * WSTMart 商品组合插件
 * @author WSTMart
 */
class Presale extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Presale',   // 插件标识
        'title' => '商品預售',  // 插件名称
        'description' => '商品提前預售，提前獲取市場反應，可先全款，可先定金付。',    // 插件简介
        'status' => 0,  // 状态
        'author' => 'WSTMart',
        'version' => '1.1.0'
    ];


    /**
     * 插件安装方法
     * @return bool
     */
    public function install(){
        $m = new DM();
        $lang = WSTSwitchLang();
        $langFile = Env::get('root_path').'addons'.DS.'presale'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->installMenu();
        WSTLangAddonJs('presale');
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
        $langFile = Env::get('root_path').'addons'.DS.'presale'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('presale');
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
     * 微信用户“我的”
     */
    public function wechatDocumentUserIndexTools(){
        return $this->fetch('view/wechat/users/index');
    }

    /**
     * 微信用户“我的”
     */
    public function mobileDocumentUserIndexTools(){
        return $this->fetch('view/mobile/users/index');
    }

}
