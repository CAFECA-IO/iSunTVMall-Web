<?php
namespace wstmart\supplier\controller;
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
    protected $beforeActionList = ['checkAuth'];
    /**
    * 查看商城消息
    */
	public function index(){
        return $this->supplierMessage();
	}
   /**
    * 查看商城消息
    */
    public function supplierMessage(){
        $this->assign('p',(int)input('p'));
        return $this->fetch('messages/list');
    }
    /**
    * 获取数据
    */
    public function pageQuery(){
        $userId = (int)session("WST_SUPPLIER.userId");
        $data = model('Messages')->pageQuery($userId);
        return WSTGrid($data);
    }
    /**
    * 查看完整商城消息
    */
    public function showSupplierMsg(){
        $userId = (int)session("WST_SUPPLIER.userId");
        $data = model('Messages')->getById($userId,3);
        return $this->fetch('messages/show',['data'=>$data,'p'=>(int)input('p')]);
    }
	
    /**
    * 删除
    */
    public function del(){
        $userId = (int)session("WST_SUPPLIER.userId");
    	$m = model('Messages');
        $rs = $m->del($userId);
        return $rs;
    }
    /**
    * 批量删除
    */
    public function batchDel(){
        $userId = (int)session("WST_SUPPLIER.userId");
        $m = model('Messages');
        $rs = $m->batchDel($userId);
        return $rs;
    }
    /**
    * 标记为已读
    */
    public function batchRead(){
        $userId = (int)session("WST_SUPPLIER.userId");
        $m = model('Messages');
        $rs = $m->batchRead($userId);
        return $rs;
    }


}
