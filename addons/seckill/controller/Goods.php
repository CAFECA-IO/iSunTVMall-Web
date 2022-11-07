<?php
namespace addons\seckill\controller;
use Env;
use think\addons\Controller;
use addons\seckill\model\Seckills as M;
use addons\seckill\model\SeckillGoods as GM;
use addons\seckill\model\SeckillTimeIntervals as TM;
use Request;
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
 * 秒杀商品控件器
 */
class Goods extends Controller{
	public function __construct(){
		parent::__construct();
        $m = new M();
        $data = $m->getConf("Seckill");
        $this->assign("seoSeckillKeywords",$data['seoSeckillKeywords']);
        $this->assign("seoSeckillDesc",$data['seoSeckillDesc']);
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}
    /***********************************PC端**********************************/
	/**
	 * 秒杀列表
	 */
	public function lists(){

        $tm = new TM();
        $times = $tm->queryCurrList();
        $this->assign("shopId",input('shopId'));
        $this->assign("keyword", input('keyword'));
        $this->assign("times", $times);
        
        $catId = (int)input('catId');
        $data = [];
        $data['catId'] = $catId;
        $cats = WSTGoodsCats(0);
        $data['catList'] = $cats;
        $this->assign('catId', $catId);
        $this->assign('goodsCat', $cats);
		return $this->fetch("/home/index/goods_list",$data);
	}

    /**
     * 商品详情
     */
    public function detail(){
        $gm = new GM();
        $id = (int)input('id/d',0);
        $userId = (int)session('WST_USER.userId');
        $goods = $gm->getBySale($id,$userId,1);
        $cats = WSTGoodsCats(0);
        $data['catList'] = $cats;
       
        $this->assign('goodsCat', $cats);
        if(!empty($goods)){
            $history = cookie("history_goods");
            $history = is_array($history)?$history:[];
            array_unshift($history, (string)$goods['goodsId']);
            $history = array_values(array_unique($history));
            if(!empty($history)){
                cookie("history_goods",$history,25920000);
            }
            $goods["nowTime"] = date("Y-m-d H:i:s");
            $this->assign('goods',$goods);
            $catIds = (explode("_", $goods['goodsCatIdPath']));
            $this->assign('catId', $catIds[0]);
            $this->assign('shop',$goods['shop']);
            $m = new M();
            //分享信息
            $conf = $m->getConf("Seckill");
            $shareInfo['link'] = addon_url('seckill://goods/detail',array('id'=>$id,'shareUserId'=>base64_encode((int)session('WST_USER.userId'))),true,true);
            $shareInfo['title'] = $goods['goodsName'];
            $shareInfo['desc'] = (isset($conf["goodsShareTitle"]) && $conf["goodsShareTitle"]!="")?$conf["goodsShareTitle"]:WSTConf("CONF.mallSlogan");
            $shareInfo['imgUrl'] = WSTConf('CONF.resourceDomain').'/'.$goods['goodsImg'];
            $this->assign('shareInfo', $shareInfo);

            $qrcode = WSTCreateQrcode(addon_url("seckill://goods/detail",array('id' =>$id),true,true),'','goods',3600,4);
            $this->assign('qrcode',$qrcode);

            return $this->fetch("/home/index/goods_detail");
        }else{
            $this->redirect('home/error/goods');
        }
    }


