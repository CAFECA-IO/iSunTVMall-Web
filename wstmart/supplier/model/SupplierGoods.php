<?php
namespace wstmart\supplier\model;
use wstmart\supplier\validate\SupplierGoods as Validate;
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
class SupplierGoods extends Base{
	protected $pk = 'goodsId';
     /**
      *  上架商品列表
      */
	public function saleByPage(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$where = [];
		$where[] = ['supplierId',"=",$supplierId];
		$where[] = ['goodsStatus',"=",1];
		$where[] = ['dataFlag',"=",1];
		$where[] = ['isSale',"=",1];
		$goodsAttr = (int)input('goodsAttr',-1);
		if(in_array($goodsAttr,[0,1,2,3])){
			$types = ['isRecom','isHot','isBest','isNew'];
			$where[] = [$types[$goodsAttr],"=",1];
		}
		$goodsType = input('goodsType');
		if($goodsType!='')$where[] = ['goodsType',"=",(int)$goodsType];
		$c1Id = (int)input('cat1');
		$c2Id = (int)input('cat2');
		$goodsName = input('goodsName');
		if($goodsName != ''){
			$where[] = ['sgl.goodsName|g.goodsSn','like',"%$goodsName%"];
		}
		if($c2Id!=0 && $c1Id!=0){
			$where[] = ['supplierCatId2',"=",$c2Id];
		}else if($c1Id!=0){
			$where[] = ['supplierCatId1',"=",$c1Id];
		}
		$rs = $this->alias('g')->join('__SUPPLIER_GOODS_LANGS__ sgl','sgl.goodsId=g.goodsId and sgl.langId='.WSTCurrLang())->where($where)
			->field('g.goodsId,sgl.goodsName,goodsImg,goodsType,goodsSn,isSale,isBest,isHot,isNew,isRecom,goodsStock,saleNum,supplierPrice,isSpec')
			->order('saleTime', 'desc')
			->paginate(input('limit/d'))->toArray();
		foreach ($rs['data'] as $key => $v){
			$rs['data'][$key]['verfiycode'] = WSTSupplierEncrypt($supplierId);
		}
		return $rs;
	}
	/**
	 * 审核中的商品
	 */
    public function auditByPage(){
    	$supplierId = (int)session('WST_SUPPLIER.supplierId');
    	$where = [];
        $where[] = ['supplierId',"=",$supplierId];
        $where[] = ['goodsStatus',"=",0];
        $where[] = ['dataFlag',"=",1];
        $where[] = ['isSale',"=",1];
        $goodsAttr = (int)input('goodsAttr',-1);
		if(in_array($goodsAttr,[0,1,2,3])){
			$types = ['isRecom','isHot','isBest','isNew'];
			$where[] = [$types[$goodsAttr],"=",1];
		}
		$goodsType = input('goodsType');
		if($goodsType!='')$where[] = ['goodsType',"=",(int)$goodsType];
		$c1Id = (int)input('cat1');
		$c2Id = (int)input('cat2');
		$goodsName = input('goodsName');
		if($goodsName != ''){
			$where[] = ['sgl.goodsName|g.goodsSn','like',"%$goodsName%"];
		}
		if($c2Id!=0 && $c1Id!=0){
			$where[] = ['supplierCatId2',"=",$c2Id];
		}else if($c1Id!=0){
			$where[] = ['supplierCatId1',"=",$c1Id];
		}

		$rs = $this->alias('g')->join('__SUPPLIER_GOODS_LANGS__ sgl','sgl.goodsId=g.goodsId and sgl.langId='.WSTCurrLang())->where($where)
			->field('g.goodsId,sgl.goodsName,goodsImg,goodsType,goodsSn,isSale,isBest,isHot,isNew,isRecom,goodsStock,saleNum,supplierPrice,isSpec')
			->order('saleTime', 'desc')
			->paginate(input('limit/d'))->toArray();
        foreach ($rs['data'] as $key => $v){
			$rs['data'][$key]['verfiycode'] =  WSTSupplierEncrypt($supplierId);
		}
		return $rs;
	}
	/**
	 * 仓库中的商品
	 */
    public function storeByPage(){
    	$supplierId = (int)session('WST_SUPPLIER.supplierId');
    	$where = [];
    	$where[]=['supplierId',"=",$supplierId];
		$where[] = ['dataFlag',"=",1];
		$where[] = ['isSale',"=",0];
		$goodsAttr = (int)input('goodsAttr',-1);
		if(in_array($goodsAttr,[0,1,2,3])){
			$types = ['isRecom','isHot','isBest','isNew'];
			$where[] = [$types[$goodsAttr],"=",1];
		}
		$goodsType = input('goodsType');
		if($goodsType!='')$where[] = ['goodsType',"=",(int)$goodsType];
		$c1Id = (int)input('cat1');
		$c2Id = (int)input('cat2');
		$goodsName = input('goodsName');
		if($goodsName != ''){
			$where[] = ['sgl.goodsName|g.goodsSn','like',"%$goodsName%"];
		}
		if($c2Id!=0 && $c1Id!=0){
			$where[] = ['supplierCatId2',"=",$c2Id];
		}else if($c1Id!=0){
			$where[] = ['supplierCatId1',"=",$c1Id];
		}
		$rs = $this->alias('g')->join('__SUPPLIER_GOODS_LANGS__ sgl','sgl.goodsId=g.goodsId and sgl.langId='.WSTCurrLang())->where($where)
		    ->where('goodsStatus','<>',-1)
			->field('g.goodsId,sgl.goodsName,goodsImg,goodsType,goodsSn,isSale,isBest,isHot,isNew,isRecom,goodsStock,saleNum,supplierPrice,isSpec')
			->order('saleTime', 'desc')
			->paginate(input('limit/d'))->toArray();
        foreach ($rs['data'] as $key => $v){
			$rs['data'][$key]['verfiycode'] =  WSTSupplierEncrypt($supplierId);
		}
		return $rs;
	}
	/**
	 * 违规的商品
	 */
	public function illegalByPage(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$where = [];
		$where[] = ['supplierId',"=",$supplierId];
		$where[] = ['goodsStatus',"=",-1];
		$where[] = ['dataFlag',"=",1];
		$goodsType = input('goodsType');
		if($goodsType!='')$where['goodsType'] = (int)$goodsType;
		$c1Id = (int)input('cat1');
		$c2Id = (int)input('cat2');
		$goodsName = input('goodsName');
		if($goodsName != ''){
			$where[] = ['sgl.goodsName|g.goodsSn','like',"%$goodsName%"];
		}
		if($c2Id!=0 && $c1Id!=0){
			$where[] = ['supplierCatId2',"=",$c2Id];
		}else if($c1Id!=0){
			$where[] = ['supplierCatId1',"=",$c1Id];
		}

		$rs = $this->alias('g')->join('__SUPPLIER_GOODS_LANGS__ sgl','sgl.goodsId=g.goodsId and sgl.langId='.WSTCurrLang())->where($where)
			->field('g.goodsId,sgl.goodsName,goodsImg,goodsType,goodsSn,isSale,isBest,isHot,isNew,isRecom,illegalRemarks,goodsStock,saleNum,supplierPrice,isSpec')
			->order('saleTime', 'desc')
			->paginate(input('limit/d'))->toArray();
		foreach ($rs['data'] as $key => $v){
			$rs['data'][$key]['verfiycode'] = WSTSupplierEncrypt($supplierId);
		}
		return $rs;
	}

