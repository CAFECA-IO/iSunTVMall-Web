<?php
namespace wstmart\common\model;
use think\Db;
use Env;
use think\Loader;
use wstmart\common\model\LogSms;
use wstmart\common\model\OrderRefunds as M;
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
	protected $pk = 'orderId';
	/**
	 * 快速下单
	 */
	public function quickSubmit($orderSrc = 0, $uId=0){
		$deliverType = 0;

		$payType = 1;
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($userId==0)return WSTReturn(lang('no_login'));
		$isUseScore = (int)input('isUseScore');
		$useScore = (int)input('useScore');
		//检测购物车
		$carts = model('common/carts')->getQuickCarts($uId);
		if(empty($carts['carts']))return WSTReturn(lang('select_want_buy_goods'));
		//计算最大可用积分
		$orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney']-$carts['promotionMoney'],$uId,($isUseScore==1)?1:0,$useScore);
		//生成订单
		Db::startTrans();
		try{
			//提交订单前执行钩子
			hook('beforeSubmitOrder',['carts'=>$carts,"payType"=>$payType]);
			$shopOrder = current($carts['carts']);
			$goods = $shopOrder['list'][0];
			if($goods['goodsStock']<$goods['cartNum'])return WSTReturn(lang('order_fail_goods_stock_less'));
			//给用户分配卡券
			$cards = model('GoodsVirtuals')->where(['goodsId'=>$goods['goodsId'],'dataFlag'=>1,'shopId'=>$goods['shopId'],'isUse'=>0])->lock(true)->limit($goods['cartNum'])->select();
			if(count($cards)<$goods['cartNum'])return WSTReturn(lang('order_fail_goods_stock_less'));
			//修改库存
			Db::name('goods')->where('goodsId',$goods['goodsId'])->update([
				'goodsStock'=>Db::raw('goodsStock-'.$goods['cartNum']),
                'saleNum'=>Db::raw('saleNum+'.$goods['cartNum'])
			]);
			$orderunique = WSTOrderQnique();
			$orderNo = WSTOrderNo();
			$orderScore = 0;
			//创建订单
			$order = [];
			$order['orderNo'] = $orderNo;
			$order['orderType'] = 1;
			$order['areaId'] = 0;
			$order['userName'] = '';
			$order['userAddress'] = '';
			$order['userId'] = $userId;
			$order['shopId'] = $shopOrder['shopId'];
			$order['payType'] = $payType;
			$order['goodsMoney'] = $shopOrder['goodsMoney'];
			$order['deliverType'] = $deliverType;
			$order['deliverMoney'] = 0;
			$order['totalMoney'] = $order['goodsMoney']+$order['deliverMoney'];
			$order['scoreMoney'] = 0;
			$order['useScore'] = 0;
			if($orderUsableScore['money']>0){
				$order['scoreMoney'] = $orderUsableScore['money'];
				$order['useScore'] = $orderUsableScore['score'];
			}
			$order['realTotalMoney'] = WSTPositiveNum($order['totalMoney'] - $order['scoreMoney'] - $shopOrder['promotionMoney']);
			$order['needPay'] = $order['realTotalMoney'];
			if($order['needPay']>0){
	            $order['orderStatus'] = -2;//待付款
				$order['isPay'] = 0;
			}else{
				$order['orderStatus'] = 0;//待发货
				$order['isPay'] = 1;
				$order['payFrom'] = 'others';
				$order['payTime'] = date('Y-m-d H:i:s');
			}
			//积分
			$orderScore = 0;
			//如果开启下单获取积分则有积分
			if(WSTConf('CONF.isOrderScore')==1){
				$orderScore = WSTMoneyGiftScore($order['goodsMoney']);
			}
			$order['orderScore'] = $orderScore;
			// 获得的积分可抵扣金额
			$order['getScoreVal'] = WSTScoreToMoney($order['orderScore']);

			$shopId = (int)$shopOrder['shopId'];
			if($shopOrder['isInvoice']==1){
				$isInvoice = ((int)input('post.isInvoice_'.$shopId)!=0)?1:0;
				$invoiceClient = ($isInvoice==1)?input('post.invoiceClient_'.$shopId):'';
				$order['isInvoice'] = $isInvoice;
				if($isInvoice==1){
					$order['invoiceJson'] = model('invoices')->getInviceInfo((int)input('param.invoiceId_'.$shopId));// 发票信息
				    $order['invoiceClient'] = $invoiceClient;
				}else{
					$order['invoiceJson'] = '';
					$order['invoiceClient'] = '';
				}
			}else{
				$order['isInvoice'] = 0;
				$order['invoiceJson'] = '';
				$order['invoiceClient'] = '';
			}

			$order['orderRemarks'] = input('post.remark_'.$shopId);
			$order['orderunique'] = $orderunique;
			$order['orderSrc'] = $orderSrc;
			$order['dataFlag'] = 1;
			$order['payRand'] = 1;
			$order['createTime'] = date('Y-m-d H:i:s');
			//创建订单前执行钩子
			hook('beforeInsertOrder',['order'=>&$order,'carts'=>$carts , 'shopOrder'=>&$shopOrder]);

			$result = $this->data($order,true)->isUpdate(false)->allowField(true)->save($order);
			if(false !== $result){

				// 经过钩子处理之后的goods
				$goods = $shopOrder['list'][0];

				$orderId = $this->orderId;
				//标记虚拟卡券为占用状态
				$goodsCards = [];
			    foreach ($cards as $key => $card) {
				    model('GoodsVirtuals')->where('id',$card['id'])->update(['isUse'  => 1,'orderId' => $orderId,'orderNo' => $orderNo]);
				    $goodsCards[] = ['cardId'=>$card->id];
			    }
				//创建订单商品记录
				$orderGgoods = [];
				$orderGoods['orderId'] = $orderId;
				$orderGoods['goodsId'] = $goods['goodsId'];
				$orderGoods['goodsNum'] = $goods['cartNum'];
				$orderGoods['goodsPrice'] = $goods['shopPrice'];
				$orderGoods['goodsSpecId'] = 0;
				$orderGoods['goodsSpecNames'] = '';
				$orderGoods['goodsName'] = $goods['goodsName'];
				$orderGoods['goodsImg'] = $goods['goodsImg'];
				$orderGoods['commissionRate'] = WSTGoodsCommissionRate($goods['goodsCatId']);
				$orderGoods['goodsCode'] = '';
				$orderGoods['goodsType'] = $goods['goodsType'];
				$orderGoods['extraJson'] = json_encode($goodsCards);
				$orderGoods['promotionJson'] = '';
				$orderGoods["orderGoodscommission"] = 0;
				//计算订单总佣金
				$commissionFee = 0;
                if((float)$orderGoods['commissionRate']>0){
                	$orderGoodscommission = round($goods['shopPrice']*1*$orderGoods['commissionRate']/100,2);
                	$orderGoods["orderGoodscommission"] = $orderGoodscommission;
                	$commissionFee += $orderGoodscommission;
				}
				/****************************************  优惠券减免的金额start  ****************************************/
				if(isset($goods['couponVal']))$orderGoods['couponVal'] = $goods['couponVal'];
				/****************************************  优惠券减免的金额end  ****************************************/

				/****************************************  积分抵扣的金额start  ****************************************/
				$rate = $goods['shopPrice']*$goods['cartNum']/$order['goodsMoney'];
				if((int)WSTConf('CONF.isOpenScorePay')==1){// 后台有开启积分支付
					// 该订单总共使用的积分->$order['useScore'] 积分可抵扣的金额->$order['scoreMoney']
					// 虚拟商品只有一件商品
					// 最后一件商品的使用积分=订单使用积分-同订单下其他商品使用积分的总和;积分减免金额同理
					$orderGoods['useScoreVal'] = $order['useScore'];
					$orderGoods['scoreMoney'] = $order['scoreMoney'];
				}
				//如果开启下单获取积分则有积分
				// $orderGoods['getScoreVal'] = 0;
				if(WSTConf('CONF.isOrderScore')==1){
					$orderGoods['getScoreVal'] = WSTMoneyGiftScore($goods['shopPrice']*$goods['cartNum']);
					// 获得的积分可抵扣金额
					$orderGoods['getScoreMoney'] = WSTScoreToMoney($orderGoods['getScoreVal']);
				}
				/****************************************  积分抵扣的金额end  ****************************************/

				$orderTotalGoods = [];
				$orderTotalGoods[] = $orderGoods;
				//创建订单商品前执行钩子
			    hook('beforeInsertOrderGoods',['orderId'=>$orderId,'orderGoods'=>&$orderTotalGoods,'carts'=>$carts]);
				Db::name('order_goods')->insertAll($orderTotalGoods);

				$this->where('orderId',$orderId)->update(['commissionFee'=>$commissionFee]);
				//提交订单后执行钩子
				hook('afterSubmitOrder',['orderId'=>$orderId]);
				//创建积分流水--如果有抵扣积分就肯定是开启了支付支付
				if($order['useScore']>0){
					$score = [];
					$score['userId'] = $userId;
					$score['score'] = $order['useScore'];
					$score['dataSrc'] = 1;
					$score['dataId'] = $orderId;
					$score['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'order_get_score_tips','params'=>[$order['orderNo'],$order['orderScore']]]);
					$score['scoreType'] = 0;
					model('UserScores')->add($score);
				}
				//建立订单记录
				$logOrder = [];
				$logOrder['orderId'] = $orderId;
				$logOrder['orderStatus'] = -2;
				$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'order_success_wait_pay']);
				$logOrder['logUserId'] = $userId;
				$logOrder['logType'] = 0;
				$logOrder['logTime'] = date('Y-m-d H:i:s');
				Db::name('log_orders')->insert($logOrder);
				//等待支付-给店铺增加提示消息
			    $tpl = WSTMsgTemplates('ORDER_SUBMIT');
		        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${ORDER_NO}'];
		            $replace = [$orderNo];

		        	$msg = array();
		            $msg["shopId"] = $shopOrder['shopId'];
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
					$msg["shopId"] = $shopOrder['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 2;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'method'=>'quickSubmit','params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);
				}
		        //微信消息
		        if((int)WSTConf('CONF.wxenabled')==1){
		            $params = [];
		            $params['ORDER_NO'] = $orderNo;
	                $params['ORDER_TIME'] = date('Y-m-d H:i:s');
	                $goodsNames = $goods['goodsName']."*".$goods['cartNum'];
		            $params['GOODS'] = $goodsNames;
		            $params['MONEY'] = $order['realTotalMoney'] + $order['scoreMoney'];
		            $params['ADDRESS'] = '';
		            $params['PAY_TYPE'] = WSTLangPayType($order['payType']);

		            $msg = array();
					$tplCode = "WX_ORDER_SUBMIT";
					$msg["shopId"] = $shopOrder['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);

		        }
				//虚拟商品支付完成-立即发货
				if($order['needPay']==0){
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = 0;
					$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'order_success_has_pay']);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);
					$this->handleVirtualGoods($orderId);
				}
			}
			//删除session的购物车商品
			session('TMP_CARTS',null);
			Db::commit();
			hook("afterOrderPaySuccess",['orderId'=>$orderunique,'isBatch'=>1,'uerSystem'=>[0,1],'printCatId'=>1]);
			return WSTReturn(lang('submit_success'), 1,$orderunique);
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('operation_fail'),-1);
        }
	}
	/**
	 * 正常订单
	 */
	public function submit($orderSrc = 0, $uId=0){
		$addressId = (int)input('post.s_addressId');
		$deliverType = ((int)input('post.deliverType')!=0)?1:0;

		$payType = ((int)input('post.payType')!=0)?1:0;
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$isUseScore = (int)input('isUseScore');
		$useScore = (int)input('useScore');
		if($userId==0)return WSTReturn(lang('no_login'));
		//检测购物车
		$carts = model('common/carts')->getCarts(true, $userId);
		if(empty($carts['carts']))return WSTReturn(lang('order_fail_goods_stock_less'));

		//检测地址是否有效
		$address = Db::name('user_address')->where(['userId'=>$userId,'addressId'=>$addressId,'dataFlag'=>1])->find();
		if(empty($address)){
			return WSTReturn(lang('invalid_user_address'));
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
		WSTUnset($address, 'isDefault,dataFlag,createTime,userId');


		//计算出每个订单应该分配的金额和积分
		$orderScoreMoney = $this->allocScoreMoney($carts,$isUseScore,$useScore, $uId);
		//生成订单
		Db::startTrans();
		try{
			//提交订单前执行钩子
			hook('beforeSubmitOrder',['carts'=>$carts,"payType"=>$payType]);
			$orderunique = WSTOrderQnique();
			foreach ($carts['carts'] as $ckey =>$shopOrder){
				$orderNo = WSTOrderNo();
				$orderScore = 0;
				$shopId = $shopOrder['shopId'];
				$deliverType = ((int)input('post.deliverType_'.$shopId)!=0)?1:0;

				//创建订单
				$order = [];
				$order = array_merge($order,$address);
				$order['orderNo'] = $orderNo;
				$order['userId'] = $userId;
				$order['shopId'] = $shopId;
				$order['payType'] = $payType;
				$order['goodsMoney'] = $shopOrder['goodsMoney'];
				//计算运费和总金额
				$order['deliverType'] = $deliverType;
				if($shopOrder['isFreeShipping']){
                    $order['deliverMoney'] = 0;
				}else{
					$order['deliverMoney'] = ($deliverType==1)?0:WSTOrderFreight($shopId,$order['areaId2'],$shopOrder);
				}
				if($deliverType==1){//自提
					$order['storeId'] = (int)input("storeId_".$shopId);
					$order['storeType'] = 1;
				}
				$order['totalMoney'] = $order['goodsMoney']+$order['deliverMoney'];
                //积分支付-计算分配积分和金额
                $shopOrderMoney = $orderScoreMoney[$shopId];
				$order['scoreMoney'] = $shopOrderMoney['useMoney'];
				$order['useScore'] = $shopOrderMoney['useScore'];
				//实付金额要减去积分兑换的金额和店铺总优惠
				$order['realTotalMoney'] = WSTPositiveNum($order['totalMoney'] - $order['scoreMoney'] - $shopOrder['promotionMoney']);
				$order['needPay'] = $order['realTotalMoney'];
                if($payType==1){
                	if($order['needPay']>0){
                        $order['orderStatus'] = -2;//待付款
				        $order['isPay'] = 0;
                	}else{
                        $order['orderStatus'] = 0;//待发货
				        $order['isPay'] = 1;
				        $order['payTime'] = date('Y-m-d H:i:s');
						$order['payFrom'] = 'others';
						if($deliverType==1)$order['verificationCode'] = WSTOrderVerificationCode($shopId);
                	}
				}else{
					$order['orderStatus'] = 0;//待发货
					if($order['needPay']==0){
						$order['isPay'] = 1;
						$order['payFrom'] = 'others';
						$order['payTime'] = date('Y-m-d H:i:s');
					}
					if($deliverType==1)$order['verificationCode'] = WSTOrderVerificationCode($shopId);
				}
				//积分
				$orderScore = 0;
				//如果开启下单获取积分则有积分
				if(WSTConf('CONF.isOrderScore')==1){
				    $orderScore = WSTMoneyGiftScore($order['realTotalMoney']-$order['deliverMoney']);
				}
				$order['orderScore'] = $orderScore;
				// 获得的积分可抵扣金额
				$order['getScoreVal'] = WSTScoreToMoney($order['orderScore']);

				if($shopOrder['isInvoice']==1){
					$isInvoice = ((int)input('post.isInvoice_'.$shopId)!=0)?1:0;
					$invoiceClient = ($isInvoice==1)?input('post.invoiceClient_'.$shopId):'';
					$order['isInvoice'] = $isInvoice;
					if($isInvoice==1){
						$order['invoiceJson'] = model('invoices')->getInviceInfo((int)input('param.invoiceId_'.$shopId),$userId);// 发票信息
						$order['invoiceClient'] = $invoiceClient;
					}else{
						$order['invoiceJson'] = '';// 发票信息
						$order['invoiceClient'] = '';
					}
				}else{
					$order['isInvoice'] = 0;
					$order['invoiceJson'] = '';// 发票信息
					$order['invoiceClient'] = '';
				}

				$order['orderRemarks'] = input('post.remark_'.$shopId);
				$order['orderunique'] = $orderunique;
				$order['orderSrc'] = $orderSrc;
				$order['dataFlag'] = 1;
				$order['payRand'] = 1;
				$order['createTime'] = date('Y-m-d H:i:s');
				//创建订单前执行钩子
				hook('beforeInsertOrder',['order'=>&$order,'carts'=>$carts, 'shopOrder'=>&$shopOrder]);
				$result = $this->data($order,true)->isUpdate(false)->allowField(true)->save($order);
				if(false !== $result){
					$orderId = $this->orderId;
					$orderTotalGoods = [];
					$commissionFee = 0;

					/**
					 * 计算订单中的商品可优惠多少金额
					 */
					// 1.先计算出商品总价  $order['goodsMoney']为订单下的商品总价
					// 2.计算积分抵扣的金额、使用的积分、获得的积分
					// 3.计算满减活动抵扣的金额【参与满减活动的商品】
					// 4.计算优惠券抵扣的金额【参与优惠券减免的商品】

					// 商品下累计减免的积分
					$ogScore = 0;
					// 商品下累计减免的积分金额
					$ogScoreMoney = 0;
					// 订单下最后一件商品索引
					$lastOgIndex = count($shopOrder['list'])-1;
					foreach ($shopOrder['list'] as $gkey =>$goods){
						//创建订单商品记录
						$orderGgoods = [];
						$orderGoods['orderId'] = $orderId;
						$orderGoods['goodsId'] = $goods['goodsId'];
						$orderGoods['goodsNum'] = $goods['cartNum'];
						$orderGoods['goodsPrice'] = $goods['shopPrice'];
						$orderGoods['goodsSpecId'] = $goods['goodsSpecId'];
						if(!empty($goods['specNames'])){
							$specNams = [];
							foreach ($goods['specNames'] as $pkey =>$spec){
								$specNams[] = $spec['catName'].'：'.$spec['itemName'];
							}
							$orderGoods['goodsSpecNames'] = implode('@@_@@',$specNams);
						}else{
							$orderGoods['goodsSpecNames'] = '';
						}
						$orderGoods['goodsName'] = $goods['goodsName'];
						$orderGoods['goodsImg'] = $goods['goodsImg'];
						$orderGoods['commissionRate'] = WSTGoodsCommissionRate($goods['goodsCatId']);
						$orderGoods['goodsCode'] = '';
						$orderGoods['goodsType'] = $goods['goodsType'];
						$orderGoods['extraJson'] = '';
						$orderGoods['promotionJson'] = '';

						/****************************************  满减活动减免的金额start  ****************************************/
						// 在插件钩子内完成金额计算
						/****************************************  满减活动减免的金额end  ****************************************/

						/****************************************  优惠券减免的金额start  ****************************************/
						if(isset($goods['couponVal']))$orderGoods['couponVal'] = $goods['couponVal'];
						/****************************************  优惠券减免的金额end  ****************************************/


						/****************************************  积分抵扣的金额start  ****************************************/
						$rate = $goods['shopPrice']*$goods['cartNum']/$order['goodsMoney'];
						if((int)WSTConf('CONF.isOpenScorePay')==1){// 后台有开启积分支付
							// 该订单总共使用的积分->$order['useScore'] 积分可抵扣的金额->$order['scoreMoney']
							if($lastOgIndex===$gkey){// 最后一件商品
								// 最后一件商品的使用积分=订单使用积分-同订单下其他商品使用积分的总和;积分减免金额同理
								$orderGoods['useScoreVal'] = $order['useScore']-$ogScore;
								$orderGoods['scoreMoney'] = $order['scoreMoney']-$ogScoreMoney;
							}else{
								// 使用的积分数;执行向下取整【余下部分会在计算最后一件商品时加上】
								$orderGoods['useScoreVal'] = floor($rate*$order['useScore']);
								$ogScore += $orderGoods['useScoreVal'];
								// 订单商品的积分减免金额 = (商品价格*购买数量/商品总价格)*订单积分减免金额;保留小数点后两位
								$orderGoods['scoreMoney'] = round(WSTScoreToMoney($orderGoods['useScoreVal']), 2);
								$ogScoreMoney += $orderGoods['scoreMoney'];
							}
						}
						//如果开启下单获取积分则有积分
						// $orderGoods['getScoreVal'] = 0;
						if(WSTConf('CONF.isOrderScore')==1){
							$orderGoods['getScoreVal'] = WSTMoneyGiftScore($goods['shopPrice']*$goods['cartNum']);
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

                        $orderTotalGoods[] = $orderGoods;

						//修改库存
						if($goods['goodsSpecId']>0){
					        Db::name('goods_specs')->where('id',$goods['goodsSpecId'])->update([
                                'specStock'=>Db::raw('specStock-'.$goods['cartNum'])
					        ]);
						}
                        Db::name('goods')->where('goodsId',$goods['goodsId'])->update([
                            'goodsStock'=>Db::raw('goodsStock-'.$goods['cartNum'])
                        ]);
					}
					//创建订单商品前执行钩子
					hook('beforeInsertOrderGoods',['orderId'=>$orderId,'orderGoods'=>&$orderTotalGoods,'carts'=>$carts]);

					Db::name('order_goods')->insertAll($orderTotalGoods);
					//更新订单佣金
					$this->where('orderId',$orderId)->update(['commissionFee'=>$commissionFee]);
					//提交订单后执行钩子
					hook('afterSubmitOrder',['orderId'=>$orderId]);

					//创建积分流水--如果有抵扣积分就肯定是开启了支付支付
					if($order['useScore']>0){
						$score = [];
					    $score['userId'] = $userId;
					    $score['score'] = $order['useScore'];
					    $score['dataSrc'] = 1;
					    $score['dataId'] = $orderId;
					    $score['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'order_get_score_tips','params'=>[$orderNo,$order['orderScore']]]);
					    $score['scoreType'] = 0;
					    model('UserScores')->add($score);
					}

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
					        	$areaCode = (int)$order['areaCode'];
					        	$storeId = $order['storeId'];
					        	$store = Db::name("stores")->where(["storeId"=>$storeId])->field("storeName,storeAddress")->find();
					        	$storeName = $store["storeName"];
					        	$storeAddress = $store["storeAddress"];
					        	$splieVerificationCode = join(" ",str_split($order['verificationCode'],4));
					            $params = ['tpl'=>$tpl,'params'=>['MALL_NAME'=>WSTConf('CONF.mallName'),'ORDER_CODE'=>$splieVerificationCode,'SHOP_NAME'=>$storeName,'SHOP_ADDRESS'=>$storeAddress]];
					            model("common/LogSms")->sendSMS(0,$areaCode.$userPhone,$params,'submit','',$userId,0);
					        }
						}
				    }
					//给店铺增加提示消息
					$tpl = WSTMsgTemplates('ORDER_SUBMIT');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	                    $find = ['${ORDER_NO}'];
	                    $replace = [$orderNo];

	                	$msg = array();
			            $msg["shopId"] = $shopOrder['shopId'];
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
						$msg["shopId"] = $shopOrder['shopId'];
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
	                	$goodsNames = [];
	                	foreach ($shopOrder['list'] as $gkey =>$goods){
                            $goodsNames[] = $goods['goodsName']."*".$goods['cartNum'];
	                	}
	                	$params['GOODS'] = implode(',',$goodsNames);
	                	$params['MONEY'] = $order['realTotalMoney'] + $order['scoreMoney'];
	                	$params['ADDRESS'] = $order['userAddress']." ".$order['userName'];
	                	$params['PAY_TYPE'] = WSTLangPayType($order['payType']);

		                $msg = array();
						$tplCode = "WX_ORDER_SUBMIT";
						$msg["shopId"] = $shopOrder['shopId'];
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 4;
			            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);
		            }
				}
			}
			//删除已选的购物车商品
			Db::name('carts')->where(['userId'=>$userId,'isCheck'=>1])->delete();
			Db::commit();
			hook("afterOrderPaySuccess",['orderId'=>$orderunique,'isBatch'=>1,'uerSystem'=>[0,1],'printCatId'=>1]);
			return WSTReturn(lang('submit_success'), 1,$orderunique);
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('operation_fail'),-1);
        }
	}
	/**
	 * 计算订单可用积分和金额【积分支付使用】
	 */
	public function allocScoreMoney($carts,$isUseScore,$useScore, $uId=0){
		//计算最大可用积分
		$orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney']-$carts['promotionMoney'],$uId,($isUseScore==1)?1:0,$useScore);
		$useScore = ($useScore>$orderUsableScore['score'])?$orderUsableScore['score']:$useScore;
		$orderScoreMap = [];
		$allocOrderMoney = $orderUsableScore['money'];//积分可兑换的总金额
		$allocOrderScore = $orderUsableScore['score'];//可用积分
		$isLastOrder = false;                      //用来判断是否到最后一个订单
		$totalShop = count($carts['carts']);
		$shopNum = 0;
		foreach ($carts['carts'] as $ckey =>$shopOrder){
			$orderScoreMap[$shopOrder['shopId']]['useMoney'] = 0;
			$orderScoreMap[$shopOrder['shopId']]['useScore'] = 0;
			$shopNum++;
            if($orderUsableScore['money']>0){
				if($shopNum==$totalShop){
					$allocMoney = $allocOrderMoney;
					$allocScore = $allocOrderScore;
				}else{
					$allocMoney = $this->allocOrderMoney($orderUsableScore['money'],$carts['goodsTotalMoney'],$shopOrder['goodsMoney']);
					$allocTmpMoney = $allocOrderMoney - $allocMoney;
					//有可能计算出来金额比实际上还要大，所以要修正一下.
					if($allocTmpMoney<0){
						$allocMoney = $allocOrderMoney;
					}else{
						$allocOrderMoney = $allocTmpMoney;
					}

					$allocScore = WSTScoreToMoney($allocMoney,true);
                    $allocTmpScore = $allocOrderScore - $allocScore;
					//有可能计算出来金额比实际上还要大，修正分数
					if($allocTmpScore<0){
					    $allocScore = $allocOrderScore;
					}else{
					    $allocOrderScore = $allocTmpScore;
					}
				}
				$orderScoreMap[$shopOrder['shopId']]['useMoney'] = $allocMoney;
				$orderScoreMap[$shopOrder['shopId']]['useScore'] = $allocScore;
			}
		}
		return $orderScoreMap;
	}

	/**
	 * 分配金额和积分
	 */
	public function allocOrderMoney($useMoney,$totalOrderMoney,$orderMoney){
		 if($useMoney>$totalOrderMoney)$useMoney = $totalOrderMoney;
         return round(($useMoney*$orderMoney)/$totalOrderMoney,2);
	}

	/**
	 * 根据订单唯一流水获取订单信息
	 */
	public function getByUnique($uId=0,$orderNo=0,$isBatch=0){

		if($orderNo==0){
			$orderNo = input('orderNo');
			$isBatch = (int)input('isBatch/d',1);
		}
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($isBatch==1){
			$rs = $this->where(['userId'=>$userId,'orderunique'=>$orderNo])->field('orderId,orderNo,payType,needPay,orderunique,deliverMoney,userName,userPhone,userAddress')->select();
		}else{
			$rs = $this->where(['userId'=>$userId,'orderNo'=>$orderNo])->field('orderId,orderNo,payType,needPay,orderunique,deliverMoney,userName,userPhone,userAddress')->select();
		}

		$data = [];
		$data['orderunique'] = $orderNo;
		$data['list'] = [];
		$payType = 0;
		$totalMoney = 0;
		$orderIds = [0];
		foreach ($rs as $key =>$v){
			if($v['payType']==1)$payType = 1;
			$totalMoney = $totalMoney + $v['needPay'];
			$orderIds[] = $v['orderId'];
			$data['list'][] = $v;
		}
		$data['totalMoney'] = $totalMoney;
		$data['payType'] = $payType;
		//获取商品信息
		$goods = Db::name('order_goods')->where([['orderId','in',$orderIds]])->select();
		foreach ($goods as $key =>$v){
			// 处理app端
			if($uId>0 && isset($v['goodsName'])){
				$v['goodsName'] = htmlspecialchars_decode($v['goodsName']);
			}
			$shotGoodsSpecNames = [];
    	 	if($v['goodsSpecNames']!=""){
    	 		$v['goodsSpecNames'] = str_replace('：',':',$v['goodsSpecNames']);
    	 		$goodsSpecNames = explode('@@_@@',$v['goodsSpecNames']);

	    	 	foreach ($goodsSpecNames as $key => $spec) {
	    	 	 	$obj = explode(":",$spec);
	    	 	 	$shotGoodsSpecNames[] = $obj[1];
	    	 	}
    	 	}
    	 	$v['shotGoodsSpecNames'] = implode('，',$shotGoodsSpecNames);

			if($v['goodsSpecNames']!=''){
				$v['goodsSpecNames'] = explode('@@_@@',$v['goodsSpecNames']);
			}else{
				$v['goodsSpecNames'] = [];
			}
			$data['goods'][$v['orderId']][] = $v;

		}
		//如果是在线支付的话就要加载支付信息
		if($data['payType']==1){
			//获取支付信息
			$payments = model('payments')->where(['isOnline'=>1,'enabled'=>1])->order('payOrder asc')->select();
			$data['payments'] = $payments;
		}
		return $data;
	}

	/**
	 * 获取用户订单列表
	 */
	public function userOrdersByPage($orderStatus, $isAppraise = -1, $uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$orderNo = input('post.orderNo');
		$shopName = input('post.shopName');
		$isRefund = (int)input('post.isRefund',-1);
		$where = ['o.userId'=>$userId,'o.dataFlag'=>1];
        $condition = [];
		if(is_array($orderStatus)){
			$condition[] = ['orderStatus','in',$orderStatus];
		}else{
			$where['orderStatus'] = $orderStatus;
		}
		if($isAppraise!=-1)$where['isAppraise'] = $isAppraise;
		if($orderNo!=''){
			$condition[] = ['o.orderNo','like',"%$orderNo%"];
		}
		if($shopName != ''){
			$condition[] = ['s.shopName','like',"%$shopName%"];
		}
		if(in_array($isRefund,[0,1])){
			$where['isRefund'] = $isRefund;
		}
		$page = $this->alias('o')->join('__SHOPS__ s','o.shopId=s.shopId','left')
		             ->join('__ORDER_COMPLAINS__ oc','oc.orderId=o.orderId','left')
		             ->join('__ORDER_REFUNDS__ orf','orf.orderId=o.orderId and orf.refundStatus!=-1','left')
		             ->where($where)->where($condition)
		             ->field('o.afterSaleEndTime,o.receiveTime,o.orderRemarks,o.noticeDeliver,o.orderId,o.orderNo,s.shopName,s.shopId,s.shopQQ,s.shopWangWang,o.goodsMoney,o.totalMoney,o.realTotalMoney,
		              o.orderStatus,o.deliverType,deliverMoney,o.isPay,payType,payFrom,o.orderStatus,needPay,isAppraise,o.isRefund,orderSrc,o.createTime,o.useScore,oc.complainId,orf.id refundId,o.orderCode')
			         ->order('o.createTime', 'desc')
			         ->group('o.orderId')
			         ->paginate(input('pagesize/d'))->toArray();
	    if(count($page['data'])>0){
	    	hook("afterUserOrdersByPage",["orders"=>&$page['data']]);
	    	 $orderIds = [];
	    	 foreach ($page['data'] as $v){
	    	 	 $orderIds[] = $v['orderId'];
	    	 }
	    	 $goods = Db::name('order_goods')->where([['orderId','in',$orderIds]])->select();
	    	 $goodsMap = [];
	    	 foreach ($goods as $v){
	    	 	$v['goodsName'] = WSTStripTags($v['goodsName']);
	    	 	$shotGoodsSpecNames = [];
	    	 	if($v['goodsSpecNames']!=""){
	    	 		$v['goodsSpecNames'] = str_replace('：',':',$v['goodsSpecNames']);
	    	 		$goodsSpecNames = explode('@@_@@',$v['goodsSpecNames']);

		    	 	foreach ($goodsSpecNames as $key => $spec) {
                        if(strpos($spec, ':') !== FALSE) {
                            $obj = explode(":",$spec);
                            $shotGoodsSpecNames[] = $obj[1];
                        }
		    	 	}
	    	 	}
	    	 	$v['shotGoodsSpecNames'] = implode('，',$shotGoodsSpecNames);
	    	 	$v['goodsSpecNames'] = str_replace('@@_@@','、',$v['goodsSpecNames']);
	    	 	$goodsMap[$v['orderId']][] = $v;
	    	 }
             // 查询一个订单下是否有物流包裹
	    	 foreach ($page['data'] as $key => $v){
                 $orderExpress = Db::name('order_express')->field('expressId,expressNo')->where([['orderId','=',$v['orderId']],['isExpress','=',1]])->count();
                 $page['data'][$key]['hasExpress'] = ($orderExpress>0)?true:false;
	    	 	 $page['data'][$key]['allowRefund'] = 0;
	    	 	 //只要是已支付的，并且没有退款的，都可以申请退款操作
	    	 	 if($v['payType']==1 && $v['isRefund']==0 && $v['refundId']=='' && ($v['isPay'] ==1 || $v['useScore']>0)){
                      $page['data'][$key]['allowRefund'] = 1;
	    	 	 }
	    	 	 //货到付款中使用了积分支付的也可以申请退款
	    	 	 if($v['payType']==0 && $v['useScore']>0 && $v['refundId']=='' && $v['isRefund']==0){
                      $page['data'][$key]['allowRefund'] = 1;
	    	 	 }
	    	 	 $page['data'][$key]['list'] = $goodsMap[$v['orderId']];
	    	 	 $page['data'][$key]['isComplain'] = 1;
	    	 	 if(($v['complainId']=='') && ($v['payType']==0 || ($v['payType']==1 && $v['orderStatus']!=-2))){
	    	 	 	$page['data'][$key]['isComplain'] = '';
	    	 	 }
	    	 	 $page['data'][$key]['payTypeName'] = WSTLangPayType($v['payType']);
	    	 	 $page['data'][$key]['deliverTypeName'] = WSTLangDeliverType($v['deliverType']==1);
	    	 	 $page['data'][$key]['status'] = WSTLangOrderStatus($v['orderStatus']);
	    		 $page['data'][$key]['orderCodeTitle'] = WSTOrderModule($v['orderCode']);
	    	 	 if($v["orderStatus"]==-2){
					$page['data'][$key]['pkey'] = WSTBase64urlEncode($v["orderNo"]."@0");
				}
				// 是否可申请售后
				$page['data'][$key]['canAfterSale'] = false;
				// 订单已确认收货
				if($v['payType']==1 && $v['orderStatus']==2){
					// 判断是否已超过售后服务有效期
					// 如果 当前时间>(确认收货时间+售后服务期限) 表示无法继续申请售后
					$now = time();
					// 售后结束日期
					$endTime = strtotime($v['afterSaleEndTime']);
					$_rs = ($now<=$endTime);
					$page['data'][$key]['canAfterSale'] = $_rs;
					if($_rs){
						// 判断订单是否还能继续申请售后 【订单商品总数-售后单商品总数>0】
						$ogNum = Db::name('order_goods')
								 ->where(['orderId'=>$v['orderId']])
								 ->where("(goodsCode='' or ISNULL(goodsCode))")
								 ->value('sum(goodsNum) ogNum');
						$osNum = Db::name('order_services')->alias('os')
															 ->join('orders o','o.orderId=os.orderId','inner')
															 ->join('service_goods sg','sg.serviceId=os.id')
															 ->where(['o.orderId'=>$v['orderId'],'os.isClose'=>0])
															 ->value('sum(sg.goodsNum) osNum');
						$page['data'][$key]['canAfterSale'] = ($ogNum>$osNum);
					}
				}
	    	 }
	    	 hook('afterQueryUserOrders',['page'=>&$page]);
	    }
	    return $page;
	}

	/**
	 * 获取商家订单
	 */
	public function shopOrdersByPage($orderStatus, $sId=0){
		$userId = input('post.userId');
		$orderNo = input('post.orderNo');
		$shopName = input('post.shopName');
		$payType = (int)input('post.payType');
		$deliverType = (int)input('post.deliverType');
		// 未退款订单
		$refund = (int)input('post.refund');
		$isRefund = (int)input('post.isRefund');

		$shopId = ($sId==0)?(int)session('WST_USER.shopId'):$sId;

		$where = ['shopId'=>$shopId,'dataFlag'=>1];
        $condition = [];
		if(is_array($orderStatus)){
			$condition[] = ['orderStatus','in',$orderStatus];
		}else{
			$where['orderStatus'] = $orderStatus;
		}
		if($userId>0)$where['userId'] =  $userId;
		if($orderNo!='')$condition[] = ['orderNo','like',"%$orderNo%"];
		if($shopName!='')$condition[] = ['shopName','like',"%$shopName%"];
		if($payType > -1)$where['payType'] =  $payType;
		if($deliverType > -1)$where['deliverType'] =  $deliverType;
		if($refund > 0){
			$condition[] =  ['orf.id','gt',0];
			$condition[] =  ['orf.refundStatus','in',[0,1,2]];
			if($isRefund>-1){
                $condition[] =  ['o.isRefund','=',$isRefund];
            }
		}

		$page = $this->alias('o')->where($where)->where($condition);
        if($refund > 0){
            $page = $page->join('__ORDER_REFUNDS__ orf','orf.orderId=o.orderId','left');
        }else{
            $page = $page->join('__ORDER_REFUNDS__ orf','orf.orderId=o.orderId and refundStatus=0','left');
        }
		$page = $page->field('o.orderRemarks,o.noticeDeliver,o.orderId,orderNo,goodsMoney,totalMoney,realTotalMoney,orderStatus,deliverType,deliverMoney,isAppraise,isRefund,o.deliverType deliverTypes
		              ,payType,payFrom,userAddress,orderStatus,isPay,isAppraise,userName,userPhone,orderSrc,o.createTime,orf.id refundId,orf.refundStatus,o.orderCode')
			  ->order('o.createTime', 'desc')
			  ->paginate()->toArray();
	    if(count($page['data'])>0){
	    	 $orderIds = [];
	    	 foreach ($page['data'] as $v){
	    	 	 $orderIds[] = $v['orderId'];
	    	 }
	    	 $goods = Db::name('order_goods')->where([['orderId','in',$orderIds]])->select();
	    	 $goodsMap = [];
	    	 foreach ($goods as $v){
	    	 	$v['goodsName'] = WSTStripTags($v['goodsName']);
	    	 	$v['goodsSpecNames'] = str_replace('@@_@@','、',$v['goodsSpecNames']);
	    	 	$goodsMap[$v['orderId']][] = $v;
	    	 }
	    	 foreach ($page['data'] as $key => $v){
	    	 	 $page['data'][$key]['orderCodeTitle'] = WSTOrderModule($v['orderCode']);
	    	 	 $page['data'][$key]['list'] = $goodsMap[$v['orderId']];
	    	 	 $page['data'][$key]['payTypeName'] = WSTLangPayType($v['payType']);
	    	 	 $page['data'][$key]['deliverTypeName'] = WSTLangDeliverType($v['deliverType']==1);
	    	 	 $page['data'][$key]['deliverType'] = $v['deliverType'];
	    	 	 $page['data'][$key]['status'] = WSTLangOrderStatus($v['orderStatus']);
	    	 	 $orderExpress = Db::name('order_express')->where(['orderId'=>$v['orderId']])->find();
	    	 	 $hasExpress = Db::name('order_express')->where(['orderId'=>$v['orderId'],'isExpress'=>1])->find();
                 $page['data'][$key]['isDeliver'] = (!empty($orderExpress))?1:0;
                 $page['data'][$key]['hasExpress'] = (!empty($hasExpress))?1:0;
	    	 }
	    }
	    return $page;
	}
	/**
	 * 商家发货
	 */
	public function deliver($uId=0, $sId=0){
		$orderId = (int)input('post.id');
		$expressId = (int)input('post.expressId');
		$expressNo = ($expressId>0)?input('post.expressNo'):'';
        $selectOrderGoodsIds = WSTFormatIn(',',input('post.selectOrderGoodsIds'));
        $shopId = ($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $deliverType = (int)input('post.deliverType');
        $order = $this->where(['shopId'=>$shopId,'orderId'=>$orderId,'orderStatus'=>0])->field('orderId,orderNo,userId,deliverType')->find();
        if(empty($order))return WSTReturn(lang('operation_failed_check_order_status'));
        // 前台传过来的deliverType,deliverType==1需要物流，deliverType=0无需物流
        $deliverType = ($deliverType==1)?1:0;
        if($deliverType==1 && $expressId>0 && $expressNo=='')return WSTReturn(lang('require_logistics_information'));
        $finishDeliver = false;
        if($order['deliverType'] == 1){//客户自提
            $finishDeliver = true;
        }else{//商家发货
            //验证前台传过来的数据
	        $selectOrderGoodsIds = Db::name('order_goods')->where([['orderId','=',$orderId],['id','in',$selectOrderGoodsIds]])->column('id');
	        if(count($selectOrderGoodsIds)==0)return WSTReturn(lang('select_deliver_goods'));
	        //该订单在订单商品表里所有的id,保存在orderGoodsIds变量中
	        $orderGoodsIds = Db::name('order_goods')->field('id')->where([['orderId','=',$orderId]])->column('id');
	        //首先去order_express表查询已发货的orderGoodsId
	        $orderExpressGoodsIds = Db::name('order_express')->field('orderGoodsId')->where('orderId','=',$orderId)->select();
	        $deliveredGoodsIds = [];
            // 如果可以查询到发货数据,则从剔除已发货的数据
	        if(!empty($orderExpressGoodsIds)){
	            $deliveredGoodsIds = [];
	            foreach($orderExpressGoodsIds as $k => $v){
	                $temp = explode(',',$v['orderGoodsId']);
	                $deliveredGoodsIds = array_merge($deliveredGoodsIds,$temp);
	            }
	            //将已发货的orderGoodsId从$orderGoodsIds中剔除掉--剩下orderGoodsId中待发货的
		        foreach($orderGoodsIds as $k => $v){
		            if(in_array($v,$deliveredGoodsIds)){
		                unset($orderGoodsIds[$k]);
		            }
		        }
            }
	        //将已发货的orderGoodsId从$selectOrderGoodsIds中删除--剩下合法的要发货的
	        foreach($selectOrderGoodsIds as $k => $v){
	            if(in_array($v,$deliveredGoodsIds)){
	                unset($selectOrderGoodsIds[$k]);
	            }
	        }
	        //没有需要发货的商品
	        if(count($selectOrderGoodsIds)==0)return WSTReturn(lang('operation_failed_no_deliver_goods'));
	        //将要发货的selectOrderGoodsIds从orderGoodsId中删除--剩下真是要发货的
	        foreach($orderGoodsIds as $k => $v){
	            if(in_array($v,$selectOrderGoodsIds)){
	                 unset($orderGoodsIds[$k]);
	            }
	        }
	        //没有待发货的商品则订单完成
	        if(count($orderGoodsIds) == 0)$finishDeliver = true;
        }
		Db::startTrans();
		try{
            //只要需要商家发货的都插入订单物流表里
            if($order['deliverType'] == 0 && count($selectOrderGoodsIds)>0){
                $expressData = [
                    'orderId'=>$orderId,
                    'orderGoodsId'=>implode(',',$selectOrderGoodsIds),
                    'isExpress'=>$deliverType,
                    'expressId'=>$expressId,
                    'expressNo'=>$expressNo,
                    'createTime'=>date('Y-m-d H:i:s'),
                    'deliveryTime'=>date('Y-m-d H:i:s')
                ];
                $orderExpressResult = Db::name('order_express')->insert($expressData);
            }
            $orderStatus = 0;
            if($finishDeliver){
            	$orderStatus = 1;
                $orderData = ['orderStatus'=>1,'deliveryTime'=>date('Y-m-d H:i:s')];
                $result = $this->where([['orderId','=',$order['orderId']],['shopId','=',$shopId]])->update($orderData);
            }
		    //新增订单日志
			$logOrder = [];
			$logOrder['orderId'] = $orderId;
			$logOrder['orderStatus'] = $orderStatus;
			$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'shop_has_deliverd']);
			$logOrder['logUserId'] = $userId;
			$logOrder['logType'] = 0;
			$logOrder['logTime'] = date('Y-m-d H:i:s');
			Db::name('log_orders')->insert($logOrder);
			//发送一条用户信息
			$tpl = WSTMsgTemplates('ORDER_DELIVERY');
	        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	            $find = ['${ORDER_NO}','${EXPRESS_NO}'];
	            $replace = [$order['orderNo'],($expressNo=='')?WSTLang('nothing'):$expressNo];
	            WSTSendMsg($order['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$orderId]);
	        }
	        //微信消息
	        if(WSTDatas('ADS_TYPE',3)!='' || WSTDatas('ADS_TYPE',4)!=''){
		        $params = [];
		        if($expressId>0){
		            $express = Db::name('express')
                                ->alias('e')
                                ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                                ->where(['e.expressId'=>$expressId])->find();
		            $params['EXPRESS'] = $express['expressName'];
		            $params['EXPRESS_NO'] = $expressNo;
		        }else{
		            $params['EXPRESS'] = WSTLang('nothing');
		            $params['EXPRESS_NO'] = WSTLang('nothing');
		        }
		        $params['ORDER_NO'] = $order['orderNo'];
		        if(WSTConf('CONF.wxenabled')==1){
			        WSTWxMessage(['CODE'=>'WX_ORDER_DELIVERY','userId'=>$order['userId'],'URL'=>Url('wechat/orders/index',['type'=>'waitReceive'],true,true),'params'=>$params]);
			    }else{
			    	//WSTWeMessage(['CODE'=>'WE_ORDER_DELIVERY','userId'=>$order['userId'],'URL'=>'pages/users/orders/orders','form_id'=>input('weappFormId'),'params'=>$params]);
			    }
			}
			Db::commit();
			hook("afterShopDeliver",['orderId'=>$orderId,'shopId'=>$shopId,'uerSystem'=>[0,1],'printCatId'=>2]);
			return WSTReturn(lang('operation_success'),1);
		}catch (\Exception $e) {
	        Db::rollback();
	       return WSTReturn(lang('operation_fail'),-1);
		}
		return WSTReturn(lang('operation_failed_check_order_status'));
	}
	/**
	 * 用户收货[同时给外部虚拟商品收货调用]
	 * 注意：修改该函数的逻辑，要留意一同修改管理员修改订单的函数逻辑[admin/model/orders->receiveOrder]
	 */
	public function receive($orderId = 0,$userId = 0){
		if($orderId==0 && $userId==0){
            $orderId = (int)input('post.id');
		    $userId = (int)session('WST_USER.userId');
		}
		hook("beforeUserReceive",["orderId"=>$orderId,"userId"=>$userId]);
		$order = $this->alias('o')->join('__SHOPS__ s','o.shopId=s.shopId','left')
		              ->where(['o.userId'=>$userId,'o.orderId'=>$orderId,'o.orderStatus'=>1])
		              ->field('o.orderId,o.orderNo,o.payType,s.userId,s.shopId,o.orderScore,o.realTotalMoney,commissionFee,o.createTime')->find();
		if(!empty($order)){
			Db::startTrans();
		    try{
				$limitDay = (int)WSTConf('CONF.afterSaleServiceDays');
				// 售后结束时间
				$afterSaleEndTime = date('Y-m-d H:i:s', strtotime("+{$limitDay} day"));
				$data = ['orderStatus'=>2,'receiveTime'=>date('Y-m-d H:i:s'),'afterSaleEndTime'=>$afterSaleEndTime];
			    $result = $this->where('orderId',$order['orderId'])->update($data);
				if(false != $result){
					//修改商品成交量
					$goodss = Db::name('order_goods')->where('orderId',$order['orderId'])->field('goodsId,goodsNum,goodsSpecId')->select();
					foreach($goodss as $key =>$v){
						Db::name('goods')->where('goodsId',$v['goodsId'])->update([
                            'saleNum'=>Db::raw('saleNum+'.$v['goodsNum'])
                        ]);
						if($v['goodsSpecId']>0){
							Db::name('goods_specs')->where('id',$v['goodsSpecId'])->update([
	                            'saleNum'=>Db::raw('saleNum+'.$v['goodsNum'])
	                        ]);
						}
					}

					//确认收货后执行钩子
					hook('afterUserReceive',['orderId'=>$orderId]);
					//修改商家未计算订单数
					$torder = Db::name('orders')->where("orderId",$orderId)->field("orderId,commissionFee")->find();
					Db::name('shops')->where('shopId',$order['shopId'])->update([
						'noSettledOrderNum'=>Db::raw('noSettledOrderNum+1'),
						'noSettledOrderFee'=>Db::raw('noSettledOrderFee-'.$torder['commissionFee'])
					]);
					//新增订单日志
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = 2;
					$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'user_has_received']);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);
					//标记会员在这个商家下已下单，有则标记，没有数据也不影响
			        Db::name('shop_members')->where(['userId'=>$userId,'shopId'=>$order["shopId"]])->update(['isOrder'=>1,'totalOrderNum'=>DB::Raw('totalOrderNum+1'),'totalOrderMoney'=>DB::Raw('totalOrderMoney+'.$order['realTotalMoney']),'lastOrderTime'=>$order['createTime']]);
			        $shopMember = model('common/ShopMembers')->checkIsShopMember($userId,$order["shopId"]);
			        if($shopMember){
                        // 计算用户在店铺有效的消费金额
                        WSTCalcUserValidConsumption($userId,$order["shopId"]);
                        // 更新用户的会员等级
                        $groupId = WSTGetMemberGroupId($userId,$order["shopId"]);
                        if($groupId>0){
                            $member = Db::name('shop_members')->where(['userId'=>$userId,'shopId'=>$order["shopId"]])->find();
                            if($member['groupId'] != $groupId){
                                Db::name('shop_members')->where(['userId'=>$userId,'shopId'=>$order["shopId"]])->update(['groupId'=>$groupId]);
                            }
                        }
                    }
					//发送一条商家信息
					$tpl = WSTMsgTemplates('ORDER_RECEIVE');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	                    $find = ['${ORDER_NO}'];
	                    $replace = [$order['orderNo']];

	                	$msg = array();
			            $msg["shopId"] = $order["shopId"];
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
			            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
			            model("common/MessageQueues")->add($msg);
	                }

					//给用户增加积分
					if(WSTConf("CONF.isOrderScore")==1 && $order['orderScore']>0){
						$score = [];
						$score['userId'] = $userId;
						$score['score'] = $order['orderScore'];
						$score['dataSrc'] = 1;
						$score['dataId'] = $orderId;
						$score['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'order_get_score_tips','params'=>[$order['orderNo'],$order['orderScore']]]);
						$score['scoreType'] = 1;
						model('UserScores')->add($score,true);
					}
					//微信消息
		            if((int)WSTConf('CONF.wxenabled')==1){
		            	$params = [];
		                $params['ORDER_NO'] = $order['orderNo'];
		                $params['ORDER_TIME'] = date('Y-m-d H:i:s');
	                    //WSTWxMessage(['CODE'=>'WX_ORDER_RECEIVE','userId'=>$order['userId'],'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params]);
		            	$msg = array();
						$tplCode = "WX_ORDER_RECEIVE";
						$msg["shopId"] = $order["shopId"];
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 4;
			            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params] ;
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);
		            }
					Db::commit();
					return WSTReturn(lang('operation_success'),1);
				}
		    }catch (\Exception $e) {
	            Db::rollback();
	            return WSTReturn(lang('operation_fail').$e->getMessage(),-1);
	        }
		}
		return WSTReturn(lang('operation_failed_check_order_status'));
	}
	/**
	 * 用户取消订单
	 * 注意：修改该函数的逻辑，要留意一同修改管理员修改订单的函数逻辑[admin/model/orders->cancelOrder]
	 */
	public function cancel($uId=0){
		$orderId = (int)input('post.id');
		hook('beforeCancelOrder',['uId'=>$uId,'orderId'=>$orderId]);
		$reason = (int)input('post.reason');
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$order = $this->alias('o')->join('__SHOPS__ s','o.shopId=s.shopId','left')
		              ->where([['o.orderStatus','in',[-2,0]],['o.userId','=',$userId],['o.orderId','=',$orderId]])
		              ->field('o.orderId,o.orderNo,s.userId,s.shopId,o.orderCode,o.isPay,o.orderType,o.payType,o.orderStatus,o.useScore,o.scoreMoney,o.realTotalMoney')->find();
		$reasonData = WSTDatas('ORDER_CANCEL',$reason);
		if(empty($reasonData))return WSTReturn(lang('invalid_cancel_reason'));

		if(!empty($order)){
			Db::startTrans();
		    try{
                $data = ['orderStatus'=>-1,'cancelReason'=>$reason];
				//把实付金额设置为0
				if($order['payType']==0 || ($order['payType']==1 && $order['isPay']==0)){
					$data['realTotalMoney'] = 0;
					$order['realTotalMoney'] = 0;
					$data['isClosed'] = 1;
				}
			    $result = $this->where('orderId',$order['orderId'])->update($data);
				if(false != $result){
                    //正常订单商品库存处理
                    $goods = Db::name('order_goods')->alias('og')->join('__GOODS__ g','og.goodsId=g.goodsId','inner')
						           ->where('orderId',$orderId)->field('og.*,g.isSpec')->select();
                    //返还商品库存
					foreach ($goods as $key => $v){
						//处理虚拟产品
						if($v['goodsType']==1){
	                        $extraJson = json_decode($v['extraJson'],true);
	                        foreach ($extraJson as  $ecard) {
	                            Db::name('goods_virtuals')->where('id',$ecard['cardId'])->update(['orderId'=>0,'orderNo'=>'','isUse'=>0]);
	                        }
	                        $counts = Db::name('goods_virtuals')->where(['dataFlag'=>1,'goodsId'=>$v['goodsId'],'isUse'=>0])->count();
	                        Db::name('goods')->where('goodsId',$v['goodsId'])->setField('goodsStock',$counts);
						}else{
							if($order['orderCode']=='order'){
								//修改库存
								if($v['isSpec']>0){
							        Db::name('goods_specs')->where('id',$v['goodsSpecId'])->setInc('specStock',$v['goodsNum']);
								}
								Db::name('goods')->where('goodsId',$v['goodsId'])->setInc('goodsStock',$v['goodsNum']);
							}
						}
                    }
					//新增订单日志
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = -1;
					$logOrder['logJson'] = json_encode(['type'=>'data','key'=>'ORDER_CANCEL','val'=>$reason]);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);
					//提交订单后执行钩子
					hook('afterCancelOrder',['orderId'=>$orderId]);
					//发送一条商家信息
					$tpl = WSTMsgTemplates('ORDER_CANCEL');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){

	                    $find = ['${ORDER_NO}','${REASON}'];
	                    $replace = [$order['orderNo'],$reasonData['dataName']];

	                	$msg = array();
			            $msg["shopId"] = $order["shopId"];
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
			            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
			            model("common/MessageQueues")->add($msg);
	                }
					//判断是否需要发送商家短信
					$tpl = WSTMsgTemplates('PHONE_SHOP_CANCEL_ORDER');
					if((int)WSTConf('CONF.smsOpen')==1 && $tpl['tplContent']!='' && $tpl['status']=='1'){

						$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$order['orderNo']]];

						$msg = array();
						$tplCode = "PHONE_SHOP_CANCEL_ORDER";
						$msg["shopId"] = $order["shopId"];
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 2;
			            $msg["paramJson"] = ['CODE'=>$tplCode,'method'=>'cancel','params'=>$params];
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);
					}
	                //微信消息
		            if((int)WSTConf('CONF.wxenabled')==1){
		            	$params = [];
		                $params['ORDER_NO'] = $order['orderNo'];
		                $goodsNames = [];
		                foreach ($goods as $gkey =>$g){
	                        $goodsNames[] = $g['goodsName']."*".$g['goodsNum'];
		                }
		                $params['REASON'] = $reasonData['dataName'];
		                $params['GOODS'] = implode(',',$goodsNames);
		                $params['MONEY'] = $order['realTotalMoney'] + $order['scoreMoney'];

		            	$msg = array();
						$tplCode = "WX_ORDER_CANCEL";
						$msg["shopId"] = $order["shopId"];
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 4;
			            $msg["paramJson"] = ['CODE'=>'WX_ORDER_CANCEL','URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);

		            }

					Db::commit();
					return WSTReturn(lang('operation_success'),1);
				}
			}catch (\Exception $e) {
		        Db::rollback();
	            return WSTReturn(lang('operation_fail'),-1);
	        }
		}
		return WSTReturn(lang('operation_failed_check_order_status'));
	}
	/**
	 * 用户拒收订单
	 */
	public function reject($uId=0){
		$orderId = (int)input('post.id');
		hook('beforeRejectOrder',['uId'=>$uId,'orderId'=>$orderId]);
		$reason = (int)input('post.reason');
		$content = input('post.content');
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$order = $this->alias('o')->join('__SHOPS__ s','o.shopId=s.shopId','left')
		              ->where(['o.userId'=>$userId,'o.orderId'=>$orderId,'o.orderStatus'=>1])
		              ->field('o.orderId,o.orderNo,o.shopId,s.userId,payType,o.userAddress,o.userName,o.realTotalMoney,o.scoreMoney')->find();
		$reasonData = WSTDatas('ORDER_REJECT',$reason);
		if(empty($reasonData))return WSTReturn(lang('invalid_reject_receive_reason'));
		if($reason==10000 && $content=='')return WSTReturn(lang('require_reject_receive_reason'));
		if(!empty($order)){
			Db::startTrans();
		    try{
				$data = ['orderStatus'=>-3,'rejectReason'=>$reason];
				if($reason==10000)$data['rejectOtherReason'] = $content;
				//如果是货到付款拒收的话，把实付金额设置为0
				if($order['payType']==0)$data['realTotalMoney'] = 0;
			    $result = $this->where('orderId',$order['orderId'])->update($data);
				if(false != $result){
					//新增订单日志
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = -3;
					$logOrder['logJson'] = json_encode(['type'=>'data','key'=>'ORDER_REJECT','val'=>$reason,'remark'=>$content]);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);
					//发送一条商家信息
					$tpl = WSTMsgTemplates('ORDER_REJECT');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	                    $find = ['${ORDER_NO}','${REASON}'];
	                    $replace = [$order['orderNo'],$reasonData['dataName'].(($reason==10000)?"-".$content:"")];

	                	$msg = array();
			            $msg["shopId"] = $order['shopId'];
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
			            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
			            model("common/MessageQueues")->add($msg);
	                }
					//判断是否需要发送商家短信
					$tpl = WSTMsgTemplates('PHONE_SHOP_REJECT_ORDER');
					if((int)WSTConf('CONF.smsOpen')==1 && $tpl['tplContent']!='' && $tpl['status']=='1'){

						$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$order['orderNo']]];

						$msg = array();
						$tplCode = "PHONE_SHOP_REJECT_ORDER";
						$msg["shopId"] = $order["shopId"];
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 2;
			            $msg["paramJson"] = ['CODE'=>$tplCode,'method'=>'reject','params'=>$params];
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);
					}

					//微信消息
		            if((int)WSTConf('CONF.wxenabled')==1){
		            	$params = [];
		                $params['ORDER_NO'] = $order['orderNo'];
	                    $goods = Db::name('order_goods')->where('orderId',$order['orderId'])->select();
		                $goodsNames = [];
		                foreach ($goods as $gkey =>$goods){
	                        $goodsNames[] = $goods['goodsName']."*".$goods['goodsNum'];
		                }
		                $params['GOODS'] = implode(',',$goodsNames);
		                $params['MONEY'] = $order['realTotalMoney'] + $order['scoreMoney'];
		                $params['ADDRESS'] = $order['userAddress']." ".$order['userName'];
		                $params['REASON'] = $reasonData['dataName'].(($reason==10000)?"-".$content:"");

		            	$msg = array();
						$tplCode = "WX_ORDER_REJECT";
						$msg["shopId"] = $order['shopId'];
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 4;
			            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);

		            }
					Db::commit();
					return WSTReturn(lang('operation_success'),1);
				}
			}catch (\Exception $e) {
		        Db::rollback();
	            return WSTReturn(lang('operation_fail'),-1);
	        }
		}
		return WSTReturn(lang('operation_failed_check_order_status'));
	}
	/**
	 * 获取订单价格
	 */
	public function getMoneyByOrder($orderId = 0){
		$orderId = ($orderId>0)?$orderId:(int)input('post.id');
		return $this->where('orderId',$orderId)->field('orderId,orderNo,goodsMoney,deliverMoney,useScore,scoreMoney,totalMoney,realTotalMoney')->find();
	}


	/**
	 * 修改订单价格
	 */
	public function editOrderMoney($uId=0, $sId=0){
		$orderId = input('post.id');
		$orderMoney = (float)input('post.orderMoney');
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$shopId = ($sId==0)?(int)session('WST_USER.shopId'):$sId;
		if($orderMoney<0.01)return WSTReturn(lang('order_price_min_price'));
		Db::startTrans();
		try{
			//修改订单价格前执行勾子
			hook('beforeEditOrderMoney',['orderId'=>$orderId,"orderMoney"=>$orderMoney]);
			$data = array();
			$data["realTotalMoney"] = $orderMoney;
			$data["needPay"] = $orderMoney;
			$data["payRand"] = Db::raw('payRand+1');
			$result = $this->where(['orderId'=>$orderId,'shopId'=>$shopId,'orderStatus'=>-2])->update($data);

			if(false !== $result){
				//新增订单日志
				$logOrder = [];
				$logOrder['orderId'] = $orderId;
				$logOrder['orderStatus'] = -2;
				$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'shop_eidt_order_price','remark'=>$orderMoney]);
				$logOrder['logUserId'] = $userId;
				$logOrder['logType'] = 0;
				$logOrder['logTime'] = date('Y-m-d H:i:s');
				Db::name('log_orders')->insert($logOrder);
				Db::commit();
				return WSTReturn(lang('operation_success'),1);
			}
		}catch (\Exception $e) {
		    Db::rollback();
	        return WSTReturn(lang('operation_fail'),-1);
	    }
	}

	/**
	 * 获取订单详情
	 */
	public function getByView($orderId, $uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$shopId = ($uId==0)?(int)session('WST_USER.shopId'):$uId;
		$orders = Db::name('orders')->alias('o')
		               ->join('__SHOPS__ s','o.shopId=s.shopId','left')
		               ->join('__ORDER_COMPLAINS__ oc','oc.orderId=o.orderId','left')
		               ->join('__ORDER_REFUNDS__ orf ','o.orderId=orf.orderId','left')
		               ->where('o.dataFlag=1 and o.orderId='.$orderId.' and ( o.userId='.$userId.' or o.shopId='.$shopId.')')
		               ->field('o.*,s.areaId shopAreaId,s.shopAddress,s.shopTel,s.shopName,s.shopQQ,s.shopWangWang,s.latitude,s.longitude,orf.id refundId,orf.refundRemark,orf.refundStatus,orf.refundTime,orf.backMoney,orf.backMoney,oc.complainId,o.orderCode')->find();
		if(empty($orders))return WSTReturn(lang('invalid_order_information'));
		hook("afterOrderQuery",["order"=>&$orders]);
		// 获取店铺地址
		$orders['shopAddr'] = model('common/areas')->getParentNames($orders['shopAreaId']);
		$orders['shopAddress'] = implode('',$orders['shopAddr']).$orders['shopAddress'];
		unset($orders['shopAddr']);
		//获取订单信息
		$log = Db::name('log_orders')->where('orderId',$orderId)->order('logId asc')->select();
		$orders['log'] = [];
		$logFilter = [];
		foreach ($log as $key => $v) {
			if(in_array($orders['orderStatus'],[-2,0,1,2]) && in_array($v['orderStatus'],$logFilter))continue;
			$orders['log'][] = $v;
			$logFilter[] = $v['orderStatus'];
		}
		//获取订单商品
		$orders['goods'] = Db::name('order_goods')->alias('og')->join('__GOODS__ g','g.goodsId=og.goodsId','left')->where('orderId',$orderId)->field('og.*,g.goodsSn')->order('id asc')->select();
		foreach ($orders['goods'] as $key => $v) {
		 	$orders['goods'][$key]['goodsName'] = WSTStripTags($v['goodsName']);
			//如果是虚拟商品
			if($orders['orderType']==1){
				$orders['goods'][$key]['extraJson'] = json_decode($v['extraJson'],true);
			}
			$shotGoodsSpecNames = [];
		 	if($v['goodsSpecNames']!=""){
		 		$v['goodsSpecNames'] = str_replace('：',':',$v['goodsSpecNames']);
		 		$goodsSpecNames = explode('@@_@@',$v['goodsSpecNames']);

	    	 	foreach ($goodsSpecNames as $key2 => $spec) {
	    	 	 	$obj = explode(":",$spec);
	    	 	 	$shotGoodsSpecNames[] = $obj[1];
	    	 	}
		 	}
		 	$orders['goods'][$key]['shotGoodsSpecNames'] = implode('，',$shotGoodsSpecNames);
		}

        // 发货时间与快递单号
        $orderExpressNos = Db::name('order_express')->where([['orderId','=',$orderId],['isExpress','=',1]])->column("expressNo");
        if($orderExpressNos){
            // 多张快递单号用逗号拼接，并过滤掉没有单号的
            $orders["expressNo"] = implode(",",array_filter($orderExpressNos));
        }else{
            $orders["expressNo"] = '';
        }
        //格式化发票信息
		if($orders['isInvoice']==1){
			$orders['invoice'] = json_decode($orders['invoiceJson'],true);
		}
		$orders['isComplain'] = 1;
		if(($orders['complainId']=='') && ($orders['payType']==0 || ($orders['payType']==1 && $orders['orderStatus']!=-2))){
			$orders['isComplain'] = '';
		}
		if($orders['deliverType']==1){
			if($orders['storeId']){
				$store = Db::name("stores")->where(['storeId'=>$orders['storeId']])->find();
				$areaNames = model("common/areas")->getParentNames($store['areaId']);
				$store["areaNames"] = implode("",$areaNames);
				$orders['store'] = $store;
			}
		}

		$orders['allowRefund'] = 0;
	 	//只要是已支付的，并且没有退款的，都可以申请退款操作
	 	if($orders['payType']==1 && $orders['isRefund']==0 && $orders['refundId']=='' && ($orders['isPay'] ==1 || $orders['useScore']>0)){
              $orders['allowRefund'] = 1;
	 	}
	 	//货到付款中使用了积分支付的也可以申请退款
	 	if($orders['payType']==0 && $orders['useScore']>0 && $orders['refundId']=='' && $orders['isRefund']==0){
              $orders['allowRefund'] = 1;
	 	}
		// 是否可申请售后
		$orders['canAfterSale'] = false;
		// 订单已确认收货
		if($orders['payType']==1 && $orders['orderStatus']==2){
			// 判断是否已超过售后服务有效期
			// 如果 当前时间>(确认收货时间+售后服务期限) 表示无法继续申请售后
			$now = time();
			// 售后结束日期
			$endTime = strtotime($orders['afterSaleEndTime']);
			$_rs = ($now<=$endTime);
			$orders['canAfterSale'] = $_rs;
			if($_rs){
				// 判断订单是否还能继续申请售后 【订单商品总数-售后单商品总数>0】
				$ogNum = Db::name('order_goods')
						 ->where(['orderId'=>$orderId])
						 ->where("(goodsCode='' or ISNULL(goodsCode))")
						 ->value('sum(goodsNum) ogNum');
				$osNum = Db::name('order_services')->alias('os')
													 ->join('orders o','o.orderId=os.orderId','inner')
													 ->join('service_goods sg','sg.serviceId=os.id')
													 ->where(['o.orderId'=>$orderId,'os.isClose'=>0])
													 ->value('sum(sg.goodsNum) osNum');
				$orders['canAfterSale'] = ($ogNum>$osNum);
			}
		}

		return $orders;
	}



	/**
	* 根据订单id获取 商品信息跟商品评价
	*/
	public function getOrderInfoAndAppr($uId=0){
		$orderId = (int)input('oId');
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;

		$goodsInfo = Db::name('order_goods')
					->field('id,orderId,goodsName,goodsId,goodsSpecNames,goodsImg,goodsSpecId,goodsCode')
					->where(['orderId'=>$orderId])
					->select();
		//根据商品id 与 订单id 取评价
		$alreadys = 0;// 已评价商品数
		$count = count($goodsInfo);//订单下总商品数
		if($count>0){
			foreach($goodsInfo as $k=>$v){
				$goodsInfo[$k]['goodsSpecNames'] = str_replace('@@_@@', ';', $v['goodsSpecNames']);

				// app端处理
				if($uId>0 && isset($v['goodsName'])){
					$goodsInfo[$k]['goodsName'] = htmlspecialchars_decode($v['goodsName']);
				}

				$appraise = Db::name('goods_appraises')
							->field('goodsScore,serviceScore,timeScore,content,images,createTime')
							->where(['goodsId'=>$v['goodsId'],
							         'goodsSpecId'=>$v['goodsSpecId'],
									 'orderId'=>$orderId,
									 'dataFlag'=>1,
									 'userId'=>$userId,
									 'orderGoodsId'=>$v['id'],
									 ])->find();
				if(!empty($appraise)){
					++$alreadys;
					$appraise['images'] = ($appraise['images']!='')?explode(',', $appraise['images']):[];
				}
				$goodsInfo[$k]['appraise'] = $appraise;
			}
		}
		return ['count'=>$count,'data'=>$goodsInfo,'alreadys'=>$alreadys];

	}

    /**
     * 根据订单id,订单商品id获取单个商品信息
     */
    public function getOrderInfoByGoodsId($uId=0){
        $orderId = (int)input('oId');
        $orderGoodsId = (int)input('orderGoodsId');
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;

        $goodsInfo = Db::name('order_goods')
            ->field('id,orderId,goodsName,goodsId,goodsSpecNames,goodsImg,goodsSpecId,goodsCode')
            ->where(['orderId'=>$orderId,'id'=>$orderGoodsId])
            ->find();
        if($goodsInfo){
            $goodsInfo['goodsSpecNames'] = str_replace('@@_@@', ';', $goodsInfo['goodsSpecNames']);
        }
        return $goodsInfo;
    }

	/**
	 * 检查订单是否已支付
	 */
	public function checkOrderPay (){
		$userId = (int)session('WST_USER.userId');
		$orderNo = input("orderNo");
		$isBatch = (int)input("isBatch");
		$rs = array();
		$where = ["userId"=>$userId,"dataFlag"=>1,"orderStatus"=>-2,"isPay"=>0,"payType"=>1];
		if($isBatch==1){
			$where['orderunique'] = $orderNo;
		}else{
			$where['orderNo'] = $orderNo;
		}
		$rs = $this->field('orderId,orderNo')->where($where)->select();
		if(count($rs)>0){
			return WSTReturn('',1);
		}else{
			return WSTReturn(lang('order_has_pay'),-1);
		}
	}

	/**
	 * 检查订单是否已支付
	 */
	public function checkOrderPay2 ($obj){
		$userId = $obj["userId"];
		$orderNo = $obj["orderNo"];
		$isBatch = $obj["isBatch"];
		$rs = array();
		$where = ["userId"=>$userId,"dataFlag"=>1,"orderStatus"=>-2,"isPay"=>0,"payType"=>1];
		if($isBatch==1){
			$where['orderunique'] = $orderNo;
		}else{
			$where['orderNo'] = $orderNo;
		}
		$rs = $this->field('orderId,orderNo')->where($where)->select();
		if(count($rs)>0){
			return WSTReturn('',1);
		}else{
			return WSTReturn(lang('order_has_pay'),-1);
		}
	}


	/**
	 * 虚拟商品支付处理
	 */
	public function handleVirtualGoods($orderId){
		$order= Db::name('orders')->alias('o')->join('__SHOPS__ s','o.shopId=s.shopId ','inner')
				->where('orderId',$orderId)->field('orderId,orderNo,o.shopId,s.userId,o.userId ouserId,o.realTotalMoney,o.payFrom,o.orderScore,o.createTime')
				->find();
		//新增订单日志
		$logOrder = [];
		$logOrder['orderId'] = $order['orderId'];
		$logOrder['orderStatus'] = 1;
		$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'shop_has_deliverd']);
		$logOrder['logUserId'] = $order['userId'];
		$logOrder['logType'] = 0;
		$logOrder['logTime'] = date('Y-m-d H:i:s');
		Db::name('log_orders')->insert($logOrder);
		$logOrder = [];
		$logOrder['orderId'] = $order['orderId'];
		$logOrder['orderStatus'] = 2;
		$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'user_has_received']);
		$logOrder['logUserId'] = $order['ouserId'];
		$logOrder['logType'] = 0;
		$logOrder['logTime'] = date('Y-m-d H:i:s');
		Db::name('log_orders')->insert($logOrder);

		//给用户增加积分
		if(WSTConf("CONF.isOrderScore")==1 && $order['orderScore']>0){
			$score = [];
			$score['userId'] = $order['ouserId'];
			$score['score'] = $order['orderScore'];
			$score['dataSrc'] = 1;
			$score['dataId'] = $order['orderId'];
			$score['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'order_get_score_tips','params'=>[$order['orderNo'],$order['orderScore']]]);
			$score['scoreType'] = 1;
			model('UserScores')->add($score,true);
		}
		//修改订单状态
		$limitDay = (int)WSTConf('CONF.afterSaleServiceDays');
		// 售后结束时间
		$afterSaleEndTime = date('Y-m-d H:i:s', strtotime("+{$limitDay} day"));
		// 写入售后有效期
		Db::name('orders')->where('orderId',$order['orderId'])
						  ->update([
									'orderStatus'=>2,
									'isClosed'=>1,
									'deliveryTime'=>date('Y-m-d H:i:s'),
									'receiveTime'=>date('Y-m-d H:i:s'),
									'afterSaleEndTime'=>$afterSaleEndTime
								]);
		//标记会员在这个商家下已下单，有则标记，没有数据也不影响
		Db::name('shop_members')->where(['userId'=>$order['userId'],'shopId'=>$order["shopId"]])->update(['isOrder'=>1,'totalOrderNum'=>DB::Raw('totalOrderNum+1'),'totalOrderMoney'=>DB::Raw('totalOrderMoney+'.$order['realTotalMoney']),'lastOrderTime'=>$order['createTime']]);
		//分配卡券号
        $orderGoods = Db::name('order_goods')->where(['orderId'=>$order['orderId'],'goodsType'=>1])->field('id,goodsName,extraJson')->find();
        $cardIds = [];
        $extraJson = json_decode($orderGoods['extraJson'],true);
        foreach ($extraJson as $ogextra) {
            $cardIds[] = $ogextra['cardId'];
        }
        $cards = model('common/GoodsVirtuals')->where([['id','in',$cardIds]])->field('id,cardNo,cardPwd')->select();
        $cardmap = [];
        foreach ($cards as $card) {
            $cardmap[$card['id']] = $card;
        }
        $ogcards = [];
        $extra = json_decode($orderGoods['extraJson'],true);
        foreach ($extra as $ogextra) {
        	$ogextra['cardId'] = $cardmap[$ogextra['cardId']]['id'];
	        $ogextra['cardNo'] = $cardmap[$ogextra['cardId']]['cardNo'];
	        $ogextra['cardPwd'] = $cardmap[$ogextra['cardId']]['cardPwd'];
	        $ogextra['isUse'] = 0;
	        $ogcards[] = $ogextra;
        }
        Db::name('order_goods')->where('id',$orderGoods['id'])->update(['extraJson'=>json_encode($ogcards)]);
        //即时结算 - 需过售后期之后才能进行结算
		// model('common/Settlements')->speedySettlement($orderId);
        //发送一条商家信息
		$tpl = WSTMsgTemplates('ORDER_SHOP_AUTO_DELIVERY');
	    if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	        $find = ['${ORDER_NO}','${GOODS}'];
	        $replace = [$order['orderNo'],$orderGoods['goodsName']];

	    	$msg = array();
            $msg["shopId"] = $order["shopId"];
            $msg["tplCode"] = $tpl["tplCode"];
            $msg["msgType"] = 1;
            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
            $msg["msgJson"] = ['from'=>1,'dataId'=>$order['orderId']];
            model("common/MessageQueues")->add($msg);
	    }
	    //发送一条用户信息
		$tpl = WSTMsgTemplates('ORDER_USER_AUTO_DELIVERY');
	    if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	        $find = ['${ORDER_NO}','${GOODS}'];
	        $replace = [$order['orderNo'],$orderGoods['goodsName']];
	        WSTSendMsg($order["ouserId"],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$order['orderId']]);
	    }
		//判断是否需要发送商家短信
		$tpl = WSTMsgTemplates('PHONE_SHOP_PAY_ORDER');
		if((int)WSTConf('CONF.smsOpen')==1 && $tpl['tplContent']!='' && $tpl['status']=='1'){

			$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$order['orderNo']]];

			$msg = array();
			$tplCode = "PHONE_SHOP_PAY_ORDER";
			$msg["shopId"] = $order["shopId"];
            $msg["tplCode"] = $tplCode;
            $msg["msgType"] = 2;
            $msg["paramJson"] = ['CODE'=>$tplCode,'method'=>'handleVirtualGoods','params'=>$params];
            $msg["msgJson"] = "";
            model("common/MessageQueues")->add($msg);
		}
		//微信消息-已支付
		if((int)WSTConf('CONF.wxenabled')==1){
			$params = [];
			$params['ORDER_NO'] = $order['orderNo'];
			$params['PAY_TIME'] = date('Y-m-d H:i:s');
			$params['MONEY'] = $order['realTotalMoney'];
			$params['PAY_SRC'] = WSTLangPayFrom($order['payFrom']);

			$msg = array();
			$tplCode = "WX_ORDER_PAY";
			$msg["shopId"] = $order["shopId"];
            $msg["tplCode"] = $tplCode;
            $msg["msgType"] = 4;
            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
            $msg["msgJson"] = "";
            model("common/MessageQueues")->add($msg);

		}

	}


	/**
	 * 完成支付订单
	 */
	public function complatePay ($obj){
		hook("beforeComplatePay",$obj);

		$trade_no = $obj["trade_no"];
		$isBatch = (int)$obj["isBatch"];
		$orderNo = $obj["out_trade_no"];
		$userId = (int)$obj["userId"];
		$payFrom = $obj["payFrom"];
		$payMoney = (float)$obj["total_fee"];
		if($payFrom!=''){
			$cnt = model('orders')
			->where(['payFrom'=>$payFrom,"userId"=>$userId,"tradeNo"=>$trade_no])
			->count();
			if($cnt>0){
				return WSTReturn(lang('order_has_pay'),-1);
			}
		}
        $where = [["userId","=",$userId],["dataFlag","=",1],["orderStatus","=",-2],["isPay","=",0],["payType","=",1]];
		$where[] = ["needPay",">",0];
		if($isBatch==1){
			$where[] = ['orderunique',"=",$orderNo];
		}else{
			$where[] = ['orderNo',"=",$orderNo];
		}
		$orders = model('orders')->where($where)->field('needPay,orderId,orderType,orderNo,shopId,payFrom,realTotalMoney,deliverType,storeId,userPhone,areaCode')->select();

	    if(count($orders)==0)return WSTReturn(lang('invalid_order_information'),-1);
		$needPay = 0;
	    foreach ($orders as $key => $v) {
	    	$needPay += $v['needPay'];
	    }
		$needPay = WSTBCMoney($needPay,0);
		if($needPay>$payMoney){
			return WSTReturn(lang('incorrect_payment_amount'),-1);
		}
		Db::startTrans();
		try{
			$data = array();
			$data["needPay"] = 0;
			$data["isPay"] = 1;
			$data["orderStatus"] = 0;
			$data["tradeNo"] = $trade_no;
			$data["payFrom"] = $payFrom;
			$data["payTime"] = date("Y-m-d H:i:s");
			$data["isBatch"] = $isBatch;
			$data["totalPayFee"] = $payMoney*100;
			$rs = model('orders')->where($where)->update($data);

			if($payFrom=='ccgwpays'){
				Db::name('ccgw_address_trans')->where(['ccTxid'=>$trade_no])->update(['dataStatus'=>1]);
			}
			
			if($needPay>0 && false != $rs){
				foreach ($orders as $key =>$v){
					$orderId = $v["orderId"];

					if($v['deliverType']==1){
						$verificationCode = WSTOrderVerificationCode($v->shopId);
						model('orders')->where(['orderId'=>$orderId])->update(["verificationCode"=>$verificationCode]);

						//自提订单（已支付）发送核验码
						$tpl = WSTMsgTemplates('PHONE_USER_ORDER_VERIFICATCODE');
				        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
				        	$userPhone = $v->userPhone;
				        	$areaCode = (int)$v->areaCode;
				        	$storeId = $v->storeId;
				        	$store = Db::name("stores")->where(["storeId"=>$storeId])->field("storeName,storeAddress")->find();
				        	$storeName = $store["storeName"];
				        	$storeAddress = $store["storeAddress"];
				        	$splieVerificationCode = join(" ",str_split($verificationCode,4));
				            $params = ['tpl'=>$tpl,'params'=>['MALL_NAME'=>WSTConf('CONF.mallName'),'ORDER_CODE'=>$splieVerificationCode,'SHOP_NAME'=>$storeName,'SHOP_ADDRESS'=>$storeAddress]];
				            model("common/LogSms")->sendSMS(0,$areaCode.$userPhone,$params,'complatePay','',$userId,0);
				        }

					}
					//新增订单日志
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = 0;
					$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'order_success_has_pay']);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);
					//创建一条充值流水记录
					$lm = [];
					$lm['targetType'] = 0;
					$lm['targetId'] = $userId;
					$lm['dataId'] = $orderId;
					$lm['dataSrc'] = 1;
					$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'order_trans_recharge_tips','params'=>[$v['orderNo'],$needPay]]);
					$lm['moneyType'] = 1;
					$lm['money'] = $needPay;
					$lm['payType'] = $payFrom;
					$lm['tradeNo'] = $trade_no;
					$lm['createTime'] = date('Y-m-d H:i:s');
					model('LogMoneys')->create($lm);
					//创建一条支出流水记录
					$lm = [];
					$lm['targetType'] = 0;
					$lm['targetId'] = $userId;
					$lm['dataId'] = $orderId;
					$lm['dataSrc'] = 1;
					$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'order_trans_expend_tips','params'=>[$v['orderNo'],$needPay]]);
					$lm['moneyType'] = 0;
					$lm['money'] = $needPay;
					$lm['payType'] = 0;
					$lm['createTime'] = date('Y-m-d H:i:s');
					model('LogMoneys')->create($lm);
					//虚拟商品处理
	                if($v['orderType']==1){
	                    	$this->handleVirtualGoods($v['orderId']);
	                }else{
						//发送一条商家信息
						$tpl = WSTMsgTemplates('ORDER_HASPAY');
				        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
				            $find = ['${ORDER_NO}'];
				            $replace = [$v['orderNo']];

				            $msg = array();
				            $msg["shopId"] = $v->shopId;
				            $msg["tplCode"] = $tpl["tplCode"];
				            $msg["msgType"] = 1;
				            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
				            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
				            model("common/MessageQueues")->add($msg);
				        }

						//判断是否需要发送商家短信
		                $tpl = WSTMsgTemplates('PHONE_SHOP_PAY_ORDER');
						if((int)WSTConf('CONF.smsOpen')==1 && $tpl['tplContent']!='' && $tpl['status']=='1'){

							$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$v['orderNo']]];

							$msg = array();
							$tplCode = "PHONE_SHOP_PAY_ORDER";
							$msg["shopId"] = $v->shopId;
				            $msg["tplCode"] = $tplCode;
				            $msg["msgType"] = 2;
				            $msg["paramJson"] = ['CODE'=>$tplCode,'method'=>'complatePay','params'=>$params];
				            $msg["msgJson"] = "";
				            model("common/MessageQueues")->add($msg);
						}
						//微信消息
					    if((int)WSTConf('CONF.wxenabled')==1){
						    $params = [];
						    $params['ORDER_NO'] = $v['orderNo'];
					        $params['PAY_TIME'] = date('Y-m-d H:i:s');
						    $params['MONEY'] = $v['realTotalMoney'];
						    $params['PAY_SRC'] = WSTLangPayFrom($v['payFrom']);

					        $msg = array();
							$tplCode = "WX_ORDER_PAY";
							$msg["shopId"] = $v->shopId;
				            $msg["tplCode"] = $tplCode;
				            $msg["msgType"] = 4;
				            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
				            $msg["msgJson"] = "";
				            model("common/MessageQueues")->add($msg);

					    }
	                }
				}
			}else{
				$data = array();
				$data["userMoney"] = Db::raw("userMoney+".$payMoney);
				Db::name('users')->where("userId",$userId)->update($data);
				//创建一条充值流水记录
				$lm = [];
				$lm['targetType'] = 0;
				$lm['targetId'] = $userId;
				$lm['dataId'] = $orderNo;
				$lm['dataSrc'] = 1;
				$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'order_trans_recharge_tips2','params'=>[$payMoney]]);
				$lm['moneyType'] = 1;
				$lm['money'] = $payMoney;
				$lm['payType'] = $payFrom;
				$lm['tradeNo'] = $trade_no;
				$lm['createTime'] = date('Y-m-d H:i:s');
				model('LogMoneys')->create($lm);
			}
			Db::commit();
			hook("afterOrderPaySuccess",['orderId'=>$orderNo,'isBatch'=>$isBatch,'uerSystem'=>[0,1],'printCatId'=>1]);
			return WSTReturn(lang('pay_success'),1);
		}catch (\Exception $e) {
			Db::rollback();
			return WSTReturn(lang('operation_fail'),-1);
		}
	}

	/**
	 * 获取支付订单信息
	 */
	public function getPayOrders ($obj){
		$userId = (int)$obj["userId"];
		$orderNo = $obj["orderNo"];
		$isBatch = (int)$obj["isBatch"];
		$needPay = 0;
		$where = [["userId",'=',$userId],
				  ["dataFlag",'=',1],
				  ["orderStatus",'=',-2],
				  ["isPay",'=',0],
				  ["payType",'=',1],
				  ["needPay",'>', 0],
				  [($isBatch==1)?'orderunique':'orderNo','=',$orderNo]];
		$data = array();
		$needPay = model('orders')->where($where)->sum('needPay');
		$payRand = model('orders')->where($where)->max('payRand');
		$data["needPay"] = $needPay;
		$data["payRand"] = $payRand;
		return $data;
	}

	/**
	 * 导出订单
	 */
	public function toExport(){
		$name='order';
		$where = [];
		$where[] = ['o.dataFlag','=',1];
		$orderStatus = (int)input('orderStatus',0);
		if($orderStatus==0){
			$name='PendingDelOrder';
		}else if($orderStatus==-2){
			$name='PendingPayorder';
		}else if($orderStatus==1){
			$name='DistributionOrder';
		}else if($orderStatus==-1){
			$name='CancelOrder';
		}else if($orderStatus==-3){
			$name='RejectionOrder';
		}else if($orderStatus==2){
			$name='ReceivedOrder';
		}else if($orderStatus==10000){
			$name='CancelOrder/RejectionOrder';
		}else if($orderStatus==20000){
			$name='PendingRecOrder';
		}else if($orderStatus==30000){
            $name='RefundOrder';
        }
		$shopId = (int)session('WST_USER.shopId');
		$where[] = ['o.shopId','=',$shopId];
		$orderNo = input('orderNo');
		$shopName = input('shopName');
		$userId = (int)input('userId');
		if($userId>0)$where[] = ['o.userId','=',$userId];
		$type = (int)input('type',-1);
		$payType = $type>0?$type:(int)input('payType',-1);
		$deliverType = (int)input('deliverType');
		if($orderStatus == 10000)$orderStatus = [-1,-3];
		if($orderStatus == 20000)$orderStatus = [0,1];
		if($orderStatus == 30000){
            $orderStatus = [-1,-3];
            $where[] =  ['orf.refundStatus','in',[0,1,2]];
            $isRefund = (int)input('isRefund');
            if($isRefund>-1){
                $where[] =  ['o.isRefund','=',$isRefund];
            }
        }
		if(is_array($orderStatus)){
			$where[] = ['o.orderStatus','in',$orderStatus];
		}else{
			$where[] = ['o.orderStatus','=',$orderStatus];
		}
		if($orderNo!=''){
			$where[] = ['orderNo','like',"%$orderNo%"];
		}
		if($shopName!=''){
			$where[] = ['shopName','like',"%$shopName%"];
		}
		if($payType > -1){
			$where[] =  ['payType','=',$payType];
		}
		if($deliverType > -1){
			$where[] =  ['deliverType','=',$deliverType];
		}

		$page = $this->alias('o')->where($where)->join('__SHOPS__ s','o.shopId=s.shopId','left');
		if($name=='RefundOrder'){
		    $page = $page->join('__ORDER_REFUNDS__ orf','orf.orderId=o.orderId','left');
        }else{
            $page = $page->join('__ORDER_REFUNDS__ orf','orf.orderId=o.orderId and refundStatus=0','left');
        }
		$page = $page->join('__LOG_ORDERS__ lo','lo.orderId=o.orderId and lo.orderStatus in (-1,-3) ','left')
		->field('o.orderId,orderNo,goodsMoney,totalMoney,realTotalMoney,o.orderStatus,deliverType,deliverMoney,isAppraise,o.deliverMoney,lo.logJson,o.payTime,o.payFrom
		,o.invoiceJson,o.isMakeInvoice,o.isInvoice,o.isRefund,payType,o.userName,o.userAddress,o.areaCode,o.userPhone,o.orderRemarks,o.invoiceClient,o.receiveTime,o.deliveryTime,orderSrc,o.createTime,orf.id refundId,s.areaId shopAreaId,s.shopAddress')
		->order('o.createTime', 'desc')
		->select();
        $name = $name.date('Ymd');
		if(count($page)>0){
			foreach ($page as $v){
				$orderIds[] = $v['orderId'];
			}
			$goods = Db::name('order_goods')->where([['orderId','in',$orderIds]])->select();
			$goodsMap = [];
			foreach ($goods as $v){
				$v['goodsSpecNames'] = str_replace('@@_@@','、',$v['goodsSpecNames']);
				$goodsMap[$v['orderId']][] = $v;
			}
			foreach ($page as $key => $v){
				$page[$key]['invoiceArr'] = '';
				if($v['isInvoice']==1){
					$invoiceArr = json_decode($v['invoiceJson'],true);
					$page[$key]['invoiceArr'] = " ".$invoiceArr['invoiceHead'];
					if(isset($invoiceArr['invoiceCode'])){
						$page[$key]['invoiceArr'] = " ".$invoiceArr['invoiceHead'].'|'.$invoiceArr['invoiceCode'];
					}
				}
				$page[$key]['shopAddr'] = model('common/areas')->getParentNames($v['shopAreaId']);
		        $page[$key]['shopAddress'] = implode('',$v['shopAddr']).$v['shopAddress'];
		        if($page[$key]['deliverType']==1)$page[$key]['userAddress'] = $page[$key]['shopAddress'];
				$page[$key]['payTypeName'] = WSTLangPayType($v['payType']);
				$page[$key]['deliverType'] = WSTLangDeliverType($v['deliverType']==1);
				$page[$key]['status'] = WSTLangOrderStatus($v['orderStatus']);
				$page[$key]['goods'] = $goodsMap[$v['orderId']];
                $page[$key]['isMakeInvoice'] = ($v['isMakeInvoice']==1)?lang('has_open'):lang('has_not_open');
			}
		}
		require Env::get('root_path') . 'extend/phpexcel/PHPExcel.php';

		$objPHPExcel = new \PHPExcel();
		// 设置excel文档的属性
		$objPHPExcel->getProperties()->setCreator("WSTMart")//创建人
		->setLastModifiedBy("WSTMart")//最后修改人
		->setTitle($name)//标题
		->setSubject($name)//题目
		->setDescription($name)//描述
		->setKeywords("订单")//关键字
		->setCategory("Test result file");//种类

		// 开始操作excel表
		$objPHPExcel->setActiveSheetIndex(0);
		// 设置工作薄名称
		$objPHPExcel->getActiveSheet()->setTitle(iconv('gbk', 'utf-8', 'Sheet'));
		// 设置默认字体和大小
		$objPHPExcel->getDefaultStyle()->getFont()->setName(iconv('gbk', 'utf-8', ''));
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
		$styleArray = array(
				'font' => array(
						'bold' => true,
						'color'=>array(
								'argb' => 'ffffffff',
						)
				)
		);

		//设置宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
		$objRow = $objPHPExcel->getActiveSheet()->getStyle('A1:W1');
		$objRow->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
		$objRow->getFill()->getStartColor()->setRGB('666699');
		$objRow->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objRow->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(25);

		$objPHPExcel->getActiveSheet()->setCellValue('A1', lang('order_no'))
		->setCellValue('B1', lang('order_status'))
		->setCellValue('C1', lang('consignee'))
		->setCellValue('D1', lang('receiving_address'))
		->setCellValue('E1', lang('contact_type'))
		->setCellValue('F1', lang('payment_method'))
		->setCellValue('G1', lang('source_of_payment'))
		->setCellValue('H1', lang('delivery_mode'))
		->setCellValue('I1', lang('invoice_status'))
		->setCellValue('W1', lang('buyer_message'))
		->setCellValue('J1', lang('invoice_information'))
		->setCellValue('K1', lang('order_goods'))
		->setCellValue('L1', lang('goods_price'))
		->setCellValue('M1', lang('quantity_number'))
		->setCellValue('N1', lang('order_total_money'))
		->setCellValue('O1', lang('freight'))
		->setCellValue('P1', lang('amount_actually_paid'))
		->setCellValue('Q1', lang('order_time'))
		->setCellValue('R1', lang('payment_time'))
		->setCellValue('S1', lang('delivery_time'))
		->setCellValue('T1', lang('receiving_time'))
		->setCellValue('U1', lang('cancel_reject_reason'))
		->setCellValue('V1', lang('refund_or_not'));
		$objPHPExcel->getActiveSheet()->getStyle('A1:W1')->applyFromArray($styleArray);
		$i = 1;
		$totalRow = 0;
		for ($row = 0; $row < count($page); $row++){
			$goodsn = count($page[$row]['goods']);
			$i = $i+1;
			$i2 = $i3 = $i;
			$i = $i+(1*$goodsn)-1;
			$objPHPExcel->getActiveSheet()->mergeCells('A'.$i2.':A'.$i)->mergeCells('B'.$i2.':B'.$i)->mergeCells('C'.$i2.':C'.$i)->mergeCells('D'.$i2.':D'.$i)->mergeCells('E'.$i2.':E'.$i)->mergeCells('F'.$i2.':F'.$i)
			->mergeCells('G'.$i2.':G'.$i)->mergeCells('H'.$i2.':H'.$i)->mergeCells('I'.$i2.':I'.$i)->mergeCells('J'.$i2.':J'.$i)->mergeCells('N'.$i2.':N'.$i)->mergeCells('O'.$i2.':O'.$i)
			->mergeCells('P'.$i2.':P'.$i)->mergeCells('Q'.$i2.':Q'.$i)->mergeCells('R'.$i2.':R'.$i)->mergeCells('S'.$i2.':S'.$i)->mergeCells('T'.$i2.':T'.$i)->mergeCells('U'.$i2.':U'.$i)->mergeCells('V'.$i2.':V'.$i)->mergeCells('W'.$i2.':W'.$i);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i2, $page[$row]['orderNo'])->setCellValue('B'.$i2, $page[$row]['status'])->setCellValue('C'.$i2, $page[$row]['userName'])->setCellValue('D'.$i2, $page[$row]['userAddress'])
			->setCellValue('E'.$i2, '+'.$page[$row]['areaCode'].' '.$page[$row]['userPhone'])->setCellValue('F'.$i2, $page[$row]['payTypeName'])->setCellValue('G'.$i2, ($page[$row]['payFrom'])?WSTLangPayFrom($page[$row]['payFrom']):'')->setCellValue('H'.$i2, $page[$row]['deliverType'])
			->setCellValue('I'.$i2, $page[$row]['isMakeInvoice'])->setCellValue('W'.$i2, $page[$row]['orderRemarks'])->setCellValue('J'.$i2, $page[$row]['invoiceArr'])->setCellValue('N'.$i2, $page[$row]['totalMoney'])->setCellValue('O'.$i2, $page[$row]['deliverMoney'])->setCellValue('P'.$i2, $page[$row]['realTotalMoney'])
			->setCellValue('Q'.$i2, $page[$row]['createTime'])->setCellValue('R'.$i2, $page[$row]['payTime'])->setCellValue('S'.$i2, $page[$row]['deliveryTime'])->setCellValue('T'.$i2, $page[$row]['receiveTime'])
			->setCellValue('U'.$i2, WSTLogJson($page[$row]['logJson']))->setCellValue('V'.$i2, ($page[$row]['isRefund']==1)?'是':'');
			$objPHPExcel->getActiveSheet()->getStyle('D'.$i2)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$objPHPExcel->getActiveSheet()->getStyle('U'.$i2)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			for ($row2 = 0; $row2 < $goodsn; $row2++){
				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i3, (($page[$row]['goods'][$row2]['goodsCode']=='gift')?lang('gift_goods'):'').$page[$row]['goods'][$row2]['goodsName'].(($page[$row]['goods'][$row2]['goodsSpecNames']!='')?'【'.$page[$row]['goods'][$row2]['goodsSpecNames'].'】':''))->setCellValue('L'.$i3, $page[$row]['goods'][$row2]['goodsPrice'])->setCellValue('M'.$i3, $page[$row]['goods'][$row2]['goodsNum']);
				$objPHPExcel->getActiveSheet()->getStyle('K'.$i3)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$i3 = $i3 + 1;
			}
			$totalRow = $i3;
		}
	    $totalRow = ($totalRow==0)?1:$totalRow-1;
	    $objPHPExcel->getActiveSheet()->getStyle('A1:W'.$totalRow)->applyFromArray(array(
				'borders' => array (
						'allborders' => array (
								'style' => \PHPExcel_Style_Border::BORDER_THIN,  //设置border样式
								'color' => array ('argb' => 'FF000000'),     //设置border颜色
						)
				)
		));
		$this->PHPExcelWriter($objPHPExcel,$name);
	}


	public function addPayLog($txt){
		$logOrder = [];
		$logOrder['txt'] = $txt;
		$logOrder['logTime'] = date('Y-m-d H:i:s');
		Db::name('pay_log')->insert($logOrder);
	}

	/**
	 * 余额支付
	 */
	public function payByWallet($uId=0,$type=0){
		hook("beforePayByWallet",['uId'=>$uId,'type'=>$type]);
		$payPwd = input('payPwd');
		if(!$payPwd)return WSTReturn(lang('require_password'),-1);
		if($uId==0 || $type == 1){// 大于0表示来自app端
			$decrypt_data = WSTRSA($payPwd);
			if($decrypt_data['status']==1){
				$payPwd = $decrypt_data['data'];
			}else{
				return WSTReturn(lang('pay_fail'));
			}
		}
        $pkey = input('pkey');
        $pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
        if(count($pkey)>1){
        	$orderNo = $pkey[0];
        	$isBatch = (int)$pkey[1];
        }else{
        	$orderNo = input('orderNo');
        	$isBatch = (int)input('isBatch');
        }
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        //判断是否开启余额支付
        $isEnbalePay = model('Payments')->isEnablePayment('wallets');
        if($isEnbalePay==0)return WSTReturn(lang('illegal_payment_type'),-1);
        //判断订单状态
        $where = ["userId"=>$userId,"dataFlag"=>1,"orderStatus"=>-2,"isPay"=>0,"payType"=>1];
		if($isBatch==1){
			$where['orderunique'] = $orderNo;
		}else{
			$where['orderNo'] = $orderNo;
		}
		$orders = $this->field('orderId,orderNo,orderType,needPay,shopId,payFrom,realTotalMoney,deliverType,storeId,userPhone,areaCode')->where($where)->select();
		if(count($orders)==0)return WSTReturn(lang('order_has_pay'),-1);
		//判断订单金额是否正确
		$needPay = 0;
		foreach ($orders as $v) {
			$needPay += $v->needPay;
		}
	    //获取用户钱包
	    $user = model('users')->get(['userId'=>$userId]);
	    if($user->payPwd=='')return WSTReturn(lang('no_pay_password_tips'),-1);
	    if($user->payPwd!=md5($payPwd.$user->loginSecret))return WSTReturn(lang('pay_password_error'),-1);
		if($needPay > $user->userMoney)return WSTReturn(lang('balance_money_less'),-1);
		$userMoney = $user->userMoney;
		$rechargeMoney = $user->rechargeMoney;
		Db::startTrans();
		try{
            //循环处理每个订单
            foreach ($orders as $order) {
            	//处理订单信息
            	$tmpNeedPay = $order->needPay;
            	$lockCashMoney = ($rechargeMoney>$tmpNeedPay)?$tmpNeedPay:$rechargeMoney;
            	$order->needPay = 0;
            	$order->isPay = 1;
            	$order->payTime = date('Y-m-d H:i:s');
            	$order->orderStatus = 0;
            	$order->payFrom = 'wallets';
            	$order->lockCashMoney = $lockCashMoney;
            	if($order->deliverType==1)$order->verificationCode = WSTOrderVerificationCode($order->shopId);
            	$result = $order->save();
                if(false != $result){

                	if($order['deliverType']==1){
						//自提订单（已支付）发送核验码
						$tpl = WSTMsgTemplates('PHONE_USER_ORDER_VERIFICATCODE');
				        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
				        	$userPhone = $order->userPhone;
				        	$areaCode = (int)$order->areaCode;
				        	$storeId = $order->storeId;
				        	$store = Db::name("stores")->where(["storeId"=>$storeId])->field("storeName,storeAddress")->find();
				        	$storeName = $store["storeName"];
					    	$storeAddress = $store["storeAddress"];
					    	$splieVerificationCode = join(" ",str_split($order->verificationCode,4));
				            $params = ['tpl'=>$tpl,'params'=>['MALL_NAME'=>WSTConf('CONF.mallName'),'ORDER_CODE'=>$splieVerificationCode,'SHOP_NAME'=>$storeName,'SHOP_ADDRESS'=>$storeAddress]];
				            model("common/LogSms")->sendSMS(0,$areaCode.$userPhone,$params,'payByWallet','',$userId,0);
				        }
					}

                	$shop = model('shops')->get(['shopId'=>$order->shopId]);
					//新增订单日志
					$logOrder = [];
					$logOrder['orderId'] = $order->orderId;
					$logOrder['orderStatus'] = 0;
					$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'order_success_has_pay']);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);

                    //创建一条支出流水记录
					$lm = [];
					$lm['targetType'] = 0;
					$lm['targetId'] = $userId;
					$lm['dataId'] = $order->orderId;
					$lm['dataSrc'] = 1;
					$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'order_trans_expend_tips','params'=>[$order->orderNo,$tmpNeedPay]]);
					$lm['moneyType'] = 0;
					$lm['money'] = $tmpNeedPay;
					$lm['payType'] = 'wallets';
					model('LogMoneys')->add($lm);
					//修改用户充值金额
					model('users')->where(["userId"=>$userId])->setDec("rechargeMoney",$lockCashMoney);
					//虚拟商品处理
	                if($order->orderType==1){
	                    $this->handleVirtualGoods($order->orderId);
	                }else{
	                    //发送一条商家信息
						$tpl = WSTMsgTemplates('ORDER_HASPAY');
				        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
				            $find = ['${ORDER_NO}'];
				            $replace = [$order->orderNo];
				            //WSTSendMsg($shop->userId,$msgContent,['from'=>1,'dataId'=>$order->orderId]);
				            $msg = array();
				            $msg["shopId"] = $order->shopId;
				            $msg["tplCode"] = $tpl["tplCode"];
				            $msg["msgType"] = 1;
				            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
				            $msg["msgJson"] = ['from'=>1,'dataId'=>$order->orderId];
				            model("common/MessageQueues")->add($msg);
				        }

						//判断是否需要发送商家短信
		                $tpl = WSTMsgTemplates('PHONE_SHOP_PAY_ORDER');
						if((int)WSTConf('CONF.smsOpen')==1 && $tpl['tplContent']!='' && $tpl['status']=='1'){

							$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$order->orderNo]];

							$msg = array();
							$tplCode = "PHONE_SHOP_PAY_ORDER";
							$msg["shopId"] = $order->shopId;
				            $msg["tplCode"] = $tplCode;
				            $msg["msgType"] = 2;
				            $msg["paramJson"] = ['CODE'=>$tplCode,'method'=>'payByWallet','params'=>$params];
				            $msg["msgJson"] = "";
				            model("common/MessageQueues")->add($msg);
						}
						//微信消息
					    if((int)WSTConf('CONF.wxenabled')==1){
						    $params = [];
						    $params['ORDER_NO'] = $order->orderNo;
					        $params['PAY_TIME'] = date('Y-m-d H:i:s');
						    $params['MONEY'] = $order->realTotalMoney;
						    $params['PAY_SRC'] = WSTLangPayFrom($order->payFrom);

					        $msg = array();
							$tplCode = "WX_ORDER_PAY";
							$msg["shopId"] = $order->shopId;
				            $msg["tplCode"] = $tplCode;
				            $msg["msgType"] = 4;
				            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
				            $msg["msgJson"] = "";
				            model("common/MessageQueues")->add($msg);
					    }
	                }

                }
            }
			Db::commit();
			hook("afterOrderPaySuccess",['orderId'=>$orderNo,'isBatch'=>$isBatch,'uerSystem'=>[0,1],'printCatId'=>1]);
			return WSTReturn(lang('pay_success'),1);
		}catch (\Exception $e) {
			Db::rollback();
			return WSTReturn(lang('pay_fail'));
		}
	}

	/**
	 * 获取订单金额以及用户钱包金额
	 */
	public function getOrderPayInfo($obj){
        $userId = (int)$obj["userId"];
		$orderNo = $obj["orderNo"];
		$isBatch = (int)$obj["isBatch"];
		$needPay = 0;
		$where = ["userId"=>$userId,"dataFlag"=>1,"orderStatus"=>-2,"isPay"=>0,"payType"=>1];
		$condition[] = ["needPay",">",0];
		if($isBatch==1){
			$where['orderunique'] = $orderNo;
		}else{
			$where['orderNo'] = $orderNo;
		}
		$orders = model('orders')->where($where)->where($condition)->field('needPay,payRand')->select();
		if(empty($orders))return [];
		$needPay = 0;
		$payRand = 0;
		foreach($orders as $order){
            $needPay += $order['needPay'];
            if($payRand<$order['payRand'])$payRand = $order['payRand'];
		}
		$data = array();
		$data["needPay"] = $needPay;
		$data["payRand"] = $payRand;
		return $data;
	}

	public function getOrderPayFrom($out_trade_no){
		$rs = $this->where(['dataFlag'=>1,'orderNo|orderunique'=>$out_trade_no])->field('orderId,userId,orderNo,orderunique')->find();
		if(!empty($rs)){
			$rs['isBatch'] = ($rs['orderunique'] == $out_trade_no)?1:0;
		}
		return $rs;
	}
	/**
	* 用户-提醒发货
	*/
	public function noticeDeliver($uId=0){
		$orderId = (int)input('id');
		hook("beforeOrderNoticeDeliver",['uId'=>$uId,'orderId'=>$orderId]);
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		Db::startTrans();
		try{
			$rs = $this->where(['userId'=>$userId,'orderId'=>$orderId])->setField('noticeDeliver',1);
			if($rs!==false){
				$info = $this->alias('o')->field('shopId,orderNo')->where(['userId'=>$userId,'orderId'=>$orderId])->find();
				//发送商城消息提醒卖家
				$tpl = WSTMsgTemplates('ORDER_REMINDER');
                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
                    $find = ['${LOGIN_NAME}','${ORDER_NO}'];
                    $replace = [session('WST_USER.loginName'),$info['orderNo']];

                    $msg = array();
		            $msg["shopId"] = $info['shopId'];
		            $msg["tplCode"] = $tpl["tplCode"];
		            $msg["msgType"] = 1;
		            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
		            $msg["msgJson"] = [];
		            model("common/MessageQueues")->add($msg);
                }
			}
			Db::commit();
			return WSTReturn(lang('operation_success'),1);
		}catch(\Exception $e){
			Db::rollback();
		}
		return WSTReturn(lang('operation_fail'),-1);
	}

    /**
     * 获取单条订单的商品信息
     */
    public function waitDeliverById(){
        $orderId = (int)input('id');
        $goods = Db::name('order_goods')->where('orderId','=',$orderId)->select();
        $order = Db::name('orders')->field('deliverType,userAddress,userName,userPhone,areaCode')->where('orderId','=',$orderId)->find();
        $orderExpressGoodsIds = Db::name('order_express')->field('orderGoodsId')->where(['orderId'=>$orderId])->select();
        $deliveredGoodsIds = [];
        foreach($orderExpressGoodsIds as $k => $v){
            $temp = explode(',',$v['orderGoodsId']);
            $deliveredGoodsIds = array_merge($deliveredGoodsIds,$temp);
        }
        $data = [];
        $data['list'] = [];
        $data['userName'] = $order['userName'];
        $data['userPhone'] = "+".$order['areaCode'] ." ".$order['userPhone'];
        $data['userAddress'] = $order['userAddress'];
        $data['deliverType'] = $order['deliverType'];
        if($goods){
            foreach($goods as $k => $v){
                $goods[$k]['hasDeliver'] = (in_array($v['id'],$deliveredGoodsIds))?true:false;
            }
            $data['list'] = $goods;
        }
        return $data;
    }


    /**
	 * 获取待核验订单
	 */
	public function getVerificatOrder($shopId,$storeId=0){
		$verificationCode = input("verificationCode");
		$verificationCode = preg_replace('# #','',$verificationCode);
		$where = [];
		$where[] = ["dataFlag","=",1];
		$where[] = ["deliverType","=",1];
		$where[] = ["shopId","=",$shopId];
		if($storeId>0)$where[] = ["storeId","=",$storeId];
		$where[] = ["orderStatus",">=",0];
		$where[] = ["verificationCode","=",$verificationCode];
		$order = Db::name('orders')->where($where)->find();
		if(empty($order)){
			return $order ;
		}
		$orderId = $order['orderId'];
		$order['orderStatusName'] = WSTLangOrderStatus($order['orderStatus']);
		//获取订单商品
		$order['goods'] = Db::name('order_goods')->alias('og')
							->join('__GOODS__ g','g.goodsId=og.goodsId','left')
							->where('orderId',$orderId)
							->field('og.*,g.goodsSn')
							->order('id asc')
							->select();
		return $order;
	}



    /**
	 * 核验订单
	 */
	public function orderVerificat($shopId,$storeId=0){
		$verificationCode = input("verificationCode");
		$verificationCode = preg_replace('# #','',$verificationCode);
		$where = [];
		$where[] = ["dataFlag","=",1];
		$where[] = ["deliverType","=",1];
		$where[] = ["shopId","=",$shopId];
		if($storeId>0)$where[] = ["storeId","=",$storeId];
		$where[] = ["orderStatus",">=",0];
		$where[] = ["verificationCode","=",$verificationCode];
		$order = Db::name('orders')
		               ->where($where)
		               ->field('shopId,orderId,orderNo,userPhone,userName,isPay,goodsMoney,totalMoney,realTotalMoney,isInvoice,invoiceClient,orderRemarks,scoreMoney,orderStatus,createTime,verificationTime,orderScore,userId')
		               ->find();
		if(empty($order)){
			return WSTReturn(lang('invalid_verification_code'));
		}else{
			if($order["orderStatus"]>0){
				return WSTReturn(lang('order_verification_picked_up'));
			}
			$orderId = $order['orderId'];
			$userId = $order['userId'];
			Db::startTrans();
		    try{
		    	$limitDay = (int)WSTConf('CONF.afterSaleServiceDays');
				// 售后结束时间
				$afterSaleEndTime = date('Y-m-d H:i:s', strtotime("+{$limitDay} day"));
				$data = ['deliveryTime'=>date('Y-m-d H:i:s'),'orderStatus'=>2,'receiveTime'=>date('Y-m-d H:i:s'),'verificationTime'=>date('Y-m-d H:i:s'),'afterSaleEndTime'=>$afterSaleEndTime];

			    $result = $this->where('orderId',$orderId)->update($data);
				if(false != $result){
					//确认收货后执行钩子
					$goodss = Db::name('order_goods')->where('orderId',$orderId)->field('goodsId,goodsNum,goodsSpecId')->select();
					foreach($goodss as $key =>$v){
						Db::name('goods')->where('goodsId',$v['goodsId'])->update([
                            'saleNum'=>Db::raw('saleNum+'.$v['goodsNum'])
                        ]);
						if($v['goodsSpecId']>0){
							Db::name('goods_specs')->where('id',$v['goodsSpecId'])->update([
	                            'saleNum'=>Db::raw('saleNum+'.$v['goodsNum'])
	                        ]);
						}
					}

					//确认收货后执行钩子
					hook('afterUserReceive',['orderId'=>$orderId]);
					//修改商家未计算订单数
					$torder = Db::name('orders')->where("orderId",$orderId)->field("orderId,commissionFee")->find();
					Db::name('shops')->where('shopId',$order['shopId'])->update([
						'noSettledOrderNum'=>Db::raw('noSettledOrderNum+1'),
						'noSettledOrderFee'=>Db::raw('noSettledOrderFee-'.$torder['commissionFee'])
					]);

					//新增订单日志
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = 1;
					$logOrder['logJson'] = ($storeId>0)?json_encode(['type'=>'lang','key'=>'store_verification_deliver']):json_encode(['type'=>'lang','key'=>'shop_verification_deliver']);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);
					//新增订单日志
					$logOrder = [];
					$logOrder['orderId'] = $orderId;
					$logOrder['orderStatus'] = 2;
					$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'user_verification_picked_up']);
					$logOrder['logUserId'] = $userId;
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);

					//发送一条用户信息
					$tpl = WSTMsgTemplates('ORDER_DELIVERY');
			        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
			            $find = ['${ORDER_NO}','${EXPRESS_NO}'];
			            $replace = [$order['orderNo'],lang('nothing')];
			            WSTSendMsg($order['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$orderId]);
			        }
			        $tpl = WSTMsgTemplates('PHONE_USER_ORDER_VERIFICAT');//用户自提订单核销提醒
			        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
			        	$user = Db::name("users")->where(['userId'=>$userId])->field("userPhone,areaCode")->find();
			        	$userPhone = $user['userPhone'];
			        	$areaCode = (int)$user['areaCode'];
			            $params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$order['orderNo']]];
			            model("common/LogSms")->sendSMS(0,$areaCode.$userPhone,$params,'orderVerificat','',$userId,0);
			        }
					//发送一条商家信息
					$tpl = WSTMsgTemplates('ORDER_RECEIVE');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	                    $find = ['${ORDER_NO}'];
	                    $replace = [$order['orderNo']];

	                	$msg = array();
			            $msg["shopId"] = $order["shopId"];
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
			            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
			            model("common/MessageQueues")->add($msg);
	                }

					//给用户增加积分
					if(WSTConf("CONF.isOrderScore")==1 && $order['orderScore']>0){
						$score = [];
						$score['userId'] = $userId;
						$score['score'] = $order['orderScore'];
						$score['dataSrc'] = 1;
						$score['dataId'] = $orderId;
						$score['dataRemarks'] =  json_encode(['type'=>'lang_params','key'=>'order_get_score_tips','params'=>[$order['orderNo'],$order['orderScore']]]);
						$score['scoreType'] = 1;
						model('UserScores')->add($score);
						model('Users')->where("userId",$userId)->setInc('userTotalScore',$order['orderScore']);
					}
					//微信消息
		            if((int)WSTConf('CONF.wxenabled')==1){
		            	$params = [];
		                $params['ORDER_NO'] = $order['orderNo'];
		                $params['ORDER_TIME'] = date('Y-m-d H:i:s');
		            	$msg = array();
						$tplCode = "WX_ORDER_RECEIVE";
						$msg["shopId"] = $order["shopId"];
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 4;
			            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params] ;
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);
		            }
					Db::commit();
					return WSTReturn(lang('verification_success'),1);
				}
		    }catch (\Exception $e) {
	            Db::rollback();
	            return WSTReturn(lang('operation_fail').$e->getMessage(),-1);
	        }

		}

		return WSTReturn(lang('verification_success'),1);
	}


	/**
	 * 导出待发货订单模板
	 */
	public function toDeliverTemplate(){
		$shopId = (int)session('WST_USER.shopId');
		$orderNo = input('orderNo');
		$payType = (int)input('payType',-1);
		$deliverType = (int)input('deliverType',-1);
		$where = [['o.dataFlag','=',1],['o.orderStatus','=',0],['o.shopId','=',$shopId]];
        if($orderNo!='')$where[] = ['o.orderNo','like','%'.$orderNo.'%'];
        if($payType!=-1)$where[] = ['o.payType','=',$payType];
        if($deliverType!=-1)$where[] = ['o.deliverType','=',$deliverType];
		//获取待发货的商品订单信息
		$orderGoods = Db::name('orders')->alias('o')
		                   ->join('__ORDER_GOODS__ og','o.orderId=og.orderId','inner')
		                   ->where($where)
		                   ->field('o.orderNo,og.id,og.goodsName,og.goodsSpecNames,og.goodsSpecId')
		                   ->select();
		$waitDeliverGoods = [];
		if(count($orderGoods)>0){
	        //获已发货的订单
	        $deliverGoods = Db::name('orders')->alias('o')
			                   ->join('__ORDER_EXPRESS__ oep','o.orderId=oep.orderId and oep.dataFlag=1','inner')
			                   ->where($where)
			                   ->column('oep.orderGoodsId');
	        foreach ($orderGoods as $key => $v) {
	        	if($v['goodsSpecId']>0)$v['goodsName'] = $v['goodsName'].'【'.str_replace("@@_@@","|",$v['goodsSpecNames']).'】';
	        	if(count($deliverGoods)>0){
	        		if(!in_array($v['id'],$deliverGoods))$waitDeliverGoods[] = $v;
	        	}else{
	                $waitDeliverGoods[] = $v;
	        	}
	        }
	    }
        //准备快递公司数据
        $express = Db::name('express')
                    ->alias('e')
                    ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                    ->where(['dataFlag'=>1,'isShow'=>1])->column('el.expressName');
		require Env::get('root_path') . 'extend/phpexcel/PHPExcel.php';
		$name = '待发货商品模板';
		$objPHPExcel = new \PHPExcel();
		// 设置excel文档的属性
		$objPHPExcel->getProperties()->setCreator("WSTMart")//创建人
		->setLastModifiedBy("WSTMart")//最后修改人
		->setTitle($name)//标题
		->setSubject($name)//题目
		->setDescription($name)//描述
		->setKeywords($name)//关键字
		->setCategory($name);//种类
		// 开始操作excel表
		$objPHPExcel->setActiveSheetIndex(0);
		// 设置工作薄名称
		$objPHPExcel->getActiveSheet()->setTitle(iconv('gbk', 'utf-8', 'Sheet'));
		// 设置默认字体和大小
		$objPHPExcel->getDefaultStyle()->getFont()->setName(iconv('gbk', 'utf-8', ''));
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
		$styleArray = array(
				'font' => array(
						'bold' => true,
						'color'=>array(
								'argb' => 'ffffffff',
						)
				)
		);

		//设置宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objRow = $objPHPExcel->getActiveSheet()->getStyle('A1:E1');
		$objRow->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
		$objRow->getFill()->getStartColor()->setRGB('666699');
		$objRow->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objRow->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(25);

		$objPHPExcel->getActiveSheet()->setCellValue('A1', lang('order_no'))
		->setCellValue('B1', lang('order_goods_number'))
		->setCellValue('C1', lang('goods_name'))
		->setCellValue('D1', lang('express_company'))
		->setCellValue('E1', lang('express_number'));
		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($styleArray);
        for ($row = 0; $row < count($waitDeliverGoods); $row++){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.($row+2), $waitDeliverGoods[$row]['orderNo'])
			                              ->setCellValue('B'.($row+2), $waitDeliverGoods[$row]['id'])
			                              ->setCellValue('C'.($row+2), $waitDeliverGoods[$row]['goodsName']);
			$objPHPExcel->getActiveSheet()->getStyle('C'.($row+2))->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('C'.($row+2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			/**
			 *     在本地测试设置下拉框通过，在服务器上测试不通过，临时屏蔽
            $objValidation1->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST )
                        ->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION )
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('输入的值有误')
                        ->setError('您输入的值不在下拉框列表内.')
                        ->setPromptTitle('')
                        ->setPrompt('')
                        ->setFormula1('"' . implode(',',$express) . '"');
            */
		}

		$objPHPExcel->getActiveSheet()->getStyle('A1:E'.(count($waitDeliverGoods)+1))->applyFromArray(array(
				'borders' => array (
						'allborders' => array (
								'style' => \PHPExcel_Style_Border::BORDER_THIN,  //设置border样式
								'color' => array ('argb' => 'FF000000'),     //设置border颜色
						)
				)
		));
		$this->PHPExcelWriter($objPHPExcel,$name);
	}

	/*
	 * 获取订单收货信息
	 */
	public function getOrderReceiveInfo(){
        $orderId = (int)input('orderId');
        $rs = $this->where(['orderId'=>$orderId])->field('areaId,areaIdPath,userName,userAddress,userPhone')->find();
        $areaIds = [];
        $areaMaps = [];
        $tmp = explode('_',$rs['areaIdPath']);
        $rs['areaId2'] = $tmp[1];
        foreach ($tmp as $vv){
            if($vv=='')continue;
            if(!in_array($vv,$areaIds))$areaIds[] = $vv;
        }
        if(!empty($areaIds)){
            $areas = Db::name('areas')->where([['areaId','in',$areaIds],['isShow','=',1],['dataFlag','=',1]])->field('areaId,areaName')->select();
            foreach ($areas as $v){
                $areaMaps[$v['areaId']] = $v['areaName'];
            }
            $tmp = explode('_',$rs['areaIdPath']);
            $areaNames = [];
            foreach ($tmp as $vv){
                if($vv=='')continue;
                if(!isset($areaMaps[$vv]))return [];
                $areaNames[] = $areaMaps[$vv];
                $rs['areaName'] = implode('',$areaNames);
            }
        }
        $areaNameLength = mb_strlen($rs['areaName']);
        $rs['userAddress'] = WSTMSubstr($rs['userAddress'],$areaNameLength,mb_strlen($rs['userAddress']));
        return $rs;
    }

    /*
     * 修改订单收货信息
     */
    public function editOrderReceiveInfo(){
        $orderId = (int)input('orderId');
        $address = input('post.');
        WSTUnset($address, 'orderId');
        if($address['userName']==''||$address['userAddress']==''||$address['userPhone']=='')return WSTReturn(lang('input_complete_receive_information'));
        $hasChild = model('Areas')->hasChild(input('areaId'));
        if($hasChild)return WSTReturn(lang('select_complete_area'),-1);
        $areaIds = model('Areas')->getParentIs((int)input('areaId'));
        if(!empty($areaIds))$address['areaIdPath'] = implode('_',$areaIds)."_";
        $areaIds = [];
        $areaMaps = [];
        $tmp = explode('_',$address['areaIdPath']);
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
        WSTUnset($address, 'areaName');
        Db::startTrans();
        try{
            $this->allowField(true)->save($address,['orderId'=>$orderId]);
            Db::commit();
            return WSTReturn(lang('operation_success'),1);
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('operation_fail'));
        }
    }

    /*
     * 获取订单物流信息
     */
    public function getOrderExpressInfo(){
        $orderId = (int)input('orderId');
        $rs = $this->alias('o')
            ->join('__ORDER_EXPRESS__ oe','oe.orderId=o.orderId','left')
            ->join('__EXPRESS__ e','e.expressId=oe.expressId','left')
            ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
            ->where(['o.orderId'=>$orderId,'oe.isExpress'=>1])->field('o.orderId,oe.id,el.expressName,oe.expressId,oe.expressNo')->select();
        return $rs;
    }

    /*
     * 修改订单物流信息
     */
    public function editOrderExpressInfo(){
        $orderId = (int)input('orderId');
        $orderExpressIdArr = explode(',',input('orderExpressId'));
        $expressIdArr = explode(',',input('expressId'));
        $expressNoArr = explode(',',input('expressNo'));
        $order = $this->where(['orderId'=>$orderId,'orderStatus'=>1])->find();
        if(empty($order))return WSTReturn(lang('invalid_order'));
        if(count($expressIdArr)>0){
            for($i=0;$i<count($expressIdArr);$i++){
                if($expressIdArr[$i]=='' || $expressNoArr[$i]=='')return WSTReturn(lang('require_logistics_information'));
            }
        }
        Db::startTrans();
        try{
            for($i=0;$i<count($expressIdArr);$i++){
                $expressData = [
                    'expressId'=>$expressIdArr[$i],
                    'expressNo'=>$expressNoArr[$i],
                ];
                Db::name('order_express')->where(['id'=>$orderExpressIdArr[$i]])->update($expressData);
            }
            Db::commit();
            return WSTReturn(lang('operation_success'),1);
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('operation_fail'));
        }
    }
}
