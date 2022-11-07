<?php
namespace wstmart\common\model;

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
 * 商城消息
 */
use think\Db;
class Messages extends Base{
    /**
     * 获取最后一条商城消息
     */
    public function getLastMsg($uId=0){
        $userId = $uId==0?(int)session('WST_USER.userId'):$uId;
        $where = ['receiveUserId'=>(int)$userId,'dataFlag'=>1];
        $data = Db::name('Messages')->where($where)
                     ->field('id,msgContent,msgStatus,createTime')
                     ->order('id desc')
                     ->find();
        if(!empty($data)){
            $data['msgContent'] = strip_tags(htmlspecialchars_decode($data['msgContent']));
            $data['createTime'] = date('Y-m-d', strtotime($data['createTime']));
        }else{
            $data = ['msgContent'=>'-', 'createTime'=>'-'];
        }
        return $data;
    }
   /**
    * 获取列表
    */
    public function pageQuery($uId=0){
        $userId = (int)session('WST_USER.userId');
        $pagesize = input('post.limit/d');
        // app端
        if($uId>0){
            $userId = $uId;
            $pagesize = input('post.pagesize/d');
        }
         $where = ['receiveUserId'=>(int)$userId,'dataFlag'=>1];
         $page = model('Messages')->where($where)->order('msgStatus asc,id desc')->paginate($pagesize)->toArray();
         foreach ($page['data'] as $key => $v){
         	$page['data'][$key]['msgContent'] = WSTMSubstr(strip_tags(htmlspecialchars_decode($v['msgContent'])),0,140);
         }
         return $page;
    }
   /**
    *  获取某一条消息详情
    */
    public function getById($uId=0,$type=0){
    	$userId = ($uId>0)?$uId:(int)session('WST_USER.userId');
        $domain = WSTConf('CONF.resourcePath');
        // app端
        if($uId>0 && $type==0){
            
            $domain = WSTConf('CONF.resourceDomain');
        }
        $id = (int)input('msgId');
        $data = $this->get(['id'=>$id,'receiveUserId'=>$userId]);
        if(!empty($data)){
          $data['msgContent'] = str_replace('${DOMAIN}', $domain, $data['msgContent']);    
          $data['msgContent'] = htmlspecialchars_decode($data['msgContent']);
          if($data['msgStatus']==0)
            model('Messages')->where('id',$id)->setField('msgStatus',1);
        }
        return $data;
    }

    /**
     * 删除
     */
    public function del($userId=0){
    	$userId = ($userId>0)?$userId:(int)session('WST_USER.userId');
        $id = input('id/d');
        $data = [];
        $data['dataFlag'] = -1;
        $result = $this->update($data,['id'=>$id,'receiveUserId'=>$userId]);
        if(false !== $result){
            return WSTReturn(lang('del_success'), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
    }
    /**
    * 批量删除
    */
    public function batchDel($uId=0){
    	$userId = (int)session('WST_USER.userId');
        $ids = input('ids/a');
        if($uId>0){
            // app端
            $userId = $uId;
            $ids = input('ids');
        }
        $data = [];
        $data['dataFlag'] = -1;
        $result = $this->update($data,[['id','in',$ids],['receiveUserId','=',$userId]]);
        if(false !== $result){
            return WSTReturn(lang('del_success'), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
    }
    /**
    * 标记为已读
    */
    public function batchRead($userId=0){
    	$userId = ($userId>0)?$userId:(int)session('WST_USER.userId');
        $ids = input('ids/a');
        $data = [];
        $data['msgStatus'] = 1;
        $result = $this->update($data,[['id','in',$ids],['receiveUserId','=',$userId]]);
        if(false !== $result){
            return WSTReturn(lang('operation_success'), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
    }

    
}
