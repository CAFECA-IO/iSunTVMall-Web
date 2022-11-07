<?php

namespace wstmart\admin\validate;

use think\Validate;
use think\Db;

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
 * 职员验证器
 */
class Staffs extends Validate
{
    protected $rule = [
        'loginName'  => 'require|max:20|checkLoginName:1',
        'loginPwd'  => 'require|min:8|checkPwdRule:1',
        'staffName' => 'require|max:60',
        'workStatus' => 'require|in:0,1',
        'staffStatus' => 'require|in:0,1',
    ];

    protected $message = [
        'loginName.require' => '{%require_loginName}',
        'loginName.max' => '{%max_loginName}',
        'loginPwd.require' => '{%require_loginPwd}',
        'loginPwd.min' => '{%min_loginPwd}',
        'staffName.require' => '{%require_staffName}',
        'staffName.max' => '{%max_staffName}',
        'workStatus.require' => '{%require_workStatus}',
        'workStatus.in' => '{%in_workStatus}',
        'staffStatus.require' => '{%require_staffStatus}',
        'staffStatus.in' => '{%in_staffStatus}',
    ];

    protected $scene = [
        'add'   =>  ['loginName', 'loginPwd', 'staffName', 'workStatus', 'staffStatus'],
        'edit'  =>  ['staffName', 'workStatus', 'staffStatus']
    ];

    protected function checkLoginName($value)
    {

        $where = [];
        $where['dataFlag'] = 1;
        $where['loginName'] = $value;
        $rs = Db::name('staffs')->where($where)->count();
        return ($rs == 0) ? true : lang('the_login_account_already_exists');
    }

    protected function checkPwdRule($value)
    {
        return WSTCheckPwdRule($value);
    }
}
