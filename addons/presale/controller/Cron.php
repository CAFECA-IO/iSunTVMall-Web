<?php
namespace addons\presale\controller;

use think\addons\Controller;
use addons\presale\model\Presales as M;
use addons\presale\model\Weixinpays as WM;
use addons\presale\model\WeixinpaysApp as PM;

class Cron extends Controller{

	/**
	 * 定时任务
	 */
	public function scanTask(){
		$m = new M();
		$m->scanCancelOrder();//取消未支付定金的订单
		$m->batchRefund();//退款
		$rs = $m->scanTask();//过期没收尾款
		return json($rs);
	}

	/**
	 * 定时任务
	 */
	public function payNotice(){
		$m = new M();
		$rs = $m->scanPayNotice();//预售结束发支付尾款通知
		return json($rs);
	}
	
	public function refundNotify(){
		$wm = new WM();
		$wm->presaleNotify();
	}

	public function refundAppNotify(){
		$wm = new PM();
		$wm->presaleNotify();
	}
}