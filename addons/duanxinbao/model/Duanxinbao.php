<?php
namespace addons\duanxinbao\model;
use think\addons\BaseModel as Base;
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
 * 短信宝短信接口
 */
class duanxinbao extends Base{
	public function getConfigs(){
		$data = cache('duanxinbao_sms');
		if(!$data){
			$rs = Db::name('addons')->where('name','duanxinbao')->field('config')->find();
		    $data =  json_decode($rs['config'],true);
		    cache('duanxinbao_sms',$data,31622400);
		}
		return $data;
	}
 
    public function install(){
		Db::startTrans();
		try{
			$hooks = ['sendSMS'];
			$this->bindHoods("duanxinbao", $hooks);
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}
	public function uninstall(){
		Db::startTrans();
		try{
			$hooks = ['sendSMS'];
			$this->unbindHoods("duanxinbao", $hooks);
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}
	/**
	 * 发送短信接口
	 */
	public function http($params){
        $resp = '-1001708';
		try{
			$smsConf = $this->getConfigs();	
		    $smsapi = "http://www.smsbao.com/"; //短信网关
			$user = $smsConf['smsKey']; //短信平台帐号
			$pass = md5($smsConf['smsPass']); //短信平台密码
			$content= $params['content'];//要发送的短信内容
			$phone = $params['phoneNumber'];
			$sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
			$result =file_get_contents($sendurl) ;
			return $result;
		}catch (\Exception $e) {
			$resp = '-1001708';
	    }
        return $resp;
	}
	public function sendSMS($params){
		$smsConf = $this->getConfigs();
		$statusStr = array(
		"-1001708" =>WSTLang('duanxinbao_tip2'),
	    "0"  => WSTLang('duanxinbao_tip1'),
		"-1" => WSTLang('duanxinbao_tip3'),
		"-2" => WSTLang('duanxinbao_tip4'),
		"30" => WSTLang('duanxinbao_tip5'),
		"40" => WSTLang('duanxinbao_tip6'),
		"41" => WSTLang('duanxinbao_tip7'),
		"42" => WSTLang('duanxinbao_tip8'),
		"43" => WSTLang('duanxinbao_tip9'),
		"50" => WSTLang('duanxinbao_tip10')
	    );
		$tpl = $params['params']['tpl']['tplContent'];
		foreach($params['params']['params'] as $key =>$v){
			$tpl = str_replace('${'.$key."}",$v,$tpl);
		}
		$params['content'] = "【".$smsConf["signature"]."】".$tpl;
		$code = $this->http($params);
		$log = model('common/logSms')->get($params['smsId']);
		$log->smsReturnCode = $code."||".$statusStr[$code];
		$log->smsContent = $params['content'];
		$log->save();
		if($code == 0){
	        $params['status']['msg'] = WSTLang('duanxinbao_tip1');
	        $params['status']['status'] = 1;
		}else{
			$params['status']['msg'] = WSTLang('duanxinbao_tip2');
	        $params['status']['status'] = -1;
		}
	}

}
