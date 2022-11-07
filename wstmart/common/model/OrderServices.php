<?php
namespace wstmart\common\model;
use think\Db;
use wstmart\common\validate\OrderServices as Validate;
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
 * 售后业务处理类
 */
class OrderServices extends Base{
    /**
     * 定时任务
     */
    public function crontab(){
        /**
         * 用户提交申请售后之后写入（shopAcceptExpireTime）商家受理期限，逾期不处理自动关闭售后单（售后日志写明“逾期不受理”）
         * 仅退款售后单受理之后不做任何写入。
         * 仅换货、退货退款售后单受理之后写入（userSendExpireTime）用户发货期限，逾期不处理自动关闭（售后日志写明“用户逾期未处理”）
         * 用户发货之后写入（shopReceiveExpireTime）商家收货期限，逾期不处理自动收货。

         * 商家收货之后写入（shopSendExpireTime）商家发货期限，逾期不处理自动关闭（售后日志写明“商家逾期未发货”）

         * 商家发货之后写入（userReceiveExpireTime）用户收货期限，逾期不处理自动收货。
         *
         */
        // 查询未结束的售后单
        $now = date('Y-m-d H:i:s');
        // 1.处理【商家】逾期受理
        $where = [['isClose','=',0],['serviceStatus','=',0],['shopAcceptExpireTime','<',$now]];
        $rs = $this->where($where)->select();
        $logs = [];
        $logJson = json_encode(['type'=>'lang','key'=>'overdue_not_accepted_tips']);
        $sIds = [];
        foreach($rs as $k=>$v){
            $logs[] = [
                'logTime'=>$now,
                'orderId'=>$v['orderId'],
                'serviceId'=>$v['id'],
                'logTargetId'=>0,
                'logType'=>2,
                'logJson'=>$logJson
            ];
            $sIds[] = $v['id'];
        }
        unset($k, $v);
        // 关闭售后单
        $this->whereIn('id',$sIds)->setField('isClose', 1);
        // 写入日志
        Db::name('log_services')->insertAll($logs);
        // 发送商城消息给用户
        $dealResult = WSTLang('overdue_not_accepted_tips');
        foreach($sIds as $serviceId){
            $userId = $this->alias("os")
                           ->join("__ORDERS__ o", "o.orderId=os.orderId","inner")
                           ->where(["os.id"=>$serviceId])
                           ->value("o.userId");
            $this->sendMessage($userId, 0, $serviceId, $dealResult);
        }


        // 2.处理【用户】逾期发货
        $where = [['isClose','=',0],['serviceStatus','=',1],['userSendExpireTime','<',$now]];
        $rs = $this->where($where)->select();
        $logs = [];
        $logJson = json_encode(['type'=>'lang','key'=>'user_overdue_not_deliver_tips']);
        $dealResult = WSTLang('user_overdue_not_deliver_tips');
        $sIds = [];
        foreach($rs as $k=>$v){
            $logs[] = [
                'logTime'=>$now,
                'orderId'=>$v['orderId'],
                'serviceId'=>$v['id'],
                'logTargetId'=>0,
                'logType'=>2,
                'logJson'=>$logJson
            ];
            $sIds[] = $v['id'];

            // 给商家发送商城消息提醒
            $shopId = $this->alias("os")
                           ->join("__ORDERS__ o", "o.orderId=os.orderId","inner")
                           ->where(["os.id"=>$v['id']])
                           ->value("o.shopId");
            $this->sendMessage(0, $shopId, $v['id'], $dealResult);
        }
        unset($k, $v);
        $this->whereIn('id',$sIds)->setField('isClose', 1);
        Db::name('log_services')->insertAll($logs);



        // 3.处理【商家】逾期未确认收货
        $where = [['isClose','=',0],['serviceStatus','=',2],['shopReceiveExpireTime','<',$now]];
        $rs = $this->where($where)->select();

        $sIds = [];
        foreach($rs as $k=>$v){
            // 将售后单状态设置为已收货
            $this->where(['id'=>$v['id']])->update(['serviceStatus'=>$v['goodsServiceType']==0?6:3]);
            $logs = [
                ['logTime'=>$now,
                'orderId'=>$v['orderId'],
                'serviceId'=>$v['id'],
                'logTargetId'=>0,
                'logType'=>2,
                'logJson'=>json_encode(['type'=>'lang','key'=>'shop_overdue_not_receiving_tips'])]
            ];
            // 若退款退货售后单，则生成退款单
            if($v['goodsServiceType']==0){
                // 退款退货
                $logs[] = ['logTime'=>$now,
                           'orderId'=>$v['orderId'],
                           'serviceId'=>$v['id'],
                           'logTargetId'=>0,
                           'logType'=>2,
                           'logJson'=>json_encode(['type'=>'lang','key'=>'wait_sys_refund'])];
                // 生成退款订单
                $flag = $this->makeRefundData($v['orderId'], $v['refundMoney'], $v['id']);
            }
            // 生成退款单失败
            if($flag===false)continue;
            Db::name('log_services')->insertAll($logs);

            // 发送商城消息给用户
            $dealResult = WSTLang('shop_confirms_receipt');
            $serviceId = $v['id'];
            $userId = $this->alias("os")
                           ->join("__ORDERS__ o", "o.orderId=os.orderId","inner")
                           ->where(["os.id"=>$serviceId])
                           ->value("o.userId");
            $this->sendMessage($userId, 0, $serviceId, $dealResult);

        }
        unset($k, $v);

        // 4.处理【商家】逾期未发货
        $where = [['isClose','=',0],['serviceStatus','=',3],['shopSendExpireTime','<',$now]];
        $rs = $this->where($where)->select();
        // 关闭售后单，售后日志写明“商家逾期未发货”
        $logs = [];
        $dealResult = WSTLang('shop_overdue_not_deliver_tips');
        $logJson = json_encode(['type'=>'lang','key'=>'shop_overdue_not_deliver_tips']);
        $sIds = [];
        foreach($rs as $k=>$v){
            $logs[] = [
                'logTime'=>$now,
                'orderId'=>$v['orderId'],
                'serviceId'=>$v['id'],
                'logTargetId'=>0,
                'logType'=>2,
                'logJson'=>$logJson
            ];
            $sIds[] = $v['id'];
        }
        unset($k, $v);
        $this->whereIn('id',$sIds)->setField('isClose', 1);
        Db::name('log_services')->insertAll($logs);
        // 4.1发送商城消息给用户
        foreach($sIds as $serviceId){
            $userId = $this->alias("os")
                           ->join("__ORDERS__ o", "o.orderId=os.orderId","inner")
                           ->where(["os.id"=>$serviceId])
                           ->value("o.userId");
            $this->sendMessage($userId, 0, $serviceId, $dealResult);
        }





        // 5.处理【用户】逾期未去确认收货
        $where = [['isClose','=',0], ['isShopSend','=',1], ['serviceStatus','=',4],['userReceiveExpireTime','<',$now]];
        $rs = $this->where($where)->select();
        $logJson = json_encode(['type'=>'lang','key'=>'user_overdue_not_receiving_tips']);
        $dealResult = WSTLang('user_overdue_not_receiving_tips');
        foreach($rs as $k=>$v){
            // 将售后单状态设置为 【用户已收货】
            $this->where(['id'=>$v['id']])->update(['serviceStatus'=>5]);
            
            $logs = [
                ['logTime'=>$now,
                'orderId'=>$v['orderId'],
                'serviceId'=>$v['id'],
                'logTargetId'=>0,
                'logType'=>2,
                'logJson'=>$logJson]
            ];
            Db::name('log_services')->insertAll($logs);

            // 给商家发送商城消息提醒
            $shopId = $this->alias("os")
                           ->join("__ORDERS__ o", "o.orderId=os.orderId","inner")
                           ->where(["os.id"=>$v['id']])
                           ->value("o.shopId");
            $this->sendMessage(0, $shopId, $v['id'], $dealResult);
        }


        return WSTReturn(lang('operation_success'), 1);
    }


