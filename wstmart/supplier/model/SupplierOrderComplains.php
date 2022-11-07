<?php
namespace wstmart\supplier\model;
use wstmart\supplier\validate\SupplierOrderComplains as Validate;
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
 * 订单投诉类
 */
class SupplierOrderComplains extends Base{
	protected $pk = 'complainId';
	/**
	 * 获取投诉详情
	 */
	public function getComplainDetail($userType = 0,$sId = 0){
		
		$supplierId = ($sId==0)?(int)session('WST_SUPPLIER.supplierId'):$sId;
		$id = (int)Input('id');
		$where['needRespond'] = 1;
		$where['respondTargetId'] = $supplierId;
		//获取订单信息
		$where['complainId'] = $id;
		$rs = $this->alias('oc')
				   ->field('oc.*,o.realTotalMoney,o.orderNo,o.orderId,o.createTime,o.deliverMoney,s.supplierName,s.supplierId')
				   ->join('supplier_orders o','oc.orderId=o.orderId','inner')
				   ->join('suppliers s','o.supplierId=s.supplierId')
				   ->where($where)->find();
		if($rs){
			if($rs['complainAnnex']!='')$rs['complainAnnex'] = explode(',',$rs['complainAnnex']);
			if($rs['respondAnnex']!='')$rs['respondAnnex'] = explode(',',$rs['respondAnnex']);

			//获取相关商品
			$goods = $this->getOrderGoods($rs['orderId']);
			$rs["goodsList"] = $goods;
		}
        return $rs;
	}

