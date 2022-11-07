<?php
namespace addons\combination\model;
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
 * 商品组合插件
 */
class Combinations extends Base{
	/***
     * 安装插件
     */
    public function installMenu(){
    	Db::startTrans();
		try{
			$hooks = [
			'homeDocumentGoodsDetailSalePromotion',
			'mobileDocumentGoodsDetailSalePromotion',
		    'wechatDocumentGoodsDetailSalePromotion'
		    ];
			$this->bindHoods("Combination", $hooks);
			$now = date("Y-m-d H:i:s");
			//商家中心
            $homeMenuLangParams = [
                1=>['menuName'=>'組合套餐'],
                2=>['menuName'=>'组合套餐'],
                3=>['menuName'=>'Combination package'],
            ];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>77,"menuUrl"=>"/addon/combination-shops-index","menuOtherUrl"=>"/addon/combination-shops-pageQuery,/addon/combination-shops-edit,/addon/combination-shops-toEdit,/addon/combination-shops-del,/addon/combination-shops-saleGoodsPageQuery,/addon/combination-shops-listQueryByGoodsIds,/addon/combination-shops-changeStatus","menuType"=>1,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"combination"]);
            $datas = [];
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $homeMenuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('home_menus_langs')->insertAll($datas);
			//新增上传目录
            $dataLangParams = [
                1=>['dataName'=>'組合套餐'],
                2=>['dataName'=>'组合套餐'],
                3=>['dataName'=>'Combination package'],
            ];
            $datas = [];
            $dataId = Db::name('datas')->insertGetId(['catId'=>3,'dataVal'=>'combination']);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['dataId'] = $dataId;
                $data['langId'] = $v['id'];
                $data['dataName'] = $dataLangParams[$v['id']]['dataName'];
                $datas[] = $data;
            }
            Db::name('datas_langs')->insertAll($datas);
			installSql("combination");
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
			$hooks = [
			'homeDocumentGoodsDetailSalePromotion',
			'mobileDocumentGoodsDetailSalePromotion',
		    'wechatDocumentGoodsDetailSalePromotion'
		    ];
			$this->unbindHoods("Combination", $hooks);
            $homeMenuId = Db::name('home_menus')->where(["menuMark"=>"combination"])->value('menuId');
            Db::name('home_menus')->where(["menuMark"=>"combination"])->delete();
            Db::name('home_menus_langs')->where(['menuId'=>$homeMenuId])->delete();

            $dataId = Db::name('datas')->where(["dataVal"=>"combination"])->value('id');
            Db::name('datas')->where(["dataVal"=>"combination"])->delete();
            Db::name('datas_langs')->where(["dataId"=>$dataId])->delete();
			uninstallSql("combination");//传入插件名
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
            Db::name('home_menus')->where(["menuMark"=>"combination"])->update(["isShow"=>$isShow]);
            Db::commit();
            return true;
        }catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

	/**
	 * 获取商品组合列表-商家
	 */
	public function pageQueryByShop($sId = 0){
		$combineName = input('combineName');
		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$where = [];
		$where[]=['shopId','=',$shopId];
		$where[]=['dataFlag','=',1];
		if($combineName !='')$where[] = ['cl.combineName', 'like', '%'.$combineName.'%'];
        $page =  $this
                ->alias('c')
                ->join('__COMBINATIONS_LANGS__ cl','cl.combineId=c.combineId and cl.langId='.WSTCurrLang())
                ->where($where)
                ->field('c.*,cl.combineName')
                ->order('combineOrder asc,combineId desc')
                ->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	$time = time();
        	foreach($page['data'] as $key =>$v){
        		$page['data'][$key]['combineImgThumb'] = WSTImg($v['combineImg']);
        		if(strtotime($v['startTime'])<=$time && strtotime($v['endTime'])>=$time){
        			$page['data'][$key]['status'] = 1;
        		}else if(strtotime($v['startTime'])>$time){
                    $page['data'][$key]['status'] = 0;
        		}else{
        			$page['data'][$key]['status'] = -1;
        		}
        	}
        }
        $page['status'] = 1;
        return $page;
	}

	/**
	 * 获取在售商品列表
	 */
	public function saleGoodsPageQuery($sId=0){
		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$where = [];
		$where[] = ['shopId',"=",$shopId];
		$where[] = ['goodsStatus',"=",1];
		$where[] = ['dataFlag',"=",1];
		$where[] = ['isSale',"=",1];
		$where[] = ['goodsType',"=",0];
		$c1Id = (int)input('cat1');
		$c2Id = (int)input('cat2');
		$goodsName = input('goodsName');
		if($goodsName != '')$where[] = ['gl.goodsName|goodsSn','like',"%$goodsName%"];
		if($c2Id!=0 && $c1Id!=0){
			$where[] = ['shopCatId2',"=",$c2Id];
		}else if($c1Id!=0){
			$where[] = ['shopCatId1',"=",$c1Id];
		}
		$rs = Db::name('goods')
                ->alias('g')
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                ->where($where)
			    ->field('g.goodsId,gl.goodsName,goodsImg,goodsStock,saleNum,shopPrice')
			    ->order('saleTime', 'desc')
			    ->paginate(20)->toArray();
		foreach ($rs['data'] as $key => $v){
			$rs['data'][$key]['goodsImgThumb'] = WSTImg($v['goodsImg']);
		}
		return $rs;
	}

	/**
	 * 根据商品ID获取商品列表
	 */
	public function listQueryByGoodsIds($sId=0){
		$goodsId = (int)input('goodsId');
		$combineGoodsIds = WSTFormatIn(',',input('combineGoodsIds'),false);
        if($goodsId!=0)$combineGoodsIds[] = $goodsId;
        if(count($combineGoodsIds)==0)return WSTReturn('',1,[]);
        $shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$where = [];
		$where[] = ['shopId',"=",$shopId];
		$where[] = ['goodsType',"=",0];
		$where[] = ['g.goodsId','in',$combineGoodsIds];
        $rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->where($where)->field('g.goodsId,gl.goodsName,goodsImg,shopPrice,isSale,goodsStatus,dataFlag')->select();
        foreach ($rs as $key => $v){
        	$rs[$key]['isOut'] = false;
        	if($v['isSale']==0 || $v['goodsStatus']!=1 || $v['dataFlag']!=1){
        		$rs[$key]['isOut'] = 1;
        		WSTUnset($rs[$key],'isSale,goodsStatus,dataFlag');
        	}
			$rs[$key]['goodsImgThumb'] = WSTImg($v['goodsImg']);
		}
		return WSTReturn('',1,$rs);
	}
	/**
	 * 获取组合商品信息【用于编辑】
	 */
	public function getById($combineId, $sId=0){
		$shopId = $shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $obj = Db::name('combinations')
                ->alias('c')
                ->join('__COMBINATIONS_LANGS__ cl','cl.combineId=c.combineId and cl.langId='.WSTCurrLang())
                ->field('c.*,cl.combineName,cl.combineDesc')
                ->where(['shopId'=>$shopId,'dataFlag'=>1,'c.combineId'=>$combineId])->find();
        if(empty($obj))return [];
        $mgoods = Db::name('combination_goods')->alias('cg')
                         ->join('__GOODS__ g','g.goodsId=cg.goodsId','inner')
                         ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                         ->where(['cg.combineId'=>$combineId])
                         ->order('cg.goodsType desc')
                         ->field('cg.id,g.goodsImg,gl.goodsName,g.shopPrice,cg.goodsId,cg.reduceMoney,g.isSale,g.goodsStatus,g.dataFlag,cg.goodsType')
                         ->select();
        $ids = [];
        $data = [];
        foreach ($mgoods as $key => $v) {
        	$v['isOut'] = 0;
        	if($v['isSale']==0 || $v['goodsStatus']!=1 || $v['dataFlag']!=1){
        		$v['isOut'] = 1;
        		WSTUnset($v[$key],'isSale,goodsStatus,dataFlag');
        	}
        	if($v['goodsType']==1){
        		$obj['goodsId'] = $v['goodsId'];
        	}else{
        		$ids[] = $v['goodsId'];
        	}
        	$data[$v['goodsId']] = $v;
        }
        $obj['combineGoodsIds'] = implode(',',$ids);
        $obj['list'] = $data;
        $obj['langParams'] = Db::name('combinations_langs')->where(['combineId'=>$combineId])->column('*','langId');
        return $obj;
	}
	/**
	 * 新增商品组合套餐
	 */
	public function add($sId=0){
		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$data = [];
		$data['shopId'] = $shopId;
        foreach (WSTSysLangs() as $key => $v) {
            $combineName = input('langParams' . $v['id'] . 'combineName');
            if (strlen($combineName)>30) {
                return WSTReturn(lang('combination_combine_name_tips'));
            }
            $combineDesc = input('langParams' . $v['id'] . 'combineDesc');
            if (strlen($combineDesc)>150) {
                return WSTReturn(lang('combination_combine_desc_tips'));
            }
        }
		$data['combineImg'] = input('post.combineImg');
		if($data['combineImg']=='')return WSTReturn(lang('combination_require_combine_img'));
		$data['combineType'] = ((int)input('post.combineType',0)==1)?1:0;
		$data['isFreeShipping'] = ((int)input('post.isFreeShipping',0)==1)?1:0;
		$data['startTime'] = input('post.startTime');
		$data['endTime'] = input('post.endTime');
		if($data['endTime']=='' || $data['startTime']=='')return WSTReturn(lang('combination_require_select_time'));
		$data['combineStatus'] = ((int)input('post.combineStatus',1)==1)?1:0;
		$data['isAdvance'] = ((int)input('post.isAdvance')==1)?1:0;
		$data['advanceDay'] = ((int)input('post.advanceDay')>0)?(int)input('post.advanceDay'):0;
		if($data['isAdvance']==1 && $data['advanceDay']<=0)return WSTReturn(lang('combination_require_advance_day'));
		$goodsId = (int)input('post.goodsId');
		if($goodsId==0)return WSTReturn(lang('combination_require_main_goods'));
		$chk = Db::name('goods')->where(['goodsId'=>$goodsId,'shopId'=>$shopId,'goodsStatus'=>1,'isSale'=>1,'dataFlag'=>1])->find();
		if(empty($chk))return WSTReturn(lang('combination_require_sale_main_goods'));
        $reduceMoney = (float)input('post.reduceMoney');
        if($reduceMoney<0)return WSTReturn(lang('combination_reduce_money_tips'));
        if($chk['shopPrice']<=$reduceMoney)return WSTReturn(lang('combination_reduce_money_tips2'));
        if($chk['isSpec']){
        	$minPrice = Db::name('goods_specs')->where(['shopId'=>$shopId,'goodsId'=>$goodsId,'dataFlag'=>1])->min('specPrice');
        	if($minPrice<=$reduceMoney)return WSTReturn(lang('combination_reduce_money_tips3'));
        }
        $data['combineOrder'] = (int)input('combineOrder');
        $combineGoodsIds = WSTFormatIn(',',input('post.combineGoodsIds'),false);
		if(count($combineGoodsIds)==0)return WSTReturn(lang('combination_require_combine_goods'));
		if(in_array($goodsId,$combineGoodsIds))return WSTReturn(lang('combination_not_select_same_goods'));
		$cgoods = Db::name('goods')
                    ->alias('g')
                    ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                    ->where([['g.goodsId','in',$combineGoodsIds],['shopId','=',$shopId]])
                    ->field('g.goodsId,gl.goodsName,isSale,goodsStatus,dataFlag,shopPrice,isSpec')->select();
		if(empty($cgoods))return WSTReturn(lang('combination_require_sale_combine_goods'));
		foreach ($cgoods as $key => $v) {
			if($v['isSale']!=1 || $v['goodsStatus']!=1 || $v['dataFlag']!=1)return WSTReturn(lang('combination_invalid_goods').'-'.$v['goodsName']);
		}
		Db::startTrans();
		try{
			$data['createTime'] = date('Y-m-d h:i:s');
			$data['dataFlag'] = 1;
			$combineId = Db::name('combinations')->insertGetId($data);
			if($combineId>0){
				//WSTUseResource(0, $combineId, $data['combineImg']);
                $dataLangs = [];
                foreach (WSTSysLangs() as $key => $v) {
                    $dataLang = [];
                    $dataLang['combineId'] = $combineId;
                    $dataLang['langId'] = $v['id'];
                    $dataLang['combineName'] = input('langParams'.$v['id'].'combineName');
                    $dataLang['combineDesc'] = input('langParams'.$v['id'].'combineDesc');
                    $dataLangs[] = $dataLang;
                }
                Db::name('combinations_langs')->insertAll($dataLangs);
				$idata = [];
				$iidata = [];
		        $iidata['combineId'] = $combineId;
		        $iidata['goodsId'] = $goodsId;
		        $iidata['reduceMoney'] = $reduceMoney;
		        $iidata['goodsType'] = 1;
		        $idata[] = $iidata;
		        foreach ($cgoods as $key => $v) {
		        	$iidata = [];
		        	$iidata['combineId'] = $combineId;
		        	$iidata['goodsId'] = $v['goodsId'];
		        	$iidata['reduceMoney'] = (float)input('post.reduceMoney_'.$v['goodsId']);
		        	if($iidata['reduceMoney']<0)return WSTReturn(lang('combination_combine_reduce_money_tips'));
		        	if($v['shopPrice']<=$iidata['reduceMoney'])return WSTReturn(lang('combination_combine_reduce_money_tips2'));
			        if($v['isSpec']){
			        	$minPrice = Db::name('goods_specs')->where(['shopId'=>$shopId,'goodsId'=>$v['goodsId'],'dataFlag'=>1])->min('specPrice');
			        	if($minPrice<=$iidata['reduceMoney'])return WSTReturn(lang('combination_combine_reduce_money_tips3'));
			        }
		        	$iidata['goodsType'] = 0;
		        	$idata[] = $iidata;
		        }
		        Db::name('combination_goods')->insertAll($idata);
		        Db::commit();
		        return WSTReturn(lang('combination_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
		return WSTReturn(lang('combination_operation_fail'));
	}

	/**
	 * 编辑商品组合套餐
	 */
	public function edit($sId=0){
		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$combineId = (int)input('post.combineId');
		$data = [];
        foreach (WSTSysLangs() as $key => $v) {
            $combineName = input('langParams' . $v['id'] . 'combineName');
            if (strlen($combineName)>30) {
                return WSTReturn(lang('combination_combine_name_tips'));
            }
            $combineDesc = input('langParams' . $v['id'] . 'combineDesc');
            if (strlen($combineDesc)>150) {
                return WSTReturn(lang('combination_combine_desc_tips'));
            }
        }
		$data['combineImg'] = input('post.combineImg');
		if($data['combineImg']=='')return WSTReturn(lang('combination_require_combine_img'));
		$data['combineType'] = ((int)input('post.combineType',0)==1)?1:0;
		$data['isFreeShipping'] = ((int)input('post.isFreeShipping',0)==1)?1:0;
		$data['startTime'] = input('post.startTime');
		$data['endTime'] = input('post.endTime');
		if($data['endTime']=='' || $data['startTime']=='')return WSTReturn(lang('combination_require_select_time'));
		$data['combineStatus'] = ((int)input('post.combineStatus',1)==1)?1:0;
		$data['isAdvance'] = ((int)input('post.isAdvance')==1)?1:0;
		$data['advanceDay'] = ((int)input('post.advanceDay')>0)?(int)input('post.advanceDay'):0;
		if($data['isAdvance']==1 && $data['advanceDay']<=0)return WSTReturn(lang('combination_require_advance_day'));
		$goodsId = (int)input('post.goodsId');
		if($goodsId==0)return WSTReturn(lang('combination_require_main_goods'));
		$chk = Db::name('goods')->where(['goodsId'=>$goodsId,'shopId'=>$shopId,'goodsStatus'=>1,'isSale'=>1,'dataFlag'=>1])->find();
		if(empty($chk))return WSTReturn(lang('combination_require_sale_main_goods'));
        $reduceMoney = (float)input('post.reduceMoney');
        if($reduceMoney<0)return WSTReturn(lang('combination_reduce_money_tips'));
        if($chk['shopPrice']<=$reduceMoney)return WSTReturn(lang('combination_reduce_money_tips2'));
        if($chk['isSpec']){
        	$minPrice = Db::name('goods_specs')->where(['shopId'=>$shopId,'goodsId'=>$goodsId,'dataFlag'=>1])->min('specPrice');
        	if($minPrice<=$reduceMoney)return WSTReturn(lang('combination_reduce_money_tips3'));
        }
        $data['combineOrder'] = (int)input('combineOrder');
        $combineGoodsIds = WSTFormatIn(',',input('post.combineGoodsIds'),false);
		if(count($combineGoodsIds)==0)return WSTReturn(lang('combination_require_combine_goods'));
		if(in_array($goodsId,$combineGoodsIds))return WSTReturn(lang('combination_not_select_same_goods'));
		$cgoods = Db::name('goods')
                ->alias('g')
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                ->where([['g.goodsId','in',$combineGoodsIds],['shopId','=',$shopId]])
                ->field('g.goodsId,gl.goodsName,isSale,goodsStatus,dataFlag,shopPrice,isSpec')->select();
		if(empty($cgoods))return WSTReturn(lang('combination_require_sale_combine_goods'));
		foreach ($cgoods as $key => $v) {
			if($v['isSale']!=1 || $v['goodsStatus']!=1 || $v['dataFlag']!=1)return WSTReturn(lang('combination_invalid_goods').'-'.$v['goodsName']);
		}
		Db::startTrans();
		try{
			$data['createTime'] = date('Y-m-d h:i:s');
			$data['dataFlag'] = 1;
			$result = Db::name('combinations')->where(['shopId'=>$shopId,'dataFlag'=>1,'combineId'=>$combineId])->update($data);
			if($result!==false){
                Db::name('combinations_langs')->where(['combineId'=>$combineId])->delete();
                $dataLangs = [];
                foreach (WSTSysLangs() as $key => $v) {
                    $dataLang = [];
                    $dataLang['combineId'] = $combineId;
                    $dataLang['langId'] = $v['id'];
                    $dataLang['combineName'] = input('langParams'.$v['id'].'combineName');
                    $dataLang['combineDesc'] = input('langParams'.$v['id'].'combineDesc');
                    $dataLangs[] = $dataLang;
                }
                Db::name('combinations_langs')->insertAll($dataLangs);
				Db::name('combination_goods')->where(['combineId'=>$combineId])->delete();
				//WSTUseResource(0, $combineId, $data['combineImg']);
				$idata = [];
				$iidata = [];
		        $iidata['combineId'] = $combineId;
		        $iidata['goodsId'] = $goodsId;
		        $iidata['reduceMoney'] = $reduceMoney;
		        $iidata['goodsType'] = 1;
		        $idata[] = $iidata;
		        foreach ($cgoods as $key => $v) {
		        	$iidata = [];
		        	$iidata['combineId'] = $combineId;
		        	$iidata['goodsId'] = $v['goodsId'];
		        	$iidata['reduceMoney'] = (float)input('post.reduceMoney_'.$v['goodsId']);
		        	if($iidata['reduceMoney']<0)return WSTReturn(lang('combination_combine_reduce_money_tips'));
		        	if($v['shopPrice']<=$iidata['reduceMoney'])return WSTReturn(lang('combination_combine_reduce_money_tips2'));
			        if($v['isSpec']){
			        	$minPrice = Db::name('goods_specs')->where(['shopId'=>$shopId,'goodsId'=>$v['goodsId'],'dataFlag'=>1])->min('specPrice');
			        	if($minPrice<=$iidata['reduceMoney'])return WSTReturn(lang('combination_combine_reduce_money_tips3'));
			        }
		        	$iidata['goodsType'] = 0;
		        	$idata[] = $iidata;
		        }
		        Db::name('combination_goods')->insertAll($idata);
		        Db::commit();
		        return WSTReturn(lang('combination_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
		return WSTReturn(lang('combination_operation_fail'));
	}

	/**
	 * 删除
	 */
	public function del($sId=0){
		$id = (int)input('id');
		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$data = [];
		$data['shopId'] = $shopId;
		$data['combineId'] = $id;
        Db::startTrans();
		try{
			$rs = $this->update(['dataFlag'=>-1],$data);
			if($rs!==false){
				//WSTUnuseResource('combinations','combineImg',$id);
				Db::commit();
				return WSTReturn(lang('combination_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('combination_operation_fail'));
	}

    /**
     * 修改套餐状态
     */
    public function changeStatus($sId=0){
        $id = (int)input('id');
        $combineStatus = ((int)input('combineStatus')==1)?1:0;
		$shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
		$data = [];
		$data['shopId'] = $shopId;
		$data['combineId'] = $id;
		$rs = $this->where($data)->update(['combineStatus'=>$combineStatus]);
        return WSTReturn(lang('combination_operation_success'),1);
    }

    /**
     * 查询相关的套餐
     */
    public function getRelateCombinte($goodsId,$hasSpec = false){
        //获取该商品为主商品或者该商品为搭配行频的组合套餐
        $goods = Db::name('combinations')->alias('c')
                  ->join('__COMBINATIONS_LANGS__ cl','cl.combineId=c.combineId and cl.langId='.WSTCurrLang())
                  ->join('__COMBINATION_GOODS__ cg','c.combineId=cg.combineId')
                  ->join('__GOODS__ g','g.goodsId=cg.goodsId','inner')
                  ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                  ->join('__GOODS_SPECS__ gs','g.goodsId=gs.goodsId and isDefault=1','left')
                  ->where(['c.dataFlag'=>1,'cg.goodsId'=>$goodsId,'g.isSale'=>1,'g.goodsStatus'=>1,'g.dataFlag'=>1])
                  ->order('cg.goodsType desc,c.combineOrder asc,c.startTime asc,c.combineId asc')
                  ->field('c.combineId,cl.combineName,cl.combineDesc,c.combineImg,c.combineType,c.isAdvance,c.advanceDay,c.startTime,c.endTime,c.isFreeShipping,g.goodsImg,gl.goodsName,g.shopPrice,g.isSpec,cg.goodsId,cg.reduceMoney,gs.specPrice,gs.specStock,gs.specIds,gs.id goodsSpecId,cg.id,g.goodsStock')
                  ->select();
        if(count($goods)==0)return ['list'=>[],'goodsSpec'=>[]];
        $data = ['list'=>[]];
        $time = time();
        $i=0;
        $goodsIds = [];
        $time = time();
        foreach ($goods as $key => $v) {
            $v['isRead'] = false;//标记是否只能看不能买
            //已经过了活动时间
            if(WSTStrToTime($v['endTime']) < $time)continue;
            //如果没预热的话看下是否到了活动时间
            if($v['isAdvance'] == 0 && WSTStrToTime($v['startTime']) > $time)continue;
	        //如果提前预热的话，进行日期分别判断
	        if($v['isAdvance'] == 1){
	        	//如果不在预热期则为空
	        	if($time < strtotime("-".$v['advanceDay']."day ",strtotime($v['startTime']))){
	        		continue;
	        	//如果在预热期间，则只能看不能买
	        	}else if(strtotime("-".$v['advanceDay']."day ",strtotime($v['startTime']))<=$time && WSTStrToTime($v['startTime'])>=$time){
	        		$v['isRead'] = true;
	        	}
	        }

        	if($v['isSpec']==1){
	        	$v['shopPrice'] = $v['specPrice'] - $v['reduceMoney'];
	        	$v['shopPrice'] = ($v['shopPrice']>0)?$v['shopPrice']:0;
	        	$v['goodsStock'] = $v['specStock'];
	        }else{
	            $v['shopPrice'] = $v['shopPrice'] - $v['reduceMoney'];
	            $v['shopPrice'] = ($v['shopPrice']>0)? $v['shopPrice']:0;
	        }
            //是否本商品为组合套餐的主商品
            $v['isMain'] = 1;
        	$mgoods = Db::name('combination_goods')->alias('cg')
        	            ->join('__GOODS__ g','g.goodsId=cg.goodsId','inner')
                        ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
        	            ->join('__GOODS_SPECS__ gs','g.goodsId=gs.goodsId and isDefault=1','left')
        	            ->where(['combineId'=>$v['combineId']])
        	            ->field('cg.id,cg.goodsId,g.goodsImg,gl.goodsName,cg.reduceMoney,g.dataFlag,g.isSale,g.goodsStatus,g.shopPrice,cg.goodsType,g.isSpec,gs.id goodsSpecId,gs.specPrice,gs.specStock,g.goodsUnit,gs.specIds,g.goodsStock')
        	            ->select();
        	if(count($mgoods)>0){
        		$isSkip = false;
        		$mdata = [];
        		$tmpMoney = 0;
		        $minMoney = -1;
		        $maxMoney = 0;
        		foreach ($mgoods as $gkey => $vg) {
        			$gunit = WSTDatas('GOODS_UNIT',$vg['goodsUnit']);
        			$vg['goodsUnit'] = ($gunit && isset($gunit['dataName']))?$gunit['dataName']:'';
        			if($vg['dataFlag']!=1 || $vg['isSale']!=1 || $vg['goodsStatus']!=1){
                        $isSkip = true;
                        continue;
        			}
        			if($vg['isSpec']==1){
			        	$vg['shopPrice'] = $vg['specPrice'] - $vg['reduceMoney'];
			        	$vg['shopPrice'] = ($vg['shopPrice']>0)?$vg['shopPrice']:0;
			        	$vg['goodsStock'] = $vg['specStock'];
			        }else{
			            $vg['shopPrice'] = $vg['shopPrice'] - $vg['reduceMoney'];
			            $vg['shopPrice'] = ($vg['shopPrice']>0)?$vg['shopPrice']:0;
			        }
        			WSTUnset($vg,'goodsStatus,isSale,dataFlag');
        			$mdata[$vg['id']] = $vg;
        			//如果是自由搭配的话，则计算最小和最大搭配金额，如果是固定套餐的话，则计算最大搭配金额
        			$maxMoney += $vg['shopPrice'];
        			$tmpMoney = $v['shopPrice']+$vg['shopPrice'];
        			if($vg['goodsType']!=1){
        				if($minMoney==-1){
        					$minMoney = $tmpMoney;
        				}else{
	                        if($minMoney>$tmpMoney)$minMoney = $tmpMoney;
	                    }
        			}else{
	        			if($v['goodsId']!=$vg['goodsId']){
	        				$v['isMain'] = 0;
	        				$v['id'] = $vg['id'];
	        				$v['goodsId'] = $vg['goodsId'];
	        				$v['goodsImg'] = $vg['goodsImg'];
	        				$v['goodsName'] = $vg['goodsName'];
	        				$v['shopPrice'] = $vg['shopPrice'];
	        				$v['isSpec'] = $vg['isSpec'];
	        				$v['specPrice'] = $vg['specPrice'];
	        				$v['specStock'] = $vg['specStock'];
	        				$v['specIds'] = $vg['specIds'];
	        				$v['goodsSpecId'] = $vg['goodsSpecId'];
	        				$v['goodsStock'] = $vg['goodsStock'];
	        				$v['combineGoodsId'] = $vg['id'];
	        			}
	        		}
	        		if(!in_array($vg['goodsId'],$goodsIds))$goodsIds[] = $vg['goodsId'];
        		}

        		if($isSkip)continue;
        		$v['minMoney'] = $minMoney;
        		$v['maxMoney'] = $maxMoney;
        	}
        	$v['list'] = $mdata;
            $data['list'][$v['combineId']] = $v;
            $i++;
            if($i==6)break;
        }
        //电脑端获取商品规格
        $data['goodsSpec'] = ['-1'=>[]];
        if($hasSpec){
            $data['goodsSpec'] = $this->getGoodsSpecs($goodsIds);
        }
        return $data;
    }
    /**
     * 加载每个商品的规格
     */
    public function getGoodsSpecs($goodsIds){
        $specMap = [];
        $tmpSpecItem = [];
        //获取规格值
		$specs = Db::name('spec_cats')->alias('gc')->join('__SPEC_ITEMS__ sit','gc.catId=sit.catId','inner')
                ->join('__SPEC_CATS_LANGS__ scl','gc.catId=scl.catId and scl.langId='.WSTCurrLang())
                ->join('__SPEC_ITEMS_LANGS__ sil','sit.itemId=sil.itemId and sil.langId='.WSTCurrLang())
                ->where([['sit.goodsId','in',$goodsIds],['gc.isShow','=',1],['sit.dataFlag','=',1]])
                ->field('gc.isAllowImg,scl.catName,sit.catId,sit.itemId,sil.itemName,sit.itemImg,sit.goodsId')
                ->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
		foreach ($specs as $key =>$v){
			$specMap[$v['goodsId']]['specs'][$v['catId']]['name'] = $v['catName'];
			$specMap[$v['goodsId']]['specs'][$v['catId']]['list'][] = $v;
			$tmpSpecItem[$v['itemId']] = ['catName'=>$v['catName'],'itemName'=>$v['itemName']];
		}
        //获取销售规格
		$sales = Db::name('goods_specs')->where([['goodsId','in',$goodsIds]])->field('id,isDefault,productNo,specIds,marketPrice,specPrice,specStock,goodsId,specIds,specWeight,specVolume')->select();
		if(!empty($sales)){
			foreach ($sales as $key =>$v){
				//处理销售规格
				$str = explode(':',$v['specIds']);
				sort($str);
				if($v['isDefault']==1){
					$specMap[$v['goodsId']]['defaultSpecs'] = $v;
					$str = explode(':',$v['specIds']);
				    foreach ($str as $vv){
				    	if(isset($tmpSpecItem[$vv]))$specMap[$v['goodsId']]['items'][] = $tmpSpecItem[$vv];
				    }
				}
				$specMap[$v['goodsId']]['saleSpec'][implode(':',$str)] = $v;
			}
		}
		$data = ['-1'=>[]];
		foreach ($goodsIds as $key => $v) {
			if(isset($specMap[$v])){
				$goods = [];
				$goods['specs'] = $specMap[$v]['specs'];
				$goods['saleSpec'] = $specMap[$v]['saleSpec'];
		        $goods['defaultSpecs'] = $specMap[$v]['defaultSpecs'];
		        $goods['goodsSpecId'] = $specMap[$v]['defaultSpecs']['id'];
	            $data[$v] = $goods;
	        }
		}
        return $data;
    }

    /**
     * 构造购物车返回
     */
    public function getCarts($data){
    	if(!isset($data['combineId']) || !isset($data['combineGoodsIds']))return WSTReturn(lang('combination_invalid_goods'));
        $combineId = (int)$data['combineId'];
        $combineGoodsIds = WSTFormatIn(',',$data['combineGoodsIds'],false);
        //核对数据有效性
        $goods = Db::name('combinations')->alias('c')
    	            ->join('__SHOPS__ s','s.shopId=c.shopId','inner')
    	            ->field('c.combineId,c.combineType,c.isAdvance,c.advanceDay,c.startTime,c.endTime,c.isFreeShipping,s.shopName,s.shopQQ,s.shopWangWang,c.shopId,s.isInvoice')
    	            ->where(['c.combineId'=>$combineId,'c.dataFlag'=>1])
    	            ->order('c.combineOrder asc,c.startTime asc,c.combineId asc')
    	            ->find();
        $time = time();
        //已经过了活动时间
        if(WSTStrToTime($goods['endTime']) < $time || WSTStrToTime($goods['startTime']) > $time)return WSTReturn(lang('combination_time_tips'));
        $where = [['combineId','=',$combineId]];
        $where[] = ['g.goodsStatus','=',1];
        $where[] = ['g.isSale','=',1];
        $where[] = ['g.dataFlag','=',1];
        if($goods['combineType']==0)$where[] = ['c.id','in',$combineGoodsIds];
        $mgoods = Db::name('combination_goods')->alias('c')
                    ->join('__GOODS__ g','g.goodsId=c.goodsId','inner')
                    ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                    ->where($where)
                    ->field('g.goodsId,c.reduceMoney,gl.goodsName,g.goodsImg,g.shopPrice,g.shopPrice,g.goodsStock,g.shippingFeeType,g.shopExpressId,g.goodsStock,g.goodsWeight,g.goodsVolume,g.isSpec,1 as cartNum,g.goodsCatId,g.isFreeShipping,g.shopId,c.goodsType,c.id')
                    ->order('c.goodsType desc')
                    ->select();
        //判断商品是否完整
        if($goods['combineType']==1 && count($mgoods)!=count($combineGoodsIds)){
            return WSTReturn('固定套餐商品必须全部购买');
        }
        //检测是否主次商品搭配
        $combineChk = ['1'=>false,'0'=>false];
        foreach ($mgoods as $key => $v) {
        	if($v['goodsType']==1){
        		$combineChk['1'] = true;
        	}else{
        		$combineChk['0'] = true;
        	}
        }
        if(!($combineChk['1'] && $combineChk['0']))return WSTReturn(lang('combination_invalid_combine'));
        $goodsIds = [];
        $gm = [];
        foreach ($mgoods as $key => $v) {
            $goodsIds[] = $v['goodsId'];
            if(isset($data['g'.$v['id']]))$gm[$v['goodsId']] = $data['g'.$v['id']];
        }
        $specMap = [];
        $tmpSpecItem = [];
		//获取规格值
		$specs = Db::name('spec_cats')->alias('gc')->join('__SPEC_ITEMS__ sit','gc.catId=sit.catId','inner')
                    ->join('__SPEC_CATS_LANGS__ scl','gc.catId=scl.catId and scl.langId='.WSTCurrLang())
                    ->join('__SPEC_ITEMS_LANGS__ sil','sit.itemId=sil.itemId and sil.langId='.WSTCurrLang())
                    ->where([['sit.goodsId','in',$goodsIds],['gc.isShow','=',1],['sit.dataFlag','=',1]])
                    ->field('gc.isAllowImg,scl.catName,sit.catId,sit.itemId,sil.itemName,sit.itemImg,sit.goodsId')
                    ->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
		foreach ($specs as $key =>$v){
			$specMap[$v['goodsId']]['specs'][$v['catId']]['name'] = $v['catName'];
			$specMap[$v['goodsId']]['specs'][$v['catId']]['list'][] = $v;
			$tmpSpecItem[$v['itemId']] = ['catName'=>$v['catName'],'itemName'=>$v['itemName']];
		}
		//获取销售规格
		$sales = Db::name('goods_specs')->where([['goodsId','in',$goodsIds]])->field('id,isDefault,productNo,specIds,marketPrice,specPrice,specStock,goodsId,specIds,specWeight,specVolume')->select();
		if(!empty($sales)){
			foreach ($sales as $key =>$v){
				$str = explode(':',$v['specIds']);
				sort($str);
				//如果循环的规格正好是前台选择的规格，则【不管原来有没有保存了默认值】都保存下来
				if(isset($gm[$v['goodsId']]) && $gm[$v['goodsId']]==$v['id']){
                    $specMap[$v['goodsId']]['defaultSpecs'] = $v;
					$str = explode(':',$v['specIds']);
					$specMap[$v['goodsId']]['items'] = [];
			    	foreach ($str as $vv){
			    		if(isset($tmpSpecItem[$vv])){
			    			$specMap[$v['goodsId']]['items'][] = $tmpSpecItem[$vv];
			    		}
			    	}
				}else{
					//如果是默认规格，并且前边又没有保存的话，则保存下来
                    if($v['isDefault']==1 && !isset($specMap[$v['goodsId']]['defaultSpecs'])){
						$specMap[$v['goodsId']]['defaultSpecs'] = $v;
						$str = explode(':',$v['specIds']);
				    	foreach ($str as $vv){
				    		if(isset($tmpSpecItem[$vv]))$specMap[$v['goodsId']]['items'][] = $tmpSpecItem[$vv];
				    	}
					}
				}
				$specMap[$v['goodsId']]['saleSpec'][implode(':',$str)] = $v;
			}
		}
		$goods['promotion'] = [];
        $goods['promotionMoney'] = 0;
        $goodsTotalMoney = 0;
        $goodsTotalNum = 1;
        $totalReduceMoney = 0;
		foreach ($mgoods as $key => $v) {
			if($v['isSpec']==1){
				$mgoods[$key]['specs'] = $specMap[$v['goodsId']]['specs'];
				$mgoods[$key]['saleSpec'] = $specMap[$v['goodsId']]['saleSpec'];
                $mgoods[$key]['specNames'] = $specMap[$v['goodsId']]['items'];
                $mgoods[$key]['defaultSpecs'] = $specMap[$v['goodsId']]['defaultSpecs'];
                $mgoods[$key]['goodsSpecId'] = $specMap[$v['goodsId']]['defaultSpecs']['id'];
                $mgoods[$key]['marketPrice'] = $specMap[$v['goodsId']]['defaultSpecs']['marketPrice'];
                $mgoods[$key]['shopPrice'] = $specMap[$v['goodsId']]['defaultSpecs']['specPrice']-$v['reduceMoney'];
                $mgoods[$key]['goodsStock'] = $specMap[$v['goodsId']]['defaultSpecs']['specStock'];
                $mgoods[$key]['goodsWeight'] = $specMap[$v['goodsId']]['defaultSpecs']['specWeight'];
                $mgoods[$key]['goodsVolume'] = $specMap[$v['goodsId']]['defaultSpecs']['specVolume'];
			}else{
				$mgoods[$key]['shopPrice'] = $v['shopPrice'] - $v['reduceMoney'];
				$mgoods[$key]['goodsSpecId'] = 0;
			}
			$mgoods[$key]['shopPrice'] = ( $mgoods[$key]['shopPrice']>0)? $mgoods[$key]['shopPrice']:0;
			$goodsTotalMoney += $mgoods[$key]['shopPrice'];
        	$totalReduceMoney +=$v['reduceMoney'];
            $goodsTotalNum++;
		}
        $goods['goodsMoney'] = $goodsTotalMoney;
        $goods['list'] = $mgoods;
        $carts[$goods['shopId']] = $goods;
        return WSTReturn('',1,['carts'=>$carts,'goodsTotalMoney'=>$goodsTotalMoney,'goodsTotalNum'=>$goodsTotalNum,'promotionMoney'=>0]);
    }
    /**
     * 检查是否有可用门店
     */
    public function checkSupportStores(){
        $combineId = (int)input('combineId');
        $shopId = Db::name("combinations")->where(['combineId'=>$combineId,'dataFlag'=>1])->value("shopId");
        $where = [];
        $where[] = ["shopId","=",$shopId];
        $where[] = ["dataFlag","=",1];
        $where[] = ["storeStatus","=",1];
        $cnt = Db::name("stores")->where($where)->count();
        $rs = ($cnt>0)?1:0;
        return $rs;
    }

    /**
	 * 计算订单金额
	 */
	public function getCartMoney($uId=0){
		$combineKey = input('combineKey');
		$pdata = WSTStrToParams($combineKey);
		$data = ['shops'=>[],'totalMoney'=>0,'totalGoodsMoney'=>0];
        $areaId = input('post.areaId2/d',-1);
		//计算各店铺运费及金额
		$deliverType = (int)input('deliverType');

		$carts = $this->getCarts($pdata);
		$carts = $carts['data'];
		$shopFreight = 0;
		$cartShops = array_shift($carts["carts"]);
		$cartGoods = $cartShops['list'];
		//判断是否包邮
		if($cartShops['isFreeShipping']){
			$shopFreight = 0;
		}else{
			if($areaId>0){
				$shopFreight = $deliverType==1?0:WSTOrderFreight($cartShops['shopId'],$areaId,$cartShops);
			}else{
				$shopFreight = 0;
			}

		}

		$data['shops']['freight'] = $shopFreight;
		$data['shops']['shopId'] = $cartShops['shopId'];
		$data['shops']['goodsMoney'] = $carts['goodsTotalMoney'];
		$data['totalGoodsMoney'] = $carts['goodsTotalMoney'];
		$data['totalMoney'] += $carts['goodsTotalMoney'] + $shopFreight;
		$data['useScore'] = 0;
		$data['scoreMoney'] = 0;


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
		$data['realTotalMoney'] = round($data['totalMoney'] - $data['scoreMoney'],2);
		return WSTReturn('',1,$data);
	}

    /**
	 * 下单
	 */
	public function submit($orderSrc = 0,$uId=0){
		$combineKey = input('combineKey');
		$data = WSTStrToParams($combineKey);
		//检测购物车
		$carts = $this->getCarts($data);
		if($carts['status']!=1)return WSTReturn($carts['msg']);
		$carts = $carts['data'];
        return $this->submitByEntity($carts,$orderSrc,$uId);
	}

	/**
	 * 实物商品下单
	 */
	public function submitByEntity($carts,$orderSrc = 0,$uId=0){
		$addressId = (int)input('post.s_addressId');
		$deliverType = ((int)input('post.deliverType')!=0)?1:0;
		$isInvoice = ((int)input('post.isInvoice')!=0)?1:0;
		$invoiceClient = ($isInvoice==1)?input('post.invoiceClient'):'';
		$payType = ((int)input('post.payType')!=0)?1:0;
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		if($userId==0)return WSTReturn(lang('no_login'));
		$isUseScore = (int)input('isUseScore');
		$useScore = (int)input('useScore');
		//检测是否重复提交
		$combineKey = input('combineKey');
		$chk = Db::name('orders')->where([['extraJson','like','%'.$combineKey.'%'],['orderCode','=','combination']])->count();
        if($chk>0)return WSTReturn(lang('combination_not_repeat_order'));
		//检测地址是否有效
		$address = Db::name('user_address')->where(['userId'=>$userId,'addressId'=>$addressId,'dataFlag'=>1])->find();
		if(empty($address)){
			return WSTReturn(lang('combination_invalid_address'));
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
		$orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney']-$carts['promotionMoney'],$uId,($isUseScore==1)?1:0,$useScore);
		$carts = $carts['carts'];
		$shopOrder = array_shift($carts);
		//生成订单
		Db::startTrans();
		try{
			$orderunique = WSTOrderQnique();
			//判断商品是否够数量
			foreach ($shopOrder['list'] as $key => $v) {
				if($v['goodsStock']<=0)return WSTReturn(lang('combination_stock_less_tips',[($key+1)]));
			}
			$orderNo = WSTOrderNo();
			$orderScore = 0;
			//创建订单
			$order = [];
			$order = array_merge($order,$address);
			$order['orderNo'] = $orderNo;
			$order['userId'] = $userId;
			$order['shopId'] = $shopOrder['shopId'];
			$order['payType'] = $payType;
			$order['goodsMoney'] = $shopOrder['goodsMoney'];
			//计算运费和总金额
			$order['deliverType'] = $deliverType;
			$s = WSTOrderFreight($shopOrder['shopId'],$order['areaId2'],$shopOrder);
			if($shopOrder['isFreeShipping']==1){
                $order['deliverMoney'] = 0;
			}else{
			    $order['deliverMoney'] = ($deliverType==1)?0:WSTOrderFreight($shopOrder['shopId'],$order['areaId2'],$shopOrder);
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
			$order['orderCode'] = 'combination';
			$order['orderCodeTargetId'] = $shopOrder['combineId'];
			$order['extraJson'] = json_encode(['combineId'=>$shopOrder['combineId'],'combineType'=>$shopOrder['combineType'],'combineKey'=>input('combineKey')]);
            if($payType==1){
                if($order['needPay']>0){
                    $order['orderStatus'] = -2;//待付款
				    $order['isPay'] = 0;
                }else{
                    $order['orderStatus'] = 0;//待发货
				    $order['isPay'] = 1;
				    $order['payTime'] = date('Y-m-d H:i:s');
				    if($deliverType==1)$order['verificationCode'] = WSTOrderVerificationCode($shopOrder['shopId']);
                }
			}else{
				$order['orderStatus'] = 0;//待发货
				if($order['needPay']==0){
					$order['isPay'] = 1;
					$order['payTime'] = date('Y-m-d H:i:s');
				}
				if($deliverType==1)$order['verificationCode'] = WSTOrderVerificationCode($shopOrder['shopId']);
			}
			//积分
			$orderScore = 0;
			//如果开启下单获取积分则有积分
			if(WSTConf('CONF.isOrderScore')==1){
				$orderScore = WSTMoneyGiftScore($order['goodsMoney']);
			}
			$order['orderScore'] = $orderScore;

			$shop = model("common/shops")->getFieldsById($shopOrder['shopId'],"isInvoice");
			$shopIsInvoice = $shop['isInvoice'];
			if($shopIsInvoice==1 && $isInvoice==1){
				$order['isInvoice'] = $isInvoice;
				$order['invoiceJson'] = model('common/invoices')->getInviceInfo((int)input('param.invoiceId'),$userId);// 发票信息
				$order['invoiceClient'] = $invoiceClient;
			}else{
				$order['isInvoice'] = 0;
				$order['invoiceJson'] = "";// 发票信息
				$order['invoiceClient'] = "";
			}


			$order['orderRemarks'] = input('post.remark_'.$shopOrder['shopId']);
			$order['orderunique'] = $orderunique;
			$order['orderSrc'] = $orderSrc;
			$order['dataFlag'] = 1;
			$order['payRand'] = 1;
			$order['createTime'] = date('Y-m-d H:i:s');
			$m = model('common/orders');

			$result = $m->data($order,true)->isUpdate(false)->allowField(true)->save($order);

			if(false !== $result){
				$orderId = $m->orderId;
				$orderTotalGoods = [];
				foreach ($shopOrder['list'] as $gkey =>$goods){
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
					/****************************************  积分抵扣的金额start  ****************************************/
					if((int)WSTConf('CONF.isOpenScorePay')==1){// 后台有开启积分支付
						// 该订单总共使用的积分->$order['useScore'] 积分可抵扣的金额->$order['scoreMoney']
						// 最后一件商品的使用积分=订单使用积分-同订单下其他商品使用积分的总和;积分减免金额同理
						$orderGoods['useScoreVal'] = $order['useScore'];
						$orderGoods['scoreMoney'] = $order['scoreMoney'];
					}
					//如果开启下单获取积分则有积分
					// $orderGoods['getScoreVal'] = 0;
					if(WSTConf('CONF.isOrderScore')==1){
						$orderGoods['getScoreVal'] = WSTMoneyGiftScore($goods['shopPrice']*$goods['cartNum']);
						// 获得的积分可抵扣金额
						$orderGoods['getScoreMoney'] = WSTScoreToMoney($orderGoods['getScoreVal']);
					}
					$orderTotalGoods[] = $orderGoods;
	                //修改库存
					if($goods['goodsSpecId']>0){
						Db::name('goods_specs')->where('id',$goods['goodsSpecId'])->update([
	                           'specStock'=>Db::raw('specStock-'.$goods['cartNum'])
						]);
					}
	                Db::name('goods')->where('goodsId',$goods['goodsId'])->update([
	                        'goodsStock'=>Db::raw('goodsStock-'.$goods['cartNum'])
	                ]);
	            }
	            Db::name('order_goods')->insertAll($orderTotalGoods);
	            //更新订单佣金
				model('common/orders')->where('orderId',$orderId)->update(['commissionFee'=>$commissionFee]);
				//创建积分流水--如果有抵扣积分就肯定是开启了支付支付
				if($order['useScore']>0){
					$score = [];
				    $score['userId'] = $userId;
					$score['score'] = $order['useScore'];
					$score['dataSrc'] = 1;
					$score['dataId'] = $orderId;
					$score['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'combination_order_use_score_tips',''=>[$orderNo,$order['useScore']]]);
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
				if($deliverType==1){//自提
					//自提订单（已支付）发送核验码
					if(($payType==1 && $order['needPay']==0) || $payType==0){
						$tpl = WSTMsgTemplates('PHONE_USER_ORDER_VERIFICATCODE');
				        if( $tpl['tplContent']!='' && $tpl['status']=='1'){

				        	$userPhone = $order['userPhone'];
				        	$storeId = $order['storeId'];
				        	$store = Db::name("stores")->where(["storeId"=>$storeId])->field("storeName,storeAddress")->find();
				        	$storeName = $store["storeName"];
				        	$storeAddress = $store["storeAddress"];
				        	$splieVerificationCode = join(" ",str_split($order['verificationCode'],4));
				            $params = ['tpl'=>$tpl,'params'=>['MALL_NAME'=>WSTConf('CONF.mallName'),'ORDER_CODE'=>$splieVerificationCode,'SHOP_NAME'=>$storeName,'SHOP_ADDRESS'=>$storeAddress]];
				            model("common/LogSms")->sendSMS(0,$userPhone,$params,'submitByEntity','',$userId,0);
				        }
					}
			    }
				//给店铺增加提示消息
				$tpl = WSTMsgTemplates('ORDER_SUBMIT');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${ORDER_NO}'];
		            $replace = [$orderNo];

		        	$msg = array();
		            $msg["shopId"] = $shopOrder['shopId'];
		            $msg["tplCode"] = $tpl["tplCode"];
		            $msg["msgType"] = 1;
		            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
		            $msg["msgJson"] = ['from'=>1,'dataId'=>$orderId];
		            model("common/MessageQueues")->add($msg);
		        }
				//判断是否需要发送商家短信
	            $tpl = WSTMsgTemplates('PHONE_SHOP_SUBMIT_ORDER');
				if((int)WSTConf('CONF.smsOpen')==1 && $tpl['tplContent']!='' && $tpl['status']=='1'){

					$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$orderNo]];

					$msg = array();
					$tplCode = "PHONE_SHOP_SUBMIT_ORDER";
					$msg["shopId"] = $shopOrder['shopId'];
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
					$msg["shopId"] = $shopOrder['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'URL'=>Url('wechat/orders/sellerorder','',true,true),'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);

			    }
			}
			Db::commit();
			return WSTReturn("提交订单成功", 1,$orderunique);
		}catch (\Exception $e) {
            Db::rollback();
            //print_r($e);
            return WSTReturn('提交订单失败',-1);
        }
	}

	/**
	 * 获取组合套餐页面信息
	 */
	public function getBySale($combined,$uId=0){
        //获取该商品为主商品或者该商品为搭配行频的组合套餐
        $goods = Db::name('combinations')->alias('c')
                  ->join('__COMBINATIONS_LANGS__ cl','cl.combineId=c.combineId and cl.langId='.WSTCurrLang())
                  ->join('__COMBINATION_GOODS__ cg','c.combineId=cg.combineId')
                  ->join('__GOODS__ g','g.goodsId=cg.goodsId','inner')
                  ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                  ->where(['c.dataFlag'=>1,'c.combineId'=>$combined,'g.isSale'=>1,'g.goodsStatus'=>1,'g.dataFlag'=>1])
                  ->order('cg.goodsType desc,c.combineOrder asc,c.startTime asc,c.combineId asc')
                  ->field('c.combineId,cl.combineName,cl.combineDesc,c.combineImg,c.combineType,c.isAdvance,c.advanceDay,c.startTime,c.endTime,c.isFreeShipping,g.goodsImg,gl.goodsName,g.isSpec,cg.goodsId,cg.reduceMoney,cg.id,g.shopPrice-cg.reduceMoney shopPrice,g.goodsStock')
                  ->find();
        if(empty($goods))return [];
        $goods['shopPrice'] = ($goods['shopPrice']>0)?$goods['shopPrice']:0;
        $time = time();
        $goods['isRead'] = false;//标记是否只能看不能买
        //已经过了活动时间
        if(WSTStrToTime($goods['endTime']) < $time)return [];
        //如果没预热的话看下是否到了活动时间
        if($goods['isAdvance'] == 0 && WSTStrToTime($goods['startTime']) > $time)return [];
        //如果提前预热的话，进行日期分别判断
        if($goods['isAdvance'] == 1){
        	//如果不在预热期则为空
        	if($time < strtotime("-".$goods['advanceDay']."day ",strtotime($goods['startTime']))){
        		return [];
        	//如果在预热期间，则只能看不能买
        	}else if(strtotime("-".$goods['advanceDay']."day ",strtotime($goods['startTime']))<=$time && WSTStrToTime($goods['startTime'])>=$time){
        		$goods['isRead'] = true;
        	}
        }

        $mgoods = Db::name('combination_goods')->alias('cg')
        	        ->join('__GOODS__ g','g.goodsId=cg.goodsId','inner')
                    ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
        	        ->where(['combineId'=>$combined])
        	        ->field('cg.id,cg.goodsId,g.goodsImg,gl.goodsName,cg.reduceMoney,g.dataFlag,g.isSale,g.goodsStatus,g.shopPrice-cg.reduceMoney shopPrice,cg.goodsType,g.isSpec,g.goodsUnit,g.marketPrice,g.goodsStock')
        	        ->select();
        $goodsReduceMoney = [];
        if(count($mgoods)>0){
        	$isSkip = false;
        	$mdata = [];
        	$tmpMoney = 0;
		    $minMoney = -1;
		    $maxMoney = 0;
		    $goodsIds = [];
        	foreach ($mgoods as $gkey => $vg) {
        		$vg['shopPrice'] = ($vg['shopPrice']>0)?$vg['shopPrice']:0;
        		$goodsReduceMoney[$vg['goodsId']] = $vg['reduceMoney'];
        		if($vg['dataFlag']!=1 || $vg['isSale']!=1 || $vg['goodsStatus']!=1)return [];
        		WSTUnset($vg,'goodsStatus,isSale,dataFlag');
        		$mdata[$vg['id']] = $vg;
        		//如果是自由搭配的话，则计算最小和最大搭配金额，如果是固定套餐的话，则计算最大搭配金额
        		$maxMoney += $vg['shopPrice'];
        		$tmpMoney = $goods['shopPrice']+$vg['shopPrice'];
        		if($vg['goodsType']!=1){
        			if($minMoney==-1){
        				$minMoney = $tmpMoney;
        			}else{
	                    if($minMoney>$tmpMoney)$minMoney = $tmpMoney;
	                }
        		}
        		$goodsIds[] = $vg['goodsId'];
        	}
        	$goods['minMoney'] = $minMoney;
        	$goods['maxMoney'] = $maxMoney;
        	$specMap = [];
            $tmpSpecItem = [];
            //获取规格值
			$specs = Db::name('spec_cats')->alias('gc')->join('__SPEC_ITEMS__ sit','gc.catId=sit.catId','inner')
                    ->join('__SPEC_CATS_LANGS__ scl','gc.catId=scl.catId and scl.langId='.WSTCurrLang())
                    ->join('__SPEC_ITEMS_LANGS__ sil','sit.itemId=sil.itemId and sil.langId='.WSTCurrLang())
                    ->where([['sit.goodsId','in',$goodsIds],['gc.isShow','=',1],['sit.dataFlag','=',1]])
                    ->field('gc.isAllowImg,scl.catName,sit.catId,sit.itemId,sil.itemName,sit.itemImg,sit.goodsId')
                    ->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
			foreach ($specs as $key =>$v){
				$specMap[$v['goodsId']]['specs'][$v['catId']]['name'] = $v['catName'];
				$specMap[$v['goodsId']]['specs'][$v['catId']]['isImg'] = $v['isAllowImg'];
				$specMap[$v['goodsId']]['specs'][$v['catId']]['list'][] = $v;
				$tmpSpecItem[$v['itemId']] = ['catName'=>$v['catName'],'itemName'=>$v['itemName']];
			}
        	//获取销售规格
			$sales = Db::name('goods_specs')->where([['goodsId','in',$goodsIds]])->field('id,isDefault,productNo,specIds,marketPrice,specPrice,specStock,goodsId,specIds,specWeight,specVolume')->select();
			if(!empty($sales)){
				foreach ($sales as $key =>$v){
					//处理成组合价格
					$v['specPrice'] = $v['specPrice'] - $goodsReduceMoney[$v['goodsId']];
					$v['specPrice'] = ($v['specPrice']>0)?$v['specPrice']:0;
					//处理销售规格
					$str = explode(':',$v['specIds']);
					sort($str);
					if($v['isDefault']==1){
						$specMap[$v['goodsId']]['defaultSpecs'] = $v;
						$str = explode(':',$v['specIds']);
				    	foreach ($str as $vv){
				    		if(isset($tmpSpecItem[$vv]))$specMap[$v['goodsId']]['items'][] = $tmpSpecItem[$vv];
				    	}
					}
					$specMap[$v['goodsId']]['saleSpec'][implode(':',$str)] = $v;
				}
			}
			foreach ($mgoods as $key => $v) {
				WSTUnset($mgoods[$key],'dataFlag,isSale,goodsStatus');
				$gunit = WSTDatas('GOODS_UNIT',$v['goodsUnit']);
        		$mgoods[$key]['goodsUnit'] = ($gunit && isset($gunit['dataName']))?$gunit['dataName']:'';
				if($v['isSpec']==1){
					$mgoods[$key]['specs'] = $specMap[$v['goodsId']]['specs'];
					$mgoods[$key]['saleSpec'] = $specMap[$v['goodsId']]['saleSpec'];
	                $mgoods[$key]['defaultSpecs'] = $specMap[$v['goodsId']]['defaultSpecs'];
	                $mgoods[$key]['goodsSpecId'] = $specMap[$v['goodsId']]['defaultSpecs']['id'];
	                $mgoods[$key]['specNames'] = $specMap[$v['goodsId']]['items'];
	                $mgoods[$key]['shopPrice'] = $specMap[$v['goodsId']]['defaultSpecs']['specPrice'];
	                $mgoods[$key]['goodsStock'] = $specMap[$v['goodsId']]['defaultSpecs']['specStock'];
				}else{
					$mgoods[$key]['shopPrice'] = $v['shopPrice'];
					$mgoods[$key]['goodsSpecId'] = 0;
				}
			}
	        $goods['list'] = $mgoods;
        }
        //print_r($goods);exit();
        return $goods;
	}

}
