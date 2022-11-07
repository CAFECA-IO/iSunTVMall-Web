<?php
namespace addons\reward\model;
use think\addons\BaseModel as Base;
use addons\reward\validate\Rewards as Validate;
use think\Db;
use think\Loader;
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
 * 满就送接口
 */
class Rewards extends Base{
	protected $pk = 'rewardId';
	/***
     * 安装插件
     */
    public function installMenu(){
    	Db::startTrans();
		try{
			$hooks = ['afterQueryGoods','homeDocumentGoodsPromotionDetail','afterQueryCarts','afterUserReceive',
                     'homeDocumentCartGoodsPromotion','homeDocumentSettlementGoodsPromotion',
                     'mobileDocumentCartGoodsPromotion','mobileDocumentSettlementGoodsPromotion','mobileDocumentGoodsPromotionDetail','mobileDocumentOrderViewGoodsPromotion',
                     'wechatDocumentCartGoodsPromotion','wechatDocumentSettlementGoodsPromotion','wechatDocumentGoodsPromotionDetail','wechatDocumentOrderViewGoodsPromotion',
                     'beforeInsertOrder','beforeInsertOrderGoods','homeDocumentOrderViewGoodsPromotion','shopDocumentOrderViewGoodsPromotion','adminDocumentOrderViewGoodsPromotion','offlineBeforeInsertOrderGoods','offlineAfterOrderPayComplete','offlineAfterQueryCarts'
                     ];
			$this->bindHoods("Reward", $hooks);
			$now = date("Y-m-d H:i:s");
			//商家中心
            $homeMenuLangParams = [
                1=>['menuName'=>'滿就送活動'],
                2=>['menuName'=>'满就送活动'],
                3=>['menuName'=>'Reward'],
            ];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>77,"menuUrl"=>"/addon/reward-shops-index","menuOtherUrl"=>"/addon/reward-shops-edit,/addon/reward-shops-pageQuery,/addon/reward-shops-toEdit,/addon/reward-shops-del","menuType"=>1,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"reward"]);
            $datas = [];
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $homeMenuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('home_menus_langs')->insertAll($datas);
			installSql("reward");
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
    }

    /**
	 * 删除菜单
	 */
	public function uninstallMenu(){
		Db::startTrans();
		try{
			$hooks = ['afterQueryGoods','homeDocumentGoodsPromotionDetail','afterQueryCarts','afterUserReceive',
                     'homeDocumentCartGoodsPromotion','homeDocumentSettlementGoodsPromotion',
                     'mobileDocumentCartGoodsPromotion','mobileDocumentSettlementGoodsPromotion','mobileDocumentGoodsPromotionDetail','mobileDocumentOrderViewGoodsPromotion',
                     'wechatDocumentCartGoodsPromotion','wechatDocumentSettlementGoodsPromotion','wechatDocumentGoodsPromotionDetail','wechatDocumentOrderViewGoodsPromotion',
                     'beforeInsertOrder','beforeInsertOrderGoods','homeDocumentOrderViewGoodsPromotion','shopDocumentOrderViewGoodsPromotion','adminDocumentOrderViewGoodsPromotion','offlineBeforeInsertOrderGoods','offlineAfterOrderPayComplete','offlineAfterQueryCarts'
                     ];
            $this->unbindHoods("Reward", $hooks);
            $homeMenuId = Db::name('home_menus')->where(["menuMark"=>"reward"])->value('menuId');
            Db::name('home_menus')->where(["menuMark"=>"reward"])->delete();
            Db::name('home_menus_langs')->where(['menuId'=>$homeMenuId])->delete();
			uninstallSql("reward");//传入插件名
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}

	/**
	 * 菜单显示隐藏
	 */
	public function toggleShow($isShow = 1){
		Db::startTrans();
		try{
			Db::name('home_menus')->where(["menuMark"=>"reward"])->update(["isShow"=>$isShow]);
			if($isShow==1){
				$this->addMobileBtn();
			}else{
				$this->delMobileBtn();
			}
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}

	/**
     * 商家-满就送列表
     */
    public function pageQueryByShop($sId=0){
    	$rewardTitle = input('rewardTitle');
        $shopId = ($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $where = [['dataFlag','=',1],['shopId','=',$shopId]];
        if($rewardTitle!='')$where[] = ['rewardTitle','like','%'.$rewardTitle.'%'];
    	$page =  $this->where($where)
                     ->order('createTime desc')
                     ->paginate(input('limit/d'))->toArray();
        $page['status'] = 1;
        if(count($page['data'])>0){
            $time = time();
            foreach ($page['data'] as $key => $v) {
                if(WSTStrToTime($v['startDate']." 00:00:00")>$time){
                    $page['data'][$key]['rewardStatus'] = -1;
                }else if((WSTStrToTime($v['startDate']." 00:00:00")<=$time) && (WSTStrToTime($v['endDate']." 23:59:59")>=$time)){
                    $page['data'][$key]['rewardStatus'] = 0;
                }else{
                    $page['data'][$key]['rewardStatus'] = 1;
                }
            }
        }
        return $page;
    }

    /**
     * 查询商品
     */
    public function searchGoods($sId=0){
    	$shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
    	$shopCatId1 = (int)input('post.shopCatId1');
    	$shopCatId2 = (int)input('post.shopCatId2');
    	$goodsName = input('post.goodsName');
    	$where = [];
    	$where[] = ['goodsStatus','=',1];
    	$where[] = ['dataFlag','=',1];
    	$where[] = ['isSale','=',1];
        $where[] = ['goodsType','=',0];
    	$where[] = ['shopId','=',$shopId];
    	if($shopCatId1>0)$where[] = ['shopCatId1','=',$shopCatId1];
    	if($shopCatId2>0)$where[] = ['shopCatId2','=',$shopCatId2];
    	if($goodsName!='')$where[] = ['gl.goodsName|goodsSn','like','%'.$goodsName.'%'];
    	$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->where($where)->field('goodsImg,gl.goodsName,g.goodsId,marketPrice,shopPrice,goodsType')->order('gl.goodsName asc')->select();
        return WSTReturn('',1,$rs);
    }
    /**
     * 获取在售商品列表
     */
    public function getSaleGoods($sId=0){
    	$shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
    	$where = [];
    	$where['goodsStatus'] = 1;
    	$where['dataFlag'] = 1;
        $where['goodsType'] = 0;
    	$where['isSale'] = 1;
    	$where['shopId'] = $shopId;
    	$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->where($where)->field('gl.goodsName,g.goodsId')->order('gl.goodsName asc')->select();
        return WSTReturn('',1,$rs);
    }

    /**
     * 获取优惠券
     */
    public function getCoupons($sId=0){
        $shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
    	$rs = Db::name('coupons')->where('endDate>"'.date('Y-m-d').'" and dataFlag=1 and shopId='.$shopId)
    	        ->field('couponId,couponValue,useCondition,useMoney,useObjectIds')
    	        ->order('couponId asc')
    	        ->select();
        return WSTReturn('',1,$rs);
    }

    /**
     * 新增
     */
    public function add($sId=0){
        $data = input('post.');
        unset($data['rewardId']);
        $shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $goodsIds = is_array($data['useObjectIds'])?$data['useObjectIds']:explode(',',$data['useObjectIds']);
        $validate = new Validate;
        if(!$validate->check($data)){
            return WSTReturn($validate->getError());
        }
        if(strtotime($data['startDate']) >= strtotime($data['endDate']))return WSTReturn(lang('reward_start_time_limit')); //判断有没有同一时间的全店满送活动
        $where = [];
        $where['dataFlag'] = 1;
        if($data['useObjects']==1)$where['useObjects'] = 0;
        $where['shopId'] = $shopId;
        $whereOr = ' ( ("'.date('Y-m-d',strtotime($data['startDate'])).'" between startDate and endDate) or ( "'.date('Y-m-d',strtotime($data['endDate'])).'" between startDate and endDate) ) ';
        $rn = $this->where($where)->where($whereOr)->Count();
        if($rn>0)return WSTReturn(lang('reward_has_same_time_exist'));
        $goods = [];
        //判断商品是否存在
        if($data['useObjects']==1){
            $goods = Db::name('goods')->where([['goodsId','in',$goodsIds],['shopId','=',$shopId],['goodsType','=',0],['isSale','=',1],['goodsStatus','=',1],['dataFlag','=',1]])
                       ->field('goodsId,goodsName')->select();
            if(empty($goods))return WSTReturn(lang('reward_require_select_goods'));
            foreach ($goods as $key => $gv) {
                $where = [];
                $where['r.useObjects'] = 1;
                $where['r.dataFlag'] = 1;
                $where['g.goodsId'] = $gv['goodsId'];
                $where['r.shopId'] = $shopId;
                $whereOr = ' ( ("'.date('Y-m-d',strtotime($data['startDate'])).'" between startDate and endDate) or ( "'.date('Y-m-d',strtotime($data['endDate'])).'" between startDate and endDate) ) ';
                $rn = $this->alias('r')->join('__REWARD_GOODS__ g','r.rewardId=g.rewardId')->where($where)->where($whereOr)->Count();
                if($rn>0)return WSTReturn(lang('reward_goos_exist_same_time',[$gv['goodsName']]));
            }
        }
        $data['shopId'] = $shopId;
        $data['createTime'] = date('Y-m-d H:i:s');
        Db::startTrans();
        try{
            $result = $this->allowField(true)->save($data);
            if(false !== $result){
                //组装优惠内容
                $no = input('no/d',0);
                $favourables = [];
                for($i=0;$i<$no;$i++){
                    $json = [];
                    $json['rewardId'] = $this->rewardId;
                    $json['orderMoney'] = (int)input('money-'.$i);
                    $cjson = [];
                    $cjson['chk0'] = ((int)input('chk-0-'.$i)!=0)?true:false;
                    $cjson['chk0val'] = (int)input('j-reward-c-0-'.$i);
                    $cjson['chk1'] = ((int)input('chk-1-'.$i)!=0)?true:false;
                    $cjson['chk1val'] = (int)input('j-reward-c-1-'.$i);
                    $cjson['chk2'] = ((int)input('chk-2-'.$i)!=0)?true:false;
                    $cjson['chk3'] = ((int)input('chk-3-'.$i)!=0)?true:false;
                    $cjson['chk3val'] = (int)input('j-reward-c-3-'.$i);
                    $json['favourableJson'] = json_encode($cjson);
                    $favourables[] = $json;
                }
                Db::name('reward_favourables')->insertAll($favourables);
                if($data['useObjects']==1){
                    //保存优惠券适用的商品
                    $arr = [];
                    for($i=0;$i<count($goods);$i++){
                        $cgoods = [];
                        $cgoods['goodsId'] = $goods[$i]['goodsId'];
                        $cgoods['rewardId'] = $this->rewardId;
                        $arr[] = $cgoods;
                    }
                    Db::name('reward_goods')->insertAll($arr);
                }
            }
            Db::commit();
            return WSTReturn(lang('reward_operation_success'),1);
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('reward_operation_fail'));
        }
    }

    /**
     * 获取活动信息
     */
    public function getById($sId=0){
        $id = (int)input('id/d',0);
        $shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $reward = $this->where(['rewardId'=>$id,'dataFlag'=>1,'shopId'=>$shopId])->find();
        if($reward){
        	$reward['goods'] = [];
        	//获取活动优惠信息
        	$rewardJson = Db::name('reward_favourables')->where('rewardId',$id)->order('orderMoney asc')->select();
        	foreach ($rewardJson as $key => $v) {
        		$rewardJson[$key]['favourableJson'] = json_decode($v['favourableJson']);
        	}
        	$reward['rewardJson'] = $rewardJson;
        	//获取适用商品
        	if($reward['useObjects']==1){
        		$reward['goods'] = Db::name('reward_goods')->alias('cg')
        		->join('__GOODS__ g','g.goodsId=cg.goodsId and g.isSale=1 and g.dataFlag=1 and g.goodsStatus=1 and goodsType=0','inner')
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
        		->where('cg.rewardId',$id)
        		->field('goodsImg,gl.goodsName,g.goodsId,marketPrice,shopPrice,goodsType')
        		->select();
        	}
        }
        return $reward;
    }
    /**
     * 编辑活动
     */
    public function edit($sId=0){
        $data = input('post.');
        $rewardId = (int)$data['rewardId'];
        unset($data['rewardId']);
        $shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $goodsIds = explode(',',$data['useObjectIds']);
        $validate = new Validate;
        if(!$validate->check($data)){
            return WSTReturn($validate->getError());
        }
        if(strtotime($data['startDate']) >= strtotime($data['endDate']))return WSTReturn(lang('reward_start_time_limit'));
        //判断有没有同一时间的全店满送活动
        $where = [];
        $where[] = ['dataFlag','=',1];
        if($data['useObjects']==1)$where[] = ['useObjects','=',0];
        $where[] = ['rewardId','<>',$rewardId];
        $where[] = ['shopId','=',$shopId];
        $whereOr = ' ( ("'.date('Y-m-d',strtotime($data['startDate'])).'" between startDate and endDate) or ( "'.date('Y-m-d',strtotime($data['endDate'])).'" between startDate and endDate) ) ';
        $rn = $this->where($where)->where($whereOr)->Count();
        if($rn>0)return WSTReturn(lang('reward_has_same_time_exist2'));
        $reward = $this->where(['rewardId'=>$rewardId,'dataFlag'=>1,'shopId'=>$shopId])->find();
        if(false == $reward)return WSTReturn('编辑失败');
        $goods = [];
        if($data['useObjects']==1){
            $goods = Db::name('goods')
                ->alias('g')
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                ->where([['g.goodsId','in',$goodsIds],['shopId','=',$shopId],['goodsType','=',0],['isSale','=',1],['goodsStatus','=',1],['dataFlag','=',1]])
                 ->field('g.goodsId,gl.goodsName')->select();
            if(empty($goods))return WSTReturn(lang('reward_require_select_goods'));
            foreach ($goods as $key => $gv) {
                $where = [];
                $where[] = ['r.useObjects','=',1];
                $where[] = ['r.dataFlag','=',1];
                $where[] = ['r.rewardId','<>',$rewardId];
                $where[] = ['g.goodsId','=',$gv['goodsId']];
                $where[] = ['r.shopId','=',$shopId];
                $whereOr = ' ( ("'.date('Y-m-d',strtotime($data['startDate'])).'" between startDate and endDate) or ( "'.date('Y-m-d',strtotime($data['endDate'])).'" between startDate and endDate) ) ';
                $rn = $this->alias('r')->join('__REWARD_GOODS__ g','r.rewardId=g.rewardId')->where($where)->where($whereOr)->Count();
                if($rn>0)return WSTReturn(lang('reward_goos_exist_same_time',[$gv['goodsName']]));
            }
        }else{
            $data['useObjectIds'] = '';
        }
        WSTAllow($data,'rewardTitle,startDate,endDate,rewardType,useObjects,useObjectIds');
        Db::startTrans();
        try{
            $result = $this->update($data,['rewardId'=>$rewardId,'shopId'=>$shopId]);
            if(false !== $result){
                Db::name('reward_goods')->where('rewardId',$rewardId)->delete();
                Db::name('reward_favourables')->where('rewardId',$rewardId)->delete();
                //组装优惠内容
                $no = input('no/d',0);
                if($data['rewardType']==0)$no=1;
                $favourables = [];
                for($i=0;$i<$no;$i++){
                    $json = [];
                    $json['rewardId'] = $rewardId;
                    $json['orderMoney'] = (int)input('money-'.$i);
                    $cjson = [];
                    $cjson['chk0'] = ((int)input('chk-0-'.$i)!=0)?true:false;
                    $cjson['chk0val'] = (int)input('j-reward-c-0-'.$i);
                    $cjson['chk1'] = ((int)input('chk-1-'.$i)!=0)?true:false;
                    $cjson['chk1val'] = (int)input('j-reward-c-1-'.$i);
                    $cjson['chk2'] = ((int)input('chk-2-'.$i)!=0)?true:false;
                    $cjson['chk3'] = ((int)input('chk-3-'.$i)!=0)?true:false;
                    $cjson['chk3val'] = (int)input('j-reward-c-3-'.$i);
                    $json['favourableJson'] = json_encode($cjson);
                    $favourables[] = $json;
                }
                Db::name('reward_favourables')->insertAll($favourables);
                if($data['useObjects']==1){
                    $goodsCatIds = [];
                    //保存活动适用的商品
                    $arr = [];
                    for($i=0;$i<count($goods);$i++){
                        $cgoods = [];
                        $cgoods['goodsId'] = $goods[$i]['goodsId'];
                        $cgoods['rewardId'] = $rewardId;
                        $arr[] = $cgoods;
                    }
                    Db::name('reward_goods')->insertAll($arr);
                }
            }
            Db::commit();
            return WSTReturn(lang('reward_operation_success'),1);
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('reward_operation_fail'));
        }
    }

    /**
     * 删除活动
     */
    public function del($sId=0){
        $shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $id = (int)input('id/d',0);
        $this->where(['rewardId'=>$id,'shopId'=>$shopId])->update(['dataFlag'=>-1]);
        return WSTReturn(lang('reward_operation_success'),1);
    }

    /**
     * 判断商品是否有参与满就送活动
     */
    public function getGoodsRewardTags($goodsId){
        $time = date('Y-m-d');
        //查询是否有针对该商品的活动
        $hasReward = Db::name('reward_goods')->alias('rg')
                       ->join('__REWARDS__ r','rg.rewardId=r.rewardId')
                       ->where('r.dataFlag=1 and goodsId='.$goodsId.' and startDate<="'.$time.'" and endDate>="'.$time.'"')->count();
        if($hasReward>0)return 1;
        //查询一下是否有针对全店分类的活动
        $goods = Db::name('goods')->where(['goodsId'=>$goodsId,'goodsType'=>0])->field('shopId')->find();
        if(empty($goods))return 0;
        $hasReward = Db::name('rewards')->where('dataFlag=1 and useObjects=0 and shopId='.$goods['shopId'].' and startDate<="'.$time.'" and endDate>="'.$time.'"')->count();
        if($hasReward>0)return 1;
        return 0;
    }

    /**
     * 获取可用的满减活动
     */
    public function getAvailableRewards($shopId,$goodsId){
        $goods = Db::name('goods')->where(['goodsId'=>$goodsId,'goodsType'=>0])->find();
        if(empty($goods))return [];
        $date = date('Y-m-d');
        //先查看有没有指定商品的满就送活动
        $reward = $this->alias('r')->join('__REWARD_GOODS__ rg','r.rewardId=rg.rewardId')
                  ->where([['dataFlag','=',1],['goodsId','=',$goodsId],['startDate','<=',$date],['endDate','>=',$date]])
                  ->order('r.rewardId asc')
                  ->find();
        if(empty($reward)){
            //查看是否有全店的满就送活动
            $reward = $this->where([['dataFlag','=',1],['useObjects','=',0],['shopId','=',$shopId],['startDate','<=',$date],['endDate','>=',$date]])
                      ->order('rewardId asc')
                      ->find();
        }
        if(empty($reward))return [];
        $reward = $reward->toArray();
        $favourables = Db::name('reward_favourables')->where('rewardId',$reward['rewardId'])->order('orderMoney asc')->select();
        foreach ($favourables as $key => $v) {
            $json = json_decode($v['favourableJson'],true);
            if($json['chk1'])$json['chk1val'] = $this->getGoods($json['chk1val']);
            if(WSTConf('WST_ADDONS.coupon')){
                if($json['chk3'])$json['chk3val'] = $this->getCouponById($json['chk3val']);
            }else{
                if($json['chk3'])$json['chk3val'] = [];
            }
            $favourables[$key]['favourableJson'] = $json;
        }
        $reward['json'] = $favourables;
        return $reward;
    }

    /**
     * 获取指定的商品
     */
    public function getGoods($id){
        $rs = Db::name('goods')->where(['goodsId'=>$id,'goodsType'=>0])->field('goodsId,goodsName,goodsType,shopPrice,goodsImg,isSpec,isFreeShipping')->find();
        //如果有规格，则取默认规格
        if($rs['isSpec']==1){
            $spec = Db::name('goods_specs')->where(['goodsId'=>$rs['goodsId'],'dataFlag'=>1,'isDefault'=>1])->find();
            if($spec['specIds']!=''){
                $rs['goodsSpecId'] = $spec['id'];
                $specIds = explode(':',$spec['specIds']);
                $specItem = Db::name('spec_items')->alias('s')->join('__SPEC_CATS__ sc','s.catId=sc.catId')
                              ->where([['itemId','in',$specIds]])
                              ->field('catName,itemName')
                              ->order('sc.catSort asc,sc.catId asc')
                              ->select();
                $str = [];
                foreach ($specItem as $key => $v) {
                    $str[] = $v['catName']."：".$v['itemName'];
                }
                $rs['goodsSpecNames'] = implode('@@_@@',$str);
            }
        }
        if(!isset($rs['goodsSpecId'])){
            $rs['goodsSpecId'] = 0;
            $rs['goodsSpecNames'] = '';
        }
        return ['text'=>$rs['goodsName'],'data'=>$rs];
    }
    /**
     * 获取优惠券
     */
    public function getCouponById($id){
        $rs = Db::name('coupons')->where('couponId',$id)->field('couponId,useCondition,couponValue,useMoney,shopId')->find();
        if($rs['useCondition']==1){
            return ['text'=>lang('reward_full_reduction',[$rs['useMoney'],$rs['couponValue']]),'data'=>$rs];
        }else{
            return ['text'=>lang('currency_symbol').$rs['couponValue'],'data'=>$rs];
        }
    }

    /**
     * 【实物】满就送活动商品排序归类
     */
    public function afterQueryCarts($params){
        foreach ($params['carts']['carts'] as $skey => $shop) {
            foreach ($shop['list'] as $key => $v) {
                //如果存在商品优惠活动则不需要继续
                if(!empty($v['promotion']))continue;

                //获取符合条件的优惠活动
                $promotion = $this->getAvailableRewards($skey,$v['goodsId']);
                if(!empty($promotion)){
                    if($promotion['useObjects']==0 && empty($params['carts']['carts'][$skey]['promotion'])){
                        $params['carts']['carts'][$skey]['promotion']['data'] = $promotion;
                        $params['carts']['carts'][$skey]['promotion']['type'] = 'reward';
                    }
                    $params['carts']['carts'][$skey]['list'][$key]['promotion']['data'] = $promotion;
                    $params['carts']['carts'][$skey]['list'][$key]['promotion']['type'] = 'reward';
                }
            }
            //避免多个活动中有多个全店适用的活动
            if(!empty($params['carts']['carts'][$skey]['promotion'])){
                foreach ($shop['list'] as $key => $v) {
                    $params['carts']['carts'][$skey]['list'][$key]['promotion']['data'] = $params['carts']['carts'][$skey]['promotion']['data'];
                    $params['carts']['carts'][$skey]['list'][$key]['promotion']['type'] = 'reward';
                }
            }
            //对商品按活动进行归类排序
            usort($params['carts']['carts'][$skey]['list'],'self::sortRewardGoods');
            //对商品进行分类标记
            $rewardId = 0;//用于标记优惠活动的第一个商品ID
            $rewardAllGoodsIds = [];//用于标记优惠活动
            //以活动的第一个商品为key，收集和他同一个活动的其他商品的id
            foreach ($params['carts']['carts'][$skey]['list'] as $bkey => $bgoods) {
                if(!empty($bgoods['promotion']) && $bgoods['promotion']['type']=='reward'){
                    if($rewardId!=$bgoods['promotion']['data']['rewardId']){
                            $rewardId = $bgoods['promotion']['data']['rewardId'];
                    }
                    $rewardAllGoodsIds[$rewardId][] = $bgoods['cartId'];
                }else{
                    $rewardId = 0;
                }
            }
            //把收集到的同一个活动的商品ID集合放到第一个商品中
            $rewardId = 0;
            foreach ($params['carts']['carts'][$skey]['list'] as $bkey => $bgoods) {
                if(!empty($bgoods['promotion']) && $bgoods['promotion']['type']=='reward'){
                    if($rewardId!=$bgoods['promotion']['data']['rewardId']){
                        $rewardId = $bgoods['promotion']['data']['rewardId'];
                        $params['carts']['carts'][$skey]['list'][$bkey]['rewardCartIds'] = $rewardAllGoodsIds[$rewardId];
                    }
                }
            }
            //如果是结算的话则要对店铺金额进行处理了
            /**************************************************************************
             结算会改变原来的carts结构
             [
                'promotionMoney'=>'这次购物总共要优惠的金额',
                '1'=> [
                        'shopId'=>1,
                        .....
                        'promotionMoney'=>'店铺要优惠的金额',
                        'promotion'=>['type'=>'reward','data'=>'店铺参与的活动Json-备用']
                        'list'=>[
                             '0'=>[
                                  'cartId'=>'1',
                                  'goodsId'=>'1',
                                  ......
                                  'rewardCartIds'=>'参与活动的商品[cartId]列表--有多个商品参与同一个活动的话则只有第一个活动才有',
                                  'promotion'=>['type'=>'reward','data'=>'商品参与的活动Json'],
                                  'rewardResult'=>'这个商品应该享受的活动优惠Json数组--有多个商品参与同一个活动的话则只有第一个活动才有'
                                  'rewardGoodsMoney'=>'活动商品达到的金额--有多个商品参与同一个活动的话则只有第一个活动才有',
                                  'rewardMoney'=>'活动要减免的金额--有多个商品参与同一个活动的话则只有第一个活动才有',
                                  'rewardText'=>'商品文字描述--有多个商品参与同一个活动的话则只有第一个活动才有'
                                  'hasRward'=>'是否满就送货活动有效，放在第一个商品中'
                             ]
                        ]
                 ]
             ]
             **************************************************************************/
            if($params['isSettlement']){
                foreach ($params['carts']['carts'][$skey]['list'] as $bkey => $bgoods) {
                    //没有优惠活动 或者 优惠活动不是满就送的跳过
                    if(empty($bgoods['promotion']) || $bgoods['promotion']['type']!='reward')continue;
                    //把活动优惠的结果放到活动的第一个商品上
                    if(isset($bgoods['rewardCartIds'])){
                        $rewardMoney = 0;
                        foreach ($params['carts']['carts'][$skey]['list'] as $tkey => $tgoods){
                             if(in_array($tgoods['cartId'],$bgoods['rewardCartIds'])){
                                  $rewardMoney = $rewardMoney + $tgoods['shopPrice'] * $tgoods['cartNum'];
                             }
                        }
                        //看下计算出来的总金额落在哪个优惠范围内
                        $favourables = $bgoods['promotion']['data']['json'];
                        $params['carts']['carts'][$skey]['list'][$bkey]['rewardResult'] = [];
                        $params['carts']['carts'][$skey]['list'][$bkey]['rewardMoney'] = 0;
                        $params['carts']['carts'][$skey]['list'][$bkey]['rewardGoodsMoney'] = $rewardMoney;
                        $params['carts']['carts'][$skey]['list'][$bkey]['hasReward'] = false;
                        for($fkey = count($favourables)-1;$fkey>=0;$fkey--) {
                            if($rewardMoney>=$favourables[$fkey]['orderMoney']){
                                $params['carts']['carts'][$skey]['list'][$bkey]['hasReward'] = true;
                                //保存优惠内容-下单时用到
                                $favourableJson = $favourables[$fkey]['favourableJson'];
                                $params['carts']['carts'][$skey]['list'][$bkey]['rewardResult'] = $favourables[$fkey];
                                $params['carts']['carts'][$skey]['list'][$bkey]['rewardMoney'] = 0;
                                //获取优惠文字-用于显示
                                $favourableTxt = [];
                                if($favourableJson['chk0']){ // 【满足减免金额条件】
                                    $favourableTxt[] = lang('reward_reduce_money',[$favourableJson['chk0val']]);
                                    $params['carts']['carts'][$skey]['list'][$bkey]['rewardMoney'] = $favourableJson['chk0val'];
                                    //记录到店铺里边，现在活动要优惠多少
                                    $params['carts']['carts'][$skey]['promotionMoney'] += $favourableJson['chk0val'];
                                    $params['carts']['promotionMoney'] += $favourableJson['chk0val'];
                                }
                                // 【满足赠送商品条件】
                                if($favourableJson['chk1'])$favourableTxt[] = lang('reward_giving_gift',[$favourableJson['chk1val']['text']]);
                                // 【满足包邮条件】
                                if($favourableJson['chk2']){
                                    $favourableTxt[] = lang('reward_free_shipping');
                                    //记录到店铺里要免邮费
                                    $params['carts']['carts'][$skey]['isFreeShipping'] = true;
                                }
                                // 【满足赠送优惠券条件】
                                if($favourableJson['chk3'] && !empty($favourableJson['chk3val']))$favourableTxt[] = lang('reward_send_coupons',[$favourableJson['chk3val']['text']]);
                                $params['carts']['carts'][$skey]['list'][$bkey]['rewardText'] = implode('、',$favourableTxt);
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 排序
     */
    public function sortRewardGoods($a,$b){
        if(!isset($a['promotion']['data']))return 1;//没有优惠活动
        if(!isset($b['promotion']['data']))return -1;//没有优惠活动
        if(!isset($a['promotion']['data']['rewardId']))return 1;
        if(!isset($b['promotion']['data']['rewardId']))return -1;
        if($a['promotion']['data']['rewardId']>$b['promotion']['data']['rewardId']){
           return -1;
        }else{
           return 1;
        }
    }

    /**
     * 重新计算运费和订单金额
     */
    public function beforeInsertOrder($params){
        //修改订单价格和运费
        $carts = $params['carts']['carts'][$params['order']['shopId']];
        //记录参与的店铺活动-全店的活动才记录，商品的活动不记录在这里
        if(!empty($carts['promotion']) && $carts['promotion']['type']=='reward'){
            $params['order']['extraJson'] = json_encode(['orderCode'=>'reward','extraJson'=>json_encode($carts['promotion']['data'])]);
        }
    }

    /**
     * 重新计算订单商品
     */
    public function beforeInsertOrderGoods($params){
        $orderGoods = $params['orderGoods'];
        $order = model('orders')->get($params['orderId']);
        $carts = $params['carts']['carts'][$order['shopId']];
        $gifts = [];
        $hasCoupon = false;
        foreach ($params['orderGoods'] as $_k => $_v) {
            $params['orderGoods'][$_k]['rewardVal'] = 0;
            if(!$hasCoupon && isset($_v['couponVal']))$hasCoupon = true;
        }
        unset($_k, $_v);
        foreach ($carts['list'] as $key => $goods) {
            if(!empty($goods['promotion']) && $goods['promotion']['type']=='reward'){
                $rewardCartIds = [];//一同参与活动的商品
                $rewardResult = []; //应该享受的优惠
                $rewardText = '';   //优惠文字描述
                $rewardMoney = 0;//优惠应该减免的金额
                if(isset($goods['rewardCartIds'])){
                    $rewardCartIds = $goods['rewardCartIds'];
                    if(empty($goods['rewardResult']))continue;//没有获得优惠就跳过
                    $rewardResult = $goods['rewardResult'];
                    $rewardText = $goods['rewardText'];
                    $rewardMoney = $goods['rewardMoney'];

                    //将cartId转换成商品ID
                    $promotionGoodsIds = [];
                    // 参与满减活动的商品的总金额
                    $goodsTotalMoney = 0;
                    // 单组商品总金额  [1=>100,3=>400]  ['商品id'=>店铺价格*购买数量]
                    $singleGMoneys = [];
                    foreach ($carts['list'] as $gkey => $gv) {
                        if(in_array($gv['cartId'],$rewardCartIds)){
                            // 记录参与当前活动的商品id
                            $promotionGoodsIds[] = $gv['goodsId'];
                            // 累加商品总金额
                            $money = $gv['cartNum']*$gv['shopPrice'];
                            $goodsTotalMoney += $money;
                            $singleGMoneys[$gv['goodsId']] = $money;
                        }
                    }
                    //如果有送赠品则加入赠品
                    $favourable = $rewardResult['favourableJson'];
                    if($favourable['chk1']){
                        $gItem = [
                            'orderId'=>$params['orderId'],
                            'goodsId'=>$favourable['chk1val']['data']['goodsId'],
                            'goodsNum'=>1,
                            'goodsPrice'=>0,
                            'goodsSpecId'=>$favourable['chk1val']['data']['goodsSpecId'],
                            'goodsSpecNames'=>$favourable['chk1val']['data']['goodsSpecNames'],
                            'goodsName'=>$favourable['chk1val']['text'],
                            'goodsImg'=>$favourable['chk1val']['data']['goodsImg'],
                            'commissionRate'=> 0,
                            'goodsCode'=>'gift',
                            'goodsType'=>$favourable['chk1val']['data']['goodsType'],
                            'extraJson'=>'',
                            'promotionJson'=>'',
                            'rewardVal'=>0,
                            'orderGoodscommission'=>0
                        ];
                        if((int)WSTConf('CONF.isOpenScorePay')==1){
                            // 有开启积分支付则存在以下字段
                            $gItem['useScoreVal'] = 0;
                            $gItem['scoreMoney'] = 0;
                        }
                        if(WSTConf('CONF.isOrderScore')==1){
                            // 有开启下单获得积分则存在以下字段
                            $gItem['getScoreVal'] = 0;
                            $gItem['getScoreMoney'] = 0;
                        }

                        if($hasCoupon)$gItem['couponVal']=0;
                        $gifts[] = $gItem;

                    }

                    //准备好数据，给每个相关的商品都填上
                    $extraJson = ['orderCode'=>'reward',
                                  'text'=>$rewardText,
                                  'promotionMoney'=>$rewardMoney,
                                  'promotionGoodsIds'=>$promotionGoodsIds,
                                  'extraJson'=>json_encode($rewardResult)
                                 ];
                    // 活动总最后一件商品索引
                    $lastGIndex = count($singleGMoneys)-1;
                    // 当前活动商品索引
                    $currGIndex = 0;
                    // 累计减免金额
                    $rMoney = 0;
                    foreach ($params['orderGoods'] as $okey => $ov) {
                        if(in_array($ov['goodsId'],$promotionGoodsIds)){
                            $params['orderGoods'][$okey]['promotionJson'] = json_encode($extraJson);
                            if($rewardMoney>0){
                                if($currGIndex==$lastGIndex){
                                    $params['orderGoods'][$okey]['rewardVal'] = $rewardMoney-$rMoney;
                                }else{
                                    // 计算商品可减免的金额,小数点后两位
                                    $val = round($singleGMoneys[$ov['goodsId']]/$goodsTotalMoney*$rewardMoney,2);
                                    $params['orderGoods'][$okey]['rewardVal'] = $val;
                                    $rMoney += $val;
                                }
                                ++$currGIndex;
                            }
                        }
                    }
                }
            }
        }
        foreach ($gifts as $key => $v) {
             $params['orderGoods'][] = $v;
        }
    }

    /**
     * 用户确认收货
     */
    public function afterUserReceive($params){
        $orderId = $params['orderId'];
        $order = Db::name('orders')->where('orderId',$orderId)->find();
        $orderGoods = Db::name('order_goods')->where('orderId',$orderId)->select();
        //把满就送的筛选出来
        $rewards = [];
        $favourableJson = [];
        foreach ($orderGoods as $key => $v) {
           if($v['promotionJson']!=''){
              $promotionJson = json_decode($v['promotionJson'],true);
              if($promotionJson['orderCode']=='reward'){
                  $promotionJson['extraJson'] = json_decode($promotionJson['extraJson'],true);
                  if(!in_array($promotionJson['extraJson']['rewardId'],$rewards)){
                      $rewards[] = $promotionJson['extraJson']['rewardId'];
                      if($promotionJson['extraJson']['favourableJson']['chk3']){
                          $favourableJson[] = $promotionJson['extraJson']['favourableJson'];
                      }
                  }
              }
           }
        }
        if(WSTConf('WST_ADDONS.coupon')){
            foreach ($favourableJson as $key => $favourable) {
                $coupon = [];
                $coupon['shopId'] = $favourable['chk3val']['data']['shopId'];
                $coupon['couponId'] = $favourable['chk3val']['data']['couponId'];
                $coupon['userId'] = $order['userId'];
                $coupon['createTime'] = date('Y-m-d H:i:s');
                Db::name('coupon_users')->insert($coupon);
                //发送一条用户信息
                $content = WSTLang('reward_get_coupons_tips',[$order['orderNo'],$favourable['chk3val']['text']]);
                WSTSendMsg($order['userId'],$content,['from'=>1,'dataId'=>$orderId]);
            }
        }
    }

    /**
     * 线下收银新增订单商品前执行
     */
    public function offlineBeforeInsertOrderGoods($params){
        $order = model('common/orders')->get($params['orderId']);
        $carts = $params['carts']['carts'][$order['shopId']];
        $gifts = [];

        $hasCoupon = false;
        foreach ($params['orderGoods'] as $_k => $_v) {
            $params['orderGoods'][$_k]['rewardVal'] = 0;
            if(!$hasCoupon && isset($_v['couponVal']))$hasCoupon = true;
        }

        foreach ($carts['list'] as $key => $goods) {
            if(!empty($goods['promotion']) && $goods['promotion']['type']=='reward'){
                $rewardCartIds = [];//一同参与活动的商品
                $rewardResult = []; //应该享受的优惠
                $rewardText = '';   //优惠文字描述
                $rewardMoney = 0;//优惠应该减免的金额
                if(isset($goods['rewardCartIds'])){
                    $rewardCartIds = $goods['rewardCartIds'];

                    if(empty($goods['rewardResult']))continue;//没有获得优惠就跳过
                    $rewardResult = $goods['rewardResult'];
                    $rewardText = $goods['rewardText'];
                    $rewardMoney = $goods['rewardMoney'];

                    //将cartId转换成商品ID
                    $promotionGoodsIds = [];
                    foreach ($carts['list'] as $gkey => $gv) {
                        if(in_array($gv['goodsId']."_".$gv['goodsSpecId'],$rewardCartIds))$promotionGoodsIds[] = $gv['goodsId'];
                    }
                    //如果有送赠品则加入赠品
                    $favourable = $rewardResult['favourableJson'];
                    if($favourable['chk1']){
                        $gifts[] = [
                                      'orderId'=>$params['orderId'],
                                      'goodsId'=>$favourable['chk1val']['data']['goodsId'],
                                      'goodsNum'=>1,
                                      'goodsPrice'=>0,
                                      //'costPrice'=>0,
                                      'goodsSpecId'=>$favourable['chk1val']['data']['goodsSpecId'],
                                      'goodsSpecNames'=>$favourable['chk1val']['data']['goodsSpecNames'],
                                      'goodsName'=>$favourable['chk1val']['text'],
                                      'goodsImg'=>$favourable['chk1val']['data']['goodsImg'],
                                      'commissionRate'=> 0,
                                      'goodsCode'=>'gift',
                                      'goodsType'=>$favourable['chk1val']['data']['goodsType'],
                                      //'goodsBuyNum'=>1,
                                      'extraJson'=>'',
                                      'promotionJson'=>''
                                   ];
                        if((int)WSTConf('CONF.isOpenScorePay')==1){
                            // 有开启积分支付则存在以下字段
                            $gItem['useScoreVal'] = 0;
                            $gItem['scoreMoney'] = 0;
                        }
                        if(WSTConf('CONF.isOrderScore')==1){
                            // 有开启下单获得积分则存在以下字段
                            $gItem['getScoreVal'] = 0;
                            $gItem['getScoreMoney'] = 0;
                        }
                        if($hasCoupon)$gItem['couponVal']=0;
                    }
                    //准备好数据，给每个相关的商品都填上
                    $extraJson = ['orderCode'=>'reward',
                                  'text'=>$rewardText,
                                  'promotionMoney'=>$rewardMoney,
                                  'promotionGoodsIds'=>$promotionGoodsIds,
                                  'extraJson'=>json_encode($rewardResult)
                                 ];
                    foreach ($params['orderObj']['orderTotalGoods'] as $okey => $ov) {
                        if(in_array($ov['goodsId'],$promotionGoodsIds))$params['orderObj']['orderTotalGoods'][$okey]['promotionJson'] = json_encode($extraJson);
                    }
                }
            }
        }
        foreach ($gifts as $key => $v) {
             $params['orderObj']['orderTotalGoods'][] = $v;
        }
    }

    /**
     * 线下收银订单完成支付前执行
     */
    public function offlineAfterOrderPayComplete($params){
        $orderId = $params['orderId'];
        $order = Db::name('orders')->where('orderId',$orderId)->find();
        if($order['userId']>0){
            $orderGoods = Db::name('order_goods')->where('orderId',$orderId)->select();
            //把满就送的筛选出来
            $rewards = [];
            $favourableJson = [];
            foreach ($orderGoods as $key => $v) {
               if($v['promotionJson']!=''){
                  $promotionJson = json_decode($v['promotionJson'],true);
                  if($promotionJson['orderCode']=='reward'){
                      $promotionJson['extraJson'] = json_decode($promotionJson['extraJson'],true);
                      if(!in_array($promotionJson['extraJson']['rewardId'],$rewards)){
                          $rewards[] = $promotionJson['extraJson']['rewardId'];
                          if($promotionJson['extraJson']['favourableJson']['chk3']){
                              $favourableJson[] = $promotionJson['extraJson']['favourableJson'];
                          }
                      }
                  }
               }
            }
            if(WSTConf('WST_ADDONS.coupon')){
                foreach ($favourableJson as $key => $favourable) {
                    $coupon = [];
                    $coupon['shopId'] = $favourable['chk3val']['data']['shopId'];
                    $coupon['couponId'] = $favourable['chk3val']['data']['couponId'];
                    $coupon['userId'] = $order['userId'];
                    $coupon['createTime'] = date('Y-m-d H:i:s');
                    Db::name('coupon_users')->insert($coupon);
                    //发送一条用户信息
                    $content = WSTLang('reward_get_coupons_tips',[$order['orderNo'],$favourable['chk3val']['text']]);
                    WSTSendMsg($order['userId'],$content,['from'=>1,'dataId'=>$orderId]);
                }
            }
        }
    }

    /**
     * 线下收银查询购物车后执行
     */
    public function offlineAfterQueryCarts($params){
        if($params['carts']['carts']){

            $userId = (int)$params['carts']["userId"];
            foreach ($params['carts']['carts'] as $skey => $shop) {
                foreach ($shop['list'] as $key => $v) {
                    //如果存在商品优惠活动则不需要继续
                    if(!empty($v['promotion']))continue;
                    //获取符合条件的优惠活动
                    $promotion = $this->getAvailableRewards($skey,$v['goodsId']);

                    if(!empty($promotion)){
                        if($promotion['useObjects']==0 && empty($params['carts']['carts'][$skey]['promotion'])){
                            $params['carts']['carts'][$skey]['promotion']['data'] = $promotion;
                            $params['carts']['carts'][$skey]['promotion']['type'] = 'reward';
                        }
                        $params['carts']['carts'][$skey]['list'][$key]['promotion']['data'] = $promotion;
                        $params['carts']['carts'][$skey]['list'][$key]['promotion']['type'] = 'reward';
                    }
                }

                //避免多个活动中有多个全店适用的活动
                if(!empty($params['carts']['carts'][$skey]['promotion'])){
                    foreach ($shop['list'] as $key => $v) {
                        $params['carts']['carts'][$skey]['list'][$key]['promotion']['data'] = $params['carts']['carts'][$skey]['promotion']['data'];
                        $params['carts']['carts'][$skey]['list'][$key]['promotion']['type'] = 'reward';
                    }
                }
                //对商品按活动进行归类排序
                usort($params['carts']['carts'][$skey]['list'],'self::sortRewardGoods');
                //对商品进行分类标记
                $rewardId = 0;//用于标记优惠活动的第一个商品ID
                $rewardAllGoodsIds = [];//用于标记优惠活动
                //以活动的第一个商品为key，收集和他同一个活动的其他商品的id
                foreach ($params['carts']['carts'][$skey]['list'] as $bkey => $bgoods) {
                    if(!empty($bgoods['promotion']) && $bgoods['promotion']['type']=='reward'){
                        if($rewardId!=$bgoods['promotion']['data']['rewardId']){
                                $rewardId = $bgoods['promotion']['data']['rewardId'];
                        }
                        $rewardAllGoodsIds[$rewardId][] = $bgoods['goodsId']."_".(int)$bgoods['goodsSpecId'];
                    }else{
                        $rewardId = 0;
                    }
                }
                //把收集到的同一个活动的商品ID集合放到第一个商品中
                $rewardId = 0;
                foreach ($params['carts']['carts'][$skey]['list'] as $bkey => $bgoods) {
                    if(!empty($bgoods['promotion']) && $bgoods['promotion']['type']=='reward'){
                        if($rewardId!=$bgoods['promotion']['data']['rewardId']){
                            $rewardId = $bgoods['promotion']['data']['rewardId'];
                            $params['carts']['carts'][$skey]['list'][$bkey]['rewardCartIds'] = $rewardAllGoodsIds[$rewardId];
                        }
                    }
                }
                $promotionDes = isset($params['carts']['carts'][$skey]['promotionDes'])?$params['carts']['carts'][$skey]['promotionDes']:[];
                foreach ($params['carts']['carts'][$skey]['list'] as $bkey => $bgoods) {
                    //没有优惠活动 或者 优惠活动不是满就送的跳过
                    if(empty($bgoods['promotion']) || $bgoods['promotion']['type']!='reward')continue;
                    //把活动优惠的结果放到活动的第一个商品上
                    if(isset($bgoods['rewardCartIds'])){
                        $rewardMoney = 0;
                        foreach ($params['carts']['carts'][$skey]['list'] as $tkey => $tgoods){
                             if(in_array($tgoods['goodsId']."_".(int)$tgoods['goodsSpecId'],$bgoods['rewardCartIds'])){
                                  $rewardMoney = $rewardMoney + $tgoods['shopPrice'] * $tgoods['cartNum'];
                             }
                        }
                        //看下计算出来的总金额落在哪个优惠范围内
                        $favourables = $bgoods['promotion']['data']['json'];
                        $params['carts']['carts'][$skey]['list'][$bkey]['rewardResult'] = [];
                        $params['carts']['carts'][$skey]['list'][$bkey]['rewardMoney'] = 0;
                        $params['carts']['carts'][$skey]['list'][$bkey]['rewardGoodsMoney'] = $rewardMoney;

                        for($fkey = count($favourables)-1;$fkey>=0;$fkey--) {
                            if($rewardMoney>=$favourables[$fkey]['orderMoney']){
                                //保存优惠内容-下单时用到
                                $favourableJson = $favourables[$fkey]['favourableJson'];
                                $params['carts']['carts'][$skey]['list'][$bkey]['rewardResult'] = $favourables[$fkey];
                                $params['carts']['carts'][$skey]['list'][$bkey]['rewardMoney'] = 0;
                                //获取优惠文字-用于显示
                                $favourableTxt = [];
                                if($favourableJson['chk0']){
                                    $txt = lang('reward_full_reduction',[$favourables[$fkey]['orderMoney'],$favourableJson['chk0val']]);
                                    $favourableTxt[] = $txt;
                                    $promotionDes[] = $txt;
                                    $params['carts']['carts'][$skey]['list'][$bkey]['rewardMoney'] = $favourableJson['chk0val'];
                                    //记录到店铺里边，现在活动要优惠多少
                                    $params['carts']['carts'][$skey]['promotionMoney'] += $favourableJson['chk0val'];
                                    $params['carts']['promotionMoney'] += $favourableJson['chk0val'];
                                }
                                if($favourableJson['chk1']){
                                    $txt = lang('reward_giving_gift',[$favourableJson['chk1val']['text']]);
                                    $favourableTxt[] = $txt;
                                    $promotionDes[] = $txt;
                                }
                                if($favourableJson['chk2']){
                                    $favourableTxt[] = lang('reward_free_shipping');
                                    //记录到店铺里要免邮费
                                    $params['carts']['carts'][$skey]['isFreeShipping'] = true;
                                }
                                if($favourableJson['chk3'] && !empty($favourableJson['chk3val'])){
                                    $txt = lang('reward_send_coupons',[$favourableJson['chk3val']['text']]);
                                    $favourableTxt[] = $txt;
                                    if($userId>0)$promotionDes[] = $txt;
                                }
                                $params['carts']['carts'][$skey]['list'][$bkey]['rewardText'] = implode('、',$favourableTxt);
                                break;
                            }
                        }
                    }
                }
                 $params['carts']['carts'][$skey]['promotionDes'] = $promotionDes;
            }
        }
    }
}
