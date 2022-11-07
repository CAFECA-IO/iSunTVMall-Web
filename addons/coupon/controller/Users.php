<?php
namespace addons\coupon\controller;

use think\addons\Controller;
use addons\coupon\model\Coupons as M;
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
 * 优惠券插件
 */
class Users extends Controller{
	protected $beforeActionList = ['checkAuth' ];
	public function __construct(){
		parent::__construct();
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}
	/**
	 * 优惠券列表
	 */
	public function index(){
		$m = new M();
		$rs = $m->getCouponNumByUser();
		$this->assign("coupons",$rs);
    	return $this->fetch("/home/users/list");
	}
	/**
	 * 加载优惠券数据
	 */
	public function pageQuery(){
		$m = new M();
		return $m->pageQueryByUser();
	}
	/**************************************** 手机版 **************************************************/
	/**
	 * 手机优惠券列表
	 */
	public function moindex(){
		$this->assign('status',0);
		return $this->fetch("/mobile/users/list");
	}
	/**************************************** 微信版 **************************************************/
	/**
	 * 手机优惠券列表
	 */
	public function wxindex(){
		$this->assign('status',0);
		return $this->fetch("/wechat/users/list");
	}
}