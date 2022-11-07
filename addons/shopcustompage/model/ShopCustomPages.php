<?php
namespace addons\shopcustompage\model;
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
 * 店铺首页自定义布局业务处理
 */
class ShopCustomPages extends Base{
    /***
     * 安装插件
     */
    public function installMenu(){
        Db::startTrans();
        try{
            $hooks = array("mobileDocumentShopHomeDisplay","wechatDocumentShopHomeDisplay","homeDocumentShopHomeDisplay");
            $this->bindHoods("Shopcustompage", $hooks);
            // 上传目录
            $dataLangParams = [
                1=>['dataName'=>'店鋪自定義佈局'],
                2=>['dataName'=>'店铺首页自定义布局'],
                3=>['dataName'=>'Shop homepage custom layout'],
            ];
            $datas = [];
            $dataId = Db::name('datas')->insertGetId(['catId'=>3,'dataVal'=>'shopcustompagedecoration']);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['dataId'] = $dataId;
                $data['langId'] = $v['id'];
                $data['dataName'] = $dataLangParams[$v['id']]['dataName'];
                $datas[] = $data;
            }
            Db::name('datas_langs')->insertAll($datas);

            $now = date("Y-m-d H:i:s");
            //商家中心
            $homeMenuLangParams = [
                1=>['menuName'=>'自定義頁面'],
                2=>['menuName'=>'自定义页面'],
                3=>['menuName'=>'Custom page'],
            ];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>38,"menuUrl"=>"/addon/shopcustompage-shop-index","menuOtherUrl"=>"/addon/shopcustompage-shop-pageQuery,/addon/shopcustompage-shop-add,/addon/shopcustompage-shop-edit,/addon/shopcustompage-shop-del","menuType"=>1,"isShow"=>1,"menuSort"=>6,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"shopcustompage"]);
            $datas = [];
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $homeMenuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('home_menus_langs')->insertAll($datas);
            installSql("shopcustompage");
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
            $hooks = array("mobileDocumentShopHomeDisplay","wechatDocumentShopHomeDisplay","homeDocumentShopHomeDisplay");
            $this->unbindHoods("Shopcustompage", $hooks);
            $dataId = Db::name('datas')->where(["dataVal"=>"shopcustompagedecoration"])->value('id');
            Db::name('datas')->where(["dataVal"=>"shopcustompagedecoration"])->delete();
            Db::name('datas_langs')->where(["dataId"=>$dataId])->delete();

            $homeMenuId = Db::name('home_menus')->where(["menuMark"=>"shopcustompage"])->value('menuId');
            Db::name('home_menus')->where(["menuMark"=>"shopcustompage"])->delete();
            Db::name('home_menus_langs')->where(['menuId'=>$homeMenuId])->delete();
            uninstallSql("shopcustompage");//传入插件名
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
            Db::name('home_menus')->where(["menuMark"=>"shopcustompage"])->update(["isShow"=>$isShow]);
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
        $shopId = (int)session('WST_USER.shopId');
        $rs = $this->where(['shopId'=>$shopId,'dataFlag'=>1,'pageType'=>(int)input('type')])->field('*')->order('id asc')->select();
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
        $shopId = (int)session('WST_USER.shopId');
        $id = (int)input('id',0);
        $val = (int)input('val',0);
        $type = (int)input('type');
        $result = $this->where('id','eq',$id)->where(['pageType'=>$type,'shopId'=>$shopId])->setField('isIndex', $val);
        $result2 = $this->where('id','neq',$id)->where(['pageType'=>$type,'shopId'=>$shopId])->setField('isIndex', 0);
        if(false !== $result && false !== $result2){
            return WSTReturn(lang("shopcustompage_operation_success"), 1);
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
        $result2 = Db::name("shop_custom_page_decoration")->where(['pageId'=>$id])->update(['dataFlag'=>-1]);
        if(false !== $result && false !== $result2){
            return WSTReturn(lang("shopcustompage_operation_success"), 1);
        }else{
            return WSTReturn($this->getError(),-1);
        }
    }

    /**
     * 复制自定义页面静态文件
     */
    public function copyCustomPage(){
        $shopId = (int)session('WST_USER.shopId');
        $pageId = input('id',0);
        $pageType = input('type');
        if($pageId<1)WSTReturn(lang("shopcustompage_illegal_request"),-1);
        $pageData = Db::name('shop_custom_pages')->where(['id'=>$pageId])->find();
        if(empty($pageData))WSTReturn(lang("shopcustompage_page_no_exist"),-1);
        $pageDecorationData =  Db::name('shop_custom_page_decoration')->field('*')->where(['dataFlag'=>1,'pageId'=>$pageId])->order('sort asc')->select();
        $copyPageData = [
            "shopId"=>$shopId,
            'pageName'=>$pageData['pageName'],
            'isIndex'=>0,
            'createTime'=>date('Y-m-d H:i:s'),
            'dataFlag'=>1,
            'pageType'=>$pageData['pageType'],
            "attr"=>$pageData['attr'],
        ];
        Db::startTrans();
        try{
            $copyPageId = Db::name('shop_custom_pages')->insertGetId($copyPageData);
            foreach($pageDecorationData as $k => $v){
                $copyPageDecorationData = [
                    "pageId"=>$copyPageId,
                    "name"=>$v['name'],
                    "attr"=>$v['attr'],
                    "createTime"=>date("Y-m-d H:i:s",time()),
                    "dataFlag"=>1,
                    "sort"=>$v['sort']
                ];
                Db::name('shop_custom_page_decoration')->insert($copyPageDecorationData);
            }
            if(in_array($pageType,[1,2,4])){
                $sourceFileName = 'custom_page_shop_home_'.$pageId.'.html';
                $destFileName = 'custom_page_shop_home_'.$copyPageId.'.html';
                switch ($pageType){
                    case 1:
                        $filePath = WSTRootPath() . "/addons/shopcustompage/view/tpl/mobile/";
                        break;
                    case 2:
                        $filePath = WSTRootPath() . "/addons/shopcustompage/view/tpl/wechat/";
                        break;
                    case 4:
                        $filePath = WSTRootPath() . "/addons/shopcustompage/view/tpl/home/";
                        break;
                }
                if(!is_dir($filePath)){
                    if (!@mkdir($filePath, 0755)){
                        return WSTReturn(lang("shopcustompage_page_copy_fail"),-1);
                    }
                }
                $sourceFilePath = $filePath.$sourceFileName;
                if(!file_exists($sourceFilePath)){
                    return WSTReturn(lang("shopcustompage_create_page_tips"),-1);
                }
                $destFilePath = $filePath.$destFileName;
                $rs = copy($sourceFilePath,$destFilePath);
                if(false !== $rs){
                    Db::commit();
                    return WSTReturn(lang("shopcustompage_operation_success"),1);
                }
            }
            Db::commit();
            return WSTReturn(lang("shopcustompage_operation_success"),1);
        }catch (\Exception $e) {
            Db::rollback();
            return WSTReturn(lang('shopcustompage_operation_fail'),-1);
        }
    }
}
