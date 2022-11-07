<?php
namespace wstmart\app\controller;
use wstmart\common\model\Favorites as M;
use wstmart\common\model\ShopMembers as SM;
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
 * 收藏控制器
 */
class Favorites extends Base{
	// 前置方法执行列表
    protected $beforeActionList = ['checkAuth'];
	/**
     * @OA\Get(
     *     tags={"Favorites"},
     *     path="/app/favorites/listGoodsQuery",
     *     summary="关注的商品列表",
     *     description="获取app端关注的商品列表",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回关注的商品列表",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="list",type="array",
	 * 								@OA\Items(
	 * 									@OA\Property(property="favoriteId", type="integer", description="关注表主键id", example="4"),
	 * 									@OA\Property(property="targetId", type="integer", description="商品id", example="4"),
	 * 									@OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                  @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                  @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                                  @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                  @OA\Property(property="saleNum", ref="#/components/schemas/saleNum"),
	 * 								)
     *                           ),
     *                      )
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
	public function listGoodsQuery(){
		$m = new M();
        $userId = model('app/index')->getUserId();
		$page = $m->listGoodsQuery($userId);
		if(!empty($page['data'])){
			foreach($page['data'] as $k=>$v){
				$page['data'][$k]['goodsImg'] = WSTImg($v['goodsImg'],3);
				$page['data'][$k]['goodsName'] = htmlspecialchars_decode($v['goodsName']);
			}
		}
		echo(json_encode(WSTReturn('success',1,$page)));die;
	}

	/**
     * @OA\Get(
     *     tags={"Favorites"},
     *     path="/app/favorites/listShopQuery",
     *     summary="关注的店铺列表",
     *     description="获取app端关注的店铺列表",
	*     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回关注的店铺列表",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="list",type="array",
	* 								@OA\Items(
	* 									@OA\Property(property="favoriteId", type="integer", description="关注表主键id", example="1"),
	* 									@OA\Property(property="targetId", type="integer", description="店铺id", example="1"),
	* 									@OA\Property(property="shopId", type="integer", description="店铺id", example="1"),
	* 									@OA\Property(property="shopName", type="string", description="店铺名称", example="新鲜鲜果旗舰店"),
	* 									@OA\Property(property="shopImg", type="string", description="店铺图标", example=""),
	* 									@OA\Property(property="totalScore", type="number", description="店铺总评分", example=""),
	* 									@OA\Property(property="shopCat", type="string", description="店铺主营分类", example="时蔬水果、网上菜场"),
	* 									@OA\Property(property="goods", type="array", description="店铺商品",
	* 									    @OA\Items(
	*                                                    @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                                    @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                                    @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                                                    @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
	* 								    	    )
	* 								     ),
	* 					               )
     *                          ),
     *                     )
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
	public function listShopQuery(){
		$m = new SM();
        $userId = model('app/index')->getUserId();
		$page = $m->listShopQuery($userId);
		if(!empty($page['data'])){
		foreach($page['data'] as $k=>$v){
			$page['data'][$k]['shopImg'] = WSTImg($v['shopImg'],3);
			if(!empty($v['goods'])){
				foreach($v['goods'] as $k1=>$v1){
                    $page['data'][$k]['goods'][$k1]['goodsImg'] = WSTImg($v1['goodsImg'],3);
				}
			}
		}
		}
		echo(json_encode(WSTReturn('success',1,$page)));die;
	}
	/**
     * @OA\Post(
     *     tags={"Favorites"},
     *     path="/app/favorites/cancel",
     *     summary="取消关注商品或店铺",
     *     description="取消关注商品或店铺",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 * 	   @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="待取关的关注表主键id，多个id以,(逗号)隔开", example="1"),
	 * 	   @OA\Parameter(name="type", in="query", @OA\Schema(type="integer"), required=true, description="0:取关商品 1:取关店铺", example="1"),
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
	public function cancel(){
		$userId = model('app/users')->getUserId();
          if((int)input("param.type")==0){
     		$rs = model('common/Favorites')->del($userId);
     		return json_encode($rs);
          }else{
               $rs = model('common/ShopMembers')->del($userId);
               return json_encode($rs);
          }
	}
	/**
     * @OA\Post(
     *     tags={"Favorites"},
     *     path="/app/favorites/add",
     *     summary="关注商品或店铺",
     *     description="关注商品或店铺",
	 * 	   @OA\Parameter(ref="#/components/parameters/tokenId"),
	 * 	   @OA\Parameter(name="type", in="query", @OA\Schema(type="integer"), required=true, description="0:商品 1:店铺", example="1"),
	 * 	   @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="关注的目标id，type为0时传递商品id(goodsId);type为1时传递店铺id(shopId)", example="1"),
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
	public function add(){
		$userId = model('app/users')->getUserId();
          if((int)input("param.type")==0){
     		$rs = model('common/Favorites')->add($userId);
     		return json_encode($rs);
          }else{
               $rs = model('common/ShopMembers')->add($userId);
               return json_encode($rs);
          }
	}
}
