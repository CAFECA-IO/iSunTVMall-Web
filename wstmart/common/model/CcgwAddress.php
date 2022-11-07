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
 * 支付参数日志类
 */
class CcgwAddress extends Base{
	
	/**
	 * 添加支付地址记录
	 */
	public function addAddress($obj){
		$obj['createTime'] = date('Y-m-d H:i:s');
		$this->insert($obj);
	}
	
	/**
	 * 获取支付地址记录
	 */
	public function getAddress($obj){
		$rs = $this->where($obj)->order("id desc")->find();
		return $rs;
	}
	
	/**
	 * 获取支付交易记录
	 */
	public function getCcgwTrans($obj){
		$rs = Db::name("ccgw_address_trans")->where($obj)->find();
		return $rs;
	}

	public function checkCcgwTrans($obj){
		$where = [];
		$where['ccTxid'] = $obj['ccTxid'];
		$where['ccAddress'] = $obj['ccAddress'];
		$rs = Db::name("ccgw_address_trans")->where($where)->find();
		if(empty($rs)){
			$data = [];
			$data['addressId'] = $obj['addressId'];
			$data['ccTxid'] = $obj['ccTxid'];
			$data['ccAddress'] = $obj['ccAddress'];
			$data['ccType'] = $obj['ccType'];
			$data['ccAmount'] = $obj['ccAmount'];
			$data['dataStatus'] = 0;
			$data['createTime'] = date("Y-m-d H:i:s");
			Db::name("ccgw_address_trans")->insert($data);
			return true;
		}else{
			if($rs['dataStatus']==0){
				return true;
			}
			return false;
		}
	}
	
	
}
