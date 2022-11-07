<?php
namespace wstmart\app\controller;
use wstmart\app\model\Orders as M;
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
	/*********************************************** 用户操作订单 ************************************************************/
	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/noticeDeliver",
     *     summary="用户提醒发货",
     *     description="用户提醒发货",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
	public function noticeDeliver(){
		$m = new M();
		$userId = $m->getUserId();
        return json_encode($m->noticeDeliver($userId));
	}
	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/getLogistics",
     *     summary="根据订单号获取物流信息",
     *     description="根据订单号获取物流信息",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="orderId", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
	 * 						  @OA\Property(property="orderInfo",type="object",
	 *     						  @OA\Property(property="goodlist",type="array",
	 *                                @OA\Items(
     *                                    @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *    				                  @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                               ),
     *                            ),
	 * 						  ),
	 *     					  @OA\Property(property="logisticInfo",type="array",
	 *                             @OA\Items(
     *                                 @OA\Property(property="expressName", type="string", description="快递名称"),
     *                                 @OA\Property(property="logs", type="object", example={ "message": "ok", "nu": "999999999999999", "ischeck": "1", "condition": "D01", "com": "shentong", "status": "200", "state": "3", "data":"" }),
	 * 								   @OA\Property(property="expressId", type="string", description="快递id"),
	 * 								   @OA\Property(property="expressNo", type="string", description="快递单号"),
     *                            ),
     *                        ),
     *                        @OA\Property(property="express",type="array",
	 *                            @OA\Items(
     *                                @OA\Property(property="stateText", type="integer", description="当前状态文本", example="收件人已签收"),
     *				                  @OA\Property(property="expressName", type="string", description="快递名称"),
     *				                   @OA\Property(property="expressId", type="string", description="快递id"),
	 * 								   @OA\Property(property="expressNo", type="string", description="快递单号"),
     *                           ),
     *                        ),
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function getLogistics(){
        $m = new M();
        $userId = $m->getUserId();
        $model = new \addons\kuaidi\model\Kuaidi();
        $orderId = (int)input('orderId');

        $hasExpress = $model->checkHasExpress($orderId);
        $data = [];
        if($hasExpress) {
            // 订单信息
            $data["orderInfo"] = $model->getOrderInfo($userId);

            // 快递信息
            $express = $model->getExpress($orderId,$userId);
            foreach ($express as $v) {
                if ($v["expressNo"] != "") {
                    $res = $model->getOrderExpresses($orderId, $v['expressId'], $v['expressNo']);
                    $res['expressId'] = $v['expressId'];
                    $res['expressNo'] = $v['expressNo'];
                    $data['logisticInfo'][] = $res;
                }
            }

            // 物流状态
            foreach($data["logisticInfo"] as $k => $v){
                $state = isset($v["logs"]["state"])?$v["logs"]["state"]:'-1';
                $data["express"][$k]["stateText"] = $model->getExpressState($state);
                $data["express"][$k]["expressName"] = $v["expressName"];
                $data["express"][$k]["expressId"] = $v["expressId"];
                $data["express"][$k]["expressNo"] = $v["expressNo"];
            }
        }
        return json_encode(WSTReturn('ok',1,$data));
	}
	/**
     * @OA\Post(
     *     tags={"Orders"},
     *     path="/app/Orders/submit",
     *     summary="提交订单",
     *     description="提交订单",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="s_addressId", in="query", @OA\Schema(type="integer"), required=true, description="用户地址Id"),
	 *     @OA\Parameter(name="deliverType", in="query", @OA\Schema(type="integer"), required=true, description="配送方式，0:快递运输 1:自提", example="0"),
	 *     @OA\Parameter(name="isUseScore", in="query", @OA\Schema(type="integer"), required=true, description="是否使用积分支付，0:否 1:是", example="0"),
	 *     @OA\Parameter(name="useScore", in="query", @OA\Schema(type="integer"), required=true, description="使用的积分数", example="100"),
	 *     @OA\Parameter(name="couponIds", in="query", @OA\Schema(type="string"), required=true, description="存在`优惠券`插件时才会出现该字段，用户选中的优惠券id,例如：2:10,1:0,3:0 表示 店铺id:优惠券id", example="2:10"),
     *     @OA\Parameter(name="isInvoice", in="query", @OA\Schema(type="integer"), required=true, description="是否需要发票，0:否 1:是"),
     *     @OA\Parameter(name="invoiceClient", in="query", @OA\Schema(type="string"), required=true, description="发票抬头"),
     *     @OA\Parameter(name="payType", in="query", @OA\Schema(type="string"), required=true, description="支付方式，0:货到付款 1:在线支付"),
     *     @OA\Parameter(name="payCode", in="query", @OA\Schema(type="integer"), required=true, description="支付code,用于区别支付方式"),
     *     @OA\Parameter(name="remark_*", in="query", @OA\Schema(type="integer"), required=true, description="*为int类型 用于区分多订单时的订单备注 *为shopId"),
     *     @OA\Parameter(name="orderSrc", in="query", @OA\Schema(type="integer"), required=true, description="android 或 ios （可在程序中自行配置）"),
     *     @OA\Parameter(name="invoiceId", in="query", @OA\Schema(type="integer"), required=true, description="所选发票id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="string", description="订单流水号", example="148782685308921167"),
     *                             )
     *                         }
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function submit(){
		$m = new M();
		$userId = $m->getUserId();
		$orderSrcArr = ['android'=>3,'ios'=>4];
		if(!isset($orderSrcArr[input('orderSrc')]))return json_encode(WSTReturn(lang('illegal_orders'),-1));
		$orderSrc = $orderSrcArr[input('orderSrc')];
		$rs = $m->submit((int)$orderSrc, $userId);
        if($rs["status"]==1){
			$pkey = WSTBase64urlEncode($rs["data"]."@1");
			$rs["pkey"] = $pkey;
		}
		return json_encode($rs);
	}
	/**
     * @OA\Post(
     *     tags={"Orders"},
     *     path="/app/Orders/quickSubmit",
     *     summary="提交虚拟商品订单",
     *     description="提交虚拟商品订单",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="isUseScore", in="query", @OA\Schema(type="integer"), required=true, description="是否使用积分支付，0:否 1:是", example="0"),
	 *     @OA\Parameter(name="useScore", in="query", @OA\Schema(type="integer"), required=true, description="使用的积分数", example="100"),
	 *     @OA\Parameter(name="couponIds", in="query", @OA\Schema(type="string"), required=true, description="存在`优惠券`插件时才会出现该字段，用户选中的优惠券id,例如：2:10,1:0,3:0 表示 店铺id:优惠券id", example="2:10"),
     *     @OA\Parameter(name="isInvoice", in="query", @OA\Schema(type="integer"), required=true, description="是否需要发票，0:否 1:是"),
     *     @OA\Parameter(name="invoiceClient", in="query", @OA\Schema(type="string"), required=true, description="发票抬头"),
     *     @OA\Parameter(name="payCode", in="query", @OA\Schema(type="integer"), required=true, description="支付code,用于区别支付方式"),
     *     @OA\Parameter(name="remark_*", in="query", @OA\Schema(type="integer"), required=true, description="*为inst类型 用于区分多订单时的订单备注 *为shopId"),
     *     @OA\Parameter(name="orderSrc", in="query", @OA\Schema(type="integer"), required=true, description="android 或 ios （可在程序中自行配置）"),
     *     @OA\Parameter(name="invoiceId", in="query", @OA\Schema(type="integer"), required=true, description="所选发票id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="string", description="订单流水号", example="148782685308921167"),
     *                             )
     *                         }
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function quickSubmit(){
		$m = new M();
		$userId = $m->getUserId();
		$rs = $m->quickSubmit(2, $userId);
		return json_encode($rs);
	}
	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/getOrderList",
     *     summary="获取订单列表",
     *     description="默认取出所有订单 type为:'waitPay','waitDelivery','waitReceive',''(为空)时,会取出cancelReason,rejectReason",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string"), required=true, description="订单状态 type：'waitPay','waitDelivery','waitReceive','waitAppraise','finish','abnormal'", example=""),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="array",
	 * 								      @OA\Items(
	 * 									      @OA\Property(property="afterSaleEndTime", type="string", description="售后结束时间"),
	 * 									      @OA\Property(property="receiveTime", type="string", description="收货时间"),
	 * 									      @OA\Property(property="orderRemarks", type="string", description="订单备注"),
	 * 									      @OA\Property(property="noticeDeliver", type="string", description="是否提醒发货，1:已提醒 0:未提醒"),
	 * 									      @OA\Property(property="orderId", type="integer", description="订单id"),
	 * 									      @OA\Property(property="orderNo", type="string", description="订单号"),
	 * 									      @OA\Property(property="shopName", type="string", description="店铺名称"),
	 * 									      @OA\Property(property="shopId", type="integer", description="店铺id"),
	 * 									      @OA\Property(property="realTotalMoney", type="string", description="收货时间"),
	 * 									      @OA\Property(property="orderStatus", type="string", description="订单状态"),
	 * 									      @OA\Property(property="deliverType", type="string", description="配送方式，0:快递运输 1:自提"),
	 * 									      @OA\Property(property="isPay", type="string", description="是否支付，0:否 1:是"),
	 * 									      @OA\Property(property="payType", type="string", description="支付方式，0:货到付款 1:在线支付"),
	 * 									      @OA\Property(property="payFrom", type="string", description="支付code,用于区别支付方式"),
	 * 									      @OA\Property(property="needPay", type="string", description="需要支付的金额"),
	 * 									      @OA\Property(property="isAppraise", type="string", description="是否已评价，0:否 1:是"),
	 * 									      @OA\Property(property="useScore", type="string", description="使用的积分"),
	 * 									      @OA\Property(property="orderCode", type="string", description="订单来源标记"),
	 * 									      @OA\Property(property="hasExpress", type="boolean", description="是否有物流", default=false),
	 * 									      @OA\Property(property="allowRefund", type="string", description="是否允许退款，0:否 1:是"),
	 * 									      @OA\Property(property="list", type="array", description="订单下的商品",
	 * 										      @OA\Items(
	 * 											      @OA\Property(property="id", type="string", description="订单商品表id"),
	 * 											      @OA\Property(property="orderId", type="integer", description="订单id"),
	 * 											      @OA\Property(property="goodsId", type="integer", description="商品id"),
	 * 											      @OA\Property(property="goodsNum", type="string", description="购买数量"),
	 * 											      @OA\Property(property="goodsPrice", type="number", description="商品价格"),
	 * 											      @OA\Property(property="goodsSpecId", type="string", description="商品规格id"),
	 * 											      @OA\Property(property="goodsSpecNames", type="string", description="商品规格名称"),
	 * 											      @OA\Property(property="goodsName", type="string", description="商品名称"),
	 * 											      @OA\Property(property="goodsImg", type="string", description="商品图片"),
	 * 											      @OA\Property(property="extraJson", type="string", description="虚拟商品相关"),
	 * 											      @OA\Property(property="goodsType", type="string", description="商品类型，1:虚拟商品 0:实物商品"),
	 * 											      @OA\Property(property="commissionRate", type="string", description="商品佣金比率"),
	 * 											      @OA\Property(property="goodsCode", type="string", description="商品标记,为'gift'时表示为赠品"),
	 * 											      @OA\Property(property="promotionJson", type="string", description="优惠信息"),
	 * 											      @OA\Property(property="couponVal", type="string", description="优惠券分摊到的金额"),
	 * 											      @OA\Property(property="rewardVal", type="string", description="满就送分摊到的金额"),
	 * 											      @OA\Property(property="useScoreVal", type="string", description="商品使用的积分"),
	 * 											      @OA\Property(property="scoreMoney", type="string", description="商品使用的积分抵扣金额"),
	 * 											      @OA\Property(property="getScoreVal", type="string", description="购买该商品获得的积分数"),
	 * 											      @OA\Property(property="orderGoodscommission", type="string", description="订单商品佣金"),
	 * 											      @OA\Property(property="getScoreMoney", type="string", description="获得的积分数可抵扣的金额"),
	 * 											      @OA\Property(property="commission", type="string", description="商品佣金"),
	 * 											      @OA\Property(property="shotGoodsSpecNames", type="string", description="商品规格"),
	 * 											  )
	 * 										),
	 * 										@OA\Property(property="isComplain", type="string", description="是否已投诉，0:否 1:是"),
	 * 										@OA\Property(property="deliverTypeName", type="string", description="配送方式文字"),
	 * 										@OA\Property(property="status", type="string", description="订单状态文字"),
	 * 										@OA\Property(property="orderCodeTitle", type="string", description="订单来源文字"),
	 * 										@OA\Property(property="canAfterSale", type="boolean", description="是否允许售后申请", default=false),
	 * 									  ),
	 * 								  ),
	 * 								  @OA\Property(property="cancelReason", type="object", description="取消订单数据", example={ "id": 1, "catId": 1, "dataName": "下错单", "dataVal": "1", "dataSort": 0, "dataFlag": 1 }),
	 * 								  @OA\Property(property="rejectReason", type="object", description="拒收理由数据", example={ "id": 6, "catId": 2, "dataName": "没有按照约定的时间送货", "dataVal": "1", "dataSort": 0, "dataFlag": 1 }),
	 * 								  @OA\Property(property="refundReason", type="object", description="退款理由数据", example={ "id": 25, "catId": 4, "dataName": "配送超时", "dataVal": "1", "dataSort": 0, "dataFlag": 1 }),
     *                             )
     *                         }
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function getOrderList(){
		/*
		 	-3:拒收、退款列表
			-2:待付款列表
			-1:已取消订单
			0,1: 待收货
			2:待评价/已完成
		*/
		$flag = -1;
		$type = input('param.type');
		$status = [];
		// 是否取出取消、拒收、退款订单理由
		$cancelData = $rejectData = $refundData = false;
		switch ($type) {
			case 'waitPay':
				$status=[-2];
				$cancelData = true;
				break;
			case 'waitDelivery':
				$status=[0];
				$rejectData = true;
				$cancelData = true;
				break;
			case 'waitReceive':
				$status=[1];
				$rejectData = true;
				$cancelData = true;
				break;
			case 'waitAppraise':
				$status=[2];
				$flag=0;
				break;
			case 'finish':
				$status=[2];
				break;
			case 'abnormal': // 退款/拒收 与取消合并
				$status=[-1,-3];
				$refundData = true;
				break;
			default:
				$status=[-3,-2,-1,0,1,2];
				$cancelData = $rejectData = $refundData = true;
				break;
		}
		$m = new M();
		$userId = $m->getUserId();
		$rs = $m->userOrdersByPage($status, $flag, $userId);
		foreach($rs['data'] as $k=>$v){
			// 删除无用字段
			WSTUnset($rs['data'][$k],'shopQQ,shopWangWang,goodsMoney,totalMoney,deliverMoney,orderSrc,createTime,complainId,refundId,payTypeName,hook,isRefund');
			// 判断是否退款
			if(in_array($v['orderStatus'],[-1,-3]) && ($v['payType']==1) && ($v['isPay']==1) ){
                
				$rs['data'][$k]['status'] .= ($v['isRefund']==1)?lang('refund'):lang('no_refund');
			}
			if(!empty($v['list'])){
				foreach($v['list'] as $k1=>$v1){
					$rs['data'][$k]['list'][$k1]['goodsImg'] = $v1['goodsImg'];
					$rs['data'][$k]['list'][$k1]['goodsName'] = htmlspecialchars_decode($v1['goodsName']);
				}
			}
		}

		// 根据获取的type来取
		// 取消理由
		if($cancelData)$rs['cancelReason'] = WSTDatas('ORDER_CANCEL');
		// 拒收理由
		if($rejectData)$rs['rejectReason'] = WSTDatas('ORDER_REJECT');
		// 退款理由
		if($refundData)$rs['refundReason'] = WSTDatas('REFUND_TYPE');


		if(empty($rs['data']))return json_encode(WSTReturn(lang('empty_orders'),-1));
		return json_encode(WSTReturn(lang('success_msg'), 1, $rs));
	}

	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/getDetail",
     *     summary="获取订单详情",
     *     description="获取订单详情",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="订单id", example="411"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="object",
	 * 								      @OA\Property(property="orderId", type="integer", description="订单id"),
	 * 								      @OA\Property(property="orderNo", type="string", description="订单号"),
	 * 								      @OA\Property(property="shopId", type="integer", description="店铺id"),
	 * 								      @OA\Property(property="goodsId", type="integer", description="商品id"),
	 * 								      @OA\Property(property="userId", type="integer", description="用户id"),
	 * 									  @OA\Property(property="orderStatus", type="string", description="订单状态"),
	 * 									  @OA\Property(property="goodsMoney", type="string", description="商品价格"),
	 * 									  @OA\Property(property="deliverType", type="string", description="配送方式，0:快递运输 1:自提"),
	 * 									  @OA\Property(property="deliverMoney", type="string", description="运费"),
	 * 									  @OA\Property(property="totalMoney", type="string", description="订单总价格"),
	 * 									  @OA\Property(property="realTotalMoney", type="string", description="实际订单总价格(在各种优惠抵扣之后，或者修改订单价格)"),
	 * 									  @OA\Property(property="isPay", type="string", description="是否支付，0:否 1:是"),
	 * 									  @OA\Property(property="payType", type="string", description="支付方式，0:货到付款 1:在线支付"),
	 * 									  @OA\Property(property="payFrom", type="string", description="支付code,用于区别支付方式"),
	 * 									  @OA\Property(property="areaId", type="string", description="最后一级地区id"),
	 * 									  @OA\Property(property="areaIdPath", type="string", description="地区Id字符串", example="28_344_2931_"),
	 * 									  @OA\Property(property="userName", type="string", description="用户名", example="啊水"),
	 * 									  @OA\Property(property="userAddress", type="integer", description="完整收货地址", example=""),
	 * 									  @OA\Property(property="userPhone", type="integer", description="收货人联系电话", example=""),
	 * 									  @OA\Property(property="orderScore", type="integer", description="订单积分", example="0"),
	 *     								  @OA\Parameter(name="isInvoice", in="query", @OA\Schema(type="integer"), required=true, description="是否需要发票，0:否 1:是"),
     *								      @OA\Parameter(name="invoiceClient", in="query", @OA\Schema(type="string"), required=true, description="发票抬头"),
	 * 									  @OA\Property(property="orderSrc", type="string", description="订单来源平台 1:pc 2:手机\微信 3:android 4:ios"),
	 * 									  @OA\Property(property="needPay", type="string", description="需要支付的金额"),
	 * 									  @OA\Property(property="orderType", type="string", description="订单类型，0:快递运输 1:自提订单"),
	 * 									  @OA\Property(property="isRefund", type="string", description="是否已申请退款，0:否 1:是"),
	 * 									  @OA\Property(property="isAppraise", type="string", description="是否已评价，0:否 1:是"),
	 * 									  @OA\Property(property="cancelReason", type="string", description="取消订单理由id"),
	 * 									  @OA\Property(property="rejectReason", type="string", description="拒收订单理由id"),
	 * 									  @OA\Property(property="rejectOtherReason", type="string", description="拒收订单说明原因"),
	 * 									  @OA\Property(property="isClosed", type="string", description="订单是否已关闭，0:否 1:是"),
	 * 									  @OA\Property(property="orderunique", type="string", description="订单唯一流水号"),
	 * 									  @OA\Property(property="receiveTime", type="string", description="收货时间"),
	 * 									  @OA\Property(property="deliveryTime", type="string", description="发货时间"),
	 * 									  @OA\Property(property="tradeNo", type="string", description="在线支付交易流水号"),
	 * 									  @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
	 * 									  @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
	 * 									  @OA\Property(property="useScore", type="string", description="订单使用的积分"),
	 * 									  @OA\Property(property="scoreMoney", type="string", description="订单使用的积分抵扣金额"),
	 * 									  @OA\Property(property="orderCode", type="string", description="订单来源标记"),
	 * 									  @OA\Property(property="noticeDeliver", type="string", description="是否提醒发货，1:已提醒 0:未提醒"),
	 * 									  @OA\Property(property="invoiceJson", type="string", description="发票内容相关"),
	 * 									  @OA\Property(property="payTime",  type="string", description="支付时间"),
	 * 									  @OA\Property(property="userCouponId",  type="string", description="使用的优惠券id"),
	 * 									  @OA\Property(property="userCouponJson",  type="string", description="优惠券信息"),
	 * 									  @OA\Property(property="isMakeInvoice",  type="string", description="是否已开票"),
	 * 									  @OA\Property(property="afterSaleEndTime",  type="string", description="售后结束时间"),
	 * 									  @OA\Property(property="getScoreVal", type="string", description="购买该订单获得的积分数"),
	 * 									  @OA\Property(property="distributType", type="string", description="分销类型"),
	 * 									  @OA\Property(property="distributOrderRate", type="string", description="分销订单分佣比例"),
	 * 									  @OA\Property(property="distributRate", type="string", description="分销分佣比例"),
	 * 									  @OA\Property(property="totalCommission", type="string", description="总佣金"),
	 * 									  @OA\Property(property="shopAddress", type="string", description="店铺地址"),
	 * 									  @OA\Property(property="shopTel", type="string", description="店铺联系电话"),
	 * 									  @OA\Property(property="shopName", type="string", description="店铺名称"),
	 * 									  @OA\Property(property="shopQQ", type="string", description="店铺qq"),
	 * 									  @OA\Property(property="shopWangWang", type="string", description="店铺旺旺"),
	 * 									  @OA\Property(property="refundId", type="string", description="退款id"),
	 * 									  @OA\Property(property="refundStatus", type="string", description="退款状态"),
	 * 									  @OA\Property(property="list", type="array", description="订单下的商品",
	 *    								      @OA\Items(
	 *    									      @OA\Property(property="id", type="string", description="订单商品表id"),
	 *    									      @OA\Property(property="orderId", type="integer", description="订单id"),
	 *    									      @OA\Property(property="goodsId", type="integer", description="商品id"),
	 *    									      @OA\Property(property="goodsNum", type="string", description="购买数量"),
	 *    									      @OA\Property(property="goodsPrice", type="number", description="商品价格"),
	 *    									      @OA\Property(property="goodsSpecId", type="string", description="商品规格id"),
	 *    									      @OA\Property(property="goodsSpecNames", type="string", description="商品规格名称"),
	 *    									      @OA\Property(property="goodsName", type="string", description="商品名称"),
	 *    									      @OA\Property(property="goodsImg", type="string", description="商品图片"),
	 *    									      @OA\Property(property="extraJson", type="string", description="虚拟商品相关"),
	 *    									      @OA\Property(property="goodsType", type="string", description="商品类型，1:虚拟商品 0:实物商品"),
	 *    									      @OA\Property(property="commissionRate", type="string", description="商品佣金比率"),
	 *    									      @OA\Property(property="goodsCode", type="string", description="商品标记,为'gift'时表示为赠品"),
	 *    									      @OA\Property(property="promotionJson", type="string", description="优惠信息"),
	 *    									      @OA\Property(property="couponVal", type="string", description="优惠券分摊到的金额"),
	 *    									      @OA\Property(property="rewardVal", type="string", description="满就送分摊到的金额"),
	 *    									      @OA\Property(property="useScoreVal", type="string", description="商品使用的积分"),
	 *    									      @OA\Property(property="scoreMoney", type="string", description="商品使用的积分抵扣金额"),
	 *    									      @OA\Property(property="getScoreVal", type="string", description="购买该商品获得的积分数"),
	 *    									      @OA\Property(property="orderGoodscommission", type="string", description="订单商品佣金"),
	 *    									      @OA\Property(property="getScoreMoney", type="string", description="获得的积分数可抵扣的金额"),
	 *    									      @OA\Property(property="commission", type="string", description="商品佣金"),
	 *    									      @OA\Property(property="shotGoodsSpecNames", type="string", description="商品规格"),
	 *    									  )
	 * 									  ),
	 * 									  @OA\Property(property="expressNo", type="string", description="快递单号"),
	 * 									  @OA\Property(property="isComplain", type="string", description="是否已投诉，0:否 1:是"),
	 * 									  @OA\Property(property="allowRefund", type="string", description="是否允许退款，0:否 1:是"),
	 * 									  @OA\Property(property="deliverTypeName", type="string", description="配送方式文字"),
	 * 									  @OA\Property(property="status", type="string", description="订单状态文字"),
	 * 									  @OA\Property(property="canAfterSale", type="boolean", description="是否允许售后申请", default=false),
	 * 								  ),
     *                             )
     *                         }
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
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
        $rs['orderCodeTitle'] = WSTOrderModule($rs['orderCode']);
		// 删除无用字段
		unset($rs['log']);
		// 发票税号
		$invoiceArr = json_decode($rs['invoiceJson'],true);
		if(isset($invoiceArr['invoiceCode']))$rs['invoiceCode'] = $invoiceArr['invoiceCode'];
		$rs['status'] = WSTLangOrderStatus($rs['orderStatus']);
		$rs['payInfo'] = WSTLangPayType($rs['payType']);
		$rs['deliverInfo'] = WSTLangDeliverType($rs['deliverType']);
        if($rs['verificationCode']){
            $rs['qrCode'] = WSTCreateQrcode($rs['verificationCode']);
        }
		foreach($rs['goods'] as $k=>$v){
			$rs['goods'][$k]['goodsImg'] = WSTImg($v['goodsImg'],3);
			$rs['goods'][$k]['goodsName'] = htmlspecialchars_decode($v['goodsName']);
		}
		// 若为取消或拒收则取出相应理由
		if($rs['orderStatus']==-1){
			if($rs['cancelReason']==0){
				$rs['cancelDesc'] = lang('overtime_to_pay');
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
		return json_encode(WSTReturn(lang('success_msg'),1,$rs));
	}
	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/receive",
     *     summary="用户确认收货",
     *     description="用户确认收货",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="orderId", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
	public function receive(){
		$m = new M();
		$orderId = input('param.orderId');
		$userId = $m->getUserId();
		$rs = $m->receive($orderId, $userId);
		return json_encode($rs);
	}
	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/orderAppraise",
     *     summary="用户-评价页",
     *     description="用户-评价页",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="oId", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                    @OA\Property(property="data",type="object",
     *                        @OA\Property(property="count", type="integer", description="总商品数"),
     *                        @OA\Property(property="data", type="array",
	 * 						      @OA\Items(
	 * 							      @OA\Property(property="id", type="integer", description="主键id"),
	 * 							      @OA\Property(property="goodsCode", type="integer", description="标记商品来源"),
	 * 							      @OA\Property(property="orderId", type="integer", description="订单id"),
	 * 							      @OA\Property(property="goodsName", type="string", description="商品名称"),
	 * 							      @OA\Property(property="goodsId", type="integer", description="商品ID"),
	 * 							      @OA\Property(property="goodsSpecNames", type="string", description="商品规格"),
	 * 							      @OA\Property(property="goodsImg", type="string", description="商品图片"),
	 * 							      @OA\Property(property="goodsSpecId", type="integer", description="商品规格Id"),
	 * 							      @OA\Property(property="appraise", type="object", description="评价", example={ "goodsScore": 3, "serviceScore": 4, "timeScore": 5, "content": "12312123", "images": "", "createTime": "2019-10-31 22:54:16" }),
	 * 							  )
	 * 						  ),
     *                        @OA\Property(property="alreadys", type="string", description="是否已完成评价，1:是 0:否"),
     *                        @OA\Property(property="shopName", type="string", description="店铺名称"),
     *                        @OA\Property(property="oId", type="string", description="订单id"),
     *                     ),
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function orderAppraise(){
		$m = model('Orders');
		$oId = (int)input('oId');
		//根据订单id获取 商品信息
		$userId = $m->getUserId();
		$data = $m->getOrderInfoAndAppr($userId);
		$data['shopName'] = model('shops')->getShopName($oId);
		$data['oId'] = $oId;
		return json_encode(WSTReturn(lang('success_msg'), 1, $data));
	}

	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/cancellation",
     *     summary="用户取消订单",
     *     description="用户取消订单",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Parameter(name="reason", in="query", @OA\Schema(type="integer"), required=true, description="取消理由id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
	public function cancellation(){
		$m = new M();
		$userId = $m->getUserId();
		$rs = $m->cancel($userId);
		return json_encode($rs);
	}
	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/reject",
     *     summary="用户拒收订单",
     *     description="用户拒收订单",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Parameter(name="reason", in="query", @OA\Schema(type="integer"), required=true, description="拒收理由id"),
     *     @OA\Parameter(name="content", in="query", @OA\Schema(type="string"), required=true, description="拒收说明，当reason=10000时该值不能为空"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
	public function reject(){
		$m = new M();
		$userId = $m->getUserId();
		$rs = $m->reject((int)$userId);
		return json_encode($rs);
	}

	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/getRefund",
     *     summary="用户获取订单退款信息",
     *     description="用户获取订单退款信息",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                    @OA\Property(property="data",type="object",
	 *                        @OA\Property(property="orderId", type="integer", description="订单id"),
	 *                        @OA\Property(property="orderNo", type="string", description="订单号"),
	 *                        @OA\Property(property="goodsMoney", type="string", description="商品价格"),
	 *                        @OA\Property(property="deliverMoney", type="string", description="运费"),
	 *                        @OA\Property(property="useScore", type="string", description="使用的积分"),
	 *                        @OA\Property(property="scoreMoney", type="string", description="订单使用的积分抵扣金额"),
	 *                        @OA\Property(property="totalMoney", type="string", description="订单总价格"),
	 *                        @OA\Property(property="realTotalMoney", type="string", description="实际支付总金额"),
     *                     ),
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function getRefund(){
		$m = new M();
		$rs = $m->getMoneyByOrder((int)input('id'));
		return json_encode(WSTReturn(lang('success_msg'),1,$rs));
	}




	/*********************************************** 商家操作订单 ************************************************************/


	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/getSellerOrderList",
     *     summary="【商家】获取订单列表",
     *     description="默认取出所有订单 type为 'waitDelivery' 或 '' 时 会取出快递信息 express",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string"), required=true, description=" 订单状态  type：'waitPay','waitReceive','waitAppraise','finish','abnormal'", example=""),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="array",
	 * 								      @OA\Items(
	 * 									      @OA\Property(property="orderRemarks", type="string", description="订单备注"),
	 * 									      @OA\Property(property="noticeDeliver", type="string", description="是否提醒发货，1:已提醒 0:未提醒"),
	 * 									      @OA\Property(property="orderId", type="integer", description="订单id"),
	 * 									      @OA\Property(property="orderNo", type="string", description="订单号"),
	 * 									      @OA\Property(property="realTotalMoney", type="string", description="收货时间"),
	 * 									      @OA\Property(property="orderStatus", type="string", description="订单状态"),
	 * 									      @OA\Property(property="deliverType", type="string", description="配送方式，0:快递运输 1:自提"),
	 * 									      @OA\Property(property="isPay", type="string", description="是否支付，0:否 1:是"),
	 * 									      @OA\Property(property="payType", type="string", description="支付方式，0:货到付款 1:在线支付"),
	 * 									      @OA\Property(property="payFrom", type="string", description="支付code,用于区别支付方式"),
	 * 									      @OA\Property(property="isAppraise", type="string", description="是否已评价，0:否 1:是"),
	 * 									      @OA\Property(property="orderCode", type="string", description="订单来源标记"),
	 * 									      @OA\Property(property="list", type="array", description="订单下的商品",
	 * 										      @OA\Items(
	 * 											      @OA\Property(property="id", type="string", description="订单商品表id"),
	 * 											      @OA\Property(property="orderId", type="integer", description="订单id"),
	 * 											      @OA\Property(property="goodsId", type="integer", description="商品id"),
	 * 											      @OA\Property(property="goodsNum", type="string", description="购买数量"),
	 * 											      @OA\Property(property="goodsPrice", type="number", description="商品价格"),
	 * 											      @OA\Property(property="goodsSpecId", type="string", description="商品规格id"),
	 * 											      @OA\Property(property="goodsSpecNames", type="string", description="商品规格名称"),
	 * 											      @OA\Property(property="goodsName", type="string", description="商品名称"),
	 * 											      @OA\Property(property="goodsImg", type="string", description="商品图片"),
	 * 											      @OA\Property(property="extraJson", type="string", description="虚拟商品相关"),
	 * 											      @OA\Property(property="goodsType", type="string", description="商品类型，1:虚拟商品 0:实物商品"),
	 * 											      @OA\Property(property="commissionRate", type="string", description="商品佣金比率"),
	 * 											      @OA\Property(property="goodsCode", type="string", description="商品标记,为'gift'时表示为赠品"),
	 * 											      @OA\Property(property="promotionJson", type="string", description="优惠信息"),
	 * 											      @OA\Property(property="couponVal", type="string", description="优惠券分摊到的金额"),
	 * 											      @OA\Property(property="rewardVal", type="string", description="满就送分摊到的金额"),
	 * 											      @OA\Property(property="useScoreVal", type="string", description="商品使用的积分"),
	 * 											      @OA\Property(property="scoreMoney", type="string", description="商品使用的积分抵扣金额"),
	 * 											      @OA\Property(property="getScoreVal", type="string", description="购买该商品获得的积分数"),
	 * 											      @OA\Property(property="orderGoodscommission", type="string", description="订单商品佣金"),
	 * 											      @OA\Property(property="getScoreMoney", type="string", description="获得的积分数可抵扣的金额"),
	 * 											      @OA\Property(property="commission", type="string", description="商品佣金"),
	 * 											  )
	 * 										),
	 * 										@OA\Property(property="status", type="string", description="订单状态文字"),
	 *
	 * 									  ),
	 * 								  ),
	 * 								  @OA\Property(property="express", type="array",
	 *  							      @OA\Items(
	 *  								      @OA\Property(property="expressId", type="string", description="快递id"),
	 *  								      @OA\Property(property="expressName", type="string", description="快递名称"),
	 *  								      @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
	 *  								      @OA\Property(property="expressCode", type="string", description="快递代码"),
	 *  							      )
	 *  							  ),
     *                             )
     *                         }
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
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
			WSTUnset($rs['data'][$k],'goodsMoney,totalMoney,deliverMoney,orderSrc,createTime,payTypeName,isRefund,userAddress,userName,deliverTypeName');
			// 判断是否退款
			if(in_array($v['orderStatus'],[-1,-3]) && ($v['payType']==1) && ($v['isPay']==1) ){
				$rs['data'][$k]['status'] .= ($v['isRefund']==1)?'(已退款)':'(未退款)';
			}
			if(!empty($v['list'])){
				foreach($v['list'] as $k1=>$v1){
					$rs['data'][$k]['list'][$k1]['goodsImg'] = WSTImg($v1['goodsImg'],3);
				}
			}
		}
		// 快递公司数据
		if($express)$rs['express'] = model('Express')->listQuery();

		if(empty($rs['data']))return json_encode(WSTReturn(lang('empty_orders'),-1));
		return json_encode(WSTReturn(lang('success_msg'), 1, $rs));
	}

	/**
     * @OA\Get(
     *     tags={"Orders"},
     *     path="/app/Orders/waitDeliverById",
     *     summary="获取单条订单的商品信息",
     *     description="获取单条订单的商品信息",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                    @OA\Property(property="data",type="object",
     *                        @OA\Property(property="list", type="array",
	 * 						      @OA\Items(
	 * 							      @OA\Property(property="id", type="string", description="订单商品表id"),
	 * 						          @OA\Property(property="orderId", type="integer", description="订单id"),
	 * 						          @OA\Property(property="goodsId", type="integer", description="商品id"),
	 * 						          @OA\Property(property="goodsNum", type="string", description="购买数量"),
	 * 						          @OA\Property(property="goodsPrice", type="number", description="商品价格"),
	 * 						          @OA\Property(property="goodsSpecId", type="string", description="商品规格id"),
	 * 						          @OA\Property(property="goodsSpecNames", type="string", description="商品规格名称"),
	 * 						          @OA\Property(property="goodsName", type="string", description="商品名称"),
	 * 						          @OA\Property(property="goodsImg", type="string", description="商品图片"),
	 * 						          @OA\Property(property="extraJson", type="string", description="虚拟商品相关"),
	 * 						          @OA\Property(property="goodsType", type="string", description="商品类型，1:虚拟商品 0:实物商品"),
	 * 						          @OA\Property(property="commissionRate", type="string", description="商品佣金比率"),
	 * 						          @OA\Property(property="goodsCode", type="string", description="商品标记,为'gift'时表示为赠品"),
	 * 						          @OA\Property(property="promotionJson", type="string", description="优惠信息"),
	 * 						          @OA\Property(property="couponVal", type="string", description="优惠券分摊到的金额"),
	 * 						          @OA\Property(property="rewardVal", type="string", description="满就送分摊到的金额"),
	 * 						          @OA\Property(property="useScoreVal", type="string", description="商品使用的积分"),
	 * 						          @OA\Property(property="scoreMoney", type="string", description="商品使用的积分抵扣金额"),
	 * 						          @OA\Property(property="getScoreVal", type="string", description="购买该商品获得的积分数"),
	 * 						          @OA\Property(property="orderGoodscommission", type="string", description="订单商品佣金"),
	 * 						          @OA\Property(property="getScoreMoney", type="string", description="获得的积分数可抵扣的金额"),
	 * 						          @OA\Property(property="commission", type="string", description="商品佣金"),
	 * 						          @OA\Property(property="hasDeliver", type="boolean", description="是否已发货", default=false),
	 * 							  )
	 * 						  ),
     *                     ),
	 *					   @OA\Property(property="userName", type="string", description="用户名", example="啊水"),
	 * 					   @OA\Property(property="userPhone", type="integer", description="收货人联系电话", example=""),
	 * 					   @OA\Property(property="userAddress", type="integer", description="完整收货地址", example=""),
	 * 					   @OA\Property(property="deliverType", type="string", description="配送方式，0:快递运输 1:自提"),
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    public function waitDeliverById(){
        $this->checkAuth();
        $m = new M();
        $rs = $m->waitDeliverById();
        return json_encode(WSTReturn("", 1,$rs));
    }

	/**
     * @OA\Post(
     *     tags={"Orders"},
     *     path="/app/Orders/deliver",
     *     summary="商家发货",
     *     description="商家发货",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Parameter(name="expressId", in="query", @OA\Schema(type="integer"), required=true, description="快递id"),
     *     @OA\Parameter(name="expressNo", in="query", @OA\Schema(type="string"), required=true, description="快递单号"),
     *     @OA\Parameter(name="selectOrderGoodsIds", in="query", @OA\Schema(type="string"), required=true, description="需要发货的订单商品id，多个id用,进行分割", example="112,113,114"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
	public function deliver(){
		$m = new M();
		$userId = (int)$m->getUserId();
		$shopId = (int)$m->getShopId($userId);
		$rs = $m->deliver($userId, $shopId);

		return json_encode($rs);
	}
	/**
     * @OA\Post(
     *     tags={"Orders"},
     *     path="/app/Orders/editOrderMoney",
     *     summary="商家修改订单价格",
     *     description="商家修改订单价格",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Parameter(name="expressId", in="query", @OA\Schema(type="integer"), required=true, description="快递id"),
     *     @OA\Parameter(name="orderMoney", in="query", @OA\Schema(type="number"), required=true, description="新价格"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
	public function editOrderMoney(){
		$m = new M();
		$userId = (int)$m->getUserId();
		$shopId = (int)$m->getShopId($userId);
		$rs = $m->editOrderMoney($userId, $shopId);

		return json_encode($rs);
	}
	/**
     * @OA\Post(
     *     tags={"Orders"},
     *     path="/app/Orders/toShopRefund",
     *     summary="【商家】获取订单退款信息",
     *     description="【商家】获取订单退款信息",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="退款id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                    @OA\Property(property="data",type="object",
	 *					      @OA\Property(property="orderId", type="integer", description="订单id"),
	 *					      @OA\Property(property="orderNo", type="string", description="订单号"),
	 *					      @OA\Property(property="goodsMoney", type="string", description="商品价格"),
	 *					      @OA\Property(property="deliverMoney", type="string", description="运费"),
	 *					      @OA\Property(property="useScore", type="string", description="使用的积分"),
	 *					      @OA\Property(property="scoreMoney", type="string", description="订单使用的积分抵扣金额"),
	 *					      @OA\Property(property="totalMoney", type="string", description="订单总价格"),
	 *					      @OA\Property(property="realTotalMoney", type="string", description="实际支付总金额"),
	 *					      @OA\Property(property="backMoney", type="number", description="退款金额"),
     *                     ),
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function toShopRefund(){
		$rs = model('OrderRefunds')->getRefundMoneyByOrder((int)input('id'));
		return json_encode(WSTReturn(lang('success_msg'), 1, $rs));
	}

    /**
     * 获取核销订单信息
     */
    public function getVerificatOrder(){
        $m = new M();
        $userId = (int)$m->getUserId();
        $shopId = (int)$m->getShopId($userId);
        $rs = $m->getVerificatOrder($shopId);
        if(isset($rs['goods'])) {
            /*******  满就送减免金额 *******/
            foreach ($rs['goods'] as $k => $v) {
                if (isset($v['promotionJson']) && $v['promotionJson'] != '') {
                    $rs['goods'][$k]['promotionJson'] = json_decode($v['promotionJson'], true);
                    $rs['goods'][$k]['promotionJson']['extraJson'] = json_decode($rs['goods'][$k]['promotionJson']['extraJson'], true);
                    // 满就送减免金额
                    $rs['rewardMoney'] = $money = $rs['goods'][$k]['promotionJson']['promotionMoney'];
                    break;
                }
            }
            /*********  优惠券  *********/
            if (isset($rs['userCouponId']) && $rs['userCouponId'] > 0) {
                // 获取优惠券信息
                $money = json_decode($rs['userCouponJson'], true)['money']; // 优惠券优惠金额
                $rs['couponMoney'] = number_format($money, 2);
            }
        }
        if(empty($rs)){
            return json_encode(lang('invalid_v_code'));
        }else{
            return json_encode(WSTReturn("", 1,$rs));
        }
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
