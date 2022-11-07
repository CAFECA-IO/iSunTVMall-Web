<?php
namespace addons\txlive\model;
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
 * 腾讯云直播业务处理
 */
class TxLive extends Base{
    /***
     * 安装插件
     */
    public function installMenu(){
        Db::startTrans();
        try{
            $hooks = ["afterUserLogin",'afterUserRegist'];
            $this->bindHoods("Txlive", $hooks);
            $dataLangParams = [
                1=>['dataName'=>'騰訊雲直播間申請'],
                2=>['dataName'=>'腾讯云直播间申请'],
                3=>['dataName'=>'Tencent cloud live apply'],
            ];
            $datas = [];
            $dataId = Db::name('datas')->insertGetId(['catId'=>3,'dataVal'=>'txliveapplys']);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['dataId'] = $dataId;
                $data['langId'] = $v['id'];
                $data['dataName'] = $dataLangParams[$v['id']]['dataName'];
                $datas[] = $data;
            }
            Db::name('datas_langs')->insertAll($datas);
            //管理员后台
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'騰訊雲直播'],
                2=>['menuName'=>'腾讯云直播'],
                3=>['menuName'=>'Tencent cloud live'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>93,"menuSort"=>1,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"txlive"]);
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
                    1=>['privilegeName_00'=>'查看直播','privilegeName_03'=>'删除直播'],
                    2=>['privilegeName_00'=>'查看直播','privilegeName_03'=>'删除直播'],
                    3=>['privilegeName_00'=>'View live','privilegeName_03'=>'Delete live'],
                ];
                $privilegeIds = [];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"TXLIVE_ZBGL_00","isMenuPrivilege"=>1,"privilegeUrl"=>"/addon/txlive-admin-index","otherPrivilegeUrl"=>"/addon/txlive-admin-pageQuery","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'00'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"TXLIVE_ZBGL_03","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/txlive-admin-del","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
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
                1=>['menuName'=>'騰訊雲直播'],
                2=>['menuName'=>'腾讯云直播'],
                3=>['menuName'=>'Tencent cloud live'],
            ];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>77,"menuUrl"=>"/addon/txlive-shops-index","menuOtherUrl"=>"/addon/txlive-shops-pageQuery,addon/txlive-shops-del","menuType"=>1,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"txlive"]);
            $datas = [];
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $homeMenuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('home_menus_langs')->insertAll($datas);
            $this->addMobileBtn();
            installSql("txlive");
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
            $hooks = ["afterUserLogin",'afterUserRegist'];
            $this->unbindHoods("Txlive", $hooks);
            $dataId = Db::name('datas')->where(["dataVal"=>"txliveapplys"])->value('id');
            Db::name('datas')->where(["dataVal"=>"txliveapplys"])->delete();
            Db::name('datas_langs')->where(["dataId"=>$dataId])->delete();

            $menuId = Db::name('menus')->where(["menuMark"=>"txlive"])->value('menuId');
            Db::name('menus')->where(["menuMark"=>"txlive"])->delete();
            Db::name('menus_langs')->where(['menuId'=>$menuId])->delete();

            $homeMenuId = Db::name('home_menus')->where(["menuMark"=>"txlive"])->value('menuId');
            Db::name('home_menus')->where(["menuMark"=>"txlive"])->delete();
            Db::name('home_menus_langs')->where(['menuId'=>$homeMenuId])->delete();

            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","TXLIVE_%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","TXLIVE_%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();
            $this->delMobileBtn();
            uninstallSql("txlive");
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
            Db::name('menus')->where("menuMark",'=',"txlive")->update(["isShow"=>$isShow]);
            Db::name('home_menus')->where(["menuMark"=>"txlive"])->update(["isShow"=>$isShow]);
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

    public function addMobileBtn(){
        // 手机端
//        $data = array();
//        $data["btnName"] = "直播";
//        $data["btnSrc"] = 0;
//        $data["btnUrl"] = "addon/txlive-txlives-molists.html";
//        $data["btnImg"] = "addons/txlive/view/images/txlive.png";
//        $data["addonsName"] = "Txlive";
//        $data["btnSort"] = 16;
//        Db::name('mobile_btns')->insert($data);

        $langParams = [
            1=>['btnName'=>'直播'],
            2=>['btnName'=>'直播'],
            3=>['btnName'=>'Live'],
        ];
        // app端
        if(WSTDatas('ADS_TYPE',4)){
            $datas = [];
            $data = array();
            $data["btnSrc"] = 3;
            $data["btnUrl"] = "wst://TxLive";
            $data["btnImg"] = "addons/txlive/view/images/txlive.png";
            $data["addonsName"] = "Txlive";
            $data["btnSort"] = 16;
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
        // 小程序端
        if(WSTDatas('ADS_TYPE',5)){
            $datas = [];
            $data = array();
            $data["btnSrc"] = 2;
            $data["btnUrl"] = "/addons/package/pages/txlive/lives/list";
            $data["btnImg"] = "addons/txlive/view/images/txlive.png";
            $data["addonsName"] = "Txlive";
            $data["btnSort"] = 16;
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

    public function delMobileBtn(){
        $btnIds =  Db::name('mobile_btns')->where(["addonsName"=>"Txlive"])->column('id');
        Db::name('mobile_btns')->where(["addonsName"=>"Txlive"])->delete();
        Db::name('mobile_btns_langs')->where([['btnId','in',$btnIds]])->delete();
    }

    /**
     * 获取配置
     */
    public function getAddonConfig(){
        $addon = Db::name('addons')->where("name","Txlive")->field("config")->find();
        $config = json_decode($addon["config"],true);
        return $config;
    }
}
