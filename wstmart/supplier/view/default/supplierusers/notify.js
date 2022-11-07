var layer = layui.layer;
$(function(){
	layui.form.on('checkbox',function (data) {
		var obj = $(data['elem']);
		$(obj).attr('checked',!$(obj).attr('checked'));
	});
})
function edit(){
	var params = WST.getParams('.ipt');
	if(params['privilegeMsgTypes'].length==0 && params["privilegeMsgs"].length>0){
		WST.msg(WST.lang('require_supp_users_msg_type'),{icon:1});
		return;
	}
	if(params['privilegeMsgTypes'].length>0 && params["privilegeMsgs"].length==0){
		WST.msg(WST.lang('require_supp_users_msg'),{icon:1});
		return;
	}
	var loading = WST.msg(WST.lang('submitting_data'), {icon: 16,time:60000});
    $.post(WST.U('supplier/supplierusers/editNotifyConfig'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status==1){
        	  WST.msg(json.msg,{icon:1});
          }
   });
}
