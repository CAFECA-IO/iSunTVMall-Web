<?php
namespace addons\Getui\model;
use think\addons\BaseModel as Base;
use think\Db;
use Env;
class Getui extends Base{
    // 错误返回值说明：http://docs.getui.com/getui/server/php/errorcode/
    private $host = "http://sdk.open.api.igexin.com/apiex.htm";
    protected $appId;
    protected $appKey;
    protected $masterSecret;
    public $cId;
    // 标题
    public $title;
    // 内容
    public $content;
    public function initialize() {
        header("Content-Type: text/html; charset=utf-8");

        $config = Db::name('addons')->where(['name'=>'Getui'])->value('config');
        if(strlen($config)==0 ){
            // 未配置插件
            return;
        }
	    require_once Env::get('root_path') . 'addons/getui/sdk/' .'IGt.Push.php';
	    require_once Env::get('root_path') . 'addons/getui/sdk/' .'igetui/IGt.AppMessage.php';
	    require_once Env::get('root_path') . 'addons/getui/sdk/' .'igetui/IGt.TagMessage.php';
	    require_once Env::get('root_path') . 'addons/getui/sdk/' .'igetui/IGt.APNPayload.php';
	    require_once Env::get('root_path') . 'addons/getui/sdk/' .'igetui/template/IGt.BaseTemplate.php';
	    require_once Env::get('root_path') . 'addons/getui/sdk/' .'IGt.Batch.php';
	    require_once Env::get('root_path') . 'addons/getui/sdk/' .'igetui/utils/AppConditions.php';
	    require_once Env::get('root_path') . 'addons/getui/sdk/' .'igetui/template/notify/IGt.Notify.php';
	    require_once Env::get('root_path') . 'addons/getui/sdk/' .'igetui/IGt.MultiMedia.php';
        require_once Env::get('root_path') . 'addons/getui/sdk/' .'payload/VOIPPayload.php';
        require_once Env::get('root_path') . 'addons/getui/sdk/' .'igetui/template/IGt.RevokeTemplate.php';
        require_once Env::get('root_path') . 'addons/getui/sdk/' .'igetui/template/IGt.StartActivityTemplate.php';


        // 设置参数
        
        $config = json_decode($config, true);
        $this->appId = $config['appId'];
        $this->appKey = $config['appKey'];
        $this->masterSecret = $config['masterSecret'];
        $this->cId = input('userId'); // 用户id
        $this->title = input('title', lang('getui_tips4')); // 标题
        $this->content = input('content', '...'); // 内容
    }
    /**
     * 设置推送模板【android】
     */
    protected function IGtNotificationTemplateDemo(){
        $template =  new \IGtNotificationTemplate();
        $template->set_appId($this->appId);//应用appid
        $template->set_appkey($this->appKey);//应用appkey
        $template->set_transmissionType(1);//透传消息类型

        $template->set_transmissionContent('{"from":"wstmart"}');//透传内容

        $template->set_title($this->title);//通知栏标题
        $template->set_text($this->content);//通知栏内容
    //    $template->set_logo("http://wwww.igetui.com/logo.png");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
    //    $template->set_notifyId(123456789);
    //    $template->set_channel("set_channel");
    //    $template->set_channelName("set_channelName");
        $template->set_channelLevel(4);
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }
    /**
     * 设置推送模板【ios】
     * 参考链接：http://docs.getui.com/getui/server/php/template/?id=doc-title-0
     */
    private function IGtTransmissionTemplateDemo(){
        $template =  new \IGtTransmissionTemplate();
        $template->set_appId($this->appId);//应用appid
        $template->set_appkey($this->appKey);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent('{"from":"wstmart"}');//透传内容
    //  APN高级推送
    $apn = new \IGtAPNPayload();
    $alertmsg=new \DictionaryAlertMsg();
    $alertmsg->body = $this->content;
    // $alertmsg->actionLocKey="ActionLockey";
    // $alertmsg->locKey="LocKey";
    // $alertmsg->locArgs=array("locargs");
    $alertmsg->launchImage="launchimage";
    //  IOS8.2 支持
    $alertmsg->title = $this->title;

    


    // $alertmsg->titleLocKey="TitleLocKey";
    // $alertmsg->titleLocArgs=array("TitleLocArg");
    // $alertmsg->subtitle="ios推送子标题";
    // $alertmsg->subtitleLocKey="subtitleLocKey";
    // $alertmsg->subtitleLocArgs=array("subtitleLocArgs");

    $apn->alertMsg=$alertmsg;
    $apn->badge=0;
    // 不设置sound推送时没声音
    $apn->sound="";
    // 新增自定义消息
    $apn->add_customMsg("payload","{a:1, b:2, c:3}");
    $template->set_apnInfo($apn);

    return $template;
    }
    /**
     * 单推接口案例
     */
    public function pushMessageToSingle(){
        $data = Db::name('app_session')->where(['userId'=>$this->cId])
                                       ->field('platform, deviceId')
                                       ->find();
        if(empty($data))return false;
        $platform = $data['platform'];
        $isOnline = $this->getUserStatus($data['deviceId']);
        if($isOnline)return;
        // 当前设备离线则进行离线推送

        $igt = new \IGeTui($this->host, $this->appKey, $this->masterSecret);
        //消息模版：
        // 1.TransmissionTemplate:透传功能模板
        // 2.LinkTemplate:通知打开链接功能模板
        // 3.NotificationTemplate：通知透传功能模板
        // 4.NotyPopLoadTemplate：通知弹框下载功能模板
    
    //    	$template = IGtNotyPopLoadTemplateDemo();
    //    	$template = IGtLinkTemplateDemo();
        if($platform==3){
            $template = $this->IGtNotificationTemplateDemo(); // android端
        }else{
            $template = $this->IGtTransmissionTemplateDemo(); // ios端
        }


        
    //    $template = IGtTransmissionVOIPTemplateDemo();
    //    $template = SmsDemo();
        //个推信息体
        $message = new \IGtSingleMessage();
    
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
    //	$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new \IGtTarget();
        $target->set_appId($this->appId);
        // $target->set_clientId($this->cId);
        // 别名跟clientId二选一
        $target->set_alias($this->cId);
    
    
        try {
            $rep = $igt->pushMessageToSingle($message, $target);
            /**
             * result	String	请求结果，其他返回结果详见错误返回值
             *     成功时为"ok"
             * taskId	String	任务ID
             * status	String	推送结果 
             *      successed_offline 离线下发
             *      successed_online 在线下发
             *      successed_ignore 最近90天内不活跃用户不下发
             */
            if(isset($rep['result']) && $rep['result']==='ok')return true;
            return false;
        }catch(RequestException $e){
            $requstId =$e->getRequestId();
            $rep = $igt->pushMessageToSingle($message, $target, $requstId);
        }
        
    
    }

    /**
     * 用户状态查询
     * result	String	在离线情况，可选值有online和offline
     * isblack	boolean	是否是黑名单
     * lastLogin String	上次登录情况
     */
    public function getUserStatus($clientId) {
        /**
         * 返回值示例
         {
            "result":"Offline",
            "lastLogin":timestatmp,
            "isblack":false
         }
         */
        $igt = new \IGeTui($this->host, $this->appKey, $this->masterSecret);
        $rep = $igt->getClientIdStatus($this->appId, $clientId);
        return (strtolower($rep['result'])==='online');
    }

    // 安装
    public function install(){
        $hooks = ["pushNotificationByThirdParty"];
        $this->bindHoods("Getui", $hooks);
    }
    // 卸载
    public function uninstall(){
        $hooks = ["pushNotificationByThirdParty"];
        $this->unbindHoods("Getui", $hooks);
    }
}