<?php
namespace addons\seckill;

use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\seckill\model\Seckill as M;
use addons\seckill\model\Orders as OM;
use addons\seckill\model\Seckills as SM;
use addons\seckill\model\SeckillTimeIntervals as ST;
use addons\seckill\model\SeckillGoods as SG;
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
 * 秒杀插件
 */

class Seckill extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Seckill',   // 插件标识
        'title' => '秒殺活動',  // 插件名称
        'description' => 'WSTMart秒殺活動插件',    // 插件简介
        'status' => 0,  // 状态
        'author' => 'WSTMart',
        'version' => '1.0.0'
    ];

	
    /**
     * 插件安装方法
     * @return bool
     */
    public function install(){
        $m = new M();
        $lang = WSTSwitchLang();  
        $langFile = Env::get('root_path').'addons'.DS.'seckill'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->installMenu();
        WSTLangAddonJs('seckill');
    	WSTClearHookCache();
        return $flag;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall(){
        $m = new M();
        $flag = $m->uninstallMenu();
        WSTClearHookCache();
        return $flag;
    }
    
	/**
     * 插件启用方法
     * @return bool
     */
    public function enable(){
        $m = new M();
        $lang = WSTSwitchLang();  
        $langFile = Env::get('root_path').'addons'.DS.'seckill'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('seckill');
        WSTClearHookCache();
        return $flag;
    }
    
    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable(){
        $m = new M();
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
        $m = new M();
        return true;
    }
    /**
     * 订单取消之后执行
     */
    public function afterCancelOrder($params){
        $m = new OM();
        $m->cancelOrder($params);
        return true;
    }
    
    /**
     * 管理员中心-活动审核
     */
    public function adminDocumentHookSummary(){
        if(WSTGrant('SECKILL_TGHD_00')){
            $m = new SM();
            $num = $m->getAuditCount();
            echo '<li>
                        <div class="icon">
                            <span><a class="menuItem" href="'.Url('/addon/seckill-admin-seckillPage').'">'.$num.'</a></span>
                        </div>
                        <div class="txt">
                            <a class="menuItem" href="'.Url('/addon/seckill-admin-seckillPage').'">
                                <p>'.lang('seckill_audit').'</p>
                            </a>
                        </div>
                  </li>';
        }
    }

    /*
     * 首页秒杀商品【mobile】
     */
    public function mobileDocumentIndex(){
        $m = new ST();
        $gm = new SG();
        $rs = $m->queryCurrSecInfo();
        $data = ['data'=>[]];
        if(isset($rs["id"]))$data = $gm->pageQuery($rs["id"],6);
        $this->assign('rs',$rs);
        $this->assign('data',$data['data']);
        return $this->fetch('view/mobile/index/index');
    }

    /*
     * 首页秒杀商品【wechat】
     */
    public function wechatDocumentIndex(){
        $m = new ST();
        $gm = new SG();
        $rs = $m->queryCurrSecInfo();
        $data = ['data'=>[]];
        if(isset($rs["id"]))$data = $gm->pageQuery($rs["id"],6);
        $this->assign('rs',$rs);
        $this->assign('data',$data['data']);
        return $this->fetch('view/wechat/index/index');
    }
}