<?php
namespace addons\presale\controller;

use think\addons\Controller;
use addons\presale\model\Presales as M;
use addons\presale\model\Goods as GM;
use addons\presale\model\Orders as OM;
use addons\presale\model\Carts as CM;
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
 * 预售商品插件
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
        $subDir =  'upload/shares/presale/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'presale_qr_app_'.$today.'_'.$id.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        $_rs = WSTReturn("",1,$outImg);
        if ($isNew==0 && file_exists($shareImg)) {
            return json_encode($_rs);
        }
        $qr_url = addon_url('presale://goods/wxdetail',array('id'=>$id,'shareUserId'=>base64_encode($userId)),true,true);//二维码内容
        //生成二维码图片
        $qr_code = WSTCreateQrcode($qr_url,'','presale',3600,2);
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
     * 域名
     */
    public function domain(){
        if(!empty(WSTConf('WST_ADDONS.aliyunoss'))){
            return WSTConf('CONF.resourcePath').'/';
        }
        return url('/','','',true);
    }

    /**
     * 预售商品列表查询
     */
    public function presaleListQuery(){
        $m = new GM();
        $rs = $m->pageQuery();
        if(!empty($rs['data'])){
            foreach ($rs['data'] as $key =>$v){
                $rs['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
                $rs['data'][$key]['goodsName'] = htmlspecialchars_decode($v['goodsName']);
            }
        }
        // 域名
        $rs['domain'] = $this->domain();
        $rs['keyword'] = WSTReplaceFilterWords(input('goodsName'),WSTConf("CONF.limitWords"));
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
    * 预售商品详情
    */
    public function getPresaleDetail(){
        $m = new GM();
        $userId = model('app/index')->getUserId();
        $id = input('id/d',0);
        $goods = $m->getBySale($id);
        // 找不到商品记录
        if(empty($goods))return json_encode(WSTReturn(lang('presale_cannt_pay'),-1));
        if($goods['isSpec']==1){
            sort($goods['spec']);
        }
        // 删除无用字段
        WSTUnset($goods,'goodsSn,goodsDesc,productNo,isSale,isBest,isHot,isNew,isRecom,goodsCatIdPath,goodsCatId,shopCatId1,shopCatId2,brandId,goodsStatus,saleTime,goodsSeoKeywords,illegalRemarks,dataFlag,createTime,read');

        $goods['goodsName'] = htmlspecialchars_decode($goods['goodsName']);
		//分享信息
        $conf = $m->getConf('Presale');
		$shareInfo['link'] = addon_url('presale://goods/wxdetail',
										['id'=>$id,'shareUserId'=>base64_encode($userId)],
										true,
										true);
		$shareInfo['title'] = $goods['goodsName'];
		$shareInfo['desc'] = (isset($conf["goodsShareTitle"]) && $conf["goodsShareTitle"]!="")?$conf["goodsShareTitle"]:WSTConf("CONF.mallSlogan");
		$shareInfo['imgUrl'] = WSTConf('CONF.resourcePath')."/".$goods['goodsImg'];
		$goods['shareInfo'] = $shareInfo;

        // 是否开启下单获得积分
        $goods['isOrderScore'] = WSTConf('CONF.isOrderScore');
        // 计算可获得积分
        $goods['score'] = intval($goods['shopPrice']*(float)WSTConf('CONF.moneyToScore'));
        return json_encode(WSTReturn('',1,$goods));

    }
    /******************************************************************* 结算页面start ****************************************************************************/
    /**
     * 下单
     */
    public function addCart(){
        $userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('presale_no_login'),-999));
        }
        $m = new CM();
        return json_encode($m->addCart($userId));
    }
    /**
     * 计算运费、积分和总商品价格
     */
    public function getCartMoney(){
        $userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('presale_no_login'),-999));
        }
        $m = new CM();
        $data = $m->getCartMoney($userId);
        return json_encode($data);
    }

    /**
     * 提交订单
     */
    public function submit(){
        $userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('presale_no_login'),-999));
        }
        $m = new OM();
        $orderSrc = input('orderSrc');
        $orderSrcArr = ['android'=>3,'ios'=>4];
        if(!isset($orderSrcArr[$orderSrc])){
            return json_encode(WSTReturn(lang('presale_illegal_order_tips'),-1));
        }
        $orderSrc = $orderSrcArr[$orderSrc];
        $rs = $m->submit($orderSrc,$userId);
        if($rs["status"]==1){
            $pkey = WSTBase64urlEncode('ding@'.$rs["data"]."@".$orderSrc);
            $rs["pkey"] = $pkey;
        }
        return json_encode($rs);
    }
    /**
     * 结算页面
     */
    public function settlement(){
        $CARTS = session('PRESALE_CARTS');
        if(empty($CARTS)){
            return json_encode(WSTReturn(lang('presale_no_settlement_goods_tips'),-1));
            exit;
        }
        $userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('presale_no_login'),-999));
        }
        $m = new CM();
        $carts = $m->getCarts();
        if(isset($carts['carts']['goods']) && isset($carts['carts']['goods']['goodsName'])){
            $carts['carts']['goods']['goodsName'] = htmlspecialchars_decode($carts['carts']['goods']['goodsName']);
        }
        //获取一个用户地址
        $addressId = (int)input('addressId');
        $ua = model('common/userAddress');
        if($addressId>0){
            $userAddress = $ua->getById($addressId,$userId);
        }else{
            $userAddress = $ua->getDefaultAddress($userId);
        }
        $carts['userAddress'] = $userAddress;
        //计算可用积分
        $orderUsableScore = WSTOrderUsableScore($carts['goodsTotalMoney'],$userId);
        $carts['userOrderScore'] = $orderUsableScore['score'];
        $carts['userOrderMoney'] = $orderUsableScore['money'];
        $carts['orderScore'] = WSTMoneyGiftScore($carts['goodsTotalMoney']);
        $carts['domain'] = $this->domain();
        // 是否开启积分支付
        $carts['isOpenScorePay'] = WSTConf('CONF.isOpenScorePay');
        //获取支付方式
        $payments = model('common/payments')->getByGroup('4', 1, true);
        $carts['payments'] = $payments;
        return json_encode(WSTReturn('ok',1,$carts));
    }





    /******************************************************************* 结算页面end ****************************************************************************/

    /******************************************************************* 余额支付start ****************************************************************************/
    /**
	 * 跳去支付页面
	 */
	public function payment(){
		$userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('presale_no_login'),-999));
        }
        $pkey = input('pkey');

        $pkey = WSTBase64urlDecode($pkey);
        $pkey = explode('@',$pkey);
        $data = [];
        $porderId = (int)$pkey[1];
        $data['porderId'] = $porderId;
        $data['userId'] = $userId;

        $m = new M();
        $needPay = 0;
        if($pkey[0]=="ding"){
            $presale = $m->getPresalePay($data['porderId'],$userId);
            $needPay = $presale["depositMoney"];
            $flag = (isset($presale["isPay"]) && $presale["isPay"]>0)?true:false;
        }else{
            $presale = $m->getPresalePay($data['porderId'],$userId);
            $needPay = $presale["surplusMoney"];
            $flag = ($presale["isPay"]==2)?true:false;
        }

        if($flag){
            return json_encode(WSTReturn(lang('presale_order_has_pay_tips'),-999));
        }else{
            if($pkey[0]=='ding'){
                $rs = $m->getPayInfo($porderId,1,$userId);
            }else{
                $rs = $m->getPayInfo($porderId,2,$userId);
            }
            if($rs["status"]==1){
                $user = model('common/users')->getFieldsById($userId,'userMoney,payPwd');
                $payPwd = $user['payPwd'];
                $payPwd = empty($payPwd)?0:1;
                $data["userMoney"] = $user['userMoney'];
                $data["payPwd"] = $payPwd;
                $data['presale'] = $rs['data']['presale'];
                $data['goodsNum'] = $rs['data']['porder']['goodsNum'];
                $data['needPay'] = $needPay;
                $data['payObj'] = $pkey[0];
                return json_encode(WSTReturn('',1,$data));
            }else{
                return json_encode(WSTReturn(lang('presale_get_order_info_fail'),-1));
            }
        }
	}

	/**
	 * 钱包支付
	 */
	public function payByWallet(){
		$userId = (int)model('app/index')->getUserId();
		if($userId<=0){
            return json_encode(WSTReturn(lang('presale_no_login'),-999));
        }
		$m = new OM();
		return json_encode($m->payByWallet($userId));
	}
	/****************************************** 订单操作 *******************************************/
	/**
	* 预售订单列表
	*/
	public function pageQuery(){
		$userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('presale_no_login'),-999));
        }
        $m = new OM();
        $rs = $m->pageQuery($userId);
        if(!empty($rs['data'])){
            foreach ($rs['data'] as $key =>$v){
                $rs['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
                $rs['data'][$key]['goodsName'] = htmlspecialchars_decode($v['goodsName']);
            }
        }
        return json_encode(WSTReturn('ok',1,$rs));
	}

    /**
     * 查看预售订单详情
     */
    public function getOrderDetail(){
    	$userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('presale_no_login'),-999));
        }
        $m = new OM();
        $rs = $m->getOrderDetail($userId);
        $rs['goodsSpecNames'] = implode(' ',explode('@@_@@',$rs['goodsSpecNames']));
        $rs['goodsImg'] = WSTImg($rs['goodsImg'],3);
        $rs['payInfo'] = WSTLangPayType($rs['payType']);
        $rs['deliverInfo'] = WSTLangDeliverType($rs['deliverType']);
        return json_encode(WSTReturn('ok',1,$rs));
    }
}
