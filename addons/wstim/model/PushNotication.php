<?php 
namespace addons\wstim\model;
use think\addons\BaseModel as Base;
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
 * 推送处理
 */

require_once Env::get('root_path') . 'addons/wstim/gatewayclient/Gateway.php';
use GatewayClient\Gateway;
// 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值(ip不能是0.0.0.0)
Gateway::$registerAddress = '127.0.0.1:1238';
class PushNotication extends Base{
    /**
     * 推送给单个用户
     * @param userId int 用户id
     * @param title string 推送标题
     * @param content string 推送内容
     */
    public function pushToSingle($params){
        $userId = $params['userId'];
        // 判断设备是否在线
        if(Gateway::isUidOnline($userId)){
            $message = ['type'=>'serverPushData',
                    'title'=>$params['title'],
                    'content'=>$params['content']];
            $message = json_encode($message);
            return Gateway::sendToUid($userId, $message);
        }
        // 不在线调用第三方推送
        hook('pushNotificationByThirdParty', ["userId"=>$userId, "content"=>$params["content"]]);

        
        
    }
    /**
     * 推送给所有用户
     */
    public function pushToAll(){
        
    }
}