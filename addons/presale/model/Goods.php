<?php
namespace addons\presale\model;
use think\addons\BaseModel as Base;
use wstmart\common\model\GoodsCats;
use addons\presale\model\Presales;
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
 * 商品业务处理类
 */
class Goods extends Base{
	/***
     * 获取前台预售列表
     */
    public function pageQuery($num=0){
        $pagesize = ($num>0)?$num:input('limit/d',16);
        $goodsCatId = (int)input('catId');
        $goodsName = input('goodsName');
        hook('afterUserSearchWords',['keyword'=>$goodsName]);
        $areaId = (int)input('areaId');
        $where = [];

        if($goodsCatId>0){
            $gc = new GoodsCats();
            $goodsCatIds = $gc->getParentIs($goodsCatId);
            $where[] = ['goodsCatIdPath','like',implode('_',$goodsCatIds).'_%'];
        }
        $now = date("Y-m-d H:i:s");
        $where[] = ['p.startTime','<=',$now];
        $where[] = ['p.endTime','>=',$now];
        if($goodsName!='')$where[] = ['p.goodsName','like','%'.$goodsName.'%'];
        $page = Db::name('presales p')
                ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
                ->join('__GOODS__ g','p.goodsId=g.goodsId','inner')
                ->where('p.dataFlag=1 and p.isSale=1 and p.presaleStatus=1  and g.dataFlag=1')
                ->where($where)
                ->field('p.goodsId,p.goodsImg,pl.goodsName,g.shopPrice,p.reduceMoney,p.startTime,p.endTime,p.id,p.goodsNum,p.orderNum,g.gallery,g.goodsUnit')
                ->order('p.startTime p,p.id')
                ->paginate($pagesize)->toArray();
        if(count($page)>0){
            $time = time();
            foreach($page['data'] as $key =>$v){
                $page['data'][$key]['presalePrice'] = WSTBCMoney($v['shopPrice'],-$v['reduceMoney']);
                $page['data'][$key]['goodsImg'] = WSTImg($v['goodsImg']);
                $page['data'][$key]['gallery'] = ($v['gallery']!='')?explode(',',$v['gallery']):'';
                if(strtotime($v['startTime'])<=$time && strtotime($v['endTime'])>=$time){
                    $page['data'][$key]['status'] = 1;
                }else if(strtotime($v['startTime'])>$time){
                    $page['data'][$key]['status'] = 0;
                }else{
                    $page['data'][$key]['status'] = -1;
                }
                $page['data'][$key]['startTime'] = substr($v['startTime'],0,16);
                $page['data'][$key]['endTime'] = substr($v['endTime'],0,16);
            }
        }
        return $page;
    }

