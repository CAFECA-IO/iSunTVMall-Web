<?php
namespace addons\txlive\model;
use think\addons\BaseModel as Base;
use addons\txlive\validate\TxLives as validate;
use think\Db;
use Env;
require_once Env::get('root_path') . '/vendor/autoload.php';
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\VodClient;
use TencentCloud\Vod\V20180717\Models\SearchMediaRequest;
use TencentCloud\Vod\V20180717\Models\DeleteMediaRequest;
use addons\txlive\model\TxLive;
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
 * 腾讯云直播业务处理
 */
class TxLives extends Base{
    /**
     * 分页
     */
    public function pageQuery($sId=0){
        $key = input('key');
        $name = input('name');
        $shopName = input('shopName','');
        $where[] = ['txl.dataFlag','=',1];
        if($key!='')$where[] = ['txl.roomName','like','%'.$key.'%'];
        if($name!='')$where[] = ['u.loginName|u.userName','like','%'.$name.'%'];
        if($shopName!='')$where[] = ['s.shopName','like','%'.$shopName.'%'];
        if($sId>0)$where[] = ['txl.shopId','=',$sId];
        $page = Db::name('tx_lives')
            ->alias('txl')
            ->join('__USERS__ u','txl.userId=u.userId','left')
            ->join('__SHOPS__ s','txl.shopId=s.shopId','left')
            ->where($where)->field('txl.*,u.loginName,u.userName,s.shopName')->order('txl.id desc')->paginate(input('post.limit/d'))->toArray();
        if(count($page['data'])>0){
            foreach ($page['data'] as $key => $v){
                $page['data'][$key]['anchorName'] = $v['userName']?$v['userName']:$v['loginName'];
                $page['data'][$key]['liveStatusText'] = WSTTxLiveStatus($v['liveStatus']);
            }
        }
        return $page;
    }

    /**
     * 删除直播间，同时删除腾讯云的云点播回放视频
     */
    public function del($sId=0){
        $id = (int)input('id');
        $where = [];
        $where[] = ['id','=',$id];
        if($sId>0)$where[] = ['shopId','=',$sId];
        $m = new TxLive();
        $config = $m->getAddonConfig();
        $replays = Db::name('tx_live_replays')->where(['roomId'=>$id])->column("fileId");
        $errorFlag = false;
        $errorMsg = '';
        Db::startTrans();
        try{
            $result = Db::name('tx_lives')->where($where)->update(['dataFlag'=>-1]);
            if(false !== $result){
                Db::name('tx_live_goods')->where(['roomId'=>$id])->update(['dataFlag'=>-1]);
                if($config['liveReplay']==1 && !empty($replays)){
                    $secretId = isset($config['secretId'])?$config['secretId']:'';
                    $secretKey = isset($config['secretKey'])?$config['secretKey']:'';
                    if($secretId == '' || $secretKey == '')return WSTReturn(lang('txlive_operation_fail_input_params'),-1);
                    $cred = new Credential($secretId, $secretKey);
                    $httpProfile = new HttpProfile();
                    $httpProfile->setEndpoint("vod.tencentcloudapi.com");
                    $clientProfile = new ClientProfile();
                    $clientProfile->setHttpProfile($httpProfile);
                    $client = new VodClient($cred, "ap-guangzhou", $clientProfile);
                    $req = new DeleteMediaRequest();
                    foreach($replays as $key => $v){
                        $params = "{\"FileId\":\"".$v."\"}";
                        $req->fromJsonString($params);
                        $resp = $client->DeleteMedia($req);
                        $rs = $resp->toJsonString();
                        $rs = json_decode($rs,true);
                        if(isset($rs['Error'])){
                            $errorFlag = true;
                            switch ($rs['Error']['Code']){
                                case 'FailedOperation.InvalidVodUser':
                                    $errorMsg = lang('txlive_del_replay_fail_reason1');
                                    break;
                                case 'InternalError':
                                    $errorMsg = lang('txlive_del_replay_fail_reason2');
                                    break;
                                case 'ResourceNotFound':
                                    $errorMsg = lang('txlive_del_replay_fail_reason3');
                                    break;
                                case 'UnauthorizedOperation':
                                    $errorMsg = lang('txlive_del_replay_fail_reason4');
                                    break;
                            }
                        }
                    }
                    Db::name('tx_live_replays')->where(['roomId'=>$id])->update(['dataFlag'=>-1]);
                }
                Db::commit();
                if($errorFlag == true){
                    return WSTReturn(lang("txlive_del_replay_fail").$errorMsg,1);
                }
                return WSTReturn(lang('txlive_operation_success'),1);
            }
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('txlive_operation_fail'));
    }

