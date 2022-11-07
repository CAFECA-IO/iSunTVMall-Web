var laytpl = layui.laytpl;
$(function (){ 
	listQuery('home',1);
});
function listQuery(styleSys,p,obj){
    var styleCat = '';
    if(obj){
        styleCat = $(obj).val();
    }
    var loading = WST.msg(WST.lang("loading"), {icon: 16,time:60000});
    $.post(WST.U('shop/shopstyles/listQueryBySys'),{styleSys:styleSys,p:p,styleCat:styleCat},function(data,textStatus){
        layer.close(loading);
        var json = WST.toJson(data);
        var pager = json.data.list;
        if(json.status=='1'){
            var gettpl = document.getElementById('tblist').innerHTML;
            laytpl(gettpl).render(json.data, function(html){
                $('#style_'+styleSys).html(html);
                $('.gImg').lazyload({ effect: "fadeIn",failurelimit : 10,skip_invisible : false,threshold: 200,placeholder:window.conf.RESOURCE_PATH+'/'+WST.conf.MALL_LOGO});
            });
            if(pager.last_page>1) {
                layui.use(['laypage'], function () {
                    var laypage = layui.laypage;
                    laypage.render({
                        elem: 'pager',
                        count:pager.total,
                        curr:pager.current_page,
                        jump: function (e, first) {
                            if (!first) {
                                listQuery(styleSys,e.curr,obj);
                            }
                        }
                    })
                });
            }
            $('.btn').click(function(){
                changeStyle($(this),$(this).attr('dataid'));
            });
        }
    });
}
function changeStyle(obj,id){
	if(obj.hasClass('btn-disabled'))return;
	var box = WST.confirm({content:WST.lang('confirm_enable_style'),yes:function(){
		var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		$.post(WST.U('shop/shopstyles/changeStyle'),{id:id},function(data,textStatus){
			layer.close(loading);
			var json = WST.toJson(data);
			if(json.status=='1'){
				WST.msg(json.msg,{icon:1});
				layer.close(box);
				$('.btn-disabled').attr('disabled',false).html("<i class='fa fa-check-circle'></i>"+WST.lang('enabled')+"").addClass('btn-success').removeClass('btn-disabled');
				$('.style_'+id).removeClass('btn-success').addClass('btn-disabled').attr('disabled',true).html("<i class='fa fa-check-circle'></i>"+WST.lang('in_application')+"");
			}else{
				WST.msg(json.msg,{icon:2});
			}
		});
	}});
}
