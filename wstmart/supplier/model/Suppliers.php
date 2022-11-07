<?php
namespace wstmart\supplier\model;
use wstmart\supplier\validate\Suppliers as VSupplier;
use think\Db;
use think\Loader;
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
class Suppliers extends Base{
    protected $pk = 'supplierId';

    /**
    * 获取供货商公告
    */
    public function getNotice(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        return model('suppliers')->where(['supplierId'=>$supplierId])->value('supplierNotice');
    }
    /**
    * 修改供货商公告
    */
    public function editNotice(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $supplierNotice = input('supplierNotice');
        if(strlen($supplierNotice)>450){
            return WSTReturn(lang("supplier_notice_max_length"));
        }
        //对供货商公告进行处理,去掉换行
        $supplierNotice = str_replace(PHP_EOL,'',$supplierNotice);
        $rs = $this->where("supplierId=$supplierId")->setField('supplierNotice',$supplierNotice);
        if($rs!==false)return WSTReturn(lang("op_ok"),1);
        return WSTReturn(lang("op_err"),-1);
    }

    /**
     * 供货商街列表
     */
    public function pageQuery($pagesize){
    	$catId = input("get.id/d");
    	$keyword = input("keyword");
        $location = WSTIpLocation();
    	$userId = (int)session('WST_SUPPLIER.userId');
    	$rs = $this->alias('s');
    	$where = [];
    	$where[] = ['s.dataFlag','=',1];
        $where[] = ['s.supplierStatus','=',1];
    	$where[] = ['s.applyStatus','=',2];
    	if($keyword!='')$where[] = ['s.supplierName','like','%'.$keyword.'%'];
    	if($catId>0){
    		$rs->join('__CAT_SUPPLIERS__ cs','cs.supplierId = s.supplierId','left');
    		$where[] = ['cs.catId','=',$catId];
    	}
    	$page = $rs->join('__SUPPLIER_SCORES__ ss','ss.supplierId = s.supplierId','left')
    	->join('__USERS__ u','u.userId = s.userId','left')
    	->where($where)
    	->order('distince asc')
    	->field('s.supplierId,s.supplierImg,s.supplierName,s.longitude,s.latitude,s.supplierTel,s.supplierQQ,s.supplierWangWang,s.supplierCompany,ss.totalScore,ss.totalUsers,ss.goodsScore,ss.goodsUsers,ss.serviceScore,ss.serviceUsers,ss.timeScore,ss.timeUsers,.u.loginName,u.userName,s.areaIdPath')
        ->field("round(6378.138*2*asin(sqrt(pow(sin( (".$location['latitude']."*pi()/180-s.latitude*pi()/180)/2),2)+cos(".$location['latitude']."*pi()/180)*cos(s.latitude*pi()/180)* pow(sin( (".$location['longitude']."*pi()/180-s.longitude*pi()/180)/2),2)))*1000)/1000 as distince")
        ->paginate($pagesize)->toArray();
    	if(empty($page['data']))return $page;
    	$supplierIds = [];
    	$areaIds = [];
    	foreach ($page['data'] as $key =>$v){
    		$supplierIds[] = $v['supplierId'];
    		$tmp = explode('_',$v['areaIdPath']);
    		$areaIds[] = $tmp[1];
    		$page['data'][$key]['areaId'] = $tmp[1];
    		//总评分
    		$page['data'][$key]['totalScore'] = WSTScore($v["totalScore"], $v["totalUsers"]);
    		$page['data'][$key]['goodsScore'] = WSTScore($v['goodsScore'],$v['goodsUsers']);
    		$page['data'][$key]['serviceScore'] = WSTScore($v['serviceScore'],$v['serviceUsers']);
    		$page['data'][$key]['timeScore'] = WSTScore($v['timeScore'],$v['timeUsers']);
    		//商品列表
    		$goods = Db::name('supplier_goods')
                ->alias('g')->join('__SUPPLIER_GOODS_LANGS__ sgl','sgl.goodsId=g.goodsId and sgl.langId='.WSTCurrLang())
                ->where(['dataFlag'=> 1,'goodsStatus'=>1,'isSale'=>1,'supplierId'=> $v["supplierId"]])->field('g.goodsId,sgl.goodsName,supplierPrice,goodsImg')->limit(10)->order('saleTime desc')->select();
    		$page['data'][$key]['goods'] = $goods;
    		//供货商商品总数
    		$page['data'][$key]['goodsTotal'] = count($goods);
		}
		$rccredMap = [];
		$goodsCatMap = [];
		$areaMap = [];
		//认证、地址、分类
		if(!empty($supplierIds)){

			$goodsCats = Db::name('cat_suppliers')->alias('cs')->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')
			               ->where([['supplierId','in',$supplierIds]])->field('cs.supplierId,gc.catName')->select();
		    foreach ($goodsCats as $v){
				$goodsCatMap[$v['supplierId']][] = $v['catName'];
			}
			$areas = Db::name('areas')->alias('a')->join('__AREAS__ a1','a1.areaId=a.parentId','left')
			           ->where([['a.areaId','in',$areaIds]])->field('a.areaId,a.areaName areaName2,a1.areaName areaName1')->select();
		    foreach ($areas as $v){
				$areaMap[$v['areaId']] = $v;
			}
		}
		foreach ($page['data'] as $key =>$v){

			$page['data'][$key]['catsuppliers'] = (isset($goodsCatMap[$v['supplierId']]))?implode(',',$goodsCatMap[$v['supplierId']]):'';
			$page['data'][$key]['areas']['areaName1'] = (isset($areaMap[$v['areaId']]['areaName1']))?$areaMap[$v['areaId']]['areaName1']:'';
			$page['data'][$key]['areas']['areaName2'] = (isset($areaMap[$v['areaId']]['areaName2']))?$areaMap[$v['areaId']]['areaName2']:'';
		}
    	return $page;
    }
    /**
     * 获取供货商中心信息
     */
    public function getSupplierSummary($supplierId){
    	$supplier = $this->alias('s')->join('__SUPPLIER_SCORES__ cs','cs.supplierId = s.supplierId','left')
    	           ->where(['s.supplierId'=>$supplierId,'dataFlag'=>1])
    	->field('s.supplierMoney,s.noSettledOrderFee,s.paymentMoney,s.supplierId,supplierImg,supplierName,supplierAddress,supplierQQ,supplierTel,serviceStartTime,serviceEndTime,s.expireDate,cs.*')
    	->find();
    	//评分
    	$scores['totalScore'] = WSTScore($supplier['totalScore'],$supplier['totalUsers']);
    	$scores['goodsScore'] = WSTScore($supplier['goodsScore'],$supplier['goodsUsers']);
    	$scores['serviceScore'] = WSTScore($supplier['serviceScore'],$supplier['serviceUsers']);
    	$scores['timeScore'] = WSTScore($supplier['timeScore'],$supplier['timeUsers']);
    	WSTUnset($supplier, 'totalUsers,goodsUsers,serviceUsers,timeUsers');
    	$supplier['scores'] = $scores;


        //查看商家钱包是否足够钱
        $USER = session('WST_SUPPLIER');
        $USER['supplierMoney'] = $supplier['supplierMoney'];
        $USER['noSettledOrderFee'] = $supplier['noSettledOrderFee'];
        $USER['paymentMoney'] = $supplier['paymentMoney'];
        session('WST_SUPPLIER',$USER);


        $stat = array();
        $date = date("Y-m-d");
        $userId = session('WST_SUPPLIER.userId');
        /**********今日动态**********/
        //待查看消息数
        $stat['messageCnt'] = Db::name('messages')->where(['receiveUserId'=>$userId,'msgStatus'=>0,'dataFlag'=>1])->count();
        //今日销售金额
        $stat['saleMoney'] = Db::name('supplier_orders')->where([['orderStatus','egt',0],['supplierId','=',$supplierId],['dataFlag','=',1]])->whereTime('createTime', 'between', [$date.' 00:00:00', $date.' 23:59:59'])->sum("goodsMoney");
        //今日订单数
        $stat['orderCnt'] = Db::name('supplier_orders')->where([['orderStatus','egt',0],['supplierId','=',$supplierId],['dataFlag','=',1]])->whereTime('createTime', 'between', [$date.' 00:00:00', $date.' 23:59:59'])->count();
        //待发货订单
        $stat['waitDeliveryCnt'] = Db::name('supplier_orders')->where(['supplierId'=>$supplierId,'orderStatus'=>0,'dataFlag'=>1])->count();
        //待收货订单
        $stat['waitReceiveCnt'] = Db::name('supplier_orders')->where(['supplierId'=>$supplierId,'orderStatus'=>1,'dataFlag'=>1])->count();
        //取消/拒收
        $stat['cancel'] = Db::name('supplier_orders')->where([['orderStatus','in',[-1,-3]],['supplierId','=',$supplierId],['dataFlag','=',1]])->count();
        //库存预警
        $goodsn = Db::name('supplier_goods')->where('supplierId ='.$supplierId.' and dataFlag = 1 and goodsStock <= warnStock and isSpec = 0 and warnStock>0')->cache('supplier_stockWarnCnt1'.$supplierId,600)->count();
        $specsn = Db::name('supplier_goods_specs')->where('supplierId ='.$supplierId.' and dataFlag = 1 and specStock <= warnStock and warnStock>0')->cache('supplier_stockWarnCnt2'.$supplierId,600)->count();
        $stat['stockWarnCnt'] = $goodsn+$specsn;

        /**********商品信息**********/
        //商品总数
        $stat['goodsCnt'] = Db::name('supplier_goods')->where(['supplierId'=>$supplierId,'dataFlag'=>1])->cache('supplier_goodsCnt'.$supplierId,600)->count();
        //上架商品
        $stat['onSaleCnt'] = Db::name('supplier_goods')->where(['supplierId'=>$supplierId,'dataFlag'=>1,'goodsStatus'=>1,'isSale'=>1])->cache('supplier_onSaleCnt'.$supplierId,600)->count();
        //待审核商品
        $stat['waitAuditCnt'] = Db::name('supplier_goods')->where(['supplierId'=>$supplierId,'dataFlag'=>1,'goodsStatus'=>0])->cache('supplier_waitAuditCnt'.$supplierId,600)->count();
        //仓库中的商品
        $stat['unSaleCnt'] = Db::name('supplier_goods')->where(['supplierId'=>$supplierId,'dataFlag'=>1,'goodsStatus'=>1,'isSale'=>0])->cache('supplier_unSaleCnt'.$supplierId,600)->count();
        //违规商品
        $stat['illegalCnt'] = Db::name('supplier_goods')->where(['supplierId'=>$supplierId,'dataFlag'=>1,'goodsStatus'=>-1])->cache('supplier_illegalCnt'.$supplierId,600)->count();
        //今日新品
        $stat['newGoodsCnt'] = Db::name('supplier_goods')->where(['supplierId'=>$supplierId,'dataFlag'=>1,'goodsStatus'=>1,'isSale'=>1,'isNew'=>1])->cache('supplier_newGoodsCnt'.$supplierId,600)->count();
        //待回复咨询
        $stat['consult'] = Db::name('supplier_goods_consult sg')->join('supplier_goods g','sg.goodsId=g.goodsId','inner')->where(['g.supplierId'=>$supplierId,'sg.dataFlag'=>1,'consultContent'=>''])->cache('supplier_consult'.$supplierId,600)->count();


        /**********订单信息**********/
        //待付款订单
        $stat['orderNeedpayCnt'] = Db::name('supplier_orders')->where(['supplierId'=>$supplierId,'orderStatus'=>-2,'dataFlag'=>1])->count();
        //待结束订单
        $stat['orderWaitCloseCnt'] = Db::name('supplier_orders')->where(['supplierId'=>$supplierId,'orderStatus'=>2,'dataFlag'=>1,'isClosed'=>0])->cache('supplier_orderWaitCloseCnt'.$supplierId,600)->count();
        //退货退款订单
        $stat['orderRefundCnt'] = Db::name('supplier_orders')->alias('o')->join('supplier_order_refunds orf','orf.orderId=o.orderId')->where(['supplierId'=>$supplierId,'refundStatus'=>0,'o.dataFlag'=>1])->count();
        //待评价订单
        $stat['orderWaitAppraisesCnt'] = Db::name('supplier_orders')->where(['supplierId'=>$supplierId,'orderStatus'=>2,'dataFlag'=>1,'isAppraise'=>0])->cache('supplier_orderWaitAppraisesCnt'.$supplierId,600)->count();
        // 投诉订单数
        $stat['complainNum'] = Db::name('supplier_order_complains')->where(['respondTargetId'=>$supplierId,'complainStatus'=>1])->count();
        // 近30天销售排行
        $start = date('Y-m-d H:i:s',strtotime("-30 day"));
        $end = date('Y-m-d H:i:s');
        $prefix = config('database.prefix');
        $stat['goodsTop'] = $rs = Db::table($prefix.'supplier_order_goods')
                            ->alias([$prefix.'supplier_order_goods'=>'og',$prefix.'supplier_orders'=>'o',$prefix.'supplier_goods'=>'g',$prefix.'supplier_goods_langs'=>'sgl'])
                            ->join($prefix.'supplier_orders','og.orderId=o.orderId')
                            ->join($prefix.'supplier_goods','og.goodsId=g.goodsId')
                            ->join($prefix.'supplier_goods_langs','sgl.goodsId=g.goodsId and sgl.langId='.WSTCurrLang())
                            ->order('goodsNum desc')
                            ->whereTime('o.createTime','between',[$start,$end])
                            ->where('(payType=0 or (payType=1 and isPay=1)) and o.dataFlag=1 and o.supplierId='.$supplierId)->group('og.goodsId')
                            ->field('og.goodsId,sgl.goodsName,goodsSn,sum(og.goodsNum) goodsNum,g.goodsImg')
                                  ->limit(10)->select();
    	return ['supplier'=>$supplier,'stat'=>$stat];
    }
    /**
     * 获取供货商信息
     */
	public function getByView($id){
		$supplier = $this->alias('s')->join('__BANKS__ b','b.bankId=s.bankId','left')
                     ->join('__BANKS_LANGS__ bl','bl.bankId=b.bankId','inner')
		             ->where(['s.dataFlag'=>1,'supplierId'=>$id])
		             ->field('s.*,bl.bankName')->find();
	     $areaIds = [];
        $areaMaps = [];
        $tmp = explode('_',$supplier['areaIdPath']);
        foreach ($tmp as $vv){
         	if($vv=='')continue;
         	if(!in_array($vv,$areaIds))$areaIds[] = $vv;
        }
        if(!empty($areaIds)){
	         $areas = Db::name('areas')->where([['areaId','in',$areaIds],['dataFlag','=',1]])->field('areaId,areaName')->select();
	         foreach ($areas as $v){
	         	 $areaMaps[$v['areaId']] = $v['areaName'];
	         }
	         $tmp = explode('_',$supplier['areaIdPath']);
	         $areaNames = [];
		     foreach ($tmp as $vv){
	         	 if($vv=='')continue;
	         	 $areaNames[] = $areaMaps[$vv];
	         	 $supplier['areaName'] = implode('',$areaNames);
	         }
         }

		//获取经营范围
		$goodsCats = Db::name('goods_cats')->alias('a')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=a.catId and langId='.WSTCurrLang())->where(['parentId'=>0,'isShow'=>1,'dataFlag'=>1])->field('a.catId,catName')->select();
		$catsuppliers = Db::name('cat_suppliers')->where('supplierId',$id)->select();
		$catsupplierMaps = [];
		foreach ($goodsCats as $v){
			$catsupplierMaps[$v['catId']] = $v['catName'];
		}
		$catsupplierNames = [];
		foreach ($catsuppliers as $key =>$v){
			if(isset($catsupplierMaps[$v['catId']]))$catsupplierNames[] = $catsupplierMaps[$v['catId']];
		}
		$supplier['catsupplierNames'] = $catsupplierNames;
		//所属行业
        $trade = Db::name("trades t")->join('__TRADES_LANGS__ tl','tl.tradeId=t.tradeId and tl.langId='.WSTCurrLang())->where(["t.tradeId"=>$supplier['tradeId']])->field("tl.tradeName")->find();
        $supplier['tradeName'] = $trade['tradeName'];
	    //开卡地址
        $areaNames  = model('areas')->getParentNames($supplier['bankAreaId']);
        $supplier['bankAreaName'] = implode('',$areaNames);
		return $supplier;
	}


