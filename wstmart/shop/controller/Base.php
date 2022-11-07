<?php

namespace wstmart\shop\controller;

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
 * 基础控制器
 */

use think\Controller;
use think\Db;

class Base extends Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $lang = WSTSwitchLang();
        //\think\facade\Lang::load(APP_PATH . 'shop' . DS . 'lang' . DS . $lang . '.php');
        $this->assign("lang", $lang);
        WSTLangJs($lang);
        $style = 'default';
        $this->assign("sysstyle",$style);
        $this->assign("v", WSTConf('CONF.wstVersion'));
        $this->view->filter(function ($content) {
            $content = str_replace("__SHOP__", str_replace('/index.php', '', $this->request->root()) . '/wstmart/shop/view/default', $content);
            $content = str_replace("__RESOURCE_PATH__", WSTConf('CONF.resourcePath'), $content);
            return $content;
        });
    }
    protected function fetch($template = '', $vars = [], $config = [])
    {
        return $this->view->fetch("default/" . $template, $vars, $config);
    }

    public function getVerify()
    {
        WSTVerify();
    }
    /**
     * 上传图
     */
    public function uploadPic()
    {
        $this->checkAuth();
        return WSTUploadPic(0);
    }
    /**
     * 上传视频
     */
    public function uploadVideo()
    {
        $this->checkAuth();
        return WSTUploadVideo();
    }

    /**
     * 编辑器上传文件
     */
    public function editorUpload()
    {
        $this->checkAuth();
        return WSTEditUpload(0);
    }

    //登录验证方法--商家
    protected function checkAuth()
    {
        $USER = session('WST_USER');
        if (!empty($USER) && $USER['userType'] == 1) {
            //如果是店主就跳转，不是店主的话，就判断请求是否有权限。
            if (!$USER['SHOP_MASTER']) {
                $request = request();
                $visit = strtolower($request->module() . "/" . $request->controller() . "/" . $request->action());
                if (!in_array($visit, $USER['visitPrivilegeUrls'])) {
                    if (request()->isAjax()) {
                        die('{"status":-998,"msg":"' . lang('no_permission') . '"}');
                    } else {
                        die('no_permission');
                    }
                }
            }
        } else {
            if (request()->isAjax()) {
                die('{"status":-999,"msg":"' . lang("not_logged") . '"}');
            } else {
                $this->redirect('shop/index/login');
                exit;
            }
        }
    }
}