    /**
     * 获取组合套餐页面信息
     */
    public function getBySale($id){
        $key = input('key');
        $rs = Db::name('presales')
            ->alias('p')
            ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
            ->field('p.*,pl.goodsName,pl.goodsTips,pl.goodsSeoKeywords,pl.goodsSeoDesc')
            ->where(['id'=>$id,'dataFlag'=>1])->find();
        if(!empty($rs)){
            $rs['read'] = false;
            //判断是否可以公开查看
            $viKey = WSTShopEncrypt($rs['shopId']);
            if(($rs['presaleStatus']!=1 || $rs['isSale']!=1)  && $viKey != $key)return [];
            if($key!='')$rs['read'] = true;

            $userId = (int)session('WST_USER.userId');

            //获取店铺信息
            $rs['shop'] = model('common/shops')->getShopInfo((int)$rs['shopId']);
            if(empty($rs['shop']))return [];
            $gallery = [];
            $gallery[] = $rs['goodsImg'];
            $goodsId = $rs['goodsId'];
            $goods = Db::name('goods')
                    ->alias('g')
                    ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                    ->field('g.*,gl.goodsDesc')
                    ->where(['g.goodsId'=>$goodsId,'dataFlag'=>1])->find();
            $gunit = WSTDatas('GOODS_UNIT',$goods['goodsUnit']);
            $goods['goodsUnit'] = ($gunit && isset($gunit['dataName']))?$gunit['dataName']:'';
            if($goods['gallery']!=''){
                $tmp = explode(',',$goods['gallery']);
                $gallery = array_merge($gallery,$tmp);
            }
            $rs['goodsDesc'] = htmlspecialchars_decode($goods['goodsDesc']);
            $rs['goodsDesc'] = str_replace('${DOMAIN}',WSTConf('CONF.resourcePath'),$rs['goodsDesc']);
            $rs['gallery'] = $gallery;
            $rs['isSpec'] = $goods['isSpec'];
            $reduceMoney = $rs['reduceMoney'];
            $rs['shopPrice'] = WSTBCMoney($goods['shopPrice'],-$reduceMoney);
            $rs['spec']=[];
            if($goods['isSpec']==1){
                //获取规格值
                $specs = Db::name('spec_cats')->alias('gc')
                        ->join('__SPEC_ITEMS__ sit','gc.catId=sit.catId','inner')
                        ->join('__SPEC_CATS_LANGS__ scl','gc.catId=scl.catId and scl.langId='.WSTCurrLang())
                        ->join('__SPEC_ITEMS_LANGS__ sil','sit.itemId=sil.itemId and sil.langId='.WSTCurrLang())
                        ->where(['sit.goodsId'=>$goodsId,'gc.isShow'=>1,'sit.dataFlag'=>1])
                        ->field('gc.isAllowImg,scl.catName,sit.catId,sit.itemId,sil.itemName,sit.itemImg')
                        ->order('gc.isAllowImg desc,gc.catSort asc,gc.catId asc')->select();
                foreach ($specs as $key =>$v){
                    $rs['spec'][$v['catId']]['name'] = $v['catName'];
                    $rs['spec'][$v['catId']]['isImg'] = $v['isAllowImg'];
                    $rs['spec'][$v['catId']]['list'][] = $v;
                }

                //获取销售规格
                $sales = Db::name('goods_specs')->where('goodsId',$goodsId)->field('id,isDefault,productNo,specIds,marketPrice,specPrice,specStock')->select();
                if(!empty($sales)){

                    foreach ($sales as $key =>$v) {

                        $sales[$key]['specStock'] = ($rs['goodsNum']-$rs['orderNum'])?($rs['goodsNum']-$rs['orderNum']):0;;
                        $sales[$key]['specPrice'] = ($v['specPrice'] - $reduceMoney>0)?($v['specPrice'] - $reduceMoney):0;

                        if($rs['saleType']==1){//定金
                            if($rs['depositType']==1){//百分比
                                $sales[$key]['depositMoney'] = WSTBCMoney($sales[$key]['specPrice']*$rs['depositRate']/100,0);
                            }else{
                                $sales[$key]['depositMoney'] = $rs['depositMoney'];
                            }
                        }else{//全额
                            $sales[$key]['depositMoney'] = $sales[$key]['specPrice'];
                        }
                        if($v['isDefault']==1)$rs['depositMoney'] = $sales[$key]['depositMoney'];
                    }

                    foreach ($sales as $key =>$v){
                        $str = explode(':',$v['specIds']);
                        sort($str);
                        unset($v['specIds']);

                        $rs['saleSpec'][implode(':',$str)] = $v;
                        if($v['isDefault']==1)$rs['defaultSpecs'] = $v;
                    }
                }
            }else{
                if($rs['saleType']==1){//定金
                    if($rs['depositType']==1){//百分比
                        $rs['depositMoney'] = WSTBCMoney($rs['shopPrice']*$rs['depositRate']/100,0);
                    }
                }else{//全额
                    $rs['depositMoney'] = $rs['shopPrice'];
                }
            }
            //获取销售默认规格
            $defaultSpec = Db::name('goods_specs')->where(['goodsId'=>$goodsId,'isDefault'=>1])->field('specIds')->find();
            $rs['goodsSpecId'] = $defaultSpec["specIds"];
            if(!empty($defaultSpec)){
                $specIdl = explode(':',$defaultSpec['specIds']);
                sort($specIdl);
                foreach ($specIdl as $key =>$v){
                    $specs = Db::name('spec_items')->where('itemId',$v)->field('catId')->find();
                    $rs['defaultSpec'][$specs['catId']] = $v;
                }
            }
            $rs['goodsCatId'] = $goods['goodsCatId'];
            $rs['goodsVideo'] = $goods['goodsVideo'];
            $rs['goodsUnit'] = $goods['goodsUnit'];
            $rs['goodsType'] = $goods['goodsType'];
            $rs['appraiseNum'] = $goods['appraiseNum'];
            $rs['goodsStock'] = ($rs['goodsNum']-$rs['orderNum'])?($rs['goodsNum']-$rs['orderNum']):0;
            $rs['isFreeShipping'] = $goods['isFreeShipping'];

            $time = time();
            if(strtotime($rs['startTime'])<=$time && strtotime($rs['endTime'])>=$time){
                $rs['status'] = 1;
            }else if(strtotime($rs['startTime'])>$time){
                $rs['status'] = 0;
            }else{
                $rs['status'] = -1;
            }

            //获取商品属性
            $rs['attrs'] = Db::name('attributes')->alias('a')
                            ->join('__ATTRIBUTES_LANGS__ al','a.attrId=al.attrId and al.langId='.WSTCurrLang())
                            ->join('goods_attributes ga','a.attrId=ga.attrId','inner')
                            ->join('__GOODS_ATTRIBUTES_LANGS__ gal','ga.id=gal.goodsAttrId and gal.langId='.WSTCurrLang())
                            ->where(['a.isShow'=>1,'dataFlag'=>1,'ga.goodsId'=>$goodsId])->field('al.attrName,gal.attrVal')
                            ->order('attrSort asc')->select();
            //获取商品评分
            $rs['scores'] = Db::name('goods_scores')->where('goodsId',$goodsId)->field('totalScore,totalUsers')->find();
            $rs['scores']['totalScores'] = ($rs['scores']['totalScore']==0)?5:WSTScore($rs['scores']['totalScore'],$rs['scores']['totalUsers'],5,0,3);
            WSTUnset($rs, 'totalUsers');
            //品牌名称
            $rs['brandName'] = Db::name('brands')->where(['brandId'=>$goods['brandId']])->value('brandName');


            $rs['consult'] = model('common/GoodsConsult')->firstQuery($goods['goodsId']);
            $rs['appraises'] = model('common/GoodsAppraises')->getGoodsEachApprNum($goods['goodsId']);
            $rs['appraise'] = model('common/GoodsAppraises')->getGoodsFirstAppraise($goods['goodsId']);

            //关注
            $f = model('common/Favorites');
            $rs['favShop'] = $rs['shop']['isfollow'];
            $rs['favGood'] = $f->checkFavorite($goodsId);
        }
        return $rs;
    }


