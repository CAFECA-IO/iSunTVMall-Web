<?php
namespace wstmart\app\controller;
use wstmart\common\model\UserScores as CM;
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
 * 积分控制器
 */
class Userscores extends Base{
    // 前置方法执行列表
   	protected $beforeActionList = ['checkAuth'];
    /**
     * @OA\Get(
     *     tags={"Userscores"},
     *     path="/app/Userscores/index",
     *     summary="积分数据",
     *     description="积分数据",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
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
     *                         @OA\Property(property="userScore",type="integer", description="用户积分数"),
     *                         @OA\Property(property="userTotalScore",type="integer", description="用户历史总积分"),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
	public function index(){
		$userId = model('app/users')->getUserId();
		$data = model('Users')->getFieldsById($userId,['userScore','userTotalScore']);
		return json_encode(WSTReturn('success',1,$data));
	}
    /**
     * @OA\Get(
     *     tags={"Userscores"},
     *     path="/app/Userscores/pageQuery",
     *     summary="地址管理列表数据",
     *     description="地址管理列表数据",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
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
     *                         @OA\Property(property="list",type="array",
     *                             @OA\Items(
     *                                  @OA\Property(property="scoreId",type="integer", description="主键id"),
     *                                  @OA\Property(property="userId",type="integer", description="用户id"),
     *                                  @OA\Property(property="score",type="integer", description="积分数"),
     *                                  @OA\Property(property="dataSrc",type="integer", description="数据来源", example="商品订单"),
     *                                  @OA\Property(property="dataId",type="integer", description="来源数据id"),
     *                                  @OA\Property(property="dataRemarks",type="integer", description="积分备注", example="交易订单【110027422】使用积分320个"),
     *                                  @OA\Property(property="scoreType",type="integer", description="1获得积分 0:支出积分"),
     *                                  @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                             )
     *                         ),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function pageQuery(){
        $userId = model('app/users')->getUserId();
        $m = new CM();
        $data = $m->pageQuery($userId);
        return json_encode(WSTReturn('success',1,$data));
    }
    /**
     * @OA\Get(
     *     tags={"Userscores"},
     *     path="/app/Userscores/sign",
     *     summary="用户签到",
     *     description="用户签到",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/response_schema")
     *          )
     *     )
     * )
     */
    public function sign(){
        $m = new CM();
        $userId = model('app/users')->getUserId();
        $rs = $m->signScore($userId);
        return json_encode($rs);
    }
}
