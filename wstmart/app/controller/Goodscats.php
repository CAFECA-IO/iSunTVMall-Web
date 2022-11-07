<?php
namespace wstmart\app\controller;
use wstmart\app\model\GoodsCats as M;
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
 * 商品分类控制器
 */
class GoodsCats extends Base{
    /**
     * @OA\Get(
     *     tags={"GoodsCats"},
     *     path="/app/goodscats/pageQuery",
     *     summary="获取商城一级分类",
     *     description="获取商城一级分类",
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="catId", type="integer", description="分类id", example=""),
     *                              @OA\Property(property="parentId", type="integer", description="父级id", example=""),
     *                              @OA\Property(property="catName", type="string", description="分类名称", example=""),
     *                              @OA\Property(property="isShow", type="integer", description="是否显示，1:显示 0:不显示", example=""),
     *                              @OA\Property(property="isFloor", type="integer", description="是否显示，1:是 0:否", example=""),
     *                              @OA\Property(property="catSort", type="integer", description="排序号", example=""),
     *                              @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
     *                              @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                              @OA\Property(property="commissionRate", type="integer", description="分类佣金", example=""),
     *                              @OA\Property(property="catImg", type="string", description="分类图片", example=""),
     *                              @OA\Property(property="subTitle", type="string", description="楼层副标题", example=""),
     *                              @OA\Property(property="simpleName", type="string", description="分类缩写", example=""),
     *                          )
     *                      )
     *                 )
     *              }             
     * 
     *         )
     *       ),
     *     )
     * )
     */
    public function pageQuery(){
        $rs = model('goodsCats')->listQuery(0);
        return json_encode(WSTReturn('success',1,$rs));
    }
    /**
     * @OA\Get(
     *     tags={"GoodsCats"},
     *     path="/app/goodscats/getGoodsCats",
     *     summary="获取一级商品分类",
     *     description="获取一级商品分类",
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="catId", type="integer", description="分类id", example=""),
     *                              @OA\Property(property="catName", type="string", description="分类名称", example=""),
     *                              @OA\Property(property="simpleName", type="string", description="分类缩写", example=""),
     *                          )
     *                      )
     *                 )
     *              }             
     * 
     *         )
     *       ),
     *     )
     * )
     */
    public function getGoodsCats(){
        $rs = WSTGoodsCats(0);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * @OA\Get(
     *     tags={"GoodsCats"},
     *     path="/app/goodscats/index",
     *     summary="分类页数据",
     *     description="分类页数据",
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data", type="object",
     *                          @OA\Property(property="catId",  type="integer", description="分类id"),
     *                          @OA\Property(property="catName",  type="string", description="分类名称"),
     *                          @OA\Property(property="ads", type="array", description="分类广告",
     *                              @OA\Items(ref="#/components/schemas/ads_response_schema"),
     *                          ),
     *                          @OA\Property(property="childList", type="array", description="二级分类",
     *                              @OA\Items(
     *                                  @OA\Property(property="catId", type="integer", description="分类id", example=""),
     *                                  @OA\Property(property="parentId", type="integer", description="父级id", example=""),
     *                                  @OA\Property(property="catName", type="string", description="分类名称", example=""),
     *                                  @OA\Property(property="childList", type="array", description="三级分类",
     *                                      @OA\Items(
     *                                          @OA\Property(property="catId", type="integer", description="分类id", example=""),
     *                                          @OA\Property(property="parentId", type="integer", description="父级id", example=""),
     *                                          @OA\Property(property="catName", type="string", description="分类名称", example=""),
     *                                          @OA\Property(property="catImg", type="string", description="分类图片", example=""),
     *                                      )
     *                                  ),
     *                              )
     *                          ),
     *                      )
     *                 )
     *              }             
     * 
     *         )
     *       ),
     *     )
     * )
     */
    public function index(){
    	$m = new M();
    	$goodsCatList = $m->getGoodsCats();
    	if(!empty($goodsCatList)){
            return json_encode(WSTReturn('success',1,$goodsCatList));
        }
        return json_encode(WSTReturn('error',-1));
    }  
}
