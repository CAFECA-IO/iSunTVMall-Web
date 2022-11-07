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
 * 快递验证器
 */
class express extends Validate{
	protected $rule = [
        'langParams' => 'require|checkLangParams',
    ];
    
    protected $message = [
        'langParams.require' => '{%require_express_name}',
        'langParams.checkLangParams' => '{%require_express_name1}',
    ];

    function checkLangParams(){
       $langs = WSTSysLangs();
       $langParams = input('langParams');
       foreach ($langs as $key => $v) {
           if(!isset($langParams[$v['id']]) || !isset($langParams[$v['id']]['expressName'])  || $langParams[$v['id']]['expressName']=='')return lang('require_express_name');
       }
       return true;
    }

    protected $scene = [
        'add'   =>  ['langParams'],
        'edit'  =>  ['langParams'],
    ]; 
}