    /*
    * 商品详情分享海报
    */
    public function createPoster($userId,$qr_code,$outImg){

        $id = input("id");
        $goods = Db::name('presales p')
            ->join('__PRESALES_LANGS__ pl','pl.presaleId=p.id and pl.langId='.WSTCurrLang())
            ->join('__GOODS__ g','p.goodsId=g.goodsId','inner')->where(['p.id'=>$id])->field("p.*,g.shopPrice,pl.goodsName")->find();
        $goods['presalePrice'] = WSTBCMoney($goods['shopPrice'],-$goods['reduceMoney']);
        //生成二维码图片
        $share_bg = WSTConf('CONF.resourceDomain').'/'.WSTConf("CONF.goodsPosterBg");
        $share_bg = imagecreatefromstring(file_get_contents($share_bg));
        $new_qrcode = imagecreatefromstring(file_get_contents($qr_code));

        $share_width = imagesx($share_bg);//二维码图片宽度
        $share_height = imagesy($share_bg);//二维码图片高度
        $new_width = imagesx($new_qrcode);//logo图片宽度
        $new_height = imagesy($new_qrcode);//logo图片高度
        $new_qr_width = $share_width / 5;
        $new_qr_height = $new_qr_width;
        $from_width = ($share_width - $new_qr_width) / 2;

        //重新组合图片并调整大小
        imagecopyresampled($share_bg, $new_qrcode, 495, 925, 0, 0, $new_qr_width, $new_qr_height, $new_width, $new_height);
        imagedestroy($new_qrcode);
        unlink($qr_code);
        $new_qrcode = WSTConf('CONF.resourceDomain').'/'.$goods['goodsImg'];
        $new_qrcode = imagecreatefromstring(file_get_contents($new_qrcode));

        $new_width = imagesx($new_qrcode);//logo图片宽度
        $new_height = imagesy($new_qrcode);//logo图片高度
        $new_qr_width = $share_width-140;
        $new_qr_height = $new_qr_width;
        $from_width = ($share_width - $new_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled($share_bg, $new_qrcode, $from_width, 70, 0, 0, $new_qr_width, $new_qr_height, $new_width, $new_height);

        // 字体文件
        $textcolor = imagecolorallocate($share_bg,50,50,50);
        $textcolor2 = imagecolorallocate($share_bg,0,0,0);
        $font = WSTRootPath().'/extend/verify/verify/ttfs/SourceHanSerifCN-Medium.otf';//思源字体
        $text = WSTImageAutoWrap(22, 0, $font, $goods['goodsName'],630);
        imagettftext($share_bg, 22, 0, 70, 760, $textcolor, $font, $text);
        $vh = 1100;
        if($userId>0){
            $user = Db::name("users")->where(["userId"=>$userId])->field("userPhoto,userName,loginName")->find();
            //$user["userPhoto"] = '';
            $new_qrcode = WSTUserPhoto($user["userPhoto"]);
            if(substr($new_qrcode,0,4)!='http' && $new_qrcode){
                $new_qrcode = WSTConf('CONF.resourceDomain').'/'.($user["userPhoto"]?$user["userPhoto"]:WSTConf('CONF.userLogo'));
                $tmpImg = WSTRootPath().'/upload/shares/presale/'.date("Y-m").'/'.$userId.'.jpg';
                $new_qrcode = WSTCutFillet($new_qrcode, $tmpImg);
                $new_qrcode = imagecreatefromstring(file_get_contents($new_qrcode));
                unlink($tmpImg);
            }else{
                $new_qrcode = imagecreatefromstring(file_get_contents($new_qrcode));
            }

            //重新组合图片并调整大小
            WSTImagecopymergeAlpha($share_bg, $new_qrcode, 70, 954, 0, 0, 100,  100, 100);

            $userName = $user["userName"]?$user["userName"]:$user["loginName"];
            $text2 = mb_convert_encoding($userName, "html-entities", "utf-8"); //转成html编码
            imagettftext($share_bg, 22, 0, 185, 1010, $textcolor2, $font, $text2);
        }else{
            $vh = 1030;
        }
        $text2 = mb_convert_encoding(lang('presale_share_poster_price'), "html-entities", "utf-8"); //转成html编码
        imagettftext($share_bg, 18, 0, 80, $vh, $textcolor2, $font, $text2);

        $textcolor3 = imagecolorallocate($share_bg,255,0,54);
        $text = WSTImageAutoWrap(20, 0, $font, lang('currency_symbol').(float)$goods['presalePrice'],700);
        imagettftext($share_bg, 24, 0, 180, $vh, $textcolor3, $font, $text);

        $text = mb_convert_encoding(lang('presale_share_poster_tips'), "html-entities", "utf-8"); //转成html编码
        imagettftext($share_bg, 24, 0, 126, 1255, $textcolor, $font, $text);
        //输出图片
        $shareImg = WSTRootPath().'/'.$outImg;
        imagepng($share_bg, $shareImg);
        imagedestroy($new_qrcode);
        imagedestroy($share_bg);

        return WSTReturn("",1,["shareImg"=>$outImg]);
    }
}

