<?php
namespace addons\seckill\model;
use think\addons\BaseModel as Base;
use wstmart\common\model\GoodsCats;
use think\Db;
use wstmart\common\model\LogSms;
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
 * 秒杀订单
 */
class Orders extends Base{

	/**
	 * 取消秒杀订单
	 */
	public function cancelOrder($params){
		$orderId = (int)$params['orderId'];
		$order = Db::name('orders')->where('orderId',$orderId)->field('orderCode,extraJson,orderCodeTargetId')->find();
        if($order['orderCode']=='seckill'){
            $goods = Db::name('order_goods')->alias('og')
                       ->join('__GOODS__ g','og.goodsId=g.goodsId','inner')
					   ->where('orderId',$orderId)->field('og.*')
					   ->find();

			//修改秒杀库存
			Db::name("seckill_goods")->where('id',$order['orderCodeTargetId'])->update(['hasBuyNum'=>Db::raw('hasBuyNum-'.$goods['goodsNum'])]);
        }
	}



	/**
     * 下单
     */
	public function addCart($userId){
		$id = (int)input('post.id');
		$cartNum = (int)input('post.buyNum',1);
		$cartNum = ($cartNum>0)?$cartNum:1;
		//验证传过来的商品是否合法
		$chk = $this->checkGoodsSaleSpec($id,$userId);
		if($chk['status']==-1)return $chk;
		//检测库存是否足够
		if($chk['data']['stock']<$cartNum)return WSTReturn(lang('seckill_fail_stock_less_tips'), -1);
        $carts = [];
        $carts['seckillId'] = $id;
        $carts['cartNum'] = $cartNum;
        session('SECKILL_CARTS',$carts);
        return WSTReturn("秒杀成功", 1);
	}
	/**
	 * 验证商品是否合法
	 */
	public function checkGoodsSaleSpec($id,$userId){
		$where = [];
		$where[] = ['sg.dataFlag','=',1];
		$where[] = ['sg.id','=',$id];
		$where[] = ['s.dataFlag','=',1];
		$where[] = ['s.seckillStatus','=',1];
		$where[] = ['s.isSale','=',1];
		$sg = Db::name("seckill_goods sg")->join("seckills s","s.id=sg.seckillId","inner")
                    ->join('__SECKILLS_LANGS__ sl','sl.seckillId=s.id and sl.langId='.WSTCurrLang())
					->where($where)
					->field("sg.*,s.startDate,s.endDate,s.seckillStatus,sl.seckillDes")
					->find();
		if(empty($sg)) return WSTReturn(lang('seckill_add_fail_invalid_goods_tips'), -1);
		$goodsId = $sg['goodsId'];
		$rs = Db::name('goods')->where(['goodsId'=>$goodsId,'dataFlag'=>1])->find();
		if(empty($rs))return WSTReturn(lang('seckill_add_fail_invalid_goods_tips'), -1);

		$today = date("Y-m-d");
		$nowTime = date("H:i:s");
		$timeId = $sg["timeId"];
		$timeItv = Db::name("seckill_time_intervals")->where(['id'=>$timeId])->find();
		if($timeItv['startTime']>=$nowTime){
			return WSTReturn(lang('seckill_not_start_tips'), -1);
    	}else if($timeItv['endTime']<$nowTime){
    	    return WSTReturn(lang('seckill_over_time_tips'), -1);
    	}
		$rs['canBuyNum']=$sg['secLimit'];
        if($userId>0){
            $myOrder=Db::name('orders')->where(['orderCode'=>'seckill','orderCodeTargetId'=>$id,'dataFlag'=>1,'userId'=>$userId])->whereNotIn('orderStatus',-1)->column("orderId");//获取个人参与此秒杀的所有订单
            $myOrderNum=Db::name('order_goods')->where("orderId","in",$myOrder)->sum("goodsNum");//获取个人参与此秒杀的总数

            if($myOrderNum>=$sg['secLimit']){
            	$gunit = WSTDatas('GOODS_UNIT',$goods['goodsUnit']);
            	$unit = ($gunit && isset($gunit['dataName']))?$gunit['dataName']:'';
                return WSTReturn(lang('seckill_purchase_limit_reached',[$sg['secLimit'].$unit]), -1);
            }else{
            	$rs['canBuyNum'] = $sg['secLimit']-$myOrderNum;
            }
        }

		$goodsStock = (int)$sg['secNum']-(int)$sg['hasBuyNum'];
		//有规格的话查询规格是否正确
		if($rs['isSpec']==1){
			$specs = Db::name('goods_specs')->where(['goodsId'=>$goodsId,'dataFlag'=>1])->field('id,isDefault')->select();
			if(count($specs)==0){
				return WSTReturn(lang('seckill_add_fail_invalid_goods_tips'), -1);
			}
			$goodsSpecId = 0;
			foreach ($specs as $key => $v){
				if($v['isDefault']==1){
					$goodsSpecId = $v['id'];
					break;
				}
			}
			if($goodsSpecId==0)return WSTReturn(lang('seckill_add_fail_invalid_goods_tips'), -1);//有规格却找不到规格的话就报错
			return WSTReturn("", 1,['goodsId'=>$rs['goodsId'],'goodsSpecId'=>$goodsSpecId,'stock'=>$goodsStock,'canBuyNum'=>$rs['canBuyNum'],'goodsType'=>$rs['goodsType']]);
		}else{
			return WSTReturn("", 1,['goodsId'=>$rs['goodsId'],'goodsSpecId'=>0,'stock'=>$goodsStock,'canBuyNum'=>$rs['canBuyNum'],'goodsType'=>$rs['goodsType']]);
		}
	}

