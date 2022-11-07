<?php
namespace addons\presale\validate;
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
 * 预售验证器
 */
class Presales extends Validate{
	protected $rule = [
        'goodsId' => 'require',
        'goodsImg' => 'require',
        'reduceMoney' => 'egt:0',
        'goodsNum' => 'gt:0',
        'limitNum' => 'egt:0',
        'saleType'=>'checkSaleType:1',
        'startTime' => 'require',
        'endTime' => 'require'
    ];

	protected $message  =   [
        'goodsId.require' => '{%presale_require_goods_id}',
        'goodsImg.require' => '{%presale_require_goods_img}',
        'reduceMoney.egt' => '{%presale_require_reduce_money}',
        'goodsNum.gt'     =>'{%presale_require_goods_num}',
        'limitNum.egt' => '{%presale_require_limit_num}',
        'saleType.checkSaleType' =>'',
        'startTime.require' => '{%presale_require_start_time}',
        'endTime.require' => '{%presale_require_end_time}'
	];

    /**
     * 检测面值
     */
    protected function checkSaleType($value){
    	$saleType = (int)input('post.saleType',0);
    	if($saleType==1){
            $depositType = (int)input('depositType',0);
            if(!in_array($depositType,[0,1]))return lang('presale_invalid_pay_type');
            if($depositType==0 && (float)input('depositMoney')<=0)return lang('presale_deposit_money_must_greater_zero');
            if($depositType==1 && ((int)input('depositRate')<10 || (int)input('depositRate')>90))return lang('presale_invalid_deposit_rate');
            if((int)input('endPayDays')<1)return lang('presale_end_pay_day_tips');
            if((int)input('deliverDays1')<1)return lang('presale_deliver_day_tips');
        }else{
            if((int)input('deliverDays0')<1)return lang('presale_deliver_day_tips');
        }
    	return true;
    }

    public function checkDeliverType(){
        $useObjects = input('post.useObjects/d');
        $useObjectIds = input('post.useObjectIds');
        if($useObjects==1 && $useObjectIds=='')return lang('presale_select_coupon_use_goods');
        return true;
    }
}
