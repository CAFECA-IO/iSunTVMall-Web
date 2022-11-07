<?php
namespace wstmart\app\controller;
use wstmart\app\model\Index as M;
use wstmart\common\model\Tags;
use wstmart\app\controller\Base;
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
 * 默认控制器
 */
use Env;
class Index extends Base{
    /**
     * 获取语言包
     */
    public function getLocales(){
        $path = Env::get('root_path').'wstmart'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR;
        $tw = $path."zh-TW.json";
        $en = $path."en-US.json";
        $zh = $path."zh-CN.json";
        $locales = [];
        $locales['zh-TW'] = json_decode(\file_get_contents($tw), true);
        $locales['en-US'] = json_decode(\file_get_contents($en), true);
        $locales['zh-CN'] = json_decode(\file_get_contents($zh), true);
        return json_encode($locales);
    }

    /**
     * @OA\Get(
     *     tags={"Index"},
     *     path="/app/index/getStartAds",
     *     summary="获取启动广告",
     *     description="获取启动广告",
     *     @OA\Response(
     *      response="200",
     *      description="返回启动广告",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="data",type="array",description="启动广告信息",
     *                               @OA\Items(
     *                                    @OA\Property(property="adFile", type="string", description="广告图片"),
     *                                    @OA\Property(property="adId", type="integer", description="广告id"),
     *                                    @OA\Property(property="adName", type="string", description="广告标题"),
     *                                    @OA\Property(property="adURL", type="string", description="广告网址"),
     *                                    @OA\Property(property="isOpen", type="boolean", description="广告网址是否为外部链接(http或https开头的网址)，true:是，false:否"),
     *                                    @OA\Property(property="positionHeight", type="integer", description="广告高度"),
     *                                    @OA\Property(property="positionWidth", type="integer", description="广告宽度"),
     *                                    @OA\Property(property="subTitle", type="string", description="广告副标题"),
     *                               ),
     *                           ),
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    /**
     * 获取启动广告图标
     */
    public function getStartAds(){
        // 获取轮播图
        $model = new Tags();
        $adsArr = $model->listAds('app-start-ads', 99, 1);
        if(empty($adsArr)){
            return json_encode(WSTReturn(lang('no_set_ads')));
        }
        // 随机取出一个
        $i = array_rand($adsArr, 1);
        return json_encode(WSTReturn('ok', 1, $adsArr[$i]));
    }

