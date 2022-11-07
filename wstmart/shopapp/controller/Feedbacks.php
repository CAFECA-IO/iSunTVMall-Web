<?php
namespace wstmart\shopapp\controller;
use wstmart\common\model\Feedbacks as M;
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
 * 功能反馈控制器
 */
class Feedbacks extends Base{
    /**
     * @OA\Get(
     *     tags={"Feedbacks"},
     *     path="/shopapp/feedbacks/getFeedbackTypes",
     *     summary="获取反馈问题类型",
     *     description="获取反馈问题类型",
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
	 *     @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
    public function getFeedbackTypes(){
        $feedbackTypes = WSTDatas('FEEDBACK_TYPE');
        return json_encode(WSTReturn('success',1,$feedbackTypes));
    }
    /**
     * @OA\Post(
     *     tags={"Feedbacks"},
     *     path="/shopapp/feedbacks/add",
     *     summary="保存反馈问题",
     *     description="保存反馈问题",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="feedbackType", in="query", @OA\Schema(type="integer"), required=true, description="反馈类型", example="2"),
     *     @OA\Parameter(name="feedbackContent", in="query", @OA\Schema(type="string"), required=true, description="反馈内容", example="我觉得主题色应该用五彩斑斓的黑"),
     *     @OA\Parameter(name="contactInfo", in="query", @OA\Schema(type="string"), required=true, description="联系方式", example=""),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema"),
     *       ),
     *     ),
	 *     @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
    public function add(){
        $m = new M();
        $userId = model('shopapp/users')->getUserId();
        $rs = $m->add($userId);
        return json_encode($rs);
    }

}
