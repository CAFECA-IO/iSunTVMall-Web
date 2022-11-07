<?php
namespace addons\wstim\model;
use think\addons\BaseModel as Base;
use think\Db;
/**
 * 自动回复业务处理
 */
class AutoReplys extends Base{
	protected $pk = 'id';
	
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		WSTUnset($data,"id");
		if(!isset($data['keyword']) || $data['keyword']==''){
			return WSTReturn('请输入触发关键字');
		}
		if(strlen($data['keyword'])>50){
			return WSTReturn('关键字不能大于50个字符');
		}
		if(!isset($data['replyContent']) || $data['replyContent']==''){
			return WSTReturn('请输入回复内容');
		}
		$data['createTime'] = date('Y-m-d H:i:s');
		$data['shopId'] = (int)session('WST_USER.shopId');
		Db::startTrans();
		try{
			$result = $this->allowField(true)->save($data);
			if(false !==$result){
				$id = $this->id;
		        if(false !== $result){
		        	Db::commit();
		        	return WSTReturn("新增成功", 1);
		        }
			}
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn('新增失败',-1);	
	}

    /**
	 * 编辑
	 */
	public function edit(){
		$data = input('post.');
		if(!isset($data['keyword']) || $data['keyword']==''){
			return WSTReturn('请输入触发关键字');
		}
		if(strlen($data['keyword'])>50){
			return WSTReturn('关键字不能大于50个字符');
		}
		if(!isset($data['replyContent']) || $data['replyContent']==''){
			return WSTReturn('请输入回复内容');
		}
		Db::startTrans();
		try{
			$shopId = (int)session('WST_USER.shopId');
		    $result = $this->allowField(true)->save($data,['id'=>(int)$data['id'],'shopId'=>$shopId]);
	        if(false !== $result){
	        	Db::commit();
	        	return WSTReturn("编辑成功", 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn('编辑失败',-1);  
	}
	    
    /**
     * 获取记录
     */
	public function getById($id){
		$where = ['shopId'=>(int)session('WST_USER.shopId'),'id'=>$id,'dataFlag'=>1];
		return $this->where($where)->find();
	}
	
	/**
	 * 删除
	 */
    public function del(){
		$id = (int)input('post.id');
		$where = ['shopId'=>(int)session('WST_USER.shopId'),'id'=>$id];
	    Db::startTrans();
		try{
		    $result = $this->where($where)->update(['dataFlag'=>-1]);	
	        if(false !== $result){
	        	Db::commit();
	        	return WSTReturn(lang('wstim_op_ok'), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('wstim_op_err'),-1); 
	}
	/**
	 * 分页列表
	 */
	public function pageQuery(){
	    $where = ['shopId'=>(int)session('WST_USER.shopId'),'dataFlag'=>1];
		return $this->where($where)->where($where)->order("id desc")->paginate(input("limit/d"));
	}
	
	
}