    /**
     * 商家发货
     */
    public function shopSend($sId=0){
        $serviceId = (int)input('id');
        $shopId = $sId==0?(int)session('WST_USER.shopId'):$sId;
        $has = $this->checkOrderService(1,0,$sId);
        if(!$has)return WSTReturn(lang('invalid_after_sales_order'));
        $data = input('param.');
        $validate = new Validate;
        if (!$validate->scene('shopSend')->check($data)) {
            return WSTReturn($validate->getError());
        }
        Db::startTrans();
        try{
            unset($data['id']);
            // 商家已发货
            $data['isShopSend'] = 1;
            // 售后单状态改为4，等待用户确认收货
            $data['serviceStatus'] = 4;
            // 用户收货期限
            $userReceiveDays = (int)WSTConf('CONF.userReceiveDays');
            $data['userReceiveExpireTime'] = date('Y-m-d H:i:s', time()+$userReceiveDays*24*60*60);

            $rs = $this->field('shopExpressType,shopExpressId,shopExpressNo,isShopSend,serviceStatus,userReceiveExpireTime')->where(['id'=>$serviceId])->update($data);
            if($rs===false)return WSTReturn(lang('operation_fail'));
            $logJson = '';
            if($data['shopExpressType']==0){
                $logJson = json_encode(['type'=>'lang','key'=>'shop_deliver_no_express_tips']);
            }else{
                $expressName = Db::name('express')
                                ->alias('e')
                                ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                                ->where(['e.expressId'=>$data['shopExpressId']])->value('el.expressName');
                $logJson = json_encode(['type'=>'lang','key'=>'shop_deliver_with_express','remark'=> $expressName." - ".$data['shopExpressNo']]);
            }
            // 写入日志
            $log = [
                'logTime'=>date('Y-m-d H:i:s'),
                'orderId'=>$has['orderId'],
                'serviceId'=>$serviceId,
                'logTargetId'=>$shopId,
                'logType'=>1,
                'logJson'=>$logJson
            ];
            $rs = Db::name('log_services')->insert($log);
            if($rs===false)return WSTReturn(lang('operation_fail'));

            //发送一条用户信息
            $dealResult = WSTLang('shop_has_deliverd');
            $this->sendMessage($has['userId'], 0, $serviceId, $dealResult);


            Db::commit();
            return WSTReturn(lang('operation_success'),1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail').$e->getMessage());
        }
    }
    /**
     * orderId 订单id
     * refundMoney 退款金额
     * serviceId 售后单id
     */
    private function makeRefundData($orderId, $refundMoney, $serviceId){
        Db::startTrans();
        try{
            //生成退款单前执行
            hook('beforeMakeRefundData',['orderId'=>$orderId,"serviceId"=>$serviceId]);
            //修改商家未结算金额
            $order = Db::name("orders")->where(["orderId"=>$orderId])
                                       ->field("orderId,shopId,useScore,scoreMoney,orderScore,realTotalMoney,
                                                refundedPayMoney,refundedScore,refundedScoreMoney,refundedGetScoreMoney,refundedGetScore")
                                        ->find();
            $shopId = $order["shopId"];
            $where = [];
            $where[] = ["sg.serviceId","=",$serviceId];
            $where[] = ["sg.orderId","=",$orderId];
            $where[] = ["sg.dataFlag","=",1];
            $list = Db::name("service_goods sg")
                    ->join("order_goods og","og.orderId=sg.orderId and og.goodsId=sg.goodsId and og.goodsSpecId=sg.goodsSpecId","inner")
                    ->where($where)
                    ->field("og.goodsNum o_goodsNum,og.orderGoodscommission,sg.goodsNum,sg.orderId")
                    ->select();
            // 非积分支付,并且待退款金额+已退款金额 > 实际支付金额
            if($order['realTotalMoney']>0 && (($order['refundedPayMoney'] + $refundMoney)>$order['realTotalMoney'])){
                return false;
            }
            /**
             * 记录
             * 已退还的金额(refundedPayMoney)
             * 退还积分(refundedScore)
             * 退还积分可抵扣金额(refundedScoreMoney)
             */
            // 退还的积分
            $refundedScore = 0;
            // 退还的积分可抵扣金额
            $refundedScoreMoney = 0;
            // 获得的积分可抵扣的金额
            $refundedGetScoreMoney = 0;
            // 获得的积分
            $refundedGetScore = 0;
            $osData = Db::name('order_services')->where('id',$serviceId)->find();
            if($order['useScore']>0){
                $refundedScore = $osData['refundScore'];
                 // 订单包含积分支付,计算本次退还的积分可抵扣金额
                // 积分可抵扣金额 = 使用的积分数/后台设置的比例
                // 换算比例 = 使用的积分数/积分可抵扣金额
                $rate = 0;
                if($order['realTotalMoney']>0){// 非积分支付才进行金额换算
                    if($order['scoreMoney']!=0){// 除数不能为0
                        $rate = $order['useScore']/$order['scoreMoney'] ;
                        // 退还积分可抵扣金额 = 退还的积分/换算比例;
                        $refundedScoreMoney = round($refundedScore/$rate, 2);
                    }
                }
            }
            // 获得的积分可抵扣金额
            $refundedGetScoreMoney = $osData['getScoreMoney'];
            // 获得的积分
            $refundedGetScore = $osData['getScoreVal'];
            // 累加已退还的金额、积分、积分可抵扣金额、获得的积分可抵扣的金额
            Db::name('orders')->where(["orderId"=>$orderId])->field("refundedPayMoney,refundedScore,refundedScoreMoney,refundedGetScoreMoney,refundedGetScore")->update([
                'refundedPayMoney'=>$order['refundedPayMoney']+$refundMoney,
                'refundedScore'=>$order['refundedScore']+$refundedScore,
                'refundedScoreMoney'=>$order['refundedScoreMoney']+$refundedScoreMoney,
                'refundedGetScoreMoney'=>$order['refundedGetScoreMoney']+$refundedGetScoreMoney,
                'refundedGetScore'=>$order['refundedGetScore']+$refundedGetScore,
            ]);
            foreach ($list as $key => $vo) {
                $goodsNum = $vo["goodsNum"];
                $o_money = $vo["orderGoodscommission"];
                $o_goodsNum = $vo["o_goodsNum"];
                $avg_money = WSTBCMoney($o_money/$o_goodsNum,0);


                $backMoney = WSTBCMoney($avg_money*$goodsNum,0);
                //修改订单总佣金
                Db::name("orders")->where(['orderId'=>$orderId])->update([
                                'commissionFee'=>Db::raw('commissionFee-'.$backMoney)
                            ]);
                //修改商家未结算佣金
                Db::name("shops")->where(['shopId'=>$shopId])->update([
                        'noSettledOrderFee'=>Db::raw('noSettledOrderFee+'.$backMoney)
                    ]);
            }

            $refundData = [
                'orderId'=>$orderId,
                'refundTo'=>0,
                // 申请退款id
                'refundReson'=>'10000',
                'refundOtherReson'=>WSTLang('order_service_refund'),
                // 退款金额
                'backMoney'=>$refundMoney,
                'createTime'=>date('Y-m-d H:i:s'),
                // 退款状态-> 1：商家同意
                'refundStatus'=>1,
                // 售后单Id
                'serviceId'=>$serviceId
            ];
            Db::commit();
            return Db::name('order_refunds')->insert($refundData);
        }catch(\Exception $e){
            Db::rollback();
            return false;
        }


    }
    /**
     * 商家确认收货
     */
    public function shopReceive($sId=0){
        $serviceId = (int)input('id');
        $shopId = $sId==0?(int)session('WST_USER.shopId'):$sId;
        $has = $this->checkOrderService(1,0,$sId);
        if(!$has)return WSTReturn(lang('invalid_after_sales_order'));
        $data = input('param.');
        $validate = new Validate;
        if (!$validate->scene('shopComfirm')->check($data)) {
            return WSTReturn($validate->getError());
        }
        try{
            Db::startTrans();
            unset($data['id']);
            $where = ['id'=>$serviceId];
            $field = ['serviceStatus', 'isShopAccept', 'shopSendExpireTime'];
            $logJson = '';
            if($data['isShopAccept']==1){
                // 确认收货
                if($has['goodsServiceType']==0){// 退款退货
                     // 状态修改为"商家确认收货"
                     $data['serviceStatus'] = 6;
                     // 生成退款订单
                     $rdRs = $this->makeRefundData($has['orderId'], $has['refundMoney'], $serviceId);
                     if($rdRs===false)return WSTReturn(lang('create_refund_order_fail'));

                }else if($has['goodsServiceType']==2){// 换货
                   // 状态值修改为"等待商家发货"
                   $data['serviceStatus'] = 3;
                   // 商家发货期限 shopSendExpireTime
                   $shopSendDays = (int)WSTConf('CONF.shopSendDays');
                   $data['shopSendExpireTime'] = date('Y-m-d H:i:s', time()+$shopSendDays*24*60*60);
                }
                $logJson = json_encode(['type'=>'lang','key'=>'shop_confirms_receipt']);
            }else{
                // 标记售后单结束
                $data['isClose'] = 1;
                $field = array_merge($field, ['shopRejectType','shopRejectOther','shopRejectImg', 'isClose']);
                if(!WSTCheckDatas('ORDER_SERVICES_SHOP_REJECT',$data['shopRejectType']))return WSTReturn(lang('invalid_reject_type'));
                // 拒收类型文字
                $rejectText = WSTDatas('ORDER_SERVICES_SHOP_REJECT', $data['shopRejectType']);
                if(empty($rejectText))return WSTReturn(lang('invalid_reject_type'));
                $dataName = $rejectText['dataName'];
                // 拒收
                $logJson = json_encode(['type'=>'lang','key'=>'shop_reject_type_tips','remark'=>$dataName]);
                if($data['shopRejectType']=='10000'){
                    $logJson = json_encode(['type'=>'lang_data','key'=>'shop_reject_type_tips2','datakey'=>'ORDER_SERVICES_SHOP_REJECT','dataval'=>$data['shopRejectType'],'remark'=>$data['shopRejectOther']]);
                }
            }
            $rs = $this->where($where)->field($field)->update($data);
            if($rs===false)return WSTReturn(lang('operation_fail'));
            // 写入日志
            $now = date('Y-m-d H:i:s');
            $log = [
                ['logTime'=>$now,
                'orderId'=>$has['orderId'],
                'serviceId'=>$serviceId,
                'logTargetId'=>$shopId,
                'logType'=>1,
                'logJson'=>$logJson]
            ];

            // 处理结果
            $dealResult = WSTLang('shop_confirms_receipt');
            if($data['isShopAccept']==1 && $has['goodsServiceType']==0){// 商家确认收货
                // 退款退货
                $log[] = ['logTime'=>$now,
                          'orderId'=>$has['orderId'],
                          'serviceId'=>$serviceId,
                          'logTargetId'=>0,
                          'logType'=>2,
                          'logJson'=>json_encode(['type'=>'lang','key'=>'wait_sys_refund'])];
                $dealResult .=  WSTLang('wait_sys_refund');
            }else if($data['isShopAccept']==-1){
                // 商家拒收、售后单结束
                $log[] = ['logTime'=>$now,
                          'orderId'=>$has['orderId'],
                          'serviceId'=>$serviceId,
                          'logTargetId'=>0,
                          'logType'=>2,
                          'logJson'=>json_encode(['type'=>'lang','key'=>'shop_reject_service_closed'])];
                $dealResult = lang('shop_reject_receiving');
            }

            $rs = Db::name('log_services')->insertAll($log);
            if($rs===false)return WSTReturn(lang('operation_fail'));

            //发送一条用户信息
            $this->sendMessage($has['userId'], 0, $serviceId, $dealResult);

            Db::commit();
            return WSTReturn(lang('operation_success'),1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail').$e->getMessage());
        }
    }
    // 商家处理退款申请
    public function dealRefund($sId=0){
        $data = input('param.');
        $validate = new Validate;
        if (!$validate->scene('refund')->check($data)) {
            return WSTReturn($validate->getError());
        }
        $serviceId = (int)input('id');
        $shopId = $sId==0?(int)session('WST_USER.shopId'):$sId;
        $has = $this->checkOrderService(1,0,$shopId);
        if(empty($has)){
            return WSTReturn(lang('after_sales_order_not_exist'));
        }
        try{
            Db::startTrans();
            $isShopAgree = (int)input('isShopAgree');
            unset($data['id']);
            $now = date('Y-m-d H:i:s');
            $dealResult = WSTLang('shop_agree_refund');
            if($isShopAgree==1){
                // 生成退款订单
                $rdRs = $this->makeRefundData($has['orderId'], $has['refundMoney'], $serviceId);
                if($rdRs===false)return WSTReturn(lang('create_refund_order_fail'));
                // 受理
                $data['serviceStatus'] = 7;
                $rs = Db::name('order_services')->field('isShopAgree,serviceStatus')
                                                ->where(['id'=>$serviceId])->update($data);
                if($rs===false)return WSTReturn(lang('operation_fail'));
                // 写入日志
                $log = [
                    ['logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$shopId,
                    'logType'=>1,
                    'logJson'=>json_encode(['type'=>'lang','key'=>'shop_agree_curr_service_apply'])],
                    ['logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$shopId,
                    'logType'=>1,
                    'logJson'=>json_encode(['type'=>'lang','key'=>'shop_agree_refund'])],
                    ['logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>0,
                    'logType'=>2,
                    'logJson'=>json_encode(['type'=>'lang','key'=>'wait_sys_refund'])]
                ];
                $dealResult .=  WSTLang('wait_sys_refund');

                $rs = Db::name('log_services')->insertAll($log);
                if($rs===false)return WSTReturn(lang('operation_fail'));
            }else{
                if(!isset($data['disagreeRemark']) && strlen($data['disagreeRemark'])==0)return WSTReturn(lang('require_not_accepte_reasons'));
                // 不受理
                $data['isClose'] = 1;
                $rs = Db::name('order_services')->field('isShopAgree,disagreeRemark,isClose')
                                                ->where(['id'=>$serviceId])->update($data);
                if($rs===false)return WSTReturn(lang('operation_fail'));
                // 写入日志
                $log = [
                    'logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$shopId,
                    'logType'=>1,
                    'logJson'=>json_encode(['type'=>'lang_params','key'=>'shop_reject_curr_service_apply_reason','params'=>[$data['disagreeRemark']]])
                ];
                $dealResult =  WSTLang('shop_reject_curr_service_apply');

                $rs = Db::name('log_services')->insert($log);
                if($rs===false)return WSTReturn(lang('operation_fail'));
            }

            //发送一条用户信息
            $this->sendMessage($has['userId'], 0, $serviceId, $dealResult);


            Db::commit();
            return WSTReturn(lang('operation_success'),1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail').$e->getMessage());
        }
    }

    // 商家处理售后申请
    public function dealApply($sId=0){
        $data = input('param.');
        $validate = new Validate;
        if (!$validate->scene('deal')->check($data)) {
            return WSTReturn($validate->getError());
        }
        $serviceId = (int)input('id');
        $shopId = $sId==0?(int)session('WST_USER.shopId'):$sId;
        // 检查售后单是否属于该商家
        $has = Db::name('orders')->alias('o')
                                 ->join('order_services os','os.orderId=o.orderId')
                                 ->where(['shopId'=>$shopId,'id'=>$serviceId])
                                 ->find();
        if(empty($has)){
            return WSTReturn(lang('after_sales_order_not_exist'));
        }
        try{
            Db::startTrans();
            $isShopAgree = (int)input('isShopAgree');
            unset($data['id']);
            $now = date('Y-m-d H:i:s');

            // 商家处理结果
            $dealResult = "";

            if($isShopAgree==1){
                // 受理
                // 等待用户发货
                $data['serviceStatus'] = 1;

                // 用户发货期限
                $userSendDays = (int)WSTConf('CONF.userSendDays');
                $data['userSendExpireTime'] = date('Y-m-d H:i:s', time()+$userSendDays*24*60*60);

                $rs = Db::name('order_services')->field('userSendExpireTime,isShopAgree,shopAddress,shopName,shopPhone,serviceStatus')
                                                ->where(['id'=>$serviceId])->update($data);
                if($rs===false)return WSTReturn(lang('operation_fail'));
                // 写入日志
                $logJson = json_encode(['type'=>'lang_params','key'=>'shop_receiving_address','params'=>[$data['shopName'],$data['shopPhone'],$data['shopAddress']]]);
                $log = [
                    ['logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$shopId,
                    'logType'=>1,
                    'logJson'=>json_encode(['type'=>'lang','key'=>'shop_agree_curr_service_apply'])],
                    ['logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$shopId,
                    'logType'=>1,
                    'logJson'=>$logJson]
                ];
                $rs = Db::name('log_services')->insertAll($log);

                $dealResult = WSTLang('shop_agree_curr_service_apply');

                if($rs===false)return WSTReturn(lang('operation_fail'));
            }else{
                // 不受理
                $data['isClose'] = 1;
                $rs = Db::name('order_services')->field('isShopAgree,disagreeRemark,isClose')
                                                ->where(['id'=>$serviceId])->update($data);
                if($rs===false)return WSTReturn(lang('operation_fail'));
                // 写入日志
                $log = [
                    'logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$shopId,
                    'logType'=>1,
                    'logJson'=>json_encode(['type'=>'lang_params','key'=>'shop_reject_curr_service_apply_reason','params'=>[$data['disagreeRemark']]])
                ];
                $rs = Db::name('log_services')->insert($log);

                $dealResult = WSTLang('shop_reject_curr_service_apply');

                if($rs===false)return WSTReturn(lang('operation_fail'));
            }
            //发送一条用户信息
            $this->sendMessage($has['userId'], 0, $serviceId, $dealResult);

            Db::commit();
            return WSTReturn(lang('operation_success'),1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail').$e->getMessage());
        }
    }

    /**
     * 发送商城消息提醒
     * @param userId 用户id
     * @param shopId 店铺id
     * @param serviceId 售后表主键id
     * @param 当前状态
     */
    public function sendMessage($userId, $shopId, $serviceId, $dealResult){
        $tpl = WSTMsgTemplates('ORDER_SERVICE_TIPS');
        if($tpl['tplContent']!='' && $tpl['status']=='1'){
            $find = ['${SERVICE_STATUS}'];
            $replace = [$dealResult];
            $content = str_replace($find,$replace,$tpl['tplContent']);
            $msgJson = ['from'=>1,'dataId'=>$serviceId];
            if($userId>0){
                // 发送一条用户信息
                WSTSendMsg($userId, $content, $msgJson);
            }else{
                // 给商家发送商城消息提醒
                $msg = array();
                $msg["shopId"] = $shopId;
                $msg["tplCode"] = $tpl["tplCode"];
                $msg["msgType"] = 1;
                $msg["content"] = $content;
                $msg["msgJson"] = $msgJson;
                model("common/MessageQueues")->add($msg);
            }
        }
    }


    // 获取协商日志
    public function getLog($uId=0){
        $serviceId = (int)input('id');

        $userId = $uId==0?(int)session('WST_USER.userId'):$uId;

        $has = Db::name('order_services')->alias('os')
                                  ->join('orders o','o.orderId=os.orderId')
                                  ->where(['o.userId'=>$userId,'os.id'=>$serviceId])
                                  ->find();
        if(empty($has))return [];


        $rs = Db::name('log_services')->where(['serviceId'=>$serviceId])->order('logId desc')->select();

        $userPhoto = session('WST_USER.userPhoto');
        $userName = session('WST_USER.loginName');
        if($uId>0){
            // app 或 小程序
            $userInfo = Db::name('users')->where(['userId'=>$userId])->find();
            if(!empty($userInfo)){
                $userPhoto = $userInfo['userPhoto'];
                $userName = $userInfo['userName']?:$userInfo['loginName'];
            }
        }

        if(!empty($rs)){
            // 取头像及名称
            $shopInfo = [];
            foreach($rs as $k=>$v){
                if($v['logType']==0){
                    // 取用户头像及名称
                    $rs[$k]['avatar'] = $userPhoto;
                    $rs[$k]['nickname'] = $userName;
                }else if($v['logType']==1){
                    if(empty($shopInfo)){
                        $shopInfo = model('shops')->getFieldsById($v['logTargetId'],['shopName','shopImg']);
                    }
                    // 取商家头像及名称
                    $rs[$k]['avatar'] = $shopInfo['shopImg'];
                    $rs[$k]['nickname'] = $shopInfo['shopName'];
                }else if($v['logType']==2){
                    // 取商城头像及名称
                    $rs[$k]['avatar'] = WSTConf('CONF.mallLogo');
                    $rs[$k]['nickname'] = lang('mall_manager');
                }
            }

        }
        return $rs;
    }
    /**
     * 获取售后详情
     * @param $type 0:用户 1:商家
     */
    public function getDetail($type=0, $uId=0, $sId=0){
        $userId = $uId==0?(int)session('WST_USER.userId'):$uId;
        $serviceId = (int)input('id');
        $where = ['s.id'=>$serviceId,'o.userId'=>$userId];
        if($type==1){
            unset($where['o.userId']);
            $shopId = $sId==0?(int)session('WST_USER.shopId'):$sId;
            $where['o.shopId'] = $shopId;
        }
        $has = $this->checkOrderService($type, $userId);
        if(!$has)return WSTReturn(lang('after_sales_order_not_exist'));
        // 查询
        $rs = $this->alias('s')
                   ->join('orders o','o.orderId=s.orderId','inner')
                   ->where($where)
                   ->field('s.*,o.orderNo')
                   ->find();
        if(!empty($rs)){
            // 查询服务单下的商品信息
            $rs['glists'] = Db::name('service_goods')->alias('sg')
                                                     ->join('order_goods og','og.goodsId=sg.goodsId and og.goodsSpecId=sg.goodsSpecId','inner')
                                                     ->where(['sg.serviceId'=>$serviceId,'og.orderId'=>$has['orderId']])
                                                     ->where("(og.goodsCode='' or ISNULL(og.goodsCode))")
                                                     ->field('sg.serviceId,og.*,sg.goodsNum')
                                                     ->select();
            // 售后单类型
            $rs['goodsServiceTypeText'] = $this->goodsServiceTypeText($rs['goodsServiceType']);
            // 售后单状态
            $rs['statusText'] = $rs['isClose']==1?lang('order_service_closed'):$this->getStatus($rs['serviceStatus'], $rs['goodsServiceType']);
            // 申请原因
            $rs['serviceTypeText'] = $this->getServiceTypeText($rs['serviceType']);
            // 用户快递公司
            if($rs['isUserSendGoods']==1 && $rs['expressType']==1 ){
                $rs['expressName'] = Db::name('express')
                    ->alias('e')
                    ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                    ->where(['e.expressId'=>$rs['expressId']])
                    ->value('el.expressName');
            }
            // 商家物流公司
            if($rs['isShopSend']==1 && $rs['shopExpressType']==1 ){
                $rs['shopExpressName'] = Db::name('express')
                    ->alias('e')
                    ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                    ->where(['e.expressId'=>$rs['shopExpressId']])
                    ->value('el.expressName');
            }
            // 若为商家则查询上一次填写的地址信息
            if($type==1){
                $shopId = $where['o.shopId'];
                $lastData = Db::name('order_services')->alias('os')
                                                     ->join('orders o','o.orderId=os.orderId','inner')
                                                     ->where(['o.shopId'=>$shopId])
                                                     ->where("os.id !={$serviceId} and shopAddress!='' ")
                                                     ->order('os.createTime desc')
                                                     ->find();
                $rs['lastShopAddress'] = $lastData['shopAddress'];
                $rs['lastShopName'] = $lastData['shopName'];
                $rs['lastShopPhone'] = $lastData['shopPhone'];
            }
        }
        return $rs;
    }
    /**
     * 售后列表查询
     * @param $type 0:用户 1:商家
     */
    public function pageQuery($type=0, $uId=0, $sId=0){
        $userId = $uId==0?(int)session('WST_USER.userId'):$uId;
        $orderNo = (int)input('orderNo');
        $where = [
            'o.userId'=>$userId,
            // 确认收货之后才能申请售后
            'o.orderStatus'=>2,
            'o.dataflag'=>1,
        ];
        if($type==1){
            $shopId = $sId==0?(int)session('WST_USER.shopId'):$sId;
            unset($where['o.userId']);
            $where['o.shopId'] = $shopId;
        }
        $where2 = [];
        if($orderNo>0){
            $where2 = "o.orderNo like '%$orderNo%'";
        }
        $order = 'os.createTime desc';
        if($type==1){
            $order = 'os.isClose asc, '.$order;
        }

        $rs = $this->alias('os')
                   ->join('orders o','os.orderId=o.orderId','inner')
                   ->where($where)
                   ->where($where2)
                   ->field('o.orderNo,os.*')
                   ->order($order)
                   ->paginate()
                   ->toArray();
        if(!empty($rs['data'])){
            // 查询售后单下的商品
            foreach($rs['data'] as $k=>$v){
                $imgs = Db::name('order_goods')->alias('og')
                                               ->join('service_goods sg','sg.goodsId=og.goodsId and sg.goodsSpecId=og.goodsSpecId','inner')
                                               ->where(['sg.serviceId'=>$v['id'],'og.orderId'=>$v['orderId']])
                                               ->where("(og.goodsCode='' or ISNULL(og.goodsCode))")
                                               ->column('og.goodsImg');
                if(!empty($imgs)){
                    $imgs = array_map(function($item){return WSTImg($item,1);},$imgs);
                }
                $rs['data'][$k]['gImgs'] = $imgs;


                // 查询服务单下的商品信息
                $rs['data'][$k]['glists'] = Db::name('service_goods')->alias('sg')
                ->join('order_goods og','og.goodsId=sg.goodsId','inner')
                ->where(['sg.serviceId'=>$v['id'],'og.orderId'=>$v['orderId']])
                ->field('sg.serviceId,og.*,sg.goodsNum')
                ->select();




                // 售后单状态
                $rs['data'][$k]['statusText'] = $v['isClose']==1?lang('order_service_closed'):$this->getStatus($v['serviceStatus'], $v['goodsServiceType']);
                $rs['data'][$k]['goodsServiceTypeText'] = $this->goodsServiceTypeText($v['goodsServiceType']);
            }
        }
        return $rs;
    }
    // 获取申请类型
    private function getServiceTypeText($val){
        $rs = WSTDatas('ORDER_SERVICES',$val);
        if(!empty($rs))return $rs['dataName'];
        return '';
    }
    // 获取售后单类型
    private function goodsServiceTypeText($val){
        // 状态备注：0：退款退货 1：退款 2：换货
        $code = '';
        switch($val){
            case 0:
                $code = lang('order_service_type_1');
            break;
            case 1:
                $code = lang('order_service_type_2');
            break;
            case 2:
                $code = lang('order_service_type_3');
            break;
        }
        return $code;
    }
    // 获取状态
    private function getStatus($val, $type){
        // $type 0:退款退货 1:仅退款 2:仅换货
        // 状态备注：0：待商家审核  1： 2： 3：  4：  5：  6：
        $code = '';
        switch($val){
            case 0:
                $code = lang('order_service_status_1');
            break;
            case 1:
                $code = lang('order_service_status_2');
            break;
            case 2:
                $code = lang('order_service_status_3');
            break;
            case 3:
                $code = lang('order_service_status_4');
            break;
            case 4:
                $code = lang('order_service_status_5');
            break;
            case 5:
                $code = lang('order_service_status_6');
                if($type==1)$code=lang('order_service_status_7');
                elseif($type==2)$code=lang('order_service_status_8');
            break;
            case 6:
                $code = lang('order_service_status_9');
            break;
            case 7:
                $code = lang('order_service_status_10');
            break;
        }
        return $code;
    }
    /**
     * 计算当前可退款金额，检测是否可以提交售后单
     * 前端用户勾选商品或改变数量时对可退款金额进行计算
     * @param orderId 订单id
     * @param ids order_goods表的主键id 例如：2,4,5,8
     * @param num_{$id} num_og表主键id 例如：num_2:1, num_4:3 表示2商品数量未1件 4商品数量为3件
     */
    public function getRefundableMoney($uId=0, $ids='', $numArr=[]){
        $userId = $uId==0?(int)session('WST_USER.userId'):$uId;
        $orderId = (int)input('orderId');
        $rs = ['totalMoney'=>0,'totalScore'=>0,'useScoreMoney'=>0,'getScoreMoney'=>0,'totalGetScore'=>0];
        $ids = input('ids',$ids);
        // 未传入og表主键
        if($ids=='')return WSTReturn('ok',1,$rs);
        $ids = explode(',', $ids);
        $where = [
            'o.userId'=>$userId,
            'o.orderId'=>$orderId,
        ];
        $orders = Db::name('orders')->alias('o')
                                    ->where($where)
                                    ->find();
        if(empty($orders))return WSTReturn(lang('order_data_exception'));
        $orderGoods = Db::name('order_goods')->where(['orderId'=>$orderId])->select();

        /**
         *  订单商品原可退款总金额 = 总金额(totalMoney)-运费(deliverMoney)-积分抵扣金额(scoreMoney)-优惠券抵扣金额(couponVal)-满减减免金额(rewardVal)-获得积分换算金额
         *
         * */
        // 优惠券抵扣金额
        $orderCouponVal = 0;
        // 满减减免金额
        $orderRewardVal = 0;
        // 计算订单下优惠券减免金额、满减减免金额
        foreach($orderGoods as $v){
            $orderCouponVal += isset($v['couponVal'])?$v['couponVal']:0;
            $orderRewardVal += isset($v['rewardVal'])?$v['rewardVal']:0;
        }
        unset($v);
        // 获得积分换算金额
        // if($orders['useScore']==0 && $orders['scoreMoney']==0){
        //     $orderGetScoreMoney = 0;
        // }else{
        //     $orderGetScoreMoney = 0;
        //     if($orders['scoreMoney']!=0)$orderGetScoreMoney = $orders['orderScore']/($orders['useScore']/$orders['scoreMoney']);
        // }
        $orderGetScoreMoney = $orders['getScoreVal'];
        // 运费不退
        $orders['realTotalMoney'] = $orders['realTotalMoney']-$orders['deliverMoney'];


        // 原可退款金额
        $originalRefundableMoney = $orders['totalMoney']-$orders['deliverMoney']-$orders['scoreMoney']-$orderCouponVal-$orderRewardVal-$orderGetScoreMoney;
        // 是否为纯积分支付
        $isScorePay = ($orders['realTotalMoney']==0);
        if(!$isScorePay && $originalRefundableMoney<=0){
            // 原可退小于等于0
            return WSTReturn('ok',1,$rs);
        }
        // 是否有修改订单价格
        $isEditOrderMoney = $originalRefundableMoney!=$orders['realTotalMoney'];
        // 修改订单价格之后实际上可退款金额 = 实际支付金额 - 获得积分换算金额
        $actuallyRefundableMoney = max(0, $orders['realTotalMoney']-$orderGetScoreMoney);


        // 是否为【商品组合-固定搭配】订单
        $isFixedCombination = $this->isFixedCombination($orderId);
        if($isFixedCombination){
            // 固定搭配的只能整单退款、ids置为全选中
            $_tmp = [];
            foreach($orderGoods as $k=>$v){
                array_push($_tmp, $v['id']);
            }
            $ids = $_tmp;
            unset($k, $v, $_tmp);
        }

        // 取出订单下的商品进行遍历，计算出当前选中的商品单件可退多少，总共可退多少【需减免已经退款的商品件数】
        foreach($orderGoods as $k=>$v){
            if(in_array($v['id'], $ids)){
                // 在待退款列表中
                // 1.查询已退款的件数【该已提交的售后申请数量】
                $currSGoods = Db::name('service_goods')->alias('sg')
                                                        ->join('order_services os','os.id=sg.serviceId','inner')
                                                        ->where(['sg.goodsId'=>$v['goodsId'],
                                                                 'sg.goodsSpecId'=>$v['goodsSpecId'],
                                                                 'os.isClose'=>0,
                                                                 'os.orderId'=>$orderId
                                                                 ])
                                                        ->field('sum(sg.goodsNum) totalNum')
                                                        ->find();
                // 获取当前商品申请的件数
                $paramIndex = 'num_'.$v['id'];
                if(!empty($numArr) && isset($numArr[$paramIndex])){
                    $currSGoodsNum = $numArr[$paramIndex];
                }else{
                    $currSGoodsNum = (int)input($paramIndex);
                }
                // 大于购买数 不予处理【异常数量】
                if($currSGoodsNum>$v['goodsNum'])return WSTReturn(lang('abnormal_return_quantity_tips'));
                // 若申请件数 大于 购买数-已申请件数 则不予处理【异常数量】
                if($isFixedCombination){
                    // 固定套餐，便于就算价格，将已申请售后的数量赋值为0
                    $currSGoods['totalNum'] = 0;
                }
                if($currSGoodsNum>($v['goodsNum']-(int)$currSGoods['totalNum']))return WSTReturn(lang('abnormal_return_quantity_tips'));
                if($currSGoodsNum<=($v['goodsNum']-(int)$currSGoods['totalNum'])){
                    // 计算单件可退价格【若为最后一件则 由 总共可退价格-已退还价格=可退价格 】
                    // 优惠券减免金额
                    $couponVal = isset($v['couponVal'])?$v['couponVal']:0;
                    // 满就送减免金额
                    $rewardVal = isset($v['rewardVal'])?$v['rewardVal']:0;
                    // 积分抵扣金额
                    $scoreMoney = isset($v['scoreMoney'])?$v['scoreMoney']:0;

                    // 获得的积分换算成金额； 兑换比例=使用的积分/积分抵扣金额【190929使用order_goods表记录的值】
                    $getScoreValToMoney = $v['getScoreMoney'];
                    if($v['goodsNum']==1){ //只有一件
                        // 当前商品可退款金额 = 商品总价格-优惠券减免金额-满就送减免金额-积分抵扣金额-获得的积分换算成金额;
                        // 累加可退款金额
                        $rs['totalMoney'] += ($v['goodsPrice']-$couponVal-$rewardVal-$scoreMoney-$getScoreValToMoney);
                            // 确保可退款金额不小于0
                            if($rs['totalMoney']<0)$rs['totalMoney'] = 0;
                            // 确保可退款金额低于实际支付金额【保留两位小数】
                            if($isEditOrderMoney)$rs['totalMoney'] = round($rs['totalMoney']/$originalRefundableMoney*$actuallyRefundableMoney, 2);
                        if(!$isScorePay){
                            // 使用的积分
                            $rs['totalScore'] += $v['useScoreVal'];
                        }else{
                            // 若为纯积分支付，则 可退还积分需要减去下单获得的积分
                            $rs['totalScore'] += $v['useScoreVal'] - $v['getScoreVal'];
                        }

                        // 使用的积分可抵扣金额
                        $rs['useScoreMoney'] += $scoreMoney;
                        // 获得的积分可抵扣金额
                        $rs['getScoreMoney'] += $getScoreValToMoney;
                        // 用户购买商品时获得的积分【则换算成金额减免可退款金额】
                        // getScoreVal
                        $rs['totalGetScore'] += $v['getScoreVal'];
                    }else{
                        /**
                         * 存在修改订单金额情况
                         */
                        // 修改订单金额后的单件可退金额
                        $avgRefundableMoney = 0;
                        // 修改订单金额后的最后一件可退金额
                        $lastRefundableMoney = 0;
                        if($isEditOrderMoney){
                            // 当前商品原可退款金额
                            $_currGoodsRefundableMoney = $v['goodsPrice']*$v['goodsNum']-$couponVal-$rewardVal-$scoreMoney-$getScoreValToMoney;
                            // 当前商品总共可退款金额
                            $_tmpTotalRefundableMoney = round($_currGoodsRefundableMoney/$originalRefundableMoney*$actuallyRefundableMoney, 2);
                            $avgRefundableMoney = round($_currGoodsRefundableMoney/$originalRefundableMoney*$actuallyRefundableMoney/$v['goodsNum'], 2);
                            $lastRefundableMoney = $_tmpTotalRefundableMoney-$avgRefundableMoney*($v['goodsNum']-1);
                        }

                        // 每件商品分摊的优惠券、满就送、积分减免金额、使用的积分数、获得的积分、获得的积分换算成金额
                        $avgCouponVal = round($couponVal/$v['goodsNum'], 2);
                        $avgRewardVal = round($rewardVal/$v['goodsNum'], 2);
                        $avgScoreMoney = round($scoreMoney/$v['goodsNum'], 2);
                        $avgScoreVal = floor($v['useScoreVal']/$v['goodsNum']);
                        $avgGetScoreVal = round($v['getScoreVal']/$v['goodsNum'], 2);
                        $avgGetScoreValToMoney = round($getScoreValToMoney/$v['goodsNum'], 2);
                        // 计算最后一件商品可退款金额（避免出现除不尽情况时，金额总数不正确）
                        $lastCouponVal = $couponVal-$avgCouponVal*($v['goodsNum']-1);
                        $lastRewardVal = $rewardVal-$avgRewardVal*($v['goodsNum']-1);
                        $lastScoreMoney = $scoreMoney-$avgScoreMoney*($v['goodsNum']-1);
                        $lastScoreVal = $v['useScoreVal']-$avgScoreVal*($v['goodsNum']-1);
                        $lastGetScoreVal =  $v['getScoreVal']-$avgGetScoreVal*($v['goodsNum']-1);
                        $lastScoreValToMoney = $getScoreValToMoney-$avgGetScoreValToMoney*($v['goodsNum']-1);

                        // 最后一件的值
                        $lastIndex = $v['goodsNum']-$currSGoods['totalNum'];
                        // 计算单件可退还价格
                        for($i=1;$i<=$currSGoodsNum;++$i){
                            if($i==$lastIndex){// 最后一件
                                // 累加可退款金额
                                $_tmpTotalMoney = ($v['goodsPrice']-$lastCouponVal-$lastRewardVal-$lastScoreMoney-$lastScoreValToMoney);
                                    // 确保可退款金额低于实际支付金额【保留两位小数】
                                    if($isEditOrderMoney)$_tmpTotalMoney = $lastRefundableMoney;
                                $rs['totalMoney'] += $_tmpTotalMoney;
                                    // 确保可退款金额不小于0
                                    if($rs['totalMoney']<0)$rs['totalMoney'] = 0;


                                if(!$isScorePay){
                                    // 累加可退退还积分
                                    $rs['totalScore'] += $lastScoreVal;
                                }else{
                                    // 若为纯积分支付，则 可退还积分需要减去下单获得的积分
                                    $rs['totalScore'] += $lastScoreVal - $lastGetScoreVal;
                                }

                                // 使用的积分可抵扣金额
                                $rs['useScoreMoney'] += $lastScoreMoney;
                                // 获得的积分可抵扣金额
                                $rs['getScoreMoney'] += $lastScoreValToMoney;
                                // 用户购买商品时获得的积分【则换算成金额减免可退款金额】
                                $rs['totalGetScore'] += $lastGetScoreVal;
                            }else{
                                // 累加可退款金额
                                $_tmpTotalMoney = ($v['goodsPrice']-$avgCouponVal-$avgRewardVal-$avgScoreMoney-$avgGetScoreValToMoney);
                                    // 确保可退款金额低于实际支付金额【保留两位小数】
                                    if($isEditOrderMoney)$_tmpTotalMoney = $avgRefundableMoney;
                                $rs['totalMoney'] += $_tmpTotalMoney;
                                    // 确保可退款金额不小于0
                                    if($rs['totalMoney']<0)$rs['totalMoney'] = 0;

                                if(!$isScorePay){
                                    // 累加可退退还积分
                                    $rs['totalScore'] += $avgScoreVal;
                                }else{
                                    // 若为纯积分支付，则 可退还积分需要减去下单获得的积分
                                    $rs['totalScore'] += $avgScoreVal - $avgGetScoreVal;
                                }

                                // 使用的积分可抵扣金额
                                $rs['useScoreMoney'] += $avgScoreMoney;
                                // 获得的积分可抵扣金额
                                $rs['getScoreMoney'] += $avgGetScoreValToMoney;
                                // 用户购买商品时获得的积分【则换算成金额减免可退款金额】
                                $rs['totalGetScore'] += $avgGetScoreVal;
                            }
                        }
                    }
                }
            }
        }
        // 若纯积分支付，则可退款金额必须为0
        if($isScorePay)$rs['totalMoney'] = 0;
        // 若为货到付款则可退款金额为0
        if($orders['payType']==0){
            $rs['totalMoney'] = 0;
        }
        // 保留两位小数
        $rs['totalMoney'] = round($rs['totalMoney'], 2);
        return WSTReturn('ok',1,$rs);

    }
    // 获取订单下可申请售后的商品
    public function getGoods($uId=0){
        $userId = $uId==0?(int)session('WST_USER.userId'):$uId;
        $orderId = (int)input('orderId');
        $where = [
            'o.userId'=>$userId,
            'o.orderId'=>$orderId,
            // 确认收货之后才能申请售后
            'o.orderStatus'=>2,
            'o.dataflag'=>1
        ];
        // 订单中的商品(赠品不参与退换货)
        $rs = Db::name('orders')->alias('o')
                                ->join('order_goods og','og.orderId=o.orderId','inner')
                                ->where($where)
                                ->where("(og.goodsCode='' or ISNULL(og.goodsCode))")
                                ->field('*')
                                ->select();
        // 已经申请售后的商品
        $sIds = Db::name('order_services')->alias('os')->where(['isClose'=>0, 'orderId'=>$orderId])->column('id');
        if(!empty($sIds)){
            // 存在售后申请，查询已经申请售后的商品
            foreach($rs as $k=>$v){
                $currGoods = Db::name('service_goods')->whereIn('serviceId',$sIds)
                                                      ->where(['goodsId'=>$v['goodsId'],
                                                               'goodsSpecId'=>$v['goodsSpecId'],
                                                               'dataflag'=>1
                                                               ])
                                                      ->field('sum(goodsNum) goodsNum')
                                                      ->find();
                if(!empty($currGoods)){
                    $rs[$k]['goodsNum'] = $v['goodsNum']-$currGoods['goodsNum'];
                }
            }
        }
        return $rs;
    }
    /****************************************** 用户操作售后单 ***************************************************/
    /**
     * 是否为固定搭配商品订单
     */
    public function isFixedCombination($orderId){
        $rs = Db::name("orders")->where(["orderId"=>$orderId])->find();
        // 订单类型：【商品组合】订单若为【固定搭配】则只能整单退换
        if($rs['orderCode']=='combination' &&  isset($rs['extraJson'])){
            $extraJson = json_decode($rs['extraJson'], true);
            return ($extraJson['combineType']==1);
        }
        return false;
    }
    /**
     * 用户确认收货
     */
    public function userReceive($uId=0){
        $userId = $uId==0?(int)session('WST_USER.userId'):$uId;
        $data = input('param.');
        $serviceId = (int)input('id');
        hook('beforeOrderServicesUserReceive',['userId'=>$userId,"serviceId"=>$serviceId]);
        $validate = new Validate;
        if (!$validate->scene('userConfirm')->check($data)) {
            return WSTReturn($validate->getError());
        }
        // 检查售后单是否存在
        $has = $this->checkOrderService(0,$uId);
        if(!$has)return WSTReturn(lang('after_sales_order_not_exist'));
        if($has['serviceStatus']!=4)return WSTReturn(lang('after_sales_order_status_changed'));
        try{
            Db::startTrans();
            unset($data['id']);
            $where = ['id'=>$serviceId];
            $field = ['serviceStatus','isUserAccept'];

            $dealResult = "";
            $logJson = '';
            if($data['isUserAccept']==1){
                // 确认收货
                // 状态值修改为"完成换货"
                $data['serviceStatus'] = 5;
                $logJson = json_encode(['type'=>'lang','key'=>'user_confirms_receipt']);
                $dealResult = WSTLang('user_confirms_receipt');
            }else{
                $field = array_merge($field, ['userRejectType','userRejectOther','isClose']);
                if(!WSTCheckDatas('ORDER_REJECT',$data['userRejectType']))return WSTReturn(lang('invalid_reject_type'));
                // 拒收类型文字
                $rejectText = WSTDatas('ORDER_REJECT', $data['userRejectType']);
                if(empty($rejectText))return WSTReturn(lang('invalid_reject_type'));
                // 拒收
                $logJson = json_encode(['type'=>'lang_data','key'=>'user_reject_type_tips','datakey'=>'ORDER_REJECT','dataval'=>$data['userRejectType']]);
                if($data['userRejectType']=='10000'){
                    $logJson = json_encode(['type'=>'lang_data','key'=>'user_reject_type_tips2','datakey'=>'ORDER_REJECT','dataval'=>$data['userRejectType'],'remark'=>$data['userRejectOther']]);
                }
                $data['isClose'] = 1;
                $dealResult = lang('user_reject');
            }
            $rs = $this->where($where)->field($field)->update($data);
            if($rs===false)return WSTReturn(lang('operation_fail'));
            $now = date('Y-m-d H:i:s');
            // 写入日志
            $log[] = [
                'logTime'=>$now,
                'orderId'=>$has['orderId'],
                'serviceId'=>$serviceId,
                'logTargetId'=>$userId,
                'logType'=>0,
                'logJson'=>$logJson
            ];
            if($data['isUserAccept']==-1){
                // 用户拒收、售后单结束
                $log[] = ['logTime'=>$now,
                          'orderId'=>$has['orderId'],
                          'serviceId'=>$serviceId,
                          'logTargetId'=>0,
                          'logType'=>2,
                          'logJson'=>json_encode(['type'=>'lang','key'=>'user_reject_closed'])];
            }
            $rs = Db::name('log_services')->insertAll($log);

            if($rs===false)return WSTReturn(lang('operation_fail'));

            // 给商家发送商城消息提醒
            $this->sendMessage(0, $has["shopId"], $serviceId, $dealResult);

            Db::commit();
            return WSTReturn(lang('operation_success'),1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail').$e->getMessage());
        }
    }
    /**
     * 用户发货
     */
    public function userExpress($uId=0){
        $serviceId = (int)input('id');
        hook("beforeServicesUserExpress",["uId"=>$uId,"serviceId"=>$serviceId]);
        $userId = $uId==0?(int)session('WST_USER.userId'):$uId;

        $data = input('param.');
        $validate = new Validate;
        if (!$validate->scene('userExpress')->check($data)) {
            return WSTReturn($validate->getError());
        }
        // 检查售后单是否存在
        $has = $this->checkOrderService(0,$uId);
        if(!$has)return WSTReturn(lang('after_sales_order_not_exist'));
        if($has['serviceStatus']!=1)return WSTReturn(lang('after_sales_order_status_changed'));
        Db::startTrans();
        try{
            unset($data['id']);
            // 用户已发货
            $data['isUserSendGoods'] = 1;
            // 售后单状态改为2，等待商家收货
            $data['serviceStatus'] = 2;

            // 商家确认收货期限
           $shopReceiveDays = (int)WSTConf('CONF.shopReceiveDays');
           $data['shopReceiveExpireTime'] = date('Y-m-d H:i:s', time()+$shopReceiveDays*24*60*60);
           $logJson = '';
            $rs = $this->field('shopReceiveExpireTime,expressType,expressId,expressNo,isUserSendGoods,serviceStatus')->where(['id'=>$serviceId])->update($data);
            if($rs===false)return WSTReturn(lang('operation_fail'));

            if($data['expressType']==0){
                $logJson = json_encode(['type'=>'lang','key'=>'buyer_returns_no_express']);
            }else{
                $expressName = Db::name('express')
                            ->alias('e')
                            ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                            ->where(['e.expressId'=>$data['expressId']])->value('el.expressName');
                $logJson = json_encode(['type'=>'lang_params','key'=>'buyer_express_tips','params'=>[$expressName,$data['expressNo']]]);
            }
            // 写入日志
            $log = [
                'logTime'=>date('Y-m-d H:i:s'),
                'orderId'=>$has['orderId'],
                'serviceId'=>$serviceId,
                'logTargetId'=>$userId,
                'logType'=>0,
                'logJson'=>$logJson
            ];
            $rs = Db::name('log_services')->insert($log);
            if($rs===false)return WSTReturn(lang('operation_fail'));

            // 给商家发送商城消息提醒
            $this->sendMessage(0, $has["shopId"], $serviceId, lang('user_has_deliverd'));



            Db::commit();
            return WSTReturn(lang('operation_success'),1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail'));
        }
    }
    /**
     * 检测售后单是否属于该对象
     * @param type 0:用户  1:商家
     */
    public function checkOrderService($type=0,$uId=0,$sId=0){
        $id = (int)input('id');
        $userId = $uId==0?(int)session('WST_USER.userId'):$uId;
        $where = ['userId'=>$userId, 'os.id'=>$id];
        if($type==1){
            $shopId = $sId==0?(int)session('WST_USER.shopId'):$sId;
            $where = ['shopId'=>$shopId, 'os.id'=>$id];
        }
        // 检查售后单是否属于该用户
        $has = Db::name('orders')->alias('o')
                                 ->join('order_services os','os.orderId=o.orderId')
                                 ->where($where)
                                 ->find();
        if(empty($has)){
            return false;
        }
        return $has;
    }
    // 提交售后申请
    public function commit($uId=0){
        $orderId = (int)input('orderId');
        hook("beforeOrderservicesCommit",["uId"=>$uId,"orderId"=>$orderId]);
        $data = input('param.');
        $ids = input('ids');
        $goodsServiceType = (int)input('goodsServiceType');

        $has = Db::name('orders')->where('orderId', $orderId)->find();
        if(empty($has))return WSTReturn(lang('invalid_order_information'));
        if($has['payType']==0)return WSTReturn(lang('cash_delivery_not_apply_after_sale'));

        // 查询商品类型
        $goodsType = Db::name('order_goods')->where(['orderId'=>$has['orderId']])->value('goodsType');
        if($goodsType==1 && $goodsServiceType!=1){
            return WSTReturn(lang('virtual_goods_service_type_tips'));
        }

        // 订单类型：【商品组合】订单若为【固定搭配】则只能整单退换
        if($has['orderCode']=='combination' &&  isset($has['extraJson'])){
            $extraJson = json_decode($has['extraJson'], true);
            if($extraJson['combineType']==1){
                // 是否存在已申请但未走完售后流程的“换货”售后单
                $pendingSwitchCount = Db::name('order_services')->where(['goodsServiceType'=>2, 'isClose'=>0, 'orderId'=>$orderId])
                                                                 ->where('serviceStatus not in (5,6)')
                                                                 ->count();
                // 存在未完成的“退款”售后单
                $pendingRefundCount = Db::name('order_services')->where(['isClose'=>0, 'orderId'=>$orderId])
                                                                 ->where('goodsServiceType <> 2')
                                                                 ->where('serviceStatus not in (5,6)')
                                                                 ->count();
                if(($pendingSwitchCount+$pendingRefundCount)>0){
                    // 【商品组合-固定搭配】
                    return WSTReturn(lang('curr_order_exist_uncomplete_apply'));
                }
                // 存在已完成的“退款”售后单
                $osCount = Db::name('order_services')->where(['isClose'=>0, 'orderId'=>$orderId])
                                                     ->where('goodsServiceType <> 2')
                                                     ->where('serviceStatus in (5,6)')
                                                     ->count();
                if($osCount>0){
                    return WSTReturn(lang('curr_order_has_submit_apply'));
                }
                if($goodsServiceType!=2){// 固定搭配&&仅退款（退款退货）
                    // 获取订单商品id
                    $ogIds = Db::name('order_goods')->where(['orderId'=>$has['orderId']])->column('id');
                    if(count(explode(",", $ids))!=count($ogIds)){
                        return WSTReturn(lang('packages_must_refunded_all'));
                    }
                    $ids = implode(",", $ogIds);
                }
            }
        }
        $validate = new Validate;
        if (!$validate->scene('commit')->check($data)) {
            return WSTReturn($validate->getError());
        }
        if($ids=='')return WSTReturn(lang('require_order_services_goods_limit'));
        $rs = [];
        /**
         * 检测是否允许提交售后单
         * 1.检查数量
         * 2.检查时间
         * 3.检测退款金额是否超额
         */
        // 检测传递过来的退款金额是否超额
        $_tmp = explode(',', $ids);
        $numArr = [];
        foreach($_tmp as $id){
            $numArr['num_'.$id] = (int)input('goodsNum_'.$id);
        }
        $rs = $this->getRefundableMoney($uId, $ids, $numArr);
        if($rs['status']!=1)return $rs;
        $refundMoney = (float)input('refundMoney');
        $refundableMoney = $rs['data']['totalMoney'];
        if($refundableMoney<$refundMoney)return WSTReturn(lang('refund_amount_not_exceed',[$refundableMoney]));
        $rs = $rs['data'];
        if($goodsServiceType==0){
            // 退货退款
            return $this->refunds($uId, $rs);
        }else if($goodsServiceType==1){
            // 退款
            return $this->refunds($uId, $rs);
        }else if($goodsServiceType==2){
            // 换货
            return $this->refunds($uId);
        }else{
            return WSTReturn(lang('illegal_status_value'));
        }
    }
    // 退货退款
    /**
     * @param $uId 用户id
     * @param $rs 查看 this->commit方法
     */
    private function refunds($uId=0,  $rs=[]){
        $userId = $uId==0?(int)session('WST_USER.userId'):$uId;
        $data = input('param.');
        $orderId = (int)input('orderId');
        $ids = input('ids');
        $ids = explode(',', $ids);
        // 1.构造数据新增记录
        $now = date('Y-m-d H:i:s');
        Db::startTrans();
        try{
           unset($data['id']);
           $orders = Db::name('orders')->field('areaId, areaIdPath, userName, userAddress, userPhone, afterSaleEndTime, shopId')->where('orderId', $orderId)->find();
           $data['createTime'] = $now;
           // 售后申请状态
           $data['serviceStatus'] = 0;
           // 退还积分
           $data['refundScore'] = isset($rs['totalScore'])?$rs['totalScore']:0;
           // 售后单可退款金额
           $data['refundableMoney'] = isset($rs['totalMoney'])?$rs['totalMoney']:0;
           // 使用的积分可抵扣金额
           $data['useScoreMoney'] = isset($rs['useScoreMoney'])?$rs['useScoreMoney']:0;
           // 获得的积分
           $data['getScoreVal'] = isset($rs['totalGetScore'])?$rs['totalGetScore']:0;
           // 获得的积分可抵扣金额
           $data['getScoreMoney'] = isset($rs['getScoreMoney'])?$rs['getScoreMoney']:0;

           // 商家受理期限
           $shopAcceptDays = (int)WSTConf('CONF.shopAcceptDays');
           $data['shopAcceptExpireTime'] = date('Y-m-d H:i:s', time()+$shopAcceptDays*24*60*60);
           $rs = Db::name('order_services')->field(true)->insert( array_merge($data, $orders) );
           if($rs===false)return WSTReturn(lang('apply_services_fail'));
           $serviceId = Db::name('order_services')->getLastInsID();
           if($serviceId==0)return WSTReturn(lang('order_services_data_exception'));
           // 延长订单售后结束日期
           $limitDay = (int)WSTConf('CONF.afterSaleServiceDays');
           $afterSaleEndTime = date('Y-m-d H:i:s', strtotime($orders['afterSaleEndTime'])+$limitDay*60*60*24);
           $rs = Db::name('orders')->where('orderId', $orderId)->setField(['afterSaleEndTime'=>$afterSaleEndTime]);
           if($rs===false)return WSTReturn(lang('apply_services_fail'));

           // 售后商品表记录
            $serviceGoods = [];
            foreach($ids as $ogId){
                $key = 'goodsNum_'.$ogId;
                $num = (int)input($key);
                if($num>0){
                    // 查goodsId、goodsSpecId
                    $goods = Db::name('order_goods')->where(['id'=>$ogId])->find();
                    $serviceGoods[] = [
                        'createTime'=>$now,
                        'dataFlag'=>1,
                        'orderId'=>$orderId,
                        'goodsId'=>$goods['goodsId'],
                        'goodsSpecId'=>$goods['goodsSpecId'],
                        'goodsNum'=>$num,
                        'serviceId'=>$serviceId
                        ];
                }
            }
            $rs = Db::name('service_goods')->insertAll($serviceGoods);
            if($rs===false)return WSTReturn(lang('apply_services_fail'));
            $refundMoney = (float)input('refundMoney');
            // 写入日志
            $logJson = json_encode(['type'=>'lang_params','key'=>'order_service_apply_type_1','params'=>[$data['serviceRemark'],$refundMoney]]);
            if($data['goodsServiceType']==1){
                $logJson = json_encode(['type'=>'lang_params','key'=>'order_service_apply_type_2','params'=>[$data['serviceRemark'],$refundMoney]]);
            }else if($data['goodsServiceType']==2){
                $logJson = json_encode(['type'=>'lang_params','key'=>'order_service_apply_type_3','params'=>[$data['serviceRemark']]]);
            }
            $log = [
                'logTime'=>$now,
                'orderId'=>$orderId,
                'serviceId'=>$serviceId,
                'logTargetId'=>$userId,
                'logType'=>0,
                'logJson'=>$logJson
            ];
            $rs = Db::name('log_services')->insert($log);
            if($rs===false)return WSTReturn(lang('apply_services_fail'));

            // 给商家发送商城消息提醒
            $this->sendMessage(0, $orders["shopId"], $serviceId, lang('wait_shop_handle'));

            Db::commit();
            return WSTReturn(lang('operation_success'), 1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('apply_services_fail').$e->getMessage());
        }
    }
}
