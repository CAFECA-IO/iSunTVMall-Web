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
 * 举报类
 */
class Informs extends Base{
	protected $pk = 'informId';
	/**
	 * 举报处理状态
	 */
	public function informStatus(){
		$data = [
			["dataVal"=>"0", "dataName"=>lang('informs_status_0')],
			["dataVal"=>"1", "dataName"=>lang('informs_status_1')],
			["dataVal"=>"2", "dataName"=>lang('informs_status_2')],
			["dataVal"=>"3", "dataName"=>lang('informs_status_3')]
		];
		return WSTReturn("ok", 1, $data);
	}

	/**
	 * 举报须知
	 */
	public function tips(){
		$content = "
		<ul>
          <li> ".lang('informs_tips_1')."</li>
          <li> ".lang('informs_tips_2')."</li>
          <li> ".lang('informs_tips_3')."</li>
          <li> ".lang('informs_tips_4')."</li>
        </ul>
		";
		return $content;
	}

	/**
	 * 跳到举报列表
	 */
	public function inform($uId=0){
		$id = input('id');
		$type = input('type');
		$userId = $uId==0?(int)session('WST_USER.userId'):$uId;
		//判断用户是否拥有举报权利
		    $s = Db::name('users')->where("userId=$userId")->find();
		    if($s['isInform']==0)return WSTReturn(lang('you_prohibit_informs'), -1);
		//判断记录是否存在
		$isFind = false;
			$c = Db::name('goods')
                ->alias('g')
                ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                ->field('g.goodsId,goodsImg,gl.goodsName,shopId')->where(['goodsStatus'=>1,'dataFlag'=>1,'g.goodsId'=>$id])->find();
			$isFind = ($c>0);
		if(!$isFind)return WSTReturn(lang('informs_fail_invalid_obj'), -1);
		    $shopId = $c['shopId'];
			$s = Db::name('shops')->where(['shopStatus'=>1,'dataFlag'=>1,'shopId'=>$shopId])->field('shopName,shopId')->find();
			$c = array_merge($c,$s, ['types'=>WSTDatas('INFORMS_TYPE')]);
		return WSTReturn('',1,$c);
	}

	/**
	  * 获取用户举报列表
	  */
	public function queryUserInformByPage($uId=0){
		$userId = $uId==0?(int)session('WST_USER.userId'):$uId;
		$informStatus = (int)Input('informStatus', -1);

		$where['oc.informTargetId'] = $userId;
		if($informStatus>=0){
			$where['oc.informStatus'] = $informStatus;
		}
		$rs = $this->alias('oc')
		           ->join('__SHOPS__ s','oc.shopId=s.shopId','left')
				   ->join('__GOODS__ o','oc.goodId=o.goodsId and o.dataFlag=1','inner')
                    ->join('__GOODS_LANGS__ gl','gl.goodsId=o.goodsId and gl.langId='.WSTCurrLang())
				   ->order('oc.informId asc')
				   ->field('oc.*, s.shopName,o.goodsId,o.goodsImg,gl.goodsName,o.shopId')
				   ->where($where)
				   ->paginate()->toArray();

		foreach($rs['data'] as $k=>$v){
			$rs['data'][$k]['informStatus'] = $this->getText($v['informStatus']);
			$rs['data'][$k]['goodsImg'] = WSTImg($v['goodsImg'], 1);
		}
		if($rs !== false){
			return WSTReturn('',1,$rs);
		}else{
			return WSTReturn($this->getError(),-1);
		}
	}
	// 判断是否已经举报过
	public function alreadyInform($goodsId,$userId){
		return $this->field('informId')->where("goodId=$goodsId and informTargetId=$userId")->find();
	}
	/**
	 * 保存订单举报信息
	 */
	public function saveInform($uId=0){
		$userId = $uId==0?(int)session('WST_USER.userId'):$uId;
		$data = [];
        $data['goodId'] = (int)input('goodsId');
		//判断是否提交过举报
		$rs = $this->alreadyInform($data['goodId'],$userId);
		if((int)$rs['informId']>0){
			return WSTReturn(lang('goods_has_informs'),-1);
		}
		$informType = (int)input('informType');
		if($informType==0 || (WSTDatas("INFORMS_TYPE", $informType)=='')){
			return WSTReturn(lang('illegal_informs_type'));
		}
		$informContent = input('informContent', '');
		if($informContent==""){
			return WSTReturn(lang('require_informs_content'));
		}
		if(strlen($informContent)<3 || strlen($informContent)>200){
			return WSTReturn(lang('informs_content_tips'));
		}
		$shopId = (int)Db::name('goods')->where('goodsId', $data['goodId'])->value('shopId');
		Db::startTrans();
		try{
			$data['informTargetId'] = $userId;
			$data['shopId'] = $shopId;
			$data['informStatus'] = 0;
			$data['informType'] = $informType;
			$data['informTime'] = date('Y-m-d H:i:s');
			$data['informAnnex'] = input('informAnnex');
			$data['informContent'] = $informContent;
			$rs = $this->save($data);
			if($rs !==false){
				Db::commit();
				return WSTReturn(lang('operation_success'),1);
			}else{
				return WSTReturn($this->getError(),-1);
			}
		}catch (\Exception $e) {
		    Db::rollback();
	    }
	    return WSTReturn(lang('operation_fail'),-1);
	}

    /**
	 * 获取举报详情
	 */
	public function getUserInformDetail($userType = 0, $uId=0){
		$userId = $uId==0?(int)session('WST_USER.userId'):$uId;
		$id = (int)Input('id');
		if($userId==0){
			$where['informTargetId']=$userId;
		}

		//获取举报信息
		$where['informId'] = $id;
		$rs = Db::name('informs')->alias('oc')
		           ->field('oc.*,o.goodsId ,gl.goodsName, o.goodsImg , s.shopId , s.shopName')
		           ->join('__SHOPS__ s','oc.shopId=s.shopId','left')
				   ->join('__GOODS__ o','oc.goodId=o.goodsId and o.dataFlag=1','inner')
                   ->join('__GOODS_LANGS__ gl','gl.goodsId=o.goodsId and gl.langId='.WSTCurrLang())
				   ->where($where)->find();
		if(!empty($rs)){
			if($rs['informAnnex']!='')$rs['informAnnex'] = explode(',',$rs['informAnnex']);
		}else{
			return [];
		}
		// 获取举报类型
		$rs['types'] = WSTDatas('INFORMS_TYPE', $rs['informType']);
		// 投诉状态
		$rs['status'] = $this->getText($rs['informStatus']);
        return $rs;
	}
	/**
	 * 投诉处理状态对应文字
	 */
	public function getText($v){
		switch($v){
			case 0:return lang('informs_status_0');
			case 1:return lang('informs_status_1');
			case 2:return lang('informs_status_2');
			case 3:return lang('informs_status_3');
		}

	}
}
