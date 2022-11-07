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
 * 广告位置验证器
 */
class AdPositions extends Validate{
	protected $rule = [
	    'positionName' => 'require|max:30',
	    'positionCode' => 'require|max:60',
		'positionType' => 'require',
	    'positionWidth' => 'require|number',
	    'positionHeight' => 'require|number'
    ];
     
    protected $message = [
        'positionName.require' => '{%require_ads_posi_name}',
        'positionName.max' => '{%require_ads_posi_name1}',
        'positionCode.require' => '{%require_ads_posi_code}',
        'positionCode.max' => '{%require_ads_posi_code1}',
        'positionType.require' => '{%require_ads_posi_type}',
        'positionWidth.require' => '{%require_ads_posi_width}',
        'positionWidth.number' => '{%require_ads_posi_width1}',
        'positionHeight.require' => '{%require_ads_posi_height}',
        'positionHeight.number' => '{%require_ads_posi_height1}'

    ];
    
    protected $scene = [
        'add'   =>  ['positionName','positionCode','positionType','positionWidth','positionHeight'],
        'edit'  =>  ['positionName','positionCode','positionType','positionWidth','positionHeight'],
    ]; 
}