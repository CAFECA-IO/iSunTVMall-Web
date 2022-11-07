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
 * 商家会员分组验证器
 */
class ShopMemberGroups extends Validate
{
    protected $rule = [
        'groupName' => 'require|max:50',
        'minMoney' => 'checkMoney',
        'maxMoney' => 'checkMoney'
    ];

    protected $message = [
        'groupName.require' => '{%please_enter_the_group_name}',
        'groupName.max' => '{%the_group_name_cannot_exceed_50_characters}',
    ];

    protected function checkMoney($value)
    {
        $minMoney = input("minMoney");
        $maxMoney = input("maxMoney");
        if ((float)$minMoney > (float)$maxMoney) return lang("the_minimum_consumption_cannot_be_greater_than_the_maximum_consumption");
        return true;
    }

    protected $scene = [
        'add'   =>  ['groupName', 'minMoney', 'maxMoney'],
        'edit'  =>  ['groupName', 'minMoney', 'maxMoney'],
    ];
}
