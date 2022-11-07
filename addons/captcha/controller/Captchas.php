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
class Captchas extends Controller{
	public function __construct(){
		parent::__construct();
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}

   /**
    * 获取验证码
    */
   public function getCaptcha(){
   	   $m = new M();
       return $m->getCaptcha();
   }

   /**
    * 对比验证码是否正确
    */
   public function checkRegistCaptcha(){
       $m = new M();
       return $m->checkRegistCaptcha();
   }
   /**
    * 显示图片
    */
   function captcha(){
       WSTCaptcha();
   }
   /**
    * 解除手机绑定
    */
   public function checkUnBindCaptcha(){
       $m = new M();
       return $m->checkUnBindCaptcha();
   }
   /**
    * 再次绑定手机
    */
   public function checkReBindCaptcha(){
       $m = new M();
       return $m->checkReBindCaptcha();
   }
   /**
    * 验证已绑定的手机号[忘记密码]
    */
    public function checkVerfiyCaptcha(){
       $m = new M();
       return $m->checkVerfiyCaptcha();
   }
   /**
    * 绑定手机
    */
   public function checkBindCaptcha(){
       $m = new M();
       return $m->checkBindCaptcha();
   }

   /**
    * 登录验证
    */
   public function checkLoginCaptcha(){
       $m = new M();
       return $m->checkLoginCaptcha();
   }

   /**
    * 忘记密码
    */
   public function checkForgetCaptcha(){
       $m = new M();
       return $m->checkForgetCaptcha();
   }

/**********************************************供货商******************************************/
   /**
    * 绑定手机
    */
   public function checkSupplierBindCaptcha(){
       $userId = (int)session('WST_SUPPLIER.userId');
       $m = new M();
       return $m->checkBindCaptcha($userId);
   }
   /**
    * 再次绑定手机
    */
   public function checkSupplierReBindCaptcha(){
       $userId = (int)session('WST_SUPPLIER.userId');
       $m = new M();
       return $m->checkReBindCaptcha($userId);
   }
   /**
    * 解除手机绑定
    */
   public function checkSupplierUnBindCaptcha(){
       $userId = (int)session('WST_SUPPLIER.userId');
       $m = new M();
       return $m->checkUnBindCaptcha($userId);
   }
   /**
    * 验证已绑定的手机号[忘记密码]
    */
    public function checkSupplierVerfiyCaptcha(){
       $userId = (int)session('WST_SUPPLIER.userId');
       $m = new M();
       return $m->checkVerfiyCaptcha($userId);
   }
/**********************************************小程序******************************************/

   /**
    * 获取验证码
    */
   public function weGetCaptcha(){
      $m = new M();
      $rs = $m->getCaptcha();
      return jsonReturn('success',1,$rs);
   }

   /**
    * 对比验证码是否正确
    */
   public function weCheckRegistCaptcha(){
       $m = new M();
       $rs = $m->checkRegistCaptcha();
       return jsonReturn('success',1,$rs);
   }
   
   function weCaptcha(){
       $rs = WSTCaptcha(1);
       return jsonReturn('success',1,$rs);
   }

   /**
    * 登录验证
    */
   public function weCheckLoginCaptcha(){
       $m = new M();
       $rs = $m->checkLoginCaptcha();
       return jsonReturn('success',1,$rs);
   }

  /**
    * 验证已绑定的手机号[忘记密码]
    */
  public function weCheckVerfiyCaptcha(){
      $userId= model('weapp/index')->getUserId();
      $m = new M();
      $rs = $m->checkVerfiyCaptcha($userId);
      return jsonReturn('success',1,$rs);
  }

   /**
    * 忘记密码
    */
   public function weCheckForgetCaptcha(){
      $userId= model('weapp/index')->getUserId();
      $m = new M();
      $rs =  $m->checkForgetCaptcha($userId);
      return jsonReturn('success',1,$rs);
   }

   /**
    * 绑定手机
    */
   public function weCheckBindCaptcha(){
      $userId = model('weapp/index')->getUserId();
      $m = new M();
      $rs = $m->checkBindCaptcha($userId);
      return jsonReturn('success',1,$rs);
   }

   /**
     * 解除手机绑定
     */
    public function weCheckUnBindCaptcha(){
        $userId = model('weapp/index')->getUserId();
        $m = new M();
        $rs = $m->checkUnBindCaptcha($userId);
        return jsonReturn('success',1,$rs);
    }
    /**
     * 再次绑定手机
     */
    public function weCheckReBindCaptcha(){
        $userId = model('weapp/index')->getUserId();
        $m = new M();
        $rs = $m->checkReBindCaptcha($userId);
        return jsonReturn('success',1,$rs);
    }
}