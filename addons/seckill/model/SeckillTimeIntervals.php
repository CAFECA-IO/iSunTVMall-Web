<?php
namespace addons\seckill\model;
use think\addons\BaseModel as Base;
use wstmart\common\model\GoodsCats;
use think\Db;
use wstmart\common\model\LogSms;
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
 * 秒杀时段
 */
class SeckillTimeIntervals extends Base{
	/**
     * 商家获取秒杀列表
     */
	public function queryPage(){
		$title = input("title");
		$where = [];
		$where[] = ["dataFlag",'=',1];
		if($title !='')$where[] = ['stil.title', 'like', '%'.$title.'%'];
		$rs = Db::name("seckill_time_intervals")
            ->alias('sti')
            ->join('__SECKILL_TIME_INTERVALS_LANGS__ stil','stil.timeId=sti.id and stil.langId='.WSTCurrLang())
            ->field('sti.*,stil.title')
			->where($where)
			->paginate(input('pagesize/d'))->toArray();
        return $rs;
	}

    /**
     * 商家获取秒杀列表
     */
	public function spQueryList($shopId){
		$seckillId = (int)input("seckillId");
		$title = input("title");
		$where = [];
		$where[] = ["sti.dataFlag",'=',1];
		if($title !='')$where[] = ['stil.title', 'like', '%'.$title.'%'];
		$rs = Db::name("seckill_time_intervals")
            ->alias('sti')
            ->join('__SECKILL_TIME_INTERVALS_LANGS__ stil','stil.timeId=sti.id and stil.langId='.WSTCurrLang())
			->join("seckill_goods sg","sti.id=sg.timeId and sg.dataFlag=1 and sg.shopId=".$shopId." and sg.seckillId=".$seckillId,"left")
			->field("sti.*,stil.title,count(sg.id) gcnt")
			->where($where)
			->group("sti.id")
			->order("sti.startTime")
			->paginate(input('pagesize/d'))->toArray();
        return $rs;
	}

	/**
     * 获取秒杀列表
     */
	public function queryList(){
		$where = [];
		$where[] = ["dataFlag",'=',1];
		$rs = Db::name("seckill_time_intervals")
            ->alias('sti')
            ->join('__SECKILL_TIME_INTERVALS_LANGS__ stil','stil.timeId=sti.id and stil.langId='.WSTCurrLang())
            ->field('sti.*,stil.title')
			->where($where)
			->order("startTime")
			->select();
        return $rs;
	}

	/**
	 * 获取秒杀时段
	 */
	public function getById(){
		$timeId = (int)input("timeId");
		$where = [];
		$where[] = ["id",'=',$timeId];
		$where[] = ["dataFlag",'=',1];
		$rs = Db::name("seckill_time_intervals")
            ->alias('sti')
            ->join('__SECKILL_TIME_INTERVALS_LANGS__ stil','stil.timeId=sti.id and stil.langId='.WSTCurrLang())
            ->field('sti.*,stil.title')
            ->where($where)->find();
        $rs['langs'] = Db::name('seckill_time_intervals_langs')->where(['timeId'=>$timeId])->column('*','langId');
        return $rs;
	}


	/**
	 * 新增秒杀时段
	 */
	public function add(){
		$data = input('post.');
		unset($data["id"]);
		if($data['startTime']>=$data['endTime'])return WSTReturn(lang('seckill_end_time_limit_tips'));
		$titleArr = [];
        foreach (WSTSysLangs() as $key => $v) {
            $titleArr[] = input('langParams'.$v['id'].'title');
        }
		Db::startTrans();
        try{
        	$where = [];
			$where[] = ["dataFlag",'=',1];
			$where[] = ['stil.title', 'in', $titleArr];
        	$times = $this
                ->alias('sti')
                ->join('__SECKILL_TIME_INTERVALS_LANGS__ stil','stil.timeId=sti.id and stil.langId='.WSTCurrLang())
                ->where($where)->find();
        	if(!empty($times))return WSTReturn(lang('seckill_time_name_exist_tips'));
			$result = $this->allowField(true)->save($data);
			if(false !== $result){
			    $timeId = $this->id;
                $dataLangs = [];
                foreach (WSTSysLangs() as $key => $v) {
                    $dataLang = [];
                    $dataLang['timeId'] = $timeId;
                    $dataLang['langId'] = $v['id'];
                    $dataLang['title'] = input('langParams'.$v['id'].'title');
                    $dataLangs[] = $dataLang;
                }
                Db::name('seckill_time_intervals_langs')->insertAll($dataLangs);
				Db::commit();
				return WSTReturn(lang('seckill_operation_success'), 1);
			}
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('seckill_operation_fail'),-1);
	}

