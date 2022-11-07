function logout(){
    $.post(WST.U('mobile/users/logout'),{},function(data,textStatus){
        var json = WST.toJson(data);
        if(data.status==1)
            location.href=WST.U('mobile/users/index');
        else
            WST.msg(WST.lang('unknown_error'),'info');
    });
}