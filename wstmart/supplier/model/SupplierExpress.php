<?php
namespace wstmart\supplier\model;
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
 * 门店管理员类
 */
class SupplierExpress extends Base{
	/**
	 * 快递列表
	 */
	public function pageQuery(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$ename = input("expressName");
		$where = [];
		$where[] = ["e.dataFlag","=",1];
		$where[] = ["e.isShow","=",1];
		if($ename!="")$where[] = ["e.expressName","like",'%'.$ename.'%'];
		$page = Db::name("express e")
		        ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
				->join("supplier_express se","e.expressId=se.expressId and se.dataFlag=1 and se.supplierId=$supplierId","left")
				->field("e.expressId,el.expressName,e.expressCode,se.id,se.isEnable,se.isDefault")
				->where($where)
				->paginate(input('limit/d'))->toArray();
		return $page;
	}

	public function toggleSet(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$expressId = (int)input("expressId");
		$spExpress = Db::name("supplier_express")->where(["supplierId"=>$supplierId,"expressId"=>$expressId,"dataFlag"=>1])->find();
		if(empty($spExpress) || $spExpress['isEnable']==0){
			return WSTReturn(lang("op_invalid"), -1);
		}else{
			Db::startTrans();
			try{
				Db::name("supplier_express")->where(["supplierId"=>$supplierId,"dataFlag"=>1])->update(['isDefault'=>0]	);
				Db::name("supplier_express")->where(["supplierId"=>$supplierId,"expressId"=>$expressId,"dataFlag"=>1])->update(['isDefault'=>1]);
				Db::commit();
				return WSTReturn(lang("op_ok"), 1);
			}catch (\Exception $e) {
	        	Db::rollback();
	        }
		}
	}

	/**
	 * 启用快递
	 */
	public function enableExpress(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$expressId = (int)input("expressId");
		$spExpress = Db::name("supplier_express")->where(["supplierId"=>$supplierId,"expressId"=>$expressId,"dataFlag"=>1])->find();
		Db::startTrans();
		try{
			if(empty($spExpress)){
				$cnt = Db::name("supplier_express")->where(["supplierId"=>$supplierId,"dataFlag"=>1])->count();
				$isDefault = ($cnt==0)?1:0;
				$data = [];
				$data["expressId"] = $expressId;
				$data["isEnable"] = 1;
				$data["isDefault"] = $isDefault;
				$data["dataFlag"] = 1;
				$data["supplierId"] = $supplierId;
				$id = Db::name("supplier_express")->insertGetId($data);

				$data = [];
				$data["supplierExpressId"] = $id;
				$data["tempName"] = lang("express_default_template_(national)");
				$data["tempType"] = 0;
				$data["provinceIds"] = '';
				$data["cityIds"] = '';
				$data["weightStart"] = 0;
				$data["weightStartPrice"] = 0;
				$data["weightContinue"] = 0;
				$data["weightContinuePrice"] = 0;
				$data["supplierId"] = $supplierId;
				$data["createTime"] = date("Y-m-d H:i:s");
				Db::name("supplier_freight_template")->insert($data);
			}else{
				$id = $spExpress["id"];
				Db::name("supplier_express")->where(["id"=>$id])->update(["isEnable"=>1]);
			}
			Db::commit();
		}catch (\Exception $e) {
        	Db::rollback();
        }
		return WSTReturn("",1);
	}

	/**
	 * 停用快递
	 */
	public function disableExpress(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$id = (int)input("id");
		Db::name("supplier_express")->where(["id"=>$id,"supplierId"=>$supplierId])->update(["isEnable"=>0]);
		return WSTReturn("",1);
	}
	/**
	 * 快递列表
	 */
	public function listQuery2(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$tname = input("tempName");
		$supplierExpressId = (int)input("supplierExpressId");
		$where = [];
		$where[] = ["dataFlag","=",1];
		$where[] = ["supplierExpressId","=",$supplierExpressId];
		if($tname!="")$where[] = ["tempName","like",'%'.$tname.'%'];
		$list = Db::name("supplier_freight_template")
				->where($where)
				->paginate(input('limit/d'))->toArray();
		return $list;
	}

