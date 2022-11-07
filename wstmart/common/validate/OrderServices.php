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
class OrderServices extends Validate{
	protected $rule = [
		'goodsServiceType'  => 'in:0,1,2',
		'serviceRemark'   => 'require|length:3,600',

		'isShopAgree' => 'in:0,1',
		'shopAddress'=>'requireIf:isShopAgree,1',
		'shopName'=>'requireIf:isShopAgree,1',
		'shopPhone'=>'requireIf:isShopAgree,1',
		'disagreeRemark'=>'requireIf:isShopAgree,0',

		'expressType'=>'require|in:0,1',
		'expressId'=>'requireIf:expressType,1',
		'expressNo'=>'requireIf:expressType,1',

		'isShopAccept'=>'require|in:-1,1',
		'shopRejectType'=>'require',
		'shopRejectOther'=>'requireIf:shopRejectType,10000',

		'shopExpressType'=>'require|in:0,1',
		'shopExpressId'=>'requireIf:shopExpressType,1',
		'shopExpressNo'=>'requireIf:shopExpressType,1',

		'isUserAccept'=>'require|in:-1,1',
		'userRejectType'=>'require',
		'userRejectOther'=>'requireIf:userRejectType,10000',
	];
	
	protected $message  =   [
		'goodsServiceType.in'   => '{%service_type_error}！',
		'serviceRemark.require' => '{%service_content_require}',
		'serviceRemark.length' => '{%service_content_require2}',
		
		'isShopAgree.in'   => '{%service_shop_audit_val_err}',
		'shopAddress.requireIf' => '{%service_shop_address_require}',
		'shopName.requireIf' => '{%service_shop_user_require}',
		'shopPhone.requireIf' => '{%service_shop_linkman_require}',
		'disagreeRemark.requireIf' => '{%service_shop_reject_content_require}',

		'expressType.in'   => '{%service_shop_express_type_require}',
		'expressId.requireIf'   => '{%service_shop_express_require}',
		'expressNo.requireIf'   => '{%service_shop_express_no_require}',
		
		'isShopAccept.in'   => '{%service_receive_error}',
		'shopRejectType.require'   => '{%service_reject_type}',
		'shopRejectOther.requireIf'   => '{%service_reject_content_require}',

		
		'shopExpressType.in'=>'{%service_shop_express_type_require}',
		'shopExpressId.requireIf'   => '{%service_shop_express_require}',
		'shopExpressNo.requireIf'   => '{%service_shop_express_no_require}',
		
		'isUserAccept.in'   => '{%service_receive_error}',
		'userRejectType.require'   => '{%service_reject_type}',
		'userRejectOther.requireIf'   => '{%service_reject_content_require}',
	];
    protected $scene = [
		// 用户提交
		'commit'   =>  ['goodsServiceType','serviceRemark'],
		// 商家受理
		'deal'   =>  ['isShopAgree', 'shopAddress', 'shopName', 'shopPhone', 'disagreeRemark' ],
		// 退款
		'refund' => ['isShopAgree'],
		// 用户发货
		'userExpress' => ['expressType','expressId','expressNo'],
		// 商家是否确认收货
		'shopComfirm' => ['isShopAccept','shopRejectType','shopRejectOther'],
		// 商家发货
		'shopSend' => ['shopExpressType','shopExpressId','shopExpressNo'],
		// 用户确认收货
		'userConfirm' => ['isUserAccept','userRejectType','userRejectOther'],
	]; 




}