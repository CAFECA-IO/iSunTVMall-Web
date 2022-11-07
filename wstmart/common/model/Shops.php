<?php
namespace wstmart\common\model;
use wstmart\shop\model\ShopConfigs;
use think\Db;
use think\facade\Cache;
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
 * 门店类
 */
class Shops extends Base{
    protected $pk = 'shopId';
    /************************************************************* 商家操作商品相关start *************************************************************/
    /**
     * 编辑店铺资料
     */
    public function editInfo($sId=0){
        $shopId = ($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $validate = new \wstmart\shop\validate\Shops;
        if (!$validate->scene('editInfo')->check(input('post.'))) {
            return WSTReturn($validate->getError());
        }else{
            $result = $this->allowField(['shopImg','isInvoice','invoiceRemarks','serviceStartTime','serviceEndTime','shopQQ','shopWangWang','shopAtive'])->save(input('post.'),['shopId'=>$shopId]);
            $scData = array();
            $shopKeywords = input("shopKeywords",'');
            $shopDesc = input("shopDesc",'');
            $shopHotWords = input("shopHotWords",'');
            if($shopKeywords!='')$scData["shopKeywords"] = $shopKeywords;
            if($shopDesc!='')$scData["shopDesc"] = $shopDesc;
            if($shopHotWords!='')$scData["shopHotWords"] = $shopHotWords;
            $result2 = Db::name('shop_configs')->where(['shopId'=>$shopId])->update($scData);
        }
        if(false !== $result && false !== $result2){
            return WSTReturn(lang("operation_success"),1);
        }else{
            return WSTReturn($this->getError());
        }
    }

    /**
    * 修改店铺公告
    */
    public function editNotice($sId=0){
        $shopId = ($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $shopNotice = input('shopNotice');
        if(strlen($shopNotice)>450){
            return WSTReturn(lang('shop_notice_limit'));
        }
        //对店铺公告进行处理,去掉换行
        $shopNotice = str_replace(PHP_EOL,'',$shopNotice);
        $rs = $this->where("shopId=$shopId")->setField('shopNotice',$shopNotice);
        if($rs!==false)return WSTReturn(lang('operation_success'),1);
        return WSTReturn(lang('operation_fail'),-1);
    }

    /**
     * 获取店铺信息
     */
	public function getByView($id){
		$shop = $this->alias('s')->join('__BANKS__ b','b.bankId=s.bankId','left')
                     ->join('__BANKS_LANGS__ bl','bl.bankId=b.bankId and langId='.WSTCurrLang())
                     ->join('__SHOP_CONFIGS__ sc','sc.shopId=s.shopId','left')
		             ->where(['s.dataFlag'=>1,'s.shopId'=>$id])
		             ->field('s.*,bl.bankName,sc.shopKeywords,sc.shopDesc,sc.shopHotWords')->find();
	    $areaIds = [];
        $areaMaps = [];
        $tmp = explode('_',$shop['areaIdPath']);
        foreach ($tmp as $vv){
         	if($vv=='')continue;
         	if(!in_array($vv,$areaIds))$areaIds[] = $vv;
        }
        if(!empty($areaIds)){
	         $areas = Db::name('areas')->where([['areaId','in',$areaIds],['dataFlag','=',1]])->field('areaId,areaName')->select();
	         foreach ($areas as $v){
	         	 $areaMaps[$v['areaId']] = $v['areaName'];
	         }
	         $tmp = explode('_',$shop['areaIdPath']);
	         $areaNames = [];
		     foreach ($tmp as $vv){
	         	 if($vv=='')continue;
	         	 $areaNames[] = $areaMaps[$vv];
	         	 $shop['areaName'] = implode('',$areaNames);
	         }
         }

		//获取经营范围
		$goodsCats = Db::name('goods_cats')->alias('c')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=c.catId and gcl.langId='.WSTCurrLang())->where(['parentId'=>0,'isShow'=>1,'dataFlag'=>1])->field('c.catId,catName')->select();
		$catshops = Db::name('cat_shops')->where('shopId',$id)->select();
		$catshopMaps = [];
		foreach ($goodsCats as $v){
			$catshopMaps[$v['catId']] = $v['catName'];
		}
		$catshopNames = [];
		foreach ($catshops as $key =>$v){
			if(isset($catshopMaps[$v['catId']]))$catshopNames[] = $catshopMaps[$v['catId']];
		}
		$shop['catshopNames'] = $catshopNames;
        //所属行业
        $trade = Db::name("trades t")->join('__TRADES_LANGS__ tl','tl.tradeId=t.tradeId and tl.langId='.WSTCurrLang())->where(["t.tradeId"=>$shop['tradeId']])->field("tl.tradeName")->find();
        $shop['tradeName'] = $trade['tradeName'];
		//获取认证类型
	    $shop['accreds'] =Db::name('shop_accreds')->alias('sac')->join('__ACCREDS__ a','sac.accredId=a.accredId and a.dataFlag=1','inner')
                        ->join('__ACCREDS_LANGS__ al','al.accredId=sac.accredId and langId='.WSTCurrLang())
	                    ->where('sac.shopId',$id)->field('accredName,accredImg')->select();
	    //开卡地址
        $areaNames  = model('areas')->getParentNames($shop['bankAreaId']);
        $shop['bankAreaName'] = implode('',$areaNames);
		return $shop;
    }



    /************************************************************* 商家操作商品相关end *************************************************************/












    /**
     * 获取商家认证
     */
    public function shopAccreds($shopId){
        $accreds= Db::table("__SHOP_ACCREDS__")->alias('sa')
        ->join('__ACCREDS__ a','a.accredId=sa.accredId','left')
        ->join('__ACCREDS_LANGS__ al','al.accredId=sa.accredId and langId='.WSTCurrLang())
        ->field('accredName,a.accredImg')
        ->where(['sa.shopId'=> $shopId])
        ->select();
        return $accreds;
    }

    /**
     * 获取店铺评分
     */
    public function getShopScore($shopId){
        $shop = $this->alias('s')->join('__SHOP_SCORES__ cs','cs.shopId = s.shopId','left')
                    ->where(['s.shopId'=>$shopId,'s.shopStatus'=>1,'s.dataFlag'=>1])->field('s.shopAddress,s.shopKeeper,s.shopImg,s.shopQQ,s.shopId,s.shopName,s.shopTel,s.areaId,cs.*')->find();
        if(empty($shop))return [];
        $shop->toArray();
        $shop['totalScore'] = WSTScore($shop['totalScore']/3,$shop['totalUsers']);
        $shop['goodsScore'] = WSTScore($shop['goodsScore'],$shop['goodsUsers']);
        $shop['serviceScore'] = WSTScore($shop['serviceScore'],$shop['serviceUsers']);
        $shop['timeScore'] = WSTScore($shop['timeScore'],$shop['timeUsers']);
        WSTUnset($shop, 'totalUsers,goodsUsers,serviceUsers,timeUsers');
        return $shop;
    }
    /**
     * 获取店铺首页信息
     */
    public function getShopInfo($shopId,$uId = 0){
    	$rs = Db::name('shops')->alias('s')
        ->join('__SHOP_EXTRAS__ ser','ser.shopId=s.shopId','inner')
        ->where(['s.shopId'=>$shopId,'s.shopStatus'=>1,'s.dataFlag'=>1])
    	->field('s.shopNotice,s.shopId,s.shopImg,s.shopName,s.shopAddress,s.shopQQ,s.shopWangWang,s.shopTel,s.serviceStartTime,s.longitude,s.latitude,s.serviceEndTime,s.shopKeeper,mapLevel,s.areaId,s.isInvoice,s.invoiceRemarks,ser.*')
    	->find();
    	if(empty($rs)){
    		//如果没有传id就获取自营店铺
    		$rs = Db::name('shops')->alias('s')
            ->join('__SHOP_EXTRAS__ ser','ser.shopId=s.shopId','inner')
            ->where(['s.shopStatus'=>1,'s.dataFlag'=>1,'s.isSelf'=>1])
    		->field('s.shopNotice,s.shopId,s.shopImg,s.shopName,s.shopAddress,s.shopQQ,s.shopWangWang,s.shopTel,s.serviceStartTime,s.longitude,s.latitude,s.serviceEndTime,s.shopKeeper,s.mapLevel,s.areaId,s.isInvoice,s.invoiceRemarks,ser.*')
    		->find();
    		if(empty($rs))return [];
    		$shopId = $rs['shopId'];
    	}
        //仅仅是为了获取businessLicenceImg而写的，因为businessLicenceImg不排除被删除掉了
        WSTAllow($rs,'shopNotice,shopId,shopImg,shopName,shopAddress,shopQQ,shopWangWang,shopTel,serviceStartTime,longitude,latitude,serviceEndTime,shopKeeper,mapLevel,areaId,isInvoice,invoiceRemarks,businessLicenceImg');
    	//评分
    	$score = $this->getShopScore($rs['shopId']);
    	$rs['scores'] = $score;
        //店铺地址
        $rs['areas'] = Db::name('areas')->alias('a')->join('__AREAS__ a1','a1.areaId=a.parentId','left')
        ->where([['a.areaId','=',$rs['areaId']]])->field('a.areaId,a.areaName areaName2,a1.areaName areaName1')->find();
    	//认证
    	$accreds = $this->shopAccreds($rs['shopId']);
    	$rs['accreds'] = $accreds;
    	//分类
    	$goodsCatMap = [];
    	$goodsCats = Db::name('cat_shops')->alias('cs')->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
    	->where(['shopId'=>$rs['shopId']])->field('cs.shopId,gc.catId,gcl.catName')->select();
        $rs['catId'] = 0;
    	foreach ($goodsCats as $key =>$v){
            if($key==0)$rs['catId'] = $v['catId'];
    		$goodsCatMap[] = $v['catName'];
    	}
    	$rs['catshops'] = (isset($goodsCatMap))?implode(',',$goodsCatMap):'';



    	$shopAds = array();
    	$config = Db::name('shop_configs')->where("shopId=".$rs['shopId'])->find();
    	//取出轮播广告
    	if($config["shopAds"]!=''){
    		$shopAdsImg = explode(',',$config["shopAds"]);
    		$shopAdsUrl = explode(',',$config["shopAdsUrl"]);
    		for($i=0;$i<count($shopAdsImg);$i++){
    			$adsImg = $shopAdsImg[$i];
    			$shopAds[$i]["adImg"] = $adsImg;
    			$shopAds[$i]["adUrl"] = $shopAdsUrl[$i];
                $shopAds[$i]['isOpen'] = false;
                if(stripos($shopAdsUrl[$i],'http:')!== false || stripos($shopAdsUrl[$i],'https:')!== false){
                    $shopAds[$i]['isOpen'] = true;
                }
    		}
            $rs['shopAds'] = $shopAds;
            unset($config['shopAds']);
    	}
        $rs = array_merge($rs,$config);
        //热搜关键词
        $rs['shopHotWords'] = ($rs['shopHotWords']!='')?explode(',',$rs['shopHotWords']):[];
    	//关注
    	$f = model('common/ShopMembers');
    	$rs['isfollow'] = $f->checkFavorite($shopId,$uId);
        $followNum = $f->followNum($shopId);
        $rs['followNum'] = $followNum;
    	return $rs;
    }

    /**
     * 获取店铺信息
     */
    public function getFieldsById($shopId,$fields){
        return $this->where(['shopId'=>$shopId,'dataFlag'=>1])->field($fields)->find();
    }

    /*
     * 获取店铺主题(小程序端、app端)
     */
    public function getShopHomeTheme($shopId,$type=''){
        $rs = '';
        if($type=='weapp'){
            $rs = Db::name('shop_configs')->where(["shopId"=>$shopId])->value('weappShopHomeTheme');
        }else{
            $rs = Db::name('shop_configs')->where(["shopId"=>$shopId])->value('appShopHomeTheme');
        }
        return $rs;
    }

    /**
     * 清除店铺缓存
     */
    public function clearCache($shopId){
        Cache::clear('CACHETAG_'.$shopId);
    }

    /*
     * 入驻缴纳年费异步回调方法
     */
    public function completeEnter($obj){
        $trade_no = $obj["trade_no"];
        $orderNo = $obj["out_trade_no"];
        $targetId = (int)$obj["targetId"];
        $targetType = (int)$obj["targetType"];
        $payFrom = $obj["payFrom"];
        $payMoney = (float)$obj["total_fee"];
        $scene = $obj["scene"];
        $log = Db::name('shop_fees')->where(["tradeNo"=>$trade_no])->find();
        if(!empty($log)){
            return WSTReturn(lang('annual_fee_paid'),-1);
        }
        Db::startTrans();
        try {
            // 更新店铺的到期日期、申请状态、申请日期等
            if($scene=="enter"){
                $shop = $this->where(['userId'=>$targetId])->find();
            }else{
                $shop = $this->where(['shopId'=>$targetId])->find();
            }
            $shopExpireDate = $shop["expireDate"];
            $newExpireDate = date('Y-m-d',strtotime("$shopExpireDate +1 year"));
            $shopsData = [];
            $shopsData['expireDate'] = $newExpireDate;
            if($scene=="enter"){
                $shopsData['applyStatus'] = 1;
                $shopsData['applyTime'] = date('Y-m-d H:i:s');
                $shopsData['isPay'] = 1;
                $shopsData['payAnnualFee'] = $payMoney;
                $shopsData['isRefund'] = 0;
            }
            if($scene=="enter"){
                $this->where(['userId'=>$targetId])->update($shopsData);
            }else{
                $this->where(['shopId'=>$targetId])->update($shopsData);
            }


            if($payFrom=='ccgwpays'){
                Db::name('ccgw_address_trans')->where(['ccTxid'=>$trade_no])->update(['dataStatus'=>1]);
            }
            
            //创建一条充值流水记录
            $lm = [];
            $lm['targetType'] = $targetType;
            $lm['targetId'] = $targetId;
            $lm['dataId'] = $orderNo;
            $lm['dataSrc'] = 5;
            $lm['remark'] = json_encode(['type'=>'lang_params','key'=>'shop_fee_charge_pay','params'=>[$payMoney]]);
            $lm['moneyType'] = 1;
            $lm['money'] = $payMoney;
            $lm['payType'] = $payFrom;
            $lm['tradeNo'] = $trade_no;
            $lm['createTime'] = date('Y-m-d H:i:s');
            model('LogMoneys')->create($lm);
            //创建一条支出流水记录
            $lm = [];
            $lm['targetType'] = $targetType;
            $lm['targetId'] = $targetId;
            $lm['dataId'] = $orderNo;
            $lm['dataSrc'] = 5;
            $lm['remark'] = json_encode(['type'=>'lang_params','key'=>'shop_fee_charge_pay2','params'=>[$payMoney]]);
            $lm['moneyType'] = 0;
            $lm['money'] = $payMoney;
            $lm['payType'] = 0;
            $lm['createTime'] = date('Y-m-d H:i:s');
            model('LogMoneys')->create($lm);
            // 创建缴费记录
            $logMoneyId = Db::name('log_moneys')->where(['tradeNo'=>$trade_no])->value('id');
            $fee = [];
            $fee['userId'] = $shop["userId"];
            $fee['shopId'] = $shop["shopId"];
            $fee['money'] = $payMoney;
            $fee['tradeNo'] = $trade_no;
            $fee['logMoneyId'] = $logMoneyId;
            $fee['remark'] = json_encode(['type'=>'lang','key'=>'shop_fee_charge_pay3']);
            $fee['startDate'] = $shopExpireDate;
            $fee['endDate'] = date('Y-m-d',strtotime("$shopExpireDate +1 year"));;
            $fee['createTime'] = date('Y-m-d H:i:s');
            Db::name('shop_fees')->insert($fee);
            Db::commit();
            return WSTReturn(lang('shopEnterFeeSuccess'),1);
        } catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('shopEnterFeeFail'),-1);
        }
    }

