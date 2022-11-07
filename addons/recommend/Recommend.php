<?php
namespace addons\recommend;

use think\addons\Addons;
use addons\recommend\model\Recommend as DM;
use addons\recommend\model\LogSearchWords as LM;
use think\facade\Lang;
use Env;
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
 * 关键词营销插件
 */
class Recommend extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Recommend',   // 插件标识
        'title' => '關鍵詞營銷',  // 插件名称
        'description' => '根據用戶搜索商品日誌進行分析營銷',    // 插件简介
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
        $langFile = Env::get('root_path').'addons'.DS.'recommend'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        WSTLangAddonJs('recommend');
    	$flag = $m->installMenu();
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
        $langFile = Env::get('root_path').'addons'.DS.'recommend'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        WSTLangAddonJs('recommend');
        $flag = $m->toggleShow(1);
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
     * 添加搜索记录
     */
    public function afterUserSearchWords($params){
        $m = new LM();
        $m->addSearchWords($params['keyword']);
    }

    /*
     * 返回搜索词关联商品的id
     */
    public function userSearchWordsCondition($params){
        $m = new LM();
        $recommendGoodsId = $m->userSearchWordsCondition($params['keyword']);
        $params['recommendGoodsId'] = $recommendGoodsId;
    }
}