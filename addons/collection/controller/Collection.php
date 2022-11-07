<?php
namespace addons\collection\controller;
use Env;
use think\addons\Controller;
use addons\collection\model\Collection as M;

class Collection extends Controller{

    public function __construct(){
        parent::__construct();
        $this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
    }
   
    
    /**
     * 加载店铺分销设置
     */
    public function index(){
        return $this->fetch("/shop/index");
    }

    /**
     * 获取用户分成列表
     */
    public function save(){
        $m = new M();
        $rs = $m->collectionSave();
        return $rs;
    }

    /**
     * 上传商品数据
     */
    public function importGoods(){
        $rs = WSTUploadFile();  
        if(json_decode($rs)->status==1){
            $m = new M();
            $rss = $m->importGoods($rs);
            return $rss;
        }
        return $rs;
    }
}