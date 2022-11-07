<?php
namespace wstmart\app\controller;
use wstmart\common\model\GoodsCats;
use wstmart\common\model\Attributes as AT;
use think\Cache;
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
     * @OA\Post(
     *     tags={"Goods"},
     *     path="/app/goods/createPoster",
     *     summary="生成海报",
     *     description="生成海报",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="goodsId", in="query", @OA\Schema(type="integer"), required=true, description="普通商品id", example="2"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                  @OA\Schema(ref="#/components/schemas/response_schema"),
     *                  @OA\Schema(
     *                      @OA\Property(property="data",type="object",description="图片路径", example="upload/shares/goods/2020-01/goods_qr_app_20200108_2_31.png")
     *                  )
     *             }
     *         )
     *       ),
     *     ),
     * )
     */
    public function createPoster(){
        $m = model('goods');
        $userId = model('index')->getUserId();
        $isNew = (int)input("isNew",0);
        $goodsId = (int)input("goodsId",0);
        $subDir =  'upload/shares/goods/'.date("Y-m");
        WSTCreateDir(WSTRootPath().'/'.$subDir);
        $today = date("Ymd");
        $fname = 'goods_qr_app_'.$today.'_'.$goodsId.'_'.$userId.'.png';
        $outImg = $subDir.'/'.$fname;
        $shareImg = WSTRootPath().'/'.$outImg;
        $_rs = WSTReturn("",1,$outImg);
        if($isNew==0){
            if (file_exists($shareImg)) {
                return json_encode($_rs);
            }
        }
        $isApp = (int)WSTConf('CONF.appShareOpenType')==2;
        if($isApp){
            // app端
            $qr_url = url('/download',array('goodsId'=>$goodsId,'shareUserId'=>base64_encode($userId)),true,true);
        }else{
            //二维码内容
            $qr_url = url('wechat/goods/detail',array('goodsId'=>$goodsId,'shareUserId'=>base64_encode($userId)),true,true);
        }

        //生成二维码图片
        $qr_code = WSTCreateQrcode($qr_url,'','goods',3600,2);
        $qr_code = WSTRootPath().'/'.$qr_code;
        $rs = $m->createPoster($userId,$qr_code,$outImg);
        return json_encode($_rs);
    }

    /**
     * @OA\Post(
     *     tags={"Goods"},
     *     path="/app/goods/getGuess",
     *     summary="获取猜你喜欢",
     *     description="获取猜你喜欢",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId"),
     *     @OA\Parameter(name="catId", in="query", @OA\Schema(type="integer"), required=true, description="商品分类id", example="47"),
     *     @OA\Parameter(name="goodsIds", in="query", @OA\Schema(type="string"), required=true, description="普通商品id组成的字符串", example="2,3,4,5,6"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="goods",type="array",
     *                               @OA\Items(
     *                                    @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                    @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                    @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                                    @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                    @OA\Property(property="saleNum", ref="#/components/schemas/saleNum"),
     *                                    @OA\Property(property="isNew", type="integer", description="是否为设置为新品，1:是 0:否", example="1"),
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
    public function getGuess(){
        $catId = (int)input('catId');
        $goodsIds = explode(',',input('goodsIds'));
        if(!empty($goodsIds))$goodsIds = array_unique($goodsIds);
        // 猜你喜欢6件商品
        $like = model('Tags')->getGuessLike($catId,6,$goodsIds);
        foreach($like as $k=>$v){
            // 删除无用字段
            unset($like[$k]['shopName']);
            unset($like[$k]['shopId']);
            unset($like[$k]['goodsSn']);
            unset($like[$k]['goodsStock']);
            unset($like[$k]['marketPrice']);
            unset($like[$k]['isSpec']);
            unset($like[$k]['appraiseNum']);
            unset($like[$k]['visitNum']);
            // 替换商品图片
            $like[$k]['goodsImg'] = WSTImg($v['goodsImg'],3);
            $like[$k]['goodsName'] = htmlspecialchars_decode($v['goodsName']);
        }
        $rs = [
            'goods'=>$like
        ];
        return json_encode(WSTReturn('ok',1,$rs));
    }
    /**
     * @OA\Post(
     *     tags={"Goods"},
     *     path="/app/goods/index",
     *     summary="商品主页",
     *     description="商品主页",
	 *     @OA\Parameter(ref="#/components/parameters/tokenId", required=false),
     *     @OA\Parameter(name="goodsId", in="query", @OA\Schema(type="integer"), required=true, description="普通商品id", example="94"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",type="object",
     *                          @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                          @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                          @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                          @OA\Property(property="shopId", ref="#/components/schemas/shopId"),
     *                          @OA\Property(property="goodsType", type="integer", description="商品类型，1:虚拟商品 0:实物商品", example="0"),
     *                          @OA\Property(property="marketPrice", ref="#/components/schemas/marketPrice"),
     *                          @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                          @OA\Property(property="warnStock", type="integer", description="预警库存", example="10"),
     *                          @OA\Property(property="goodsStock", type="integer", description="商品库存", example="10000"),
     *                          @OA\Property(property="goodsUnit", type="string", description="商品单位", example="台"),
     *                          @OA\Property(property="goodsTips", type="string", description="商品促销信息", example="这位大哥来根葱"),
     *                          @OA\Property(property="visitNum", type="integer", description="浏览数", example="1000"),
     *                          @OA\Property(property="appraiseNum", type="integer", description="评价数", example="1000"),
     *                          @OA\Property(property="isSpec", type="integer", description="是否为规格商品，1:是 0:否"),
     *                          @OA\Property(property="gallery",type="array", description="商品相册数组",
     *                              @OA\Items()
     *                          ),
     *                          @OA\Property(property="isFreeShipping", type="integer", description="是否包邮，1:是 0:否"),
     *                          @OA\Property(property="goodsSerachKeywords", type="string", description="商品搜索关键字"),
     *                          @OA\Property(property="goodsVideo", type="string", description="商品视频"),
     *                          @OA\Property(property="isPifa", type="string", description="是否为批发商品，1:是 0:否"),
     *                          @OA\Property(property="goodsVideoThumb", type="string", description="视频缩略图"),
     *                          @OA\Property(property="goodsSeoDesc", type="string", description="seo描述"),
     *                          @OA\Property(property="shippingFeeType", type="integer", description="商品计价方式 1:计件 2:重量 3:体积"),
     *                          @OA\Property(property="goodsWeight", type="number", description="商品重量", example="0.00"),
     *                          @OA\Property(property="goodsVolume", type="number", description="商品体积", example="0.00"),
     *                          @OA\Property(property="shopExpressId", type="integer", description="店铺快递公司ID", example=""),
     *                          @OA\Property(property="isDistribut", type="integer", description="是否为分销商品，1:是 0:否", example=""),
     *                          @OA\Property(property="commission", type="number", description="佣金", example=""),
     *                          @OA\Property(property="shop", type="object", description="店铺信息",
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
     *                              @OA\Property(property="catshops", type="object", description="店铺主营", example=""),
     *
     *                          ),
     *                          @OA\Property(property="spec", type="array", description="规格属性",
     *                              @OA\Items(
     *                                    @OA\Property(property="name", type="string", description="规格名称", example="颜色"),
     *                                    @OA\Property(property="list", type="array", description="规格项",
     *                                        @OA\Items(
     *                                            @OA\Property(property="isAllowImg", type="string", description="是否允许上传图片，1:是 0:否"),
     *                                            @OA\Property(property="catName", type="string", description="规格项名称"),
     *                                            @OA\Property(property="catId", type="string", description="规格id"),
     *                                            @OA\Property(property="itemId", type="string", description="规格项id"),
     *                                            @OA\Property(property="itemName", type="string", description="规格项名称"),
     *                                            @OA\Property(property="itemImg", type="string", description="规格项图片"),
     *                                        )
     *                                    ),
     *                               ),
     *                          ),
     *                          @OA\Property(property="saleSpec", type="array", description="销售规格",
     *                              @OA\Items(
     *                                  @OA\Property(property="47:48", type="object", description="规格项id:规格项id, 规格项的组合",
     *                                      @OA\Property(property="id", type="string", description="规格组合项id"),
     *                                      @OA\Property(property="isDefault", type="string", description="是否为默认规格，1:是 0:否"),
     *                                      @OA\Property(property="productNo", type="string", description="规格组合项产品号"),
     *                                      @OA\Property(property="marketPrice", type="string", description="规格组合项市场价"),
     *                                      @OA\Property(property="specPrice", type="string", description="规格组合项店铺价"),
     *                                      @OA\Property(property="specStock", type="string", description="规格组合项库存"),
     *                                  )
     *                              ),
     *                          ),
     *                          @OA\Property(property="attrs", type="array", description="商品属性",
     *                              @OA\Items(
     *                                    @OA\Property(property="attrName", type="string", description="属性名", example="分辨率"),
     *                                    @OA\Property(property="attrVal", type="string", description="属性值", example="标清SD"),
     *                               ),
     *                          ),
     *                          @OA\Property(property="favShop", type="integer", description="店铺关注id", example=""),
     *                          @OA\Property(property="favGood", type="integer", description="商品关注id", example=""),
     *                          @OA\Property(property="goodsAppr", type="object", description="商品评价",
     *                              @OA\Property(property="id", type="integer", description="评价表id", example=""),
     *                              @OA\Property(property="content", type="string", description="评价内容", example=""),
     *                              @OA\Property(property="images", type="string", description="评价附件", example=""),
     *                              @OA\Property(property="shopReply", type="string", description="商家回复", example=""),
     *                              @OA\Property(property="replyTime", type="string", description="商家回复时间", example=""),
     *                              @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                              @OA\Property(property="goodsScore", type="integer", description="商品评分", example=""),
     *                              @OA\Property(property="serviceScore", type="integer", description="服务评分", example=""),
     *                              @OA\Property(property="timeScore", type="integer", description="时效评分", example=""),
     *                              @OA\Property(property="shopId", type="integer", description="店铺id", example=""),
     *                              @OA\Property(property="orderId", type="integer", description="订单id", example=""),
     *                              @OA\Property(property="shopName", type="string", description="店铺名称", example=""),
     *                              @OA\Property(property="userPhoto", type="string", description="用户头像", example=""),
     *                              @OA\Property(property="loginName", type="string", description="登录名称，经过*号替换处理，类似ws***rt", example=""),
     *                              @OA\Property(property="userTotalScore", type="integer", description="用户总评分", example=""),
     *                              @OA\Property(property="goodsSpecNames", type="array", description="商品规格",
     *                                  @OA\Items()
     *                              ),
     *                          ),
     *                          @OA\Property(property="shareUserId", type="string", description="分享者id，若用户已登录则不为空", example=""),
     *                          @OA\Property(property="isOrderScore", type="string", description="是否开启下单获得积分，1:开启 0:未开启", example=""),
     *                      )
     *
     *                 )
     *             }
     *         )
     *       ),
     *     )
     * )
     */
	public function index(){
		$m = model('goods');
		$userId = model('app/index')->getUserId();
        $goods = $m->getBySale(input('goodsId/d'),$userId);
        // 找不到商品记录
        if(empty($goods))return json_encode(WSTReturn(lang('no_find_goods'),-1));
        // 删除无用字段
        WSTUnset($goods,'goodsSn,productNo,isSale,isBest,isHot,isNew,isRecom,goodsCatIdPath,goodsCatId,shopCatId1,shopCatId2,brandId,goodsStatus,saleTime,goodsSeoKeywords,illegalRemarks,dataFlag,createTime,read');
        // 计算可获得积分
        $goods['score'] = intval($goods['shopPrice']*(float)WSTConf('CONF.moneyToScore'));
        // shareUserId
        $userId = model('index')->getUserId();
        $goods['shareUserId'] = base64_encode($userId);
        // 是否开启下单获得积分
        $goods['isOrderScore'] = WSTConf('CONF.isOrderScore');
        return json_encode(WSTReturn(lang('success_msg'),1,$goods));
	}
    /**
     * @OA\Get(
     *     tags={"Goods"},
     *     path="/app/goods/goodsDetail",
     *     summary="获取商品详情",
     *     description="获取商品详情，由webview调用",
	 *     @OA\Parameter(name="goodsId", in="query", @OA\Schema(type="integer"), required=true, description="普通商品id", example="94"),
     *     @OA\Response(
     *      response="200",
     *      description="返回商品详情页",
     *     ),
     * )
     */
    public function goodsDetail(){
        $detail = model('goods')->getGoodsDetail((int)input('goodsId'));
        if(empty($detail))die();
        $detail['goodsDesc'] = htmlspecialchars_decode($detail['goodsDesc']);
        /*$detail['goodsDesc'] = preg_replace("/(<img.*?)(\.)(.*?>)/i",'$1_m$2$3',$detail['goodsDesc']);*/
        $detail['goodsDesc'] = preg_replace("/(<img.*?)(\.)(.*?>)/i",'$1$2$3',$detail['goodsDesc']);
        $detail['goodsDesc'] = str_replace('${DOMAIN}',WSTConf('CONF.resourceDomain'),$detail['goodsDesc']);
        $this->assign('goodsDesc',$detail);
        return $this->fetch('goods_desc');
    }

    /**
     * @OA\Get(
     *     tags={"Goods"},
     *     path="/app/goods/pageQuery",
     *     summary="获取商品列表",
     *     description="获取商品列表",
	 *     @OA\Parameter(name="brandId", in="query", @OA\Schema(type="integer"), required=false, description="品牌id", example="2"),
	 *     @OA\Parameter(name="shopId", in="query", @OA\Schema(type="integer"), required=false, description="店铺id", example="1"),
	 *     @OA\Parameter(name="catId", in="query", @OA\Schema(type="integer"), required=false, description="商城分类id", example="47"),
	 *     @OA\Parameter(name="condition", in="query", @OA\Schema(type="integer"), required=false, description="排序条件 0=>'saleNum'(销量),1=>'shopPrice'(店铺价格),2=>'visitNum'(浏览量),3=>'saleTime'(上架时间)", example="1"),
	 *     @OA\Parameter(name="desc", in="query", @OA\Schema(type="integer"), required=false, description="排序方式 '0'=>'desc'(降序),'1'=>'asc'(升序)", example="0"),
	 *     @OA\Parameter(name="keyword", in="query", @OA\Schema(type="string"), required=false, description="搜索关键字", example="苹果"),
	 *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="showWay", type="integer", description="商品排列方式，0:一行两个 1:一行一个"),
     *                          @OA\Property(property="goodsPage",
     *                              allOf={
     *                                @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                                @OA\Schema(
     *                                    @OA\Property(property="data",type="array",
     *                                         @OA\Items(
     *                                              @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                              @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                              @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                                              @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                              @OA\Property(property="saleNum", ref="#/components/schemas/saleNum"),
     *                                              @OA\Property(property="totalScore", type="integer", description="商品总评分", example="4"),
     *                                              @OA\Property(property="totalUsers", type="integer", description="商品总评分人数", example="80"),
     *                                              @OA\Property(property="praiseRate", type="string", description="好评率", example="100%"),
     *                                              @OA\Property(property="isFreeShipping", type="string", description="是否包邮，1:是 0:否", example="0"),
     *                                         ),
     *                                    ),
     *                                )
     *                             }
     *                          ),
     *                          @OA\Property(property="goodsFilter", type="array", description="商品筛选数据",
     *                              @OA\Items(
     *                                  @OA\Property(property="attrId", type="integer", description="属性id", example="2"),
     *                                  @OA\Property(property="attrName", type="string", description="属性名称", example="储藏方式"),
     *                                  @OA\Property(property="attrVal", type="array", description="属性值数组，类似：['冷藏','常温']",
     *                                      @OA\Items()
     *                                  ),
     *                              )
     *                          ),
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
    	$m = model('goods');
    	$gc = new GoodsCats();
        $catId = (int)input('catId');
        $rs = [
            // 展示方式一行两个
            'showWay'=>0,
            'keyword'=>WSTReplaceFilterWords(input('keyword'),WSTConf("CONF.limitWords"))
        ];
    	if($catId>0){
            $goodsCatIds = $gc->getParentIs($catId);
            $rs['showWay'] = model('app/GoodsCats')->getShowWay($catId);
    	}else{
    		$goodsCatIds = [];
    	}

         //处理已选属性
        $vs = input('vs');
        $vs = ($vs!='')?explode(',',$vs):[];
        $at = new AT();
        $goodsFilter = $at->listQueryByFilter((int)input('catId/d'));
        $ngoodsFilter = [];
        if(!empty($vs)){
            // 存在筛选条件,取出符合该条件的商品id,根据商品id获取可选属性进行拼凑
            $goodsId = model('goods')->filterByAttributes();

            $attrs = model('Attributes')->getAttribute($goodsId);
            // 去除已选择属性
            foreach ($attrs as $key =>$v){
                if(!in_array($v['attrId'],$vs)){$ngoodsFilter[] = $v;}
            }
        }else{
            // 当前无筛选条件,取出分类下所有属性
            foreach ($goodsFilter as $key =>$v){
                if(!in_array($v['attrId'],$vs))$ngoodsFilter[] = $v;
            }
        }

    	$rs['goodsPage'] = $m->pageQuery($goodsCatIds);

        foreach ($ngoodsFilter as $k => $val) {
           $result = array_values(array_unique($ngoodsFilter[$k]['attrVal']));

           $ngoodsFilter[$k]['attrVal'] = $result;
        }
        $rs['goodsFilter'] = $ngoodsFilter;
        // `券`、`满送`标签
        hook('afterQueryGoods',['page'=>&$rs['goodsPage'],'isApp'=>true]);
    	foreach ($rs['goodsPage']['data'] as $key =>$v){
            $rs['goodsPage']['data'][$key]['goodsName'] = htmlspecialchars_decode($rs['goodsPage']['data'][$key]['goodsName']);
    		$rs['goodsPage']['data'][$key]['goodsImg'] = WSTImg($v['goodsImg'],3);
            $rs['goodsPage']['data'][$key]['praiseRate'] = ($v['totalScore']>0)?(sprintf("%.2f",$v['totalScore']/($v['totalUsers']*15))*100).'%':'100%';
    	}
    	return json_encode(WSTReturn(lang('success_msg'),1,$rs));
    }
    /**
     * @OA\Post(
     *     tags={"Goods"},
     *     path="/app/goods/historyQuery",
     *     summary="获取浏览历史",
     *     description="获取浏览历史",
     *     @OA\Parameter(name="history", in="query", @OA\Schema(type="string"), required=true, description="普通商品id组成的字符串", example="2,3,4,5,6"),
     *     @OA\Response(
     *      response="200",
     *      description="返回数据",
     *      @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_schema"),
     *                 @OA\Schema(
     *                      @OA\Property(property="data",
     *                          @OA\Property(property="list",type="array",
     *                               @OA\Items(
     *                                    @OA\Property(property="goodsId", ref="#/components/schemas/goodsId"),
     *                                    @OA\Property(property="goodsName", ref="#/components/schemas/goodsName"),
     *                                    @OA\Property(property="goodsImg", ref="#/components/schemas/goodsImg"),
     *                                    @OA\Property(property="shopPrice", ref="#/components/schemas/shopPrice"),
     *                                    @OA\Property(property="saleNum", ref="#/components/schemas/saleNum"),
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
    public function historyQuery(){
        $data['list'] = model('goods')->historyQuery();
        if(!empty($data['list'])){
	        foreach($data['list'] as $k=>$v){
	            $data['list'][$k]['goodsImg'] = WSTImg($v['goodsImg'],3);
	        }
        }
        return json_encode(WSTReturn(lang('success_msg'),1,$data));
    }
}
