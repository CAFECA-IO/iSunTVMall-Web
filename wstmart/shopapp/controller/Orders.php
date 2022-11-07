<?php
namespace wstmart\shopapp\controller;
use wstmart\shopapp\model\Orders as M;
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
 * 订单控制器
 */
class Orders extends Base{
	// 前置方法执行列表
    protected $beforeActionList = [
        'checkAuth'
    ];
	/**
	 * 订单详情
	 */
	public function getDetail(){
		$m = new M();
		$userId = (int)$m->getUserId();
		$isShop = (int)input('isShop');
		if($isShop==1){
			// 根据用户id查询店铺id
			$userId = (int)model('shops')->getShopId($userId);
		}
		$rs = $m->getByView((int)input('id'), $userId);
		if(isset($rs['status']))return json_encode($rs);
		// 删除无用字段
		unset($rs['log']);
		// 发票税号
		$invoiceArr = json_decode($rs['invoiceJson'],true);
		if(isset($invoiceArr['invoiceCode']))$rs['invoiceCode'] = $invoiceArr['invoiceCode'];
		$rs['status'] = WSTLangOrderStatus($rs['orderStatus']);
		$rs['payInfo'] = WSTLangPayType($rs['payType']);
		$rs['deliverInfo'] = WSTLangDeliverType($rs['deliverType']);
		foreach($rs['goods'] as $k=>$v){
			$v['goodsImg'] = WSTImg($v['goodsImg'],3);
		}
		// 若为取消或拒收则取出相应理由
		if($rs['orderStatus']==-1){
			if($rs['cancelReason']==0){
				$rs['cancelDesc'] = lang("overtime_to_pay");
			}else{
				// 取消理由
				$reason = WSTDatas('ORDER_CANCEL');
				$rs['cancelDesc'] = $reason[$rs['cancelReason']]['dataName'];
			}
		}else if($rs['orderStatus']==-3){
			// 拒收理由
			$reason = WSTDatas('ORDER_REJECT');
			$rs['cancelDesc'] = $reason[$rs['rejectReason']]['dataName'];
		}
		// 退款理由   $rs['refundReason'] = WSTDatas('REFUND_TYPE');
		/*******  满就送减免金额 *******/
        foreach($rs['goods'] as $k=>$v){
            if(isset($v['promotionJson']) && $v['promotionJson']!=''){// 有使用优惠券
                $rs['goods'][$k]['promotionJson'] = json_decode($v['promotionJson'],true);
                $rs['goods'][$k]['promotionJson']['extraJson'] = json_decode($rs['goods'][$k]['promotionJson']['extraJson'],true);
                // 满就送减免金额
                $rs['rewardMoney'] = $money = $rs['goods'][$k]['promotionJson']['promotionMoney'];
                break;
            }
        }
        /*********  优惠券  *********/
        if(isset($rs['userCouponId']) && $rs['userCouponId']>0){
            // 获取优惠券信息
            $money = json_decode($rs['userCouponJson'],true)['money']; // 优惠券优惠金额
            $rs['couponMoney'] = number_format($money,2);
        }
		return json_encode(WSTReturn(lang("success_msg"),1,$rs));
	}
	/*********************************************** 商家操作订单 ************************************************************/


	/**
	* 商家-订单列表
	*/
	public function getSellerOrderList(){
		/*
		 	-3:拒收、退款列表
			-2:待付款列表
			-1:已取消订单
			 0: 待发货
			1,2:待评价/已完成
		*/
		$type = input('param.type');
		$express = false;// 快递公司数据
		$status = [];
		switch ($type) {
			case 'waitPay':
				$status=-2;
				break;
			case 'waitReceive':
				$status=1;
				break;
			case 'waitDelivery':
				$status=0;
				$express=true;
				break;
			case 'finish':
				$status=2;
				break;
			case 'abnormal': // 退款/拒收 与取消合并
				$status=[-1,-3];
				break;
			case 'waitRefund': // 待退款
				$status=[-1,-3];
				break;
			default:
				$status=[-5,-4,-3,-2,-1,0,1,2];
				$express=true;
				break;
		}
		$m = new M();
		$userId = $m->getUserId();
		$shopId = (int)$m->getShopId($userId);

		$rs = $m->shopOrdersByPage($status, $shopId);

		foreach($rs['data'] as $k=>$v){
			// 删除无用字段
			WSTUnset($rs['data'][$k],'goodsMoney,totalMoney,orderSrc,createTime,payTypeName,isRefund,userAddress,userName,deliverTypeName');
			// 判断是否退款
			if(in_array($v['orderStatus'],[-1,-3]) && ($v['payType']==1) && ($v['isPay']==1) ){
				$rs['data'][$k]['status'] .= ($v['isRefund']==1)?lang("refund"):lang("no_refund");
			}
			if(!empty($v['list'])){
				foreach($v['list'] as $k1=>$v1){
					$rs['data'][$k]['list'][$k1]['goodsImg'] = $v1['goodsImg'];
				}
			}
		}
		// 快递公司数据
		if($express)$rs['express'] = model('Express')->listQuery();

		if(empty($rs['data']))return json_encode(WSTReturn(lang("empty_orders"),-1));
		return json_encode(WSTReturn(lang("success_msg"), 1, $rs));
	}

