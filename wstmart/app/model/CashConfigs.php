<?php
namespace wstmart\app\model;
use wstmart\common\model\CashConfigs as CCM;
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
 * 提现账号业务处理器
 */
class CashConfigs extends CCM{
     /**
      * 获取列表
      */
      public function pageQuery($targetType,$targetId){
      	  $type = (int)input('post.type',-1);
          $where = [];
          $where['targetType'] = (int)$targetType;
          $where['targetId'] = (int)$targetId;
          $where['c.dataFlag'] = 1;
          if(in_array($type,[0,1]))$where['moneyType'] = $type;
          $page = $this->alias('c')->join('__BANKS__ b','c.accTargetId=b.bankId')->where($where)->field('b.bankName,b.bankImg,c.*')->order('c.id desc')->select();
          if(count($page)>0){
              foreach($page as $key => $v){
                  $page[$key]['accNo'] = '**** '.substr($v['accNo'],-4);
                  // 删除无用字段
                  unset($v['dataFlag']);
                  unset($v['createTime']);
              }
          }
          return $page;
      }
}
