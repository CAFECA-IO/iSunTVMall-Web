<?php
namespace addons\seckill\model;
use think\addons\BaseModel as Base;
use wstmart\common\model\GoodsCats;
use think\Db;
use wstmart\common\model\LogSms;
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
 * 秒杀活动
 */
class Seckills extends Base{

    /**
     * 商家获取秒杀活动列表
     */
	public function pageQueryByShop($shopId){
		$title =input('title');
		$seckillStatus = (int)input('seckillStatus',-3);
		$where = [];
		$where[] = ['shopId','=',$shopId];
		$where[] = ['dataFlag','=',1];
		if($title !='')$where[] = ['sl.title', 'like', '%'.$title.'%'];
		if($seckillStatus > -3)$where[]=['seckillStatus','=',$seckillStatus];
        $page =  $this
                    ->alias('s')
                    ->join('__SECKILLS_LANGS__ sl','sl.seckillId=s.id and sl.langId='.WSTCurrLang())
                    ->field('s.*,sl.title')
                    ->where($where)
					->order('id desc')
                	->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	$today = date("Y-m-d");
        	foreach($page['data'] as $key =>$v){
        		if($v['startDate']<=$today && $v['endDate']>=$today){
        			$page['data'][$key]['status'] = 1;
        		}else if($v['startDate']>$today){
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
     * 搜索商品
     */
    public function searchGoods($sId=0){
    	$shopId =($sId==0)?(int)session('WST_USER.shopId'):$sId;
    	$shopCatId1 = (int)input('post.shopCatId1');
    	$shopCatId2 = (int)input('post.shopCatId2');
    	$goodsName = input('post.goodsName');
    	$where = [];
    	$where[] = ['goodsStatus','=',1];
    	$where[] = ['dataFlag','=',1];
    	$where[] = ['isSale','=',1];
    	$where[] = ['shopId','=',$shopId];
    	if($shopCatId1>0)$where[] = ['shopCatId1','=',$shopCatId1];
    	if($shopCatId2>0)$where[] = ['shopCatId2','=',$shopCatId2];
    	if($goodsName!='')$where[] = ['gl.goodsName','like','%'.$goodsName.'%'];
    	$rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->where($where)->field('gl.goodsName,g.goodsId,marketPrice,shopPrice,goodsType')->select();
        return WSTReturn('',1,$rs);
    }
	/**
     * 获取商品类别
	 */
	public function getGoodsCats(){
		$rs = Db::name('goods_cats')
                ->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
		        ->where(['dataFlag'=>1,'isShow'=>1])
		        ->order('catSort asc')
		        ->field('parentId pid,gc.catId id,gcl.catName text')
		        ->select();
		return WSTReturn('',1,$rs);
	}
	/**
	 *  获取秒杀商品
	 */
	public function getById($id,$shopId){
		$where = [];
		$where['id'] = $id;
		$where['shopId'] = $shopId;
		$where['dataFlag'] = 1;
        $rs = $this->where($where)->find();
        $rs['langParams'] = Db::name('seckills_langs')->where(['seckillId'=>$id])->column('*','langId');
        return $rs;
	}

	/**
	 *  获取秒杀商品
	 */
	public function getById2($id){
		$where = [];
		$where['id'] = $id;
		$where['dataFlag'] = 1;
		return $this->where($where)->find();
	}


	/**
	 * 新增秒杀
	 */
	public function add($shopId){
		$data = input('post.');
		if($data['startDate']=='' || $data['endDate']=='')return WSTReturn(lang('seckill_select_valid_time'));
		if(strtotime($data['startDate']) >= strtotime($data['endDate']))return WSTReturn(lang('seckill_time_limit_tips'));
		//判断是否已经存在同时间的秒杀
		$where = [];
		$where['shopId'] = $shopId;
		$where['dataFlag'] = 1;
		$whereOr = ' ( ("'.date('Y-m-d H:i:s',strtotime($data['startDate'])).'" between startDate and endDate) or ( "'.date('Y-m-d H:i:s',strtotime($data['endDate'])).'" between startDate and endDate) ) ';
		$rn = $this->where($where)->where($whereOr)->count();
		if($rn>0)return WSTReturn(lang('seckill_exist_other_tips'));
		WSTUnset($data,'id,illegalRemarks');
		$data['shopId'] = $shopId;
		$data['dataFlag'] = 1;
		$data['seckillStatus'] = -2;
		$data['createTime'] = date('Y-m-d H:i:s');
		$result = $this->allowField(true)->save($data);
		if(false !== $result){
            $seckillId = $this->id;
            $dataLangs = [];
            foreach (WSTSysLangs() as $key => $v) {
                $dataLang = [];
                $dataLang['seckillId'] = $seckillId;
                $dataLang['langId'] = $v['id'];
                $dataLang['title'] = input('langParams'.$v['id'].'title');
                $dataLang['seckillDes'] = input('langParams'.$v['id'].'seckillDes');
                $dataLangs[] = $dataLang;
            }
            Db::name('seckills_langs')->insertAll($dataLangs);
			return WSTReturn(lang('seckill_operation_success'),1);
		}
		return WSTReturn(lang('seckill_operation_fail'));
	}

	/**
	 * 编辑商品
	 */
	public function edit($shopId){
		$data = input('post.');
		if($data['startDate']=='' || $data['endDate']=='')return WSTReturn(lang('seckill_select_valid_time'));
		if(strtotime($data['startDate']) >= strtotime($data['endDate']))return WSTReturn(lang('seckill_time_limit_tips'));
		//判断是否已经存在同时间的秒杀
		$id = $data['id'];
		$where = [];
		$where[] = ['id','<>',$id];
		$where[] = ['dataFlag','=',1];
		$where[] = ['shopId','=',$shopId];
		$whereOr = ' ( ("'.date('Y-m-d H:i:s',strtotime($data['startDate'])).'" between startDate and endDate) or ( "'.date('Y-m-d H:i:s',strtotime($data['endDate'])).'" between startDate and endDate) ) ';
		$rn = $this->where($where)->where($whereOr)->count();
		if($rn>0)return WSTReturn(lang('seckill_exist_other_tips'));
		WSTUnset($data,'id,shopId,dataFlag,createTime,illegalRemarks');
		$data['seckillStatus'] = -2;
		$result = $this->allowField(true)->update($data,['id'=>$id,'shopId'=>$shopId]);
		if(false !== $result){
            Db::name('seckills_langs')->where(['seckillId'=>$id])->delete();
            $dataLangs = [];
            foreach (WSTSysLangs() as $key => $v) {
                $dataLang = [];
                $dataLang['seckillId'] = $id;
                $dataLang['langId'] = $v['id'];
                $dataLang['title'] = input('langParams'.$v['id'].'title');
                $dataLang['seckillDes'] = input('langParams'.$v['id'].'seckillDes');
                $dataLangs[] = $dataLang;
            }
            Db::name('seckills_langs')->insertAll($dataLangs);
			return WSTReturn(lang('seckill_operation_success'),1);
		}
		return WSTReturn(lang('seckill_operation_fail'));
	}

	/**
	 * 秒杀活动提交审核
	 */
	public function submitAudit($shopId){
		$seckillId = (int)input("id");
		$where = [];
		$where[] = ["shopId","=",$shopId];
		$where[] = ["seckillId","=",$seckillId];
		$where[] = ["dataFlag","=",1];
		$cnt = Db::name("seckill_goods")->where($where)->count();
		if($cnt<1)return WSTReturn(lang('seckill_fail_no_goods_tips'),-1);

		$cnt = Db::name("seckill_goods")->where($where)->where("secPrice<=0 or secNum<=0 or secLimit<=0")->count();
		if($cnt>0)return WSTReturn(lang('seckill_check_set_complete_tips'),-1);
		Db::name("seckills")->where(["shopId"=>$shopId,"id"=>$seckillId])->update(["seckillStatus"=>0]);
		return WSTReturn(lang('seckill_operation_success'),1);
	}

	/**
	 * 秒杀活动上下架
	 */
	public function toggleSet($shopId){
		$id = (int)input("id");
		$isSale = (int)input("isSale")==1?1:0;
		$data = [];
		$data["isSale"] = $isSale;
		$result = $this->allowField(true)->update($data,['id'=>$id,'shopId'=>$shopId]);
		if(false !== $result){
			return WSTReturn(lang('seckill_operation_success'),1);
		}
		return WSTReturn(lang('seckill_operation_fail'));
	}

	/**
	 * 删除秒杀
	 */
	public function del($shopId){
		$id = (int)input('id');
		$data = [];
		$data['shopId'] = $shopId;
		$data['id'] = $id;
        $rs = $this->update(['dataFlag'=>-1],$data);
        return WSTReturn(lang('seckill_operation_success'),1);
	}

	/**
	 * 管理员查看秒杀列表
	 */
	public function pageQueryByAdmin($seckillStatus){
		$title =input('title');
		$shopName =input('shopName');
		//$seckillStatus = (int)input('seckillStatus',-2);
		$where = [];
		$where[] = ['s.dataFlag','=',1];
		if($title !='')$where[] = ['sl.title', 'like', '%'.$title.'%'];
		if($shopName !='')$where[] = ['sp.shopName', 'like', '%'.$shopName.'%'];
		if($seckillStatus >= -2)$where[]=['s.seckillStatus','=',$seckillStatus];
        $page =  $this->alias("s")
                    ->join('__SECKILLS_LANGS__ sl','sl.seckillId=s.id and sl.langId='.WSTCurrLang())
        			->join("shops sp","s.shopId=sp.shopId","inner")
        			->field("s.*,sl.title,sp.shopName")
        			->where($where)
					->order('s.id desc')
                	->paginate(input('limit/d'))->toArray();
        if(count($page['data'])>0){
        	$today = date("Y-m-d");
        	foreach($page['data'] as $key =>$v){
        		if($v['startDate']<=$today && $v['endDate']>=$today){
        			$page['data'][$key]['status'] = 1;
        		}else if($v['startDate']>$today){
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
	* 待审核秒杀活动数
	*/
	public function getAuditCount(){
		return Db::name("seckills")->where(['dataFlag'=>1,'seckillStatus'=>0])->count();
	}

	/**
	* 设置商品违规状态
	*/
	public function illegal(){
		$illegalRemarks = input('post.illegalRemarks');
		$id = (int)input('post.id');
		if($illegalRemarks=='')return WSTReturn(lang('seckill_input_violation_reason_tips'));
		//判断商品状态
		$rs = $this
            ->alias('s')
            ->join('__SECKILLS_LANGS__ sl','sl.seckillId=s.id and sl.langId='.WSTCurrLang())
            ->field('s.*,sl.title')
            ->where(['id'=>$id,"dataFlag"=>1])->find();
		if(empty($rs))return WSTReturn(lang('seckill_invalid_goods'));
		if((int)$rs['seckillStatus']!=0)return WSTReturn(lang('seckill_fail_status_changed_tips'));
		Db::startTrans();
		try{
			$res = $this->where(['id'=>$id])->update(['seckillStatus'=>-1,'illegalRemarks'=>$illegalRemarks]);
			if($res!==false){
				//发送一条商家信息
				$tpl = WSTMsgTemplates('SECKILL_ACTIVITY_REJECT');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${TITLE}','${TIME}','${REASON}'];
		            $replace = [$rs['title'],date('Y-m-d H:i:s'),$illegalRemarks];

		            $msg = array();
		            $msg["shopId"] = $rs['shopId'];
		            $msg["tplCode"] = $tpl["tplCode"];
		            $msg["msgType"] = 1;
		            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
		            $msg["msgJson"] = ['from'=>7,'dataId'=>$id];
		            model("common/MessageQueues")->add($msg);
		        }
		        if((int)WSTConf('CONF.wxenabled')==1){
					$params = [];
					$params['TITLE'] = $rs['title'];
					$params['TIME'] = date('Y-m-d H:i:s');
					$params['REASON'] = $illegalRemarks;

					$msg = array();
					$tplCode = "WX_SECKILL_ACTIVITY_REJECT";
					$msg["shopId"] = $rs['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);
				}
				Db::commit();
				return WSTReturn(lang('seckill_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('seckill_operation_fail'),-1);
	}
   /**
	* 通过商品审核
	*/
	public function allow(){
		$id = (int)input('post.id');
		//判断商品状态
		$rs = $this
            ->alias('s')
            ->join('__SECKILLS_LANGS__ sl','sl.seckillId=s.id and sl.langId='.WSTCurrLang())
            ->field('s.*,sl.title')
            ->where(['id'=>$id,"dataFlag"=>1])->find();
		if(empty($rs))return WSTReturn(lang('seckill_invalid_goods'));
		if((int)$rs['seckillStatus']!=0)return WSTReturn(lang('seckill_fail_status_changed_tips'));
		Db::startTrans();
		try{
			$res = $this->where(['id'=>$id])->update(['seckillStatus'=>1]);
			if($res!==false){
				//发送一条商家信息
				$tpl = WSTMsgTemplates('SECKILL_ACTIVITY_ALLOW');
		        if($tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${TITLE}','${TIME}'];
		            $replace = [$rs['title'],date('Y-m-d H:i:s')];

		            $msg = array();
		            $msg["shopId"] = $rs['shopId'];
		            $msg["tplCode"] = $tpl["tplCode"];
		            $msg["msgType"] = 1;
		            $msg["content"] = str_replace($find,$replace,$tpl['tplContent']);
		            $msg["msgJson"] = ['from'=>7,'dataId'=>$id];
		            model("common/MessageQueues")->add($msg);
		        }
		        if((int)WSTConf('CONF.wxenabled')==1){
					$params = [];
					$params['TITLE'] = $rs['title'];
					$params['RESULT'] = WSTLang('seckill_audit_pass');
					$params['TIME'] = date('Y-m-d H:i:s');

					$msg = array();
					$tplCode = "WX_SECKILL_ACTIVITY_ALLOW";
					$msg["shopId"] = $rs['shopId'];
		            $msg["tplCode"] = $tplCode;
		            $msg["msgType"] = 4;
		            $msg["paramJson"] = ['CODE'=>$tplCode,'params'=>$params];
		            $msg["msgJson"] = "";
		            model("common/MessageQueues")->add($msg);
				}
				Db::commit();
				return WSTReturn(lang('seckill_operation_success'),1);
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('seckill_operation_fail'),-1);
	}

    /**
	 * 删除秒杀
	 */
	public function delByAdmin(){
		$id = (int)input('id');
		Db::startTrans();
		try{
			$this->where(['id'=>$id])->update(['dataFlag'=>-1]);
			Db::name("seckill_goods")->where(['seckillId'=>$id])->update(['dataFlag'=>-1]);
			Db::commit();
			return WSTReturn(lang('seckill_operation_success'),1);
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('seckill_operation_fail'),-1);
	}

	/**
	 * 秒杀活动上下架
	 */
	public function toggleSetByAdmin(){
		$id = (int)input("id");
		$isSale = (int)input("isSale")==1?1:0;
		$data = [];
		$data["isSale"] = $isSale;
		$result = $this->allowField(true)->update($data,['id'=>$id]);
		if(false !== $result){
			return WSTReturn(lang('seckill_operation_success'),1);
		}
		return WSTReturn(lang('seckill_operation_fail'));
	}

	/**
	 * 查询商品订单
	 */
	public function pageQueryByGoods(){
	    $grouponId = (int)input('grouponId');
		$orderNo = input('post.orderNo');
		$payType = (int)input('post.payType');
		$deliverType = (int)input('post.deliverType');
		$shopId = (int)session('WST_USER.shopId');
		$where[] = ['shopId','=',$shopId];
		$where[] = ['dataFlag','=',1];
		$where[] = ['orderCode','=','seckill'];
		if($grouponId>0)$where[] = ['orderCodeTargetId','=',$grouponId];
		if($orderNo!=''){
			$where[] = ['orderNo','like',"%$orderNo%"];
		}
		if($payType > -1){
			$where[] = ['payType','=',$payType];
		}
		if($deliverType > -1){
			$where[] = ['deliverType','=',$deliverType];
		}
		$page = Db::name('orders')->alias('o')->where($where)
		      ->join('__ORDER_REFUNDS__ orf','orf.orderId=o.orderId and refundStatus=0','left')
		      ->field('o.orderId,orderNo,goodsMoney,totalMoney,realTotalMoney,orderStatus,deliverType,deliverMoney,isAppraise,isRefund
		              ,payType,payFrom,userAddress,orderStatus,isPay,isAppraise,userName,orderSrc,o.createTime,orf.id refundId')
			  ->order('o.createTime', 'desc')
			  ->paginate()->toArray();
	    if(count($page['data'])>0){
	    	 $orderIds = [];
	    	 foreach ($page['data'] as $v){
	    	 	 $orderIds[] = $v['orderId'];
	    	 }
	    	 $goods = Db::name('order_goods')->where([['orderId','in',$orderIds]])->select();
	    	 $goodsMap = [];
	    	 foreach ($goods as $v){
	    	 	 $v['goodsSpecNames'] = str_replace('@@_@@','、',$v['goodsSpecNames']);
	    	 	 $goodsMap[$v['orderId']][] = $v;
	    	 }
	    	 foreach ($page['data'] as $key => $v){
	    	 	 $page['data'][$key]['list'] = $goodsMap[$v['orderId']];
	    	 	 $page['data'][$key]['payTypeName'] = WSTLangPayType($v['payType']);
	    	 	 $page['data'][$key]['deliverType'] = WSTLangDeliverType($v['deliverType']==1);
	    	 	 $page['data'][$key]['status'] = WSTLangOrderStatus($v['orderStatus']);
	    	 }
	    }
	    return WSTReturn('',1,$page);
	}

}

