<?php
namespace wstmart\supplier\model;
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

class SupplierCarts extends Base{
	protected $pk = 'cartId';

	/**
	 * 验证商品是否合法
	 */
	public function checkGoodsSaleSpec($goodsId,$goodsSpecId){
		$goods = model('Goods')->where(['goodsStatus'=>1,'dataFlag'=>1,'isSale'=>1,'goodsId'=>$goodsId])->field('goodsId,isSpec,goodsStock,goodsType')->find();
		if(empty($goods))return WSTReturn(lang("add_cart_fail_invalid_good"), -1);
		$goodsStock = (int)$goods['goodsStock'];
		//有规格的话查询规格是否正确
		if($goods['isSpec']==1){
			$specs = Db::name('supplier_goods_specs')->where(['goodsId'=>$goodsId,'dataFlag'=>1])->field('id,isDefault,specStock')->select();
			if(count($specs)==0){
				return WSTReturn(lang("add_cart_fail_invalid_good"), -1);
			}
			$defaultGoodsSpecId = 0;
			$defaultGoodsSpecStock = 0;
			$isFindSpecId = false;
			foreach ($specs as $key => $v){
				if($v['isDefault']==1){
					$defaultGoodsSpecId = $v['id'];
					$defaultGoodsSpecStock = (int)$v['specStock'];
				}
				if($v['id']==$goodsSpecId){
					$goodsStock = (int)$v['specStock'];
					$isFindSpecId = true;
				}
			}

			if($defaultGoodsSpecId==0)return WSTReturn(lang("add_cart_fail_invalid_good"), -1);//有规格却找不到规格的话就报错
			if(!$isFindSpecId)return WSTReturn("", 1,['goodsSpecId'=>$defaultGoodsSpecId,'stock'=>$defaultGoodsSpecStock,'goodsType'=>$goods['goodsType']]);//如果没有找到的话就取默认的规格
			return WSTReturn("", 1,['goodsSpecId'=>$goodsSpecId,'stock'=>$goodsStock,'goodsType'=>$goods['goodsType']]);
		}else{
			return WSTReturn("", 1,['goodsSpecId'=>0,'stock'=>$goodsStock,'goodsType'=>$goods['goodsType']]);
		}
	}

	/**
	 * 删除购物车商品
	 */
	public function delCartByUpdate($goodsId){
		if(is_array($goodsId)){
            $this->where([['goodsId','in',$goodsId]])->delete();
		}else{
			$this->where('goodsId',$goodsId)->delete();
		}

	}


	/**
	 * 计算运费价格
	 */
    public function getSupplierFreight($supplierId,$cityId,$carts=[]){

        $calculatePrice = 0;
        if(isset($carts['list'])){
        	foreach ($carts['list'] as $key => $goods) {
	        	$supplierExpressId = (int)$goods["supplierExpressId"];
		        $shippingFeeType = (int)$goods["shippingFeeType"];
		        $where = [];
		        $where[] = ["supplierId",'=',$supplierId];
		        $where[] = ["supplierExpressId",'=',$supplierExpressId];
		        $where[] = ["tempType",'=',1];
		        $where[] = ["dataFlag",'=',1];
		        $freightTemp = Db::name("supplier_freight_template")->where($where)->where("FIND_IN_SET(".$cityId.",cityIds)")->find();
		       	if(empty($freightTemp)){
		       		$where = [];
			        $where[] = ["supplierId",'=',$supplierId];
			        $where[] = ["supplierExpressId",'=',$supplierExpressId];
		       		$where[] = ["tempType",'=',0];
		       		$where[] = ["dataFlag",'=',1];
		       		$freightTemp = Db::name("supplier_freight_template")->where($where)->find();
		       	}
		       	$cartNum = (int)$goods['cartNum'];
		       	if($shippingFeeType==1){//计件
		       		$buyNumStart = (int)$freightTemp["buyNumStart"];
			       	$buyNumStartPrice = $freightTemp["buyNumStartPrice"];
			       	$buyNumContinue = (int)$freightTemp["buyNumContinue"];
			       	$buyNumContinuePrice = $freightTemp["buyNumContinuePrice"];

			       	if($cartNum>$buyNumStart){
			       		$moreBuyNum = $cartNum-$buyNumStart;
			       		$times = 0;
			       		if($buyNumContinue>0){
			       			$times = ceil($moreBuyNum/$buyNumContinue);
			       		}
			       		$calculatePrice += $buyNumStartPrice + $buyNumContinuePrice*$times;
			       	}else{
			       		$calculatePrice += $buyNumStartPrice;
			       	}
		       	}else if($shippingFeeType==2){//重量
		       		$weightStart = (float)$freightTemp["weightStart"];
			       	$weightStartPrice = (float)$freightTemp["weightStartPrice"];
			       	$weightContinue = (float)$freightTemp["weightContinue"];
			       	$weightContinuePrice = (float)$freightTemp["weightContinuePrice"];
			       	$goodsWeight = (float)$goods['goodsWeight']*$cartNum;
			       	if($goodsWeight>$weightStart){
			       		$moreWeight = $goodsWeight-$weightStart;
			       		$times = 0;
			       		if($weightContinue>0){
			       			$times = ceil($moreWeight/$weightContinue);
			       		}
			       		$calculatePrice += $weightStartPrice + $weightContinuePrice*$times;
			       	}else{
			       		$calculatePrice += $weightStartPrice;
			       	}
		       	}else if($shippingFeeType==3){//体积
		       		$volumeStart = (float)$freightTemp["volumeStart"];
			       	$volumeStartPrice = (float)$freightTemp["volumeStartPrice"];
			       	$volumeContinue = (float)$freightTemp["volumeContinue"];
			       	$volumeContinuePrice = (float)$freightTemp["volumeContinuePrice"];
		       		$goodsVolume = (float)$goods['goodsVolume']*$cartNum;
			       	if($goodsVolume>$volumeStart){
			       		$moreVolume = $goodsVolume-$volumeStart;
			       		$times = 0;
			       		if($volumeContinue>0){
			       			$times = ceil($moreVolume/$volumeContinue);
			       		}
			       		$calculatePrice += $volumeStartPrice + $volumeContinuePrice*$times;
			       	}else{
			       		$calculatePrice += $volumeStartPrice;
			       	}
		       	}
	        }
        }

        return WSTBCMoney($calculatePrice,0);
    }
}
