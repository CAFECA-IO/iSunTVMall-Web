<?php
namespace addons\aliyunpush;	// 注意命名空间规范
use think\addons\Addons;
use addons\aliyunpush\model\AliyunPush as M;
use think\Db;
use think\facade\Lang;
use Env;
class Aliyunpush extends Addons	// 需继承think\addons\Addons类
{
    // 该插件的基础信息
    public $info = [
        'name' => 'Aliyunpush',	// 插件标识
        'title' => '阿裏雲推送-推送插件',	// 插件名称
        'description' => '用於商家端app推送',	// 插件简介
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
        $m->userId = $userId;
        $m->content = $params['content'];
        // 3.调用推送
        $m->pushMessageToSingle(true);
        return 1;
    }

    public function install(){
        (new M())->install();
        $lang = WSTSwitchLang();
        $langFile = Env::get('root_path').'addons'.DS.'aliyunpush'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        WSTLangAddonJs('aliyunpush');
        return true;
    }
    public function uninstall(){
        (new M())->uninstall();
        return true;
    }
    
    public function enable(){
        WSTLangAddonJs('aliyunpush');
        return true;
    }
    
    public function disable(){
        return true;
    }
    
    public function saveConfig(){
        return true;
    }
}




