	/**
	 * 获取session中购物车列表
	 */
	public function getCarts($userId){
		$tmp_carts = session('SECKILL_CARTS');
		$seckillId = (int)$tmp_carts['seckillId'];
		$where = [];
		$where['sg.id'] = $seckillId;
		$where['sg.dataFlag'] = 1;
		$where['k.seckillStatus'] = 1;
		$where['k.isSale'] = 1;
		$where['g.goodsStatus'] = 1;
		$where['g.dataFlag'] = 1;
		$rs = Db::name("seckill_goods sg")
                   ->join('seckills k','sg.seckillId=k.id','inner')
                   ->join('__GOODS__ g','sg.goodsId=g.goodsId','inner')
                   ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
		           ->join('__SHOPS__ s','s.shopId=sg.shopId','left')
		           ->join('__GOODS_SPECS__ gs','g.goodsId=gs.goodsId and gs.isDefault','left')
		           ->where($where)
		           ->field('sg.id,s.userId,s.shopId,s.shopName,s.shopImg,s.shopAddress,g.goodsId,s.shopTel,s.isInvoice,s.shopQQ,shopWangWang,gl.goodsName,sg.timeId,sg.secPrice shopPrice,sg.secNum,sg.hasBuyNum,g.goodsImg,g.goodsCatId,g.goodsType,gs.specIds,gs.id goodsSpecId,k.startDate,k.endDate,g.isFreeShipping,
						g.isSpec,g.shippingFeeType,g.shopExpressId,g.goodsWeight,g.goodsVolume,gs.specWeight,gs.specVolume')
		           ->find();
		if(empty($rs))return ['carts'=>[],'goodsTotalMoney'=>0,'goodsTotalNum'=>0];
		$rs['goodsStock'] = ($rs['secNum']-$rs['hasBuyNum']>0)? $rs['secNum']-$rs['hasBuyNum']:0;

		$timeId = $rs["timeId"];
		$timeItv = Db::name("seckill_time_intervals")->where(['id'=>$timeId])->find();
		$today = date("Y-m-d");
		$nowTime = date("H:i:s");
		if($timeItv['startTime']<$nowTime && $timeItv['endTime']>=$nowTime){
    		$rs['status'] = 1;
    	}else if($timeItv['startTime']>=$nowTime){
            $rs['status'] = 0;
    	}else{
    	    $rs['status'] = 2;
    	}
		$rs["startTime"] = $today." ".$timeItv["startTime"];
		$rs["endTime"] = $today." ".$timeItv["endTime"];

		// 确保goodsSpecId不为null.
		$rs['goodsSpecId'] = (int)$rs['goodsSpecId'];
		$rs['cartNum'] = (int)$tmp_carts['cartNum'];
		$carts = [];

		$list = [];
		$item = [];
		$item["shippingFeeType"] = $rs["shippingFeeType"];
		$item["shopExpressId"] = $rs["shopExpressId"];
		$item["goodsWeight"] = $rs["goodsWeight"];
		$item["goodsVolume"] = $rs["goodsVolume"];
		if($rs["isSpec"]==1){
			$item["goodsWeight"] = $rs["specWeight"];
			$item["goodsVolume"] = $rs["specVolume"];
		}
		$item["cartNum"] = $rs["cartNum"];
		$list[] = $item;

		$goodsTotalNum = 0;
		$goodsTotalMoney = 0;
		if(!isset($carts['goodsMoney']))$carts['goodsMoney'] = 0;
		$carts['isFreeShipping'] = ($rs['isFreeShipping']==1)?1:0;
		$carts['seckillId'] = $tmp_carts['seckillId'];
		$carts['shopId'] = $rs['shopId'];
		$carts['shopName'] = $rs['shopName'];
		$carts['shopImg'] = WSTImg($rs['shopImg']);
		$carts['shopAddress'] = $rs['shopAddress'];
		$carts['isInvoice'] = $rs['isInvoice'];
		$carts['shopTel'] = $rs['shopTel'];
		$carts['shopQQ'] = $rs['shopQQ'];
		$carts['userId'] = $rs['userId'];
		$carts['shopWangWang'] = $rs['shopWangWang'];
		//判断能否购买，预设allowBuy值为10，为将来的各种情况预留10个情况值，从0到9
		$rs['allowBuy'] = 10;
		if($rs['goodsStock']<=0){
			$rs['allowBuy'] = 0;//库存不足
		}else if($rs['goodsStock']<(int)$tmp_carts['cartNum']){
			$rs['allowBuy'] = 1;//库存比购买数小
		}
		$carts['goodsMoney'] = $carts['goodsMoney'] + $rs['shopPrice'] * $rs['cartNum'];
		$goodsTotalMoney = $goodsTotalMoney + $rs['shopPrice'] * $rs['cartNum'];
		$goodsTotalNum = $rs['cartNum'];
		if($rs['specIds']!=''){
			//加载规格值
			$specs = DB::name('spec_items')->alias('s')->join('__SPEC_CATS__ sc','s.catId=sc.catId','left')
                    ->join('__SPEC_CATS_LANGS__ scl','sc.catId=scl.catId and scl.langId='.WSTCurrLang())
                    ->join('__SPEC_ITEMS_LANGS__ sil','s.itemId=sil.itemId and sil.langId='.WSTCurrLang())
                    ->where(['s.goodsId'=>$rs['goodsId'],'s.dataFlag'=>1])
                    ->field('scl.catName,s.itemId,sil.itemName')
                    ->select();
		    if(count($specs)>0){
			    $specMap = [];
			    foreach ($specs as $key =>$v){
			    	$specMap[$v['itemId']] = $v;
			    }
				$strName = [];
				if($rs['specIds']!=''){
				    $str = explode(':',$rs['specIds']);
				    foreach ($str as $vv){
				    	if(isset($specMap[$vv]))$strName[] = $specMap[$vv];
				    }
				}
				$rs['specNames'] = $strName;
			}
		}
		unset($rs['shopName']);
		$carts['goods'] = $rs;
		$carts['list'] = $list;
		return ['carts'=>$carts,'seckillId'=>$seckillId,'goodsType'=>$rs['goodsType'],'goodsTotalMoney'=>$goodsTotalMoney,'goodsTotalNum'=>$goodsTotalNum];
	}

	/**
	 * 计算订单金额
	 */
	public function getCartMoney($userId){
		$data = ['shops'=>[],'totalMoney'=>0,'totalGoodsMoney'=>0];
        $areaId = input('post.areaId2/d',-1);
		//计算各店铺运费及金额
		$deliverType = (int)input('deliverType');
		$carts = $this->getCarts($userId);
		$deliverType = ($carts['goodsType']==1)?1:$deliverType;
		$shopFreight = 0;
		//判断是否包邮
		if($carts['carts']['isFreeShipping']){
			$shopFreight = 0;
		}else{
			$shopFreight = ($deliverType==1)?0:WSTOrderFreight($carts['carts']['shopId'],$areaId,$carts['carts']);
		}
		$data['shops']['freight'] = $shopFreight;
		$data['shops']['shopId'] = $carts['carts']['shopId'];
		$data['shops']['goodsMoney'] = $carts['carts']['goodsMoney'];
		$data['totalGoodsMoney'] = $carts['carts']['goodsMoney'];
		$data['totalMoney'] += $carts['carts']['goodsMoney'] + ($shopFreight>=0?$shopFreight:0);
		$data['maxScore'] = 0;
		$data['maxScoreMoney'] = 0;
		$data['useScore'] = 0;
		$data['scoreMoney'] = 0;
		//判断是否使用积分
		$isUseScore = (int)input('isUseScore');
		$useScore = (int)input('useScore');
		$orderUsableScore = WSTOrderUsableScore($data['totalGoodsMoney'],$userId);
		$data['maxScore'] = $orderUsableScore['score'];
		$data['maxScoreMoney'] = $orderUsableScore['money'];
        if($isUseScore==1){
			//不能比用户积分还多
			if($useScore>$data['maxScore'])$useScore = $data['maxScore'];
			$data['useScore'] = $useScore;
            $data['scoreMoney'] = WSTScoreToMoney($useScore);
		}
		$data['realTotalMoney'] = WSTPositiveNum($data['totalMoney'] - $data['scoreMoney']);
		return WSTReturn('',1,$data);
	}

	/**
	 * 下单
	 */
	public function submit($userId){
		//检测购物车
		$carts = $this->getCarts($userId);
		if(empty($carts['carts']))return WSTReturn(lang('seckill_select_want_buy_goods'));
		$id = $carts['seckillId'];
		$cartNum = $carts['goodsTotalNum'];
		//验证传过来的商品是否合法
		$chk = $this->checkGoodsSaleSpec($id,$userId);
		if($chk['status']==-1)return $chk;
		//检测库存是否足够
		if($chk['data']['stock']<$cartNum)return WSTReturn(lang('seckill_stock_less_number_tips',[$chk['data']['stock']]), -1);
      	return $this->submitByEntity($carts,$userId);
	}

	/**
	 * 实物商品下单
	 */
	public function submitByEntity($carts,$userId){
		$orderSrc =  (int)input('post.orderSrc');
		$addressId = (int)input('post.addressId');
		$areaId = (int)input('post.areaId');
		$deliverType = ((int)input('post.deliverType')>0)?1:0;
		$isInvoice = ((int)input('post.isInvoice')>0)?1:0;
		$invoiceClient = ($isInvoice==1)?input('post.invoiceClient'):'';
		$payType = ((int)input('post.payType')>0)?1:0;

		$isUseScore = (int)input('isUseScore');
		$useScore = (int)input('useScore');
        //再次检查秒杀是否已满
        $goods = $carts['carts']['goods'];
        $seckillId = $carts['seckillId'];

		//生成订单
		Db::startTrans();
		try{
			$tmpSeckill = Db::name("seckill_goods")->where(['id'=>$seckillId])->lock(true)->field('id,secLimit')->find();
	        $myOrder=Db::name('orders')->where(['orderCode'=>'seckill','orderCodeTargetId'=>$seckillId,'dataFlag'=>1,'userId'=>$userId])->whereNotIn('orderStatus',-1)->column("orderId");//获取个人参与此秒杀的所有订单
	        $myOrderNum=Db::name('order_goods')->where("orderId","in",$myOrder)->sum("goodsNum");//获取个人参与此秒杀的总数
	        $nums=$myOrderNum+$goods['cartNum'];
			if($tmpSeckill['secLimit']<$nums)return WSTReturn(lang('seckill_order_fail_limit_num_tips'));


			//检测地址是否有效
			$address = Db::name('user_address')->where(['userId'=>$userId,'addressId'=>$addressId,'dataFlag'=>1])->find();
			if(empty($address)){
				return WSTReturn(lang('seckill_invalid_user_address'));
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
		         $areas = Db::name('areas')->where([['dataFlag','=',1],['areaId','in',$areaIds]])->field('areaId,areaName')->select();
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


			//计算最大可用积分
		    $orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney'],$userId,($isUseScore==1)?1:0,$useScore);
			$carts = $carts['carts'];
			$orderNo = WSTOrderNo();
			$orderScore = 0;
			//创建订单
			$order = [];
			$order = array_merge($order,$address);
			$order['orderNo'] = $orderNo;
			$order['userId'] = $userId;
			$order['shopId'] = $carts['shopId'];
			$order['payType'] = $payType;
			$order['goodsMoney'] = $carts['goodsMoney'];
			//计算运费和总金额
			$order['deliverType'] = $deliverType;
			if($carts['isFreeShipping']){
                $order['deliverMoney'] = 0;
			}else{
				$order['deliverMoney'] = 0;
				if($deliverType==0){
					$freight = WSTOrderFreight($carts['shopId'],$order['areaId2'],$carts);
					if($freight<0){
						return WSTReturn(lang('seckill_delivery_out_range_tips'), -1);
					}else{
						$order['deliverMoney'] = $freight;
					}
				}
			}
			if($deliverType==1){//自提
				$order['storeId'] = (int)input("storeId");
				$order['storeType'] = 1;
			}
			$order['totalMoney'] = $order['goodsMoney']+$order['deliverMoney'];
            //积分支付-计算分配积分和金额
            $order['scoreMoney'] = 0;
			$order['useScore'] = 0;
			if($orderUsableScore['money']>0){
				$order['scoreMoney'] = $orderUsableScore['money'];
				$order['useScore'] = $orderUsableScore['score'];
			}
			//实付金额要减去积分兑换的金额
			$order['realTotalMoney'] = $order['totalMoney'] - $order['scoreMoney'];
			$order['needPay'] = $order['realTotalMoney'];
			$order['orderCode'] = 'seckill';
			$order['orderCodeTargetId'] = $carts['seckillId'];
			$order['extraJson'] = json_encode(['seckillId'=>$carts['seckillId']]);
            if($payType==1){
                if($order['needPay']>0){
                    $order['orderStatus'] = -2;//待付款
				    $order['isPay'] = 0;
                }else{
                    $order['orderStatus'] = 0;//待发货
				    $order['isPay'] = 1;
				    $order['payTime'] = date('Y-m-d H:i:s');
				    if($deliverType==1)$order['verificationCode'] = WSTOrderVerificationCode($carts['shopId']);
                }
			}else{
				$order['orderStatus'] = 0;//待发货
				if($order['needPay']==0){
					$order['isPay'] = 1;
					$order['payTime'] = date('Y-m-d H:i:s');
				}
				if($deliverType==1)$order['verificationCode'] = WSTOrderVerificationCode($carts['shopId']);
			}
			//积分
			$orderScore = 0;
			//如果开启下单获取积分则有积分
			if(WSTConf('CONF.isOrderScore')==1){
				$orderScore = WSTMoneyGiftScore($order['goodsMoney']);
			}
			$orderunique = WSTOrderQnique();
			$order['orderScore'] = $orderScore;

			$shop = model("common/shops")->getFieldsById($carts['shopId'],"isInvoice");
			$shopIsInvoice = $shop['isInvoice'];
			if($shopIsInvoice==1 && $isInvoice==1){
				$order['isInvoice'] = $isInvoice;
				$order['invoiceJson'] = model('common/invoices')->getInviceInfo((int)input('param.invoiceId'));// 发票信息
				$order['invoiceClient'] = $invoiceClient;
			}else{
				$order['isInvoice'] = 0;
				$order['invoiceJson'] = "";// 发票信息
				$order['invoiceClient'] = "";
			}

			$order['orderRemarks'] = input('post.remark');
			$order['orderSrc'] = $orderSrc;
			$order['orderunique'] = $orderunique;
			$order['dataFlag'] = 1;
			$order['payRand'] = 1;
			$order['createTime'] = date('Y-m-d H:i:s');
			$m = model('common/orders');

			$result = $m->data($order,true)->isUpdate(false)->allowField(true)->save($order);
			$orderId = 0;
			if(false !== $result){
				$orderId = $m->orderId;

				$goods = $carts['goods'];
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
				//计算订单佣金
				$commissionFee = 0;
				if((float)$orderGoods['commissionRate']>0){
					$orderGoodscommission = round($orderGoods['goodsPrice']*$orderGoods['goodsNum']*$orderGoods['commissionRate']/100,2);
					$orderGoods["orderGoodscommission"] = $orderGoodscommission;
             		$commissionFee += $orderGoodscommission;
				}
				// 记录商品获得的积分及 获得积分可抵扣的金额
				$orderGoods['getScoreVal'] = WSTMoneyGiftScore($orderGoods['goodsPrice']*$orderGoods['goodsNum']);
				// 获得的积分可抵扣金额
				$orderGoods['getScoreMoney'] = WSTScoreToMoney($orderGoods['getScoreVal']);

				Db::name('order_goods')->insert($orderGoods);

				model('common/orders')->where('orderId',$orderId)->update(['commissionFee'=>$commissionFee]);

				//修改秒杀数量
				Db::name("seckill_goods")->where('id',$carts['seckillId'])->update(['hasBuyNum'=>Db::raw("hasBuyNum+".$goods['cartNum'])]);
				//创建积分流水--如果有抵扣积分就肯定是开启了支付支付
				if($order['useScore']>0){
					$score = [];
				    $score['userId'] = $userId;
					$score['score'] = $order['useScore'];
					$score['dataSrc'] = 1;
					$score['dataId'] = $orderId;
					$score['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'seckill_order_use_score_tips','params'=>[$orderNo,$order['useScore']]]);
					$score['scoreType'] = 0;
					model('common/UserScores')->add($score);
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
				//给店铺增加提示消息
				$tpl = WSTMsgTemplates('ORDER_SUBMIT');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${ORDER_NO}'];
		            $replace = [$orderNo];

		        	$msg = array();
		            $msg["shopId"] = $carts['shopId'];
		            $msg["tplCode"] = $tpl["tplCode"];
		            $msg["msgType"] = 1;
		            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
		            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
		            model("common/MessageQueues")->add($msg);
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
				            model("common/LogSms")->sendSMS(0,$areaCode.$userPhone,$params,'submitByEntity','',$userId,0);
				        }
					}
			    }
				//判断是否需要发送商家短信
	            $tpl = WSTMsgTemplates('PHONE_SHOP_SUBMIT_ORDER');
				if((int)WSTConf('CONF.smsOpen')==1 && $tpl['tplContent']!='' && $tpl['status']=='1'){

					$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$orderNo]];

					$msg = array();
					$tplCode = "PHONE_SHOP_SUBMIT_ORDER";
					$msg["shopId"] = $carts['shopId'];
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
	                $goodsNames = $goods['goodsName']."*".$goods['cartNum'];
		            $params['GOODS'] = $goodsNames;
		            $params['MONEY'] = $order['realTotalMoney'] + $order['scoreMoney'];
		            $params['ADDRESS'] = $order['userAddress']." ".$order['userName'];
		            $params['PAY_TYPE'] = WSTLangPayType($order['payType']);

			        $msg = array();
					$tplCode = "WX_ORDER_SUBMIT";
					$msg["shopId"] = $carts['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);

			    }
			}
			Db::commit();
			//删除session的购物车商品
			session('SECKILL_CARTS',null);
			return WSTReturn(lang('seckill_submit_success'), 1,$orderNo);
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('seckill_submit_fail'),-1);
        }
	}


   	/*
	*支付超时取消订单
	*/
	public function payOverTime(){
		$cfg = $this->getConf("Seckill");
		$seckillPaytime = (int)$cfg['seckillPaytime'];
	 	$seckillPaytime = ($seckillPaytime>0)?$seckillPaytime:15;
	 	$lastDay = date("Y-m-d H:i:s",strtotime("-".$seckillPaytime." minute"));
	 	$orders = Db::name('orders o')
				 	->join('__SHOPS__ s','o.shopId=s.shopId','left')
				 	->where([
				 		['o.orderCode','=','seckill'],
				 		['o.createTime','<',$lastDay],
				 		['o.orderStatus','=',-2],
				 		['o.dataFlag','=',1],
				 		['o.payType','=',1],
				 		['o.isPay','=',0]
				 	])
				 	->field("o.orderId,o.orderNo,o.userId,o.shopId,o.useScore,s.userId shopUserId,orderCode,extraJson,orderCodeTargetId")
				 	->select();
	 	if(!empty($orders)){
	 		$orderIds = [];
	 		foreach ($orders as $okey => $order){
	 			$orderIds[] = $order['orderId'];
	 		}
	 		Db::startTrans();
		    try{
		    	//提前锁定订单
		    	Db::name('orders')->where([['orderId','in',$orderIds]])->update(['orderStatus'=>-1]);
                foreach ($orders as $okey => $order){
                	$shopId = $order['shopId'];
                	//未付款状态则直接退回积分
                    if($order['useScore']>0){
                    	$score = [];
						$score['userId'] = $order['userId'];
						$score['score'] = $order['useScore'];
						$score['dataSrc'] = 1;
						$score['dataId'] = $order['orderId'];
						$score['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'seckill_cancel_order_tips','params'=>[$order['orderNo'],$order['useScore']]]);
						$score['scoreType'] = 1;
						model('common/UserScores')->add($score);
                    }


		            $goods = Db::name('order_goods og')->where('orderId',$order['orderId'])->find();
		            $sec = Db::name('seckill_goods')->where(['id'=>$order['orderCodeTargetId']])->field("hasBuyNum")->find();
					//修改秒杀库存
					if($sec['hasBuyNum']<$goods['goodsNum']){
						Db::name('seckill_goods')->where(['id'=>$order['orderCodeTargetId']])->update(['hasBuyNum'=>0]);
					}else{
						Db::name('seckill_goods')->where(['id'=>$order['orderCodeTargetId']])->setDec('hasBuyNum',$goods['goodsNum']);
					}

					//新增订单日志
					$logOrder = [];
					$logOrder['orderId'] = $order['orderId'];
					$logOrder['orderStatus'] = -1;
					$logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'cron_jobs_tips1']);
					$logOrder['logUserId'] = $order['userId'];
					$logOrder['logType'] = 0;
					$logOrder['logTime'] = date('Y-m-d H:i:s');
					Db::name('log_orders')->insert($logOrder);
                    //发送消息
	                $tpl = WSTMsgTemplates('ORDER_USER_PAY_TIMEOUT');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	                    $find = ['${ORDER_NO}'];
	                    $replace = [$order['orderNo']];
	                    //发送一条用户信息
					    WSTSendMsg($order['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$order['orderId']]);
	                }
                    $tpl = WSTMsgTemplates('ORDER_SHOP_PAY_TIMEOUT');
	                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
	                    $find = ['${ORDER_NO}'];
	                    $replace = [$order['orderNo']];
	                    //发送一条商家信息

	                	$msg = array();
			            $msg["shopId"] = $shopId;
			            $msg["tplCode"] = $tpl["tplCode"];
			            $msg["msgType"] = 1;
			            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']) ;
			            $msg["msgJson"] = ['from'=>1,'dataId'=>$order['orderId']];
			            model("common/MessageQueues")->add($msg);
	                }
	                //微信消息
		            if((int)WSTConf('CONF.wxenabled')==1){
		            	$params = [];
		                $params['ORDER_NO'] = $order['orderNo'];
	                    WSTWxMessage(['CODE'=>'WX_ORDER_USER_PAY_TIMEOUT','userId'=>$order['userId'],'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params]);

		            	$msg = array();
		            	$tplCode = "WX_ORDER_SHOP_PAY_TIMEOUT";
						$msg["shopId"] = $shopId;
			            $msg["tplCode"] = $tplCode;
			            $msg["msgType"] = 4;
			            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/index',['type'=>'abnormal'],true,true),'params'=>$params] ;
			            $msg["msgJson"] = "";
			            model("common/MessageQueues")->add($msg);
		            }
                }

		        Db::commit();
				return WSTReturn(lang('seckill_operation_success'),1);
	 		}catch (\Exception $e) {
	            Db::rollback();
	            return WSTReturn(lang('seckill_operation_fail'),-1);
	        }
	 	}
	 	return WSTReturn(lang('seckill_operation_success'),1);
	}

	public function checkSupportStores(){
      $id = (int)session('SECKILL_CARTS.seckillId');
      $rs = Db::name("seckill_goods")->where(['id'=>$id])->field("shopId")->find();
      $shopId = (int)$rs["shopId"];
      $where = [];
      $where[] = ["shopId","=",$shopId];
      $where[] = ["dataFlag","=",1];
      $where[] = ["storeStatus","=",1];
      $cnt = Db::name("stores")->where($where)->count();
      $rs = ($cnt>0)?1:0;
      return $rs;
    }
}

