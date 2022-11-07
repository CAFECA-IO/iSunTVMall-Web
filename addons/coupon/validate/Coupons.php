<?php
namespace addons\coupon\validate;
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
 * 优惠券验证器
 */
class Coupons extends Validate{
	protected $rule = [
        'couponValue' => 'checkInteger:1',
        'useCondition' => 'in:,0,1',
        'useMoney' => 'requireIf:useCondition,1',
        'useMoney' => 'checkUseMoney:1',
        'couponNum' => 'gt:0',
        'limitNum' => 'egt:0',
        'startDate' => 'require',
        'endDate' => 'require',
        'useObjects' => 'in:,0,1',
        'useObjectIds' => 'requireIf:useObjects,1',
        'useObjectIds' => 'checkUseObjectIds:1',
        'couponType' =>'in:,1,2'
    ];

	protected $message  =   [
        'couponValue.checkInteger' => '',
        'useCondition.in' => '非法的参数',
        'useMoney.requireIf' => '请输入满减金额',
        'useMoney.checkUseMoney' => '',
        'couponNum.gt' => '发行量必须大于0',
        'limitNum.egt' => '每人限领数不能为负数',
        'startDate.require' => '请输入有效期开始日期',
        'endDate.require' => '请输入有效期结束日期',
        'useObjects.in' => '非法的参数',
        'useObjectIds.requireIf' => '请选择优惠券适用的商品',
        'useObjectIds.checkUseObjectIds' => '',
        'couponType.in' => '非法的优惠券发放方式',
	];

    /**
     * 检测面值
     */
    protected function checkInteger($value){
    	$couponValue = Input('post.couponValue/d',0);
    	if($couponValue<=0)return '优惠券面值必须大于0';
    	return true;
    }
    protected function checkUseMoney($value){
        $useCondition = Input('post.useCondition/d',0);
        $useMoney = Input('post.useMoney/d',0);
        $couponValue = Input('post.couponValue/d',0);
        if($useCondition==1 && $useMoney<=0)return '满减金额必须大于0';
        if($useCondition==1){
            if($couponValue>$useMoney){
                return '优惠金额不能比使用条件值还大';
            }
        }
        return true;
    }
    public function checkUseObjectIds(){
        $useObjects = input('post.useObjects/d');
        $useObjectIds = input('post.useObjectIds');
        if($useObjects==1 && $useObjectIds=='')return '请选择优惠券适用的商品';
        return true;
    }
}
