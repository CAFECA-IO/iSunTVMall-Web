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
 * 规格类型验证器
 */
class SpecCats extends Validate{
	protected $rule = [
        'langParams' => 'require|checkLangParams',
		'goodsCatId' => 'require|gt:0',
		'isAllowImg' => 'number|in:0,1',
		'isShow' => 'number|in:0,1',
	];

	protected $message = [
        'langParams.require' => '{%require_catName}',
        'goodsCatId.require' => '{%require_goodsCatId}',
        'goodsCatId.gt' => '{%gt_goodsCatId}',
        'isAllowImg.number' => '{%number_isAllowImg}',
        'isAllowImg.in' => '{%in_isAllowImg}',
        'isShow.number' => '{%number_isShow}',
        'isShow.in' => '{%in_isShow}',
	];

    function checkLangParams(){
        $langs = WSTSysLangs();
        $langParams = input('langParams');
        foreach ($langs as $key => $v) {
            if(!isset($langParams[$v['id']]) || !isset($langParams[$v['id']]['catName'])  || $langParams[$v['id']]['catName']=='')return lang('require_catName');
        }
        return true;
    }

	protected $scene = [
		'add'=>['langParams','goodsCatId','isAllowImg','isShow'],
		'edit'=>['langParams','goodsCatId','isAllowImg','isShow']
	];

}