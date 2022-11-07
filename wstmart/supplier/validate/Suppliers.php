<?php 
namespace wstmart\supplier\validate;
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
 * 供货商验证器
 */
class Suppliers extends Validate{
	protected $rule = [
        //入驻步骤1
        'applyLinkMan' => 'require',
        'applyLinkTel' => 'require',
        'applyLinkEmail' => 'require',
        'isInvestment' => 'in:0,1',
        'investmentStaff' => 'checkInvestment:1',
        //入驻步骤2
        'businessLicenceType' => 'require',
        'businessLicence' => 'require',
        'legalPersonName' => 'require',
        'businessAreaPath0' => 'require',
        'licenseAddress' => 'require',
        'establishmentDate' => 'require',
        'businessStartDate' => 'require',
        'businessEndDate' => 'checkBusinessEndDate:1',
        'isLongbusinessDate' => 'in:0,1',
        'registeredCapital' => 'require',
        'empiricalRange' => 'require',
        'areaIdPath0' => 'require',
        'supplierCompany' => 'require',
        'supplierAddress' => 'require',
        'supplierTel' => 'require',
        'supplierkeeper' => 'require',
        'telephone' => 'require',
        'legalCertificateType' => 'require',
        'legalCertificate' => 'require',
        'legalCertificateStartDate' => 'require',
        'legalCertificateEndDate' => 'checkLegalCertificateEndDate:1',
        'isLonglegalCertificateDate' => 'in:0,1',
        'legalCertificateImg' => 'require',
        'businessLicenceImg' => 'require',
        'bankAccountPermitImg' => 'require',
        'organizationCode' => 'require',
        'organizationCodeStartDate' => 'require',
        'organizationCodeEndDate' => 'checkOrganizationCodeEndDate:1',
        'isLongOrganizationCodeDate' => 'in:0,1',
        'organizationCodeImg' => 'require',
        //入驻步骤3
        'taxpayerType' => 'require',
        'taxpayerNo' => 'require',
        'taxRegistrationCertificateImg' => 'require',
        'taxpayerQualificationImg' => 'require',
        'bankUserName' => 'require|max:100',
        'bankNo' => 'require',
        'bankId' => 'require',
        'bankAreaId' => 'require',
        //入驻步骤4
        'supplierName' => 'require',
        'supplierImg' => 'require',
        'goodsCatIds' => 'require',
        'isInvoice' => 'in:0,1',
        'invoiceRemarks' => 'checkInvoiceRemark:1',
        'serviceStartTime' => 'require',
        'longitude' => 'checkLocation',
        'latitude' => 'checkLocation',
        'mapLevel' => 'checkLocation',
        'serviceEndTime' => 'require'
    ];
	
