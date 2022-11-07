<?php
namespace wstmart\app\controller;
use wstmart\app\model\CashConfigs as M;
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
 * 提现账号控制器
 */
class Cashconfigs extends Base{
    // 前置方法执行列表
    protected $beforeActionList = ['checkAuth'];
    /**
     * @OA\Get(
     *     tags={"Cashconfigs"},
     *     path="/app/cashconfigs/index",
     *     summary="获取省市县、可选银行",
     *     description="获取后台设置的省市县、可选银行",
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
     *                          @OA\Property(property="areas",type="array",description="获取省",
	 * 								@OA\Items(
	 * 									 @OA\Property(property="areaId", type="integer", description="地区id", example="2"),
	 * 									 @OA\Property(property="areaName", type="string", description="地区名称", example="北京市"),
	 * 									 @OA\Property(property="parentId", type="integer", description="父级id", example="0"),
	 * 								)
     *                          ),
     *                          @OA\Property(property="banks",type="array",description="获取省",
	 * 								@OA\Items(
	 * 									 @OA\Property(property="bankId", type="integer", description="银行id", example="17"),
	 * 									 @OA\Property(property="bankName", type="string", description="银行名称", example="中国工商银行"),
	 * 									 @OA\Property(property="bankImg", type="integer", description="银行图标", example=""),
	 * 								)
     *                          ),
     *                      )
     *
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
    public function index(){
    	$data['areas'] =  model('areas')->listQuery(0);
    	$data['banks'] =  model('banks')->listQuery(0);
    	return json_encode(WSTReturn('success',1,$data));
    }
    /**
     * @OA\Get(
     *     tags={"Cashconfigs"},
     *     path="/app/cashconfigs/pageQuery",
     *     summary="提现账户列表",
     *     description="获取提现账户列表",
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
     *                          @OA\Property(property="userMoney",type="number",description="用户余额"),
     *                          @OA\Property(property="list",type="array",description="提现账号列表",
	 * 								@OA\Items(
	 * 									 @OA\Property(property="bankName", type="string", description="所属银行"),
	 * 									 @OA\Property(property="bankImg", type="string", description="银行图标"),
	 * 									 @OA\Property(property="id", type="integer", description="账户id"),
	 * 									 @OA\Property(property="targetType", type="integer", description="账户类型，0: 用户1: 商家"),
	 * 									 @OA\Property(property="targetId", type="integer", description="用户id"),
	 * 									 @OA\Property(property="accType", type="integer", description="账户类型，1: 支付宝2: 微信3: 银行卡"),
	 * 									 @OA\Property(property="accTargetId", type="integer", description="提现对象id"),
	 * 									 @OA\Property(property="accAreaId", type="integer", description="地址id"),
	 * 									 @OA\Property(property="accNo", type="string", description="支付宝帐号/微信帐号/银行卡号"),
	 * 									 @OA\Property(property="accUser", type="string", description="账户所有人"),
	 * 								)
     *                          ),
     *                          @OA\Property(property="drawCashLimit",type="number",description="最低提现金额"),
     *                          @OA\Property(property="drawCashCommission",type="number",description="提现手续费"),
     *                      )
     *
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
    public function pageQuery(){
        $userId = model('app/users')->getUserId();
        $m = new M();
        $money = model('Users')->getFieldsById($userId,['userMoney','rechargeMoney']);
        $data['userMoney'] = (($money['userMoney']-$money['rechargeMoney'])>0)?sprintf('%.2f',($money['userMoney']-$money['rechargeMoney'])):0;
        $data['list'] = $m->pageQuery(0,$userId);
        // 获取后台限制的最低提现金额
        $data['drawCashLimit'] = (int)WSTConf('CONF.drawCashUserLimit');
        // 获取后台设置的提现手续费
        $data['drawCashCommission'] = (int)WSTConf('CONF.drawCashCommission');
        // 是否开启微信端
        $data['wxenabled'] = WSTConf('CONF.wxenabled');
        return json_encode(WSTReturn('success',1,$data));
    }
    /**
     * @OA\Get(
     *     tags={"Cashconfigs"},
     *     path="/app/cashconfigs/getById",
     *     summary="提现账户列表",
     *     description="获取提现账户列表",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="提现账户id", example="22"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="array",description="提现账号列表",
	 *          		       @OA\Items(
	 *          			       @OA\Property(property="bankName", type="string", description="所属银行"),
	 *          			       @OA\Property(property="bankImg", type="string", description="银行图标"),
	 *          			       @OA\Property(property="id", type="integer", description="账户id"),
	 *          			       @OA\Property(property="targetType", type="integer", description="账户类型，0: 用户1: 商家"),
	 *          			       @OA\Property(property="targetId", type="integer", description="用户id"),
	 *          			       @OA\Property(property="accType", type="integer", description="账户类型，1: 支付宝2: 微信3: 银行卡"),
	 *          			       @OA\Property(property="accTargetId", type="integer", description="提现对象id"),
	 *          			       @OA\Property(property="accAreaId", type="integer", description="地址id"),
	 *          			       @OA\Property(property="accNo", type="string", description="支付宝帐号/微信帐号/银行卡号"),
	 *          			       @OA\Property(property="accUser", type="string", description="账户所有人"),
	 *          			       @OA\Property(property="dataFlag", type="string", description="账户所有人"),
	 *          			       @OA\Property(property="createTime", type="string", description="账户所有人"),
	 *          			       @OA\Property(property="accAreaIdPath", type="string", description="账户地址字符串"),
	 *                 		   )
     *                     ),
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
    public function getById(){
       $id = (int)input('id');
       $m = new M();
       $userId = model('app/users')->getUserId();
       $data = $m->getById($id, $userId);
       return json_encode(WSTReturn('success',1,$data));
    }
    /**
     * @OA\Post(
     *     tags={"Cashconfigs"},
     *     path="/app/cashconfigs/add",
     *     summary="新增提现账户列表",
     *     description="新增提现账户列表",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="accAreaId", in="query", @OA\Schema(type="integer"), required=true, description="地址id", example="22"),
     *     @OA\Parameter(name="accUser", in="query", @OA\Schema(type="string"), required=true, description="账户所有人", example="啊水"),
     *     @OA\Parameter(name="accNo", in="query", @OA\Schema(type="string"), required=true, description="支付宝帐号/微信帐号/银行卡号", example=""),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema")
     *       ),
     *     ),
	 *     @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
    public function add(){
        $m = new M();
        $userId = model('app/users')->getUserId();
        $rs = $m->add($userId);
        return json_encode($rs);
    }
    /**
     * @OA\Post(
     *     tags={"Cashconfigs"},
     *     path="/app/cashconfigs/edit",
     *     summary="编辑提现账户列表",
     *     description="编辑提现账户列表",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="accAreaId", in="query", @OA\Schema(type="integer"), required=true, description="地址id", example="22"),
     *     @OA\Parameter(name="accUser", in="query", @OA\Schema(type="string"), required=true, description="账户所有人", example="啊水"),
     *     @OA\Parameter(name="accNo", in="query", @OA\Schema(type="string"), required=true, description="支付宝帐号/微信帐号/银行卡号", example=""),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="提现账户id", example="22"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema")
     *       ),
     *     ),
	 *     @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
    public function edit(){
        $m = new M();
        $userId = model('app/users')->getUserId();
        $rs = $m->edit($userId);
        return json_encode($rs);
    }
    /**
     * @OA\Post(
     *     tags={"Cashconfigs"},
     *     path="/app/cashconfigs/del",
     *     summary="删除提现账户列表",
     *     description="删除提现账户列表",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="提现账户id", example="22"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/response_schema")
     *       ),
     *     ),
	 *     @OA\Response(
     *          response=401,
     *          ref="#/components/responses/response_401",
     *     ),
     * )
     */
    public function del(){
        $m = new M();
        $userId = model('app/users')->getUserId();
        $rs = $m->del($userId);
        return json_encode($rs);
    }
}
