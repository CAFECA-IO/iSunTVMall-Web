<?php
namespace addons\auction;  // 注意命名空间规范


use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\auction\model\Auctions as DM;

/**
 * WSTMart 拍卖活动插件
 * @author WSTMart
 */
class Auction extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Auction',   // 插件标识
        'title' => '拍賣活動',  // 插件名称
        'description' => '<font color="red">請確保"計劃任務"插件開啟或自行添加了系統計劃任務!!</font>',    // 插件简介
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
        $langFile = Env::get('root_path').'addons'.DS.'auction'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->installMenu();
        WSTLangAddonJs('auction');
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
        $langFile = Env::get('root_path').'addons'.DS.'auction'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('auction');
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
    public function beforeCancelOrder($params){
        $m = new DM();
        $m->beforeCancelOrder($params);
        return true;
    }
    
    /**
     * 微信用户“我的”
     */
    public function wechatDocumentUserIndexTools(){
    	return $this->fetch('view/wechat/users/index');
    }
    /**
     * 手机用户“我的”
     */
    public function mobileDocumentUserIndexTools(){
    	return $this->fetch('view/mobile/users/index');
    }
    /**
     * 管理员中心-活动审核
     */
    public function adminDocumentHookSummary(){
        if(WSTGrant('AUCTION_PMHD_00')){
            $m = new DM();
            $num = $m->getAuditCount();
            echo '<li>
                        <div class="icon">
                            <span><a class="menuItem" href="'.Url('/addon/auction-goods-pageByAdmin').'">'.$num.'</a></span>
                        </div>
                        <div class="txt">
                            <a class="menuItem" href="'.Url('/addon/auction-goods-pageByAdmin').'">
                                <p>'.lang('auction_activity_audit').'</p>
                            </a>   
                        </div>
                  </li>';
        }
    }

    /*
     * 首页拍卖商品【mobile】
     */
    public function mobileDocumentIndex(){
        $m = new DM();
        $data = $m->pageQuery(2);
        $this->assign('rs',$data['data']);
        return $this->fetch('view/mobile/index/index');
    }

    /*
     * 首页拍卖商品【wechat】
     */
    public function wechatDocumentIndex(){
        $m = new DM();
        $data = $m->pageQuery(2);
        $this->assign('rs',$data['data']);
        return $this->fetch('view/wechat/index/index');
    }
}