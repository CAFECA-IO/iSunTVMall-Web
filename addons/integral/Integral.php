<?php
namespace addons\integral;  // 注意命名空间规范

use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\integral\model\Integrals as DM;

/**
 * WSTMart 积分商城插件
 * @author WSTMart
 */
class Integral extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Integral',   // 插件标识
        'title' => '積分商城',  // 插件名称
        'description' => 'WSTMart積分商城',    // 插件简介
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
        $langFile = Env::get('root_path').'addons'.DS.'integral'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->installMenu();
        WSTLangAddonJs('integral');
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
        $langFile = Env::get('root_path').'addons'.DS.'integral'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('integral');
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
     * 订单取消之后执行
     */
    public function afterCancelOrder($params){
        $m = new DM();
        $m->cancelOrder($params);
        return true;
    }

    /*
     * 首页积分商城商品【mobile】
     */
    public function mobileDocumentIndex(){
        $m = new DM();
        $data = $m->pageQuery(3);
        $this->assign('rs',$data['data']);
        return $this->fetch('view/mobile/index/index');
    }

    /*
     * 首页积分商城商品【wechat】
     */
    public function wechatDocumentIndex(){
        $m = new DM();
        $data = $m->pageQuery(3);
        $this->assign('rs',$data['data']);
        return $this->fetch('view/wechat/index/index');
    }
}
