<?php
namespace wstmart\admin\controller;
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
class Base extends Controller {
	public function __construct(){
		parent::__construct();
		$lang = WSTSwitchLang();
		\think\facade\Lang::load(APP_PATH . 'admin' . DS . 'lang' . DS . $lang . '.php');
        $this->assign("lang", $lang);
        WSTLangJs($lang);
		$this->assign("v",WSTConf('CONF.wstVersion'));
		$this->view->filter(function($content){
			$content = str_replace("__ADMIN__",str_replace('/index.php','',$this->request->root()).'/wstmart/admin/view',$content);
            $content = str_replace("__RESOURCE_PATH__",WSTConf('CONF.resourcePath'),$content);
            return $content;
        });
	}
    protected function fetch($template = '', $vars = [], $config = [])
    {
        return $this->view->fetch($template, $vars, $config);
    }

	public function getVerify(){
		WSTVerify();
	}
	
	public function uploadPic(){
		return WSTUploadPic(1);
	}

	/**
    * 编辑器上传文件
    */
    public function editorUpload(){
        return WSTEditUpload(1);
    }
}