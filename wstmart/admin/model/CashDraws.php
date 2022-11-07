<?php
namespace wstmart\admin\model;
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
 * 提现分类业务处理
 */
class CashDraws extends Base{
	protected $pk = 'cashId';
	/**
	 * 分页
	 */
	public function pageQuery(){
		$where = [];
		$targetType = input('targetType',-1);
		$cashNo = input('cashNo');
		$cashSatus = input('cashSatus',-2);
        if(in_array($targetType,[0,1,2,3]))$where[] = ['targetType','=',$targetType];
        if(in_array($cashSatus,[0,1,-1]))$where[] = ['cashSatus','=', $cashSatus];
        if($cashNo!='')$where[] = ['cashNo','like','%'.$cashNo.'%'];
        // 排序
		$sort = input('sort');
		$order = [];
		if($sort!=''){
			$sortArr = explode('.',$sort);
			$order = $sortArr[0].' '.$sortArr[1];
			if($sortArr[0]=='cashNo'){
				$order ='createTime '.$sortArr[1];
			}
		}
		$isOpenSupplier = (int)WSTConf('CONF.isOpenSupplier');
        $page = $this->where($where)->order($order)->order('createTime desc')->paginate(input('limit/d'))->toArray();
	    if(count($page['data'])>0){
	    	$userIds = [];
	    	$shopIds = [];
	    	$suppliers = [];
	    	foreach ($page['data'] as $key => $v) {
	    		if($v['targetType']==0)$userIds[] = $v['targetId'];
	    		if($v['targetType']==1)$shopIds[] = $v['targetId'];
	    		if($isOpenSupplier ==1 && $v['targetType']==3)$suppliers[] = $v['targetId'];
	    	}
	    	$userMap = [];
	    	if(count($userIds)>0){
	    		$user = Db::name('users')->where([['userId','in',$userIds]])->field('userId,loginName,userName')->select();
	    	    foreach ($user as $key => $v) {
	    	    	$userMap["0_".$v['userId']] = $v; 
	    	    }
	    	}
	    	if(count($shopIds)>0){
	    		$user = Db::name('shops')->alias('s')
	    		          ->join('__USERS__ u','u.userId=s.userId')
	    		          ->where([['shopId','in',$shopIds]])
	    		          ->field('s.shopId,u.loginName,s.shopName as userName')
	    		          ->select();
	    	    foreach ($user as $key => $v) {
	    	    	$userMap["1_".$v['shopId']] = $v; 
	    	    }
	    	}
	    	if($isOpenSupplier ==1 && count($suppliers)>0){
	    		$user = Db::name('suppliers')->alias('s')
	    		          ->join('__USERS__ u','u.userId=s.userId')
	    		          ->where([['supplierId','in',$suppliers]])
	    		          ->field('s.supplierId,u.loginName,s.supplierName as userName')
	    		          ->select();
	    	    foreach ($user as $key => $v) {
	    	    	$userMap["3_".$v['supplierId']] = $v; 
	    	    }
	    	}
	    	foreach ($page['data'] as $key => $v) {
	    		if(!$isOpenSupplier && $v['targetType']==3){
	    			continue;
	    		}
	    		$page['data'][$key]['targetTypeName'] = WSTGetTargetTypeName($v['targetType']);
	    		$page['data'][$key]['loginName'] = $userMap[$v['targetType']."_".$v['targetId']]['loginName'];
	    		$page['data'][$key]['userName'] = $userMap[$v['targetType']."_".$v['targetId']]['userName'];
	    	}
	    }
	    return $page;
	}

	/**
	 * 获取提现详情
	 */
	public function getById(){
		$id = (int)input('id');
		$rs =  $this->get($id);
		$user = [];
		if($rs['targetType']==1){
			$user = Db::name('shops')->alias('s')
	    		      ->join('__USERS__ u','u.userId=s.userId')
	    		      ->where('shopId',$rs['targetId'])
	    		      ->field('s.shopId,u.loginName,s.shopName as userName')
	    		      ->find();
            
		}else if($rs['targetType']==3){
			$user = Db::name('suppliers')->alias('s')
	    		      ->join('__USERS__ u','u.userId=s.userId')
	    		      ->where('supplierId',$rs['targetId'])
	    		      ->field('s.supplierId,u.loginName,s.supplierName as userName')
	    		      ->find();
            
		}else{
			$user = Db::name('users')->where('userId',$rs['targetId'])->field('userId,loginName,userName')->find();   
		}
		$rs['userName'] = $user['userName'];
        $rs['loginName'] = $user['loginName'];
		return $rs;
	}

