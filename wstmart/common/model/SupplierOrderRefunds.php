<?php
namespace wstmart\common\model;
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
 * 退款业务处理类
 */
class SupplierOrderRefunds extends Base{
	/**
	 * 用户申请退款
	 */
	public function refund($uId=0){
		$orderId = (int)input('post.id');
		$reason = (int)input('post.reason');
		$content = input('post.content');
		$money = (float)input('post.money');
		$shopId = (int)session('WST_USER.shopId');
		if($money<0)return WSTReturn(lang('refund_amount_not_negative'));
		$order = Db::name('supplier_orders')->alias('o')->join('__SUPPLIER_ORDER_REFUNDS__ orf','orf.orderId=o.orderId','left')->join('__SUPPLIERS__ s','o.supplierId=s.supplierId','left')
		           ->where([['o.orderStatus','in',[-3,-1]],['o.orderId','=',$orderId],['o.shopId','=',$shopId],['o.isRefund','=',0]])
		           ->field('o.orderId,s.userId,o.supplierId,o.orderStatus,o.orderNo,o.realTotalMoney,o.isPay,o.payType,orf.id refundId')->find();
		$reasonData = WSTDatas('REFUND_TYPE',$reason);
		if(empty($reasonData))return WSTReturn(lang('invalid_refund_reason'));
		if($reason==10000 && $content=='')return WSTReturn(lang('please_input_refund_reason'));
		if(empty($order))return WSTReturn(lang('refund_fail_tips'));
		$allowRequest = false;
		if($order['isPay']==1){
			$allowRequest = true;
		}
		if(!$allowRequest)return WSTReturn(lang('refund_apply_submit_tips'));
		if($money>$order['realTotalMoney'])return WSTReturn(lang('refund_amount_gt_pay_money_tips'));
		//查看退款申请是否已存在
		$orfId = $this->where('orderId',$orderId)->value('id');
		Db::startTrans();
		try{
			$result = false;
			//如果退款单存在就进行编辑
			if($orfId>0){
				$object = $this->get($orfId);
				$object->refundReson = $reason;
				if($reason==10000)$object->refundOtherReson = $content;
				$object->backMoney = $money;
				$object->refundStatus = 0;
				$result = $object->save();
			}else{
				$data = [];
				$data['orderId'] = $orderId;
	            $data['refundTo'] = 0;
	            $data['refundReson'] = $reason;
	            if($reason==10000)$data['refundOtherReson'] = $content;
	            $data['backMoney'] = $money;
	            $data['createTime'] = date('Y-m-d H:i:s');
	            $data['refundStatus'] = 0;
	            $result = $this->save($data);
			}
            if(false !== $result){
            	//拒收申请退款的话要给商家发送信息
            		$tpl = WSTMsgTemplates('ORDER_REFUND_CONFER');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	                    $find = ['${ORDER_NO}'];
	                    $replace = [$order['orderNo']];

	                	$msg = array();
			            $msg["supplierId"] = $order['supplierId'];
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
			            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
			            model("common/SupplierMessageQueues")->add($msg);
	                }
            	Db::commit();
                return WSTReturn(lang('refund_apply_submit_tips'),1);
            }
		}catch (\Exception $e) {
		    Db::rollback();
	    }
	    return WSTReturn(lang('operation_fail'),-1);
	}

	/**
	 * 获取订单价格以及申请退款价格
	 */
	public function getRefundMoneyByOrder($orderId = 0){
		return Db::name('supplier_orders')->alias('o')->join('__SUPPLIER_ORDER_REFUNDS__ orf','orf.orderId=o.orderId')->where('orf.id',$orderId)->field('o.orderId,orderNo,goodsMoney,deliverMoney,totalMoney,realTotalMoney,orf.backMoney')->find();
	}

	/**
	 * 商家处理是否同意退款
	 */
	public function supplierrefund($supplierId=0){
        $id = (int)input('id');
		hook('beforeSupplierRefund',['id'=>$id,'supplierId'=>$supplierId]);
        $supplierId = $supplierId > 0 ? $supplierId : (int)session('WST_SUPPLIER.supplierId');
        $refundStatus = (int)input('refundStatus');
        $content = input('content');
        if($id==0)return WSTReturn(lang('invalid_operation'));
        if(!in_array($refundStatus,[1,-1]))return WSTReturn(lang('invalid_operation'));
        if($refundStatus==-1 && $content=='')return WSTReturn(lang('require_reject_reason'));
        Db::startTrans();
        try{
        	$object = $this->get($id);
        	if(empty($object))return WSTReturn(lang('invalid_operation'));
        	$order = Db::name('supplier_orders')->where(['orderId'=>$object->orderId,'supplierId'=>$supplierId])->field('userId,orderNo,orderId')->find();
        	if(empty($order))return WSTReturn(lang('invalid_operation'));
            $object->refundStatus = $refundStatus;
            if($object->refundStatus==-1)$object->supplierRejectReason = $content;
            $result = $object->save();
            if(false !== $result){
            	//如果是拒收话要给用户发信息
            	if($refundStatus==-1){
            		$tpl = WSTMsgTemplates('ORDER_REFUND_FAIL');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	                    $find = ['${ORDER_NO}','${REASON}'];
	                    $replace = [$order['orderNo'],$content];
	                    WSTSendMsg($order['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$order['orderId']]);
	                }
	                //微信消息
					if((int)WSTConf('CONF.wxenabled')==1){
						$reasonData = WSTDatas('REFUND_TYPE',$object->refundReson);
						$params = [];
						$params['ORDER_NO'] = $order['orderNo'];
					    $params['REASON'] = $reasonData['dataName'].(($object->refundReson==10000)?" - ".$object->refundOtherReson:"");
					    $params['SUPPLIER_REASON'] = $object->supplierRejectReason;
						$params['MONEY'] = $object->backMoney;
				        WSTWxMessage(['CODE'=>'WX_ORDER_REFUND_FAIL','userId'=>$order['userId'],'URL'=>'','params'=>$params]);
					}
            	}else{
            		//判断是否需要发送管理员短信
					$tpl = WSTMsgTemplates('PHONE_ADMIN_REFUND_ORDER');
					if((int)WSTConf('CONF.smsOpen')==1 && (int)WSTConf('CONF.smsRefundOrderTip')==1 &&  $tpl['tplContent']!='' && $tpl['status']=='1'){
						$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$order['orderNo']]];
						$staffs = Db::name('staffs')->where([['staffId','in',explode(',',WSTConf('CONF.refundOrderTipUsers'))],['staffStatus','=',1],['dataFlag','=',1]])->field('staffPhone,areaCode')->select();
						for($i=0;$i<count($staffs);$i++){
							if($staffs[$i]['staffPhone']=='')continue;
							$m = new LogSms();
							$rv = $m->sendAdminSMS(0,$staffs[$i]['areaCode'].$staffs[$i]['staffPhone'],$params,'supplierrefund','');
						}
					}
					//微信消息
					if((int)WSTConf('CONF.wxenabled')==1){
						//判断是否需要发送给管理员消息
		                if((int)WSTConf('CONF.wxRefundOrderTip')==1){
		                	$reasonData = WSTDatas('REFUND_TYPE',$object->refundReson);
		                	$params = [];
						    $params['ORDER_NO'] = $order['orderNo'];
					        $params['REASON'] = $reasonData['dataName'].(($object->refundReson==10000)?" - ".$object->refundOtherReson:"");
						    $params['MONEY'] = $object->backMoney;
			            	WSTWxBatchMessage(['CODE'=>'WX_ADMIN_ORDER_REFUND','userType'=>3,'userId'=>explode(',',WSTConf('CONF.refundOrderTipUsers')),'params'=>$params]);
		                }
					}
            	}
            	Db::commit();
            	return WSTReturn(lang('operation_success'),1);
            }
        }catch (\Exception $e) {
		    Db::rollback();
	    }
	    return WSTReturn(lang('operation_fail'),-1);
	}

}
