<?php
namespace wstmart\supplier\model;
use think\Db;
use Env;
use think\Loader;
use wstmart\supplier\model\SupplierOrderRefunds as M;
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
 * 订单业务处理类
 */
class SupplierOrders extends Base{
	protected $pk = 'orderId';


	/**
	 * 获取供货商订单
	 */
	public function supplierOrdersByPage($orderStatus, $sId=0){
		$orderNo = input('post.orderNo');
		$supplierName = input('post.supplierName');
		$payType = (int)input('post.payType');
		$deliverType = (int)input('post.deliverType');
		// 未退款订单
		$refund = (int)input('post.refund');
        $isRefund = (int)input('post.isRefund');

		$supplierId = ($sId==0)?(int)session('WST_SUPPLIER.supplierId'):$sId;

		$where = ['supplierId'=>$supplierId,'dataFlag'=>1];
        $condition = [];
		if(is_array($orderStatus)){
			$condition[] = ['orderStatus','in',$orderStatus];
		}else{
			$where['orderStatus'] = $orderStatus;
		}
		if($orderNo!=''){
			$condition[] = ['orderNo','like',"%$orderNo%"];
		}
		if($supplierName!=''){
			$condition[] = ['supplierName','like',"%$supplierName%"];
		}
		if($payType > -1){
			$where['payType'] =  $payType;
		}
		if($deliverType > -1){
			$where['deliverType'] =  $deliverType;
		}
		if($refund > 0){
            $condition[] =  ['orf.id','gt',0];
            $condition[] =  ['orf.refundStatus','in',[0,1,2]];
            if($isRefund>-1){
                $condition[] =  ['o.isRefund','=',$isRefund];
            }
		}

		$page = $this->alias('o')->where($where)->where($condition);
        if($refund > 0){
            $page = $page->join('__SUPPLIER_ORDER_REFUNDS__ orf','orf.orderId=o.orderId','left');
        }else{
            $page = $page->join('__SUPPLIER_ORDER_REFUNDS__ orf','orf.orderId=o.orderId and refundStatus=0','left');
        }
		$page = $page->field('o.orderRemarks,o.noticeDeliver,o.orderId,orderNo,goodsMoney,totalMoney,realTotalMoney,orderStatus,deliverType,deliverMoney,isAppraise,isRefund,o.deliverType deliverTypes
		              ,payType,payFrom,userAddress,orderStatus,isPay,isAppraise,userName,orderSrc,o.createTime,orf.id refundId,orf.refundStatus,o.orderCode')
			  ->order('o.createTime', 'desc')
			  ->paginate()->toArray();
	    if(count($page['data'])>0){
	    	 $orderIds = [];
	    	 foreach ($page['data'] as $v){
	    	 	 $orderIds[] = $v['orderId'];
	    	 }
	    	 $goods = Db::name('supplier_order_goods')->where([['orderId','in',$orderIds]])->select();
	    	 hook('afterSupplierOrdersPageGoods',['goods'=>&$goods]);
	    	 $goodsMap = [];
	    	 foreach ($goods as $v){
	    	 	$v['goodsName'] = WSTStripTags($v['goodsName']);
	    	 	$v['goodsSpecNames'] = str_replace('@@_@@','、',$v['goodsSpecNames']);
	    	 	$goodsMap[$v['orderId']][] = $v;
	    	 }
	    	 foreach ($page['data'] as $key => $v){
	    	 	 $page['data'][$key]['orderCodeTitle'] = WSTOrderModule($v['orderCode']);
	    	 	 $page['data'][$key]['list'] = $goodsMap[$v['orderId']];
	    	 	 $page['data'][$key]['payTypeName'] = WSTLangPayType($v['payType']);
	    	 	 $page['data'][$key]['deliverTypeName'] = WSTLangDeliverType($v['deliverType']==1);
	    	 	 $page['data'][$key]['deliverType'] = $v['deliverType'];
	    	 	 $page['data'][$key]['status'] = WSTLangOrderStatus($v['orderStatus']);
	    	 }
	    }
	    return $page;
	}
	/**
	 * 供货商发货
	 */
	public function deliver($uId=0, $sId=0){
		$orderId = (int)input('post.id');
		hook('beforeSupplierDeliver',['uId'=>$uId,'sId'=>$sId,'orderId'=>$orderId]);
		$expressId = (int)input('post.expressId');
		$expressNo = ($expressId>0)?input('post.expressNo'):'';
        $selectOrderGoodsIds = WSTFormatIn(',',input('post.selectOrderGoodsIds'));
        $supplierId = ($sId==0)?(int)session('WST_SUPPLIER.supplierId'):$sId;
        $userId = ($uId==0)?(int)session('WST_SUPPLIER.userId'):$uId;
        $deliverType = (int)input('post.deliverType');
        $order = $this->where(['supplierId'=>$supplierId,'orderId'=>$orderId,'orderStatus'=>0])->field('orderId,orderNo,userId,deliverType')->find();
        if(empty($order))return WSTReturn(lang("please_check_if_the_order_status_has_changed"));
        // 前台传过来的deliverType,deliverType==1需要物流，deliverType=0无需物流
        $deliverType = ($deliverType==1)?1:0;
        if($deliverType==1 && $expressId>0 && $expressNo=='')return WSTReturn(lang("require_express_info"));
        $finishDeliver = false;
        if($order['deliverType'] == 1){//客户自提
            $finishDeliver = true;
        }else{//供货商发货
            //验证前台传过来的数据
	        $selectOrderGoodsIds = Db::name('supplier_order_goods')->where([['orderId','=',$orderId],['id','in',$selectOrderGoodsIds]])->column('id');
	        if(count($selectOrderGoodsIds)==0)return WSTReturn(lang("require_order_deliver_goods"));
	        //该订单在订单商品表里所有的id,保存在orderGoodsIds变量中
	        $orderGoodsIds = Db::name('supplier_order_goods')->field('id')->where([['orderId','=',$orderId]])->column('id');
	        //首先去supplier_order_express表查询已发货的orderGoodsId
	        $orderExpressGoodsIds = Db::name('supplier_order_express')->field('orderGoodsId')->where('orderId','=',$orderId)->select();
	        $deliveredGoodsIds = [];
            // 如果可以查询到发货数据,则从剔除已发货的数据
	        if(!empty($orderExpressGoodsIds)){
	            $deliveredGoodsIds = [];
	            foreach($orderExpressGoodsIds as $k => $v){
	                $temp = explode(',',$v['orderGoodsId']);
	                $deliveredGoodsIds = array_merge($deliveredGoodsIds,$temp);
	            }
	            //将已发货的orderGoodsId从$orderGoodsIds中剔除掉--剩下orderGoodsId中待发货的
		        foreach($orderGoodsIds as $k => $v){
		            if(in_array($v,$deliveredGoodsIds)){
		                unset($orderGoodsIds[$k]);
		            }
		        }
            }
	        //将已发货的orderGoodsId从$selectOrderGoodsIds中删除--剩下合法的要发货的
	        foreach($selectOrderGoodsIds as $k => $v){
	            if(in_array($v,$deliveredGoodsIds)){
	                unset($selectOrderGoodsIds[$k]);
	            }
	        }
	        //没有需要发货的商品
	        if(count($selectOrderGoodsIds)==0)return WSTReturn(lang("there_are_no_goods_to_ship"));
	        //将要发货的selectOrderGoodsIds从orderGoodsId中删除--剩下真是要发货的
	        foreach($orderGoodsIds as $k => $v){
	            if(in_array($v,$selectOrderGoodsIds)){
	                 unset($orderGoodsIds[$k]);
	            }
	        }
	        //没有待发货的商品则订单完成
	        if(count($orderGoodsIds) == 0)$finishDeliver = true;
        }
		Db::startTrans();
		try{
            //只要需要供货商发货的都插入订单物流表里
            if($order['deliverType'] == 0 && count($selectOrderGoodsIds)>0){
                $expressData = [
                    'orderId'=>$orderId,
                    'orderGoodsId'=>implode(',',$selectOrderGoodsIds),
                    'isExpress'=>$deliverType,
                    'expressId'=>$expressId,
                    'expressNo'=>$expressNo,
                    'createTime'=>date('Y-m-d H:i:s'),
                    'deliveryTime'=>date('Y-m-d H:i:s')
                ];
                $orderExpressResult = Db::name('supplier_order_express')->insert($expressData);
            }
            $orderStatus = 0;
            if($finishDeliver){
            	$orderStatus = 1;
                $orderData = ['orderStatus'=>1,'deliveryTime'=>date('Y-m-d H:i:s')];
                $result = $this->where([['orderId','=',$order['orderId']],['supplierId','=',$supplierId]])->update($orderData);
            }
		    //新增订单日志
			$logOrder = [];
			$logOrder['orderId'] = $orderId;
			$logOrder['orderStatus'] = $orderStatus;
			$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>"the_supplier_has_delivered_the_goods"]);
			$logOrder['logUserId'] = $userId;
			$logOrder['logType'] = 0;
			$logOrder['logTime'] = date('Y-m-d H:i:s');
			Db::name('supplier_log_orders')->insert($logOrder);
			//发送一条用户信息
			$tpl = WSTMsgTemplates('ORDER_DELIVERY');
	        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	            $find = ['${ORDER_NO}','${EXPRESS_NO}'];
	            $replace = [$order['orderNo'],($expressNo=='')?lang("none"):$expressNo];
	            WSTSendMsg($order['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$orderId]);
	        }
	        //微信消息
	        if(WSTDatas('ADS_TYPE',3)!='' || WSTDatas('ADS_TYPE',4)!=''){
		        $params = [];
		        if($expressId>0){
		            $express = Db::name('express')
                            ->alias('e')
                            ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                            ->where(['e.expressId'=>$expressId])->find();
		            $params['EXPRESS'] = $express['expressName'];
		            $params['EXPRESS_NO'] = $expressNo;
		        }else{
		            $params['EXPRESS'] = lang("none");
		            $params['EXPRESS_NO'] = lang("none");
		        }
		        $params['ORDER_NO'] = $order['orderNo'];
		        if(WSTConf('CONF.wxenabled')==1){
			        WSTWxMessage(['CODE'=>'WX_ORDER_DELIVERY','userId'=>$order['userId'],'params'=>$params]);
			    }
			}
			Db::commit();
			return WSTReturn(lang("op_ok"),1);
		}catch (\Exception $e) {
	        Db::rollback();
	       return WSTReturn(lang("op_err"),-1);
		}
		return WSTReturn(lang("please_check_if_the_order_status_has_changed"));
	}

