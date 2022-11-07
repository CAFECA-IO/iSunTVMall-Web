<?php
namespace wstmart\common\model;
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
 * 积分业务处理器
 */
class UserScores extends Base{
	protected $pk = 'scoreId';

	/**
	 * 是否已签到
	 */
	public function isSign($userId){
		$createTime = Db::name('user_scores')->where(["userId"=>$userId,"dataSrc"=>5,])->order('createTime desc')->value('createTime');
		if(empty($createTime))return false;
		if(date('Y-m-d',strtotime($createTime)) == date('Y-m-d')){
			// 签到获得积分的最后日期与当前日期相同
			return true;
		}
		return false;
	}


     /**
      * 获取列表
      */
      public function pageQuery($userId){
      	  $type = (int)input('post.type');
          $where = ['userId'=>(int)$userId];
          if($type!=-1)$where['scoreType'] = $type;
          $page = $this->where($where)->order('scoreId desc')->paginate()->toArray();
          foreach ($page['data'] as $key => $v){
          	  $page['data'][$key]['dataSrc'] = WSTLangScore($v['dataSrc']);
              $page['data'][$key]['dataRemarks'] = WSTLogJson($v['dataRemarks']);
          }
          return $page;
      }

      /**
       * 新增记录
       */
      public function add($score,$isAddTotal = false){
      	$score['createTime'] = date('Y-m-d H:i:s');
      	$this->create($score);
      	$user = model('common/users')->get($score['userId']);
      	if($score['scoreType']==1){
      		$user->userScore = $user->userScore + $score['score'];
      		if($isAddTotal)$user->userTotalScore = $user->userTotalScore+$score['score'];
      	}else{
      		$user->userScore = $user->userScore - $score['score'];
      	}
      	$userinfo = session('WST_USER');
      	$userinfo['userScore'] = $user->userScore;
      	session('WST_USER',$userinfo);
      	$user->save();
      }
      
      /**
       *签到获得积分
       */
      public function signScore($userId){
      	$time = date('Y-m-d');
      	$frontTime = date("Y-m-d",strtotime("-1 day"));
      	if(WSTConf('CONF.signScoreSwitch')==0)return WSTReturn(lang('sign_fail'));
		$userscores = $this->where([["userId",'=',$userId],["dataSrc",'=',5]])->where('left(createTime,7)="'.date('Y-m').'"')->order('createTime desc')->find();
		if(!$userscores){
			$userscores = $this->getEModel('user_scores');
		}
		$strToTime = date("Y-m-d",strtotime($userscores['createTime']));
      	if($strToTime!=$time){
      		$rs = Db::name('users')->where(["userId"=>$userId])->field('userScore')->find();
      		$days = $score = 0;
      		$days = ($strToTime==$frontTime)?($userscores['dataId']==30)?$userscores['dataId']:$userscores['dataId']+1:1;
      		$signScore = explode(",",WSTConf('CONF.signScore'));
      		if($signScore[0]!=0){
      			  $score = $signScore[$days-1];
      		}
			$data = [];  
			$data['totalScore'] = $rs['userScore'] + $score;
      		$data['score'] = $score;
      		if($score>0){
      			//添加
      			$userinfo = session('WST_USER');
      			$userinfo['signScoreTime'] = $time;
      			session('WST_USER',$userinfo);
      			$uscore = [];
      			$uscore['userId'] = $userId;
      			$uscore['score'] = $score;
      			$uscore['dataSrc'] = 5;
      			$uscore['dataId'] = $days;
      			$uscore['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'sign_score_tips','params'=>[$days,$score]]);
      			$uscore['scoreType'] = 1;
      			$this->add($uscore,true);
      			return WSTReturn(lang('sign_the_day_score',[$days,$score]),1,$data);
      		}else{
      			return WSTReturn(lang('sign_fail'));
      		}
      	}else{
      		return WSTReturn(lang('has_sign_tips'));
      	}
      }
}