	/**
	 * 商家发货
	 */
	public function deliver(){
		$m = new M();
		$userId = (int)$m->getUserId();
		$shopId = (int)$m->getShopId($userId);
		$rs = $m->deliver($userId, $shopId);

		return json_encode($rs);
	}
	/**
	 * 商家修改订单价格
	 */
	public function editOrderMoney(){
		$m = new M();
		$userId = (int)$m->getUserId();
		$shopId = (int)$m->getShopId($userId);
		$rs = $m->editOrderMoney($userId, $shopId);

		return json_encode($rs);
	}
	 /**
     * 获取单条订单的商品信息
     */
    public function waitDeliverById(){
        $m = new M();
        $userId = (int)$m->getUserId();
		$shopId = (int)$m->getShopId($userId);
        $rs = $m->waitDeliverById();
        return json_encode(WSTReturn('ok', 1, $rs));
    }
	/**
	 * 商家-操作退款
	 */
	public function toShopRefund(){
		$rs = model('OrderRefunds')->getRefundMoneyByOrder((int)input('id'));
		return json_encode(WSTReturn(lang("success_msg"), 1, $rs));
	}

    /**
     * 获取核销订单信息
     */
    public function getVerificatOrder(){
        $m = new M();
        $userId = (int)$m->getUserId();
        $shopId = (int)$m->getShopId($userId);
        $rs = $m->getVerificatOrder($shopId);
        if(empty($rs))return json_encode(WSTReturn(lang("invalid_v_code")));
        /*******  满就送减免金额 *******/
        foreach($rs['goods'] as $k=>$v){
            if(isset($v['promotionJson']) && $v['promotionJson']!=''){
                $rs['goods'][$k]['promotionJson'] = json_decode($v['promotionJson'],true);
                $rs['goods'][$k]['promotionJson']['extraJson'] = json_decode($rs['goods'][$k]['promotionJson']['extraJson'],true);
                // 满就送减免金额
                $rs['rewardMoney'] = $money = $rs['goods'][$k]['promotionJson']['promotionMoney'];
                break;
            }
        }
        /*********  优惠券  *********/
        if(isset($rs['userCouponId']) && $rs['userCouponId']>0){
            // 获取优惠券信息
            $money = json_decode($rs['userCouponJson'],true)['money']; // 优惠券优惠金额
            $rs['couponMoney'] = number_format($money,2);
        }
        return json_encode(WSTReturn("", 1,$rs));
    }

    /**
     * 核销验证
     */
    public function orderVerificat(){
        $m = new M();
        $userId = (int)$m->getUserId();
        $shopId = (int)$m->getShopId($userId);
        $rs = $m->orderVerificat($shopId);
        return json_encode($rs);
    }

    /*
     * 获取订单收货信息
     */
    public function getOrderReceiveInfo(){
        $m = new M();
        $rs = $m->getOrderReceiveInfo();
        $areaM = model('areas');
        $rs['area1'] = $areaM->listQuery(0);
        return json_encode(WSTReturn("", 1,$rs));
    }

    /*
     * 修改订单收货信息
     */
    public function editOrderReceiveInfo(){
        $m = new M();
        $rs = $m->editOrderReceiveInfo();
        return json_encode($rs);
    }

    /*
     * 获取订单物流信息
     */
    public function getOrderExpressInfo(){
        $m = new M();
        $rs = [];
        $rs['list'] = $m->getOrderExpressInfo();
        // 快递公司数据
        $rs['express'] = model('Express')->listQuery();
        return json_encode(WSTReturn("", 1,$rs));
    }

    /*
     * 修改订单物流信息
     */
    public function editOrderExpressInfo(){
        $m = new M();
        $rs = $m->editOrderExpressInfo();
        return json_encode($rs);
    }
}
