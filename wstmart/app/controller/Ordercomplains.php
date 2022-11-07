<?php
namespace wstmart\app\controller;
use wstmart\common\model\OrderComplains as M;
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
 * 投诉控制器
 */
class orderComplains extends Base{
    // 前置方法执行列表
    protected $beforeActionList = [
        'checkAuth'
    ];
    /**
     * @OA\Post(
     *     tags={"orderComplains"},
     *     path="/app/orderComplains/saveComplain",
     *     summary="保存订单投诉信息",
     *     description="保存订单投诉信息",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="orderId", in="query", @OA\Schema(type="integer"), required=true, description="订单id"),
     *     @OA\Parameter(name="complainType", in="query", @OA\Schema(type="integer"), required=true, description="投诉类型"),
     *     @OA\Parameter(name="complainAnnex", in="query", @OA\Schema(type="string"), required=true, description="投诉附件"),
     *     @OA\Parameter(name="complainContent", in="query", @OA\Schema(type="string"), required=true, description="投诉内容"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
    public function saveComplain(){
        $m = new M(); 
        $userId = (int)model('app/index')->getUserId();
        $rs = $m->saveComplain($userId);
        return json_encode($rs);
    }

    /**
     * @OA\Get(
     *     tags={"orderComplains"},
     *     path="/app/orderComplains/complainByPage",
     *     summary="获取用户投诉列表",
     *     description="获取用户投诉列表",
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
     *                                        @OA\Property(property="complainId", type="integer", description="主键id"),
     *				                          @OA\Property(property="orderNo", type="string", description="订单号"),
     *				                          @OA\Property(property="shopName", type="string", description="被投诉的店铺名称"),
     *				                          @OA\Property(property="complainStatus", type="string", description="处理状态 0:新投诉 1:转给应诉人 2:应诉人回应  3:等待仲裁  4:已仲裁"),
     *				                          @OA\Property(property="complainTime", type="string", description="投诉时间"),
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
    public function complainByPage(){
        $m = new M();
        $userId = (int)model('app/index')->getUserId();
        $data = $m->queryUserComplainByPage($userId);
        return json_encode($data);
    }

    /**
     * @OA\Get(
     *     tags={"orderComplains"},
     *     path="/app/orderComplains/getComplainDetail",
     *     summary="用户获取投诉详情",
     *     description="用户获取投诉详情",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="投诉表主键id", example="22"),
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
     *                                @OA\Property(property="list",type="array",
	 *                                    @OA\Items(
     *                                        @OA\Property(property="complainId", type="integer", description="主键id"),
     *                                        @OA\Property(property="complainTargetId", type="integer", description="投诉人id"),
     *                                        @OA\Property(property="respondTargetId", type="integer", description="应诉人id"),
     *                                        @OA\Property(property="orderId", type="integer", description="订单id"),
     *                                        @OA\Property(property="complainType", type="integer", description="1: 承诺的没有做到2: 未按约定时间发货3: 未按成交价格进行交易4: 恶意骚扰"),
     *				                          @OA\Property(property="complainContent", type="string", description="投诉内容"),
     *				                          @OA\Property(property="complainAnnex", type="array", description="投诉附件",
     *                                            @OA\Items()
     *                                        ),
     *				                          @OA\Property(property="complainStatus", type="string", description="处理状态 0:新投诉 1:转给应诉人 2:应诉人回应  3:等待仲裁  4:已仲裁"),
     *				                          @OA\Property(property="respondContent", type="string", description="应诉内容"),
     *                                        @OA\Property(property="respondAnnex", type="array", description="应诉附件",
     *                                            @OA\Items()
     *                                        ),
     *				                          @OA\Property(property="respondTime", type="string", description="应诉时间"),
     *				                          @OA\Property(property="orderNo", type="string", description="订单号"),
     *				                          @OA\Property(property="shopName", type="string", description="被投诉的店铺名称"),
     *				                          @OA\Property(property="complainTime", type="string", description="投诉时间"),
     *				                          @OA\Property(property="finalResult", type="string", description="仲裁结果"),
     *				                          @OA\Property(property="finalResultTime", type="string", description="仲裁时间"),
     *				                          @OA\Property(property="finalHandleStaffId", type="string", description="仲裁人id"),
     *				                          @OA\Property(property="realTotalMoney", type="string", description="实付金额"),
     *				                          @OA\Property(property="deliverMoney", type="string", description="运费"),
     *				                          @OA\Property(property="goodsList", type="array", description="订单下的商品列表",
     *                                            @OA\Items()
     *                                        ),
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
    public function getComplainDetail(){
        $m = new M();
        $userId = (int)model('app/index')->getUserId();
        $rs['list'] = $m->getComplainDetail(0, 0, $userId);
        $annex = $rs['list']['complainAnnex'];
        if($annex){
        	foreach($annex as $k=>$v){
        		$annex1[] = WSTImg($v,2);
        	}
        	$rs['list']['complainAnnex'] = $annex1;
        }
        return json_encode(WSTReturn('success',1,$rs));
    }

}
