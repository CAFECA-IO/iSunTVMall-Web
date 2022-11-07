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
* 取出正在进行中的团购商品
* @_field 需要取的字段。
* @extra 需要额外取的字段
* @num
*/
function WSTSeckillList($field='',$extra=[],$num=5){
	$gm = new \addons\seckill\model\Seckills;
	$time = date('Y-m-d H:i:s');
	$where = [];
	$where[] = ['startTime','<=',$time];
	$where[] = ['gu.endTime','>',$time];
	$_field = array_merge(['gl.goodsName','g.goodsImg','g.marketPrice','gu.grouponId','gu.grouponPrice'],$extra);
	if($field!='')$_field=$field;
	$rs = $gm->alias('gu')->join('__GOODS__ g','gu.goodsId=g.goodsId','inner')
              ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
	          ->where('g.dataFlag=1  and g.goodsStatus=1 and gu.dataFlag=1 and gu.grouponStatus=1')
	          ->where($where)
	          ->field($_field)
	          ->order('gu.updateTime desc,gu.startTime asc,grouponId desc')
	          ->limit($num)
	          ->select();
	return $rs;
}

/**
 * 店铺自定义页面取出正在进行中的秒杀商品
 */
function custom_page_seckill_list($shopId=0,$num=6){
    $sg = new \addons\seckill\model\SeckillGoods;
    $sti = new \addons\seckill\model\SeckillTimeIntervals;
    $currTime = date("H:i:s");
    $where[] = ["dataFlag",'=',1];
    $where[] = ['startTime','<',$currTime];
    $where[] = ['endTime','>',$currTime];
    $timeId = $sti
        ->where($where)
        ->value('id');
    if(empty($timeId))return [];
    $today = date("Y-m-d");
    $where = [];
    if($shopId>0)$where[] = ["sg.shopId",'=',$shopId];
    $where[] = ["sg.timeId",'=',$timeId];
    $where[] = ["sg.dataFlag",'=',1];
    $where[] = ['sg.secPrice','>',0];
    $where[] = ['g.goodsStatus','=',1];
    $where[] = ['g.dataFlag','=',1];
    $where[] = ['k.seckillStatus','=',1];
    $where[] = ['k.dataFlag','=',1];
    $where[] = ['k.startDate','<=',$today];
    $where[] = ['k.endDate','>=',$today];
    $rs = $sg->alias('sg')
        ->join("goods g","sg.goodsId=g.goodsId","inner")
        ->join('__GOODS_LANGS__ gl','gl.goodsId=g.goodsId and gl.langId='.WSTCurrLang())
        ->join("seckills k","k.id=sg.seckillId","inner")
        ->join("shops s","g.shopId = s.shopId")
        ->field("g.goodsId,gl.goodsName,g.shopPrice,g.goodsImg,sg.id,sg.seckillId,sg.timeId,sg.secPrice,sg.secNum,sg.secLimit,sg.createTime,sg.hasBuyNum")
        ->where($where)
        ->order("sg.saleNum desc,sg.id")
        ->limit($num)
        ->select()
        ->toArray();
    return $rs;
}

/**
 * 店铺自定义页面取出正在进行中的秒杀信息
 */
function custom_page_seckill_info(){
    $sti = new \addons\seckill\model\SeckillTimeIntervals;
    $rs = [];
    if(WSTConf('WST_ADDONS.seckill')!='') {
        $currTime = date("H:i:s");
        $where = [];
        $where[] = ["dataFlag",'=',1];
        $where[] = ['startTime','<',$currTime];
        $where[] = ['endTime','>',$currTime];
        $secRes = $sti
            ->where($where)
            ->field('title,startTime,endTime')
            ->find();
        $rs['secTitle'] = $secRes['title']?$secRes['title']:'';
        $rs['secStartTime'] = date("Y-m-d").' '.$secRes['startTime'];
        $rs['secEndTime'] = date("Y-m-d").' '.$secRes['endTime'];
    }else{
        $rs['secTitle'] = '';
        $rs['secStartTime'] = '';
        $rs['secEndTime'] = '';
    }
    return $rs;
}

