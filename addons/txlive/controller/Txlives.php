<?php
namespace addons\txlive\controller;
use think\addons\Controller;
use addons\txlive\model\TxLives as M;
use wstmart\common\model\ShopMembers;
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
 * 腾讯云直播控制器
 */
class Txlives extends Controller
{
    /***********************************小程序端**********************************/
    /*
     * 获取直播间列表数据
     */
    public function wePageQuery(){
        $m = new M();
        $rs = $m->roomPageQuery();
        return jsonReturn('success',1,$rs);
    }

    /**
     * 获取直播间状态
     */
    public function weGetLiveStatus(){
        $m = new M();
        $rs = $m->getLiveStatus();
        return jsonReturn('success',1,$rs);
    }

    /**
     * 获取直播间详情
     */
    public function weGetById(){
        $m = new M();
        $userId = model('weapp/index')->getUserId();
        $rs = $m->getById($userId);
        return jsonReturn('success',1,$rs);
    }

    /*
     * 获取直播间商品
     */
    public function weGetLiveGoods(){
        $m = new M();
        $rs = $m->getLiveGoods();
        return jsonReturn('success',1,$rs);
    }

    /*
     * 获取直播间回放视频
     */
    public function weRoomReplays(){
        $m = new M();
        $rs = $m->getRoomReplays();
        return jsonReturn('success',1,$rs);
    }

    /**
     * 取消关注
     */
    public function weFavoritesCancel(){
        $userId = model('weapp/index')->getUserId();
        $m = new ShopMembers();
        $rs = $m->del($userId);
        return json_encode($rs);
    }
    /**
     * 增加关注
     */
    public function weFavoritesAdd(){
        $userId = model('weapp/index')->getUserId();
        $m = new ShopMembers();
        $rs = $m->add($userId);
        return json_encode($rs);
    }

    /*
     * 判断是否已关注
     */
    public function weCheckFavorite(){
        $userId = model('weapp/index')->getUserId();
        $shopId = (int)input('shopId');
        $m = new ShopMembers();
        $rs['isFollow'] = $m->checkFavorite($shopId,$userId);
        return jsonReturn('success',1,$rs);
    }

    /*
     * 点赞
     */
    public function weGiveLike(){
        $m = new M();
        $rs = $m->giveLike();
        return jsonReturn('success',1,$rs);
    }

    /***********************************手机端**********************************/
    /*
     * 跳去直播间列表页
     */
    public function molists(){
        $this->assign("keyword", input('keyword'));
        return $this->fetch("/mobile/index/list");
    }

    /*
     * 获取直播间列表数据
     */
    public function moPageQuery(){
        $m = new M();
        $rs = $m->roomPageQuery();
        return $rs;
    }

    /**
     * 直播间详情
     */
    public function modetail(){
        $m = new M();
        $rs = $m->getById();
        $rs['coverImg'] = WSTConf('CONF.resourcePath').'/'.$rs['coverImg'];
        $this->assign('room',$rs);
        return $this->fetch("/mobile/index/detail");
    }

    /**
     * 取消关注
     */
    public function moFavoritesCancel(){
        $m = new ShopMembers();
        $rs = $m->del();
        return $rs;
    }
    /**
     * 增加关注
     */
    public function moFavoritesAdd(){
        $m = new ShopMembers();
        $rs = $m->add();
        return $rs;
    }

    /*
     * 判断是否已关注
     */
    public function moCheckFavorite(){
        $shopId = (int)input('shopId');
        $m = new ShopMembers();
        $rs['isFollow'] = $m->checkFavorite($shopId);
        return WSTReturn('success',1,$rs);
    }

    /*
     * 获取直播间商品
     */
    public function moGetLiveGoods(){
        $m = new M();
        $rs = $m->getLiveGoods();
        return WSTReturn('success',1,$rs);
    }
}
