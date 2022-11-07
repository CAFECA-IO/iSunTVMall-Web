<?php
use think\Db;

function getShopUserUrls($userId){
    $privilegeUrls = Db::name('shop_users')->alias('su')
                                ->join('shop_roles sr','su.roleId=sr.id','inner')
                                ->where(['su.userId'=>$userId])
                                ->value('privilegeUrls');
    if(!empty($privilegeUrls)){
        $rs = json_decode($privilegeUrls, true);
        $rs = WSTShopAppGetUrls($rs);
        return $rs;
    }
    return [];
}

function WSTShopAppGetUrls($arr){
    $urls = [];
    foreach ($arr as $k=>$v) {
        if(is_array($v)){
           $urls = array_merge($urls, WSTShopAppGetUrls($v));
           continue;
        }
        if(!is_array($v) && $v!='')array_push($urls, $v);   
    }
    return array_unique($urls);
}

// 根据插件名称获取插件状态
function WSTGetAddonStatus($addonName){
    return Db::name('addons')->where('dataFlag',1)->where('name', $addonName)->field('name,title,status')->find();
}