<?php
namespace addons\custompage\model;
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
 * 首页自定义布局业务处理
 */
class Index extends Base{

    /*
     * 获取商城是否开启首页自定义页面功能
     */
    public function getCustomPagesSetting($type){
        return  Db::name('custom_pages')->where(['dataFlag'=>1,'isIndex'=>1,'pageType'=>$type])->value('id');
    }

    /*
     * 获取商城首页自定义页面的页面标题
     */
    public function getCustomPageTitle($pageId){
        $pageAttr = Db::name('custom_pages')->where(['dataFlag'=>'1','id'=>$pageId])->value('attr');
        $pageAttr = unserialize($pageAttr);
        return $pageAttr['title'];
    }

    /*
     * 获取首页自定义商品组件
     */
    public function getCustomPageDecorationGoodsGroupData($pageId){
        $data = Db::name('custom_page_decoration')->field('id,name,attr,sort')->where(['name'=>'goods_group','dataFlag'=>'1','pageId'=>$pageId])->order('sort asc')->select();
        foreach($data as $k => $v){
            $data[$k]["attr"] = unserialize($data[$k]["attr"]);
        }
        foreach($data as $k => $v){
            if($v["name"] == "goods_group") {
                $data[$k]['backgroundColor'] = $v["attr"]["background_color"];
                $data[$k]['showGoodsName'] = $v["attr"]["show_goods_name"];
                $data[$k]['showGoodsPrice'] = $v["attr"]["show_goods_price"];
                $data[$k]['showPraiseRate'] = $v["attr"]["show_praise_rate"];
                $data[$k]['showSaleNum'] = $v["attr"]["show_sale_num"];
                $data[$k]['showColumnsTitle'] = $v["attr"]["show_columns_title"];
                $data[$k]['columns'] = $v["attr"]["columns"];
                $data[$k]['columnsTitle'] = $v["attr"]["columns_title"];
                $where = [
                    'columns_title'=>$v['attr']['columns_title'],
                    'goods_select'=>$v['attr']['goods_select'],
                    'goods_nums'=>$v['attr']['goods_nums'],
                    'goods_select_ids'=>$v['attr']['goods_select_ids'],
                    'goods_select_cats_id'=>$v['attr']['goods_select_cats_id'],
                    'goods_tag'=>$v['attr']['goods_tag'],
                    'goods_min_price'=>$v['attr']['goods_min_price'],
                    'goods_max_price'=>$v['attr']['goods_max_price'],
                    'goods_order'=>$v['attr']['goods_order'],
                ];
                $data[$k]['goods'] = $this->getGoods($where);
                unset($data[$k]['attr']);
            }
        }
        return $data;
    }

