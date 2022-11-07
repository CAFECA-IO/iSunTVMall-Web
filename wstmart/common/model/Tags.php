<?php
namespace wstmart\common\model;
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
 * 标签业务处理类
 */
class Tags extends Base{
	/**
	* 单数据库查询
	*/
	public function wstDb($table,$where,$order,$field,$num,$cache){
		//$cacheData = WSTCache('TAG_GOODS_'.$table."_".$field."_".$num."_".$cache);
		if($cacheData)return $cacheData;
		$goods = model($table)->field($field)
							  ->where($where)
							  ->order($order)
							  ->limit($num)
							  ->select();
		//WSTCache('TAG_GOODS_'.$table."_".$field."_".$num."_".$cache,$goods,$cache);
		return $goods;

	}
	/**
	 * 获取指定商品
	 */
	public function listGoods($type,$catId = 0,$num,$cache = 0){
		$type = strtolower($type);
		if(strtolower($type)=='history'){
			return $this->historyByGoods($num);
		}elseif(strtolower($type)=='guess'){
			return $this->getGuessLike($catId,$num);
		}else{
			return $this->listByGoods($type,$catId,$num,$cache);
		}
	}
	/**
	* 猜你喜欢
	* @param catId:分类id
	* @param num:数据条数
	*/
	public function getGuessLike($catId,$num,$goodsIds=[]){
		$module = request()->module();
		if($module=='app'||$module=='weapp'){
			$ids = $goodsIds;
		}else{
			$ids = ($module=='home')?cookie("history_goods"):(($module=='mobile')?cookie("mo_history_goods"):cookie("wx_history_goods"));
		}

		// 当前无浏览记录，取热销商品
	    $where = [];
	    $where[] = ['isSale','=',1];
	    $where[] = ['goodsStatus','=',1];
	    $where[] = ['dataFlag','=',1];
	    $where[] = ['g.goodsId','in',$ids];
        $goods_group = Db::name('goods')->alias('g')
                       ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang(),'inner')
	                   ->where($where)
	                   ->group('goodsCatId')
	                   ->column('goodsCatId');
	    $goods = Db::name('goods')->alias('g')
	               ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang(),'inner')->field('g.goodsId,gl.goodsName,goodsImg,goodsSn,goodsStock,saleNum,shopPrice,marketPrice,isSpec,appraiseNum,visitNum,isNew')
                   ->where([['isSale','=',1],['goodsStatus','=',1],['dataFlag','=',1]])
	    		   ->where([['goodsCatId','in',$goods_group]])
	    		   ->limit(3*$num)
	    		   ->select();

	    if(empty($goods))return $this->listByGoods('hot',$catId,$num);

	    // 从数组中随机取$num个单元
	    shuffle($goods);
	    $goods = array_slice($goods,0,$num);
        return $goods;
	}
	/**
	 * 浏览商品
	 */
	public function historyByGoods($num){
		$hids = $ids = cookie("history_goods");
		if(empty($ids))return [];
	    $where = [];
	    $where[] = ['isSale','=',1];
	    $where[] = ['goodsStatus','=',1];
	    $where[] = ['g.dataFlag','=',1];
	    $where[] = ['g.goodsId','in',$ids];
	    $orderBy = "field(g.goodsId,".implode(',',$ids).")";
        $goods = Db::name('goods')->alias('g')->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                   ->join('__SHOPS__ s','g.shopId=s.shopId')
                   ->where($where)->field('s.shopName,s.shopId,g.goodsId,gl.goodsName,goodsImg,goodsSn,goodsStock,saleNum,shopPrice,marketPrice,isSpec,appraiseNum,visitNum')
                   ->limit($num)
                   ->orderRaw($orderBy)
                   ->select();
        $ids = [];
        foreach($goods as $key =>$v){
        	if($v['isSpec']==1)$ids[] = $v['goodsId'];
        }
        if(!empty($ids)){
        	$specs = [];
        	$rs = Db::name('goods_specs gs ')->where([['goodsId','in',$ids],['dataFlag','=',1]])->order('id')->select();
        	foreach ($rs as $key => $v){
        		$specs[$v['goodsId']] = $v;
        	}
        	foreach($goods as $key =>$v){
        		if(isset($specs[$v['goodsId']]))
        		$goods[$key]['specs'] = $specs[$v['goodsId']];
        	}
        }
        return $goods;
	}
	/**
	 * 推荐商品
	 */
	public function listByGoods($type,$catId,$num,$cache = 0){
		if(!in_array($type,[0,1,2,3]))return [];
		$cacheData = WSTCache('TAG_GOODS_'.$type."_".$catId."_".$num."_".$cache);
		if($cacheData)return WSTMergeLang($cacheData);
		//检测是否有数据
		$types = ['recom'=>0,'new'=>3,'hot'=>1,'best'=>2];
        $where = [];
        $where[] = ['r.dataSrc','=',0];
        $where[] = ['g.isSale','=',1];
        $where[] = ['g.goodsStatus','=',1];
        $where[] = ['g.dataFlag','=',1];
        $goods=[];
        if($type!='visit'){
	        $where[] = ['r.dataType','=',$types[$type]];
	        $where[] = ['r.goodsCatId','=',(int)$catId];
	        $goods = Db::name('goods')->alias('g')
	                   ->join('__RECOMMENDS__ r','g.goodsId=r.dataId')
	                   ->join('__SHOPS__ s','g.shopId=s.shopId')
	                   ->where($where)->field('s.shopName,s.shopId,g.goodsId,goodsImg,goodsSn,goodsStock,saleNum,shopPrice,marketPrice,isSpec,appraiseNum,visitNum,isNew')//gl.goodsTips,gl.goodsName
	                   ->order('r.dataSort asc')->limit($num)->select();

        }
        //判断有没有设置，如果没有设置的话则获取实际的数据
	    if(empty($goods)){
	    	$goodsCatIds = WSTGoodsCatPath($catId);
	    	$types = ['recom'=>'isRecom','new'=>'isNew','hot'=>'isHot','best'=>'isBest'];
	    	$order = ['recom'=>'saleNum desc,goodsId asc',
	    			  'new'=>'saleTime desc,goodsId asc',
	    			  'hot'=>'saleNum desc,goodsId asc',
	    			  'best'=>'saleNum desc,goodsId asc',
	    			  'visit'=>'visitNum desc'
	    			 ];

	    	$where = [];
	        $where[] = ['isSale','=',1];
	        $where[] = ['goodsStatus','=',1];
	        $where[] = ['g.dataFlag','=',1];

	        if($type!='visit')
	        $where[] = [$types[$type],'=',1];



	        if(!empty($goodsCatIds))$where[] = ['g.goodsCatIdPath','like',implode('_',$goodsCatIds).'_%'];
        	$goods = Db::name('goods')->alias('g')
        	       ->join('__SHOPS__ s','g.shopId=s.shopId')
                   ->where($where)->field('s.shopName,s.shopId,g.goodsId,goodsImg,goodsSn,goodsStock,saleNum,shopPrice,marketPrice,isSpec,appraiseNum,visitNum,isNew')//gl.goodsTips,gl.goodsName,
                   ->order($order[$type])->limit($num)->select();
        }
        $ids = [];
        $goodsIds = [];
        foreach($goods as $key =>$v){
        	if($v['isSpec']==1)$ids[] = $v['goodsId'];
        	$goodsIds[] = $v['goodsId'];
        }
        $goodsMap = [];
        //加载多语言
        if(count($goodsIds)>0){
           $gpm = Db::name('goods_langs')->where([['goodsId','in',$goodsIds]])->field('goodsTips,goodsName,goodsId,langId')->select();
           foreach($gpm as $key =>$v){
           	  $goodsMap[$v['goodsId']][$v['langId']] = $v;
           }
           foreach($goods as $key =>$v){
           	   $goods[$key]['langParams'] = $goodsMap[$v['goodsId']];
           }
        }
        if(!empty($ids)){
        	$specs = [];
        	$rs = Db::name('goods_specs gs ')->where([['goodsId','in',$ids],['dataFlag','=',1]])->order('id asc')->select();
        	foreach ($rs as $key => $v){
        		$specs[$v['goodsId']] = $v;
        	}
        	foreach($goods as $key =>$v){
        		if(isset($specs[$v['goodsId']]))
        		$goods[$key]['specs'] = $specs[$v['goodsId']];
        	}
        }
        WSTCache('TAG_GOODS_'.$type."_".$catId."_".$num."_".$cache,$goods,$cache,'CACHETAG_GOODS');
        //加载当前语言
        return WSTMergeLang($goods);
	}

	/**
	 * 获取广告位置
	 */
	public function listAds($positionCode,$num,$cache = 0,$sortType=0){
		$cacheData = WSTCache('TAG_ADS'.$positionCode."_".$cache);
		if($cacheData)return $cacheData;
		$today = date('Y-m-d');
		$order = ($sortType==0)?'adSort':'createTime desc';
		$rs = Db::name("ads")->alias('a')->join('__AD_POSITIONS__ ap','a.adPositionId= ap.positionId and ap.dataFlag=1','left')
		          ->where("a.dataFlag=1 and ap.positionCode='".$positionCode."' and adStartDate<= '$today' and adEndDate>='$today'")
		          ->field('adId,adName,adURL,subTitle,adFile,positionWidth,positionHeight')
		          ->order($order)->limit($num)->select();
		if(count($rs)>0){
			foreach ($rs as $key => $v) {
				 $rs[$key]['isOpen'] = false;
				if(stripos($v['adURL'],'http:')!== false || stripos($v['adURL'],'https:')!== false){
                     $rs[$key]['isOpen'] = true;
				}
			}
		}
		WSTCache('TAG_ADS'.$positionCode."_".$cache,$rs,$cache,'CACHETAG_ADS');
		return $rs;
	}

	/**
	 * 获取友情链接
	 */
	public function listFriendlink($num,$cache = 0){
		$cacheData = WSTCache('TAG_FRIENDLINK_'.$cache);
		if($cacheData)return $cacheData;
		$rs = Db::name("friendlinks")->where(["dataFlag"=>1])->order("friendlinkSort asc")->select();
		WSTCache('TAG_FRIENDLINK_'.$cache,$rs,$cache,'CACHETAG_FRIENDLINK');
	    return $rs;
	}

    /**
	 * 获取文章列表
	 */
	public function listArticle($catId,$num,$cache = 0){
		$rs = [];
		if($catId=='new'){
			$rs = $this->listByNewArticle($num,$cache);
		}else{
			$rs = $this->listByArticle($catId,$num,$cache);
		}
		return $rs;
	}
    /**
	 * 获取最新文章
	 */
	public function listByNewArticle($num,$cache){
		$cacheData = WSTCache('TAG_NEW_ARTICLES_'.$cache);
		if($cacheData)return WSTMergeLang($cacheData);
		$rs = Db::name('articles')->alias('a')
		        ->join('article_cats ac','a.catId=ac.catId','inner')
		        ->where('ac.catType=0 and ac.parentId<>7 and a.dataFlag=1 and ac.isShow=1 and a.isShow=1 and ac.dataFlag=1')
		        ->field('a.articleId,a.coverImg,a.createTime')
		        ->order('a.catSort asc,a.createTime desc')->limit($num)->select();//articleTitle
		//加载多语言
		if(count($rs)>0){
			$ids = [];
			foreach ($rs as $key => $v) {
                $ids[] = $v['articleId'];
			}
			$ars = Db::name('articles_langs')->where([['articleId','in',$ids]])->field('articleId,langId,articleTitle')->select();
			$armap = [];
			foreach ($ars as $key => $v) {
				$armap[$v['articleId']][$v['langId']] = $v;
			}
            foreach ($rs as $key => $v) {
            	$rs[$key]['langParams'] = $armap[$v['articleId']];
            }
		}
		WSTCache('TAG_NEW_ARTICLES_'.$cache,$rs,$cache,'CACHETAG_ARTICLE');
		//加载当前语言
	    return WSTMergeLang($rs);
	}
	/**
	 * 获取指定分类的文章
	 */
	public function listByArticle($catId,$num,$cache){
		$cacheData = WSTCache('TAG_ARTICLES_'.$catId."_".$num."_".$cache);
		if($cacheData)return WSTMergeLang($cacheData);
		$where = [];
		$where[] = ['dataFlag','=',1];
		$where[] = ['isShow','=',1];
		if(is_array($catId)){
		    $where[] = ['a.catId','in',$catId];
		}else{
			$where[] = ['a.catId','=',$catId];
		}
		$rs = Db::name('articles')->alias('a')
		         ->join('__ARTICLES_LANGS__ al','al.articleId=a.articleId and al.langId='.WSTCurrLang(),'inner')
		         ->where($where)
		         ->field("a.articleId, catId, coverImg,createTime")->order('catSort asc,createTime desc')->limit($num)->select();//al.articleTitle,al.articleContent,
        //加载多语言
        if(count($rs)>0){
        	$ids = [];
        	foreach ($rs as $key => $v) {
        		$ids[] = $v['articleId'];
        	}
        	$ars = Db::name('articles_langs')->where([['articleId','in',$ids]])->field('articleId,langId,articleTitle,articleContent')->select();
        	$amp = [];
        	foreach ($ars as $key => $v) {
        		$amp[$v['articleId']][$v['langId']] = $v;
        	}
        	foreach ($rs as $key => $v) {
        		$rs[$key]['langParams'] = $amp[$v['articleId']];
        	}
        }
		WSTCache('TAG_ARTICLES_'.$catId."_".$num."_".$cache,$rs,$cache,'CACHETAG_ARTICLE');
		//加载当前语言
		return WSTMergeLang($rs);
	}

	/**
	* 获取分类下的品牌
	*/
	public function listBrand($catId,$num,$cache = 0){
		$cacheData = WSTCache('TAG_BRANDS_'.$catId."_".$cache);
		if($cacheData)return $cacheData;
        $where = [];
        $where[] = ['r.dataSrc','=',2];
        $where[] = ['b.dataFlag','=',1];
        $where[] = ['r.dataType','=',0];
	    $where[] = ['r.goodsCatId','=',$catId];
	    $brands = Db::name('brands')->alias('b')->join('__RECOMMENDS__ r','b.brandId=r.dataId')
	                   ->where($where)->field('b.brandId,b.brandImg,b.brandName,r.goodsCatId catId')
	                   ->order('r.dataSort')->limit($num)->select();
        //为空的话就取分类关联的
        if(empty($brands)){
        	$where = [['b.dataFlag','=',1]];
        	if($catId>0){
        	 	$where[] = ['gc.catId','=',$catId];
		        $brands = Db::name('goods_cats')->alias('gc')
						   ->join('__CAT_BRANDS__ gcb','gc.catId=gcb.catId','inner')
						   ->join('__BRANDS__ b','gcb.brandId=b.brandId and b.dataFlag=1','inner')
						   ->field('b.brandId,b.brandImg,b.brandName,gcb.catId')
						   ->group('b.brandId,gcb.catId')
						   ->where('gc.dataFlag=1 and gc.isShow=1')
						   ->where($where)
						   ->limit($num)
						   ->select();
			}else{
                $brands = Db::name('brands')->field('brandId,brandImg,brandName,0 catId')->where(['dataFlag'=>1])->order('sortNo asc')->limit($num)->select();
			}
		}
		$brands = $this->unique_multidim_array($brands,'brandId');
        WSTCache('TAG_BRANDS_'.$catId."_".$cache,$brands,$cache,'CACHETAG_BRAND');
        return $brands;
	}
	// 二位数组去重
	protected function unique_multidim_array($array, $key) {
	    $temp_array = array();
	    $i = 0;
	    $key_array = array();
	    foreach($array as $val) {
	        if (!in_array($val[$key], $key_array)) {
	            $key_array[$i] = $val[$key];
	            $temp_array[$i] = $val;
	        }
	        $i++;
	    }
	    return $temp_array;
	}

	/**
	* 获取分类下的店铺
	*/
	public function listShop($catId,$num,$cache = 0){
		$cacheData = WSTCache('TAG_SHOPS_'.$catId."_".$cache);
		if($cacheData)return $cacheData;
        $where = [];
        $where[] = ['r.dataSrc','=',1];
        $where[] = ['b.dataFlag','=',1];
        $where[] = ['b.applyStatus','=',2];
        $where[] = ['r.dataType','=',0];
	    $where[] = ['r.goodsCatId','=',$catId];
	    $shops = Db::name('shops')->alias('b')->join('__RECOMMENDS__ r','b.shopId=r.dataId')
	                   ->join('__SHOP_CONFIGS__ sc','b.shopId=sc.shopId','inner')
	                   ->where($where)->field('b.shopId,b.shopImg,b.shopName,r.goodsCatId catId,sc.shopStreetImg')
	                   ->order('r.dataSort','asc')->limit($num)->select();
        //为空的话就取分类关联的
        if(empty($shops) && $catId>0){
	         $shops = Db::name('goods_cats')->alias('gc')
					   ->join('__CAT_SHOPS__ gcb','gc.catId=gcb.catId','inner')
					   ->join('__SHOPS__ b','gcb.shopId=b.shopId and b.shopStatus=1 and b.dataFlag=1','inner')
					   ->join('__SHOP_CONFIGS__ sc','b.shopId=sc.shopId','inner')
					   ->field('b.shopId,b.shopImg,b.shopName,gcb.catId,sc.shopStreetImg')
					   ->where('gc.dataFlag=1 and gc.isShow=1 and gc.catId='.$catId)
					   ->limit($num)
					   ->select();
		}
        WSTCache('TAG_SHOPS_'.$catId."_".$cache,$shops,$cache,'CACHETAG_SHOP');
        return $shops;
	}

	/**
	 * 获取订单列表
	 */
	public function listOrder($type,$num,$cache,$fields = ''){
		if(!in_array($type,['user','shop']))return [];
		$ownId = (int)($type=='user')?session('WST_USER.userId'):session('WST_USER.shopId');
		if($ownId==0)return [];
		if($fields=='')$fields = 'orderId,orderNo,createTime,orderStatus,payType,deliverType,userName,realTotalMoney';
        $data = WSTCache('TAG_ORDER_'.$type."_".$ownId."_".$cache);
        if(!$data){
        	$where = '';
        	if($type=='user')$where = 'userId='.$ownId;
        	if($type=='shop')$where = 'shopId='.$ownId;
            $db = Db::name('orders')->where($where)->limit($num)->order('createTime','desc');
            if($fields!='')$db->field($fields);
            $data =$db->select();
            if(!empty($data)){
            	$ids = [];
            	foreach ($data as $key => $v) {
            		$ids[] = $v['orderId'];
            	}
            	$goods = Db::name('order_goods')->where('orderId in ('.implode(',',$ids).')')->order('id','asc')->select();
                $goodsMap = [];
                foreach($goods as $g){
                    $goodsMap[$g['orderId']][] = $g;
                }
                foreach ($data as $key => $v) {
            		$data[$key]['goods'] = $goodsMap[$v['orderId']];
            	}
            }
            WSTCache('TAG_ORDER_'.$type."_".$ownId."_".$cache,$data,$cache);
        }
        return $data;
	}

	/**
	 * 获取收藏商品/商家列表
	 */
	public function listFavorite($type,$num,$fields = ''){
		if(!in_array($type,['goods','shop']))return [];
		$userId = (int)session('WST_USER.userId');
		if($userId==0)return [];
    	$where = 'userId='.$userId;
        if($type=='goods'){
           $db = Db::name('favorites')->alias('f');
           $db->join('__GOODS__ g','f.goodsId=g.goodsId and g.dataFlag=1 ');
           $db->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang(),'inner');
           if($fields=='')$fields = 'g.goodsId,gl.goodsName,g.goodsImg,g.isSale,g.shopPrice';
           $db->field($fields);
           $db->limit($num)->where($where);
           $db->order('favoriteId desc');
           return $db->select();
        }else{
           $db = Db::name('shop_members')->alias('f');
           $db->join('__SHOPS__ s','f.shopId=s.shopId and s.dataFlag=1 and s.shopStatus=1');
           if($fields=='')$fields = 's.shopName,s.shopId,s.shopImg';
           $db->field($fields);
           $db->limit($num)->where($where);
           $db->order('id desc');
           return $db->select();
        }


	}

	/**
	 * 获取搜索关键词
	 */
	public function listSearchkey($type,$cache = 0){
		$cacheData = WSTCache('TAG_SEARCHKEY_'.$type."_".$cache);
		if($cacheData)return $cacheData;
		$keys = WSTConf("CONF.hotWordsSearch");
		if($keys!=''){
			$keys = explode(',',$keys);
			if($type==1){
				foreach ($keys as $key => $v){
					$keys[$key] = [];
					$keys[$key]['name'] = $v;
					$where = [];
					$where[] = ['dataFlag','=',1];
					$where[] = ['isSale','=',1];
					$where[] = ['gl.goodsName','like','%'.$v.'%'];
					$keys[$key]['count'] = Db::name('goods')
                                        ->alias('g')
                                        ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                                        ->where($where)->count();
				}
			}
		}
		WSTCache('TAG_SEARCHKEY_'.$type."_".$cache,$keys,$cache);
		return $keys;
	}

	/**
	 * 获取高评分商品
	 */
	public function listScore($catId,$num,$cache = 0){
		$cacheData = WSTCache('TAG_SCORE_'.$catId."_".$cache);
		if($cacheData)return $cacheData;
		$scores = WSTConf("CONF.hotWordsSearch");
		if($scores!=''){
			$where = [];
			$where[] = ['serviceScore','>=',4];
			$where[] = ['g.dataFlag','=',1];
			$where[] = ['ga.dataFlag','=',1];
			$where[] = ['goodsScore','>=',4];
			$where[] = ['timeScore','>=',4];
			if($catId>0)$where[] = ['g.goodsCatIdPath','like',$catId."_%"];
			$scores = Db::name('goods')->alias('g')
			->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang(),'inner')
			->field('g.goodsId,g.goodsImg,gl.goodsName,g.shopPrice,ga.content,u.loginName,u.userName')
			->join('__GOODS_APPRAISES__ ga','g.goodsId=ga.goodsId','inner')
			->join('__USERS__ u','u.userId=ga.userId','inner')
			->where($where)
			->order('ga.createTime desc')
			->limit($num)
			->select();
		}
		WSTCache('TAG_SCORE_'.$catId."_".$cache,$scores,$cache);
		return $scores;
	}

	/**
	 * 获取店铺分类列表
	 */
	public function listShopCats($parentId=0,$num,$shopId = 0,$cache = 0){
		if($shopId==0)return [];
		$cacheData = WSTCache('TAG_SHOP_CATS_'.$shopId."_".$parentId."_".$num."_".$cache);
		if($cacheData)return WSTMergeLang($cacheData);
		$where = [];
		$where[] = ['isShow','=',1];
		$where[] = ['dataFlag','=',1];
		$where[] = ['shopId','=',$shopId];
		$where[] = ['parentId','=',$parentId];
		$data = Db::name('shop_cats')
        ->alias('a')
		->field('a.catId,shopId')//scl.catName
		->limit($num)->where($where)
		->order('catSort asc')->select();
		//加载多语言
		$ids = [];
		if(count($data)>0){
			foreach ($data as $key => $v) {
				$ids[] = $v['catId'];
			}
		}
		if(count($ids)>0){
			$crs = Db::name('shop_cats_langs')->where([['catId','in',$ids]])->select();
			$crsmp = [];
			foreach ($crs as $key => $v) {
				$crsmp[$v['catId']][$v['langId']] = $v;
			}
			foreach ($data as $key => $v) {
				$data[$key]['langParams'] = $crsmp[$v['catId']];
			}
		}
		WSTCache('TAG_SHOP_CATS_'.$shopId."_".$parentId."_".$num."_".$cache,$data,$cache,'CACHETAG_'.$shopId);
		return WSTMergeLang($data);
	}

	/**
	 * 获取指定店铺商品
	 */
	public function listShopGoods($type,$shopId,$cat = 0,$num = 0,$cache = 0){
		$cacheData = WSTCache('TAG_SHOP_GOODS_'.$type."_".$shopId."_".$cat."_".$num."_".$cache);
		if($cacheData)return WSTMergeLang($cacheData);
	    $types = ['recom'=>'isRecom','new'=>'isNew','hot'=>'isHot','best'=>'isBest'];
	    $order = ['recom'=>'saleNum desc,goodsId asc','new'=>'saleTime desc,goodsId asc','hot'=>'saleNum desc,goodsId asc','best'=>'saleNum desc,goodsId asc'];
	    if(!isset($types[$type]))return [];
	    $where = [];
	    if($cat>0){
		    $parentId = Db::name('shop_cats')->where(['catId'=>$cat,'shopId'=>$shopId,'dataFlag'=>1,'isShow'=>1])->value('parentId');
	        if($parentId>0){
                 $where[] = ['shopCatId2','=',$cat];
	        }else{
                 $where[] = ['shopCatId1','=',$cat];
	        }
	    }
	    $where[] = ['g.shopId','=',$shopId];
	    $where[] = ['isSale','=',1];
	    $where[] = ['goodsStatus','=',1];
	    $where[] = ['dataFlag','=',1];
	    $where[] = [$types[$type],'=',1];
        $goods = Db::name('goods')
                    ->alias('g')
                    ->join('__GOODS_SCORES__ gs','gs.goodsId = g.goodsId','left')
                   ->where($where)->field('g.goodsId,goodsImg,goodsSn,goodsStock,saleNum,shopPrice,marketPrice,isSpec,appraiseNum,visitNum,gs.totalScore,gs.totalUsers')//,gl.goodsName,
                   ->order($order[$type])->limit($num)->select();
        $gids = [];
        $ids = [];
        foreach($goods as $key =>$v){
        	 $gids[] = $v['goodsId'];
        	if($v['isSpec']==1)$ids[] = $v['goodsId'];
            $goods[$key]['praiseRate'] = ($v['totalScore']>0)?(sprintf("%.2f",$v['totalScore']/($v['totalUsers']*15))*100).'%':'100%';
        }
        if(count($gids)){
        	//加载多语言
        	$cgrs = Db::name('goods_langs')->where([['goodsId','in',$gids]])->field('goodsId,langId,goodsName')->select();
        	$cgrsmp = [];
        	foreach ($cgrs as $key => $v) {
				$cgrsmp[$v['goodsId']][$v['langId']] = $v;;
			}
			foreach($goods as $key =>$v){
        		$goods[$key]['langParams'] = $cgrsmp[$v['goodsId']];
        	}
        }
        if(!empty($ids)){
			//加载规格
        	$specs = [];
        	$rs = Db::name('goods_specs gs ')->where([['goodsId','in',$ids],['dataFlag','=',1]])->order('id')->select();
        	foreach ($rs as $key => $v){
        		$specs[$v['goodsId']] = $v;
        	}
        	foreach($goods as $key =>$v){
        		if(isset($specs[$v['goodsId']]))
        		$goods[$key]['specs'] = $specs[$v['goodsId']];
        	}
        }
        WSTCache('TAG_SHOP_GOODS_'.$type."_".$shopId."_".$cat."_".$num."_".$cache,$goods,$cache,'CACHETAG_'.$shopId);
        return WSTMergeLang($goods);
	}
}
