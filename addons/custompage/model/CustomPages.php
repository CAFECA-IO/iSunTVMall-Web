<?php
namespace addons\custompage\model;
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
 * 首页自定义布局业务处理
 */
class CustomPages extends Base{
    /***
     * 安装插件
     */
    public function installMenu(){
        Db::startTrans();
        try{
            $hooks = array("mobileDocumentIndexDisplay","wechatDocumentIndexDisplay","mobileDocumentIndexFooter","wechatDocumentIndexFooter","homeDocumentIndexDisplay");
            $this->bindHoods("Custompage", $hooks);
            // 上传目录
            $dataLangParams = [
                1=>['dataName'=>'首页自定义布局'],
                2=>['dataName'=>'首頁自定義布局'],
                3=>['dataName'=>'Home page custom layout'],
            ];
            $datas = [];
            $dataId = Db::name('datas')->insertGetId(['catId'=>3,'dataVal'=>'custompagedecoration']);
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
                1=>['menuName'=>'自定義頁面'],
                2=>['menuName'=>'自定义页面'],
                3=>['menuName'=>'Custom page'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>2,"menuSort"=>102,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"custompage"]);
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
                    1=>[
                        'privilegeName_00'=>'查看自定義頁面',
                        'privilegeName_01'=>'新增自定義頁面',
                        'privilegeName_02'=>'編輯自定義頁面',
                        'privilegeName_03'=>'刪除自定義頁面'
                    ],
                    2=>[
                        'privilegeName_00'=>'查看自定义页面',
                        'privilegeName_01'=>'新增自定义页面',
                        'privilegeName_02'=>'编辑自定义页面',
                        'privilegeName_03'=>'删除自定义页面'
                    ],
                    3=>[
                        'privilegeName_00'=>'View custom page',
                        'privilegeName_01'=>'Add custom page',
                        'privilegeName_02'=>'Edit custom page',
                        'privilegeName_03'=>'Delete custom page'
                    ],
                ];
                $privilegeIds = [];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"CUSTOMPAGE_CPGL_00","isMenuPrivilege"=>1,"privilegeUrl"=>"/addon/custompage-admin-index","otherPrivilegeUrl"=>"/addon/custompage-admin-pageQuery","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'00'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"CUSTOMPAGE_CPGL_01","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/custompage-admin-add","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'01'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"CUSTOMPAGE_CPGL_02","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/custompage-admin-edit","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'02'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"CUSTOMPAGE_CPGL_03","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/custompage-admin-del","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
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
            installSql("custompage");
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
            $hooks = array("mobileDocumentIndexDisplay","wechatDocumentIndexDisplay","mobileDocumentIndexFooter","wechatDocumentIndexFooter","homeDocumentIndexDisplay");
            $this->unbindHoods("Custompage", $hooks);
            $dataId = Db::name('datas')->where(["dataVal"=>"custompagedecoration"])->value('id');
            Db::name('datas')->where(["dataVal"=>"custompagedecoration"])->delete();
            Db::name('datas_langs')->where(["dataId"=>$dataId])->delete();