    /***********************************微信端**********************************/
    /**
     * 微信秒杀列表页
     */
    public function wxlists(){
    	$tm = new TM();
    	$times = $tm->queryCurrList();
        $this->assign("shopId",input('shopId'));
    	$this->assign("keyword", input('keyword'));
    	$this->assign("times", $times);
        if(WSTConf('CONF.wxenabled')==1){
            $we = WSTWechat();
            $datawx = $we->getJsSignature(request()->scheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            $this->assign("datawx", $datawx);
        }
    	return $this->fetch("/wechat/index/goods_list");
    }
    /**
     * 秒杀列表
     */
    public function pageQuery(){
    	$m = new GM();
    	$rs = $m->pageQuery();

    	if(!empty($rs['data'])){
    		foreach ($rs['data'] as $key =>$v){
    			$rs['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
    		}
    	}
    	return $rs;
    }

    public function getNowTime(){
        return array("nowTime"=>date("Y-m-d H:i:s"));
    }
    /**
     * 微信商品详情
     */
    public function wxdetail(){
       
    	$gm = new GM();
        $userId = (int)session('WST_USER.userId');
    	$id = (int)input('id/d',0);
    	$goods = $gm->getBySale($id,$userId,3);
    	if(!empty($goods)){
    		$goods['goodsDesc']=htmlspecialchars_decode($goods['goodsDesc']);
            $goods['goodsDesc'] = str_replace('${DOMAIN}',WSTConf('CONF.resourcePath'),$goods['goodsDesc']);
            $rule = '/<img src="\/.*?(upload.*?)"/';
    		preg_match_all($rule, $goods['goodsDesc'], $images);
    		
    		foreach($images[0] as $k=>$v){
    			$goods['goodsDesc'] = str_replace(WSTConf('CONF.resourcePath').'/'.$images[1][$k], Request::root().'/'.WSTConf("CONF.goodsLogo") . "\"  data-echo=\"".Request::root()."/".WSTImg($images[1][$k],2), $goods['goodsDesc']);
    		}
    		
    		$history = cookie("history_goods");
    		$history = is_array($history)?$history:[];
    		array_unshift($history, (string)$goods['goodsId']);
    		$history = array_values(array_unique($history));
    
    		if(!empty($history)){
    			cookie("history_goods",$history,25920000);
    		}
    		
    		if(WSTConf('CONF.wxenabled')==1){
    			$we = WSTWechat();
    			$datawx = $we->getJsSignature(request()->scheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    			$this->assign("datawx", $datawx);
    		}
    		//分享信息
            $m = new M();
    		$conf = $m->getConf("Seckill");
    		$shareInfo['link'] = addon_url('seckill://goods/wxdetail',array('id'=>$id,'shareUserId'=>base64_encode((int)session('WST_USER.userId'))),true,true);
    		$shareInfo['title'] = $goods['goodsName'];
    		$shareInfo['desc'] = (isset($conf["goodsShareTitle"]) && $conf["goodsShareTitle"]!="")?$conf["goodsShareTitle"]:WSTConf("CONF.mallSlogan");
    		$shareInfo['imgUrl'] = WSTConf('CONF.resourceDomain').'/'.$goods['goodsImg'];
    		$this->assign('shareInfo', $shareInfo);
            $goods["nowTime"] = date("Y-m-d H:i:s");
            $this->assign('goods',$goods);
    		return $this->fetch("/wechat/index/goods_detail");
    	}else{
    		session('wxdetail','对不起你要找的商品不见了~~o(>_<)o~~');
    		$this->redirect('wechat/error/message',['code'=>'wxdetail']);
    	}
    }

    public function getGoodsDesc(){
        $m = new GM();
        $goodsId = (int)input("goodsId");
        $goods = $m->getGoodsDetail($goodsId);
        $goods['goodsDesc']=htmlspecialchars_decode($goods['goodsDesc']);
        $goods['goodsDesc'] = str_replace('${DOMAIN}',WSTConf('CONF.resourcePath'),$goods['goodsDesc']);
        $rule = '/<img src="\/(upload.*?)"/';
        preg_match_all($rule, $goods['goodsDesc'], $images);
        foreach($images[0] as $k=>$v){
            $goods['goodsDesc'] = str_replace('/'.$images[1][$k], Request::root().'/'.WSTConf("CONF.goodsLogo") . "\"  data-echo=\"".Request::root()."/".WSTImg($images[1][$k],3), $goods['goodsDesc']);
        }
        return WSTReturn('',1,$goods);
    }
    
    /***********************************手机端**********************************/
    /**
     * 手机秒杀列表页
     */
    public function molists(){
    	$tm = new TM();
        $times = $tm->queryCurrList();
        $this->assign("shopId",input('shopId'));
        $this->assign("keyword", input('keyword'));
        $this->assign("times", $times);
    	return $this->fetch("/mobile/index/goods_list");
    }
    /**
     * 手机商品详情
     */
    public function modetail(){
        $gm = new GM();
        $userId = (int)session('WST_USER.userId');
        $id = (int)input('id/d',0);
        $goods = $gm->getBySale($id,$userId,3);
        if(!empty($goods)){
            $goods['goodsDesc']=htmlspecialchars_decode($goods['goodsDesc']);
            $goods['goodsDesc'] = str_replace('${DOMAIN}',WSTConf('CONF.resourcePath'),$goods['goodsDesc']);
            $rule = '/<img src="\/.*?(upload.*?)"/';
            preg_match_all($rule, $goods['goodsDesc'], $images);
            
            foreach($images[0] as $k=>$v){
                $goods['goodsDesc'] = str_replace(WSTConf('CONF.resourcePath').'/'.$images[1][$k], Request::root().'/'.WSTConf("CONF.goodsLogo") . "\"  data-echo=\"".Request::root()."/".WSTImg($images[1][$k],2), $goods['goodsDesc']);
            }
            
            $history = cookie("history_goods");
            $history = is_array($history)?$history:[];
            array_unshift($history, (string)$goods['goodsId']);
            $history = array_values(array_unique($history));
    
            if(!empty($history)){
                cookie("history_goods",$history,25920000);
            }
            
            //分享信息
            $m = new M();
            $conf = $m->getConf("Seckill");
            $shareInfo['link'] = addon_url('seckill://goods/modetail',array('id'=>$id,'shareUserId'=>base64_encode((int)session('WST_USER.userId'))),true,true);
            $shareInfo['title'] = $goods['goodsName'];
            $shareInfo['desc'] = (isset($conf["goodsShareTitle"]) && $conf["goodsShareTitle"]!="")?$conf["goodsShareTitle"]:WSTConf("CONF.mallSlogan");
            $shareInfo['imgUrl'] = WSTConf('CONF.resourceDomain').'/'.$goods['goodsImg'];
            $this->assign('shareInfo', $shareInfo);
            $goods["nowTime"] = date("Y-m-d H:i:s");
            $this->assign('goods',$goods);
            return $this->fetch("/mobile/index/goods_detail");
        }else{
            session('wxdetail','对不起你要找的商品不见了~~o(>_<)o~~');
            $this->redirect('mobile/error/message',['code'=>'modetail']);
        }
    }

    /***********************************小程序端**********************************/

    /**
     * 秒杀时段列表
     */
    public function weTimes(){
        $tm = new TM();
        $rs = $tm->queryCurrList();
        return jsonReturn('success',1,$rs);
    }
    /**
     * 秒杀列表
     */
    public function wePageQuery(){
        $m = new GM();
        $rs = $m->pageQuery();
        if(!empty($rs['data'])){
            foreach ($rs['data'] as $key =>$v){
                $rs['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
            }
        }
        return jsonReturn('success',1,$rs);
    }

    public function weGetNowTime(){
        $rs = array("nowTime"=>date("Y-m-d H:i:s"));
        return jsonReturn('success',1,$rs);
    }


    /**
     * 小程序首页商品列表
     */
    public function weHomeList(){
        $m = new TM();
        $seckTimer = $m->queryCurrSecInfo();
        $m = new GM();
        $seckGodsList = [];
        if(isset($seckTimer["id"]))$seckGodsList = $m->pageQuery($seckTimer["id"],6);
        $rs = ["seckTimer"=>$seckTimer,"seckGodsList"=>$seckGodsList];
        return jsonReturn('success',1,$rs);
    }


    /**
     * 手机商品详情
     */
    public function wedetail(){
        $gm = new GM();
        $userId= model('weapp/index')->getUserId();
        $id = (int)input('id/d',0);
        $goods = $gm->getBySale($id,$userId,3,1);
        $goods['goodsDesc']=htmlspecialchars_decode($goods['goodsDesc']);
        $rule = '/<img src="\/(upload.*?)"/';
        preg_match_all($rule, $goods['goodsDesc'], $images);
        
        foreach($images[0] as $k=>$v){
            $goods['goodsDesc'] = str_replace('/'.$images[1][$k], Request::root().'/'.WSTConf("CONF.goodsLogo") . "\"  data-echo=\"".Request::root()."/".WSTImg($images[1][$k],3), $goods['goodsDesc']);
        }

        
      
        $history = cookie("history_goods");
        $history = is_array($history)?$history:[];
        array_unshift($history, (string)$goods['goodsId']);
        $history = array_values(array_unique($history));

        if(!empty($history)){
            cookie("history_goods",$history,25920000);
        }
        $goods['carts'] = model('weapp/carts')->cartNum();
        $goods["nowTime"] = date("Y-m-d H:i:s");
        return jsonReturn('success',1,$goods);
       
    }

    /**
     * 创建分享图片
     */
    public function moCreatePoster(){
        
        $m = new GM();
        $userId = (int)session("WST_USER.userId");
        $isNew = (int)input("isNew",0);
        $id = (int)input("id",0);
        $subDir =  'upload/shares/seckill/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'seckill_qr_wx_'.$today.'_'.$id.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        if($isNew==0){
            if (file_exists($shareImg)) {
                return WSTReturn("",1,["shareImg"=>$outImg]);
            }
        }
        $qr_url = addon_url('seckill://goods/modetail',array('id'=>$id,'shareUserId'=>base64_encode($userId)),true,true);//二维码内容   
        //生成二维码图片   
        $qr_code = WSTCreateQrcode($qr_url,'','seckill',3600,2);
        $qr_code = WSTRootPath().'/'.$qr_code;
        $rs = $m->createPoster($userId,$qr_code,$outImg);
        return $rs;
          
    }

    /**
     * 创建分享图片
     */
    public function wxCreatePoster(){

        $m = new GM();
        $userId = (int)session("WST_USER.userId");
        $isNew = (int)input("isNew",0);
        $id = (int)input("id",0);
        $subDir =  'upload/shares/seckill/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'seckill_qr_wx_'.$today.'_'.$id.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        if($isNew==0){
            if (file_exists($shareImg)) {
                return WSTReturn("",1,["shareImg"=>$outImg]);
            }
        }
        $qr_url = addon_url('seckill://goods/wxdetail',array('id'=>$id,'shareUserId'=>base64_encode($userId)),true,true);//二维码内容   
        //生成二维码图片   
        $qr_code = WSTCreateQrcode($qr_url,'','seckill',3600,2);
        $qr_code = WSTRootPath().'/'.$qr_code;
        $rs = $m->createPoster($userId,$qr_code,$outImg);
        return $rs;

    }


    /**
     * 获取商品小程序码
     */
    public function weAppShareQrCode(){
        $goodsId = (int)input("id",0);
        $subDir =  'upload/shares/seckill/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'seckill_qr_we_'.$today.'_'.$goodsId.'.png';
        $outImg = $subDir.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        if (file_exists($shareImg)) {
            return jsonReturn("",1,$outImg);
        }
        $weapp = new \weapp\WSTWeapp(WSTConf('CONF.weAppId'),WSTConf('CONF.weAppKey'));
        $tokenId = $weapp->getToken();
       
        $parm['scene'] = $goodsId;
        $parm['page'] = input('pages');//上线时解除注释
        $parm['width'] = 200;
		$parm['is_hyaline'] = true;
        $url='https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$tokenId;
        $qrdata = $weapp->http($url,json_encode($parm));
        $qr_code = WSTRootPath().'/'.$subDir.'/'.$fname;// 小程序码
        file_put_contents( $qr_code,$qrdata );
        return jsonReturn("",1,$subDir.'/'.$fname);
    }
}