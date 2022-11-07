<?php
namespace addons\presale\model;
use think\addons\BaseModel as Base;
use think\Db;
use Env;
use think\Loader;
use wstmart\common\model\LogSms;
use wstmart\common\model\OrderRefunds as M;
use addons\presale\model\Carts;
use wstmart\common\validate\OrderComplains as OrderComplainsValidate;
use wstmart\common\validate\GoodsAppraises as GoodsAppraisesValidate;
use wstmart\common\validate\OrderServices as OrderServicesValidate;
use wstmart\common\validate\SupplierOrderServices as SupplierOrderServicesValidate;
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
 * 订单业务处理类
 */
class Orders extends Base{

	 /**
	 * 下单
	 */
	public function submit($orderSrc = 0,$uId=0){
		//检测购物车
		$m = new Carts();
		$carts = $m->getCarts();
		if(empty($carts['carts']))return WSTReturn(lang("presale_select_buy_goods"));
		$checkNum = $carts['carts']['goods']['goodsStock'];
		if($checkNum<$carts['goodsTotalNum'])return WSTReturn(lang("presale_fail_goods_stock_is").$checkNum);

		return $this->submitByEntity($carts,$orderSrc,$uId);

	}


	/**
	 * 实物商品下单
	 */
	public function submitByEntity($carts,$orderSrc = 0,$uId=0){
		$tmp_carts = session('PRESALE_CARTS');

        $presaleId = (int)$tmp_carts['presaleId'];
        $goodsSpecId = (int)$tmp_carts['goodsSpecId'];
		$cartNum = (int)$tmp_carts['cartNum'];

		$addressId = (int)input('post.s_addressId');
		$deliverType = ((int)input('post.deliverType')!=0)?1:0;
		$isInvoice = ((int)input('post.isInvoice')!=0)?1:0;
		$invoiceClient = ($isInvoice==1)?input('post.invoiceClient'):'';
		$payType = 1;
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($userId==0)return WSTReturn(lang('presale_no_login'));
		$isUseScore = (int)input('isUseScore');
		$useScore = 0;

		$presale = Db::name("presales")
                    ->alias('p')
                    ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
                    ->field('p.*,pl.goodsName')
                    ->where(['id'=>$presaleId,"presaleStatus"=>1,"dataFlag"=>1])->find();
		if(empty($presale)){
			return WSTReturn(lang("presale_invalid_goods"));
		}
		$limitNum = (int)$presale['limitNum'];
		$cnt = Db::name("presale_orders")->where(['userId'=>$userId,'presaleId'=>$presaleId,"dataFlag"=>1])->sum("goodsNum");
		if(($cnt+$cartNum)>$limitNum){
			return WSTReturn(lang('presale_everyone_limit_buy_count',[$limitNum,$cnt]));
		}
		$address = Db::name('user_address')->where(['userId'=>$userId,'addressId'=>$addressId,'dataFlag'=>1])->find();
		if(empty($address)){
			return WSTReturn(lang("presale_invalid_user_address"));
		}
	    $areaIds = [];
        $areaMaps = [];
        $tmp = explode('_',$address['areaIdPath']);
        $address['areaId2'] = $tmp[1];//记录配送城市
        foreach ($tmp as $vv){
         	if($vv=='')continue;
         	if(!in_array($vv,$areaIds))$areaIds[] = $vv;
        }
        if(!empty($areaIds)){
	         $areas = Db::name('areas')->where([['areaId','in',$areaIds],['dataFlag','=',1]])->field('areaId,areaName')->select();
	         foreach ($areas as $v){
	         	 $areaMaps[$v['areaId']] = $v['areaName'];
	         }
	         $tmp = explode('_',$address['areaIdPath']);
	         $areaNames = [];
		     foreach ($tmp as $vv){
	         	 if($vv=='')continue;
	         	 $areaNames[] = $areaMaps[$vv];
	         	 $address['areaName'] = implode('',$areaNames);
	         }
        }
		$address['userAddress'] = $address['areaName'].$address['userAddress'];


		//生成订单
		Db::startTrans();
		try{
			$totalDepositMoney = $carts['totalDepositMoney'];
			$orderunique = WSTOrderQnique();
			$carts = $carts['carts'];
			$orderNo = WSTOrderNo();
			$orderScore = 0;
			//创建订单
			$porder = [];
			$porder['presaleId'] = $presaleId;
			$porder['goodsId'] = $presale["goodsId"];
			$porder['goodsName'] = $presale["goodsName"];
			$porder['goodsImg'] = $presale["goodsImg"];
			$porder['goodsSpecId'] = $goodsSpecId;
			if(!empty($carts['goods']['specNames'])){
				$specNams = [];
				foreach ($carts['goods']['specNames'] as $pkey =>$spec){
					$specNams[] = $spec['catName'].'：'.$spec['itemName'];
				}
				$porder['goodsSpecNames'] = implode('@@_@@',$specNams);
			}else{
				$porder['goodsSpecNames'] = '';
			}

			$porder['goodsNum'] = $cartNum;
			$porder['presaleStatus'] = 0;

			$porder['orderType'] = 0;
			$porder['areaId'] = $address['areaId'];
			$porder['areaIdPath'] = $address['areaIdPath'];
			$porder['userName'] = $address['userName'];
			$porder['areaCode'] = $address['areaCode'];
			$porder['userPhone'] = $address['userPhone'];
			$porder['userAddress'] = $address['userAddress'];
			$porder['orderNo'] = $orderNo;
			$porder['userId'] = $userId;
			$porder['shopId'] = $carts['shopId'];
			$porder['payType'] = $payType;
			$porder['goodsMoney'] = $carts['goodsMoney'];
			//计算运费和总金额
			$porder['deliverType'] = $deliverType;
			if($carts['isFreeShipping']){
                $porder['deliverMoney'] = 0;
			}else{
			    $porder['deliverMoney'] = ($deliverType==1)?0:WSTOrderFreight($carts['shopId'],$address['areaId2'],$carts);
			}
			if($deliverType==1){//自提
				$porder['storeId'] = (int)input("storeId");
				$porder['storeType'] = 1;
			}
			$saleType = (int)$carts['goods']['saleType'];

			if($saleType==0){
				$totalDepositMoney = $totalDepositMoney+$porder['deliverMoney'];
			}

			$porder['totalMoney'] = $porder['goodsMoney']+$porder['deliverMoney'];
            //积分支付-计算分配积分和金额
            $porder['scoreMoney'] = 0;
			$porder['useScore'] = 0;

			//实付金额要减去积分兑换的金额
			$porder['realTotalMoney'] = $porder['totalMoney'] - $porder['scoreMoney'];

			$porder['depositMoney'] = $totalDepositMoney;


			$goods = Db::name('goods')->where('goodsId',$presale["goodsId"])->field('goodsCatId')->find();

			$porder['commissionRate'] = WSTGoodsCommissionRate($goods['goodsCatId']);

			$commissionFee = 0;
			if((float)$porder['commissionRate']>0){
            	$commissionFee += round($carts['goods']['shopPrice']*$cartNum*$porder['commissionRate']/100,2);
            }
            $porder['commissionFee'] = $commissionFee;


			$porder['isPay'] = 0;

			//积分
			$orderScore = 0;
			//如果开启下单获取积分则有积分
			if(WSTConf('CONF.isOrderScore')==1){
				$orderScore = WSTMoneyGiftScore($porder['goodsMoney']);
			}
			$porder['orderScore'] = $orderScore;

			$shop = model("common/shops")->getFieldsById($carts['shopId'],"isInvoice");
			$shopIsInvoice = $shop['isInvoice'];
			if($shopIsInvoice==1 && $isInvoice==1){
				$porder['isInvoice'] = $isInvoice;
				$porder['invoiceJson'] = model('common/invoices')->getInviceInfo((int)input('invoiceId'),$userId);// 发票信息
			    $porder['invoiceClient'] = $invoiceClient;
			}else{
				$porder['isInvoice'] = 0;
				$porder['invoiceJson'] = "";// 发票信息
				$porder['invoiceClient'] = "";
			}

			$porder['orderRemarks'] = input('post.remark_'.$carts['shopId']);
			$porder['orderunique'] = $orderunique;
			$porder['orderSrc'] = $orderSrc;
			$porder['payRand'] = 1;
			$porder['createTime'] = date('Y-m-d H:i:s');
			$surplusMoney =  WSTBCMoney($porder['realTotalMoney']- $totalDepositMoney,0);
			$porder['surplusMoney'] = $surplusMoney;
			$orderId = Db::name('presale_orders')->insertGetId($porder);
			if(false !== $orderId){
				$goods = $carts['goods'];

				//修改预售数量
				Db::name('presales')->where('id',$presaleId)->setInc('orderNum',$goods['cartNum']);
				//创建积分流水--如果有抵扣积分就肯定是开启了支付支付
				if($porder['useScore']>0){
					$score = [];
				    $score['userId'] = $userId;
					$score['score'] = $porder['useScore'];
					$score['dataSrc'] = 1;
					$score['dataId'] = $orderId;
					$score['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'presale_order_score_log','params'=>[$orderNo,$porder['useScore']]]);
					$score['scoreType'] = 0;
					model('common/UserScores')->add($score);
				}
			}
			Db::commit();
			//删除session的购物车商品
			session('PRESALE_CARTS',null);
			return WSTReturn(lang("presale_order_success"), 1,$orderId);
		}catch (\Exception $e) {
			//print_r($e);
            Db::rollback();
            return WSTReturn(lang('presale_fail'),-1);
        }
	}


	/**
	 * 用户钱包支付定金
	 */
	public function payByWallet($uId=0){
		$payPwd = input('payPwd');
		$isWeapp = (int)input('isWeapp');
		if($uId==0 || $isWeapp==1){// 大于0表示来自app端
			$decrypt_data = WSTRSA($payPwd);
			if($decrypt_data['status']==1){
				$payPwd = $decrypt_data['data'];
			}else{
				return WSTReturn(lang('presale_pay_fail'));
			}
		}
		$pkey = input('pkey');
		$pkey = WSTBase64urlDecode($pkey);
		$pkey = explode('@',$pkey);
		$moneyType = ($pkey[0]=="ding")?1:2;

		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		//判断是否开启余额支付
		$isEnbalePay = model('common/Payments')->isEnablePayment('wallets');
		if($isEnbalePay==0)return WSTReturn(lang('presale_illegal_pay_type'),-1);
		$payMoney = 0;
		$porderId = (int)$pkey[1];
		$porder = Db::name('presale_orders po')
                     ->where(['po.id'=>$porderId,'po.userId'=>$userId])
                     ->find();
        $presale = Db::name('presales')->where(['id'=>$porder['presaleId']])->find();

		$now = date("Y-m-d H:i:s");
		if($porder["presaleStatus"]>0 && $porder["startPayTime"]>$now){
			return WSTReturn(lang('presale_cannt_pay_balance_tips'),-1);
		}
		if($porder["presaleStatus"]>0 && $porder["endPayTime"]<$now){
			return WSTReturn(lang('presale_over_time_balance_tips'),-1);
		}
		//$data = array();
		if($moneyType==1){
			$payMoney = $porder["depositMoney"];
		}else{
			$payMoney = $porder["surplusMoney"];
		}
		//获取用户钱包
		$user = model('common/users')->get(['userId'=>$userId]);
		if($user->payPwd=='')return WSTReturn(lang('presale_not_set_pay_pwd'),-1);
		if($user->payPwd!=md5($payPwd.$user->loginSecret))return WSTReturn(lang('presale_pay_pwd_wrong'),-1);
		if($payMoney > $user->userMoney)return WSTReturn(lang('presale_wallet_balance_not_enough'),-1);
		$rechargeMoney = $user->rechargeMoney;
		Db::startTrans();
		try {
			$lockCashMoney = 0;
			if(empty($data)){
				$lockCashMoney = ($rechargeMoney>$payMoney)?$payMoney:$rechargeMoney;
				//创建一条支出流水记录
				$lm = [];
				$lm['targetType'] = 0;
				$lm['targetId'] = $userId;
				$lm['dataId'] =  $porderId;
				$lm['dataSrc'] = 'presale';
				$lm['remark'] = ($moneyType==1)?(json_encode(['type'=>'lang_params','key'=>'presale_log_money_remark1','params'=>[$porder['goodsName'],$payMoney]])):(json_encode(['type'=>'lang_params','key'=>'presale_log_money_remark2','params'=>[$porder['goodsName'],$payMoney]]));
				$lm['moneyType'] = 0;
				$lm['money'] = $payMoney;
				$lm['payType'] = 'wallets';
				model('common/LogMoneys')->add($lm);
				//修改用户充值金额
				model('common/users')->where(["userId"=>$userId])->setDec("rechargeMoney",$lockCashMoney);
				if($moneyType==2){//尾款
					$data = array();
					$data["isPay"] = 2;
					$data["presaleStatus"] = 2;
					$data["payTime"] = date("Y-m-d H:i:s");
					Db::name('presale_orders')->where(["id"=>$porderId])->update($data);
					$this->createOrder($porderId,$userId);
				}else{
					$data = array();
					$startPayTime = $presale['endTime'];
					$endPayTime = date("Y-m-d H:i:s",strtotime("+".$presale['endPayDays']." days",WSTStrToTime($presale['endTime'])));
					if($presale['saleType']==0 && $presale['deliverType']==0){
						$startPayTime = date('Y-m-d H:i:s');
					}
					if(($presale['saleType']==0) && ($porder["surplusMoney"]==0)){//付全款,且支付成功后发货
						$startPayTime = date('Y-m-d H:i:s');
						$data["isPay"] = 2;
						$data["presaleStatus"] = 2;
						$data["payTime"] = date("Y-m-d H:i:s");
						$data["startPayTime"] = $startPayTime;
						$data["endPayTime"] = $endPayTime;
						Db::name('presale_orders')->where(["id"=>$porderId])->update($data);
						$this->createOrder($porderId,$userId);
					}else{
						$data["isPay"] = 1;
						$data["presaleStatus"] = 1;
						$data["startPayTime"] = $startPayTime;
						$data["endPayTime"] = $endPayTime;
						Db::name('presale_orders')->where(["id"=>$porderId])->update($data);
					}
				}
				//创建一条预售金记录
				$am = [];
				$am['porderId'] = $porderId;
				$am['userId'] = $userId;
				$am['presaleMoney'] =  $payMoney;
				$am['presaleStatus'] = 1;
				$am['moneyType'] = $moneyType;
				$am['payType'] = 'wallets';
				$am['tradeNo'] = '';
				$am['createTime'] = date('Y-m-d H:i:s');
				Db::name('presale_moneys')->insert($am);

				Db::commit();
				return WSTReturn(lang('presale_pay_success'),1);
			}else{
				return WSTReturn(lang('presale_order_has_pay_tips'),-1);
			}
		} catch (Exception $e) {
			Db::rollback();
			return WSTReturn(lang('presale_pay_fail'),-1);
		}
	}

	/**
	 * 完成在线支付
	 */
	public function complatePay($obj){

		$trade_no = $obj["trade_no"];
		$userId = (int)$obj["userId"];
		$porderId = (int)$obj["porderId"];
		$payFrom = $obj["payFrom"];
		$payMoney = (float)$obj["total_fee"];
		$moneyType = ($obj["payObj"]=="ding")?1:2;

		$premoney = Db::name('presale_moneys')->where(["userId"=>$userId,"moneyType"=>$moneyType,"tradeNo"=>$trade_no,"payType"=>$payFrom])->find();
		if(!empty($premoney)){
			return WSTReturn(($moneyType==1)?lang('presale_deposit_money_is_pay'):lang('presale_deposit_balance_is_pay'),-1);
		}
		$porder = Db::name('presale_orders')->where(["id"=>$porderId,"dataFlag"=>1])->find();
		$needPay = 0;
		if($moneyType==1){
			$needPay = $porder["depositMoney"];
		}else{
			$needPay = $porder["surplusMoney"];
		}

		if($payMoney<$needPay){
			return WSTReturn(($moneyType==1)?lang('presale_deposit_money_wrong'):lang('presale_deposit_balance_wrong'),-1);
		}

		Db::startTrans();
		try {
			//创建一条充值流水记录
			$lm = [];
			$lm['targetType'] = 0;
			$lm['targetId'] = $userId;
			$lm['dataId'] = $porderId;
			$lm['dataSrc'] = 'presale';
            $lm['remark'] = ($moneyType==1)?(json_encode(['type'=>'lang_params','key'=>'presale_log_money_remark3','params'=>[$porder['goodsName'],$payMoney]])):(json_encode(['type'=>'lang_params','key'=>'presale_log_money_remark4','params'=>[$porder['goodsName'],$payMoney]]));
			$lm['moneyType'] = 1;
			$lm['money'] = $payMoney;
			$lm['payType'] = $payFrom;
			$lm['tradeNo'] = $trade_no;
			model('common/LogMoneys')->add($lm);

			$presale = Db::name('presales')->where(['id'=>$porder['presaleId']])->find();

			$mmoney = Db::name('presale_moneys')->where(["userId"=>$userId,"moneyType"=>$moneyType,"porderId"=>$porderId])->find();
			if(empty($mmoney)){
				if($moneyType==2){//尾款
					if($porder["endPayTime"]<date("Y-m-d H:i:s")){
						Db::commit();
						return WSTReturn(lang('presale_over_time_balance_tips'),-1);
					}

					$data = array();
					$data["isPay"] = 2;
					$data["presaleStatus"] = 2;
					$data["payTime"] = date("Y-m-d H:i:s");
					Db::name('presale_orders')->where(["id"=>$porderId])->update($data);
					$this->createOrder($porderId,$userId);

				}else{

					$data = array();
					$startPayTime = $presale['endTime'];
					$endPayTime = date("Y-m-d H:i:s",strtotime("+".$presale['endPayDays']." days",WSTStrToTime($presale['endTime'])));
					if($presale['saleType']==0 && $presale['deliverType']==0){
						$startPayTime = date('Y-m-d H:i:s');
					}
					if(($presale['saleType']==0) && ($porder["surplusMoney"]==0)){//付全款,且支付成功后发货
						$startPayTime = date('Y-m-d H:i:s');
						$data["isPay"] = 2;
						$data["presaleStatus"] = 2;
						$data["payTime"] = date("Y-m-d H:i:s");
						$data["startPayTime"] = $startPayTime;
						$data["endPayTime"] = $endPayTime;
						Db::name('presale_orders')->where(["id"=>$porderId])->update($data);
						$this->createOrder($porderId,$userId);
					}else{
						$data["isPay"] = 1;
						$data["presaleStatus"] = 1;
						$data["startPayTime"] = $startPayTime;
						$data["endPayTime"] = $endPayTime;
						Db::name('presale_orders')->where(["id"=>$porderId])->update($data);
					}
				}


				if($payFrom=='ccgwpays'){
	                Db::name('ccgw_address_trans')->where(['ccTxid'=>$trade_no])->update(['dataStatus'=>1]);
	            }
	            
				//创建一条支出流水记录
				$lm = [];
				$lm['targetType'] = 0;
				$lm['targetId'] = $userId;
				$lm['dataId'] = $porderId;
				$lm['dataSrc'] = 'presale';
                $lm['remark'] = ($moneyType==1)?(json_encode(['type'=>'lang_params','key'=>'presale_log_money_remark1','params'=>[$porder['goodsName'],$payMoney]])):(json_encode(['type'=>'lang_params','key'=>'presale_log_money_remark2','params'=>[$porder['goodsName'],$payMoney]]));
				$lm['moneyType'] = 0;
				$lm['money'] = $payMoney;
				$lm['payType'] = $payFrom;
				model('common/LogMoneys')->add($lm);

				//创建一条预售金记录
				$am = [];
				$am['porderId'] = $porderId;
				$am['userId'] = $userId;
				$am['presaleMoney'] =  $payMoney;
				$am['presaleStatus'] = 1;
				$am['moneyType'] = $moneyType;
				$am['payType'] = $payFrom;
				$am['tradeNo'] = $trade_no;
				$am['createTime'] = date('Y-m-d H:i:s');
				Db::name('presale_moneys')->insert($am);
			}
			Db::commit();
			return WSTReturn(lang('presale_pay_success'),1);
		} catch (Exception $e) {
			Db::rollback();
			return WSTReturn(lang('presale_pay_fail'),-1);
		}
	}

	public function createOrder($porderId,$userId){
		$porder = Db::name('presale_orders')
                     ->where(['id'=>$porderId,'userId'=>$userId,'orderId'=>0])
                     ->find();
        if(empty($porder))return WSTReturn(lang('presale_can_not_create_order'),-1);
        $presale = Db::name('presales')->where(['id'=>$porder['presaleId']])->find();
		//创建订单
		$order = [];
		$shopId = $porder['shopId'];
		$order['orderNo'] = $porder['orderNo'];
		$order['userId'] = $porder['userId'];
		$order['shopId'] = $porder['shopId'];
		$order['payType'] = $porder['payType'];
		$order['goodsMoney'] = $porder['goodsMoney'];
		$order['orderCode'] = "presale";

		$order['areaId'] = $porder['areaId'];
		$order['userName'] = $porder['userName'];
		$order['userAddress'] = $porder['userAddress'];
		$order['areaCode'] = $porder['areaCode'];
		$order['userPhone'] = $porder['userPhone'];
		//计算运费和总金额
		$order['deliverType'] = $porder['deliverType'];
		$order['deliverMoney'] = $porder['deliverMoney'];
		$order['storeId'] = $porder['storeId'];
		$order['storeType'] = $porder['storeType'];
		$order['totalMoney'] = $porder['totalMoney'];
	    //积分支付-计算分配积分和金额
		$order['scoreMoney'] = $porder['scoreMoney'];
		$order['useScore'] = $porder['useScore'];
		//实付金额要减去积分兑换的金额和店铺总优惠
		$order['realTotalMoney'] = $porder['realTotalMoney'];
		$order['needPay'] = 0;
	    $order['orderStatus'] = 0;//待发货
		if($order['needPay']==0){
			$order['isPay'] = 1;
			$order['payFrom'] = 'others';
			$order['payTime'] = date('Y-m-d H:i:s');
		}
		if($order['deliverType']==1)$order['verificationCode'] = WSTOrderVerificationCode($shopId);

		$order['orderScore'] = $porder['orderScore'];
		// 获得的积分可抵扣金额
		$order['getScoreVal'] = WSTScoreToMoney($order['orderScore']);

		$order['isInvoice'] = $porder['isInvoice'];
		$order['invoiceJson'] = $porder['invoiceJson']; // 发票信息
		$order['invoiceClient'] = $porder['invoiceClient'];

		$order['orderRemarks'] = $porder['orderRemarks'];
		$order['orderunique'] = $porder['orderunique'];
		$order['orderSrc'] = $porder['orderSrc'];
		$order['dataFlag'] = 1;
		$order['payRand'] = 1;
		$order['createTime'] = date('Y-m-d H:i:s');
		$result = model("common/Orders")->data($order,true)->isUpdate(false)->allowField(true)->save($order);
		if(false !== $result){
			$orderId = model("common/Orders")->orderId;
			//更新预售单orderId
			Db::name("presale_orders")->where(['id'=>$porderId,'userId'=>$userId])->update(['orderId'=>$orderId]);

			$orderTotalGoods = [];
			$commissionFee = 0;

			$payType = $porder['payType'];
			$deliverType = $porder['deliverType'];
			$orderNo = $porder['orderNo'];
			//创建订单商品记录
			$orderGgoods = [];
			$orderGoods['orderId'] = $orderId;
			$orderGoods['goodsId'] = $porder['goodsId'];
			$orderGoods['goodsNum'] = $porder['goodsNum'];
			$orderGoods['goodsPrice'] = WSTBCMoney($porder['goodsMoney']/$porder['goodsNum'],0);
			$orderGoods['goodsSpecId'] = $porder['goodsSpecId'];
			$orderGoods['goodsSpecNames'] = $porder['goodsSpecNames'];

			$orderGoods['goodsName'] = $porder['goodsName'];
			$orderGoods['goodsImg'] = $porder['goodsImg'];
			$orderGoods['commissionRate'] = $porder['commissionRate'];
			$orderGoods['goodsCode'] = '';
			$orderGoods['goodsType'] = 0;
			$orderGoods['extraJson'] = '';
			$orderGoods['promotionJson'] = '';


			$rate = $porder['goodsMoney']*$porder['goodsNum']/$order['goodsMoney'];
			if((int)WSTConf('CONF.isOpenScorePay')==1){// 后台有开启积分支付

				$orderGoods['useScoreVal'] = $porder['useScore'];
				$orderGoods['scoreMoney'] = round(WSTScoreToMoney($orderGoods['useScoreVal']), 2);

			}
			//如果开启下单获取积分则有积分
			// $orderGoods['getScoreVal'] = 0;
			if(WSTConf('CONF.isOrderScore')==1){
				$orderGoods['getScoreVal'] = WSTMoneyGiftScore($porder['goodsMoney']);
				// 获得的积分可抵扣金额
				$orderGoods['getScoreMoney'] = WSTScoreToMoney($orderGoods['getScoreVal']);
			}
			/****************************************  积分抵扣的金额end  ****************************************/
			$orderGoods["orderGoodscommission"] = 0;
			//计算订单总佣金
            if((float)$orderGoods['commissionRate']>0){
            	$orderGoodscommission = round($orderGoods['goodsPrice']*$orderGoods['goodsNum']*$orderGoods['commissionRate']/100,2);
            	$orderGoods["orderGoodscommission"] = $orderGoodscommission;
            	$commissionFee += $orderGoodscommission;
            }

			Db::name('order_goods')->insert($orderGoods);
			//更新订单佣金
			Db::name('orders')->where('orderId',$orderId)->update(['commissionFee'=>$commissionFee]);

			//建立订单记录
			$logOrder = [];
			$logOrder['orderId'] = $orderId;
			$logOrder['orderStatus'] = ($payType==1 && $order['needPay']==0)?-2:$order['orderStatus'];
			$logOrder['logJson'] = ($payType==1)?json_encode(['type'=>'lang','key'=>'order_success_wait_pay']):json_encode(['type'=>'lang','key'=>'order_success']);
			$logOrder['logUserId'] = $userId;
			$logOrder['logType'] = 0;
			$logOrder['logTime'] = date('Y-m-d H:i:s');
			Db::name('log_orders')->insert($logOrder);
			if($payType==1 && $order['needPay']==0){
				$logOrder = [];
				$logOrder['orderId'] = $orderId;
				$logOrder['orderStatus'] = 0;
				$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'order_success_has_pay']);
				$logOrder['logUserId'] = $userId;
				$logOrder['logType'] = 0;
				$logOrder['logTime'] = date('Y-m-d H:i:s');
				Db::name('log_orders')->insert($logOrder);
			}
			if($deliverType==1){//自提
				//自提订单（已支付）发送核验码
				if(($payType==1 && $order['needPay']==0) || $payType==0){
					$tpl = WSTMsgTemplates('PHONE_USER_ORDER_VERIFICATCODE');
			        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
			        	$userPhone = $order['userPhone'];
			        	$storeId = $order['storeId'];
			        	$store = Db::name("stores")->where(["storeId"=>$storeId])->field("storeName,storeAddress")->find();
			        	$storeName = $store["storeName"];
			        	$storeAddress = $store["storeAddress"];
			        	$splieVerificationCode = join(" ",str_split($order['verificationCode'],4));
			            $params = ['tpl'=>$tpl,'params'=>['MALL_NAME'=>WSTConf('CONF.mallName'),'ORDER_CODE'=>$splieVerificationCode,'SHOP_NAME'=>$storeName,'SHOP_ADDRESS'=>$storeAddress]];
			            model("common/LogSms")->sendSMS(0,$userPhone,$params,'submit','',$userId,0);
			        }
				}
		    }
			//给店铺增加提示消息
			$tpl = WSTMsgTemplates('ORDER_SUBMIT');
	        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	            $find = ['${ORDER_NO}'];
	            $replace = [$orderNo];

