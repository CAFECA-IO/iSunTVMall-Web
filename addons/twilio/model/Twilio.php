<?php
namespace addons\twilio\model;
use think\addons\BaseModel as Base;
use think\Db;
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
 * Twilio短信接口
 */
class Twilio extends Base{
	public function getConfigs(){
		$data = cache('twilio_sms');
		if(!$data){
			$rs = Db::name('addons')->where('name','Twilio')->field('config')->find();
		    $data =  json_decode($rs['config'],true);
		    cache('twilio_sms',$data,31622400);
		}
		return $data;
	}
 
    public function install(){
		Db::startTrans();
		try{
			$hooks = ['sendSMS'];
			$this->bindHoods("Twilio", $hooks);
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
			$this->unbindHoods("Twilio", $hooks);
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}

	public function sendSMS($params){
		include_once WST_ADDON_PATH."twilio/sdk/Twilio/autoload.php";
		$smsConf = $this->getConfigs();
		$code = [];
		$isVerfy = false;
		try{
			$tpl = $params['params']['tpl']['tplContent'];
			foreach($params['params']['params'] as $key =>$v){
				$tpl = str_replace('${'.$key."}",$v,$tpl);
				if($key=='VERFIYCODE')$isVerfy = true;
			}
			foreach($params['params']['params'] as $key =>$v){
				$key = str_replace('_','',$key);
				if($isVerfy){
	                if($key=='VERFIYCODE')$code[] = '"'.$key.'":"'.$v.'"';
				}else{
					$code[] = '"'.$key.'":"'.$v.'"';
				}
			}
			$codes = "{".implode(',',$code)."}";
	        $params['content'] = $codes;
			$content = $tpl;

			$account_sid = $smsConf['accountSid'];
			$auth_token = $smsConf['authToken'];
			// In production, these should be environment variables. E.g.:
			// $auth_token = $_ENV["TWILIO_ACCOUNT_SID"]
			// A Twilio number you own with SMS capabilities
			$twilio_number = '+'.$smsConf['triaNumber'];
			$client = new \Twilio\Rest\Client($account_sid, $auth_token);
			$result = $client->messages->create(
			    // Where to send a text message (your cell phone?)
			    '+'.$params['phoneNumber'],
			    array(
			        'from' => $twilio_number,
			        'body' => $content
			    )
			);
			$twilioRs = $result->toArray();
			$log = model('common/logSms')->get($params['smsId']);
			$log->smsReturnCode = json_encode($twilioRs);
			$log->smsContent = $params['content'];
			$log->save();
			$twilioStatus = ['accepted','queued','sending','sent','receiving','received','delivered','read'];
			if(in_array($twilioRs['status'], $twilioStatus)){
		        $params['status']['msg'] = lang('twilio_SMS_sent_successfully');
		        $params['status']['status'] = 1;
			}else{
				$params['status']['msg'] = lang('twilio_SMS_sending_failed');
		        $params['status']['status'] = -1;
			}
		}catch (\Exception $e) {
	 		Db::rollback();
	  		$params['status']['msg'] = lang('twilio_SMS_sending_failed');
			$params['status']['status'] = -1;
	   	}
	}
}
