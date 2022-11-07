<?php
namespace wstmart\common\model;
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
 * 银行业务处理
 */
use \think\Db;
class Banks extends Base{
	protected $pk = 'bankId';
	/**
	 * 列表
	 */
	public function listQuery(){
		$data = WSTCache('WST_BANKS');
		if(!$data){
			$data = $this->where(['dataFlag'=>1,'isShow'=>1])->field('bankId,bankImg')->select()->toArray();
			//加载多语言
			$ids = [];
			foreach ($data as $key => $v) {
				$ids[] = $v['bankId'];
			}
			if(count($ids)>0){
				$brs = Db::name('banks_langs')->where([['bankId','in',$ids]])->select();
				$brsmp = [];
				foreach ($brs as $key => $v) {
					$brsmp[$v['bankId']][$v['langId']] = $v;
				}
				foreach ($data as $key => $v) {
					$data[$key]['langParams'] = $brsmp[$v['bankId']];
				}
			}
			WSTCache('WST_BANKS',$data,31536000);
		}
		return WSTMergeLang($data);
	}
}