	protected $message  =  [
        //入驻步骤1
        'applyLinkMan.require' => '{%require_applyLinkMan}',
        'applyLinkTel.require' => '{%require_applyLinkTel}',
        'applyLinkEmail.require' => '{%require_applyLinkEmail}',
        'isInvestment.in' => '{%in_isInvestment}',
        'investmentStaff.checkInvestment' => '{%please_enter_the_name_of_the_merchants_in_the_mall}',
        //入驻步骤2
        'businessLicenceType.require' => '{%require_businessLicenceType}',
        'businessLicence.require' => '{%require_businessLicence}',
        'legalPersonName.require' => '{%require_legalPersonName}',
        'businessAreaPath0.require' => '{%require_businessAreaPath0}',
        'licenseAddress.require' => '{%require_licenseAddress}',
        'establishmentDate.require' => '{%require_establishmentDate}',
        'businessStartDate.require' => '{%require_businessStartDate}',
        'businessEndDate.checkBusinessEndDate' => '{%checkBusinessEndDate_businessEndDate}',
        'isLongbusinessDate.in' => '{%in_isLongbusinessDate}',
        'registeredCapital.require' => '{%require_registeredCapital}',
        'empiricalRange.require' => '{%require_empiricalRange}',
        'areaIdPath0.require' => '{%require_areaIdPath0}',
        'supplierCompany.require' => '{%require_shopCompany}',
        'supplierAddress.require' => '{%require_shopAddress}',
        'supplierTel.require' => '{%require_shopTel}',
        'supplierkeeper.require' => '{%require_shopkeeper}',
        'telephone.require' => '{%require_telephone}',
        'legalCertificateType.require' => '{%require_legalCertificateType}',
        'legalCertificate.require' => '{%require_legalCertificate}',
        'legalCertificateStartDate.require' => '{%require_legalCertificateStartDate}',
        'legalCertificateEndDate.checkLegalCertificateEndDate' => '{%checkLegalCertificateEndDate_legalCertificateEndDate}',
        'isLonglegalCertificateDate.in' => '{%in_isLonglegalCertificateDate}',
        'legalCertificateImg.require' => '{%require_legalCertificateImg}',
        'businessLicenceImg.require' => '{%require_businessLicenceImg}',
        'bankAccountPermitImg.require' => '{%require_bankAccountPermitImg}',
        'organizationCode.require' => '{%require_organizationCode}',
        'organizationCodeStartDate.require' => '{%require_organizationCodeStartDate}',
        'organizationCodeEndDate.checkOrganizationCodeEndDate' => '{%checkOrganizationCodeEndDate_organizationCodeEndDate}',
        'isLongOrganizationCodeDate.in' => '{%in_isLongOrganizationCodeDate}',
        'organizationCodeImg.require' => '{%require_organizationCodeImg}',
        //入驻步骤3
        'taxpayerType.require' => '{%require_taxpayerType}',
        'taxpayerNo.require' => '{%require_taxpayerNo}',
        'taxRegistrationCertificateImg.require' => '{%require_taxRegistrationCertificateImg}',
        'taxpayerQualificationImg.require' => '{%require_taxpayerQualificationImg}',
        'bankUserName.require' => '{%require_bankUserName}',
		'bankUserName.max' => '{%max_bankUserName}',
        'bankNo.require' => '{%require_bankNo}',
        'bankId.require' => '{%require_bankId}',
        'bankAreaId.require' => '{%require_bankAreaId}',
        //入驻步骤4
        'supplierName.require' => '{%require_supplierName}',
        'supplierImg.require' => '{%require_supplierImg}',
        'goodsCatIds.require' => '{%require_goodsCatIds}',
        'isInvoice.in' => '{%in_isInvoice}',
        'invoiceRemarks.checkInvoiceRemark' => '{%checkInvoiceRemark_invoiceRemarks}',
        'serviceStartTime.require' => '{%require_serviceStartTime}',
        'longitude.checkLocation' => '{%checkLocation_longitude}',
        'latitude.checkLocation' => '{%checkLocation_longitude}',
        'mapLevel.checkLocation' => '{%checkLocation_longitude}',
        'serviceEndTime.require' => '{%require_serviceEndTime}'
	];

 
    public $scene = [
        'editInfo'  =>['supplierImg','isInvoice','serviceStartTime','serviceEndTime'],
        'editBank'  =>['bankId','bankAreaId','bankNo','bankUserName'],
        'applyStep1'=>['applyLinkMan','applyLinkTel','applyLinkEmail','isInvestment','investmentStaff'],
        'applyStep2'=>['businessLicenceType','businessLicence','legalPersonName','businessAreaPath0','licenseAddress','establishmentDate','businessStartDate','businessEndDate','isLongbusinessDate','registeredCapital','empiricalRange','areaIdPath0','supplierCompany','supplierAddress','supplierTel','supplierkeeper','telephone','supplierEmergencyLinkMan','legalCertificateType','legalCertificate','legalCertificateStartDate','legalCertificateEndDate','isLonglegalCertificateDate','legalCertificateImg','businessLicenceImg','bankAccountPermitImg','organizationCode','organizationCodeStartDate','organizationCodeEndDate','isLongOrganizationCodeDate','organizationCodeImg','longitude','latitude','mapLevel'],
        'applyStep3'=>['taxpayerType','taxpayerNo','taxRegistrationCertificateImg','taxpayerQualificationImg','bankUserName','bankNo','bankId','bankAreaId'],
        'applyStep4'=>['supplierName','supplierImg','goodsCatIds','isInvoice','invoiceRemarks','serviceStartTime','serviceEndTime']
    ]; 
    
    protected function checkInvoiceRemark($value){
    	$isInvoice = input('post.isInvoice/d',0);
    	$key = Input('post.invoiceRemarks');
    	return ($isInvoice==1 && $key=='')?lang("please_enter_invoice_description"):true;
    }

    protected function checkInvestment($value){
        $isInvestment = input('post.isInvestment/d',0);
        $key = Input('post.investmentStaff');
        return ($isInvestment==1 && $key=='')?lang("please_enter_the_name_of_the_merchants_in_the_mall"):true;
    }

    protected function checkBusinessEndDate($value){
        $isLongbusinessDate = input('post.isLongbusinessDate/d',0);
        $key = Input('post.businessEndDate');
        return ($isLongbusinessDate==0 && $key=='')?lang("please_enter_the_closing_date_of_the_business_term"):true;
    }
    protected function checkLegalCertificateEndDate($value){
        $isLonglegalCertificateDate = input('post.isLonglegalCertificateDate/d',0);
        $key = Input('post.legalCertificateEndDate');
        return ($isLonglegalCertificateDate==0 && $key=='')?lang("Please_select_the_end_date_of_the_validity_period_of_the_legal_representative"):true;
    }
    protected function checkOrganizationCodeEndDate($value){
        $isLonglegalCertificateDate = input('post.isLongOrganizationCodeDate/d',0);
        $key = Input('post.organizationCodeEndDate');
        return ($isLonglegalCertificateDate==0 && $key=='')?lang("please_enter_the_end_date_of_the_validity_period_of_the_organization_code_certificate"):true;
    }
    protected function checkLocation($value){
        $longitude = (float)input('post.longitude',0);
        $latitude = (float)input('post.latitude',0);
        $mapLevel = input('post.mapLevel',0);
        if(WSTConf('CONF.mapKey') == ''){
            return true;
        }else{
            return ($longitude==0 ||  $latitude==0 || $mapLevel==0)?lang("checkLocation_longitude"):true;
        }

    }
}