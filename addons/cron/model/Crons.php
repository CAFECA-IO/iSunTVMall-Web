<?php
namespace addons\cron\model;
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
 * 计划任务业务处理
 */
class Crons extends Base{
	/***
     * 安装插件
     */
    public function install(){
    	Db::startTrans();
		try{
			$hooks = ['initCronHook'];
			$this->bindHoods("Cron", $hooks);
			//管理员后台
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'計劃任務'],
                2=>['menuName'=>'计划任务'],
                3=>['menuName'=>'Scheduled tasks'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>2,"menuSort"=>11,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"cron"]);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $menuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $menuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('menus_langs')->insertAll($datas);

			if($menuId!==false){
                $privilegeLangParams = [
                    1=>['privilegeName_00'=>'查看計劃任務','privilegeName_04'=>'操作計劃任務'],
                    2=>['privilegeName_00'=>'查看计划任务','privilegeName_04'=>'操作计划任务'],
                    3=>['privilegeName_00'=>'View scheduled tasks','privilegeName_04'=>'Operation plan task'],
                ];
                $privilegeIds = [];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"CRON_JHRW_00","isMenuPrivilege"=>1,"privilegeUrl"=>"/addon/cron-cron-index","otherPrivilegeUrl"=>"/addon/cron-cron-pageQuery","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'00'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"CRON_JHRW_04","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/cron-cron-toEdit","otherPrivilegeUrl"=>"/addon/cron-cron-edit,/addon/cron-cron-changeEnableStatus,/addon/cron-cron-runCron,/addon/cron-cron-del","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'04'];
                $datas = [];
                for($i=0;$i<count($privilegeIds);$i++){
                    foreach (WSTSysLangs() as $key => $v) {
                        $data = [];
                        $data['privilegeId'] = $privilegeIds[$i]['privilegeId'];
                        $data['langId'] = $v['id'];
                        $data['privilegeName'] = $privilegeLangParams[$v['id']]['privilegeName_'.$privilegeIds[$i]['code']];
                        $datas[] = $data;
                    }
                }
                Db::name('privileges_langs')->insertAll($datas);
			}
			installSql("cron");
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();

	  		return false;
	   	}
    }

	/**
	 * 删除菜单
	 */
	public function uninstall(){
		Db::startTrans();
		try{
			$hooks = ['initCronHook'];
			$this->unbindHoods("Cron", $hooks);
            $menuId = Db::name('menus')->where(["menuMark"=>"cron"])->value('menuId');
            Db::name('menus')->where(["menuMark"=>"cron"])->delete();
            Db::name('menus_langs')->where(['menuId'=>$menuId])->delete();

            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","CRON_%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","CRON_%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();
            uninstallSql("cron");//传入插件名
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}
	/**
	 * 分页
	 */
	public function pageQuery(){
		return $this->order('id desc')->paginate(input('limit/d'));
	}
	/**
	 * 列表
	 */
    public function listQuery(){
		return $this->order('id desc')->select();
	}
	public function getById($id){
		$rs = $this->get($id);
		if($rs['cronJson']!='')$rs['cronJson'] = unserialize($rs['cronJson']);
		return $rs;
	}
	/**
	 * 新增
	 */
	public function add(){
		$data = input('post.');
		unset($data['id']);
		$data['isRunSuccess'] = 0;
		$data['cronMinute'] = str_replace('，',',',$data['cronMinute']);
		if($data['cronMinute']=='')$data['cronMinute'] = '0';
		Db::startTrans();
		try{
			if($data['cronName']=='')return WSTReturn(lang('cron_require_task_name'));
			if($data['cronDesc']=='')return WSTReturn(lang('cron_require_task_desc'));
			if($data['cronUrl']=='')return WSTReturn(lang('cron_require_task_url'));
			$data['cronCycle'] = (int)$data['cronCycle'];
			if(!in_array($data['cronCycle'],[0,1,2]))return WSTReturn(lang('cron_invalid_plan_time'));
			if($data['cronDay']<=0 || $data['cronDay']>=32)return WSTReturn(lang('cron_invalid_plan_date'));
			if($data['cronWeek']<0 || $data['cronWeek']>6)return WSTReturn(lang('cron_invalid_plan_week'));
			$data['cronMinute'] = str_replace('，',',',$data['cronMinute']);
			if(stripos($data['cronMinute'],',')!==false){
                $mins = WSTFormatIn(',',$data['cronMinute'],false);
                foreach ($mins as $key => $vm) {
                	if($vm<0 || $vm>59)return WSTReturn(lang('cron_invalid_plan_time'));
                }
			}else{
				if((int)$data['cronMinute']<0 || (int)$data['cronMinute']>59)return WSTReturn(lang('cron_invalid_plan_time'));
			}

			$json = [];
			foreach ($json as $key => $v) {
				$json[$key]['fieldVal'] = input('post.'.$v['fieldCode']);
			}
			$data['cronJson'] = serialize($json);
			$data['isEnable'] = (int)$data['isEnable'];
			$data['nextTime'] = $this->getNextRunTime($data);
			$result = $this->save($data);
	        if(false !== $result){
	        	cache('WST_CRONS',null);
	        	Db::commit();
	        	return WSTReturn(lang('cron_operation_success'), 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('cron_operation_fail'),-1);
	}
    /**
	 * 编辑
	 */
	public function edit(){
		$data = input('post.');
		$data['cronMinute'] = str_replace('，',',',$data['cronMinute']);
		if($data['cronMinute']=='')$data['cronMinute'] = '0';
		Db::startTrans();
		try{
			$corn = $this->get((int)$data['id']);
			if($data['cronName']=='')return WSTReturn(lang('cron_require_task_name'));
			if($data['cronDesc']=='')return WSTReturn(lang('cron_require_task_desc'));
			if($data['cronUrl']=='')return WSTReturn(lang('cron_require_task_url'));
			$data['cronCycle'] = (int)$data['cronCycle'];
			if(!in_array($data['cronCycle'],[0,1,2]))return WSTReturn(lang('cron_invalid_plan_time'));
			if($data['cronDay']<=0 || $data['cronDay']>=32)return WSTReturn(lang('cron_invalid_plan_date'));
			if($data['cronWeek']<0 || $data['cronWeek']>6)return WSTReturn(lang('cron_invalid_plan_week'));
			$data['cronMinute'] = str_replace('，',',',$data['cronMinute']);
			if(stripos($data['cronMinute'],',')!==false){
                $mins = WSTFormatIn(',',$data['cronMinute'],false);
                foreach ($mins as $key => $vm) {
                	if($vm<0 || $vm>59)return WSTReturn(lang('cron_invalid_plan_time'));
                }
			}else{
				if((int)$data['cronMinute']<0 || (int)$data['cronMinute']>59)return WSTReturn(lang('cron_invalid_plan_time'));
			}
			$json = [];
			foreach ($json as $key => $v) {
				$json[$key]['fieldVal'] = input('post.'.$v['fieldCode']);
			}
			$data['cronJson'] = serialize($json);
			$data['isEnable'] = (int)$data['isEnable'];
			$data['nextTime'] = $this->getNextRunTime($data);
			$result = $corn->where('id',(int)$data['id'])->update($data);
	        if(false !== $result){
	        	cache('WST_CRONS',null);
	        	Db::commit();
	        	return WSTReturn(lang('cron_operation_success'), 1);
	        }
	    }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('cron_operation_fail'),-1);
	}
	/**
	 * 删除
	 */
    public function changeEnableStatus(){
	    $id = (int)input('post.id/d');
	    $status = ((int)input('post.status/d')==1)?1:0;
	    Db::startTrans();
		try{
		    $result = $this->setField(['isEnable'=>$status,'id'=>$id]);
	        if(false !== $result){
	        	cache('WST_CRONS',null);
	        	Db::commit();
	        	return WSTReturn(lang('cron_operation_success'), 1);
	        }
		}catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('cron_operation_fail'),-1);
	}

	/**
	 * 执行计划任务
	 */
	public function runCron(){
		$id = (int)input('post.id');
		$cron = $this->get($id);
		if(!$cron)return WSTReturn(lang('cron_status_jump_1'),1);
		if($cron->isEnable==0)return WSTReturn(lang('cron_status_jump_2'),1);
		if($cron->isRunning==1)return WSTReturn(lang('cron_status_jump_3'),1);
		$cron->runTime = date('Y-m-d H:i:s');
		$cron->nextTime = $this->getNextRunTime($cron);
		Db::startTrans();
		try{
	        $cron->isRunning = 1;
	        $cron->save();
	        $domain = request()->root(true);
	        $domain = $domain."/".$cron->cronUrl;
	        $data = $this->http($domain);
	        $data = json_decode($data,true);
	        $cron->isRunning = 0;
	        if($data['status']==1){
			    $cron->isRunSuccess = 1;
	        }else{
	            $cron->isRunSuccess = 0;
	        }
	        $cron->save();
	        Db::commit();
	    }catch (\Exception $e) {
            Db::rollback();
            $cron->isRunning = 0;
            $cron->isRunSuccess = 0;
            $cron->save();
            return WSTReturn(lang('cron_run_fail'));
        }
        return WSTReturn(lang('cron_run_success'),1);
	}

	public function http($url){
		$ch=curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置否输出到页面
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30 ); //设置连接等待时间
        curl_setopt($ch, CURLOPT_ENCODING, "gzip" );
        $data=curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $data;
	}

    /**
     * 执行所有定时任务
     */
	public function runCrons(){
		$cons = $this->where('isEnable',1)->select();
		$day = date('d');
		$hour = date('H');
		$minute = date('i');
		$week = date('w');
		foreach($cons as $key =>$cron){
			if($cron->isRunning==1)contnie;
			//判断能否执行
			if(strtotime($cron->nextTime)>time())continue;
			Db::startTrans();
			try{
				//fopen(time().'_'.rand(0,10000)."_auctionEnd.txt", "w");
		        $cron->isRunning = 1;
		        $cron->runTime = date('Y-m-d H:i:s');
		        $cron->nextTime = $this->getNextRunTime($cron);
		        $cron->save();
		        $domain = request()->root(true);
		        $domain = $domain."/".$cron->cronUrl;
		        $data = $this->http($domain);
		        $data = json_decode($data,true);
		        $cron->isRunning = 0;
		        if($data['status']==1){
				    $cron->isRunSuccess = 1;
		        }else{
		            $cron->isRunSuccess = 0;
		        }
		        $cron->save();
		        Db::commit();
		    }catch (\Exception $e) {
	            Db::rollback();
	            $cron->isRunning = 0;
	            $cron->isRunSuccess = 0;
	            $cron->save();
	        }
		}
		echo "done";
	}

	public function getNextRunTime($cron){
		$monthDay = date("t");
		$today = date('j');
		$thisWeek = date('w');
		$thisHour = date('H');
		$thisMinute = date('i');
		$nextDay = date('Y-m-d');
		$nextHour = 0;
		$nextMinute = 0;
		$isFurther = false;//标记是否要往前进一位
		$tmpMinute = [];
		if($cron['cronMinute']==-1){
            $nextMinute = date('i',strtotime('+1 Minute'));
            if($nextMinute<$thisMinute)$isFurther = true;
            $tmpMinute[] = $nextMinute;
		}else{
			$tmpMinute = explode(',',$cron['cronMinute']);
			sort($tmpMinute);
            $isFind = false;
            foreach($tmpMinute as $key => $v){
                if((int)$v>59)continue;
                if($thisMinute<(int)$v){
                	$nextMinute = (int)$v;
                	$isFind = true;
                	break;
                }
            }
            if(!$isFind){
            	$nextMinute = (int)$tmpMinute[0];
            	$isFurther = true;
            }
		}
		if($cron['cronHour']==-1){
            $nextHour = date("H",time()+($isFurther?3200:0));
            $isFurther = false;
            if($nextHour<$thisHour)$isFurther = true;
        }else{
            $nextHour = $cron['cronHour'];
            $isFurther = false;
		}
		if(time()>strtotime(date('Y-m-d')." ".$nextHour.":".$nextMinute.":00"))$isFurther = true;
		if($cron['cronCycle']==0){
			if($isFurther){
				$today = date('j',strtotime('+1 day'));
			}
			if($today<$cron['cronDay']){
                 $nextDay = date('Y-m-'.$cron['cronDay']);
			}else{
				 $nextDay = date("Y-m",strtotime(" +1 month"))."-".$cron['cronDay'];
			}
			if(date('j',strtotime($nextDay))!=$today){
            	if($cron['cronHour']==-1){
            		$nextHour = 0;
            	}else{
                    $nextHour = $cron['cronHour'];
            	}
            	if($cron['cronMinute']==-1){
            		$nextMinute = 0;
            	}else{
            		$nextMinute = (int)$tmpMinute[0];
            	}
            }
		}
		if($cron['cronCycle']==1){
			// if($isFurther){
			// 	$thisWeek = date('w',strtotime('+1 day'));
			// }
            $num = 0;
            if($cron['cronWeek']>$thisWeek){
                $num = $cron['cronWeek'] - $thisWeek;
            }else{
            	$num = $cron['cronWeek'] - $thisWeek + 7;
            }

            $nextDay = date("Y-m-d",strtotime("+".$num." day"));
            if(date('j',strtotime($nextDay))!=$today){
            	if($cron['cronHour']==-1){
            		$nextHour = 0;
            	}else{
                    $nextHour = $cron['cronHour'];
            	}
            	if($cron['cronMinute']==-1){
            		$nextMinute = 0;
            	}else{
            		$nextMinute = (int)$tmpMinute[0];
            	}
            }
		}
		if($cron['cronCycle']==2){
			if($isFurther){
				$nextDay = date('Y-m-d',strtotime('+1 day'));
			}else{
				$nextDay = date('Y-m-d');
			}
		}
		return date('Y-m-d H:i:s',strtotime($nextDay." ".$nextHour.":".$nextMinute.":00"));
	}

	/**
	 * 删除计划任务
	 */
	public function del(){
		$id = (int)input('id');
		$result = $this->where('id='.$id.' and cronCode is null')->delete();
		if(false !== $result){
            return WSTReturn(lang('cron_operation_success'),1);
		}else{
			return WSTReturn(lang('cron_operation_fail'));
		}
	}

}
