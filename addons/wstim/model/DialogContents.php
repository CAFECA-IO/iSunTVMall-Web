<?php 
namespace addons\wstim\model;
use think\addons\BaseModel as Base;
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
 * 聊天记录处理
 */
class DialogContents extends Base{
	/**
	* 查询聊天记录
	*/
	public function pageQuery($shopId){
		$receiveId = (int)input('id');// 用户id
		// 根据receiveId及店铺id查找dialogId
		$dialogId = Db::name('dialogs')->where(['userId'=>$receiveId,'shopId'=>$shopId])
									   ->column('id');
		$rs = Db::name('dialog_contents')->whereIn('dialogId',$dialogId)
										 ->order('createTime desc')
										 ->paginate(input('pagesize/d'))
										 ->toArray();
		// 获取店铺下的serviceIds
		$serviceIds = Db::name('shop_services')->where(['shopId'=>$shopId])->column('serviceId');
		// 获取店铺下的职员信息userId
		$shopUsers = Db::name('shop_users')->alias('su')
										   ->join('__USERS__ u','u.userId=su.userId','inner')
										   ->field('u.loginName,u.userName,su.serviceId')
										   ->whereIn('su.serviceId',$serviceIds)
										   ->select();
		// 获取该用户的信息
		$userInfo = Db::name('users')->where('userId',$receiveId)->field('loginName,userName')->find();

		// 聊天双方的用户名
		$chatUserInfo = [];
		foreach($shopUsers as $v){
			$chatUserInfo[$v['serviceId']] = $v['userName']?:$v['loginName'];
		}
		$chatUserInfo[$receiveId] = $userInfo['userName']?:$userInfo['loginName'];
		// 反序列化
		foreach($rs['data'] as $k=>$v){
			$content = unserialize($v['content']);
			$rs['data'][$k]['content'] = $content['content'];
			$rs['data'][$k]['from'] = $content['from'];
			$rs['data'][$k]['userName'] = $chatUserInfo[$content['from']];
		}
		if(!empty($rs['data']))usort($rs['data'], "createTimeAscSort");
		return $rs;
	}
}