<?php
namespace addons\member\controller;
use Env;
use think\addons\Controller;
use addons\member\model\Member as M;
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
 * 会员营销控制器
 */
class Member extends Controller{
    public function __construct(){
        parent::__construct();
        $this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
    }

    /*************************用户中心*****************************/
    /**
     * 加载我的推荐用户页面
     */
    public function userMemberUsers(){
        return $this->fetch("/home/users/user_list");
    }

    /**
     * 获取用户分成列表
     */
    public function queryMineUsers(){
        $m = new M();
        $rs = $m->queryMineUsers();
        $rs['status'] = 1;
        return $rs;
    }

    /**
     * 加载我的奖励列表页面
     */
    public function userMemberAwards(){
        return $this->fetch("/home/users/award_list");
    }

    /**
     * 获取用户分成列表
     */
    public function queryUserAwards(){
        $m = new M();
        $rs = $m->queryUserAwards();
        $rs['status'] = 1;
        return $rs;
    }

    /*******************************wechat*********************************/
    /**
     * 用户营销主页
     */
    public function wechatMemberHome(){
        $this->checkAuth();
        $m = new M();
        $user = $m->getUserInfo();
        $cfg = $m->getAddonConfig();
        //分享信息
        $shareInfo= array(
            'title'=>$cfg["mallShareTitle"],
            'desc'=>WSTConf('CONF.mallName'),
            'link'=>url('wechat/index/index',array('shareUserId'=>base64_encode((int)session('WST_USER.userId'))),true,true),
            'imgUrl'=>WSTConf('CONF.mallLogo')
        );
        $this->assign('shareInfo', $shareInfo);
        $this->assign('user', $user);
        return $this->fetch("/wechat/users/member_home");
    }


    /**
     * 获取用户列表
     */
    public function wechatMemberUsers(){
        $this->checkAuth();
        $m = new M();
        $user = $m->getUserInfo();
        $this->assign('user', $user);
        return $this->fetch("/wechat/users/user_list");
    }

    /**
     * 获取用户列表
     */
    public function queryWechatMemberUsers(){
        $this->checkAuth();
        $m = new M();
        $rs = $m->queryMineUsers();
        $rs['status'] = 1;
        return $rs;
    }

    /**
     * 获取奖励列表
     */
    public function wechatMemberAwards(){
        $this->checkAuth();
        $m = new M();
        $user = $m->getUserInfo();
        $this->assign('user', $user);
        return $this->fetch("/wechat/users/award_list");
    }

    /**
     * 获取奖励列表
     */
    public function queryWechatMemberAwards(){
        $this->checkAuth();
        $m = new M();
        $rs = $m->queryUserAwards();
        $rs['status'] = 1;
        return $rs;
    }

