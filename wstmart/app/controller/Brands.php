<?php
namespace wstmart\app\controller;
use wstmart\common\model\Brands as M;
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
 * 品牌控制器
 */
class Brands extends Base{
    /**
     * @OA\Get(
     *     tags={"Brands"},
     *     path="/app/brands/pageQuery",
     *     summary="品牌列表查询",
     *     description="获取品牌列表查询",
     *     @OA\Parameter(name="catId", in="query", @OA\Schema(type="integer"), required=true, description="商城分类id", example="47"),
     *     @OA\Response(
     *      response="200",
     *      description="返回品牌信息",
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
     *                                @OA\Property(property="data",type="array",description="品牌信息",
     *                                     @OA\Items(
     *                                          @OA\Property(property="catId", type="integer", description="品牌所属的商城分类id", example="47"),
     *                                          @OA\Property(property="brandId", type="integer", description="品牌id", example="1"),
     *                                          @OA\Property(property="brandName", type="string",description="品牌名称", example="商淘"),
     *                                          @OA\Property(property="brandImg", type="string",description="品牌图片"),
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
    	$m = new M();
        $selectedId = (int)input('id');
    	$rs = $m->pageQuery(100,$selectedId);
        foreach ($rs['data'] as $key =>$v){
            $rs['data'][$key]['brandImg'] = WSTImg($v['brandImg'],2);
        }       
    	return json_encode(WSTReturn('success',1,$rs));
    }
}
