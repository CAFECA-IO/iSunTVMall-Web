<?php 
namespace addons\wstim\controller;
use think\addons\Controller;
use addons\wstim\model\DisableKeywords as M;
// 店铺禁用关键字控制器
class DisableKeywords extends Controller{
    protected $beforeActionList = ['checkAdminAuth'];
    /**
     * 禁用关键字
     */
    public function adminIndex(){
        return $this->fetch('admin/disablekeyword/index');
    }
    /**
     * 禁用关键字
     */
    public function commit(){
        return (new M())->commit();
    }
    /**
     * 禁用关键字
     */
    public function getKeywords(){
        return (new M())->getKeywords();
    }


}