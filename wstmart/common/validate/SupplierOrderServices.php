<?php 
namespace wstmart\common\validate;
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
		'goodsServiceType.in'   => '{%service_type_error}',
		'serviceRemark.require' => '{%service_content_require}',
		'serviceRemark.length' => '{%service_content_require2}',
		
		'isSupplierAgree.in'   => '{%service_shop_audit_val_err}',
		'supplierAddress.requireIf' => '{%service_shop_address_require}',
		'supplierName.requireIf' => '{%service_shop_user_require}',
		'supplierPhone.requireIf' => '{%service_shop_linkman_require}',
		'disagreeRemark.requireIf' => '{%service_shop_reject_content_require}',

		'expressType.in'   => '{%service_shop_express_type_require}',
		'expressId.requireIf'   => '{%service_shop_express_require}',
		'expressNo.requireIf'   => '{%service_shop_express_no_require}',
		
		'isSupplierAccept.in'   => '{%service_receive_error}',
		'supplierRejectType.require'   => '{%service_reject_type}',
		'supplierRejectOther.requireIf'   => '{%service_reject_content_require}',

		
		'supplierExpressType.in'=>'{%service_shop_express_type_require}',
		'supplierExpressId.requireIf'   => '{%service_shop_express_require}',
		'supplierExpressNo.requireIf'   => '{%service_shop_express_no_require}',
		
		'isUserAccept.in'   => '{%service_receive_error}',
		'userRejectType.require'   => '{%service_reject_type}',
		'userRejectOther.requireIf'   => '{%service_reject_content_require}',
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