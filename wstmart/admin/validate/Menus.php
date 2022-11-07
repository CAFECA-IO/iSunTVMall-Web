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
 * 菜单验证器
 */
class Menus extends Validate{
	protected $rule = [
        'langParams' => 'require|checkLangs:1',
        'parentId' => 'number',
    ];
    
    protected $message = [
        'langParams.require' => '{%require_sys_menu_name}',
        'langParams.checkLangs' => '{%require_sys_menu_name}',
        'parentId.number' => '{%require_sys_menu_parent}',
    ];
    function checkLangs(){
       $langs = WSTSysLangs();
       $langParams = input('langParams');
       foreach ($langs as $key => $v) {
           if(!isset($langParams[$v['id']]) || !isset($langParams[$v['id']]['menuName'])  || $langParams[$v['id']]['menuName']=='')return lang('require_sys_menu_name');
       }
       return true;
    }
    protected $scene = [
        'add'   =>  ['langParams','parentId'],
        'edit'  =>  ['langParams'],
    ]; 
}