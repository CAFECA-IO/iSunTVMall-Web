<?php
namespace addons\txlive\controller;
use think\addons\Controller;
use addons\txlive\model\TxLives as M;
use addons\txlive\model\TxLive as DM;

class Cron extends Controller{
	public function __construct(){
		parent::__construct();
	}
	/**
	 * 定时任务 获取直播房间的回放视频
	 */
	public function getLiveReplays(){
        $dm = new DM();
        $config = $dm->getAddonConfig();
        if($config['liveReplay']==1){
            $m = new M();
            $rs = $m->getLiveReplays($config);
            return json($rs);
        }else{
           return WSTReturn(lang('txlive_operation_success'),1);
        }
	}
}
