<?php
namespace wstmart\app\controller;
use wstmart\common\model\OrderServices as CM;
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
 * 售后控制器
 */
class Orderservices extends Base{
    protected $beforeActionList = ['checkAuth'];
    /**
     * @OA\Get(
     *     tags={"Orderservices"},
     *     path="/app/Orderservices/pagequery",
     *     summary="售后申请列表查询",
     *     description="售后申请列表查询",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
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
	 * 									      @OA\Property(property="orderId", type="integer", description="订单id"),
	 * 									      @OA\Property(property="orderNo", type="string", description="订单号"),
	 * 									      @OA\Property(property="goodsServiceType", type="string", description="售后申请类型 0：退款退货 1：退款 2：换货"),
	 * 									      @OA\Property(property="serviceType", type="string", description="退换货类型,数据由基础数据类型里取"),
	 * 									      @OA\Property(property="serviceRemark", type="string", description="退换货原因"),
	 * 									      @OA\Property(property="serviceAnnex", type="string", description="附件"),
	 * 									      @OA\Property(property="refundScore", type="integer", description="本次申请可退还的积分"),
	 * 									      @OA\Property(property="useScoreMoney", type="number", description="使用的积分可抵扣金额"),
	 * 									      @OA\Property(property="getScoreMoney", type="number", description="获得的积分可抵扣金额"),
	 * 									      @OA\Property(property="refundMoney", type="number", description="申请退款的金额"),
	 * 									      @OA\Property(property="refundableMoney", type="number", description="售后单可退款金额"),
	 * 									      @OA\Property(property="isShopAgree", type="integer", description="商家是否同意，1:同意 0:不同意"),
	 * 									      @OA\Property(property="disagreeRemark", type="string", description="不同意原因"),
	 * 									      @OA\Property(property="userAddressId", type="string", description="用户收货地址id"),
	 * 									      @OA\Property(property="areaId", type="string", description="地区id"),
	 * 									      @OA\Property(property="areaIdPath", type="string", description="地区ID值"),
	 * 									      @OA\Property(property="userName", type="string", description="用户收货人"),
	 * 									      @OA\Property(property="userAddress", type="string", description="用户详细收货地址"),
	 * 									      @OA\Property(property="userPhone", type="string", description="用户收货电话"),
	 * 									      @OA\Property(property="shopAreaId", type="string", description="商家收货地区ID"),
	 * 									      @OA\Property(property="shopAreaIdPath", type="string", description="商家收货地区ID值"),
	 * 									      @OA\Property(property="shopAddress", type="string", description="商家详细收货地址"),
	 * 									      @OA\Property(property="shopPhone", type="string", description="商家收货电话"),
	 * 									      @OA\Property(property="isUserSendGoods", type="string", description="是有是否已发货，0：未发货 1：已发货"),
	 * 									      @OA\Property(property="expressType", type="string", description="用户发货方式，0：无需物流  1：快递"),
	 * 									      @OA\Property(property="expressId", type="string", description="买家快递ID"),
	 * 									      @OA\Property(property="expressNo", type="string", description="买家物流单号"),
	 * 									      @OA\Property(property="isShopAccept", type="string", description="商家是否收到货 -1：拒收  0：未收货  1：已收货"),
	 * 									      @OA\Property(property="shopRejectType", type="string", description="商家拒收类型,数据由基础数据类型里取"),
	 * 									      @OA\Property(property="shopRejectOther", type="string", description="商家拒收原因,选择“其他”的时候填写文字"),
	 * 									      @OA\Property(property="shopRejectImg", type="string", description="拒收时的货物图片"),
	 * 									      @OA\Property(property="isShopSend", type="string", description="商家是否发货 0：未发货 1：已发货"),
	 * 									      @OA\Property(property="shopExpressType", type="string", description="商家发货方式，0:无需物流  1:快递"),
	 * 									      @OA\Property(property="shopExpressId", type="string", description="商家快递ID"),
	 * 									      @OA\Property(property="shopExpressNo", type="string", description="商家快递单号"),
	 * 									      @OA\Property(property="isUserAccept", type="string", description="用户收货状态，-1：拒收 0：未收货  1：已收货"),
	 * 									      @OA\Property(property="userRejectType", type="string", description="用户拒收原因,数据由基础数据类型里取"),
	 * 									      @OA\Property(property="userRejectOther", type="string", description="用户拒收原因,选择“其他”的时候填写文字"),
     * 									      @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
	 * 									      @OA\Property(property="isClose", type="string", description="售后单是否已关闭，1:是 0:否"),
	 * 									      @OA\Property(property="serviceStatus", type="string", description="状态备注：0：待商家审核  1：等待用户发货 2：商家等待收货 3：商家确认收货  4：等待商家发货  5：确认收货  6：完成退款/退货"),
	 * 									      @OA\Property(property="shopAcceptExpireTime", type="string", description="商家受理期限"),
	 * 									      @OA\Property(property="userSendExpireTime", type="string", description="用户发货期限"),
	 * 									      @OA\Property(property="shopReceiveExpireTime", type="string", description="商家收货期限"),
	 * 									      @OA\Property(property="gImgs", type="array", description="商品图片数组",
     *                                             @OA\Items()
     *                                        ),
	 * 									      @OA\Property(property="shopName", type="string", description="店铺名称"),
	 * 									      @OA\Property(property="glists", type="array", description="订单下的商品",
	 * 										      @OA\Items(
	 * 											      @OA\Property(property="serviceId", type="string", description="售后表主键id"),
	 * 											      @OA\Property(property="id", type="string", description="主键表id"),
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
	 * 										@OA\Property(property="statusText", type="string", description="售后单状态文字", example="售后已关闭"),
	 * 										@OA\Property(property="goodsServiceTypeText", type="string", description="售后单类型文字", example="退款退货"),
	 * 									  ),
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
    public function pagequery(){
        $m = new CM();
        $userId = model('app/index')->getUserId();
        $rs = $m->pageQuery(0,$userId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * @OA\Get(
     *     tags={"Orderservices"},
     *     path="/app/Orderservices/apply",
     *     summary="售后申请页面所需数据",
     *     description="售后申请页面所需数据",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="orderId", in="query", @OA\Schema(type="integer"), required=true, description="订单Id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object", 
     *                         @OA\Property(property="isFixedCombination", type="boolean", description="是否为固定搭配订单"),
     *                         @OA\Property(property="goods",type="array", 
     *                             @OA\Items(
     *                                 @OA\Property(property="orderId", type="integer", description="订单id"),
     *                                 @OA\Property(property="orderNo", type="string", description="订单号"),
     *                                 @OA\Property(property="shopId", type="integer", description="店铺id"),
     *                                 @OA\Property(property="userId", type="integer", description="用户id"),
     *                                 @OA\Property(property="id", type="integer", description="订单商品表id"),
     *                                 @OA\Property(property="goodsId", type="integer", description="商品id"),
     *                                 @OA\Property(property="goodsNum", type="integer", description="购买数量"),
     *                                 @OA\Property(property="goodsPrice", type="number", description="商品价格"),
     *                                 @OA\Property(property="goodsSpecId", type="integer", description="商品规格id"),
     *                                 @OA\Property(property="goodsSpecNames", type="string", description="商品规格名称"),
     *                                 @OA\Property(property="goodsName", type="string", description="商品名称"),
     *                                 @OA\Property(property="goodsImg", type="string", description="商品图片"),
     *                              ),
     *                        ),
     *                        @OA\Property(property="reasons", type="object",
     *                            @OA\Property(property="1", type="object", example={ "id": 319, "catId": 19, "dataName": "拍错/不喜欢/效果差", "dataVal": "1", "dataSort": 0, "dataFlag": 1 })
     *                        ),
     *                     ),
     *                  )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    public function apply(){
        $m = new CM();
        $userId = model('app/index')->getUserId();
        $goods = $m->getGoods($userId);
        // 退换货原因
        $reasons = WSTDatas('ORDER_SERVICES');
        // 商品类型，虚拟商品在订单中只能有一款
        $goodsType = $goods[0]['goodsType'];
        $rs = [
            // 是否为固定搭配订单
            'isFixedCombination'=>$m->isFixedCombination(input('orderId')),
            'goods'=>$goods, 
            'reasons'=>$reasons, 
            'goodsType'=>$goodsType
        ];
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * @OA\Post(
     *     tags={"Orderservices"},
     *     path="/app/Orderservices/commit",
     *     summary="提交售后申请",
     *     description="提交售后申请",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="orderId", in="query", @OA\Schema(type="integer"), required=true, description="订单Id"),
     *     @OA\Parameter(name="serviceType", in="query", @OA\Schema(type="integer"), required=true, description="退换货类型,数据由基础数据类型里取"),
     *     @OA\Parameter(name="goodsServiceType", in="query", @OA\Schema(type="string"), required=true, description="退换货原因"),
     *     @OA\Parameter(name="serviceRemark", in="query", @OA\Schema(type="string"), required=true, description="退换货说明"),
     *     @OA\Parameter(name="refundMoney", in="query", @OA\Schema(type="integer"), required=true, description="申请退还金额"),
     *     @OA\Parameter(name="serviceAnnex", in="query", @OA\Schema(type="string"), required=true, description="附件"),
     *     @OA\Parameter(name="ids", in="query", @OA\Schema(type="string"), required=true, description="被勾选中的商品id", example="2,17,38"),
     *     @OA\Parameter(name="goodsNum_商品id", in="query", @OA\Schema(type="string"), required=true, description="被勾选中的商品本次提交申请的数量，{goodsNum_商品id:数量}", example="{goodsNum_59:1}"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema"),
     *       ),
     *     )
     * )
     */
    public function commit(){
        $m = new CM();
        $userId = model('app/index')->getUserId();
        $rs = $m->commit($userId);
        return json_encode($rs);
    }
    /**
     * @OA\Get(
     *     tags={"Orderservices"},
     *     path="/app/Orderservices/detail",
     *     summary="售后申请详情",
     *     description="售后申请详情",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="售后Id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object", 
     *                         @OA\Property(property="detail", type="object",
     *                             @OA\Property(property="id", type="integer", description="主键id"),
     *                             @OA\Property(property="orderId", type="integer", description="订单id"),
	 * 							   @OA\Property(property="orderNo", type="string", description="订单号"),
	 * 							   @OA\Property(property="goodsServiceType", type="string", description="售后申请类型 0：退款退货 1：退款 2：换货"),
	 * 							   @OA\Property(property="serviceType", type="string", description="退换货类型,数据由基础数据类型里取"),
	 * 							   @OA\Property(property="serviceRemark", type="string", description="退换货原因"),
	 * 							   @OA\Property(property="serviceAnnex", type="string", description="附件"),
	 * 							   @OA\Property(property="refundScore", type="integer", description="本次申请可退还的积分"),
	 * 							   @OA\Property(property="useScoreMoney", type="number", description="使用的积分可抵扣金额"),
	 * 							   @OA\Property(property="getScoreMoney", type="number", description="获得的积分可抵扣金额"),
	 * 							   @OA\Property(property="refundMoney", type="number", description="申请退款的金额"),
	 * 							   @OA\Property(property="refundableMoney", type="number", description="售后单可退款金额"),
	 * 							   @OA\Property(property="isShopAgree", type="integer", description="商家是否同意，1:同意 0:不同意"),
	 * 							   @OA\Property(property="disagreeRemark", type="string", description="不同意原因"),
	 * 							   @OA\Property(property="userAddressId", type="string", description="用户收货地址id"),
	 * 							   @OA\Property(property="areaId", type="string", description="地区id"),
	 * 							   @OA\Property(property="areaIdPath", type="string", description="地区ID值"),
	 * 							   @OA\Property(property="userName", type="string", description="用户收货人"),
	 * 							   @OA\Property(property="userAddress", type="string", description="用户详细收货地址"),
	 * 							   @OA\Property(property="userPhone", type="string", description="用户收货电话"),
	 * 							   @OA\Property(property="shopAreaId", type="string", description="商家收货地区ID"),
	 * 							   @OA\Property(property="shopAreaIdPath", type="string", description="商家收货地区ID值"),
	 * 							   @OA\Property(property="shopAddress", type="string", description="商家详细收货地址"),
	 * 							   @OA\Property(property="shopPhone", type="string", description="商家收货电话"),
	 * 							   @OA\Property(property="isUserSendGoods", type="string", description="是有是否已发货，0：未发货 1：已发货"),
	 * 							   @OA\Property(property="expressType", type="string", description="用户发货方式，0：无需物流  1：快递"),
	 * 							   @OA\Property(property="expressId", type="string", description="买家快递ID"),
	 * 							   @OA\Property(property="expressNo", type="string", description="买家物流单号"),
	 * 							   @OA\Property(property="isShopAccept", type="string", description="商家是否收到货 -1：拒收  0：未收货  1：已收货"),
	 * 							   @OA\Property(property="shopRejectType", type="string", description="商家拒收类型,数据由基础数据类型里取"),
	 * 							   @OA\Property(property="shopRejectOther", type="string", description="商家拒收原因,选择“其他”的时候填写文字"),
	 * 							   @OA\Property(property="shopRejectImg", type="string", description="拒收时的货物图片"),
	 * 							   @OA\Property(property="isShopSend", type="string", description="商家是否发货 0：未发货 1：已发货"),
	 * 							   @OA\Property(property="shopExpressType", type="string", description="商家发货方式，0:无需物流  1:快递"),
	 * 							   @OA\Property(property="shopExpressId", type="string", description="商家快递ID"),
	 * 							   @OA\Property(property="shopExpressNo", type="string", description="商家快递单号"),
	 * 							   @OA\Property(property="isUserAccept", type="string", description="用户收货状态，-1：拒收 0：未收货  1：已收货"),
	 * 							   @OA\Property(property="userRejectType", type="string", description="用户拒收原因,数据由基础数据类型里取"),
	 * 							   @OA\Property(property="userRejectOther", type="string", description="用户拒收原因,选择“其他”的时候填写文字"),
     * 							   @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
	 * 							   @OA\Property(property="isClose", type="string", description="售后单是否已关闭，1:是 0:否"),
	 * 							   @OA\Property(property="serviceStatus", type="string", description="状态备注：0：待商家审核  1：等待用户发货 2：商家等待收货 3：商家确认收货  4：等待商家发货  5：确认收货  6：完成退款/退货"),
	 * 							   @OA\Property(property="shopAcceptExpireTime", type="string", description="商家受理期限"),
	 * 							   @OA\Property(property="userSendExpireTime", type="string", description="用户发货期限"),
	 * 							   @OA\Property(property="shopReceiveExpireTime", type="string", description="商家收货期限"),
	 * 							   @OA\Property(property="shopName", type="string", description="店铺名称"),
	 * 							   @OA\Property(property="glists", type="array", description="订单下的商品",
	 *  						       @OA\Items(
	 *  							       @OA\Property(property="serviceId", type="string", description="售后表主键id"),
	 *  							       @OA\Property(property="id", type="string", description="主键表id"),
	 *  							       @OA\Property(property="orderId", type="integer", description="订单id"),
	 *  							       @OA\Property(property="goodsId", type="integer", description="商品id"),
	 *  							       @OA\Property(property="goodsNum", type="string", description="购买数量"),
	 *  							       @OA\Property(property="goodsPrice", type="number", description="商品价格"),
	 *  							       @OA\Property(property="goodsSpecId", type="string", description="商品规格id"),
	 *  							       @OA\Property(property="goodsSpecNames", type="string", description="商品规格名称"),
	 *  							       @OA\Property(property="goodsName", type="string", description="商品名称"),
	 *  							       @OA\Property(property="goodsImg", type="string", description="商品图片"),
	 *  							       @OA\Property(property="extraJson", type="string", description="虚拟商品相关"),
	 *  							       @OA\Property(property="goodsType", type="string", description="商品类型，1:虚拟商品 0:实物商品"),
	 *  							       @OA\Property(property="commissionRate", type="string", description="商品佣金比率"),
	 *  							       @OA\Property(property="goodsCode", type="string", description="商品标记,为'gift'时表示为赠品"),
	 *  							       @OA\Property(property="promotionJson", type="string", description="优惠信息"),
	 *  							       @OA\Property(property="couponVal", type="string", description="优惠券分摊到的金额"),
	 *  							       @OA\Property(property="rewardVal", type="string", description="满就送分摊到的金额"),
	 *  							       @OA\Property(property="useScoreVal", type="string", description="商品使用的积分"),
	 *  							       @OA\Property(property="scoreMoney", type="string", description="商品使用的积分抵扣金额"),
	 *  							       @OA\Property(property="getScoreVal", type="string", description="购买该商品获得的积分数"),
	 *  							       @OA\Property(property="orderGoodscommission", type="string", description="订单商品佣金"),
	 *  							       @OA\Property(property="getScoreMoney", type="string", description="获得的积分数可抵扣的金额"),
	 *  							       @OA\Property(property="commission", type="string", description="商品佣金"),
	 *  					          )
	 *  						),
	 *  						@OA\Property(property="statusText", type="string", description="售后单状态文字", example="售后已关闭"),
	 *  						@OA\Property(property="goodsServiceTypeText", type="string", description="售后单类型文字", example="退款退货"),
	 *  						@OA\Property(property="serviceTypeText", type="string", description="售后单申请理由文字", example="拍错/不喜欢/效果差"),
     *                          @OA\Property(property="log", type="array", description="日志数组", 
     *                              @OA\Items(
     *                                  @OA\Property(property="logId", type="integer", description="售后日志表主键id"),
     *                                  @OA\Property(property="orderId", type="string", description="订单id"),
     *                                  @OA\Property(property="serviceId", type="string", description="售后表主键id"),
     *                                  @OA\Property(property="logContent", type="string", description="日志内容"),
     *                                  @OA\Property(property="logTargetId", type="string", description="日志对象id"),
     *                                  @OA\Property(property="logType", type="string", description="logType，0:用户操作生成的日志 1:商家操作生成的日志"),
     *                                  @OA\Property(property="logTime", type="string", description="日志生成时间"),
     *                                  @OA\Property(property="avatar", type="string", description="操作者头像"),
     *                                  @OA\Property(property="nickname", type="string", description="操作者名称"),
     *                              )
     *                          ),
     *                         ),
     *                     ),
     *                  )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    public function detail(){
        $m = new CM();
        $userId = model('app/index')->getUserId();
        $detail = $m->getDetail(0, $userId);
        $log = $m->getLog($userId);
        foreach($log as $k=>$v){
            $log[$k]['avatar'] = WSTUserPhoto($v['avatar'], 1);
        }
        $rs = ['detail'=>$detail,'log'=>$log];
        return json_encode(WSTReturn('ok', 1, $rs));
    }
    /**
     * @OA\Get(
     *     tags={"Orderservices"},
     *     path="/app/Orderservices/sendPage",
     *     summary="售后申请用户发货页",
     *     description="售后申请用户发货页",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="售后Id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="express", type="array", 
	 *						      @OA\Items(
	 *							      @OA\Property(property="expressId", type="string", description="快递id"),
	 *							      @OA\Property(property="expressName", type="string", description="快递名称"),
	 *							      @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
	 *							      @OA\Property(property="expressCode", type="string", description="快递代码"),
	 *						      )
	 *						  ),
     *                     ),
     *                  )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    public function sendPage(){
        $m = new CM();
        $userId = model('app/index')->getUserId();
        $detail = $m->getDetail(0, $userId);
        $express = model('Express')->listQuery();
        $rs = ['express'=>$express];
        return json_encode(WSTReturn('ok', 1, $rs));
    }
    /**
     * @OA\Post(
     *     tags={"Orderservices"},
     *     path="/app/Orderservices/userExpress",
     *     summary="售后申请用户发货",
     *     description="售后申请用户发货",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="expressType", in="query", @OA\Schema(type="integer"), required=true, description="用户发货方式，0：无需物流  1：快递"),
     *     @OA\Parameter(name="expressId", in="query", @OA\Schema(type="integer"), required=true, description="快递id"),
     *     @OA\Parameter(name="expressNo", in="query", @OA\Schema(type="string"), required=true, description="快递单号"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema"),
     *       ),
     *     )
     * )
     */
    public function userExpress(){
        $m = new CM();
        $userId = model('app/index')->getUserId();
        $rs = $m->userExpress($userId);
        return json_encode($rs);
    }
    /**
     * @OA\Post(
     *     tags={"Orderservices"},
     *     path="/app/Orderservices/userReceive",
     *     summary="售后申请用户确收货(拒收)",
     *     description="售后申请用户确收货(拒收)",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="售后表主键id"),
     *     @OA\Parameter(name="isUserAccept", in="query", @OA\Schema(type="integer"), required=true, description="是否收货，1:是 0:拒收"),
     *     @OA\Parameter(name="userRejectType", in="query", @OA\Schema(type="integer"), required=true, description="拒收类型id"),
     *     @OA\Parameter(name="userRejectOther", in="query", @OA\Schema(type="string"), required=true, description="拒收说明"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data", type="object", 
     *                          @OA\Property(property="1", type="object", description="下标",
     *                             @OA\Property(property="id", type="integer", description="主键id"),
     *                             @OA\Property(property="catId", type="integer", description="所属类型id"),
     *                             @OA\Property(property="dataName", type="string", description="类型名称"),
     *                             @OA\Property(property="dataVal", type="string", description="类型值"),
     *                             @OA\Property(property="dataSort", type="integer", description="排序号"),
     *                             @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
     *                          ),
     *                          @OA\Property(property="2", type="object", description="下标",)
     *                      ),
     *                 )
     *             }
     *         )
     *       ),
     *     ),
     * )
     */
    public function userReceive(){
        $m = new CM();
        $userId = model('app/index')->getUserId();
        $rs = $m->userReceive($userId);
        return json_encode($rs);
    }
    /**
     * @OA\Get(
     *     tags={"Orderservices"},
     *     path="/app/Orderservices/getRejectReason",
     *     summary="获取拒收理由",
     *     description="获取拒收理由",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema"),
     *       ),
     *     )
     * )
     */
    public function getRejectReason(){
        $data = WSTDatas('ORDER_REJECT');
        return json_encode(WSTReturn('ok',1,$data));
    }
    /**
     * @OA\Get(
     *     tags={"Orderservices"},
     *     path="/app/Orderservices/getRefundableMoney",
     *     summary="获取当前售后单可退款金额",
     *     description="获取当前售后单可退款金额",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="orderId", in="query", @OA\Schema(type="integer"), required=true, description="订单Id"),
     *     @OA\Parameter(name="ids", in="query", @OA\Schema(type="string"), required=true, description="被勾选中的商品id", example="2,17,38"),
     *     @OA\Parameter(name="goodsNum_商品id", in="query", @OA\Schema(type="string"), required=true, description="被勾选中的商品本次提交申请的数量，{goodsNum_商品id:数量}", example="{goodsNum_59:1}"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data", type="object", 
     *                          @OA\Property(property="totalMoney", type="number", description="总共可退金额"),
     *                          @OA\Property(property="totalScore", type="integer", description="总共可退积分"),
     *                          @OA\Property(property="useScoreMoney", type="string", description="使用的积分抵扣金额"),
     *                          @OA\Property(property="getScoreMoney", type="string", description="获得的积分"),
     *                          @OA\Property(property="totalGetScore", type="integer", description="总共获得的积分"),
     *                      ),
     *                 )
     *             }
     *         )
     *       ),
     *     ),
     * )
     */
    public function getRefundableMoney(){
        $m = new CM();
        $userId = model('app/index')->getUserId();
        $rs = $m->getRefundableMoney($userId);
        return json_encode($rs);
    }
}
