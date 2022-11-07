<?php
namespace wstmart\app\controller;
use wstmart\common\model\UserAddress as M;
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
 * 用户地址控制器
 */
class UserAddress extends Base{
	// 前置方法执行列表
    protected $beforeActionList = ['checkAuth'];
	/**
     * @OA\Get(
     *     tags={"UserAddress"},
     *     path="/app/UserAddress/index",
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
     *                             @OA\Items(ref="#/components/schemas/user_address_response_schema")
     *                         ),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
	public function index(){
		$m = new M();
		$userId = model('app/index')->getUserId();
		$addressList = $m->listQuery($userId);
		//获取省级地区信息
		$area = model('app/Areas')->listQuery(0);
		$data = [];
		// 已保存的用户地址列表
		$data['list'] = $addressList;
		return json_encode(WSTReturn(lang('success_msg'), 1, $data));
	}
	/**
     * @OA\Get(
     *     tags={"UserAddress"},
     *     path="/app/UserAddress/getById",
     *     summary="获取地址信息",
     *     description="获取地址信息",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="addressId", in="query", @OA\Schema(type="integer"), required=true, description="地址id", example="465"),
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
     *                            @OA\Schema(ref="#/components/schemas/user_address_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="area1",type="array",
     *                                     @OA\Items(
     *                                          @OA\Property(property="areaId", type="integer", description="地区id", example="4"),
     *                                          @OA\Property(property="areaName", type="integer", description="地区名称", example="4"),
     *                                          @OA\Property(property="parentId", type="integer", description="父级id", example="4"),
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
	public function getById(){
		$m = new M();
		$userId = model('app/index')->getUserId();
		$rs = $m->getById(input('addressId/d'), $userId);
		// 查询到为空,即为新增收货地址,返回省级地址
		$areaM = model('app/Areas');
		if(empty($rs)){
			$rs = $areaM->listQuery(0);
		}else{
			// 获取地区数据
			$rs['area1'] = $areaM->listQuery(0);

			// $rs['area2'] = $areaM->listQuery($rs['areaId2']);
		}
		return json_encode(WSTReturn(lang('success_msg'),1,$rs));

	}
	/**
     * @OA\Post(
     *     tags={"UserAddress"},
     *     path="/app/UserAddress/setDefault",
     *     summary="设置为默认地址",
     *     description="设置为默认地址",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="addressId", in="query", @OA\Schema(type="integer"), required=true, description="地址id", example="465"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *     )
     * )
     */
	public function setDefault(){
		$m = new M();
		$userId = model('app/index')->getUserId();
		$rs = $m->setDefault($userId);
		return json_encode($rs);
	}
	/**
     * @OA\Post(
     *     tags={"UserAddress"},
     *     path="/app/UserAddress/edits",
     *     summary="新增/编辑地址",
     *     description="新增/编辑地址",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="addressId", in="query", @OA\Schema(type="integer"), required=true, description="地址id,若不为0执行修改,否则执行新增", example="465"),
	 *     @OA\Parameter(name="userName", in="query", @OA\Schema(type="integer"), required=true, description="收货人", example="465"),
	 *     @OA\Parameter(name="userPhone", in="query", @OA\Schema(type="number"), required=true, description="联系电话", example="465"),
	 *     @OA\Parameter(name="areaId", in="query", @OA\Schema(type="integer"), required=true, description="最后一级地区Id", example="465"),
	 *     @OA\Parameter(name="userAddress", in="query", @OA\Schema(type="integer"), required=true, description="完整收货地址", example="465"),
	 *     @OA\Parameter(name="isDefault", in="query", @OA\Schema(type="integer"), required=true, description="是否为默认收货地址，", example="465"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *     )
     * )
     */
    public function edits(){
        $m = new M();
        $userId = (int)model('app/index')->getUserId();
        if(input('post.addressId/d')){
        	$rs = $m->edit($userId);
        }else{
        	$rs = $m->add($userId);
        } 
        return json_encode($rs);
    }
	/**
     * @OA\Post(
     *     tags={"UserAddress"},
     *     path="/app/UserAddress/del",
     *     summary="删除地址",
     *     description="删除地址",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="addressId", in="query", @OA\Schema(type="integer"), required=true, description="地址id", example="465"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *     )
     * )
     */
    public function del(){
    	$m = new M();
    	$userId = (int)model('index')->getUserId();
    	return json_encode($m->del($userId));
    }
}
