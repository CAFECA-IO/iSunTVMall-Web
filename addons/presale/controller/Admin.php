<?php
namespace addons\presale\controller;

use think\addons\Controller;
use addons\presale\model\Presales as M;

class Admin extends Controller{
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 预售列表
	 */
	public function index(){
		$this->checkAdminPrivileges();
		$this->assign("areaList",model('common/areas')->listQuery(0));
	    $this->assign("p",(int)input("p"));
    	return $this->fetch("/admin/list");
	}
	/**
	 * 加载预售数据
	 */
	public function pageQuery(){
		$this->checkAdminPrivileges();
		$m = new M();
		return WSTGrid($m->pageQueryByAdmin(1));
	}
	/**
	 * 加载待审核预售数据
	 */
	public function pageAuditQuery(){
		$this->checkAdminPrivileges();
		$m = new M();
		return WSTGrid($m->pageQueryByAdmin(0));
	}

	/**
    * 设置违规商品
    */
    public function illegal(){
        $this->checkAdminPrivileges();
        $m = new M();
        return $m->illegal();
    }
    /**
     * 通过商品审核
     */
    public function allow(){
        $this->checkAdminPrivileges();
        $m = new M();
        return $m->allow();
    }

    /**
     * 删除
     */
    public function del(){
        $this->checkAdminPrivileges();
        $m = new M();
        return $m->delByAdmin();
    }
}
