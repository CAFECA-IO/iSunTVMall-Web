<?php
namespace wstmart\common\exception;
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
 */
use think\exception\Handle;

class WstHttpException extends Handle
{

    public function render(\Exception $e)
    {
    	if(config('app_debug')){
    		return parent::render($e);
    	}else{
    		$request = request();
    		$isMobile = $request->isMobile();
			$isWeChat = (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false);
			$hasMobile = (WSTDatas('ADS_TYPE',3)!='')?true:false;
			$hasWechat = (WSTConf('CONF.wxenabled',2)==1)?true:false;
			if($isWeChat && $hasWechat){
				header("Location:".url('wechat/error/index'));
			}else if($isMobile && $hasMobile){
                header("Location:".url('mobile/error/index'));
			}else{
                print_r($e);
	    	    //header("Location:".url('home/error/index'));
	    	}
    	}
    }

}