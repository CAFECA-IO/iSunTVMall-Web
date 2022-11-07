<?php
namespace addons\aliyunoss\model;
use think\addons\BaseModel as Base;
use think\Env;
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
 * 阿里云-对象存储OSS
 */
class Aliyunoss extends Base{
	public function getConfig(){
		$codes = explode(',','ossService,ossAccessKey,ossAccessSecret,ossBucket,ossBucketDomain');
        $rs = Db::name('sys_configs')->field('fieldCode,fieldValue')->where([['fieldCode','in',$codes]])->select();
	    $data = [];
	    foreach ($rs as $key => $v) {
	    	$data[$v['fieldCode']] = $v['fieldValue'];
	    }
	    return $data;
	}
	/**
	 * 安装插件
	 */
	public function install(){
		Db::startTrans();
		try{
			$hooks = ['adminDocumentSysConfig','afterUploadPic','delPic'];
			$this->bindHoods("Aliyunoss", $hooks);
			Db::name('sys_configs')->where([['fieldCode','=','ossService']])->update(['fieldValue'=>'Aliyunoss']);
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}
    /**
     * 卸载插件
     */
	public function uninstall(){
		Db::startTrans();
		try{
			$hooks = ['adminDocumentSysConfig','afterUploadPic','delPic'];
			$this->unbindHoods("Aliyunoss", $hooks);
			$codes = explode(',','ossService,ossAccessKey,ossAccessSecret,ossBucket,ossBucketDomain');
		    foreach ($codes as $key => $v) {
		    	Db::name('sys_configs')->where([['fieldCode','=',$v]])->update(['fieldValue'=>'']);
		    }
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}
	/**
	 * 启用/停用插件
	 * @param  [type] $isEnable 是否启用
	 */
	public function enableConfig($isEnable){
		Db::startTrans();
		$ossService = $isEnable?"Aliyunoss":'';
		try{
			Db::name('sys_configs')->where([['fieldCode','=','ossService']])->update(['fieldValue'=>$ossService]);
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}
	/**
	 * 获取阿里云对象
	 */
	public function getOSSClient(){
		require WST_ADDON_PATH.'aliyunoss/sdk/autoload.php';
		return new \OSS\OssClient(WSTConf('CONF.ossAccessKey'),WSTConf('CONF.ossAccessSecret'),WSTConf('CONF.ossBucketDomain'));
	}
    /**
     * 上传文件到oss服务器
     */
	public function upload($params){
		$data = $params['data'];
		$isDelete = isset($params['isLocation'])?false:true;
		$isVideo = isset($params['isVideo'])?true:false;
		try{
			$path = str_replace('\\','/',$data['savePath']);
		    $oss = $this->getOSSClient();
		    //上传及删除图片
			    if(!$isVideo){
			    //大图
			    if(file_exists(WSTRootPath()."/".$path.$data['name'])){
			        $oss->uploadFile(WSTConf('CONF.ossBucket'), $path.$data['name'], $data['savePath'].$data['name']);
			        if($isDelete)@unlink($data['savePath'].$data['name']);
			    }
			    //缩略图
			    if(isset($data['thumb']) && file_exists(WSTRootPath()."/".$path.$data['thumb'])){
			        $oss->uploadFile(WSTConf('CONF.ossBucket'), $path.$data['thumb'], $data['savePath'].$data['thumb']);
			        if($isDelete)@unlink($data['savePath'].$data['thumb']);
			    }
			    $m = WSTConf('CONF.wstMobileImgSuffix');
			    //手机端大图
	            $img =  str_replace('.',$m.'.',$data['name']);
			    if(file_exists(WSTRootPath()."/".$data['savePath'].$img)){
			        $oss->uploadFile(WSTConf('CONF.ossBucket'), $path.$img, $data['savePath'].$img);
			        if($isDelete)@unlink($data['savePath'].$img);
			    }
			    //手机端缩略图
			    $img =  str_replace('.',$m.'_thumb.',$data['name']);
			    if(file_exists(WSTRootPath()."/".$data['savePath'].$img)){
			        $oss->uploadFile(WSTConf('CONF.ossBucket'), $path.$img, $data['savePath'].$img);
			        if($isDelete)@unlink($data['savePath'].$img);
			    }
			}else{
				if(file_exists(WSTRootPath()."/".$path.$data['name'])){
			        $oss->uploadFile(WSTConf('CONF.ossBucket'), $path.$data['name'], WSTRootPath()."/".$data['savePath'].$data['name']);
			        unset($oss);
			        if($isDelete)@unlink(WSTRootPath()."/".$data['savePath'].$data['name']);
			    }
			}
		    return $params;
		} catch(OssException $e) {
		    die($e->getMessage());
		}
	}

	/**
	 * 删除文件
	 */
	public function del($params){
        $images = $params['images'];
        $img = [];
        foreach ($images as $key => $value) {
        	if(WSTCheckResourceFile($value))$img[] = $value;
        }
        if(count($img)<=0)return true;
        try{
	        $oss = $this->getOSSClient();
	        if(count($img)>1){
	        	$oss->deleteObjects(WSTConf('CONF.ossBucket'),$img);
	        }else{
	            $oss->deleteObject(WSTConf('CONF.ossBucket'),$img[0]);
	        }
	    } catch(OssException $e) {
		    die($e->getMessage());
		}
	}
}
