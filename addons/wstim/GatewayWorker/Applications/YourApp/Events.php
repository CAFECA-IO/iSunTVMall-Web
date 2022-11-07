<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
date_default_timezone_set('Asia/Shanghai');
/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

// 引入mysql组件
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__.'/../../mysql-master/src/Connection.php';
use \GatewayWorker\Lib\Gateway;
use \Workerman\Worker;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{ 
    /**
     * 第三方推送url
     */
    protected static $notifyUrl = "";
    /**
    * 表前缀
    */
    protected static $prefix = null;
    /**
    * 客服最大接待用户数(以userId作为计数)
    */
    protected static $connectLimit = 10;
    /**
    * 保存在线用户
    */
    public static $clients = [];
    public static $uids = [];
    /**
     * 新建一个类的静态成员，用来保存数据库实例
     */
    public static $db = null;

    /**
     * 触发推送
     */
    public static function notification($data=[]){
      if(self::$notifyUrl=="")return;
      $http = new \Workerman\Http\Client();
      $http->post(self::$notifyUrl, $data, function($response){
          var_dump($response->getStatusCode());
      }, function($exception){
          echo $exception;
      });
    }

    /**
     * 进程启动后初始化数据库连接
     */
    public static function onWorkerStart($worker)
    { 
        $configs = require __DIR__.'/../../../workerman_config.php';
        // 推送url
        self::$notifyUrl = isset($configs['notify_url'])?$configs['notify_url']:'';
        // 表前缀
        self::$prefix = $configs['prefix'];
        self::$db = new \Workerman\MySQL\Connection($configs['host'], $configs['port'], $configs['user'], $configs['password'], $configs['db_name'], "utf8mb4");
        // 客服最大接待用户数
        if((int)$configs['connect_limit']>0){
          self::$connectLimit = $configs['connect_limit'];
        }
    }
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {   
        // 当有客户端连接时，将client_id返回，让mvc框架判断当前uid并执行绑定
        /*Gateway::sendToClient($client_id, json_encode(array(
            'type'      => 'init',
            'client_id' => $client_id
        )));*/
    }
    /**
     * 启用已删除的会话
     */
    static function enableDialog($where){
      // 设置userDel、shopDel为0
      self::$db->update(self::$prefix.'dialogs')
      ->cols(array('userDel'=>0,'shopDel'=>0))
      ->where($where)
      ->query();
    }

    /**
     * 自动回复
     * @param any $content 用户发送的内容
     */
    static function autoReply($content){
      $shopId = $_SESSION['shopId'];
      // 判断是否为json格式
      $data = json_decode($content, true);
      if ($data && (is_object($data)) || (is_array($data) && !empty($data))) {
        // json格式消息
        return false;
      }
      $keywords = self::$db->select('keyword')
                           ->from(self::$prefix.'auto_replys')
                           ->where("shopId={$shopId} AND dataFlag=1")
                           ->column();
      if(empty($keywords))return false;
      $pattern = join('|',$keywords);
      preg_match_all("/$pattern/", $content, $matches);
      if(isset($matches[0]) && empty($matches[0]))return false;
      $matches = array_unique($matches[0]);
      $matches = array_map(function($item){
        return "'$item'";
      },$matches);
      $matches = join(',',$matches);
      $replys = self::$db->select('replyContent')
                         ->from(self::$prefix.'auto_replys')
                         ->where("keyword in($matches) and shopId=$shopId and dataFlag=1")
                         ->column();
      return $replys;
    }

    /**
     * 聊天内容过滤
     * @param any $content 用户发送的内容
     */
    public static function filterContent($content){
      // 判断是否为json格式
      $data = json_decode($content, true);
      if ($data && (is_object($data)) || (is_array($data) && !empty($data))) {
        // json格式消息
        return $content;
      }
      // 纯文本消息
      $shopId = $_SESSION['shopId'];
      $disKeywords = self::$db->select('keywords')
                              ->from(self::$prefix.'disable_keywords')
                              ->single();
      if($disKeywords=='')return $content;
      // 执行过滤
      // 转为数组
      $disKeywords = explode(',',$disKeywords);
      // 过滤空值及空格
      $disKeywords = array_filter($disKeywords, function($item){
          $item = trim($item);
          return ($item!='' || strlen($item)>0);
      });
      // 设置分隔符
      $disKeywords = array_map(function($item){
          if($item!='')return "/$item/";
      },$disKeywords);
      return preg_replace($disKeywords,'*',$content);

    }


    /**
    * 获取排队人数
    * @param $shopId 店铺id
    */
    public static function getWaitCount($shopId){
      // 排队人数
      $clients = Gateway::getClientSessionsByGroup("wait_".$shopId);
      $userUids = [];
      foreach($clients as $k=>$v){
        $userUids[$v['uid']][] = $k;
      }
      return count($userUids);
    }
    /**
    * 当前客服接待人数
    * @param $serviceId 客服id(userId)
    */
    public static function getChatCount($serviceId){
      // 当前接待人数
      $w_clients = Gateway::getClientSessionsByGroup($serviceId);
      $u_clients = array();
      foreach($w_clients as $k=>$v){
          $u_clients[$v['uid']][] = $k;
      }
      // 客服已接待的人数
      return count($u_clients);
    }
    /**
    * 刷新店铺排队人数【用户进入(离开)排队状态时调用】
    * @param $shopId 店铺id
    */
    public static function refreshWaitCount($shopId){
      $data = array(
        'waitCount'=>self::getWaitCount($shopId)
      );
      $msg = array(
        'type'=>'serviceStatus',
        'data'=>$data
      );
      Gateway::sendToGroup('kf_'.$shopId,json_encode($msg));
    }
    /**
    * 当前客服接待人数【用户被接待(离开)时调用】
    * @param $serviceId 客服id(userId)
    */
    public static function refreshChatCount($serviceId){
      $data = array(
        'chatCount'=>self::getChatCount($serviceId)
      );
      $msg = array(
        'type'=>'serviceStatus',
        'data'=>$data
      );
      Gateway::sendToUid($serviceId,json_encode($msg));
    }
    /**
    * 获取用户(客服)的未读消息
    * @param $userId 用户id
    * @param $shopId 客服所属店铺id
    */
    public static function getUnReadNum($userId,$_shopId){
        // 1.获取与用户联系过的所有店铺id
        //  (1).先获取会话id
        /*$dialogIdArrs = Db::name('dialogs')->where(['userId'=>$userId])
                        ->group('shopId')
                        ->field('GROUP_CONCAT(id) id,shopId')
                        ->select();*/

        $sql = "SELECT GROUP_CONCAT(id) id,shopId FROM ".self::$prefix."dialogs WHERE userId='{$userId}' GROUP BY shopId";
        $dialogIdArrs = self::$db->query($sql);
        //  (2).根据会话id
        // 根据dialogId查询所有未读会话
        $result = array();
        // 作为用户身份的未读消息数
        $result['userUnRead'] = 0;
        foreach($dialogIdArrs as $k=>$v){
            $dialogIds = $v['id'];
            $shopId = $v['shopId'];
            /*$unReadArr = Db::name('dialog_contents')->field('content,dialogId')
                                ->where(['dialogId'=>['in',$dialogIds],'isRead'=>0])
                                ->select();*/
            $sql = "SELECT content,dialogId FROM ".self::$prefix."dialog_contents WHERE isRead=0 AND dialogId in ({$dialogIds})";
            $unReadArr = self::$db->query($sql);
            // 统计未读消息数
            $currUnReadNum = 0;
            foreach($unReadArr as $v){
              $content = unserialize($v['content']);
              if($content['from']!=$userId){// 将用户发送给客服的未读消息排除
                ++$result['userUnRead'];
                ++$currUnReadNum;
              }
            }
            if($currUnReadNum>0){
              // (3).有未读消息，则userDel不能为1
              // 设置userDel0
              self::$db->update(self::$prefix.'dialogs')
              ->cols(array('userDel'=>0))
              ->where("id in ({$dialogIds})")
              ->query();
            }
        }

        // 作为客服身份，店铺的未读消息数
        $result['serviceUnRead'] = 0;
        if($_shopId>0){
            // shopId查询dialogId【仅查询未读包含消息的dialogId】
            $sql = 'select GROUP_CONCAT(DISTINCT(d.id)) as dialogId from '.self::$prefix.'dialogs as d LEFT JOIN '.self::$prefix.'dialog_contents as dc ON dc.dialogId=d.id WHERE d.shopId='.$_shopId.' AND dc.isRead=0 GROUP BY d.userId';
            $dialogIds = self::$db->query($sql);
            if(!empty($dialogIds)){
                $_dialogIds = array();
                foreach($dialogIds as $v){
                    array_push($_dialogIds, $v['dialogId']);
                }
                $dialogIds = '('.implode(',',$_dialogIds).')';

                // 根据dialogId查询所有未读会话
                $unReadArr = self::$db->select('content,dialogId')
                                      ->from(self::$prefix.'dialog_contents')
                                      ->where("dialogId in {$dialogIds} AND isRead='0'")
                                      ->query();
                // --- 查询到店铺下的客服id，将客服发送给用户的未读消息排除
                $serviceId = self::$db->select('serviceId')
                                      ->from(self::$prefix.'shop_services')
                                      ->where("shopId={$_shopId} AND dataFlag=1")
                                      ->column();
                $serviceId = array_flip($serviceId);
                $_unReadArr = array();
                foreach($unReadArr as $v){
                  $content = unserialize($v['content']);
                  if(!isset($_unReadArr[$content['from']])){
                    $_unReadArr[$content['from']] = array(
                      'unReadNum'=>0,
                      'dialogIds'=>array()
                    );
                  }
                  ++$_unReadArr[$content['from']]['unReadNum'];
                  $_unReadArr[$content['from']]['dialogIds'][] = $v['dialogId'];
                }
                // 过滤【客服发送给用户的未读消息数】
                $_unReadArr = array_diff_key($_unReadArr,$serviceId);

                // 设置shopDel
                foreach($_unReadArr as $_k=>$_v){
                    if($_v['unReadNum']>0){
                        $_dialogIds = '('.implode(',',$_v['dialogIds']).')';
                        // (3).有未读消息，则shopDel不能为1
                        // 设置shopDel为0
                        self::$db->update(self::$prefix.'dialogs')
                        ->cols(array('shopDel'=>'0'))
                        ->where("id in {$_dialogIds}")
                        ->query();
                    }
                }
                $_unReadArr = array_column($_unReadArr,'unReadNum');
                $result['serviceUnRead'] =  array_sum($_unReadArr);
     
            }
        }
        return $result;
    }

    /**
    * 获取店铺下的所有未读留言
    */
    public static function getMessage(){
        $shopId = (int)$_SESSION['shopId'];
        // shopId查询dialogId【仅查询未读包含消息的dialogId】
        $sql = 'select GROUP_CONCAT(DISTINCT(d.id)) as dialogId from '.self::$prefix.'dialogs as d LEFT JOIN '.self::$prefix.'dialog_contents as dc ON dc.dialogId=d.id WHERE d.shopId='.$shopId.' AND dc.isRead=0 GROUP BY d.userId';
        $dialogIds = self::$db->query($sql);

        if(empty($dialogIds))return;

        $_dialogIds = array();
        foreach($dialogIds as $v){
            array_push($_dialogIds, $v['dialogId']);
        }
        $dialogIds = '('.implode(',',$_dialogIds).')';

        // 根据dialogId查询所有未读会话
        $unReadArr = self::$db->select('content,dialogId')
                              ->from(self::$prefix.'dialog_contents')
                              ->where("dialogId in {$dialogIds} AND isRead='0'")
                              ->query();
        // --- 查询到店铺下的客服id，将客服发送给用户的未读消息排除
        $serviceId = self::$db->select('serviceId')
                              ->from(self::$prefix.'shop_services')
                              ->where("shopId={$shopId} AND dataFlag=1")
                              ->column();
        $serviceId = array_flip($serviceId);
        $_unReadArr = array();
        // 180413过滤【已有客服接待的未读消息】
        $groupIndex = 'chat_'.$shopId;
        $currChatGroup = Gateway::getClientSessionsByGroup($groupIndex);
        // 待移除未读消息组
        $removeUids = array();
        if(!empty($currChatGroup)){
          $removeUids = array_column($currChatGroup, 'uid');
        }
        echo "当前客服组：\n\r";
        var_export($currChatGroup);
        var_export($removeUids);
        foreach($unReadArr as $v){
          // 过滤【已有客服接待的未读消息】
          $content = unserialize($v['content']);
          if(in_array($content['from'], $removeUids))continue;
          if(!isset($_unReadArr[$content['from']])){
            $_unReadArr[$content['from']] = array(
              'unReadNum'=>0,
              'dialogIds'=>array()
            );
          }
          ++$_unReadArr[$content['from']]['unReadNum'];
          $_unReadArr[$content['from']]['dialogIds'][] = $v['dialogId'];
        }
        // 过滤【客服发送给用户的未读消息数】
        $_unReadArr = array_diff_key($_unReadArr,$serviceId);

        // 查询用户信息
        $userIds = array_keys($_unReadArr);
        $rs = array();

        $todayStart = strtotime(date('Y-m-d'));
        $todayEnd = strtotime(date('Y-m-d 23:59:59'));
        foreach($userIds as $userId){
          $userInfo = [
            'loginName'=>'',
            'userPhoto'=>''
          ];
          $userInfo['unReadNum'] = $_unReadArr[$userId]['unReadNum'];
          // 未读留言中的最后一条消息
          $dialogId = array_unique($_unReadArr[$userId]['dialogIds']);
          $dialogId = '('.implode(',',$dialogId).')';
          $dialog_content = self::$db->select('content,createTime,type')
                                     ->from(self::$prefix.'dialog_contents')
                                     ->where("dialogId in {$dialogId}")
                                     ->orderByDESC(array('createTime'))
                                     ->row();
          // 处理时间 时间格式：若为今天则显示时:分:秒，否则显示 月:日
          $cTime = strtotime($dialog_content['createTime']);
          $dialog_content['createTime'] = ($todayEnd>=$cTime && $todayStart<$cTime )?date('H:i',$cTime):date('m-d',$cTime);
          $dialog_content['content'] = unserialize($dialog_content['content']);
          if(isset($dialog_content['content']['content'])){
            $dialog_content['content']['content'] = htmlspecialchars_decode($dialog_content['content']['content']);
          }
          $rs[$userId] = array_merge($userInfo,$dialog_content);
        }
        return $rs;
    }
    /**
    * 判断当前客服是否属于该店铺
    */
    public static function isShopService($serviceId,$shopId){
        if($serviceId=='' || $shopId=='')return false;
        $rs = self::$db->select('shopId')
                       ->from(self::$prefix.'shop_services')
                       ->where("serviceId='{$serviceId}' AND shopId='{$shopId}' AND dataFlag=1")
                       ->single();
        return $rs;
    }
    /**
    * 客服接待事件：获取会话信息
    */
    public static function conversationInfo($userId,$shopId){
      // 用户信息
      $userInfo = [
            'loginName'=>'',
            'userPhoto'=>''
          ];
      // 查询最后一条消息
      $dialogId = self::$db->select('id')
                         ->from(self::$prefix.'dialogs')
                         ->where("userId='{$userId}' AND shopId='{$shopId}'")
                         ->column();
      $dialog_content = array();
      if(!empty($dialogId)){
        $dialogId = '('.implode(',',$dialogId).')';
        $dialog_content = self::$db->select('content,createTime,type')
                                   ->from(self::$prefix.'dialog_contents')
                                   ->where("dialogId in {$dialogId}")
                                   ->orderByDESC(array('createTime'))
                                   ->row();
        // 处理时间
        if(isset($dialog_content['createTime'])){
          // 时间格式：若为今天则显示时:分:秒，否则显示 月:日
          $todayStart = strtotime(date('Y-m-d'));
          $todayEnd = strtotime(date('Y-m-d 23:59:59'));
          $cTime = strtotime($dialog_content['createTime']);
          $dialog_content['createTime'] = ($todayEnd>=$cTime && $todayStart<$cTime )?date('H:i',$cTime):date('m-d',$cTime);
        }
        // 未读消息数 
        $userInfo['unReadNum'] = 0;

        $unReadArr = self::$db->select('content')
                              ->from(self::$prefix.'dialog_contents')
                              ->where("dialogId in {$dialogId} AND isRead='0'")
                              ->query();
        foreach($unReadArr as $v){
          $content = unserialize($v['content']);
          if($content['from']==$userId)$userInfo['unReadNum']++;
        }
      }
      if($dialog_content===false){
        $dialog_content = array();
      }
      if(isset($dialog_content['content'])){
        $dialog_content['content']=unserialize($dialog_content['content']);
        if(isset($dialog_content['content']['content'])){
              $dialog_content['content']['content'] = htmlspecialchars_decode($dialog_content['content']['content']);
        }
      }

      return array_merge($userInfo,$dialog_content);
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {  
        /* 监听事件，需要把客户端发来的json转为数组*/
        $message = json_decode($message,true);
        if(is_array($message) && !empty($message)){
          foreach($message as $key=>$value){
            $message[$key] = trim(strip_tags($value,'<img><a>'));
          }
        }
        // var_dump($message);
        extract($message);

        switch ($role) {
            case 'lisenter':
                // 用户监听使用
                Gateway::bindUid($client_id,$uid);
                $msg = self::getUnReadNum($uid,$shopId);
                $msg['type'] = 'unReadMsgNum';
                Gateway::sendToUid($uid,json_encode($msg));
                echo "called";
            break;
            //处理用户连接
            case 'user':
                if($type=='login'){
                  // 用户登录 {"role":"user","type":"login","uid":"用户id","shopId":"所属店铺id","platform":1}
                  Gateway::bindUid($client_id,$uid);
                  // 记录开始访问时间
                  $_SESSION['startTime'] = time();
                  $_SESSION['platform'] = $platform;
                  $_SESSION['uid'] = $uid;
                  $_SESSION['role'] = $role;
                  $_SESSION['shopId'] = $shopId;
                  $_SESSION['userName'] = $userName;
                  $_SESSION['group'] = "";// 所属客服组


                  // 处理用户刷新页面
                  $canUse = false; //当前客服是否可再分配标识
                  if(isset($group) && self::isShopService($group,$shopId)){
                    // 判断客服是否在线
                    if(Gateway::isUidOnline($group)){
                      // 判断当前客服是否符合继续接待该用户的条件
                      $s_clients = Gateway::getClientSessionsByGroup($group);
                      $s_n = array();
                      foreach($s_clients as $k=>$v){
                        $s_n[$v['uid']] = $k;
                      }
                      // 当前接待人数小于self::$connectLimit人 或者 当前接待人数大于等于self::$connectLimit人并且该用户存在于接待列表中
                      if(count($s_n)<self::$connectLimit || (count($s_n)>=self::$connectLimit && isset($s_n[$uid]))){
                          $canUse = true;
                      }
                    }
                  }
                  // 如果该客服可分配
                  if($canUse){
                      $_SESSION['group'] = $group;//指定客服
                      // 将用户加入当前客服会话组
                      Gateway::joinGroup($client_id,$group);
                      // 用户会话状态转移
                      Gateway::leaveGroup($client_id,"wait_".$shopId);
                      Gateway::joinGroup($client_id,"chat_".$shopId);
                      // 客服会话转移
                      $w_client_ids = Gateway::getClientIdByUid($group);
                      /*
                          array(
                              '7f00000108fc00000008',
                              '7f00000108fc00000009'
                          )
                      */
                      foreach($w_client_ids as $val){
                        Gateway::leaveGroup($val,"free_".$group);
                        Gateway::joinGroup($val,"busy_".$group);
                      }
                      // 给用户推送消息【有客服接待】
                      $msg = array(
                        'type'=>'chat',
                        // 接待的客服id
                        'group'=>$group,
                        // 客服名称
                        'groupName'=>''
                      );
                      if(empty($msg['groupName']))$msg['groupName']='店铺客服';
                      Gateway::sendToUid($_SESSION['uid'],json_encode($msg));

                      // 给客服推送消息【有客户加入会话】
                      $msg = array(
                        'type'=>'conversation',
                        'from'=>$_SESSION['uid']
                      );
                      $conversationArr = self::conversationInfo($_SESSION['uid'],$shopId);
                      $msg = array_merge($conversationArr,$msg);
                      Gateway::sendToUid($group,json_encode($msg));
                      // 刷新排队人数，接待人数
                      self::refreshWaitCount($_SESSION['shopId']);
                      self::refreshChatCount($group);
                  }else{
                    // 根据shopId查询当前店铺是否有客服在线
                    $workerCount = Gateway::getClientCountByGroup("kf_".$shopId);
                    if($workerCount>0){
                      // 当前有客服在线，进行客服分配：分配给当前接待用户数量最少的客服
                      $shopServices = Gateway::getClientSessionsByGroup("kf_".$shopId);
                      /*
                          $shopServices = array(
                              '7f00000108fc00000008' => array('serviceId'=>'客服id', 'shopId'=>'店铺id'),
                              '7f00000108fc00000009' => array('serviceId'=>'客服id', 'shopId'=>'店铺id'),
                          )
                      */
                      $w_n = array();// array('客服id'=>'接待人数'))
                      foreach($shopServices as $k=>$v){
                          // 获取当前客服接待人数
                          $w_u = GateWay::getClientSessionsByGroup($v['serviceId']);
                          $w_u_c = array();// 客服接待人数
                          foreach($w_u as $_k=>$_v){
                            $w_u_c[$_v['serviceId']] = $_k;
                          }
                          $w_n[$v['serviceId']] = count($w_u_c);
                      }
                      // -------------------------start-----------------------
                      /*
                        针对同一个用户从不同端进来的情况：
                        例如一个客户正在pc端对自营店铺进行咨询，又打开手机端对自营店铺
                        进行咨询，则该用户应由同一个客服进行接待
                      */
                      if(isset($w_n[$uid])){
                        // 判断该用户id当前是否已有客服接待
                        $w_uid = $uid;
                        $w_num = self::$connectLimit-1;// 令其可进入判断
                      }else{
                        // 根据键值从小到大排序
                        asort($w_n);
                        // 将指针指第一单元
                        reset($w_n);
                        // 获取接待用户最少的客服id
                        $w_uid = key($w_n);
                        // 获取该客服接待用户数量
                        $w_num = current($w_n);
                      }
                      // -------------------------end-----------------------
                      if($w_num<self::$connectLimit){
                        $_SESSION['group'] = $w_uid;//指定客服
                        // 将用户加入当前客服会话组
                        Gateway::joinGroup($client_id,$w_uid);
                        // 用户会话状态转移
                        Gateway::leaveGroup($client_id,"wait_".$shopId);
                        Gateway::joinGroup($client_id,"chat_".$shopId);
                        // 客服会话转移
                        $w_client_ids = Gateway::getClientIdByUid($w_uid);
                        /*
                            array(
                                '7f00000108fc00000008',
                                '7f00000108fc00000009'
                            )
                        */
                        foreach($w_client_ids as $val){
                          Gateway::leaveGroup($val,"free_".$w_uid);
                          Gateway::joinGroup($val,"busy_".$w_uid);
                        }
                        // 给用户推送消息【有客服接待】
                        $msg = array(
                          'type'=>'chat',
                          // 接待的客服id
                          'group'=>$w_uid,
                          // 客服名称
                          'groupName'=>''
                        );
                        if(empty($msg['groupName']))$msg['groupName']='店铺客服';
                        Gateway::sendToUid($_SESSION['uid'],json_encode($msg));
                        // 给客服推送消息【有客户加入会话】
                        $msg = array(
                          'type'=>'conversation',
                          'from'=>$_SESSION['uid']
                        );
                        $conversationArr = self::conversationInfo($_SESSION['uid'],$shopId);
                        $msg = array_merge($conversationArr,$msg);
                        Gateway::sendToUid($w_uid,json_encode($msg));

                        // 刷新排队人数，接待人数
                        self::refreshChatCount($w_uid);
                      }else{
                        // 没有客服接待，进入排队状态
                        Gateway::joinGroup($client_id,"wait_".$shopId);
                        $msg = array('type'=>'wait');
                        Gateway::sendToUid($_SESSION['uid'],json_encode($msg));
                      }
                      // 通知店铺所有在线客服，刷新排队数
                      self::refreshWaitCount($_SESSION['shopId']);
                    }else{
                      // 没有客服在线，留言状态
                      Gateway::joinGroup($client_id,"wait_".$shopId);
                      $msg = array('type'=>'message');
                      Gateway::sendToUid($_SESSION['uid'],json_encode($msg));
                    }
                  }
                }elseif($type=='say'){
                  // 用户发言 {"role":"user","type":"say","to":"客服id","content":"发送内容"}
                  $content = self::filterContent($content);
                  $sendTime = time();
                  $msg = array(
                    'type'=>'say',
                    'from'=>$_SESSION['uid'],
                    'content'=>$content,
                    'createTime'=>date('H:i',$sendTime),
                    'role'=>'user'// 表示为用户身份才渲染
                  );
                  // 推送给用户
                  Gateway::sendToUid($_SESSION['uid'],json_encode($msg));
                  
                  // 推送给客服
                  $msg['userName'] = $_SESSION['userName'];
                  // 表示为客服身份才渲染
                  $msg['role'] = 'worker';
                  Gateway::sendToUid($to,json_encode($msg));

                  // app端离线推送
                  self::notification([ 'userId'=>$to, 'content'=>$content ]);
                  /************************************* newMsg start ************************************/
                  if(Gateway::isUidOnline($to)){
                    echo "\n\r service newMsg start called";
                    $newMsg = array(
                      'type'=>'newMsg',
                      'from'=>$_SESSION['uid'],
                      '_object'=>'service'
                    );
                    Gateway::sendToUid($to,json_encode($newMsg));
                  }
                  /************************************* newMsg end ************************************/

                  // 数据入库
                  $dialogId = self::$db->from(self::$prefix.'dialogs')
                                       ->where("userId='{$_SESSION['uid']}' AND shopId='{$_SESSION['shopId']}' AND serviceId='{$to}'")
                                       ->select('id')
                                       ->single();
                  // 将会话记录写入会话表
                  if(!$dialogId){
                    $dialogId = self::$db->insert(self::$prefix.'dialogs')
                                          ->cols(array(
                                                'userId'=>$_SESSION['uid'], 
                                                'serviceId'=>$to, 
                                                'shopId'=>$_SESSION['shopId'], 
                                                'createTime'=>date('Y-m-d H:i:s',$sendTime)))
                                          ->query();
                  }
                  if($dialogId){
                    // 设置userDel、shopDel为0
                    self::enableDialog("userId='{$_SESSION['uid']}' AND shopId='{$_SESSION['shopId']}'");

                     $chat_content = serialize(array("content"=>$content,"from"=>"{$_SESSION['uid']}","serviceId"=>"{$to}"));
                     self::$db->insert(self::$prefix.'dialog_contents')
                              ->cols(array('dialogid'=>"{$dialogId}",
                                           'content'=>"{$chat_content}",
                                           'createTime'=>date('Y-m-d H:i:s',$sendTime)))
                              ->query();
                    /***********************************  自动回复start  ******************************************/
                    $autoReplys = self::autoReply($content);
                    if($autoReplys!==false){
                      // 当前客服名称
                      $clientId = Gateway::getClientIdByUid($to);
                      $clientId = isset($clientId[0])?$clientId[0]:'';
                      $workername = '';
                      if($clientId!=''){
                        $wokerSession = Gateway::getSession($clientId);
                        $workername = isset($wokerSession['userName'])?$wokerSession['userName']:'';
                      }
                      foreach($autoReplys as $autoReplyContent){
                        
                        // 自动回复入库时，发送者为当前客服
                        $chat_content = serialize(array("content"=>$autoReplyContent,"from"=>"{$to}"));
                        self::$db->insert(self::$prefix.'dialog_contents')
                                  ->cols(array('dialogid'=>"{$dialogId}",
                                              'content'=>"{$chat_content}",
                                              'createTime'=>date('Y-m-d H:i:s',$sendTime+1)))
                                  ->query();
                      
                        // 自动回复入库之后给用户及客服推送消息
                        $msg = array(
                          'type'=>'say',
                          'from'=>$to,
                          'shopId'=>$_SESSION['shopId'],
                          'content'=>$autoReplyContent,
                          'createTime'=>date('H:i',$sendTime+1),
                          'role'=>'worker'// 表示为用户身份才渲染
                        );
                        // 推送给客服
                        Gateway::sendToUid($to,json_encode($msg));
                        $msg['userName'] = $workername;
                        $msg['role'] = 'user';
                        Gateway::sendToUid($_SESSION['uid'],json_encode($msg));
                        
                      }
                    }
                  /***********************************  自动回复end  ******************************************/
                 }
                }elseif($type=='visit'){
                    // 正在浏览 {"role":"user","type":"visit","to":"客服id","content":"发送内容"}
                    $msg = array(
                      'type'=>'visit',
                      'from'=>$_SESSION['uid'],
                      'content'=>$content,
                    );
                    Gateway::sendToUid($to,json_encode($msg));
                }
            break;
            // 处理客服连接
            case 'worker':
                if($type=='login'){
                  // 客服登录 {"role":"worker","type":"login","serviceId":"客服id","shopId":"所属店铺id"}
                  $_SESSION['serviceId'] = $serviceId;
                  $_SESSION['userName'] = $userName;
                  $_SESSION['role'] = $role;
                  $_SESSION['shopId'] = $shopId;
                  $_SESSION['platform'] = $platform;
                  Gateway::bindUid($client_id,$serviceId);
                  // 加入店铺客服组
                  Gateway::joinGroup($client_id,'kf_'.$shopId);
                  // 刷新操作：查看当前客服组内是否有客户
                  if(Gateway::getClientCountByGroup($serviceId)){
                    $w_clients = Gateway::getClientSessionsByGroup($serviceId);
                    $clientLists = array();
                    foreach($w_clients as $k=>$v){
                      if(!isset($clientLists[$v['uid']])){
                        $clientLists[$v['uid']] = self::conversationInfo($v['uid'],$shopId);
                      }
                    }
                    $msg = array(
                      'type'=>'load',
                      'list'=>$clientLists
                    );
                    Gateway::sendToUid($serviceId,json_encode($msg));
                  }elseif(Gateway::getClientCountByGroup("wait_".$shopId)){
                        // 查看当前是否存在未被客服接待的用户
                        //## $_SESSION['group'] = $uid;//指定客服
                        $clients = Gateway::getClientSessionsByGroup("wait_".$shopId);
                        $userUids = [];
                        foreach($clients as $k=>$v){
                          $userUids[$v['uid']][] = $k;
                        }
                        // 最多接待self::$connectLimit人
                        $userUids = array_slice($userUids, 0, self::$connectLimit, true);
                        var_dump($userUids);
                        foreach($userUids as $k=>$v){
                          foreach($v as $v1){
                            // 将用户加入当前客服会话组
                            Gateway::joinGroup($v1,$serviceId);
                            // 用户会话状态转移
                            Gateway::leaveGroup($v1,"wait_".$shopId);
                            Gateway::joinGroup($v1,"chat_".$shopId);
                          }
                        }
                        // 客服会话转移
                        $w_client_ids = Gateway::getClientIdByUid($serviceId);
                        /*
                            array(
                                '7f00000108fc00000008',
                                '7f00000108fc00000009'
                            )
                        */
                        foreach($w_client_ids as $val){
                          Gateway::leaveGroup($val,"free_".$serviceId);
                          Gateway::joinGroup($val,"busy_".$serviceId);
                        }
                        // 给用户推送消息【有客服接待】
                        $msg = array(
                          'type'=>'chat',
                          // 接待的客服id
                          'group'=>$serviceId,
                          // 客服名称
                          'groupName'=>''
                        );
                        if(empty($msg['groupName']))$msg['groupName']='店铺客服';
                        foreach($userUids as $userUid=>$v){
                            Gateway::sendToUid($userUid,json_encode($msg));
                            // 给客服推送消息【有客户加入会话】
                            $msg = array(
                              'type'=>'conversation',
                              'from'=>$userUid
                            );
                            $conversationArr = self::conversationInfo($userUid,$shopId);
                            $msg = array_merge($conversationArr,$msg);
                            Gateway::sendToUid($serviceId,json_encode($msg));
                        }
                  }else{
                    // 客服空闲状态
                    Gateway::joinGroup($client_id,'free_'.$shopId);
                  }

                  // 获取未读留言
                  $unReadMessage = self::getMessage();
                  if(!empty($unReadMessage)){
                    $msg = array(
                      'type'=>'unReadMsg',
                      'list'=>$unReadMessage
                    );
                    Gateway::sendToUid($_SESSION['serviceId'],json_encode($msg));
                  }
                  // 统计未接待数及当前客服所接待的用户数
                  $data = array(
                    'waitCount'=>self::getWaitCount($_SESSION['shopId']),
                    'chatCount'=>self::getChatCount($_SESSION['serviceId'])
                  );
                  $msg = array(
                    'type'=>'serviceStatus',
                    'data'=>$data
                  );
                  Gateway::sendToUid($_SESSION['serviceId'],json_encode($msg));
                  // 通知店铺所有在线客服，刷新排队数
                  self::refreshWaitCount($_SESSION['shopId']);

                }elseif($type=='say'){
                  // 客服发言 {"role":"worker","type":"say","to":"用户id","content":"发送内容"}
                  $content = self::filterContent($content);
                  $sendTime = time();
                  $msg = array(
                    'type'=>'say',
                    'from'=>$_SESSION['serviceId'],
                    'shopId'=>$_SESSION['shopId'],
                    'content'=>$content,
                    'createTime'=>date('H:i:s',$sendTime),
                    'role'=>'worker',
                  );
                  // 推送给客服
                  Gateway::sendToUid($_SESSION['serviceId'],json_encode($msg));
                  // 推送给用户
                  $msg['userName'] = $_SESSION['userName'];
                  $msg['role'] = 'user';

                  //  ------------------------------start------------------------------
                  /*
                    目前是将client_id绑定到userId上
                    当一个用户打开多个店铺页面进行咨询时，若调用Gateway::sendToUid(),
                    用户将会在同一个窗口收到来自不同店铺的消息。
                    故调用Gateway::sendToClient()
                  */
                  $w_clients = Gateway::getClientSessionsByGroup($_SESSION['serviceId']);
                  $u_clients = array();
                  foreach($w_clients as $k=>$v){
                      $u_clients[$v['uid']][] = $k;
                  }
                  echo "UID:\n\r{$to}";
                  var_dump($u_clients);
                  if(isset($u_clients[$to])){
                    foreach($u_clients[$to] as $k=>$u_client){
                      Gateway::sendToClient($u_client,json_encode($msg));
                    }
                  }
                  /************************************* newMsg start ************************************/
                  if(Gateway::isUidOnline($to)){
                    echo "user newMsg start called";
                    $newMsg = array(
                      'type'=>'newMsg',
                      // 'from'=>$_SESSION['uid'],
                      'from'=>$_SESSION['shopId'],
                      'content'=>$content,
                      '_object'=>'user',
                      'createTime'=>$msg['createTime']
                    );
                    Gateway::sendToUid($to,json_encode($newMsg));
                  }
                  /************************************* newMsg end ************************************/

                  // ------------------------------end------------------------------
                  // 数据入库
                  $dialogId = self::$db->from(self::$prefix.'dialogs')
                                       ->where("serviceId='{$_SESSION['serviceId']}' AND shopId='{$_SESSION['shopId']}' AND userId='{$to}'")
                                       ->select('id')
                                       ->single();
                  // 将会话记录写入会话表
                  if(!$dialogId){
                    $dialogId = self::$db->insert(self::$prefix.'dialogs')
                                          ->cols(array(
                                                'serviceId'=>$_SESSION['serviceId'], 
                                                'userId'=>$to, 
                                                'shopId'=>$_SESSION['shopId'], 
                                                'createTime'=>date('Y-m-d H:i:s',$sendTime)))
                                          ->query();
                  }
                  if($dialogId){
                    // 设置userDel、shopDel为0
                    self::enableDialog("userId='{$to}' AND shopId='{$_SESSION['shopId']}'");

                     $chat_content = serialize(array("content"=>$content,"from"=>"{$_SESSION['serviceId']}","userId"=>"{$to}"));
                     
                     self::$db->insert(self::$prefix.'dialog_contents')
                              ->cols(array('dialogid'=>"{$dialogId}",
                                           'content'=>"{$chat_content}",
                                           'createTime'=>date('Y-m-d H:i:s',$sendTime),
                                           'isRead'=>0
                                          )
                                    )
                              ->query();
                 }
                }
            break;
            // 处理客服管理员
            case 'admin':
                if($type=='login'){
                  // 用户登录 {"role":"admin","type":"login","uid":"管理员id","shopId":"所属店铺id"}
                  $_SESSION['uid'] = $uid;
                  $_SESSION['role'] = 'admin';
                  $_SESSION['shopId'] = $shopId;
                  Gateway::bindUid($client_id,$uid);
                  // 获取在线客服数
                  $services = Gateway::getClientSessionsByGroup("kf_".$shopId);
                  $_services = array();
                  foreach($services as $k=>$v){
                    $_services[$v['uid']][] = $k;
                  }
                  // 获取正在咨询人数
                  $chats = Gateway::getClientSessionsByGroup("chat_".$shopId);
                  $_chats = array();
                  foreach($chats as $k=>$v){
                    $_chats[$v['uid']][] = $k;
                  }
                  // 获取排队人数
                  $waits = Gateway::getClientSessionsByGroup("wait_".$shopId);
                  $_waits = array();
                  foreach($waits as $k=>$v){
                    $_waits[$v['uid']][] = $k;
                  }
                  $msg = array(
                    'type'=>'init',
                    'services'=>count($services),
                    'chats'=>count($chats),
                    'waits'=>count($waits),
                  );
                  Gateway::sendToUid($uid,json_encode($msg));
                }
            break;
        }

        return;
   }
   /**
    * 客服访问统计
    */
   public static function chatStatistics($userId){
     echo "call chatStatistics";
     if(isset($_SESSION['startTime'])){
       $data = [
           'userId'=>$userId,
           'startTime'=>date('Y-m-d H:i:s', $_SESSION['startTime']),
           'stayTime'=>time()-$_SESSION['startTime'],
           'shopId'=>(int)$_SESSION['shopId'],
           'ip'=>$_SERVER['REMOTE_ADDR'],
           'platform'=>$_SESSION['platform']
       ];
       self::$db->insert(self::$prefix.'im_chat_statistics')
                ->cols($data)
                ->query();
     }
   }
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
        //如果访客已经退出 通知客服
        $group = isset($_SESSION['group'])?(int)$_SESSION['group']:0;
        if($_SESSION['role'] === 'user' && $group>0){
            $w_clients = Gateway::getClientSessionsByGroup($group);
            $clientLists = array();
            foreach($w_clients as $k=>$v){
                $clientLists[$v['uid']][] = $k;
            }
            if(!isset($clientLists[$_SESSION['uid']])){ //.. 一个uid可以绑定多个client_id，当所有client_id都离线才视为离线
                $msg = array();
                $msg['type'] = 'userExit'; //访客的信息 用于更新访客列表
                $msg['clientUid'] = $_SESSION['uid']; //访客的标识uid
                Gateway::sendToUid($_SESSION['group'], json_encode($msg));

                // 统计数据写入
                self::chatStatistics($_SESSION['uid']);



                $shopId = $_SESSION['shopId'];
                $uid = $group;
               // 查看当前是否存在未被客服接待的用户
                $clients = Gateway::getClientSessionsByGroup("wait_".$shopId);
                $userUids = [];
                foreach($clients as $k=>$v){
                  $userUids[$v['uid']][] = $k;
                }
                // 最多接待self::$connectLimit人
                $userUids = array_slice($userUids, 0, self::$connectLimit, true);
                echo "userUids\n\r";
                var_export($userUids);
                foreach($userUids as $k=>$v){
                  foreach($v as $v1){
                    // 设定被接待用户的session
                    $userSession = Gateway::getSession($v1);
                    $userSession['group'] = $uid;//指定客服
                    Gateway::setSession($v1,$userSession);
                    // 将用户加入当前客服会话组
                    Gateway::joinGroup($v1,$uid);
                    // 用户会话状态转移
                    Gateway::leaveGroup($v1,"wait_".$shopId);
                    Gateway::joinGroup($v1,"chat_".$shopId);
                  }
                }
                // 客服会话转移
                $w_client_ids = Gateway::getClientIdByUid($uid);
                /*
                    array(
                        '7f00000108fc00000008',
                        '7f00000108fc00000009'
                    )
                */
                foreach($w_client_ids as $val){
                  Gateway::leaveGroup($val,"free_".$uid);
                  Gateway::joinGroup($val,"busy_".$uid);
                }
                // 给用户推送消息【有客服接待】
                $msg = array(
                  'type'=>'chat',
                  // 接待的客服id
                  'group'=>$uid,
                  // 客服名称
                  'groupName'=>''
                );
                if(empty($msg['groupName']))$msg['groupName']='店铺客服';
                foreach($userUids as $userUid=>$v){
                    Gateway::sendToUid($userUid,json_encode($msg));
                    // 给客服推送消息【有客户加入会话】
                    $msg = array(
                      'type'=>'conversation',
                      'from'=>$userUid
                    );
                    $conversationArr = self::conversationInfo($userUid,$shopId);
                    $msg = array_merge($conversationArr,$msg);
                    Gateway::sendToUid($uid,json_encode($msg));
                }
                // 刷新接待人数
                self::refreshChatCount($group);
            }
          
        }elseif($_SESSION['role']==='user'){
          // 未被接待的用户退出
          self::refreshWaitCount($_SESSION['shopId']);
          // 统计数据写入
          self::chatStatistics($_SESSION['uid']);
        }
   }
}
