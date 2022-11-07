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
		WST.msg(WST.lang("chose_notice_type"),{icon:1});
		return;
	}
	if(params['privilegeMsgTypes'].length>0 && params["privilegeMsgs"].length==0){
		WST.msg(WST.lang("chose_notice_ops"),{icon:1});
		return;
	}
	var loading = WST.msg(WST.lang("waiting"), {icon: 16,time:60000});
    $.post(WST.U('store/storeusers/editNotifyConfig'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status==1){
        	  WST.msg(json.msg,{icon:1});
          }
   });
}