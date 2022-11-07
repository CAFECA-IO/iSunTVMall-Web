<?php
namespace wstmart\shopapp\controller;
use wstmart\common\model\Messages as M;
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
 * 商城消息控制器
 */
class Messages extends Base{
	// 前置方法执行列表
    protected $beforeActionList = [
        'checkShopAuth'  =>  ['except'=>'index'],
    ];
	/**
	 * 获取列表
	 */
	public function pageQuery(){
		$m = new M();
		$userId = model('shopapp/users')->getUserId();
		$data =  $m->pageQuery($userId);
		echo(json_encode(WSTReturn('success',1,$data)));die;
	}
	/**
	* 查看消息
	*/
	public function index(){
		$m = new M();
		$userId = model('shopapp/users')->getUserId();
		$data = $m->getById($userId);
		$this->assign('data',$data);
		return $this->fetch('message');
	}
	/**
	 * 删除消息
	 */
	public function del(){
		$m = new M();
		$userId = model('shopapp/users')->getUserId();
		$rs = $m->batchDel($userId);
		return json_encode($rs);
	}
}
