<?php
namespace addons\captcha\controller;

use think\addons\Controller;
use addons\captcha\model\Captchas as M;
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
 * 商淘软件图形验证码
 */
class Admin extends Controller{
	/**
     * 查看秒杀活动列表
     */
    public function index(){
        $this->checkAdminAuth();
        $this->assign("p",(int)input("p"));
        return $this->fetch("/admin/list");
    }

    /**
     * 查询列表
     */
    public function pageQuery(){
        $this->checkAdminAuth();
        $m = new M();
        return WSTGrid($m->pageQuery());
    }

    /**
     * 新增图片
     */
    public function add(){
        $this->checkAdminAuth();
        $m = new M();
        return $m->add();
    }

    /**
     * 删除图片
     */
    public function del(){
        $this->checkAdminAuth();
        $m = new M();
        return $m->del();
    }
    /**
     * 批量删除图片
     */
    public function batchDel(){
        $this->checkAdminAuth();
        $m = new M();
        return $m->batchDel();
    }

    /**
     * 获取图片编辑
     */
    public function getById(){
        $this->checkAdminAuth();
        $m = new M();
        return $m->getById();
    }

    /**
     * 保存图片
     */
    public function edit(){
        $this->checkAdminAuth();
        $m = new M();
        return $m->edit();
    }
    
}