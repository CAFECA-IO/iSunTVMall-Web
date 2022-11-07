<?php
namespace wstmart\admin\model;
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
 * 订单投诉业务处理
 */
class SupplierOrderComplains extends Base{
	/**
	 * 获取订单投诉列表
	 */
	public function pageQuery(){
		$startDate = input('startDate');
		$endDate = input('endDate');
		$supplierName = Input('supplierName');
     	$orderNo = Input('orderNo');
     	$complainStatus = (int)Input('complainStatus',-1);
     	// 搜素条件
     	$where = [];
     	$areaId1 = (int)input('areaId1');
		if($areaId1>0){
			$where[] = ['s.areaIdPath','like',"$areaId1%"];
			$areaId2 = (int)input("areaId1_".$areaId1);
			if($areaId2>0)$where[] = ['s.areaIdPath','like',$areaId1."_"."$areaId2%"];
			$areaId3 = (int)input("areaId1_".$areaId1."_".$areaId2);
			if($areaId3>0)$where[] = ['s.areaId','=',$areaId3];
		}
		if($startDate!='' && $endDate!=''){
			$where[] = ['oc.complainTime','between',[$startDate.' 00:00:00',$endDate.' 23:59:59']];
		}else if($startDate!=''){
			$where[] = ['oc.complainTime','>=',$startDate.' 00:00:00'];
		}else if($endDate!=''){
			$where[] = ['oc.complainTime','<=',$endDate.' 23:59:59'];
		}


	 	if($complainStatus>-1)$where[] = ['oc.complainStatus','=',$complainStatus];
	 	if($orderNo!='')$where[] = ['o.orderNo','like',"%$orderNo%"];
     	$where[] = ['o.dataFlag','=',1];
     	$sort = input('sort');
		$order = [];
		if($sort!=''){
			$sortArr = explode('.',$sort);
			$order = $sortArr[0].' '.$sortArr[1];
		}
		$rs = Db::name('supplier_orders')->alias('o')
							  ->field('oc.complainId,o.orderId,o.orderNo,o.orderSrc,s.supplierName,u.userName,u.loginName,oc.complainTime,oc.complainStatus,oc.complainType,o.orderCode')
						      ->join('__SUPPLIERS__ s','o.supplierId=s.supplierId','left')
						      ->join('__USERS__ u','o.userId=u.userId','inner')
						      ->join('__SUPPLIER_ORDER_COMPLAINS__ oc','oc.orderId=o.orderId','inner')
						      ->where($where)
						      ->order($order)
						      ->order('complainId desc')
						      ->paginate(input('limit/d'))
						      ->toArray();

	    foreach($rs['data'] as $key => $val){
	    	$reason = WSTDatas('ORDER_COMPLAINT',$val['complainType']);
	    	$rs['data'][$key]['complainName'] = $reason['dataName'];
	    }
		if(count($rs['data'])>0){
			foreach ($rs['data'] as $key => $v){
	    	 	$rs['data'][$key]['orderCodeTitle'] = WSTOrderModule($v['orderCode']);
	    	}
		}
		
		return $rs;
	}

	/**
	 * 获取订单详细信息
	 */
	 public function getDetail(){
	 	$complainId = (int)Input('cid');
	 	$data = $this->alias('oc')
	 				 ->field('oc.*,u.userName,u.loginName')
	 				 ->join('__USERS__ u','oc.complainTargetId=u.userId','inner')
	 				 ->where("oc.complainId=$complainId")
	 				 ->find();
	 	if($data){
	 		if($data['complainAnnex']!='')$data['complainAnnex'] = explode(',',$data['complainAnnex']);
	 		if($data['respondAnnex']!='')$data['respondAnnex'] = explode(',',$data['respondAnnex']);
			$data['userName'] = ($data['userName']=='')?$data['loginName']:$data['userName'];
		 	$rs = Db::name('supplier_orders')->alias('o')
		 					  ->field('o.orderStatus,o.areaId,o.userAddress,o.orderNo,o.userName,s.supplierName,o.userAddress')
		 					  ->join('__SUPPLIERS__ s','o.supplierId=s.supplierId','left')
		 					  ->where(['o.dataFlag'=>1,
		 					  		   'o.orderId'=>$data['orderId']])
		 					  ->find();
			//获取日志信息
			$rs['log'] = Db::name('supplier_log_orders')->alias('lo')
										  ->field('lo.*,u.loginName,u.userType,s.supplierName')
									      ->join('__USERS__ u','lo.logUserId=u.userId','left')
									      ->join('__SUPPLIERS__ s','u.userType=1 and s.userId=u.userId','left')
									      ->where(['orderId'=>$data['orderId']])
									      ->select();
			//获取相关商品
			$rs['goodslist'] = Db::name('supplier_order_goods')->where(['orderId'=>$data['orderId']])->select();
			$data['order'] = $rs;
	 	}
		return $data;
	 }

