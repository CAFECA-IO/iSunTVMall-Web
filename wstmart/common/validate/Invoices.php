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
 * 发票信息验证器
 */
class Invoices extends Validate{
	protected $rule = [
		'invoiceHead' => 'require',
		'invoiceCode' => 'require',
		'invoiceType' => 'in:0,1|checkInvoiceBankName',
	];
	
	protected $message  =   [
		'invoiceHead.require' => '{%invoice_title_require}',
		'invoiceCode.require' => '{%invoice_no_reuqire}',
		'invoiceType.in' => '{%invoice_type_reuqire}'
	];

    protected $scene = [
        'add' => ['invoiceHead', 'invoiceCode', 'invoiceType'],
        'edit' => ['invoiceHead', 'invoiceCode', 'invoiceType']
    ];

    /**
     * 当发票类型不是普通
     */
    public function checkInvoiceBankName($value){
    	$invoiceType = (int)input('post.invoiceType');
    	if($invoiceType == 1){
    		if (input('post.invoiceAddr') == '') return '{%invoice_address_require}';
    		if (input('post.invoicePhoneNumber') == '') return '{%invoice_tel_require}';
    		if (input('post.invoiceBankName') == '') return '{%invoice_bank_require}';
    		if (input('post.invoiceBankNo') == '') return '请填写发票银行账户';
    	}
    	return true;
    }
}