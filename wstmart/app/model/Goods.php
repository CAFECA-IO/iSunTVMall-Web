<?php
namespace wstmart\app\model;
use wstmart\common\model\Goods as CGoods;
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
 * 商品类
 */
class Goods extends CGoods{
	/**
	* 预加载商品
	*/
	public function preloadGoods(){
		$goodsId = (int)input('goodsId');
		$rs = $this->alias('g')
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                ->field('gl.goodsName,goodsImg')->where(['g.goodsId'=>$goodsId])->find();
		if(empty($rs))return WSTReturn(lang('no_find_goods'),-1);

		return $rs;
	}
	/**
	 * 获取列表
	 */
	public function pageQuery($goodsCatIds = []){
		//查询条件
		$keyword = input('keyword');
        hook('afterUserSearchWords',['keyword'=>$keyword]);
		$brandId = input('brandId/d');
		$shopId =  (int)input('shopId/d');
		$isFreeShipping = input('isFreeShipping/d');
		$where = $where2 = $where3 = $where4 = [];
		$where[] = ['goodsStatus','=',1];
		$where[] = ['g.dataFlag','=',1];
		$where[] = ['isSale','=',1];
        $recommendWhere = [];
        $recommendOrder = [];
        if($keyword!=''){
            $where2 = $this->getKeyWords($keyword);
            $recommendGoodsId = [];
            hook('userSearchWordsCondition',['recommendGoodsId'=>&$recommendGoodsId,'keyword'=>$keyword]);
            if($recommendGoodsId!=''){
                foreach($recommendGoodsId as $v){
                    $recommendWhere[] = "find_in_set(g.goodsId,'".$v."')";
                    $recommendOrder[] = "find_in_set(g.goodsId,'".$v."') desc";
                }
            }
        }
        if($brandId>0)$where[] = ['g.brandId','=',$brandId];
        if($shopId&&$shopId>0)$where[] = ['g.shopId','=',$shopId];
		//排序条件
		$orderBy = input('condition/d',0);
		$orderBy = ($orderBy>=0 && $orderBy<=4)?$orderBy:0;
		$order = (input('desc/d',0)==1)?1:0;
		$pageBy = ['saleNum','shopPrice','visitNum','saleTime'];
		$pageOrder = ['desc','asc'];
		if($isFreeShipping==1)$where[] = ['isFreeShipping','=',1];

		//属性筛选
		$goodsIds = $this->filterByAttributes();
		//处理价格
		$minPrice = input("minPrice/d");//开始价格
		$maxPrice = input("maxPrice/d");//结束价格
		if($minPrice!="")$where3 = "g.shopPrice >= ".(float)$minPrice;
		if($maxPrice!="")$where4 = "g.shopPrice <= ".(float)$maxPrice;

		if(!empty($goodsIds))$where[] = ['g.goodsId','in',$goodsIds];
		if(!empty($goodsCatIds))$where[] = ['goodsCatIdPath','like',implode('_',$goodsCatIds).'_%'];
        $list = Db::name('goods')->alias('g')
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                ->join("__SHOPS__ s","g.shopId = s.shopId")
                ->join('__GOODS_SCORES__ gs','gs.goodsId=g.goodsId')
                ->where($where)
                ->where(function ($query) use($where2) {
                    $query->whereOr($where2);
                })
                ->where($where3)
                ->where($where4);
        foreach($recommendWhere as $v){
            $list->whereOrRaw($v);
        }
        $list = $list->field('g.shopId,g.goodsId,gl.goodsName,g.saleNum,g.shopPrice,g.goodsImg,g.isFreeShipping,gs.totalScore,gs.totalUsers');
        foreach($recommendOrder as $v){
            $list->orderRaw($v);
        }
        $list = $list->order($pageBy[$orderBy]." ".$pageOrder[$order].",goodsId asc")
            ->paginate(input('pagesize/d'))->toArray();
		return $list;
	}

	/**
	 * 关键字
	 */
	private function getKeyWords($name){
		$words = WSTAnalysis($name);
		if(!empty($words)){
			if(count($words)==1){
				return "g.goodsSerachKeywords LIKE '%$words[0]%' ";
			}else{
				$str = [];
				foreach ($words as $v){
					$str[] = " g.goodsSerachKeywords LIKE '%$v%' ";
				}
				return implode(" and ",$str);
			}
		}
		return "";
	}

