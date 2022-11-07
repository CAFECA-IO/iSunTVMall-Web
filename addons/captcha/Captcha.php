<?php
namespace addons\captcha;  // 注意命名空间规范

use Env;
use think\facade\Lang;
use think\addons\Addons;
use addons\captcha\model\Captchas as DM;

/**
 * WSTMart 图形验证码接口
 * @author WSTMart
 */
class Captcha extends Addons{
    // 该插件的基础信息
    public $info = [
        'name' => 'Captcha',   // 插件标识
        'title' => '圖形驗證碼',  // 插件名称
        'description' => '商淘雲圖形驗證碼',    // 插件简介
        'status' => 0,  // 状态
        'author' => 'WSTMart',
        'version' => '1.0.0'
    ];

	
    /**
     * 插件安装方法
     * @return bool
     */
    public function install(){
        $m = new DM();
        $lang = WSTSwitchLang();  
        $langFile = Env::get('root_path').'addons'.DS.'captcha'.DS.'lang'.DS.$lang.'.php';
        if(is_file($langFile)){
            Lang::load($langFile);
        }
        $flag = $m->install();
        WSTLangAddonJs('captcha');
    	WSTClearHookCache();
        return $flag;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall(){
        $m = new DM();
        $flag = $m->uninstall();
    	WSTClearHookCache();
        return $flag;
    }
    
	/**
     * 插件启用方法
     * @return bool
     */
    public function enable(){
        $m = new DM();
        $m->changgeEnableStatus(1);
        WSTLangAddonJs('captcha');
    	WSTClearAllCache();
        return true;
    }
    
    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable(){
        $m = new DM();
        $m->changgeEnableStatus(0);
    	WSTClearAllCache();
    	return true;
    }

    /**
     * 插件设置方法
     * @return bool
     */
    public function saveConfig(){
    	WSTClearHookCache();
    	return true;
    }
    
    /**
     * 电脑端验证码
     */
    function homeDocumentRegistSmsCaptcha($params){
       return $this->fetch('view/home/regist');
    }

    /**
     * 校验图片是否正确
     */
    public function checkSendSmsCaptcha($params){
        $params['rdata'] = WSTReturn(lang('captcha_sms_send_success'),1);
        return true;
    }

    /**
     * 解除手机绑定
     */
    public function homeDocumentUnbindSmsCaptcha(){
        return $this->fetch('view/home/unbind');
    }
    /**
     * 再次绑定手机验证码
     */
    function homeDocumentReBindSmsCaptcha(){
        return $this->fetch('view/home/rebind');
    }
    /**
     * 验证绑定的手机[忘记密码]
     */
    function homeDocumentVerfiySmsCaptcha(){
        return $this->fetch('view/home/verfiy');
    }
    /**
     * 绑定手机验证码
     */
    function homeDocumentBindSmsCaptcha(){
        return $this->fetch('view/home/bind');
    }
    /**
     * 手机登录验证
     */
    function homeDocumentLoginSmsCaptcha(){
        return $this->fetch('view/home/login');
    }
    /**
     * 忘记密码
     */
    function homeDocumentForgetSmsCaptcha(){
        return $this->fetch('view/home/forget');
    }


    /**
     * 商家端绑定手机验证码
     */
    function shopDocumentBindSmsCaptcha(){
        return $this->fetch('view/shop/bind');
    }
    /**
     * 商家端解除手机绑定
     */
    public function shopDocumentUnbindSmsCaptcha(){
        return $this->fetch('view/shop/unbind');
    }
    /**
     * 商家端再次绑定手机验证码
     */
    function shopDocumentReBindSmsCaptcha(){
        return $this->fetch('view/shop/rebind');
    }
    /**
     * 商家端验证绑定的手机[忘记支付密码]
     */
    function shopDocumentVerfiySmsCaptcha(){
        return $this->fetch('view/shop/verfiy');
    }


    /**
     * 供货商端绑定手机验证码
     */
    function supplierDocumentBindSmsCaptcha(){
        return $this->fetch('view/supplier/bind');
    }
    /**
     * 供货商解除手机绑定
     */
    public function supplierDocumentUnbindSmsCaptcha(){
        return $this->fetch('view/supplier/unbind');
    }
    /**
     * 供货商再次绑定手机验证码
     */
    function supplierDocumentReBindSmsCaptcha(){
        return $this->fetch('view/supplier/rebind');
    }
    /**
     * 供货商验证绑定的手机[忘记支付密码]
     */
    function supplierDocumentVerfiySmsCaptcha(){
        return $this->fetch('view/supplier/verfiy');
    }


    /**
     * 手机端注册验证码
     */
    function mobileDocumentRegistSmsCaptcha($params){
       return $this->fetch('view/mobile/regist');
    }
    /**
     * 手机登录验证
     */
    function mobileDocumentLoginSmsCaptcha(){
        return $this->fetch('view/mobile/login');
    }
    /**
     * 绑定手机[绑定、再次绑定]
     */
    function mobileDocumentBindSmsCaptcha(){
        return $this->fetch('view/mobile/bind');
    }
    /**
     * 解除手机绑定
     */
    function mobileDocumentUnBindSmsCaptcha(){
        return $this->fetch('view/mobile/unbind');
    }
    /**
     * 验证绑定的手机[忘记支付密码]
     */
    function mobileDocumentVerfiySmsCaptcha(){
        return $this->fetch('view/mobile/verfiy');
    }
    /**
     * 忘记密码
     */
    public function mobileDocumentForgetSmsCaptcha(){
        return $this->fetch('view/mobile/forget');
    }



    /**
     * 微信手机注册验证码
     */
    function wechatDocumentRegistSmsCaptcha($params){
       return $this->fetch('view/wechat/regist');
    }
    /**
     * 微信登录验证
     */
    function wechatDocumentLoginSmsCaptcha(){
        return $this->fetch('view/wechat/login');
    }
    /**
     * 绑定手机[绑定、再次绑定]
     */
    function wechatDocumentBindSmsCaptcha(){
        return $this->fetch('view/wechat/bind');
    }
    /**
     * 解除手机绑定
     */
    function wechatDocumentUnBindSmsCaptcha(){
        return $this->fetch('view/wechat/unbind');
    }
    /**
     * 验证绑定的手机[忘记支付密码]
     */
    function wechatDocumentVerfiySmsCaptcha(){
        return $this->fetch('view/wechat/verfiy');
    }
    /**
     * 忘记密码
     */
    public function wechatDocumentForgetSmsCaptcha(){
        return $this->fetch('view/wechat/forget');
    }
}