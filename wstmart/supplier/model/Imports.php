<?php
namespace wstmart\supplier\model;
use think\Db;
use think\Loader;
use Env;
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
 * 导入类
 */
class Imports{
    /**
     * 上传商品数据
     */
    public function importGoods($data){
        require Env::get('root_path') . 'extend/phpexcel/PHPExcel/IOFactory.php';
        $objReader = \PHPExcel_IOFactory::load(WSTRootPath().json_decode($data)->route.json_decode($data)->name);
        $objReader->setActiveSheetIndex(0);
        $sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $cells = $sheet->getHighestColumn();
        //数据集合
        $readData = [];
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $importNum = 0;
        $goodsCatMap = []; //记录最后一级商品分类
        $goodsCatPathMap = [];//记录商品分类路径
        $supplierCatMap = [];//记录供货商分类
        $goodsCat1Map = [];//记录最后一级商品分类对应的一级分类
        $tmpGoodsCatId = 0;
        $goodsCatBrandMap = [];//商品分类和品牌的对应关系

        // 规格商品导入错误集合 [['msg'=>'错误提示']];
        $specGoodsErrMsgArr = [];

        //生成订单
        Db::startTrans();
        try{
            //获取商家商城分类
            $applyCatIds = model('suppliers')->getSupplierApplyGoodsCatsById($supplierId);
            //循环读取每个单元格的数据
            for ($row = 3; $row <= $rows; $row++){//行数是以第3行开始
                $goodsLang = [];
                $tmpGoodsCatId = 0;
                $goods = [];
                $goods['supplierId'] = $supplierId;
                $goodsName = trim($sheet->getCell("A".$row)->getValue());
                if($goodsName=='')break;//如果某一行第一列为空则停止导入
                foreach (WSTSysLangs() as $lkey => $lv) {
                    $goodsLang[$lv['id']]['goodsName'] = $goodsName;
                }
                $importGoodsSn = trim($sheet->getCell("B".$row)->getValue());
                $result = $this->checkUniqueNo('goodsSn',$importGoodsSn);
                if($result>0){

                    $specGoodsErrMsgArr[] = ['msg'=>lang("import_goods_fail_1", [ $goodsName, $importGoodsSn  ])];
                    continue;
                }
                $goods['goodsSn'] = $importGoodsSn;
                $importProductNo = trim($sheet->getCell("C".$row)->getValue());
                $result = $this->checkUniqueNo('productNo',$importProductNo);
                if($result>0){

                    $specGoodsErrMsgArr[] = ['msg'=>lang("import_goods_fail_2", [ $goodsName, $importProductNo  ])];
                    continue;
                }
                $goods['productNo'] = $importProductNo;
                $goods['marketPrice'] = trim($sheet->getCell("D".$row)->getValue());
                if(floatval($goods['marketPrice'])<0.01)$goods['marketPrice'] = 0.01;
                $goods['supplierPrice'] = trim($sheet->getCell("E".$row)->getValue());
                if(floatval($goods['supplierPrice'])<0.01)$goods['supplierPrice'] = 0.01;
                $goods['costPrice'] = trim($sheet->getCell("F".$row)->getValue());
                if(floatval($goods['costPrice'])<0.01)$goods['costPrice'] = 0;
                $goods['goodsStock'] = trim($sheet->getCell("G".$row)->getValue());
                if(intval($goods['goodsStock'])<=0)$goods['goodsStock'] = 0;
                $goods['warnStock'] = trim($sheet->getCell("H".$row)->getValue());
                if(intval($goods['warnStock'])<=0)$goods['warnStock'] = 0;
                $goods['goodsImg'] = '';
                $goods['supplierCatId1'] = 0;
                $goods['supplierCatId2'] = 0;
                $goodsUnit = trim($sheet->getCell("I".$row)->getValue());
                $unit = WSTDatas('GOODS_UNIT',$goodsUnit);
                $goods['goodsUnit'] = ($unit && isset($unit['dataVal']))?$unit['dataVal']:'';
                $goods['goodsWeight'] = trim($sheet->getCell("J".$row)->getValue());
                $goods['goodsVolume'] = trim($sheet->getCell("K".$row)->getValue());
                $goods['goodsSeoKeywords'] = trim($sheet->getCell("L".$row)->getValue());
                foreach (WSTSysLangs() as $lkey => $lv) {
                    $goodsLang[$lv['id']]['goodsSeoKeywords'] = $goods['goodsSeoKeywords'];
                }
                $goods['goodsTips'] = trim($sheet->getCell("M".$row)->getValue());
                foreach (WSTSysLangs() as $lkey => $lv) {
                    $goodsLang[$lv['id']]['goodsTips'] = $goods['goodsTips'];
                }

                $goods['isFreeShipping'] = (trim($sheet->getCell("N".$row)->getValue())=='是')?1:0;
                $shippingFeeTypeText = trim($sheet->getCell("O".$row)->getValue());
                $shippingFeeType = 1;
                if($shippingFeeTypeText=='重量'){
                    $shippingFeeType = 2;
                }else if($shippingFeeTypeText=='体积'){
                    $shippingFeeType = 3;
                }
                $goods['shippingFeeType'] = $shippingFeeType;

                $supplierExpressText = trim($sheet->getCell("P".$row)->getValue());
                if($goods['isFreeShipping']==1){
                    $goods['supplierExpressId'] = 0;
                }else{
                    $where = [];
                    $where[] = ["supplierId","=",$supplierId];
                    $where[] = ["isEnable","=",1];
                    $where[] = ["se.dataFlag","=",1];
                    $where[] = ["e.dataFlag","=",1];
                    $where[] = ["el.expressName","=",$supplierExpressText];
                    $supplierExpress = Db::name("supplier_express se")
                        ->join("express e","se.expressId=e.expressId","inner")
                        ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                        ->field("se.id,e.expressId,el.expressName")
                        ->where($where)
                        ->find();
                    if(!empty($supplierExpress)){
                        $supplierExpressId = $supplierExpress['id'];
                        $goods['supplierExpressId'] = $supplierExpressId;
                    }else{
                        $specGoodsErrMsgArr[] = ['msg'=>lang('failed_to_import_express_not_enabled',[$goodsName,$supplierExpressText])];
                    }
                }

                $goods['isRecom'] = (trim($sheet->getCell("Q".$row)->getValue())!='')?1:0;
                $goods['isBest'] = (trim($sheet->getCell("R".$row)->getValue())!='')?1:0;
                $goods['isNew'] = (trim($sheet->getCell("S".$row)->getValue())!='')?1:0;
                $goods['isHot'] = (trim($sheet->getCell("T".$row)->getValue())!='')?1:0;
                $goods['goodsCatId'] = 0;
                //查询商城分类
                $goodsCat = trim($sheet->getCell("U".$row)->getValue());
                if(!empty($goodsCat)){
                    //先判断集合是否存在，不存在的时候才查数据库
                    if(isset($goodsCatMap[$goodsCat])){
                        $goods['goodsCatId'] = $goodsCatMap[$goodsCat];
                        $goods['goodsCatIdPath'] = $goodsCatPathMap[$goodsCat];
                        $catIds = explode('_',$goods['goodsCatIdPath']);
                        $tmpGoodsCatId = $catIds[0];
                    }else{
                        $goodsCatId = Db::name('goods_cats')->alias('gc')->join('__GOODS_CATS_LANGS__ gcl','gcl.catId=gc.catId and gcl.langId='.WSTCurrLang())->where([['gcl.catName','=',$goodsCat], ['gc.dataFlag','=', 1]])->field('gc.catId,gcl.catName')->find();
                        if(!empty($goodsCatId['catId']) && in_array($goodsCatId['catId'],$applyCatIds)){
                            $goodsCats = model('GoodsCats')->getParentIs($goodsCatId['catId']);
                            $goods['goodsCatId'] = $goodsCatId['catId'];
                            $goods['goodsCatIdPath'] = implode('_',$goodsCats)."_";
                            //放入集合
                            $goodsCatMap[$goodsCat] = $goodsCatId['catId'];
                            $goodsCatPathMap[$goodsCat] = implode('_',$goodsCats)."_";
                            $goodsCat1Map[$goodsCat] = (count($goodsCats)==3)?$goodsCats[2]:0;
                            $tmpGoodsCatId = $goodsCat1Map[$goodsCat];
                        }
                    }
                }
                // 判断商城分类是否为最后一级分类
                $lastCatCount = Db::name('goods_cats')->where(['parentId'=>$goods['goodsCatId']])->count();
                if($lastCatCount!=0){

                    $specGoodsErrMsgArr[] = ['msg'=>lang("import_goods_fail_3", [ $goods['goodsName'] ])];
                    continue;
                }
                //查询供货商分类
                $supplierGoodsCat = trim($sheet->getCell("V".$row)->getValue());
                if(!empty($supplierGoodsCat)){
                    //先判断集合是否存在，不存在的时候才查数据库
                    if(isset($supplierCatMap[$supplierGoodsCat])){
                        $goods['supplierCatId1'] = $supplierCatMap[$supplierGoodsCat]['s1'];
                        $goods['supplierCatId2'] = $supplierCatMap[$supplierGoodsCat]['s2'];
                    }else{
                        $supplierCat= Db::name("supplier_cats")->alias('sc1')
                            ->join('__SUPPLIER_CATS__ sc2','sc2.parentId=sc1.catId','left')
                            ->join('__SUPPLIER_CATS_LANGS__ scl','scl.catId=sc2.catId and scl.langId='.WSTCurrLang())
                            ->field('sc1.catId catId1,sc2.catId catId2,scl.catName')
                            ->where(['sc1.supplierId'=> $supplierId,'sc1.dataFlag'=>1,'scl.catName'=>$supplierGoodsCat])
                            ->find();
                        if(!empty($supplierCat)){
                            $goods['supplierCatId1'] = $supplierCat['catId1'];
                            $goods['supplierCatId2'] = $supplierCat['catId2'];
                            //放入集合
                            $supplierCatMap[$supplierGoodsCat] = [];
                            $supplierCatMap[$supplierGoodsCat]['s1'] = $goods['supplierCatId1'];
                            $supplierCatMap[$supplierGoodsCat]['s2'] = $goods['supplierCatId2'];
                        }
                    }
                }
                //查询品牌
                $brand = trim($sheet->getCell("W".$row)->getValue());
                if(!empty($brand)){

                    if(isset($goodsCatBrandMap[$brand])){
                        $goods['brandId'] = $goodsCatBrandMap[$brand];
                    }else{
                        $brands = Db::name('brands')->alias('a')->join('__CAT_BRANDS__ cb','a.brandId=cb.brandId','inner')
                            ->where(['catId'=>$tmpGoodsCatId,'brandName'=>$brand,'dataFlag'=>1])->field('a.brandId')->find();
                        if(!empty($brands)){
                            $goods['brandId'] = $brands['brandId'];
                            $goodsCatBrandMap[$brand] = $brands['brandId'];
                        }
                    }
                }
                $goods['goodsDesc'] = trim($sheet->getCell("X".$row)->getValue());
                foreach (WSTSysLangs() as $lkey => $lv) {
                    $goodsLang[$lv['id']]['goodsDesc'] = $goods['goodsDesc'];
                }
                $goods['isSale'] = 0;
                $goods['goodsStatus'] = (WSTConf("CONF.isGoodsVerify")==1)?0:1;
                $goods['dataFlag'] = 1;
                $goods['saleTime'] = date('Y-m-d H:i:s');
                $goods['createTime'] = date('Y-m-d H:i:s');
                WSTUnset($goods,'goodsTips,goodsSeoKeywords,goodsDesc');
                // 商品数据写入
                $goodsId = Db::name('supplier_goods')->insertGetId($goods);
                if($goodsId===false)continue;
                foreach (WSTSysLangs() as $lkey => $lv) {
                    $goodsLang[$lv['id']]['goodsId'] = $goodsId;
                    $goodsLang[$lv['id']]['langId'] = $lv['id'];
                }
                Db::name('supplier_goods_langs')->insertAll($goodsLang);
                // 商品评分表
                $gs = [];
                $gs['goodsId'] = $goodsId;
                $gs['supplierId'] = $supplierId;
                Db::name('supplier_goods_scores')->insert($gs);
                /**
                 * 导入商品规格
                 * 1.判断规格值是否存在
                 * 2.判断规格值与商品分类是否对应
                 * 3.判断组合的总数是否正确
                 */
                $sheetName = trim($sheet->getCell("Y".$row)->getValue());
                if($sheetName!=''){// 存在规格页的key
                    $currSheet = $objReader->getSheetByName($sheetName);
                    if($currSheet!=null){// 规格页存在
                        // 固定的标题、其余为规格值
                        $titles = [
                            '货号' => 'productNo',
                            '市场价' => 'marketPrice',
                            '店铺价' => 'specPrice',
                            '成本价'=>'costPrice',
                            '库存'=>'specStock',
                            '库存预警'=>'warnStock',
                            '重量(kg)'=>'specWeight',
                            '体积(m3)'=>'specVolume'
                        ];
                        $titKeys = array_keys($titles);
                        // 总行数
                        $_rows = $currSheet->getHighestRow();
                        // 总列数
                        $_cells = count($currSheet->toArray()[0]);
                        // 第一行为标题及规格名称

                        // 遍历第一行，筛选出规格名称

                        // 记录货号-预警库存
                        $titKeyArr = [];
                        // 记录规格对应的字母
                        $specKeyArr = [];
                        for ($_cell = 2; $_cell <= $_cells; $_cell++){// 第一列为推荐，从第二列开始遍历
                            $letter = $this->intToChr($_cell-1);
                            $cellVal = $currSheet->getCell($letter.'1')->getValue();
                            if(in_array($cellVal, $titKeys)){
                                // 为固定值
                                $titKeyArr[$titles[$cellVal]] = $letter; // ['productNo'=>'E']
                            }else{
                                // 为规格值
                                $specKeyArr[$cellVal] = $letter; // ['颜色'=>'B']
                            }
                        }

                        /**
                         * 2.判断规格值是否存在、是否与商品分类对应
                         */
                        $goodsCatPath = explode('_',$goods['goodsCatIdPath'])[0];
                        // 存在的规格总数，若存在总数与找到的规格总数不同，则终止规格操作
                        $findCount = count($specKeyArr);
                        $existsCount = 0;

                        // 不存在的规格名称，用于错误提示
                        $emptyCatName = [];
                        foreach($specKeyArr as $catName=>$v){
                            $catNameData = Db::name('spec_cats')
                                ->alias('gc')
                                ->join('__SPEC_CATS_LANGS__ scl','gc.catId=scl.catId and scl.langId='.WSTCurrLang())
                                ->where(['scl.catName'=>$catName])
                                ->where("goodsCatPath like '{$goodsCatPath}_%'")
                                ->field('gc.catId')
                                ->find();
                            if(!empty($catNameData)){
                                // 保存规格的catId
                                $specKeyArr[$catName] = ['catId'=>$catNameData['catId'], 'letter'=>$v]; // ['颜色'=>['catId'=>1,'letter'=>'B']]
                                ++$existsCount;
                            }else{
                                $emptyCatName[] = $catName;
                            }
                        }
                        // 找到的总数 与 存在的总数相同
                        if($findCount===$existsCount){
                            /**
                             *  3.判断组合的总数是否正确
                             */
                            // 取出每一列规格对应的值
                            for ($_row = 2; $_row <= $_rows; $_row++){// 从第二列开始取值
                                foreach($specKeyArr as $k => $v){
                                    $letter = $v['letter'];
                                    if( !isset($specKeyArr[$k]['specValArr']) ) $specKeyArr[$k]['specValArr'] = [];
                                    $val = $currSheet->getCell($letter.$_row)->getValue();
                                    $specKeyArr[$k]['specValArr'][] = $val;
                                }
                            }
                            // 计算组合总条数
                            $specItemTotal = 1;
                            foreach($specKeyArr as $k => $v){
                                $specKeyArr[$k]['specValArr'] = array_unique($v['specValArr']); // ['颜色'=>['catId'=>1,'letter'=>'B','specValArr'=>['红色','蓝色','白色']]]

                                $specItemTotal = count($specKeyArr[$k]['specValArr']) * $specItemTotal;
                            }
                            // 总组合数 = 总行数-第一行(title)
                            $sitemLangs = [];
                            if($specItemTotal===($_rows-1)){
                                // 执行数据写入
                                $specMap = [];
                                foreach ($specKeyArr as $k=>$v){
                                    foreach($v['specValArr'] as $itemName){
                                        $sitem = [];
                                        $sitem['supplierId'] = $supplierId;
                                        $sitem['catId'] = $v['catId'];
                                        $sitem['goodsId'] = $goodsId;
                                        $sitem['itemImg'] = '';
                                        $sitem['dataFlag'] = 1;
                                        $sitem['createTime'] = date('Y-m-d H:i:s');
                                        $itemId = Db::name('supplier_spec_items')->insertGetId($sitem);
                                        //if($sitem['itemImg']!='')WSTUseResource(0, $itemId, $sitem['itemImg']);
                                        $specMap[$itemName] = $itemId; // ['红色'=>1,'蓝色'=>2,'白色'=>3]
                                        foreach (WSTSysLangs() as $lkey => $lv) {
                                            $sitemLang = [];
                                            $sitemLang['itemId'] = $itemId;
                                            $sitemLang['langId'] = $lv['id'];
                                            $sitemLang['goodsId'] = $goodsId;
                                            $sitemLang['itemName'] = $itemName;
                                            $sitemLangs[] = $sitemLang;
                                        }
                                    }
                                }
                                //保存规格多语言文字
                                if(count($sitemLangs)>0)Db::name('supplier_spec_items_langs')->insertAll($sitemLangs);

                                $defaultPrice = 0;//最低价
                                $totalStock = 0;//总库存
                                $gspecArray = [];
                                $isFindDefaultSpec = false;

                                // 取出每一列规格对应的值
                                for ($_row = 2; $_row <= $_rows; $_row++){// 从第二行开始取值

                                    // 写入固定的值
                                    // '货号'=>'productNo','市场价'=>'marketPrice', '供货商价'=>'specPrice', '库存'=>'specStock', '库存预警'=>'warnStock'

                                    $goodsSpecIds = [];
                                    foreach($specKeyArr as $k1=>$v1){// ['颜色'=>['catId'=>1,'letter'=>'B','specValArr'=>['红色','蓝色','白色']]]
                                        $specItemVal = $currSheet->getCell($v1['letter'].$_row)->getValue();// 红色
                                        $goodsSpecIds[] = $specMap[$specItemVal]; // ['红色'=>1,'蓝色'=>2,'白色'=>3]
                                    }
                                    $gspec = [];
                                    $gspec['specIds'] = implode(':',$goodsSpecIds);
                                    $gspec['supplierId'] = $supplierId;
                                    $gspec['goodsId'] = $goodsId;
                                    $gspec['productNo'] =  $currSheet->getCell($titKeyArr['productNo'].$_row)->getValue();
                                    $gspec['marketPrice'] = (float)$currSheet->getCell($titKeyArr['marketPrice'].$_row)->getValue();
                                    $gspec['specPrice'] = (float)$currSheet->getCell($titKeyArr['specPrice'].$_row)->getValue();
                                    $gspec['costPrice'] = (float)$currSheet->getCell($titKeyArr['costPrice'].$_row)->getValue();
                                    $gspec['specStock'] = (int)$currSheet->getCell($titKeyArr['specStock'].$_row)->getValue();
                                    $gspec['warnStock'] = (int)$currSheet->getCell($titKeyArr['warnStock'].$_row)->getValue();
                                    $gspec['specWeight'] = (float)$currSheet->getCell($titKeyArr['specWeight'].$_row)->getValue();
                                    $gspec['specVolume'] = (float)$currSheet->getCell($titKeyArr['specVolume'].$_row)->getValue();

                                    //设置默认规格  lang("yes")==="是"
                                    if($currSheet->getCell('A'.$_row)->getValue()==lang("yes")){
                                        $isFindDefaultSpec = true;
                                        $defaultPrice = $gspec['specPrice'];
                                        $gspec['isDefault'] = 1;
                                    }else{
                                        $gspec['isDefault'] = 0;
                                    }

                                    // 如果未找到默认规格值，则取最后一行作为默认规格
                                    if( !$isFindDefaultSpec  && $_row==$_rows ){
                                        $defaultPrice = $gspec['specPrice'];
                                        $gspec['isDefault'] = 1;
                                    }
                                    $gspecArray[] = $gspec;
                                    //获取总库存
                                    $totalStock = $totalStock + $gspec['specStock'];
                                }
                                if(count($gspecArray)>0){
                                    Db::name('supplier_goods_specs')->insertAll($gspecArray);
                                    //更新默认价格和总库存
                                    Db::name('supplier_goods')->where('goodsId',$goodsId)->update(['isSpec'=>1,'supplierPrice'=>$defaultPrice,'goodsStock'=>$totalStock]);
                                }
                            }else{
                                /**
                                 * 错误信息：组合总数不匹配
                                 */

                                $specGoodsErrMsgArr[] = ['msg'=>lang("import_spec_goods_fail_1", [ $goodsName, $specItemTotal, ($_rows-1) ])];
                            }
                        }else{
                            /**
                             * 错误信息：部分规格值不存在
                             */

                            $specGoodsErrMsgArr[] = ['msg'=>lang("import_spec_goods_fail_2", [ $goodsName, $emptyCatName ])];
                        }
                    }
                }
                $importNum++;
            }
            Db::commit();
            return json_encode(['status'=>1,'importNum'=>$importNum,'specErrMsg'=>$specGoodsErrMsgArr]);
        }catch (\Exception $e) {
            Db::rollback();
            return json_encode(WSTReturn(lang("import_fail"),-1));
        }
    }

