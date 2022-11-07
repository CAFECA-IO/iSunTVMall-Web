<?php
namespace addons\groupon;  // 注意命名空间规范

use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\groupon\model\Groupons as DM;

/**
 * WSTMart 团购插件
 * @author WSTMart
 */
class Groupon extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Groupon',   // 插件标识
        'title' => '团购活动',  // 插件名称
        'description' => 'WSTMart团购活动插件',    // 插件简介
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
        $langFile = Env::get('root_path').'addons'.DS.'groupon'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->installMenu();
        WSTLangAddonJs('groupon');
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
        $langFile = Env::get('root_path').'addons'.DS.'groupon'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('groupon');
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
     * 商品编辑之后执行
     */
    public function afterEditGoods($params){
        $m = new DM();
        $m->changeGroupon($params);
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

    /**
     * 管理员中心-活动审核
     */
    public function adminDocumentHookSummary(){
        if(WSTGrant('GROUPON_TGHD_00')){
            $m = new DM();
            $num = $m->getAuditCount();
            echo '<li>
                        <div class="icon">
                            <span><a class="menuItem" href="'.Url('/addon/groupon-goods-pageByAdmin').'">'.$num.'</a></span>
                        </div>
                        <div class="txt">
                            <a class="menuItem" href="'.Url('/addon/groupon-goods-pageByAdmin').'">
                                <p>'.lang('group_activity_audit').'</p>
                            </a>
                        </div>
                  </li>';
        }
    }

    /*
     * 首页团购商品【mobile】
     */
    public function mobileDocumentIndex(){
        $m = new DM();
        $data = $m->pageQuery(3);
        $this->assign('rs',$data['data']);
        return $this->fetch('view/mobile/index/index');
    }

    /*
     * 首页团购商品【wechat】
     */
    public function wechatDocumentIndex(){
        $m = new DM();
        $data = $m->pageQuery(3);
        $this->assign('rs',$data['data']);
        return $this->fetch('view/wechat/index/index');
    }
}
