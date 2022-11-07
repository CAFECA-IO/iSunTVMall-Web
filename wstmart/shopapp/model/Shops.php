<?php
namespace wstmart\shopapp\model;
use wstmart\common\model\Shops as CShops;
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
 * 门店类
 */
class Shops extends CShops{
    /**
     * 保存店铺头像
     */
    public function saveShopImg($shopId){
        $shopImg = input('shopImg');
        $_rs = WSTReturn(lang("op_err"), 1);
        if($shopImg=='')return $_rs;
        $rs = $this->where(['shopId'=>$shopId])->setField('shopImg', $shopImg);
        if($rs!==false)return WSTReturn(lang("op_ok"), 1);
        return $_rs;
    }
    /**
    * 获取登录职员拥有权限的按钮
    */
    public function getShopBtn($shopId){
        $userId = model('users')->getUserId();
        $urls = getShopUserUrls($userId);
        $rs = [
            'orders'=>[],
            'goods'=>[],
            'shops'=>[],
            'addons'=>[]
        ];
        if(empty($urls)){
            // 判断是否为店铺拥有者，是则显示全部菜单，否则不显示任何菜单
            $owner = Db::name('shop_users')->where(['shopId'=>$shopId,'userId'=>$userId,'roleId'=>0])->find();
            if(!empty($owner))return WSTReturn('ok',1,['owner'=>true]);
            return WSTReturn('ok',1,$rs);
        }


        $field = 'menuId,parentId';
        $where = ['menuType'=>1,'dataFlag'=>1,'isShow'=>1];




        // 订单管理(交易管理) menuId=23;
            // 全部订单、待付款、代发货、退款投诉
        $orders = Db::name('home_menus')->where($where)
                                              ->where(['parentId'=>23])
                                              ->whereIn('menuUrl',$urls)
                                              ->field($field)
                                              ->select();
        foreach ($orders as $k => $v) {
            $rs['orders'][$v['menuId']] = $v;
        }





        // 商品管理 menuId=29;
            // 商品管理、分类管理、商品咨询、商品评价
        $goods = Db::name('home_menus')->where($where)
                                              ->where(['parentId'=>29])
                                              ->whereIn('menuUrl',$urls)
                                              ->field($field)
                                              ->select();
        foreach ($goods as $k => $v) {
            $rs['goods'][$v['menuId']] = $v;
        }





        // 店铺管理(店铺设置) menuId=38;
            // 店铺设置、销售统计(63)、提现(67)、客服(menuUrl：/addon/wstim-shopchats-index)
        $shops = Db::name('home_menus')->where($where)
                                              ->where(['parentId'=>38])
                                              ->whereIn('menuUrl',$urls)
                                              ->field($field)
                                              ->select();
        foreach ($shops as $k => $v) {
            $rs['shops'][$v['menuId']] = $v;
        }
        // 【销售统计(报表统计)】
        $reports = Db::name('home_menus')->where($where)
                                         ->where(['parentId'=>63])
                                         ->field($field.',menuUrl')
                                         ->select();
        foreach ($reports as $v) {
            if(in_array(strtolower($v['menuUrl']), $urls)){
                $rs['shops'][$v['menuId']] = $v;
            }
        }
        // 【提现】
        $cashs = Db::name('home_menus')->where($where)
                                       ->where(['menuId'=>67])
                                       ->field($field.',menuUrl')
                                       ->find();
        if(isset($cashs['menuUrl']) && in_array(strtolower($cashs['menuUrl']), $urls)){
            $rs['shops'][$cashs['menuId']] = $cashs;
        }
        // 【客服】
        if(in_array('/addon/wstim-shopchats-index', $urls)){
            $rs['shops']['service'] = 1;
        }

        // 促销活动
        $addonMarks = ['auction','coupon','reward','pintuan','bargain','groupon','distribut','live','combination','presale','txlive'];
        $addons = Db::name('home_menus')->where($where)
                                        ->whereIn('menuMark', $addonMarks)
                                        ->field($field.',menuMark,menuUrl')
                                        ->select();
        foreach ($addons as $v) {
            if(in_array(strtolower($v['menuUrl']), $urls)){
                $rs['addons'][$v['menuMark']] = $v;
            }
        }
        return WSTReturn('ok',1,$rs);
    }
    /**
     * 获取店铺提现账号
     */
    public function getShopAccount($shopId){
        $shops = Db::name('shops')->alias('s')
                                  ->join('banks b','b.bankId=s.bankId','inner')
                                  ->join('__BANKS_LANGS__ bl','bl.bankId=b.bankId','inner')
                                  ->where('s.shopId',$shopId)
                                  ->field('b.bankId,b.bankImg,bl.bankName,s.bankAreaId,bankNo,bankUserName')
                                  ->find();
        return $shops;
    }
    /**
    * 获取店铺信息
    */
    public function getShopInfo($shopId,$uId=0){
        $shop = $this->alias('s')->join('__SHOP_SCORES__ cs','cs.shopId = s.shopId','left')
                                 ->join('__SHOP_CONFIGS__ sc','sc.shopId = s.shopId','left')
                                 ->where(['s.shopId'=>$shopId,'dataFlag'=>1])
        ->field('s.shopId,shopImg,shopName,shopkeeper,shopAddress,shopQQ,shopTel,serviceStartTime,serviceEndTime,isInvoice,invoiceRemarks,expireDate,cs.*,sc.shopMoveBanner')
        ->find();
        // 昨日订单数与昨日收益
        $yesterday_start = date('Y-m-d 00:00:00',strtotime("-1 day"));
        $yesterday_end = date('Y-m-d 23:59:59',strtotime("-1 day"));
        $yesterday_rs = Db::field('left(createTime,10) createTime,sum(totalMoney) totalMoney,count(orderId) orderNum')
            ->name('orders')
            ->whereTime('createTime','between',[$yesterday_start,$yesterday_end])
            ->where('shopId',$shopId)
            ->where('(payType=0 or (payType=1 and isPay=1)) and dataFlag=1 ')
            ->order('createTime asc')
            ->group('left(createTime,10)')
            ->find();
        $shop['yesterdayMoney'] = isset($yesterday_rs['totalMoney'])?WSTBCMoney($yesterday_rs['totalMoney'],0):0;
        $shop['yesterdayOrderNum'] = isset($yesterday_rs['orderNum'])?$yesterday_rs['orderNum']:0;
        // 今日营业额、今日订单数
        $start = date('Y-m-d 00:00:00');
        $end = date('Y-m-d 23:59:59');
        $rs = Db::field('left(createTime,10) createTime,sum(totalMoney) totalMoney,count(orderId) orderNum')
                ->name('orders')
                ->whereTime('createTime','between',[$start,$end])
                ->where('shopId',$shopId)
                ->where('(payType=0 or (payType=1 and isPay=1)) and dataFlag=1 ')
                ->order('createTime asc')
                ->group('left(createTime,10)')
                ->find();
        $invalidOrder = Db::field('left(createTime,10) createTime,sum(totalMoney) totalMoney')
            ->name('orders')
            ->whereTime('createTime','between',[$start,$end])
            ->where('shopId',$shopId)
            ->where('(payType=0 or (payType=1 and isPay=1)) and dataFlag=1 ')
            ->where('orderStatus','in',[-1,-3])
            ->group('left(createTime,10)')
            ->find();
        $totalMoney = (float)$rs['totalMoney'] - (float)$invalidOrder['totalMoney'];
        $shop['toDayOrderNum'] = isset($rs['orderNum'])?$rs['orderNum']:0;
        $shop['toDayMoney'] = isset($totalMoney)?WSTBCMoney($totalMoney,0):0;

        // 待付款、待发货、退款投诉、待回复商品咨询数
        $shop['waitPayNum'] = Db::name('orders')->where(['orderStatus'=>-2,'dataFlag'=>1,'shopId'=>$shopId])->count();
        // 待发货
        $shop['waitSendNum'] = Db::name('orders')->where(['orderStatus'=>0,'dataFlag'=>1,'shopId'=>$shopId])->count();
        // 待退款订单数
        $shop['waitRefundNum'] = Db::name('orders')->alias('o')
                                    ->join('order_refunds orf','orf.orderId=o.orderId')
                                    ->where(['shopId'=>$shopId,'refundStatus'=>0,'o.dataFlag'=>1])
                                    ->count();
        // 投诉订单数
        $shop['complainNum'] = Db::name('order_complains')->where(['respondTargetId'=>$shopId,'complainStatus'=>1])->count();
        // 待回复商品咨询数
        $shop['consultNum'] = Db::name('goods_consult')->alias('gc')
                                                       ->join('__GOODS__ g','g.goodsId=gc.goodsId','inner')
                                                       ->where(['gc.dataFlag'=>1,'g.shopId'=>$shopId])
                                                       ->whereNull('gc.replyTime')
                                                       ->count();
        $now = date('Y-m-d H:i:s');
        $shop['shopStatus'] = (($shop['serviceStartTime']<$now) && ($shop['serviceEndTime']>$now))?lang("work"):lang("rest");
        // 是否有设置支付密码
        $um = model('users');
        $userId = $um->getUserId();
        $urs = $um->getFieldsById($userId,'payPwd');
        $shop['isSetPayPwd'] = ($urs['payPwd']=='')?0:1;
        // 未读消息数
        $shop['msgNum'] = Db::name('messages')->where(['receiveUserId'=>$userId,'msgStatus'=>0,'dataFlag'=>1])->count();
        return $shop;
    }
    /*
     * 获取店铺公告
     */
    public function getShopNotice($shopId){
        $shopNotice = $this->where(['shopId'=>$shopId,'dataFlag'=>1])->value('shopNotice');
        return $shopNotice;
    }

