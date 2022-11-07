<?php
namespace wstmart\shop\model;
use wstmart\shop\validate\SpecCats as validate;
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
class SpecCats extends Base{
	
	/**
	 * 新增
	 */
	public function add(){
		$shopId = (int)session('WST_USER.shopId');
		$isExistAllowImg = false;
		$msg = '';
		$data = input('post.');
		if($data['isAllowImg']==1){
			if($this->checkExistAllowImg((int)$data['goodsCatId'],0)){
				return WSTReturn(lang('there_is_already_a_specification_type_for_uploading_pictures_under_the_same_category'));
			}
		}
		$data['createTime'] = date('Y-m-d H:i:s');
		$data["dataFlag"] = 1;
		$data["shopId"] = $shopId;
		$goodsCats = model('GoodsCats')->getParentIs($data['goodsCatId']);
		if(!empty($goodsCats))$data['goodsCatPath'] = implode('_',$goodsCats)."_";
		$validate = new validate();
		if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
		$result = $this->allowField(['catName','isShow','isAllowImg','goodsCatPath','goodsCatId','dataFlag','createTime','shopId'])->save($data);
        if(false !== $result){
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	/**
	 * 检测是否存在统一分类下的上传图片分类
	 */
	public function checkExistAllowImg($goodsCatId,$catId){
		$dbo = $this->where(['goodsCatId'=>$goodsCatId,'dataFlag'=>1,'isAllowImg'=>1]);
		if($catId>0)$dbo->where('catId','<>',$catId);
		$rs = $dbo->count();
		if($rs>0)return true;
		return false;
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$shopId = (int)session('WST_USER.shopId');
		$catId = input('post.catId/d');
		$data = input('post.');
	    if($data['isAllowImg']==1){
			if($this->checkExistAllowImg((int)$data['goodsCatId'],$catId)){
				return WSTReturn(lang('there_is_already_a_specification_type_for_uploading_pictures_under_the_same_category'));
			}
		}
		$goodsCats = model('GoodsCats')->getParentIs($data['goodsCatId']);
		if(!empty($goodsCats))$data['goodsCatPath'] = implode('_',$goodsCats)."_";
		$validate = new validate();
		if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
	    $result = $this->allowField(['catName','goodsCatPath','goodsCatId','isShow','isAllowImg'])
	    ->save($data,['catId'=>$catId,"dataFlag"=>1,'shopId'=>$shopId]);
        if(false !== $result){
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	/**
	 * 删除
	 */
    public function del(){
    	$shopId = (int)session('WST_USER.shopId');
	    $catId = input('post.catId/d');
	    $data["dataFlag"] = -1;
	  	$result = $this->save($data,['catId'=>$catId,'shopId'=>$shopId]);
        if(false !== $result){
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	
	/**
	 * 显示隐藏
	 */
	public function setToggle(){
		$shopId = (int)session('WST_USER.shopId');
		$catId = input('post.catId/d');
		$isShow = input('post.isShow/d');
		$result = $this->where(['shopId'=>$shopId,'catId'=>$catId,"dataFlag"=>1])->setField("isShow", $isShow);
		if(false !== $result){
			return WSTReturn(lang('op_ok'), 1);
		}else{
			return WSTReturn($this->getError(),-1);
		}
	}
	
	/**
	 * 
	 * 根据ID获取
	 */
	public function getById($catId){
		$shopId = (int)session('WST_USER.shopId');
		$obj = null;
		if($catId>0){
			$obj = $this->get(['shopId'=>$shopId,'catId'=>$catId,"dataFlag"=>1]);
		}else{
			$obj = self::getEModel("spec_cats");
		}
		return $obj;
	}
	
	
	/**
	 * 分页
	 */
	public function pageQuery(){
		$shopId = (int)session('WST_USER.shopId');
		$specSrc = (int)input('specSrc');
		$keyName = input('keyName');
		$goodsCatPath = input('goodsCatPath');
		$dbo = $this->field(true);
		$map = array();
		$map[] = ['dataFlag','=',1];
		if($specSrc==1){
			$map[] = ['shopId','=',0];
		}else if($specSrc==2){
			$map[] = ['shopId','=',$shopId];
		}else{
			$map[] = ['shopId','in',[0,$shopId]];
		}
		if($keyName!="")$map[] = ['catName',"like","%".$keyName."%"];
		if($goodsCatPath!='')$map[] = ['goodsCatPath',"like",$goodsCatPath."_%"];
		$dbo = $dbo->field("catName name, catId id, isShow ,isAllowImg")->where($map);
		$page = $dbo->order('catSort asc,catId asc')->paginate(input('limit/d'))->toArray();
		if(count($page['data'])>0){
			$keyCats = model('GoodsCats')->listKeyAll();
			foreach ($page['data'] as $key => $v){
				$goodsCatPath = $page['data'][$key]['goodsCatPath'];
				$page['data'][$key]['goodsCatNames'] = self::getGoodsCatNames($goodsCatPath,$keyCats);
				$page['data'][$key]['children'] = [];
				$page['data'][$key]['isextend'] = false;
			}
		}
		return $page;
	}
	
	public function getGoodsCatNames($goodsCatPath, $keyCats){
		$catIds = explode("_",$goodsCatPath);
		$catNames = array();
		for($i=0,$k=count($catIds);$i<$k;$i++){
			if($catIds[$i]=='')continue;
			if(isset($keyCats[$catIds[$i]]))$catNames[] = $keyCats[$catIds[$i]];
		}
		return implode("→",$catNames);
	}
}
