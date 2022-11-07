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
 * 购物车业务
 */
class Carts extends Controller{
	public function __construct(){
		parent::__construct();
        $this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}

	/**
	 * 购物车
	 */
	public function index(){
        $this->checkAuth();
        $key = input('id');
        if($key=='')return;
        $this->assign('combineKey',$key);
        $data = WSTStrToParams($key);
        $m = new M();
        $rs = $m->getCarts($data);
        if($rs['status']!=1)die($rs['msg']);
        $carts = $rs['data'];
        $this->assign('carts',$carts);
        //获取一个用户地址
        $userAddress = model('common/UserAddress')->getDefaultAddress();
        $this->assign('userAddress',$userAddress);
        //获取省份
        $areas = model('common/Areas')->listQuery();
        $this->assign('areaList',$areas);
        //计算可用积分
        $orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney']-$carts['promotionMoney']);
        $this->assign('userOrderScore',$orderUsableScore['score']);
        $this->assign('userOrderMoney',$orderUsableScore['money']);
        //获取支付方式
        $payments = model('common/payments')->getByGroup('1');
        $this->assign('payments',$payments);
		return $this->fetch("/home/settlement");
	}

    /**
     * 获取指定地址店铺是否支付自提
     */
    public function checkSupportStores(){
        $this->checkAuth();
        $m = new M();
        $rs = $m->checkSupportStores();
        return WSTReturn("", 1,$rs);
    }
    /**
     * 获取店铺自提点
     */
    public function getStores(){
        $this->checkAuth();
        $rs = model("common/stores")->listQuery();
        return WSTReturn("", 1,$rs);
    }

    /**
     * 获取店铺自提点[移动]
     */
    public function getShopStores(){
        $this->checkAuth();
        $userId = (int)session('WST_USER.userId');
        $rs = model("common/Stores")->shopStores($userId);
        return WSTReturn("", 1,$rs);
    }

    /**
     * 计算运费、积分和总商品价格
     */
    public function getCartMoney(){
        $this->checkAuth();
        $m = new M();
        $data = $m->getCartMoney();
        return $data;
    }

    /**
     * 下单
     */
    public function submit(){
        $this->checkAuth();
        $m = new M();
        $rs = $m->submit((int)input('orderSrc'));
        if($rs["status"]==1){
            $pkey = WSTBase64urlEncode($rs["data"]."@1");
            $rs["pkey"] = $pkey;
        }
        return $rs;
    }

    /**
     * 手机结算页面
     */
    public function moSettlement(){
        $this->checkAuth();
        $key = input('id');
        if($key=='')return;
        $this->assign('combineKey',$key);
        $data = WSTStrToParams($key);
        $m = new M();
        $rs = $m->getCarts($data);
        if($rs['status']!=1){
            $this->assign('message',$rs['msg']);
            return $this->fetch('mobile@'.WSTConf('CONF.wstmobileStyle').'/error_sys');
        }
        $carts = $rs['data'];
        $this->assign('combineId',$data['combineId']);
        $this->assign('carts',$carts);
        //获取省级地区信息
        $area = model('common/areas')->listQuery(0);
        $this->assign('area',$area);
        //获取一个用户地址
        $addressId = (int)input('addressId');
        $ua = new UserAddress();
        if($addressId>0){
            $userAddress = $ua->getById($addressId);
        }else{
            $userAddress = $ua->getDefaultAddress();
        }
        $this->assign('userAddress',$userAddress);
        //获取省份
        $areas = model('common/Areas')->listQuery();
        $this->assign('areaList',$areas);
        //计算可用积分
        $orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney']-$carts['promotionMoney']);
        $this->assign('userOrderScore',$orderUsableScore['score']);
        $this->assign('userOrderMoney',$orderUsableScore['money']);
        //获取支付方式
        $payments = model('common/payments')->getByGroup('1');
        $this->assign('payments',$payments);
        return $this->fetch("/mobile/settlement");
    }

    /**
     * 微信结算页面
     */
    public function wxSettlement(){
        $this->checkAuth();
        $key = input('id');
        if($key=='')return;
        $this->assign('combineKey',$key);
        $data = WSTStrToParams($key);
        $m = new M();
        $rs = $m->getCarts($data);
        if($rs['status']!=1){
            $this->assign('message',$rs['msg']);
            return $this->fetch('mobile@'.WSTConf('CONF.wstmobileStyle').'/error_sys');
        }
        $carts = $rs['data'];
        $this->assign('combineId',$data['combineId']);
        $this->assign('carts',$carts);
        //获取省级地区信息
        $area = model('common/areas')->listQuery(0);
        $this->assign('area',$area);
        //获取一个用户地址
        $addressId = (int)input('addressId');
        $ua = new UserAddress();
        if($addressId>0){
            $userAddress = $ua->getById($addressId);
        }else{
            $userAddress = $ua->getDefaultAddress();
        }
        $this->assign('userAddress',$userAddress);
        //获取省份
        $areas = model('common/Areas')->listQuery();
        $this->assign('areaList',$areas);
        //计算可用积分
        $orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney']-$carts['promotionMoney']);
        $this->assign('userOrderScore',$orderUsableScore['score']);
        $this->assign('userOrderMoney',$orderUsableScore['money']);
        //获取支付方式
        $payments = model('common/payments')->getByGroup('1');
        $this->assign('payments',$payments);
        return $this->fetch("/wechat/settlement");
    }

    /**
     * 结算页面
     */
    public function weSettlement(){
        $this->checkWeappAuth();
        $userId= model('weapp/index')->getUserId();
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
        $payments = model('common/payments')->getByGroup('3');
        //获取已选的购物车商品
        $m = new M();
        $carts = $m->getCarts($data);
        if(empty($carts['data']['carts'])) return jsonReturn(lang('combination_goods_not_exist'),-1);
        if($carts['data']['goodsTotalNum']>0){
            if(empty($carts['data']['carts']))return jsonReturn(lang('combination_require_goods'),-1);
        }
        $carts['data']['carts'] = array_shift($carts['data']['carts']);
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
            $carts['data']['payNames'] = ['没有支付方式'];
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
    public function weGetCartMoney(){
        $this->checkWeappAuth();
        $userId= model('weapp/index')->getUserId();
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
    public function weSubmit(){
        $this->checkWeappAuth();
        $userId= model('weapp/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('no_login'),-999));
        }
        $m = new M();
        $rs = $m->submit(5,$userId);
        return jsonReturn('success',1,$rs);
    }
}