	        	$msg = array();
	            $msg["shopId"] = $shopId;
	            $msg["tplCode"] = $tpl["tplCode"];
	            $msg["msgType"] = 1;
	            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
	            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
	            model("common/MessageQueues")->add($msg);
	        }
			//判断是否需要发送商家短信
	        $tpl = WSTMsgTemplates('PHONE_SHOP_SUBMIT_ORDER');
			if((int)WSTConf('CONF.smsOpen')==1 && $tpl['tplContent']!='' && $tpl['status']=='1'){

				$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$orderNo]];

				$msg = array();
				$tplCode = "PHONE_SHOP_SUBMIT_ORDER";
				$msg["shopId"] = $shopId;
	            $msg["tplCode"] = $tplCode;
	            $msg["msgType"] = 2;
	            $msg["paramJson"] = ['CODE'=>$tplCode,'method'=>'submit','params'=>$params];
	            $msg["msgJson"] = "";
	            model("common/MessageQueues")->add($msg);
			}
	        //微信消息
	        if((int)WSTConf('CONF.wxenabled')==1){
	        	$params = [];
	        	$params['ORDER_NO'] = $orderNo;
	            $params['ORDER_TIME'] = date('Y-m-d H:i:s');

	        	$params['GOODS'] = $porder['goodsName']."*".$porder['goodsNum'];
	        	$params['MONEY'] = $porder['realTotalMoney'] + $porder['scoreMoney'];
	        	$params['ADDRESS'] = $porder['userAddress']." ".$porder['userName'];
	        	$params['PAY_TYPE'] = WSTLangPayType($order['payType']);

	            $msg = array();
				$tplCode = "WX_ORDER_SUBMIT";
				$msg["shopId"] = $shopId;
	            $msg["tplCode"] = $tplCode;
	            $msg["msgType"] = 4;
	            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
	            $msg["msgJson"] = "";
	            model("common/MessageQueues")->add($msg);
	        }
		}
	}


	/**
	 * 获取规格数组
	 */
	public function getSpecs($goodsId){
		$sales = Db::name('goods_specs')->where(['goodsId'=>$goodsId,'isDefault'=>1])->field('specIds')->find();
		$specIds = [];
		if(!empty($sales)){
			$specIds = explode(':',$sales['specIds']);
			sort($specIds);
		}
		$spec = [];
		//获取默认规格值
		$specs = Db::name('spec_cats')->alias('gc')
				   ->join('__SPEC_ITEMS__ sit','gc.catId=sit.catId','inner')
				   ->where(['sit.goodsId'=>$goodsId,'gc.isShow'=>1,'sit.dataFlag'=>1])
				   ->field('gc.isAllowImg,gc.catName,sit.catId,sit.itemId,sit.itemName,sit.itemImg')
				   ->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
		foreach ($specs as $key =>$v){
			if(in_array($v['itemId'],$specIds)){
				$catId = $v['catId'];
				$spec[$catId]['name'] = $v['catName'];
				unset($v['catName'],$v['catId']);
				$spec[$catId]['list'][] = $v;
			}
		}
		return $spec;
	}



	public function pageQuery($uId=0){
		$ftype = (int)input("ftype/d",-2);
		$orderNo = input("orderNo");
		$shopName = input("shopName");

		$where = [];
		if($ftype>-2){//待付款
			$where[] = ["po.presaleStatus",'=',$ftype];
		}
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($orderNo!='')$where[] = ["po.orderNo",'like','%'.$orderNo.'%'];
		if($shopName!='')$where[] = ["s.shopName",'like','%'.$shopName.'%'];
		$page = Db::name('presale_orders po')
					->join("presales p","p.id=po.presaleId","inner")
					->join("shops s","s.shopId=po.shopId","inner")
					->where(["po.userId"=>$userId])
					->where($where)
					->field("po.*,po.shopId,s.shopImg,s.shopName")
					->order("po.id","desc")
            		 ->paginate(input('pagesize/d'))->toArray();
        if(count($page['data'])>0){
        	$now = date("Y-m-d H:i:s");
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);

        		$page['data'][$key]['goodsPrice'] = WSTBCMoney($v['goodsMoney']/$v['goodsNum'],0);
        		$page['data'][$key]['ftype'] = $ftype;
        		if($v['presaleStatus']==0){
        			$page['data'][$key]['pkey'] = WSTBase64urlEncode('ding@'.$v["id"]);
        		}else if($v['presaleStatus']==1){
        			$page['data'][$key]['pkey'] = WSTBase64urlEncode('surplus@'.$v["id"]);
        		}
        		if($v['presaleStatus']==1){
        			$page['data'][$key]['canPaySurplus'] = ($v['startPayTime']<=$now && $v['endPayTime']>=$now)?1:0;
        		}
        		$page['data'][$key]['payTypeName'] = WSTLangPayType($v['payType']);
	    	 	$page['data'][$key]['deliverTypeName'] = WSTLangDeliverType($v['deliverType']==1);

        		$shotGoodsSpecNames = [];
        		if($v["goodsSpecNames"]){
        			$goodsSpecNames = explode('@@_@@',$v["goodsSpecNames"]);
	        		foreach ($goodsSpecNames as $key2 => $spec) {
	        			if($spec!=''){
		        			$vspec = explode('：',$spec);
			    	 	 	$shotGoodsSpecNames[] = $vspec[1];
	        			}
		    	 	}
        		}
	    	 	$page['data'][$key]['shotGoodsSpecNames'] = count($shotGoodsSpecNames)>0?(implode('，',$shotGoodsSpecNames)):"";
        	}
        }
        $page['status'] = 1;
        return $page;
	}


	public function getOrderDetail($uId=0,$shopId=0){
		$porderId = (int)input("porderId");
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$where = [];
		$where[] = ['po.id','=',$porderId];
		if($shopId>0){
			$where[] = ['po.shopId','=',$shopId];
		}else{
			$where[] = ['po.userId','=',$userId];
		}
        $rs = Db::name('presale_orders po')
					->join("presales p","p.id=po.presaleId","inner")
					->join("shops s","s.shopId=po.shopId","inner")
					->field("po.*,po.shopId,s.shopImg,s.shopName,s.shopAddress")
                    ->where($where)
                    ->find();
       	$mlist = Db::name("presale_moneys")->where(["porderId"=>$porderId])->field("moneyType,refundStatus")->select();
       	$refundMap = [];
       	$payMap = [1=>0,2=>0];
       	foreach ($mlist as $key => $v) {
       		$refundMap[$v['moneyType']] = $v["refundStatus"];
       		$payMap[$v['moneyType']] = 1;
       	}
       	$rs['payMap'] = $payMap;
       	$rs['refundMap'] = $refundMap;
        $rs['goodsPrice'] = WSTBCMoney($rs['goodsMoney']/$rs['goodsNum'],0);
       	return $rs;
	}


	public function pageQueryOrders($uId=0){
		$ftype = (int)input("ftype/d",-2);
		$orderNo = input("orderNo");
		$shopName = input("shopName");
		$presaleId = (int)input("presaleId");

		$where = [];
		$where[] = ["po.presaleId",'=',$presaleId];
		if($ftype>-2){//待付款
			$where[] = ["po.presaleStatus",'=',$ftype];
		}
		$shopId = (int)session('WST_USER.shopId');
		if($orderNo!='')$where[] = ["po.orderNo",'like','%'.$orderNo.'%'];
		if($shopName!='')$where[] = ["s.shopName",'like','%'.$shopName.'%'];

		$page = Db::name('presale_orders po')
					->join("presales p","p.id=po.presaleId","inner")
					->join("shops s","s.shopId=po.shopId","inner")
					->where(["po.shopId"=>$shopId])
					->where($where)
					->field("po.*,po.shopId,s.shopImg,s.shopName")
					->order("po.id","desc")
            		 ->paginate(input('pagesize/d'))->toArray();
        if(count($page['data'])>0){
        	$now = date("Y-m-d H:i:s");
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);

        		$page['data'][$key]['goodsPrice'] = WSTBCMoney($v['goodsMoney']/$v['goodsNum'],0);
        		$page['data'][$key]['ftype'] = $ftype;
        		if($v['presaleStatus']==0){
        			$page['data'][$key]['pkey'] = WSTBase64urlEncode('ding@'.$v["id"]);
        		}else if($v['presaleStatus']==1){
        			$page['data'][$key]['pkey'] = WSTBase64urlEncode('surplus@'.$v["id"]);
        		}
        		if($v['presaleStatus']==1){
        			$page['data'][$key]['canPaySurplus'] = ($v['startPayTime']<=$now && $v['endPayTime']>=$now)?1:0;
        		}
        		$page['data'][$key]['payTypeName'] = WSTLangPayType($v['payType']);
	    	 	$page['data'][$key]['deliverTypeName'] = WSTLangDeliverType($v['deliverType']==1);

        		$shotGoodsSpecNames = [];
        		if($v["goodsSpecNames"]){
        			$goodsSpecNames = explode('@@_@@',$v["goodsSpecNames"]);
	        		foreach ($goodsSpecNames as $key2 => $spec) {
	        			if($spec!=''){
		        			$vspec = explode('：',$spec);
			    	 	 	$shotGoodsSpecNames[] = $vspec[1];
	        			}
		    	 	}
        		}
	    	 	$page['data'][$key]['shotGoodsSpecNames'] = count($shotGoodsSpecNames)>0?(implode('，',$shotGoodsSpecNames)):"";
        	}
        }
        $page['status'] = 1;
        return $page;
	}

}
