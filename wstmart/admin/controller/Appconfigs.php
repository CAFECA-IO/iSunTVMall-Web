<?php
namespace wstmart\admin\controller;
use wstmart\admin\model\SysConfigs as M;
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
 * app配置控制器
 */
class Appconfigs extends Base{
    /**
     * 查看配置信息
     */
    public function index(){
    	$m = new M();
    	$object = $m->getSysConfigs();
    	$this->assign("object",$object);
    	return $this->fetch("edit");
    }
    /**
     * 保存
     */
    public function edit(){
        // 判断是否需要清理海报
        $m = new M();
    	$object = $m->getSysConfigs();
        $prev = $object['appShareOpenType'];
        $curr = (int)input('post.appShareOpenType');
        if($prev!=$curr){
            // 清理海报
            $this->clearPoster();
        }
    	$m = new M();
    	return $m->edit(44);
    }

    /**
	 * 清除海报图片
	 * @return [type] [description]
	 */
	private function clearPoster(){
		$dirpath = WSTRootPath()."/upload/shares/goods/";
        WSTDelDir($dirpath);
		return true;
	}
}