    /*
      * 获取首页自定义商品组件的商品
      */
    public function getGoods($array){
        $goodsNums = $array['goods_nums'];
        $columnsTitle = $array['columns_title'];
        $goodsSelect = $array['goods_select'];
        $goodsSelectIds = $array['goods_select_ids'];
        $goodsSelectCatsId = $array['goods_select_cats_id'];
        $goodsOrder = $array['goods_order'];
        $goodsTag = $array['goods_tag'];
        $goodsMinPrice = $array['goods_min_price'];
        $goodsMaxPrice = $array['goods_max_price'];
        $order = '';
        switch ($goodsOrder){
            case 1:
                //销量从高到低
                $order = 'saleNum desc';
                break;
            case 2:
                //销量从低到高
                $order = 'saleNum asc';
                break;
            case 3:
                //价格从高到低
                $order = 'shopPrice desc';
                break;
            case 4:
                //价格从低到高
                $order = 'shopPrice asc';
                break;
            case 5:
                //更新时间由近到远
                $order = 'createTime desc';
                break;
            case 6:
                //更新时间由远到近
                $order = 'createTime asc';
                break;
            case 7:
                //商品排序由大到小
                $order = 'goodsId desc';
                break;
            case 8:
                //商品排序由小到大
                $order = 'goodsId asc';
                break;
        }
        $data = [];
        for($i=0;$i<count($columnsTitle);$i++){
            if($goodsSelect[$i]==1){
                // 条件选取
                $where = [];
                $where2 = '';
                $where3 = '';
                switch ($goodsTag[$i]) {
                    case '1':
                        // 推荐
                        $where[] = ['g.isRecom','=',1];
                        break;
                    case '2':
                        // 新品
                        $where[] = ['g.isNew','=',1];
                        break;
                    case '3':
                        // 热卖
                        $where[] = ['g.isHot','=',1];
                        break;
                }
                $minPrice = $goodsMinPrice[$i]; // 最低价格
                $maxPrice = $goodsMaxPrice[$i]; // 最高价格
                if($minPrice!="")$where2 = "g.shopPrice >= ".(float)$minPrice;
                if($maxPrice!="")$where3 = "g.shopPrice <= ".(float)$maxPrice;
                $data[$i] = Db::name('goods')->alias('g')->join('__GOODS_SCORES__ gs','gs.goodsId=g.goodsId')
                    ->where([['g.goodsCatIdPath','like',$goodsSelectCatsId[$i].'_%'],['g.isSale','=',1],['g.dataFlag','=',1],['g.goodsStatus','=',1]])
                    ->where($where)
                    ->where($where2)
                    ->where($where3)
                    ->field('g.goodsId,g.goodsName,g.goodsImg,g.shopPrice,g.saleNum,gs.totalScore,gs.totalUsers')
                    ->order($order)->limit($goodsNums)->select();
                if($data[$i]){
                    foreach ($data[$i] as $key =>$v){
                        $data[$i][$key]['praiseRate'] = ($v['totalScore']>0)?(sprintf("%.2f",$v['totalScore']/($v['totalUsers']*15))*100).'%':'100%';
                    }
                }
            }else{
                // 手动添加
                $data[$i] = Db::name('goods')->alias('g')->join('__GOODS_SCORES__ gs','gs.goodsId=g.goodsId','left')
                    ->where([['g.goodsId','in',$goodsSelectIds[$i]],['g.isSale','=',1],['g.dataFlag','=',1],['g.goodsStatus','=',1]])
                    ->field('g.goodsId,g.goodsName,g.goodsImg,g.shopPrice,g.saleNum,gs.totalScore,gs.totalUsers')
                    ->order($order)->select();
                if($data[$i]){
                    foreach ($data[$i] as $key =>$v){
                        $data[$i][$key]['praiseRate'] = ($v['totalScore']>0)?(sprintf("%.2f",$v['totalScore']/($v['totalUsers']*15))*100).'%':'100%';
                    }
                }
            }
        }
        return $data;
    }

