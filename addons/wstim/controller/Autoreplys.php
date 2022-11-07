<?php
namespace addons\wstim\controller;
use think\addons\Controller;
use addons\wstim\model\AutoReplys as M;
class Autoreplys extends Controller{
    protected $beforeActionList = ['checkShopAuth'];
	/**
     * 列表页面
     */
    public function index(){
    	return $this->fetch("shop/autoreplys/list");
    }
    
    /**
     * 跳去新增/编辑页面
     */
    public function toEdit(){
        $id = (int)input("id");
        $m = new M();
        if($id>0){
            $object = $m->getById($id);
        }else{
            $object = $m->getEModel('auto_replys');
        }
        $this->assign('object',$object);
        $this->assign("p",(int)input('p'));
        return $this->fetch("shop/autoreplys/edit");
    }
    /**
     * 编辑
     */
    public function edit(){
        $m = new M();
        if((int)input('id')>0){
            return $m->edit();
        }
        return $m->add();
    }
    /**
     * 删除
     */
    public function del(){
        $m = new M();
        return $m->del();
    }

    /**
     * 获取数据
     */
    public function getById(){
        $m = new M();
        return $m->getById((int)input("id"));
    }
    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        return WSTGrid($m->pageQuery());
    }
    
}
