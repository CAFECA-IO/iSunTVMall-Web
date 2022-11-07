<?php
namespace wstmart\app\controller;
use wstmart\common\model\CashDraws as M;
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
 * 提现记录控制器
 */
class Cashdraws extends Base{
    // 前置方法执行列表
    protected $beforeActionList = [
        'checkAuth',
    ];
    /**
     * @OA\Get(
     *     tags={"Cashdraws"},
     *     path="/app/cashdraws/pageQuery",
     *     summary="提现记录查询",
     *     description="提现记录查询",
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
     *                                @OA\Property(property="data",type="array",description="提现记录",
     *                                     @OA\Items(
     *                                          @OA\Property(property="cashId", type="string", description="提现id", example=""),
     *                                          @OA\Property(property="cashNo", type="string", description="提现号", example=""),
     *                                          @OA\Property(property="targetType", type="string", description="1: 用户2: 商家", example=""),
     *                                          @OA\Property(property="targetId", type="string", description="用户Id", example=""),
     *                                          @OA\Property(property="money", type="string", description="提现金额", example=""),
     *                                          @OA\Property(property="accType", type="string", description="1: 支付宝2：微信3: 银行卡", example=""),
     *                                          @OA\Property(property="accTargetName", type="string", description="所属银行", example=""),
     *                                          @OA\Property(property="accAreaName", type="string", description="广东省广州市天河区", example=""),
     *                                          @OA\Property(property="accNo", type="string", description="支付宝帐号/微信帐号/银行卡号", example=""),
     *                                          @OA\Property(property="accUser", type="string", description="账户所有人", example=""),
     *                                          @OA\Property(property="cashSatus", type="string", description="-1: 提现失败0: 待处理1: 提现成功", example=""),
     *                                          @OA\Property(property="cashRemarks", type="string", description="提现备注", example=""),
     *                                          @OA\Property(property="cashConfigId", type="string", description="提现设置对应的id", example=""),
     *                                          @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                                     ),
     *                                 ),
     *                             )
     *                         }
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
    public function pageQuery(){
        $userId = model('app/users')->getUserId();
        $m = new M();
        $data = $m->pageQuery(0,$userId);
        echo(json_encode(WSTReturn('success',1,$data)));die;
    }

    /**
     * @OA\Post(
     *     tags={"Cashdraws"},
     *     path="/app/cashdraws/drawMoney",
     *     summary="新增提现申请",
     *     description="新增提现申请",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="accId", in="query", @OA\Schema(type="integer"), required=true, description="提现账户id", example="22"),
     *     @OA\Parameter(name="money", in="query", @OA\Schema(type="number"), required=true, description="提现金额", example="10"),
     *     @OA\Parameter(name="payPwd", in="query", @OA\Schema(type="string"), required=true, description="支付密码", example=""),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema")
     *       ),
     *     ),
	 *     @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
    public function drawMoney(){
        $m = new M();
        $userId = model('app/users')->getUserId();
    	$rs = $m->drawMoney($userId);
        return json_encode($rs);
    }
}
