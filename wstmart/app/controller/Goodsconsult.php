<?php
namespace wstmart\app\controller;
use wstmart\common\model\GoodsConsult as M;
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
 * 商品咨询控制器
 */
class GoodsConsult extends Base{
    protected $beforeActionList = [];
    /**
     * @OA\Get(
     *     tags={"GoodsConsult"},
     *     path="/app/GoodsConsult/getConsultType",
     *     summary="获取商品咨询类别",
     *     description="获取商品咨询类别",
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data", type="array", 
     *                          @OA\Items(
     *                             @OA\Property(property="id", type="integer", description="主键id"),
     *                             @OA\Property(property="catId", type="integer", description="所属类型id"),
     *                             @OA\Property(property="dataName", type="string", description="类型名称"),
     *                             @OA\Property(property="dataVal", type="string", description="类型值"),
     *                             @OA\Property(property="dataSort", type="integer", description="排序号"),
     *                             @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
     *                          ),
     *                      ),
     *                 )
     *             }
     *         )
     *       ),
     *     ),
     * )
     */
    public function getConsultType(){
        $arr = WSTDatas('COUSULT_TYPE');
        sort($arr);
        return json_encode(WSTReturn('success',1,$arr));
    }
    /**
     * @OA\Get(
     *     tags={"GoodsConsult"},
     *     path="/app/GoodsConsult/listQuery",
     *     summary="根据商品id获取商品咨询",
     *     description="根据商品id获取商品咨询",
     *     @OA\Parameter(name="goodsId", in="query", @OA\Schema(type="integer"), required=true, description="普通商品id", example="2"),
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/pagesize"),
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
     *                                     @OA\Items(
     *                                          @OA\Property(property="id", type="integer", description="主键id"),
     *                                          @OA\Property(property="goodsId", type="integer", description="所属商品Id"),
     *                                          @OA\Property(property="userId", type="integer", description="所属用户id"),
     *                                          @OA\Property(property="consultType", type="integer", description="咨询的类别"),
     *                                          @OA\Property(property="consultContent", type="string", description="咨询内容 "),
     *                                          @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                                          @OA\Property(property="reply", type="string", description="回复内容"),
     *                                          @OA\Property(property="replyTime", type="string", description="回复时间"),
     *                                          @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
     *                                          @OA\Property(property="isShow", type="integer", description="是否显示，1:是 0:否"),
     *                                          @OA\Property(property="loginName", type="integer", description="所属用户名称（匿名处理可在后台程序自行修改"),
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
     *     )
     * )
     */
    public function listQuery(){
        $m = new M();
        $rs = $m->listQuery();
        return json_encode($rs);
    }
    /**
     * @OA\Post(
     *     tags={"GoodsConsult"},
     *     path="/app/GoodsConsult/add",
     *     summary="新增商品咨询",
     *     description="新增商品咨询",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="goodsId", in="query", @OA\Schema(type="integer"), required=true, description="普通商品id", example="2"),
     *     @OA\Parameter(name="consultType", in="query", @OA\Schema(type="integer"), required=true, description="咨询类别id", example="2"),
     *     @OA\Parameter(name="咨询内容", in="query", @OA\Schema(type="string"), required=true, description="咨询内容", example=""),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema")
     *       ),
     *     )
     * )
     */
    public function add(){
    	$m = new M();
        $userId = model('index')->getUserId();
    	$rs = $m->add($userId);
        return json_encode($rs);
    }
}
