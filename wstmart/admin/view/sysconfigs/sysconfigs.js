var layer = layui.layer;
var laytpl, form,laypage;
$(function(){
	form = layui.form;
  	form.render();
  	form.on('switch(mailOpen)', function(data){
	  	if(this.checked){
	  		WST.showHide(1,'.mailOpenTr')
	  	}else{
	  		WST.showHide(0,'.mailOpenTr')
	  	}
	});
	form.on('switch(seoMallSwitch)', function(data){
	  	if(this.checked){
	  		WST.showHide(0,'#close');
	  	}else{
	  		WST.showHide(1,'#close');
	  	}
	});
	form.on('switch(isCryptPwd)', function(data){
	  	if(this.checked){
	  		WST.showHide(1,'.pwdCryptKeyTr')
	  	}else{
	  		WST.showHide(0,'.pwdCryptKeyTr')
	  	}
	});
    var element = layui.element;
	element.on('tab(msgTab)', function(data){
	   if(data.index==3)initUploads();
	   if($(this).attr('isApp')==1 && !this.appLogo){
		   	this.appLogo=true;
			_initUpload('appLogo');
	   }
	});
});
var isInitUpload = false;
function initUploads(){
	if(isInitUpload)return;
	var uploads = ['watermarkFile','mallLogo','shopLogo','shopAdtop','userLogo','goodsLogo','goodsPosterBg'],key;
	for(var i=0;i<uploads.length;i++){
		key = uploads[i];
		_initUpload(key);
	}
	isInitUpload = true;
}
function _initUpload(key){
	WST.upload({
		k:key,
		pick:'#'+key+"Picker",
		formData: {dir:'sysconfigs'},
		accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
		callback:function(f){
			var json = WST.toAdminJson(f);
			if(json.status==1){
				$('#'+this.k+'Msg').empty().hide();
				$('#'+this.k+'Prevw').attr('src',WST.conf.RESOURCE_PATH+'/'+json.savePath+json.name);
				$('#'+this.k).val(json.savePath+json.name);
			}
		},
		progress:function(rate){
			$('#'+this.k+'Msg').show().html(WST.lang('upload_rate')+rate+"%");
		}
	});
}

function edit(){
	if(!WST.GRANT.SCPZ_02)return;
	var params = WST.getParams('.ipt');
	if(params.loginType==''){
		WST.msg(WST.lang('require_config_mall_login_pc_set'),{icon:1});
		return;
	}
	if(params.mobileLoginType==''){
		WST.msg(WST.lang('require_config_mall_login_mobile_set'),{icon:1});
		return;
	}
	if(params.registerType==''){
		WST.msg(WST.lang('require_config_mall_regist_pc_set'),{icon:1});
		return;
	}
	if(params.mobileRegisterType==''){
		WST.msg(WST.lang('require_config_mall_regist_mobile_set'),{icon:1});
		return;
	}
	if(params.mailOpen==1){
		var fieldObj = ['mailSmtp','mailPort','mailAddress','mailUserName','mailPassword','mailSendTitle'];
		var fieldTip = [WST.lang('config_mall_email_smtp'),WST.lang('config_mall_email_smtp_port'),WST.lang('config_mall_email_address'),WST.lang('config_mall_email_user_name'),WST.lang('config_mall_email_user_pass'),WST.lang('config_mall_email_send_title')];
		for(var i=0;i<fieldObj.length;i++){
			if(params[fieldObj[i]]==''){
				WST.msg(WST.lang('require')+fieldTip[i],{icon:1});
				return;
			}
		}
	}
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/sysconfigs/edit'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toAdminJson(data);
          if(json.status==1){
        	  WST.msg(json.msg,{icon:1});
          }else{
          	  WST.msg(json.msg,{icon:2});
          }
   });
}


$(function(){
	$('#watermarkColor').colpick({
	layout:'hex',
	submit:1,
	colorScheme:'dark',
	onChange:function(hsb,hex,rgb,el,bySetColor) {
		$(el).css('border-color','#'+hex);
	},
	onSubmit:function(hsb,hex,rgb,el,bySetColor){
		if(!bySetColor) $(el).val('#'+hex);
		$(el).colpickHide();
	}
	}).keyup(function(){
		$(this).colpickSetColor(this.value);
		$(this).colpickHide();
	});

});