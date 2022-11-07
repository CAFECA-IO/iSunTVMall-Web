<?php 
namespace addons\wstim\controller;
use think\addons\Controller;
use addons\wstim\model\Chats as M;
use think\Db;
class Chats extends Controller{
    static private $isApp;
    static private $isWeapp;
    static private $userInfo;
    static function wstImg($img){
        return WSTUserPhoto($img, true);
    }
    // 确保用户登录之后才能使用客服功能
    protected $beforeActionList = [
        'setUserInfo' => ['except'=>'getapis'],
        'checkAuth'   => ['except'=>'getapis']
    ];

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

    // 获取接口信息
    public function getApis(){
        $filePath = WST_ADDON_PATH.'wstim/Apis.json';
        $rs = file_get_contents($filePath);
        $rs = json_decode($rs,true);
        $_rs = [];
        $domain = url('/','','',true);
        $isApp = input('isApp');
        $isWeapp = input('isWeapp');
        foreach ($rs as $k => $v) {
            if($k=='imServer'){
                $_rs[$k] = $v['link'];
            }else{
                $_rs[$k] = $v['link'];
                if(!$isApp && !$isWeapp){
                    $_rs[$k] = $domain.$v['link'];
                }
            }
        }
        return WSTImReturn('ok',1,$_rs);
    }
    /**
     * 获取订单信息
     */
    public function getOrderInfo(){
        $data = (new M())->getOrderInfo(self::$userInfo['userId'], isset(self::$userInfo['shopId'])?self::$userInfo['shopId']:0);
        return WSTImReturn("ok", 1, $data);
    }

    /**
     * 获取商品图片
     */
    public function getChatGoods(){
        $goodsId = (int)input("id");
        $data = (new M())->getChatGoods($goodsId);
        return WSTImReturn("ok", 1, $data);
    }

     /**
     * 删除对话
     */
    public function deldialog(){
        $m = new M();
        $rs = $m->deldialog(self::$userInfo['userId']);
        if(self::$isApp==1 || self::$isWeapp==1)return json_encode($rs);
        return $rs;
    }
    public function listenerData(){
        $users = self::$userInfo;
        $userId = $users['userId'];
        $shopId = isset($users['shopId'])?$users['shopId']:0;
        $shopData = model('common/shops')->getFieldsById((int)$shopId,['shopImg','userId']);
        $shopImg = self::wstImg($shopData['shopImg']);;
        $data = [
            'serviceId'=>isset($users['serviceId'])?$users['serviceId']:'',
            'userId'=>(int)$userId,
            'loginName'=>$users['loginName'],
            'shopId'=>$shopId,
            'userPhoto'=>WSTImUserPhoto($users['userPhoto']),
            'shopImg'=>$shopImg,
            // 是否为客服
            'isService'=>isset($users['serviceId'])?(new M())->isService($users['serviceId']):false,
        ];
        return WSTImReturn('ok',1,$data);
    }
    // 获取连接客服系统的基础数据
    public function getBaseData(){
        $this->checkIsService();
        // 根据传过来的店铺id查询店铺资料
        $shopData = model('common/shops')->getFieldsById((int)input('shopId'),['shopName','shopImg','userId']);
        if(empty($shopData)){
            $shopData['shopName'] = "";
            $shopData['shopImg'] = "";
            $shopData['userId'] = 0;
        }
        $shopImg = self::wstImg($shopData['shopImg']);
        // 根据用户id和店铺id获取会话id

        $users = self::$userInfo;
        $userId = $users['userId'];
        if(isset($shopData['userId']) && $userId==$shopData['userId']){
             return WSTImReturn(lang('wstim_not_login'),-999);
        }
        $data = [
            'userId'=>(int)$userId,
            'userPhoto'=>WSTImUserPhoto($users['userPhoto']),
            'shopId'=>(int)input('shopId'),
            'shopName'=>$shopData['shopName'],
            'receiveId'=>$shopData['userId'],
            'shopImg'=>$shopImg,
            'loginName'=>$users['loginName'],
            // 聊天服务器地址
            'server'=>chatServer()
        ];
        // 正在浏览
        $goodsId = (int)input('goodsId');
        if($goodsId>0){
            $m = new M();
            $goods = $m->getGoodsInfo();
            $data['goods'] = $goods;
        }
        return WSTImReturn('ok',1,$data);
    }
	// addon/wstim-chats-index
	/**
	* 用户咨询客服页
	*/
    public function index(){
        return $this->fetch('home/chats/index');
    }
    // 最近会话列表
    public function getChatList(){
        $m = new M();
        $recent = $m->getRecent(self::$userInfo['userId']);
        // 处理用户头像
        foreach ($recent as $k => $v) {
            $recent[$k]['shopImg'] = self::wstImg($v['shopImg']);
        }
        return WSTImReturn('ok',1,$recent);
    }