	public function getById(){
		$id = (int)input("id");
		$rs = $this->where(["id"=>$id,'isShow'=>1])->find();
		return $rs;
	}

	public function getFreightById(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$id = (int)input("id");
		$rs = Db::name("supplier_freight_template")->where(["id"=>$id,"supplierId"=>$supplierId])->find();
		return $rs;
	}

	public function getSupplierExpressInfo($supplierExpressId){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$rs = Db::name("supplier_express se")
			->join("express e","e.expressId=se.expressId and e.isShow=1 and e.dataFlag=1","inner")
			->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
			->field("e.expressId,el.expressName")
			->where(["se.id"=>$supplierExpressId,"supplierId"=>$supplierId])
			->find();
		return $rs;
	}

	public function getOtherAreas($id,$supplierExpressId){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$where = [];
		if($id>0)$where[] = ["id","<>",$id];
		$where[] = ["dataFlag","=",1];
		$where[] = ["tempType","=",1];
		$where[] = ["supplierId","=",$supplierId];
		$where[] = ["supplierExpressId","=",$supplierExpressId];
		$list = Db::name("supplier_freight_template")->where($where)->field("id,provinceIds,cityIds")->select();

		$otherProvinceIds = [];
		$otherCityIds = [];
		foreach ($list as $key => $vo) {
			$otherProvinceIds = array_merge(explode(",", $vo['provinceIds']),$otherProvinceIds);
			$otherCityIds = array_merge(explode(",", $vo['cityIds']),$otherCityIds);
		}
		$data = [];
		$data['otherProvinceIds'] = array_unique($otherProvinceIds);
		$data['otherCityIds'] = array_unique($otherCityIds);
		return $data;
	}
	
	/**
	 * 新增运费模板
	 */
	public function add(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$data = input('post.');
		if($data["tempName"]=="")return WSTReturn(lang("please_enter_a_template_name"),-1);
		if($data["provinceIds"]=="")return WSTReturn(lang("please_select_a_region"),-1);
		$supplierExpressId = (int)$data['supplierExpressId'];
		$otherAreas = $this->getOtherAreas(0,$supplierExpressId);
		$otherCityIds = $otherAreas["otherCityIds"];
		$cityIds = explode(",",$data['cityIds']);

		foreach ($cityIds as $key => $cityId) {
			if(in_array($cityId,$otherCityIds)){
				return WSTReturn(lang("the_selected_region_already_exists_in_another_freight_template"),-1);
			}
		}
		$temp = [];
		$temp["supplierExpressId"] = $supplierExpressId;
		$temp["supplierId"] = $supplierId;
		$temp["tempName"] = $data['tempName'];
		$temp["provinceIds"] = $data['provinceIds'];
		$temp["cityIds"] = $data['cityIds'];


		$temp["buyNumStart"] = (float)$data['buyNumStart'];
		$temp["buyNumStartPrice"] = (float)$data['buyNumStartPrice'];
		$temp["buyNumContinue"] = (float)$data['buyNumContinue'];
		$temp["buyNumContinuePrice"] = (float)$data['buyNumContinuePrice'];

		$temp["weightStart"] = (float)$data['weightStart'];
		$temp["weightStartPrice"] = (float)$data['weightStartPrice'];
		$temp["weightContinue"] = (float)$data['weightContinue'];
		$temp["weightContinuePrice"] = (float)$data['weightContinuePrice'];

		$temp["volumeStart"] = (float)$data['volumeStart'];
		$temp["volumeStartPrice"] = (float)$data['volumeStartPrice'];
		$temp["volumeContinue"] = (float)$data['volumeContinue'];
		$temp["volumeContinuePrice"] = (float)$data['volumeContinuePrice'];

		$temp["dataFlag"] = 1;
		$temp["tempType"] = 1;
		$temp["createTime"] = date("Y-m-d H:i:s");
		$result = Db::name("supplier_freight_template")->insert($temp);
		if(false !== $result){
        	return WSTReturn(lang("op_ok"), 1);
        }
        return WSTReturn(lang("op_err"),-1);
	}

