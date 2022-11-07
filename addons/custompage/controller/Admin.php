<?php
namespace addons\custompage\controller;

use think\addons\Controller;
use addons\custompage\model\CustomPages as M;
use addons\custompage\model\CustomPageDecoration as DM;
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
 * 首页自定义布局后台管理控制器
 */
class Admin extends Controller{
    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $this->assign("p",(int)input("p"));
        $this->assign("type",(int)input("type",1));
        return $this->fetch("/admin/custompages/list");
    }
    /**
     * 获取分页
     */
    public function pageQuery(){
        $m = new M();
        $rs = $m->pageQuery();
        return WSTReturn('',1,$rs);
    }

    /**
     * 设置是否为首页
     */
    public function editIsIndex(){
        $m = new M();
        $rs = $m->editIsIndex();
        // 当用户选择默认模板设置为首页，且默认模板的静态文件不存在，则生成默认模板的静态文件
        $id = (int)input('id',0);
        $type = (int)input('type');
        $mobile_html_path = WSTRootPath() . "/addons/custompage/view/tpl/mobile/custom_page_index_1.html";
        $wechat_html_path = WSTRootPath() . "/addons/custompage/view/tpl/wechat/custom_page_index_2.html";
        $home_html_path = WSTRootPath() . "/addons/custompage/view/tpl/home/custom_page_index_4.html";
        if($id == 1 && !file_exists($mobile_html_path))$this->build($id,$type);
        if($id == 2 && !file_exists($wechat_html_path))$this->build($id,$type);
        if($id == 4 && !file_exists($home_html_path))$this->build($id,$type);
        return $rs;
    }

    /**
     * 删除
     */
    public function del(){
        $m = new M();
        $rs = $m->del();
        return $rs;
    }

    /*
     * 链接地址说明
     */
    public function link(){
        $this->assign("type",(int)input("type",1));
        return $this->fetch("/admin/custompages/link");
    }

    /**
     * 复制自定义页面
     */
    public function copyCustomPage(){
        $m = new M();
        return $m->copyCustomPage();
    }

    /**
     * 生成自定义页面静态文件
     */
    public function build($pageId,$pageType){
        $m = new DM();
        //静态文件路径
        $html_path = '';
        $fetch_path = '';
        switch ($pageType){
            case 1:
                $html_path = WSTRootPath() . "/addons/custompage/view/tpl/mobile/";
                $fetch_path = '/admin/custompagedecoration/mo_index';
                break;
            case 2:
                $html_path = WSTRootPath() . "/addons/custompage/view/tpl/wechat/";
                $fetch_path = '/admin/custompagedecoration/wx_index';
                break;
            case 4:
                $html_path = WSTRootPath() . "/addons/custompage/view/tpl/home/";
                $fetch_path = '/admin/custompagedecoration/pc_index';
                break;
        }
        if(!is_dir($html_path)){
            if (!@mkdir($html_path, 0755)){
                return WSTReturn(lang("custompage_page_create_fail"),-1);
            }
        }
        $file_name = 'custom_page_index_'.$pageId;
        $customPageData = $m->getCustomPageDecorationData($pageId);
        $this->assign('customPageData',$customPageData);
        $temp = $this->fetch($fetch_path);
        $rs = file_put_contents($html_path . $file_name . '.html', $temp);
        if($rs) {
            return WSTReturn(lang("custompage_page_create_success"),1);
        } else {
            return WSTReturn(lang("custompage_page_create_fail"),-1);
        }
    }
}
