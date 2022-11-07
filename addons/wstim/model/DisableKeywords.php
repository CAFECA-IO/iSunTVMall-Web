<?php 
namespace addons\wstim\model;
use think\addons\BaseModel as Base;
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
 * 禁用关键字设置
 */
class DisableKeywords extends Base{
	/**
	* 禁用关键字设置
	*/
	public function commit(){
        Db::name('disable_keywords')->delete(true);
        $data = ['keywords'=>input('keywords'), 'createTime'=>date('Y-m-d H:i:s')];
        $rs = $this->save($data);
        if($rs!==false)return WSTReturn(lang('wstim_op_ok'),1);
		return WSTReturn(lang('wstim_op_err'));
    }
    /**
     * 获取店铺设置的禁用关键字
     */
    public function getKeywords(){
        return $this->value('keywords');
    }
}