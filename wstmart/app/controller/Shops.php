<?php
namespace wstmart\app\controller;
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
class Shops extends Base{
    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/getShopHomeTheme",
     *     summary="获取店铺主题",
     *     description="获取店铺主题",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", type="string", description="店铺风格 shop_home:默认风格 shop_floor:楼层风格", example="shop_home")
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function getShopHomeTheme(){
        $rs = model('shops')->getShopHomeTheme((int)input('shopId'));
        return json_encode(WSTReturn('ok', 1, $rs));
    }
    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/shopStreet",
     *     summary="店铺街头部: 广告及商品分类",
     *     description="店铺街头部: 广告及商品分类",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="goodscats",type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="catId", type="integer", description="分类id", example=""),
     *                                 @OA\Property(property="catName", type="string", description="分类名称", example=""),
     *                                 @OA\Property(property="simpleName", type="string", description="分类缩写", example=""),
     *                                 @OA\Property(property="catImg",type="string", description="分类图片"),
     *                             )
     *                         ),
     *                         @OA\Property(property="keyword",type="string", description="搜索的内容"),
     *                         @OA\Property(property="swiper",type="array", description="名铺抢购广告",
     *                             @OA\Items(ref="#/components/schemas/ads_response_schema")
     *                         ),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function shopStreet(){
    	$gc = new GoodsCats();
        $goodscats = $gc->listQuery(0);
        foreach ($goodscats as $k => $v) {
            $_this = $goodscats[$k];
            // 删除无用字段
            unset(
                $_this['parentId'],
                $_this['isShow'],
                $_this['isFloor'],
                $_this['catSort'],
                $_this['dataFlag'],
                $_this['createTime'],
                $_this['commissionRate'],
                $_this['seoTitle'],
                $_this['seoKeywords'],
                $_this['seoDes'],
                $_this['catListTheme'],
                $_this['detailTheme'],
                $_this['mobileCatListTheme'],
                $_this['mobileDetailTheme'],
                $_this['wechatCatListTheme'],
                $_this['subTitle'],
                $_this['showWay'],
                $_this['wechatDetailTheme']);

        }
    	$data['goodscats'] =  $goodscats;
    	$data['keyword'] = urldecode(input('keyword'));
    	$ta = new Tags();
        $swiper = $ta->listAds('app-ads-street',4);
        foreach ($swiper as $k1 => $v1) {
            WSTAllow($swiper[$k1],'adFile,adURL');
        }
        $data['swiper'] = $swiper;
    	echo json_encode(WSTReturn(lang('success_msg'),1,$data));
    	die;
    }
    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/index",
     *     summary="店铺首页",
     *     description="店铺首页",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="shopId", in="query", @OA\Schema(type="integer"), required=true, description="店铺id", example="2"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="shop", type="object", description="店铺信息",
	 *								@OA\Property(property="shopNotice", type="string", description="店铺公告"),
     *                              @OA\Property(property="shopId", type="integer", description="店铺id", example="1"),
	 *								@OA\Property(property="shopImg", type="string", description="店铺头像", example=""),
	 *								@OA\Property(property="shopName", type="string", description="店铺名称", example="WSTMart自营超市"),
     *								@OA\Property(property="shopAddress", type="string", description="店铺地址", example="WSTMart自营超市"),
	 *								@OA\Property(property="shopQQ", type="number", description="店铺qq", example=""),
	 *								@OA\Property(property="shopWangWang", type="string", description="店铺旺旺", example=""),
	 *								@OA\Property(property="shopTel", type="string", description="店铺联系电话", example=""),
	 *								@OA\Property(property="serviceStartTime", type="string", description="店铺开始营业时间", example=""),
	 *								@OA\Property(property="longitude", type="string", description="店铺经度", example=""),
	 *								@OA\Property(property="latitude", type="string", description="店铺纬度", example=""),
	 *								@OA\Property(property="serviceEndTime", type="string", description="店铺开始结束时间", example=""),
	 *								@OA\Property(property="shopKeeper", type="string", description="紧急联系人", example=""),
	 *								@OA\Property(property="mapLevel", type="string", description="", example=""),
	 *								@OA\Property(property="areaId", type="string", description="店铺地址最后一级areaId", example=""),
	 *								@OA\Property(property="isInvoice", type="string", description="是否可开发票，1:是 0:否", example=""),
	 *								@OA\Property(property="freight", type="string", description="店铺运费", example=""),
	 *								@OA\Property(property="invoiceRemarks", type="string", description="开发票须知", example=""),
     *								@OA\Property(property="businessLicenceImg", type="string", description="营业执照", example=""),
     *								@OA\Property(property="scores", type="object", description="店铺评分相关",
     *                                  @OA\Property(property="totalScore", type="number", description="总评分", example=""),
     *                                  @OA\Property(property="totalUsers", type="number", description="总评分人数", example=""),
     *                                  @OA\Property(property="goodsScore", type="number", description="商品总评分", example=""),
     *                                  @OA\Property(property="goodsUsers", type="number", description="商品总评分人数", example=""),
     *                                  @OA\Property(property="serviceScore", type="number", description="服务总评分", example=""),
     *                                  @OA\Property(property="serviceUsers", type="number", description="服务总评分人数", example=""),
     *                                  @OA\Property(property="timeScore", type="number", description="时效总评分", example=""),
     *                                  @OA\Property(property="timeUsers", type="number", description="时效总评分人数", example=""),
     *                              ),
     *                              @OA\Property(property="areas", type="object", description="店铺所在区域", example={"areaId": 693,"areaName2": "天河区","areaName1": "广州市"}),
     *                              @OA\Property(property="accreds", type="array", description="店铺认证",
     *                                  @OA\Items(
     *                                      @OA\Property(property="accredName", type="string", description="认证名称", example="七天无条件退款"),
     *                                      @OA\Property(property="accredImg", type="string", description="认证图标", example=""),
     *                                  )
     *                              ),
     *                              @OA\Property(property="catshops", type="string", description="店铺主营", example=""),
     *                              @OA\Property(property="shopMoveBanner", type="string", description="店铺设置的顶部背景图", example=""),
     *                              @OA\Property(property="shopAdtop", type="string", description="店铺主页顶部默认背景图", example=""),
     *                         ),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function index(){
        $s = model('shops');
        $shopId = (int)input('shopId',1);
        $uId = model('index')->getUserId();
        $data = $s->getShopInfo($shopId, $uId);
        unset(
            $data['configId'],
            $data['shopTitle'],
            $data['shopKeywords'],
            $data['shopDesc'],
            $data['shopBanner'],
            $data['shopAds'],
            $data['shopAdsUrl'],
            $data['shopServicer'],
            $data['shopHotWords'],
            $data['shopStreetImg'],
            $data['shopHomeTheme'],
            $data['mobileShopHomeTheme'],
            $data['wechatShopHomeTheme'],
            $data['userDecoration'],
            $data['isDistribut'],
            $data['distributType'],
            $data['distributOrderRate'],
            $data['followNum']
        );
        $data['shopAdtop'] = WSTConf('CONF.shopAdtop');
        echo json_encode(WSTReturn(lang('success_msg'),1,$data));
        die;
    }
    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/home",
     *     summary="店铺详情",
     *     description="店铺详情",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="shopId", in="query", @OA\Schema(type="integer"), required=true, description="店铺id", example="2"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="shop", type="object", description="店铺信息",
	 *								@OA\Property(property="shopNotice", type="string", description="店铺公告"),
     *                              @OA\Property(property="shopId", type="integer", description="店铺id", example="1"),
	 *								@OA\Property(property="shopImg", type="string", description="店铺头像", example=""),
	 *								@OA\Property(property="shopName", type="string", description="店铺名称", example="WSTMart自营超市"),
     *								@OA\Property(property="shopAddress", type="string", description="店铺地址", example="WSTMart自营超市"),
	 *								@OA\Property(property="shopQQ", type="number", description="店铺qq", example=""),
	 *								@OA\Property(property="shopWangWang", type="string", description="店铺旺旺", example=""),
	 *								@OA\Property(property="shopTel", type="string", description="店铺联系电话", example=""),
	 *								@OA\Property(property="serviceStartTime", type="string", description="店铺开始营业时间", example=""),
	 *								@OA\Property(property="longitude", type="string", description="店铺经度", example=""),
	 *								@OA\Property(property="latitude", type="string", description="店铺纬度", example=""),
	 *								@OA\Property(property="serviceEndTime", type="string", description="店铺开始结束时间", example=""),
	 *								@OA\Property(property="shopKeeper", type="string", description="紧急联系人", example=""),
	 *								@OA\Property(property="mapLevel", type="string", description="", example=""),
	 *								@OA\Property(property="areaId", type="string", description="店铺地址最后一级areaId", example=""),
	 *								@OA\Property(property="isInvoice", type="string", description="是否可开发票，1:是 0:否", example=""),
	 *								@OA\Property(property="freight", type="string", description="店铺运费", example=""),
	 *								@OA\Property(property="invoiceRemarks", type="string", description="开发票须知", example=""),
     *								@OA\Property(property="businessLicenceImg", type="string", description="营业执照", example=""),
     *								@OA\Property(property="scores", type="object", description="店铺评分相关",
     *                                  @OA\Property(property="totalScore", type="number", description="总评分", example=""),
     *                                  @OA\Property(property="totalUsers", type="number", description="总评分人数", example=""),
     *                                  @OA\Property(property="goodsScore", type="number", description="商品总评分", example=""),
     *                                  @OA\Property(property="goodsUsers", type="number", description="商品总评分人数", example=""),
     *                                  @OA\Property(property="serviceScore", type="number", description="服务总评分", example=""),
     *                                  @OA\Property(property="serviceUsers", type="number", description="服务总评分人数", example=""),
     *                                  @OA\Property(property="timeScore", type="number", description="时效总评分", example=""),
     *                                  @OA\Property(property="timeUsers", type="number", description="时效总评分人数", example=""),
     *                              ),
     *                              @OA\Property(property="areas", type="object", description="店铺所在区域", example={"areaId": 693,"areaName2": "天河区","areaName1": "广州市"}),
     *                              @OA\Property(property="accreds", type="array", description="店铺认证",
     *                                  @OA\Items(
     *                                      @OA\Property(property="accredName", type="string", description="认证名称", example="七天无条件退款"),
     *                                      @OA\Property(property="accredImg", type="string", description="认证图标", example=""),
     *                                  )
     *                              ),
     *                              @OA\Property(property="catshops", type="string", description="店铺主营", example=""),
     *                              @OA\Property(property="shopMoveBanner", type="string", description="店铺设置的顶部背景图", example=""),
     *                         ),
     *                         @OA\Property(property="ct1", type="string", description="一级分类id"),
     *                         @OA\Property(property="ct2", type="string", description="二级分类id"),
     *                         @OA\Property(property="rec", type="array", description="推荐商品",
     *                             @OA\Items(
     *                                 @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                 @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                 @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                 @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                             ),
     *                         ),
     *                         @OA\Property(property="shopAdtop", type="string", description="店铺主页顶部默认背景图", example=""),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function home(){
        $s = model('shops');
        $shopId = (int)input("param.shopId/d",1);
        $uId = model('index')->getUserId();
        $data['shop'] = $s->getShopInfo($shopId,$uId);
        $ct1 = input("param.ct1/d",0);
        $ct2 = input("param.ct2/d",0);
        $goodsName = input("param.goodsName");
        /*搜索数据*/
        $data['ct1'] = $ct1;//一级分类
        $data['ct2'] = $ct2;//二级分类
        $data['goodsName'] = urldecode($goodsName);//搜索

        // 店主推荐
        $rec = model('Tags')->listShopGoods('recom',$shopId,0,4);
        $_rec = [];
        foreach($rec as $k=>$v){
            $_rec[$k]['goodsId'] = $v['goodsId'];
            $_rec[$k]['goodsName'] = htmlspecialchars_decode($v['goodsName']);
            $_rec[$k]['shopPrice'] = $v['shopPrice'];
            $_rec[$k]['saleNum'] = $v['saleNum'];
            $_rec[$k]['praiseRate'] = $v['praiseRate'];
            $_rec[$k]['goodsImg'] = WSTImg($v['goodsImg'],3);
        }
        $data['rec'] = $_rec;
        unset(
                $data['shop']['shopAddress'],
                $data['shop']['shopQQ'],
                $data['shop']['shopWangWang'],
                $data['shop']['serviceStartTime'],
                $data['shop']['serviceEndTime'],
                $data['shop']['shopKeeper'],
                $data['shop']['catshops'],
                $data['shop']['shopTitle'],
                $data['shop']['shopDesc'],
                $data['shop']['shopKeywords'],
                $data['shop']['shopHotWords']);
        $data['shopAdtop'] = WSTConf('CONF.shopAdtop');
        echo json_encode(WSTReturn(lang('success_msg'),1,$data));
        die;
    }
    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/getShopGoods",
     *     summary="获取店铺商品",
     *     description="获取店铺商品",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="shopId", in="query", @OA\Schema(type="integer"), required=true, description="店铺id", example="2"),
     *     @OA\Response(
     *      response="200",
     *      description="返回楼层信息",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="array",
     *                                     @OA\Items(
     *                                          @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                          @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                          @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                                          @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                          @OA\Property(property="saleNum", ref="#/components/schemas/saleNum"),
     *                                          @OA\Property(property="marketPrice", type="number", description="市场价格"),
     *                                          @OA\Property(property="appraiseNum", type="integer", description="评价数"),
     *                                          @OA\Property(property="goodsStock", type="integer", description="商品库存"),
     *                                          @OA\Property(property="isFreeShipping", type="string", description="是否包邮 0:不包邮 1:包邮", example="0"),
     *                                     ),
     *                                 ),
     *                             )
     *                         }
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    public function getShopGoods(){
        $shopId = (int)input('shopId',1);
        $g = model('goods');
        $rs = $g->shopGoods($shopId);
        if(empty($rs['data']))return json_encode(WSTReturn(lang('empty_goods'),-1));
        foreach($rs['data'] as $k=>$v){
            $rs['data'][$k]['goodsImg'] = WSTImg($v['goodsImg'],2);
            $rs['data'][$k]['goodsName'] = htmlspecialchars_decode($rs['data'][$k]['goodsName']);
        }
        // 购物车信息
        $carts = model('carts')->getCartInfo();
        // 删除无用字段
        unset($carts['list']);
        $data['carts'] = $carts;
        return json_encode(WSTReturn(lang('success_msg'),1,$rs));
    }

    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/floorShopHome",
     *     summary="楼层风格店铺数据",
     *     description="楼层风格店铺数据",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data",type="object",
     *                         @OA\Property(property="shop", type="object", description="店铺信息",
	 *								@OA\Property(property="shopNotice", type="string", description="店铺公告"),
     *                              @OA\Property(property="shopId", type="integer", description="店铺id", example="1"),
	 *								@OA\Property(property="shopImg", type="string", description="店铺头像", example=""),
	 *								@OA\Property(property="shopName", type="string", description="店铺名称", example="WSTMart自营超市"),
     *								@OA\Property(property="shopAddress", type="string", description="店铺地址", example="WSTMart自营超市"),
	 *								@OA\Property(property="shopQQ", type="number", description="店铺qq", example=""),
	 *								@OA\Property(property="shopWangWang", type="string", description="店铺旺旺", example=""),
	 *								@OA\Property(property="shopTel", type="string", description="店铺联系电话", example=""),
	 *								@OA\Property(property="serviceStartTime", type="string", description="店铺开始营业时间", example=""),
	 *								@OA\Property(property="longitude", type="string", description="店铺经度", example=""),
	 *								@OA\Property(property="latitude", type="string", description="店铺纬度", example=""),
	 *								@OA\Property(property="serviceEndTime", type="string", description="店铺开始结束时间", example=""),
	 *								@OA\Property(property="shopKeeper", type="string", description="紧急联系人", example=""),
	 *								@OA\Property(property="mapLevel", type="string", description="", example=""),
	 *								@OA\Property(property="areaId", type="string", description="店铺地址最后一级areaId", example=""),
	 *								@OA\Property(property="isInvoice", type="string", description="是否可开发票，1:是 0:否", example=""),
	 *								@OA\Property(property="freight", type="string", description="店铺运费", example=""),
	 *								@OA\Property(property="invoiceRemarks", type="string", description="开发票须知", example=""),
     *								@OA\Property(property="businessLicenceImg", type="string", description="营业执照", example=""),
     *								@OA\Property(property="scores", type="object", description="店铺评分相关",
     *                                  @OA\Property(property="totalScore", type="number", description="总评分", example=""),
     *                                  @OA\Property(property="totalUsers", type="number", description="总评分人数", example=""),
     *                                  @OA\Property(property="goodsScore", type="number", description="商品总评分", example=""),
     *                                  @OA\Property(property="goodsUsers", type="number", description="商品总评分人数", example=""),
     *                                  @OA\Property(property="serviceScore", type="number", description="服务总评分", example=""),
     *                                  @OA\Property(property="serviceUsers", type="number", description="服务总评分人数", example=""),
     *                                  @OA\Property(property="timeScore", type="number", description="时效总评分", example=""),
     *                                  @OA\Property(property="timeUsers", type="number", description="时效总评分人数", example=""),
     *                              ),
     *                              @OA\Property(property="areas", type="object", description="店铺所在区域", example={"areaId": 693,"areaName2": "天河区","areaName1": "广州市"}),
     *                              @OA\Property(property="accreds", type="array", description="店铺认证",
     *                                  @OA\Items(
     *                                      @OA\Property(property="accredName", type="string", description="认证名称", example="七天无条件退款"),
     *                                      @OA\Property(property="accredImg", type="string", description="认证图标", example=""),
     *                                  )
     *                              ),
     *                              @OA\Property(property="catshops", type="string", description="店铺主营", example=""),
     *                              @OA\Property(property="shopMoveBanner", type="string", description="店铺设置的顶部背景图", example=""),
     *                         ),
     *                         @OA\Property(property="rec", type="array", description="推荐商品",
     *                             @OA\Items(
     *                                 @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                 @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                 @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                 @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                                 @OA\Property(property="marketPrice", type="number", description="市场价格"),
     *                                 @OA\Property(property="visitNum", type="integer", description="浏览量"),
     *                                 @OA\Property(property="appraiseNum", type="integer", description="评价数"),
     *                                 @OA\Property(property="goodsStock", type="integer", description="商品库存"),
     *                                 @OA\Property(property="saleNum", ref="#/components/schemas/saleNum"),
     *                             ),
     *                         ),
     *                         @OA\Property(property="hot", type="array", description="热销商品(与推荐商品结构一致)",
     *                             @OA\Items(),
     *                         ),
     *                         @OA\Property(property="shopAdtop", type="string", description="店铺主页顶部默认背景图", example=""),
     *                         @OA\Property(property="cartNum", type="integer", description="购物车数量", example=""),
     *                         @OA\Property(property="goodsCats", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="catId", type="integer", description="分类id"),
     *                                 @OA\Property(property="shopId", type="integer", description="店铺id"),
     *                                 @OA\Property(property="parentId", type="integer", description="父级分类id"),
     *                                 @OA\Property(property="catName", type="string", description="分类名称"),
     *                                 @OA\Property(property="catSort", type="integer", description="分类排序号"),
     *                                 @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
     *                                 @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                             ),
     *                         ),
     *                     )
     *                 ),
     *              }
     *             )
     *          )
     *     )
     * )
     */
    public function floorShopHome(){
        $s = model('shops');
        $uId = model('index')->getUserId();
        $shopId = (int)input('shopId');
        $data['shop'] = $s->getShopInfo($shopId, $uId);
        if(empty($data['shop']))return json_encode(WSTReturn(lang('empty_shops'),-1));
        // 删除无用字段
        unset(
            $data['shop']['shopAddress'],
            $data['shop']['shopQQ'],
            $data['shop']['shopWangWang'],
            $data['shop']['serviceStartTime'],
            $data['shop']['serviceEndTime'],
            $data['shop']['shopKeeper'],
            $data['shop']['catshops'],
            $data['shop']['shopTitle'],
            $data['shop']['shopDesc'],
            $data['shop']['shopKeywords'],
            $data['shop']['shopHotWords'],
            $data['shop']['configId'],
            $data['shop']['shopBanner'],
            $data['shop']['shopAds'],
            $data['shop']['shopAdsUrl'],
            $data['shop']['shopServicer'],
            $data['shop']['shopStreetImg'],
            $data['shop']['shopHomeTheme'],
            $data['shop']['mobileShopHomeTheme'],
            $data['shop']['wechatShopHomeTheme'],
            $data['shop']['userDecoration'],
            $data['shop']['isDistribut'],
            $data['shop']['distributType'],
            $data['shop']['distributOrderRate']
        );


        // 店长推荐
        $data['rec'] = model('Tags')->listShopGoods('recom',$shopId,0,5);
        // 热销商品
        $data['hot'] = model('Tags')->listShopGoods('hot',$shopId,0,5);
        $func = function($v){
            $v['goodsName'] = htmlspecialchars_decode($v['goodsName']);
            $v['goodsImg'] = WSTImg($v['goodsImg'],3);
            return $v;
        };
        $data['rec'] = array_map($func, $data['rec']);
        $data['hot'] = array_map($func, $data['hot']);
        $data['shopAdtop'] = WSTConf('CONF.shopAdtop');
        $data['cartNum'] = model('app/Carts')->getCartNum();

        $sm = model("common/ShopCats");
        $data['goodsCats'] = $sm->listQuery($shopId,0);
		return json_encode(WSTReturn(lang('success_msg'), 1, $data));
    }
    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/getFloorData",
     *     summary="获取店铺楼层商品",
     *     description="获取店铺楼层商品",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="catId", in="query", @OA\Schema(type="integer"), required=true, description="店铺分类id", example="2"),
     *     @OA\Response(
     *      response="200",
     *      description="返回楼层信息",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="array",
     *                                     @OA\Items(
     *                                          @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                          @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                          @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                                          @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                          @OA\Property(property="saleNum", ref="#/components/schemas/saleNum"),
     *                                     ),
     *                                 ),
     *                             )
     *                         }
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    public function getFloorData(){
        $m = model("common/Goods");
        $rs = $m->shopCatPageQuery(3);
        return json_encode(WSTReturn('success',1,$rs));
    }
    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/getShopCats",
     *     summary="店铺分类",
     *     description="店铺分类",
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data", type="object",
     *                          @OA\Property(property="catId",  type="integer", description="分类id"),
     *                          @OA\Property(property="catName",  type="string", description="分类名称"),
     *                          @OA\Property(property="shopId",  type="integer", description="店铺id"),
     *                          @OA\Property(property="children", type="array", description="二级分类",
     *                              @OA\Items(
     *                                  @OA\Property(property="catId", type="integer", description="分类id", example=""),
     *                                  @OA\Property(property="parentId", type="integer", description="父级id", example=""),
     *                                  @OA\Property(property="catName", type="string", description="分类名称", example=""),
     *                                  @OA\Property(property="shopId",  type="integer", description="店铺id")
     *                              )
     *                          ),
     *                      )
     *                 )
     *              }
     *
     *         )
     *       ),
     *     )
     * )
     */
    public function getShopCats(){
        $shopId = (int)input('shopId');
        $rs = model('ShopCats')->getShopCats($shopId);
        if(empty($rs))return json_encode(WSTReturn(lang('empty_shop_cats'),-1));
        return json_encode(WSTReturn(lang('success_msg'),1,$rs));
    }

    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/pageQuery",
     *     summary="店铺街列表",
     *     description="店铺街列表",
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data", type="object",
     *                          @OA\Property(property="shopId",  type="integer", description="店铺id"),
     *                          @OA\Property(property="shopImg",  type="string", description="店铺头像"),
     *                          @OA\Property(property="shopName",  type="integer", description="店铺名称"),
     *                          @OA\Property(property="totalScore",  type="integer", description="店铺评分"),
     *                          @OA\Property(property="distince",  type="integer", description="店铺距离"),
     *                          @OA\Property(property="lng",  type="integer", description="经度"),
     *                          @OA\Property(property="lat",  type="integer", description="纬度"),
     *                          @OA\Property(property="accreds", type="array", description="店铺认证",
     *                              @OA\Items(
     *                                  @OA\Property(property="accredName", type="string", description="认证名称", example="七天无条件退款"),
     *                                  @OA\Property(property="accredImg", type="string", description="认证图标", example=""),
     *                              )
     *                          ),
     *                          @OA\Property(property="rec", type="array", description="推荐商品",
     *                             @OA\Items(
     *                                 @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                 @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                 @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                 @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                             ),
     *                         ),
     *                         @OA\Property(property="catshops", type="string", description="店铺主营", example=""),
     *                     ),
     *                     @OA\Property(property="screen", type="string", description="筛选页数据", example=""),
     *                 )
     *              }
     *
     *         )
     *       ),
     *     )
     * )
     */
    public function pageQuery(){
    	$m = model('shops');
    	$rs = $m->pageQuery();
    	$rs['keyword'] = WSTReplaceFilterWords(input('keyword'),WSTConf("CONF.limitWords"));
    	foreach ($rs['data'] as $key =>$v){
    		$rs['data'][$key]['shopImg'] = WSTImg($v['shopImg'],3);
            // 删除无用字段
            unset(
                    $rs['data'][$key]['areaId'],
                    $rs['data'][$key]['areaIdPath'],
                    $rs['data'][$key]['timeUsers'],
                    $rs['data'][$key]['timeScore'],
                    $rs['data'][$key]['serviceUsers'],
                    $rs['data'][$key]['serviceScore'],
                    $rs['data'][$key]['goodsUsers'],
                    $rs['data'][$key]['goodsScore'],
                    $rs['data'][$key]['totalUsers'],
                    $rs['data'][$key]['shopCompany']);
            $_rec = [];
            foreach ($rs['data'][$key]['rec'] as $k1 => $v1) {
                $_rec[$k1]['goodsId'] = $v1['goodsId'];
                $_rec[$k1]['goodsName'] = htmlspecialchars_decode($v1['goodsName']);
                $_rec[$k1]['shopPrice'] = $v1['shopPrice'];
                $_rec[$k1]['goodsImg'] = WSTImg($v1['goodsImg'],3);
            }
            $rs['data'][$key]['rec'] = $_rec;
    	}
    	echo json_encode(WSTReturn(lang('success_msg'),1,$rs));
    	die;
    }
    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/listShopGoods",
     *     summary="普通店铺首页商品数据",
     *     description="普通店铺首页商品数据",
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\Parameter(name="shopId", in="query", @OA\Schema(type="integer"), required=true, description="店铺id", example="2"),
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *              allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data", type="object",
     *                          @OA\Property(property="recom", type="array", description="推荐商品",
     *                             @OA\Items(
     *                                 @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                 @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                 @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                 @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                                 @OA\Property(property="marketPrice", type="number", description="市场价格"),
     *                                 @OA\Property(property="visitNum", type="integer", description="浏览量"),
     *                                 @OA\Property(property="appraiseNum", type="integer", description="评价数"),
     *                                 @OA\Property(property="goodsStock", type="integer", description="商品库存"),
     *                                 @OA\Property(property="saleNum", ref="#/components/schemas/saleNum"),
     *                             ),
     *                         ),
     *                         @OA\Property(property="new", type="array", description="新品(与推荐商品数据一致)",
     *                             @OA\Items(),
     *                         ),
     *                         @OA\Property(property="hot", type="array", description="热销商品(与推荐商品数据一致)",
     *                             @OA\Items(),
     *                         ),
     *                         @OA\Property(property="best", type="array", description="精品商品(与推荐商品数据一致)",
     *                             @OA\Items(),
     *                         ),
     *                     ),
     *                 )
     *              }
     *
     *         )
     *       ),
     *     )
     * )
     */
    public function listShopGoods(){
        $shopId = (int)input('shopId');
        $type = ['0'=>'recom','1'=>'new','2'=>'hot','3'=>'best'];
        $num = 5;
        $cache = 0;
        /*$cacheData = WSTCache('App_SHOP_GOODS_'.$type."_".$shopId);
        if($cacheData)return $cacheData;*/
        foreach ($type as $key => $value) {
        $types = ['recom'=>'isRecom','new'=>'isNew','hot'=>'isHot','best'=>'isBest'];
        $order = ['recom'=>'saleNum desc,goodsId asc','new'=>'saleTime desc,goodsId asc','hot'=>'saleNum desc,goodsId asc','best'=>'saleNum desc,goodsId asc'];
        $where = [];
        $where['g.shopId'] = $shopId;
        $where['isSale'] = 1;
        $where['goodsStatus'] = 1;
        $where['dataFlag'] = 1;
        $where[$types[$value]] = 1;

        $goods[$value] = Db::name('goods')
                    ->alias('g')
                    ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
                    ->join('__GOODS_SCORES__ gs','gs.goodsId = g.goodsId','left')
                   ->where($where)->field('g.goodsId,gl.goodsName,goodsImg,goodsSn,goodsStock,saleNum,shopPrice,marketPrice,isSpec,appraiseNum,visitNum,gs.totalScore,gs.totalUsers')
                   ->order($order[$value])->limit($num)->select();
        $ids = [];
        foreach($goods[$value] as $key =>$v){
            if($v['isSpec']==1)$ids[] = $v['goodsId'];
            $goods[$value][$key]['goodsName'] = htmlspecialchars_decode($v['goodsName']);
            $goods[$value][$key]['praiseRate'] = ($v['totalScore']>0)?(sprintf("%.2f",$v['totalScore']/($v['totalUsers']*15))*100).'%':'100%';
        }
        if(!empty($ids)){
            $specs = [];
            $rs = Db::name('goods_specs gs ')->where([['goodsId','in',$ids],['dataFlag','=',1]])->order('id asc')->select();
            foreach ($rs as $key => $v){
                $specs[$v['goodsId']] = $v;
            }
            foreach($goods[$value] as $key =>$v){
                if(isset($specs[$v['goodsId']]))
                $goods[$value][$key]['specs'] = $specs[$v['goodsId']];
            }
        }
        }
       //WSTCache('App_SHOP_GOODS_'.$type."_".$shopId,$goods,$cache);
        echo json_encode(WSTReturn(lang('success_msg'),1,$goods));
        exit;
    }
    /**
     * @OA\Get(
     *     tags={"Shops"},
     *     path="/app/Shops/map",
     *     summary="查看店铺在图片上的位置",
     *     description="查看店铺在图片上的位置,由webview调用",
	 *     @OA\Parameter(name="longitude", in="query", @OA\Schema(type="string"), required=true, description="经度"),
	 *     @OA\Parameter(name="latitude", in="query", @OA\Schema(type="string"), required=true, description="纬度"),
	 *     @OA\Parameter(name="shopName", in="query", @OA\Schema(type="string"), required=true, description="店铺名称"),
     *     @OA\Response(
     *      response="200",
     *      description="返回文章详情页",
     *     )
     * )
     */
    public function map(){
        $longitude = (float)input('longitude');
        $latitude = (float)input('latitude');
        $shopName = input('shopName');
        $this->assign('longitude',$longitude);
        $this->assign('latitude',$latitude);
        $this->assign('shopName',$shopName);
        return $this->fetch('shop_map');
    }


}
