<?php
namespace wstmart\app\controller;
use wstmart\common\model\Messages as M;
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
 * 商城消息控制器
 */
class Messages extends Base{
	// 前置方法执行列表
    protected $beforeActionList = [
        'checkAuth'  =>  ['except'=>'index'],
	];
	/**
     * @OA\Get(
     *     tags={"Messages"},
     *     path="/app/Messages/getLastMsg",
     *     summary="获取最后一条商城消息",
     *     description="获取最后一条商城消息",
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
     *                    @OA\Property(property="data",type="object",
     *                        @OA\Property(property="id", type="integer", description="主键id"),
     *                        @OA\Property(property="msgContent", type="string", description="消息内容"),
     *                        @OA\Property(property="msgStatus", type="string", description="是否已读，1:已读 0:未读"),
	* 						  @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                     ),
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function getLastMsg(){
		$m = new M();
		$userId = (int)model('app/index')->getUserId();
		$rs = model('common/Messages')->getLastMsg($userId);
		return json_encode(WSTReturn('ok',1,$rs));
	}
	/**
     * @OA\Get(
     *     tags={"Messages"},
     *     path="/app/Messages/pageQuery",
     *     summary="获取商城消息列表",
     *     description="获取商城消息列表",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
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
	*                                    @OA\Items(
     *                                        @OA\Property(property="id", type="integer", description="主键id"),
     *				                          @OA\Property(property="msgContent", type="string", description="消息内容"),
     *				                          @OA\Property(property="msgStatus", type="string", description="是否已读，1:已读 0:未读"),
	*				 						  @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                                    ),
     *                                ),
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
          $userId = (int)model('app/index')->getUserId();
		$data =  $m->pageQuery($userId);
		return json_encode(WSTReturn('success',1,$data));
	}
	/**
     * @OA\Get(
     *     tags={"Messages"},
     *     path="/app/Messages/index",
     *     summary="查看商城消息详情",
     *     description="查看商城消息详情,由webview调用",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	*     @OA\Parameter(name="msgId", in="query", @OA\Schema(type="integer"), required=true, description="商城消息Id", example="1"),
     *     @OA\Response(
     *      response="200",
     *      description="返回商城消息详情页",
     *     )
     * )
     */
	public function index(){
          $m = new M();
          $userId = (int)model('app/index')->getUserId();
		$data = $m->getById($userId);
		$this->assign('data',$data);
		return $this->fetch('message');
	}
	/**
     * @OA\Post(
     *     tags={"Messages"},
     *     path="/app/Messages/del",
     *     summary="删除消息",
     *     description="删除消息",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="msgId", in="query", @OA\Schema(type="integer"), required=true, description="消息id 多个id以,隔开", example="1,2,3,4"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
	public function del(){
          $m = new M();
          $userId = (int)model('app/index')->getUserId();
		$rs = $m->batchDel($userId);
		return json_encode($rs);
	}
}
