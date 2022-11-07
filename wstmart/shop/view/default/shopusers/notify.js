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
		WST.msg(WST.lang('shopUsers_tips6'),{icon:1});
		return;
	}
	if(params['privilegeMsgTypes'].length>0 && params["privilegeMsgs"].length==0){
		WST.msg(WST.lang('shopUsers_tips7'),{icon:1});
		return;
	}
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('shop/shopusers/editNotifyConfig'),params,function(data,textStatus){
          layer.close(loading);
          var json = WST.toJson(data);
          if(json.status==1){
        	  WST.msg(json.msg,{icon:1});
          }
   });
}