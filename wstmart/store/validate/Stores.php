<?php 
namespace wstmart\store\validate;
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
class Stores extends Validate{
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
        'loginName.require' => '{%require_login_name}',
		'loginName.max' => '{%login_name_min_length}',
        'loginName.max' => '{%login_name_max_length}',
        'loginPwd.require' => '{%require_login_pwd}',
        'loginPwd.min' => '{%login_pwd_min_length}',
        'storeName.require' => '{%require_store_name}',
        'storeName.max' => '{%store_name_max_length}',
		'areaId.require' => '{%require_area_id}',
		'longitude.checkLocation' => '{%require_location}',
        'latitude.checkLocation' => '{%require_location}',
        'mapLevel.checkLocation' => '{%require_location}',
        'staffStatus.require' => '{%require_staff_status}',
        'storeImg.require' => '{%require_store_img}',
        'staffStatus.in' => '{%in_staff_status}',
    ];
    
    protected $scene = [
        'add'   =>  ['loginName','loginPwd','storeName','areaId','storeAddress','longitude','latitude','mapLevel','storeImg','storeAddress'],
        'edit'  =>  ['storeName','areaId','storeAddress','longitude','latitude','mapLevel','storeImg','storeAddress']
    ]; 
    
    protected function checkLoginName($value){
    	$where = [];
    	$where['dataFlag'] = 1;
    	$where['loginName'] = $value;
    	$rs = Db::name('staffs')->where($where)->count();
    	return ($rs==0)?true:lang("login_name_exists");
    }
	
	protected function checkLocation($value){
        $longitude = (float)input('post.longitude',0);
        $latitude = (float)input('post.latitude',0);
        $mapLevel = input('post.mapLevel',0);
        if(WSTConf('CONF.mapKey') == ''){
            return true;
        }else{
            return ($longitude==0 ||  $latitude==0 || $mapLevel==0)?'{%require_location}':true;
        }

    }
}