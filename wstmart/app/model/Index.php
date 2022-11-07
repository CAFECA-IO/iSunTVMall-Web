<?php
namespace wstmart\app\model;
use wstmart\common\model\Tags as T;
use think\Db;
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
 * 默认类
 */
class Index extends Base{
	/**
	 * 获取系统消息
	 */
	function getSysMsg($msg='',$order=''){
		$data = [];
		$userId = $this->getUserId();
		if($userId>0){
			if($msg!=''){
				$data['message']['num'] = Db::name('messages')->where(['receiveUserId'=>$userId,'msgStatus'=>0,'dataFlag'=>1])->count();
			}
			if($order!=''){
				$data['order']['waitPay'] = Db::name('orders')->where(['userId'=>$userId,'orderStatus'=>-2,'dataFlag'=>1])->count();
				$data['order']['waitSend'] = Db::name('orders')->where(['userId'=>$userId,'orderStatus'=>0,'dataFlag'=>1])->count();
				$data['order']['waitReceive'] = Db::name('orders')->where(['userId'=>$userId,'orderStatus'=>1,'dataFlag'=>1])->count();
				$data['order']['waitAppraise'] = Db::name('orders')->where(['userId'=>$userId,'orderStatus'=>2,'isAppraise'=>0,'dataFlag'=>1])->count();
			}
		}else{
			$data['message']['num'] = 0;
			$data['order']['waitPay'] = 0;
			$data['order']['waitSend'] = 0;
			$data['order']['waitReceive'] = 0;
			$data['order']['waitAppraise'] = 0;
		}
		
		return $data;
	}
    /**
     * 获取插件配置
     */
    public function getAddonConfig($addonName){
        $addon = Db::name('addons')->where("name",$addonName)->field("config")->find();
        $config = json_decode($addon["config"],true);
        return $config;
    }
}
