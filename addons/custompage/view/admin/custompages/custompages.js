var laytpl = layui.laytpl;
function changeCustomPageType(type){
    listQuery(type);
}
function listQuery(type){
    var loading = WST.msg(WST.lang('custompage_loading_data'), {icon: 16,time:60000});
    $.post(WST.AU('custompage://admin/pageQuery'),{type:type},function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            var gettpl = document.getElementById('tblist').innerHTML;
            laytpl(gettpl).render(json.data, function(html){
                $('#page_type_'+type).html(html);
            });
        }
    });
}
function isIndexToggle(id, val,type){
    if(!WST.GRANT.CUSTOMPAGE_CPGL_02)return;
    $.post(WST.AU('custompage://admin/editIsIndex'), {'id':id, 'val':val,'type':type}, function(data, textStatus){
        var json = WST.toAdminJson(data);
        if(json.status=='1'){
            WST.msg(WST.lang("custompage_operation_success"),{icon:1});
            setTimeout(function(){
                location.href=WST.AU('custompage://admin/index','type='+type);
            },1000);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    })
}
function toDel(id,type,isIndex){
    if(!WST.GRANT.CUSTOMPAGE_CPGL_03)return;
    if(isIndex==1){
        WST.msg(WST.lang('custompage_delete_page_tips'),{time:1000,anim: 6});
        return;
    }
	var box = WST.confirm({content:WST.lang("custompage_confirm_delete_page"),yes:function(){
       var loading = WST.msg(WST.lang('custompage_submitting'), {icon: 16,time:60000});
        $.post(WST.AU('custompage://admin/del'),{id:id},function(data,textStatus){
            layer.close(loading);
            var json = WST.toAdminJson(data);
            if(json.status=='1'){
                WST.msg(WST.lang("custompage_operation_success"),{icon:1});
                layer.close(box);
                setTimeout(function(){
                    location.href=WST.AU('custompage://admin/index','type='+type);
                },1000);
            }else{
                WST.msg(json.msg,{icon:2});
            }
        });
    }});
}
function toEdit(id,type){
    location.href=WST.AU('custompage://custompagedecoration/index','id='+id+'&type='+type);
}
function showCustomPageDetail(obj,type){
    if(type!=4){
        $(obj).find('.page-poster-hover').show();
    }else{
        $(obj).find('.pc-page-poster-hover').show();
    }
}
function hideCustomPageDetail(obj,type){
    if(type!=4){
        $(obj).find('.page-poster-hover').hide();
    }else{
        $(obj).find('.pc-page-poster-hover').hide();
    }
}

function copy(id,type){
    var box = WST.confirm({content:WST.lang("custompage_confirm_copy_page"),yes:function(){
        var loading = WST.msg(WST.lang('custompage_submitting'), {icon: 16,time:60000});
        $.post(WST.AU('custompage://admin/copyCustomPage'),{id:id,type:type},function(data,textStatus){
            layer.close(loading);
            var json = WST.toAdminJson(data);
            if(json.status=='1'){
                WST.msg(WST.lang("custompage_operation_success"),{icon:1});
                layer.close(box);
                setTimeout(function(){
                    location.href=WST.AU('custompage://admin/index','type='+type);
                },1000);
            }else{
                WST.msg(json.msg,{icon:2});
            }
        });
    }});
}




