<?php
namespace wstmart\app\controller;
use wstmart\common\model\GoodsAppraises as M;
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
 * 评价控制器
 */
class GoodsAppraises extends Base{
    // 前置方法执行列表
    protected $beforeActionList = [
        'checkAuth'  =>  ['except'=>'getbyid']// 只要访问only下的方法才才需要执行前置操作
    ];
	/**
     * @OA\Get(
     *     tags={"GoodsAppraises"},
     *     path="/app/goodsappraises/getById",
     *     summary="根据商品id评论",
     *     description="根据商品id评论",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/pagesize"),
	 *     @OA\Parameter(name="goodsId", in="query", @OA\Schema(type="integer"), required=true, description="普通商品id", example="2"),
	 *     @OA\Parameter(name="anonymous", in="query", @OA\Schema(type="integer"), required=true, description="是否对用户名进行匿名处理，0:不处理 1:匿名处理", example="1"),
	 *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string"), required=true, description="'all':全部 'pic': 晒图 'best': 好评 'good': 中评 'bad': 差评", example="all"),
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
     *                                          @OA\Property(property="id", type="integer", description="评价表id", example=""),
     *                                          @OA\Property(property="content", type="string", description="评价内容", example=""),
     *                                          @OA\Property(property="images", type="string", description="评价附件", example=""),
     *                                          @OA\Property(property="shopReply", type="string", description="商家回复", example=""),
     *                                          @OA\Property(property="replyTime", type="string", description="商家回复时间", example=""),
     *                                          @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                                          @OA\Property(property="goodsScore", type="integer", description="商品评分", example=""),
     *                                          @OA\Property(property="serviceScore", type="integer", description="服务评分", example=""),
     *                                          @OA\Property(property="timeScore", type="integer", description="时效评分", example=""),
     *                                          @OA\Property(property="shopId", type="integer", description="店铺id", example=""),
     *                                          @OA\Property(property="orderId", type="integer", description="订单id", example=""),
     *                                          @OA\Property(property="shopName", type="string", description="店铺名称", example=""),
     *                                          @OA\Property(property="userPhoto", type="string", description="用户头像", example=""),
     *                                          @OA\Property(property="loginName", type="string", description="登录名称，经过*号替换处理，类似ws***rt", example=""),
     *                                          @OA\Property(property="userTotalScore", type="integer", description="用户总评分", example=""),
	 *                                          @OA\Property(property="goodsSpecNames", type="array", description="商品规格",
	 *                                              @OA\Items()
	 *                                          ),
     *                                          @OA\Property(property="avgScore", type="integer", description="平均分", example="4"),
     *                                          @OA\Property(property="rankImg", type="string", description="会员等级图标", example=""),
     *                                          @OA\Property(property="rankName", type="string", description="会员等级名称", example=""),
	 * 											
     *                                     ),
     *                                 ),
	 * 								  @OA\Property(property="bestNum", type="integer", description="好评数", example="1"),
	 * 								  @OA\Property(property="goodNum", type="integer", description="中评数", example="1"),
	 * 								  @OA\Property(property="badNum", type="integer", description="差评数", example="1"),
	 * 								  @OA\Property(property="picNum", type="integer", description="晒图数", example="1"),
	 * 								  @OA\Property(property="sum", type="integer", description="总评价数", example="4"),
     *                           ),
     *                         }
     *                      )
     *                      
     *                 )
     *             }
     *         )
     *       ),
     *     ),
	 * 	   @OA\Response(
     *       response=401,
     *       ref="#/components/responses/response_401",
     *    ),
     * )
     */
	public function getById(){
		$m = new M();
		$rs = $m->getById();
		if(isset($rs['data']['data'])){
			foreach($rs['data']['data'] as $k=>$v){
				if($v['images']!=''){
					$imgs = explode(',',$v['images']);
					foreach($imgs as $k2=>$v2){
						$imgs[$k2] = WSTImg($v2,3);
					}
					$rs['data']['data'][$k]['images'] = $imgs;
				}
			}
		}
		return json_encode($rs);
	}
	/**
     * @OA\Get(
     *     tags={"GoodsAppraises"},
     *     path="/app/goodsappraises/getAppr",
     *     summary="根据订单id, 商品id, 商品规格id, 获取评价",
     *     description="根据订单id, 商品id, 商品规格id, 获取评价",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="oId", in="query", @OA\Schema(type="integer"), required=true, description="订单id", example="2"),
	 *     @OA\Parameter(name="gId", in="query", @OA\Schema(type="integer"), required=true, description="商品id", example="1"),
	 *     @OA\Parameter(name="sId", in="query", @OA\Schema(type="integer"), required=true, description="商品规格id", example="0"),
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
     *                          @OA\Property(property="id", type="integer", description="评价Id", example="1"),
     *                          @OA\Property(property="orderId", type="integer", description="订单Id", example="1"),
     *                          @OA\Property(property="goodsId", type="integer", description="商品Id", example="1"),
     *                          @OA\Property(property="goodsSpecId", type="integer", description="规格Id", example="1"),
     *                          @OA\Property(property="userId", type="integer", description="用户Id", example="1"),
     *                          @OA\Property(property="goodsScore", type="integer", description="商品评分", example="1"),
     *                          @OA\Property(property="serviceScore", type="integer", description="服务评分", example="1"),
     *                          @OA\Property(property="timeScore", type="integer", description="时效评分", example="1"),
     *                          @OA\Property(property="content", type="string", description="评价内容", example="1"),
     *                          @OA\Property(property="images", type="string", description="评价附件", example="1"),
     *                          @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                          @OA\Property(property="orderGoodsId", type="integer", description="订单商品id", example="1"),
     *                      )
     *                      
     *                 )
     *             }
     *         )
     *       ),
     *     ),
	 * 	   @OA\Response(
     *       response=401,
     *       ref="#/components/responses/response_401",
     *     ),
     * )
     */
	public function getAppr(){
		$m = model('GoodsAppraises');
		$userId = (int)model('app/index')->getUserId();
		$rs = $m->getAppr($userId);
		// 删除无用字段
		unset($rs['data']['shopId']);
		unset($rs['data']['shopReply']);
		unset($rs['data']['isShow']);
		unset($rs['data']['dataFlag']);
		unset($rs['data']['replyTime']);
		if(!empty($rs['data']['images'])){
			$imgs = explode(',',$rs['data']['images']);
			foreach($imgs as $k=>$v){
				$imgs[$k] = WSTImg($v,1);
			}
			$rs['data']['images'] = $imgs;
		}
		return json_encode($rs);
	}
	/**
     * @OA\Post(
     *     tags={"GoodsAppraises"},
     *     path="/app/goodsappraises/add",
     *     summary="新增评价",
     *     description="新增评价",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="content", in="query", @OA\Schema(type="integer"), required=true, description="评价内容", example="2"),
	 *     @OA\Parameter(name="goodsId", in="query", @OA\Schema(type="integer"), required=true, description=" 商品Id", example="2"),
	 *     @OA\Parameter(name="goodsSpecId", in="query", @OA\Schema(type="integer"), required=true, description="商品规格Id", example="2"),
	 *     @OA\Parameter(name="orderId", in="query", @OA\Schema(type="integer"), required=true, description="订单Id", example="2"),
	 *     @OA\Parameter(name="timeScore", in="query", @OA\Schema(type="integer"), required=true, description="时效评分", example="2"),
	 *     @OA\Parameter(name="goodsScore", in="query", @OA\Schema(type="integer"), required=true, description="商品评分", example="2"),
	 *     @OA\Parameter(name="serviceScore", in="query", @OA\Schema(type="integer"), required=true, description="服务评分", example="2"),
	 *     @OA\Parameter(name="images", in="query", @OA\Schema(type="integer"), required=true, description="评价附件", example="2"),
	 *     @OA\Parameter(name="orderGoodsId", in="query", @OA\Schema(type="integer"), required=true, description="订单商品id", example="2"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema"),
     *       ),
     *    ),
	 * 	  @OA\Response(
     *       response=401,
     *       ref="#/components/responses/response_401",
     *    ),
     * )
     */
	public function add(){
		$m = new M();
		$userId = model('app/index')->getUserId();
		$rs = $m->add((int)$userId);
		return json_encode($rs);
	}
}
