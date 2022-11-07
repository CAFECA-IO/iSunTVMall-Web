<?php
namespace addons\wstim\controller;
use think\addons\Controller;
use addons\wstim\model\Chats as M;
// 店铺客服控制器
class ShopChats extends Controller{
    static private $isApp;
    static private $isWeapp;
    static private $userInfo;
    static function wstImg($img){
        return WSTUserPhoto($img, true);
    }
	// 确保用户登录之后【并且为客服】才能使用客服功能
    protected $beforeActionList = ['setUserInfo','checkService'];

    protected function setUserInfo(){
        self::$isApp = (int)input('isApp');
        self::$isWeapp = (int)input('isWeapp');
        $tokenId = input('tokenId');
        if($tokenId!=''){
            $userId = getUserId();
            $userData = model('common/users')->getFieldsById($userId,['loginName','userName','userPhoto']);
            $userData['userId'] = $userId;
            $shopInfo = getShopInfo($userId);
            $userData['shopId'] = $shopInfo['shopId'];
            $userData['shopName'] = $shopInfo['shopName'];
            $userData['shopImg'] = $shopInfo['shopImg'];
            $userData['serviceId'] = $userData['shopId']>0?$userId:'';
            $userData['isService'] = $userData['serviceId']>0;
            self::$userInfo = $userData;
        }else{
            self::$userInfo = session('WST_USER');
        }
    }
    protected function checkService(){
        $USER = self::$userInfo;
        if(!empty($USER) && isset($USER['isService']) && $USER['isService']==1){
            return true;
        }else{
            $isApp = input('isApp');
            $isWeapp = (int)input('isWeapp');
            if($isApp || $isWeapp==1){
                die(WSTImReturn(lang('wstim_wstim_no_permission')));
            }
            $this->redirect('shop/index/index');
            exit;
        }
    }





    /**
    * 获取用户信息
    */
    public function getUserInfo(){
        $m = new M();
        $rs = $m->getUserInfo();
        $rs['userPhoto'] = WSTImUserPhoto(isset($rs['userPhoto'])?$rs['userPhoto']:'');
        return $rs;
    }
    public function getBaseData(){
        $users = self::$userInfo;
        $data = [
            // 客服基础信息
            'userId'=>(int)$users['userId'],
            'shopId'=>(int)$users['shopId'],
            'serviceId'=>$users['serviceId'],
            'shopName'=>$users['shopName'],
            'shopImg'=>self::wstImg($users['shopImg']),

            // 客服名称
            'workerName'=>empty($users['userName'])?$users['loginName']:$users['userName'],
            // 客服头像
            'userPhoto'=>WSTImUserPhoto($users['userPhoto']),
            // 聊天服务器地址
            'server'=>chatServer()
        ];
        return WSTImReturn('ok',1,$data);
    }
    /**
	* 店铺客服主页
	*/
    public function index(){
        return $this->fetch('shop/chats/index');
    }
    // 最近会话列表
    public function getChatList(){
        $m = new M();
        $recent = $m->getShopRecent(self::$userInfo['shopId']);
        // 处理用户头像
        foreach ($recent as $k => $v) {
            $recent[$k]['userPhoto'] = self::wstImg($v['userPhoto']);
        }
        return WSTImReturn('ok',1,$recent);
    }
    /**
    * 设置为已读
    */
    public function setRead(){
        $m = new M();
        $rs = $m->setReadForService(self::$userInfo['serviceId']);
        if(self::$isApp || self::$isWeapp)return json_encode($rs);
        return $rs;
    }
	/**
	* 查找聊天记录
	*/
	public function pagequery(){
		$m = new M();
		$rs = $m->shopPagequery((int)input('userId'), self::$userInfo['serviceId']);
        foreach($rs['data'] as $k=>$v){
            // 过滤除a标签外的所有html标签
            $content = strip_tags(htmlspecialchars_decode($v['content']), '<img><a>');
            $contentDecode = json_decode($rs['data'][$k]['content'], true);
            // 若为图片则加上图片域名
            if($contentDecode['type']=='image'){
                $contentDecode['content'] = self::wstImg($contentDecode['content']);
                $content = json_encode($contentDecode);
            }
            $rs['data'][$k]['content'] = $content;
        }
        if(self::$isApp || self::$isWeapp)return json_encode($rs);
        return $rs;
	}
    /******************************* mobile *********************************/
    /**
    * 客服-最近会话列表
    */
    public function moChatList(){
        $msg = model('common/Messages')->getLastMsg();
        return $this->fetch('mobile/shopchats/chat_list', $msg);
    }
    /**
    * 检测是否为客服
    */
    public function isService(){
        $rs = model('chats')->isService();
        if($rs)return WSTImReturn('ok',1);
        return WSTImReturn('err');
    }
    /**
    * 留言【服务器未启动的情况】
    */
    public function sendMsg(){
        $m = new M();
        $rs = $m->sendOffLineMsg(self::$userInfo['serviceId']);
        if(self::$isApp || self::$isWeapp)return json_encode($rs);
        return $rs;
    }
    /**
    * 用户-最近会话列表
    */
    public function userChatList(){
        $m = new M();
        $rs = $m->getRecent();
        if(self::$isApp)return json_encode($rs);
        return $rs;
    }
    /******************************* wechat *********************************/
    /**
    * 客服-最近会话列表
    */
    public function wxChatList(){
        $userId = (int)session('WST_USER.userId');
        $userData = model('common/users')->getFieldsById($userId,['loginName','userName']);
        // 获取客服所属店铺id
        $m = new M();
        $shopId = $m->getShopId();
        if(empty($shopId))die(lang('wstim_it_doesn_belong_to_the_shop'));
        return $this->fetch('mobile/shopchats/chat_list');
    }

}
