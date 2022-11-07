<?php
namespace wstmart\store\model;
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
        $shopId = (int)session('WST_STORE.shopId');
        $storeId = (int)session('WST_STORE.storeId');
        //获已发货的订单商品ID
        $deliverGoodsIds = Db::name('orders')->alias('o')
                           ->join('__ORDER_EXPRESS__ oep','o.orderId=oep.orderId and oep.dataFlag=1','inner')
                           ->where(['o.dataFlag'=>1,'o.orderStatus'=>0,'o.shopId'=>$shopId,'storeId'=>$storeId])
                           ->column('oep.orderGoodsId');
        //获取待发货的商品订单信息
        $db = Db::name('orders')->alias('o')
                           ->join('__ORDER_GOODS__ og','o.orderId=og.orderId','inner')
                           ->where(['o.dataFlag'=>1,'o.orderStatus'=>0,'o.shopId'=>$shopId,'storeId'=>$storeId]);
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
            $importFail = lang("import_fail", [ $orderNo, $order['orderGoodsId']]);
            if(!isset($waitDeliverGoods[$orderNo]) || !isset($waitDeliverGoods[$orderNo][$order['orderGoodsId']])){

                $specGoodsErrMsgArr[] = ['msg'=>$importFail . lang("import_fail_dismatch")];
                continue;
            }
            if($expressName!='' && !isset($express[$expressName])){
                $specGoodsErrMsgArr[] = ['msg'=>$importFail . lang("import_fail_express_not_exists")];
                continue;
            }
            //快递信息检查
            $goods = $waitDeliverGoods[$orderNo][$order['orderGoodsId']];
            if(($expressName!='' && $order['expressNo']=='') || ($expressName=='' && $order['expressNo']!='')){
                $specGoodsErrMsgArr[] = ['msg'=>$importFail . lang("import_fail_info_incomplete")];
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
                $specGoodsErrMsgArr[] = ['msg'=>$importFail . lang("import_fail_duplicate")];
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
                        $chk = Db::name('order_express')->where(['orderId'=>$orderGoodsExpress['orderId'],'orderGoodsId'=>$orderGoodsExpress['orderGoodsId']])->count();
                        if($chk>0){
                            $failMsg = lang("import_fail", [ $orderNo, $orderGoodsExpress['orderGoodsId']]) . lang("import_fail_have_been_delivered");
                            $specGoodsErrMsgArr[] = ['msg'=>$failMsg];
                            continue;
                        }
                        //把数据转移走，然后清除带过来的三条记录，保证能新增成功
                        $lastOrderExpress = $orderGoodsExpress;
                        WSTUnset($orderGoodsExpress,'userId,orderNo,expressName');
                        Db::name('order_express')->insert($orderGoodsExpress);
                        $isDeliver = true;
                        $importNum++;
                    }
                    //有发货动作发送发货通知
                    if($isDeliver){
                        //新增订单日志
                        $logOrder = [];
                        $logOrder['orderId'] = $lastOrderExpress['orderId'];
                        $logOrder['orderStatus'] = 1;
                        $logOrder['logJson'] = json_encode(['type'=>'lang','key'=>'the_merchant_has_delivered_the_goods']);
                        $logOrder['logUserId'] = $lastOrderExpress['userId'];
                        $logOrder['logType'] = 0;
                        $logOrder['logTime'] = date('Y-m-d H:i:s');
                        Db::name('log_orders')->insert($logOrder);
                        //查询一下订单是否发送完了
                        $orderGoodsNum = Db::name('order_goods')->where(['orderId'=>$lastOrderExpress['orderId']])->count();
                        $orderDeliverGoodsNum = Db::name('order_express')->where(['orderId'=>$lastOrderExpress['orderId'],'dataFlag'=>1])->count();
                        if($orderDeliverGoodsNum>0 && ($orderGoodsNum==$orderDeliverGoodsNum)){
                             Db::name('orders')->where(['orderStatus'=>0,'dataFlag'=>1,'shopId'=>$shopId,'storeId'=>$storeId,'orderId'=>$lastOrderExpress['orderId']])->update(['orderStatus'=>1,'deliveryTime'=>date('Y-m-d H:i:s')]);
                        }
                        //发送一条用户信息
                        $tpl = WSTMsgTemplates('ORDER_DELIVERY');
                        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
                            $find = ['${ORDER_NO}','${EXPRESS_NO}'];
                            $replace = [$lastOrderExpress['orderNo'],($lastOrderExpress['expressNo']=='')?lang("nothing"):$lastOrderExpress['expressNo']];
                            WSTSendMsg($lastOrderExpress['userId'],str_replace($find,$replace,$tpl['tplContent']),['from'=>1,'dataId'=>$lastOrderExpress['orderId']]);
                        }
                        //微信消息
                        if(WSTDatas('ADS_TYPE',3)!='' || WSTDatas('ADS_TYPE',4)!=''){
                            $params = [];
                            if($lastOrderExpress['expressId']>0){
                                $params['EXPRESS'] = $lastOrderExpress['expressName'];
                                $params['EXPRESS_NO'] = $lastOrderExpress['expressNo'];
                            }else{
                                $params['EXPRESS'] = lang("nothing");
                                $params['EXPRESS_NO'] = lang("nothing");
                            }
                            $params['ORDER_NO'] = $lastOrderExpress['orderNo'];
                            if(WSTConf('CONF.wxenabled')==1){
                                WSTWxMessage(['CODE'=>'WX_ORDER_DELIVERY','userId'=>$lastOrderExpress['userId'],'URL'=>Url('wechat/orders/index',['type'=>'waitReceive'],true,true),'params'=>$params]);
                            }
                        }
                    }
                }
                Db::commit();
                hook("afterShopDeliver",['orderId'=>$lastOrderExpress['orderId'],'shopId'=>$shopId,'uerSystem'=>[0,1],'printCatId'=>2]);
            }catch (\Exception $e) {
                Db::rollback();
            }
        }
        return json_encode(['status'=>1,'importNum'=>$importNum,'specErrMsg'=>$specGoodsErrMsgArr]);
    }
}
