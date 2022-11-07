<?php
namespace wstmart\admin\controller;
use wstmart\admin\model\Goods as M;
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
 * 商品控制器
 */
class Goods extends Base{
   /**
	* 查看上架商品列表
	*/
	public function index(){
	    $this->assign("p",(int)input("p"));
    	$this->assign("areaList",model('areas')->listQuery(0));
		return $this->fetch('list_sale');
	}
   /**
    * 批量删除商品
    */
    public function batchDel(){
        $m = new M();
        return $m->batchDel();
    }

    /**
    * 设置违规商品
    */
    public function illegal(){
        $m = new M();
        return $m->illegal();
    }
    /**
    * 批量设置违规商品
    */
    public function batchIllegal(){
        $m = new M();
        return $m->batchIllegal();
    }
    /**
     * 通过商品审核
     */
    public function allow(){
        $m = new M();
        return $m->allow();
    } 
    /**
     * 批量通过商品审核
     */
    public function batchAllow(){
        $m = new M();
        return $m->batchAllow();
    }
	/**
	 * 获取上架商品列表
	 */
	public function saleByPage(){
		$m = new M();
		$rs = $m->saleByPage();
		$rs['status'] = 1;
		return WSTGrid($rs);
	}
	
    /**
	 * 审核中的商品
	 */
    public function auditIndex(){
        $this->assign("p",(int)input("p"));
    	$this->assign("areaList",model('areas')->listQuery(0));
		return $this->fetch('goods/list_audit');
	}
	/**
	 * 获取审核中的商品
	 */
    public function auditByPage(){
		$m = new M();
		$rs = $m->auditByPage();
		$rs['status'] = 1;
		return WSTGrid($rs);
	}
   /**
	 * 审核中的商品
	 */
    public function illegalIndex(){
        $this->assign("p",(int)input("p"));
    	$this->assign("areaList",model('areas')->listQuery(0));
		return $this->fetch('list_illegal');
	}
    /**
	 * 获取违规商品列表
	 */
	public function illegalByPage(){
		$m = new M();
		$rs = $m->illegalByPage();
		$rs['status'] = 1;
		return WSTGrid($rs);
	}
    
    /**
     * 删除商品
     */
    public function del(){
    	$m = new M();
    	return $m->del();
    }

    /**
     * 检测要导出二维码的商品
     */
    public function checkExportGoods(){
        $m = new M();
        $data = [];
        $data['glist'] = $m->checkExportGoods();
        $data['gdir'] = 'upload/shares/goods/'.date("Y-m").'/'.date("Ymdhis").mt_rand(1000,9999);
        return WSTReturn("",1,$data);
    }

    /**
     * 生成商品二维码
     */
    public function createGoodsQrcode(){
        $m = new M();
        $m = model('goods');
        $vtype = (int)input("vtype",0);
        $goodsId = (int)input("goodsId",0);
        if($goodsId>0){
            $subDir =  input("dir");
            WSTCreateDir(WSTRootPath().'/'.$subDir);
            $today = date("Ymd");
            $fname = 'goods_qr_'.($vtype==1?'mo':'we').'_'.$goodsId.'.png';
            $outImg = $subDir.'/'.$fname;
            $shareImg = WSTRootPath().'/'.$outImg;
            
            if($vtype==2){
                $weapp = new \weapp\WSTWeapp(WSTConf('CONF.weAppId'),WSTConf('CONF.weAppKey'));
                $tokenId = $weapp->getToken();
               
                $parm['scene'] = $goodsId;
                $parm['page'] = "pages/goods-detail/goods-detail";
                $parm['width'] = 200;
                $parm['is_hyaline'] = true;
                $url='https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$tokenId;
                $qrdata = $weapp->http($url,json_encode($parm));
                $qr_code = WSTRootPath().'/'.$subDir.'/'.$fname;// 小程序码
                file_put_contents( $qr_code,$qrdata );
            }else{
                $qr_url = url('mobile/goods/detail',array('goodsId'=>$goodsId),true,true);//二维码内容   
                //生成二维码图片   
                $qr_code = WSTCreateQrcode($qr_url,'','goods',3600,2);
                $qr_code = WSTRootPath().'/'.$qr_code;
            }   

            $rs = model("common/Goods")->createPoster(0,$qr_code,$outImg);
            return $rs;
        }else{
            return WSTReturn("生成失败，无效商品ID");
        }
    }

    /**
     * 打包下载商品二维码
     */
    public function packageDownQrcode(){
        $m = new M();
        // 需要压缩的目录名
        $dirpath =  input("dir");
        $dirs = explode("/",$dirpath);
        // 创建压缩目录的名称
        $zipFile = input("dir").".zip";
        $subDir = $dirpath."/";
        // 创建新的zip类
        $zip = new \ZipArchive();
        if($zip -> open($zipFile, \ZipArchive::CREATE ) === TRUE) {
            // 将路径存储到变量中
            $dir = opendir($subDir);
            while($file = readdir($dir)) {
                if(is_file($subDir.$file)) {
                    $zip -> addFile($subDir.$file, $file);
                }
            }
            $zip ->close();
            
        }
        //清除文件
        WSTDelDir($dirpath);
        if(count(scandir($dirpath)) == 2){
            rmdir($dirpath);
        }
        return WSTReturn("",1,$zipFile);
    }
}
