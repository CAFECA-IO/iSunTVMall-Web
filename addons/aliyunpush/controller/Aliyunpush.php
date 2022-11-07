<?php
namespace addons\aliyunpush\controller;

use think\addons\Controller;
use addons\aliyunpush\model\AliyunPush as M;
use Request;
/**
 * 阿里云推送接口
 */
class Aliyunpush extends Controller{
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