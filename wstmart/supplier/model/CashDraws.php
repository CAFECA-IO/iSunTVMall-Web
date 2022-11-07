<?php
namespace wstmart\supplier\model;
use wstmart\common\model\CashDraws as CCashDraws;
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
 * 提现流水业务处理器
 */
class CashDraws extends CCashDraws{
	 protected $pk = 'cashId';
     

      public function drawMoneyBySupplier(){
          $supplierId = (int)session('WST_SUPPLIER.supplierId');
          $userId = (int)session('WST_SUPPLIER.userId');
          $money = (float)input('money');
          $payPwd = input('payPwd');
          $accType = (int)input('accType');
          if(!in_array($accType,[1,2,3]))return WSTReturn(lang("invalid_drawal_type"));
          
          $decrypt_data = WSTRSA($payPwd);
          if($decrypt_data['status']==1){
            $payPwd = $decrypt_data['data'];
          }else{
            return WSTReturn(lang("op_err"));
          }
          

          $limitMoney = (float)WSTConf('CONF.drawCashShopLimit');
          if($money<$limitMoney)return WSTReturn(lang("draw_money_limit", [$limitMoney]));
          if($payPwd=='')return WSTReturn(lang("require_pay_pwd"));
          $suppliers = model('suppliers')->get($supplierId);
          $wxOpenId = '';
          if($accType==1){
             $wxOpenId = model('users')->where('userId',$suppliers->userId)->value('wxOpenId');
             if($wxOpenId=='')return WSTReturn(lang("wx_drawal_fail"));
          }
          $supplierMoney = $suppliers->supplierMoney;
          $rechargeMoney = $suppliers->rechargeMoney;
          
          $areas = null;
          $bank = null;
          if($accType==3){
               $areas = model('areas')->getParentNames($suppliers->bankAreaId);
               $bank = Db::name('banks')->alias('b')->join('__BANKS_LANGS__ bl','bl.bankId=b.bankId','inner')->where(['b.bankId'=>$suppliers->bankId])->find();
          }else if($accType==1){
               if(input('accUser')=='')return WSTReturn(lang("require_real_name"));
          }
          //加载用户
          $user = model('users')->get($userId);
          $payPwd = md5($payPwd.$user->loginSecret);
          if($payPwd!=$user->payPwd)return WSTReturn(lang("pay_pwd_error"));
          if($money>($supplierMoney-$rechargeMoney))return WSTReturn(lang("drawal_money_limit"));
          //减去要提取的金额
          $suppliers->supplierMoney = $suppliers->supplierMoney-$money;
          $suppliers->lockMoney = $suppliers->lockMoney+$money;
          $actualMoney = 0;
          $commission = 0;
          $commissionRate = (float)WSTConf('CONF.drawCashCommission');
          if($commissionRate>0){
              $commission = $money*$commissionRate*0.01;
              $actualMoney = $money-$commission;
          }
          Db::startTrans();
          try{
             $result = $suppliers->save();
             if(false !==$result){
                //创建提现记录
                $data = [];
                $data['targetType'] = 3;
                $data['targetId'] = $supplierId;
                $data['money'] = $money;
                $data['accType'] = $accType;
                if($accType==3){
                   $data['accTargetName'] = $bank->bankName;
                   $data['accAreaName'] = implode('',$areas);
                   $data['accTargetId'] = $bank->bankId;
                   $data['accNo'] = $suppliers->bankNo;
                   $data['accUser'] = $suppliers['bankUserName'];
                }else if($accType==1){
                   $data['accNo'] = $wxOpenId;
                   $data['accUser'] = $acc['accUser'] = input('accUser');
                }
                $data['cashSatus'] = 0;
                $data['cashConfigId'] = 0;
                $data['createTime'] = date('Y-m-d H:i:s');
                $data['cashNo'] = '';
                $data['commission'] = $commission;
                $data['actualMoney'] = $actualMoney;
                $data['commissionRate'] = $commissionRate;
                $this->save($data);
                $this->cashNo = $this->cashId.(fmod($this->cashId,7));
                $this->save();
                //判断是否需要发送管理员短信
                $tpl = WSTMsgTemplates('PHONE_ADMIN_CASH_DRAWS');
                if((int)WSTConf('CONF.smsOpen')==1 && (int)WSTConf('CONF.smsCashDrawsTip')==1 &&  $tpl['tplContent']!='' && $tpl['status']=='1'){
                   $params = ['tpl'=>$tpl,'params'=>['CASH_NO'=>$this->cashNo]];
                    $staffs = Db::name('staffs')->where([['staffId','in',explode(',',WSTConf('CONF.cashDrawsTipUsers'))],['staffStatus','=',1],['dataFlag','=',1]])->field('staffPhone,areaCode')->select();
                    for($i=0;$i<count($staffs);$i++){
                       if($staffs[$i]['staffPhone']=='')continue;
                       $m = new LogSms();
                       $rv = $m->sendAdminSMS(0,$staffs[$i]['areaCode'].$staffs[$i]['staffPhone'],$params,'drawMoney','');
                    }
                }
               
                Db::commit();
                return WSTReturn(lang("drawal_success"),1);
             }
          }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang("op_err"),-1);
          }
      }

     
}
