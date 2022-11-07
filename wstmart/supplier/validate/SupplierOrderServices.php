<?php 
namespace wstmart\supplier\validate;
use think\Validate;
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
 * 售后服务验证器
 */
class SupplierOrderServices extends Validate{
	protected $rule = [
		'goodsServiceType'  => 'in:0,1,2',
		'serviceRemark'   => 'require|length:3,600',

		'isSupplierAgree' => 'in:0,1',
		'supplierAddress'=>'requireIf:isSupplierAgree,1',
		'supplierName'=>'requireIf:isSupplierAgree,1',
		'supplierPhone'=>'requireIf:isSupplierAgree,1',
		'disagreeRemark'=>'requireIf:isSupplierAgree,0',

		'expressType'=>'require|in:0,1',
		'expressId'=>'requireIf:expressType,1',
		'expressNo'=>'requireIf:expressType,1',

		'isSupplierAccept'=>'require|in:-1,1',
		'supplierRejectType'=>'require',
		'supplierRejectOther'=>'requireIf:supplierRejectType,10000',

		'supplierExpressType'=>'require|in:0,1',
		'supplierExpressId'=>'requireIf:supplierExpressType,1',
		'supplierExpressNo'=>'requireIf:supplierExpressType,1',

		'isUserAccept'=>'require|in:-1,1',
		'userRejectType'=>'require',
		'userRejectOther'=>'requireIf:userRejectType,10000',
	];
	
	protected $message  =   [
		'goodsServiceType.in'   => '{%invalid_service_type}',
		'serviceRemark.require' => '{%require_service_content}',
		'serviceRemark.length' => '{%service_content_length}',
		
		'isSupplierAgree.in'   => '{%invalid_supplier_agree}',
		'supplierAddress.requireIf' => '{%require_order_service_supp_addr}',
		'supplierName.requireIf' => '{%require_order_service_supp_name}',
		'supplierPhone.requireIf' => '{%require_order_service_supp_phone}',
		'disagreeRemark.requireIf' => '{%require_order_service_disagree}',

		'expressType.in'   => '{%invalid_express_type}',
		'expressId.requireIf'   => '{%require_order_service_express}',
		'expressNo.requireIf'   => '{%require_express_no}',
		
		'isSupplierAccept.in'   => '{%invalid_supplier_accept}',
		'supplierRejectType.require'   => '{%require_reject_type}',
		'supplierRejectOther.requireIf'   => '{%require_order_service_reject_reason}',

		
		'supplierExpressType.in'=>'{%invalid_express_type}',
		'supplierExpressId.requireIf'   => '{%require_order_service_express}',
		'supplierExpressNo.requireIf'   => '{%require_express_no}',
		
		'isUserAccept.in'   => '{%invalid_supplier_accept}',
		'userRejectType.require'   => '{%require_reject_type}',
		'userRejectOther.requireIf'   => '{%require_order_service_reject_reason}',
	];
    protected $scene = [
		// 用户提交
		'commit'   =>  ['goodsServiceType','serviceRemark'],
		// 商家受理
		'deal'   =>  ['isSupplierAgree', 'supplierAddress', 'supplierName', 'supplierPhone', 'disagreeRemark' ],
		// 退款
		'refund' => ['isSupplierAgree'],
		// 用户发货
		'userExpress' => ['expressType','expressId','expressNo'],
		// 商家是否确认收货
		'supplierComfirm' => ['isSupplierAccept','supplierRejectType','supplierRejectOther'],
		// 商家发货
		'supplierSend' => ['supplierExpressType','supplierExpressId','supplierExpressNo'],
		// 用户确认收货
		'userConfirm' => ['isUserAccept','userRejectType','userRejectOther'],
	]; 




}