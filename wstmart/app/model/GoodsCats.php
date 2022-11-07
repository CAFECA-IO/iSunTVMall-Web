<?php
namespace wstmart\app\model;
use Cache;
use think\Db;
use wstmart\common\model\GoodsCats as CGC;
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
 * 商品分类类
 */
class GoodsCats extends CGC{

	/**
	 * 获取列表
	 */
	public function listQuery($parentId,$isFloor = -1){
		$dbo = $this
            ->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
            ->field('gc.*,gcl.catName,gcl.subTitle,gcl.simpleName')
            ->where(['dataFlag'=>1,'isShow'=>1,'parentId'=>$parentId]);
		if($isFloor!=-1)$dbo->where('isFloor',$isFloor);
		$rs = $dbo->order('catSort asc')->select();
		return $rs;
	}

	/**
	 * 获取商品列表展示方式
	 * @param $catId 分类id
	 */
	public function getShowWay($catId){
		return (int)$this->where('catId',$catId)->value('showWay');
	}
	/**
	 * 列表
	 */
	public function getGoodsCats(){
		$list = WSTCache('WSTAPP_CACHE_GOODS_CAT_MOB');
		if(!$list){
			//查询一级分类
			$trs1s = $this
                ->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
                ->where(["dataFlag"=>1,"isShow"=>1,"parentId"=>0])->field('gc.catId,parentId,gcl.simpleName')->order('catSort asc')->select();

			$trs1 = array();
			$list = array();
			$rs2 = array();
			$maprs = array();
			$ids = array();
			$tModel = model('Tags');
			foreach ($trs1s as $key =>$v){
				$trs1[$key]['catId'] = $v['catId'];
				$trs1[$key]['catName'] = trim($v['simpleName']);
				// 获取该分类下广告
				$trs1[$key]['ads'] = $this->transitionImg($tModel->listAds('app-category-'.($key+1),4));
				$ids[] = $v['catId'];
			}
			//查询二级分类
			$trs2s = $this
                ->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
                ->where("dataFlag=1 and isShow=1 and parentId in(".implode(',',$ids).")")->field('gc.catId,parentId,gcl.catName')->order('catSort asc')->select();
			$trs2 = array();
			$ids = array();
			foreach ($trs2s as $key =>$v){
				$trs2[$key]['catId'] = $v['catId'];
				$trs2[$key]['parentId'] = $v['parentId'];
				$trs2[$key]['catName'] = $v['catName'];
			}
			foreach ($trs2 as $v2){
				$ids[] = $v2['catId'];
				$maprs[$v2['parentId']][] = $v2;
			}
			//查询三级分类
			$trs3s = $this
                ->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())
                ->where("dataFlag=1 and isShow=1 and parentId in(".implode(',',$ids).")")->field('gc.catId,parentId,gcl.catName,catImg')->order('catSort asc')->select();
			$trs3 = array();
			$ids = array();
			foreach ($trs3s as $key =>$v){
				$trs3[$key]['catId'] = $v['catId'];
				$trs3[$key]['parentId'] = $v['parentId'];
				$trs3[$key]['catName'] = $v['catName'];
				$trs3[$key]['catImg'] = strval($v['catImg']);
			}
			foreach ($trs3 as $v2){
				$maprs[$v2['parentId']][] = $v2;
			}
			//倒序建立樹形
			foreach ($trs2 as $v2){
				$v2['childList'] = [];
				if(isset($maprs[$v2['catId']]))$v2['childList'] = $maprs[$v2['catId']];
				$rs2[] = $v2;
			}
			foreach ($trs1 as $v2){
				foreach ($rs2 as $vv2){
					if($vv2['parentId']==$v2['catId']){
						$v2['childList'][] = $vv2;
					}
				}
				$list[] = $v2;
			}
			WSTCache('WSTAPP_CACHE_GOODS_CAT_MOB',$list);
		}
		return $list;
	}
	/**
    * 转换图片即删除无用字段
    */
    private function transitionImg($img){
        if(empty($img))return [];
        // 图片转换及删除无用字段
        $_img = [];
        foreach ($img as $k => $v) {
            $_img[$k]['adId'] = $v['adId'];
            $_img[$k]['adURL'] = $v['adURL'];
            $_img[$k]['adFile'] = WSTImg($v['adFile'],2);
        }
        return $_img;
    }
}
