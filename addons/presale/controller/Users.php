<?php
namespace addons\presale\controller;

use think\addons\Controller;
use addons\presale\model\Presales as M;
use addons\presale\model\Orders as OM;
use wstmart\common\model\Payments;
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
 * 预售活动插件
 */
class Users extends Controller{
	public function __construct(){
		parent::__construct();
		$m = new M();
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}

	/**
	 * 预售列表页
	 */
	public function plist(){
		return $this->fetch("/home/users/list");
	}

	/**
	 * 获取预售单列表
	 */
	public function toView(){
		$m = new OM();
		$rs = $m->getOrderDetail();
		$rs['goodsImg'] = WSTImg($rs['goodsImg'],3);
		$rs['payInfo'] = WSTLangPayType($rs['payType']);
		$rs['deliverInfo'] = WSTLangDeliverType($rs['deliverType']);
		$this->assign('object',$rs);
		return $this->fetch("/home/users/view");
	}

	/**
	 * 获取保证金列表
	 */
	public function pageQuery(){
		$m = new OM();
		return $m->pageQuery();
	}

	/**
	 * 微信预售列表页
	 */
	public function wxlist(){
		return $this->fetch("/wechat/users/list");
	}

	/**
	 * 在线支付方式
	 */
	public function wxpayTypes(){
		//获取支付方式
		$payments = model('common/payments')->getByGroup('3',1);
        $this->assign('payments',$payments);
        $pkey = input("pkey");

        $pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
		$this->assign('pkey',input("pkey"));
		$this->assign('payObj',$pkey[0]);
		return $this->fetch("/wechat/index/pay_list");
	}

	/**
	 * 获取预售单列表
	 */
	public function getOrderDetail(){
		$m = new OM();
		$rs = $m->getOrderDetail();
		$rs['goodsImg'] = WSTImg($rs['goodsImg'],3);
		$rs['payTypeName'] = WSTLangPayType($rs['payType']);
		$rs['deliverTypeName'] = WSTLangDeliverType($rs['deliverType']);
		return $rs;
	}

	/**
	 * 微信检测是否支付预售价格
	 */
	public function wxcheckPayStatus(){
		$m = new M();
		$data = $m->checkPayStatus((int)input('id'));
		if(empty($data)){
			session('wxcheckPayStatus',lang('presale_invoid_deposit_recode'));
			$this->redirect('wechat/error/message',['code'=>'wxcheckPayStatus']);
		}else{
			if($data["isCanPay"]==1){
				$m = new M();
				$pa = new Payments();
				$payments = $pa->getByGroup('3');
				$this->assign('payments',$payments);
				$rs = $m->getPayInfo((int)input('id'),2);
				$this->assign("object",$rs['data']['presale']);
				$this->assign("payObj",'deal');
				return $this->fetch("/wechat/index/pay_list");
			}else{
				return $this->fetch("/wechat/users/list");
			}
		}
	}


	/**
	 * 手机预售列表页
	 */
	public function molist(){
		return $this->fetch("/mobile/users/list");
	}
	/**
	 * 在线支付方式
	 */
	public function mopayTypes(){
		//获取支付方式
		$payments = model('common/payments')->getByGroup('2',1);
        $this->assign('payments',$payments);
        $pkey = input("pkey");

        $pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
		$this->assign('pkey',input("pkey"));
		$this->assign('payObj',$pkey[0]);
		return $this->fetch("/mobile/index/pay_list");
	}
	/**
	 * 手机检测是否支付预售价格
	 */
	public function mocheckPayStatus(){
		$m = new M();
		$data = $m->checkPayStatus((int)input('id'));
		if(empty($data)){
			session('mocheckPayStatus',lang('presale_invoid_deposit_recode'));
			$this->redirect('mobile/error/message',['code'=>'mocheckPayStatus']);
		}else{
			if($data["isCanPay"]==1){
				$m = new M();
				$pa = new Payments();
				$payments = $pa->getByGroup('3');
				$this->assign('payments',$payments);
				$rs = $m->getPayInfo((int)input('id'),2);
				$this->assign("object",$rs['data']['presale']);
				$this->assign("payObj",'deal');
				return $this->fetch("/mobile/index/pay_list");
			}else{
				return $this->fetch("/mobile/users/list");
			}
		}
	}

    /**
     * 获取预售订单列表
     */
    public function wePageQuery(){
        $this->checkWeappAuth();
        $userId = model('weapp/index')->getUserId();
        $m = new OM();
        $rs = $m->pageQuery($userId);
        return jsonReturn('success',1,$rs);
    }

    /**
     * 获取预售订单详情
     */
    public function weGetOrderDetail(){
        $this->checkWeappAuth();
        $userId = model('weapp/index')->getUserId();
        $m = new OM();
        $rs = $m->getOrderDetail($userId);
        $rs['goodsSpecNames'] = implode(' ',explode('@@_@@',$rs['goodsSpecNames']));
        $rs['goodsImg'] = WSTImg($rs['goodsImg'],3);
        $rs['payInfo'] = WSTLangPayType($rs['payType']);
        $rs['deliverInfo'] = WSTLangDeliverType($rs['deliverType']);
        return jsonReturn('success',1,$rs);
    }
}