    /*
     * 获取首页自定义多店铺组件的店铺
     */
    public function customPageShopsList(){
        $radius = (int)input('radius');
        $lng = (float)input("longitude");
        $lat = (float)input("latitude");
        $where = [];
        $where[] = ['dataFlag','=',1];
        $where[] = ['shopStatus','=',1];
        $where[] = ['applyStatus','=',2];
        $where2 = '';
        if($radius>0){
            $where2 = "round(6378.138*2*asin(sqrt(pow(sin( (".$lat."*pi()/180-s.latitude*pi()/180)/2),2)+cos(".$lat."*pi()/180)*cos(s.latitude*pi()/180)* pow(sin( (".$lng."*pi()/180-s.longitude*pi()/180)/2),2)))*1000)/1000 < ".$radius;
        }
        $rs['shops'] = Db::name('shops')
            ->alias('s')
            ->join('__SHOP_SCORES__ ss','ss.shopId = s.shopId','left')
            ->fieldRaw('s.shopId,s.shopImg,s.shopName,s.shopCompany,ss.totalScore,ss.totalUsers,ss.goodsScore,ss.goodsUsers,ss.serviceScore,ss.serviceUsers,ss.timeScore,ss.timeUsers,s.areaIdPath,s.shopAddress')
            ->fieldRaw("round(6378.138*2*asin(sqrt(pow(sin( (".$lat."*pi()/180-s.latitude*pi()/180)/2),2)+cos(".$lat."*pi()/180)*cos(s.latitude*pi()/180)* pow(sin( (".$lng."*pi()/180-s.longitude*pi()/180)/2),2)))*1000)/1000 as distince")
            ->where($where)
            ->where($where2)
            ->select();
        $shopIds = [];
        $totalScores = [];
        $goodsCatMap = [];
        foreach ($rs['shops'] as $key =>$v){
            $shopIds[] = $v['shopId'];
            $rs['shops'][$key]['totalScore'] = WSTScore($v["totalScore"]/3, $v["totalUsers"]==0?1: $v["totalUsers"]);
            $rs['shops'][$key]['goodsScore'] = WSTScore($v['goodsScore'],$v['goodsUsers']);
            $rs['shops'][$key]['serviceScore'] = WSTScore($v['serviceScore'],$v['serviceUsers']);
            $rs['shops'][$key]['timeScore'] = WSTScore($v['timeScore'],$v['timeUsers']);
        }
        $goodsCats = Db::name('cat_shops')->alias('cs')->join('__GOODS_CATS__ gc','cs.catId=gc.catId and gc.dataFlag=1','left')
            ->where([['shopId','in',$shopIds]])->field('cs.shopId,gc.catName')->select();
        foreach ($goodsCats as $v){
            $goodsCatMap[$v['shopId']][] = $v['catName'];
        }
        foreach ($rs['shops'] as $key =>$v){
            $rs['shops'][$key]['catshops'] = (isset($goodsCatMap[$v['shopId']]))?implode(',',$goodsCatMap[$v['shopId']]):'';
        }
        $location = '';
        if(WSTConf('CONF.mapKey')!=''){
            $res = WSTLatLngAddress($lat,$lng);
            $location = $res['result']['address'];
        }
        $rs['location'] = $location;
        return $rs;
    }

    /*
     * 微信端判断自定义页面是否含有多店铺组件
     */
    public function hasShopComponent($pageId){
        $data = Db::name('custom_page_decoration')->field('name')->where(['dataFlag'=>'1','pageId'=>$pageId])->select();
        foreach($data as $k => $v){
            if($v['name'] == 'shop'){
                return 1;
            }
        }
        return 0;
    }

    /*
     * 获取后台自定义的底部导航栏菜单【weapp】
     */
    public function getTabbarMenu($pageId){
        $rs = Db::name('custom_page_decoration')->field('name,attr,sort')->where(['pageId'=>$pageId,'name'=>'tabbar','dataFlag'=>'1'])->find();
        $rs['attr'] = unserialize($rs["attr"]);
        $rs['color'] = $rs["attr"]["text_color"];
        $rs['selectedColor'] = $rs["attr"]["text_checked_color"];
        $rs['backgroundColor'] = $rs["attr"]["background_color"];
        $rs['borderStyle'] = $rs["attr"]["border_color"];
        for($i=0;$i<count($rs["attr"]["icon"]);$i++){
            $tabbarData['icon'] = $rs["attr"]["icon"][$i];
            $tabbarData['selectIcon'] = $rs["attr"]["select_icon"][$i];
            $tabbarData['link'] = $rs["attr"]["link"][$i];
            $tabbarData['text'] = $rs["attr"]["text"][$i];
            $tabbarData['menuFlag'] = $rs["attr"]["menu_flag"][$i];
            $rs["tabbars"][] = $tabbarData;
        }
        unset($rs['attr']);
        return $rs;
    }

    /*
     * 获取首页自定义页面的组件id
     */
    function getCustomPageDecorationIds($pageId,$name){
        $rs = Db::name('custom_page_decoration')->where(['pageId'=>$pageId,'name'=>$name,'dataFlag'=>'1'])->order('sort asc')->column('id');
        return $rs;
    }
}
