<?php
namespace addons\member\model;
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
 * 会员营销业务处理
 */
class Member extends Base{
    /***
     * 安装插件
     */
    public function installMenu(){
        Db::startTrans();
        try{
            $hooks = array("afterUserRegist","mobileControllerIndexIndex","mobileDocumentUserIndexTools","wechatControllerIndexIndex","wechatDocumentUserIndexTools");
            $this->bindHoods("Member", $hooks);
            // 上传目录
            $dataLangParams = [
                1=>['dataName'=>'會員營銷'],
                2=>['dataName'=>'会员营销'],
                3=>['dataName'=>'Member Marketing'],
            ];
            $datas = [];
            $dataId = Db::name('datas')->insertGetId(['catId'=>3,'dataVal'=>'member']);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['dataId'] = $dataId;
                $data['langId'] = $v['id'];
                $data['dataName'] = $dataLangParams[$v['id']]['dataName'];
                $datas[] = $data;
            }
            Db::name('datas_langs')->insertAll($datas);
            //后台管理中心
            // 新增菜单权限
            $datas = [];
            $menuLangParams = [
                1=>['menuName'=>'會員營銷'],
                2=>['menuName'=>'会员营销'],
                3=>['menuName'=>'Member Marketing'],
            ];
            $menuId = Db::name('menus')->insertGetId(["parentId"=>15,"menuSort"=>1,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"member"]);
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $menuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $menuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('menus_langs')->insertAll($datas);

            if($menuId!==false){
                $datas = [];
                $menuLangParams = [
                    1=>['menuName'=>'營銷設置'],
                    2=>['menuName'=>'营销设置'],
                    3=>['menuName'=>'Marketing Settings'],
                ];
                $menuId = Db::name('menus')->insertGetId(["parentId"=>$menuId,"menuSort"=>1,"dataFlag"=>1,"isShow"=>1,"menuMark"=>"member"]);
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['menuId'] = $menuId;
                    $data['langId'] = $v['id'];
                    $data['menuName'] = $menuLangParams[$v['id']]['menuName'];
                    $datas[] = $data;
                }
                Db::name('menus_langs')->insertAll($datas);
                $privilegeLangParams = [
                    1=>['privilegeName_00'=>'查看營銷設置','privilegeName_02'=>'編輯營銷設置'],
                    2=>['privilegeName_00'=>'查看营销设置','privilegeName_02'=>'编辑营销设置'],
                    3=>['privilegeName_00'=>'View marketing settings','privilegeName_02'=>'Edit marketing settings'],
                ];
                $privilegeIds = [];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"MEMBER_YXSZ_00","isMenuPrivilege"=>1,"privilegeUrl"=>"/addon/member-admin-index","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
                $privilegeIds[] = ['privilegeId'=>$privilegeId,'code'=>'00'];
                $privilegeId = Db::name('privileges')->insertGetId(["menuId"=>$menuId,"privilegeCode"=>"MEMBER_YXSZ_02","isMenuPrivilege"=>0,"privilegeUrl"=>"/addon/member-admin-edit","otherPrivilegeUrl"=>"","dataFlag"=>1,"isEnable"=>1]);
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

            $now = date("Y-m-d H:i:s");
            //用户中心
            $homeMenuLangParams = [
                1=>['menuName'=>'我的邀請'],
                2=>['menuName'=>'我的邀请'],
                3=>['menuName'=>'My invitation'],
            ];
            $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>100,"menuUrl"=>"#","menuOtherUrl"=>"","menuType"=>0,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"member"]);
            $datas = [];
            foreach (WSTSysLangs() as $key => $v) {
                $data = [];
                $data['menuId'] = $homeMenuId;
                $data['langId'] = $v['id'];
                $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                $datas[] = $data;
            }
            Db::name('home_menus_langs')->insertAll($datas);

            if($homeMenuId!==false){
                $homeMenuLangParams = [
                    1=>['menuName'=>'我邀請的用戶'],
                    2=>['menuName'=>'我邀请的用户'],
                    3=>['menuName'=>'My invitation'],
                ];
                $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$homeMenuId,"menuUrl"=>"addon/member-member-usermemberusers","menuOtherUrl"=>"addon/member-member-querymineusers","menuType"=>0,"isShow"=>1,"menuSort"=>1,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"member"]);
                $datas = [];
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['menuId'] = $homeMenuId;
                    $data['langId'] = $v['id'];
                    $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                    $datas[] = $data;
                }
                Db::name('home_menus_langs')->insertAll($datas);

                $homeMenuLangParams = [
                    1=>['menuName'=>'邀請獎勵'],
                    2=>['menuName'=>'邀请奖励'],
                    3=>['menuName'=>'Invite rewards'],
                ];
                $homeMenuId = Db::name('home_menus')->insertGetId(["parentId"=>$homeMenuId,"menuUrl"=>"addon/member-member-usermemberawards","menuOtherUrl"=>"addon/member-member-queryuserawards","menuType"=>0,"isShow"=>1,"menuSort"=>2,"dataFlag"=>1,"createTime"=>$now,"menuMark"=>"member"]);
                $datas = [];
                foreach (WSTSysLangs() as $key => $v) {
                    $data = [];
                    $data['menuId'] = $homeMenuId;
                    $data['langId'] = $v['id'];
                    $data['menuName'] = $homeMenuLangParams[$v['id']]['menuName'];
                    $datas[] = $data;
                }
                Db::name('home_menus_langs')->insertAll($datas);
            }
            installSql("member");
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
            $hooks = array("afterUserRegist","mobileControllerIndexIndex","mobileDocumentUserIndexTools","wechatControllerIndexIndex","wechatDocumentUserIndexTools");
            $this->unbindHoods("Member", $hooks);
            $dataId = Db::name('datas')->where(["dataVal"=>"member"])->value('id');
            Db::name('datas')->where(["dataVal"=>"member"])->delete();
            Db::name('datas_langs')->where(["dataId"=>$dataId])->delete();

            $menuId = Db::name('menus')->where(["menuMark"=>"member"])->value('menuId');
            Db::name('menus')->where(["menuMark"=>"member"])->delete();
            Db::name('menus_langs')->where(['menuId'=>$menuId])->delete();

            $homeMenuIds = Db::name('home_menus')->where(["menuMark"=>"member"])->column('menuId');
            Db::name('home_menus')->where(["menuMark"=>"member"])->delete();
            Db::name('home_menus_langs')->where([['menuId','in',$homeMenuIds]])->delete();

            $privilegeIds = Db::name('privileges')->where("privilegeCode","like","MEMBER_%")->column('privilegeId');
            Db::name('privileges')->where("privilegeCode","like","MEMBER_%")->delete();
            Db::name('privileges_langs')->where([['privilegeId','in',$privilegeIds]])->delete();

            uninstallSql("member");//传入插件名
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
            Db::name('menus')->where("menuMark",'=',"member")->update(["isShow"=>$isShow]);
            Db::commit();
            return true;
        }catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 获取配置
     */
    public function getAddonConfig(){
        $addon = Db::name('addons')->where("name","Member")->field("config")->find();
        $config = json_decode($addon["config"],true);
        return $config;
    }

    /*
     * 编辑会员营销设置
     */
    public function edit(){
        $config = [];
        $config['recommendSwitch'] = input('recommendSwitch',0);
        $config['registerSwitch'] = input('registerSwitch',0);
        $config['registerScore'] = input('registerScore',0);
        $config['mallShareTitle'] = input('mallShareTitle');
        $config['posterBg'] = input('posterBg');
        $recommendNum = (array)input("recommendNum");
        $recommendScore = (array)input("recommendScore");
        Db::startTrans();
        try{
            $res = Db::name('addons')->where("name","Member")->update(['config'=>json_encode($config)]);
            if(false !== $res){
                if(count($recommendNum)>0) {
                    Db::name('member_recommend_configs')->where(['dataFlag'=>1])->delete();
                    for ($i = 0; $i < count($recommendNum); $i++) {
                        $data = [
                            'recommendNum' => $recommendNum[$i],
                            'score' => $recommendScore[$i],
                            'createTime' => date('Y-m-d H:i:s')
                        ];
                        Db::name('member_recommend_configs')->insert($data);
                    }
                }
            }
            Db::commit();
            return WSTReturn("操作成功", 1);
        }catch (\Exception $e) {
            Db::rollback();
        }
        return WSTReturn("操作失败");
    }

    /*
     * 获取会员营销推荐配置
     */
    public function getMemberRecommendConfigs(){
        $rs = Db::name('member_recommend_configs')->where(['dataFlag'=>1])->select();
        return (!empty($rs))?$rs:'';
    }

    /**
     * 用户注册设置
     */
    public function userRegist($userId){
        $isWeapp = (int)input("isWeapp");
        $recommendUserId = 0;
        if($isWeapp==1){
            $recommendUserId = (int)input("shareUserId");
        }else{
            $recommendUserId = (int)session("WST_shareUserId");
        }
        $addon = Db::name('addons')->where("name","Member")->field("config")->find();
        if($addon){
            $config = json_decode($addon["config"],true);
            Db::startTrans();
            try{
                if((int)$config['registerSwitch']==1 && (int)$config['registerScore']>0){
                    $registerScore = (int)$config['registerScore'];
                    $uscore = [];
                    $uscore['userId'] = $userId;
                    $uscore['score'] = $registerScore;
                    $uscore['dataSrc'] = 6;
                    $uscore['dataId'] = 1;
                    $uscore['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'addon_member_new_user_score','params'=>[$registerScore]]);
                    $uscore['scoreType'] = 1;
                    $uscore["createTime"] = date("Y-m-d H:i:s");
                    model("common/UserScores")->add($uscore);
                }
                if($recommendUserId>0 && (int)$config['recommendSwitch']==1){
                    $recommendUserNum = Db::name('member_users')->where(['parentId'=>$recommendUserId])->count();
                    $recommendUserNum = ($recommendUserNum==0)?1:$recommendUserNum+1;
                    $recommendScore = Db::name('member_recommend_configs')->where('recommendNum','<=',$recommendUserNum)->max('score');
                    if($recommendScore>0){
                        $dataId = Db::name('user_scores')->where(['userId'=>$recommendUserId,'dataSrc'=>6])->max('dataId');
                        $uscore = [];
                        $uscore['userId'] = $recommendUserId;
                        $uscore['score'] = $recommendScore;
                        $uscore['dataSrc'] = 7;
                        $uscore['dataId'] = (empty($dataId))?1:$dataId+1;
                        $uscore['dataRemarks'] = json_encode(['type'=>'lang_params','key'=>'addon_member_new_user_score1','params'=>[$recommendScore]]);
                        $uscore['scoreType'] = 1;
                        $uscore["createTime"] = date("Y-m-d H:i:s");
                        model("common/UserScores")->add($uscore);
                    }

                    $data = [];
                    $data["parentId"] = $recommendUserId;
                    $data["userId"] = $userId;
                    $data["createTime"] = date("Y-m-d H:i:s");
                    $data['ip'] = request()->ip();
                    Db::name('member_users')->insert($data);
                }
                Db::commit();
            }catch (\Exception $e) {
                Db::rollback();
            }
        }
        return true;
    }

    /**
     * 会员中心
     */
    public function getUserInfo($uId=0){
        $userId = $uId==0?session('WST_USER.userId'):$uId;
        $user =  Db::name('users')->where(['userId'=>$userId])->find();
        if($user['userName']=='')$user['userName']=$user['loginName'];
        $cnt = Db::name('member_users')->where(['parentId'=>$userId])->count();
        $user["userCnt"] = $cnt;
        $score = Db::name('user_scores')->where(['dataSrc'=>7,'userId'=>$userId])->sum('score');
        $user["memberScore"] = $score;
        return $user;
    }

    /*
    * 生成分享海报
    */
    public function createPoster($userId,$qr_code,$outImg){
        $cfg = self::getAddonConfig();
        $user = Db::name("users")->where(["userId"=>$userId])->find();
        //生成二维码图片
        $share_bg = WSTConf('CONF.resourceDomain').'/'.$cfg["posterBg"];
        $share_bg = imagecreatefromstring(file_get_contents($share_bg));
        $new_qrcode = imagecreatefromstring(file_get_contents($qr_code));

        $share_width = imagesx($share_bg);//二维码图片宽度
        $share_height = imagesy($share_bg);//二维码图片高度

        $zhen = imagecreatetruecolor($share_width, $share_height);
        imagecolortransparent($zhen, imagecolorallocatealpha($zhen, 0, 0, 0, 127));
        imagealphablending($zhen, false);
        imagesavealpha($zhen, true);
        imagecopyresized($zhen,$share_bg,0,0,0,0,$share_width,$share_height,$share_width,$share_height);
        $share_bg = $zhen;

        $new_width = imagesx($new_qrcode);//logo图片宽度
        $new_height = imagesy($new_qrcode);//logo图片高度
        $new_qr_width = 230;
        $new_qr_height = $new_qr_width;
        $from_width = ($share_width - $new_qr_width) / 2;

        //重新组合图片并调整大小
        imagecopyresampled($share_bg, $new_qrcode, $from_width, 346, 0, 0, $new_qr_width,
            $new_qr_height, $new_width, $new_height);
        imagedestroy($new_qrcode);

        $new_qrcode = WSTUserPhoto($user["userPhoto"]);
        if(substr($new_qrcode,0,4)!='http' && $new_qrcode){
            $new_qrcode = WSTConf('CONF.resourceDomain').'/'.($user["userPhoto"]?$user["userPhoto"]:WSTConf('CONF.userLogo'));
            $tmpImg = WSTRootPath().'/upload/shares/member/'.date("Y-m").'/'.$userId.'.jpg';
            $new_qrcode = WSTCutFillet($new_qrcode, $tmpImg);
            $new_qrcode = imagecreatefromstring(file_get_contents($new_qrcode));
        }else{
            $new_qrcode = imagecreatefromstring(file_get_contents($new_qrcode));
        }

        //重新组合图片并调整大小
        WSTImagecopymergeAlpha($share_bg, $new_qrcode, 280, 154, 0, 0, 100,  100, 100);

        // 先保存图片，再重新打开【直接写入字体背景色为透明】
        $shareImg = WSTRootPath().'/'.$outImg;
        imagepng($share_bg, $shareImg);
        imagedestroy($share_bg);
        $share_bg = imagecreatefromstring(file_get_contents($shareImg));

        // 字体文件
        $textcolor = imagecolorat($share_bg, 115, 88);
        $font = WSTRootPath().'/extend/verify/verify/ttfs/SourceHanSerifCN-Medium.otf';
        //$text = mb_convert_encoding('长按识别微信二维码', "html-entities", "utf-8"); //转成html编码

        $text = lang('addon_member_new_code_tips');
        // 文字宽度
        $letterW = WSTImageLetterWidth(13, 0, $font, $text, 400);
        $from_width = ($share_width - $letterW)/2;
        imagettftext($share_bg, 13, 0, $from_width, 625, $textcolor, $font, $text);
        $userName = $user["userName"]?$user["userName"]:$user["loginName"];
        $text = WSTImageAutoWrap(30, 0, $font, $userName,400);

        // 文字起始位置 = (图片宽度 - 文字宽度)/2;
        // 文字宽度
        $letterW = WSTImageLetterWidth(30, 0, $font, $userName, 400);
        $from_width = ($share_width - $letterW)/2 + 10;
        imagettftext($share_bg, 28, 0, $from_width, $new_qr_height+58, $textcolor, $font, $text);

        //输出图片
        $shareImg = WSTRootPath().'/'.$outImg;
        imagepng($share_bg, $shareImg);
        imagedestroy($new_qrcode);
        imagedestroy($share_bg);
        unlink($qr_code);
        return WSTReturn("",1,["shareImg"=>$outImg]);
    }

    /**
     * 我的推荐用户
     */
    public function queryMineUsers($uId=0){
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $where = array();
        $where[] = ["mu.parentId",'=',$userId];
        $userName = input("userName");
        if($userName!=""){
            $where[] = ["u.userName|u.loginName","like","%".$userName."%"];
        }
        $rs = Db::name('member_users mu')
            ->join("__USERS__ u","u.userId=mu.userId")
            ->where($where)
            ->field("u.userId,u.userName,u.userSex,u.loginName,u.createTime,u.userPhoto")
            ->order('u.userId', 'desc')
            ->paginate(input('pagesize/d'))->toArray();
        foreach ($rs['data'] as $key => $v){
            $rs['data'][$key]['userPhoto'] = WSTUserPhoto($v['userPhoto']);
        }
        return $rs;
    }

    /**
     * 用户奖励记录
     */
    public function queryUserAwards($uId=0){
        $userId = ($uId==0)?(int)session('WST_USER.userId'):$uId;
        $where = array();
        $where[] = ['dataSrc','=',7];
        $where[] = ['userId','=',$userId];
        $rs = Db::name('user_scores')
            ->where($where)
            ->field("scoreId,dataSrc,dataRemarks,score,scoreType,createTime")
            ->order('scoreId', 'desc')
            ->paginate(input('pagesize/d'))->toArray();
        foreach ($rs['data'] as $key => $v){
            $rs['data'][$key]['dataSrc'] = WSTLangScore($v['dataSrc']);
        }
        return $rs;
    }
}