	//获取相关商品
	public function getOrderGoods($orderId){
	  $rs = db('supplier_goods')->alias('g')
						->field('og.orderId, og.goodsId ,g.goodsSn, og.goodsName , og.goodsPrice supplierPrice,og.goodsImg,og.goodsNum,og.goodsSpecNames,og.goodsCode')
						->join('supplier_order_goods og','g.goodsId = og.goodsId','inner')
						->where("og.orderId=$orderId")
						->select();
        foreach ($rs as $key => $v) {
            $shotGoodsSpecNames = [];
            if ($v['goodsSpecNames'] != "") {
                $v['goodsSpecNames'] = str_replace('：', ':', $v['goodsSpecNames']);
                $goodsSpecNames = explode('@@_@@', $v['goodsSpecNames']);

                foreach ($goodsSpecNames as $key2 => $spec) {
                    $obj = explode(":", $spec);
                    $shotGoodsSpecNames[] = $obj[1];
                }
            }
            $rs[$key]['goodsSpecNames'] = implode('，',$shotGoodsSpecNames);
        }
        return $rs;
	}
	/**
	  * 获取商家被投诉列表
	  */
	public function querySupplierComplainByPage($sId=0){
		$supplierId = ($sId==0)?(int)session('WST_SUPPLIER.supplierId'):$sId;
		$orderNo = (int)Input('orderNo');
		$where = [];
		if($orderNo!=''){
			$where[] = ['o.orderNo','like',"%$orderNo%"];
		}
		$where[] = ['oc.needRespond','=',1];
		$where[] = ['o.dataFlag','=',1];
		$where[] = ['oc.respondTargetId','=',$supplierId];
		$rs = $this->alias('oc')
				   ->field('oc.complainId,o.orderId,o.orderNo,u.userName,u.loginName,oc.complainContent,oc.complainStatus,oc.complainTime,o.orderCode')
				   ->join('__USERS__ u','oc.complainTargetId=u.userId','left')
				   ->join('__SUPPLIER_ORDERS__ o','oc.orderId=o.orderId')
				   ->where($where)
				   ->order('oc.complainId desc')
				   ->paginate(input('post.limit/d'))
				   ->toArray();
		foreach($rs['data'] as $k=>$v){
			if($v['complainStatus']==0){
				$rs['data'][$k]['complainStatus'] = lang("complaint_status1");
			}elseif($v['complainStatus']==1){
				$rs['data'][$k]['complainStatus'] = lang("complaint_status2");
				$rs['data'][$k]['needReply'] = 1;
			}elseif($v['complainStatus']==2 || $v['complainStatus']==3 ){
				$rs['data'][$k]['complainStatus'] = lang("complaint_status3");
			}elseif($v['complainStatus']==4){
				$rs['data'][$k]['complainStatus'] = lang("complaint_status4");
			}
			$rs['data'][$k]['orderCodeTitle'] = WSTOrderModule($v['orderCode']);
		}
		return WSTReturn('ok',1,$rs);
	}
	/**
	 * 保存订单应诉信息
	 */
	public function saveRespond($sId=0){
		$supplierId = ($sId==0)?(int)session('WST_SUPPLIER.supplierId'):$sId;
		$complainId = (int)Input('complainId');
		//判断是否提交过应诉和是否有效的投诉信息
		$complainRs = $this->field('needRespond,complainStatus,orderId,complainType,complainContent')->where("complainId=$complainId AND respondTargetId=$supplierId")->find();
		
        if((int)$complainRs['needRespond']!=1){
			return WSTReturn(lang("invalid_order_complain"),-1);
		}
		if((int)$complainRs['complainStatus']!=1){
			return WSTReturn(lang("the_order_has_been_complained"),-1);
		}
		Db::startTrans();
		try{
			$data['complainStatus'] = 3;
			$data['respondTime'] = date('Y-m-d H:i:s');
			$data['respondAnnex'] = Input('respondAnnex');
			$data['respondContent'] = Input('respondContent');
			$validate = new Validate;
			if (!$validate->scene('respond')->check($data)) {
				return WSTReturn($validate->getError());
			}else{
				$rs = $this->where('complainId='.$complainId)->update($data);
			}
			if($rs !==false){
				WSTUseResource(0, $complainId, $data['respondAnnex']);
				$order = Db::name('supplier_orders')->alias('o')->join('__USERS__ u','u.userId=o.userId')
				           ->where('orderId',$complainRs['orderId'])
				           ->field('o.orderNo,u.loginName')->find();
				//判断是否需要发送管理员短信
				$tpl = WSTMsgTemplates('PHONE_ADMIN_COMPLAINT_ORDER');
				if((int)WSTConf('CONF.smsOpen')==1 && (int)WSTConf('CONF.smsComplaintOrderTip')==1 &&  $tpl['tplContent']!='' && $tpl['status']=='1'){
					$params = ['tpl'=>$tpl,'params'=>['ORDER_NO'=>$order['orderNo']]];
					$staffs = Db::name('staffs')->where([['staffId','in',explode(',',WSTConf('CONF.complaintOrderTipUsers'))],['staffStatus','=',1],['dataFlag','=',1]])->field('staffPhone')->select();
					for($i=0;$i<count($staffs);$i++){
						if($staffs[$i]['staffPhone']=='')continue;
						$m = new LogSms();
						$rv = $m->sendAdminSMS(0,$staffs[$i]['staffPhone'],$params,'saveRespond','');
					}
				}
				//微信消息
				if((int)WSTConf('CONF.wxenabled')==1){
					//判断是否需要发送给管理员消息
		            if((int)WSTConf('CONF.wxComplaintOrderTip')==1){
		            	$remark = WSTDatas('ORDER_COMPLAINT',$complainRs['complainType']);
		                $params = [];
						$params['ORDER_NO'] = $order['orderNo'];
					    $params['REMARK'] = "【".$remark['dataName']."】".WSTMSubstr($complainRs['complainContent'],0,20,'utf-8','...');           
						$params['LOGIN_NAME'] = $order['loginName'];
			            WSTWxBatchMessage(['CODE'=>'WX_ADMIN_ORDER_COMPLAINT','userType'=>3,'userId'=>explode(',',WSTConf('CONF.complaintOrderTipUsers')),'params'=>$params]);
		            }
				}
				Db::commit();
				return WSTReturn(lang("op_ok"),1);
			}
		}catch (\Exception $e) {
		    Db::rollback();
	    }
	    return WSTReturn(lang("op_err"),-1);


	}
	
	
}