	/**
	 * 编辑秒杀时段
	 */
	public function edit(){
		$timeId = input('post.timeId/d');
		$data = input('post.');
		if($data['startTime']>=$data['endTime'])return WSTReturn(lang('seckill_end_time_limit_tips'));
        $titleArr = [];
        foreach (WSTSysLangs() as $key => $v) {
            $titleArr[] = input('langParams'.$v['id'].'title');
        }
		Db::startTrans();
        try{
        	$where = [];
			$where[] = ["dataFlag",'=',1];
			$where[] = ["id",'<>',$timeId];
			$where[] = ['stil.title', 'in', $titleArr];
        	$times = $this
                ->alias('sti')
                ->join('__SECKILL_TIME_INTERVALS_LANGS__ stil','stil.timeId=sti.id and stil.langId='.WSTCurrLang())
                ->where($where)->find();
        	if(!empty($times))return WSTReturn(lang('seckill_time_name_exist_tips'));
			$result = $this->allowField(['startTime','endTime'])->save($data,['id'=>$timeId]);
			if(false !== $result){
                Db::name('seckill_time_intervals_langs')->where(['timeId'=>$timeId])->delete();
                $dataLangs = [];
                foreach (WSTSysLangs() as $key => $v) {
                    $dataLang = [];
                    $dataLang['timeId'] = $timeId;
                    $dataLang['langId'] = $v['id'];
                    $dataLang['title'] = input('langParams'.$v['id'].'title');
                    $dataLangs[] = $dataLang;
                }
                Db::name('seckill_time_intervals_langs')->insertAll($dataLangs);
				Db::commit();
				return WSTReturn(lang('seckill_operation_success'), 1);
			}
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn(lang('seckill_operation_fail'),-1);
	}

	/**
	 * 删除秒杀时段
	 */
	public function del(){
		$id = (int)input('timeId');
		$data = [];
		$data['id'] = $id;
        $rs = $this->update(['dataFlag'=>-1],$data);
        Db::startTrans();
		try{
			Db::name("seckill_goods")->where(['timeId'=>$id])->update(['dataFlag'=>-1]);
			Db::commit();
			return WSTReturn(lang('seckill_operation_success'), 1);
		}catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('seckill_operation_fail'),-1);
        }
	}

	/**
     * 获取当前秒杀时间列表
     */
	public function queryCurrList(){
		$where = [];
		$where[] = ["dataFlag",'=',1];
		$rs = Db::name("seckill_time_intervals")
            ->alias('sti')
            ->join('__SECKILL_TIME_INTERVALS_LANGS__ stil','stil.timeId=sti.id and stil.langId='.WSTCurrLang())
            ->field('sti.*,stil.title')
			->where($where)
			->order("startTime")
			->select();
		$currTime = date("H:i:s");
		$today = date("Y-m-d");
		$tomorrow = date("Y-m-d",strtotime("+1 day"));
		$times = [];
		$vtimes = [];
		foreach ($rs as $key => $vo) {
			$startTime = $vo["startTime"];
			$endTime = $vo["endTime"];
			$status = 0;//未开始
			if($currTime>$startTime && $currTime<=$endTime){//进行中
				$status = 1;
			}else if($currTime>$endTime){//已结束
				$status = 2;
			}
			if($status<2){
				if(count($times)<5){
					$rs[$key]["status"] = $status;
					$rs[$key]["startTime"] = $today." ".$startTime;
					$rs[$key]["endTime"] = $today." ".$endTime;
					$times[] = $rs[$key];
				}
			}else{
				$rs[$key]["status"] = 0;
				$rs[$key]["startTime"] = $tomorrow." ".$startTime;
				$rs[$key]["endTime"] = $tomorrow." ".$endTime;
				$vtimes[] = $rs[$key];
			}
		}
		foreach ($vtimes as $key => $vo) {
			if(count($times)<5){
				$times[] = $vo;
			}
		}
        return $times;
	}

    /**
     * 获取当前秒杀基础信息
     */
    public function queryCurrSecInfo(){
        $currTime = date("H:i:s");
        $where = [];
        $where[] = ["dataFlag",'=',1];
        $where[] = ['startTime','<',$currTime];
        $where[] = ['endTime','>=',$currTime];
        $rs = [];
        $rs = Db::name("seckill_time_intervals")
            ->alias('sti')
            ->join('__SECKILL_TIME_INTERVALS_LANGS__ stil','stil.timeId=sti.id and stil.langId='.WSTCurrLang())
            ->field('sti.*,stil.title')
            ->where($where)
            ->field('id,stil.title,startTime,endTime')
            ->find();
        if($rs){
            $rs['secTitle'] = substr($rs['title'],0,strpos($rs['title'],':'));
            $rs['secStartTime'] = date("Y-m-d").' '.$rs['startTime'];
            $rs['secEndTime'] = date("Y-m-d").' '.$rs['endTime'];
        }else{
            $rs['secTitle'] = '';
            $rs['secStartTime'] = '';
            $rs['secEndTime'] = '';
        }
        return $rs;
    }
}

