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
 * 订单投诉验证器
 */
class Informs extends Validate{
	protected $rule = [
		'informType'  => 'in:1,2,3,4',
		'informContent'   => 'require|length:3,600',
		'respondContent' => 'require|length:3,600'
	];
	
	protected $message  =   [
		'informType.in'   => '{%invalid_complaint_type}',
		'informContent.require' => '{%require_informs_content}',
		'informContent.length' => '{%informs_content_tips}',
		'respondContent.require'   => '{%informs_respond_content_require}',
		'respondContent.length' => '{%informs_respond_content_require2}'
	];

    protected $scene = [
        'add'   =>  ['informType','informContent'],
        'edit'   =>  ['informType','informContent'],
        'respond' =>['respondContent']
    ]; 
}