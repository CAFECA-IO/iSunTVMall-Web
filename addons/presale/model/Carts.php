<?php
namespace addons\presale\model;
use think\addons\BaseModel as Base;
use think\Db;
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
 * 购物车业务处理类
 */

class Carts extends Base{
	/**
     * 下单
     */
	public function addCart($uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($userId==0)return WSTReturn(lang('presale_no_login'));
		$id = (int)input('post.id');
		$cartNum = (int)input('post.buyNum',1);
		$cartNum = ($cartNum>0)?$cartNum:1;
		$goodsSpecId = (int)input('post.goodsSpecId');
		//验证传过来的商品是否合法
		$chk = $this->checkGoodsSaleSpec($id,$goodsSpecId);
		if($chk['status']==-1)return $chk;
		//检测库存是否足够
		if($chk['data']['stock']<$cartNum)return WSTReturn(lang("presale_order_fail_stock_not_enough"), -1);
        $carts = [];
        $carts['presaleId'] = $id;
        $carts['goodsSpecId'] = $goodsSpecId;
        $carts['cartNum'] = $cartNum;
        session('PRESALE_CARTS',$carts);
        return WSTReturn(lang("presale_success"), 1);
	}

	/**
	 * 验证商品是否合法
	 */
	public function checkGoodsSaleSpec($presaleId,$goodsSpecId){

		$goods = DB::name('presales p')->join('__GOODS__ g','p.goodsId=g.goodsId','inner')
		              ->where(['g.goodsStatus'=>1,'p.presaleStatus'=>1,'p.dataFlag'=>1,'g.dataFlag'=>1,'p.id'=>$presaleId,'p.isSale'=>1])
		              ->field('g.goodsId,isSpec,goodsType,p.goodsNum,p.startTime,p.endTime')
		              ->find();
		if(empty($goods))return WSTReturn(lang("presale_add_fail_invalid_goods"), -1);
		//判断预售是否过期
		$time = time();
		if(strtotime($goods['startTime']) > $time)return WSTReturn(lang('presale_no_start'));
		if(strtotime($goods['endTime']) < $time)return WSTReturn(lang('presale_is_end'));
		$goodsId = $goods['goodsId'];
		$goodsStock = (int)$goods['goodsNum'];
		//有规格的话查询规格是否正确
		if($goods['isSpec']==1){
			$specs = Db::name('goods_specs')->where(['goodsId'=>$goodsId,'id'=>$goodsSpecId,'dataFlag'=>1])->field('id,isDefault')->select();
			if(count($specs)==0){
				return WSTReturn(lang("presale_add_fail_invalid_goods"), -1);
			}

			return WSTReturn("", 1,['goodsId'=>$goods['goodsId'],'goodsSpecId'=>$goodsSpecId,'stock'=>$goodsStock,'goodsType'=>$goods['goodsType']]);
		}else{
			return WSTReturn("", 1,['goodsId'=>$goods['goodsId'],'goodsSpecId'=>0,'stock'=>$goodsStock,'goodsType'=>$goods['goodsType']]);
		}
	}

