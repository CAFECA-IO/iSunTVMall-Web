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
 * 秒杀商家控制器
 */
class Shop extends Controller{
	public function __construct(){
		parent::__construct();
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}
	/**
	 * 秒杀列表
	 */
	public function index(){
	    $this->assign("p",(int)input("p"));
    	return $this->fetch("/shop/list");
	}
	/**
	 * 加载秒杀活动
	 */
	public function seckillPageQuery(){
		$m = new M();
		$shopId = (int)session('WST_USER.shopId');
		return WSTGrid($m->pageQueryByShop($shopId));
	}
	/**
	 * 设置上下架
	 */
	public function toggleSet(){
		$m = new M();
		$shopId = (int)session('WST_USER.shopId');
		return $m->toggleSet($shopId);
	}

	/**
	 * 时段列表
	 */
	public function seckillTimes(){
		$m = new TM();
		$shopId = (int)session('WST_USER.shopId');
		//$times = $m->spQueryList($shopId);
		//$this->assign("times",$times);
		$this->assign("seckillId",(int)input("id"));
		return $this->fetch("/shop/seckill_times");
	}
	/**
	 * 加载秒杀活动
	 */
	public function seckillTimesPageQuery(){
		$m = new TM();
		$shopId = (int)session('WST_USER.shopId');
		return WSTGrid($m->spQueryList($shopId));
	}
	/**
	 * 商品列表
	 */
	public function setGoods(){
		$m = new TM();
		$shopId = (int)session('WST_USER.shopId');
		$timeInterval = $m->getById();
		$seckillId = (int)input("seckillId");
		$m = new M();
		$seckill = $m->getById($seckillId,$shopId);
		$secCanEdit = 0;
		if($seckill["seckillStatus"]<0){
			$secCanEdit = 1;
		}
		$this->assign("secCanEdit",$secCanEdit);
		$this->assign("seckillId",$seckillId);
		$this->assign("timeId",(int)input("timeId"));
		$this->assign("timeInterval",$timeInterval);
		return $this->fetch("/shop/set_goods");
	}

	/**
	 * 添加商品列表
	 */
	public function toSecGoods(){
		$shopId = (int)session('WST_USER.shopId');
		$seckillId = (int)input("seckillId");
		$m = new M();
		$seckill = $m->getById($seckillId,$shopId);
		$secCanEdit = 0;
		if($seckill["seckillStatus"]<0){
			$secCanEdit = 1;
		}
		$this->assign("secCanEdit",$secCanEdit);
		$this->assign("seckillId",$seckillId);
		$this->assign("timeId",(int)input("timeId"));
		return $this->fetch("/shop/goods_list");
	}
	/**
	 * 加载秒杀商品
	 */
	public function queryGoodsByPage(){
		$m = new GM();
		$shopId = (int)session('WST_USER.shopId');
		return WSTGrid($m->spQueryGoodsByPage($shopId));
	}

	/**
	 * 添加下架商品
	 */
	public function checkGoods(){
		$m = new GM();
		$shopId = (int)session('WST_USER.shopId');
		return $m->checkGoods($shopId);
	}

	/**
	 * 设置秒杀商品
	 */
	public function goodsSet(){
		$m = new GM();
		$shopId = (int)session('WST_USER.shopId');
		$isBatch = (int)input("isBatch");
		if($isBatch==1){
			return $m->goodsBatchSet($shopId);
		}else{
			return $m->goodsSet($shopId);
		}

	}
	/**
	 * 提交审核秒杀活动
	 */
	public function submitAudit(){
		$m = new M();
		$shopId = (int)session('WST_USER.shopId');
		return $m->submitAudit($shopId);
	}

	/**
	 * 删除商品
	 */
	public function delGoods(){
		$m = new GM();
		$shopId = (int)session('WST_USER.shopId');
		return $m->spDelGoods($shopId);
	}
	/**
	 * 搜索商品
	 */
	public function searchGoodsByPage(){
		$m = new GM();
		$shopId = (int)session('WST_USER.shopId');
		return $m->spSearchGoodsByPage($shopId);
	}

	/**
	 * 跳去编辑页面
	 */
	public function edit(){
		$id = (int)input('id');
		$shopId = (int)session('WST_USER.shopId');
		$object = [];
		$m = new M();
		if($id>0){
            $object = $m->getById($id,$shopId);
		}else{
			$object = $m->getEModel('seckills');
			$object['marketPrice'] = '';
			$object['goodsName'] = '请选择秒杀商品';
			$object['startDate'] = date('Y-m-d');
			$object['endDate'] = date('Y-m-d',strtotime("+7 day"));
            foreach (WSTSysLangs() as $key => $lv) {
                $object['langParams'][$lv['id']]['title'] = '';
                $object['langParams'][$lv['id']]['seckillDes'] = '';
            }
		}
		$this->assign("object",$object);
        $this->assign("p",(int)input("p"));
		return $this->fetch("/shop/edit");
	}

	/**
	 * 保存秒杀信息
	 */
	public function toEdit(){
		$shopId = (int)session('WST_USER.shopId');
		$id = (int)input('post.id');
		$m = new M();
		if($id==0){
            return $m->add($shopId);
		}else{
            return $m->edit($shopId);
		}
	}

	/**
	 * 删除秒杀
	 */
	public function del(){
		$m = new M();
		$shopId = (int)session('WST_USER.shopId');
		return $m->del($shopId);
	}

    /**
     * 查询订单列表
     */
    public function pageQueryByGoods(){
    	$m = new M();
		return $m->pageQueryByGoods();
    }
}
