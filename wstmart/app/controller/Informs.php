<?php
namespace wstmart\app\controller;
use wstmart\common\model\Informs as M;
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
 * 商品举报控制器
 */
class Informs extends Base{
    protected $beforeActionList = [
        'checkAuth'  =>  ['except'=>'tips'],
    ];
    /******************************** 用户 ******************************************/

    /**
     * @OA\Get(
     *     tags={"Informs"},
     *     path="/app/informs/informStatus",
     *     summary="获取举报处理状态",
     *     description="获取举报处理状态",
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
     *                      @OA\Property(property="data", type="array",description="举报处理状态信息",
     *                           @OA\Items(
     *                               @OA\Property(property="dataVal, type="string", description="举报处理状态数据值",example="1"),
     *                               @OA\Property(property="dataName", type="string", description="举报处理状态",example="无效举报"),
     *                           ),
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
    public function informStatus(){
        $rs = (new M())->informStatus();
        return json_encode($rs);
    }

    /**
     * @OA\Post(
     *     tags={"Informs"},
     *     path="/app/informs/informStatus",
     *     summary="获取用户举报列表",
     *     description="获取用户举报列表",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="informStatus", in="query", @OA\Schema(type="string"), required=false, description="举报处理状态", example="7,8,9"),
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
     *                                          @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
     *                                          @OA\Property(property="finalHandleStaffId", type="integer", description="处理举报的职员id"),
     *                                          @OA\Property(property="finalHandleTime", ref="#/components/schemas/finalHandleTime"),
     *                                          @OA\Property(property="goodId", type="integer", description="商品id"),
     *                                          @OA\Property(property="goodsId", type="integer", description="商品id"),
     *                                          @OA\Property(property="goodsImg", type="string", description="商品图片"),
     *                                          @OA\Property(property="goodsName", type="string", description="商品名称"),
     *                                          @OA\Property(property="informAnnex", type="string", description="举报图片"),
     *                                          @OA\Property(property="informContent", type="string", description="举报内容"),
     *                                          @OA\Property(property="informId", type="integer, description="举报id"),
     *                                          @OA\Property(property="informStatus", type="integer", description="举报处理状态"),
     *                                          @OA\Property(property="informTargetId", type="integer", description="举报的用户Id"),
     *                                          @OA\Property(property="informTime", ref="#/components/schemas/informTime"),
     *                                          @OA\Property(property="informType", type="integer", description="举报分类Id"),
     *                                          @OA\Property(property="respondContent", type="string", description="处理结果"),
     *                                          @OA\Property(property="shopId", type="integer", description="被举报的店铺id"),
     *                                          @OA\Property(property="shopName", type="string", description="被举报的店铺名称"),
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
    public function pageQuery(){
        $m = model('Informs');
        $userId = model('app/index')->getUserId();
        $rs = $m->queryUserInformByPage($userId);
        return json_encode($rs);

    }

    /**
     * @OA\Get(
     *     tags={"Informs"},
     *     path="/app/informs/tips",
     *     summary="查看举报须知",
     *     description="查看举报须知,由webview调用",
     *     @OA\Response(
     *      response="200",
     *      description="返回举报须知页",
     *     )
     * )
     */
    public function tips(){
        $m = new M();
        $tips = $m->tips();
        return $this->fetch('tips', ['tips'=>$tips]);
    }

