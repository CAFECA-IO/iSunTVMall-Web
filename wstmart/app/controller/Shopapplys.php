<?php
namespace wstmart\app\controller;
use wstmart\common\model\ShopApplys as M;
use wstmart\common\model\Users as UM;
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
 * 商家入驻控制器
 */
class Shopapplys extends Base{
    /**
     * @OA\Get(
     *     tags={"Shopapplys"},
     *     path="/app/Shopapplys/getData",
     *     summary="获取用户数据",
     *     description="获取用户数据",
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
     *                         @OA\Property(property="isApply",type="boolean", description="是否已申请", default=false),
     *                         @OA\Property(property="linkPhone",type="string", description="联系电话"),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function getData(){
        $m = new M();
        $um = new UM();
        $userId = model('app/index')->getUserId();
        $isApply = $m->isApply($userId);
        $rs = $um->getFieldsById($userId,'userPhone');
        $data = [];
        $data['isApply'] = $isApply;
        $data['linkPhone'] = $rs['userPhone'];
        return json_encode(WSTReturn('success',1,$data));
    }

    /**
     * @OA\Post(
     *     tags={"Shopapplys"},
     *     path="/app/Shopapplys/add",
     *     summary="保存商家入驻",
     *     description="保存商家入驻",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="linkman", in="query", @OA\Schema(type="string"), required=true, description="联系人"),
     *     @OA\Parameter(name="linkPhone", in="query", @OA\Schema(type="string"), required=true, description="联系方式"),
     *     @OA\Parameter(name="applyIntention", in="query", @OA\Schema(type="string"), required=true, description="营业范围"),
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
    public function add(){
        $m = new M();
        $userId = model('app/index')->getUserId();
        $rs = $m->add($userId);
        return json_encode($rs);
    }
}
