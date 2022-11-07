<?php 
namespace wstmart\admin\validate;
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
        'brandName.require' => '{%require_brand_apply_name}',
        'brandName.max' => '{%require_brand_apply_name1}',
        'brandImg.require' => '{%require_upload_img2}',
        'brandDesc.require' => '{%require_brand_apply_desc}',
        'accreditImg.require' => '{%require_brand_apply_grant}',
    ];

    protected $scene = [
        'edit'  =>  ['brandName','brandImg','brandDesc'],
        'join_edit'  =>  ['accreditImg'],
    ];
}