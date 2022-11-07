var mmg;
function initGrid(){
	var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_payment_name1'), name:'payName', width: 30},
            {title:WST.lang('label_payment_txt'), name:'payDesc' },
            {title:WST.lang('label_payment_status'), name:'enabled', renderer: function(val,item,rowIndex){
            	return (val==1)?"<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('label_payment_status1')+"</span>":"<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('label_payment_status0')+"</span>";
            }},
            {title:WST.lang('sort'), name:'payOrder' },
            {title:WST.lang('op'),name:'op' ,width:80, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(item['enabled']==1){
		            if(WST.GRANT.ZFGL_02)h += "<a  class='btn btn-blue' href='"+WST.U('admin/payments/toEdit','id='+item['id']+'&payCode='+item['payCode'])+"'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
		            if(WST.GRANT.ZFGL_03)h += "<a  class='btn btn-red' href='javascript:toDel(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('label_payment_uninstall')+"</a> ";
	            }
	            else{
	            	if(WST.GRANT.ZFGL_02)h += "<a class='btn btn-blue' href='"+WST.U('admin/payments/toEdit','id='+item['id']+'&payCode='+item['payCode'])+"'><i class='fa fa-gear'></i>"+WST.lang('install')+"</a> ";
	            }
	            return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: (h-49),indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/payments/pageQuery'), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });      
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('label_payment_uninstall_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/payments/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('op_ok'),{icon:1});
	           			    	layer.close(box);
	           		            mmg.load();
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}


function edit(id){
	//获取所有参数
	var params = WST.getParams('.ipt');
	//接收配置信息并转成JSON
	var configs = WST.getParams('.cfg');
	//保 存配置信息
	params.payConfig = configs;
	params.id = id;
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	$.post(WST.U('admin/payments/'+((id==0)?"add":"edit")),params,function(data,textStatus){
	  layer.close(loading);
	  var json = WST.toAdminJson(data);
	  if(json.status=='1'){
	      WST.msg(WST.lang('op_ok'),{icon:1});
	      location.href=WST.U('Admin/payments/index');
	  }else{
	        WST.msg(json.msg,{icon:2});
	  }
	});
}

$(function(){
	$('#payForm').validator({
      fields: {
      		/*默认验证*/
            payName: {rule:"required;",msg:{required:WST.lang('require_payment_name')},tip:WST.lang('require_payment_name'),ok:"",},
            payDesc: {rule:"required;",msg:{required:WST.lang('require_payment_txt')},tip:WST.lang('require_payment_txt'),ok:"",},
            payOrder: {rule:"required;",msg:{required:WST.lang('require_sort')},tip:WST.lang('require_sort'),ok:"",},
            /*微信验证*/
            appId: {rule:"required;",msg:{required:WST.lang('require_payment_app_id')},tip:WST.lang('require_payment_app_id'),ok:"",},
            mchId: {rule:"required;",msg:{required:WST.lang('require_weixinpay_no')},tip:WST.lang('require_weixinpay_no'),ok:"",},
            apiKey: {rule:"required;",msg:{required:WST.lang('require_weixinpay_pri_key')},tip:WST.lang('require_weixinpay_pri_key'),ok:"",},
            appsecret: {rule:"required;",msg:{required:WST.lang('require_payment_app_secret')},tip:WST.lang('require_payment_app_secret'),ok:"",},
            /*支付宝验证*/
            payAccount: {rule:"required;",msg:{required:WST.lang('require_aplipay_name')},tip:WST.lang('require_aplipay_name'),ok:"",},
            parterID: {rule:"required;",msg:{required:WST.lang('require_aplipay_parter_id')},tip:WST.lang('require_aplipay_parter_id'),ok:"",},
            parterKey: {rule:"required;",msg:{required:WST.lang('require_aplipay_aq_key')},tip:WST.lang('require_aplipay_aq_key'),ok:"",},
            /*银联支付验证*/
            unionAccount: {rule:"required;",msg:{required:WST.lang('require_paymeny_un_name')},tip:WST.lang('require_paymeny_un_name'),ok:"",},
            unionSignCertPwd: {rule:"required;",msg:{required:WST.lang('require_paymeny_cert_pass')},tip:WST.lang('require_paymeny_cert_pass'),ok:"",},
            /*APP支付宝支付*/
            rsaPrivateKey: {rule:"required;",msg:{required:WST.lang('require_patment_pri_key')},tip:WST.lang('require_patment_pri_key'),ok:"",},
            alipayrsaPublicKey: {rule:"required;",msg:{required:WST.lang('require_aplipay_public_key')},tip:WST.lang('require_aplipay_public_key'),ok:"",}
            
        },
        valid:function(form){
          edit($('#id').val())
        },
  });

});