function toEdits(id,p){
    var params = WST.getParams('.ipt');
    params.id = id;
    var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    var type = $('#type').val();
	$.post(WST.U('admin/brandapplys/handleApply'),params,function(data,textStatus){
		  layer.close(loading);
		  var json = WST.toAdminJson(data);
		  if(json.status=='1'){
		    	WST.msg(json.msg,{icon:1});
			    location.href=WST.U('admin/brands/index',"p="+p+'&type='+type);
		  }else{
		        WST.msg(json.msg,{icon:2});
		  }
	});
}