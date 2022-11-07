<?php

namespace wstmart\shop\validate;

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
class Stores extends Validate
{
    protected $rule = [
        'loginName'  => 'require|min:3|max:20|checkLoginName:1',
        'loginPwd'  => 'require|min:6',
        'storeName' => 'require|max:50',
        'areaId' => 'require',
        'storeAddress' => 'require|max:100',
        'longitude' => 'checkLocation',
        'latitude' => 'checkLocation',
        'mapLevel' => 'checkLocation',
        'storeImg' => 'require',
        'staffStatus' => 'require|in:0,1',
    ];

    protected $message = [
        'loginName.require' => '{%require_loginName}',
        'loginName.max' => '{%max_loginName}',
        'loginName.min' => '{%min_loginName}',
        'loginPwd.require' => '{%require_loginPwd}',
        'loginPwd.min' => '{%min_loginPwd}',
        'storeName.require' => '{%require_storeName}',
        'storeName.max' => '{%max_storeName}',
        'areaId.require' => '{%require_areaId}',
        'longitude.checkLocation' => '{%checkLocation_longitude}',
        'latitude.checkLocation' => '{%checkLocation_latitude}',
        'mapLevel.checkLocation' => '{%checkLocation_mapLevel}',
        'staffStatus.require' => '{%require_staffStatus}',
        'storeImg.require' => '{%require_storeImg}',
        'staffStatus.in' => '{%in_staffStatus}',
    ];

    protected $scene = [
        'add'   =>  ['loginName', 'loginPwd', 'storeName', 'areaId', 'storeAddress', 'longitude', 'latitude', 'mapLevel', 'storeImg', 'storeAddress'],
        'edit'  =>  ['storeName', 'areaId', 'storeAddress', 'longitude', 'latitude', 'mapLevel', 'storeImg', 'storeAddress']
    ];

    protected function checkLoginName($value)
    {
        $where = [];
        $where['dataFlag'] = 1;
        $where['loginName'] = $value;
        $rs = Db::name('staffs')->where($where)->count();
        return ($rs == 0) ? true : lang('data_exists');
    }

    protected function checkLocation($value)
    {
        $longitude = (float)input('post.longitude', 0);
        $latitude = (float)input('post.latitude', 0);
        $mapLevel = input('post.mapLevel', 0);
        if (WSTConf('CONF.mapKey') == '') {
            return true;
        } else {
            return ($longitude == 0 ||  $latitude == 0 || $mapLevel == 0) ? lang('please_select_latitude_and_longitude') : true;
        }
    }
}
