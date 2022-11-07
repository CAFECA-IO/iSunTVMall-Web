<?php
namespace addons\txlive\controller;
use think\addons\Controller;
use addons\txlive\model\TxLives as M;
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
 * APP端腾讯云直播控制器
 */
class Apis extends Controller{
    /*
     * 获取直播间列表数据
     */
    public function pageQuery(){
        $m = new M();
        $rs = $m->roomPageQuery();
        return json_encode($rs);
    }

    /**
     * 获取直播间状态
     */
    public function getLiveStatus(){
        $m = new M();
        $rs = $m->getLiveStatus();
        return json_encode(WSTReturn('success',1,$rs));
    }

    /**
     * 获取直播间详情
     */
    public function getById(){
        $m = new M();
        $userId = model('app/users')->getUserId();
        $rs = $m->getById($userId);
        return json_encode(WSTReturn('success',1,$rs));
    }

    /*
     * 获取直播间商品
     */
    public function getLiveGoods(){
        $m = new M();
        $rs = $m->getLiveGoods();
        return json_encode(WSTReturn('success',1,$rs));
    }

    /*
     * 获取直播间回放视频
     */
    public function getRoomReplays(){
        $m = new M();
        $rs = $m->getRoomReplays();
        return json_encode(WSTReturn('success',1,$rs));
    }

    /**
     * 取消关注
     */
    public function favoritesCancel(){
        $userId = model('app/users')->getUserId();
        $rs = model('common/ShopMembers')->del($userId);
        return json_encode($rs);
    }

    /**
     * 增加关注
     */
    public function favoritesAdd(){
        $userId = model('app/users')->getUserId();
        $rs = model('common/ShopMembers')->add($userId);
        return json_encode($rs);
    }

    /*
     * 点赞
     */
    public function giveLike(){
        $m = new M();
        $rs = $m->giveLike();
        return json($rs);
    }
}
