<?php
namespace addons\combination;  // 注意命名空间规范

use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\combination\model\Combinations as DM;

/**
 * WSTMart 商品组合插件
 * @author WSTMart
 */
class Combination extends Addons {
    // 该插件的基础信息
    public $info = [
        'name' => 'Combination',   // 插件标识
        'title' => '組合套餐',  // 插件名称
        'description' => '商品關聯搭配工具：支持固定及自由搭配，推薦適合的搭配商品，幫助店鋪提升客單價和轉化率',    // 插件简介
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
        $langFile = Env::get('root_path').'addons'.DS.'combination'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->installMenu();
        WSTLangAddonJs('combination');
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
        $langFile = Env::get('root_path').'addons'.DS.'combination'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('combination');
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
     * 商品详情顶部展示
     */
    public function homeDocumentGoodsDetailSalePromotion($params){
        $m = new DM();
        $rs = $m->getRelateCombinte($params['goodsId'],true);
        $this->assign('combineGoods',$rs['list']);
        $this->assign('goodsSpec',$rs['goodsSpec']);
        $this->assign('goodsId',$params['goodsId']);
        return $this->fetch('view/home/goods_detail');
    }
    
    /**
     * 商品详情顶部展示
     */
    public function mobileDocumentGoodsDetailSalePromotion($params){
        $m = new DM();
        $rs = $m->getRelateCombinte($params['goodsId']);
        $this->assign('combineGoods',$rs['list']);
        $this->assign('goodsId',$params['goodsId']);
        return $this->fetch('view/mobile/goods_detail');
    }

    /**
     * 商品详情顶部展示
     */
    public function wechatDocumentGoodsDetailSalePromotion($params){
        $m = new DM();
        $rs = $m->getRelateCombinte($params['goodsId']);
        $this->assign('combineGoods',$rs['list']);
        $this->assign('goodsId',$params['goodsId']);
        return $this->fetch('view/wechat/goods_detail');
    }

}