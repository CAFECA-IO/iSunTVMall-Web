<?php
namespace wstmart\shopapp\controller;
use wstmart\shopapp\model\ShopCats as M;
class Shopcats extends Base{
	protected $beforeActionList = ['checkAuth'];
	static $model = null;
	static $shopId = null;
	public function __construct(){
		parent::__construct();
		$userId = model('users')->getUserId();
		self::$shopId = model('shopapp/shops')->getShopId($userId);
		self::$model = new M();
	}
	// 列表查询
	public function listQuery(){
		$rs = self::$model->listQuery(self::$shopId,(int)input('parentId'),0);
		return json_encode(WSTReturn('ok',1,$rs));
	}
	// 获取
	public function getById(){
		return json_encode(self::$model->getById((int)input('catId')));
	}
    // 新增
    public function add(){
        return json_encode(self::$model->add(self::$shopId));
    }
    // 编辑
    public function edit(){
        return json_encode(self::$model->edit());
    }
	// 删除
	public function del(){
		return json_encode(self::$model->del(self::$shopId));
	}
}
