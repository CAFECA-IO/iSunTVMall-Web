<?php
namespace addons\coupon\model;
use think\addons\BaseModel as Base;
use addons\coupon\validate\Coupons as Validate;
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
 * 优惠券接口
 */
class Coupons extends Base{
	protected $pk = 'couponId';
	/***
     * 安装插件
     */
    public function installMenu(){
    	Db::startTrans();
		try{
			$hooks = ['homeDocumentGoodsPropDetail','mobileDocumentGoodsPropDetail','wechatDocumentGoodsPropDetail',
			          'afterQueryGoods','afterQueryShops','afterQueryCarts','afterCalculateCartMoney','beforeInsertOrder','homeDocumentCartShopPromotion',
			          'mobileDocumentCartShopPromotion','wechatDocumentCartShopPromotion','adminDocumentOrderSummaryView','homeDocumentOrderSummaryView','shopDocumentOrderSummaryView',
			          'mobileDocumentOrderSummaryView','wechatDocumentOrderSummaryView','mobileDocumentUserIndexTools','wechatDocumentUserIndexTools',
			          'homeDocumentSettlementShopSummary','mobileDocumentUserIndexTerm','wechatDocumentUserIndexTerm','offlineBeforeOrderPay','offlineAfterOrderPayComplete','offlineAfterQueryCarts','shopDocumentGiveUserCouponButton','shopDocumentGiveUserCoupon','mobileDocumentIndex','wechatDocumentIndex'];
			$this->bindHoods("Coupon", $hooks);
			$now = date("Y-m-d H:i:s");
            //商家中心
            $homeMenuLangParams = [
                1=>['menuName_01'=>'我的優惠券','menuName_02'=>'優惠券'],
                2=>['menuName_01'=>'我的优惠券','menuName_02'=>'优惠券'],
                3=>['menuName_01'=>'My coupon','menuName_02'=>'Coupon'],
            ];
            $homeMenuIds = [];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>77,"menuUrl"=>"/addon/coupon-shops-index","menuOtherUrl"=>"/addon/coupon-shops-edit,/addon/coupon-shops-pageQuery,/addon/coupons-shops-toEdit,/addon/coupon-shops-del,/addon/coupon-shops-coupons,/addon/shops/pageQueryByCoupons,/addon/shops/toStat,/addon/shops/stat,/addon/shops/toGive,/addon/shops/toGiveCoupon,/addons/shops/searchOrder,/addon/shops/searchMemberGroupUsers","menuType"=>1,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"coupon"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'01'];
            //用户中心
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>10,"menuUrl"=>"addon/coupon-users-index","menuOtherUrl"=>"addon/coupon-users-pageQuery","menuType"=>0,"isShow"=>1,"menuSort"=>3,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"coupon"]);
            $homeMenuIds[] = ['homeMenuId'=>$homeMenuId,'code'=>'02'];
            $datas = [];
            for($i=0;$i<count($homeMenuIds);$i++){
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['menuId'] = $homeMenuIds[$i]['homeMenuId'];
                    $data['langId'] = $v['id'];
                    $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName_'.$homeMenuIds[$i]['code']];
                    $datas[] = $data;
                }
            }
            Db::name('home_menus_langs')->insertAll($datas);
            $this->addNavMenu();
			$this->addMobileBtn();
			installSql("coupon");
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
			$hooks = ['homeDocumentGoodsPropDetail','mobileDocumentGoodsPropDetail','wechatDocumentGoodsPropDetail',
			          'afterQueryGoods','afterQueryShops','afterQueryCarts','afterCalculateCartMoney','beforeInsertOrder','homeDocumentCartShopPromotion',
			          'mobileDocumentCartShopPromotion','wechatDocumentCartShopPromotion','adminDocumentOrderSummaryView','homeDocumentOrderSummaryView','shopDocumentOrderSummaryView',
			          'mobileDocumentOrderSummaryView','wechatDocumentOrderSummaryView','mobileDocumentUserIndexTools','wechatDocumentUserIndexTools',
			          'homeDocumentSettlementShopSummary','mobileDocumentUserIndexTerm','wechatDocumentUserIndexTerm','offlineBeforeOrderPay','offlineAfterOrderPayComplete','offlineAfterQueryCarts','shopDocumentGiveUserCouponButton','shopDocumentGiveUserCoupon','mobileDocumentIndex','wechatDocumentIndex'];
			$this->unbindHoods("Coupon", $hooks);
            $homeMenuIds = Db::name('home_menus')->where(["menuMark"=>"coupon"])->column('menuId');
            Db::name('home_menus')->where(["menuMark"=>"coupon"])->delete();
            Db::name('home_menus_langs')->where([['menuId','in',$homeMenuIds]])->delete();
			uninstallSql("coupon");//传入插件名
            $this->delNavMenu();
			$this->delMobileBtn();
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
			Db::name('home_menus')->where(["menuMark"=>"coupon"])->update(["isShow"=>$isShow]);
			Db::name('navs')->where(["navUrl"=>"addon/coupon-coupons-index.html"])->update(["isShow"=>$isShow]);
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

    public function addNavMenu(){
        $navData = [
            'navType'=>0,
            'navUrl'=>'addon/coupon-coupons-index.html',
            'isShow'=>1,
            'isOpen'=>0,
            'navSort'=>0,
            'createTime'=>date('Y-m-d H:i:s')
        ];
        $navId = Db::name('navs')->insertGetId($navData);
        $datas = [];
        $langParams = [
            1=>['navTitle'=>'領券中心'],
            2=>['navTitle'=>'领券中心'],
            3=>['navTitle'=>'Coupon'],
        ];
        foreach (WSTSysLangs() as $key => $v) {
            $data = [];
            $data['navId'] = $navId;
            $data['langId'] = $v['id'];
            $data['navTitle'] = $langParams[$v['id']]['navTitle'];
            $datas[] = $data;
        }
        Db::name('navs_langs')->insertAll($datas);
    }

	public function addMobileBtn(){
        $langParams = [
            1=>['btnName'=>'領券中心'],
            2=>['btnName'=>'领券中心'],
            3=>['btnName'=>'Coupon Center'],
        ];
        $datas = [];
        $data = array();
        $data["btnSrc"] = 0;
        $data["btnUrl"] = "addon/coupon-coupons-moindex.html";
        $data["btnImg"] = "addons/coupon/view/images/logo.png";
        $data["addonsName"] = "Coupon";
        $data["btnSort"] = 6;
        $btnId = Db::name('mobile_btns')->insertGetId($data);
        foreach (WSTSysLangs() as $key => $v) {
            $data = [];
            $data['btnId'] = $btnId;
            $data['langId'] = $v['id'];
            $data['btnName'] = $langParams[$v['id']]['btnName'];
            $datas[] = $data;
        }
        Db::name('mobile_btns_langs')->insertAll($datas);

		// app端
		if(WSTDatas('ADS_TYPE',4)){
            $datas = [];
            $data = array();
            $data["btnSrc"] = 3;
            $data["btnUrl"] = "wst://Coupon";
            $data["btnImg"] = "addons/coupon/view/images/logo.png";
            $data["addonsName"] = "Coupon";
            $data["btnSort"] = 5;
            $btnId = Db::name('mobile_btns')->insertGetId($data);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['btnId'] = $btnId;
                $data['langId'] = $v['id'];
                $data['btnName'] = $langParams[$v['id']]['btnName'];
                $datas[] = $data;
            }
            Db::name('mobile_btns_langs')->insertAll($datas);
		}
	}

    public function delNavMenu(){
        $navId = Db::name('navs')->where(['navUrl'=>'addon/coupon-coupons-index.html'])->value('id');
        Db::name('navs')->where(['navUrl'=>'addon/coupon-coupons-index.html'])->delete();
        Db::name('navs_langs')->where(['navId'=>$navId])->delete();
    }

	public function delMobileBtn(){
        $btnIds =  Db::name('mobile_btns')->where(["addonsName"=>"Coupon"])->column('id');
        Db::name('mobile_btns')->where(["addonsName"=>"Coupon"])->delete();
        Db::name('mobile_btns_langs')->where([['btnId','in',$btnIds]])->delete();
	}
    /**
     * 获取优惠券信息
     */
	public function getById($id = 0,$sId=0){
		$id = ($id>0)?$id:(int)input('id/d');
		$shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$couppn = $this->where(['couponId'=>$id,'dataFlag'=>1,'shopId'=>$shopId])->find();
		$couppn['goods'] = [];
		//判断是否需要加载商品信息
		if($couppn['useObjects']==1){
			$couppn['goods'] = Db::name('coupon_goods')->alias('cg')
			       ->join('__GOODS__ g','g.goodsId=cg.goodsId and g.isSale=1 and g.dataFlag=1 and g.goodsStatus=1','inner')
                   ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
			       ->where('cg.couponId',$id)
			       ->field('goodsImg,gl.goodsName,g.goodsId,marketPrice,shopPrice,goodsType')
			       ->select();
		}
		return $couppn;
	}
    /**
     * 获取优惠券信息
     */
	public function getByView($id){
		$id = ($id>0)?$id:(int)input('id/d');
		return $this->alias('c')->join('__SHOPS__ s','c.shopId=s.shopId')
		       ->where(['c.couponId'=>$id,'c.dataFlag'=>1])
		       ->field('c.*,s.shopName')
		       ->find();
	}
	/**
	 * 获取优惠券下边的商品列表
	 */
	public function pageQueryByCouponGoods(){
		//查询条件
		$keyword = input('keyword');
		$where = [];
		if($keyword!='')$where[] = ['goodsName','like','%'.$keyword.'%'];
		//排序条件
		$orderBy = input('condition/d',0);
		$orderBy = ($orderBy>=0 && $orderBy<=4)?$orderBy:0;
		$order = (input('desc/d',0)==1)?1:0;
		$pageBy = ['saleNum','shopPrice','visitNum','saleTime'];
		$pageOrder = ['desc','asc'];
		$couponId = (int)input('couponId/d',0);
		$coupon = $this->where(['couponId'=>$couponId,'dataFlag'=>1])->find();
		if($coupon['useObjects']==1){
	        $rs = Db::name('coupon_goods')->alias('cg')
				     ->join('__GOODS__ g','g.goodsId=cg.goodsId and g.isSale=1 and g.dataFlag=1 and g.goodsStatus=1','inner')
                     ->join('__GOODS_SCORES__ gs','gs.goodsId = g.goodsId','left')
				     ->where('cg.couponId',$couponId)
				     ->where($where)
				     ->field('goodsImg,goodsName,g.goodsId,marketPrice,shopPrice,goodsType,appraiseNum,saleNum,gs.totalScore,gs.totalUsers')
				     ->order($pageBy[$orderBy]." ".$pageOrder[$order].",goodsId asc")
                     ->paginate(input('pagesize/d'))->toArray();
		}else{
			$rs = Db::name('goods')
                    ->alias('g')
                    ->join('__GOODS_SCORES__ gs','gs.goodsId = g.goodsId','left')
			         ->where('isSale=1 and dataFlag=1 and goodsStatus=1 and g.shopId='.(int)$coupon['shopId'])
			         ->where($where)
				     ->field('goodsImg,goodsName,g.goodsId,marketPrice,shopPrice,goodsType,appraiseNum,saleNum,gs.totalScore,gs.totalUsers')
				     ->order($pageBy[$orderBy]." ".$pageOrder[$order].",goodsId asc")
                     ->paginate(input('pagesize/d'))->toArray();
		}
        foreach($rs['data'] as $k=>$v){
            $rs['data'][$k]['praiseRate'] = ($v['totalScore']>0)?(sprintf("%.2f",$v['totalScore']/($v['totalUsers']*15))*100).'%':'100%';
        }
		return $rs;
	}

    /**
     * 查询商品
     */
    public function searchGoods($sId=0){
    	$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
    	$shopCatId1 = (int)input('post.shopCatId1');
    	$shopCatId2 = (int)input('post.shopCatId2');
    	$goodsName = input('post.goodsName');
    	$where =[];
    	$where[] =['goodsStatus','=',1];
    	$where[] =['dataFlag','=',1];
    	$where[] =['isSale','=',1];
    	$where[] =['goodsType','=',0];
    	$where[] =['shopId','=',$shopId];
    	if($shopCatId1>0)$where[] =['shopCatId1','=',$shopCatId1];
    	if($shopCatId2>0)$where[] =['shopCatId2','=',$shopCatId2];
    	if($goodsName !='')$where[] = ['gl.goodsName', 'like', '%'.$goodsName.'%'];
    	$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->where($where)->field('gl.goodsName,g.goodsId,marketPrice,shopPrice,goodsSn,goodsImg')->select();
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
    	$where['isSale'] = 1;
    	$where['shopId'] = $shopId;
    	$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->where($where)->field('gl.goodsName,g.goodsId')->order('goodsName asc')->select();
        return WSTReturn('',1,$rs);
    }

    /**
     * 新增优惠前
     */
    public function add($sId=0){
    	$data = input('post.');
    	unset($data['couponId']);
    	$shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
    	$goodsIds = explode(',',$data['useObjectIds']);
    	$validate = new Validate;
        if(!$validate->check($data)){
        	return WSTReturn($validate->getError());
		}
		if(strtotime($data['startDate']) >= strtotime($data['endDate']))return WSTReturn(lang('coupon_time_tips'));
        $goods = [];
        if($data['useObjects']==1){
	        $goods = Db::name('goods')->where([['goodsId','in',$goodsIds],['shopId','=',$shopId],['isSale','=',1],['goodsStatus','=',1],['dataFlag','=',1]])
	                   ->field('goodsId,goodsCatIdPath')->select();
	        if(empty($goods))return WSTReturn(lang('coupon_select_suit_goods'));
	    }
        $data['shopId'] = $shopId;
        $data['createTime'] = date('Y-m-d H:i:s');
        Db::startTrans();
		try{
	    	$result = $this->allowField(true)->save($data);
	    	if(false !== $result){
	    		if($data['useObjects']==1){
		    		$goodsCatIds = [];
		    		//保存优惠券适用的商品
		    		$arr = [];
		            for($i=0;$i<count($goods);$i++){
			    		$cgoods = [];
			    		$cgoods['goodsId'] = $goods[$i]['goodsId'];
		                $cgoods['couponId'] = $this->couponId;
		                $arr[] = $cgoods;
		                $goodsCatId = explode('_',$goods[$i]['goodsCatIdPath']);
		                if(!in_array((int)$goodsCatId[0],$goodsCatIds))$goodsCatIds[] = (int)$goodsCatId[0];
			    	}
			    	Db::name('coupon_goods')->insertAll($arr);
		    		//保存优惠券涉及的分类
		    		$arr = [];
		    		foreach ($goodsCatIds as $key => $v) {
		    			$cgoods = [];
		    			$cgoods['catId'] = $v;
		    			$cgoods['shopId'] = $shopId;
		                $cgoods['couponId'] = $this->couponId;
		                $arr[] = $cgoods;
		    		}
		    		Db::name('coupon_cats')->insertAll($arr);
		    	}else{
		    		//获取所有分类
		    		$cats = Db::name('goods_cats')->where(['dataFlag'=>1,'parentId'=>0])->field('catId')->select();
                    $arr = [];
		    		foreach ($cats as $key => $v) {
		    			$cgoods = [];
		    			$cgoods['catId'] = $v['catId'];
		    			$cgoods['shopId'] = $shopId;
		                $cgoods['couponId'] = $this->couponId;
		                $arr[] = $cgoods;
		    		}
		    		Db::name('coupon_cats')->insertAll($arr);
		    	}
	    	}
	    	Db::commit();
	    	return WSTReturn(lang('coupon_operation_success'),1);
	    }catch (\Exception $e) {
	    	echo $e;
	 		Db::rollback();
	  		return WSTReturn(lang('coupon_operation_fail'));
	   	}
    }
    /**
     * 编辑优惠券
     */
    public function edit($sId=0){
        $data = input('post.');
        $shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
    	$goodsIds = explode(',',$data['useObjectIds']);
    	$validate = new Validate;
        if(!$validate->check($data)){
        	return WSTReturn($validate->getError());
		}
		if(strtotime($data['startDate']) >= strtotime($data['endDate']))return WSTReturn(lang('coupon_time_tips'));
        $goods = [];
        if($data['useObjects']==1){
	        $goods = Db::name('goods')->where([['goodsId','in',$goodsIds],['shopId','=',$shopId],['isSale','=',1],['goodsStatus','=',1],['dataFlag','=',1]])
	                   ->field('goodsId,goodsCatIdPath')->select();
	        if(empty($goods))return WSTReturn(lang('coupon_select_suit_goods'));
	    }else{
            $data['useObjectIds'] = '';
        }
        $couponNum = $this->where(['couponId'=>$data['couponId'],'shopId'=>$shopId])->find();
        if(empty($couponNum))return WSTReturn(lang('coupon_invalid_record'));
        if($data['couponNum']<$couponNum['couponNum'])return WSTReturn(lang('coupon_coupon_num_limit_tips'));
        $couponId = $data['couponId'];
        WSTUnset($data,'shopId,createTime,dataFlag,couponId');
        Db::startTrans();
		try{
	    	$result = $this->allowField(true)->update($data,['couponId'=>$couponId,'shopId'=>$shopId]);
	    	if(false !== $result){
	    		Db::name('coupon_goods')->where('couponId',$couponId)->delete();
	    		Db::name('coupon_cats')->where('couponId',$couponId)->delete();
	    		if($data['useObjects']==1){
	    			$goodsCatIds = [];
	    			//保存优惠券适用的商品
		    		$arr = [];
		            for($i=0;$i<count($goods);$i++){
			    		$cgoods = [];
			    		$cgoods['goodsId'] = $goods[$i]['goodsId'];
		                $cgoods['couponId'] = $couponId;
		                $arr[] = $cgoods;
		                $goodsCatId = explode('_',$goods[$i]['goodsCatIdPath']);
		                if(!in_array((int)$goodsCatId[0],$goodsCatIds))$goodsCatIds[] = (int)$goodsCatId[0];
			    	}
			    	Db::name('coupon_goods')->insertAll($arr);
			    	//保存优惠券涉及的分类
		    		$arr = [];
		    		foreach ($goodsCatIds as $key => $v) {
		    			$cgoods = [];
		    			$cgoods['catId'] = $v;
		    			$cgoods['shopId'] = $shopId;
		                $cgoods['couponId'] = $couponId;
		                $arr[] = $cgoods;
		    		}
		    		Db::name('coupon_cats')->insertAll($arr);
			    }else{
		    		//获取所有分类
		    		$cats = Db::name('goods_cats')->where(['dataFlag'=>1,'parentId'=>0])->field('catId')->select();
                    $arr = [];
		    		foreach ($cats as $key => $v) {
		    			$cgoods = [];
		    			$cgoods['catId'] = $v['catId'];
		    			$cgoods['shopId'] = $shopId;
		                $cgoods['couponId'] = $couponId;
		                $arr[] = $cgoods;
		    		}
		    		Db::name('coupon_cats')->insertAll($arr);
		    	}
	    	}
	    	Db::commit();
	    	return WSTReturn(lang('coupon_operation_success'),1);
	    }catch (\Exception $e) {
	 		Db::rollback();
	  		return WSTReturn(lang('coupon_operation_fail'));
	   	}
    }

    /**
     * 删除优惠券
     */
    public function del($sId=0){
    	$shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
    	$id = (int)input('id/d',0);
    	$result = $this->where(['couponId'=>$id,'shopId'=>$shopId])->update(['dataFlag'=>-1]);
    	if(false !== $result){
    		Db::name('coupon_users')->where('couponId',$id)->delete();
    		Db::name('coupon_goods')->where('couponId',$id)->delete();
    		Db::name('coupon_cats')->where('couponId',$id)->delete();
    	}
    	return WSTReturn(lang('coupon_operation_success'),1);
    }

    /**
     * 商家-优惠券列表
     */
    public function pageQueryByShop($sId=0){
    	$useCondition = (int)input('useCondition/d',-1);
    	$couponType = (int)input('couponType/d',-1);
    	$isTrue =(int)input('isTrue',-1);//判断试过有效,1为有效,0为过期,-1则无调用
        $time = date('Y-m-d');// 当天有效
        $condition = '';
        if($isTrue!=-1){
            if($isTrue==1){
                $condition = " endDate >= '{$time}'";
            }else{
                $condition = " endDate < '{$time}'";
            }
        }
        $shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $where = ['dataFlag'=>1,'shopId'=>$shopId];
        if(in_array($useCondition,[0,1]))$where['useCondition'] = $useCondition;
        if($couponType>0)$where['couponType'] = $couponType;
    	$page =  $this->where($where)
                      ->where($condition)
                     ->order('createTime desc')
                     ->paginate(input('limit/d'))->toArray();
        foreach ($page['data'] as $key => $v) {
            $page['data'][$key]['couponStatus'] = ($v['endDate']>=$time)?true:false;
        }
        $page['status'] = 1;
        return $page;
    }

    /**
     * 获取前台优惠券列表
     */
    public function pageCouponQuery($uId=0,$num=0){
        $num = ($num>0)?$num:input('pagesize/d');
    	$catId = (int)input('catId/d',-1);
    	$useCondition = (int)input('useCondition/d',-1);
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $where[] = ['c.dataFlag','=',1];
        $where[] = ['c.couponType','=',2];
        $where[] = ['endDate','>=',date('Y-m-d')];
        if(in_array($useCondition,[0,1]))$where[] = ['useCondition','=',$useCondition];
        if($catId<=0){
	    	$page =  Db::name('coupons')->alias('c')
	    	              ->join('__SHOPS__ s','c.shopId=s.shopId and s.dataFlag=1 and s.shopStatus=1')
	    	              ->where($where)
	    	              ->field('shopName,shopImg,c.*')
	                      ->order('c.endDate desc')
                      ->paginate($num)->toArray();
        }else{
        	$where[] = ['cg.catId','=',$catId];
        	$page =  Db::name('coupon_cats')->alias('cg')
	    	              ->join('__COUPONS__ c','cg.couponId=c.couponId')
	    	              ->join('__SHOPS__ s','c.shopId=s.shopId and s.dataFlag=1 and s.shopStatus=1')
	    	              ->where($where)
	    	              ->field('shopName,shopImg,c.*')
	                      ->order('c.endDate desc')
                      ->paginate($num)->toArray();
        }
        $userCoupons = [];
        if($userId>0){
	        $userCoupons = Db::name('coupon_users')->where(['userId'=>$userId])->column('couponId');
	    }
        $time = time();
        foreach ($page['data'] as $key => $v) {
        	$page['data'][$key]['isOut'] = (($v['couponNum']<=$v['receiveNum']) || ($time>WSTStrToTime($v['endDate']." 23:59:59")))?true:false;
            $page['data'][$key]['isReceive'] = ($userId>0)?in_array($v['couponId'],$userCoupons):false;
        }
        return $page;
    }

    /**
     * 领取优惠券
     */
    public function receive($uId=0){
    	$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
    	$couponId = (int)input('couponId/d',0);
    	if($userId==0 || $couponId<=0)return WSTReturn(lang('coupon_receive_fail'));
    	$coupon = $this->get($couponId);
    	if($coupon->dataFlag==-1)return WSTReturn(lang('coupon_receive_fail'));
    	if($coupon->couponNum<=$coupon->receiveNum)return WSTReturn(lang('coupon_receive_finished_tips'));
    	Db::startTrans();
		try{
			//查询用户是否领取过，是否已超过临取数量
			$receiveNum = Db::name('coupon_users')->where(['userId'=>$userId,'couponId'=>$couponId])->count();
			if($coupon->limitNum!=0){
                if($receiveNum>=$coupon->limitNum)return WSTReturn(lang('coupon_receive_upper_limit'));
			}
			$couponUser = [];
            $couponUser['shopId'] = $coupon->shopId;
            $couponUser['couponId'] = $coupon->couponId;
            $couponUser['userId'] = $userId;
            $couponUser['isUse'] = 0;
            $couponUser['createTime'] = date('Y-m-d h:i:s');
            Db::name('coupon_users')->insert($couponUser);
            $coupon->receiveNum = $coupon->receiveNum+1;
            $coupon->save();
            Db::commit();
            $rs = [];
            $rs['status'] = 0;
            $receiveNum = Db::name('coupon_users')->where(['userId'=>$userId,'couponId'=>$couponId])->count();
            if($receiveNum>=$coupon->limitNum)$rs['status'] = 1;
            if($coupon->couponNum<=$coupon->receiveNum)$rs['status'] = -1;
	    	return WSTReturn(lang('coupon_receive_success'),1,$rs);
	    }catch (\Exception $e) {
	 		Db::rollback();
	  		return WSTReturn(lang('coupon_receive_fail'));
	   	}
    }

    /**
     * 获取商品是否有满减券
     */
    public function getGoodsCouponTags($goodsId){
    	$time = date('Y-m-d');
    	//查询是否有针对该商品的优惠券
        $hasCoupon = Db::name('coupon_goods')->alias('cg')
                    ->join('__COUPONS__ c','cg.couponId=c.couponId')
                    ->where([['endDate','>=',$time],['goodsId','=',$goodsId],['dataFlag','=',1]])->count();
        if($hasCoupon>0)return 1;
        //查询一下是否有针对分类的优惠券
        $goods = Db::name('goods')->where('goodsId',$goodsId)->field('goodsCatIdPath,shopId')->find();
        $goodsCatIdPath = explode('_',$goods['goodsCatIdPath']);
        $hasCoupon = Db::name('coupon_cats')->alias('cg')
                       ->join('__COUPONS__ c','cg.couponId=c.couponId')
                       ->where([['endDate','>=',$time],['catId','=',(int)$goodsCatIdPath[0]],['cg.shopId','=',$goods['shopId']],['dataFlag','=',1]])->count();
        if($hasCoupon>0)return 1;
        return 0;
    }

    /**
     * 加载合适的商品优惠券
     */
    public function getCouponsByGoods($uId=0){
    	$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $goodsId = (int)input('goodsId/d',0);
        $shopId = Db::name('goods')->where('goodsId',$goodsId)->value('shopId');
        //获取优惠券列表
        $rs =  Db::name('coupons')->where(['dataFlag'=>1,'shopId'=>$shopId,'couponType'=>2])->order('couponValue asc')->select();
        //获取已领优惠券列表
        $reRs = Db::name('coupon_users')->where('userId',$userId)->column('couponId');
        $coupons = [];
        $time = time();
        foreach ($rs as $key => $v) {
        	$v['isReceive'] = false;
        	//过期的优惠券
        	if($time > WSTStrToTime($v['endDate']." 23:59:59"))continue;
            //指定商品，但又不是本商品的优惠券
            if($v['useObjects']==1){
            	$ids = explode(',',$v['useObjectIds']);
            	if(!in_array($goodsId,$ids))continue;
            }
            if(in_array($v['couponId'],$reRs)){
            	$receiveNum = Db::name('coupon_users')->where(['userId'=>$userId,
            												   'couponId'=>$v['couponId'],
            												   'shopId'=>$shopId])
            										  ->count();
            	if($receiveNum==$v['limitNum'])$v['isReceive'] = true;
            }
            $v['isOut'] = ($v['couponNum']<=$v['receiveNum'])?true:false;
            unset($v['dataFlag'],$v['createTime'],$v['useObjectIds'],$v['useObjects']);
            $coupons[] = $v;
        }
        return WSTReturn('',1,$coupons);
    }

    /**
     * 加载店铺下的优惠券
     */
    public function getCouponsByShop($shopId = 0,$uId=0){
    	$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
    	$shopId = ($shopId>0)?$shopId:(int)input('shopId/d',0);
        //获取优惠券列表
        $rs =  Db::name('coupons')->where(['dataFlag'=>1,'shopId'=>$shopId])->order('couponValue asc')->select();
        //获取已领优惠券列表
        $reRs = Db::name('coupon_users')->where(['userId'=>$userId,'shopId'=>$shopId])->column('couponId');
        $coupons = [];
        $time = time();
        foreach ($rs as $key => $v) {
        	$v['isReceive'] = false;
        	//过期的优惠券
        	if($time > strtotime($v['endDate']." 23:59:59"))continue;
            if(in_array($v['couponId'],$reRs)){
            	$receiveNum = Db::name('coupon_users')->where(['userId'=>$userId,
            												   'couponId'=>$v['couponId'],
            												   'shopId'=>$shopId])
            										  ->count();
            	if($receiveNum==$v['limitNum'])$v['isReceive'] = true;
            }
            unset($v['dataFlag'],$v['createTime'],$v['useObjectIds'],$v['useObjects']);
            $coupons[] = $v;
        }
        return WSTReturn('',1,['coupons'=>$coupons,'receive'=>count($reRs)]);
    }

    /**
     * 加载已领未使用的商品优惠券
     * 1.【指定商品】要商品符合
     * 2.【指定商品】要商品总价符合
     * 3.【店铺通用】订单总价符合
     */
    public function getAvailableCoupons($cartGoods,$shopId,$uId=0){
    	//构造用于比较的数组
    	$carts = ['ids'=>[],'totalMoney'=>0];//存放优惠券里指定的商品id，每个商品的总价,订单总价
    	foreach($cartGoods as $key =>$v){
            $carts['ids'][] = $v['goodsId'];
            $carts[$v['goodsId']]['totalMoney'] = $v['cartNum']*$v['shopPrice'];
            $carts['totalMoney'] += $v['cartNum']*$v['shopPrice'];
    	}
    	$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        //获取该店优惠券列表
        $rs =  Db::name('coupons')->where(['dataFlag'=>1,'shopId'=>$shopId])->order('couponValue asc')->select();
        //获取该店已领未用的优惠券列表
        $reRs = Db::name('coupon_users')->where(['userId'=>$userId,'isUse'=>0,'shopId'=>$shopId])->column('couponId');
        $coupons = [];
        $time = time();
        foreach ($rs as $key => $v) {
        	//过期的优惠券要过滤
        	if($time > strtotime($v['endDate']." 23:59:59"))continue;
            //如果没有领取的优惠券也过滤掉
            if(!in_array($v['couponId'],$reRs))continue;
            //指定商品，但又不是本商品的优惠券要过滤
            if($v['useObjects']==1){
            	$ids = explode(',',$v['useObjectIds']);
            	//判断两个数组是否有交集，没有交集则跳过
            	$intersection = array_intersect($carts['ids'],$ids);
            	if(empty($intersection))continue;
            	//取数组内的交易进行判断金额是否满足，有一个满足的话都通过
            	$isFind = false;
            	foreach ($intersection as $gkey => $goodsId) {
            		//有设置使用条件
                    if($v['useCondition']==1){
                    	if($v['useMoney']<=$carts[$goodsId]['totalMoney']){
	                        $isFind = true;
	                        continue;
	                    }
	            	}else{
	            		$isFind = true;
	                    continue;
	            	}
            	}
            	if(!$isFind)continue;
            }else{
                //商品总价不符合的要过滤
            	if($v['useCondition']==1 && $v['useMoney']>$carts['totalMoney'])continue;
            }
            unset($v['dataFlag'],$v['createTime'],$v['useObjectIds'],$v['useObjects']);
            $coupons[] = $v;
        }
        return $coupons;
    }

    /**
     * 计算订单金额
     */
    public function calculateCartMoney($params){
    	$couponIds = input('couponIds');
        if($couponIds=='')return;
        $couponIds = explode(',',$couponIds);
        $shopCoupons = [];
        foreach ($couponIds as $key => $v) {
            $tmp = explode(':',$v);
            if((int)$tmp[0]<=0 || (int)$tmp[1]<=0)continue;
            $shopCoupons[$tmp[0]] = (int)$tmp[1];
        }
        if(empty($shopCoupons))return;
        $derateMoney = 0;
        //根据优惠券组建店铺优惠券对应数组
        $totalOrderScore = 0;
        foreach ($params['carts']['carts'] as $key => $v) {
            $coupons = $this->getAvailableCoupons($v['list'],$v['shopId'],$params['uId']);
            //校验优惠券是否有效
            $rightCoupon = [];
            foreach ($coupons as $ckey => $cv) {
                if(isset($shopCoupons[$cv['shopId']]) && $cv['couponId']==$shopCoupons[$cv['shopId']])$rightCoupon = $cv;
            }

            if(empty($rightCoupon)){
            	$totalOrderScore += $params['data']['shops'][$key]['orderScore'];
            	continue;
            }
            //计算出店铺可以优惠后的价格
            if($rightCoupon['useCondition']==1){
                if($params['data']['shops'][$key]['oldGoodsMoney']>=$rightCoupon['useMoney']){
                    $derateMoney = $derateMoney + $rightCoupon['couponValue'];
                }
            }else{
                $derateMoney = $derateMoney + $rightCoupon['couponValue'];
            }
            $params['data']['shops'][$key]['goodsMoney'] = WSTPositiveNum($params['data']['shops'][$key]['goodsMoney'] - $derateMoney);
            $params['data']['shops'][$key]['orderScore'] = WSTMoneyGiftScore($params['data']['shops'][$key]['goodsMoney']);
            $totalOrderScore += $params['data']['shops'][$key]['orderScore'];
        }
        $params['data']['totalMoney'] = WSTPositiveNum($params['data']['totalMoney'] - $derateMoney);
        $params['data']['orderScore'] = $totalOrderScore;
    }

    /**
     * 计算虚拟商品购物车金额
     */
    public function calculateVirtualCartMoney($params){
    	$couponIds = input('couponIds');
        if($couponIds=='')return;
        $couponIds = explode(',',$couponIds);
        $shopCoupons = [];
        foreach ($couponIds as $key => $v) {
            $tmp = explode(':',$v);
            if((int)$tmp[0]<=0 || (int)$tmp[1]<=0)continue;
            $shopCoupons[$tmp[0]] = (int)$tmp[1];
        }
        if(empty($shopCoupons))return;
        $derateMoney = 0;
        $totalOrderScore = 0;
        //根据优惠券组建店铺优惠券对应数组
        foreach ($params['carts']['carts'] as $key => $v) {
            $coupons = $this->getAvailableCoupons($v['list'],$v['shopId'],$params['uId']);
            //校验优惠券是否有效
            $rightCoupon = [];
            foreach ($coupons as $ckey => $cv) {
                if($cv['couponId']==$shopCoupons[$cv['shopId']])$rightCoupon = $cv;
            }

            if(empty($rightCoupon)){
            	$totalOrderScore += $params['data']['shops'][$key]['orderScore'];
            	continue;
            }
            //计算出店铺可以优惠后的价格
            if($rightCoupon['useCondition']==1){
                if($params['data']['shops'][$key]['goodsMoney']>=$rightCoupon['useMoney']){
                    $derateMoney = $rightCoupon['couponValue'];
                }
            }else{
                $derateMoney = $rightCoupon['couponValue'];
            }
            $params['data']['shops'][$key]['goodsMoney'] = WSTPositiveNum($params['data']['shops'][$key]['goodsMoney'] - $derateMoney);
            $params['data']['shops'][$key]['orderScore'] = WSTMoneyGiftScore($params['data']['shops'][$key]['goodsMoney']);
            $totalOrderScore += $params['data']['shops'][$key]['orderScore'];
        }
        $params['data']['totalMoney'] = WSTPositiveNum($params['data']['totalMoney']-$derateMoney);
        $params['data']['orderScore'] = $totalOrderScore;
    }

    /**
     * 订单执行前插入
     */
    public function beforeInsertOrder($params){
        $carts = $params['carts'];
		$order = $params['order'];
		$shopOrder = $params['shopOrder'];
        $couponId = (int)input('couponId_'.$order['shopId']);
        $shopCart = $carts['carts'][$order['shopId']];
        $coupon = [];
        foreach ($shopCart['coupons'] as $key => $v) {
            if($couponId==$v['couponId']){
                $coupon = $v;
                break;
            }
		}
		foreach($params['shopOrder']['list'] as $_k => $_v){
			// 商品分摊到的优惠券减免金额
			$params['shopOrder']['list'][$_k]['couponVal'] = 0;
		}
		unset($_k, $_v);
        if(!empty($coupon)){
            //加载未使用的优惠券
            $couponUser = Db::name('coupon_users')->where(['userId'=>$order['userId'],'isUse'=>0,'couponId'=>$coupon['couponId']])->limit(1)->select();
            if(!empty($couponUser)){
				$couponUser = $couponUser[0];
            	Db::name('coupon_users')->where(['id'=>$couponUser['id']])->update(['isUse'=>1,'orderNo'=>$order['orderNo'],'useTime'=>date('Y-m-d H:i:s')]);
                //使用优惠券
				$params['order']['userCouponId'] = $couponUser['id'];
				$gIds = Db::name('coupons')->where('couponId', $coupon['couponId'])->value('useObjectIds');
				$gIds = $gIds==''?[]:explode(',', $gIds);
				if(!empty($gIds)){
					// 累计减免金额
					$cMoney = 0;
					// 最后一项索引值
					$lastOne = count($gIds)-1;

					// 参与满减活动的商品的总金额
					$goodsTotalMoney = 0;
					// 单组商品总金额  [1=>100,3=>400]  ['商品id'=>店铺价格*购买数量]
					$singleCMoneys = [];
					// 计算出符合条件的商品总金额
					foreach($params['shopOrder']['list'] as $_v){
						if(in_array($_v['goodsId'], $gIds)){
							// 累加商品总金额
							$money = $_v['cartNum']*$_v['shopPrice'];
							$goodsTotalMoney += $money;
							$singleCMoneys[$_v['goodsId']] = $money;
						}
					}
					unset($_v);
					// 给符合条件的商品写入减免金额
					foreach($params['shopOrder']['list'] as $_k => $_v){
						if(in_array($_v['goodsId'], $gIds)){
							// 商品分摊到的优惠券减免金额
							if($_k==$lastOne){
								$params['shopOrder']['list'][$_k]['couponVal'] = $coupon['couponValue']-$cMoney;
							}else{
								$val = round($singleCMoneys[$_v['goodsId']], 2);
								$params['shopOrder']['list'][$_k]['couponVal'] = $val/$goodsTotalMoney*$coupon['couponValue'];
								$cMoney += $val;
							}
						}
					}
				}else{
					// 商品分摊到的优惠券减免金额
					// 累计减免金额
					$cMoney = 0;
					// 最后一项索引值
					$lastOne = count($params['shopOrder']['list'])-1;
					foreach($params['shopOrder']['list'] as $_k => $_v){
						// 商品分摊到的优惠券减免金额
						if($_k==$lastOne){
							$params['shopOrder']['list'][$_k]['couponVal'] = $coupon['couponValue']-$cMoney;
						}else{
							$val = round(($_v['cartNum']*$_v['shopPrice'])/$params['shopOrder']['goodsMoney']*$coupon['couponValue'],2);
							$params['shopOrder']['list'][$_k]['couponVal'] = $val;
							$cMoney += $val;
						}
					}
				}
                if($coupon['useCondition']==1){
					// 部分商品可用
					$params['order'] ['userCouponJson'] = json_encode(['text'=>lang('coupon_full_reduction_tips',[$coupon['useMoney'],$coupon['couponValue']]),'money'=>$coupon['couponValue']]);
                }else{
					// 全店通用
					$params['order'] ['userCouponJson'] = json_encode(['text'=>lang('coupon_money_tips',[$coupon['couponValue']]),'money'=>$coupon['couponValue']]);
	            }
                //修改订单信息
                $realTotalMoney = $order['realTotalMoney']-$coupon['couponValue'];
                $params['order']['realTotalMoney'] = ($realTotalMoney>0)?$realTotalMoney:0;
                if(WSTConf('CONF.isOrderScore')==1){
                	$orderScore =  (($params['order']['realTotalMoney'] - $params['order']['deliverMoney'])>0)?($params['order']['realTotalMoney'] - $params['order']['deliverMoney']):0;
					$params['order']['orderScore'] = WSTMoneyGiftScore($orderScore);
				}
                $params['order']['needPay'] = $params['order']['realTotalMoney'];
                if($params['order']['needPay']<=0){
					$params['order']['orderStatus'] = 0;//待发货
					$params['order']['isPay'] = 1;
				}
            }
        }
    }

    /**
     * 用户-优惠券列表
     */
    public function pageQueryByUser($uId=0){
    	$status = (int)input('status/d',0);
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $where = ['c.dataFlag'=>1,'cu.userId'=>$userId];
        $where2 = '';
        if($status==0)$where2 =' isUse=0 and c.endDate>="'.date('Y-m-d').'"';
        if($status==1)$where2 =' isUse=1 ';
        if($status==2)$where2 =' isUse=0 and c.endDate<"'.date('Y-m-d').'"';
    	$page =  $this->alias('c')->join('__COUPON_USERS__ cu','c.couponId=cu.couponId')
    	              ->join('__SHOPS__ s','c.shopId=s.shopId and s.dataFlag=1 and s.shopStatus=1')
	    	          ->where($where)
	    	          ->where($where2)
	    	          ->field('c.*,s.shopName')
                      ->order('c.createTime desc')
                      ->paginate(input('pagesize/d'))->toArray();
        $page['status'] = 1;
        $page['couponStatus'] = $status;
        return $page;
    }

    /**
     * 获取用户
     */
    public function getCouponNumByUser($uId=0){
       $data = [];
       $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
       $where = ['c.dataFlag'=>1,'cu.userId'=>$userId];
       $data['num0'] = $this->alias('c')->join('__COUPON_USERS__ cu','c.couponId=cu.couponId')
    	              ->join('__SHOPS__ s','c.shopId=s.shopId and s.dataFlag=1 and s.shopStatus=1')
	    	          ->where($where)
	    	          ->where(' isUse=0 and c.endDate>"'.date('Y-m-d').'"')
	    	          ->count();
	   $data['num1'] = $this->alias('c')->join('__COUPON_USERS__ cu','c.couponId=cu.couponId')
    	              ->join('__SHOPS__ s','c.shopId=s.shopId and s.dataFlag=1 and s.shopStatus=1')
	    	          ->where($where)
	    	          ->where(' isUse=1 ')
	    	          ->count();
	   $data['num2'] = $this->alias('c')->join('__COUPON_USERS__ cu','c.couponId=cu.couponId')
    	              ->join('__SHOPS__ s','c.shopId=s.shopId and s.dataFlag=1 and s.shopStatus=1')
	    	          ->where($where)
	    	          ->where(' isUse=0 and c.endDate<"'.date('Y-m-d').'" ')
	    	          ->count();
	   return $data;
    }

    /**
     * 更改店铺状态时的处理函数
     */
    public function afterChangeShopStatus($params){
        $shopId = (int)$params['shopId'];
        if($shopId<=0)return;
        $shop = model('common/shops')->get($shopId);
        //店铺状态不正常的话就删除了优惠券
        if($shop->applyStatus==2 && ($shop->dataFlag!=1 || $shop->shopStatus!=1)){
             $this->where('shopId',$shopId)->update(['dataFlag'=>-1]);
        }
    }

    /**
     * 领取的优惠券数
     */
    public function couponsNum($uId=0){
    	$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
    	$rs = Db::name('coupon_users')->alias('cu')->join('__COUPONS__ c','c.couponId=cu.couponId')
    	->where('cu.userId='.$userId.' and cu.isUse=0 and c.endDate>='.date('Y-m-d'))->count();
    	$rs = (int)$rs;
    	return $rs;
    }

    /**
     * 获取优惠券列表
     */
    public function pageQueryByCoupons(){
    	$shopId = (int)session('WST_USER.shopId');
    	$couponId = (int)input('couponId');
    	$isUse = (int)input('isUse');
    	if(!in_array($isUse,[-1,0,1]))$isUse = -1;
    	$where = [];
    	$where[] = ['shopId','=',$shopId];
    	$where[] = ['couponId','=',$couponId];
    	if($isUse!=-1)$where[] = ['isUse','=',$isUse];
    	return Db::name('coupon_users')->alias('c')
    	              ->join('__USERS__ u','u.userId=c.userId')
	    	          ->where($where)
	    	          ->field('u.loginName,c.createTime,c.isUse,useTime,orderNo')
                      ->order('c.createTime desc')
                      ->paginate(input('pagesize/d'))->toArray();
    }

    /**
     * 统计优惠券
     */
    public function stat(){
    	$startDate = input('startDate',date('Y-M-d'));
    	$endDate = input('endDate',date('Y-M-d'));
    	$shopId = (int)session('WST_USER.shopId');
    	$couponId = (int)input('couponId');
    	$where = [];
    	$where[] = ['shopId','=',$shopId];
    	$where[] = ['couponId','=',$couponId];
    	$where[] = ['createTime','>=',$startDate.' 00:00:00'];
    	$where[] = ['createTime','<=',$endDate.' 23:59:59'];
    	$coupon = Db::name('coupon_users')->field('left(createTime,10) createTime,count(id) couponNum')->where($where)->group('left(createTime,10)')->select();

    	$where[] = ['isUse','=',1];
    	$use = Db::name('coupon_users')->field('left(useTime,10) useTime,count(id) couponNum')->where($where)->group('left(useTime,10)')->select();
    	$days = [];
    	$dayCoupon = [];
    	foreach ($coupon as $key => $v) {
    		$days[] = $v['createTime'];
    		$dayCoupon[] = $v['couponNum'];
    	}
    	$useMap = [];
        foreach ($use as $key => $v) {
    		$useMap[$v['useTime']]= $v['couponNum'];
    	}
    	$dayUse = [];
    	foreach ($coupon as $key => $v) {
    		$dayUse[] = (isset($useMap[$v['createTime']]))?$useMap[$v['createTime']]:0;
    	}
    	return WSTReturn('',1,['days'=>$days,'coupon'=>$dayCoupon,'use'=>$dayUse]);
    }



    /**
     * 线下收银新增订单商品前执行
     */
    public function offlineBeforeOrderPay($params){
    	$orderId = (int)$params["order"]["orderId"];
        $shopId = (int)$params["order"]["shopId"];

        $extraJson = json_decode($params["order"]["extraJson"],true);
		$promotionItems = $extraJson["promotionItems"];
        if(isset($promotionItems['1'])){
        	$order = Db::name("orders")->where(["orderId"=>$orderId])->find();
        	$couponId = (int)$promotionItems['1'];
        	$coupon = Db::name('coupons c')->join('__COUPON_USERS__ cu','c.couponId=cu.couponId','inner')
			        	->where(['c.shopId'=>$shopId,'c.couponId'=>$couponId,'c.dataFlag'=>1,'cu.userId'=>$order['userId'],'cu.isUse'=>0])
			        	->find();
			if(!empty($coupon)){
				$vmoney = $params["order"]["totalMoney"]-$coupon["couponValue"];
				$vmoney = ($vmoney>0)?$vmoney:0;
				$realTotalMoney =  $vmoney;
				$needPay =  $vmoney;

				$params["order"]["realTotalMoney"] = $realTotalMoney;
				$params["order"]["needPay"] = $needPay;
				$data = [];
				$data['realTotalMoney'] = $realTotalMoney;
				$data['needPay'] =  $needPay;
				if($needPay==0)$data['isPay'] =  1;
				Db::name("orders")->where(["orderId"=>$orderId])->update($data);
			}
        }

    }


    /**
     * 线下收银订单完成支付前执行
     */
    public function offlineAfterOrderPayComplete($params){
        $orderId = (int)$params["orderId"];
        $order = Db::name("orders")->where(["orderId"=>$orderId])->find();
        $shopId = (int)$order["shopId"];
        $extraJson = json_decode($order["extraJson"],true);
        if(isset($extraJson["promotionItems"]["1"])){
        	$couponId = (int)$extraJson["promotionItems"]["1"];
        	$coupon = Db::name('coupons')->where(['shopId'=>$shopId,'couponId'=>$couponId,'dataFlag'=>1])->find();
        	//加载未使用的优惠券
            $couponUser = Db::name('coupon_users')->where(['userId'=>$order['userId'],'isUse'=>0,'couponId'=>$couponId,'shopId'=>$shopId])->find();
            if(!empty($couponUser)){
            	Db::name('coupon_users')->where(['id'=>$couponUser['id']])->update(['isUse'=>1,'orderNo'=>$order['orderNo'],'useTime'=>date('Y-m-d H:i:s')]);
                //使用优惠券
                $data = [];
                $data["userCouponId"] = $couponUser['id'];
                if($coupon['useCondition']==1){
                    $data['userCouponJson'] = json_encode(['text'=>WSTLang('coupon_full_reduction_tips',[$coupon['useMoney'],$coupon['couponValue']]),'money'=>$coupon['couponValue']]);
                }else{
	                $data['userCouponJson'] = json_encode(['text'=>WSTLang('coupon_money_tips',[$coupon['couponValue']]),'money'=>$coupon['couponValue']]);
	            }

				Db::name("orders")->where(["orderId"=>$orderId])->update($data);
            }
        }
    }

    /**
     * 线下收银查询购物车后执行
     */
    public function offlineAfterQueryCarts($params){
        if($params['carts']['carts']){
            foreach ($params['carts']['carts'] as $key => $v) {
            	$coupons = $this->getAvailableCoupons($v['list'],$v['shopId'],$params['carts']['userId'],1);
                $params['carts']['carts'][$key]['coupons'] = $coupons;
                $items = [];
                foreach ($coupons as $ckey => $coupon) {
                	$item = [];
                	$item["itemId"] = $coupon["couponId"];
                	$item["itemTxt"] = WSTLang('coupon_full_reduction_tips',[$coupon["useMoney"],$coupon["couponValue"]]);
                	$item["itemMoney"] = $coupon["couponValue"];
                	$items[] = $item;
                }
                if(count($items)>0){
                	$params['carts']['carts'][$key]['promotionObjs']["1"] = $items;
                }
            }
        }
    }

    /**
     * 发放优惠券
     */
    public function give(){
        $shopId = (int)session('WST_USER.shopId');
        $couponId = input('couponId');
        $userIds = explode(',',input('ids'));
        $couponNum = count($userIds);
        $coupon = Db::name('coupons')->where(['couponId'=>$couponId])->find();
        // 判断优惠券是否过期
        $time = time();
        if($time > WSTStrToTime($coupon['endDate']." 23:59:59"))return WSTReturn(lang('coupon_over_time_tips'));
        // 优惠券剩余数量
        $couponCount = (int)$coupon['couponNum']-(int)$coupon['receiveNum'];
        if($couponNum>$couponCount)return WSTReturn(lang('coupon_num_less_tips',[$couponCount]));
        $limitNum = $coupon['limitNum'];
        for($i=0;$i<count($userIds);$i++){
            $receiveNum = Db::name('coupon_users')->where(['userId'=>$userIds[$i],'couponId'=>$couponId])->count();
            if($limitNum!=0 && $receiveNum>=$limitNum)return WSTReturn(lang('coupon_user_receive_upper_limit'));
        }
        Db::startTrans();
        try{
            $tpl = WSTMsgTemplates('GIVE_USER_COUPON');
            for($i=0;$i<count($userIds);$i++){
                $data = [
                    'shopId'=>$shopId,
                    'couponId'=>$couponId,
                    'userId'=>$userIds[$i],
                    'isUse'=>0,
                    'createTime'=>date('Y-m-d H:i:s')
                ];
                Db::name('coupon_users')->insert($data);
                //发送一条用户信息
                if( $tpl['tplContent']!='' && $tpl['status']=='1'){
                    $find = ['${MALL_NAME}'];
                    $replace = [WSTConf('CONF.mallName')];
                    WSTSendMsg($userIds[$i],str_replace($find,$replace,$tpl['tplContent']),['from'=>0,'dataId'=>0]);
                }
            }
            $rs = Db::name('coupons')->where(['couponId'=>$couponId])->setInc('receiveNum',$couponNum);
            if(false !== $rs){
                Db::commit();
                return WSTReturn(lang('coupon_operation_success'),1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('coupon_operation_fail'));
        }
    }

    /**
     * 查询订单
     */
    public function searchOrder($sId=0){
        $shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $orderNo = input('post.orderNo');
        $where =[];
        $where[] =['orderNo','=',$orderNo];
        $where[] =['dataFlag','=',1];
        $where[] =['shopId','=',$shopId];
        $rs = Db::name('orders')->where($where)->field('orderNo,userId,userName,userAddress,userPhone,createTime')->find();
        if(!empty($rs)){
            return WSTReturn('',1,$rs);
        }
        return WSTReturn(lang('coupon_no_related_order'));
    }

    /*
     * 查询会员分组下的会员
     */
    public function searchMemberGroupUsers($sId=0){
        $shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $id = (int)input('id');
        $where =[];
        $where[] =['groupId','=',$id];
        $where[] =['shopId','=',$shopId];
        $rs = Db::name('shop_members')
            ->alias('sm')
            ->join('__USERS__ u','u.userId=sm.userId')
            ->field('sm.userId,u.loginName,u.userName')
            ->where($where)->select();
        if(!empty($rs)){
            foreach($rs as $key => $v){
                if($v['userName']=='')$rs[$key]['userName'] = '-';
            }
        }
        return $rs;
    }
}
