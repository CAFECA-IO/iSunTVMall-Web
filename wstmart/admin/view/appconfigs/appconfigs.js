$(function(){
	// 初始化上传
	_initUpload("appLogo");
})
function edit(){
	if(!WST.GRANT.WX_GZHSZ_04)return;
	var params = WST.getParams('.ipt');
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/appconfigs/edit'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toAdminJson(data);
          if(json.status==1){
        	  WST.msg(json.msg,{icon:1});
          }
   });
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