            $menuId = Db::name('menus')->where(["menuMark"=>"custompage"])->value('menuId');
            Db::name('menus')->where(["menuMark"=>"custompage"])->delete();
            Db::name('menus_langs')->where(['menuId'=>$menuId])->delete();

            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","CUSTOMPAGE__%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","CUSTOMPAGE__%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();
            uninstallSql("custompage");//传入插件名
            Db::commit();
            return true;
        }catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 删除静态文件
     */
    public function delTplFile($path){
        //扫描一个文件夹内的所有文件夹和文件并返回数组
        $res = scandir($path);
        foreach($res as $val) {
            //排除目录中的.和..
            if ($val != "." && $val != "..") {
                //如果是文件直接删除
                unlink($path . $val);
            }
        }
    }


    /**
     * 菜单显示隐藏
     */
    public function toggleShow($isShow = 1){
        Db::startTrans();
        try{
            Db::name('menus')->where("menuMark",'=',"custompage")->update(["isShow"=>$isShow]);
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
        $rs = $this->where(['dataFlag'=>1,'pageType'=>(int)input('type')])->field('*')->order('id asc')->select();
        foreach($rs as $k => $v){
            $pageAttrData = unserialize($v['attr']);
            $rs[$k]['pagePoster'] = $pageAttrData['poster'];
        }
        return ['list'=>$rs];
    }
    /*
     * 设置是否为首页
     */
    public function editIsIndex(){
        $id = (int)input('id',0);
        $val = (int)input('val',0);
        $type = (int)input('type');
        $result = $this->where('id','eq',$id)->where(['pageType'=>$type])->setField('isIndex', $val);
        $result2 = $this->where('id','neq',$id)->where(['pageType'=>$type])->setField('isIndex', 0);
        if(false !== $result && false !== $result2){
            return WSTReturn(lang("custompage_operation_success"), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
    }

    /**
     * 删除
     */
    public function del(){
        $id = input('id',0);
        $data = [];
        $data['dataFlag'] = -1;
        $result = $this->update($data,['id'=>$id]);
        $result2 = Db::name("custom_page_decoration")->where(['pageId'=>$id])->update(['dataFlag'=>-1]);
        if(false !== $result && false !== $result2){
            return WSTReturn(lang("custompage_operation_success"), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
    }

    /**
     * 复制自定义页面静态文件
     */
    public function copyCustomPage(){
        $pageId = input('id',0);
        $pageType = input('type');
        if($pageId<1)WSTReturn(lang("custompage_illegal_request"),-1);
        $pageData = Db::name('custom_pages')->where(['id'=>$pageId])->find();
        if(empty($pageData))WSTReturn(lang("custompage_page_no_exist"),-1);
        $pageDecorationData =  Db::name('custom_page_decoration')->field('*')->where(['dataFlag'=>1,'pageId'=>$pageId])->order('sort asc')->select();
        $copyPageData = [
            'pageName'=>$pageData['pageName'],
            'isIndex'=>0,
            'createTime'=>date('Y-m-d H:i:s'),
            'dataFlag'=>1,
            'pageType'=>$pageData['pageType'],
            "attr"=>$pageData['attr'],
        ];
        Db::startTrans();
        try{
            $copyPageId = Db::name('custom_pages')->insertGetId($copyPageData);
            foreach($pageDecorationData as $k => $v){
                $copyPageDecorationData = [
                    "pageId"=>$copyPageId,
                    "name"=>$v['name'],
                    "attr"=>$v['attr'],
                    "createTime"=>date("Y-m-d H:i:s",time()),
                    "dataFlag"=>1,
                    "sort"=>$v['sort']
                ];
                Db::name('custom_page_decoration')->insert($copyPageDecorationData);
            }
            if(in_array($pageType,[1,2,4])){
                $sourceFileName = 'custom_page_index_'.$pageId.'.html';
                $destFileName = 'custom_page_index_'.$copyPageId.'.html';
                switch ($pageType){
                    case 1:
                        $filePath = WSTRootPath() . "/addons/custompage/view/tpl/mobile/";
                        break;
                    case 2:
                        $filePath = WSTRootPath() . "/addons/custompage/view/tpl/wechat/";
                        break;
                    case 4:
                        $filePath = WSTRootPath() . "/addons/custompage/view/tpl/home/";
                        break;
                }
                if(!is_dir($filePath)){
                    if (!@mkdir($filePath, 0755)){
                        return WSTReturn(lang("custompage_page_copy_fail"),-1);
                    }
                }
                $sourceFilePath = $filePath.$sourceFileName;
                if(!file_exists($sourceFilePath)){
                    return WSTReturn(lang("custompage_create_page_tips"),-1);
                }
                $destFilePath = $filePath.$destFileName;
                $rs = copy($sourceFilePath,$destFilePath);
                if(false !== $rs){
                    Db::commit();
                    return WSTReturn(lang("custompage_operation_success"),1);
                }
            }
            Db::commit();
            return WSTReturn(lang("custompage_operation_success"),1);
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang("custompage_operation_fail"),-1);
        }
    }
}
