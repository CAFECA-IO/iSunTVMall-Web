<?php
namespace wstmart\app\controller;
use wstmart\common\model\Invoices as M;
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
 * 发票信息控制器
 */
class Invoices extends Base{
    // 前置方法执行列表
    protected $beforeActionList = [
        'checkAuth'
    ];
    /**************************************************** 结算页操作 ****************************************************/
    /**
     * @OA\Get(
     *     tags={"Invoices"},
     *     path="/app/Invoices/pageQuery",
     *     summary="获取发票列表",
     *     description="获取发票列表",
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
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="array",
     *                                     @OA\Items(
     *                                          @OA\Property(property="id", type="integer", description="主键id"),
     *                                          @OA\Property(property="invoiceHead", type="string", description="发票抬头"),
     *                                          @OA\Property(property="invoiceCode", type="string", description="发票税号"),
     *                                          @OA\Property(property="userId", type="integer", description="用户id"),
     *                                          @OA\Property(property="invoiceType", type="integer", description="发票类别，0:普票 1:专票"),
     *                                          @OA\Property(property="invoiceAddr", type="string", description="注册地址"),
     *                                          @OA\Property(property="invoicePhoneNumber", type="string", description="注册电话"),
     *                                          @OA\Property(property="invoiceBankName", type="string", description="开户银行"),
     *                                          @OA\Property(property="invoiceBankNo", type="string", description="银行账户"),
     *                                          @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                                          @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
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
        $page = (int)input('page', 5);
        $m = new M();
        $userId = model('app/Users')->getUserId();
        $rs = $m->pageQuery($page, $userId);
        return json_encode(WSTReturn('success',1,$rs));
    }
    /**
     * @OA\Post(
     *     tags={"Invoices"},
     *     path="/app/Invoices/add",
     *     summary="新增发票",
     *     description="新增发票",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="invoiceHead", in="query", @OA\Schema(type="string"), required=true, description="发票抬头", example=""),
     *     @OA\Parameter(name="invoiceCode", in="query", @OA\Schema(type="string"), required=true, description="发票税号", example=""),
     *     @OA\Parameter(name="invoiceType", in="query", @OA\Schema(type="integer"), required=true, description="发票类别，0:普票 1:专票", example="0"),
     *     @OA\Parameter(name="invoiceAddr", in="query", @OA\Schema(type="string"), required=true, description="注册地址，invoiceType=1时该值必填"),
     *     @OA\Parameter(name="invoicePhoneNumber", in="query", @OA\Schema(type="string"), required=true, description="注册电话，invoiceType=1时该值必填"),
     *     @OA\Parameter(name="invoiceBankName", in="query", @OA\Schema(type="string"), required=true, description="开户银行，invoiceType=1时该值必填"),
     *     @OA\Parameter(name="invoiceBankNo", in="query", @OA\Schema(type="string"), required=true, description="银行账户，invoiceType=1时该值必填"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *     )
     * )
     */
    public function add(){
        $m = new M();
        $userId = model('app/Users')->getUserId();
        $rs = $m->add($userId);
        return json_encode($rs);
    }
    /**
     * @OA\Post(
     *     tags={"Invoices"},
     *     path="/app/Invoices/edit",
     *     summary="修改发票",
     *     description="修改发票",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="发票id", example=""),
     *     @OA\Parameter(name="invoiceHead", in="query", @OA\Schema(type="string"), required=true, description="发票抬头", example=""),
     *     @OA\Parameter(name="invoiceCode", in="query", @OA\Schema(type="string"), required=true, description="发票税号", example=""),
     *     @OA\Parameter(name="invoiceType", in="query", @OA\Schema(type="integer"), required=true, description="发票类别，0:普票 1:专票", example="0"),
     *     @OA\Parameter(name="invoiceAddr", in="query", @OA\Schema(type="string"), required=true, description="注册地址，invoiceType=1时该值必填"),
     *     @OA\Parameter(name="invoicePhoneNumber", in="query", @OA\Schema(type="string"), required=true, description="注册电话，invoiceType=1时该值必填"),
     *     @OA\Parameter(name="invoiceBankName", in="query", @OA\Schema(type="string"), required=true, description="开户银行，invoiceType=1时该值必填"),
     *     @OA\Parameter(name="invoiceBankNo", in="query", @OA\Schema(type="string"), required=true, description="银行账户，invoiceType=1时该值必填"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *     )
     * )
     */
    public function edit(){
        $m = new M();
        $userId = model('app/Users')->getUserId();
        $rs = $m->edit($userId);
        return json_encode($rs);
    }
    /**
     * @OA\Post(
     *     tags={"Invoices"},
     *     path="/app/Invoices/del",
     *     summary="删除发票",
     *     description="删除发票",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="发票id", example=""),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Schema(ref="#/components/schemas/response_schema")
     *      )
     * )
     */
    public function del(){
        $m = new M();
        $userId = model('app/Users')->getUserId();
        $rs = $m->del($userId);
        return json_encode($rs);
    }
    /**
     * @OA\Get(
     *     tags={"Invoices"},
     *     path="/app/Invoices/getById",
     *     summary="获取发票数据",
     *     description="获取发票数据",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="发票id", example="1"),
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
     *                                @OA\Property(property="data",type="object",
     *                                    @OA\Property(property="id", type="integer", description="主键id"),
     *                                    @OA\Property(property="invoiceHead", type="string", description="发票抬头"),
     *                                    @OA\Property(property="invoiceCode", type="string", description="发票税号"),
     *                                    @OA\Property(property="userId", type="integer", description="用户id"),
     *                                    @OA\Property(property="invoiceType", type="integer", description="发票类别，0:普票 1:专票"),
     *                                    @OA\Property(property="invoiceAddr", type="string", description="注册地址"),
     *                                    @OA\Property(property="invoicePhoneNumber", type="string", description="注册电话"),
     *                                    @OA\Property(property="invoiceBankName", type="string", description="开户银行"),
     *                                    @OA\Property(property="invoiceBankNo", type="string", description="银行账户"),
     *                                    @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                                    @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
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
        $userId = model('app/Users')->getUserId();
        $id = (int)input('id');
        $rs = $m->getById($id, $userId);
        return json_encode(WSTReturn('success',1,$rs));
    }
    /**************************************************** 发票管理操作 ****************************************************/
    



}
