<?php
namespace wstmart\app\controller;
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
 * 资金流水控制器
 */
class Logmoneys extends Base{
	// 前置方法执行列表
    protected $beforeActionList = [
        'checkAuth'
    ];
	/**
     * @OA\Get(
     *     tags={"Logmoneys"},
     *     path="/app/Logmoneys/toRecharge",
     *     summary="充值页面所需数据",
     *     description="充值页面所需数据",
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
     *                      @OA\Property(property="data",
	 * 							@OA\Property(property="payments",type="array",
	 * 							    @OA\Items(
	 * 								    @OA\Property(property="id", type="integer", description="支付表主键id", example="1"),
	 *	                                @OA\Property(property="payCode", type="integer", description="支付code，用户区分支付类型", example="alipays"),
	 *	                                @OA\Property(property="payName", type="integer", description="支付名称 ", example="支付宝支付"),
	 *                                  @OA\Property(property="isOnline", type="integer", description="是否在线支付， 1:是 0:否", example="1")
	 * 								)
	 * 							),
     *                          @OA\Property(property="chargeItems",type="array", description="后台设置的充值项",
	 * 								@OA\Items(
     *                                  @OA\Property(property="id", type="integer", description="主键id"),
     *                                  @OA\Property(property="chargeMoney", type="number", description="充值金额"),
     *                                  @OA\Property(property="giveMoney", type="number", description="赠送金额"),
     *                                  @OA\Property(property="itemSort", type="number", description="排序号"),
     *                                  @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                                  @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
	 * 								)
     *                          ),
     *                      )
     *                      
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    public function toRecharge(){
    	$data = array();
    	$data['payments'] = model('common/payments')->recharePayments('4');
    	$data['chargeItems'] = model('common/ChargeItems')->queryList();
    	return json_encode(WSTReturn('ok',1,$data));
    }
	/**
     * @OA\Get(
     *     tags={"Logmoneys"},
     *     path="/app/Logmoneys/usermoneys",
     *     summary="查看用户资金流水",
     *     description="查看用户资金流水",
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
     *                      @OA\Property(property="data",
	 * 							@OA\Property(property="lockMoney", type="number", description="冻结金额"),
	 * 							@OA\Property(property="userMoney", type="number", description="余额"),
	 * 							@OA\Property(property="withdrawMoney", type="integer", description="是否有设置支付密码，0: 没有支付密码 1: 有支付密码"),
	 * 							@OA\Property(property="isSetPayPwd", type="number", description="可提现金额"),
	 * 							@OA\Property(property="num", type="integer", description="资金流水记录数"),
     *                      )
     *                      
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function usermoneys(){
		$userId = model('app/users')->getUserId();
		$rs = model('Users')->getFieldsById($userId,['lockMoney','userMoney','payPwd','rechargeMoney']);
		$rs['withdrawMoney'] = (($rs['userMoney']-$rs['rechargeMoney'])>0)?sprintf('%.2f',($rs['userMoney']-$rs['rechargeMoney'])):0;
		unset($rs['rechargeMoney']);
		$rs['isSetPayPwd'] = ($rs['payPwd']=='')?0:1;
        unset($rs['payPwd']);
		$rs['num'] = count(model('cashConfigs')->listQuery(0,$userId));
		return json_encode(WSTReturn('success',1,$rs));
	}
	/**
     * @OA\Get(
     *     tags={"Logmoneys"},
     *     path="/app/Logmoneys/checkPayPwd",
     *     summary="验证支付密码",
     *     description="验证支付密码",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 * 	   @OA\Parameter(name="payPwd", in="query", @OA\Schema(type="string"), required=true, description="支付密码", example=""),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema"),
     *       ),
     *     )
     * )
     */
	public function checkPayPwd(){
		$rs = model('app/users')->checkPayPwd();
		return json_encode($rs);
	}
	/**
     * @OA\Get(
     *     tags={"Logmoneys"},
     *     path="/app/Logmoneys/record",
     *     summary="获取用户账户余额及冻结余额",
     *     description="获取用户账户余额及冻结余额",
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
     *                      @OA\Property(property="data",
	 * 							@OA\Property(property="lockMoney", type="number", description="冻结金额"),
	 * 							@OA\Property(property="userMoney", type="number", description="余额"),
     *                      )
     *                      
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function record(){
		$userId = model('app/index')->getUserId();
		$rs = model('Users')->getFieldsById($userId,['lockMoney','userMoney']);
		return json_encode(WSTReturn('ok',1,$rs));
	}
	/**
     * @OA\Get(
     *     tags={"Logmoneys"},
     *     path="/app/Logmoneys/pageQuery",
     *     summary="获取资金流水记录",
     *     description="获取资金流水记录",
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
     *                                        @OA\Property(property="remark", type="integer", description="资金流水备注"),
     *                                        @OA\Property(property="moneyType", type="integer", description="1:收入 0:支出"),
     *                                        @OA\Property(property="money", type="integer", description="金额"),
     *                                        @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
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
		$userId = model('app/index')->getUserId();
		$data = model('LogMoneys')->pageQuery("",$userId);
		if(!empty($data['data'])){
			foreach($data['data'] as $k=>$v){
				// 删除无用字段
				unset($data['data'][$k]['dataFlag']);
				unset($data['data'][$k]['targetType']);
				unset($data['data'][$k]['targetId']);
				unset($data['data'][$k]['dataId']);
				unset($data['data'][$k]['dataSrc']);
				unset($data['data'][$k]['id']);
				unset($data['data'][$k]['payType']);
				unset($data['data'][$k]['tradeNo']);
			}
		}
		return json_encode(WSTReturn("ok", 1,$data));
	}
}