	/**
	 * 获取商品资料在前台展示
	 */
	public function getBySale($goodsId,$userId=0){
		$key = input('key');
		// 浏览量
		$this->where('goodsId',$goodsId)->setInc('visitNum',1);
		//成本价不能泄露
		$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->field('g.*,gl.goodsName,gl.goodsTips,gl.goodsDesc,gl.goodsSeoKeywords,gl.goodsSeoDesc')->where(['g.goodsId'=>$goodsId,'dataFlag'=>1])->find();
		WSTUnset($rs,'costPrice');
		if(isset($rs['goodsName']))$rs['goodsName'] = htmlspecialchars_decode($rs['goodsName']);
		$uId = model('index')->getUserId();

		if(!empty($rs)){
			$rs['read'] = false;
			//判断是否可以公开查看
			$viKey = WSTShopEncrypt($rs['shopId']);
			if(($rs['isSale']==0 || $rs['goodsStatus']==0) && $viKey != $key)return [];
			if($key!='')$rs['read'] = true;
            // 获取会员分组信息
            $rs['shopMemberGroups'] = Db::name('shop_member_groups')->where(['shopId'=>(int)$rs['shopId']])->select();
            $reduceMoney = 0;
            if($userId>0){
                $reduceMoney = WSTGetMemberGoodsReduceMoney($goodsId,$userId,$rs['shopId']);
            }
            if($reduceMoney>0)$rs['shopPrice'] = WSTBCMoney($rs['shopPrice'],-$reduceMoney);
			//获取店铺信息
			$rs['shop'] = model('shops')->getShopInfo((int)$rs['shopId'],$uId);
			if(empty($rs['shop']))return [];
			$goodsCats = Db::name('cat_shops')->alias('cs')->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')->join('__SHOPS__ s','s.shopId = cs.shopId','left')
			->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
			->where('cs.shopId',$rs['shopId'])->field('cs.shopId,s.shopTel,gc.catId,gcl.catName')->select();
			$rs['shop']['catId'] = $goodsCats[0]['catId'];
			$rs['shop']['shopTel'] = $goodsCats[0]['shopTel'];

			$cat = [];
			foreach ($goodsCats as $v){
				$cat[] = $v['catName'];
			}
			$rs['shop']['cat'] = implode('，',$cat);
			if(empty($rs['shop']))return [];
			$gallery = [];
			$gallery[] = $rs['goodsImg'];
			if($rs['gallery']!=''){
				$tmp = explode(',',$rs['gallery']);
				$gallery = array_merge($gallery,$tmp);
			}
			$rs['gallery'] = $gallery;
			//获取规格值
			$specs = Db::name('spec_cats')->alias('gc')->join('__SPEC_ITEMS__ sit','gc.catId=sit.catId','inner')
                    ->join('__SPEC_CATS_LANGS__ scl','gc.catId=scl.catId and scl.langId='.WSTCurrLang())
                    ->join('__SPEC_ITEMS_LANGS__ sil','sit.itemId=sil.itemId and sil.langId='.WSTCurrLang())
                    ->where(['sit.goodsId'=>$goodsId,'gc.isShow'=>1,'sit.dataFlag'=>1])
                    ->field('gc.isAllowImg,scl.catName,sit.catId,sit.itemId,sil.itemName,sit.itemImg')
                    ->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
			$rs['spec']=[];
			foreach ($specs as $key =>$v){
				$rs['spec'][$v['catId']]['name'] = $v['catName'];
				$rs['spec'][$v['catId']]['list'][] = $v;
			}
			sort($rs['spec']);
			foreach ($rs['spec'] as $k=>$v){
			    if($v['list'][0]['isAllowImg']==1){
			       $spec1[]=$rs['spec'][$k];
			       unset($rs['spec'][$k]);
                    $rs['spec']=array_merge($spec1,$rs['spec']);
                }
            }
			//获取销售规格
			$sales = Db::name('goods_specs')->where('goodsId',$goodsId)->field('id,isDefault,productNo,specIds,marketPrice,specPrice,specStock')->select();
			if(!empty($sales)){
                if($reduceMoney>0){
                    foreach ($sales as $key =>$v) {
                        $sales[$key]['specPrice'] =  WSTBCMoney($v['specPrice'],-$reduceMoney);
                    }
                }
				foreach ($sales as $key =>$v){
					$str = explode(':',$v['specIds']);
					sort($str);
					unset($v['specIds']);
					$rs['saleSpec'][implode(':',$str)] = $v;
				}
			}
			//获取商品属性
			$rs['attrs'] = Db::name('attributes')->alias('a')
                ->join('__ATTRIBUTES_LANGS__ al','a.attrId=al.attrId and al.langId='.WSTCurrLang())
                ->join('goods_attributes ga','a.attrId=ga.attrId','inner')
                ->join('__GOODS_ATTRIBUTES_LANGS__ gal','ga.id=gal.goodsAttrId and gal.langId='.WSTCurrLang())
                ->where(['a.isShow'=>1,'dataFlag'=>1,'ga.goodsId'=>$goodsId])->field('al.attrName,gal.attrVal')
                ->order('attrSort asc')->select();

			//获取商品评分
			$scores = $rs['shop']['scores'];
			$rs['shop']['scores']['totalScores'] = ($scores['totalScore']==0)?5:WSTScore($scores['totalScore'],$scores['totalUsers'],5,0,3);

			WSTUnset($rs, 'totalUsers');

			//关注
			$rs['favShop'] = $rs['shop']['isfollow'];// 大于0表示关注Id
			$rs['favGood'] = model('common/Favorites')->checkFavorite($goodsId,$uId);



			// 获取一条商品评价
			$appr = model('GoodsAppraises')->getGoodsFirstAppraise($goodsId);
			if(!empty($appr)){
				// 若未设置头像,则取商城默认头像
				$appr['userPhoto'] = ($appr['userPhoto']!='')?$appr['userPhoto']:WSTConf('CONF.userLogo');
				// 过滤html标签
				$appr['content'] = strip_tags($appr['content']);
				// 处理匿名
				$start = floor((strlen($appr['loginName'])/2))-1;
				$appr['loginName'] = substr_replace($appr['loginName'],'**',$start,2);
			}
			$rs['goodsAppr'] = $appr;


		}
		return $rs;
	}
	// 获取商品详情
	public function getGoodsDetail($goodsId=0){
		// 未传递goodsId返回空数组
		if($goodsId <= 0)return [];
		return Db::name('goods')
                ->alias('g')
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                ->field('gl.goodsDesc')->where(['g.goodsId'=>$goodsId,'dataFlag'=>1])->find();
	}


