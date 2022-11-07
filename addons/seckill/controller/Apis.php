<?php
namespace addons\seckill\controller;
use Env;
use think\addons\Controller;
use addons\seckill\model\Seckills as M;
use addons\seckill\model\SeckillGoods as GM;
use addons\seckill\model\SeckillTimeIntervals as TM;
use addons\seckill\model\Orders as OM;
use wstmart\common\model\UserAddress;
use Request;
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
 * app端接口
 */
class Apis extends Controller{
    /**
     * 生成海报
     */
    public function createPoster(){
        $m = new GM();
        $userId = model('app/index')->getUserId();
        $isNew = (int)input("isNew",0);
        $id = (int)input("id",0);
        $subDir =  'upload/shares/seckill/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'seckill_qr_app_'.$today.'_'.$id.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        $_rs = WSTReturn("",1,$outImg);
        if ($isNew==0 && file_exists($shareImg)) {
            return json_encode($_rs);
        }
        $qr_url = addon_url('seckill://goods/wxdetail',array('id'=>$id,'shareUserId'=>base64_encode($userId)),true,true);//二维码内容
        //生成二维码图片
        $qr_code = WSTCreateQrcode($qr_url,'','seckill',3600,2);
        $qr_code = WSTRootPath().'/'.$qr_code;
        $rs = $m->createPoster($userId,$qr_code,$outImg);
        return json_encode($_rs);
    }



    /**
    * APP请求检测是否有安装插件
    */
    public function index(){
        return json_encode(['status'=>1]);
    }
    /**
     * 获取秒杀时间段
     */
    public function getTimes(){
        $tm = new TM();
        $times = $tm->queryCurrList();
        $now = array("nowTime"=>date("Y-m-d H:i:s"));
        $rs = ['times'=>$times,'now'=>$now];
        return json_encode(WSTReturn('ok', 1, $rs));
    }
    /**
     * 秒杀列表页
     * @param timeId int 时间段id
     * @param page int 当前页码
     * @param pagesize 每页条数
     */
    public function pageQuery(){
        $m = new GM();
    	$rs = $m->pageQuery();
    	if(!empty($rs['data'])){
    		foreach ($rs['data'] as $key =>$v){
    			$rs['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
    		}
    	}
    	return json_encode(WSTReturn('ok', 1, $rs));
    }
    /**
     * 获取秒杀商品详情
     */
    public function getGoodsDetail(){
        $gm = new GM();
        $userId = model('app/index')->getUserId();
        $id = (int)input('id/d',0);
        $goods = $gm->getBySale($id,$userId,3);
        // 找不到商品记录
        if(empty($goods))return json_encode(WSTReturn('未找到商品记录',-1));
        $goods['goodsName'] = htmlspecialchars_decode($goods['goodsName']);
        // 删除无用字段
        WSTUnset($goods,'apprs,goodsSn,goodsDesc,productNo,isSale,isBest,isHot,isNew,isRecom,goodsCatIdPath,goodsCatId,shopCatId1,shopCatId2,brandId,goodsStatus,saleTime,goodsSeoKeywords,illegalRemarks,dataFlag,createTime,read');
        //分享信息
        $m = new M();
        $conf = $m->getConf("Seckill");
        $shareInfo['link'] = addon_url('seckill://goods/wxdetail',array('id'=>$id,'shareUserId'=>base64_encode($userId)),true,true);
        $shareInfo['title'] = $goods['goodsName'];
        $shareInfo['desc'] = (isset($conf["goodsShareTitle"]) && $conf["goodsShareTitle"]!="")?$conf["goodsShareTitle"]:WSTConf("CONF.mallSlogan");
        $shareInfo['imgUrl'] = WSTConf('CONF.resourceDomain')."/". WSTImg($goods['goodsImg'],3);
        $goods['shareInfo'] = $shareInfo;
        $goods["nowTime"] = date("Y-m-d H:i:s");
        return json_encode(WSTReturn('请求成功', 1, $goods));
    }


    /*********************************************************** 下单操作 ***********************************************************/
    /**
     * 下单
     */
    public function addCart(){
        $userId= model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn('您还未登录~',-999));
        }
		$m = new OM();
        $rs = $m->addCart($userId);
        return json_encode($rs);
    }
    /**
	 * 手机结算页面
	 */
	public function settlement(){
        $userId= model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn('您还未登录~',-999));
        }
		$CARTS = session('SECKILL_CARTS');

		if(empty($CARTS)){
			return jsonReturn('请选择商品',-1);
			exit;
		}
		//获取一个用户地址
		$addressId = (int)input('addressId');
		$ua = new UserAddress();
		if($addressId>0){
			$userAddress = $ua->getById($addressId,$userId);
		}else{
			$userAddress = $ua->getDefaultAddress($userId);
		}
		//获取支付方式
        $payments = model('common/payments')->getByGroup('4', 1, true);
		//获取已选的购物车商品
		$m = new OM();
		$carts = $m->getCarts($userId);
        if(empty($carts['carts'])) return jsonReturn('秒杀商品不存在',-1);
		if($carts['goodsTotalNum']>0){
			if(empty($carts['carts']))return jsonReturn('请选择商品',-1);
		}
		$carts['userAddress'] = $userAddress;
		$carts['payments'] = $payments;
		$carts['payNames'] = $carts['payCodes'] = $carts['isOnline'] =  [];
		if($payments){
			foreach ($payments as $key =>$v){
				foreach ($v as $key2 =>$v2){
					$carts['payNames'][] = $v2['payName'];
					$carts['payCodes'][] = $v2['payCode'];
					$carts['isOnlines'][] = $v2['isOnline'];
				}
			}
		}else{
			$carts['payNames'] = ['没有支付方式'];
		}

		//计算可用积分
        $orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney'],$userId);
        $carts['userOrderScore'] = $orderUsableScore['score'];
        $carts['userOrderMoney'] = $orderUsableScore['money'];
		// 是否开启积分支付
		$carts['isOpenScorePay'] = WSTConf('CONF.isOpenScorePay');
		return jsonReturn('success',1,$carts);
	}

	/**
	 * 计算运费、积分和总商品价格
	 */
	public function getCartMoney(){
        $userId= model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn('您还未登录~',-999));
        }
		$m = new OM();
		$data = $m->getCartMoney($userId);
		return jsonReturn('success',1,$data);
	}

	/**
	 * 下单
	 */
	public function submit(){
        $userId= model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn('您还未登录~',-999));
        }
		$m = new OM();
		$data = $m->submit($userId);
		return jsonReturn('success',1,$data);
    }
    /*********************************************************** 下单操作 ***********************************************************/
    /**
     * 首页商品列表
     */
    public function getHomeList(){
        $m = new TM();
        $seckTimer = $m->queryCurrSecInfo();
        $m = new GM();
        $seckGodsList = [];
        if(isset($seckTimer["id"]))$seckGodsList = $m->pageQuery($seckTimer["id"],6);
        $rs = ["seckTimer"=>$seckTimer,"seckGodsList"=>$seckGodsList];
        return jsonReturn('success',1,$rs);
    }
}
