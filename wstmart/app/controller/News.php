<?php
namespace wstmart\app\controller;
use wstmart\common\model\Articles as M;
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
 * 新闻控制器
 */
class News extends Base{
    /**
     * @OA\Get(
     *     tags={"News"},
     *     path="/app/News/getNewsList",
     *     summary="获取商城快讯列表",
     *     description="获取商城快讯列表",
     *     @OA\Parameter(name="catId", in="query", @OA\Schema(type="integer"), required=true, description="文章分类id", example="1"),
	 *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/pagesize"),
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
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="array",
	 *                                    @OA\Items(
     *                                        @OA\Property(property="articleId", type="integer", description="主键id"),
     *                                        @OA\Property(property="catId", type="integer", description="所属文章分类id"),
     *                                        @OA\Property(property="articleTitle", type="string", description="标题"),
     *                                        @OA\Property(property="isShow", type="integer", description="是否显示， 1:是 0:否"),
     *                                        @OA\Property(property="articleContent", type="string", description="内容"),
     *                                        @OA\Property(property="articleKey", type="string", description="文章搜索关键字"),
     *                                        @OA\Property(property="articleDesc", type="string", description="主键id"),
     *                                        @OA\Property(property="staffId", type="integer", description="职员id"),
     *                                        @OA\Property(property="solve", type="integer", description="用户表示'有用'数"),
     *                                        @OA\Property(property="unsolve", type="integer", description="用户表示'无用'数"),
     *                                        @OA\Property(property="coverImg", type="string", description="预览图"),
     *                                        @OA\Property(property="visitorNum", type="integer", description="浏览数"),
     *                                        @OA\Property(property="TypeStatus", type="integer", description="预览图类型"),
     *                                        @OA\Property(property="likeNum", type="integer", description="点赞数"),
     *                                        @OA\Property(property="catSort", type="integer", description="排序号"),
     *				 						  @OA\Property(property="dataFlag", ref="#/components/schemas/dataFlag"),
	 *				 						  @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                                    ),
     *                                ),
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
    public function getNewsList(){
    	$m = new M();
    	$data = $m->getArticles();
    	foreach($data['data'] as $k=>$v){
    		$data['data'][$k]['articleContent'] = str_replace('${DOMAIN}',WSTConf('CONF.resourcePath'),strip_tags(html_entity_decode($v['articleContent'])));
            $data['data'][$k]['createTime'] = date('Y-m-d',strtotime($data['data'][$k]['createTime']));
    	}
    	return json_encode(WSTReturn('success',1,$data));
    }
    /**
     * @OA\Get(
     *     tags={"News"},
     *     path="/app/News/getNews",
     *     summary="查看文章详情数据",
     *     description="查看文章详情数据",
     *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="文章id", example="25"),
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
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="array",
	 *                                    @OA\Items(
     *                                        @OA\Property(property="articleId", type="integer", description="主键id"),
     *                                        @OA\Property(property="articleTitle", type="string", description="标题"),
     *                                        @OA\Property(property="likeNum", type="integer", description="点赞数"),
	 *				 						  @OA\Property(property="createTime", ref="#/components/schemas/createTime"),
     *                                    ),
     *                                ),
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
    public function getNews(){
    	$m = new M();
    	$data = $m->getNewsById(false);
        if(empty($data)){
            die(lang('no_find_news'));
        }
        unset($data['articleContent']);
        $data['createTime'] = date('Y-m-d',strtotime($data['createTime']));
        return json_encode(WSTReturn('success',1,$data));
    }
    /**
     * @OA\Get(
     *     tags={"News"},
     *     path="/app/News/geturlNews",
     *     summary="查看文章详情",
     *     description="查看文章详情,由webview调用",
	 *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="文章Id", example="1"),
     *     @OA\Response(
     *      response="200",
     *      description="返回文章详情页",
     *     )
     * )
     */
    public function geturlNews(){
    	$m = new M();
    	$data = $m->getNewsById();
    	$data['articleContent']=str_replace('${DOMAIN}',WSTConf('CONF.resourcePath'),htmlspecialchars_decode($data['articleContent']));
    	echo '<!DOCTYPE html><html><head><meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no"><meta name="format-detection" content="telephone=no"></head><body><style>img{width:100%}</style>'.$data['articleContent'].'<script>window.onload=function(){window.location.hash = 1;document.title = document.body.clientHeight;}</script></body></html>';
    }
    /**
     * @OA\Get(
     *     tags={"News"},
     *     path="/app/News/like",
     *     summary="点赞文章",
     *     description="点赞文章",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="文章Id", example="1"),
     *     @OA\Response(
     *          response="200",
     *          description="返回首页基础数据",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/response_schema"),
     *          )
     *     )
     * )
     */
    public function like(){
        $m = new M();
        $data = $m->like();
        return json_encode($data);
    }
    /**
     * @OA\Get(
     *     tags={"News"},
     *     path="/app/News/getChild",
     *     summary="获取文章分类",
     *     description="获取文章分类",
     *     @OA\Parameter(ref="#/components/parameters/tokenId"),
	 *     @OA\Parameter(name="id", in="query", @OA\Schema(type="integer"), required=true, description="文章Id", example="1"),
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
     *                        allOf={
     *                            @OA\Schema(ref="#/components/schemas/paginate_response_schema"),
     *                            @OA\Schema(
     *                                @OA\Property(property="data",type="array",
	 *                                    @OA\Items(
     *                                        @OA\Property(property="catId", type="integer", description="文字分类id"),
     *                                        @OA\Property(property="catName", type="string", description="文字分类名称"),
     *                                    ),
     *                                ),
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
    public function getChild(){
         $m = new M();
         $data = $m->getChildInfos();
         return json_encode(WSTReturn('success','1',$data));
    }
}