    /*
     * 获取店铺推荐的店铺id
     */
    public function getRecommendShopsId($catId=0){
        $where = [];
        $where[] = ['dataSrc','=',1];
        $where[] = ['goodsCatId','=',$catId];
        $rs = Db::name('recommends')->where($where)->order('dataSort desc')->column('dataId');
        return (!empty($rs))?implode(',',$rs):'';
    }

    /*
     * 商家续费
     */
    public function renew($uId=0,$sId=0){
        $userId = ($uId>0)?$uId:(int)session('WST_USER.userId');
        $shopId = ($sId>0)?$sId:(int)session('WST_USER.shopId');
        $shops = $this->getCatShopInfo($shopId);
        if((int)$shops['needPay']>0)return WSTReturn(lang('needPayShopFee'));
        Db::startTrans();
        try{
            $shopExpireDate = $this->where(['userId'=>$userId])->value('expireDate');
            $shopData = [];
            $newExpireDate = date('Y-m-d',strtotime("$shopExpireDate +1 year"));
            $shopData['expireDate'] = $newExpireDate;
            $shopRes = $this->where(['userId'=>$userId])->update($shopData);
            $fee = [];
            $fee['userId'] = $userId;
            $fee['shopId'] = $shopId;
            $fee['money'] = 0;
            $fee['remark'] = json_encode(['type'=>'lang','key'=>'shop_fee_charge_pay3']);
            $fee['startDate'] = date('Y-m-d');
            $fee['endDate'] = date('Y-m-d',strtotime("$shopExpireDate +1 year"));;
            $fee['createTime'] = date('Y-m-d H:i:s');
            $result = Db::name('shop_fees')->insert($fee);
            if(false !== $shopRes && false !== $result){
                session('WST_USER.expireDate',$newExpireDate);
                Db::commit();
                return WSTReturn(lang('shopEnterFeeSuccess'),1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('shopEnterFeeFail'));
        }
    }

    public function getCatShopInfo($sId=0){
        $shopId = ($sId>0)?$sId:(int)session('WST_USER.shopId');
        //获取经营范围
        $trade = Db::name("trades t")->join("shops s","s.tradeId=t.tradeId","inner")
            ->field("s.tradeId,t.tradeFee")
            ->where(['s.shopId'=>$shopId])
            ->find();
        $rs['needPay'] = $trade['tradeFee'];
        $rs['tradeId'] = $trade['tradeId'];
        return $rs;
    }

    /**
     * 入驻、续费余额支付
     */
    public function payByWallet($obj){
        $payPwd = input('payPwd');
        $orderNo = $obj['orderNo'];
        $targetId = (int)$obj["targetId"];
        $targetType = (int)$obj["targetType"];
        $payMoney = (float)$obj["total_fee"];
        $scene = $obj["scene"];
        if(!$payPwd)return WSTReturn(lang('require_password'),-1);
        $decrypt_data = WSTRSA($payPwd);
        if(!isset($obj['type'])){// 当有设置type时表示来自app端
            if($decrypt_data['status']==1){
                $payPwd = $decrypt_data['data'];
            }else{
                return WSTReturn(lang('pay_fail'));
            }
        }


        //判断是否开启余额支付
        $isEnbalePay = model('Payments')->isEnablePayment('wallets');
        if($isEnbalePay==0)return WSTReturn(lang('illegal_payment_type'),-1);
        $user = [];
        $shop = [];
        //获取用户钱包/商家钱包
        if($scene=="enter"){
            $user = Db::name('users')->where(['userId'=>$targetId])->find();
            $shop = Db::name('shops')->where(['userId'=>$targetId])->find();
        }else{
            $shop = Db::name('shops')->where(['shopId'=>$targetId])->find();
            $user = Db::name('users')->where(['userId'=>$shop['userId']])->find();
        }

        if($user['payPwd']=='')return WSTReturn(lang('no_pay_password_tips'),-1);
        if($user['payPwd']!=md5($payPwd.$user['loginSecret']))return WSTReturn(lang('pay_password_error'),-1);
        if($scene=="enter"){
            if($payMoney > $user['userMoney'])return WSTReturn(lang('balance_money_less'),-1);
        }else{
            if($payMoney > $shop['shopMoney'])return WSTReturn(lang('shop_balance_money_less'),-1);
        }

        Db::startTrans();
        try{
            // 更新店铺的到期日期、申请状态、申请日期等
            if($scene=="enter"){
                $shop = $this->where(['userId'=>$targetId])->find();
            }else{
                $shop = $this->where(['shopId'=>$targetId])->find();
            }
            $shopExpireDate = $shop["expireDate"];
            $newExpireDate = date('Y-m-d',strtotime("$shopExpireDate +1 year"));
            $shopsData = [];
            $shopsData['expireDate'] = $newExpireDate;
            if($scene=="enter"){
                $shopsData['applyStatus'] = 1;
                $shopsData['applyTime'] = date('Y-m-d H:i:s');
                $shopsData['isPay'] = 1;
                $shopsData['payAnnualFee'] = $payMoney;
                $shopsData['isRefund'] = 0;
            }
            if($scene=="enter"){
                $this->where(['userId'=>$targetId])->update($shopsData);
            }else{
                $this->where(['shopId'=>$targetId])->update($shopsData);
            }

            $lm = [];
            $lm['targetType'] = $targetType;
            $lm['targetId'] = $targetId;
            $lm['dataId'] = $orderNo;
            $lm['dataSrc'] = 5;
            $lm['remark'] = json_encode(['type'=>'lang','key'=>'shop_fee_charge_pay2','params'=>[$payMoney]]);
            $lm['moneyType'] = 0;
            $lm['money'] = $payMoney;
            $lm['payType'] = 'wallets';
            $lm['createTime'] = date('Y-m-d H:i:s');
            model('LogMoneys')->add($lm);
            $rechargeMoney = 0;
            if($scene=="enter"){
                //修改用户充值金额
                $rechargeMoney = ((float)$user['rechargeMoney']>(float)$payMoney)?$payMoney:$user['rechargeMoney'];
                model('users')->where(["userId"=>$user['userId']])->setDec("rechargeMoney",$rechargeMoney);
            }else{
                //修改商家充值金额
                $rechargeMoney = ((float)$shop['rechargeMoney']>(float)$payMoney)?$payMoney:$shop['rechargeMoney'];
                model('shops')->where(["userId"=>$shop['userId']])->setDec("rechargeMoney",$rechargeMoney);
            }

            // 创建缴费记录
            $logMoneyId = Db::name('log_moneys')->where(['dataId'=>$orderNo])->value('id');
            $fee = [];
            $fee['userId'] = $shop["userId"];
            $fee['shopId'] = $shop["shopId"];
            $fee['money'] = $payMoney;
            $fee['tradeNo'] = '';
            $fee['logMoneyId'] = $logMoneyId;
            $fee['remark'] = json_encode(['type'=>'lang','key'=>'shop_fee_charge_pay3']);
            $fee['startDate'] = $shopExpireDate;
            $fee['endDate'] = date('Y-m-d',strtotime("$shopExpireDate +1 year"));;
            $fee['createTime'] = date('Y-m-d H:i:s');
            $fee['lockCashMoney'] = $rechargeMoney;
            Db::name('shop_fees')->insert($fee);
            Db::commit();
            return WSTReturn(lang('pay_success'),1);
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('pay_fail'));
        }
    }
}
