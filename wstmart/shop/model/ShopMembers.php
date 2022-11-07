<?php
namespace wstmart\shop\model;
use think\Db;
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
 * 店铺客户业务处理类
 */
class ShopMembers extends Base{
	protected $pk = 'applyId';
    /**
     * 分页
     */
    public function pageQuery(){
        $key = input('key');
        $isOrder = (int)input('isOrder',1);
        $where[] = ['sm.isOrder','=',$isOrder];
        $where[] = ['sm.shopId','=',(int)session('WST_USER.shopId')];
        if($key!='')$where[] = ['u.loginName|u.userName','like','%'.$key.'%'];
        $page = Db::name('shop_members')->alias('sm')
            ->join('__USERS__ u','u.userId=sm.userId','inner')
            ->join('__SHOP_MEMBER_GROUPS__ sg','sg.id=sm.groupId','left')
            ->where($where)
            ->field('u.loginName,u.userName,sm.*,sg.groupName')
            ->order('sm.createTime', 'desc')
            ->paginate(input('post.limit/d'))->toArray();
        return $page;
    }

    /**
     * 设置分组
     */
    public function setGroup(){
        $ids = WSTFormatIn(',',input('ids'),false);
        $groupId = (int)input('groupId');
        $shopId = (int)session('WST_USER.shopId');
        $result = $this->where([['shopId','=',$shopId],['userId','in',$ids]])->update(['groupId'=>$groupId]);
        if($result!==false){
            return WSTReturn(lang('op_ok'),1);
        }else{
            return WSTReturn(lang('op_err'));
        }
    }
}
