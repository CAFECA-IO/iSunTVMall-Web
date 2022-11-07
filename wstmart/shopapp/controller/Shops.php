<?php
namespace wstmart\shopapp\controller;
use wstmart\common\model\GoodsCats;
use wstmart\common\model\Tags;
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
 * 门店控制器
 */
use Env;
class Shops extends Base{
    protected $beforeActionList = [
            'checkShopAuth'=>['only'=>'getshopbtn,getshopinfo,editinfo,info,getshopnotice,editnotice']
        ];
    /**
     * 获取语言包
     */
    public function getLocales(){
        $path = Env::get('root_path').'wstmart'.DIRECTORY_SEPARATOR.'shopapp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR;
        $cn = $path."zh-CN.json";
        $tw = $path."zh-TW.json";
        $en = $path."en-US.json";
        $locales = [];
        $locales['zh-CN'] = json_decode(\file_get_contents($cn), true);
        $locales['zh-TW'] = json_decode(\file_get_contents($tw), true);
        $locales['en-US'] = json_decode(\file_get_contents($en), true);
        return json_encode($locales);
    }
    /***************************************************************** 商家接口start *************************************************************************/
    /**
     * 保存店铺头像
     */
    public function saveShopImg(){
        $rs = model('shops')->saveShopImg($this->getShopId());
        return json_encode($rs);
    }
    /**
    * 获取登录职员拥有权限的按钮
    */
    public function getShopBtn(){
        $rs = model('shops')->getShopBtn($this->getShopId());
        return json_encode($rs);
    }


    /**
    * 获取店铺信息
    */
    public function getShopInfo(){
        $shopId = $this->getShopId();
        $rs = model('shops')->getshopInfo($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 获取店铺公告
     */
    public function getShopNotice(){
        $shopId = $this->getShopId();
        $rs = model('shops')->getShopNotice($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 编辑店铺公告
     */
    public function editNotice(){
        $shopId = $this->getShopId();
        $rs = model('shops')->editNotice($shopId);
        return json_encode($rs);
    }
    /**
     * 查看店铺设置
     */
    public function info(){
        $shopId = $this->getShopId();
        $rs = model('shops')->getByView($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 编辑店铺资料
     */
    public function editInfo(){
        $shopId = $this->getShopId();
        $rs = model('shops')->editInfo($shopId);
        return json_encode($rs);
    }

    /**
     * 获取店铺广告
     */
    public function getShopAds(){
        $shopId = $this->getShopId();
        $rs = model('ShopConfigs')->getShopAds($shopId);
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * 编辑店铺广告
     */
    public function editAds(){
        $shopId = $this->getShopId();
        $rs = model('ShopConfigs')->editAds($shopId);
        return json_encode($rs);
    }

    /**
     * 配置信息
     */
    public function confInfo(){
        $data['smsOpen'] = WSTConf('CONF.smsOpen');//开启短信验证
        $data['smsVerfy'] = WSTConf('CONF.smsVerfy');//发送短信前是否需要输入验证码
        $data['userLogo'] = WSTConf('CONF.userLogo');//会员默认头像
        $data['shopLogo'] = WSTConf('CONF.shopLogo');//店铺默认头像
        $data['goodsLogo'] = WSTConf('CONF.goodsLogo');//商品默认图片
        $data['hotWordsSearch'] = WSTConf('CONF.hotWordsSearch');//商品热搜词
        $data['mallName'] = WSTConf('CONF.mallName');//商城名称
        $data['mallLogo'] = WSTConf('CONF.mallLogo');//商城Logo
        $data['serviceTel'] = WSTConf('CONF.serviceTel');//联系电话
        $data['serviceQQ'] = WSTConf('CONF.serviceQQ');//客服QQ
        $data['serviceEmail'] = WSTConf('CONF.serviceEmail');//联系邮箱
        $data['copyRight'] = WSTConf('CONF.copyRight');//版权所有
        $data['areaCodes'] = WSTAareCodes();// 区号

        $data['resourceDomain'] = $this->domain();//图片域名
        $data['sysLangs'] = WSTConf('CONF.sysLangs');
        return json_encode(WSTReturn('success',1,$data));
    }

    /**
     * 清除店铺缓存
     */
    public function clearCache(){
        $shopId = $this->getShopId();
        model('common/shops')->clearCache($shopId);
        return json_encode(WSTReturn(lang("clear_cache_ok"), 1));
    }

    /*
     * 商家续费
     */
    public function renew(){
        $userId = model('shopapp/users')->getUserId();
        $shopId = $this->getShopId();
        $rs = model('common/shops')->renew($userId,$shopId);
        return json_encode($rs);
    }

    /***************************************************************** 商家接口end *************************************************************************/
}
