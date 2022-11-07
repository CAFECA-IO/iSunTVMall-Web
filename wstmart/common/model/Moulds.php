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
 * 规格分类业务处理
 */
class Moulds extends Base{
	
	/**
	 * 新增
	 */
	public function add(){
		$shopId = (int)session('WST_USER.shopId');
		$mouldName = input("mouldName");
		$mouldId = (int)input("mouldId");
		if($mouldId==0 && $mouldName=="")return WSTReturn(lang('reqiure_mould_name'),-1);
		
		$goodsCatId = (int)input("goodsCatId");
		$rs = $this->checkMouldName();
		if($rs['status']==1){
			Db::startTrans();
	        try{
	        	$specsIds = input('post.specsIds');
				$goodmodel = model('GoodsCats');
				$goodsCats = $goodmodel->getParentIs($goodsCatId);
				if($mouldId==0){
					$data = [];
					$data["shopId"] = $shopId;
					$data["mouldName"] = $mouldName;
					$data["goodsCatId"] = $goodsCatId;
					$data["dataFlag"] = 1;
					$data["createTime"] = date("Y-m-d H:i:s");

					$mouldId = Db::name("moulds")->insertGetId($data);
				}
				
				
				//如果是实物商品并且有销售规格则保存销售和规格值
		        if($specsIds!=''){
		        	//把之前之前的销售规格
	    	        $specsIds = explode(',',$specsIds);
			    	$specsArray = [];
			    	foreach ($specsIds as $v){
			    		$vs = explode(':',$v);
			    		$specsArray[] = $vs;
			    	}
			    	Db::name('mould_goods_spec_items')->where(['shopId'=>$shopId,'mouldId'=>$mouldId])->update(['dataFlag'=>-1]);
			    	//删除模板规格多语言
			    	Db::name('mould_goods_spec_items_langs')->where(['mouldId'=>$mouldId])->delete();
		    		//保存规格名称
		    		$specMap = [];
		    		$sitemLangs = [];
		    		foreach ($specsArray as $v){
		    			$specNumId = $v[0]."_".$v[1];
		    			$isNotNull = false;
		    			foreach (WSTSysLangs() as $lkey => $lv) {
	    			    	$itemName = input('post.specName_'.$specNumId.'_'.$lv['id']);
		    			    if($itemName!='')$isNotNull = true;
	    			    }
		    			if($isNotNull){
			    			$sitem = [];
			    			$sitem['mouldId'] = $mouldId;
			    			$sitem['goodsCatId'] = $goodsCatId;
			    			$sitem['catId'] = $v[0];
			    			$sitem['itemImg'] = input('post.specImg_'.$specNumId,'');
		    				$sitem['shopId'] = $shopId;
		    				$sitem['dataFlag'] = 1;
		    			    $sitem['createTime'] = date('Y-m-d H:i:s');
		    			    $itemId = Db::name('mould_goods_spec_items')->insertGetId($sitem);
		    			    foreach (WSTSysLangs() as $lkey => $lv) {
                                 $sitemLang = [];
			    				 $sitemLang['mouldGoodsSpecItemId'] = $itemId;
			    				 $sitemLang['langId'] = $lv['id'];
			    				 $sitemLang['mouldId'] = $mouldId;
			    				 $sitemLang['itemName'] = input('post.specName_'.$specNumId.'_'.$lv['id']);
                                 $sitemLangs[] = $sitemLang;
		    			    }
		    			}
	    			    //if($sitem['itemImg']!='')WSTUseResource(0, $itemId, $sitem['itemImg']);
		    		}
		    		Db::name('mould_goods_spec_items_langs')->insertAll($sitemLangs);
		    		
		        }
		        //保存商品属性
		        //删除之前的商品属性
		        Db::name('mould_goods_attributes')->where(['mouldId'=>$mouldId,'shopId'=>$shopId])->update(['dataFlag'=>-1]);
		        //删除模板商品属性多语言
		         Db::name('mould_goods_attributes_langs')->where(['mouldId'=>$mouldId])->delete();
		        //新增商品属性
		    	$attrsArray = [];
		    	$attrRs = Db::name('attributes')->where([['goodsCatId','in',$goodsCats],['isShow','=',1],['dataFlag','=',1],['shopId','in',[0,$shopId]]])
		    		            ->field('attrId,attrType')->select();
		    	$attrRsVals =  Db::name('attributes')->alias('a')->join('__ATTRIBUTES_LANGS__ al','al.attrId=a.attrId and al.langId='.WSTCurrLang())->where([['attrType','<>',0],['goodsCatId','in',$goodsCats],['isShow','=',1],['dataFlag','=',1],['shopId','in',[0,$shopId]]])->field('al.attrId,al.langId,al.attrVal')->select();
                $attrRsMap = [];
		        foreach ($attrRsVals as $key => $av) {
		        	$attrRsMap[$av['attrId']][$av['langId']] = explode(',',$av['attrVal']);
		        }
		    	$attrLangs = [];
		    	foreach ($attrRs as $key =>$v){
		    		$attrs = [];
		    		$isNotNull = false;
		    		if($v['attrType']==0){
			    		foreach (WSTSysLangs() as $lkey => $lv) {
				    		if(input('attr_'.$v['attrId'].'_'.$lv['id'])!='')$isNotNull = true;
			    	    }
			    	}else{
			    		if(input('attr_'.$v['attrId'])!='')$isNotNull = true;
			    	}
		    	    if($isNotNull){
			    		$attrs['shopId'] = $shopId;
			    		$attrs['mouldId'] = $mouldId;
			    		$attrs['goodsCatId'] = $goodsCatId;
			    		$attrs['dataFlag'] = 1;
			    		$attrs['attrId'] = $v['attrId'];
			    		$attrs['createTime'] = date('Y-m-d H:i:s');
			    		$mouldGoodsAttrId = Db::name('mould_goods_attributes')->insertGetId($attrs);
			    		//文本输入框直接取获取的内容
			    		if($v['attrType']==0){
				    		foreach (WSTSysLangs() as $lkey => $lv) {
				    			$attrLang = [];
				    			$attrLang['mouldGoodsAttrId'] = $mouldGoodsAttrId;
				    			$attrLang['langId'] = $lv['id'];
				    			$attrLang['mouldId'] = $mouldId;
				    			$attrLang['attrVal'] = input('attr_'.$v['attrId'].'_'.$lv['id']);
				    			$attrLangs[] = $attrLang;
				    		}
				    	}else{
				    		//复选框和下拉框则需要关联表把其他多语言的一起获取过来保存
				    		$checkAttrValNo = [];
				    		$aval = explode(',',input('attr_'.$v['attrId']));
				    		//找出传入值的各种语言版本
				    		foreach (WSTSysLangs() as $lkey => $lv) {
				    			if(isset($attrRsMap[$v['attrId']]) && isset($attrRsMap[$v['attrId']][$lv['id']])){
				    				foreach ($attrRsMap[$v['attrId']][$lv['id']] as $akey => $av) {
				    					if(in_array($av,$aval)){
                                            $checkAttrValNo[] = $akey;
				    					}
				    				}
				    			}
				    		}
				    		
				    		if(count($checkAttrValNo)>0){
                                foreach (WSTSysLangs() as $lkey => $lv) {
                                	$attrVal = [];
                                	$attrLang = [];
					    			$attrLang['mouldGoodsAttrId'] = $mouldGoodsAttrId;
					    			$attrLang['langId'] = $lv['id'];
					    			$attrLang['mouldId'] = $mouldId;
					    			foreach ($checkAttrValNo as $cav) {
					    				$attrVal[] = isset($attrRsMap[$v['attrId']][$lv['id']][$cav])?$attrRsMap[$v['attrId']][$lv['id']][$cav]:'';
					    			}
					    			$attrLang['attrVal'] = implode(',',$attrVal);
					    			$attrLangs[] = $attrLang;
                                }
				    		}
				    	}
			    	}
		    	}
		    	//保存商品属性多语言
		    	if(count($attrLangs)>0)Db::name('mould_goods_attributes_langs')->insertAll($attrLangs);
			    Db::commit();
				return WSTReturn(lang('operation_success'), 1,['mouldId'=>$mouldId]);
			
			}catch (\Exception $e) {
				print_r($e);
	            Db::rollback();
	            return WSTReturn(lang('operation_fail'),-1);
	        }
		}else{
			return $rs;
		}
	}