    /**
     * @OA\Post(
     *     tags={"Informs"},
     *     path="/app/informs/inform",
     *     summary="获取举报商品信息",
     *     description="获取举报商品信息",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="商品id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     * 		   @OA\Schema(
     *     		   allOf={
     *             		@OA\Schema(ref="#/components/schemas/response_schema"),
     *                  @OA\Schema(
     *                      @OA\Property(property="data",type="object",description="举报商品信息",
     *                           @OA\Property(property="goodsId", type="integer", description="商品id"),
     *                           @OA\Property(property="goodsImg", type="string", description="商品图片"),
     *                           @OA\Property(property="goodsName", type="string",description="商品名称"),
     *                           @OA\Property(property="shopId", type="integer", description="被举报的店铺id"),
     *                           @OA\Property(property="shopName", type="string", description="被举报的店铺名称"),
     *                           @OA\Property(property="types", type="array", description="举报分类信息",
     *                               @OA\Items(
     *                                   @OA\Property(property="catId", type="integer", description="分类id"),
     *                                   @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
     *                                   @OA\Property(property="dataName", type="string", description="分类名称" ),
     *                                   @OA\Property(property="dataSort", type="integer", description="分类排序"),
     *                                   @OA\Property(property="dataVal", type="string", description="分类数据值"),
     *                                   @OA\Property(property="id", type="integer", description="自增分类id" ),
     *                                   @OA\Property(property="subCatId", type="integer", description="子分类ID" ),
     *                                   @OA\Property(property="subDataVal", type="integer", description="子分类数据内容"),
     *                               )
     *                           ),
     *                       ),
     *                 )
     *     		   }
     * 		   )
     *       ),
     *     )
     * )
     */
    public function inform(){
        $userId = model('app/index')->getUserId();
    	$m = new M();
        $data = $m->inform($userId);
        return json_encode($data);
    }

    /**
     * @OA\Post(
     *     tags={"Informs"},
     *     path="/app/informs/saveInform",
     *     summary="保存举报信息",
     *     description="保存举报信息",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="informType", in="query", @OA\Schema(type="integer"), required=true, description="举报分类Id"),
     *     @OA\Parameter(name="goodsId", in="query", @OA\Schema(type="integer"), required=true, description="商品id"),
     *     @OA\Parameter(name="shopsId", in="query", @OA\Schema(type="integer"), required=true, description="被举报的店铺id"),
     *     @OA\Parameter(name="informContent", in="query", @OA\Schema(type="string"), required=true, description="举报内容"),
     *     @OA\Parameter(name="informAnnex", in="query", @OA\Schema(type="string"), required=true, description="举报图片"),
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
    public function saveInform(){
        $userId = model('app/index')->getUserId();
        $rs = model('Informs')->saveInform($userId);
        return json_encode($rs);
    }

    /**
     * @OA\Post(
     *     tags={"Informs"},
     *     path="/app/informs/saveInform",
     *     summary="用户查举报详情",
     *     description="用户查举报详情",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="举报id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     * 		   @OA\Schema(
     *     		   allOf={
     *             		@OA\Schema(ref="#/components/schemas/response_schema"),
     *                  @OA\Schema(
     *                      @OA\Property(property="data",type="object",description="举报详情信息",
     *                              @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
     *                              @OA\Property(property="finalHandleStaffId", type="integer", description="处理举报的职员id"),
     *                              @OA\Property(property="finalHandleTime", ref="#/components/schemas/finalHandleTime"),
     *                              @OA\Property(property="goodId", type="integer", description="商品id"),
     *                              @OA\Property(property="goodsId", type="integer", description="商品id"),
     *                              @OA\Property(property="goodsImg", type="string", description="商品图片"),
     *                              @OA\Property(property="goodsName", type="string", description="商品名称"),
     *                              @OA\Property(property="informAnnex", type="string", description="举报图片"),
     *                              @OA\Property(property="informContent", type="string", description="举报内容"),
     *                              @OA\Property(property="informId", type="integer, description="举报id"),
     *                              @OA\Property(property="informStatus", type="integer", description="举报处理状态", example="0"),
     *                              @OA\Property(property="informTargetId", type="integer", description="举报的用户Id"),
     *                              @OA\Property(property="informTime", ref="#/components/schemas/informTime"),
     *                              @OA\Property(property="informType", type="integer", description="举报分类Id"),
     *                              @OA\Property(property="respondContent", type="string", description="处理结果"),
     *                              @OA\Property(property="shopId", type="integer", description="被举报的店铺id"),
     *                              @OA\Property(property="shopName", type="string", description="被举报的店铺名称"),
     *                              @OA\Property(property="status", type="string", description="举报处理状态", example="等待处理"),
     *                       ),
     *                 )
     *     		   }
     * 		   )
     *       ),
     *     )
     * )
     */
    public function getUserInformDetail(){
        $userId = model('app/index')->getUserId();
        $data = model('Informs')->getUserInformDetail(0, $userId);
        if(!empty($data)){
            return json_encode(WSTReturn('', 1, $data));
        }
        return json_encode(WSTReturn(lang('no_find_informs')));
    }
}
