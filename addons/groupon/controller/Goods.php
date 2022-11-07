<?php
namespace addons\groupon\controller;

use think\addons\Controller;
use addons\groupon\model\Groupons as M;
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
 * 团购商品插件
 */
class Goods extends Controller{
	public function __construct(){
		parent::__construct();
        $m = new M();
        $data = $m->getConfigs();
        $this->assign("seoGrouponKeywords",$data['seoGrouponKeywords']);
        $this->assign("seoGrouponDesc",$data['seoGrouponDesc']);
		$this->assign("v",WSTConf('CONF.wstVersion')."_".WSTConf('CONF.wstPCStyleId'));
	}

	/**
	 * 团购列表
	 */
	public function lists(){
        $catId = (int)input('catId');
        $orderBy = (int)input('orderBy');
        $order = (int)input('order');
        $data = [];
        $data['grouponCatId'] = $catId;
        $m = new M();
        $data['goodsPage'] = $m->pageQuery();
        $cats = WSTGoodsCats(0);
        $catName = lang('group_all_goods_cat');
        foreach($cats as $k => $v){
            if($catId==$v['catId'])$catName = $v['catName'];
        }
        $data['catName'] = $catName;
        $data['catList'] = $cats;
		return $this->fetch("/home/index/list",$data);
	}

    /**
     * 商品详情
     */
    public function detail(){
        $m = new M();
        $goodsId = input('id/d',0);
        $goods = $m->getBySale($goodsId);
        if(!empty($goods)){
            $history = cookie("history_goods");
            $history = is_array($history)?$history:[];
            array_unshift($history, (string)$goods['goodsId']);
            $history = array_values(array_unique($history));

            if(!empty($history)){
                cookie("history_goods",$history,25920000);
            }
            $this->assign('goods',$goods);
            $this->assign('shop',$goods['shop']);

            //分享信息
            $conf = $m->getConfigs();
            $shareInfo['link'] = addon_url('groupon://goods/detail',array('id'=>$goodsId,'shareUserId'=>base64_encode((int)session('WST_USER.userId'))),true,true);
            $shareInfo['title'] = $goods['goodsName'];
            $shareInfo['desc'] = (isset($conf["goodsShareTitle"]) && $conf["goodsShareTitle"]!="")?$conf["goodsShareTitle"]:WSTConf("CONF.mallSlogan");
            $shareInfo['imgUrl'] = WSTConf('CONF.resourceDomain').'/'.$goods['goodsImg'];
            $this->assign('shareInfo', $shareInfo);

            return $this->fetch("/home/index/detail");
        }else{
            $this->redirect('home/error/goods');
        }
    }


    /**
     * 查看团购商品列表
     */
    public function pageByAdmin(){
        $this->checkAdminPrivileges();
        $this->assign("areaList",model('common/areas')->listQuery(0));
        $this->assign("p",(int)input("p"));
        return $this->fetch("/admin/list");
    }