    /*
     * 获取直播间信息
     */
    public function getById($uId=0){
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $id = (int)input('roomId',0);
        $type = input('type','live');
        $rs = $this->where(['id'=>$id])->find();
        if(empty($rs))return [];
        // 观看人数
        if($rs['liveStatus']==101 && $type=='live')$this->where(['id'=>$id])->setInc('visitorNum',1);
        $rs = $this->where(['id'=>$id])->find();
        $shop = Db::name('shops')->where(['shopId'=>$rs['shopId']])->field('shopId,shopName,shopImg')->find();
        $shopId = $shop['shopId'];
        $shop['shopImg'] = WSTUserPhoto($shop['shopImg'],true);
        $user = Db::name('users')->where(['userId'=>$userId])->field('loginName,userName')->find();
        $rs['loginName'] = $user['loginName'];
        $rs['userName'] = $user['userName'];
        // 群公告
        $addon = Db::name('addons')->where("name","Txlive")->field("config")->find();
        $config = json_decode($addon["config"],true);
        $rs['groupNotice'] = $config['IMNotice'];
        $rs['liveIM'] = $config['liveIM'];
        $rs['txIMUserSig'] = '';
        // 进入直播间要登录腾讯IM，防止一些用户没有重新登录小程序导致无法聊天
        if($config['liveIM']==1){
            $IMAppID = $config['IMAppID'];
            $IMSecretKey = $config['IMSecretKey'];
            if($IMAppID != '' || $IMSecretKey != ''){
                $loginName = $user['loginName'];
                $api = new \Tencent\TLSSigAPIv2($IMAppID, $IMSecretKey);
                $sig = $api->genSig($loginName);
                $rs['txIMUserSig'] = $sig;
            }
        }
        //关注
        $f = model('common/ShopMembers');
        $shop['isFollow'] = $f->checkFavorite($shopId,$userId);
        $rs['shop'] = $shop;
        $rs['goods'] = Db::name('tx_live_goods')->alias('txlg')->join('__GOODS__ g','txlg.goodsId = g.goodsId','left')->where(['txlg.roomId'=>$id,'txlg.dataFlag'=>1])->field('g.goodsId,g.goodsName,g.goodsImg,g.shopPrice')->select();
        return $rs;
    }

    /*
     * 获取直播间信息商品
     */
    public function getLiveGoods(){
        $id = (int)input('roomId',0);
        $rs = Db::name('tx_live_goods')->alias('txlg')->join('__GOODS__ g','txlg.goodsId = g.goodsId','left')->where(['txlg.roomId'=>$id,'txlg.dataFlag'=>1])->field('g.goodsId,g.goodsName,g.goodsImg,g.shopPrice')->select();
        return $rs;
    }

    /**
     * 获取推流地址
     * 如果不传key和过期时间，将返回不含防盗链的url
     * @param domain 您用来推流的域名
     *        streamName 您用来区别不同推流地址的唯一流名称
     *        key 推流安全密钥
     *        time 过期时间 sample 2016-11-12 12:00:00
     * @return String url
     */
    public function getPushUrl($domain, $streamName, $key = null, $time = null){
        if($key && $time){
            $txTime = strtoupper(base_convert(strtotime($time),10,16));
            $txSecret = md5($key.$streamName.$txTime);
            $ext_str = "?".http_build_query(array(
                    "txSecret"=> $txSecret,
                    "txTime"=> $txTime
                ));
        }
        return "rtmp://".$domain."/live/".$streamName . (isset($ext_str) ? $ext_str : "");
    }

    /**
     * 获取播流地址(rtmp,flv,hls三种格式)
     * 如果不传key和过期时间，将返回不含防盗链的url
     * @param domain 您用来播流的域名
     *        streamName 您用来区别不同播流地址的唯一流名称
     *        key 播流安全密钥
     *        time 过期时间 sample 2016-11-12 12:00:00
     *        validTime 有效时间，仅支持整数
     * @return array url
     */
    public function getLiveUrls($domain, $streamName, $key = null, $time = null,$validTime=0){
        if($key && $time){
            $txTime = strtoupper(base_convert((strtotime($time)-$validTime),10,16));
            $txSecret = md5($key.$streamName.$txTime);
            $ext_str = "?".http_build_query(array(
                    "txSecret"=> $txSecret,
                    "txTime"=> $txTime
                ));
        }
        $rtmpUrl = 'rtmp://'.$domain.'/live/'.$streamName. (isset($ext_str) ? $ext_str : "");
        $flvUrl = 'http://'.$domain.'/live/'.$streamName.'.flv'. (isset($ext_str) ? $ext_str : "");
        $hlsUrl = 'http://'.$domain.'/live/'.$streamName.'.m3u8'. (isset($ext_str) ? $ext_str : "");
        return ['rtmpUrl'=>$rtmpUrl,'flvUrl'=>$flvUrl,'hlsUrl'=>$hlsUrl];
    }

