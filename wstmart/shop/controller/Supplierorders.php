<?php
namespace wstmart\shop\controller;
use wstmart\shop\model\SupplierOrders as M;
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
 * 订单控制器
 */
class Supplierorders extends Base{
	protected $beforeActionList = ['checkAuth'];
	
    /**
    * 提交订单
    */
	public function submit(){
		$m = new M();
		$rs = $m->submit();
		if($rs["status"]==1){
			$pkey = WSTBase64urlEncode($rs["data"]."@1");
			$rs["pkey"] = $pkey;
		}
		return $rs;
	}
	/**
	 * 订单提交成功
	 */
	public function succeed(){
		$m = new M();
		$pkey = input("pkey");
		$this->assign('pkey',$pkey);
		$pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
        $orderNo = $pkey[0];
        $isBatch = (int)$pkey[1];
		$shopId = (int)session('WST_USER.shopId');
		$rs = $m->getByUnique($shopId,$orderNo,$isBatch);
		$this->assign('object',$rs);
		if(!empty($rs['list'])){
			if($rs['payType']==1 && $rs['totalMoney']>0){
				$this->assign('rs',$rs);
				return $this->fetch('supplier/order_pay_step1');
			}else{
			    return $this->fetch('supplier/order_success');
			}
		}else{
			$this->assign('message','Sorry~404。。。');
			return $this->fetch('supplier/error_msg');
		}
	}
	/**
	* 用户-提醒发货
	*/
	public function noticeDeliver(){
		$m = new M();
		return $m->noticeDeliver();
	}
	
	
	/**
	 * 用户-待付款订单
	 */
	public function waitPay(){
	    $this->assign("p",(int)input("p"));
		return $this->fetch('supplier/orders/list_wait_pay');
	}
    /**
	 * 用户-获取待付款列表
	 */
    public function waitPayByPage(){
		$m = new M();
		$rs = $m->userOrdersByPage(-2);
		return WSTReturn("", 1,$rs);
	}
    /**
	 * 等待收货
	 */
	public function waitReceive(){
        $this->assign("p",(int)input("p"));
		return $this->fetch('supplier/orders/list_wait_receive');
	}
    /**
	 * 获取收货款列表
	 */
    public function waitReceiveByPage(){
		$m = new M();
		$rs = $m->userOrdersByPage([0,1]);
		return WSTReturn("", 1,$rs);
	}
	/**
	 * 用户-待评价
	 */
    public function waitAppraise(){
        $this->assign("p",(int)input("p"));
		return $this->fetch('supplier/orders/list_appraise');
	}
	/**
	 * 用户-待评价
	 */
	public function waitAppraiseByPage(){
		$m = new M();
		$rs = $m->userOrdersByPage(2,0);
		return WSTReturn("", 1,$rs);
	}
	/**
	 * 用户-已完成订单
	 */
    public function finish(){
        $this->assign("p",(int)input("p"));
		return $this->fetch('supplier/orders/list_finish');
	}
	/**
	 * 用户-已完成订单
	 */
	public function finishByPage(){
		$m = new M();
		$rs = $m->userOrdersByPage(2,-1);
		return WSTReturn("", 1,$rs);
	}
   /**
	 * 用户-加载取消订单页面
	 */
	public function toCancel(){
		return $this->fetch('supplier/orders/box_cancel');
	}

	/**
	 * 用户取消订单
	 */
	public function cancellation(){
		$m = new M();
		$rs = $m->cancel();
		return $rs;
	}
    /**
	 * 用户-取消订单列表
	 */
	public function cancel(){
        $this->assign("p",(int)input("p"));
		return $this->fetch('supplier/orders/list_cancel');
	}
	/**
	 * 用户-获取已取消订单
	 */
    public function cancelByPage(){
		$m = new M();
		$rs = $m->userOrdersByPage(-1);
		return WSTReturn("", 1,$rs);
	}
    /**
	 * 用户-拒收订单
	 */
	public function toReject(){
		return $this->fetch('supplier/orders/box_reject');
	}
	/**
	 * 用户拒收订单
	 */
	public function reject(){
		$m = new M();
		$rs = $m->reject();
		return $rs;
	}
	/**
	 * 用户-申请退款
	 */
	public function toRefund(){
		$m = new M();
		$rs = $m->getMoneyByOrder((int)input('id'));
		$this->assign('object',$rs);
		return $this->fetch('supplier/orders/box_refund');
	}

	/**
	 * 用户-拒收/退款列表
	 */
	public function abnormal(){
        $this->assign("p",(int)input("p"));
		return $this->fetch('supplier/orders/list_abnormal');
	}
	/**
	 * 获取用户拒收/退款列表
	 */
    public function abnormalByPage(){
		$m = new M();
		$rs = $m->userOrdersByPage([-3]);
		return WSTReturn("", 1,$rs);
	}

	/**
	 * 用户收货
	 */
	public function receive(){
		$m = new M();
		$rs = $m->receive();
		return $rs;
	}

    /**
	 * 用户-订单详情
	 */
	public function detail(){
		$m = new M();
		$rs = $m->getByView((int)input('id'));
		$this->assign('object',$rs);
        $this->assign("p",(int)input("p"));
        $this->assign("src",input("src",""));
		return $this->fetch('supplier/orders/view');
	}
	
   /**
	* 用户-评价页
	*/
	public function orderAppraise(){
		$m = new M();
		//根据订单id获取 商品信息跟商品评价
		$data = $m->getOrderInfoAndAppr();
		$this->assign(['data'=>$data['data'],
					   'count'=>$data['count'],
					   'alreadys'=>$data['alreadys']
						]);
		return $this->fetch('supplier/orders/list_order_appraise');
	}
	/**
	* 设置完成评价
	*/
	public function complateAppraise($orderId){
		$m = new M();
		return $m->complateAppraise($orderId);
	}

}