    /**
     * 编辑供货商资料
     */
    public function editInfo(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $validate = new VSupplier;
        if (!$validate->scene('editInfo')->check(input('post.'))) {
        	return WSTReturn($validate->getError());
        }else{
        	$result = $this->allowField(['supplierImg','isInvoice','invoiceRemarks','serviceStartTime','serviceEndTime','supplierQQ','supplierWangWang'])->save(input('post.'),['supplierId'=>$supplierId]);
        }
        if(false !== $result){
             return WSTReturn(lang("op_ok"),1);
        }else{
             return WSTReturn($this->getError());
        }
    }

    /**
     * 获取供货商提现账号
     */
    public function getSupplierAccount(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $suppliers = Db::name('suppliers')->alias('s')->join('banks b','b.bankId=s.bankId','inner')->join('__BANKS_LANGS__ bl','bl.bankId=b.bankId','inner')->where('s.supplierId',$supplierId)->field('bl.bankName,s.bankAreaId,bankNo,bankUserName')->find();
        return $suppliers;
    }

    /**
     * 获取商家入驻资料
     */
    public function getSupplierApply(){
        $userId = (int)session('WST_SUPPLIER.userId');
        $rs = $this->alias('s')->join('__SUPPLIER_EXTRAS__ ss','s.supplierId=ss.supplierId','inner')
                   ->where('s.userId',$userId)
                   ->find();
        if(!empty($rs)){
            $rs = $rs->toArray();
            $goodscats = Db::name('cat_suppliers')->where('supplierId',$rs['supplierId'])->select();
            $rs['catsuppliers'] = [];
            foreach ($goodscats as $v){
                $rs['catsuppliers'][$v['catId']] = true;
            }
            $rs['taxRegistrationCertificateImgVO'] = ($rs['taxRegistrationCertificateImg']!='')?explode(',',$rs['taxRegistrationCertificateImg']):[];
        }else{
            $rs = [];
            $data1 = $this->getEModel('suppliers');
            $data2 = $this->getEModel('supplier_extras');
            $rs = array_merge($data1,$data2);
            $rs['taxRegistrationCertificateImgVO'] = [];
        }
        return $rs;
    }