    /**
     * 生成海报【微信】
     */
    public function wxCreatePoster(){
        $m = new M();
        $userId = (int)session("WST_USER.userId");
        $isNew = (int)input("isNew",0);
        $id = (int)input("id",0);
        $subDir =  'upload/shares/member/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'member_qr_wx_'.$today.'_'.$userId.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        if($isNew==0){
            if (file_exists($shareImg)) {
                return WSTReturn("",1,["shareImg"=>$outImg]);
            }
        }
        $qr_url = url('wechat/index/index',array('shareUserId'=>base64_encode($userId)),true,true);//二维码内容
        //生成二维码图片
        $qr_code = WSTCreateQrcode($qr_url,'','member',3600,2);
        $qr_code = WSTRootPath().'/'.$qr_code;
        $rs = $m->createPoster($userId,$qr_code,$outImg);
        return $rs;
    }

    /*******************************mobile*********************************/

    /**
     * 用户营销主页
     */
    public function mobileMemberHome(){
        $this->checkAuth();
        $m = new M();
        $user = $m->getUserInfo();
        $cfg = $m->getAddonConfig();
        //分享信息
        $shareInfo= array(
            'title'=>$cfg["mallShareTitle"],
            'desc'=>WSTConf('CONF.mallName'),
            'link'=>url('mobile/index/index',array('shareUserId'=>base64_encode((int)session('WST_USER.userId'))),true,true),
            'imgUrl'=>WSTConf('CONF.mallLogo')
        );
        $this->assign('shareInfo', $shareInfo);
        $this->assign('user', $user);
        return $this->fetch("/mobile/users/member_home");
    }
    
    
    /**
     * 获取用户列表
     */
    public function mobileMemberUsers(){
        $this->checkAuth();
        $m = new M();
        $user = $m->getUserInfo();
        $this->assign('user', $user);
        return $this->fetch("/mobile/users/user_list");
    }
    
    /**
     * 获取用户列表
     */
    public function queryMobileMemberUsers(){
        $this->checkAuth();
        $m = new M();
        $rs = $m->queryMineUsers();
        $rs['status'] = 1;
        return $rs;
    }
    
    /**
     * 获取奖励列表
     */
    public function mobileMemberAwards(){
        $this->checkAuth();
        $m = new M();
        $user = $m->getUserInfo();
        $this->assign('user', $user);
        return $this->fetch("/mobile/users/award_list");
    }
    
    /**
     * 获取奖励列表
     */
    public function queryMobileMemberAwards(){
        $this->checkAuth();
        $m = new M();
        $rs = $m->queryUserAwards();
        $rs['status'] = 1;
        return $rs;
    }

    /**
     * 生成海报【手机】
     */
    public function moCreatePoster(){
        $m = new M();
        $userId = (int)session("WST_USER.userId");
        $isNew = (int)input("isNew",0);
        $id = (int)input("id",0);
        $subDir =  'upload/shares/member/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'member_qr_mo_'.$today.'_'.$userId.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        if($isNew==0){
            if (file_exists($shareImg)) {
                return WSTReturn("",1,["shareImg"=>$outImg]);
            }
        }
        $qr_url = url('mobile/index/index',array('shareUserId'=>base64_encode($userId)),true,true);//二维码内容
        //生成二维码图片   
        $qr_code = WSTCreateQrcode($qr_url,'','member',3600,2);
        $qr_code = WSTRootPath().'/'.$qr_code;
        $rs = $m->createPoster($userId,$qr_code,$outImg);
        return $rs;
    }

    /*******************************weapp*********************************/

    public function weShareInfo(){
        $userId = model('weapp/index')->getUserId();
        $m = new M();
        $cfg = $m->getAddonConfig();
        //分享信息
        $shareInfo= array(
            'title'=>$cfg["mallShareTitle"],
            'desc'=>WSTConf('CONF.mallName'),
            'shareUserId'=> $userId,
            'imgUrl'=>WSTConf('CONF.mallLogo')
        );
        return jsonReturn('success',1,$shareInfo);
    }

    /**
     * 获取分销用户信息
     */
    public function weGetUser(){
        $userId = model('weapp/index')->getUserId();
        $m = new M();
        $user = $m->getUserInfo($userId);
        return jsonReturn('success',1,$user);
    }

    /**
     * 获取用户列表
     */
    public function weQueryMemberUsers(){
        $userId= model('weapp/index')->getUserId();
        $m = new M();
        $rs = $m->queryMineUsers($userId);
        $rs['status'] = 1;
        return jsonReturn('success',1,$rs);
    }

    /**
     * 获取奖励列表
     */
    public function weQueryMemberAwards(){
        $userId= model('weapp/index')->getUserId();
        $m = new M();
        $rs = $m->queryUserAwards($userId);
        $rs['status'] = 1;
        return jsonReturn('success',1,$rs);
    }


    /**
     * 获取商品小程序码
     */
    public function weAppShareQrCode(){
        $shareUserId = (int)input("shareUserId",0);
        $today = date("Ymd");
        $subDir =  'upload/shares/member/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $fname = 'member_qr_we_'.$today.'_'.$shareUserId.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        if (file_exists($shareImg)) {
            return jsonReturn("",1,$outImg);
        }
        $weapp = new \weapp\WSTWeapp(WSTConf('CONF.weAppId'),WSTConf('CONF.weAppKey'));
        $tokenId = $weapp->getToken();

        $parm['scene'] = $shareUserId;
        $parm['page'] = input('pages');//上线时解除注释
        $parm['width'] = 200;
        $parm['is_hyaline'] = true;
        $url='https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$tokenId;
        $qrdata = $weapp->http($url,json_encode($parm));
        $qr_code = WSTRootPath().'/'.$subDir.'/'.$fname;// 小程序码
        file_put_contents( $qr_code,$qrdata );
        return jsonReturn("",1,$subDir.'/'.$fname);
    }
}