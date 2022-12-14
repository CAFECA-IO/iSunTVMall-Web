<?php
namespace wstmart\supplier\model;
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
 * 某些较杂业务处理类
 */
use think\Db;
class Systems extends Base{
	/**
	 * 获取定时任务
	 */
	public function getSysMessages(){
		$tasks = strtolower(input('post.tasks'));
		$tasks = explode(',',$tasks);
		$userId = (int)session('WST_SUPPLIER.userId');
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$data = [];
		if(in_array('message',$tasks)){
		    //获取用户未读消息
		    $data['message']['num'] = Db::name('messages')->where(['receiveUserId'=>$userId,'msgStatus'=>0,'dataFlag'=>1])->count();
		    $data['message']['id'] = 500;
		    $data['message']['sid'] = 506;
		}
		if($supplierId>0){
			//获取商家待处理订单
			if(in_array('supplierorder515',$tasks)){
			    $data['supplierorder']['515'] = Db::name('supplier_orders')->where(['supplierId'=>$supplierId,'orderStatus'=>0,'dataFlag'=>1])->count();
			}
			if(in_array('supplierorder542',$tasks)){
			    $data['supplierorder']['542'] = Db::name('supplier_order_complains')->where(['respondTargetId'=>$supplierId,'complainStatus'=>1])->count();
			}
			if(in_array('supplierorder514',$tasks)){
			    $data['supplierorder']['514'] = Db::name('supplier_orders')->where(['supplierId'=>$supplierId,'orderStatus'=>-2,'dataFlag'=>1])->count();
			}
			if(in_array('supplierorder541',$tasks)){
			    //在线支付的退款单
			    $data['supplierorder']['541'] = Db::name('supplier_orders')->alias('o')->join('supplier_order_refunds orf','orf.orderId=o.orderId')->where(['supplierId'=>$supplierId,'refundStatus'=>0,'o.dataFlag'=>1])->count();
			}
			if(in_array('supplierorder524',$tasks)){
			    //获取库存预警数量
			    $goodsn = Db::name('supplier_goods')->where('supplierId ='.$supplierId.' and dataFlag = 1 and goodsStock <= warnStock and isSpec = 0 and warnStock>0')->count();
			    $specsn = Db::name('supplier_goods_specs')->where('supplierId ='.$supplierId.' and dataFlag = 1 and specStock <= warnStock and warnStock>0')->count();
			    $data['supplierorder']['524'] = $goodsn+$specsn;
			}
		}
	
		return $data;
	}
}