    /**
    * 客服身份无法进入该页面【防止自己发送给自己】,仅针对自身所属店铺
    */
    protected function checkIsService(){
        $USER = self::$userInfo;
        if(!empty($USER) && isset($USER['isService']) && $USER['isService']==1){
            $userId = (int)$USER['userId'];
            $shopId = (int)input('shopId');
            $m = new M();
            $flag = $m->checkIsShopService($userId,$shopId);
            if(!empty($flag))$this->redirect('home/index/index');
        }
    }
	/**
	* 查询与店铺相关的浏览记录
	*/
	public function getHistory(){
		$m = new M();
        $rs = $m->getHistory((int)input('wap'));
        foreach ($rs as $k => $v) {
            $rs[$k]['goodsImg'] = self::wstImg($v['goodsImg']);
        }
		return WSTImReturn('ok',1,$rs);
	}
	/**
	* 查询与店铺相关的订单列表
	*/
	public function getOrderList(){
        $m = new M();
        $rs = $m->getOrderList(self::$userInfo['userId']);
        foreach ($rs['data'] as $k => $v) {
            foreach ($v['list'] as $k1 => $v1) {
                $rs['data'][$k]['list'][$k1]['goodsImg'] = self::wstImg($v1['goodsImg']);
            }
        }
        if(self::$isApp==1 || self::$isWeapp==1)return json_encode($rs);
		return $rs;
	}
	/**
	* 查找聊天记录
	*/
	public function pagequery(){
		$m = new M();
		$rs = $m->pagequery((int)input('receiveId'), self::$userInfo['userId']);
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
        if(self::$isApp==1 || self::$isWeapp==1)return json_encode($rs);
		return $rs;
	}
    /**
    * 主动推送消息
    */
    public function sendMsg(){
        $m = new M();
        $rs = $m->add(self::$userInfo['userId']);
        if(self::$isApp==1 || self::$isWeapp==1)return json_encode($rs);
        return $rs;
    }
    /************************************* mobile *****************************************/
    /**
    * 用户-最近会话列表
    */
    public function moChatList(){
        $msg = model('common/Messages')->getLastMsg();
        return $this->fetch('mobile/chats/chat_list', $msg);
    }
    public function moIndex(){
        return $this->fetch('mobile/chats/index');
    }
    /**
    * 设置为已读
    */
    public function setRead(){
        $m = new M();
        $rs = $m->setRead(self::$userInfo['userId']);
        if(self::$isApp==1 || self::$isWeapp==1)return json_encode($rs);
        return $rs;
    }
    

    /************************************* wechat *****************************************/
    /**
    * 用户-最近会话列表
    */
    public function wxChatList(){
        $USER = session('WST_USER');
         if(!empty($USER)){
             $isService = Db::name('shop_services')
                        ->where(['userId'=>$USER['userId'],'dataFlag'=>1])
                        ->find();
            if(!empty($isService)){
                $USER['isService'] = 1;
                $USER['shopId'] = $isService['shopId'];
                session('WST_USER',$USER);
                return $this->redirect(url('/addon/wstim-shopchats-wxChatlist'));
            }else{
                $USER['isService'] = 0;
                session('WST_USER',$USER);
            }
         }
        return $this->fetch('mobile/chats/chat_list');
    }
    public function wxIndex(){
        $this->checkIsService();
        // 根据传过来的店铺id查询店铺资料
        $shopData = model('common/shops')->getFieldsById((int)input('shopId'),['shopName','userId','shopImg']);
        // 根据用户id和店铺id获取会话id
        $userId = (int)session('WST_USER.userId');
        if($userId==$shopData['userId'])return $this->redirect(url('wechat/index/index'));
        if(empty($shopData))die(lang('wstim_invalid_shop'));
        

        return $this->fetch('mobile/chats/index');
    }

    /**
     * 离线推送
     */
    public function pushNotification(){
        // 推送离线消息【app】
        $a = hook('pushNotificationByThirdParty',['shopId'=>(int)input('to'),'content'=>input('content')]);
        return $a;
    }
}