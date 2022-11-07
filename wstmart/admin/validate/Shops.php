<?php

namespace wstmart\admin\validate;

use think\Validate;

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
 * 店铺验证器
 */
class Shops extends Validate
{
    protected $rule = [
        'shopSn' => 'checkShopSn:1|max:40',
        'shopName' => 'require|max:40',
        'shopCompany' => 'require|max:300',
        'shopTel' => 'require|max:40',
        'longitude' => 'checkLocation',
        'latitude' => 'checkLocation',
        'mapLevel' => 'checkLocation',
        'shopkeeper' => 'require|max:100',
        'telephone' => 'require|max:40',
        'isSelf' => 'in:0,1',
        'shopImg' => 'require',
        'areaId'  => 'require',
        'shopAddress' => 'require',
        'isInvoice' => 'in:0,1',
        'invoiceRemarks' => 'checkInvoiceRemark:1',
        'shopAtive' => 'in:0,1',
        'bankUserName' => 'require|max:100',
        'bankNo' => 'require',
        'bankId' => 'require',
        'bankAreaId' => 'require',
        'shopStatus' => 'in:-1,1',
        'statusDesc' => 'checkStatusDesc:1',
    ];

    protected $message = [
        'shopSn.checkShopSn' => '{%checkShopSn_shopSn}',
        'shopSn.max' => '{%max_shopSn}',
        'shopName.require' => '{%require_shopName}',
        'shopName.max' => '{%max_shopName}',
        'shopCompany.require' => '{%require_shopCompany}',
        'shopCompany.max' => '{%max_shopCompany}',
        'longitude.checkLocation' => '{%checkLocation_longitude}',
        'latitude.checkLocation' => '{%checkLocation_latitude}',
        'mapLevel.checkLocation' => '{%checkLocation_mapLevel}',
        'shopTel.require' => '{%require_shopTel}',
        'shopTel.max' => '{%max_shopTel}',
        'shopkeeper.require' => '{%require_shopkeeper}',
        'shopkeeper.max' => '{%max_shopkeeper}',
        'telephone.require' => '{%require_telephone}',
        'telephone.max' => '{%max_telephone}',
        'isSelf.in' => '{%in_isSelf}',
        'shopImg.require' => '{%require_shopImg}',
        'areaId.require' => '{%require_areaId}',
        'shopAddress.require' => '{%require_shopAddress}',
        'isInvoice.in' => '{%in_isInvoice}',
        'invoiceRemarks.checkInvoiceRemark' => '{%checkInvoiceRemark_invoiceRemarks}',
        'shopAtive.in' => '{%in_shopAtive}',
        'bankUserName.require' => '{%require_bankUserName}',
        'bankUserName.max' => '{%max_bankUserName}',
        'bankNo.require' => '{%require_bankNo}',
        'bankId.require' => '{%require_bankId}',
        'bankAreaId.require' => '{%require_bankAreaId}',
        'shopStatus.in' => '{%in_shopStatus}',
        'statusDesc.checkStatusDesc' => '{%checkStatusDesc_statusDesc}',
    ];

    protected $scene = [
        'add'   =>  [
            'shopSn', 'shopName', 'shopCompany', 'longitude', 'latitude', 'shopkeeper', 'telephone', 'shopCompany', 'shopTel', 'isSelf', 'shopImg',
            'areaId', 'shopAddress', 'isInvoice', 'shopAtive', 'bankId', 'bankAreaId', 'bankNo', 'bankUserName', 'shopAtive'
        ],
        'edit'  =>  [
            'shopSn', 'shopName', 'shopCompany', 'shopkeeper', 'telephone', 'shopCompany', 'shopTel', 'isSelf', 'shopImg',
            'areaId', 'shopAddress', 'isInvoice', 'shopAtive', 'bankId', 'bankAreaId', 'bankNo', 'bankUserName', 'shopAtive'
        ]
    ];

    protected function checkShopSn($value)
    {
        $shopId = Input('post.shopId/d', 0);
        $key = Input('post.shopSn');
        if ($shopId > 0) {
            if ($key == '') return lang('checkShopSn_shopSn');
            $isChk = model('Shops')->checkShopSn($key, $shopId);
            if ($isChk) return lang('the_store_number_already_exists');
        }
        return true;
    }

    protected function checkInvoiceRemark($value)
    {
        $isInvoice = Input('post.isInvoice/d', 0);
        $key = Input('post.invoiceRemarks');
        return ($isInvoice == 1 && $key == '') ? lang('checkInvoiceRemark_invoiceRemarks') : true;
    }

    protected function checkStatusDesc($value)
    {
        $shopStatus = Input('post.shopStatus/d', 0);
        $key = Input('post.statusDesc');
        return ($shopStatus == -1 && $key == '') ? lang('checkStatusDesc_statusDesc') : true;
    }
    protected function checkLocation($value)
    {
        $longitude = (float)input('post.longitude', 0);
        $latitude = (float)input('post.latitude', 0);
        $mapLevel = input('post.mapLevel', 0);
        if (WSTConf('CONF.mapKey') == '') {
            return true;
        } else {
            return ($longitude == 0 ||  $latitude == 0 || $mapLevel == 0) ? lang('checkLocation_longitude') : true;
        }
    }
}
