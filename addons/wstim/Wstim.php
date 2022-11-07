<?php

namespace addons\wstim;    // 注意命名空间规范
use think\addons\Addons;
use think\Db;
use addons\wstim\model\Chats as M;
use addons\wstim\model\PushNotication;
use Env;
use think\facade\Lang;

/**
 * 客服插件
 * @author byron sampson
 */
class Wstim extends Addons    // 需继承think\addons\Addons类
{
    static $gatewayError = 0;
    /*
        INSERT INTO `wst_hooks` VALUES (null, 'homeDocumentContact', '在线客服pc版入口', '1', '2018-05-09 15:03:53', 'Wstim');
        INSERT INTO `wst_hooks` VALUES (null, 'homeDocumentListener', '在线客服pc版监听', '1', '2018-05-09 15:03:53', 'Wstim');
        INSERT INTO `wst_hooks` VALUES (null, 'mobileDocumentContact', '在线客服手机版入口', '1', '2018-05-10 14:54:30', 'Wstim');
        INSERT INTO `wst_hooks` VALUES (null, 'mobileDocumentBottomNav', '最近会话列表入口', '1', '2018-05-10 15:10:14', 'Wstim');
        INSERT INTO `wst_hooks` VALUES (null, 'wechatDocumentBottomNav', '最近会话列表入口', '1', '2018-05-10 16:33:58', 'Wstim');
        INSERT INTO `wst_hooks` VALUES (null, 'wechatDocumentContact', '在线客服微信版入口', '1', '2018-05-10 16:34:29', 'Wstim');
        INSERT INTO `wst_hooks` VALUES (null, 'adminAfterConfigAddon', '插件配置保存之后', '1', '2018-05-10 17:24:42', 'Wstim');
    */
    // 该插件的基础信息
    public $info = [
        'name' => 'Wstim',    // 插件标识
        'title' => '客服插件',    // 插件名称
        'description' => 'wstmart客服插件',    // 插件简介
        'status' => 0,    // 状态
        'author' => 'WSTMart',
        'version' => '1.0.0'
    ];

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        $m = new M();
        $lang = WSTSwitchLang();
        $langFile = Env::get('root_path') . 'addons' . DS . 'wstim' . DS . 'lang' . DS . $lang . '.php';
        if (is_file($langFile)) {
            Lang::load($langFile);
        }
        $flag = $m->toggleShow(1);
        WSTLangAddonJs('wstim');
        WSTClearHookCache();
        return $flag;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        $m = new M();
        $flag = $m->toggleShow(0);
        WSTClearHookCache();
        return $flag;
    }

    /**
     * 插件设置方法
     * @return bool
     */
    public function saveConfig()
    {
        WSTClearHookCache();
        return true;
    }
    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $m = new M();
        $lang = WSTSwitchLang();
        $langFile = Env::get('root_path') . 'addons' . DS . 'wstim' . DS . 'lang' . DS . $lang . '.php';
        if (is_file($langFile)) {
            Lang::load($langFile);
        }
        WSTLangAddonJs('wstim');
        $flag = $m->install();
        WSTClearHookCache();
        return $flag;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        $m = new M();
        $flag = $m->uninstall();
        WSTClearHookCache();
        return $flag;
    }
    /**
     * http推送消息
     * $params = ["type"=>"pushToSingle", "userId"=>"目标用户id", "title"=>"推送标题", "content"=>"推送内容"]
     */
    public function pushNoticeToApp($params)
    {
        // 异常捕获，防止未启动socket服务时调用钩子报错
        // 错误数不为0 直接return
        if (self::$gatewayError == 1) return;
        try {
            (new PushNotication())->pushToSingle($params);
        } catch (\Exception $e) {
            ++self::$gatewayError;
            return;
        }
    }




    /**
     * 判断是否为客服【用户登录时调用】
     */
    public function afterUserLogin($param)
    {
        // 根据用户id查出serviceId
        $serviceId = Db::name('shop_users')->where(['userId' => $param['user']['userId'], 'dataFlag' => 1])->value('serviceId');
        if ($serviceId == '') return;
        $isService = Db::name('shop_services')
            ->where(['serviceId' => $serviceId, 'dataFlag' => 1])
            ->find();
        if (!empty($isService)) {
            $param['user']['isService'] = 1;
            $param['user']['shopId'] = $isService['shopId'];
            // 客服id
            $param['user']['serviceId'] = $serviceId;
            if (!isset($param['isApp'])) session('WST_USER', $param['user']);
        }
    }
    /**
     * 保存插件配置文件之后
     */
    public function adminAfterConfigAddon($param)
    {
        $data = Db::name('addons')->where(['addonId' => $param['addonId']])->find();
        if (strtolower($data['name']) == 'wstim') {
            // workerman配置文件写入
            $config = json_decode($data['config'], true);
            // 表前缀
            $config['prefix'] = config('database.prefix');
            $value = var_export($config, true);
            $filePath = WST_ADDON_PATH . 'wstim/workerman_config.php';
            file_put_contents($filePath, ("<?php \n\r return " . $value . ";"));
        }
    }
    /************************************ admin ************************************/

    /************************************ home ************************************/
    /**
     * 用户端端监听
     */
    public function homeDocumentListener()
    {
        if (session('WST_USER.userId') > 0) {
            $filePath = '__ROOT__/addons/wstim/view/home/listener.js';
            $code = "<script src='$filePath'></script>
                     <script>var IM_PATH = '__ROOT__/addons/wstim/view/';</script>";
            return $this->display($code);
        }
    }
    /**
     *  商家端监听
     */
    public function shopDocumentListener()
    {
        if (session('WST_USER.userId') > 0) {
            $filePath = '__ROOT__/addons/wstim/view/home/listener.js';
            $code = "<script src='$filePath'></script>
                    <script>var IM_PATH = '__ROOT__/addons/wstim/view/';</script>";
            return $this->display($code);
        }
    }
    /**
     * pc版客服入口
     */
    public function homeDocumentContact($param)
    {
        switch ($param['type']) {
                // 商品详情入口
            case 'goodsDetail':
                $url = url('/addon/wstim-chats-index') . "?shopId={$param['shopId']}&goodsId={$param['goodsId']}";
                $code = "<a style='color:green;' target='_blank'
                    href='$url'>
                    <img src='__ROOT__/addons/wstim/view/home/img/custom.png' style='vertical-align: sub;width: 18px;height: 16px;' title='".lang('wstim_online_service')."' alt='".lang('wstim_online_service')."' />
                    ".lang('wstim_online_service')."
                  </a>";
                break;
                // 店铺主页入口
            case 'shopHome':
                $url = url('/addon/wstim-chats-index') . "?shopId={$param['shopId']}";
                $code = "<a  target='_blank' title='".lang('wstim_online_service')."'
                        href='$url'>
                      <img src='__ROOT__/addons/wstim/view/home/img/custom.png' style='vertical-align: middle;width: 23px;height: 21px;' alt='".lang('wstim_online_service')."' />
                      </a>";
                break;
                // 店铺客服入口
            case 'shopService':
                $code = '';
                if (session('WST_USER.userId') > 0 && session('WST_USER.isService') == 1) {
                    $code = "<div><a href='{:url('/addon/wstim-shopchats-index')}' target='_blank'>".lang('wstim_customer_service_background')."</a></div>";
                }
                break;
        }
        return $this->display($code);
    }
    /************************************ mobile ************************************/
    public function mobileDocumentContact($param)
    {
        // 隐藏默认qq客服入口
        echo "<script>$('.J_service').hide()</script>";
        $url = url('/addon/wstim-chats-moIndex') . "?shopId={$param['shopId']}";
        switch ($param['type']) {
                // 商品详情入口
            case 'goodsDetail':
                $url .= "&goodsId={$param['goodsId']}";
                $code = "<a href='$url'>
                            <span class='img qq'></span><span class='word'>".lang('wstim_service')."</span></a>";
                break;
                // 店铺主页入口
            case 'shopHome':
                $code = "<a target='_blank'
                            style='color:#fff;padding-right:15px;'
                            title='".lang('wstim_contact_service')."' href='$url'><img style='width:0.16rem;height:0.15rem;margin-right:0.05rem' src='__ROOT__/addons/wstim/view/mobile/img/icon-service.png'>".lang('wstim_contact_service')."</a>";
                break;
                // 店铺介绍页
            case 'shopIndex':
                $code = "<a  href='$url' 
                            style='width:45.5%;margin-right:15px;' 
                            class='ui-btn-lg shop-home-btn ui-col ui-col-50'>".lang('wstim_contact_merchant')."</a>";
                break;
        }
        return $this->display($code);
    }
    /**
     * 替换底部导航图标(隐藏关注，显示消息列表入口)
     */
    public function mobileDocumentBottomNav()
    {
        if (session('WST_USER.userId') > 0) {
            $filePath = '__ROOT__/addons/wstim/view/mobile/listener.js';
            $code = "<script src='$filePath'></script>
                    <script>var IM_PATH = '__ROOT__/addons/wstim/view/';</script>";
            return $this->display($code);
        }
    }

    /************************************ wechat ************************************/
    public function wechatDocumentContact($param)
    {
        // 隐藏默认qq客服入口
        echo "<script>$('.J_service').hide();function goToChat(url){if(WST.conf.IS_LOGIN==0){return WST.inLogin(url)};window.location.href= url;}</script>";
        $url = url('/addon/wstim-chats-wxIndex') . "?shopId={$param['shopId']}";
        switch ($param['type']) {
                // 商品详情入口
            case 'goodsDetail':
                $url .= "&goodsId={$param['goodsId']}";
                $code = "<a onclick='goToChat(\"{$url}\")'>
                            <span class='img qq'></span><span class='word'>".lang('wstim_service')."</span></a>";
                break;
                // 店铺主页入口
            case 'shopHome':
                $code = "<a onclick='goToChat(\"{$url}\")'
                            target='_blank'
                            style='color:#fff;padding-right:15px;'
                            title='".lang('wstim_contact_service')."'><img style='width:0.16rem;height:0.15rem;margin-right:0.05rem' src='__ROOT__/addons/wstim/view/mobile/img/icon-service.png'>".lang('wstim_contact_service')."</a>";
                break;
                // 店铺介绍页
            case 'shopIndex':
                $code = "<a onclick='goToChat(\"{$url}\")'
                            style='width:45.5%;margin-right:15px;' 
                            class='ui-btn-lg shop-home-btn ui-col ui-col-50'>".lang('wstim_contact_merchant')."</a>";
                break;
        }
        return $this->display($code);
    }
    /**
     * 替换底部导航图标(隐藏关注，显示消息列表入口)
     */
    public function wechatDocumentBottomNav()
    {
        if (session('WST_USER.userId') > 0) {
            $filePath = '__ROOT__/addons/wstim/view/mobile/listener.js';
            $code = "<script src='$filePath'></script>
                    <script>var IM_PATH = '__ROOT__/addons/wstim/view/';</script>";
            return $this->display($code);
        }
    }
}
