<?php
namespace wstmart\common\model;
use wstmart\common\validate\Feedbacks as Validate;
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
 * 功能反馈类
 */
class Feedbacks extends Base{
	protected $pk = 'feedbackId';

	/**
	 * 保存反馈问题
	 */
	public function add($uid=0){
		$userId = $uid > 0 ? $uid : (int)session('WST_USER.userId');
		$feedbackType = (int)input('feedbackType');
        if(!WSTCheckDatas('FEEDBACK_TYPE',$feedbackType))return WSTReturn(lang('invalid_complaint_type'),-1);
		Db::startTrans();
		try{
			$data['userId'] = $userId?$userId:0;
			$data['feedbackStatus'] = 0;
			$data['feedbackType'] = $feedbackType;
			$data['createTime'] = date('Y-m-d H:i:s');
			$data['feedbackContent'] = input('feedbackContent');
			$data['contactInfo'] = input('contactInfo');
			$validate = new Validate;
			if (!$validate->scene('add')->check($data)) {
				return WSTReturn($validate->getError());
			}else{
				$rs = $this->save($data);
				if($rs !==false){
					Db::commit();
					return WSTReturn(lang('complaint_submit_success_tips'),1);
				}
			}
		}catch (\Exception $e) {
		    Db::rollback();
	    }
	    return WSTReturn(lang('operation_fail'),-1);
	}

	
	
}