    /*
     * 获取直播流名称
     */
    public function getStreamName(){
        $chars = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",'0','1','2','3','4','5','6','7','8','9');
        $streamName = '';
        for($i=0;$i<8;$i++){
            $rand = rand(0,35);
            $streamName .= $chars[$rand];
        }
        $streamName = 'wst_'.$streamName;
        $crs = $this->checkStreamNameKey($streamName);
        if($crs['status']==1)return $streamName;
        return '';
    }

    /**
     * 检测流名称是否可用
     */
    function checkStreamNameKey($val){
        if($val=='')return WSTReturn(lang("txlive_stream_name_not_empty"));
        $dbo = $this->where([["streamName",'=',$val],['dataFlag','=',1]]);
        $rs = $dbo->count();
        if($rs==0){
            return WSTReturn(lang("txlive_stream_name_can_use"),1);
        }
        return WSTReturn(lang("txlive_stream_name_is_exist"));
    }

    /**
     * 新增直播间申请
     */
    public function add($uId=0,$sId=0,$config=[]){
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $shopId = ($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $data = input('post.');
        $goodsIds = array_filter(explode(',',$data['useObjectIds']));
        WSTUnset($data,'tokenId,useObjectIds,lang');
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['userId'] = $userId;
        $data['shopId'] = $shopId;
        $pushDomain = isset($config['pushDomain'])?$config['pushDomain']:'';
        $liveDomain = isset($config['liveDomain'])?$config['liveDomain']:'';
        $pushKey = isset($config['pushKey'])?$config['pushKey']:'';
        $liveKey = isset($config['liveKey'])?$config['liveKey']:'';
        $validTime = isset($config['validTime'])?$config['validTime']:0;
        $licenceUrl = isset($config['licenceUrl'])?$config['licenceUrl']:'';
        $licenceKey = isset($config['licenceKey'])?$config['licenceKey']:'';
        if($pushDomain==''||$liveDomain==''||$pushKey==''||$liveKey==''||$licenceUrl==''||$licenceKey=='')return WSTReturn(lang('txlive_add_fail_config_params_first'));
        $streamName = $this->getStreamName();
        if($streamName=='')return WSTReturn(lang("txlive_add_fail_try_later"));//分派不了流名称
        $data['streamName'] = $streamName;
        $pushUrl = $this->getPushUrl($pushDomain,$streamName,$pushKey,$data['expireTime']);
        $data['pushUrl'] = $pushUrl;
        $liveUrls = $this->getLiveUrls($liveDomain,$streamName,$liveKey,$data['expireTime'],$validTime);
        $data['rtmpUrl'] = $liveUrls['rtmpUrl'];
        $data['flvUrl'] = $liveUrls['flvUrl'];
        $data['hlsUrl'] = $liveUrls['hlsUrl'];
        $data['liveStatus'] = 101;
        Db::startTrans();
        try{
            $validate = new validate();
            if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
            $roomId = $this->allowField(true)->insertGetId($data);
            if(false !== $roomId){
                if(count($goodsIds)>0){
                    for($i=0;$i<count($goodsIds);$i++){
                        $goodsData = [
                            'roomId'=>$roomId,
                            'goodsId'=>$goodsIds[$i],
                            'createTime'=>date('Y-m-d H:i:s')
                        ];
                        Db::name('tx_live_goods')->insert($goodsData);
                    }
                }
                WSTClearAllCache();
                WSTUseResource(0, $roomId, $data['coverImg']);
                Db::commit();
                return WSTReturn(lang("txlive_operation_success"), 1,$roomId);
            }
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('txlive_operation_fail'));
    }

    /*
     * 将直播间状态改为已结束
     */
    public function changeLiveStatus(){
        $roomId = input('roomId');
        $rs = $this->where(['id'=>$roomId])->update(['liveStatus'=>103]);
        return $rs;
    }

    /*
     * 查询直播间列表
     */
    public function roomPageQuery(){
        $keyword = input('keyword');
        $where[] = ['dataFlag','=',1];
        //$where[] = ['liveStatus','=',101]; // 只显示直播中的房间
        if($keyword!='')$where[] = ['roomName','like','%'.$keyword.'%'];
        $page = Db::name('tx_lives')->where($where)->order('id desc')->paginate(input('pagesize/d'))->toArray();
        if(count($page['data'])>0){
            foreach ($page['data'] as $key => $v){
                $user = Db::name('users')->where(['userId'=>$v['userId']])->field('userName,loginName,userPhoto')->find();
                $page['data'][$key]['anchorName'] = $user['userName']?$user['userName']:$user['loginName'];
                $page['data'][$key]['userPhoto'] = WSTUserPhoto($user['userPhoto'],true);
                $page['data'][$key]['goods'] = Db::name('tx_live_goods')->alias('txlg')->join('__GOODS__ g','txlg.goodsId = g.goodsId','left')->where(['txlg.roomId'=>$v['id'],'txlg.dataFlag'=>1])->field('g.goodsId,g.goodsName,g.goodsImg,g.shopPrice')->select();
                $mediaUrlRes = Db::name('tx_live_replays')->where(['roomId'=>$v['id']])->find();
                $page['data'][$key]['hasReplay'] = (!empty($mediaUrlRes))?true:false;
            }
        }
        return WSTReturn('',1,$page);
    }

    /*
     * 获取直播间的直播状态
     */
    public function getLiveStatus(){
        $roomId = input('roomId');
        $rs = $this->where(['id'=>$roomId])->value('liveStatus');
        return $rs;
    }

    /**
     * 查询商品
     */
    public function searchGoods($sId=0){
        $shopId=($sId==0)?(int)session('WST_USER.shopId'):$sId;
        $shopCatId1 = (int)input('post.shopCatId1');
        $shopCatId2 = (int)input('post.shopCatId2');
        $goodsName = input('post.goodsName');
        $where =[];
        $where[] =['goodsStatus','=',1];
        $where[] =['dataFlag','=',1];
        $where[] =['isSale','=',1];
        $where[] =['shopId','=',$shopId];
        if($shopCatId1>0)$where[] =['shopCatId1','=',$shopCatId1];
        if($shopCatId2>0)$where[] =['shopCatId2','=',$shopCatId2];
        if($goodsName !='')$where[] = ['goodsName', 'like', '%'.$goodsName.'%'];
        $rs = Db::name('goods')
            ->alias('g')
            ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
            ->where($where)->field('gl.goodsName,g.goodsId,marketPrice,shopPrice,goodsSn,goodsImg')->select();
        return WSTReturn('',1,$rs);
    }

    /*
     * 编辑直播商品
     */
    public function editLiveGoods(){
        $roomId = input('roomId');
        $goodsIds = input('goodsIds');
        Db::name('tx_live_goods')->where(['roomId'=>$roomId])->delete();
        for($i=0;$i<count($goodsIds);$i++){
            $goodsData = [
                'roomId'=>$roomId,
                'goodsId'=>$goodsIds[$i],
                'createTime'=>date('Y-m-d H:i:s')
            ];
            Db::name('tx_live_goods')->insert($goodsData);
        }
        return WSTReturn('success',1);
    }

    /*
     * 获取直播回放视频（请求腾讯云接口）
     */
    public function getLiveReplays($config=[]){
        $secretId = isset($config['secretId'])?$config['secretId']:'';
        $secretKey = isset($config['secretKey'])?$config['secretKey']:'';
        if($secretId == '' || $secretKey == '')return WSTReturn(lang('txlive_operation_fail_input_params'),-1);
        $rooms = $this->where(['liveStatus'=>103,'dataFlag'=>1])->select();
        Db::startTrans();
        try{
            $cred = new Credential($secretId, $secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("vod.tencentcloudapi.com");
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new VodClient($cred, "ap-guangzhou", $clientProfile);
            $req = new SearchMediaRequest();
            foreach($rooms as $key => $v){
                $mediaUrlRes = Db::name('tx_live_replays')->where(['roomId'=>$v['id']])->find();
                if(empty($mediaUrlRes)){
                    $params = "{\"StreamId\":\"".$v['streamName']."\"}";
                    $req->fromJsonString($params);
                    $resp = $client->SearchMedia($req);
                    $replays = $resp->toJsonString();
                    $replays = json_decode($replays,true);
                    if(count($replays['MediaInfoSet'])>0){
                        foreach($replays['MediaInfoSet'] as $key2 => $v2){
                            $expireTime = date('Y-m-d H:i:s', strtotime($v2['BasicInfo']['ExpireTime']));
                            // 处理永久保存视频的到期时间
                            if(strpos($v2['BasicInfo']['ExpireTime'],'9999-12-31')!==false){
                                $expireTime = '9999-12-31 23:59:59';
                            }
                            $replayData = [
                                'roomId' => $v['id'],
                                'startTime' => date('Y-m-d H:i:s', strtotime($v2['BasicInfo']['CreateTime'])),
                                'expireTime' => $expireTime,
                                'mediaUrl' => $v2['BasicInfo']['MediaUrl'],
                                'createTime' => date('Y-m-d H:i:s'),
                                'fileId' => $v2['FileId']
                            ];
                            Db::name('tx_live_replays')->insert($replayData);
                        }
                    }
                }
            }
            Db::commit();
            return WSTReturn(lang("txlive_operation_success"), 1);
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('txlive_operation_fail'));
    }

    /*
     * 删除直播回放视频（请求腾讯云接口）
     */
    public function delLiveReplays($config=[]){
        $secretId = isset($config['secretId'])?$config['secretId']:'';
        $secretKey = isset($config['secretKey'])?$config['secretKey']:'';
        if($secretId == '' || $secretKey == '')return WSTReturn(lang('txlive_operation_fail_input_params'),-1);
        $rooms = $this->where(['liveStatus'=>103,'dataFlag'=>1])->select();
        Db::startTrans();
        try{
            $cred = new Credential($secretId, $secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("vod.tencentcloudapi.com");
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new VodClient($cred, "ap-guangzhou", $clientProfile);
            $req = new SearchMediaRequest();
            foreach($rooms as $key => $v){
                $mediaUrlRes = Db::name('tx_live_replays')->where(['roomId'=>$v['id']])->find();
                if(empty($mediaUrlRes)){
                    $params = "{\"StreamId\":\"".$v['streamName']."\"}";
                    $req->fromJsonString($params);
                    $resp = $client->SearchMedia($req);
                    $replays = $resp->toJsonString();
                    $replays = json_decode($replays,true);
                    if(count($replays['MediaInfoSet'])>0){
                        foreach($replays['MediaInfoSet'] as $key2 => $v2){
                            $expireTime = date('Y-m-d H:i:s', strtotime($v2['BasicInfo']['ExpireTime']));
                            // 处理永久保存视频的到期时间
                            if(strpos($v2['BasicInfo']['ExpireTime'],'9999-12-31')!==false){
                                $expireTime = '9999-12-31 23:59:59';
                            }
                            $replayData = [
                                'roomId' => $v['id'],
                                'startTime' => date('Y-m-d H:i:s', strtotime($v2['BasicInfo']['CreateTime'])),
                                'expireTime' => $expireTime,
                                'mediaUrl' => $v2['BasicInfo']['MediaUrl'],
                                'createTime' => date('Y-m-d H:i:s'),
                                'fileId' => $v2['FileId']
                            ];
                            Db::name('tx_live_replays')->insert($replayData);
                        }
                    }
                }
            }
            Db::commit();
            return WSTReturn(lang("txlive_operation_success"), 1);
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('txlive_operation_fail'));
    }

    /*
     * 获取直播间回放视频
     */
    public function getRoomReplays(){
        $roomId = (int)input('roomId');
        $mediaUrls = Db::name('tx_live_replays')->where(['roomId'=>$roomId,'dataFlag'=>1])->order('id desc')->column('mediaUrl');
        return ($mediaUrls!='')?$mediaUrls:'';
    }

    /*
     * 点赞
     */
    public function giveLike(){
        $roomId = (int)input('roomId');
        Db::startTrans();
        try{
            $result = Db::name('tx_lives')->where(['id'=>$roomId,'dataFlag'=>1])->setInc('likeNum',1);
            if(false !== $result){
                Db::commit();
                return WSTReturn(lang('txlive_operation_success'),1);
            }
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('txlive_operation_fail'));
    }

    /*
     * 记录腾讯IM用户的userId
     */
    public function addUserLoginLog($loginName){
        $data = [
            'userId'=>$loginName,
            'createTime'=>date('Y-m-d H:i:s')
        ];
        Db::name('tx_live_user_login_logs')->insert($data);
    }
}
