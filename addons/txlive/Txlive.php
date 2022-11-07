<?php
namespace addons\txlive;

use think\addons\Addons;
use addons\txlive\model\TxLive as DM;
use addons\txlive\model\TxLives as M;
use Env;
use think\facade\Lang;
require_once Env::get('root_path') . '/vendor/autoload.php';

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
 * 直播插件
 */
class Txlive extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Txlive',   // 插件标识
        'title' => '騰訊雲直播',  // 插件名称
        'description' => '騰訊雲直播',    // 插件简介
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
        $langFile = Env::get('root_path').'addons'.DS.'txlive'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->installMenu();
        WSTLangAddonJs('txlive');
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
        $langFile = Env::get('root_path').'addons'.DS.'txlive'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('txlive');
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
     * 用户登录时调用，获取登录腾讯IM的userSig
     */
    public function afterUserLogin($params){
        $dm = new DM();
        $m = new M();
        $config = $dm->getAddonConfig();
        $sig = '';
        if($config['liveIM']==1){
            $IMAppID = $config['IMAppID'];
            $IMSecretKey = $config['IMSecretKey'];
            if($IMAppID != '' || $IMSecretKey != ''){
                $loginName = $params['user']['loginName'];
                $m->addUserLoginLog($loginName);
                $api = new \Tencent\TLSSigAPIv2($IMAppID, $IMSecretKey);
                $sig = $api->genSig($loginName);
            }
        }
        $params["user"]["txIMUserSig"] = $sig;
    }

    /**
     * 用户注册时调用，获取登录腾讯IM的userSig
     */
    public function afterUserRegist($params){
        $dm = new DM();
        $m = new M();
        $config = $dm->getAddonConfig();
        $sig = '';
        if($config['liveIM']==1){
            $IMAppID = $config['IMAppID'];
            $IMSecretKey = $config['IMSecretKey'];
            if($IMAppID != '' || $IMSecretKey != ''){
                $loginName = $params['user']['loginName'];
                $m->addUserLoginLog($loginName);
                $api = new \Tencent\TLSSigAPIv2($IMAppID, $IMSecretKey);
                $sig = $api->genSig($loginName);
            }
        }
        $params["user"]["txIMUserSig"] = $sig;
    }
}