	/**
     * 修改运费模板
     */
    public function edit(){

    	$supplierId = (int)session('WST_SUPPLIER.supplierId');
    	Db::startTrans();
		try{
	    	$supplierId = (int)session('WST_SUPPLIER.supplierId');
			$data = input('post.');
			$id = (int)$data["id"];
			$ftemp = Db::name("supplier_freight_template")->where(["supplierId"=>$supplierId,"id"=>$id])->find();
			if($data["tempName"]=="")return WSTReturn(lang("please_enter_a_template_name"),-1);
			if($ftemp["tempType"]==1 && $data["provinceIds"]=="")return WSTReturn(lang("please_select_Province"),-1);
			
			$temp = [];
			$temp["tempName"] = $data['tempName'];
			if($ftemp["tempType"]==1){
				$temp["provinceIds"] = $data['provinceIds'];
				$temp["cityIds"] = $data['cityIds'];
				$otherAreas = $this->getOtherAreas($id,$ftemp['supplierExpressId']);
				$otherCityIds = $otherAreas["otherCityIds"];
				$cityIds = explode(",",$data['cityIds']);

				foreach ($cityIds as $key => $cityId) {
					if(in_array($cityId,$otherCityIds)){
						return WSTReturn(lang("the_selected_region_already_exists_in_another_freight_template"),-1);
					}
				}
			}
			$temp["buyNumStart"] = (float)$data['buyNumStart'];
			$temp["buyNumStartPrice"] = (float)$data['buyNumStartPrice'];
			$temp["buyNumContinue"] = (float)$data['buyNumContinue'];
			$temp["buyNumContinuePrice"] = (float)$data['buyNumContinuePrice'];

			$temp["weightStart"] = (float)$data['weightStart'];
			$temp["weightStartPrice"] = (float)$data['weightStartPrice'];
			$temp["weightContinue"] = (float)$data['weightContinue'];
			$temp["weightContinuePrice"] = (float)$data['weightContinuePrice'];

			$temp["volumeStart"] = (float)$data['volumeStart'];
			$temp["volumeStartPrice"] = (float)$data['volumeStartPrice'];
			$temp["volumeContinue"] = (float)$data['volumeContinue'];
			$temp["volumeContinuePrice"] = (float)$data['volumeContinuePrice'];
			Db::name("supplier_freight_template")
						->where(["supplierId"=>$supplierId,"id"=>$id])
						->update($temp);
			Db::commit();
			return WSTReturn(lang("op_ok"), 1);
			
		}catch (\Exception $e) {
        	Db::rollback();
        	return WSTReturn(lang("op_err"), -1);
        }
    }

	/**
	 * 删除运费模板
	 */
	public function del(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$id = (int)input('post.id');
		$data = [];
		$data['dataFlag'] = -1;
		Db::startTrans();
		try{
			$where = [];
			$where[] = ["supplierId",'=',$supplierId];
			$where[] = ["id",'=',$id];
			$where[] = ["tempType",'=',1];
	   		Db::name("supplier_freight_template")
	   		->where(["supplierId"=>$supplierId,"id"=>$id])
	   		->update(['dataFlag'=>-1]);
	   		Db::commit();
        	return WSTReturn(lang("op_ok"), 1);
     	}catch (\Exception $e) {
        	Db::rollback();
        }
        return WSTReturn(lang("op_err"),-1);
	}

	public function supplierExpressList(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$where = [];
		$where[] = ["supplierId","=",$supplierId];
		$where[] = ["isEnable","=",1];
		$where[] = ["se.dataFlag","=",1];
		$where[] = ["e.dataFlag","=",1];
		$rs = Db::name("supplier_express se")
				->join("express e","se.expressId=e.expressId and e.isShow=1 and e.dataFlag=1","inner")
				->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
				->field("se.id,el.expressName")
				->where($where)
				->select();
		return $rs;
	}
}