    /**
     * @OA\Get(
     *     tags={"Index"},
     *     path="/app/index/getBtns",
     *     summary="首页导航按钮",
     *     description="获取app端首页导航按钮",
     *     @OA\Response(
     *      response="200",
     *      description="返回首页导航按钮",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="btns",type="array",description="首页导航按钮",
     *                               @OA\Items(
     *                                    @OA\Property(property="id", type="integer", description="主键Id", example="1"),
     *                                    @OA\Property(property="btnName", type="string", description="按钮名称", example="店铺街"),
     *                                    @OA\Property(property="btnSrc", type="string", description="3表示为app端按钮", example="3"),
     *                                    @OA\Property(property="btnUrl", type="string", description="按钮链接的页面，取自app_screens表", example="ShopStreet"),
     *                                    @OA\Property(property="btnImg", type="string", description="导航按钮图片", example="upload/sysconfigs/2018-08/5b73f0a549aec.png"),
     *                                    @OA\Property(property="addonsName", type="string", description="所属插件", example=""),
     *                                    @OA\Property(property="btnSort", type="number", description="排序号", example="0"),
     *                                    @OA\Property(property="params", type="object", description="进入页面时传递的参数，若无参数该字段值为空", example={"catId":47, "goodsName":"wstmart商品"}),
     *                               ),
     *                           ),
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    public function getBtns(){
        $btns = WSTMobileBtns(3);
        // 过滤不符合规则的按钮
        $_btns = [];
        foreach($btns as $v){
            if(strstr($v['btnUrl'],'wst://') && parse_url($v['btnUrl'])){
                $urls = parse_url($v['btnUrl']);
                $v['btnUrl'] = $urls['host'];
                $v['params'] = [];
                if(isset($urls['query']))parse_str($urls['query'],$v['params']);
                array_push($_btns, $v);
            }
        }
        $data = ['btns'=>$_btns];
        return json_encode(WSTReturn('ok',1,$data));
    }
    /**
     * @OA\Get(
     *     tags={"Index"},
     *     path="/app/index/pageQuery",
     *     summary="首页楼层数据",
     *     description="获取app端首页楼层数据",
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/pagesize"),
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
     *                                @OA\Property(property="data",type="array",description="首页楼层信息",
     *                                     @OA\Items(
     *                                          @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                          @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                          @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                                          @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                          @OA\Property(property="saleNum", ref="#/components/schemas/saleNum"),
     *                                          @OA\Property(property="totalScore", type="integer", description="商品总评分", example="4"),
     *                                          @OA\Property(property="totalUsers", type="integer", description="商品总评分人数", example="80"),
     *                                          @OA\Property(property="praiseRate", type="string", description="好评率", example="100%"),
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
    public function pageQuery(){
        $m = model("common/Goods");
        $rs = $m->homeCatPageQuery(3);
        return json_encode(WSTReturn('ok', 1, $rs));
    }
    /**
     * @OA\Get(
     *     tags={"Index"},
     *     path="/app/index/getIndexData",
     *     summary="首页数据",
     *     description="获取app端首页数据",
     *     @OA\Response(
     *      response="200",
     *      description="返回首页基础数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                   description="首页基础数据",
     *                   @OA\Property(property="swiper",type="array",description="首页轮播广告",
     *                        @OA\Items(ref="#/components/schemas/ads_response_schema"),
     *                   ),
     *                   @OA\Property(property="indexAds",type="array",description="商城快讯下方单张广告图",
     *                        @OA\Items(ref="#/components/schemas/ads_response_schema"),
     *                   ),
     *                   @OA\Property(property="ads",type="array",description="横向循环滚动广告图",
     *                        @OA\Items(ref="#/components/schemas/ads_response_schema"),
     *                   ),
     *                   @OA\Property(property="leftAds",type="array",description="热门活动下方左侧单张广告图",
     *                        @OA\Items(ref="#/components/schemas/ads_response_schema"),
     *                   ),
     *                   @OA\Property(property="rightAds",type="array",description="热门活动下方右侧2张广告图",
     *                        @OA\Items(ref="#/components/schemas/ads_response_schema"),
     *                   ),
     *                   @OA\Property(property="bottomAds",type="array",description="热门活动底部3张广告",
     *                        @OA\Items(ref="#/components/schemas/ads_response_schema"),
     *                   ),
     *                   @OA\Property(property="news",type="array",description="获取最新资讯",
     *                        @OA\Items(
     *                            @OA\Property(property="articleId", type="integer", description="文章Id", example="1"),
     *                            @OA\Property(property="articleTitle", type="string", description="文章标题", example="商城更新公告"),
     *                            @OA\Property(property="coverImg", type="string", description="文章缩略图", example="upload/adspic/2019-11/5dca76a2f03c4.jpg"),
     *                            @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                        ),
     *                   ),
     *                   @OA\Property(property="goodsCats",type="array",description="商城一级分类，第一项恒定为'热销商品'",
     *                        @OA\Items(
     *                            @OA\Property(property="catId", type="integer", description="商城分类Id", example="0"),
     *                            @OA\Property(property="parentId", type="integer", description="父级id", example="0"),
     *                            @OA\Property(property="catName", type="string", description="分类名称", example="热销商品"),
     *                            @OA\Property(property="simpleName", type="string", description="分类名缩写", example="热销商品" ),
     *                        ),
     *                   ),
     *                )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    public function getIndexData(){
        $rs = [];
        $rs = WSTReturn('success',1,$rs);
        // 获取轮播图
        $model = new Tags();
        $rs['swiper'] = $model->listAds('app-ads-index',99);
        // 商城快讯下方单张广告图
        $rs['indexAds'] = $model->listAds('app-index-long',1);
        // 横向循环滚动广告图
        $rs['ads'] = $model->listAds('app-index-small',10);
        // 热门活动下方左侧单张广告图
        $rs['leftAds'] = $model->listAds('app-index-left',1);
        // 热门活动下方右侧2张广告图
        $rs['rightAds'] = $model->listAds('app-index-right',2);
        // 热门活动底部3张广告
        $rs['bottomAds'] = $model->listAds('app-index-three',3);
        // 获取弹窗广告
        $rs['popAds'] = $model->listAds('app-pop-ads',1,86400,1);
        // 获取最新资讯
        $rs['news'] = $model->listByNewArticle(4, 86400);
        // 获取商城一级分类
        $goodsCat = model("app/GoodsCats")->listQuery(0);
        $goodsCat = $goodsCat->toArray();
        array_unshift($goodsCat,['catId'=>0,'parentId'=>0,'catName'=>lang('hot_goods'),'simpleName'=>lang('hot_goods')]);
        $rs['goodsCats'] = $goodsCat;
        return json_encode($rs);
    }
    /**
    * 转换图片即删除无用字段
    */
    private function transitionImg($img){
        if(empty($img))return [];
        // 图片转换及删除无用字段
        $_img = [];
        foreach ($img as $k => $v) {
            $_img[$k]['adId'] = $v['adId'];
            $_img[$k]['adURL'] = $v['adURL'];
            $_img[$k]['adFile'] = WSTImg($v['adFile'],2);
        }
        return $_img;
    }
    /**
     * @OA\Get(
     *     tags={"Index"},
     *     path="/app/index/confInfo",
     *     summary="配置信息",
     *     description="获取商城配置信息",
     *     @OA\Response(
     *      response="200",
     *      description="返回商城配置信息",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="smsOpen", type="string", description="开启短信验证，1->开启 0->关闭"),
     *                          @OA\Property(property="smsVerfy", type="string", description="发送短信前是否需要输入验证码，1->开启 0->关闭"),
     *                          @OA\Property(property="userLogo", type="string", description="会员默认头像"),
     *                          @OA\Property(property="shopLogo", type="string", description="店铺默认头像"),
     *                          @OA\Property(property="goodsLogo", type="string", description="商品默认图片"),
     *                          @OA\Property(property="hotWordsSearch", type="string", description="商品热搜词", example="WSTMart,b2c,多商户"),
     *                          @OA\Property(property="mallName", type="string", description="商城名称"),
     *                          @OA\Property(property="mallLogo", type="string", description="商城Logo"),
     *                          @OA\Property(property="serviceTel", type="string", description="联系电话"),
     *                          @OA\Property(property="serviceQQ", type="string", description="客服QQ"),
     *                          @OA\Property(property="serviceEmail", type="string", description="联系邮箱"),
     *                          @OA\Property(property="copyRight", type="string", description="版权所有"),
     *                          @OA\Property(property="resourceDomain", type="string", description="图片域名"),
     *                          @OA\Property(property="isOpenShopApply", type="string", description="是否开启商家入驻"),
     *                          @OA\Property(property="isOpenRecharge", type="string", description="是否开启会员充值"),
     *                          @OA\Property(property="isOrderScore", type="string", description="是否开启下单获积分"),
     *                          @OA\Property(property="moneyToScore", type="string", description="金额兑积分"),
     *                          @OA\Property(property="drawCashUserLimit", type="string", description="最低提现金额"),
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
    public function confInfo(){
        $data['appScheme'] = WSTConf('CONF.appScheme');//appScheme
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
        $data['resourceDomain'] = $this->domain();//图片域名
        $data['isOpenShopApply'] = WSTConf('CONF.isOpenShopApply');// 是否开启商家入驻
        $data['isOpenRecharge'] = WSTConf('CONF.isOpenRecharge');// 是否开启会员充值
        $data['isOrderScore'] = WSTConf('CONF.isOrderScore');// 是否开启下单获积分
        $data['moneyToScore'] = WSTConf('CONF.moneyToScore');// 金额兑积分
        $data['drawCashUserLimit'] = WSTConf('CONF.drawCashUserLimit');// 最低提现金额
        $data['mapKey'] = WSTConf('CONF.mapKey');// 腾讯地图key
        $data['areaCodes'] = WSTAareCodes();// 区号
    	return json_encode(WSTReturn('success',1,$data));
    }
    /**
     * @OA\Get(
     *     tags={"Index"},
     *     path="/app/index/getSysMsgs",
     *     summary="获取用户未读消息数",
     *     description="获取用户未读消息数",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Response(
     *      response="200",
     *      description="返回用户未读消息数",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *            @OA\Property(property="num", type="number", description="未读消息数", example="0")
     *         )
     *       ),
     *     )
     * )
     */
    public function getSysMsgs(){
        $rs = model('index')->getSysMsg('msg');
        $num = $rs['message']['num'];
        return json_encode(['num'=>$num]);
    }
}
