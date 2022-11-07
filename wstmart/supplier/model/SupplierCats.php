<?php
namespace wstmart\supplier\model;
use wstmart\supplier\validate\SupplierCats as Validate;
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
 * 供货商商品分类
 */
class SupplierCats extends Base{
    protected $pk = 'catId';
    /**
     * 分页
     */
    public function pageQuery(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $parentId = input('catId/d',0);
        return $this->alias('a')
            ->join('__SUPPLIER_CATS_LANGS__ scl','scl.catId=a.catId and scl.langId='.WSTCurrLang(),'inner')
            ->where([['dataFlag','=',1],['a.parentId','=',$parentId],['a.supplierId','=',$supplierId]])->field('a.*,scl.catName')->order('a.catId desc')->paginate(1000)->toArray();
    }

    /**
     * 新增
     */
    public function add(){
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $createTime = date("Y-m-d H:i:s");
        $data = input('post.');
        $data['createTime'] = $createTime;
        $data['supplierId'] = $supplierId;
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
                Db::name('supplier_cats_langs')->insertAll($datas);
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
            Db::name('supplier_cats_langs')->where(['catId'=>$catId])->delete();
            $datas = [];
            $langParams = input('post.langParams');
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['catId'] = $catId;
                $data['langId'] = $v['id'];
                $data['catName'] = $langParams[$v['id']]['catName'];
                $datas[] = $data;
            }
            Db::name('supplier_cats_langs')->insertAll($datas);
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
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		$rs = $this->save($data,["catId"=>$id,"supplierId"=>$supplierId]);
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
         $rs['langs'] = Db::name('supplier_cats_langs')->where(['catId'=>$id])->column('*','langId');
         return $rs;
	 }


	/**
	* 获取列表
	*/
	public function listQuery($supplierId,$parentId){
		$rs = $this->alias('a')
            ->join('__SUPPLIER_CATS_LANGS__ scl','scl.catId=a.catId and scl.langId='.WSTCurrLang(),'inner')
            ->where(['a.supplierId'=>$supplierId,'a.dataFlag'=>1,'a.isShow'=>1,'a.parentId'=>$parentId])
			->order('a.catSort asc')->select();
		return $rs;
	}

	 /**
	  * 删除
	  */
	 public function del(){
	 	$id = input("post.id/d");
	 	if($id==0)return WSTReturn(lang('op_err'),-1);;
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
		//把相关的商品下架了
		$data = array();
		$data['isSale'] = 0;
		$gm = model("SupplierGoods");
		$gm->save($data,['supplierId'=>$supplierId,"supplierCatId1"=>$id]);
		$gm->save($data,['supplierId'=>$supplierId,"supplierCatId2"=>$id]);
		//删除商品分类
		$data = array();
		$data["dataFlag"] = -1;
	 	$rs = $this->where("catId|parentId",$id)->where(["supplierId"=>$supplierId])->update($data);
	    if(false !== $rs){
			return WSTReturn(lang('op_ok'),1);
		}else{
			return WSTReturn($this->getError());
		}
	 }

	/**
	 * 显示状态
	 */
	public function changeCatStatus(){
		$supplierId = (int)session('WST_SUPPLIER.supplierId');
        $ids = array();
        $id = input('post.id/d');
        $ids = $this->getChild($id);
        $isShow = input('post.isShow/d')?1:0;
        $result = $this->where("catId in(".implode(',',$ids).")")->update(['isShow' => $isShow]);
        if(false !== $result){
            //如果是隐藏的话还要下架的商品
            if($isShow==0){
                $gm = model("SupplierGoods");
                $data = array();
                $data["isSale"] = 0;
                $gm->save($data,[["supplierId","=",$supplierId],["supplierCatId1|supplierCatId2",'=',$id]]);
            }
            return WSTReturn(lang('op_ok'), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
	}

}
