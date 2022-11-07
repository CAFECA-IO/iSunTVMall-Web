<?php
namespace addons\wstim\model;
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
 * 店铺客服评分
 */
use think\Model;
use think\Db;
use wstmart\admin\validate\Users;
class ServiceEvaluates extends Model {
	/**
    * 用户评分
    */
    public function commitEval($userId){
        $data =	input('param.');
        $serviceId = (int)input('serviceId');
        $shopId = (int)input('shopId');
        if(!isset($data['score']))return WSTReturn(lang('wstim_op_err'));
        $data['userId'] = $userId;
        if($serviceId==0){
            // 无客服接待，不写入数据库
            return WSTReturn(lang('wstim_thank_you_for_your_rating'), 1);
        }else{
            // 检测客服id与店铺id是否匹配
            $isShopService = Db::name('shop_services')->where(['serviceId'=>$serviceId, 'shopId'=>$shopId])->find();
            if(empty($isShopService))return WSTReturn(lang('wstim_err1'));
        }
        // 评分写入
        $rs = $this->field(true)->insert($data);
        if($rs===false)return WSTReturn(lang('wstim_op_err'));
        return WSTReturn(lang('wstim_thank_you_for_your_rating'), 1);
    }
    /**
     * 查看店铺客服评分
     */
	public function pageQuery(){
		$shopId = (int)session('WST_USER.shopId');
		// 店铺客服id
        $serviceIds = $this->where(['shopId'=>$shopId])->column('serviceId');
        // 查询客服评分
        $page = $this->whereIn('serviceId', $serviceIds)
                     ->group('serviceId')
                     ->where([['serviceId','<>',0]])
                     ->field('*,sum(score) as score')
                     ->paginate(input('limit/d'))
                     ->toArray();
        foreach ($page['data'] as $k => $v) {
            $page['data'][$k]['loginName'] = Db::name('users')->where(['userId'=>$v['serviceId']])->value('loginName');
        }
		return $page;
    }
}