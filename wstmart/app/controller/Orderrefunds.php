<?php
namespace wstmart\app\controller;
use wstmart\common\model\OrderRefunds as M;
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
 * 订单退款控制器
 */
class Orderrefunds extends Base{
	/**
     * @OA\Post(
     *     tags={"Orderrefunds"},
     *     path="/app/Orderrefunds/refund",
     *     summary="用户申请退款",
     *     description="用户申请退款",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Parameter(name="reason", in="query", @OA\Schema(type="integer"), required=true, description="退款理由Id"),
     *     @OA\Parameter(name="content", in="query", @OA\Schema(type="string"), required=true, description="退款说明"),
     *     @OA\Parameter(name="money", in="query", @OA\Schema(type="number"), required=true, description="退款金额"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
	public function refund(){
		$m = new M();
		$userId = (int)model('app/index')->getUserId();
		$rs = $m->refund($userId);
		return json_encode($rs);
	}
	/**
     * @OA\Post(
     *     tags={"Orderrefunds"},
     *     path="/app/Orderrefunds/shopRefund",
     *     summary="商家处理是否同意退款",
     *     description="商家处理是否同意退款",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="退款表id"),
     *     @OA\Parameter(name="refundStatus", in="query", @OA\Schema(type="integer"), required=true, description="是否同意退款，1:是 0:否"),
     *     @OA\Parameter(name="content", in="query", @OA\Schema(type="string"), required=true, description="拒绝退款说明"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
	public function shopRefund(){
		$m = new M();
        $userId = (int)model('app/index')->getUserId();
        $shopId = (int)model('app/shops')->getShopId($userId);
		$rs = $m->shopRefund($shopId);
		return json_encode($rs);
	}
}
