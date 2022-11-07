<?php
namespace wstmart\admin\controller;
use wstmart\admin\model\SupplierGoods as M;
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
 * 商品控制器
 */
class Suppliergoods extends Base{
   /**
	* 查看上架商品列表
	*/
	public function index(){
	    $this->assign("p",(int)input("p"));
    	$this->assign("areaList",model('areas')->listQuery(0));
		return $this->fetch('list_sale');
	}
   /**
    * 批量删除商品
    */
    public function batchDel(){
        $m = new M();
        return $m->batchDel();
    }

    /**
    * 设置违规商品
    */
    public function illegal(){
        $m = new M();
        return $m->illegal();
    }
    /**
    * 批量设置违规商品
    */
    public function batchIllegal(){
        $m = new M();
        return $m->batchIllegal();
    }
    /**
     * 通过商品审核
     */
    public function allow(){
        $m = new M();
        return $m->allow();
    } 
    /**
     * 批量通过商品审核
     */
    public function batchAllow(){
        $m = new M();
        return $m->batchAllow();
    }
	/**
	 * 获取上架商品列表
	 */
	public function saleByPage(){
		$m = new M();
		$rs = $m->saleByPage();
		$rs['status'] = 1;
		return WSTGrid($rs);
	}
	
    /**
	 * 审核中的商品
	 */
    public function auditIndex(){
        $this->assign("p",(int)input("p"));
    	$this->assign("areaList",model('areas')->listQuery(0));
		return $this->fetch('list_audit');
	}
	/**
	 * 获取审核中的商品
	 */
    public function auditByPage(){
		$m = new M();
		$rs = $m->auditByPage();
		$rs['status'] = 1;
		return WSTGrid($rs);
	}
   /**
	 * 审核中的商品
	 */
    public function illegalIndex(){
        $this->assign("p",(int)input("p"));
    	$this->assign("areaList",model('areas')->listQuery(0));
		return $this->fetch('list_illegal');
	}
    /**
	 * 获取违规商品列表
	 */
	public function illegalByPage(){
		$m = new M();
		$rs = $m->illegalByPage();
		$rs['status'] = 1;
		return WSTGrid($rs);
	}
    
    /**
     * 删除商品
     */
    public function del(){
    	$m = new M();
    	return $m->del();
    }

    /**
     * 查看商品详情
     */
    public function detail(){
        $m = model("shop/SupplierGoods");
        $goods = $m->getBySale(input('goodsId/d',0));
        if(!empty($goods)){
            // 商品详情延迟加载
            $rule = '/<img src="\/(upload.*?)"/';
            preg_match_all($rule, $goods['goodsDesc'], $images);
            foreach($images[0] as $k=>$v){
                $goods['goodsDesc'] = str_replace($v, "<img class='goodsImg' data-original=\"".str_replace('/index.php','',request()->root())."/".WSTImg($images[1][$k],3)."\"", $goods['goodsDesc']);
            }
            $this->assign('goods',$goods);
            $this->assign('supplier',$goods['supplier']);
            return $this->fetch('suppliergoods/goods_detail');
        }else{
            return $this->fetch("suppliergoods/error_lost");
        }
    }
}