	/**
	 * 删除
	 */
    public function del(){
    	$shopId = (int)session('WST_USER.shopId');
	    $mouldId = (int)input('mouldId');
	    Db::startTrans();
        try{
        	$data["dataFlag"] = -1;
		    $where = [];
			$where[] = ["shopId","=",$shopId];
			$where[] = ["id","=",$mouldId];
			Db::name("moulds")->where($where)->update($data);
			$where = [];
			$where[] = ["shopId","=",$shopId];
			$where[] = ["mouldId","=",$mouldId];
			Db::name("mould_goods_attributes")->where($where)->update($data);
			Db::name("mould_goods_spec_items")->where($where)->update($data);
        	Db::commit();
			return WSTReturn(lang('del_success'), 1);
		
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('operation_fail'),-1);
        }
	    
	}
	
	/**
	 * 
	 * 根据ID获取
	 */
	public function getById(){
		$shopId = (int)session('WST_USER.shopId');
		$mouldId = (int)input('mouldId');
		$mould = Db::name("moulds")->where(["shopId"=>$shopId,"id"=>$mouldId])->find();
		$goodsCatId = $mould['goodsCatId'];
		$goodsCatIds = model('GoodsCats')->getParentIs($goodsCatId);
		$data = [];
		
		$specs = Db::name('spec_cats')->alias('a')->join('__SPEC_CATS_LANGS__ scl','scl.catId=a.catId and langId='.WSTCurrLang())->where([['shopId','in',[0,$shopId]],['goodsCatId','in',$goodsCatIds],['isShow','=',1],['dataFlag','=',1]])->field('a.catId,scl.catName,isAllowImg')->order('isAllowImg desc,catSort asc,a.catId asc')->select();
		$spec0 = null;
		$spec1 = [];
		foreach ($specs as $key => $v){
			if($v['isAllowImg']==1){
				if(!$spec0){
					$spec0 = $v;
				}else{
					$spec1[] = $v;
				}
			}else{
				$spec1[] = $v;
			}
		}
		$data['spec0'] = $spec0;
		$data['spec1'] = $spec1;
		
		$data['attrs'] = Db::name('attributes')->alias('a')->join('__ATTRIBUTES_LANGS__ al','al.attrId=a.attrId and langId='.WSTCurrLang())->where([['shopId','in',[0,$shopId]],['goodsCatId','in',$goodsCatIds],['isShow','=',1],['dataFlag','=',1]])->field('a.attrId,al.attrName,attrType,al.attrVal')->order('attrSort asc,a.attrId asc')->select();

		$specAttrObj = [];
		//获取规格值
		$specs = Db::name('spec_cats gc')
				->join('mould_goods_spec_items sit','gc.catId=sit.catId','inner')
				->join('mould_goods_spec_items_langs sitl','sitl.mouldGoodsSpecItemId=itemId')
				->where(['sit.mouldId'=>$mouldId,'gc.isShow'=>1,'sit.dataFlag'=>1])
				->field('gc.isAllowImg,sit.catId,sit.itemId,sitl.langId,sitl.itemName,sit.itemImg')
				->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')
				->select();
		$specsMap = [];
		foreach ($specs as $key => $v) {
			if(!isset($specsMap[$v['itemId']])){
                $specsMap[$v['itemId']] = $v;
                $specsMap[$v['itemId']]['langParams'][$v['langId']] = $v['itemName'];
			}else{
                $specsMap[$v['itemId']]['langParams'][$v['langId']] = $v['itemName'];
			}
		}
		$spec0 = [];
		$spec1 = [];
		foreach ($specsMap as $key =>$v){
			if($v['isAllowImg']==1){
				$spec0[] = $v;
			}else{
				$spec1[] = $v;
			}
		}
		$specAttrObj['spec0'] = $spec0;
		$specAttrObj['spec1'] = $spec1;
		//获取销售规格
		$specAttrObj['saleSpec'] = [];
		//获取属性值
		$rs = Db::name('mould_goods_attributes')->alias('ga')
                                ->join('mould_goods_attributes_langs mgal','mgal.mouldGoodsAttrId=ga.id ','inner')
								->join('attributes a','ga.attrId=a.attrId','inner')
								->where('ga.mouldId',$mouldId)
								->field('ga.attrId,a.attrType,mgal.langId,mgal.attrVal')
								->select();
		$attrs = [];
		foreach ($rs as $key => $v) {
			if(!isset($attrs[$v['attrId']])){
				$attrs[$v['attrId']] = $v;
				$attrs[$v['attrId']]['langParams'][$v['langId']] = $v['attrVal'];
			}else{
                $attrs[$v['attrId']]['langParams'][$v['langId']] = $v['attrVal'];
			}
		}
        $specAttrObj['attrs'] = $attrs;
		$data['specAttrObj'] = $specAttrObj;

	    return WSTReturn("", 1,$data);
	}
	


	
	/**
	 * 分页
	 */
	public function getMouldList(){
		$shopId = (int)session('WST_USER.shopId');
		$goodsCatId = (int)input('goodsCatId');
		$where = [];
		$where[] = ["shopId","=",$shopId];
		$where[] = ["goodsCatId","=",$goodsCatId];
		$where[] = ["dataFlag","=",1];
		$rs = Db::name("moulds")->where($where)->order("createTime desc")->select();
		return $rs;
	}

	public function editMouldName(){
		$shopId = (int)session('WST_USER.shopId');
		$rs = $this->checkMouldName();
		if($rs['status']==1){
			$mouldId = (int)input("mouldId");
			$mouldName = input("mouldName");
			$data = [];
			$data["mouldName"] = $mouldName;
			$where = [];
			$where[] = ["shopId","=",$shopId];
			$where[] = ["id","=",$mouldId];
			Db::name("moulds")->where($where)->update($data);
			return WSTReturn(lang('edit_success'),1);
		}else{
			return $rs;
		}
	}

	public function checkMouldName(){
		$mouldId = (int)input("mouldId");
		$shopId = (int)session('WST_USER.shopId');
		$mouldName = input("mouldName");
		$goodsCatId = (int)input("goodsCatId");
		$where = [];
		$where[] = ["shopId","=",$shopId];
		$where[] = ["goodsCatId","=",$goodsCatId];
		$where[] = ["mouldName","=",$mouldName];
		if($mouldId>0)$where[] = ["id","<>",$mouldId];
		$rs = Db::name("moulds")->where($where)->find();
		if(empty($rs)){
			return WSTReturn("",1);
		}else{
			return WSTReturn(lang('mould_already_exists'),-1);
		}
	}

}
