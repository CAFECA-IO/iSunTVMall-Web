<?php
namespace addons\wstim\model;
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
 * 店铺客服业务处理
 */
use think\Model;
use think\Db;
use wstmart\admin\validate\Users;
class ImChatStatistics extends Model {
    /**
     * 查看用户访问统计
     */
	public function pageQuery(){
        $shopId = (int)session('WST_USER.shopId');
        // 查询客服评分
        $page = $this->alias('ist')
                     ->join("users u",'u.userId=ist.userId','inner')
                     ->where([['shopId','=',$shopId]])
                     ->field('ist.*,u.loginName')
                     ->paginate(input('limit/d'))
                     ->toArray();
		return $page;
    }
}