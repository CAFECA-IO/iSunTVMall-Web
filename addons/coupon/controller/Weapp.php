<?php
namespace addons\coupon\controller;
use think\addons\Controller;
use addons\coupon\model\Coupons as M;
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
 * weapp优惠券接口插件
 */
class WeApp extends Controller{
	/**
	 * 权限验证方法
	 */
	protected function checkAuth(){
		$tokenId = input('tokenId');
		if($tokenId==''){
			$rs = jsonReturn(lang('no_login'),-999);
			die($rs);
		}
		$userId = db('weapp_session')->where("tokenId='{$tokenId}'")->value('userId');
		if(empty($userId)){
			$rs = jsonReturn(lang('login_over_time'),-999);
			die($rs);
		}
		return true;
	}
	/**
	 * 领券中心列表查询
	 */
	public function pageCouponQuery(){
		$m = new M();
		$userId = model('weapp/index')->getUserId();
		$rs =  $m->pageCouponQuery($userId);
		if($rs){
			return jsonReturn('success',1,$rs);
		}else{
			return jsonReturn('',-1);
		}
	}
	/**
	 * 领取优惠券
	 */
	public function receive(){
		$this->checkAuth();
		$m = new M();
		$userId = model('weapp/index')->getUserId();
		$rs = $m->receive($userId);
		return jsonReturn('',1,$rs);
	}
	/**
	 * 加载优惠券数据
	 */
	public function pageQueryByUser(){
		$this->checkAuth();
		$m = new M();
		$userId = model('weapp/index')->getUserId();
		$rs =  $m->pageQueryByUser($userId);
		if($rs){
			return jsonReturn('success',1,$rs);
		}else{
			return jsonReturn('',-1);
		}
	}
	/**
	 *  可用优惠券商品查询
	 */
	public function pageQueryByCouponGoods(){
		$m = new M();
		$rs =  $m->pageQueryByCouponGoods();
		if($rs){
			return jsonReturn('success',1,$rs);
		}else{
			return jsonReturn('',-1);
		}
	}
	/**
	 *  可用优惠券商品查询
	 */
	public function getCouponsByGoods(){
		$m = new M();
		$userId = model('weapp/index')->getUserId();
		$rs =  $m->getCouponsByGoods($userId);
		if($rs){
			return jsonReturn('success',1,$rs);
		}else{
			return jsonReturn('',-1);
		}
	}
	/**
	 *  领取的优惠券数
	 */
	public function couponsNum(){
		$this->checkAuth();
		$m = new M();
		$userId = model('weapp/index')->getUserId();
		$rs = $m->couponsNum($userId);
		return jsonReturn('',1,$rs);
	}


    /**
     * 小程序首页优惠券列表
     */
    public function weHomeList(){
        $m = new M();
        $data = $m->pageCouponQuery(0,4);
        return jsonReturn('success',1,$data);
    }
}
