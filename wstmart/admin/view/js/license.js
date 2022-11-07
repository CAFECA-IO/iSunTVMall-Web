function edit(){
	var params = {};
	params.license = $('#license').val();
	$('#licenseTr').hide();
	$('#editFrom').isValid(function(v){
	if(v){
		var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		$.post(WST.U('admin/index/verifyLicense'),params,function(data,textStatus){
			layer.close(loading);
			var json = WST.toAdminJson(data);
			if(json.status=='1' && json.license){
				$('#licenseTr').show();
				$('#licenseStatus').html(json.license.licenseStatus);
			}else{
				WST.msg("授权码验证失败",{icon:1});
			}
		});
	}});
}  