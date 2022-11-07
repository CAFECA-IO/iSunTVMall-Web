<?php
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
 */
use think\Db;
// 自动回复
function ImAutoReply($content, $shopId){
	// 判断是否为json格式
	$data = json_decode($content, true);
	if ($data && (is_object($data)) || (is_array($data) && !empty($data))) {
	  // json格式消息
	  return false;
	}
	$keywords = Db::name('auto_replys')->where(['shopId'=>$shopId,'dataFlag'=>1])->column('keyword');
	if(empty($keywords))return false;
	$pattern = join('|',$keywords);
	preg_match_all("/$pattern/", $content, $matches);
	if(isset($matches[0]) && empty($matches[0]))return false;
	$matches = array_unique($matches[0]);
	$replys = Db::name('auto_replys')->whereIn('keyword',$matches)->where(['shopId'=>$shopId,'dataFlag'=>1])->column('replyContent');
	return $replys;
}
// 过滤禁用关键字
function filterContent($content){
	// 判断是否为json格式
	$data = json_decode($content, true);
	if ($data && (is_object($data)) || (is_array($data) && !empty($data))) {
	  // json格式消息
	  return $content;
	}
	// 纯文本消息
	$disKeywords = Db::name('disable_keywords')->value('keywords');
	if($disKeywords=='')return $content;
	// 执行过滤
	// 转为数组
	$disKeywords = explode(',',$disKeywords);
	// 过滤空值及空格
	$disKeywords = array_filter($disKeywords, function($item){
		$item = trim($item);
		return ($item!='' || strlen($item)>0);
	});
	// 设置分隔符
	$disKeywords = array_map(function($item){
		if($item!='')return "/$item/";
	},$disKeywords);
	return preg_replace($disKeywords,'*',$content);
}

// 图片域名
function resourceDomain(){
	if(!empty(WSTConf('WST_ADDONS.aliyunoss'))){
		return WSTConf('CONF.resourcePath').'/';
	}
	return url('/','','',true);
}

function WSTImUserPhoto($userPhoto){
	$isApp = (int)input('isApp');
	$isWeapp = (int)input('isWeapp');
	if($isApp==1 || $isWeapp==1){
		$resourcePath = resourceDomain();
		if(substr($userPhoto,0,4)!='http' && $userPhoto){
			$userPhoto  = $resourcePath.'/'.$userPhoto;
		}else if(!$userPhoto){
			$userPhoto  = $resourcePath.'/'.WSTConf('CONF.userLogo');
		}
		return $userPhoto;
	}
	return WSTUserPhoto($userPhoto);
}
function getShopInfo($userId){
	// 根据用户id查出serviceId,判断是否为客服
	$serviceId = Db::name('shop_users')->where(['userId'=>$userId,'dataFlag'=>1])->value('serviceId');
	if($serviceId=='')return;
	$isService = Db::name('shop_services')->where(['serviceId'=>$serviceId,'dataFlag'=>1])->find();
	if(!empty($isService)){
		return Db::name('shop_users')
			->alias('su')
			->join('__SHOPS__ s','su.shopId=s.shopId')
			->field('s.shopId,s.shopName,s.shopImg')
			->where(['su.userId'=>$userId])
			->find();
	}
}
// 获取socket服务器地址
function chatServer(){
	$filePath = WST_ADDON_PATH.'wstim/Apis.json';
	$rs = file_get_contents($filePath);
	$rs = json_decode($rs,true);
	$_rs = '';
	foreach ($rs as $k => $v) {
		if($k=='imServer'){
			$_rs = $v['link'];
			break;
		}
	}
	return $_rs;
}
/**
 * 生成数据返回值
 */
function WSTImReturn($msg,$status = -1,$data = []){
	$rs = WSTReturn($msg, $status, $data);
	$isApp = (int)input('isApp');
    $isWeapp = (int)input('isWeapp');
	if($isApp==1 || $isWeapp==1)$rs = json_encode($rs);
	return $rs;
}
// 根据tokenId获取userId
function getUserId(){
    $isApp = (int)input('isApp');
    $isWeapp = (int)input('isWeapp');
    if($isApp==1){
        return model('app/index')->getUserId();
    }
    if($isWeapp==1){
        return model('weapp/index')->getUserId();
    }
}
// 获取用户所属店铺id
function getShopId($userId){
	return (int)Db::name('shop_users')->where(['userId'=>$userId])->value('shopId');
}
// 最近联系排序函数（倒序）【chatsModel中调用】
function createTimeDescSort($a,$b){
	return strtotime($a['createTime'])<strtotime($b['createTime']);
}
// 最近联系排序函数（顺序）【chatsModel中调用】
function createTimeAscSort($a,$b){
	return strtotime($a['createTime'])>strtotime($b['createTime']);
}