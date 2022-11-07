<?php
namespace addons\getui;	// 注意命名空间规范
use think\addons\Addons;
use addons\getui\model\Getui as M;
use think\Db;
use think\facade\Lang;
use Env;
class Getui extends Addons	// 需继承think\addons\Addons类
{
    // 该插件的基础信息
    public $info = [
        'name' => 'Getui',	// 插件标识
        'title' => '個推-推送插件',	// 插件名称
        'description' => '個推-推送插件個推-推送插件,用於商家端app推送',	// 插件简介
        'status' => 0,	// 状态1：已安装 0：卸载
        'author' => 'WSTMart',
        'version' => '1.0.0'
    ];
    /**
     * 发送离线消息时的钩子
     * params = ["userId"=>"", "shopId"=>"", "content"=>"推送内容"]
     */
    public function pushNotificationByThirdParty($params){
        // 1.根据shopId查询userId【若未设置userId】
        $m = new M();
        if(isset($params['userId']) && $params['userId']>0){
            $userId = $params['userId'];
        }else{
            $userId = Db::name('shop_users')->where(['shopId'=>$params['shopId'], 
                                                     'roleId'=>0])
                                            ->value('userId');
            
        }
        // 2.设置alias及content
        $m->cId = $userId;
        $m->content = $params['content'];
        // 3.调用推送
        $m->pushMessageToSingle();
        return 1;
    }
    //必须实现的抽象方法
    public function install(){
        (new M())->install();
        $lang = WSTSwitchLang();
        $langFile = Env::get('root_path').'addons'.DS.'getui'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        WSTLangAddonJs('getui');
        return true;
    }
    public function uninstall(){
        (new M())->uninstall();
        return true;
    }
    
    public function enable(){
        WSTLangAddonJs('getui');
        return true;
    }
    
    public function disable(){
        return true;
    }
    
    public function saveConfig(){
        return true;
    }
}




























