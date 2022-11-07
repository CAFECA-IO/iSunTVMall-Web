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
 * 插件控制器
 */
class Apis extends Controller{
    /**
     * 获取验证码
     */
    public function getCaptcha(){
        $m = new M();
        $rs = $m->getCaptcha();
        $rs['data']['imageCaptcha'] = WSTCaptcha(1);
        return json_encode($rs);
    }

    /**
     * 登录验证
     */
    public function checkLoginCaptcha(){
        $m = new M();
        return json_encode($m->checkLoginCaptcha());
    }

    /**
     * 注册验证
     */
    public function checkRegistCaptcha(){
        $m = new M();
        return json_encode($m->checkRegistCaptcha());
    }

    /**
     * 解除手机绑定
     */
    public function checkUnBindCaptcha(){
        $m = new M();
        $userId = model('app/index')->getUserId();
        return json_encode($m->checkUnBindCaptcha($userId));
    }
    /**
     * 再次绑定手机
     */
    public function checkReBindCaptcha(){
        $m = new M();
        $userId = model('app/index')->getUserId();
        return json_encode($m->checkReBindCaptcha($userId));
    }
    /**
     * 验证已绑定的手机号[忘记支付密码]
     */
    public function checkVerfiyCaptcha(){
        $m = new M();
        $userId = model('app/index')->getUserId();
        return json_encode($m->checkVerfiyCaptcha($userId));
    }
    /**
     * 绑定手机
     */
    public function checkBindCaptcha(){
        $m = new M();
        $userId = model('app/index')->getUserId();
        return json_encode($m->checkBindCaptcha($userId));
    }

    /**
     * 验证已绑定的手机号[忘记登录密码]
     */
    public function checkForgetCaptcha(){
        $m = new M();
        return json_encode($m->checkForgetCaptcha());
    }
}