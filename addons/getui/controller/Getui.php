<?php
namespace addons\getui\controller;

use think\addons\Controller;
use addons\getui\model\Getui as M;
use Request;
/**
 * 个推推送接口
 */
class Getui extends Controller{
    public function Index(){
    }
    /**
     * 单推
     * @param userId int 用户id（客服id）
     * @param content string 内容
     */
    public function pushMessageToSingle(){
        $m = new M();
        $rs = $m->pushMessageToSingle();
        return $rs;
    }
}