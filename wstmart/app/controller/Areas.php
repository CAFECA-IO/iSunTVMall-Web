<?php
namespace wstmart\app\controller;
use wstmart\common\model\Areas as M;
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
 * 地区控制器
 */
class Areas extends Base{
    /**
     * @OA\Get(
     *     tags={"Areas"},
     *     path="/app/areas/listQuery",
     *     summary="地区列表查询",
     *     description="获取地区列表查询",
     *     @OA\Parameter(name="parentId", in="query", @OA\Schema(type="integer"), required=false, description="父级id"),
     *     @OA\Response(
     *      response="200",
     *      description="返回地区信息",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                    @OA\Property(property="data",type="array",description="地区信息",
     *                        @OA\Items(
     *                            @OA\Property(property="areaId", type="integer", description="地区id", example="52"),
     *                            @OA\Property(property="areaName", type="string",description="地区名称", example="北京"),
     *                            @OA\Property(property="parentId", type="integer",description="父级id", example="2"),
     *                        ),
     *                    ),
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
        return Json_encode(WSTReturn(lang('success_msg'), 1,$rs));
    }
}