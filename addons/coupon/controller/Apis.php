<?php
namespace addons\coupon\controller;
use think\addons\Controller;
use addons\coupon\model\Coupons as M;
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
 * 插件控制器
 */
class Apis extends Controller{
    /**
    * APP请求检测是否有安装插件
    */
    public function index(){
        return json_encode(['status'=>1]);
    }
    /**
    * 获取用户优惠券数量
    */
    public function getUserCouponNum(){
        $this->checkAuth();
        $m = new M();
        $userId = model('app/index')->getUserId();
        $num = $m->couponsNum($userId);
        return json_encode(['status'=>1,'num'=>$num]);
    }
     // 权限验证方法
    protected function checkAuth(){
        $tokenId = input('tokenId');
        if($tokenId==''){
            $rs = json_encode(WSTReturn(lang('no_login'),-999));
            die($rs);
        }
        $userId = Db::name('app_session')->where("tokenId='{$tokenId}'")->value('userId');
        if(empty($userId)){
            $rs = json_encode(WSTReturn(lang('login_over_time'),-999));
            die($rs);
        }
        return true;
    }
    /*
    * 领券中心列表查询
    */
    public function pageCouponQuery(){
        $m = new M();
        $userId = model('app/index')->getUserId();
        $rs = $m->pageCouponQuery($userId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
	/**
    * 领取优惠券
    */
    public function receive(){
        $this->checkAuth();
        $m = new M();
        $userId = model('app/index')->getUserId();
        $rs = $m->receive($userId);
        return json_encode($rs);
    }
    /*
     * 获取指定商品的优惠券
     */
    public function getCouponsByGoods(){
        $m = new M();
        $userId = model('app/index')->getUserId();
        $rs = $m->getCouponsByGoods($userId);
        return json_encode($rs);
    }
    /**
     * 加载用户优惠券数据
     */
    public function pageQueryByUser(){
        $this->checkAuth();
        $m = new M();
        $userId = model('app/index')->getUserId();
        $rs = $m->pageQueryByUser($userId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
    *  可用优惠券商品查询
    *  @condition 排序条件
    *  @desc
    *  @couponId  优惠券id
    */
    public function pageQueryByCouponGoods(){
        $m = new M();
        $rs = $m->pageQueryByCouponGoods();
        $rs['domain'] = url('/','','',true);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 首页优惠券列表
     */
    public function getHomeList(){
        $m = new M();
        $data = $m->pageCouponQuery(0,4);
        return jsonReturn('success',1,$data);
    }
}
