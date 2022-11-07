<?php
namespace addons\kuaidi;  // 注意命名空间规范

use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\kuaidi\model\Kuaidi as DM;

/**
 * WSTMart 快递100
 * @author WSTMart
 */
class Kuaidi extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Kuaidi',   // 插件标识
        'title' => '快遞100',  // 插件名称
        'description' => '為您更好的跟蹤您的訂單動態',    // 插件简介
        'status' => 0,  // 状态
        'author' => 'WSTMart',
        'version' => '1.0.1'
    ];

	
    /**
     * 插件安装方法
     * @return bool
     */
    public function install(){
    	$m = new DM();
        $lang = WSTSwitchLang();  
        $langFile = Env::get('root_path').'addons'.DS.'kuaidi'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
    	$flag = $m->install();
        WSTLangAddonJs('kuaidi');
    	WSTClearHookCache();
        return $flag;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall(){
    	$m = new DM();
    	$flag = $m->uninstall();
    	WSTClearHookCache();
        return $flag;
    }
    
	/**
     * 插件启用方法
     * @return bool
     */
    public function enable(){
        WSTLangAddonJs('kuaidi');
    	WSTClearHookCache();
        return true;
    }
    
    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable(){
    	WSTClearHookCache();
    	return true;
    }

    /**
     * 插件设置方法
     * @return bool
     */
    public function saveConfig(){
    	$m = new DM();
    	WSTClearHookCache();
    	return true;
    }
    /**
     * 跳转订单详情【admin】
     */
    public function adminDocumentOrderView($params){
        $m = new DM();
        $hasExpress = $m->checkHasExpress($params['orderId']);
        if($hasExpress){
        	$express = $m->getExpress($params['orderId']);
            $expressLogs = [];
            foreach($express as $v){
                if($v["expressNo"]!=""){
                    $rs = $m->getOrderExpresses($params['orderId'],$v['expressId'],$v['expressNo']);
                    $expressLogs[] = ['expressNo'=>$v['expressNo'],'log'=>$rs['logs']];
                }
            }
	        $this->assign('expressLogs', $expressLogs);
	        return $this->fetch('view/admin/view');
        }
    }
    
    /**
     * 跳转订单详情【home】
     */
    public function homeDocumentOrderView($params){
    	$m = new DM();
    	$hasExpress = $m->checkHasExpress($params['orderId']);
    	if($hasExpress){
    		$express = $m->getExpress($params['orderId']);
            $expressLogs = [];
            foreach($express as $v){
                if($v["expressNo"]!=""){
    			    $rs = $m->getOrderExpresses($params['orderId'],$v['expressId'],$v['expressNo']);
    			    $expressLogs[] = ['expressNo'=>$v['expressNo'],'log'=>$rs['logs']];
                }
            }
            $this->assign('expressLogs', $expressLogs);
            return $this->fetch('view/home/view');
    	}
    }
    /**
     * 跳转订单详情【shop】
     */
    public function shopDocumentOrderView($params){
        $m = new DM();
        $hasExpress = $m->checkHasExpress($params['orderId']);
        if($hasExpress){
            $express = $m->getExpress($params['orderId']);
            $expressLogs = [];
            foreach($express as $v){
                if($v["expressNo"]!=""){
                    $rs = $m->getOrderExpresses($params['orderId'],$v['expressId'],$v['expressNo']);
                    $expressLogs[] = ['expressNo'=>$v['expressNo'],'log'=>$rs['logs']];
                }
            }
            $this->assign('expressLogs', $expressLogs);
            return $this->fetch('view/shop/view');
        }
    }
    
	public function afterQueryUserOrders($params){
		$m = new DM();
    	foreach ($params["page"]["data"] as $key => $v){
    		$hasExpress = $m->checkHasExpress($v['orderId']);
    		if($hasExpress){
    			$bnt = '<button class="ui-btn o-btn o-cancel-btn" onclick="checkExpress('.$v['orderId'].')">查看物流</button>';
    			$params["page"]["data"][$key]['hook'] = $bnt;
    		}else{
    			$params["page"]["data"][$key]['hook'] = "";
    		}
    		
    	}
    }
    
    /**
     * 订单列表【mobile】
     */
	public function mobileDocumentOrderList(){
		return $this->fetch('view/mobile/view');
	}
	
	/**
	 * 订单列表【wechat】
	 */
	public function wechatDocumentOrderList(){
		return $this->fetch('view/wechat/view');
	}


    /**
     * 跳转订单详情【商家供货商订单】
     */
    public function supplierShopDocumentOrderView($params){
        $m = new DM();
        $hasExpress = $m->checkSupplierHasExpress($params['orderId']);
        if($hasExpress){
            $express = $m->getSupplierExpress($params['orderId']);
            $expressLogs = [];
            foreach($express as $v){
                if($v["expressNo"]!=""){
                    $rs = $m->getOrderExpresses($params['orderId'],$v['expressId'],$v['expressNo']);
                    $expressLogs[] = ['expressNo'=>$v['expressNo'],'log'=>$rs['logs']];
                }
            }
            $this->assign('expressLogs', $expressLogs);
            return $this->fetch('view/shop/supplier_view');
        }
    }

    /**
     * 跳转订单详情【供货商订单】
     */
    public function supplierDocumentOrderView($params){
        $m = new DM();
        $hasExpress = $m->checkSupplierHasExpress($params['orderId']);
        if($hasExpress){
            $express = $m->getSupplierExpress($params['orderId']);
            $expressLogs = [];
            foreach($express as $v){
                if($v["expressNo"]!=""){
                    $rs = $m->getOrderExpresses($params['orderId'],$v['expressId'],$v['expressNo']);
                    $expressLogs[] = ['expressNo'=>$v['expressNo'],'log'=>$rs['logs']];
                }
            }
            $this->assign('expressLogs', $expressLogs);
            return $this->fetch('view/supplier/view');
        }
    }

    /**
     * 跳转订单详情【admin供货商订单】
     */
    public function supplierAdminDocumentOrderView($params){
        $m = new DM();
        $hasExpress = $m->checkSupplierHasExpress($params['orderId']);
        if($hasExpress){
            $express = $m->getSupplierExpress($params['orderId']);
            $expressLogs = [];
            foreach($express as $v){
                if($v["expressNo"]!=""){
                    $rs = $m->getOrderExpresses($params['orderId'],$v['expressId'],$v['expressNo']);
                    $expressLogs[] = ['expressNo'=>$v['expressNo'],'log'=>$rs['logs']];
                }
            }
            $this->assign('expressLogs', $expressLogs);
            return $this->fetch('view/admin/supplier_view');
        }
    }

}