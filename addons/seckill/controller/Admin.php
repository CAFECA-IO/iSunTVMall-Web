<?php
namespace addons\seckill\controller;

use think\addons\Controller;
use addons\seckill\model\Seckills as M;
use addons\seckill\model\SeckillGoods as GM;
use addons\seckill\model\SeckillTimeIntervals as TM;
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
 * 秒杀管理后台控制器
 */
class Admin extends Controller{
	
	/**
     * 查看秒杀活动列表
     */
    public function seckillPage(){
        $this->checkAdminAuth();
        $this->assign("areaList",model('common/areas')->listQuery(0));
        $this->assign("p",(int)input("p"));
        return $this->fetch("/admin/list");
    }

    /**
     * 查询秒杀已审核活动列表
     */
    public function pageQuery(){
        $this->checkAdminAuth();
        $m = new M();
        return WSTGrid($m->pageQueryByAdmin(1));
    }
    /**
     * 查询秒杀待审核活动列表
     */
    public function pageAuditQuery(){
        $this->checkAdminAuth();
        $m = new M();
        return WSTGrid($m->pageQueryByAdmin(0));
    }

    /**
     * 查询秒杀时段列表
     */
    public function timesPageQuery(){
        $this->checkAdminAuth();
        $m = new TM();
        return WSTGrid($m->queryPage());
    }
    /**
     * 查询秒杀时段
     */
    public function getTimes(){
        $this->checkAdminAuth();
        $m = new TM();
        return $m->getById();
    }

    /**
     * 查询秒杀时段
     */
    public function addTimes(){
        $this->checkAdminAuth();
        $m = new TM();
        return $m->add();
    }

    /**
     * 查询秒杀时段
     */
    public function editTimes(){
        $this->checkAdminAuth();
        $m = new TM();
        return $m->edit();
    }

    public function timesDel(){
        $this->checkAdminAuth();
        $m = new TM();
        return $m->del();
    }

    /**
    * 秒杀活动审核不通过
    */
    public function allow(){
        $this->checkAdminAuth();
        $m = new M();
        return $m->allow();
    }
    /**
     * 秒杀活动审核不通过
     */
    public function illegal(){
        $this->checkAdminAuth();
        $m = new M();
        return $m->illegal();
    }

    /**
     * 删除秒杀活动
     */
    public function del(){
        $this->checkAdminAuth();
        $m = new M();
        return $m->delByAdmin();
    }

    public function seckillGoods(){
        $m = new TM();
        $seckillId = (int)input("seckillId");
        $times = $m->queryList();
        $this->assign("times",$times);
        $this->assign("seckillId",$seckillId);
        return $this->fetch("/admin/seckill_goods");
    }

    /**
     * 加载秒杀商品
     */
    public function queryGoodsByPage(){
        $m = new GM();
        return WSTGrid($m->queryGoodsByPage());
    }

    /**
     * 秒杀活动审核不通过
     */
    public function delGoods(){
        $this->checkAdminAuth();
        $m = new GM();
        return $m->delGoods();
    }

    /**
     * 设置上下架
     */
    public function toggleSet(){
        $m = new M();
        return $m->toggleSetByAdmin();
    }
}