<?php
namespace addons\auction\controller;

use think\addons\Controller;
use addons\auction\model\Auctions as M;
use wstmart\common\model\Payments;
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
 * 拍卖活动插件
 */
class Users extends Controller{
	public function __construct(){
		parent::__construct();
		$m = new M();
		$data = $m->getConf('Auction');
		$this->assign("seoAuctionKeywords",$data['seoAuctionKeywords']);
		$this->assign("seoAuctionDesc",$data['seoAuctionDesc']);
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}
	/**
	 * 拍卖列表
	 */
	public function auction(){
    	return $this->fetch("/home/users/list");
	}
	/**
	 * 加载拍卖数据
	 */
	public function pageQuery(){
		$m = new M();
		return $m->pageQueryByUser();
	}
	/**
	 * 检测是否支付拍卖价格
	 */
	public function checkPayStatus(){
		$m = new M();
		$data = $m->checkAuctionPayStatus((int)input('id'));
        if(empty($data)){
        	session('0001',lang('auction_invalid_record'));
        	$this->redirect('home/error/message',['code'=>'0001']);
        }else{
        	if($data['isPay']==1){
        		$shopId = $data["shopId"];
				$shop = model("common/shops")->getFieldsById($shopId,"isInvoice");
        		//获取一个用户地址
				$userAddress = model('common/UserAddress')->getDefaultAddress();
				$this->assign('userAddress',$userAddress);
				//获取省份
				$areas = model('common/Areas')->listQuery();
				$this->assign('areaList',$areas);
				$this->assign('shopId',$shopId);
				$this->assign('payPrice',$data['payPrice']);
				$this->assign('auctionId',$data['auctionId']);
				$this->assign('isInvoice',$shop['isInvoice']);
                return $this->fetch("/home/users/form");
        	}else{
        		$m = new M();
				$rs = $m->getPayInfo((int)input('id'),2);
				$this->assign("object",$rs['data']);
				$this->assign("payObj","deal");
				return $this->fetch("/home/index/pay_step1");
        	}
        }
	}
	/**
	 * 我的拍卖保证金
	 */
	public function money(){
		return $this->fetch("/home/users/list_money");
	}
	/**
	 * 获取保证金列表
	 */
	public function pageQueryByMoney(){
		$m = new M();
		return $m->pageQueryByMoney();
	}


	/**
	 * 微信我的拍卖保证金
	 */
	public function wxmoney(){
		return $this->fetch("/wechat/users/list_money");
	}
	/**
	 * 微信拍卖列表页
	 */
	public function wxauction(){
		return $this->fetch("/wechat/users/list");
	}
	/**
	 * 微信检测是否支付拍卖价格
	 */
	public function wxcheckPayStatus(){
		$m = new M();
		$data = $m->checkAuctionPayStatus((int)input('id'));
		if(empty($data)){
			session('wxcheckPayStatus',lang('auction_invalid_record'));
			$this->redirect('wechat/error/message',['code'=>'wxcheckPayStatus']);
		}else{
			if($data['isPay']==1){
				$auction =  $m->get($data['auctionId']);
				$shopId = $auction["shopId"];
				$shop = model("common/shops")->getFieldsById($shopId,"isInvoice");
				if($auction->orderId>0){
					$this->assign('message',lang('auction_has_complete_order'));
				}else{
                    //获取省级地区信息
                    $area = model('common/areas')->listQuery(0);
                    $this->assign('area',$area);
					//获取一个用户地址
					$addressId = (int)input('addressId');
					if($addressId>0){
						$userAddress = model('common/UserAddress')->getById($addressId);
					}else{
						$userAddress = model('common/UserAddress')->getDefaultAddress();
					}
					$this->assign('userAddress',$userAddress);
					//获取省份
					$areas = model('common/Areas')->listQuery();
					$this->assign('areaList',$areas);
					$this->assign('shopId',$shopId);
					$this->assign('payPrice',$data['payPrice']);
					$this->assign('auctionId',$data['auctionId']);
					$this->assign('isInvoice',$shop['isInvoice']);
				}
				return $this->fetch("/wechat/users/settlement");
			}else{
				//获取支付方式
				$m = new M();
				$pa = new Payments();
				$payments = $pa->getByGroup('3');
				$this->assign('payments',$payments);
				$rs = $m->getPayInfo((int)input('id'),2);
				$this->assign("object",$rs['data']['auction']);
				$this->assign("payObj",'deal');
				return $this->fetch("/wechat/index/pay_list");
			}
		}
	}

	/**
	 * 手机我的拍卖保证金
	 */
	public function momoney(){
		return $this->fetch("/mobile/users/list_money");
	}
	/**
	 * 手机拍卖列表页
	 */
	public function moauction(){
		return $this->fetch("/mobile/users/list");
	}
	/**
	 * 手机检测是否支付拍卖价格
	 */
	public function mocheckPayStatus(){
		$m = new M();
		$data = $m->checkAuctionPayStatus((int)input('id'));
		if(empty($data)){
			session('mocheckPayStatus',lang('auction_invalid_record'));
			$this->redirect('mobile/error/message',['code'=>'mocheckPayStatus']);
		}else{
			if($data['isPay']==1){
				$auction =  $m->get($data['auctionId']);
				$shopId = $auction["shopId"];
				$shop = model("common/shops")->getFieldsById($shopId,"isInvoice");
				if($auction->orderId>0){
					$this->assign('message',lang('auction_has_complete_order'));
				}else{
                    //获取省级地区信息
                    $area = model('common/areas')->listQuery(0);
                    $this->assign('area',$area);
					//获取一个用户地址
					$addressId = (int)input('addressId');
					if($addressId>0){
						$userAddress = model('common/UserAddress')->getById($addressId);
					}else{
						$userAddress = model('common/UserAddress')->getDefaultAddress();
					}
					$this->assign('userAddress',$userAddress);
					//获取省份
					$areas = model('common/Areas')->listQuery();
					$this->assign('areaList',$areas);
					$this->assign('shopId',$shopId);
					$this->assign('payPrice',$data['payPrice']);
					$this->assign('auctionId',$data['auctionId']);
					$this->assign('isInvoice',$shop['isInvoice']);
				}
				return $this->fetch("/mobile/users/settlement");
			}else{
				//获取支付方式
				$m = new M();
				$pa = new Payments();
				$payments = $pa->getByGroup('2');
				$this->assign('payments',$payments);
				$rs = $m->getPayInfo((int)input('id'),2);
				$this->assign("object",$rs['data']['auction']);
				$this->assign("payObj",'deal');
				return $this->fetch("/mobile/index/pay_list");
			}
		}
	}
}