    /**
     * 查询团购商品
     */
    public function pageQueryByAdmin(){
        $this->checkAdminPrivileges();
        $m = new M();
        return WSTGrid($m->pageQueryByAdmin(1));
    }
    /**
     * 查询待审核团购商品
     */
    public function pageAuditQueryByAdmin(){
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
    public function delByAdmin(){
        $this->checkAdminPrivileges();
        $m = new M();
        return $m->delByAdmin();
    }

    /**
     * 微信团购列表页
     */
    public function wxlists(){
    	$gModel = model('wechat/GoodsCats');
    	$data['goodscats'] = $gModel->getGoodsCats();
        $keyword = WSTReplaceFilterWords(input('keyword'),WSTConf("CONF.limitWords"));
        hook('afterUserSearchWords',['keyword'=>input('keyword')]);
    	$this->assign("keyword", $keyword);
    	$this->assign("goodsCatId", input('goodsCatId/d'));
    	$this->assign("data", $data);
        if(WSTConf('CONF.wxenabled')==1){
            $we = WSTWechat();
            $datawx = $we->getJsSignature(request()->scheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            $this->assign("datawx", $datawx);
        }
    	return $this->fetch("/wechat/index/list");
    }
    /**
     * 团购列表
     */
    public function wxGrouplists(){
    	$m = new M();
    	$rs = $m->pageQuery();
    	if(!empty($rs['data'])){
    		foreach ($rs['data'] as $key =>$v){
    			$rs['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
    		}
    	}
    	return $rs;
    }
    /**
     * 微信商品详情
     */
    public function wxdetail(){
    	$m = new M();
    	$goodsId = input('id/d',0);
    	$goods = $m->getBySale($goodsId);

    	if(!empty($goods)){

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
    		$goods['imgcount'] =  count($goods['gallery']);
    		$goods['imgwidth'] = 'width:'.$goods['imgcount'].'00%';
    		$this->assign('info',$goods);
    		if(WSTConf('CONF.wxenabled')==1){
    			$we = WSTWechat();
    			$datawx = $we->getJsSignature(request()->scheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    			$this->assign("datawx", $datawx);
    		}
    		//分享信息
    		$conf = $m->getConfigs();
    		$shareInfo['link'] = addon_url('groupon://goods/wxdetail',array('id'=>$goodsId,'shareUserId'=>base64_encode((int)session('WST_USER.userId'))),true,true);
    		$shareInfo['title'] = $goods['goodsName'];
    		$shareInfo['desc'] = (isset($conf["goodsShareTitle"]) && $conf["goodsShareTitle"]!="")?$conf["goodsShareTitle"]:WSTConf("CONF.mallSlogan");
    		$shareInfo['imgUrl'] = WSTConf('CONF.resourceDomain').'/'.$goods['goodsImg'];
    		$this->assign('shareInfo', $shareInfo);

    		return $this->fetch("/wechat/index/detail");
    	}else{
    		session('wxdetail',lang('group_goods_lost'));
    		$this->redirect('wechat/error/message',['code'=>'wxdetail']);
    	}
    }

    /**
     * 手机团购列表页
     */
    public function molists(){
    	$gModel = model('mobile/GoodsCats');
    	$data['goodscats'] = $gModel->getGoodsCats();
        $keyword = WSTReplaceFilterWords(input('keyword'),WSTConf("CONF.limitWords"));
        hook('afterUserSearchWords',['keyword'=>input('keyword')]);
    	$this->assign("keyword", $keyword);
    	$this->assign("goodsCatId", input('goodsCatId/d'));
    	$this->assign("data", $data);
    	return $this->fetch("/mobile/index/list");
    }
    /**
     * 手机商品详情
     */
    public function modetail(){
    	$m = new M();
    	$goodsId = input('id/d',0);
    	$goods = $m->getBySale($goodsId);
    	if(!empty($goods)){
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
    		$goods['imgcount'] =  count($goods['gallery']);
    		$goods['imgwidth'] = 'width:'.$goods['imgcount'].'00%';
    		$this->assign('info',$goods);

    		//分享信息
    		$conf = $m->getConfigs();
    		$shareInfo['link'] = addon_url('groupon://goods/modetail',array('id'=>$goodsId,'shareUserId'=>base64_encode((int)session('WST_USER.userId'))),true,true);
    		$shareInfo['title'] = $goods['goodsName'];
    		$shareInfo['desc'] = (isset($conf["goodsShareTitle"]) && $conf["goodsShareTitle"]!="")?$conf["goodsShareTitle"]:WSTConf("CONF.mallSlogan");
    		$shareInfo['imgUrl'] = WSTConf('CONF.resourceDomain').'/'.$goods['goodsImg'];
    		$this->assign('shareInfo', $shareInfo);
    		return $this->fetch("/mobile/index/detail");
    	}else{
    		session('modetail',lang('group_goods_lost'));
    		$this->redirect('mobile/error/message',['code'=>'modetail']);
    	}
    }

    /*****************************小程序*****************************/
    /**
     * 积分商城列表页
     */
    public function welists(){
        $user = model('weapp/users')->getById();
        $gModel = model('weapp/GoodsCats');
        $data['goodscats'] = $gModel->getGoodsCats();
        $data['user'] = $user;
        return jsonReturn('success',1,$data);
    }
    /**
     * 积分商城列表
     */
    public function wePageQuery(){
        $m = new M();
        $rs = $m->pageQuery();
        if(!empty($rs['data'])){
            foreach ($rs['data'] as $key =>$v){
                $rs['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
            }
        }
        return jsonReturn('success',1,$rs);
    }

    /**
     * 商品详情
     */
    public function weDetail(){
        $userId= model('weapp/index')->getUserId();
        $m = new M();
        $grouponId = (int)input('id');
        $goods = $m->getBySale($grouponId,$userId);

        if(!empty($goods)){
            $goods['goodsDesc']=htmlspecialchars_decode($goods['goodsDesc']);
            $rule = '/<img src="\/(upload.*?)"/';
            preg_match_all($rule, $goods['goodsDesc'], $images);

            foreach($images[0] as $k=>$v){
                $goods['goodsDesc'] = str_replace('/'.$images[1][$k], Request::root().'/'.WSTConf("CONF.goodsLogo") . "\"  data-echo=\"".Request::root()."/".WSTImg($images[1][$k],3), $goods['goodsDesc']);
            }

            //分享信息
            $conf = $m->getConfigs();
            $shareInfo['title'] = $goods['goodsName'];
            $shareInfo['desc'] = (isset($conf["goodsShareTitle"]) && $conf["goodsShareTitle"]!="")?$conf["goodsShareTitle"]:WSTConf("CONF.mallSlogan");
            $shareInfo['imgUrl'] = WSTConf('CONF.resourceDomain').'/'.$goods['goodsImg'];
            $goods['shareInfo'] = $shareInfo;
            // 猜你喜欢6件商品
            $like = model('common/Tags')->listByGoods('best',$goods['goodsCatId'],6);
            foreach($like as $k=>$v){
                // 删除无用字段
                unset($like[$k]['shopName']);
                unset($like[$k]['shopId']);
                unset($like[$k]['goodsSn']);
                unset($like[$k]['goodsStock']);
                unset($like[$k]['saleNum']);
                unset($like[$k]['marketPrice']);
                unset($like[$k]['isSpec']);
                unset($like[$k]['appraiseNum']);
                unset($like[$k]['visitNum']);
                // 替换商品图片
                $like[$k]['goodsImg'] = WSTImg($v['goodsImg'],3,'goodsLogo');
            }
            $goods['like'] = $like;
            $goods['carts'] = model('weapp/carts')->cartNum();
            $goods["nowTime"] = date("Y-m-d H:i:s");


            $firstAppraise = model('weapp/GoodsAppraises')->getGoodsFirstAppraise($goods['goodsId'],0);
            $images = $firstAppraise['images'];
            if(is_array($images)){
                foreach($images as $k2=>$v2){
                    $images[$k2] = WSTImg($v2,3);
                }
            }
            $firstAppraise['images'] = $images;
            $goods['firstAppraise'] = $firstAppraise;
            return jsonReturn('success',1,$goods);
        }
        return jsonReturn(lang('group_not_find_goods'),-1);
    }


    /***************************************海报****************************************/

    /**
     * 生成海报【微信】
     */
    public function wxCreatePoster(){
        $m = new M();
        $userId = (int)session("WST_USER.userId");
        $isNew = (int)input("isNew",0);
        $id = (int)input("id",0);
        $subDir =  'upload/shares/groupon/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'groupon_qr_wx_'.$today.'_'.$id.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        if($isNew==0){
            if (file_exists($shareImg)) {
                return WSTReturn("",1,["shareImg"=>$outImg]);
            }
        }
        $qr_url = addon_url('groupon://goods/wxdetail',array('id'=>$id,'shareUserId'=>base64_encode($userId)),true,true);//二维码内容
        //生成二维码图片
        $qr_code = WSTCreateQrcode($qr_url,'','groupon',3600,2);
        $qr_code = WSTRootPath().'/'.$qr_code;
        $rs = $m->createPoster($userId,$qr_code,$outImg);
        return $rs;
    }

    /**
     * 生成海报【手机版】
     */
    public function moCreatePoster(){
        $m = new M();
        $userId = (int)session("WST_USER.userId");
        $isNew = (int)input("isNew",0);
        $id = (int)input("id",0);
        $subDir =  'upload/shares/groupon/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'groupon_qr_mo_'.$today.'_'.$id.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        if($isNew==0){
            if (file_exists($shareImg)) {
                return WSTReturn("",1,["shareImg"=>$outImg]);
            }
        }
        $qr_url = addon_url('groupon://goods/modetail',array('id'=>$id,'shareUserId'=>base64_encode($userId)),true,true);//二维码内容
        //生成二维码图片
        $qr_code = WSTCreateQrcode($qr_url,'','groupon',3600,2);
        $qr_code = WSTRootPath().'/'.$qr_code;
        $rs = $m->createPoster($userId,$qr_code,$outImg);
        return $rs;
    }

    /**
     * 获取商品小程序码
     */
    public function weAppShareQrCode(){
        $goodsId = (int)input("id",0);
        $subDir =  'upload/shares/groupon/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'groupon_qr_we_'.$today.'_'.$goodsId.'.png';
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

    /**
     * 小程序首页商品列表
     */
    public function weHomeList(){
        $m = new M();
        $rs = $m->pageQuery(3);
        foreach ($rs['data'] as $key =>$v){
            $rs['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],2);
        }
        return jsonReturn('success',1,$rs);
    }
}
