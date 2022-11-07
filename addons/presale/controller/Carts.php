<?php
namespace addons\presale\controller;

use think\addons\Controller;
use addons\presale\model\Carts as M;
use addons\presale\model\Orders as OM;
use addons\presale\model\Presales as PM;
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
	    $CARTS = session('PRESALE_CARTS');
		if(empty($CARTS)){
			header("Location:".addon_url('presale://goods/lists'));
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
	 * 订单提交成功
	 */
	public function succeed(){
		$this->checkAuth();
		$m = new PM();
		$pkey = input("pkey");
		$this->assign('pkey',$pkey);
        $vpkey = WSTBase64urlDecode($pkey);
        $vpkey = explode('@',$vpkey);
		$payObj = $vpkey[0];
		$porderId = (int)$vpkey[1];

		$userId = (int)session('WST_USER.userId');
		$rs = $m->getPresalePay($porderId);
		$rs['needPay'] = ($payObj=="ding")?$rs['depositMoney']:$rs['surplusMoney'];
		$this->assign('object',$rs);
		$this->assign('payObj',$payObj);
		if($rs["isCanPay"]==0){
			$this->assign('message',lang('presale_cannt_pay'));
			return $this->fetch('error_msg');
		}else{
			$m = new M();
			$payments = $m->getPayments();
        	$this->assign('payments',$payments);
			if(($payObj=="ding" && $rs['presaleStatus']==1) || ($rs['presaleStatus']==2)){
				return $this->fetch('/home/index/pay_success');
			}else{
				return $this->fetch('/home/index/pay_step1');
			}
		}
	}

	/**
     * 获取店铺自提点
     */
    public function getShopStores(){
        $userId = (int)session('WST_USER.userId');
        $rs = model("common/Stores")->shopStores($userId);
    	return WSTReturn("", 1,$rs);
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
		$m = new OM();
		$orderSrc = (int)input('orderSrc');
		$rs = $m->submit($orderSrc);
		if($rs["status"]==1){
			$pkey = WSTBase64urlEncode('ding@'.$rs["data"]."@".$orderSrc);
			$rs["pkey"] = $pkey;
		}
		return $rs;
	}

	/**
	 * 微信结算页面
	 */
	public function wxSettlement(){
		$CARTS = session('PRESALE_CARTS');
		if(empty($CARTS)){
			header("Location:".addon_url('presale://goods/wxlists'));
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
		$CARTS = session('PRESALE_CARTS');
		if(empty($CARTS)){
			header("Location:".addon_url('presale://goods/molists'));
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
        $m = new OM();
        $rs = $m->submit(5,$userId);
        if($rs["status"]==1){
            $pkey = WSTBase64urlEncode('ding@'.$rs["data"]."@5");
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
        $CARTS = session('PRESALE_CARTS');

        if(empty($CARTS)){
            return jsonReturn(lang('presale_please_select_goods'),-1);
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

        //获取已选的购物车商品
        $m = new M();
        $carts = $m->getCarts($userId);
        if(empty($carts['carts'])) return jsonReturn(lang('presale_goods_not_exist'),-1);
        if($carts['goodsTotalNum']>0){
            if(empty($carts['carts']))return jsonReturn(lang('presale_please_select_goods'),-1);
        }
        $carts['userAddress'] = $userAddress;
        //获取支付方式
        $onlineType = 1;
        $payments = model('common/payments')->getByGroup('3',$onlineType);
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
            $carts['payNames'] = [lang('presale_no_pay_type')];
        }

        //计算可用积分
        $orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney'],$userId);
        $carts['userOrderScore'] = $orderUsableScore['score'];
        $carts['userOrderMoney'] = $orderUsableScore['money'];
        $carts['isOrderScore'] =  WSTConf('CONF.isOrderScore');
        $carts['orderScore'] = WSTMoneyGiftScore($carts['goodsTotalMoney']);
        return jsonReturn('success',1,$carts);
    }
}
