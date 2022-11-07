<?php
namespace wstmart\shop\controller;
use wstmart\common\model\GoodsCats;
use wstmart\shop\validate\Shops as Validate;
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
 * 门店控制器
 */

class Shops extends Base{
    protected $beforeActionList = ['checkAuth'];
    /**
    * 店铺公告页
    */
    public function notice(){
        $notice = model('shops')->getNotice();
        $this->assign('notice',$notice);
        return $this->fetch('shop/notice');
    }
    /**
    * 修改店铺公告
    */
    public function editNotice(){
        $s = model('shops');
        return $s->editNotice();
    }
    
    
    /**
     * 查看店铺设置
     */
    public function info(){
    	$s = model('shops');
    	$object = $s->getByView((int)session('WST_USER.shopId'));
    	$this->assign('object',$object);
    	return $this->fetch('shop/view');
    }

    /**
     * 编辑店铺资料
     */
    public function editInfo(){
        $rs = model('shops')->editInfo();
        return $rs;
    }

    /**
     * 获取店铺金额
     */
    public function getShopMoney(){
        $rs = model('shops')->getFieldsById((int)session('WST_USER.shopId'),'shopMoney,lockMoney,rechargeMoney');
        $urs = model('users')->getFieldsById((int)session('WST_USER.userId'),'payPwd');
        $rs['isSetPayPwd'] = ($urs['payPwd']=='')?0:1;
        $rs['isDraw'] = ((float)WSTConf('CONF.drawCashShopLimit')<=$rs['shopMoney'])?1:0;
        unset($urs);
        return WSTReturn('',1,$rs);
    }

    /*
     * 商家续费
     */
    public function renew(){
        $rs = model('shops')->renew();
        return $rs;
    }

    /**
     * 生成商品二維碼
     */
    public function createShopQrcode(){
        $m = model("shops");
        $vtype = (int)input("vtype",0);
        $shopId = (int)session('WST_USER.shopId');
        
        if($shopId>0){
            $subDir =  'upload/shares/shop/'.date("Y-m");
            WSTCreateDir(WSTRootPath().'/'.$subDir);
            $today = date("Ymd");
            $fname = 'shop_qr_'.($vtype==1?'mo':'we').'_'.$shopId.'.png';
            $outImg = $subDir.'/'.$fname;
            $shareImg = WSTRootPath().'/'.$outImg;
            
            if($vtype==2){
                $shopHomeTheme = $m->getShopHomeTheme($shopId,"weapp");
                $weapp = new \weapp\WSTWeapp(WSTConf('CONF.weAppId'),WSTConf('CONF.weAppKey'));
                $tokenId = $weapp->getToken();
               
                $parm['scene'] = $shopId;
                $page = "pages/shop-home/shop-home";
                if($shopHomeTheme == 'shop_floor'){
                    $page = "pages/shop-self/shop-self";
                }
                $parm['page'] = $page;
                $parm['width'] = 400;
                $url='https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$tokenId;
                $qrdata = $weapp->http($url,json_encode($parm));
                $qr_code = WSTRootPath().'/'.$subDir.'/'.$fname;// 小程序碼
                file_put_contents( $qr_code,$qrdata );
            }else{
                $qr_url = url('mobile/shops/index',array('shopId'=>$shopId),true,true);//二維碼內容   
                //生成二維碼圖片   
                $qr_code = WSTCreateQrcode($qr_url,'','shop',3600,2);
                $qr_code = WSTRootPath().'/'.$qr_code;
            }   

            $rs = $m->createPoster(0,$qr_code,$outImg);
            return WSTReturn(lang('op_ok'),1,$outImg);
        }else{
            return WSTReturn(lang('op_err'));
        }
    }
}
