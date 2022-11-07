<?php
namespace addons\custompage\controller;

use think\addons\Controller;
use addons\custompage\model\CustomPageDecoration as M;
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
 * 自定义页面详情控制器
 */

class Custompagedecoration extends Controller{
    public function index(){
        $m = new M();
        $pageId = (int)input('id',0);
        $pageType = (int)input('type',1);
        $data = $m->pageQuery($pageId);
        $page_data = $m->pageDetail($pageId);
        if($page_data)$page_data["attr"] = unserialize($page_data["attr"]);
        $tabbar_data = [];
        foreach($data as $k => &$v){
            if($v["name"] == "tabbar"){
                $tabbar_data[] = $v;
            }

            if ($v["name"] == "coupon") {
                if(WSTConf('WST_ADDONS.coupon')!='') {
                    $data[$k]['coupons'] = $m->getCouponsByIds($v['attr']['coupon_select_ids']);
                }else {
                    if(isset($v['attr']['style'])){
                        $couponArr = [];
                        $couponItem = [
                            'couponValue'=>0,
                            'useCondition'=>0
                        ];
                        if($v['attr']['style'] == 1){
                            array_push($couponArr,$couponItem);
                            array_push($couponArr,$couponItem);
                            array_push($couponArr,$couponItem);
                        }else{
                            array_push($couponArr,$couponItem);
                            array_push($couponArr,$couponItem);
                        }
                        $data[$k]['coupons'] = $couponArr;
                    }
                }
            }
        }
        $this->assign("pageId",$pageId);
        $this->assign("pageType",$pageType);
        $this->assign("data",$data);
        $this->assign("page_data",$page_data);
        $this->assign("tabbar_data",$tabbar_data);
        $this->assign("p",(int)input("p"));
        $floors = ($pageType==4)?$m->getFloors():'';
        $this->assign("floors",$floors);
        if($pageType==4){
            return $this->fetch("/admin/custompagedecoration/home_index");
        }else{
            return $this->fetch("/admin/custompagedecoration/index");
        }
    }

    /*
     * 保存首页自定义装修内容
     */
    public function edit(){
        $m = new M();
        $pageId = $m->edit();
        $pageType = input('page_type');
        // 手机端、微信端和电脑端生成静态文件
        if(in_array($pageType,[1,2,4])){
            return $this->build($pageId,$pageType);
        }
        return WSTReturn(lang("custompage_operation_success"),1);
    }


    /**
     * 加载商品数据
     */
    public function goodPageQuery(){
        $m = new M();
        return WSTGrid($m->goodPageQuery());
    }

    /**
     * 加载优惠券数据
     */
    public function couponPageQuery(){
        $m = new M();
        return WSTGrid($m->couponPageQuery());
    }

    /**
     * 加载新闻数据
     */
    public function newPageQuery(){
        $m = new M();
        return WSTGrid($m->newPageQuery());
    }

    /**
     * 生成自定义页面静态文件
     */
    public function build($pageId,$pageType){
        $m = new M();
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
