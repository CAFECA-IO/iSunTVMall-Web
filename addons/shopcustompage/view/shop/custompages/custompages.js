var laytpl = layui.laytpl;
function changeCustomPageType(type){
    listQuery(type);
}
function listQuery(type){
    var loading = WST.msg('正在获取数据，请稍后...', {icon: 16,time:60000});
    $.post(WST.AU('shopcustompage://shop/pageQuery'),{type:type},function(data,textStatus){
        layer.close(loading);
        var json = WST.toJson(data);
        if(json.status=='1'){
            var gettpl = document.getElementById('tblist').innerHTML;
            laytpl(gettpl).render(json.data, function(html){
                $('#page_type_'+type).html(html);
            });
        }
    });
}
function isIndexToggle(id, val,type){
    $.post(WST.AU('shopcustompage://shop/editIsIndex'), {'id':id, 'val':val,'type':type}, function(data, textStatus){
        var json = WST.toJson(data);
        if(json.status=='1'){
            WST.msg("操作成功",{icon:1});
            setTimeout(function(){
                location.href=WST.AU('shopcustompage://shop/index','type='+type);
            },1000);
        }else{
            WST.msg(json.msg,{icon:2});
        }
    })
}
function toDel(id,type,isIndex){
    if(isIndex==1){
        WST.msg('使用中的页面不可删除',{time:1000,anim: 6});
        return;
    }
	var box = WST.confirm({content:"您确定要删除该页面吗?",yes:function(){
       var loading = WST.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
        $.post(WST.AU('shopcustompage://shop/del'),{id:id},function(data,textStatus){
            layer.close(loading);
            var json = WST.toJson(data);
            if(json.status=='1'){
                WST.msg("操作成功",{icon:1});
                layer.close(box);
                setTimeout(function(){
                    location.href=WST.AU('shopcustompage://shop/index','type='+type);
                },1000);
            }else{
                WST.msg(json.msg,{icon:2});
            }
        });
    }});
}
function toEdit(id,type){
    location.href=WST.AU('shopcustompage://shopcustompagedecoration/index','id='+id+'&type='+type);
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
    var box = WST.confirm({content:"您确定要复制该页面吗?",yes:function(){
        var loading = WST.msg('正在提交数据，请稍后...', {icon: 16,time:60000});
        $.post(WST.AU('shopcustompage://shop/copyCustomPage'),{id:id,type:type},function(data,textStatus){
            layer.close(loading);
            var json = WST.toJson(data);
            if(json.status=='1'){
                WST.msg("操作成功",{icon:1});
                layer.close(box);
                setTimeout(function(){
                    location.href=WST.AU('shopcustompage://shop/index','type='+type);
                },1000);
            }else{
                WST.msg(json.msg,{icon:2});
            }
        });
    }});
}




