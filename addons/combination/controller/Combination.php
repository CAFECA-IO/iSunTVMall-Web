<?php
namespace addons\combination\controller;

use think\addons\Controller;
use addons\combination\model\Combinations as M;

class Combination extends Controller{
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 移动端组合套餐页面
	 */
	public function mobile(){
		$m = new M();
        $combineId = input('combineId/d',0);
        $goods = $m->getBySale($combineId);
        if(!empty($goods)){
	        $this->assign('goods',$goods);
	        return $this->fetch("/mobile/index");
	    }else{
	    	$this->assign('message',lang('combination_package_not_exist'));
            return $this->fetch('mobile@'.WSTConf('CONF.wstmobileStyle').'/error_sys');
	    }
	}

	/**
	 * 微信端组合套餐页面
	 */
	public function wechat(){
		$m = new M();
        $combineId = input('combineId/d',0);
        $goods = $m->getBySale($combineId);
        if(!empty($goods)){
	        $this->assign('goods',$goods);
	        return $this->fetch("/wechat/index");
	    }else{
	    	$this->assign('message',lang('combination_package_not_exist'));
            return $this->fetch('wechat@'.WSTConf('CONF.wstwechatStyle').'/error_sys');
	    }
	}

    /***********************************小程序端**********************************/
    /*
     * 获取商品套餐列表
     */
    public function wePageQuery(){
        $m = new M();
        $goodsId = (int)input('goodsId');
        $rs = $m->getRelateCombinte($goodsId);
        return jsonReturn('success',1,$rs['list']);
    }

    /**
     * 商品组合套餐详情
     */
    public function weDetail(){
        $m = new M();
        $rs = $m->getBySale((int)input('combineId'));
        $rs['startTime'] = date('Y-m-d H:i:s',strtotime($rs['startTime']));
        $rs['endTime'] = date('Y-m-d H:i:s',strtotime($rs['endTime']));
        // 未找到该商品组合套餐
        if(empty($rs))return jsonReturn(lang('combination_package_detail_not_exist'),1);
        return jsonReturn('ok',1,$rs);
    }
}
