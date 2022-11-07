<?php
namespace wstmart\common\model;
use wstmart\common\validate\ShopCats as Validate;
use think\Db;
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
 * 商家商品分类
 */
class ShopCats extends Base{
    protected $pk = 'catId';
    /**
     * 分页
     */
    public function pageQuery($sId=0){
        $shopId = ($sId>0)?$sId:(int)session('WST_USER.shopId');
        $parentId = input('catId/d',0);
        return $this->alias('a')
            ->join('__SHOP_CATS_LANGS__ scl','scl.catId=a.catId and scl.langId='.WSTCurrLang(),'inner')
            ->where([['dataFlag','=',1],['a.parentId','=',$parentId],['a.shopId','=',$shopId]])->field('a.*,scl.catName')->order('a.catId desc')->paginate(1000)->toArray();
    }

    /**
     * 新增
     */
    public function add($sId=0){
        $shopId = ($sId>0)?$sId:(int)session('WST_USER.shopId');
        $createTime = date("Y-m-d H:i:s");
        $data = input('post.');
        $data['createTime'] = $createTime;
        $data['shopId'] = $shopId;
        $validate = new validate();
        if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
        Db::startTrans();
        try{
            $result = $this->allowField(true)->save($data);
            if(false !== $result){
                $catId = $this->catId;
                $datas = [];
                $langParams = input('post.langParams');
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['catId'] = $catId;
                    $data['langId'] = $v['id'];
                    $data['catName'] = $langParams[$v['id']]['catName'];
                    $datas[] = $data;
                }
                Db::name('shop_cats_langs')->insertAll($datas);
                Db::commit();
                return WSTReturn(lang('op_ok'), 1);
            }else{
                return WSTReturn($this->getError(),-1);
            }
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('op_err'),-1);
    }

    /**
     * 编辑
     */
    public function edit(){
        $data = input('post.');
        $catId = input('post.id/d');
        $validate = new validate();
        if(!$validate->scene('edit')->check($data))return WSTReturn($validate->getError());
        $result = $this->allowField(['isShow','catSort'])->save($data,['catId'=>$catId]);
        $ids = array();
        $ids = $this->getChild($catId);
        $this->where("catId in(".implode(',',$ids).")")->update(['isShow' => input('post.')['isShow']]);
        if(false !== $result){
            Db::name('shop_cats_langs')->where(['catId'=>$catId])->delete();
            $datas = [];
            $langParams = input('post.langParams');
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['catId'] = $catId;
                $data['langId'] = $v['id'];
                $data['catName'] = $langParams[$v['id']]['catName'];
                $datas[] = $data;
            }
            Db::name('shop_cats_langs')->insertAll($datas);
            WSTClearAllCache();
            return WSTReturn(lang('op_ok'), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
    }

    /**
     * 迭代获取下级
     * 获取一个分类下的所有子级分类id
     */
    public function getChild($pid=1){
        $data = $this->where("dataFlag=1")->select();
        //获取该分类id下的所有子级分类id
        $ids = $this->_getChild($data, $pid, true);//每次调用都清空一次数组
        //把自己也放进来
        array_unshift($ids, $pid);
        return $ids;
    }
    public function _getChild($data, $pid, $isClear=false){
        static $ids = array();
        if($isClear)//是否清空数组
            $ids = array();
        foreach($data as $k=>$v)
        {
            if($v['parentId']==$pid && $v['dataFlag']==1)
            {
                $ids[] = $v['catId'];//将找到的下级分类id放入静态数组
                //再找下当前id是否还存在下级id
                $this->_getChild($data, $v['catId']);
            }
        }
        return $ids;
    }

	 /**
	  * 修改排序号
	  */
	 public function editSort(){
	 	$id = input("post.id/d");
		$data = array();
		$data["catSort"] = input("post.catSort/d");
		$shopId = (int)session('WST_USER.shopId');
		$rs = $this->save($data,["catId"=>$id,"shopId"=>$shopId]);
		if(false !== $rs){
			return WSTReturn(lang('op_ok'),1);
		}
		return WSTReturn($this->getError());
	 }
	 /**
	  * 获取指定对象
	  */
     public function getById($id){
         $rs = $this->where(['dataFlag'=>1,'catId'=>$id])->find();
         $rs['langs'] = Db::name('shop_cats_langs')->where(['catId'=>$id])->column('*','langId');
         return $rs;
	 }

	/**
	* 获取列表
	*/
	public function listQuery($shopId,$parentId,$isShow=1){
	    $where = [];
	    if($isShow==1)$where[] = ['isShow','=',1];
		$rs = $this->alias('a')->join('__SHOP_CATS_LANGS__ scl','scl.catId=a.catId and langId='.WSTCurrLang())->where(['shopId'=>$shopId,'dataFlag'=>1,'parentId'=>$parentId])
                   ->where($where)
				   ->order('catSort asc')->select();
		return $rs;
	}

	 /**
	  * 删除
	  */
	 public function del($sId=0){
		$shopId = ($sId==0)?(int)session('WST_USER.shopId'):$sId;
	 	$id = input("post.id/d");
	 	if($id==0)return WSTReturn(lang('op_err'));
		//把相关的商品下架了
		$data = array();
		$data['isSale'] = 0;
		$gm = new \wstmart\common\model\Goods();
		$gm->save($data,['shopId'=>$shopId,"shopCatId1"=>$id]);
		$gm->save($data,['shopId'=>$shopId,"shopCatId2"=>$id]);
		//删除商品分类
		$data = array();
		$data["dataFlag"] = -1;
	 	$rs = $this->where("catId|parentId",$id)->where(["shopId"=>$shopId])->update($data);
	    if(false !== $rs){
			return WSTReturn(lang('op_ok'),1);
		}else{
			return WSTReturn($this->getError());
		}
	 }


	/**
	  * 获取店铺商品分类列表
	*/
    public function getShopCats($shopId = 0){
		$data = [];
		if(!$data){
			$data = $this->alias('a')->join('__SHOP_CATS_LANGS__ scl','scl.catId=a.catId and langId='.WSTCurrLang())->field("a.catId,a.parentId,a.shopId,scl.catName")->where(["shopId"=>$shopId,"parentId"=>0,"isShow"=>1 ,"dataFlag"=>1])->order("catSort asc")->select();
			if(count($data)>0){
				$ids = array();
				foreach ($data as $v){
					$ids[] = $v['catId'];
				}

				$crs = $this->alias('a')->join('__SHOP_CATS_LANGS__ scl','scl.catId=a.catId and langId='.WSTCurrLang())
                            ->field("a.catId,a.parentId,a.shopId,scl.catName")
							->where(["shopId"=>$shopId,"isShow"=>1 ,"dataFlag"=>1])
							->where([["parentId","in",implode(',',$ids)]])
							->order("catSort asc")->select();
				$ids = array();
			    foreach ($crs as $v){
			    	$ids[$v['parentId']][] = $v;
				}
				foreach ($data as $key =>$v){
					$data[$key]['children'] = '';
					if(isset($ids[$v['catId']])){
						$data[$key]['children'] = $ids[$v['catId']];
					}
				}
			}
	    }
		return $data;
	}

	/**
	 * 显示状态
	 */
	public function changeCatStatus(){
        $shopId = (int)session('WST_USER.shopId');
        $ids = array();
        $id = input('post.id/d');
        $ids = $this->getChild($id);
        $isShow = input('post.isShow/d')?1:0;
        $result = $this->where("catId in(".implode(',',$ids).")")->update(['isShow' => $isShow]);
        if(false !== $result){
            //如果是隐藏的话还要下架的商品
            if($isShow==0){
                $gm = new \wstmart\common\model\Goods();
                $data = array();
                $data["isSale"] = 0;
                $gm->save($data,[["shopId","=",$shopId],["shopCatId1|shopCatId2",'=',$id]]);
            }
            return WSTReturn(lang('op_ok'), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
	}
}
