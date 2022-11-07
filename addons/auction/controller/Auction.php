<?php
namespace addons\auction\controller;

use think\addons\Controller;
use addons\auction\model\Auctions as M;
use wstmart\common\model\Payments;

class Auction extends Controller{
	public function __construct(){
		parent::__construct();
		$m = new M();
		$data = $m->getConf('Auction');
		$this->assign("seoAuctionKeywords",$data['seoAuctionKeywords']);
		$this->assign("endPayDate",(int)$data['endPayDate']);
		$this->assign("seoAuctionDesc",$data['seoAuctionDesc']);
	}

	/**
	 * 拍卖商品
	 */
	public function addAcution(){
		 $m = new M();
		 return $m->addAcution();
	}

	/**
	 * 获取拍卖纪录
	 */
	public function pageQueryByAuctionLog(){
		$m = new M();
		return $m->pageQueryByAuctionLog((int)input('id'));
	}

	/**
	 * 去支付保证金
	 */
	public function toPay(){
		$m = new M();
		$rs = $m->getPayInfo((int)input('auctionId/d',0),1);
		if($rs['status']==-1){
			session('0001',$rs['msg']);
        	$this->redirect('home/error/message',['code'=>'0001']);
		}
		$this->assign("object",$rs['data']);
		$this->assign("payObj","bao");
		return $this->fetch("/home/index/pay_step1");
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
	 * 微信在线支付方式
	 */
	public function wxsucceed(){
		//获取支付方式
		$m = new M();
		$pa = new Payments();
		$payments = $pa->getByGroup('3');
		$this->assign('payments',$payments);
		$rs = $m->getPayInfo((int)input('auctionId/d',0),1);
		$this->assign("object",$rs['data']['auction']);
		$this->assign("payObj",'bao');
		return $this->fetch("/wechat/index/pay_list");
	}
	public function numberOrder(){
		$m = new M();
		$payObj = input("payObj/s");
		$pkey = "";
		$data = array();
		$data['status'] = 1;
		$auctionId = input("auctionId/d",0);
		if($payObj=="bao"){
			$auction = $m->getUserAuction($auctionId);
			$orderAmount = $auction["cautionMoney"];
			$userId = (int)session('WST_USER.userId');
			if($auction["userId"]>0){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_tips');
			}else{
				$data["status"] = $orderAmount>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_bond'):"";
				$pkey = $payObj."@".$auctionId;
			}
		}else{
			$auction = $m->getAuctionPay($auctionId);
			$orderAmount = $auction["payPrice"];
			$userId = (int)session('WST_USER.userId');
			if($auction["isPay"]==1){
				$data["status"] = -1;
				$data["msg"] = lang('auction_have_paid_order');
			}else{
				$data["status"] = $orderAmount>0?1:-1;
				$data["msg"] = ($data["status"]==-1)?lang('auction_not_need_pay_order'):"";
				$pkey = $payObj."@".$auctionId;
			}
		}
        $key = WSTBase64urlEncode($pkey);
        $data['key'] = $key;
		return $data;
	}

	/**
	 * 手机在线支付方式
	 */
	public function mosucceed(){
		//获取支付方式
		$m = new M();
		$pa = new Payments();
		$payments = $pa->getByGroup('2');
		$this->assign('payments',$payments);
		$rs = $m->getPayInfo((int)input('auctionId/d',0),1);
		$this->assign("object",$rs['data']['auction']);
		$this->assign("payObj",'bao');
		return $this->fetch("/mobile/index/pay_list");
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
}
