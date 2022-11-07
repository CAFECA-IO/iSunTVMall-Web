var laytpl = layui.laytpl;
$(function (){ 
	listQuery('home');
});
function listQuery(styleSys){
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	$.post(WST.U('admin/styles/listQueryBySys'),{styleSys:styleSys},function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
			var gettpl = document.getElementById('tblist').innerHTML;
	       	laytpl(gettpl).render(json.data, function(html){
	       		$('#style_'+styleSys).html(html);
	       	});
	       	$('.btn').click(function(){
                changeStyle($(this),$(this).attr('dataid'));
            });
		}
	});
}
function changeStyle(obj,id){
	if(obj.hasClass('btn-disabled'))return;
	var box = WST.confirm({content:WST.lang('styles_tips1'),yes:function(){
		var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		$.post(WST.U('admin/styles/changeStyle'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = WST.toAdminJson(data);
			if(json.status=='1'){
				WST.msg(json.msg,{icon:1});
				layer.close(box);
                $('.btn-disabled').attr('disabled',false).html("<i class='fa fa-check-circle'></i>"+WST.lang('addon_set_enable')).addClass('btn-success').removeClass('btn-disabled');
                $('.style_'+id).removeClass('btn-success').addClass('btn-disabled').attr('disabled',true).html("<i class='fa fa-check-circle'></i>"+WST.lang('in_application'));
			}else{
				WST.msg(json.msg,{icon:2});
			}
		});
	}});
}