	public function historyQuery(){
		$ids = input('history');
		if(empty($ids))return [];
	    $where = [];
	    $where[] = ['isSale','=',1];
	    $where[] = ['goodsStatus','=',1];
	    $where[] = ['dataFlag','=',1];
	    $where[] = ['g.goodsId','in',$ids];
        return Db::name('goods')
                ->alias('g')
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                ->where($where)->field('g.goodsId,gl.goodsName,goodsImg,saleNum,shopPrice')
                ->orderRaw("find_in_set(g.goodsId,'".$ids."')")
                ->select();
	}

	/**
     * 获取符合筛选条件的商品ID
     */
    public function filterByAttributes(){
    	$vs = input('vs');
    	if($vs=='')return [];
    	$vs = explode(',',$vs);
    	$goodsIds = [];
    	$prefix = config('database.prefix');
		//循环遍历每个属性相关的商品ID
	    foreach ($vs as $v){
	    	$goodsIds2 = [];
	    	$attrVal = input('v_'.(int)$v);
	    	if($attrVal=='')continue;
		    	$sql = "select goodsId from ".$prefix."goods_attributes 
		    	where attrId=".(int)$v." and find_in_set('".$attrVal."',attrVal) ";
				$rs = Db::query($sql);
				if(!empty($rs)){
					foreach ($rs as $vg){
						$goodsIds2[] = $vg['goodsId'];
					}
				}
			//如果有一个属性是没有商品的话就不需要查了
			if(empty($goodsIds2))return [-1];
			//第一次比较就先过滤，第二次以后的就找集合
			$goodsIds2[] = -1;
			if(empty($goodsIds)){
				$goodsIds = $goodsIds2;
			}else{
				$goodsIds = array_intersect($goodsIds,$goodsIds2);
			}
		}
		return $goodsIds;
    }
}