    /**
     * 判断是否申请入驻过
     */
    public function checkApply(){
        $userId = (int)session('WST_SUPPLIER.userId');
        $rs = $this->where(['userId'=>$userId])->find();
        if(!empty($rs)){
            $WST_SUPPLIER = session('WST_SUPPLIER');
            $WST_SUPPLIER['tempSupplierId'] = $rs->supplierId;
            session('WST_SUPPLIER',$WST_SUPPLIER);
            session('apply_step',$rs['applyStep']);
        }
        return $rs;
    }

    /**
     * 获取供货商评分
     */
    public function getSupplierScore($supplierId){
        $supplier = $this->alias('s')->join('__SUPPLIER_SCORES__ cs','cs.supplierId = s.supplierId','left')
                    ->where(['s.supplierId'=>$supplierId,'s.supplierStatus'=>1,'s.dataFlag'=>1])->field('s.supplierAddress,s.supplierkeeper,s.supplierImg,s.supplierQQ,s.supplierId,s.supplierName,s.supplierTel,s.areaId,cs.*')->find();
        if(empty($supplier))return [];
        $supplier->toArray();
        $supplier['totalScore'] = WSTScore($supplier['totalScore']/3,$supplier['totalUsers']);
        $supplier['goodsScore'] = WSTScore($supplier['goodsScore'],$supplier['goodsUsers']);
        $supplier['serviceScore'] = WSTScore($supplier['serviceScore'],$supplier['serviceUsers']);
        $supplier['timeScore'] = WSTScore($supplier['timeScore'],$supplier['timeUsers']);
        WSTUnset($supplier, 'totalUsers,goodsUsers,serviceUsers,timeUsers');
        return $supplier;
    }
    /**
     * 获取供货商首页信息
     */
    public function getSupplierInfo($supplierId,$uId = 0){
        $rs = Db::name('suppliers')->alias('s')
        ->join('__SUPPLIER_EXTRAS__ ser','ser.supplierId=s.supplierId','inner')
        ->where(['s.supplierId'=>$supplierId,'s.supplierStatus'=>1,'s.dataFlag'=>1])
        ->field('s.supplierId,s.supplierImg,s.supplierName,s.supplierAddress,s.supplierQQ,s.supplierWangWang,s.supplierTel,s.serviceStartTime,s.longitude,s.latitude,s.serviceEndTime,s.supplierkeeper,mapLevel,s.areaId,s.isInvoice,s.invoiceRemarks,ser.*')
        ->find();
        if(empty($rs)){
            //如果没有传id就获取自营供货商
            $rs = Db::name('suppliers')->alias('s')
            ->join('__SUPPLIER_EXTRAS__ ser','ser.supplierId=s.supplierId','inner')
            ->where(['s.supplierStatus'=>1,'s.dataFlag'=>1,'s.isSelf'=>1])
            ->field('s.supplierId,s.supplierImg,s.supplierName,s.supplierAddress,s.supplierQQ,s.supplierWangWang,s.supplierTel,s.serviceStartTime,s.longitude,s.latitude,s.serviceEndTime,s.supplierkeeper,s.mapLevel,s.areaId,s.isInvoice,s.invoiceRemarks,ser.*')
            ->find();
            if(empty($rs))return [];
            $supplierId = $rs['supplierId'];
        }
        //仅仅是为了获取businessLicenceImg而写的，因为businessLicenceImg不排除被删除掉了
        WSTAllow($rs,'supplierNotice,supplierId,supplierImg,supplierName,supplierAddress,supplierQQ,supplierWangWang,supplierTel,serviceStartTime,longitude,latitude,serviceEndTime,supplierkeeper,mapLevel,areaId,isInvoice,invoiceRemarks,businessLicenceImg');
        //评分
        $score = $this->getSupplierScore($rs['supplierId']);
        $rs['scores'] = $score;
        //供货商地址
        $rs['areas'] = Db::name('areas')->alias('a')->join('__AREAS__ a1','a1.areaId=a.parentId','left')
        ->where([['a.areaId','=',$rs['areaId']]])->field('a.areaId,a.areaName areaName2,a1.areaName areaName1')->find();

        //分类
        $goodsCatMap = [];
        $goodsCats = Db::name('cat_suppliers')->alias('cs')->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')
        ->where(['supplierId'=>$rs['supplierId']])->field('cs.supplierId,gc.catName')->select();
        foreach ($goodsCats as $v){
            $goodsCatMap[] = $v['catName'];
        }
        $rs['catsuppliers'] = (isset($goodsCatMap))?implode(',',$goodsCatMap):'';

        $supplierAds = array();
        $config = Db::name('supplier_configs')->where("supplierId=".$rs['supplierId'])->find();
        //取出轮播广告
        if($config["supplierAds"]!=''){
            $supplierAdsImg = explode(',',$config["supplierAds"]);
            $supplierAdsUrl = explode(',',$config["supplierAdsUrl"]);
            for($i=0;$i<count($supplierAdsImg);$i++){
                $adsImg = $supplierAdsImg[$i];
                $supplierAds[$i]["adImg"] = $adsImg;
                $supplierAds[$i]["adUrl"] = $supplierAdsUrl[$i];
                $supplierAds[$i]['isOpen'] = false;
                if(stripos($supplierAdsUrl[$i],'http:')!== false || stripos($supplierAdsUrl[$i],'https:')!== false){
                    $supplierAds[$i]['isOpen'] = true;
                }
            }
            $rs['supplierAds'] = $supplierAds;
            unset($config['supplierAds']);
        }
        $rs = array_merge($rs,$config);
        //热搜关键词
        $rs['supplierHotWords'] = ($rs['supplierHotWords']!='')?explode(',',$rs['supplierHotWords']):[];
        return $rs;
    }