    /**
     * 数字转字母 （类似于Excel列标）
     * @param Int $index 索引值
     * @param Int $start 字母起始值
     * @return String 返回字母
     */
    private function intToChr($index, $start = 65) {
        $str = '';
        if (floor($index / 26) > 0) {
            $str .= intToChr(floor($index / 26)-1);
        }
        return $str . chr($index % 26 + $start);
    }

    /*
     * 检查导入商品的商品编号和商品货号是否唯一
     */
    public function checkUniqueNo($filed,$value){
        $uniqueNo = WSTGoodsNo();
        $condition = [];
        if($value == '') $value = $uniqueNo;
        $condition[$filed] = $value;
        $condition['dataFlag'] = 1;
        return Db::name('supplier_goods')->where($condition)->count();
    }

    /**
     * 上传订单数据
     */
    public function importOrders($data){
        require Env::get('root_path') . 'extend/phpexcel/PHPExcel/IOFactory.php';
        $objReader = \PHPExcel_IOFactory::load(WSTRootPath().json_decode($data)->route.json_decode($data)->name);
        $objReader->setActiveSheetIndex(0);
        $sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $cells = $sheet->getHighestColumn();
        $specGoodsErrMsgArr = [];

        $importNum = 0;
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        //获已发货的订单商品ID
        $deliverGoodsIds = Db::name('supplier_orders')->alias('o')
                           ->join('__SUPPLIER_ORDER_EXPRESS__ oep','o.orderId=oep.orderId and oep.dataFlag=1','inner')
                           ->where(['o.dataFlag'=>1,'o.orderStatus'=>0,'o.supplierId'=>$supplierId])
                           ->column('oep.orderGoodsId');
        //获取待发货的商品订单信息
        $db = Db::name('supplier_orders')->alias('o')
                           ->join('__SUPPLIER_ORDER_GOODS__ og','o.orderId=og.orderId','inner')
                           ->where(['o.dataFlag'=>1,'o.orderStatus'=>0,'o.supplierId'=>$supplierId]);
        if(count($deliverGoodsIds)>0)$db->where([['og.id','not in',$deliverGoodsIds]]);
        $orderGoods = $db->field('o.orderNo,o.orderId,og.id,og.goodsName,o.userId')
                           ->order('o.orderId asc')
                           ->select();
        if(count($orderGoods)==0)return json_encode(WSTReturn(lang("there_are_no_orders_to_ship"),-1));
        $waitDeliverGoods = [];
        foreach ($orderGoods as $key => $v) {
            $waitDeliverGoods[$v['orderNo']][$v['id']] = $v;
        }
        //获取快递公司
        $express = Db::name('express')
                    ->alias('e')
                    ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                    ->where(['dataFlag' => 1, 'isShow' => 1])->column('e.expressId', 'el.expressName');
        $saveDatas = ['filter'=>[],'orders'=>[]];
        /**
         * 结构:目的是为了同一个订单，不同快递号也发一次商城消息
         * [
         *   'fliter'=>['2020012300_1','2020012300_2']---订单号_订单商品编号(order_goods表的自增ID)
         *   'orders'=>[---订单列表
         *        '2020012300'=>[---订单号
                      '23_2020021000234221'=>[---快递公司ID_快递单号
                         [.....], [.....] ---发货记录
                      ]
                   ]
         *   ]
         * ]
         */
        for ($row = 2; $row <= $rows; $row++){
            $order = [];
            $orderNo = trim($sheet->getCell("A".$row)->getValue());
            if($orderNo=='')continue;
            $order['orderGoodsId'] = trim($sheet->getCell("B".$row)->getValue());
            $expressName = trim($sheet->getCell("D".$row)->getValue());
            $order['expressNo'] = trim($sheet->getCell("E".$row)->getValue());
            //根据订单号和商品订单ID查询是否存在待发货的商品
            $errMsg = lang("import_orders_fail_prefix", [ $orderNo, $order['orderGoodsId'] ]);
            if(!isset($waitDeliverGoods[$orderNo]) || !isset($waitDeliverGoods[$orderNo][$order['orderGoodsId']])){
                $specGoodsErrMsgArr[] = ['msg'=>$errMsg.lang("import_orders_fail_1")];
                continue;
            }
            if($expressName!='' && !isset($express[$expressName])){
                $specGoodsErrMsgArr[] = ['msg'=>$errMsg.lang("import_orders_fail_2")];
                continue;
            }
            //快递信息检查
            $goods = $waitDeliverGoods[$orderNo][$order['orderGoodsId']];
            if(($expressName!='' && $order['expressNo']=='') || ($expressName=='' && $order['expressNo']!='')){
                $specGoodsErrMsgArr[] = ['msg'=>$errMsg.lang("import_orders_fail_3")];
                continue;
            }
            $order['orderId'] = $goods['orderId'];
            $order['expressId'] = ($expressName=='' || $order['expressNo']=='' || !isset($express[$expressName]))?0:$express[$expressName];
            $order['dataFlag'] = 1;
            $order['isExpress'] = ($order['expressId']>0)?1:0;
            $order['deliveryTime'] = date('Y-m-d H:i:s');
            $order['createTime'] = date('Y-m-d H:i:s');
            $order['deliverType'] = 1;
            $filterKey = $orderNo.'_'.$goods['orderId'];
            if(in_array($filterKey,$saveDatas['filter'])){
                $specGoodsErrMsgArr[] = ['msg'=>$errMsg.lang("import_orders_fail_4")];
                continue;
            }
            //这里的三条记录是为了带给下一个循环用的
            $order['userId'] = $goods['userId'];
            $order['orderNo'] = $orderNo;
            $order['expressName'] = $expressName;
            $saveDatas['orders'][$orderNo][$order['expressId'].'_'.$order['expressNo']][] = $order;
        }
        foreach ($saveDatas['orders'] as $orderNo => $order) {
            $isDeliver = false;//用来判断本次是否有发货动作
            Db::startTrans();
            try{
                $lastOrderExpress = [];
                foreach ($order as $key => $orderExpress) {
                    foreach ($orderExpress as $key => $orderGoodsExpress) {
                        //检测记录是否已经存在了
                        $chk = Db::name('supplier_order_express')->where(['orderId'=>$orderGoodsExpress['orderId'],'orderGoodsId'=>$orderGoodsExpress['orderGoodsId']])->count();
                        if($chk>0){
                            $specGoodsErrMsgArr[] = ['msg'=>lang("import_orders_fail_5", [ $orderNo, $orderGoodsExpress['orderGoodsId'] ])];
                            continue;
                        }
                        //把数据转移走，然后清除带过来的三条记录，保证能新增成功
                        $lastOrderExpress = $orderGoodsExpress;
                        WSTUnset($orderGoodsExpress,'userId,orderNo,expressName');
                        Db::name('supplier_order_express')->insert($orderGoodsExpress);
                        $isDeliver = true;
                        $importNum++;
                    }
                    //有发货动作发送发货通知
                    if($isDeliver){
                        //新增订单日志
                        $logOrder = [];
                        $logOrder['orderId'] = $lastOrderExpress['orderId'];
                        $logOrder['orderStatus'] = 1;
                        $logOrder['logJson'] = json_encode(['type'=>'lang','key'=>"the_merchant_has_delivered_the_goods"]);
                        $logOrder['logUserId'] = $lastOrderExpress['userId'];
                        $logOrder['logType'] = 0;
                        $logOrder['logTime'] = date('Y-m-d H:i:s');
                        Db::name('supplier_log_orders')->insert($logOrder);
                        //查询一下订单是否发送完了
                        $orderGoodsNum = Db::name('supplier_order_goods')->where(['orderId'=>$lastOrderExpress['orderId']])->count();
                        $orderDeliverGoodsNum = Db::name('supplier_order_express')->where(['orderId'=>$lastOrderExpress['orderId'],'dataFlag'=>1])->count();
                        if($orderDeliverGoodsNum>0 && ($orderGoodsNum==$orderDeliverGoodsNum)){
                             Db::name('supplier_orders')->where(['orderStatus'=>0,'dataFlag'=>1,'supplierId'=>$supplierId,'orderId'=>$lastOrderExpress['orderId']])->update(['orderStatus'=>1,'deliveryTime'=>date('Y-m-d H:i:s')]);
                        }
                        //发送一条用户信息
                        $tpl = WSTMsgTemplates('ORDER_DELIVERY');
                        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
                            $find = ['${ORDER_NO}','${EXPRESS_NO}'];
                            $replace = [$lastOrderExpress['orderNo'],($lastOrderExpress['expressNo']=='')?lang("none"):$lastOrderExpress['expressNo']];
                            WSTSendMsg($lastOrderExpress['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$lastOrderExpress['orderId']]);
                        }
                        //微信消息
                        if(WSTDatas('ADS_TYPE',3)!='' || WSTDatas('ADS_TYPE',4)!=''){
                            $params = [];
                            if($lastOrderExpress['expressId']>0){
                                $params['EXPRESS'] = $lastOrderExpress['expressName'];
                                $params['EXPRESS_NO'] = $lastOrderExpress['expressNo'];
                            }else{
                                $params['EXPRESS'] = lang("none");
                                $params['EXPRESS_NO'] = lang("none");
                            }
                            $params['ORDER_NO'] = $lastOrderExpress['orderNo'];
                            if(WSTConf('CONF.wxenabled')==1){
                                WSTWxMessage(['CODE'=>'WX_ORDER_DELIVERY','userId'=>$lastOrderExpress['userId'],'params'=>$params]);
                            }
                        }
                    }
                }
                Db::commit();
            }catch (\Exception $e) {
                Db::rollback();
            }
        }
        return json_encode(['status'=>1,'importNum'=>$importNum,'specErrMsg'=>$specGoodsErrMsgArr]);
    }
}
