<?php 
namespace wstmart\shop\validate;
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
 * 评价验证器
 */
class SupplierGoodsAppraises extends Validate{
	protected $rule = [
		'goodsScore'  => 'between:1,5',
		'serviceScore'   => 'between:1,5',
		'timeScore' => 'between:1,5',
		'content' => 'require|length:3,600'
	];
	
	protected $message  =   [
		'goodsScore.between'   => '{%between_goodsScore}',
		'serviceScore.between' => '{%between_serviceScore}',
		'timeScore.between' => '{%between_timeScore}',
		'content.require'   => '{%require_content}',
		'content.length'   => '{%length_content}'
	];
	
    protected $scene = [
        'add'   =>  ['goodsScore','serviceScore','timeScore','content']
    ];
}