	/**
	 * 获取订单价格
	 */
	public function getMoneyByOrder($orderId = 0){
		$orderId = ($orderId>0)?$orderId:(int)input('post.id');
		return $this->where('orderId',$orderId)->field('orderId,orderNo,goodsMoney,deliverMoney,totalMoney,realTotalMoney')->find();
	}


	/**
	 * 修改订单价格
	 */
	public function editOrderMoney($uId=0, $sId=0){
		$orderId = input('post.id');
		$orderMoney = (float)input('post.orderMoney');
		$userId = ($uId==0)?(int)session('WST_SUPPLIER.userId'):$uId;
		$supplierId = ($sId==0)?(int)session('WST_SUPPLIER.supplierId'):$sId;
		if($orderMoney<0.01)return WSTReturn(lang("order_price_must_be_greater_than_0.01"));
		Db::startTrans();
		try{
			//修改订单价格前执行勾子
			hook('beforeSupplierEditOrderMoney',['orderId'=>$orderId,"orderMoney"=>$orderMoney]);

			$data = array();
			$data["realTotalMoney"] = $orderMoney;
			$data["needPay"] = $orderMoney;
			$data["payRand"] = Db::raw('payRand+1');
			$result = $this->where(['orderId'=>$orderId,'supplierId'=>$supplierId,'orderStatus'=>-2])->update($data);

			if(false !== $result){
				//新增订单日志
				$logOrder = [];
				$logOrder['orderId'] = $orderId;
				$logOrder['orderStatus'] = -2;
				$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>"supplier_modify_order_price",'remark'=>$orderMoney]);
				$logOrder['logUserId'] = $userId;
				$logOrder['logType'] = 0;
				$logOrder['logTime'] = date('Y-m-d H:i:s');
				Db::name('supplier_log_orders')->insert($logOrder);
				Db::commit();
				return WSTReturn(lang("op_ok"),1);
			}
		}catch (\Exception $e) {
		    Db::rollback();
	        return WSTReturn(lang("op_err"),-1);
	    }
	}

	/**
	 * 获取订单详情
	 */
	public function getByView($orderId, $uId=0){
		$supplierId = ($uId==0)?(int)session('WST_SUPPLIER.supplierId'):$uId;
		$orders = Db::name('supplier_orders')->alias('o')
		               ->join('__SUPPLIERS__ s','o.supplierId=s.supplierId','left')
		               ->join('__SUPPLIER_ORDER_COMPLAINS__ oc','oc.orderId=o.orderId','left')
		               ->join('__SUPPLIER_ORDER_REFUNDS__ orf ','o.orderId=orf.orderId','left')
		               ->where('o.dataFlag=1 and o.orderId='.$orderId.' and ( o.supplierId='.$supplierId.')')
		               ->field('o.*,s.areaId supplierAreaId,s.supplierAddress,s.supplierTel,s.supplierName,s.supplierQQ,s.supplierWangWang,orf.id refundId,orf.refundRemark,orf.refundStatus,orf.refundTime,orf.backMoney,orf.backMoney,oc.complainId')->find();
		if(empty($orders))return WSTReturn(lang("invalid_order_information"));
		// 获取店铺地址
		$orders['supplierAddr'] = model('common/areas')->getParentNames($orders['supplierAreaId']);
		$orders['supplierAddress'] = implode('',$orders['supplierAddr']).$orders['supplierAddress'];
		unset($orders['supplierAddr']);
		//下单用户
		$orderUser = Db::name("users")->where(["userId"=>$orders['userId']])->field("userId,userName,loginName")->find();
		$orders['orderUser'] = $orderUser;
		//获取订单信息
		$log = Db::name('supplier_log_orders')->where('orderId',$orderId)->order('logId asc')->select();
		$orders['log'] = [];
		$logFilter = [];
		foreach ($log as $key => $v) {
			if(in_array($orders['orderStatus'],[-2,0,1,2]) && in_array($v['orderStatus'],$logFilter))continue;
			$orders['log'][] = $v;
			$logFilter[] = $v['orderStatus'];
		}
		//获取订单商品
		$orders['goods'] = Db::name('supplier_order_goods')->alias('og')->join('supplier_goods g','g.goodsId=og.goodsId','left')->where('orderId',$orderId)->field('og.*,g.goodsSn')->order('id asc')->select();
		hook('afterSupplierOrdersPageGoods',['goods'=>&$orders['goods']]);

		foreach ($orders['goods'] as $key => $v) {
		 	$orders['goods'][$key]['goodsName'] = WSTStripTags($v['goodsName']);
			//如果是虚拟商品
			if($orders['orderType']==1){
				$orders['goods'][$key]['extraJson'] = json_decode($v['extraJson'],true);
			}
			$shotGoodsSpecNames = [];
		 	if($v['goodsSpecNames']!=""){
		 		$v['goodsSpecNames'] = str_replace('：',':',$v['goodsSpecNames']);
		 		$goodsSpecNames = explode('@@_@@',$v['goodsSpecNames']);

	    	 	foreach ($goodsSpecNames as $key2 => $spec) {
	    	 	 	$obj = explode(":",$spec);
	    	 	 	$shotGoodsSpecNames[] = $obj[1];
	    	 	}
		 	}
		 	$orders['goods'][$key]['shotGoodsSpecNames'] = implode('，',$shotGoodsSpecNames);
		}

        // 发货时间与快递单号
        $orderExpressNos = Db::name('supplier_order_express')->where([['orderId','=',$orderId],['isExpress','=',1]])->column("expressNo");
        if($orderExpressNos){
            // 多张快递单号用逗号拼接，并过滤掉没有单号的
            $orders["expressNo"] = implode(",",array_filter($orderExpressNos));
        }else{
            $orders["expressNo"] = '';
        }
        //格式化发票信息
		if($orders['isInvoice']==1){
			$orders['invoice'] = json_decode($orders['invoiceJson'],true);
		}
		$orders['isComplain'] = 1;
		if(($orders['complainId']=='') && ($orders['payType']==0 || ($orders['payType']==1 && $orders['orderStatus']!=-2))){
			$orders['isComplain'] = '';
		}

		$orders['allowRefund'] = 0;
	 	//只要是已支付的，并且没有退款的，都可以申请退款操作
	 	if($orders['payType']==1 && $orders['isRefund']==0 && $orders['refundId']=='' && ($orders['isPay'] ==1)){
              $orders['allowRefund'] = 1;
	 	}
	 	//货到付款中使用了积分支付的也可以申请退款
	 	if($orders['payType']==0 && $orders['refundId']=='' && $orders['isRefund']==0){
              $orders['allowRefund'] = 1;
	 	}
		// 是否可申请售后
		$orders['canAfterSale'] = false;
		// 订单已确认收货
		if($orders['payType']==1 && $orders['orderStatus']==2){
			// 判断是否已超过售后服务有效期
			// 如果 当前时间>(确认收货时间+售后服务期限) 表示无法继续申请售后
			$now = time();
			// 售后结束日期
			$endTime = strtotime($orders['afterSaleEndTime']);
			$_rs = ($now<=$endTime);
			$orders['canAfterSale'] = $_rs;
			if($_rs){
				// 判断订单是否还能继续申请售后 【订单商品总数-售后单商品总数>0】
				$ogNum = Db::name('supplier_order_goods')
						 ->where(['orderId'=>$orderId])
						 ->value('sum(goodsNum) ogNum');
				$osNum = Db::name('supplier_order_services')->alias('os')
													 ->join('orders o','o.orderId=os.orderId','inner')
													 ->join('service_goods sg','sg.serviceId=os.id')
													 ->where(['o.orderId'=>$orderId,'os.isClose'=>0])
													 ->value('sum(sg.goodsNum) osNum');
				$orders['canAfterSale'] = ($ogNum>$osNum);
			}
		}

		return $orders;
	}



    /**
     * 根据订单id,订单商品id获取单个商品信息
     */
    public function getOrderInfoByGoodsId($uId=0){
        $orderId = (int)input('oId');
        $orderGoodsId = (int)input('orderGoodsId');
        $userId = ($uId==0)?(int)session('WST_SUPPLIER.userId'):$uId;

        $goodsInfo = Db::name('supplier_order_goods')
            ->field('id,orderId,goodsName,goodsId,goodsSpecNames,goodsImg,goodsSpecId,goodsCode')
            ->where(['orderId'=>$orderId,'id'=>$orderGoodsId])
            ->find();
        if($goodsInfo){
            $goodsInfo['goodsSpecNames'] = str_replace('@@_@@', ';', $goodsInfo['goodsSpecNames']);
        }
        return $goodsInfo;
    }

	/**
	 * 导出订单
	 */
	public function toExport(){
		$name='order';
        $where = [];
        $where[] = ['o.dataFlag','=',1];
		$orderStatus = (int)input('orderStatus',0);
		if($orderStatus==0){
			$name='PendingDelOrder';
		}else if($orderStatus==-2){
			$name='PendingPayorder';
		}else if($orderStatus==1){
			$name='DistributionOrder';
		}else if($orderStatus==-1){
			$name='CancelOrder';
		}else if($orderStatus==-3){
			$name='RejectionOrder';
		}else if($orderStatus==2){
			$name='ReceivedOrder';
		}else if($orderStatus==10000){
			$name='CancelOrder/RejectionOrder';
		}else if($orderStatus==20000){
			$name='PendingRecOrder';
		}else if($orderStatus==30000){
            $name='RefundOrder';
        }
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$where[] = ['o.supplierId','=',$supplierId];
		$orderNo = input('orderNo');
		$supplierName = input('supplierName');

		$type = (int)input('type',-1);
		$payType = $type>0?$type:(int)input('payType',-1);
		$deliverType = (int)input('deliverType');
		if($orderStatus == 10000)$orderStatus = [-1,-3];
		if($orderStatus == 20000)$orderStatus = [0,1];
        if($orderStatus == 30000){
            $orderStatus = [-1,-3];
            $where[] =  ['orf.refundStatus','in',[0,1,2]];
            $isRefund = (int)input('isRefund');
            if($isRefund>-1){
                $where[] =  ['o.isRefund','=',$isRefund];
            }
        }
		if(is_array($orderStatus)){
			$where[] = ['o.orderStatus','in',$orderStatus];
		}else{
			$where[] = ['o.orderStatus','=',$orderStatus];
		}
		if($orderNo!=''){
			$where[] = ['orderNo','like',"%$orderNo%"];
		}
		if($supplierName!=''){
			$where[] = ['supplierName','like',"%$supplierName%"];
		}
		if($payType > -1){
			$where[] =  ['payType','=',$payType];
		}
		if($deliverType > -1){
			$where[] =  ['deliverType','=',$deliverType];
		}
		$page = $this->alias('o')->where($where)->join('__SUPPLIERS__ s','o.supplierId=s.supplierId','left');
        if($name=='RefundOrder'){
            $page = $page->join('__SUPPLIER_ORDER_REFUNDS__ orf','orf.orderId=o.orderId','left');
        }else{
            $page = $page->join('__SUPPLIER_ORDER_REFUNDS__ orf','orf.orderId=o.orderId and refundStatus=0','left');
        }
		$page = $page->join('supplier_log_orders lo','lo.orderId=o.orderId and lo.orderStatus in (-1,-3) ','left')
		->field('o.orderId,orderNo,goodsMoney,totalMoney,realTotalMoney,o.orderStatus,deliverType,deliverMoney,isAppraise,o.deliverMoney,lo.logJson,o.payTime,o.payFrom
		,o.invoiceJson,o.isMakeInvoice,o.isInvoice,o.isRefund,payType,o.userName,o.userAddress,o.userPhone,o.orderRemarks,o.invoiceClient,o.receiveTime,o.deliveryTime,orderSrc,o.createTime,orf.id refundId,s.areaId supplierAreaId,s.supplierAddress')
		->order('o.createTime', 'desc')
		->select();
        $name = $name.date('Ymd');
		if(count($page)>0){
			foreach ($page as $v){
				$orderIds[] = $v['orderId'];
			}
			$goods = Db::name('supplier_order_goods')->where([['orderId','in',$orderIds]])->select();
			$goodsMap = [];
			foreach ($goods as $v){
				$v['goodsSpecNames'] = str_replace('@@_@@','、',$v['goodsSpecNames']);
				$goodsMap[$v['orderId']][] = $v;
			}
			foreach ($page as $key => $v){
				$page[$key]['invoiceArr'] = '';
				if($v['isInvoice']==1){
					$invoiceArr = json_decode($v['invoiceJson'],true);
					$page[$key]['invoiceArr'] = " ".$invoiceArr['invoiceHead'];
					if(isset($invoiceArr['invoiceCode'])){
						$page[$key]['invoiceArr'] = " ".$invoiceArr['invoiceHead'].'|'.$invoiceArr['invoiceCode'];
					}
				}
				$page[$key]['supplierAddr'] = model('common/areas')->getParentNames($v['supplierAreaId']);
		        $page[$key]['supplierAddress'] = implode('',$v['supplierAddr']).$v['supplierAddress'];
		        if($page[$key]['deliverType']==1)$page[$key]['userAddress'] = $page[$key]['supplierAddress'];
				$page[$key]['payTypeName'] = WSTLangPayType($v['payType']);
				$page[$key]['deliverType'] = WSTLangDeliverType($v['deliverType']==1);
				$page[$key]['status'] = WSTLangOrderStatus($v['orderStatus']);
				$page[$key]['goods'] = $goodsMap[$v['orderId']];
                $page[$key]['isMakeInvoice'] = ($v['isMakeInvoice']==1)?lang("opened"):lang("not_yet_opened");
			}
		}
		require Env::get('root_path') . 'extend/phpexcel/PHPExcel.php';

		$objPHPExcel = new \PHPExcel();
		// 设置excel文档的属性
		$objPHPExcel->getProperties()->setCreator("WSTMart")//创建人
		->setLastModifiedBy("WSTMart")//最后修改人
		->setTitle($name)//标题
		->setSubject($name)//题目
		->setDescription($name)//描述
		->setKeywords(lang("orders"))//关键字
		->setCategory("Test result file");//种类

		// 开始操作excel表
		$objPHPExcel->setActiveSheetIndex(0);
		// 设置工作薄名称
		$objPHPExcel->getActiveSheet()->setTitle(iconv('gbk', 'utf-8', 'Sheet'));
		// 设置默认字体和大小
		$objPHPExcel->getDefaultStyle()->getFont()->setName(iconv('gbk', 'utf-8', ''));
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
		$styleArray = array(
				'font' => array(
						'bold' => true,
						'color'=>array(
								'argb' => 'ffffffff',
						)
				)
		);

		//设置宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
		$objRow = $objPHPExcel->getActiveSheet()->getStyle('A1:W1');
		$objRow->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
		$objRow->getFill()->getStartColor()->setRGB('666699');
		$objRow->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objRow->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(25);

		$objPHPExcel->getActiveSheet()->setCellValue('A1', lang("label_order_no"))->setCellValue('B1', lang("label_order_status"))->setCellValue('C1', lang("receive_name"))->setCellValue('D1', lang("receive_addr"))->setCellValue('E1', lang("label_order_contact"))
		->setCellValue('F1', lang("label_pay_type"))->setCellValue('G1', lang("source_payment"))->setCellValue('H1', lang("label_deliver_type"))->setCellValue('I1', lang("label_invoice_status"))->setCellValue('W1', lang("buyer_message"))->setCellValue('J1', lang("order_invoice_info"))
		->setCellValue('K1', lang("label_order_goods"))->setCellValue('L1', lang("label_order_goods_price"))->setCellValue('M1', lang("num"))->setCellValue('N1', lang("label_total_money"))->setCellValue('O1', lang("label_deliver_money"))->setCellValue('P1', lang("label_real_total_money"))
		->setCellValue('Q1', lang("label_pay_time"))->setCellValue('R1', lang("payment_time"))->setCellValue('S1', lang("label_order_delivery_time"))->setCellValue('T1', lang("receiving_time"))->setCellValue('U1', lang("reasons_for_cancellation/rejection"))->setCellValue('V1', lang("refund_or_not"));
		$objPHPExcel->getActiveSheet()->getStyle('A1:W1')->applyFromArray($styleArray);
		$i = 1;
		$totalRow = 0;
		for ($row = 0; $row < count($page); $row++){
			$goodsn = count($page[$row]['goods']);
			$i = $i+1;
			$i2 = $i3 = $i;
			$i = $i+(1*$goodsn)-1;
			$objPHPExcel->getActiveSheet()->mergeCells('A'.$i2.':A'.$i)->mergeCells('B'.$i2.':B'.$i)->mergeCells('C'.$i2.':C'.$i)->mergeCells('D'.$i2.':D'.$i)->mergeCells('E'.$i2.':E'.$i)->mergeCells('F'.$i2.':F'.$i)
			->mergeCells('G'.$i2.':G'.$i)->mergeCells('H'.$i2.':H'.$i)->mergeCells('I'.$i2.':I'.$i)->mergeCells('J'.$i2.':J'.$i)->mergeCells('N'.$i2.':N'.$i)->mergeCells('O'.$i2.':O'.$i)
			->mergeCells('P'.$i2.':P'.$i)->mergeCells('Q'.$i2.':Q'.$i)->mergeCells('R'.$i2.':R'.$i)->mergeCells('S'.$i2.':S'.$i)->mergeCells('T'.$i2.':T'.$i)->mergeCells('U'.$i2.':U'.$i)->mergeCells('V'.$i2.':V'.$i)->mergeCells('W'.$i2.':W'.$i);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i2, $page[$row]['orderNo'])->setCellValue('B'.$i2, $page[$row]['status'])->setCellValue('C'.$i2, $page[$row]['userName'])->setCellValue('D'.$i2, $page[$row]['userAddress'])
			->setCellValue('E'.$i2, $page[$row]['userPhone'])->setCellValue('F'.$i2, $page[$row]['payTypeName'])->setCellValue('G'.$i2, ($page[$row]['payFrom'])?WSTLangPayFrom($page[$row]['payFrom']):'')->setCellValue('H'.$i2, $page[$row]['deliverType'])
			->setCellValue('I'.$i2, $page[$row]['isMakeInvoice'])->setCellValue('W'.$i2, $page[$row]['orderRemarks'])->setCellValue('J'.$i2, $page[$row]['invoiceArr'])->setCellValue('N'.$i2, $page[$row]['totalMoney'])->setCellValue('O'.$i2, $page[$row]['deliverMoney'])->setCellValue('P'.$i2, $page[$row]['realTotalMoney'])
			->setCellValue('Q'.$i2, $page[$row]['createTime'])->setCellValue('R'.$i2, $page[$row]['payTime'])->setCellValue('S'.$i2, $page[$row]['deliveryTime'])->setCellValue('T'.$i2, $page[$row]['receiveTime'])
			->setCellValue('U'.$i2, WSTLogJson($page[$row]['logJson']))->setCellValue('V'.$i2, ($page[$row]['isRefund']==1)?lang("yes"):'');
			$objPHPExcel->getActiveSheet()->getStyle('D'.$i2)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$objPHPExcel->getActiveSheet()->getStyle('U'.$i2)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			for ($row2 = 0; $row2 < $goodsn; $row2++){
				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i3, (($page[$row]['goods'][$row2]['goodsCode']=='gift')?'【'.lang("gift").'】':'').$page[$row]['goods'][$row2]['goodsName'].(($page[$row]['goods'][$row2]['goodsSpecNames']!='')?'【'.$page[$row]['goods'][$row2]['goodsSpecNames'].'】':''))->setCellValue('L'.$i3, $page[$row]['goods'][$row2]['goodsPrice'])->setCellValue('M'.$i3, $page[$row]['goods'][$row2]['goodsNum']);
				$objPHPExcel->getActiveSheet()->getStyle('K'.$i3)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$i3 = $i3 + 1;
			}
			$totalRow = $i3;
		}
	    $totalRow = ($totalRow==0)?1:$totalRow-1;
	    $objPHPExcel->getActiveSheet()->getStyle('A1:W'.$totalRow)->applyFromArray(array(
				'borders' => array (
						'allborders' => array (
								'style' => \PHPExcel_Style_Border::BORDER_THIN,  //设置border样式
								'color' => array ('argb' => 'FF000000'),     //设置border颜色
						)
				)
		));
		$this->PHPExcelWriter($objPHPExcel,$name);
	}


    /**
     * 获取单条订单的商品信息
     */
    public function waitDeliverById(){
        $orderId = (int)input('id');
        $goods = Db::name('supplier_order_goods')->where('orderId','=',$orderId)->select();
        $order = Db::name('supplier_orders')->field('deliverType,userAddress,userName,userPhone')->where('orderId','=',$orderId)->find();
        $orderExpressGoodsIds = Db::name('supplier_order_express')->field('orderGoodsId')->where(['orderId'=>$orderId])->select();
        $deliveredGoodsIds = [];
        foreach($orderExpressGoodsIds as $k => $v){
            $temp = explode(',',$v['orderGoodsId']);
            $deliveredGoodsIds = array_merge($deliveredGoodsIds,$temp);
        }
        $data = [];
        $data['list'] = [];
        $data['userName'] = $order['userName'];
        $data['userPhone'] = $order['userPhone'];
        $data['userAddress'] = $order['userAddress'];
        $data['deliverType'] = $order['deliverType'];
        if($goods){
            foreach($goods as $k => $v){
                $goods[$k]['hasDeliver'] = (in_array($v['id'],$deliveredGoodsIds))?true:false;
            }
            $data['list'] = $goods;
        }
        return $data;
    }




    /**
	 * 获取待核验订单
	 */
	public function getVerificatOrder($supplierId){
		$verificationCode = input("verificationCode");
		$verificationCode = preg_replace('# #','',$verificationCode);
		$where = [];
		$where[] = ["dataFlag","=",1];
		$where[] = ["deliverType","=",1];
		$where[] = ["supplierId","=",$supplierId];
		$where[] = ["orderStatus",">=",0];
		$where[] = ["verificationCode","=",$verificationCode];
		$order = Db::name('supplier_orders')
		               ->where($where)
		               ->field('orderId,orderNo,userPhone,userName,isPay,goodsMoney,totalMoney,realTotalMoney,isInvoice,invoiceClient,orderRemarks,orderStatus,createTime,verificationTime,isMakeInvoice')
		               ->find();
		if(empty($order)){
			return $order ;
		}
		$orderId = $order['orderId'];
		$order['orderStatusName'] = WSTLangOrderStatus($order['orderStatus']);
		//获取订单商品
		$order['goods'] = Db::name('supplier_order_goods')->alias('og')
							->join('supplier_goods g','g.goodsId=og.goodsId','left')
							->where('orderId',$orderId)
							->field('og.*,g.goodsSn')
							->order('id asc')
							->select();
		return $order;
	}



    /**
	 * 核验订单
	 */
	public function orderVerificat($supplierId){
		$verificationCode = input("verificationCode");
		$verificationCode = preg_replace('# #','',$verificationCode);
		$where = [];
		$where[] = ["dataFlag","=",1];
		$where[] = ["deliverType","=",1];
		$where[] = ["supplierId","=",$supplierId];
		$where[] = ["orderStatus",">=",0];
		$where[] = ["verificationCode","=",$verificationCode];
		$order = Db::name('supplier_orders')
		               ->where($where)
		               ->field('supplierId,orderId,orderNo,userPhone,userName,isPay,goodsMoney,totalMoney,realTotalMoney,isInvoice,invoiceClient,orderRemarks,orderStatus,createTime,verificationTime,userId')
		               ->find();
		if(empty($order)){
			return WSTReturn(lang("invalid_v_code"));
		}else{
			if($order["orderStatus"]>0){
				return WSTReturn(lang("orders_already_take_delivery"));
			}
			$orderId = $order['orderId'];
			$userId = $order['userId'];
			Db::startTrans();
		    try{
		    	$limitDay = (int)WSTConf('CONF.afterSaleServiceDays');
				// 售后结束时间
				$afterSaleEndTime = date('Y-m-d H:i:s', strtotime("+{$limitDay} day"));
				$data = ['deliveryTime'=>date('Y-m-d H:i:s'),'orderStatus'=>2,'receiveTime'=>date('Y-m-d H:i:s'),'verificationTime'=>date('Y-m-d H:i:s'),'afterSaleEndTime'=>$afterSaleEndTime];
			    $result = $this->where('orderId',$orderId)->update($data);
				if(false != $result){
					//确认收货后执行钩子
					$goodss = Db::name('supplier_order_goods')->where('orderId',$orderId)->field('goodsId,goodsNum,goodsSpecId')->select();
					foreach($goodss as $key =>$v){
						Db::name('supplier_goods')->where('goodsId',$v['goodsId'])->update([
                            'saleNum'=>Db::raw('saleNum+'.$v['goodsNum'])
                        ]);
						if($v['goodsSpecId']>0){
							Db::name('supplier_goods_specs')->where('id',$v['goodsSpecId'])->update([
	                            'saleNum'=>Db::raw('saleNum+'.$v['goodsNum'])
	                        ]);
						}
					}

					//修改商家未计算订单数
					$torder = Db::name('supplier_orders')->where("orderId",$orderId)->field("orderId,commissionFee")->find();
					Db::name('suppliers')->where('supplierId',$order['supplierId'])->update([
						'noSettledOrderNum'=>Db::raw('noSettledOrderNum+1'),
						'noSettledOrderFee'=>Db::raw('noSettledOrderFee-'.$torder['commissionFee'])
					]);

					//新增订单日志
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = 1;
					$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>"supplier_already_delivery"]);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('supplier_log_orders')->insert($logOrder);
					//新增订单日志
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = 2;
					$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>"user_already_take_delivery"]);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('supplier_log_orders')->insert($logOrder);

					//发送一条用户信息
					$tpl = WSTMsgTemplates('ORDER_DELIVERY');
			        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
			            $find = ['${ORDER_NO}','${EXPRESS_NO}'];
			            $replace = [$order['orderNo'],lang("none")];
			            WSTSendMsg($order['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$orderId]);
			        }
			        $tpl = WSTMsgTemplates('PHONE_USER_ORDER_VERIFICAT');//用户自提订单核销提醒
			        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
			        	$user = Db::name("users")->where(['userId'=>$userId])->field("userPhone")->find();
			        	$userPhone = $user['userPhone'];
			            $params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$order['orderNo']]];
			            model("common/LogSms")->sendSMS(0,$userPhone,$params,'orderVerificat','',$userId,0);
			        }
					//发送一条商家信息
					$tpl = WSTMsgTemplates('ORDER_RECEIVE');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	                    $find = ['${ORDER_NO}'];
	                    $replace = [$order['orderNo']];

	                	$msg = array();
			            $msg["supplierId"] = $order["supplierId"];
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
			            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
			            model("common/SupplierMessageQueues")->add($msg);
	                }

					Db::commit();
					return WSTReturn(lang("order_verification_code_success"),1);
				}
		    }catch (\Exception $e) {
	            Db::rollback();
	            return WSTReturn(lang("op_err"),-1);
	        }

		}

		return WSTReturn(lang("order_verification_success"),1);
	}

		/**
	 * 导出待发货订单模板
	 */
	public function toDeliverTemplate(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$orderNo = input('orderNo');
		$payType = (int)input('payType',-1);
		$deliverType = (int)input('deliverType',-1);
		$where = [['o.dataFlag','=',1],['o.orderStatus','=',0],['o.supplierId','=',$supplierId]];
		if($orderNo!='')$where[] = ['o.orderNo','like','%'.$orderNo.'%'];
        if($payType!=-1)$where[] = ['o.payType','=',$payType];
        if($deliverType!=-1)$where[] = ['o.deliverType','=',$deliverType];
		//获取待发货的商品订单信息
		$orderGoods = Db::name('supplier_orders')->alias('o')
		                   ->join('__SUPPLIER_ORDER_GOODS__ og','o.orderId=og.orderId','inner')
		                   ->where($where)
		                   ->field('o.orderNo,og.id,og.goodsName,og.goodsSpecNames,og.goodsSpecId')
		                   ->select();
		$waitDeliverGoods = [];
		if(count($orderGoods)>0){
	        //获已发货的订单
	        $deliverGoods = Db::name('supplier_orders')->alias('o')
			                   ->join('__SUPPLIER_ORDER_EXPRESS__ oep','o.orderId=oep.orderId and oep.dataFlag=1','inner')
			                   ->where($where)
			                   ->column('oep.orderGoodsId');
	        foreach ($orderGoods as $key => $v) {
	        	if($v['goodsSpecId']>0)$v['goodsName'] = $v['goodsName'].'【'.str_replace("@@_@@","|",$v['goodsSpecNames']).'】';
	        	if(count($deliverGoods)>0){
	        		if(!in_array($v['id'],$deliverGoods))$waitDeliverGoods[] = $v;
	        	}else{
	                $waitDeliverGoods[] = $v;
	        	}
	        }
	    }
        //准备快递公司数据
        $express = Db::name('express')
                    ->alias('e')
                    ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                    ->where(['dataFlag'=>1,'isShow'=>1])->column('el.expressName');
		require Env::get('root_path') . 'extend/phpexcel/PHPExcel.php';
		$name = lang("wait_delivery_template");
		$objPHPExcel = new \PHPExcel();
		// 设置excel文档的属性
		$objPHPExcel->getProperties()->setCreator("WSTMart")//创建人
		->setLastModifiedBy("WSTMart")//最后修改人
		->setTitle($name)//标题
		->setSubject($name)//题目
		->setDescription($name)//描述
		->setKeywords($name)//关键字
		->setCategory($name);//种类
		// 开始操作excel表
		$objPHPExcel->setActiveSheetIndex(0);
		// 设置工作薄名称
		$objPHPExcel->getActiveSheet()->setTitle(iconv('gbk', 'utf-8', 'Sheet'));
		// 设置默认字体和大小
		$objPHPExcel->getDefaultStyle()->getFont()->setName(iconv('gbk', 'utf-8', ''));
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
		$styleArray = array(
				'font' => array(
						'bold' => true,
						'color'=>array(
								'argb' => 'ffffffff',
						)
				)
		);

		//设置宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objRow = $objPHPExcel->getActiveSheet()->getStyle('A1:E1');
		$objRow->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
		$objRow->getFill()->getStartColor()->setRGB('666699');
		$objRow->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objRow->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(25);

		$objPHPExcel->getActiveSheet()->setCellValue('A1', lang("label_order_no"))->setCellValue('B1', lang("og_number"))->setCellValue('C1', lang("label_goods_name"))->setCellValue('D1', lang("label_express"))->setCellValue('E1', lang("courier_number"));
		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($styleArray);
        for ($row = 0; $row < count($waitDeliverGoods); $row++){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.($row+2), $waitDeliverGoods[$row]['orderNo'])
			                              ->setCellValue('B'.($row+2), $waitDeliverGoods[$row]['id'])
			                              ->setCellValue('C'.($row+2), $waitDeliverGoods[$row]['goodsName']);
			$objPHPExcel->getActiveSheet()->getStyle('C'.($row+2))->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('C'.($row+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			/*
			$objValidation1 =$objPHPExcel->getActiveSheet()->getCell('D'.($row+2))->getDataValidation(); //从第二行开始有下拉样式
            $objValidation1->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST )
                        ->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION )
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('输入的值有误')
                        ->setError('您输入的值不在下拉框列表内.')
                        ->setPromptTitle('')
                        ->setPrompt('')
                        ->setFormula1('"' . implode(',',$express) . '"');
            */
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1:E'.(count($waitDeliverGoods)+1))->applyFromArray(array(
				'borders' => array (
						'allborders' => array (
								'style' => \PHPExcel_Style_Border::BORDER_THIN,  //设置border样式
								'color' => array ('argb' => 'FF000000'),     //设置border颜色
						)
				)
		));
		$this->PHPExcelWriter($objPHPExcel,$name);
	}

}
