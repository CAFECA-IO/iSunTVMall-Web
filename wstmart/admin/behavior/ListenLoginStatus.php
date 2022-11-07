<?php
namespace wstmart\admin\behavior;
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
 * 检测用户有没有登录
 */
class ListenLoginStatus
{
    public function run($params){
        $STAFF = session('WST_STAFF');
        $allowUrl = ['admin/index/login',
			         'admin/index/checklogin',
			         'admin/index/logout',
                     'admin/index/getverify',
                     'admin/cronjobs/autocancelnopay',
                     'admin/cronjobs/autoappraise',
                     'admin/cronjobs/autoreceive',
                     'admin/cronjobs/autosendmsg',
        			 'admin/cronjobs/autofilexml',
        			 'admin/cronjobs/autodealorderservice',
                     'admin/cronjobs/autoshopsettlement',
                     'admin/cronjobs/autochangeshopstatus',
                     'admin/cronjobs/autochangesupplierstatus',
                     'admin/cronjobs/autohandleshoprefund',
                     'admin/cronjobs/autohandlesupplierrefund',
                     'admin/cronjobs/clearposter',
                     'admin/orderrefunds/wxrefundnodify',
                     'admin/orderrefunds/wxapprefundnodify',
                     'admin/supplierorderrefunds/wxrefundnodify',
                     'admin/shops/wxenterrefundnotify',
                     'admin/suppliers/wxenterrefundnotify',
                     'admin/orderrefunds/paypalrefundnodify'
			        ];
	    $request = request();
        $visit = strtolower($request->module()."/".$request->controller()."/".$request->action());
        if(empty($STAFF) && !in_array($visit,$allowUrl)){
	        if($request->isAjax()){
	        	echo json_encode(['status'=>-999,'msg'=>lang('login_out')]);
	        }else{
	        	header("Location:".url('admin/index/login'));
	        }
	        exit();
        }
    }
}
