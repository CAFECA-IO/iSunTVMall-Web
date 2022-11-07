<?php
namespace addons\integral\controller;

use think\addons\Controller;
use addons\integral\model\Integrals as M;
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
 * 积分商城插件
 */
class Apis extends Controller{
    /**
     * 生成海报
     */
    public function createPoster(){
        $m = new M();
        $userId = model('app/index')->getUserId();
        $isNew = (int)input("isNew",0);
        $id = (int)input("id",0);
        $subDir =  'upload/shares/integral/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'integral_qr_app_'.$today.'_'.$id.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        $_rs = WSTReturn("",1,$outImg);
        if ($isNew==0 && file_exists($shareImg)) {
            return json_encode($_rs);
        }
        $qr_url = addon_url('integral://goods/wxdetail',array('id'=>$id,'shareUserId'=>base64_encode($userId)),true,true);//二维码内容
        //生成二维码图片
        $qr_code = WSTCreateQrcode($qr_url,'','integral',3600,2);
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
     * 积分商品列表查询
     */
    public function integralListQuery(){
        $userId = model('app/index')->getUserId();
        $userScore = 0;// 用户积分
        $userMoney = 0;
        if($userId>0){
            $user = model('common/users')->getFieldsById($userId,["userScore","userMoney","userId"]);
            $userScore = $user['userScore'];
            $userMoney = $user['userMoney'];
        }
        $m = new M();
        $rs = $m->pageQuery();
        if(!empty($rs['data'])){
            foreach ($rs['data'] as $key =>$v){
                $rs['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
                $rs['data'][$key]['goodsName'] = htmlspecialchars_decode($v['goodsName']);
            }
        }
        // 用户积分
        $rs['userScore'] = $userScore;
        $rs['userMoney'] = $userMoney;
        $rs['keyword'] = WSTReplaceFilterWords(input('goodsName'),WSTConf("CONF.limitWords"));
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
    * 积分商品详情
    */
    public function getIntegralDetail(){
        $m = new M();
        $userId = model('app/index')->getUserId();
        $id = input('id/d',0);
        $goods = $m->getBySale($id,$userId);
        // 找不到商品记录
        if(empty($goods))return json_encode(WSTReturn(lang('integral_not_find_goods'),-1));
        // 删除无用字段
        WSTUnset($goods,'goodsSn,goodsDesc,productNo,isSale,isBest,isHot,isNew,isRecom,goodsCatIdPath,goodsCatId,shopCatId1,shopCatId2,brandId,goodsStatus,saleTime,goodsSeoKeywords,illegalRemarks,dataFlag,createTime,read');
        $goods['goodsName'] = htmlspecialchars_decode($goods['goodsName']);
        $goods['domain'] = $this->domain();
        // 猜你喜欢6件商品
        $like = model('common/Tags')->listByGoods('best',$goods['shop']['catId'],6);
        foreach($like as $k=>$v){
            // 删除无用字段
            unset($like[$k]['shopName']);
            unset($like[$k]['shopId']);
            unset($like[$k]['goodsSn']);
            unset($like[$k]['goodsStock']);
            unset($like[$k]['saleNum']);
            unset($like[$k]['marketPrice']);
            unset($like[$k]['isSpec']);
            unset($like[$k]['appraiseNum']);
            unset($like[$k]['visitNum']);
            // 替换商品图片
            $like[$k]['goodsImg'] = WSTImg($v['goodsImg'],3);
        }
        $goods['like'] = $like;
        return json_encode(WSTReturn('',1,$goods));
    }
    /******************************************************************* 结算页面start ****************************************************************************/
    /**
     * 下单
     * bayNum:
     * id:积分商品Id
     * tokenId:
     */
    public function addCart(){
        $userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('integral_no_login'),-999));
        }
        $m = new M();
        return json_encode($m->addCart($userId));
    }
    /**
     * 计算运费、积分和总商品价格
     */
    public function getCartMoney(){
        $userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('integral_no_login'),-999));
        }
        $m = new M();
        $data = $m->getCartMoney($userId);
        return json_encode($data);
    }

    /**
     * 提交订单
     */
    public function submit(){
        $userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('integral_no_login'),-999));
        }
        $m = new M();
        $orderSrc = input('orderSrc');
        $orderSrcArr = ['android'=>3,'ios'=>4];
        if(!isset($orderSrcArr[$orderSrc])){
            return json_encode(WSTReturn(lang('integral_illegal_order_source'),-1));
        }
        $orderSrc = $orderSrcArr[$orderSrc];
        $rs = $m->submit($orderSrc,$userId);
        return json_encode($rs);
    }
    /**
     * 结算页面
     */
    public function settlement(){
        $CARTS = session('INTEGRAL_CARTS');
        if(empty($CARTS)){
            return json_encode(WSTReturn(lang('integral_no_settlement_tips'),-1));
            exit;
        }
        $userId = model('app/index')->getUserId();
        if($userId<=0){
            return json_encode(WSTReturn(lang('integral_no_login'),-999));
        }
        $m = new M();
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
        //获取用户积分
        $user = model('common/users')->getFieldsById($userId,'userScore');
        //计算可用积分和金额
        $goodsTotalMoney = $carts['goodsTotalMoney'];
        $goodsTotalScore = WSTScoreToMoney($goodsTotalMoney,true);
        $useOrderScore =0;
        $useOrderMoney = 0;
        if($user['userScore']>$goodsTotalScore){
            $useOrderScore = $goodsTotalScore;
            $useOrderMoney = $goodsTotalMoney;
        }else{
            $useOrderScore = $user['userScore'];
            $useOrderMoney = WSTScoreToMoney($useOrderScore);
        }

        $carts['userOrderScore'] = $useOrderScore;
        $carts['userOrderMoney'] = $useOrderMoney;
        //获取支付方式
        $payments = model('common/payments')->getByGroup('4',1, true);
        $carts['payments'] = $payments;
        return json_encode(WSTReturn('ok',1,$carts));
    }





    /******************************************************************* 结算页面end ****************************************************************************/
    /**
     * 首页商品列表
     */
    public function getHomeList(){
        $m = new M();
        $rs = $m->pageQuery(3);
        foreach ($rs['data'] as $key =>$v){
            $rs['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
        }
        return jsonReturn('success',1,$rs);
    }
}
