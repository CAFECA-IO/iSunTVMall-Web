<?php
namespace wstmart\common\model;
use think\Db;
use wstmart\common\model\Shops;
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
 * 收藏类
 */
class Favorites extends Base{
	protected $pk = 'favoriteId';
	/**
	 * 关注的商品列表
	 */
	public function listGoodsQuery($userId = 0){
		$pagesize = input("param.pagesize/d");
		$userId = ($userId>0)?$userId:(int)session('WST_USER.userId');
		$page = Db::name("favorites")->alias('f')
    	->join('__GOODS__ g','g.goodsId = f.goodsId','left')
        ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
    	->join('__SHOPS__ s','s.shopId = g.shopId','left')
    	->field('f.favoriteId,f.goodsId,gl.goodsName,g.goodsImg,g.shopPrice,g.marketPrice,g.saleNum,g.appraiseNum,s.shopId,s.shopName')
    	->where(['f.userId'=> $userId])
    	->order('f.favoriteId desc')
    	->paginate($pagesize)->toArray();
		foreach ($page['data'] as $key =>$v){
			//认证
			$shop = new Shops();
			$accreds = $shop->shopAccreds($v["shopId"]);
			$page['data'][$key]['accreds'] = $accreds;
		}
		return $page;
	}
	/**
	 * 取消关注
	 */
	public function del($userId = 0){
		$id = input("param.id");
		$userId = ($userId>0)?$userId:(int)session('WST_USER.userId');
		$ids = explode(',',$id);
		if(empty($ids))return WSTReturn(lang('cancel_fail'), -1);
		Db::startTrans();
		try{
			$rs = $this->where([['favoriteId','in',$ids],['userId','=',$userId]])->select();
            foreach ($rs as $key => $v) {
            	$collectNum = $this->where('goodsId',$v['goodsId'])->count();
            	$collectNum = $collectNum-1;
            	Db::name('goods')->where('goodsId',$v['goodsId'])->update(['collectNum'=>$collectNum]);
            }
            $rs = $this->where([['favoriteId','in',$ids],['userId','=',$userId]])->delete();
			if(false !== $rs){
				Db::commit();
				return WSTReturn(lang('cancel_success'), 1);
			}else{
				return WSTReturn($this->getError(),-1);
			}
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('cancel_fail'),-1);
        }
	}


	/**
	 * 新增关注
	 */
	public function add($userId = 0){
	    $id = input("param.id/d");
		$userId = ($userId>0)?$userId:(int)session('WST_USER.userId');
		if($userId==0)return WSTReturn(lang('no_login'),-2);
		//判断记录是否存在
		$isFind = false;
		$c = Db::name('goods')->where(['goodsStatus'=>1,'dataFlag'=>1,'goodsId'=>$id])->count();
		$isFind = ($c>0);
		if(!$isFind)return WSTReturn(lang('follow_fail_invalid_obj'), -1);
		$data = [];
		$data['userId'] = $userId;
		$data['goodsId'] = $id;
		//判断是否已关注
		$rc = $this->where($data)->count();
		if($rc>0)return WSTReturn(lang('follow_success'), 1);
		$data['createTime'] = date('Y-m-d H:i:s');
		$rs = $this->save($data);
		if(false !== $rs){
			Db::name('goods')->where('goodsId',$id)->setInc('collectNum',1);
			return WSTReturn(lang('follow_success'), 1,['fId'=>$this->favoriteId]);
		}else{
			return WSTReturn($this->getError(),-1);
		}
	}
	/**
	 * 判断是否已关注
	 */
	public function checkFavorite($id,$uId=0){
		$userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
		$rs = $this->where(['userId'=>$userId,'goodsId'=>$id])->find();
		return empty($rs)?0:$rs['favoriteId'];
	}
}