	/**
	 * 删除商品
	 */
	public function del(){
	    $id = input('post.id/d');
		$data = [];
		$data['dataFlag'] = -1;
		Db::startTrans();
		try{
		    $result = $this->update($data,['goodsId'=>$id]);
	        if(false !== $result){
	   //      	WSTUnuseResource('goods','goodsImg',$id);
	   //      	WSTUnuseResource('goods','gallery',$id);
	   //      	WSTUnuseResource('goods','goodsVideo',$id);
	   //      	// 商品描述图片
	   //      	$desc = $this->where('goodsId',$id)->value('goodsDesc');
				// WSTEditorImageRocord(0, $id, $desc,'');
				model('SupplierCarts')->delCartByUpdate($id);
				Db::commit();
				WSTClearAllCache();
	        	//标记删除购物车
	        	return WSTReturn(lang("op_ok"), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang("op_err"),-1);
	}
	/**
	  * 批量删除商品
	  */
	 public function batchDel(){
	 	$supplierId = (int)session('WST_SUPPLIER.supplierId');
	   	$ids = input('post.ids/a');
	   	Db::startTrans();
		try{
		   	$rs = $this->where([['goodsId','in',$ids],['supplierId','=',$supplierId]])->setField('dataFlag',-1);
			if(false !== $rs){
				//标记删除购物车
				foreach ($ids as $v){
					// WSTUnuseResource('goods','goodsImg',(int)$v);
					// WSTUnuseResource('goods','gallery',(int)$v);
					// WSTUnuseResource('goods','goodsVideo',(int)$v);
	    //     	    // 商品描述图片
		   //      	$desc = $this->where('goodsId',(int)$v)->value('goodsDesc');
					// WSTEditorImageRocord(0, (int)$v, $desc,'');
					model('SupplierCarts')->delCartByUpdate((int)$v);
				}
				Db::commit();
				WSTClearAllCache();
	        	return WSTReturn(lang("op_ok"), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang("op_err"),-1);
	 }

	/**
	 * 批量上架商品
	 */
	public function changeSale(){
		$ids = input('post.ids/a');
		$isSale = (int)input('post.isSale',1);
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$now = date('Y-m-d H:i:s');
		//判断商品是否满足上架要求
		if($isSale==1){
			//0.核对供货商状态
	 		$supplierRs = model('suppliers')->find($supplierId);
	 		if($supplierRs['supplierStatus']!=1 || $supplierRs['dataFlag']==-1){
	 			return 	WSTReturn(lang("cannot_sale_goods"),-3);
	 		}
	 		//直接设置上架 返回受影响条数
	 		$where = [];
	 		$where[] = ['g.goodsId','in',$ids];
	 		$where[] = ['gc.dataFlag','=',1];
	 		$where[] = ['g.supplierId','=',$supplierId];
	 		$where[] = ['gc.isShow','=',1];
	 		$where[] = ['g.goodsImg','<>',''];
	 		$data = [];
	 		$data['isSale'] = 1;
	 		$data['saleTime'] = $now;
			if(WSTConf("CONF.isGoodsVerify")==1){
				$data['goodsStatus'] = 0;
			}else{
				$data['goodsStatus'] = 1;
			}
			$rs = $this->alias('g')
				  ->join('__GOODS_CATS__ gc','g.goodsCatId=gc.CatId','inner')
				  ->where($where)->setField($data);
			if($rs!==false){

				$status = ($rs==count($ids))?1:2;
				if($status==1){
					return WSTReturn(lang("op_ok"), 1,['num'=>$rs]);
				}else{

					return WSTReturn(lang("some_sale_goods_fail",[$rs]), 2,['num'=>$rs]);
				}
			}else{
	 			return WSTReturn(lang("goods_change_to_sale_fail"), -2);
	 		}

		}else{
			$rs = $this->where([['goodsId','in',$ids],['supplierId','=',$supplierId]])->setField(['isSale'=>0,'saleTime'=>$now]);
			if($rs !== false){
				model('SupplierCarts')->delCartByUpdate($ids);
				return WSTReturn(lang("op_ok"), 1);
			}else{
				return WSTReturn($this->getError(), -1);
			}
		}
	}
	/**
	* 修改商品状态
	*/
	public function changSaleStatus(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$allowArr = ['isHot','isNew','isBest','isRecom'];
		$is = input('post.is');
		if(!in_array($is,$allowArr))return WSTReturn(lang("op_illegal"),-1);
		$status = (input('post.status',1)==1)?0:1;
		$id = (int)input('post.id');
		$rs = $this->where(["supplierId"=>$supplierId,'goodsId'=>$id])->setField($is,$status);
		if($rs!==false){
			return WSTReturn(lang("op_ok"),1);
		}else{
			return WSTReturn($this->getError(),-1);
		}
	}
	/**
	 * 批量修改商品状态
	 */
	public function changeGoodsStatus(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		//设置为什么 hot new best rec
		$allowArr = ['isHot','isNew','isBest','isRecom'];
		$is = input('post.is');
		if(!in_array($is,$allowArr))return WSTReturn(lang("op_illegal"),-1);
		//设置哪一个状态
		$status = input('post.status',1);
		$ids = input('post.ids/a');
		$rs = $this->where([['goodsId','in',$ids],['supplierId','=',$supplierId]])->setField($is, $status);
		if($rs!==false){
			return WSTReturn(lang("op_ok"),1);
		}else{
			return WSTReturn($this->getError(),-1);
		}

	}
	/**
	 * 获取商品规格属性
	 */
	public function getSpecAttrs(){
		$goodsType = (int)input('goodsType');
		$goodsCatId = Input('post.goodsCatId/d');
		$goodsCatIds = model('GoodsCats')->getParentIs($goodsCatId);
		$data = [];
		if($goodsType==0){
			$specs = Db::name('spec_cats')->alias('a')->join('__SPEC_CATS_LANGS__ scl','scl.catId=a.catId and scl.langId='.WSTCurrLang())->where([['goodsCatId','in',$goodsCatIds],['isShow','=',1],['dataFlag','=',1],['shopId','=',0]])->field('a.catId,scl.catName,isAllowImg')->order('isAllowImg desc,catSort asc,catId asc')->select();
			$spec0 = null;
			$spec1 = [];
			foreach ($specs as $key => $v){
				if($v['isAllowImg']==1){
					$spec0 = $v;
				}else{
					$spec1[] = $v;
				}
			}
			$data['spec0'] = $spec0;
			$data['spec1'] = $spec1;
		}
		$data['attrs'] = Db::name('attributes')->alias('a')->join('__ATTRIBUTES_LANGS__ al','al.attrId=a.attrId and al.langId='.WSTCurrLang())->where([['goodsCatId','in',$goodsCatIds],['isShow','=',1],['dataFlag','=',1],['shopId','=',0]])->field('a.attrId,al.attrName,attrType,al.attrVal')->order('attrSort asc,attrId asc')->select();
	    return WSTReturn("", 1,$data);
	}
	/**
	 * 修改商品库存/价格
	 */
	public function editGoodsBase(){
		$goodsId = (int)Input("goodsId");
		$post = input('post.');
		$data = [];
		if(isset($post['goodsStock'])){
			$data['goodsStock'] = (int)input('post.goodsStock',0);
			if($data['goodsStock']<0)return WSTReturn(lang("inventory_cannot_be_negative"));
		}elseif(isset($post['supplierPrice'])){
			$data['supplierPrice'] = (float)input('post.supplierPrice',0);
			if($data['supplierPrice']<0.01)return WSTReturn(lang("price_must_be_greater_than_0.01"));
		}else{
			return WSTReturn(lang("op_err"),-1);
		}
		$rs = $this->update($data,['goodsId'=>$goodsId,'supplierId'=>(int)session('WST_SUPPLIER.supplierId')]);
		if($rs!==false){
			return WSTReturn(lang("op_ok"),1);
		}else{
			return WSTReturn(lang("op_err"),-1);
		}
	}
	/**
	 *  预警库存列表
	 */
	public function stockByPage(){
		$where = [];
		$c1Id = (int)input('cat1');
		$c2Id = (int)input('cat2');
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		if($c1Id!=0)$where[] = " supplierCatId1=".$c1Id;
		if($c2Id!=0)$where[] = " supplierCatId2=".$c2Id;
		$where[] = " g.supplierId = ".$supplierId;
		$prefix = config('database.prefix');
		$sql1 = 'SELECT g.goodsId,gl.goodsName,g.goodsType,g.goodsImg,gs.specStock goodsStock ,gs.warnStock warnStock,g.isSpec,gs.productNo,gs.id,gs.specIds,g.isSale
                    FROM '.$prefix.'supplier_goods g inner join '.$prefix.'goods_langs gl on gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang().' 
                    inner JOIN '.$prefix.'supplier_goods_specs gs ON gs.goodsId=g.goodsId and gs.specStock <= gs.warnStock and gs.warnStock>0
                    WHERE g.dataFlag = 1 and '.implode(' and ',$where);

		$sql2 = 'SELECT g.goodsId,gl.goodsName,g.goodsType,g.goodsImg,g.goodsStock,g.warnStock,g.isSpec,g.productNo,0 as id,"" as specIds,g.isSale
                    FROM '.$prefix.'supplier_goods g inner join '.$prefix.'goods_langs gl on gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang().'
                    WHERE g.dataFlag = 1  and isSpec=0 and g.goodsStock<=g.warnStock 
                    and g.warnStock>0 and '.implode(' and ',$where);
		$page = (int)input('post.'.config('paginate.var_page'));
		$page = ($page<=0)?1:$page;
		$pageSize = 20;
		$start = ($page-1)*$pageSize;
		$sql = $sql1." union ".$sql2;
		$sqlNum = 'select count(*) wstNum from ('.$sql.") as c";
		$sql = 'select * from ('.$sql.') as c order by isSale desc limit '.$start.','.$pageSize;
		$rsNum = Db::query($sqlNum);
		$rsdata = Db::query($sql);
		$rs = WSTPager((int)$rsNum[0]['wstNum'],$rsdata,$page,$pageSize);
		if(empty($rs['data']))return $rs;
		$specIds = [];
		foreach ($rs['data'] as $key =>$v){
			$specIds[$key] = explode(':',$v['specIds']);
			$rss = Db::name('supplier_spec_items')->alias('si')
			->join('__SPEC_ITEMS_LANGS__ sil','sil.itemId=si.itemId and sil.langId='.WSTCurrLang())
			->join('__SPEC_CATS__ sc','sc.catId=si.catId','left')
			->join('__SPEC_CATS_LANGS__ scl','scl.catId=sc.catId and scl.langId='.WSTCurrLang(),'inner')
			->where('si.supplierId = '.$supplierId.' and si.goodsId = '.$v['goodsId'])
			->where([['si.itemId','in',$specIds[$key]]])
			->field('si.itemId,sil.itemName,sc.catId,scl.catName')
			->select();
			$rs['data'][$key]['spec'] = $rss;
		}
		return $rs;
	}
	/**
	 *  预警修改预警库存
	 */
	public function editwarnStock(){
		$id = input('post.id/d');
		$type = input('post.type/d');
		$number = (int)input('post.number');
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$data = $data2 = [];
		$data['supplierId'] =  $data2['supplierId'] = $supplierId;
		$datat=array('1'=>'specStock','2'=>'warnStock','3'=>'goodsStock','4'=>'warnStock');
		if(!empty($type)){
			$data[$datat[$type]] = $number;
			if($type==1 || $type==2){
				$data['goodsId'] = $goodsId = input('post.goodsId/d');
				$rss = Db::name("supplier_goods_specs")->where('id', $id)->update($data);
				//更新商品库存
				$goodsStock = 0;
				if($rss!==false){
					$specStocks = Db::name("supplier_goods_specs")->where(['supplierId'=>$supplierId,'goodsId'=>$goodsId,'dataFlag'=>1])->field('specStock')->select();
					foreach ($specStocks as $key =>$v){
						$goodsStock = $goodsStock+$v['specStock'];
					}
					$data2['goodsStock'] = $goodsStock;
					$rs = $this->update($data2,['goodsId'=>$goodsId]);
				}else{
					return WSTReturn(lang("op_err"),-1);
				}
			}
			if($type==3 || $type==4){
				$rs = $this->update($data,['goodsId'=>$id]);
			}
			if($rs!==false){
				return WSTReturn(lang("op_ok"),1);
			}else{
				return WSTReturn(lang("op_err"),-1);
			}
		}
		return WSTReturn(lang("op_err"),-1);
	}

	/**
	 * 获取商品资料方便编辑
	 */
	public function getById($goodsId){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
        $isApp = (int)input('post.isApp',0);
		$rs = Db::name('supplier_goods')->where(['supplierId'=>$supplierId,'goodsId'=>$goodsId])->find();
		$rs['langParams'] = Db::name('supplier_goods_langs')->where(['goodsId'=>$goodsId])->column('*','langId');
		if(!empty($rs)){
			if($rs['gallery']!='')$rs['gallery'] = explode(',',$rs['gallery']);
			$resourceDomain = ($isApp==1)?WSTConf('CONF.resourceDomain'):WSTConf('CONF.resourcePath');
			foreach ($rs['langParams'] as $key => $lv) {
				$lv['goodsDesc'] = htmlspecialchars_decode($lv['goodsDesc']);
                $rs['langParams'][$key]['goodsDesc'] = str_replace('${DOMAIN}',$resourceDomain,$lv['goodsDesc']);
			}
			//获取规格值
			$specs = Db::name('spec_cats')->alias('gc')->join('supplier_spec_items sit','gc.catId=sit.catId','inner')
			                      ->join('__SUPPLIER_SPEC_ITEMS_LANGS__ sil','sil.itemId=sit.itemId','inner')
			                      ->where(['sit.goodsId'=>$goodsId,'gc.isShow'=>1,'sit.dataFlag'=>1])
			                      ->field('gc.isAllowImg,sit.catId,sit.itemId,sil.itemName,sit.itemImg,sil.langId')
			                      ->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
			$fspecs = [];
			foreach ($specs as $key => $fv) {
				if(!isset($fspecs[$fv['itemId']])){
					$fspecs[$fv['itemId']] = $fv;
                    $fspecs[$fv['itemId']]['langParams'][$fv['langId']] = $fv['itemName'];
				}else{
                    $fspecs[$fv['itemId']]['langParams'][$fv['langId']] = $fv['itemName'];
				}
			}
			$spec0 = [];
			$spec1 = [];
			foreach ($fspecs as $key =>$v){
				if($v['isAllowImg']==1){
					$spec0[] = $v;
				}else{
					$spec1[] = $v;
				}
			}
			$rs['spec0'] = $spec0;
			$rs['spec1'] = $spec1;
			//获取销售规格
			$rs['saleSpec'] = Db::name('supplier_goods_specs')->where('goodsId',$goodsId)->field('id,isDefault,productNo,specIds,marketPrice,specPrice,costPrice,specStock,warnStock,saleNum,specWeight,specVolume')->select();
			//获取属性值
			$rs['attrs'] = Db::name('supplier_goods_attributes')->alias('ga')->join('attributes a','ga.attrId=a.attrId','inner')
			                 ->join('__SUPPLIER_GOODS_ATTRIBUTES_LANGS__ gal','gal.goodsAttrId=ga.id','inner')
			                 ->where('ga.goodsId',$goodsId)->field('ga.attrId,a.attrType,gal.attrVal,gal.langId')->select();
			//echo Db::name('supplier_goods_attributes')->getLastSql();
			$attr = [];
			foreach ($rs['attrs'] as $key => $av) {
				if(!isset($attr[$av['attrId']])){
					$attr[$av['attrId']] = $av;
					$attr[$av['attrId']]['langParams'][$av['langId']] = $av['attrVal'];
				}else{
					$attr[$av['attrId']]['langParams'][$av['langId']] = $av['attrVal'];
				}
			}
			$rs['attrs'] = $attr;
			if($rs['isWholesale']==1){
		        $rs['wholesale']  = Db::name('supplier_wholesale_goods')->where('goodsId',$goodsId)->order('buyNum asc')->select();
		        $wholesale = [];
		        foreach ($rs['wholesale'] as $key => $v) {
		        	$v['goodsPrice'] = $rs['supplierPrice'] - $v['rebate'];
		        	$wholesale[] = $v;
		        }
		        $rs['wholesale'] = $wholesale;
		    }else{
		    	$rs['wholesale'] = [];
		    }
		}
		return $rs;
	}


	/**
	 * 新增商品
	 */
	public function add($sId=0){
		$supplierId = ($sId==0)?(int)session('WST_SUPPLIER.supplierId'):$sId;
		$data = input('post.');
		$data['goodsType'] = 0;
        $isApp = (int)input('post.isApp',0);
		$specsIds = input('post.specsIds');
		WSTUnset($data,'goodsId,statusRemarks,goodsStatus,dataFlag');
        if(WSTConf("CONF.isGoodsVerify")==1){
            $data['goodsStatus'] = 0;
        }else{
            $data['goodsStatus'] = 1;
        }
        foreach (WSTSysLangs() as $key => $v) {
			$goodsName = input('langParams'.$v['id'].'goodsName');
			if(!WSTCheckFilterWords($goodsName,WSTConf("CONF.limitWords"))){
				return WSTReturn(lang('goods_name_illegal_characters'));
			}
	        if(!WSTCheckFilterWords($goodsName,WSTConf("CONF.sensitiveWords"))){
	            $data['goodsStatus'] = 0;
			}
			$goodsSeoKeywords = input('langParams'.$v['id'].'goodsSeoKeywords');
			if($goodsSeoKeywords!=''){
	        	if(!WSTCheckFilterWords($goodsSeoKeywords,WSTConf("CONF.limitWords"))){
					return WSTReturn(lang('goods_seo_key_illegal_characters'));
				}
	            if(!WSTCheckFilterWords($goodsSeoKeywords,WSTConf("CONF.sensitiveWords"))){
	                $data['goodsStatus'] = 0;
	            }
	        }
            $goodsSeoDesc = input('langParams'.$v['id'].'goodsSeoDesc');
            if($goodsSeoDesc!=''){
	        	if(!WSTCheckFilterWords($goodsSeoDesc,WSTConf("CONF.limitWords"))){
					return WSTReturn(lang('goods_seo_desc_illegal_characters'));
				}
	            if(!WSTCheckFilterWords($goodsSeoDesc,WSTConf("CONF.sensitiveWords"))){
	                $data['goodsStatus'] = 0;
	            }
	        }
	        $goodsTips = input('langParams'.$v['id'].'goodsTips');
			if(isset($goodsTips)){
				if(!WSTCheckFilterWords($goodsTips,WSTConf("CONF.limitWords"))){
					return WSTReturn(lang('goods_promotion_illegal_characters'));
				}
	            if(!WSTCheckFilterWords($goodsTips,WSTConf("CONF.sensitiveWords"))){
	                $data['goodsStatus'] = 0;
	            }
			}
			$goodsDesc = input('langParams'.$v['id'].'goodsDesc');
	        if(!WSTCheckFilterWords($goodsDesc,WSTConf("CONF.limitWords"))){
	            return WSTReturn(lang('goods_desc_illegal_characters'));
	        }
	        if(!WSTCheckFilterWords($goodsDesc,WSTConf("CONF.sensitiveWords"))){
	            $data['goodsStatus'] = 0;
	        }
		}


		if($data['isSale']==1 && $data['goodsImg']==''){
			return WSTReturn(lang("require_sale_goods_img"));
		}
        if((int)$data['goodsType']==0 &&  (int)$data['isFreeShipping']==0 && (int)$data['supplierExpressId']==0){
        	return WSTReturn(lang("please_choose_express_company"));
        }
		$data['supplierId'] = $supplierId;
		$data['saleTime'] = date('Y-m-d H:i:s');
		$data['createTime'] = date('Y-m-d H:i:s');
		$goodmodel = model('GoodsCats');
		$goodsCats = $goodmodel->getParentIs($data['goodsCatId']);
		//校验商品分类有效性
		$applyCatIds = model("suppliers")->getSupplierApplyGoodsCatsById($supplierId);
		$isApplyCatIds = array_intersect($applyCatIds,$goodsCats);
		if(empty($isApplyCatIds))return WSTReturn(lang("goods_cats_incomplete"));
		$data['goodsCatIdPath'] = implode('_',$goodsCats)."_";

		if($data['goodsType']==0){
			$data['isSpec'] = ($specsIds!='')?1:0;
		}else{
			$data['isSpec'] = 0;
		}

		Db::startTrans();
        try{
        	//对图片域名进行处理
			// $resourceDomain = ($isApp==1)?WSTConf('CONF.resourceDomain'):WSTConf('CONF.resourcePath');
			// $data['goodsDesc'] = htmlspecialchars_decode($data['goodsDesc']);
   //          $data['goodsDesc'] = WSTRichEditorFilter($data['goodsDesc']);
   //          $data['goodsDesc'] = str_replace($resourceDomain.'/upload/','${DOMAIN}/upload/',$data['goodsDesc']);
        	//保存插件数据钩子
        	hook('supplierBeforeEidtGoods',['data'=>&$data]);
        	$supplier = model('suppliers')->get(['supplierId'=>$supplierId]);
        	if($supplier['dataFlag'] ==-1 || $supplier['supplierStatus'] != 1)$data['isSale'] = 0;
        	$validate = new Validate;
        	if (!$validate->scene(true)->check($data)) {
        		return WSTReturn($validate->getError());
        	}else{
        		$result = $this->allowField(true)->save($data);
        	}
			if(false !== $result){
				$goodsId = $this->goodsId;
				//更新主要文字
				$resourceDomain = ($isApp==1)?WSTConf('CONF.resourceDomain'):WSTConf('CONF.resourcePath');
				$goodsLangs = [];
				foreach (WSTSysLangs() as $key => $v) {
				   $dataLang = [];
				   $dataLang['langId'] = $v['id'];
				   $dataLang['goodsId'] = $goodsId;
			       $dataLang['goodsName'] = input('langParams'.$v['id'].'goodsName');
			       $dataLang['goodsSeoKeywords'] = input('langParams'.$v['id'].'goodsSeoKeywords');
			       $dataLang['goodsSeoDesc'] = input('langParams'.$v['id'].'goodsSeoDesc');
                   $dataLang['goodsTips'] = input('langParams'.$v['id'].'goodsTips');
                   $dataLang['goodsDesc'] = input('langParams'.$v['id'].'goodsDesc');
                   //对图片域名进行处理

				   $dataLang['goodsDesc'] = htmlspecialchars_decode($dataLang['goodsDesc']);
		           $dataLang['goodsDesc'] = WSTRichEditorFilter($dataLang['goodsDesc']);
		           $dataLang['goodsDesc'] = str_replace($resourceDomain.'/upload/','${DOMAIN}/upload/',$dataLang['goodsDesc']);
                   $goodsLangs[] = $dataLang;
                }
                Db::name('supplier_goods_langs')->insertAll($goodsLangs);
				//商品图片
				// WSTUseResource(0, $goodsId, $data['goodsImg']);
				// //商品相册
				// WSTUseResource(0, $goodsId, $data['gallery']);
				// //商品描述图片
				// WSTEditorImageRocord(0, $goodsId, '',$data['goodsDesc']);
				// // 视频
				// if(isset($data['goodsVideo']) && $data['goodsVideo']!=''){
				// 	WSTUseResource(0, $goodsId, $data['goodsVideo']);
				// }
				//建立商品评分记录
				$gs = [];
				$gs['goodsId'] = $goodsId;
				$gs['supplierId'] = $supplierId;
				Db::name('supplier_goods_scores')->insert($gs);
				//如果是实物商品并且有销售规格则保存销售和规格值
    	        if($data['goodsType']==0 && $specsIds!=''){
	    	        $specsIds = explode(',',$specsIds);
			    	$specsArray = [];
			    	foreach ($specsIds as $v){
			    		$vs = explode('-',$v);
			    		foreach ($vs as $vv){
			    		   if(!in_array($vv,$specsArray))$specsArray[] = $vv;
			    		}
			    	}
		    		//保存规格名称
		    		$specMap = [];
		    		$sitemLangs = [];
		    		foreach ($specsArray as $v){
		    			$vv = explode('_',$v);
		    			$sitem = [];
		    			$sitem['supplierId'] = $supplierId;
		    			$sitem['catId'] = (int)$vv[0];
		    			$sitem['goodsId'] = $goodsId;
		    			$sitem['itemImg'] = input('post.specImg_'.$vv[0]."_".$vv[1],'');
		    			$sitem['dataFlag'] = 1;
		    			$sitem['createTime'] = date('Y-m-d H:i:s');
		    			$itemId = Db::name('supplier_spec_items')->insertGetId($sitem);
		    			foreach (WSTSysLangs() as $lkey => $lv) {
		    				$sitemLang = [];
		    				$sitemLang['itemId'] = $itemId;
		    				$sitemLang['langId'] = $lv['id'];
		    				$sitemLang['goodsId'] = $goodsId;
			    			$sitemLang['itemName'] = input('post.specName_'.$vv[0]."_".$vv[1].'_'.$lv['id']);
			    			$sitemLangs[] = $sitemLang;
			    		}
		    			// if($sitem['itemImg']!='')WSTUseResource(0, $itemId, $sitem['itemImg']);
		    			$specMap[$v] = $itemId;
		    		}
		    		//保存规格多语言文字
		    		if(count($sitemLangs)>0)Db::name('supplier_spec_items_langs')->insertAll($sitemLangs);
		    		//保存销售规格
		    		$defaultPrice = 0;//最低价
                    $costPrice = 0;
		    		$totalStock = 0;//总库存
		    		$gspecArray = [];
		    		$isFindDefaultSpec = false;
		    		$defaultSpec = Input('post.defaultSpec');
		    		foreach ($specsIds as $v){
		    			$vs = explode('-',$v);
		    			$goodsSpecIds = [];
		    			foreach ($vs as $gvs){
		    				$goodsSpecIds[] = $specMap[$gvs];
		    			}
		    			$gspec = [];
		    			$gspec['specIds'] = implode(':',$goodsSpecIds);
		    			$gspec['supplierId'] = $supplierId;
		    			$gspec['goodsId'] = $goodsId;
		    			$gspec['productNo'] = Input('productNo_'.$v);
		    			$gspec['marketPrice'] = (float)Input('marketPrice_'.$v);
		    			$gspec['specPrice'] = (float)Input('specPrice_'.$v);
		    			$gspec['specStock'] = (int)Input('specStock_'.$v);
                        $gspec['costPrice'] = (float)Input('costPrice_'.$v);
		    			$gspec['warnStock'] = (int)Input('warnStock_'.$v);
		    			$gspec['specWeight'] = (float)Input('specWeight_'.$v);
		    			$gspec['specVolume'] = (float)Input('specVolume_'.$v);
		    			//设置默认规格
		    			if($defaultSpec==$v){
		    				$isFindDefaultSpec = true;
		    				$defaultPrice = $gspec['specPrice'];
                            $costPrice = $gspec['costPrice'];
		    				$gspec['isDefault'] = 1;
		    			}else{
		    				$gspec['isDefault'] = 0;
		    			}
                        $gspecArray[] = $gspec;
                        //获取总库存
                        $totalStock = $totalStock + $gspec['specStock'];
		    		}
		    		if(!$isFindDefaultSpec)return WSTReturn(lang("require_default_spec"));
		    		if(count($gspecArray)>0){
		    		    Db::name('supplier_goods_specs')->insertAll($gspecArray);
		    		    //更新默认价格和总库存
    	                $this->where('goodsId',$goodsId)->update(['isSpec'=>1,'supplierPrice'=>$defaultPrice,'costPrice'=>$costPrice,'goodsStock'=>$totalStock]);
		    		}
    	        }

    	        //保存商品属性
		    	$attrsArray = [];
		    	$attrRs = Db::name('attributes')->where([['goodsCatId','in',$goodsCats],['isShow','=',1],['dataFlag','=',1],['shopId','=',0]])
		    		            ->field('attrId,attrType')->select();
		        $attrRsVals =  Db::name('attributes')->alias('a')->join('__ATTRIBUTES_LANGS__ al','al.attrId=a.attrId ')->where([['attrType','<>',0],['goodsCatId','in',$goodsCats],['isShow','=',1],['dataFlag','=',1],['shopId','=',0]])->field('al.attrId,al.langId,al.attrVal')->select();
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
			    		$attrs['supplierId'] = $supplierId;
			    		$attrs['goodsId'] = $goodsId;
			    		$attrs['attrId'] = $v['attrId'];
			    		$attrs['createTime'] = date('Y-m-d H:i:s');
			    		$goodsAttrId = Db::name('supplier_goods_attributes')->insertGetId($attrs);
			    		//文本输入框直接取获取的内容
			    		if($v['attrType']==0){
				    		foreach (WSTSysLangs() as $lkey => $lv) {
				    			$attrLang = [];
				    			$attrLang['goodsAttrId'] = $goodsAttrId;
				    			$attrLang['langId'] = $lv['id'];
				    			$attrLang['goodsId'] = $goodsId;
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
                                            if(!in_array($akey,$checkAttrValNo))$checkAttrValNo[] = $akey;
				    					}
				    				}
				    			}
				    		}

				    		if(count($checkAttrValNo)>0){
                                foreach (WSTSysLangs() as $lkey => $lv) {
                                	$attrVal = [];
                                	$attrLang = [];
					    			$attrLang['goodsAttrId'] = $goodsAttrId;
					    			$attrLang['langId'] = $lv['id'];
					    			$attrLang['goodsId'] = $goodsId;
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
		    	if(count($attrLangs)>0)Db::name('supplier_goods_attributes_langs')->insertAll($attrLangs);
		    	//保存关键字
        	    $searchKeys = WSTSupplierGoodsSearchKey($goodsId);
        	    $this->where('goodsId',$goodsId)->update(['goodsSerachKeywords'=>implode(',',$searchKeys)]);

        	    $isWholesale = (int)input('isWholesale');
				$wholesaleNum = (int)input('wholesaleNum');
				$realWholesaleNum = 0;
				$checkBuyNum = [];
				if($isWholesale==1 && $wholesaleNum<=0)exit(json_encode(WSTReturn(lang("pifa_incomplete"),-1)));

				if($isWholesale==1){
					//获取商品价格
					$goods = Db::name('supplier_goods')->alias('g')->join('supplier_goods_specs gsp','g.goodsId=gsp.goodsId and g.isSpec=1','left')
					                          ->where('g.goodsId',$goodsId)
					                          ->field('g.supplierPrice,g.isSpec,gsp.specPrice')
					                          ->select();
					$wholesale = [];
					for($i=0;$i<$wholesaleNum;$i++) {
						$buyNumVal = input('buyNum_'.$i);
						$buyNum = (int)input('buyNum_'.$i);
						$rebate = (float)input('rebate_'.$i);
						if($buyNumVal=='')continue;
						if($buyNum<=0)exit(json_encode(WSTReturn(lang("goods_wholesale_tips3"),-1)));
						if(in_array($buyNum,$checkBuyNum))exit(json_encode(WSTReturn(lang("goods_wholesale_tips4"),-1)));
						$checkBuyNum[] = $buyNum;
						if($rebate<=0)exit(json_encode(WSTReturn(lang("goods_wholesale_tips6"),-1)));
						$this->checkRebate($rebate,$goods);
						$wholesaledata = [];
						$wholesaledata['goodsId'] = $goodsId;
						$wholesaledata['buyNum'] = $buyNum;
						$wholesaledata['rebate'] = $rebate;
						$wholesale[] = $wholesaledata;
					}
					Db::name('supplier_wholesale_goods')->insertAll($wholesale);
				}


    	        Db::commit();
				return WSTReturn(lang("op_ok"), 1,['id'=>$goodsId]);
			}else{
				return WSTReturn($this->getError(),-1);
			}
        }catch (\Exception $e) {
        	print_r($e);
            Db::rollback();
            return WSTReturn(lang("op_err"),-1);
        }
	}

	/**
	 * 编辑商品资料
	 */
	public function edit($sId=0){
		$supplierId = ($sId==0)?(int)session('WST_SUPPLIER.supplierId'):$sId;
        $isApp = (int)input('post.isApp',0);
	    $goodsId = input('post.goodsId/d');
	    $specsIds = input('post.specsIds');
		$data = input('post.');
		WSTUnset($data,'goodsId,dataFlag,statusRemarks,goodsStatus,createTime');
		$ogoods = $this->where(['goodsId'=>$goodsId,'supplierId'=>$supplierId,'dataFlag'=>1])->field('goodsImg,goodsStatus,goodsType')->find();
		if(empty($ogoods))return WSTReturn(lang("no_find_goods"));
        if(WSTConf("CONF.isGoodsVerify")==1){
            $data['goodsStatus'] = 0;
        }else{
            $data['goodsStatus'] = 1;
        }
        foreach (WSTSysLangs() as $key => $v) {
			$goodsName = input('langParams'.$v['id'].'goodsName');
			if(!WSTCheckFilterWords($goodsName,WSTConf("CONF.limitWords"))){
				return WSTReturn(lang('goods_name_illegal_characters'));
			}
	        if(!WSTCheckFilterWords($goodsName,WSTConf("CONF.sensitiveWords"))){
	            $data['goodsStatus'] = 0;
			}
			$goodsSeoKeywords = input('langParams'.$v['id'].'goodsSeoKeywords');
			if($goodsSeoKeywords!=''){
	        	if(!WSTCheckFilterWords($goodsSeoKeywords,WSTConf("CONF.limitWords"))){
					return WSTReturn(lang('goods_seo_key_illegal_characters'));
				}
	            if(!WSTCheckFilterWords($goodsSeoKeywords,WSTConf("CONF.sensitiveWords"))){
	                $data['goodsStatus'] = 0;
	            }
	        }
            $goodsSeoDesc = input('langParams'.$v['id'].'goodsSeoDesc');
            if($goodsSeoDesc!=''){
	        	if(!WSTCheckFilterWords($goodsSeoDesc,WSTConf("CONF.limitWords"))){
					return WSTReturn(lang('goods_seo_desc_illegal_characters'));
				}
	            if(!WSTCheckFilterWords($goodsSeoDesc,WSTConf("CONF.sensitiveWords"))){
	                $data['goodsStatus'] = 0;
	            }
	        }
	        $goodsTips = input('langParams'.$v['id'].'goodsTips');
			if(isset($goodsTips)){
				if(!WSTCheckFilterWords($goodsTips,WSTConf("CONF.limitWords"))){
					return WSTReturn(lang('goods_promotion_illegal_characters'));
				}
	            if(!WSTCheckFilterWords($goodsTips,WSTConf("CONF.sensitiveWords"))){
	                $data['goodsStatus'] = 0;
	            }
			}
			$goodsDesc = input('langParams'.$v['id'].'goodsDesc');
	        if(!WSTCheckFilterWords($goodsDesc,WSTConf("CONF.limitWords"))){
	            return WSTReturn(lang('goods_desc_illegal_characters'));
	        }
	        if(!WSTCheckFilterWords($goodsDesc,WSTConf("CONF.sensitiveWords"))){
	            $data['goodsStatus'] = 0;
	        }
		}



		if($ogoods['goodsImg']=='' && $data['isSale']==1 && $data['goodsImg']==''){
			return WSTReturn(lang("require_sale_goods_img"));
		}

        if((int)$ogoods['goodsType']==0 && (int)$data['isFreeShipping']==0 && (int)$data['supplierExpressId']==0){
        	return WSTReturn(lang("please_choose_express_company"));
        }

		//不允许修改商品类型
		$data['goodsType'] = $ogoods['goodsType'];
		$data['saleTime'] = date('Y-m-d H:i:s');
		$goodmodel = model('GoodsCats');
		$goodsCats = $goodmodel->getParentIs($data['goodsCatId']);
		//校验商品分类有效性
		$applyCatIds = model("suppliers")->getSupplierApplyGoodsCatsById($supplierId);
		$isApplyCatIds = array_intersect($applyCatIds,$goodsCats);
		if(empty($isApplyCatIds))return WSTReturn(lang("goods_cats_incomplete"));
		$data['goodsCatIdPath'] = implode('_',$goodsCats)."_";
		if($data['goodsType']==0){
		    $data['isSpec'] = ($specsIds!='')?1:0;
	    }else{
	    	$data['isSpec'] = 0;
	    }
		Db::startTrans();
        try{
            //对图片域名进行处理
			// $resourceDomain = ($isApp==1)?WSTConf('CONF.resourceDomain'):WSTConf('CONF.resourcePath');
			// $data['goodsDesc'] = htmlspecialchars_decode($data['goodsDesc']);
   //          $data['goodsDesc'] = WSTRichEditorFilter($data['goodsDesc']);
   //          $data['goodsDesc'] = str_replace($resourceDomain.'/upload/','${DOMAIN}/upload/',$data['goodsDesc']);

            //保存插件数据钩子
        	hook('supplierBeforeEidtGoods',['data'=>&$data]);

        	//商品图片
			// WSTUseResource(0, $goodsId, $data['goodsImg'],'supplier_goods','goodsImg');
			// //商品相册
			// WSTUseResource(0, $goodsId, $data['gallery'],'supplier_goods','gallery');
			// // 商品描述图片
	  //       $desc = $this->where('goodsId',$goodsId)->value('goodsDesc');
			// WSTEditorImageRocord(0, $goodsId, $desc, $data['goodsDesc']);
			// 视频
			// if(isset($data['goodsVideo']) && $data['goodsVideo']!=''){
			// 	WSTUseResource(0, $goodsId, $data['goodsVideo'], 'supplier_goods', 'goodsVideo');
			// }

            $supplier = model('suppliers')->get(['supplierId'=>$supplierId]);
        	if($supplier['dataFlag'] ==-1 || $supplier['supplierStatus'] != 1)$data['isSale'] = 0;

			$validate = new Validate;
			if (!$validate->scene(true)->check($data)) {
				return WSTReturn($validate->getError());
			}else{
				$result = $this->allowField(true)->save($data,['goodsId'=>$goodsId]);
			}

			if(false !== $result){
				//更新主要文字
				Db::name('supplier_goods_langs')->where(['goodsId'=>$goodsId])->delete();
				$resourceDomain = ($isApp==1)?WSTConf('CONF.resourceDomain'):WSTConf('CONF.resourcePath');
				$goodsLangs = [];
				foreach (WSTSysLangs() as $key => $v) {
				   $dataLang = [];
				   $dataLang['langId'] = $v['id'];
				   $dataLang['goodsId'] = $goodsId;
			       $dataLang['goodsName'] = input('langParams'.$v['id'].'goodsName');
			       $dataLang['goodsSeoKeywords'] = input('langParams'.$v['id'].'goodsSeoKeywords');
			       $dataLang['goodsSeoDesc'] = input('langParams'.$v['id'].'goodsSeoDesc');
                   $dataLang['goodsTips'] = input('langParams'.$v['id'].'goodsTips');
                   $dataLang['goodsDesc'] = input('langParams'.$v['id'].'goodsDesc');
                   //对图片域名进行处理

				   $dataLang['goodsDesc'] = htmlspecialchars_decode($dataLang['goodsDesc']);
		           $dataLang['goodsDesc'] = WSTRichEditorFilter($dataLang['goodsDesc']);
		           $dataLang['goodsDesc'] = str_replace($resourceDomain.'/upload/','${DOMAIN}/upload/',$dataLang['goodsDesc']);
                   $goodsLangs[] = $dataLang;
                }
                Db::name('supplier_goods_langs')->insertAll($goodsLangs);
				/**
				 * 编辑的时候如果不想影响商品销售规格的销量，那么就要在保存的时候区别对待已经存在的规格和销售规格记录。
				 * $specNameMap的保存关系是：array('页面上生成的规格值ID'=>数据库里规则值的ID)
				 * $specIdMap的保存关系是:array('页面上生成的销售规格ID'=>数据库里销售规格ID)
				 */
				$specNameMapTmp = explode(',',input('post.specmap'));
				$specIdMapTmp = explode(',',input('post.specidsmap'));
				$specNameMap = [];//规格值对应关系
				$specIdMap = [];//规格和表对应关系
				foreach ($specNameMapTmp as $key =>$v){
					if($v=='')continue;
					$v = explode(':',$v);
					$specNameMap[$v[1]] = $v[0];   //array('页面上的规则值ID'=>数据库里规则值的ID)
				}
				foreach ($specIdMapTmp as $key =>$v){
					if($v=='')continue;
					$v = explode(':',$v);
					$specIdMap[$v[1]] = $v[0];     //array('页面上的销售规则ID'=>数据库里销售规格ID)
				}
				//如果是实物商品并且有销售规格则保存销售和规格值
    	        if($data['goodsType'] ==0 && $specsIds!=''){
    	        	//把之前之前的销售规格
	    	        $specsIds = explode(',',$specsIds);
			    	$specsArray = [];
			    	foreach ($specsIds as $v){
			    		$vs = explode('-',$v);
			    		foreach ($vs as $vv){
			    		   if(!in_array($vv,$specsArray))$specsArray[] = $vv;//过滤出不重复的规格值
			    		}
			    	}
			    	//先标记作废之前的规格值
			    	Db::name('supplier_spec_items')->where(['supplierId'=>$supplierId,'goodsId'=>$goodsId])->update(['dataFlag'=>-1]);
			    	//删除规格多语言
			    	Db::name('supplier_spec_items_langs')->where(['goodsId'=>$goodsId])->delete();
		    		//保存规格名称
		    		$specMap = [];
		    		foreach ($specsArray as $v){
		    			$vv = explode('_',$v);
		    			$specNumId = $vv[0]."_".$vv[1];
		    			$sitem = [];
		    			$sitem['itemImg'] = input('post.specImg_'.$specNumId,'');
		    			$itemId = 0;
		    			//如果已经存在的规格值则修改，否则新增
		    			if(isset($specNameMap[$specNumId]) && (int)$specNameMap[$specNumId]!=0){
		    				$sitem['dataFlag'] = 1;
		    				// WSTUseResource(0, (int)$specNameMap[$specNumId], $sitem['itemImg'],'supplier_spec_items','itemImg');
		    				$itemId = (int)$specNameMap[$specNumId];
		    				Db::name('supplier_spec_items')->where(['supplierId'=>$supplierId,'itemId'=>$itemId])->update($sitem);
		    				$specMap[$v] = (int)$specNameMap[$specNumId];
		    			}else{
		    				$sitem['goodsId'] = $goodsId;
		    				$sitem['supplierId'] = $supplierId;
		    			    $sitem['catId'] = (int)$vv[0];
		    				$sitem['dataFlag'] = 1;
		    			    $sitem['createTime'] = date('Y-m-d H:i:s');
		    			    $itemId = Db::name('supplier_spec_items')->insertGetId($sitem);
		    			    // if($sitem['itemImg']!='')WSTUseResource(0, $itemId, $sitem['itemImg']);
		    			    $specMap[$v] = $itemId;
		    			}
		    			foreach (WSTSysLangs() as $lkey => $lv) {
			    			$sitemLang = [];
			    			$sitemLang['itemId'] = $itemId;
			    			$sitemLang['langId'] = $lv['id'];
			    			$sitemLang['goodsId'] = $goodsId;
				    		$sitemLang['itemName'] = input('post.specName_'.$specNumId.'_'.$lv['id']);
				    		$sitemLangs[] = $sitemLang;
				    	}
		    		}
		    		//新增规格多语言
		    		if(count($sitemLangs)>0)Db::name('supplier_spec_items_langs')->insertAll($sitemLangs);
		    		//删除已经作废的规格值
		    		Db::name('supplier_spec_items')->where(['supplierId'=>$supplierId,'goodsId'=>$goodsId,'dataFlag'=>-1])->delete();
		    		//保存销售规格
		    		$defaultPrice = 0;//默认价格
                    $costPrice = 0;
		    		$totalStock = 0;//总库存
		    		$gspecArray = [];
		    		//把之前的销售规格值标记删除
		    		Db::name('supplier_goods_specs')->where(['goodsId'=>$goodsId,'supplierId'=>$supplierId])->update(['dataFlag'=>-1,'isDefault'=>0]);
		    		$isFindDefaultSpec = false;
		    		$defaultSpec = Input('post.defaultSpec');
		    		foreach ($specsIds as $v){
		    			$vs = explode('-',$v);
		    			$goodsSpecIds = [];
		    			foreach ($vs as $gvs){
		    				$goodsSpecIds[] = $specMap[$gvs];
		    			}
		    			$gspec = [];
		    			$gspec['specIds'] = implode(':',$goodsSpecIds);
		    			$gspec['productNo'] = Input('productNo_'.$v);
			    		$gspec['marketPrice'] = (float)Input('marketPrice_'.$v);
			    		$gspec['specPrice'] = (float)Input('specPrice_'.$v);
                        $gspec['costPrice'] = (float)Input('costPrice_'.$v);
			    		$gspec['specStock'] = (int)Input('specStock_'.$v);
			    		$gspec['warnStock'] = (int)Input('warnStock_'.$v);
			    		$gspec['specWeight'] = (float)Input('specWeight_'.$v);
		    			$gspec['specVolume'] = (float)Input('specVolume_'.$v);
			    		//设置默认规格
			    		if($defaultSpec==$v){
			    			$gspec['isDefault'] = 1;
			    			$isFindDefaultSpec = true;
		    				$defaultPrice = $gspec['specPrice'];
                            $costPrice = $gspec['costPrice'];
			    		}else{
			    			$gspec['isDefault'] = 0;
			    		}
			    		//如果是已经存在的值就修改内容，否则新增
		    			if(isset($specIdMap[$v]) && $specIdMap[$v]!=''){
		    				$gspec['dataFlag'] = 1;
		    				Db::name('supplier_goods_specs')->where(['supplierId'=>$supplierId,'id'=>(int)$specIdMap[$v]])->update($gspec);
		    			}else{
			    			$gspec['supplierId'] = $supplierId;
			    			$gspec['goodsId'] = $goodsId;
			    			$gspecArray[] = $gspec;
		    			}
                        //获取总库存
                        $totalStock = $totalStock + $gspec['specStock'];
		    		}
		    		if(!$isFindDefaultSpec)return WSTReturn(lang("require_default_spec"));
		    		//删除作废的销售规格值
		    		Db::name('supplier_goods_specs')->where(['goodsId'=>$goodsId,'supplierId'=>$supplierId,'dataFlag'=>-1])->delete();
		    		if(count($gspecArray)>0){
		    		    Db::name('supplier_goods_specs')->insertAll($gspecArray);
		    		}
		    		//更新推荐规格和总库存
    	            $this->where('goodsId',$goodsId)->update(['isSpec'=>1,'supplierPrice'=>$defaultPrice,'costPrice'=>$costPrice,'goodsStock'=>$totalStock]);
    	        }else if($specsIds==''){
    	        	Db::name('supplier_spec_items')->where(['goodsId'=>$goodsId,'supplierId'=>$supplierId])->delete();
    	        	Db::name('supplier_spec_items_langs')->where(['goodsId'=>$goodsId])->delete();
    	        	Db::name('supplier_goods_specs')->where(['goodsId'=>$goodsId,'supplierId'=>$supplierId])->delete();
    	        }
    	        //保存商品属性
    	        //删除之前的商品属性
    	        Db::name('supplier_goods_attributes')->where(['goodsId'=>$goodsId,'supplierId'=>$supplierId])->delete();
    	        //删除商品属性多语言
    	        Db::name('supplier_goods_attributes_langs')->where(['goodsId'=>$goodsId])->delete();
    	        //新增商品属性
		    	$attrsArray = [];
		    	$attrRs = Db::name('attributes')->where([['goodsCatId','in',$goodsCats],['isShow','=',1],['dataFlag','=',1]])
		    		            ->field('attrId,attrType')->select();
		        $attrRsVals =  Db::name('attributes')->alias('a')->join('__ATTRIBUTES_LANGS__ al','al.attrId=a.attrId ')->where([['attrType','<>',0],['goodsCatId','in',$goodsCats],['isShow','=',1],['dataFlag','=',1],['shopId','=',0]])->field('al.attrId,al.langId,al.attrVal')->select();
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
			    		$attrs['supplierId'] = $supplierId;
			    		$attrs['goodsId'] = $goodsId;
			    		$attrs['attrId'] = $v['attrId'];
			    		$attrs['createTime'] = date('Y-m-d H:i:s');
			    		$goodsAttrId = Db::name('supplier_goods_attributes')->insertGetId($attrs);
			    		//文本输入框直接取获取的内容
			    		if($v['attrType']==0){
				    		foreach (WSTSysLangs() as $lkey => $lv) {
				    			$attrLang = [];
				    			$attrLang['goodsAttrId'] = $goodsAttrId;
				    			$attrLang['langId'] = $lv['id'];
				    			$attrLang['goodsId'] = $goodsId;
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
                                            if(!in_array($akey,$checkAttrValNo))$checkAttrValNo[] = $akey;
				    					}
				    				}
				    			}
				    		}

				    		if(count($checkAttrValNo)>0){
                                foreach (WSTSysLangs() as $lkey => $lv) {
                                	$attrVal = [];
                                	$attrLang = [];
					    			$attrLang['goodsAttrId'] = $goodsAttrId;
					    			$attrLang['langId'] = $lv['id'];
					    			$attrLang['goodsId'] = $goodsId;
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
		    	if(count($attrLangs)>0)Db::name('supplier_goods_attributes_langs')->insertAll($attrLangs);
		    	//保存关键字
        	    $searchKeys = WSTSupplierGoodsSearchKey($goodsId);
        	    $this->where('goodsId',$goodsId)->update(['goodsSerachKeywords'=>implode(',',$searchKeys)]);
		    	//删除购物车里的商品
		    	model('SupplierCarts')->delCartByUpdate($goodsId);

		    	$isWholesale = (int)input('isWholesale');
				$wholesaleNum = (int)input('wholesaleNum');
				$realWholesaleNum = 0;
				$checkBuyNum = [];
				if($isWholesale==1 && $wholesaleNum<=0)exit(json_encode(WSTReturn(lang("pifa_incomplete"),-1)));
				Db::name('supplier_wholesale_goods')->where('goodsId',$goodsId)->delete();
				if($isWholesale==1){
					//获取商品价格
					$goods = Db::name('supplier_goods')->alias('g')->join('supplier_goods_specs gsp','g.goodsId=gsp.goodsId and g.isSpec=1','left')
					                          ->where('g.goodsId',$goodsId)
					                          ->field('g.supplierPrice,g.isSpec,gsp.specPrice')
					                          ->select();
					$wholesale = [];
					for($i=0;$i<$wholesaleNum;$i++) {
						$buyNumVal = input('buyNum_'.$i);
						$buyNum = (int)input('buyNum_'.$i);
						$rebate = (float)input('rebate_'.$i);
						if($buyNumVal=='')continue;
						if($buyNum<=0)exit(json_encode(WSTReturn(lang("goods_wholesale_tips3"),-1)));
						if(in_array($buyNum,$checkBuyNum))exit(json_encode(WSTReturn(lang("goods_wholesale_tips4"),-1)));
						$checkBuyNum[] = $buyNum;
						if($rebate<=0)exit(json_encode(WSTReturn(lang("goods_wholesale_tips6"),-1)));
						$this->checkRebate($rebate,$goods);
						$wholesaledata = [];
						$wholesaledata['goodsId'] = $goodsId;
						$wholesaledata['buyNum'] = $buyNum;
						$wholesaledata['rebate'] = $rebate;
						$wholesale[] = $wholesaledata;
					}
					Db::name('supplier_wholesale_goods')->insertAll($wholesale);
				}
				Db::commit();
				return WSTReturn(lang("op_ok"), 1,['id'=>$goodsId]);
			}else{
				return WSTReturn($this->getError(),-1);
			}
	    }catch (\Exception $e) {
	    	print_r($e);
        	Db::rollback();
            return WSTReturn(lang("op_err"));
        }
        return WSTReturn(lang("op_err"));
	}

	/**
	 * 检测批发价格是不是比原价还低
	 */
	public function checkRebate($price,$goods){
        foreach ($goods as $key => $v) {
        	if($v['supplierPrice']<=$price)exit(json_encode(WSTReturn(lang("valid_preferential_price_1"),-1)));
        	if($v['isSpec']==1 && $v['specPrice']<=$price)exit(json_encode(WSTReturn(lang("valid_preferential_price_2"),-1)));
        }
	}

    /**
     * 检测商品主表的货号或者商品编号
     */
    public function checkExistGoodsKey($key,$val,$id = 0){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        if(!in_array($key,array('goodsSn','productNo')))return WSTReturn(lang('illegal_query_field'));
        $conditon[] = [$key,'=',$val];
        if($id>0)$conditon[] = ['goodsId','<>',$id];
        $conditon[] = ['supplierId','in',$supplierId];
        $conditon[] = ['dataFlag','=',1];
        $rs = $dbo = $this->where($conditon)->count();
        return ($rs==0)?false:true;
    }
}
