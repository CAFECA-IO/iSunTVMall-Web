<?php
namespace wstmart\app\controller;
use wstmart\common\model\Orders as OM;
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
 * 余额控制器
 */
class Wallets extends Base{
	// 前置方法执行列表
	protected $beforeActionList = [
			'checkAuth'
	];
	/**
     * @OA\Get(
     *     tags={"Wallets"},
     *     path="/app/Wallets/payment",
     *     summary="余额支付页所需数据",
     *     description="余额支付页所需数据",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="isBatch", in="query", @OA\Schema(type="string"), required=true, description="1：批量支付 0：单订单支付"),
     *     @OA\Parameter(name="orderNo", in="query", @OA\Schema(type="string"), required=true, description="当isBatch为1时,传入orderunique；当isBatch为0时,传入orderNo"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
	 * 						   @OA\Property(property="orderunique", type="string", description="订单唯一流水号"),
     *                         @OA\Property(property="list",type="array",
     *                             @OA\Items(
	 * 							       @OA\Property(property="orderId", type="integer", description="订单id"),
	 * 							       @OA\Property(property="orderNo", type="string", description="订单号"),
	 * 							       @OA\Property(property="payType", type="string", description="支付方式 1:在线支付 0:货到付款"),
	 * 							       @OA\Property(property="needPay", type="number", description="需要支付的金额"),
	 * 							       @OA\Property(property="orderunique", type="string", description="订单唯一流水号"),
	 * 							       @OA\Property(property="deliverMoney", type="number", description="运费"),
	 * 							       @OA\Property(property="userName", type="string", description="收货人"),
	 * 							       @OA\Property(property="userPhone", type="string", description="收货人联系电话"),
	 * 							       @OA\Property(property="userAddress", type="string", description="收货地址"),
	 * 							   )
     *                        ),
	 * 						  @OA\Property(property="totalMoney", type="string", description="总价格"),
	 * 						  @OA\Property(property="goods",type="array",
	 * 							  @OA\Items(
	 *   						      @OA\Property(property="435",type="object", description="订单id",
	 *   							      @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
	 *   							      @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
	 *   							      @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
	 *   						          @OA\Property(property="goodsNum", type="integer", description="购买数"),
	 *   						          @OA\Property(property="goodsPrice", type="number", description="商品价格"),
	 *   						          @OA\Property(property="goodsSpecNames", type="number", description="规格名称"),
	 *   						          @OA\Property(property="goodsType", type="number", description="商品类型 1:虚拟商品 0:实物商品"),
	 *   						          @OA\Property(property="goodsCode", type="number", description="商品备注，为gift时表示为赠品"),
	 *   							  )
	 * 							  )
     *                        ),
	 * 						  @OA\Property(property="userMoney", type="string", description="用户钱包余额"),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
	public function payment(){
        $data = [];
        $data['orderNo'] = input('orderNo');
        $data['isBatch'] = (int)input('isBatch');
        $data['userId'] = model('index')->getUserId();
        //$this->assign('data',$data); //订单号、用户id、isBatch
		$m = new OM();
		$rs = $m->getOrderPayInfo($data);// needPay、payRand
		// 订单信息
		$list = $m->getByUnique($data['userId']);// 根据订单唯一流水号 获取订单信息

		// 删除无用字段
		unset($list['payments']);

		if(empty($rs)){
			return json_encode(WSTReturn(lang('duplicate_pay'),-1));
			// 判断获取的需要支付信息为空，则说明已支付.跳转订单列表
		}else{
			//获取用户钱包
			$user = model('users')->getFieldsById($data['userId'],'userMoney,payPwd');
			$list['userMoney'] = $user['userMoney'];// 用户钱包可用余额
            $payPwd = $user['payPwd'];
            $list['payPwd'] = empty($payPwd)?0:1;
	    }
	    return json_encode(WSTReturn(lang('success_msg'), 1, $list));die;
	}
	/**
     * @OA\Post(
     *     tags={"Wallets"},
     *     path="/app/Wallets/payByWallet",
     *     summary="执行余额支付",
     *     description="执行余额支付",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="isBatch", in="query", @OA\Schema(type="string"), required=true, description="1：批量支付 0：单订单支付"),
     *     @OA\Parameter(name="orderNo", in="query", @OA\Schema(type="string"), required=true, description="当isBatch为1时,传入orderunique；当isBatch为0时,传入orderNo"),
     *     @OA\Parameter(name="payPwd", in="query", @OA\Schema(type="string"), required=true, description="支付密码"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema"),
	 * 			)
     *     )
     * )
     */
	public function payByWallet(){
		$m = new OM();
		$userId = (int)model('index')->getUserId();
		return json_encode($m->payByWallet($userId));
	}
}
