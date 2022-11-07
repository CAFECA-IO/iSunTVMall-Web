<?php
namespace addons\member\controller;
use think\addons\Controller;
use addons\member\model\Member as DM;
use think\Db;
use Env;
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
     // 权限验证方法
    protected function checkAuth(){
        $tokenId = input('tokenId');
        if($tokenId==''){
            $rs = json_encode(WSTReturn(lang('addon_member_no_login'),-999));
            die($rs);
        }
        $userId = Db::name('app_session')->where("tokenId='{$tokenId}'")->value('userId');
        if(empty($userId)){
            $rs = json_encode(WSTReturn(lang('addon_member_login_out'),-999));
            die($rs);
        }
        return true;
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
     * 用户“我的”
     */
    public function userIndex(){
        $this->checkAuth();
        $m = new DM();
        $userId = model('app/index')->getUserId();
        $user = $m->getUserInfo($userId);
        return json_encode(WSTReturn('ok',1,$user));
    }
    /*
     * 获取分销设置信息
     */
    public function getAddonConfig(){
        $m = new DM();
        $data = $m->getAddonConfig();
        return json_encode(WSTReturn('ok',1,$data));
    }
    /**
     * 分销主页
     */
    public function memberHome(){
        $this->checkAuth();
        $m = new DM();
        $userId = model('app/index')->getUserId();
        $user = $m->getUserInfo($userId);
        $cfg = $m->getAddonConfig();
        //分享信息
        $shareInfo= array(
            'title'=>$cfg["mallShareTitle"],
            'desc'=>WSTConf('CONF.mallName'),
            'link'=>url('wechat/index/index',array('shareUserId'=>base64_encode($userId)),true,true),
            'imgUrl'=>WSTConf('CONF.mallLogo')
        );
        // 删除敏感信息
        unset($user['loginSecret'],$user['loginPwd'],$user['payPwd']);
        // 分享信息
        $user['shareInfo'] = $shareInfo;
        // 域名
        $user['domain'] = $this->domain();
        return json_encode(WSTReturn('ok',1,$user));
    }
    /**
     * 获取用户列表
     */
    public function queryMemberUsers(){
        $this->checkAuth();
        $userId = model('app/index')->getUserId();
        $m = new DM();
        $rs = $m->queryMineUsers($userId);
        foreach ($rs['data'] as $key => $v) {
            $userPhoto = $v['userPhoto'];
            if (WSTMSubstr($userPhoto, 0, 4) != 'http') {
                $userPhoto = WSTMSubstr($userPhoto, 10, strlen($userPhoto));
            }
            $rs['data'][$key]['userPhoto'] = $userPhoto;
        }
        $rs['domain'] = $this->domain();
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 获取奖励列表
     */
    public function queryMemberAwards(){
        $this->checkAuth();
        $userId = model('app/index')->getUserId();
        $m = new DM();
        $rs = $m->queryUserAwards($userId);
        $user = $m->getUserInfo($userId);
        $rs['user'] = $user;
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
      *生成二维码
      *@param $sharerId分享者id
      */
    public function makeQrCode(){
        // 分享者id
        $sharerId = (int)input('sharerId');
        // 二维码宽高
        $qrcodeWH = 275;
        $savePath = WSTRootPath().DS.'upload'.DS.'shareImg'.DS;
        if(!is_dir($savePath)){
          // 判断文件夹是否存在，若不存在则创建
          mkdir($savePath,0777);
        }

        $fileName = $sharerId.'.png';// 用户id.png
        $filePath = $savePath.$fileName;
        $imgUrl = url('/','','',true).'upload'.DS.'shareImg'.DS.$fileName;

        if(is_file($filePath))return json_encode(WSTReturn('ok',1,$imgUrl));

        $qrcodeInfo = url('mobile/index/index','','',true).'?sharerId='.base64_encode((string)$sharerId);
        $url = 'http://pan.baidu.com/share/qrcode?w='.$qrcodeWH.'&h='.$qrcodeWH.'&url='.urlencode($qrcodeInfo);

        $flag = file_put_contents($filePath, file_get_contents($url));
        if($flag!==false){
          return json_encode(WSTReturn('ok',1,$imgUrl));
        }
        return json_encode(lang('addon_member_code_fail'));
    }

    /**
     * 生成海报【手机】
     */
    public function appCreatePoster(){
        $userId = model('app/index')->getUserId();
        $isNew = (int)input("isNew",0);
        $subDir =  'upload/shares/member/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $outImg = $subDir.'/share_img_mo_'.$userId.'.png';
        $shareImg = WSTRootPath().'/'.$outImg;
        if($isNew==0){
            if (file_exists($shareImg)) {
                return json_encode(WSTReturn("",1,["shareImg"=>$outImg]));
            }
        }
        require Env::get('root_path') . 'extend/qrcode/phpqrcode.php';
        $m = new DM();
        $qr_url = url('wechat/Index/index',array('shareUserId'=>base64_encode($userId)),true,true);//二维码内容
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 10;//生成图片大小
        //生成二维码图片
        $qrcode = new \QRcode();
        $today = date("Ymd");
        $qr_code = WSTRootPath().'/'.$subDir.'/qrcode_mo_'.$today.'_'.$userId.'.png';
        $qrcode->png($qr_url, $qr_code, $errorCorrectionLevel, $matrixPointSize, 0);
        $rs = $m->createPoster($userId,$qr_code,$outImg);
        return json_encode($rs);
    }
}