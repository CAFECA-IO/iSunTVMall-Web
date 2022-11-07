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
 * 秒杀插件
 */
class Seckill extends Base{

    /***
     * 安装插件
     */
    public function installMenu(){
    	Db::startTrans();
		try{
			$hooks = ['afterEditGoods','afterCancelOrder','adminDocumentHookSummary','mobileDocumentIndex','wechatDocumentIndex'];
			$this->bindHoods("Seckill", $hooks);
			//管理员后台
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'秒殺活動'],
                2=>['menuName'=>'秒杀活动'],
                3=>['menuName'=>'Seckill'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>93,"menuSort"=>1,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"seckill"]);
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
                    1=>['privilegeName_00'=>'查看秒殺活動','privilegeName_04'=>'秒殺活動操作','privilegeName_03'=>'刪除秒殺活動'],
                    2=>['privilegeName_00'=>'查看秒杀活动','privilegeName_04'=>'秒杀活动操作','privilegeName_03'=>'删除秒杀活动'],
                    3=>['privilegeName_00'=>'View seckill','privilegeName_04'=>'Seckill activity operation','privilegeName_03'=>'Delete seckill'],
                ];
                $privilegeIds = [];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"SECKILL_TGHD_00","isMenuPrivilege"=>1,"privilegeUrl"=>"/addon/seckill-admin-seckillPage","otherPrivilegeUrl"=>"/addon/seckill-admin-seckillPageQuery,/addon/seckill-admin-seckillPageAuditQuery","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'00'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"SECKILL_TGHD_04","isMenuPrivilege"=>0,"privilegeUrl"=>"","otherPrivilegeUrl"=>"/addon/seckill-admin-seckillAllow,/addon/seckill-admin-seckillIllegal","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'04'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"SECKILL_TGHD_03","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/seckill-admin-seckillDel","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'03'];
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

			$now = date("Y-m-d H:i:s");
            //商家中心
            $homeMenuLangParams = [
                1=>['menuName'=>'秒殺活動'],
                2=>['menuName'=>'秒杀活动'],
                3=>['menuName'=>'Seckill'],
            ];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>77,"menuUrl"=>"/addon/seckill-shop-index","menuOtherUrl"=>"/addon/seckill-shop-pageQuery,/addon/seckill-shop-searchGoods,/addon/seckill-shop-edit,/addon/seckill-shop-toEdit,/addon/seckill-shop-del","menuType"=>1,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"seckill"]);
            $datas = [];
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $homeMenuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('home_menus_langs')->insertAll($datas);

            // 系统数据
            $dataLangParams = [
                1=>[
                    'dataName_01'=>'秒殺活動審核通過',
                    'dataName_02'=>'秒殺活動審核不通過'
                ],
                2=>[
                    'dataName_01'=>'秒杀活动审核通过',
                    'dataName_02'=>'秒杀活动审核不通过'
                ],
                3=>[
                    'dataName_01'=>'seckill activity approved',
                    'dataName_02'=>'Audit failed seckill activity'
                ],
            ];
            $dataIds = [];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'SECKILL_ACTIVITY_ALLOW']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'01'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>6,'dataVal'=>'SECKILL_ACTIVITY_REJECT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'02'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_SECKILL_ACTIVITY_ALLOW']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'01'];
            $dataId = Db::name('datas')->insertGetId(['catId'=>9,'dataVal'=>'WX_SECKILL_ACTIVITY_REJECT']);
            $dataIds[] = ['dataId'=>$dataId,'code'=>'02'];
            $datas = [];
            for($i=0;$i<count($dataIds);$i++){
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['dataId'] = $dataIds[$i]['dataId'];
                    $data['langId'] = $v['id'];
                    $data['dataName'] = $dataLangParams[$v['id']]['dataName_'.$dataIds[$i]['code']];
                    $datas[] = $data;
                }
            }
            Db::name('datas_langs')->insertAll($datas);
			$this->addNavMenu();
			$this->addMobileBtn();
            installSql("seckill");
            $timeLangParams = [
                1=>[
                    'title_01'=>'8:00',
                    'title_02'=>'10:00',
                    'title_03'=>'12:00',
                    'title_04'=>'14:00',
                    'title_05'=>'16:00',
                ],
                2=>[
                    'title_01'=>'8:00',
                    'title_02'=>'10:00',
                    'title_03'=>'12:00',
                    'title_04'=>'14:00',
                    'title_05'=>'16:00',
                ],
                3=>[
                    'title_01'=>'8:00',
                    'title_02'=>'10:00',
                    'title_03'=>'12:00',
                    'title_04'=>'14:00',
                    'title_05'=>'16:00',
                ],
            ];
			$timeIds = [];
            $timeId = Db::name('seckill_time_intervals')->insertGetId(['startTime'=>'08:00:00','endTime'=>'10:00:00']);
            $timeIds[] = ['timeId'=>$timeId,'code'=>'01'];
            $timeId = Db::name('seckill_time_intervals')->insertGetId(['startTime'=>'10:00:00','endTime'=>'12:00:00']);
            $timeIds[] = ['timeId'=>$timeId,'code'=>'02'];
            $timeId = Db::name('seckill_time_intervals')->insertGetId(['startTime'=>'12:00:00','endTime'=>'14:00:00']);
            $timeIds[] = ['timeId'=>$timeId,'code'=>'03'];
            $timeId = Db::name('seckill_time_intervals')->insertGetId(['startTime'=>'14:00:00','endTime'=>'16:00:00']);
            $timeIds[] = ['timeId'=>$timeId,'code'=>'04'];
            $timeId = Db::name('seckill_time_intervals')->insertGetId(['startTime'=>'16:00:00','endTime'=>'22:00:00']);
            $timeIds[] = ['timeId'=>$timeId,'code'=>'05'];
            $datas = [];
            for($i=0;$i<count($timeIds);$i++){
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['timeId'] = $timeIds[$i]['timeId'];
                    $data['langId'] = $v['id'];
                    $data['title'] = $timeLangParams[$v['id']]['title_'.$timeIds[$i]['code']];
                    $datas[] = $data;
                }
            }
            Db::name('seckill_time_intervals_langs')->insertAll($datas);
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
	public function uninstallMenu(){
		Db::startTrans();
		try{
			$hooks = ['afterEditGoods','afterCancelOrder','adminDocumentHookSummary','mobileDocumentIndex','wechatDocumentIndex'];
			$this->unbindHoods("Seckill", $hooks);
            $menuId = Db::name('menus')->where(["menuMark"=>"seckill"])->value('menuId');
            Db::name('menus')->where(["menuMark"=>"seckill"])->delete();
            Db::name('menus_langs')->where(['menuId'=>$menuId])->delete();

            $homeMenuId = Db::name('home_menus')->where(["menuMark"=>"seckill"])->value('menuId');
            Db::name('home_menus')->where(["menuMark"=>"seckill"])->delete();
            Db::name('home_menus_langs')->where(['menuId'=>$homeMenuId])->delete();

            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","SECKILL_%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","SECKILL_%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();
            //删除微信参数数据
			$tplMsgIds = Db::name('template_msgs')->where([['tplCode','in',explode(',','SECKILL_ACTIVITY_ALLOW,SECKILL_ACTIVITY_REJECT,WX_SECKILL_ACTIVITY_ALLOW,WX_SECKILL_ACTIVITY_REJECT')]])
			  ->column('id');
			if((int)WSTConf('CONF.wxenabled')==1)Db::name('wx_template_params')->where([['parentId','in',$tplMsgIds]])->delete();
            $dataIds = Db::name('datas')->where([["dataVal",'like',"%SECKILL%"]])->column('id');
            Db::name('datas')->where([["dataVal",'like',"%SECKILL%"]])->delete();
            Db::name('datas_langs')->where([["dataId","in",$dataIds]])->delete();
			uninstallSql("seckill");//传入插件名
            $this->delNavMenu();
			$this->delMobileBtn();
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}

	/**
	 * 菜单显示隐藏
	 */
	public function toggleShow($isShow = 1){
		Db::startTrans();
		try{
			Db::name('menus')->where(["menuMark"=>"seckill"])->update(["isShow"=>$isShow]);
			Db::name('home_menus')->where(["menuMark"=>"seckill"])->update(["isShow"=>$isShow]);
			Db::name('navs')->where(["navUrl"=>"addon/seckill-goods-lists.html"])->update(["isShow"=>$isShow]);
			if($isShow==1){
				$this->addMobileBtn();
			}else{
				$this->delMobileBtn();
			}
			Db::commit();
			return true;
		}catch (\Exception $e) {
	 		Db::rollback();
	  		return false;
	   	}
	}

    public function addNavMenu(){
        $navData = [
            'navType'=>0,
            'navUrl'=>'addon/seckill-goods-lists.html',
            'isShow'=>1,
            'isOpen'=>0,
            'navSort'=>0,
            'createTime'=>date('Y-m-d H:i:s')
        ];
        $navId = Db::name('navs')->insertGetId($navData);
        $datas = [];
        $langParams = [
            1=>['navTitle'=>'秒殺'],
            2=>['navTitle'=>'秒杀'],
            3=>['navTitle'=>'seckill'],
        ];
        foreach (WSTSysLangs() as $key => $v) {
            $data = [];
            $data['navId'] = $navId;
            $data['langId'] = $v['id'];
            $data['navTitle'] = $langParams[$v['id']]['navTitle'];
            $datas[] = $data;
        }
        Db::name('navs_langs')->insertAll($datas);
    }

	public function addMobileBtn(){
        $langParams = [
            1=>['btnName'=>'秒殺'],
            2=>['btnName'=>'秒杀'],
            3=>['btnName'=>'seckill'],
        ];
        $datas = [];
		$data = array();
		$data["btnSrc"] = 0;
		$data["btnUrl"] = "addon/seckill-goods-molists.html";
		$data["btnImg"] = "addons/seckill/view/images/seckill.png";
		$data["addonsName"] = "Seckill";
		$data["btnSort"] = 5;
        $btnId = Db::name('mobile_btns')->insertGetId($data);
        foreach (WSTSysLangs() as $key => $v) {
            $data = [];
            $data['btnId'] = $btnId;
            $data['langId'] = $v['id'];
            $data['btnName'] = $langParams[$v['id']]['btnName'];
            $datas[] = $data;
        }
        Db::name('mobile_btns_langs')->insertAll($datas);

        $datas = [];
		$data = array();
		$data["btnSrc"] = 1;
		$data["btnUrl"] = "addon/seckill-goods-wxlists.html";
		$data["btnImg"] = "addons/seckill/view/images/seckill.png";
		$data["addonsName"] = "Seckill";
		$data["btnSort"] = 5;
        $btnId = Db::name('mobile_btns')->insertGetId($data);
        foreach (WSTSysLangs() as $key => $v) {
            $data = [];
            $data['btnId'] = $btnId;
            $data['langId'] = $v['id'];
            $data['btnName'] = $langParams[$v['id']]['btnName'];
            $datas[] = $data;
        }
        Db::name('mobile_btns_langs')->insertAll($datas);

		// app端
		if(WSTDatas('ADS_TYPE',4)){
            $datas = [];
			$data = array();
			$data["btnSrc"] = 3;
			$data["btnUrl"] = "wst://Seckill";
			$data["btnImg"] = "addons/seckill/view/images/seckill.png";
			$data["addonsName"] = "Seckill";
			$data["btnSort"] = 6;
            $btnId = Db::name('mobile_btns')->insertGetId($data);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['btnId'] = $btnId;
                $data['langId'] = $v['id'];
                $data['btnName'] = $langParams[$v['id']]['btnName'];
                $datas[] = $data;
            }
            Db::name('mobile_btns_langs')->insertAll($datas);
		}

        if(WSTDatas('ADS_TYPE',5)){
            $datas = [];
            $data = array();
            $data["btnSrc"] = 2;
            $data["btnUrl"] = "/addons/package/pages/seckill/goods/list";
            $data["btnImg"] = "addons/seckill/view/images/seckill.png";
            $data["addonsName"] = "Seckill";
            $data["btnSort"] = 5;
            $btnId = Db::name('mobile_btns')->insertGetId($data);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['btnId'] = $btnId;
                $data['langId'] = $v['id'];
                $data['btnName'] = $langParams[$v['id']]['btnName'];
                $datas[] = $data;
            }
            Db::name('mobile_btns_langs')->insertAll($datas);
        }
	}

    public function delNavMenu(){
        $navId = Db::name('navs')->where(['navUrl'=>'addon/seckill-goods-lists.html'])->value('id');
        Db::name('navs')->where(['navUrl'=>'addon/seckill-goods-lists.html'])->delete();
        Db::name('navs_langs')->where(['navId'=>$navId])->delete();
    }

	public function delMobileBtn(){
        $btnIds =  Db::name('mobile_btns')->where(["addonsName"=>"Seckill"])->column('id');
        Db::name('mobile_btns')->where(["addonsName"=>"Seckill"])->delete();
        Db::name('mobile_btns_langs')->where([['btnId','in',$btnIds]])->delete();
	}

}

