<?php
namespace addons\combination\controller;
use think\addons\Controller;
use addons\combination\model\Combinations as M;
use wstmart\common\model\UserAddress;
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
 * 插件控制器
 */
class Apis extends Controller{
	/**
	* APP请求检测是否有安装插件
	*/
	public function index(){
		return json_encode(['status'=>1]);
	}
	/**
     * 域名
     */
    public function domain(){
    	if(!empty(WSTConf('WST_ADDONS.aliyunoss'))){
            return WSTConf('CONF.resourcePath').'/';
        }
        return url('/','','',true);
    }

    /*
     * 获取商品套餐
     */
    public function getRelateCombinte(){
        $m = new M();
        $goodsId = (int)input('goodsId');
        $rs = $m->getRelateCombinte($goodsId);
        return json_encode(WSTReturn('ok',1,$rs['list']));
    }

    /**
     * 商品组合套餐详情
     */
    public function getCombinationDetail(){
        $m = new M();
        $rs = $m->getBySale((int)input('combineId'));
        $rs['startTime'] = date('Y-m-d H:i:s',strtotime($rs['startTime']));
        $rs['endTime'] = date('Y-m-d H:i:s',strtotime($rs['endTime']));
        // 未找到该商品组合套餐
        if(empty($rs))return json_encode(WSTReturn(lang('combination_package_detail_not_exist'),1));
        return json_encode(WSTReturn('ok',1,$rs));
    }

    /**
     * 结算页面
     */
    public function settlement(){
        $userId= model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('no_login'),-999));
        }
        $key = input('id');
        if($key=='')return json_encode(WSTReturn(lang('combination_illegal_operation'),-999));;
        $data = WSTStrToParams($key);
        //获取一个用户地址
        $addressId = (int)input('addressId');
        $ua = new UserAddress();
        if($addressId>0){
            $userAddress = $ua->getById($addressId,$userId);
        }else{
            $userAddress = $ua->getDefaultAddress($userId);
        }
        //获取支付方式
        $payments = model('common/payments')->getByGroup('4', -1, true);
        //获取已选的购物车商品
        $m = new M();
        $carts = $m->getCarts($data);
        if(empty($carts['data']['carts'])) return jsonReturn(lang('combination_goods_not_exist'),-1);
        if($carts['data']['goodsTotalNum']>0){
            if(empty($carts['data']['carts']))return jsonReturn(lang('combination_require_goods'),-1);
        }
        $carts['data']['combineKey'] = $key;
        $carts['data']['userAddress'] = $userAddress;
        $carts['data']['payments'] = $payments;
        $carts['data']['payNames'] = $carts['data']['payCodes'] = $carts['data']['isOnline'] =  [];
        if($payments){
            foreach ($payments as $key =>$v){
                foreach ($v as $key2 =>$v2){
                    $carts['data']['payNames'][] = $v2['payName'];
                    $carts['data']['payCodes'][] = $v2['payCode'];
                    $carts['data']['isOnlines'][] = $v2['isOnline'];
                }
            }
        }else{
            $carts['data']['payNames'] = [lang('combination_no_pay_type')];
        }

        //获取用户积分
        $user = model('common/users')->getFieldsById($userId, 'userScore');
        //计算可用积分和金额
        $goodsTotalMoney = $carts['data']['goodsTotalMoney'];
        $goodsTotalScore = WSTScoreToMoney($goodsTotalMoney,true);
        $useOrderScore =0;
        $useOrderMoney = 0;
        if($user['userScore']>$goodsTotalScore){
            $useOrderScore = $goodsTotalScore;
            $useOrderMoney = $goodsTotalMoney;
        }else{
            $useOrderScore = $user['userScore'];
            $useOrderMoney = WSTScoreToMoney($useOrderScore);
        }
        $carts['data']['userOrderScore'] = $useOrderScore;

        $carts['data']['userOrderMoney'] = $useOrderMoney;
        // 是否开启积分支付
        $carts['data']['isOpenScorePay'] = WSTConf('CONF.isOpenScorePay');
        return jsonReturn('success',1,$carts);
    }

    /**
     * 计算运费、积分和总商品价格
     */
    public function getCartMoney(){
        $userId= model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('no_login'),-999));
        }
        $m = new M();
        $data = $m->getCartMoney($userId);
        return jsonReturn('success',1,$data);
    }

    /**
     * 下单
     */
    public function submit(){
        $userId= model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('no_login'),-999));
        }
        $m = new M();
        $rs = $m->submit((int)input('orderSrc'),$userId);
        return jsonReturn('success',1,$rs);
    }
}
