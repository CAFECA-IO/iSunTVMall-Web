<?php
namespace addons\integral\controller;

use think\addons\Controller;
use addons\integral\model\Integrals as M;
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
 * 积分商城插件
 */
class Carts extends Controller{
	public function __construct(){
		parent::__construct();
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}

    /**
     * 下单
     */
    public function addCart(){
        $m = new M();
        return $m->addCart();
    }


	/**
	 * 结算页面
	 */
	public function settlement(){
	    $CARTS = session('INTEGRAL_CARTS');
		if(empty($CARTS)){
			header("Location:".addon_url('integral://goods/lists'));
			exit;
		}
		//获取一个用户地址
		$userAddress = model('common/UserAddress')->getDefaultAddress();
		$this->assign('userAddress',$userAddress);
		//获取省份
		$areas = model('common/Areas')->listQuery();
		$this->assign('areaList',$areas);
		$m = new M();
		$carts = $m->getCarts();
		$this->assign('carts',$carts);
		//获取用户积分
        $user = model('common/users')->getFieldsById((int)session('WST_USER.userId'),'userScore');
        $this->assign('userScore',$user['userScore']);
        //获取支付方式
		$onlineType = 1;

		$payments = model('common/payments')->getByGroup('1',$onlineType);
        $this->assign('payments',$payments);

		return $this->fetch("/home/index/settlement");
	}

	/**
	 * 计算运费、积分和总商品价格
	 */
	public function getCartMoney(){
		$m = new M();
		$data = $m->getCartMoney();
		return $data;
	}

	/**
	 * 下单
	 */
	public function submit(){
		$m = new M();
		$rs = $m->submit((int)input('orderSrc'));
		if($rs["status"]==1){
			$pkey = WSTBase64urlEncode($rs["data"]."@1");
			$rs["pkey"] = $pkey;
		}
		return $rs;
	}

	/**
	 * 微信结算页面
	 */
	public function wxSettlement(){
		$CARTS = session('INTEGRAL_CARTS');
		if(empty($CARTS)){
			header("Location:".addon_url('integral://goods/wxlists'));
			exit;
		}
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
		$m = new M();
		$carts = $m->getCarts();
		$this->assign('carts',$carts);
		//获取用户积分
		$user = model('common/users')->getFieldsById((int)session('WST_USER.userId'),'userScore');
		//计算可用积分和金额
		$goodsTotalMoney = $carts['goodsTotalMoney'];
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
		$this->assign('userOrderScore',$useOrderScore);
		$this->assign('userOrderMoney',$useOrderMoney);
		//获取支付方式
		$onlineType = 1;
		$payments = model('common/payments')->getByGroup('3',$onlineType);
		$this->assign('payments',$payments);
		return $this->fetch("/wechat/index/settlement");
	}

	/**
	 * 手机结算页面
	 */
	public function moSettlement(){
		$CARTS = session('INTEGRAL_CARTS');
		if(empty($CARTS)){
			header("Location:".addon_url('integral://goods/molists'));
			exit;
		}
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
		$m = new M();
		$carts = $m->getCarts();
		$this->assign('carts',$carts);
		//获取用户积分
		$user = model('common/users')->getFieldsById((int)session('WST_USER.userId'),'userScore');
		//计算可用积分和金额
		$goodsTotalMoney = $carts['goodsTotalMoney'];
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
		$this->assign('userOrderScore',$useOrderScore);
		$this->assign('userOrderMoney',$useOrderMoney);
		//获取支付方式
		$onlineType = 1;
		$payments = model('common/payments')->getByGroup('2',$onlineType);
		$this->assign('payments',$payments);
		return $this->fetch("/mobile/index/settlement");
	}

	/**
     * 获取指定地址店铺是否支付自提
     */
    public function checkSupportStores(){
    	$m = new M();
        $rs = $m->checkSupportStores();
    	return WSTReturn("", 1,$rs);
    }
    /**
     * 获取店铺自提点
     */
    public function getStores(){
    	$rs = model("common/stores")->listQuery();
    	return WSTReturn("", 1,$rs);
    }

    /**
     * 获取店铺自提点[移动]
     */
    public function getShopStores(){
        $userId = (int)session('WST_USER.userId');
        $rs = model("common/Stores")->shopStores($userId);
    	return WSTReturn("", 1,$rs);
    }

	/**************************************小程序**********************************/
	/**
     * 去下单
     */
    public function weAddCart(){
        $this->checkWeappAuth();
        $userId= model('weapp/index')->getUserId();
        $m = new M();
        $rs = $m->addCart($userId);
        return jsonReturn('success',1,$rs);
    }
    /**
     * 下单
     */
    public function weSubmit(){
        $this->checkWeappAuth();
        $userId= model('weapp/index')->getUserId();
        $m = new M();
        $rs = $m->submit(5,$userId);
        if($rs["status"]==1){
			$pkey = WSTBase64urlEncode($rs["data"]."@1");
			$rs["pkey"] = $pkey;
		}
        return jsonReturn('success',1,$rs);
    }
    /**
     * 计算运费、积分和总商品价格
     */
    public function weGetCartMoney(){
        $this->checkWeappAuth();
        $userId= model('weapp/index')->getUserId();
        $m = new M();
        $data = $m->getCartMoney($userId);
        return jsonReturn('success',1,$data);
    }

    /**
     * 结算页面
     */
    public function weSettlement(){
        $this->checkWeappAuth();
        $userId= model('weapp/index')->getUserId();
        $CARTS = session('INTEGRAL_CARTS');

        if(empty($CARTS)){
            return jsonReturn(lang('integral_please_select_goods'),-1);
            exit;
        }
        //获取一个用户地址
        $addressId = (int)input('addressId');
        $ua = new UserAddress();
        if($addressId>0){
            $userAddress = $ua->getById($addressId,$userId);
        }else{
            $userAddress = $ua->getDefaultAddress($userId);
        }
        //获取支付方式
        $payments = model('common/payments')->getByGroup('3',1);
        //获取已选的购物车商品
        $m = new M();
        $carts = $m->getCarts($userId);
        if(empty($carts['carts'])) return jsonReturn(lang('integral_goods_not_exist'),-1);
        if($carts['goodsTotalNum']>0){
            if(empty($carts['carts']))return jsonReturn(lang('integral_please_select_goods'),-1);
        }
        $carts['userAddress'] = $userAddress;
        $carts['payments'] = $payments;
        $carts['payNames'] = $carts['payCodes'] = $carts['isOnline'] =  [];
        if($payments){
            foreach ($payments as $key =>$v){
                foreach ($v as $key2 =>$v2){
                    $carts['payNames'][] = $v2['payName'];
                    $carts['payCodes'][] = $v2['payCode'];
                    $carts['isOnlines'][] = $v2['isOnline'];
                }
            }
        }else{
            $carts['payNames'] = [lang('integral_no_payment_method')];
        }

        //获取用户积分
        $user = model('common/users')->getFieldsById($userId, 'userScore');
        //计算可用积分和金额
        $goodsTotalMoney = $carts['goodsTotalMoney'];
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
        $carts['userOrderScore'] = $useOrderScore;

        $carts['userOrderMoney'] = $useOrderMoney;
        // 是否开启积分支付
        $carts['isOpenScorePay'] = WSTConf('CONF.isOpenScorePay');
        return jsonReturn('success',1,$carts);
    }
}
