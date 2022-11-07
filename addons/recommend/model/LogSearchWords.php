<?php
namespace addons\recommend\model;
use think\addons\BaseModel as Base;
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
 * 商品搜索日志业务处理
 */
class LogSearchWords extends Base{
    /**
	 * 分页
	 */
	public function pageQuery(){
		$startDate = input('startDate');
		$endDate = input('endDate');
        $searchWord = input('searchWord');
        // 排序
		$sort = input('sort');
		$order = 'lastTime desc';
		if($sort!=''){
			$sortArr = explode('.',$sort);
			$order = $sortArr[0].' '.$sortArr[1];
		}
		$where = [];
		if($startDate!='')$where[] = ['lastTime','>=',$startDate." 00:00:00"];
		if($endDate!='')$where[] = ['lastTime','<=',$endDate." 23:59:59"];
        if($searchWord!='')$where[] = [' searchWord','like',"%".$searchWord."%"];
		return $mrs = Db::name('log_search_words')
			->where($where)
			->order($order)
			->paginate(input('limit/d'));
			
	}

    /**
     * 添加搜索记录
     */
    public function addSearchWords($searchWords){
        $wordList = WSTAnalysis($searchWords);
        if(!empty($wordList)){
            foreach ($wordList as $word){
                if($word!=''){
                    $rs = Db::name("log_search_words")->field("id,searchCnt")->where('searchWord','=',$word)->find();
                    if(empty($rs)){
                        $data = [];
                        $data["searchWord"] = $word;
                        $data["searchCnt"] = 1;
                        $data["lastTime"] = date("Y-m-d H:i:s");
                        Db::name("log_search_words")->insert($data);
                    }else{
                        $data = [];
                        $data["searchCnt"] = $rs["searchCnt"]+1;
                        $data["lastTime"] = date("Y-m-d H:i:s");
                        Db::name("log_search_words")->where("id","=",$rs["id"])->update($data);
                    }
                }
            }
        }
    }

    /**
     * 返回搜索词关联商品的id
     */
    public function userSearchWordsCondition($keyword){
        $words = WSTAnalysis($keyword);
        $searchGoodsIdsRes = [];
        foreach ($words as $v){
            $where = [];
            $where[] = ['l.searchWord','=',$v];
            $where[] = ['g.dataFlag','=',1];
            $where[] = ['g.isSale','=',1];
            $where[] = ['g.goodsStatus','=',1];
            $searchGoodsIds = Db::name('log_search_words')->alias('l')->join('__LOG_SEARCH_WORD_GOODS__ lg','l.id = lg.logId','left')
                ->join('__GOODS__ g','lg.goodsId=g.goodsId','left')
                ->where($where)->order('sort desc,lg.id desc')->column('lg.goodsId');
            if(count($searchGoodsIds)>0)$searchGoodsIdsRes[] = implode(',',$searchGoodsIds);
        }
        if(count($searchGoodsIdsRes)>0)return $searchGoodsIdsRes;
        return '';
    }

    /**
     * 获取已选择商品
     */
    public function listQueryByGoods(){
        $logId = input('logId',0);
        $rs = Db::name("log_search_word_goods")->alias('lg')->join('__GOODS__ g','lg.goodsId=g.goodsId','inner')
            ->join('__SHOPS__ s','s.shopId=g.shopId','inner')
            ->where(['lg.logId'=>$logId,'g.isSale'=>1,'g.dataFlag'=>1,'g.goodsStatus'=>1])
            ->field('g.goodsId,g.goodsName,s.shopName,lg.sort')->order('lg.sort asc')->select();
        return $rs;
    }

    /**
     * 设置商品
     */
    public function edit(){
        $logId = input('logId',0);
        $ids = input('post.ids');
        $ids = explode(',',$ids);
        Db::startTrans();
        try{
            Db::name('log_search_word_goods')->where(['logId'=>$logId])->delete();
            if(!empty($ids)){
                //查看商品是否有效
                $rs = Db::name('goods')->where([['goodsStatus','=',1],['dataFlag','=',1],['goodsId','in',$ids]])->field('goodsId')->select();
                if(!empty($rs)){
                    $data = [];
                    foreach ($rs as $key => $v){
                        $tmp = [];
                        $tmp['logId'] = $logId;
                        $tmp['goodsId'] = $v['goodsId'];
                        $tmp['sort'] = (int)input('post.ipt'.$v['goodsId']);
                        $tmp['createTime'] = date('Y-m-d H:i:s');
                        $data[] = $tmp;
                    }
                    Db::name('log_search_word_goods')->insertAll($data);
                }
            }
            Db::commit();
            return WSTReturn(lang('recommend_op_ok'), 1);
        }catch(\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('recommend_op_err'),-1);
        }
    }

    /**
     * 查询商品
     */
    public function searchQuery(){
        $goodsCatatId = (int)input('post.goodsCatId');
        if($goodsCatatId<=0)return [];
        $goodsCatIds = WSTGoodsCatPath($goodsCatatId);
        $key = input('post.key');
        $where[] = ['g.dataFlag','=',1];
        $where[] = ['g.isSale','=',1];
        $where[] = ['g.goodsStatus','=',1];
        $where[] = ['goodsCatIdPath','like',implode('_',$goodsCatIds).'_%'];
        if($key!='')$where[] = ['goodsName|goodsSn|productNo|shopName','like','%'.$key.'%'];
        return Db::name('goods')->alias('g')->join('__SHOPS__ s','g.shopId=s.shopId','inner')
            ->where($where)->field('g.goodsName,s.shopName,g.goodsId')->limit(50)->select();
    }
}