    /**
     * 清除供货商缓存
     */
    public function clearCache($supplierId){
        Cache::clear('CACHETAG_SUPPLIER_'.$supplierId);
    }

    /**
     * 获取指定店铺经营的商城分类
     */
    public function getSupplierApplyGoodsCats($parentId = 0){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $rs = Db::name('goods_cats')->alias('gc')
                 ->join('cat_suppliers csp','gc.catId=csp.catId')
                 ->where(['dataFlag'=>1, 'isShow' => 1,'gc.parentId'=>$parentId,'csp.supplierId'=>$supplierId])
                 ->field("catName,simpleName,gc.catId,parentId")->order('catSort asc')->select();
        return $rs;
    }


    /**
     * 获取店铺申请的商品分类
     */
    public function getSupplierApplyGoodsCatsById($supplierId){
        $ids = Db::name('cat_suppliers')->where(['supplierId'=>$supplierId])->column('catId');
        $gcat = model("common/GoodsCats");
        return $gcat->getChildIds($ids);
    }

    /**
     * 获取供货商指定字段
     */
    public function getFieldsById($supplierId,$fields){
        return $this->where(['supplierId'=>$supplierId,'dataFlag'=>1])->field($fields)->find();
    }

    public function getCatShopInfo(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        //获取经营范围
        $trade = Db::name("trades t")->join("suppliers s","s.tradeId=t.tradeId","inner")
            ->field("s.tradeId,t.tradeFee")
            ->where(['s.supplierId'=>$supplierId])
            ->find();
        $rs['needPay'] = $trade['tradeFee'];
        $rs['tradeId'] = $trade['tradeId'];
        return $rs;
    }