	/**
	 * 获取session中购物车列表
	 */
	public function getCarts(){
		$userId = (int)session('WST_USER.userId');
		$tmp_carts = session('PRESALE_CARTS');

        $presaleId = (int)$tmp_carts['presaleId'];
        $goodsSpecId = (int)$tmp_carts['goodsSpecId'];

		$where = [];
		$where['p.id'] = $presaleId;
		$where['p.isSale'] = 1;
		$where['p.dataFlag'] = 1;
		$where['p.presaleStatus'] = 1;
		$where['g.goodsStatus'] = 1;
		$where['g.dataFlag'] = 1;
		$rs = DB::name('presales p')
                ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
                ->join('__GOODS__ g','p.goodsId=g.goodsId','inner')
                ->join('__SHOPS__ s','s.shopId=p.shopId','left')
                ->join('__GOODS_SPECS__ gs','g.goodsId=gs.goodsId and gs.id='.$goodsSpecId,'left')
                ->where($where)
                ->field('s.userId,s.shopId,s.shopName,g.goodsId,s.shopQQ,shopWangWang,pl.goodsName,p.reduceMoney,g.shopPrice,p.goodsNum goodsStock,p.goodsImg,p.saleType,g.goodsCatId,g.goodsType,gs.specIds,gs.id goodsSpecId,gs.specPrice,p.depositType, p.startTime,p.endTime,g.isFreeShipping,s.isInvoice,p.depositMoney,p.depositRate,p.id,p.saleType,
                    g.isSpec,g.shippingFeeType,g.shopExpressId,g.goodsWeight,g.goodsVolume,gs.specWeight,gs.specVolume')
                ->find();
		if(empty($rs))return ['carts'=>[],'goodsTotalMoney'=>0,'goodsTotalNum'=>0];

		// 确保goodsSpecId不为null.
		$rs['goodsSpecId'] = (int)$rs['goodsSpecId'];
		$rs['cartNum'] = $tmp_carts['cartNum'];
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
		$carts['isFreeShipping'] = ($rs['isFreeShipping']==1)?true:false;
		$carts['presaleId'] = $tmp_carts['presaleId'];
		$carts['shopId'] = $rs['shopId'];
		$carts['shopName'] = $rs['shopName'];
		$carts['shopQQ'] = $rs['shopQQ'];
		$carts['userId'] = $rs['userId'];
		$carts['shopWangWang'] = $rs['shopWangWang'];
		$carts['isInvoice'] = $rs['isInvoice'];
		//判断能否购买，预设allowBuy值为10，为将来的各种情况预留10个情况值，从0到9
		$rs['allowBuy'] = 10;
		if($rs['goodsStock']<0){
			$rs['allowBuy'] = 0;//库存不足
		}else if($rs['goodsStock']<$tmp_carts['cartNum']){
			$rs['allowBuy'] = 1;//库存比购买数小
		}

		$isSpec = $rs['isSpec'];
        $reduceMoney = $rs['reduceMoney'];
        $rs['shopPrice'] = WSTBCMoney($rs['shopPrice'],-$reduceMoney);

		$carts['goodsMoney'] = $carts['goodsMoney'] + $rs['shopPrice'] * $rs['cartNum'];

		if($isSpec==1){
			$rs['specPrice'] = ($rs['specPrice'] - $reduceMoney>0)?($rs['specPrice'] - $reduceMoney):0;
			if($rs['saleType']==1){//定金
	            if($rs['depositType']==1){//百分比
	                $rs['depositMoney'] = WSTBCMoney($rs['specPrice']*$rs['depositRate']/100,0);
	            }else{
	                $rs['depositMoney'] = $rs['depositMoney'];
	            }
	        }else{//全额
	            $rs['depositMoney'] = $rs['specPrice'];
	        }
		}else{
			if($rs['saleType']==1){//定金
                if($rs['depositType']==1){//百分比
                    $rs['depositMoney'] = WSTBCMoney($rs['shopPrice']*$rs['depositRate']/100,0);
                }
            }else{//全额
                $rs['depositMoney'] = $rs['shopPrice'];
            }
		}

		$totalDepositMoney = $rs['depositMoney'] * $rs['cartNum'];
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
		//unset($rs['shopName']);
		$carts['goods'] = $rs;
		$carts['list'] = $list;
		return ['carts'=>$carts,'goodsType'=>$rs['goodsType'],'totalDepositMoney'=>$totalDepositMoney,'goodsTotalMoney'=>$goodsTotalMoney,'goodsTotalNum'=>$goodsTotalNum];
	}

	/**
	 * 计算订单金额
	 */
	public function getCartMoney($uId=0){
		$data = ['shops'=>[],'totalMoney'=>0,'totalGoodsMoney'=>0];
        $areaId = input('post.areaId2/d',-1);
		//计算各店铺运费及金额
		$deliverType = (int)input('deliverType');
		$carts = $this->getCarts();
		$deliverType = ($carts['goodsType']==1)?1:$deliverType;
		$shopFreight = 0;
		//判断是否包邮
		if($carts['carts']['isFreeShipping']){
			$shopFreight = 0;
		}else{
			if($areaId>0){
				$shopFreight = ($deliverType==1)?0:WSTOrderFreight($carts['carts']['shopId'],$areaId,$carts['carts']);
			}else{
				$shopFreight = 0;
			}

		}

		$data['shops']['freight'] = $shopFreight;
		$data['shops']['shopId'] = $carts['carts']['shopId'];
		$data['shops']['goodsMoney'] = $carts['carts']['goodsMoney'];
		$data['totalGoodsMoney'] = $carts['carts']['goodsMoney'];
		$data['totalMoney'] += $carts['carts']['goodsMoney'] + $shopFreight;
		$data['useScore'] = 0;
		$data['scoreMoney'] = 0;

		$saleType = (int)$carts['carts']['goods']['saleType'];
		//判断是否使用积分
		$isUseScore = (int)input('isUseScore');
		$useScore = (int)input('useScore');
		$orderUsableScore = WSTOrderUsableScore($data['totalGoodsMoney'],$uId);
		$data['maxScore'] = $orderUsableScore['score'];
		$data['maxScoreMoney'] = $orderUsableScore['money'];
        if($isUseScore==1){
			//不能比用户积分还多
			if($useScore>$data['maxScore'])$useScore = $data['maxScore'];
			$data['useScore'] = $useScore;
            $data['scoreMoney'] = WSTScoreToMoney($useScore);
		}
		$data['realTotalMoney'] = $data['totalMoney'] - $data['scoreMoney'];
		if($saleType==1){
			$data['totalDepositMoney'] = WSTBCMoney($carts['totalDepositMoney'],0);
			$data['totalSurplusMoney'] = WSTBCMoney($data['totalMoney'] - $data['scoreMoney'] - $carts['totalDepositMoney'],0);
		}else{
			$data['totalDepositMoney'] = $data['totalMoney'];
			$data['totalSurplusMoney'] = 0;
		}


		return WSTReturn('',1,$data);
	}

	public function getPayments(){
		//获取支付信息
		$payments = Db::name('payments')->where(['isOnline'=>1,'enabled'=>1])->order('payOrder asc')->select();
		return $payments;
	}
}
