<?php
namespace addons\wstim\controller;
use think\addons\Controller;
use addons\wstim\model\ServiceEvaluates as M;
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
 * 客服评分控制器
 */
class Serviceevaluates extends Controller{
    protected $beforeActionList = ['checkAuth','setUserInfo'];
    static private $isApp;
    static private $isWeapp;
    static private $userInfo;

    // 权限验证方法
    protected function checkAuth(){
        $isApp = input('isApp');
        $isWeapp = (int)input('isWeapp');
        if($isApp){
            self::$isApp=1;
            $tokenId = input('tokenId');
            if($tokenId==''){
                $rs = WSTImReturn(lang('wstim_not_login'),-999);
                die($rs);
            }
            $userId = Db::name('app_session')->where("tokenId='{$tokenId}'")->value('userId');
            if(empty($userId)){
                $rs = WSTImReturn(lang('wstim_login_expired'),-999);
                die($rs);
            }
            return true;
        }else if($isWeapp==1){
            self::$isWeapp=1;
            $tokenId = input('tokenId');
            if($tokenId==''){
                $rs = WSTImReturn(lang('wstim_not_login'),-999);
                die($rs);
            }
            $userId = Db::name('weapp_session')->where("tokenId='{$tokenId}'")->value('userId');
            if(empty($userId)){
                $rs = WSTImReturn(lang('wstim_login_expired'),-999);
                die($rs);
            }
            return true;
        }else{
            parent::checkAuth();
        }
    }

    protected function setUserInfo(){
        $tokenId = input('tokenId');
        if($tokenId!=''){
            $userId = getUserId();
            $userData = model('common/users')->getFieldsById($userId,['loginName','userName','userPhoto']);
            $userData['userId'] = $userId;
            $userData['shopId'] = getShopId($userId);
            $userData['serviceId'] = $userData['shopId']>0?$userId:'';
            $userData['isService'] = $userData['serviceId']>0;
            self::$userInfo = $userData;
        }else{
            self::$userInfo = session('WST_USER');
        }
    }

    /**
     * 客服评分查询
     */
    public function pagequery(){
        $m = new M();
        return WSTGrid($m->pageQuery());
    }
    /**
     * 店铺查看客服评分
     */
    public function index(){
    	return $this->fetch("shop/serviceevaluates/list");
    }
    /**
     * 新增评分
     */
    public function commitEval(){
        $m = new M();
        $rs = $m->commitEval(self::$userInfo['userId']);
        if(self::$isApp==1 || self::$isWeapp==1)return json_encode($rs);
        return $rs;
    }
}
