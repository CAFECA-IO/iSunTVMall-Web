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
 * 品牌申请验证器
 */
class BrandApplys extends Validate{
	protected $rule = [
	    'brandName' => 'require|max:60',
		'brandImg'  => 'require',
		'brandDesc' => 'require',
		'accreditImg' => 'require',
    ];
    
    protected $message = [
        'brandName.require' => '{%please_enter_the_brand_name}',
        'brandName.max' => '{%brand_name_cannot_exceed_20_characters}',
        'brandImg.require' => '{%please_upload_brand_icon}',
        'brandDesc.require' => '{%please_input_the_brand_introduction}',
        'accreditImg.require' => '{%please_upload_the_brand_authorization}',
    ];

    protected $scene = [
        'add'   =>  ['brandName','brandImg','brandDesc'],
        'edit'  =>  ['brandName','brandImg','brandDesc'],
        'join_add' => ['brandName','brandImg','brandDesc','accreditImg'],
        'join_edit' => ['brandName','brandImg','brandDesc','accreditImg'],
    ]; 
}