    public function renew(){
        $userId = (int)session('WST_SUPPLIER.userId');
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $suppliers = $this->getCatShopInfo();
        if((int)$suppliers['needPay']>0)return WSTReturn(lang("please_pay_annual_fee"));
        Db::startTrans();
        try{
            $supplierExpireDate = $this->where(['userId'=>$userId])->value('expireDate');
            $supplierData = [];
            $newExpireDate = date('Y-m-d',strtotime("$supplierExpireDate +1 year"));
            $supplierData['expireDate'] = $newExpireDate;
            $supplierRes = $this->where(['userId'=>$userId])->update($supplierData);
            $fee = [];
            $fee['userId'] = $userId;
            $fee['supplierId'] = $supplierId;
            $fee['money'] = 0;
            $fee['remark'] = json_encode(['type'=>'lang','key'=>"s_pays_annual_fee"]);
            $fee['startDate'] = date('Y-m-d');
            $fee['endDate'] = date('Y-m-d',strtotime("$supplierExpireDate +1 year"));;
            $fee['createTime'] = date('Y-m-d H:i:s');
            $result = Db::name('supplier_fees')->insert($fee);
            if(false !== $supplierRes && false !== $result){
                session('WST_SUPPLIER.expireDate',$newExpireDate);
                Db::commit();
                return WSTReturn(lang("op_ok"),1);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang("op_err"));
        }
    }
}
