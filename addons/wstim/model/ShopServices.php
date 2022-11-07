<?php
namespace addons\wstim\model;
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
 * 店铺客服业务处理
 */
use think\Model;
use think\Db;
use wstmart\admin\validate\Users;
class ShopServices extends Model {
	/**
    * 设置为客服
    */
    public function setService(){
    	$shopId = (int)session('WST_USER.shopId');
        $userId = (int)input('id');
        $isSet = (int)input('isSet');
        // 检测用户id是否属于该店铺的职员
        $flag = Db::name('shop_users')->where(['shopId'=>$shopId, 'userId'=>$userId, 'dataFlag'=>1])->find();
        if(!$flag){
        	return WSTReturn(lang('wstim_err2'));
		}

		// 根据用户id获取serviceId
		$serviceId = $flag['serviceId'];
		if($flag['serviceId']==''){
			$serviceId = $userId;
			// 记录该职员serviceId
			Db::name('shop_users')->where(['dataFlag'=>1,'userId'=>$userId])->update(['serviceId'=>$serviceId]);
		}
		
        if(!$isSet){
        	$rs = $this->where(['shopId'=>$shopId,'serviceId'=>$serviceId])->setField('dataFlag',-1);
        }else{
        	// 防止出现多个dialogId
        	$has = $this->where(['shopId'=>$shopId,'serviceId'=>$serviceId])->find();
        	if($has){
        		$rs = $this->where(['shopId'=>$shopId,'serviceId'=>$serviceId])->setField('dataFlag',1);
        	}else{
	        	$data = array();
		        $data['serviceId'] = $serviceId;
		        $data['createTime'] = date('Y-m-d H:i:s');
		        $data['shopId'] = $shopId;
		        $rs = $this->allowField(true)->save($data);
        	}
        }
        $_session = session('WST_USER');
		if($_session['userId']==$userId){// 店家设置自己的客服身份时
			$_session['isService'] = !!$isSet;
			$_session['serviceId'] = !!$isSet?$serviceId:'';
			session('WST_USER',$_session);
        }
        if($rs!==false)return WSTReturn(lang('op_ok'),1);
        return WSTReturn(lang('op_err'),1);
    }
	public function pageQuery(){
		$shopId = (int)session('WST_USER.shopId');
		$where = ["s.shopId"=>$shopId,"s.dataFlag"=>1];
		$page = Db::name('shop_users')
				->alias('s')
	    		->join("__SHOP_ROLES__ r","s.roleId=r.id","LEFT")
	    		->join("__USERS__ u", "u.userId=s.userId and u.dataFlag=1")
	    		->field('s.serviceId,s.userId,s.id,s.shopId,s.roleId,u.userName,u.loginName,u.createTime,u.userStatus,r.roleName,u.userPhone,u.userEmail')
				->where($where)
				->paginate(input('limit/d'))->toArray();
		// 店铺客服id
		$serviceIds = $this->where(['shopId'=>$shopId,'dataFlag'=>1])->column('serviceId');
		foreach ($page['data'] as $k => $v) {
			if(in_array($v['serviceId'], $serviceIds)){
				$page['data'][$k]['isService'] = 1;
			}else{
				$page['data'][$k]['isService'] = 0;
			}
		}
		return $page;
    }
}