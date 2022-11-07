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
 * 店铺入驻表单字段验证器
 */
class ShopBase extends Validate{
	protected $rule = [
	    'fieldName' => 'require',
	    'dataType' => 'require|checkDataType',
	    'fieldTitle' => 'require',
	    'dataLength' => 'require',
	    'isRequire' => 'require|in:0,1',
	    'isRelevance' => 'in:0,1',
        'fieldType' => 'require|checkFieldType',
	    'fieldAttr' => 'require',
    ];
    protected $message  =   [
	    'fieldName.require' => '{%require_fieldName}',
        'dataType.require' => '{%require_dataType}',
	    'fieldTitle.require' => '{%require_fieldTitle}',
	    'dataLength.require' => '{%require_dataLength}',
	    'isRequire.require' => '{%require_isRequire}',
	    'isRequire.in' => '{%in_isRequire}',
	    'fieldType.require' => '{%require_fieldType}',
	    'fieldAttr.require' => '{%require_fieldAttr}',
    ];
    protected function checkDataType($value){
        $array = ['varchar','char','int','mediumint','smallint','tinyint','text','decimal','date','time'];
        if(!in_array($value,$array))return lang('invalid_data_type');
        return true;
    }
    protected function checkFieldType($value){
        $array = ['input','textarea','radio','checkbox','select','other'];
        if(!in_array($value,$array))return lang('invalid_form_type');
        return true;
    }
    protected $scene = [
        'edit'  =>  ['fieldName','dataType','fieldTitle','dataLength','isRequire','isRelevance','fieldType','fieldAttr'],
    ]; 
}