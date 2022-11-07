<?php
namespace addons\recommend\model;
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
 * 关键词推荐业务处理
 */
class Recommend extends Base{
    /***
     * 安装插件
     */
    public function installMenu(){
        Db::startTrans();
        try{
            $hooks = array("afterUserSearchWords","userSearchWordsCondition");
            $this->bindHoods("Recommend", $hooks);
            //后台管理中心
            // 新增菜单权限
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'商品搜索'],
                2=>['menuName'=>'商品搜索'],
                3=>['menuName'=>'Product search'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>57,"menuSort"=>0,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"recommend","menuIcon"=>"thumbs-up"]);
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
                    1=>['privilegeName_00'=>'查看商品搜索日誌','privilegeName_02'=>'編輯關聯商品'],
                    2=>['privilegeName_00'=>'查看商品搜索日志','privilegeName_02'=>'编辑关联商品'],
                    3=>['privilegeName_00'=>'View product search log','privilegeName_02'=>'Edit related products'],
                ];
                $privilegeIds = [];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"RECOMMEND_SPSSRZ_00","isMenuPrivilege"=>1,"privilegeUrl"=>"/addon/recommend-logsearchwords-index","otherPrivilegeUrl"=>"/addon/recommend-logsearchwords-pageQuery","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'02'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"RECOMMEND_SPSSRZ_02","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/recommend-logsearchwords-toedit","otherPrivilegeUrl"=>"/addon/recommend-logsearchwords-edit","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'02'];
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
            installSql("recommend");
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
            $hooks = array("afterUserSearchWords","userSearchWordsCondition");
            $this->unbindHoods("Recommend", $hooks);
            $menuId = Db::name('menus')->where(["menuMark"=>"recommend"])->value('menuId');
            Db::name('menus')->where(["menuMark"=>"recommend"])->delete();
            Db::name('menus_langs')->where(['menuId'=>$menuId])->delete();

            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","RECOMMEND_%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","RECOMMEND_%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();
            uninstallSql("recommend");//传入插件名
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
            Db::name('menus')->where("menuMark",'=',"recommend")->update(["isShow"=>$isShow]);
            Db::commit();
            return true;
        }catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }
}
