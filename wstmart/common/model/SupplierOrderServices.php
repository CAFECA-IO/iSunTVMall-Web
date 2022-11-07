<?php
namespace wstmart\common\model;
use think\Db;
use wstmart\common\validate\SupplierOrderServices as Validate;
/**
 * ============================================================================
 * WSTMart多买家商城
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
class SupplierOrderServices extends Base{
    /**
     * 发送商城消息提醒
     * @param userId 用户id
     * @param supplierId 店铺id
     * @param serviceId 售后表主键id
     * @param 当前状态
     */
    public function sendMessage($userId, $supplierId, $serviceId, $dealResult){
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
                // 给【供货商】发送商城消息提醒
                // $msg = array();
                // $msg["supplierId"] = $supplierId;
                // $msg["tplCode"] = $tpl["tplCode"];
                // $msg["msgType"] = 1;
                // $msg["content"] = $content;
                // $msg["msgJson"] = $msgJson;
                // model("common/MessageQueues")->add($msg);
                $supplierUserId = Db::name('supplier_users')->where(['supplierId'=>$supplierId])->value('userId');
                WSTSendMsg($supplierUserId, $content, $msgJson);
            }
        }
    }
    /**
     * 定时任务
     */
    public function crontab(){
        /**
         * 买家提交申请售后之后写入（supplierAcceptExpireTime）卖家受理期限，逾期不处理自动关闭售后单（售后日志写明“逾期不受理”）
            仅退款售后单受理之后不做任何写入。
            仅换货、退货退款售后单受理之后写入（userSendExpireTime）买家发货期限，逾期不处理自动关闭（售后日志写明“买家逾期未处理”）
            买家发货之后写入（supplierReceiveExpireTime）卖家收货期限，逾期不处理自动收货。
         */
        // 查询未结束的售后单
        $now = date('Y-m-d H:i:s');
        // 1.处理卖家逾期受理
        $where = [['isClose','=',0],['serviceStatus','=',0],['supplierAcceptExpireTime','<',$now]];
        $rs = $this->where($where)->select();
        $logs = [];
        $logJson = json_encode(['type'=>'lang','key'=>'shop_overdue_not_accepted_tips']);
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
        Db::name('supplier_log_services')->insertAll($logs);
        // 发送商城消息给用户
        foreach($sIds as $serviceId){
            $userId = $this->alias("os")
                           ->join("__ORDERS__ o", "o.orderId=os.orderId","inner")
                           ->where(["os.id"=>$serviceId])
                           ->value("o.userId");
            $this->sendMessage($userId, 0, $serviceId, $logContent);
        }


        // 2.处理买家逾期发货
        $where = [['isClose','=',0],['serviceStatus','=',1],['userSendExpireTime','<',$now]];
        $rs = $this->where($where)->select();
        $logs = [];
        $logJson = json_encode(['type'=>'lang','key'=>'user_overdue_not_deliver_tips']);
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
        Db::name('supplier_log_services')->insertAll($logs);


        // 3.处理卖家逾期未确认收货
        $where = [['isClose','=',0],['serviceStatus','=',2],['supplierReceiveExpireTime','<',$now]];
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
                'logJson'=>json_encode(['type'=>'lang','key'=>'user_overdue_not_receiving_tips'])
                ]
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
            Db::name('supplier_log_services')->insertAll($logs);

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
             $this->sendMessage($userId, 0, $serviceId, $logContent);
         }

		// 5.处理用户逾期未去确认收货
        $where = [['isClose','=',0], ['isSupplierSend','=',1], ['serviceStatus','=',4],['userReceiveExpireTime','<',$now]];
        $rs = $this->where($where)->select();

        foreach($rs as $k=>$v){
            // 将售后单状态设置为 【用户已收货】
            $this->where(['id'=>$v['id']])->update(['serviceStatus'=>5]);
            $logs = [
                ['logTime'=>$now,
                'orderId'=>$v['orderId'],
                'serviceId'=>$v['id'],
                'logTargetId'=>0,
                'logType'=>2,
                'logJson'=>json_encode(['type'=>'lang','key'=>'user_overdue_not_receiving_tips'])
                ]
            ];
            Db::name('supplier_log_services')->insertAll($logs);
        }

        return WSTReturn(lang('add_success'), 1);
    }


    /**
     * 卖家发货
     */
    public function supplierSend(){
        $serviceId = (int)input('id');
        hook('beforeSupplierOrderServicesSend',["serviceId"=>$serviceId]);
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $has = $this->checkOrderService(1,0,$supplierId);
        if(!$has)return WSTReturn(lang('invalid_after_sales_order'));
        $data = input('param.');
        $validate = new Validate;
        if (!$validate->scene('supplierSend')->check($data)) {
            return WSTReturn($validate->getError());
        }

        Db::startTrans();
        try{
            unset($data['id']);
            // 卖家已发货
            $data['isSupplierSend'] = 1;
            // 售后单状态改为4，等待买家确认收货
            $data['serviceStatus'] = 4;

            // 用户收货期限
            $userReceiveDays = (int)WSTConf('CONF.userReceiveDays');
            $data['userReceiveExpireTime'] = date('Y-m-d H:i:s', time()+$userReceiveDays*24*60*60);

            $rs = $this->field('supplierExpressType,supplierExpressId,supplierExpressNo,isSupplierSend,serviceStatus,userReceiveExpireTime')->where(['id'=>$serviceId])->update($data);
            if($rs===false)return WSTReturn(lang('operation_fail'));
            $logJson = '';
            if($data['supplierExpressType']==0){
                $logJson = json_encode(['type'=>'lang','key'=>'shop_deliver_no_express_tips']);
            }else{
                $expressName = Db::name('express')
                                ->alias('e')
                                ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                                ->where(['e.expressId'=>$data['supplierExpressId']])->value('el.expressName');
                $logJson = json_encode(['type'=>'lang','key'=>'shop_deliver_with_express','remark'=>$expressName.' - '.$data['supplierExpressNo']]);
            }
            // 写入日志
            $log = [
                'logTime'=>date('Y-m-d H:i:s'),
                'orderId'=>$has['orderId'],
                'serviceId'=>$serviceId,
                'logTargetId'=>$supplierId,
                'logType'=>1,
                'logJson'=>$logJson
            ];
            $rs = Db::name('supplier_log_services')->insert($log);
            if($rs===false)return WSTReturn(lang('operation_fail'));

            //发送一条用户信息
            $dealResult = WSTLang('shop_deliver_with_express');
            $this->sendMessage($has['userId'], 0, $serviceId, $dealResult);

            Db::commit();
            return WSTReturn(lang('add_success'),1);
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
            hook('beforeSupplierMakeRefundData',['orderId'=>$orderId,"serviceId"=>$serviceId]);
            //修改卖家未结算金额
            $order = Db::name("supplier_orders")->where(["orderId"=>$orderId])
                                       ->field("orderId,supplierId,realTotalMoney,refundedPayMoney")
                                        ->find();
            $supplierId = $order["supplierId"];
            $where = [];
            $where[] = ["sg.serviceId","=",$serviceId];
            $where[] = ["sg.orderId","=",$orderId];
            $where[] = ["sg.dataFlag","=",1];
            $list = Db::name("supplier_service_goods sg")
                    ->join("supplier_order_goods og","og.orderId=sg.orderId and og.goodsId=sg.goodsId and og.goodsSpecId=sg.goodsSpecId","inner")
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
             */
            $osData = Db::name('supplier_order_services')->where('id',$serviceId)->find();

            // 累加已退还的金额、积分、积分可抵扣金额、获得的积分可抵扣的金额
            Db::name('supplier_orders')->where(["orderId"=>$orderId])->field("refundedPayMoney")->update([
                'refundedPayMoney'=>$order['refundedPayMoney']+$refundMoney
            ]);
            foreach ($list as $key => $vo) {
                $goodsNum = $vo["goodsNum"];
                $o_money = $vo["orderGoodscommission"];
                $o_goodsNum = $vo["o_goodsNum"];
                $avg_money = WSTBCMoney($o_money/$o_goodsNum,0);


                $backMoney = WSTBCMoney($avg_money*$goodsNum,0);
                //修改订单总佣金
                Db::name("supplier_orders")->where(['orderId'=>$orderId])->update([
                                'commissionFee'=>Db::raw('commissionFee-'.$backMoney)
                            ]);
                //修改卖家未结算佣金
                Db::name("suppliers")->where(['supplierId'=>$supplierId])->update([
                        'noSettledOrderFee'=>Db::raw('noSettledOrderFee+'.$backMoney)
                    ]);
            }

            $refundData = [
                'orderId'=>$orderId,
                'refundTo'=>0,
                // 申请退款id
                'refundReson'=>'10000',
                'refundOtherReson'=>'售后单退款',
                // 退款金额
                'backMoney'=>$refundMoney,
                'createTime'=>date('Y-m-d H:i:s'),
                // 退款状态-> 1：卖家同意
                'refundStatus'=>1,
                // 售后单Id
                'serviceId'=>$serviceId
            ];
            Db::commit();
            return Db::name('supplier_order_refunds')->insert($refundData);
        }catch(\Exception $e){
            Db::rollback();
            return false;
        }


    }
    /**
     * 卖家确认收货
     */
    public function supplierReceive(){
        $serviceId = (int)input('id');

        hook('beforeSupplierOrderServicesReceive',["serviceId"=>$serviceId]);
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $has = $this->checkOrderService(1,0,$supplierId);
        if(!$has)return WSTReturn(lang('invalid_after_sales_order'));
        $data = input('param.');
        $validate = new Validate;
        if (!$validate->scene('supplierComfirm')->check($data)) {
            return WSTReturn($validate->getError());
        }
        try{
            Db::startTrans();
            unset($data['id']);
            $logJson = '';
            $where = ['id'=>$serviceId];
            $field = ['serviceStatus', 'isSupplierAccept', 'shopSendExpireTime'];
            if($data['isSupplierAccept']==1){
                // 确认收货
                if($has['goodsServiceType']==0){// 退款退货
                     // 状态修改为"卖家确认收货"
                     $data['serviceStatus'] = 6;
                     // 生成退款订单
                     $rdRs = $this->makeRefundData($has['orderId'], $has['refundMoney'], $serviceId);
                     if($rdRs===false)return WSTReturn(lang('create_refund_order_fail'));

                }else if($has['goodsServiceType']==2){// 换货
                   // 状态值修改为"等待卖家发货"
                   $data['serviceStatus'] = 3;
                   // 商家发货期限 shopSendExpireTime
                   $shopSendDays = (int)WSTConf('CONF.shopSendDays');
                   $data['shopSendExpireTime'] = date('Y-m-d H:i:s', time()+$shopSendDays*24*60*60);
                }
                $logJson = json_encode(['type'=>'lang','key'=>'shop_confirms_receipt']);
            }else{
                // 标记售后单结束
                $data['isClose'] = 1;
                $field = array_merge($field, ['supplierRejectType','supplierRejectOther','supplierRejectImg', 'isClose']);
                if(!WSTCheckDatas('ORDER_SERVICES_SHOP_REJECT',$data['supplierRejectType']))return WSTReturn(lang('invalid_reject_type'));
                // 拒收类型文字
                $rejectText = WSTDatas('ORDER_SERVICES_SHOP_REJECT', $data['supplierRejectType']);
                if(empty($rejectText))return WSTReturn(lang('invalid_reject_type'));
                // 拒收
                $logJson = json_encode(['type'=>'lang_data','key'=>'shop_reject_type_tips','datakey'=>'ORDER_SERVICES_SHOP_REJECT','dataval'=>$data['supplierRejectType']]);
                if($data['supplierRejectType']=='10000'){
                    $logJson = json_encode(['type'=>'lang_data','key'=>'shop_reject_type_tips','datakey'=>'ORDER_SERVICES_SHOP_REJECT','dataval'=>$data['supplierRejectType'],'remark'=>$data['supplierRejectOther']]);
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
                'logTargetId'=>$supplierId,
                'logType'=>1,
                'logJson'=>$logJson]
            ];
            // 处理结果
            $dealResult = WSTLang('order_service_status_9');
            if($data['isSupplierAccept']==1 && $has['goodsServiceType']==0){// 卖家确认收货
                // 退款退货
                $log[] = ['logTime'=>$now,
                          'orderId'=>$has['orderId'],
                          'serviceId'=>$serviceId,
                          'logTargetId'=>0,
                          'logType'=>2,
                          'logJson'=>json_encode(['type'=>'lang','key'=>'wait_sys_refund'])];
                $dealResult .=  ",".WSTLang('wait_sys_refund');
            }else if($data['isSupplierAccept']==-1){
                // 卖家拒收、售后单结束
                $log[] = ['logTime'=>$now,
                          'orderId'=>$has['orderId'],
                          'serviceId'=>$serviceId,
                          'logTargetId'=>0,
                          'logType'=>2,
                          'logJson'=>json_encode(['type'=>'lang','key'=>'shop_reject_service_closed'])
                         ];
                $dealResult = WSTLang('shop_reject_receiving');
            }

            $rs = Db::name('supplier_log_services')->insertAll($log);
            if($rs===false)return WSTReturn(lang('operation_fail'));

            //发送一条用户信息
            $this->sendMessage($has['userId'], 0, $serviceId, $dealResult);

            Db::commit();
            return WSTReturn(lang('add_success'),1);
        }catch(\Exception $e){
            //print_r($e);
            Db::rollback();
            return WSTReturn(lang('operation_fail').$e->getMessage());
        }
    }
    // 卖家处理退款申请
    public function dealRefund(){
        $data = input('param.');
        $validate = new Validate;
        if (!$validate->scene('refund')->check($data)) {
            return WSTReturn($validate->getError());
        }
        $serviceId = (int)input('id');
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        $has = $this->checkOrderService(1,0,$supplierId);
        if(empty($has)){
            return WSTReturn(lang('after_sales_order_not_exist'));
        }
        try{
            Db::startTrans();
            $isSupplierAgree = (int)input('isSupplierAgree');
            unset($data['id']);
            $now = date('Y-m-d H:i:s');
            $dealResult = lang('shop_agree_refund');

            if($isSupplierAgree==1){
                // 生成退款订单
                $rdRs = $this->makeRefundData($has['orderId'], $has['refundMoney'], $serviceId);
                if($rdRs===false)return WSTReturn(lang('create_refund_order_fail'));
                // 受理
                $data['serviceStatus'] = 7;
                $rs = Db::name('supplier_order_services')->field('isSupplierAgree,serviceStatus')
                                                ->where(['id'=>$serviceId])->update($data);
                if($rs===false)return WSTReturn(lang('operation_fail'));
                // 写入日志
                $logJson = json_encode(['type'=>'lang','key'=>'shop_agree_refund']);
                $log = [
                    ['logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$supplierId,
                    'logType'=>1,
                    'logJson'=>json_encode(['type'=>'lang','key'=>'shop_agree_curr_service_apply'])],
                    ['logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$supplierId,
                    'logType'=>1,
                    'logJson'=>$logJson],
                    ['logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>0,
                    'logType'=>2,
                    'logJson'=>json_encode(['type'=>'lang','key'=>'wait_sys_refund'])]
                ];
                $dealResult .=  '，'.WSTLang('wait_sys_refund');

                $rs = Db::name('supplier_log_services')->insertAll($log);
                if($rs===false)return WSTReturn(lang('operation_fail'));
            }else{
                if(!isset($data['disagreeRemark']) && strlen($data['disagreeRemark'])==0)return WSTReturn(lang('require_not_accepte_reasons'));
                // 不受理
                $data['isClose'] = 1;
                $rs = Db::name('supplier_order_services')->field('isSupplierAgree,disagreeRemark,isClose')
                                                ->where(['id'=>$serviceId])->update($data);
                if($rs===false)return WSTReturn(lang('operation_fail'));

                // 写入日志
                $log = [
                    'logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$supplierId,
                    'logType'=>1,
                    'logJson'=>json_encode(['type'=>'lang_params','key'=>'shop_reject_curr_service_apply_reason','params'=>[$data['disagreeRemark']]])
                ];
                $dealResult =  WSTLang('shop_reject_curr_service_apply');

                $rs = Db::name('supplier_log_services')->insert($log);
                if($rs===false)return WSTReturn(lang('operation_fail'));
            }

            //发送一条用户信息
            $this->sendMessage($has['userId'], 0, $serviceId, $dealResult);

            Db::commit();
            return WSTReturn(lang('add_success'),1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail').$e->getMessage());
        }
    }

    // 卖家处理售后申请
    public function dealApply(){
        $serviceId = (int)input('id');
        $supplierId = (int)session('WST_SUPPLIER.supplierId');
        hook("beforeSupplierDealApply",["serviceId"=>$serviceId,"supplierId"=>$supplierId]);
        $data = input('param.');
        $validate = new Validate;
        if (!$validate->scene('deal')->check($data)) {
            return WSTReturn($validate->getError());
        }

        // 检查售后单是否属于该卖家
        $has = Db::name('supplier_orders')->alias('o')
                                 ->join('supplier_order_services os','os.orderId=o.orderId')
                                 ->where(['supplierId'=>$supplierId,'id'=>$serviceId])
                                 ->find();
        if(empty($has)){
            return WSTReturn(lang('after_sales_order_not_exist'));
        }
        try{
            Db::startTrans();
            $isSupplierAgree = (int)input('isSupplierAgree');
            unset($data['id']);
            $now = date('Y-m-d H:i:s');
            // 商家处理结果
            $dealResult = "";

            if($isSupplierAgree==1){
                // 受理
                // 等待买家发货
                $data['serviceStatus'] = 1;

                // 买家发货期限
                $userSendDays = (int)WSTConf('CONF.userSendDays');
                $data['userSendExpireTime'] = date('Y-m-d H:i:s', time()+$userSendDays*24*60*60);

                $rs = Db::name('supplier_order_services')->field('userSendExpireTime,isSupplierAgree,supplierAddress,supplierName,supplierPhone,serviceStatus')
                                                ->where(['id'=>$serviceId])->update($data);
                if($rs===false)return WSTReturn(lang('operation_fail'));
                // 写入日志
                $logJson =  json_encode(['type'=>'lang_params','key'=>'shop_receiving_address','params'=>[$data['supplierName'],$data['supplierPhone'],$data['supplierAddress']]]);;
                $log = [
                    ['logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$supplierId,
                    'logType'=>1,
                    'logJson'=>json_encode(['type'=>'lang','key'=>'shop_agree_curr_service_apply'])],
                    ['logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$supplierId,
                    'logType'=>1,
                    'logJson'=>$logJson]
                ];

                $dealResult = json_encode(['type'=>'lang','key'=>'shop_agree_curr_service_apply']);

                $rs = Db::name('supplier_log_services')->insertAll($log);
                if($rs===false)return WSTReturn(lang('operation_fail'));
            }else{
                // 不受理
                $data['isClose'] = 1;
                $rs = Db::name('supplier_order_services')->field('isSupplierAgree,disagreeRemark,isClose')
                                                ->where(['id'=>$serviceId])->update($data);
                if($rs===false)return WSTReturn(lang('operation_fail'));
                // 写入日志
                $log = [
                    'logTime'=>$now,
                    'orderId'=>$has['orderId'],
                    'serviceId'=>$serviceId,
                    'logTargetId'=>$supplierId,
                    'logType'=>1,
                    'logJson'=>json_encode(['type'=>'lang_params','key'=>'shop_reject_curr_service_apply_reason','params'=>[$data['disagreeRemark']]])
                ];

                $dealResult = json_encode(['type'=>'lang','key'=>'shop_reject_curr_service_apply']);

                $rs = Db::name('supplier_log_services')->insert($log);
                if($rs===false)return WSTReturn(lang('operation_fail'));
            }

            //发送一条用户信息
            $this->sendMessage($has['userId'], 0, $serviceId, $dealResult);

            Db::commit();
            return WSTReturn(lang('add_success'),1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail').$e->getMessage());
        }
    }
    // 获取协商日志
    public function getLog($shopId=0){
        $serviceId = (int)input('id');
        $has = Db::name('supplier_order_services')->alias('os')
                                  ->join('supplier_orders o','o.orderId=os.orderId')
                                  ->where(['o.shopId'=>$shopId,'os.id'=>$serviceId])
                                  ->find();
        if(empty($has))return [];


        $rs = Db::name('supplier_log_services')->where(['serviceId'=>$serviceId])->order('logId desc')->select();

        $userPhoto = session('WST_USER.userPhoto');
        $userName = session('WST_USER.loginName');

        if(!empty($rs)){
            // 取头像及名称
            $supplierInfo = [];
            foreach($rs as $k=>$v){
                if($v['logType']==0){
                    // 取买家头像及名称
                    $rs[$k]['avatar'] = $userPhoto;
                    $rs[$k]['nickname'] = $userName;
                }else if($v['logType']==1){
                    if(empty($supplierInfo)){
                        $supplierInfo = model('suppliers')->getFieldsById($v['logTargetId'],['supplierName','supplierImg']);
                    }
                    // 取卖家头像及名称
                    $rs[$k]['avatar'] = $supplierInfo['supplierImg'];
                    $rs[$k]['nickname'] = $supplierInfo['supplierName'];
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
     * @param $type 0:买家 1:卖家
     */
    public function getDetail($type=0, $shopId=0, $supplierId=0){

        $serviceId = (int)input('id');
        $where = ['s.id'=>$serviceId,'o.shopId'=>$shopId];
        if($type==1){
            unset($where['o.shopId']);
            $where['o.supplierId'] = $supplierId;
        }
        $has = $this->checkOrderService($type, $shopId,$supplierId);
        if(!$has)return WSTReturn(lang('after_sales_order_not_exist'));
        // 查询
        $rs = $this->alias('s')
                   ->join('supplier_orders o','o.orderId=s.orderId','inner')
                   ->where($where)
                   ->field('s.*,o.orderNo')
                   ->find();
        if(!empty($rs)){
            // 查询服务单下的商品信息
            $rs['glists'] = Db::name('supplier_service_goods')->alias('sg')
                                                     ->join('supplier_order_goods og','og.goodsId=sg.goodsId and og.goodsSpecId=sg.goodsSpecId and og.goodsCode=""','inner')
                                                     ->where(['sg.serviceId'=>$serviceId,'og.orderId'=>$has['orderId']])
                                                     ->field('sg.serviceId,og.*,sg.goodsNum')
                                                     ->select();
            // 售后单类型
            $rs['goodsServiceTypeText'] = $this->goodsServiceTypeText($rs['goodsServiceType']);
            // 售后单状态
            $rs['statusText'] = $rs['isClose']==1?lang('order_service_closed'):$this->getStatus($rs['serviceStatus'], $rs['goodsServiceType']);
            // 申请原因
            $rs['serviceTypeText'] = $this->getServiceTypeText($rs['serviceType']);
            // 买家快递公司
            if($rs['isUserSendGoods']==1 && $rs['expressType']==1 ){
                $rs['expressName'] = Db::name('express')
                                    ->alias('e')
                                    ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                                    ->where(['e.expressId'=>$rs['expressId']])
                                    ->value('el.expressName');
            }
            // 卖家物流公司
            if($rs['isSupplierSend']==1 && $rs['supplierExpressType']==1 ){
                $rs['supplierExpressName'] = Db::name('express')
                                            ->alias('e')
                                            ->join('__EXPRESS_LANGS__ el','el.expressId=e.expressId and el.langId='.WSTCurrLang())
                                            ->where(['e.expressId'=>$rs['supplierExpressId']])
                                            ->value('el.expressName');
            }
            // 若为卖家则查询上一次填写的地址信息
            if($type==1){
                $supplierId = $where['o.supplierId'];
                $lastData = Db::name('supplier_order_services')->alias('os')
                                                     ->join('supplier_orders o','o.orderId=os.orderId','inner')
                                                     ->where(['o.supplierId'=>$supplierId])
                                                     ->where("os.id !={$serviceId} and supplierAddress!='' ")
                                                     ->order('os.createTime desc')
                                                     ->find();
                $rs['lastSupplierAddress'] = $lastData['supplierAddress'];
                $rs['lastSupplierName'] = $lastData['supplierName'];
                $rs['lastSupplierPhone'] = $lastData['supplierPhone'];
            }
        }
        return $rs;
    }
    /**
     * 售后列表查询
     * @param $type 0:买家 1:卖家
     */
    public function pageQuery($type=0, $shopId=0, $supplierId=0){

        $orderNo = (int)input('orderNo');
        $where = [
            'o.shopId'=>$shopId,
            // 确认收货之后才能申请售后
            'o.orderStatus'=>2,
            'o.dataflag'=>1,
        ];
        if($type==1){
            unset($where['o.shopId']);
            $where['o.supplierId'] = $supplierId;
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
                   ->join('supplier_orders o','os.orderId=o.orderId','inner')
                   ->where($where)
                   ->where($where2)
                   ->field('o.orderNo,os.*')
                   ->order($order)
                   ->paginate()
                   ->toArray();
        if(!empty($rs['data'])){
            // 查询售后单下的商品
            foreach($rs['data'] as $k=>$v){
                $imgs = Db::name('supplier_order_goods')->alias('og')
                                               ->join('supplier_service_goods sg','sg.goodsId=og.goodsId and sg.goodsSpecId=og.goodsSpecId and og.goodsCode=""','inner')
                                               ->where(['sg.serviceId'=>$v['id'],'og.orderId'=>$v['orderId']])
                                               ->column('og.goodsImg');
                if(!empty($imgs)){
                    $imgs = array_map(function($item){return WSTImg($item,1);},$imgs);
                }
                $rs['data'][$k]['gImgs'] = $imgs;


                // 查询服务单下的商品信息
                $rs['data'][$k]['glists'] = Db::name('supplier_service_goods')->alias('sg')
                ->join('supplier_order_goods og','og.goodsId=sg.goodsId','inner')
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
        // 状态备注：0：待卖家审核  1： 2： 3：  4：  5：  6：
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
     * 前端买家勾选商品或改变数量时对可退款金额进行计算
     * @param orderId 订单id
     * @param ids supplier_order_goods表的主键id 例如：2,4,5,8
     * @param num_{$id} num_og表主键id 例如：num_2:1, num_4:3 表示2商品数量未1件 4商品数量为3件
     */
    public function getRefundableMoney($shopId=0, $ids='', $numArr=[]){
        $orderId = (int)input('orderId');
        $rs = ['totalMoney'=>0];
        $ids = input('ids',$ids);
        // 未传入og表主键
        if($ids=='')return WSTReturn('ok',1,$rs);
        $ids = explode(',', $ids);
        $where = [
            'o.shopId'=>$shopId,
            'o.orderId'=>$orderId,
        ];
        $orders = Db::name('supplier_orders')->alias('o')
                                    ->where($where)
                                    ->find();
        if(empty($orders))return WSTReturn('订单数据异常');
        $orderGoods = Db::name('supplier_order_goods')->where(['orderId'=>$orderId])->select();


        unset($v);

        // 运费不退
        $orders['realTotalMoney'] = $orders['realTotalMoney']-$orders['deliverMoney'];


        // 原可退款金额
        $originalRefundableMoney = $orders['totalMoney']-$orders['deliverMoney'];
        if($originalRefundableMoney<=0){
            // 原可退小于等于0
            return WSTReturn('ok',1,$rs);
        }
        // 是否有修改订单价格
        $isEditOrderMoney = $originalRefundableMoney!=$orders['realTotalMoney'];
        // 修改订单价格之后实际上可退款金额 = 实际支付金额
        $actuallyRefundableMoney = max(0, $orders['realTotalMoney']);

        // 取出订单下的商品进行遍历，计算出当前选中的商品单件可退多少，总共可退多少【需减免已经退款的商品件数】
        foreach($orderGoods as $k=>$v){
            if(in_array($v['id'], $ids)){
                // 在待退款列表中
                // 1.查询已退款的件数【该已提交的售后申请数量】
                $currSGoods = Db::name('supplier_service_goods')->alias('sg')
                                                        ->join('supplier_order_services os','os.id=sg.serviceId','inner')
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
                if($currSGoodsNum>($v['goodsNum']-(int)$currSGoods['totalNum']))return WSTReturn(lang('abnormal_return_quantity_tips'));
                if($currSGoodsNum<=($v['goodsNum']-(int)$currSGoods['totalNum'])){
                    // 计算单件可退价格【若为最后一件则 由 总共可退价格-已退还价格=可退价格 】

                    if($v['goodsNum']==1){ //只有一件
                        // 当前商品可退款金额 = 商品总价格-优惠券减免金额-满就送减免金额-积分抵扣金额-获得的积分换算成金额;
                        // 累加可退款金额
                        $rs['totalMoney'] += ($v['goodsPrice']);
                            // 确保可退款金额不小于0
                            if($rs['totalMoney']<0)$rs['totalMoney'] = 0;
                            // 确保可退款金额低于实际支付金额【保留两位小数】
                            if($isEditOrderMoney)$rs['totalMoney'] = round($rs['totalMoney']/$originalRefundableMoney*$actuallyRefundableMoney, 2);

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
                            $_currGoodsRefundableMoney = $v['goodsPrice']*$v['goodsNum'];
                            // 当前商品总共可退款金额
                            $_tmpTotalRefundableMoney = round($_currGoodsRefundableMoney/$originalRefundableMoney*$actuallyRefundableMoney, 2);
                            $avgRefundableMoney = round($_currGoodsRefundableMoney/$originalRefundableMoney*$actuallyRefundableMoney/$v['goodsNum'], 2);
                            $lastRefundableMoney = $_tmpTotalRefundableMoney-$avgRefundableMoney*($v['goodsNum']-1);
                        }


                        // 最后一件的值
                        $lastIndex = $v['goodsNum']-$currSGoods['totalNum'];
                        // 计算单件可退还价格
                        for($i=1;$i<=$currSGoodsNum;++$i){
                            if($i==$lastIndex){// 最后一件
                                // 累加可退款金额
                                $_tmpTotalMoney = ($v['goodsPrice']);
                                    // 确保可退款金额低于实际支付金额【保留两位小数】
                                    if($isEditOrderMoney)$_tmpTotalMoney = $lastRefundableMoney;
                                $rs['totalMoney'] += $_tmpTotalMoney;
                                    // 确保可退款金额不小于0
                                    if($rs['totalMoney']<0)$rs['totalMoney'] = 0;

                            }else{
                                // 累加可退款金额
                                $_tmpTotalMoney = ($v['goodsPrice']);
                                    // 确保可退款金额低于实际支付金额【保留两位小数】
                                    if($isEditOrderMoney)$_tmpTotalMoney = $avgRefundableMoney;
                                $rs['totalMoney'] += $_tmpTotalMoney;
                                    // 确保可退款金额不小于0
                                    if($rs['totalMoney']<0)$rs['totalMoney'] = 0;
                            }
                        }
                    }
                }
            }
        }
        // 若为货到付款则可退款金额为0
        if($orders['payType']==0){
            $rs['totalMoney'] = 0;
        }
        // 保留两位小数
        $rs['totalMoney'] = round($rs['totalMoney'], 2);
        return WSTReturn('ok',1,$rs);

    }
    // 获取订单下可申请售后的商品
    public function getGoods($shopId=0){
        $orderId = (int)input('orderId');
        $where = [
            'o.shopId'=>$shopId,
            'o.orderId'=>$orderId,
            // 确认收货之后才能申请售后
            'o.orderStatus'=>2,
            'o.dataflag'=>1,
            // 赠品不参与退换货
            'og.goodsCode'=>''
        ];
        // 订单中的商品
        $rs = Db::name('supplier_orders')->alias('o')
                                ->join('supplier_order_goods og','og.orderId=o.orderId','inner')
                                ->where($where)
                                ->field('*')
                                ->select();
        // 已经申请售后的商品
        $sIds = Db::name('supplier_order_services')->alias('os')->where(['isClose'=>0, 'orderId'=>$orderId])->column('id');
        if(!empty($sIds)){
            // 存在售后申请，查询已经申请售后的商品
            foreach($rs as $k=>$v){
                $currGoods = Db::name('supplier_service_goods')->whereIn('serviceId',$sIds)
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
    /****************************************** 买家操作售后单 ***************************************************/
    /**
     * 买家确认收货
     */
    public function userReceive($shopId=0,$userId=0){
        $data = input('param.');
        $serviceId = (int)input('id');
        $validate = new Validate;
        if (!$validate->scene('userConfirm')->check($data)) {
            return WSTReturn($validate->getError());
        }
        // 检查售后单是否存在
        $has = $this->checkOrderService(0,$shopId,0);
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
                    $logJson = json_encode(['type'=>'lang_data','key'=>'user_reject_type_tips','datakey'=>'ORDER_REJECT','dataval'=>$data['userRejectType'],'remark'=>$data['userRejectOther']]);
                }
                $data['isClose'] = 1;
                $dealResult = "用户拒收";
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
                // 买家拒收、售后单结束
                $log[] = ['logTime'=>$now,
                          'orderId'=>$has['orderId'],
                          'serviceId'=>$serviceId,
                          'logTargetId'=>0,
                          'logType'=>2,
                          'logJson'=>json_encode(['type'=>'lang','key'=>'user_reject_closed'])];
            }
            $rs = Db::name('supplier_log_services')->insertAll($log);

            if($rs===false)return WSTReturn(lang('operation_fail'));

             // 给商家发送商城消息提醒
             $this->sendMessage(0, $has["supplierId"], $serviceId, $dealResult);

            Db::commit();
            return WSTReturn(lang('add_success'),1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail').$e->getMessage());
        }
    }
    /**
     * 买家发货
     */
    public function userExpress($shopId=0,$userId=0){
        $data = input('param.');
        $serviceId = (int)input('id');
        $validate = new Validate;
        if (!$validate->scene('userExpress')->check($data)) {
            return WSTReturn($validate->getError());
        }
        // 检查售后单是否存在
        $has = $this->checkOrderService(0,$shopId,0);
        if(!$has)return WSTReturn(lang('after_sales_order_not_exist'));
        if($has['serviceStatus']!=1)return WSTReturn(lang('after_sales_order_status_changed'));
        Db::startTrans();
        try{
            unset($data['id']);
            // 买家已发货
            $data['isUserSendGoods'] = 1;
            // 售后单状态改为2，等待卖家收货
            $data['serviceStatus'] = 2;

            // 卖家确认收货期限
           $supplierReceiveDays = (int)WSTConf('CONF.shopReceiveDays');
           $data['supplierReceiveExpireTime'] = date('Y-m-d H:i:s', time()+$supplierReceiveDays*24*60*60);

            $rs = $this->field('supplierReceiveExpireTime,expressType,expressId,expressNo,isUserSendGoods,serviceStatus')->where(['id'=>$serviceId])->update($data);
            if($rs===false)return WSTReturn(lang('operation_fail'));
            $logJson = '';
            if($data['expressType']==0){
                $logJson = json_encode(['type'=>'lang','key'=>'buyer_returns_no_express']);;
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
            $rs = Db::name('supplier_log_services')->insert($log);
            if($rs===false)return WSTReturn(lang('operation_fail'));

            // 给商家发送商城消息提醒
            $this->sendMessage(0, $has["supplierId"], $serviceId, lang('user_has_deliverd'));

            Db::commit();
            return WSTReturn(lang('add_success'),1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail'));
        }
    }
    /**
     * 检测售后单是否属于该对象
     * @param type 0:买家  1:卖家
     */
    public function checkOrderService($type=0,$shopId=0,$supplierId=0){
        $id = (int)input('id');
        $where = ['o.shopId'=>$shopId, 'os.id'=>$id];
        if($type==1){
            $where = ['supplierId'=>$supplierId, 'os.id'=>$id];
        }
        // 检查售后单是否属于该买家
        $has = Db::name('supplier_orders')->alias('o')
                                 ->join('supplier_order_services os','os.orderId=o.orderId')
                                 ->where($where)
                                 ->find();
        if(empty($has)){
            return false;
        }
        return $has;
    }
    // 提交售后申请
    public function commit($shopId=0,$userId=0){
        $data = input('param.');
        $ids = input('ids');
        $goodsServiceType = (int)input('goodsServiceType');
        $orderId = (int)input('orderId');
        $has = Db::name('supplier_orders')->where('orderId', $orderId)->find();
        if(empty($has))return WSTReturn(lang('invalid_order_information'));
        if($has['payType']==0)return WSTReturn(lang('cash_delivery_not_apply_after_sale'));
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
        $rs = $this->getRefundableMoney($shopId, $ids, $numArr);
        if($rs['status']!=1)return $rs;
        $refundMoney = (float)input('refundMoney');
        $refundableMoney = $rs['data']['totalMoney'];
        if($refundableMoney<$refundMoney)return WSTReturn(WSTLang('refund_amount_not_exceed',[$refundableMoney]));
        $rs = $rs['data'];

        if($goodsServiceType==0){
            // 退货退款
            return $this->refunds($userId, $rs);
        }else if($goodsServiceType==1){
            // 退款
            return $this->refunds($userId, $rs);
        }else if($goodsServiceType==2){
            // 换货
            return $this->refunds($userId);
            // return $this->exchange($uId);
        }else{
            return WSTReturn(lang('illegal_status_value'));
        }
    }
    // 退货退款
    /**
     * @param $uId 买家id
     * @param $rs 查看 this->commit方法
     */
    private function refunds($userId=0,  $rs=[]){
        $data = input('param.');
        $orderId = (int)input('orderId');
        $ids = input('ids');
        $ids = explode(',', $ids);
        // 1.构造数据新增记录
        $now = date('Y-m-d H:i:s');
        Db::startTrans();
        try{
           unset($data['id']);
           $orders = Db::name('supplier_orders')->field('areaId, areaIdPath, userName, userAddress, userPhone, afterSaleEndTime, supplierId')->where('orderId', $orderId)->find();
           $data['createTime'] = $now;
           // 售后申请状态
           $data['serviceStatus'] = 0;
           // 售后单可退款金额
           $data['refundableMoney'] = isset($rs['totalMoney'])?$rs['totalMoney']:0;

           // 卖家受理期限
           $supplierAcceptDays = (int)WSTConf('CONF.shopAcceptDays');
           $data['supplierAcceptExpireTime'] = date('Y-m-d H:i:s', time()+$supplierAcceptDays*24*60*60);
           $rs = Db::name('supplier_order_services')->field(true)->insert( array_merge($data, $orders) );
           if($rs===false)return WSTReturn(lang('operation_fail'));
           $serviceId = Db::name('supplier_order_services')->getLastInsID();
           if($serviceId==0)return WSTReturn(lang('operation_fail'));
           // 延长订单售后结束日期
           $limitDay = (int)WSTConf('CONF.afterSaleServiceDays');
           $afterSaleEndTime = date('Y-m-d H:i:s', strtotime($orders['afterSaleEndTime'])+$limitDay*60*60*24);
           $rs = Db::name('supplier_orders')->where('orderId', $orderId)->setField(['afterSaleEndTime'=>$afterSaleEndTime]);
           if($rs===false)return WSTReturn(lang('operation_fail'));

           // 售后商品表记录
            $serviceGoods = [];
            foreach($ids as $ogId){
                $key = 'goodsNum_'.$ogId;
                $num = (int)input($key);
                if($num>0){
                    // 查goodsId、goodsSpecId
                    $goods = Db::name('supplier_order_goods')->where(['id'=>$ogId])->find();
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
            $rs = Db::name('supplier_service_goods')->insertAll($serviceGoods);
            if($rs===false)return WSTReturn(lang('operation_fail'));
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
            $rs = Db::name('supplier_log_services')->insert($log);
            if($rs===false)return WSTReturn(lang('operation_fail'));

            // 给商家发送商城消息提醒
            $this->sendMessage(0, $orders["supplierId"], $serviceId, lang('wait_shop_handle'));

            Db::commit();
            return WSTReturn(lang('add_success'), 1);
        }catch(\Exception $e){
            Db::rollback();
            return WSTReturn(lang('operation_fail').$e->getMessage());
        }
    }
}
