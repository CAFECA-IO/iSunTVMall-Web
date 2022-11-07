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
 * 文章验证器
 */
class Articles extends Validate{
	protected $rule = [
	    'langPrams' => 'require|checkLangParams:1',
    ];
     
    protected $message = [
        'langParams.require' => '{%require_article_name}',
        'langParams.checkLangParams' => '{%require_article_name}'
    ];
    function checkLangParams(){
       $langs = WSTSysLangs();
       $langParams = input('langParams');
       foreach ($langs as $key => $v) {
           if(!isset($langParams[$v['id']]) || !isset($langParams[$v['id']]['articleTitle'])  || $langParams[$v['id']]['articleTitle']=='')return lang('require_article_name');
           if(!isset($langParams[$v['id']]) || !isset($langParams[$v['id']]['articleKey'])  || $langParams[$v['id']]['articleKey']=='')return lang('require_article_key_word');
           if(!isset($langParams[$v['id']]) || !isset($langParams[$v['id']]['articleDesc'])  || $langParams[$v['id']]['articleDesc']=='')return lang('require_article_desc');
           if(!isset($langParams[$v['id']]) || !isset($langParams[$v['id']]['articleContent'])  || $langParams[$v['id']]['articleContent']=='')return lang('require_article_content');
       }
       return true;
    }
    protected $scene = [
        'add'   =>  ['langParams'],
        'edit'  =>  ['langParams']
    ]; 
}