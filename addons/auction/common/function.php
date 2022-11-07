<?php
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
 */
/**
* 取出正在进行中的拍卖商品
*/
function auction_list($num=4){
	$au = new \addons\auction\model\Auctions;
	$rs = $au
        ->alias('au')
        ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
        ->where(['dataFlag'=>1,'isClose'=>0,'auctionStatus'=>1,'isSale'=>1])
        ->where([['endTime','>',date('Y-m-d H:i:s')],['startTime','<=',date('Y-m-d H:i:s')]])
        ->limit($num)->order('auctionNum desc,visitNum desc')
        ->field('au.auctionId,aul.goodsName,goodsImg,startTime,endTime')
        ->cache(600)
        ->select();
	return $rs;
}

/**
 * 自定义页面取出正在进行中的拍卖商品
 */
function custom_page_auction_list($shopId=0,$num=6){
    $au = new \addons\auction\model\Auctions;
    $where = [];
    if($shopId>0)$where[] = ["au.shopId",'=',$shopId];
    $rs = $au->alias('au')
        ->join('__AUCTIONS_LANGS__ aul','aul.auctionId=au.auctionId and aul.langId='.WSTCurrLang())
        ->join('__GOODS__ g', 'au.goodsId=g.goodsId', 'inner')
        ->where($where)
        ->where([['endTime','>',date('Y-m-d H:i:s')],['startTime','<=',date('Y-m-d H:i:s')]])
        ->where('au.dataFlag=1 and au.auctionStatus=1 and g.dataFlag=1 and au.isSale=1')
        ->field('au.goodsId,au.goodsImg,aul.goodsName,au.currPrice,au.startTime,au.endTime,au.auctionId,au.auctionNum')
        ->order('au.isClose asc,au.startTime asc,au.updateTime desc')
        ->limit($num)
        ->select()
        ->toArray();
    return $rs;
}

