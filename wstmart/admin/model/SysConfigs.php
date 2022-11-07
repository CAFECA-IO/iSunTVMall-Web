<?php
namespace wstmart\admin\model;
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
 * 商城配置业务处理
 */
use think\Db;
use think\facade\Config;
use Env;
class SysConfigs extends Base{
	/**
	 * 获取商城配置
	 */
	public function getSysConfigs(){
		$rs = $this->field('fieldCode,fieldValue')->select();
		$rv = [];
		$split = [
		    'submitOrderTipUsers','payOrderTipUsers','cancelOrderTipUsers','rejectOrderTipUsers','refundOrderTipUsers','complaintOrderTipUsers','cashDrawsTipUsers'
		];
		foreach ($rs as $v){
			if(in_array($v['fieldCode'],$split)){
                $rv[$v['fieldCode']] = ($v['fieldValue']=='')?[]:explode(',',$v['fieldValue']);
			}else{
                $rv[$v['fieldCode']] = $v['fieldValue'];
			}
		}
		$signScore = explode(",",$rv['signScore']);
		for($i=0;$i<31;++$i){
			$rv['signScore'.$i] = ($signScore[0]==0)?0:$signScore[$i];
		}
		return $rv;
	}

	/**
	 * 获取商城设置
	 */
	public function getSysConfigsByType($type = 0){
		$rs = $this->field('fieldCode,fieldValue')->where('fieldType','=',$type)->select();
		$rv = [];
		$split = [
		    'submitOrderTipUsers','payOrderTipUsers','cancelOrderTipUsers','rejectOrderTipUsers','refundOrderTipUsers','complaintOrderTipUsers','cashDrawsTipUsers'
		];
		foreach ($rs as $v){
			if(in_array($v['fieldCode'],$split)){
                $rv[$v['fieldCode']] = ($v['fieldValue']=='')?[]:explode(',',$v['fieldValue']);
			}else{
                $rv[$v['fieldCode']] = $v['fieldValue'];
			}
		}

		if(isset($rv['signScore'])){
			$signScore = explode(",",$rv['signScore']);
			for($i=0;$i<31;++$i){
				$rv['signScore'.$i] = ($signScore[0]==0)?0:$signScore[$i];
			}
		}
		return $rv;
	}

	
    /**
	 * 编辑
	 */
	public function edit($fieldType = 0){
		$list = $this->where('fieldType',$fieldType)->field('configId,fieldCode,fieldValue')->select();
		if($fieldType == 0){
			//检测语言
			$langId = (int)input('post.defaultSysLang');
			$lan = Db::name('languages')->where(['id'=>$langId,'status'=>1])->find();
			if(empty($lan))return WSTReturn(lang('sysConfig_err4'));
			$this->setconfig('default_lang',$lan['code']);
            //调试开关
			$isDebug =  input('post.isDebug');
			$this->setconfig('app_debug',($isDebug==1)?'true':'false');
			$isOpenCache =  (int)input('post.isOpenCache');
			if($isOpenCache==0){
                 WSTClearAllCache();
			}
		}else if($fieldType == 4){
			$isOpenScorePay = (int)input('post.isOpenScorePay');
			if($isOpenScorePay==1){
				$orderUsableScoreRate = (int)input('post.orderUsableScoreRate');
				if($orderUsableScoreRate>100 || $orderUsableScoreRate<0)return WSTReturn(lang('sysConfig_err1'), -1);
			}
			$commissionRate = (int)input('post.drawCashCommission');
			if($commissionRate<0 || $commissionRate>100)return WSTReturn(lang('sysConfig_err2'), -1);
			$afterSaleServiceDays = (int)input('post.afterSaleServiceDays');
			$shopAcceptDays = (int)input('post.shopAcceptDays');
			$userSendDays = (int)input('post.userSendDays');
			$shopReceiveDays = (int)input('post.shopReceiveDays');
			$shopSendDays = (int)input('post.shopSendDays');
			$userReceiveDays = (int)input('post.userReceiveDays');
			$totalAfterSaleDays = $shopAcceptDays + $userSendDays + $shopReceiveDays + $shopSendDays + $userReceiveDays;
			// 售后有效日 需大于整个流程售后流程限定日
			if($afterSaleServiceDays<=$totalAfterSaleDays){
				return WSTReturn(lang('sysConfig_err3'), -1);
			}
        }

		Db::startTrans();
        try{
			foreach ($list as $key =>$v){
				$code = trim($v['fieldCode']);
				if(in_array($code,['wstVersion','wstMd5','wstMobileImgSuffix','mallLicense']))continue;
				$val = input('post.'.trim($v['fieldCode']));
				//将全角，改成,并且除去空格
				$val = trim($val);
				$val = str_replace('，',',',$val);
			    //启用图片
				if(substr($val,0,7)=='upload/' && strpos($val,'.')!==false){
					WSTUseResource(1, $v['configId'],$val, 'sys_configs','fieldValue');
				}
				$this->update(['fieldValue'=>$val],['fieldCode'=>$code]);
				//如果是关闭会员充值的话就对禁用菜单
				if($v['fieldCode']=='isOpenRecharge'){
					Db::name('home_menus')->where('menuUrl','shop/logmoneys/torecharge')->update(['isShow'=>((int)$val==1)?1:0]);
					Db::name('home_menus')->where('menuUrl','home/logmoneys/toUserRecharge')->update(['isShow'=>((int)$val==1)?1:0]);
					cache('WST_HOME_MENUS',null);
				}
			}
			Db::commit(); 
			cache('WST_CONF',null);
			return WSTReturn(lang('op_ok'), 1);
        }catch (\Exception $e) {
		    Db::rollback();
		}
		return WSTReturn(lang('op_err'), 1);
	}
	/**
	 * 修改config
	 */
	function setconfig($pat, $rep){
		$pats = '/\'' . $pat . '\'(.*?),/';
		$reps = "'". $pat. "'". "              => " . "'".$rep ."',";
		$fileurl = Env::get('root_path')."config/app.php";
		$string = file_get_contents($fileurl); //加载配置文件
		$string = preg_replace($pats, $reps, $string); // 正则查找然后替换
		file_put_contents($fileurl, $string); // 写入配置文件
	}

}
