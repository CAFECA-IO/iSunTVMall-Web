<?php
namespace wstmart\app\controller;
use wstmart\app\model\Carts as M;
use wstmart\common\model\UserAddress;
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
 * 购物车控制器
 */
class Carts extends Base{
	// 前置方法执行列表
	protected $beforeActionList = ['checkAuth'];
	/**
     * @OA\Get(
     *     tags={"Carts"},
     *     path="/app/carts/getStores",
     *     summary="自提点列表",
     *     description="获取app端自提点列表",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="shopId", in="query", @OA\Schema(type="integer"), required=true, description="店铺id", example="1"),
	 *     @OA\Parameter(name="addressId", in="query", @OA\Schema(type="integer"), required=true, description="用户地址id", example="10"),
     *     @OA\Response(
     *      response="200",
     *      description="返回自提点列表",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="storeId",type="integer",description="自提点id",),
     *                          @OA\Property(property="shopId",type="integer",description="店铺id",),
     *                          @OA\Property(property="areaIdPath", type="string", description="地区Id字符串", example="28_344_2931_"),
     *                          @OA\Property(property="storeName",type="string",description="自提点名称",),
     *                          @OA\Property(property="storeTel",type="string",description="自提点联系电话",),
     *                          @OA\Property(property="storeAddress",type="string",description="自提点地址",),
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     ),
	 *     @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function getStores(){
        $userId = (int)model('app/index')->getUserId();
        $rs = model("common/Stores")->shopStores($userId);
    	return json_encode(WSTReturn("", 1,$rs));
	}


	/**
     * @OA\Get(
     *     tags={"Carts"},
     *     path="/app/carts/index",
     *     summary="查看购物车列表",
     *     description="获取app端查看购物车列表",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回查看购物车列表",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="carts",type="object",description="购物车数据",
	 * 								@OA\Property(property="1", type="object", description="店铺id",
	 * 									@OA\Property(property="goodsMoney", type="number", description="商品价格", example="32.5"),
	 * 									@OA\Property(property="isFreeShipping", type="integer", description="是否包邮，1->包邮 0->不包邮", example="0"),
	 * 									@OA\Property(property="promotion", type="string", description="促销活动信息", example="待补充"),
	 * 									@OA\Property(property="promotionMoney", type="number", description="本次购物店铺总共要优惠的金额，开启`满就送`插件时才存在该字段", example="1"),
	 * 									@OA\Property(property="shopId", type="integer", description="店铺id", example="1"),
	 * 									@OA\Property(property="shopName", type="string", description="店铺名称", example="WSTMart自营超市"),
	 * 									@OA\Property(property="shopQQ", type="number", description="店铺qq", example=""),
	 * 									@OA\Property(property="userId", type="integer", description="用户id", example="1"),
	 * 									@OA\Property(property="shopWangWang", type="string", description="店铺旺旺", example=""),
	 * 									@OA\Property(property="list", ref="#/components/schemas/carts_goods_response_schema"),
	 * 								)
     *                           ),
	 * 							 @OA\Property(property="goodsTotalMoney",type="number",description="店铺商品商品总价格"),
	 * 							 @OA\Property(property="goodsTotalNum",type="integer",description="商品总个数"),
	 * 							 @OA\Property(property="promotionMoney",type="number",description="本次购物总共要优惠的金额，开启`满就送`插件时才存在该字段", example="0")
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     ),
	 *     @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function index(){
		$m = new M();
        $userId = (int)model('app/index')->getUserId();
		$carts = $m->getCarts(false,$userId);
		if(!empty($carts['carts'])){
			return json_encode(WSTReturn('ok',1,$carts));
		}
		return json_encode(WSTReturn(lang('empty_carts'),-1));
	}
	/**
     * @OA\Post(
     *     tags={"Carts"},
     *     path="/app/carts/addCart",
     *     summary="加入购物车",
     *     description="加入购物车",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 * 	   @OA\Parameter(ref="#/components/parameters/goodsId"),
	 * 	   @OA\Parameter(name="goodsSpecId", in="query", @OA\Schema(type="integer"), required=false, description="商品规格id", example="165"),
	 * 	   @OA\Parameter(name="buyNum", in="query", @OA\Schema(type="integer"), required=true, description="购买数量", example="1"),
     *     @OA\Response(
     *      response="200",
     *      description="返回操作结果",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema")
     *       ),
     *     ),
	 * 	   @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function addCart(){
		$m = new M();
        $userId = (int)model('app/index')->getUserId();
		$rs = $m->addCart($userId);
		return json_encode($rs);
	}
	/**
     * @OA\Post(
     *     tags={"Carts"},
     *     path="/app/carts/changeCartGoods",
     *     summary="修改购物车商品状态",
     *     description="修改购物车商品选中状态、购买数量",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 * 	   @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=false, description="购物车Id", example="7"),
	 * 	   @OA\Parameter(name="buyNum", in="query", @OA\Schema(type="integer"), required=true, description="购买数量", example="1"),
	 * 	   @OA\Parameter(name="isCheck", in="query", @OA\Schema(type="integer"), required=true, description="是否选中，1:选中 0:取消选中", example="1"),
     *     @OA\Response(
     *      response="200",
     *      description="返回操作结果",
     *      @OA\MediaType(
     *         mediaType="application/json",
	 * 		   @OA\Schema(
	 *     		   allOf={
     *             		@OA\Schema(ref="#/components/schemas/response_schema"),
	 *     				@OA\Schema(
	 *     				    @OA\Property(property="data",
	 *     				        @OA\Property(property="goodsTotalMoney",type="number",description="当前购物车商品总价格"),
	 *     					)
	 *     				)
	 *     		   }
	 * 		   )
     *       ),
     *     ),
	 * 	   @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function changeCartGoods(){
		$m = new M();
        $userId = (int)model('app/index')->getUserId();
		$rs = $m->changeCartGoods($userId);
		return json_encode($rs);
	}
	/**
     * @OA\Post(
     *     tags={"Carts"},
     *     path="/app/carts/batchSetIsCheck",
     *     summary="批量修改购物车商品选中状态",
     *     description="批量修改购物车商品选中状态",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 * 	   @OA\Parameter(name="ids", in="query", @OA\Schema(type="string"), required=true, description="购物车Id拼凑的字符串", example="7,8,9"),
	 * 	   @OA\Parameter(name="isCheck", in="query", @OA\Schema(type="integer"), required=true, description="是否选中，1:选中 0:取消选中", example="1"),
     *     @OA\Response(
     *      response="200",
     *      description="返回操作结果",
     *      @OA\MediaType(
     *         mediaType="application/json",
	 * 		   @OA\Schema(ref="#/components/schemas/response_schema")
     *       ),
     *     ),
	 * 	   @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function batchSetIsCheck(){
		$m = new M();
        $userId = (int)model('app/index')->getUserId();
		$rs = $m->batchChangeCartGoods($userId);
		return json_encode($rs);
	}
	/**
     * @OA\Post(
     *     tags={"Carts"},
     *     path="/app/carts/delCart",
     *     summary="删除购物车里的商品",
     *     description="删除购物车里的商品",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 * 	   @OA\Parameter(name="id", in="query", @OA\Schema(type="string"), required=true, description="购物车Id拼凑的字符串", example="7,8,9"),
     *     @OA\Response(
     *      response="200",
     *      description="返回操作结果",
     *      @OA\MediaType(
     *         mediaType="application/json",
	 * 		   @OA\Schema(ref="#/components/schemas/response_schema")
     *       ),
     *     ),
	 * 	   @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function delCart(){
		$m = new M();
        $userId = (int)model('app/index')->getUserId();
		$rs= $m->delCart($userId);
		return json_encode($rs);
	}
	/**
     * @OA\Post(
     *     tags={"Carts"},
     *     path="/app/carts/getCartMoney",
     *     summary="【实物商品】计算运费、积分和总商品价格",
     *     description="【实物商品】计算运费、积分和总商品价格",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="areaId2", in="query", @OA\Schema(type="integer"), required=true, description="地区id", example="5"),
	 *     @OA\Parameter(name="deliverType", in="query", @OA\Schema(type="integer"), required=true, description="配送方式，0:快递运输 1:自提", example="0"),
	 *     @OA\Parameter(name="isUseScore", in="query", @OA\Schema(type="integer"), required=true, description="是否使用积分支付，0:否 1:是", example="0"),
	 *     @OA\Parameter(name="useScore", in="query", @OA\Schema(type="integer"), required=true, description="使用的积分数", example="100"),
	 *     @OA\Parameter(name="couponIds", in="query", @OA\Schema(type="string"), required=true, description="存在`优惠券`插件时才会出现该字段，用户选中的优惠券id,例如：2:10,1:0,3:0 表示 店铺id:优惠券id", example="2:10"),
     *     @OA\Response(
     *      response="200",
     *      description="返回操作结果",
     *      @OA\MediaType(
     *         mediaType="application/json",
	 * 		   @OA\Schema(
	 *     		   allOf={
     *             		@OA\Schema(ref="#/components/schemas/response_schema"),
	 *     				@OA\Schema(
	 *     				    @OA\Property(property="data",
	 *     				        @OA\Property(property="shops", type="object", description="各店铺的运费、商品价格、订单可获得积分",
	 * 								@OA\Property(property="1", type="object", description="店铺id",
	 * 								    @OA\Property(property="freight", type="number", description="运费", example="5"),
	 * 								    @OA\Property(property="oldGoodsMoney", type="number", description="店铺总价格(包含运费)【原价】", example="876"),
	 * 								    @OA\Property(property="goodsMoney", type="number", description="店铺总价格(包含运费)【优惠活动后】", example="876"),
	 * 								    @OA\Property(property="orderScore", type="number", description="店铺当前订单可获得的积分", example="8"),
	 * 								)
	 * 							),
	 * 							@OA\Property(property="totalMoney", type="number", description="总价格(包含运费)", example="876"),
	 * 							@OA\Property(property="totalGoodsMoney", type="number", description="商品总价格", example="876"),
	 * 							@OA\Property(property="orderScore", type="number", description="订单可获得的积分"),
	 * 							@OA\Property(property="maxScore", type="number", description="最大使用积分"),
	 * 							@OA\Property(property="maxScoreMoney", type="number", description="积分最大可抵扣金额"),
	 * 							@OA\Property(property="useScore", type="number", description="使用积分数"),
	 * 							@OA\Property(property="scoreMoney", type="number", description="可用积分"),
	 * 							@OA\Property(property="realTotalMoney", type="number", description="实际订单待支付金"),
	 *     					)
	 *     				)
	 *     		   }
	 * 		   )
     *       ),
     *     ),
	 * 	   @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function getCartMoney(){
		$m = new M();
        $userId = (int)model('app/index')->getUserId();
		$data = $m->getCartMoney($userId);
		return json_encode($data);
	}
	/**
     * @OA\Post(
     *     tags={"Carts"},
     *     path="/app/carts/getQuickCartMoney",
     *     summary="【虚拟商品】计算运费、积分和总商品价格",
     *     description="【虚拟商品】计算运费、积分和总商品价格",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="isUseScore", in="query", @OA\Schema(type="integer"), required=true, description="是否使用积分支付，0:否 1:是", example="0"),
	 *     @OA\Parameter(name="useScore", in="query", @OA\Schema(type="integer"), required=true, description="使用的积分数", example="100"),
	 *     @OA\Parameter(name="couponIds", in="query", @OA\Schema(type="string"), required=true, description="存在`优惠券`插件时才会出现该字段，用户选中的优惠券id,例如：2:10,1:0,3:0 表示 店铺id:优惠券id", example="2:10"),
     *     @OA\Response(
     *      response="200",
     *      description="返回操作结果",
     *      @OA\MediaType(
     *         mediaType="application/json",
	 * 		   @OA\Schema(
	 *     		   allOf={
     *             		@OA\Schema(ref="#/components/schemas/response_schema"),
	 *     				@OA\Schema(
	 *     				    @OA\Property(property="data",
	 *     				        @OA\Property(property="shops", type="object", description="各店铺的运费、商品价格、订单可获得积分",
	 * 								@OA\Property(property="1", type="object", description="店铺id",
	 * 								    @OA\Property(property="freight", type="number", description="运费", example="5"),
	 * 								    @OA\Property(property="oldGoodsMoney", type="number", description="店铺总价格(包含运费)【原价】", example="876"),
	 * 								    @OA\Property(property="goodsMoney", type="number", description="店铺总价格(包含运费)【优惠活动后】", example="876"),
	 * 								    @OA\Property(property="orderScore", type="number", description="店铺当前订单可获得的积分", example="8"),
	 * 								)
	 * 							),
	 * 							@OA\Property(property="totalMoney", type="number", description="总价格(包含运费)", example="876"),
	 * 							@OA\Property(property="totalGoodsMoney", type="number", description="商品总价格", example="876"),
	 * 							@OA\Property(property="orderScore", type="number", description="订单可获得的积分"),
	 * 							@OA\Property(property="maxScore", type="number", description="最大使用积分"),
	 * 							@OA\Property(property="maxScoreMoney", type="number", description="积分最大可抵扣金额"),
	 * 							@OA\Property(property="useScore", type="number", description="使用积分数"),
	 * 							@OA\Property(property="scoreMoney", type="number", description="可用积分"),
	 * 							@OA\Property(property="realTotalMoney", type="number", description="实际订单待支付金"),
	 *     					)
	 *     				)
	 *     		   }
	 * 		   )
     *       ),
     *     ),
	 * 	   @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function getQuickCartMoney(){
		$m = new M();
        $userId = (int)model('app/index')->getUserId();
		$data = $m->getQuickCartMoney($userId);
		return json_encode($data);
	}
	/**
     * @OA\Post(
     *     tags={"Carts"},
     *     path="/app/carts/settlement",
     *     summary="【实物商品】结算页面数据",
     *     description="【实物商品】结算页面数据",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="addressId", in="query", @OA\Schema(type="integer"), required=true, description="用户地址Id", example="5"),
     *     @OA\Response(
     *      response="200",
     *      description="返回查看购物车列表",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="carts",type="object",description="购物车数据",
	 * 								@OA\Property(property="1", type="object", description="店铺id",
	 * 									@OA\Property(property="goodsMoney", type="number", description="商品价格", example="32.5"),
	 * 									@OA\Property(property="isFreeShipping", type="integer", description="是否包邮，1->包邮 0->不包邮", example="0"),
	 * 									@OA\Property(property="promotion", type="string", description="促销活动信息", example="待补充"),
	 * 									@OA\Property(property="promotionMoney", type="number", description="本次购物店铺总共要优惠的金额，开启`满就送`插件时才存在该字段", example="1"),
	 * 									@OA\Property(property="shopId", type="integer", description="店铺id", example="1"),
	 * 									@OA\Property(property="shopName", type="string", description="店铺名称", example="WSTMart自营超市"),
	 * 									@OA\Property(property="shopQQ", type="number", description="店铺qq", example=""),
	 * 									@OA\Property(property="userId", type="integer", description="用户id", example="1"),
	 * 									@OA\Property(property="shopWangWang", type="string", description="店铺旺旺", example=""),
	 * 									@OA\Property(property="list", ref="#/components/schemas/carts_goods_response_schema"),
	 * 									@OA\Property(property="coupons", ref="#/components/schemas/coupons_response_schema"),
	 * 								)
     *                          ),
	 *   						@OA\Property(property="goodsTotalMoney",type="number",description="店铺商品商品总价格"),
	 *   						@OA\Property(property="goodsTotalNum",type="integer",description="商品总个数"),
	 *   						@OA\Property(property="promotionMoney",type="number",description="本次购物总共要优惠的金额，开启`满就送`插件时才存在该字段", example="0"),
	 *   						@OA\Property(property="userAddress", ref="#/components/schemas/user_address_response_schema"),
	 * 							@OA\Property(property="payments", ref="#/components/schemas/payments_response_schema"),
	 * 							@OA\Property(property="userOrderScore",type="number",description="用户可用积分"),
	 * 							@OA\Property(property="userOrderMoney",type="number",description="积分可抵扣的金额"),
	 * 							@OA\Property(property="isOpenScorePay",type="number",description="后台是否开启积分支付，1:开启 0:未开启"),
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     ),
	 *     @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function settlement(){
		$m = new M();
		//获取一个用户地址
		$addressId = (int)input('addressId');
		$ua = new UserAddress();
		$userId = (int)model('app/index')->getUserId();
		if($addressId>0){
			$userAddress = $ua->getById($addressId, $userId);
			if(empty($userAddress))$userAddress = $ua->getDefaultAddress($userId);
		}else{
			$userAddress = $ua->getDefaultAddress($userId);
		}
		//获取支付方式
		$pa = new Payments();
		$payments = $pa->getByGroup('4', -1, true);
		//获取已选的购物车商品
		$carts = $m->getCarts(true,$userId);
		if(empty($carts['carts']))return json_encode(WSTReturn(lang('chose_goods'),-1));
		$carts['userAddress'] = $userAddress;

		hook("mobileControllerCartsSettlement",["carts"=>$carts,"payments"=>&$payments]);

		$carts['payments'] = $payments;
		//计算可用积分和金额
          $orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney']-$carts['promotionMoney'],$userId,-1);
          $carts['userOrderScore'] = $orderUsableScore['score'];
          $carts['userOrderMoney'] = $orderUsableScore['money'];
		// 是否开启积分支付
		$carts['isOpenScorePay'] = WSTConf('CONF.isOpenScorePay');
		return json_encode(WSTReturn(lang('success_msg'),1,$carts));
	}
	/**
     * @OA\Post(
     *     tags={"Carts"},
     *     path="/app/carts/quickSettlement",
     *     summary="【虚拟商品】结算页面数据",
     *     description="【虚拟商品】结算页面数据",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回查看购物车列表",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="carts",type="object",description="购物车数据",
	 * 								@OA\Property(property="1", type="object", description="店铺id",
	 * 									@OA\Property(property="goodsMoney", type="number", description="商品价格", example="32.5"),
	 * 									@OA\Property(property="isFreeShipping", type="integer", description="是否包邮，1->包邮 0->不包邮", example="0"),
	 * 									@OA\Property(property="promotion", type="string", description="促销活动信息", example="待补充"),
	 * 									@OA\Property(property="promotionMoney", type="number", description="本次购物店铺总共要优惠的金额，开启`满就送`插件时才存在该字段", example="1"),
	 * 									@OA\Property(property="shopId", type="integer", description="店铺id", example="1"),
	 * 									@OA\Property(property="shopName", type="string", description="店铺名称", example="WSTMart自营超市"),
	 * 									@OA\Property(property="shopQQ", type="number", description="店铺qq", example=""),
	 * 									@OA\Property(property="userId", type="integer", description="用户id", example="1"),
	 * 									@OA\Property(property="shopWangWang", type="string", description="店铺旺旺", example=""),
	 * 									@OA\Property(property="list", ref="#/components/schemas/carts_goods_response_schema"),
	 * 									@OA\Property(property="coupons", ref="#/components/schemas/coupons_response_schema"),
	 * 								)
     *                          ),
	 *   						@OA\Property(property="goodsTotalMoney",type="number",description="店铺商品商品总价格"),
	 *   						@OA\Property(property="goodsTotalNum",type="integer",description="商品总个数"),
	 *   						@OA\Property(property="promotionMoney",type="number",description="本次购物总共要优惠的金额，开启`满就送`插件时才存在该字段", example="0"),
	 * 							@OA\Property(property="payments", ref="#/components/schemas/payments_response_schema"),
	 * 							@OA\Property(property="userOrderScore",type="number",description="用户可用积分"),
	 * 							@OA\Property(property="userOrderMoney",type="number",description="积分可抵扣的金额"),
	 * 							@OA\Property(property="isOpenScorePay",type="number",description="后台是否开启积分支付，1:开启 0:未开启"),
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     ),
	 *     @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function quickSettlement(){
		$m = new M();
		$userId = (int)model('app/index')->getUserId();
		//获取已选的购物车商品
		$carts = $m->getQuickCarts($userId);
		//获取支付方式
		$pa = new Payments();
		$payments = $pa->getByGroup('4', -1, true);
		$carts['payments'] = $payments;
          //计算可用积分
          $orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney']-$carts['promotionMoney'],$userId,-1);
		$carts['userOrderScore'] = $orderUsableScore['score'];
          $carts['userOrderMoney'] = $orderUsableScore['money'];
          // 是否开启积分支付
		$carts['isOpenScorePay'] = WSTConf('CONF.isOpenScorePay');
		return json_encode(WSTReturn(lang('success_msg'),1,$carts));
	}
	/**
     * @OA\Get(
     *     tags={"Carts"},
     *     path="/app/carts/getCartNum",
     *     summary="获取购物车数量",
     *     description="获取购物车数量",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回操作结果",
     *      @OA\MediaType(
     *         mediaType="application/json",
	 * 		   @OA\Schema(
	 *     		   allOf={
     *             		@OA\Schema(ref="#/components/schemas/response_schema"),
	 *     				@OA\Schema(
	 *     				    @OA\Property(property="data",
	 *     				        @OA\Property(property="cartNum",type="number",description="当前购物车数量"),
	 *     					)
	 *     				)
	 *     		   }
	 * 		   )
     *       ),
     *     ),
	 * 	   @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function getCartNum(){
		$data = ['cartNum' => model('app/Carts')->getCartNum()];
		return json_encode(WSTReturn(lang('success_msg'), 1, $data));
	}

    /**
     * @OA\Post(
     *     tags={"Carts"},
     *     path="/app/carts/moveToFavorites",
     *     summary="将购物车里选择的商品移入我的关注",
     *     description="将购物车里选择的商品移入我的关注",
     * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="goodsIds", in="query", @OA\Schema(type="string"), required=true, description="商品的id拼凑的字符串", example="7,8,9"),
     *     @OA\Parameter(name="cartIds", in="query", @OA\Schema(type="string"), required=true, description="购物车记录的id拼凑的字符串", example="1,2,3"),
     *     @OA\Response(
     *      response="200",
     *      description="返回操作结果",
     *      @OA\MediaType(
     *         mediaType="application/json",
     * 		   @OA\Schema(ref="#/components/schemas/response_schema")
     *       ),
     *     ),
     * 	   @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
    public function moveToFavorites(){
        $userId = (int)model('app/index')->getUserId();
        $m = new M();
        $rs= $m->moveToFavorites($userId);
        return json_encode($rs);
    }

    /**
     * @OA\Post(
     *     tags={"Carts"},
     *     path="/app/carts/getGuess",
     *     summary="获取购物车页的推荐商品",
     *     description="获取购物车页的推荐商品",
     * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="goodsIds", in="query", @OA\Schema(type="string"), required=true, description="用户浏览过商品的id拼凑的字符串", example="7,8,9"),
     *     @OA\Response(
     *      response="200",
     *      description="返回操作结果",
     *      @OA\MediaType(
     *         mediaType="application/json",
     * 		   @OA\Schema(
     *     		   allOf={
     *             		@OA\Schema(ref="#/components/schemas/response_schema"),
     *                  @OA\Schema(
     *                      @OA\Property(property="data",type="array",description="推荐商品信息",
     *                           @OA\Items(
     *                               @OA\Property(property="goodsId", type="integer", description="商品id"),
     *                               @OA\Property(property="goodsImg", type="string", description="商品图片"),
     *                               @OA\Property(property="goodsName", type="string",description="商品名称"),
     *                               @OA\Property(property="isNew", type="integer", description="是否为设置为新品，1:是 0:否", example="1"),
     *                               @OA\Property(property="saleNum", type="integer",description="销量"),
     *                               @OA\Property(property="shopPrice, type="string",description="店铺价格"),
     *                           ),
     *                       ),
     *                 )
     *     		   }
     * 		   )
     *       ),
     *     )
     * )
     */
    public function getGuess(){
        $catId = (int)input('catId',0);
        $goodsIds = explode(',',input('goodsIds'));
        if(!empty($goodsIds))$goodsIds = array_unique($goodsIds);
        // 猜你喜欢10件商品
        $like = model('Tags')->getGuessLike($catId,10,$goodsIds);
        foreach($like as $k=>$v){
            // 删除无用字段
            unset($like[$k]['shopName']);
            unset($like[$k]['shopId']);
            unset($like[$k]['goodsSn']);
            unset($like[$k]['goodsStock']);
            unset($like[$k]['marketPrice']);
            unset($like[$k]['isSpec']);
            unset($like[$k]['appraiseNum']);
            unset($like[$k]['visitNum']);
            // 替换商品图片
            $like[$k]['goodsImg'] = WSTImg($v['goodsImg'],3);
            $like[$k]['goodsName'] = htmlspecialchars_decode($v['goodsName']);
        }
        $rs = [
            'goods'=>$like
        ];
        return json_encode(WSTReturn('ok',1,$rs));
    }
}

