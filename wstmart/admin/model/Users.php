<?php
namespace wstmart\admin\model;
use wstmart\admin\validate\Users as validate;
use think\Db;
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
 * 会员业务处理
 */
class Users extends Base{
	protected $pk = 'userId';
	/**
	 * 分页
	 */
	public function pageQuery(){
		/******************** 查询 ************************/
		$where = [];
		$where[] = ['u.dataFlag','=',1];
		$lName = input('loginName1');
		$phone = input('loginPhone');
		$email = input('loginEmail');
		$uType = input('userType');
		$uStatus = input('userStatus1');
		$sort = input('sort');
		if(!empty($lName))
			$where[] = ['loginName|s.shopName','like',"%$lName%"];
		if(!empty($phone))
			$where[] = ['userPhone','like',"%$phone%"];
		if(!empty($email))
			$where[] = ['userEmail','like',"%$email%"];
		if(is_numeric($uType))
			$where[] = ['userType','=',"$uType"];
		if(is_numeric($uStatus))
			$where[] = ['userStatus','=',"$uStatus"];
		$order = 'u.userId desc';
		if($sort){
			$sort =  str_replace('.',' ',$sort);
			$order = $sort;
		}
		/********************* 取数据 *************************/
		$rs = $this->alias('u')->join('__SHOPS__ s','u.userId=s.userId and s.dataFlag=1','left')->where($where)
					->field(['u.*,s.shopId'])
					->order($order)
					->paginate(input('limit/d'))
					->toArray();
	    foreach ($rs['data'] as $key => $v) {
	    	$r = WSTUserRank($v['userTotalScore']);
	    	$rs['data'][$key]['rank'] = $r['rankName'];
	    }
		return $rs;
	}
	public function getById($id){
		return $this->get(['userId'=>$id]);
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		$data['createTime'] = date('Y-m-d H:i:s');
		$data["loginSecret"] = rand(1000,9999);
    	$data['loginPwd'] = md5($data['loginPwd'].$data['loginSecret']);
    	$data['payPwd'] = md5($data['payPwd'].$data['loginSecret']);
    	if($data['brithday']=='')unset($data['brithday']);
    	WSTUnset($data,'userId,userType,userScore,userTotalScore,lastIP,lastTime,userMoney,lockMoney,dataFlag,rechargeMoney');
    	Db::startTrans();
		try{
			$validate = new validate();
		    if(!$validate->scene('add')->check($data))return WSTReturn($validate->getError());
			$result = $this->allowField(true)->save($data);
			$id = $this->userId;
	        if(false !== $result){
	        	hook("adminAfterAddUser",["userId"=>$id]);
	        	WSTUseResource(1, $id, $data['userPhoto']);
	        	Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$Id = (int)input('post.userId');
		$data = input('post.');
		$u = $this->where('userId',$Id)->field('loginSecret')->find();
		if(empty($u))return WSTReturn(lang('users_err1'));
		if(!isset($data['brithday']) || $data['brithday']=='')unset($data['brithday']);
		//判断是否需要修改密码
		if(empty($data['loginPwd'])){
			unset($data['loginPwd']);
		}else{
    		$data['loginPwd'] = md5($data['loginPwd'].$u['loginSecret']);
		}
		if(empty($data['payPwd'])){
			unset($data['payPwd']);
		}else{
    		$data['payPwd'] = md5($data['payPwd'].$u['loginSecret']);
		}
		Db::startTrans();
		try{
			if(isset($data['userPhoto'])){
			    WSTUseResource(1, $Id, $data['userPhoto'], 'users', 'userPhoto');
			}

			WSTUnset($data,'loginName,createTime,userId,userType,userScore,userTotalScore,lastIP,lastTime,userMoney,lockMoney,dataFlag,rechargeMoney');
		    $result = $this->allowField(true)->save($data,['userId'=>$Id]);
	        if(false !== $result){
	        	hook("adminAfterEditUser",["userId"=>$Id]);
	        	Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }
	}
	/**
	 * 删除
	 */
    public function del(){
	    $id = (int)input('post.id');
	    if($id==1){
	    	return WSTReturn(lang('users_err2'),-1);
	    }
	    Db::startTrans();
	    try{
		    $data = [];
			$data['dataFlag'] = -1;
		    $result = $this->update($data,['userId'=>$id]);
	        if(false !== $result){
	        	//删除店铺信息
	        	model('shops')->delByUserId($id);
	        	hook("adminAfterDelUser",["userId"=>$id]);
	        	WSTUnuseResource('users','userPhoto',$id);
	        	// 删除app端、小程序端对应用户登录凭证
	        	delAppToken($id);
	        	Db::commit();
	        	return WSTReturn(lang('op_ok'), 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('op_err'),-1);
        }
	}
	/**
	* 是否启用
	*/
	public function changeUserStatus($id, $status){
		$result = $this->update(['userStatus'=>(int)$status],['userId'=>(int)$id]);
		if(false !== $result){
        	return WSTReturn(lang('op_ok'), 1);
        }else{
        	return WSTReturn($this->getError(),-1);
        }
	}
	/**
	* 根据用户名查找用户
	*/
	public function getByName($name){
		return $this->field(['userId','loginName'])->where([['loginName','like',"%$name%"],['dataFlag','=',1]])->select();
	}
	/**
	* 获取所有用户id
	*/
	public function getAllUserId()
	{
		return $this->where('dataFlag',1)->column('userId');
	}

	/**
	 * 根据用户账号查找用户信息
	 */
	public function getUserByKey(){
		$key = input('key');
		$user = $this->where([['loginName|userPhone|userEmail','=',$key],['dataFlag','=',1]])->find();
        if(empty($user))return WSTReturn(lang('users_err3'),-1);
        $shop = model('shops')->where([['userId','=',$user->userId],['dataFlag','=',1]])->find();
        if(!empty($shop))return WSTReturn(lang('users_err4'),-1);
        return WSTReturn('',1,['loginName'=>$user->loginName,'userId'=>$user->userId]);
	}

	/**
	 * 导出会员信息
	 */
	public function toExport(){
		$name ='users';
		/******************** 查询 ************************/
		$where = [];
		$where[] = ['u.dataFlag','=',1];
		$lName = input('loginName1');
		$phone = input('loginPhone');
		$email = input('loginEmail');
		$uType = input('userType');
		$uStatus = input('userStatus1');
		$sort = input('sort');
		if(!empty($lName))$where[] = ['loginName|s.shopName','like',"%$lName%"];
		if(!empty($phone))$where[] = ['userPhone','like',"%$phone%"];
		if(!empty($email))$where[] = ['userEmail','like',"%$email%"];
		if(is_numeric($uType))$where[] = ['userType','=',"$uType"];
		if(is_numeric($uStatus))$where[] = ['userStatus','=',"$uStatus"];
		$order = 'u.userId desc';
		if($sort){
			$sort =  str_replace('.',' ',$sort);
			$order = $sort;
		}
		/********************* 取数据 *************************/
		$rs = $this->alias('u')->join('__SHOPS__ s','u.userId=s.userId and s.dataFlag=1','left')->where($where)
					->field(['u.userId','u.rechargeMoney','loginName','userName','userType','userPhone','userEmail','userScore','u.createTime','userStatus','lastTime','s.shopId','userMoney','u.lockMoney','u.userName','u.trueName','u.brithday','u.lastIP','u.lastTime','u.userSex','u.userQQ'])
					->order($order)
					->select();
	    foreach ($rs as $key => $v) {
	    	$r = WSTUserRank($v['userScore']);
	    	$rs[$key]['rank'] = $r['rankName'];
	    }
		require Env::get('root_path') . 'extend/phpexcel/PHPExcel/IOFactory.php';
        $objPHPExcel = new \PHPExcel();
        // 设置excel文档的属性
        $objPHPExcel->getProperties()->setCreator("WSTMart")//创建人
        ->setLastModifiedBy("WSTMart")//最后修改人
        ->setTitle($name)//标题
        ->setSubject($name)//题目
        ->setDescription($name)//描述
        ->setKeywords(lang('users_err5'));//种类
        // 开始操作excel表
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置工作薄名称
        $objPHPExcel->getActiveSheet()->setTitle(iconv('gbk', 'utf-8', 'Sheet'));
        // 设置默认字体和大小
        $objPHPExcel->getDefaultStyle()->getFont()->setName(iconv('gbk', 'utf-8', ''));
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $styleArray = array('font' => array('bold' => true,'color'=>array('argb' => 'ffffffff')));
        //设置宽
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(16);
        $objRow = $objPHPExcel->getActiveSheet()->getStyle('A1:Q1');
        $objRow->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objRow->getFill()->getStartColor()->setRGB('666699');
        $objRow->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objRow->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(25);
        $objPHPExcel->getActiveSheet()
        ->setCellValue('A1', lang('label_user_score_account'))
        ->setCellValue('B1', lang('label_user_name'))
        ->setCellValue('C1', lang('label_user_true_name'))
        ->setCellValue('D1', lang('label_user_sex'))
        ->setCellValue('E1', lang('label_user_birthday'))
        ->setCellValue('F1', lang('label_user_phone'))
        ->setCellValue('G1', lang('label_user_email'))
        ->setCellValue('H1', 'QQ')
        ->setCellValue('I1', lang('user_can_use_money'))
        ->setCellValue('J1', lang('user_lock_money'))
        ->setCellValue('K1', lang('user_recharge_give'))
        ->setCellValue('L1', lang('label_user_score'))
        ->setCellValue('M1', lang('label_user_rank'))
        ->setCellValue('N1', lang('label_user_create_time'))
        ->setCellValue('O1', lang('users_info1'))
        ->setCellValue('P1', lang('users_info2'))
        ->setCellValue('Q1', lang('label_supp_settlement_status'));
        $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->applyFromArray($styleArray);
        $i = 1;
        $totalRow = 0;
        for ($row = 0; $row < count($rs); $row++){
            $i = $row+2;
            $objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$i, $rs[$row]['loginName'])
            ->setCellValue('B'.$i, $rs[$row]['userName'])
            ->setCellValue('C'.$i, $rs[$row]['trueName'])
            ->setCellValue('D'.$i, ($rs[$row]['userSex']==1)?lang('user_sex_type1'):(($rs[$row]['userSex']==2)?lang('user_sex_type2'):lang('user_sex_type3')))
            ->setCellValue('E'.$i, $rs[$row]['brithday'])
            ->setCellValue('F'.$i, " ".$rs[$row]['userPhone'])
            ->setCellValue('G'.$i, $rs[$row]['userEmail'])
            ->setCellValue('H'.$i, $rs[$row]['userQQ'])
            ->setCellValue('I'.$i, $rs[$row]['userMoney'])
            ->setCellValue('J'.$i, $rs[$row]['lockMoney'])
            ->setCellValue('K'.$i, $rs[$row]['rechargeMoney'])
            ->setCellValue('L'.$i, " ".$rs[$row]['userScore'])
            ->setCellValue('M'.$i, $rs[$row]['rank'])
            ->setCellValue('N'.$i, $rs[$row]['createTime'])
            ->setCellValue('O'.$i, $rs[$row]['lastTime'])
            ->setCellValue('P'.$i, $rs[$row]['lastIP'])
            ->setCellValue('Q'.$i, ($rs[$row]['userStatus']==1)?lang('addon_set_enable'):lang('user_status_type2'));
            $totalRow++;
        }
        $totalRow = ($totalRow==0)?1:$totalRow+1;
        $objPHPExcel->getActiveSheet()->getStyle('A1:Q'.$totalRow)->applyFromArray(array(
                'borders' => array (
                        'allborders' => array ('style' => \PHPExcel_Style_Border::BORDER_THIN,'color' => array ('argb' => 'FF000000'))
                )
        ));
        $this->PHPExcelWriter($objPHPExcel,$name);
	}

}