	 /**
	  * 转交给应诉人应诉
	  */
	 public function deliverRespond(){
	 	$id = (int)Input('id');
	 	if($id==0){
	 		return WSTReturn(lang('orderComplains_err2'),-1);
	 	}
	 	//判断是否已经处理过了
	 	$rs = $this->alias('oc')
	 			   ->field('oc.complainStatus,oc.respondTargetId,o.orderNo,s.userId,o.supplierId')
	 			   ->join('supplier_orders o','oc.orderId=o.orderId','inner')
	 			   ->join('__SUPPLIERS__ s','o.supplierId = s.supplierId','left')
	 			   ->where("complainId=$id")
	 			   ->find();
	 	if($rs['complainStatus']==0){
	 		$data = array();
	 		$data['needRespond'] = 1;
	 		$data['complainStatus'] = 1;
	 		$data['deliverRespondTime'] = date('Y-m-d H:i:s');
	 		Db::startTrans();
		    try{
		 	    $ers = $this->where('complainId='.$id)->update($data);
		 	    if($ers!==false){
			 	    //发站内信息提醒
					$tpl = WSTMsgTemplates('ORDER_NEW_COMPLAIN');
			        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
			            $find = ['${ORDER_NO}'];
			            $replace = [$rs['orderNo']];
			            $msg = array();
			            $msg["supplierId"] = $rs["supplierId"];
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']) ;
			            $msg["msgJson"] = ['from'=>3,'dataId'=>$id];
			            model("common/SupplierMessageQueues")->add($msg);
			        }
					Db::commit();
					return WSTReturn(lang('op_ok'),1);
		 	    }
		    }catch (\Exception $e) {
	            Db::rollback();
	            return WSTReturn(lang('op_err'),-1);
	        }
	 	}else{
	 	    return WSTReturn(lang('orderComplains_err1'),-1);
	 	}
	 	return $rd;
	 }

	 /**
	  * 仲裁
	  */
	 public function finalHandle(){
	 	$rd = array('status'=>-1,'msg'=>lang('orderComplains_err2'));
	 	$complainId = (int)Input('cid');
	 	if($complainId==0){
	 		return WSTReturn(lang('orderComplains_err2'),-1);
	 	}
	 	//判断是否已经处理过了
	 	$rs = $this->alias('oc')
	 			   ->field('oc.complainStatus,s.userId supplierUserId,o.supplierId,o.userId,o.orderNo,o.orderId,oc.needRespond')
	 			   ->join('__SUPPLIER_ORDERS__ o','oc.orderId=o.orderId','inner')
	 			   ->join('__SUPPLIERS__ s','o.supplierId=s.supplierId','left')
	 			   ->where("complainId=$complainId")
	 			   ->find();
	 	if($rs['complainStatus']!=4){
	 		$data = array();
	 		$data['finalHandleStaffId'] = session('WST_STAFF.staffId');
	 		$data['complainStatus'] = 4;
	 		$data['finalResult'] = Input('finalResult');
	 		$data['finalResultTime'] = date('Y-m-d H:i:s');
	 		Db::startTrans();
		    try{
	 	        $ers = $this->where('complainId='.$complainId)->update($data);
	 	        if($ers!==false){
	 	        	//需要卖家回应的话则给卖家也一条消息
		 	    	
	 	    		
		 	    	$tpl = WSTMsgTemplates('ORDER_HANDLED_COMPLAIN');
					if( $tpl['tplContent']!='' && $tpl['status']=='1'){
						//发站内商家信息提醒
				     	$find = ['${ORDER_NO}'];
				        $replace = [$rs['orderNo']];
				        $content = str_replace($find,$replace,$tpl['tplContent']) ;
				     	if($rs['needRespond']==1){
				            $msg = array();
				            $msg["supplierId"] = $rs["supplierId"];
				            $msg["tplCode"] = $tpl["tplCode"];
				            $msg["msgType"] = 1;
				            $msg["content"] = $content ;
				            $msg["msgJson"] = ['from'=>3,'dataId'=>$complainId];
				            model("common/SupplierMessageQueues")->add($msg);
				        }
				        //发站内用户信息提醒
		 	    		WSTSendMsg($rs['userId'],$content,['from'=>3,'dataId'=>$complainId]);   
		 	    	}
					             
					Db::commit();
					return WSTReturn(lang('op_ok'),1);
	 	        }
	 	    }catch(\Exception $e){
	 	    	Db::rollback();
	            return WSTReturn(lang('op_err'),-1);
	 	    }
	 	}else{
	 	    return WSTReturn(lang('orderComplains_err1'),-1);
	 	}

	 }
}
