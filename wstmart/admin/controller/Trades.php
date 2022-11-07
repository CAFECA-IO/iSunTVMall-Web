<?php
namespace wstmart\admin\controller;
use wstmart\admin\model\Trades as M;
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
 * 行业控制器
 */
class Trades extends Base{
	
    public function index(){
    	return $this->fetch("list");
    }
    
    /**
     * 获取分页
     */
    public function pageQuery(){
    	$m = new M();
    	return $m->pageQuery();
    }
    /**
     * 获取列表
     */
    public function listQuery(){
    	$m = new M();
    	$rs = $m->listQuery(input('parentId/d',0));
    	return WSTReturn("", 1,$rs);
    }
    /**
     * 获取行业
     */
    public function get(){
    	$m = new M();
    	return $m->getById((int)Input("post.id"));
    }
    
       
    /**
     * 设置是否显示/隐藏
     */
    public function editiIsShow(){
    	$m = new M();
    	return $m->editiIsShow();
    }
    
    /**
     * 新增
     */
    public function add(){
    	$m = new M();
    	return $m->add();
    }
    
    /**
     * 编辑
     */
    public function edit(){
    	$m = new M();
    	return $m->edit();
    }
   
    /**
     * 编辑分类排序
     */
    public function editOrder(){
        $m = new M();
        return $m->editOrder();
    }
    
    /**
     * 删除
     */
    public function del(){
    	$m = new M();
    	return $m->del();
    }

    /**
     * 跳去新增/编辑页面
     */
    public function toEdit(){
        $id = Input("get.id/d",0);
        $pid = Input("get.pid/d",0);
        $m = new M();
        if($id>0){
            $object = $m->getById($id);
        }else{
            if($pid > 0){
                $parentObject = $m->getById($pid);
                $object = $m->getEModel('trades');
                foreach (WSTSysLangs() as $key => $v) {
                    $object['langParams'][$v['id']]['tradeName'] = '';
                    $object['langParams'][$v['id']]['simpleName'] = '';
                    $object['langParams'][$v['id']]['subTitle'] = '';
                    $object['langParams'][$v['id']]['seoKeywords'] = '';
                    $object['langParams'][$v['id']]['seoTitle'] = '';
                    $object['langParams'][$v['id']]['seoDes'] = '';
                }
                $object['parentId'] = $pid;
            }else{
                $object = $m->getEModel('trades');
                foreach (WSTSysLangs() as $key => $v) {
                    $object['langParams'][$v['id']]['tradeName'] = '';
                    $object['langParams'][$v['id']]['simpleName'] = '';
                    $object['langParams'][$v['id']]['subTitle'] = '';
                    $object['langParams'][$v['id']]['seoTitle'] = '';
                    $object['langParams'][$v['id']]['seoKeywords'] = '';
                    $object['langParams'][$v['id']]['seoDes'] = '';
                }
                $object['parentId'] = $pid;
            }
        }
        $this->assign('object',$object);
        return $this->fetch("edit");
    }
}
