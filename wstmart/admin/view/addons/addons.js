var WST_CURR_PAGE;
$(function(){
	var element = layui.element;
	element.on('tab(msgTab)', function(data){
	   if(data.index==1)initAddons(0,0,20);
	   if(data.index==0){
           initAddons(1);
	       initAddons(2);
	   }
	});
	initAddons(1);
	initAddons(2);
})
function initAddons(status,p,limit){
	var params = {};
	params.status = status;
	params.page = p;
    params.limit = (limit)?limit:100;
    var loading = WST.msg(WST.lang('updating'), {icon: 16});
    $.post(WST.U('admin/addons/pageQuery'),params,function(data,textStatus){
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
			layer.close(loading);
			if(status==0 && params.page>json.data.last_page && json.data.last_page >0){
                initAddons(status,json.data.last_page,limit);
                return;
            }
            var pager  =  json.data;
			json.data.addonStatus = status;
	       	var gettpl = document.getElementById('addonsTpl').innerHTML;
	       	layui.laytpl(gettpl).render(json.data, function(html){
	       		$('#addonsBox'+status).html(html);
	       	});
	       	if(pager.last_page>1 && status==0) {
                layui.use(['laypage'], function () {
                    var laypage = layui.laypage;
                    laypage.render({
                        elem: 'pager',
                        limit:limit,
                        count:pager.total,
                        curr:pager.current_page,
                        jump: function (e, first) {
                            if (!first) {
                                initAddons(status,e.curr,e.limit);
                            }
                        }
                    })
                });
            }else{
            	$('#pager').empty();
            }
		}else{
			WST.msg(json.msg,{icon:2});
	     }
	});
}

//安装
function install(id){
	var loading = WST.msg(WST.lang('updating'), {icon: 16,time:60000});
	$.post(WST.U('admin/addons/install'),{id:id},function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
	       	WST.msg(WST.lang('op_ok'),{icon:1});
	        layer.close(loading);
	        initAddons(0,WST_CURR_PAGE,20);
		}else{
			WST.msg(json.msg,{icon:2});
	     }
	});
}

//卸载
function uninstall(id){
	var box = WST.confirm({content:WST.lang('addon_uninstall_tips'),yes:function(){
		var loading = WST.msg(WST.lang('updating'), {icon: 16,time:60000});
		$.post(WST.U('admin/addons/uninstall'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = WST.toAdminJson(data);
			if(json.status=='1'){
	        	WST.msg(WST.lang('op_ok'),{icon:1});
	         	layer.close(box);
	         	initAddons(1);
	         	initAddons(2);
			}else{
				WST.msg(json.msg,{icon:2});
	     	}
		});
	}});
}

//禁用
function enable(id){
	var loading = WST.msg(WST.lang('updating'), {icon: 16,time:60000});
	$.post(WST.U('admin/addons/enable'),{id:id},function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
	       	WST.msg(WST.lang('op_ok'),{icon:1});
	        layer.close(loading);
	        initAddons(1);
	        initAddons(2);
		}else{
			WST.msg(json.msg,{icon:2});
	     }
	});
}

//启用
function disable(id){
	var loading = WST.msg(WST.lang('updating'), {icon: 16,time:60000});
	$.post(WST.U('admin/addons/disable'),{id:id},function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
	       	WST.msg(WST.lang('op_ok'),{icon:1});
	        layer.close(loading);
	        initAddons(1);
	        initAddons(2);
		}else{
			WST.msg(json.msg,{icon:2});
	     }
	});
}

