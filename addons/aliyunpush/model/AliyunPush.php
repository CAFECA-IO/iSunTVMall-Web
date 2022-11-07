<?php
namespace addons\aliyunpush\model;
use think\addons\BaseModel as Base;
use think\Db;
use Env;
require_once Env::get('root_path') . 'addons/aliyunpush/sdk/autoload.php';
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
class AliyunPush extends Base{
    protected $accessKeyId;
    protected $accessKeySecret;
    protected $androidAppKey;
    protected $androidPkgName;
    protected $iosAppKey;
    protected $iOSApnsEnv;
    public $cId;
    // 标题
    public $title;
    // 内容
    public $content;
    public function initialize() {
        // 设置参数
        $config = Db::name('addons')->where(['name'=>'Aliyunpush'])->value('config');
        if(strlen($config)==0 ){
            // 未配置插件
            return;
        }
        $config = json_decode($config, true);
        $this->accessKeyId = $config['accessKeyId'];
        $this->accessKeySecret = $config['accessKeySecret'];
        $this->androidAppKey = $config['androidAppKey'];
        $this->androidPkgName = $config['androidPkgName'].".MainActivity";
        $this->iosAppKey = $config['iosAppKey'];
        $this->iOSApnsEnv = $config['iOSApnsEnv'];

        AlibabaCloud::accessKeyClient($this->accessKeyId, $this->accessKeySecret)
                        ->regionId('cn-guangzhou')
                        ->asDefaultClient();


        $this->userId = input('userId'); // 苹果
        $this->title = input('title', '新消息'); // 标题
        $this->content = input('content', '...'); // 内容
    }
    /**
     * 设置推送模板【android】
     */
    private function androidTemplate(){
        // ---- ANDROID端推送参数
        return [
        'AppKey' => $this->androidAppKey,
        // 设备类型 ANDROID|IOS
        'DeviceType' => "ANDROID",
        'AndroidNotifyType' => "BOTH",
        'AndroidOpenType' => "APPLICATION",
        'AndroidPopupActivity' => $this->androidPkgName,
        // 厂商推送标题
        'AndroidPopupTitle' => $this->title,
        // 厂商推送内容
        'AndroidPopupBody' => $this->content ,
        'AndroidNotificationBarPriority' => "2",
        // 厂商推送附加参数
        'AndroidExtParameters' => "{a:1,b:2}",
        'AndroidRemind' => "true",
        // 推送通道必须，不设置不显示通知
        'AndroidNotificationChannel' => "1"
        ];
    }
    /**
     * 设置推送模板【ios】
     */
    private function iosTemplate(){
         // --- IOS端推送参数
        return [
            'AppKey' => $this->iosAppKey,
            'DeviceType' => "iOS",
            // DEV：表示开发环境 PRODUCT：表示生产环境
            'iOSApnsEnv' => $this->iOSApnsEnv,
            // 消息推送时设备不在线（既与移动推送的服务端的长连接通道不通），则这条推送会做为通知，通过苹果的APNs通道送达一次。注意：离线消息转通知仅适用于生产环境
            'iOSRemind' => "true",
            // IOS消息转通知时使用的iOS通知内容，仅当iOSApnsEnv=PRODUCT && iOSRemind为true时有效
            'iOSRemindBody' => $this->content,
            // 角标数【角标自增与角标数不能同时设置】
            // 'iOSBadge' => "2",
            // 角标自增 是否开启角标自增功能，默认为False，当该项为True时，iOSBadge必须为为空。角标自增功能由推送服务端维护每个设备的角标计数，需要用户使用1.9.5以上版本的sdk，并且需要用户主动同步角标数字到服务端。
            'iOSBadgeAutoIncrement' => "true",
            // 静默通知
            'iOSSilentNotification' => "false",
            // 推送副标题
            // 'iOSSubtitle' => "ios推送副标题",
            // 附加参数 注意 : 该参数要以json map的格式传入,否则会解析出错
            'iOSExtParameters' => "{a:1, b:2}",
        ];
    }
    /**
     * 单推接口案例
     * @param ignoreOnline boolean 是否忽略设备在线状态 
     * 错误代码参考：https://help.aliyun.com/document_detail/48054.html?spm=a2c4g.11186623.2.21.51c8650c5Hb7qW
     */
    public function pushMessageToSingle($ignoreOnline=false){
        $data = Db::name('app_session')->where(['userId'=>$this->userId])
                                       ->field('platform, deviceId')
                                       ->find();
        if(empty($data))return false;
        $platform = $data['platform'];
        $ignoreOnline = $platform==4; // ios端直接推送
        if(!$ignoreOnline){
            // 忽略在线状态
            $isOnline = $this->isDeviceOnline($data['deviceId'], $platform);
            trace('···设备在线状态``'.(int)$isOnline, 'error');
            if($isOnline)return;
        }

        // 当前设备离线则进行离线推送
        $template = [];
        if($platform==3){
            $template = $this->androidTemplate(); // android端
        }else{
            $template = $this->iosTemplate(); // ios端
        }
        $query = [
            // 推送类型：NOTICE通知（推送） MESSAGE(透传消息)
            'PushType' => "NOTICE",
            // 推送对象为 别名
            'Target' => "ALIAS",
            // 别名
            'TargetValue' => $this->userId,
            // 厂商推送
            'Title' => $this->title,
            // 厂商推送
            'Body' => $this->content,
            // 离线保存【使用厂商推送必须设置为true】
            'StoreOffline' => "true",
        ];
        $query = array_merge($query, $template);

        try {
            $result = AlibabaCloud::rpc()
                                  ->product('Push')
                                  // ->scheme('https') // https | http
                                  ->version('2016-08-01') // 必须指定
                                  ->action('Push')
                                  ->method('POST')
                                  ->host('cloudpush.aliyuncs.com')
                                  ->options(['query' => $query])
                                  ->request();
            // print_r($result->toArray());
        } catch (ClientException $e) {
            // dump($e);
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            // dump($e);
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }

    /**
     * 设备状态查询
     * @param deviceId 设备id
     成功返回：
     {
        "RequestId": "AB729F13-1DF2-496A-9ED1-F9D43CDC8828",
        "DeviceInfo": {
            "Tags": "",
            "DeviceId": "f0964577f96b490f8da568bc3af61e43",
            "PushEnabled": true,
            "Alias": "1",
            "LastOnlineTime": "2019-12-04T02:06:21Z",
            "Online": false,
            "DeviceType": "Android"
        }
    }
    失败返回：
    {
        "RequestId": "5CC2008F-2B84-4166-8DBD-AB77225EBA70",
        "HostId": "cloudpush.aliyuncs.com",
        "Code": "InvalidDeviceId.NotFound",
        "Message": "The specified DeviceId is not found."
    }
     */
    public function isDeviceOnline($deviceId, $platform) {
        $appKey = $platform==3?$this->androidAppKey:$this->iosAppKey;
        try {
            $result = AlibabaCloud::rpc()
                                ->product('Push')
                                // ->scheme('https') // https | http
                                ->version('2016-08-01')
                                ->action('QueryDeviceInfo')
                                ->method('POST')
                                ->host('cloudpush.aliyuncs.com')
                                ->options([
                                            'query' => [
                                                'RegionId' => "cn-guangzhou",
                                                'AppKey' => $appKey,
                                                'DeviceId' => $deviceId,
                                            ],
                                        ])
                                ->request();
            $result = $result->toArray();
            return (isset($result['DeviceInfo']) && $result['DeviceInfo']['Online']==true);
        } catch (ClientException $e) {
            // echo $e->getErrorMessage() . PHP_EOL;
            return true;
        } catch (ServerException $e) {
            // echo $e->getErrorMessage() . PHP_EOL;
            return true;
        }
        
    }
    // 安装
    public function install(){
        $hooks = ["pushNotificationByThirdParty"];
        $this->bindHoods("Aliyunpush", $hooks);
    }
    // 卸载
    public function uninstall(){
        $hooks = ["pushNotificationByThirdParty"];
        $this->unbindHoods("Aliyunpush", $hooks);
    }


}