	/**
	 * 处理提现成功
	 */
	public function handle(){
		$id = (int)input('cashId');
		$cash = $this->get($id);
		if(empty($cash))return WSTReturn(lang('cashdraws_invalid_no'));
		Db::startTrans();
		try{
            if($cash->targetType==1){
                $shop = model('shops')->get($cash->targetId);
				if($shop->lockMoney<$cash->money)return WSTReturn(lang('cashdraw_err1'));
                $shop->lockMoney = $shop->lockMoney-$cash->money;
            	$shop->save();
            	$targetId = $shop->userId;
            }else if($cash->targetType==3){
		        $supplier = model('suppliers')->get($cash->targetId);
				if($supplier->lockMoney<$cash->money)return WSTReturn(lang('cashdraw_err1'));
                $supplier->lockMoney = $supplier->lockMoney-$cash->money;
            	$supplier->save();
            	$targetId = $supplier->userId;
            }else{
                $user = model('users')->get($cash->targetId);
				if($user->lockMoney<$cash->money)return WSTReturn(lang('cashdraw_err1'));
				$user->lockMoney = $user->lockMoney-$cash->money;
            	$user->save();
            	$targetId = $user->userId;
            }
            $cash->cashSatus = 1;
            $cash->cashRemarks = input('cashRemarks');
            $result = $cash->save();

            if(false != $result){
                $commissionLog = '';
                if($cash->commission>0){
                    $commissionLog = '，'.lang('label_cashdraws_fee').$cash->commission;
                }
            	//创建一条流水记录
            	$lm = [];
				$lm['targetType'] = $cash->targetType;
				$lm['targetId'] = $cash->targetId;
				$lm['dataId'] = $id;
				$lm['dataSrc'] = 3;
				$lm['remark'] = json_encode(['type'=>'lang_params','key'=>'cashdraw_log','params'=>[$cash->cashNo,$cash->money.$commissionLog,$cash->actualMoney,(($cash->cashRemarks!='')?$cash->cashRemarks:'')]]);
				$lm['moneyType'] = 0;
				$lm['money'] = $cash->money;
				$lm['payType'] = 0;
				$lm['createTime'] = date('Y-m-d H:i:s');
				model('LogMoneys')->insert($lm);
				//发送信息信息
				$tpl = WSTMsgTemplates('CASH_DRAW_SUCCESS');
		        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${CASH_NO}'];
		            $replace = [$cash->cashNo];
		            WSTSendMsg($targetId,str_replace($find,$replace,$tpl['tplContent']),['from'=>5,'dataId'=>$id]);
		        } 
				//微信消息
				if((int)WSTConf('CONF.wxenabled')==1){
					$params = [];
					$params['CASH_NO'] = $cash->cashNo;
					$params['MONEY'] = $cash->money;
					$params['CASH_TYPE'] = lang('label_cashdraws_type_name');           
					$params['CASH_TIME'] = $cash->createTime;
					$params['CASH_RESULT'] = WSTLang('label_cashdraws_op_ok',[((input('cashRemarks')=='')?"-":input('cashRemarks'))]);
					$params['EXAMINE_TIME'] = date('Y-m-d H:i:s');
					WSTWxMessage(['CODE'=>'WX_CASH_DRAW_SUCCESS','userId'=>$targetId,'params'=>$params]);
				}
				Db::commit();
				return WSTReturn(lang('op_ok'),1);
            }
		}catch (\Exception $e) {
            Db::rollback();
        }
		return WSTReturn(lang('op_err'),-1);
	}

	/**
	 * 处理提现失败
	 */
	public function handleFail(){
		$id = (int)input('cashId');
		$cash = $this->get($id);
		if(empty($cash))return WSTReturn(lang('cashdraws_invalid_no'));
		if(input('cashRemarks')=='')return WSTReturn(lang('require_cashdraw_txt'));
		Db::startTrans();
		try{
			
            if($cash->targetType==0){
		        $user = model('users')->get($cash->targetId);
				if($user->lockMoney<$cash->money)return WSTReturn(lang('cashdraw_err2'));
				$user->userMoney = $user->userMoney + $cash->money;
				$user->lockMoney = $user->lockMoney-$cash->money;
            	$user->save();
            	$targetId = $user->userId;
            }else if($cash->targetType==3){
		        $supplier = model('suppliers')->get($cash->targetId);
				if($supplier->lockMoney<$cash->money)return WSTReturn(lang('cashdraw_err2'));
                $supplier->supplierMoney = $supplier->supplierMoney + $cash->money;
                $supplier->lockMoney = $supplier->lockMoney-$cash->money;
            	$supplier->save();
            	$targetId = $supplier->userId;
            }else{
                $shop = model('shops')->get($cash->targetId);
				if($shop->lockMoney<$cash->money)return WSTReturn(lang('cashdraw_err2'));
                $shop->shopMoney = $shop->shopMoney + $cash->money;
                $shop->lockMoney = $shop->lockMoney-$cash->money;
            	$shop->save();
            	$targetId = $shop->userId;
            }
            $cash->cashSatus = -1;
            $cash->cashRemarks = input('cashRemarks');
            $result = $cash->save();

            if(false != $result){
				//发送信息信息
				$tpl = WSTMsgTemplates('CASH_DRAW_FAIL');
		        if( $tpl['tplContent']!='' && $tpl['status']=='1'){
		            $find = ['${CASH_NO}','${CASH_RESULT}'];
		            $replace = [$cash->cashNo,input('cashRemarks')];
		            WSTSendMsg($targetId,str_replace($find,$replace,$tpl['tplContent']),['from'=>5,'dataId'=>$id]);
		        } 
				//微信消息
				if((int)WSTConf('CONF.wxenabled')==1){
					$params = [];
					$params['CASH_NO'] = $cash->cashNo;
					$params['MONEY'] = $cash->money;
					$params['CASH_TYPE'] = lang('label_cashdraws_type_name');           
					$params['CASH_TIME'] = $cash['createTime'];
					$params['CASH_RESULT'] = WSTlang('cashdraw_err3',[((input('cashRemarks')=='')?"-":input('cashRemarks'))]);
					$params['EXAMINE_TIME'] = date('Y-m-d H:i:s');
					WSTWxMessage(['CODE'=>'WX_CASH_DRAW_FAIL','userId'=>$targetId,'params'=>$params]);
				}
				Db::commit();
				return WSTReturn(lang('op_ok'),1);
            }
		}catch (\Exception $e) {
            Db::rollback();
        }
		return WSTReturn(lang('op_err'),-1);
	}
	/**
	 * 导出提现申请
	 */
	public function toExport(){
		$where = [];
		$name=lang('cashdraw_xls_title');
		$targetType = input('targetType',-1);
		$cashNo = input('cashNo');
		$cashSatus = input('cashSatus',-1);
        if(in_array($targetType,[0,1,3]))$where[] = ['targetType','=',$targetType];
        if(in_array($cashSatus,[0,1,-1]))$where[] = ['cashSatus','=',$cashSatus];
        if($cashNo!='')$where[] = ['cashNo','like','%'.$cashNo.'%'];
        // 排序
		$sort = input('sort');
		$order = [];
		if($sort!=''){
			$sortArr = explode('.',$sort);
			$order = $sortArr[0].' '.$sortArr[1];
			if($sortArr[0]=='cashNo'){
				$order = $sortArr[0].'+0 '.$sortArr[1];
			}
		}
		$isOpenSupplier = (int)WSTConf('CONF.isOpenSupplier');
        $page = $this->where($where)->order($order)->order('createTime desc')->select();
	    if(count($page)>0){
	    	$userIds = [];
	    	$shopIds = [];
	    	$suppliers = [];
	    	foreach ($page as $key => $v) {
	    		if($v['targetType']==0)$userIds[] = $v['targetId'];
	    		if($v['targetType']==1)$shopIds[] = $v['targetId'];
	    		if($isOpenSupplier ==1 && $v['targetType']==3)$suppliers[] = $v['targetId'];
	    	}
	    	$userMap = [];
	    	if(count($userIds)>0){
	    		$user = Db::name('users')->where([['userId','in',$userIds]])->field('userId,loginName,userName')->select();
	    	    foreach ($user as $key => $v) {
	    	    	$userMap["0_".$v['userId']] = $v; 
	    	    }
	    	}
	    	if(count($shopIds)>0){
	    		$user = Db::name('shops')->alias('s')
	    		          ->join('__USERS__ u','u.userId=s.userId')
	    		          ->where([['shopId','in',$shopIds]])
	    		          ->field('s.shopId,u.loginName,s.shopName as userName')
	    		          ->select();
	    	    foreach ($user as $key => $v) {
	    	    	$userMap["1_".$v['shopId']] = $v; 
	    	    }
	    	}
	    	if($isOpenSupplier ==1 && count($suppliers)>0){
	    		$user = Db::name('suppliers')->alias('s')
	    		          ->join('__USERS__ u','u.userId=s.userId')
	    		          ->where([['supplierId','in',$suppliers]])
	    		          ->field('s.supplierId,u.loginName,s.supplierName as userName')
	    		          ->select();
	    	    foreach ($user as $key => $v) {
	    	    	$userMap["3_".$v['supplierId']] = $v; 
	    	    }
	    	}
	    	foreach ($page as $key => $v) {
	    		if(!$isOpenSupplier && $v['targetType']==3){
	    			continue;
	    		}
	    		$page[$key]['targetTypeName'] = WSTGetTargetTypeName($v['targetType']);
	    		$page[$key]['loginName'] = $userMap[$v['targetType']."_".$v['targetId']]['loginName'];
	    		$page[$key]['userName'] = $userMap[$v['targetType']."_".$v['targetId']]['userName'];
	    		$page[$key]['cashSatus'] = ($page[$key]['cashSatus']==1)?lang('label_cashdraws_status1'):(($page[$key]['cashSatus']==-1)?lang('label_cashdraws_status0'):lang('label_cashdraws_status2'));
	    	}
	    }
	   

		require Env::get('root_path') . 'extend/phpexcel/PHPExcel/IOFactory.php';
		$objPHPExcel = new \PHPExcel();
		// 设置excel文档的属性
		$objPHPExcel->getProperties()->setCreator("WSTMart")//创建人
		->setLastModifiedBy("WSTMart")//最后修改人
		->setTitle($name)//标题
		->setSubject($name)//题目
		->setDescription($name)//描述
		->setKeywords(lang('cashdraw_xls_title'));//种类
	
		// 开始操作excel表
		$objPHPExcel->setActiveSheetIndex(0);
		// 设置工作薄名称
		$objPHPExcel->getActiveSheet()->setTitle(iconv('gbk', 'utf-8', 'Sheet'));
		// 设置默认字体和大小
		$objPHPExcel->getDefaultStyle()->getFont()->setName(iconv('gbk', 'utf-8', ''));
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
		$styleArray = array(
				'font' => array(
						'bold' => true,
						'color'=>array(
								'argb' => 'ffffffff',
						)
				)
		);
		//设置宽
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $objRow = $objPHPExcel->getActiveSheet()->getStyle('A1:H1');
		$objRow->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
		$objRow->getFill()->getStartColor()->setRGB('666699');
		$objRow->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objRow->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);	
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(25);
		
		$objPHPExcel->getActiveSheet()->setCellValue('A1', lang('label_cashdraws_no'))
		->setCellValue('B1', lang('label_cashdraws_user_type'))->setCellValue('C1', lang('label_cashdraws_user_name'))
		->setCellValue('D1', lang('label_cashdraws_account'))
		->setCellValue('E1', lang('cashdraws_bank_user'))->setCellValue('F1', lang('label_cashdraws_money'))
		->setCellValue('G1', lang('label_cashdraws_apply_time'))->setCellValue('H1', lang('label_cashdraws_status'));
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($styleArray);
	    $totalRow = 0;
		for ($row = 0; $row < count($page); $row++){
			$i = $row+2;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $page[$row]['cashNo'])
			->setCellValue('B'.$i, $page[$row]['targetTypeName'])->setCellValue('C'.$i, $page[$row]['userName'].'('.$page[$row]['loginName'].')' )
			->setCellValue('D'.$i, $page[$row]['accTargetName'].'|'.$page[$row]['accNo'])
			->setCellValue('E'.$i, $page[$row]['accUser'])->setCellValue('F'.$i, lang('currency_symbol').$page[$row]['money'])
			->setCellValue('G'.$i, $page[$row]['createTime'])->setCellValue('H'.$i, $page[$row]['cashSatus']);
			$totalRow = $row;
		}
		$totalRow = (count($page)==0)?1:$totalRow+2;
	    $objPHPExcel->getActiveSheet()->getStyle('A1:H'.$totalRow)->applyFromArray(array(
				'borders' => array (
						'allborders' => array (
								'style' => \PHPExcel_Style_Border::BORDER_THIN,  //设置border样式
								'color' => array ('argb' => 'FF000000'),     //设置border颜色
						)
				)
		));
		$this->PHPExcelWriter($objPHPExcel,$name);
	}

	/**
     * 提现统计报表
     */
    public function statCashDrawal(){
        $start = input('startDate');
        $end = input('endDate');
        $where = [];
        $targetType = input('type',-1);
        $cashNo = input('cashNo');
        if(in_array($targetType,[0,1,2,3]))$where[] = ['targetType','=',$targetType];
        $where[] = ['cashSatus','=', 1];
    	if($cashNo!='')$where[] = ['cashNo','like','%'.$cashNo.'%'];
        if($start!='' && $end!='')$where[] = ['createTime','between',[$start,$end]];
        $isOpenSupplier = (int)WSTConf('CONF.isOpenSupplier');
        $page = Db::name("cash_draws")
                ->where($where)
                ->order('cashId desc')->paginate(input('limit/d'))->toArray();
        $sumMoney = Db::name("cash_draws")
                ->where($where)
                ->order('cashId desc')->field('sum(money) money,sum(commission) commission')->find(); 
        if(count($page['data'])>0){
            $userIds = [];
            $shopIds = [];
            $supplierIds = [];
            foreach ($page['data'] as $key => $v) {
                if($v['targetType']==0)$userIds[] = $v['targetId'];
                if($v['targetType']==1)$shopIds[] = $v['targetId'];
                if($isOpenSupplier ==1 && $v['targetType']==3)$supplierIds[] = $v['targetId'];
            }
            $userMap = [];
            if(count($userIds)>0){
                $user = Db::name('users')->where([['userId','in',$userIds]])->field('userId,loginName,userName')->select();
                foreach ($user as $key => $v) {
                    $userMap["0_".$v['userId']] = $v; 
                }
            }
            if(count($shopIds)>0){
                $user = Db::name('shops')->alias('s')
                          ->join('__USERS__ u','u.userId=s.userId')
                          ->where([['shopId','in',$shopIds]])
                          ->field('s.shopId,u.loginName,s.shopName as userName')
                          ->select();
                foreach ($user as $key => $v) {
                    $userMap["1_".$v['shopId']] = $v; 
                }
            }
            if($isOpenSupplier ==1 && count($supplierIds)>0){
                $user = Db::name('suppliers')->alias('s')
                          ->join('__USERS__ u','u.userId=s.userId')
                          ->where([['supplierId','in',$supplierIds]])
                          ->field('s.supplierId,u.loginName,s.supplierName as userName')
                          ->select();
                foreach ($user as $key => $v) {
                    $userMap["3_".$v['supplierId']] = $v; 
                }
            }
            foreach ($page['data'] as $key => $v) {
                if(!$isOpenSupplier && $v['targetType']==3){
                    continue;
                }
                $page['data'][$key]['targetTypeName'] = WSTGetTargetTypeName($v['targetType']);
                $page['data'][$key]['loginName'] = $userMap[$v['targetType']."_".$v['targetId']]['loginName'];
                $page['data'][$key]['userName'] = $userMap[$v['targetType']."_".$v['targetId']]['userName'];
                $page['data'][$key]['totalCashDrawMoney'] = $sumMoney['money'];
                $page['data'][$key]['totalCashDrawCommission'] = $sumMoney['commission'];
            }
        }
        return $page;
    }
}
