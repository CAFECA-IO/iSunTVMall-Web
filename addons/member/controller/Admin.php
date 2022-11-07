<?php
namespace addons\member\controller;

use think\addons\Controller;
use addons\member\model\Member as M;

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
 * 会员营销后台管理控制器
 */
class Admin extends Controller{
    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $m = new M();
        $cfg = $m->getAddonConfig();
        $recommends = $m->getMemberRecommendConfigs();
        $this->assign("recommendSwitch",$cfg['recommendSwitch']);
        $this->assign("registerSwitch",$cfg['registerSwitch']);
        $this->assign("registerScore",$cfg['registerScore']);
        $this->assign("mallShareTitle",$cfg['mallShareTitle']);
        $this->assign("posterBg",$cfg['posterBg']);
        $this->assign("recommends",$recommends);
        return $this->fetch("/admin/index");
    }

    /**
     * 编辑
     */
    public function edit(){
        $m = new M();
        $rs = $m->edit();
        return $rs;
    }


}