    /**
     * 店铺街列表
     */
    public function pageQuery(){
        $lng = (float)input("longitude");
        $lat = (float)input("latitude");
        if($lng=='' && $lat==''){
            $lng = 0;
            $lat = 0;
        }
    	$catId = (int)input("id");
    	$keyword = input("keyword");
    	$condition = input("condition");
    	$desc = input("desc");
        $totalScore = input('totalScore');
    	$datas = array('sort'=>array('1'=>'ss.totalScore/ss.totalUsers'),'desc'=>array('desc','asc'));
    	$datas1 = array('sort'=>array('1'=>'distince'),'desc'=>array('desc','asc'));
        $rs = $this->alias('s');
        $tol = $this->alias('s');
    	$where = [];
    	$where[] = ['s.dataFlag','=',1];
    	$where[] = ['s.shopStatus','=',1];
        $where[] = ['s.applyStatus','=',2];
    	if($keyword!='')$where[] = ['s.shopName','like','%'.$keyword.'%'];
    	if($catId>0){
    		$rs->join('__CAT_SHOPS__ cs','cs.shopId = s.shopId','left');
            $tol->join('__CAT_SHOPS__ cs','cs.shopId = s.shopId','left');
    		$where[] = ['cs.catId','=',$catId];
    	}
        $order = ['distince'=>'asc'];
    	if($condition == 1){
    		$order = [$datas['sort'][$condition]=>$datas['desc'][$desc]];
    	}
        if($condition == 2){
            $order = [$datas1['sort'][1]=>$datas['desc'][$desc]];
        }
        $shopIds = $this->filterByCondition();
        if(!empty($shopIds)){
            $where[] = $where1[] = ['s.shopId','in',$shopIds];
        }
        $a = [];
        if($totalScore != ''){
            $totalScore = explode('_',$totalScore);
            $maxScore = (float)(($totalScore[1])*5/100);
            $minScore = (float)(($totalScore[0])*5/100);
            //$a['maxScore'] = $maxScore;
            //$a['minScore1'] = $minScore;
            $where[] = ['(ss.totalScore/3)/ss.totalUsers','>=',$minScore];
            $where[] = ['(ss.totalScore/3)/(case when ss.totalUsers = 0 then 1 else ss.totalUsers end)','<=',$maxScore];
           // $where[] = ['(ss.totalScore/3)/ss.totalUsers','=',null];
        }
            $rs->field('(ss.totalScore/3)/(case when ss.totalUsers = 0 then 1 else ss.totalUsers end) as aa');
    	$page = $rs->join('__SHOP_SCORES__ ss','ss.shopId = s.shopId','left')
    	->where($where)->order($order)
    	->field('s.shopId,s.shopImg,s.isSelf,s.shopName,s.shopCompany,ss.totalScore,ss.totalUsers,ss.goodsScore,ss.goodsUsers,ss.serviceScore,ss.serviceUsers,ss.timeScore,ss.timeUsers,s.areaIdPath')
        ->field("round(6378.138*2*asin(sqrt(pow(sin( (".$lat."*pi()/180-s.latitude*pi()/180)/2),2)+cos(".$lat."*pi()/180)*cos(s.latitude*pi()/180)* pow(sin( (".$lng."*pi()/180-s.longitude*pi()/180)/2),2)))*1000)/1000 as distince")
    	->paginate()
        ->toArray();

        $totalShop = $tol->join('__SHOP_SCORES__ ss','ss.shopId = s.shopId','left')
                        ->where($where)
                        ->field('ss.totalScore,ss.totalUsers')
                        ->select();
        foreach ($page['data'] as $key =>$v){
            //商品列表
            $goods = model('Tags')->listShopGoods('recom',$v['shopId'],0,4);
            $page['data'][$key]['goods'] = $goods;
        }
        //$page['a'] = $a;

    	if(empty($page['data']))return $page;
    	$shopIds = [];
    	$areaIds = [];
        foreach ($totalShop as $key => $v) {
            if($v["totalUsers"] != 0){
               $totalScores[] = round(($v["totalScore"]/3)/$v["totalUsers"],8);
            }else{
               $totalScores[] = 5;
            }
        }
    	foreach ($page['data'] as $key =>$v){
    		$shopIds[] = $v['shopId'];
    		$tmp = explode('_',$v['areaIdPath']);
    		$areaIds[] = $tmp[1];
    		$page['data'][$key]['areaId'] = $tmp[1];
    		//总评分
            $page['data'][$key]['totalScore'] = WSTScore($v["totalScore"]/3, $v["totalUsers"]==0?1: $v["totalUsers"]);
    		$page['data'][$key]['goodsScore'] = WSTScore($v['goodsScore'],$v['goodsUsers']);
    		$page['data'][$key]['serviceScore'] = WSTScore($v['serviceScore'],$v['serviceUsers']);
    		$page['data'][$key]['timeScore'] = WSTScore($v['timeScore'],$v['timeUsers']);
		}
         $maxScore = max($totalScores)<5?((max($totalScores)/5)*100):100;
        $minScore = min($totalScores)>0?(((min($totalScores)-0.01)/5)*100):0;
        $scores = [];
        $scores['attrName'] =lang("praise_rate");
        $scores['attrId'] = 1;
        //区间跨度
        $span = ($maxScore - $minScore) / 3;
        for($i=1;$i<=3;$i++){
            if(($minScore+$i*$span)>100) {
                $scores['attrVal'][($minScore+($i-1)*$span)."_".(100)] = round(($minScore+($i-1)*$span),0)."%-".(100)."%";
                break;
            }
            $scores['attrVal'][($minScore+($i-1)*$span)."_".($minScore+$i*$span)] = round(($minScore+($i-1)*$span),0)."%-".round(($minScore+$i*$span),0)."%";
        }
        if($totalScore == ''){
         $page['screen'][] = $scores;
        }
        $page['minScore'] = $minScore.'-'.$maxScore;
		$rccredMap = [];
		$goodsCatMap = [];
		$areaMap = [];
		//认证、地址、分类、4件店铺推荐商品
		if(!empty($shopIds)){
			$rccreds = Db::name('shop_accreds')->alias('sac')->join('__ACCREDS__ a','a.accredId=sac.accredId and a.dataFlag=1','left')
                         ->join('__ACCREDS_LANGS__ al','al.accredId=sac.accredId and langId='.WSTCurrLang())
			             ->where([['shopId','in',$shopIds]])->field('sac.shopId,accredName,accredImg,a.accredId')->select();
			$accredIds = [];
            $accreds = [];
            $accreds['attrName'] = lang("shop_service");
            $accreds['attrId'] = 2;
            foreach ($rccreds as $v){
				$rccredMap[$v['shopId']][] = $v;
                if(!in_array($v['accredId'],$accredIds)){
                    $accredIds[] = $v['accredId'];
                    $accreds['attrVal'][$v['accredId']] = $v['accredName'];
                }
			}
            if(input('accredId') == ''){
              $page['screen'][] = $accreds;
            }
			$goodsCats = Db::name('cat_shops')->alias('cs')->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')
                           ->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=cs.catId and langId='.WSTCurrLang(),'inner')
			               ->where([['shopId','in',$shopIds]])->field('cs.shopId,gcl.catName')->select();
		    foreach ($goodsCats as $v){
				$goodsCatMap[$v['shopId']][] = $v['catName'];
			}



		}
		foreach ($page['data'] as $key =>$v){
            $page['data'][$key]['lng'] = $lng;
            $page['data'][$key]['lat'] = $lat;
			$page['data'][$key]['accreds'] = (isset($rccredMap[$v['shopId']]))?$rccredMap[$v['shopId']]:[];
			$page['data'][$key]['catshops'] = (isset($goodsCatMap[$v['shopId']]))?$goodsCatMap[$v['shopId']][0]:'';// 取第一个分类为主营
			// 4件推荐商品
            $page['data'][$key]['rec'] = model('Tags')->listShopGoods('recom',$v['shopId'],0,4);
		}
    	return $page;
    }
    /**
    * 自营店铺楼层
    */
    public function getFloorData(){
        $limit = (int)input('page');
        if($limit>6)return;
        if($limit<=0)$limit=1;
        $cacheData = WSTCache('APP_SHOP_FLOOR'.$limit);
        if($cacheData)return $cacheData;
        $rs = Db::name('shop_cats')->where(['dataFlag'=>1,'isShow'=>1,'parentId'=>0,'shopId'=>1])->field('catId,catName')->order('catSort asc')->limit($limit-1,1)->select();
        if($rs){
            $rs= $rs[0];
            $goods = Db::name('goods')->where(['shopCatId1'=>$rs['catId'],'dataFlag'=>1,'isSale'=>1,'goodsStatus'=>1])->field('goodsId,goodsName,goodsImg,shopPrice,saleNum,isFreeShipping')->order('isHot desc')->limit(6)->select();
            // 图片转换
            foreach($goods as $k1=>$v1){
                $goods[$k1]['goodsImg'] = WSTImg($v1['goodsImg'],2);
            }
            $rs['goods'] = $goods;
            $rs['current_page'] = $limit;
        }
        // 分类下没有商品
       // if(empty($rs['goods']))return;
        $rs['total'] = 6;// 最多取6个楼层
        WSTCache('APP_SHOP_FLOOR'.$limit,$rs,86400);
        return $rs;
    }
    /**
    * 根据userId 获取shopId
    */
    public function getShopId($userId){
        return Db::name('shop_users')->where("userId=$userId")->value('shopId');
    }
      /*过滤条件获取符合店铺Id*/
    public function filterByCondition(){
        $accredId = input('accredId');
        $res = [];
        $shopIds = Db::name('shop_accreds')->where(['accredId'=>$accredId])->field('shopId')->select();
        foreach ($shopIds as $key => $value) {
            $res[] = $value['shopId'];
        }
        return $res;
    }
}
